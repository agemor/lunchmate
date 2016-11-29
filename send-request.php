<?php
include "session.php";
include "module.db.php";
include "module.sms.php";
include "widget.timetable.php";


// 기본적인 assert 함수
function assertc($condition, $text) {
  if (!$condition) {
    echo $text;
    exit();
  }
}

// 로그인 체크
assertc(assigned(), '{"response": false, "message": "signin-required"}');

// 액션 체크
assertc(isset($_POST["action"]), '{"response": false, "message": "no-action"}');

// 기여자 로드
$sender = $module->db->in("lunchmate_users")
                 ->select("no")
                 ->select("name_korean")
                 ->select("phone_number")
                 ->select("alarm_settings")
                 ->where("student_id", "=", getUserId())
                 ->goAndGet();
assertc($sender, '{"response": false, "message": "cannot-load-sender"}');


// 수혜자 로드
$useTargetNo = false;
if (!isset($_POST["target_id"])) {
  // id가 없으면 no라도 있어야한다.
  assertc(isset($_POST["target_no"]), '{"response": false, "message": "not-enough-parameters"}');
  $useTargetNo = true;
}

$recipient = $module->db->in("lunchmate_users")
                 ->select("no")
                 ->select("name_korean")
                 ->select("phone_number")
                 ->select("student_id")
                 ->select("alarm_settings")
                 ->where(($useTargetNo ? "no" : "student_id"), "=", ($useTargetNo ? $_POST["target_no"] : $_POST["target_id"]))
                 ->goAndGet();
assertc($recipient, '{"response": false, "message": "cannot-load-recipient"}');

// 자기 자신에 대한 수혜는 금지
assertc($recipient["no"] != $sender["no"], '{"response": false, "message": "self"}');

// 데이터 체크 후, 수락, 삭제 등도 여기서 구현
if ($_POST["action"] == "request") {

  // 해당자에게 기존에 pending 중인 요청이 있는지 체크. 있으면 취소 후 다시 보내야 함
  $sentRequest = $module->db->in("lunchmate_requests")
                   ->select("status")
                   ->where("sender_id", "=", getUserId())
                   ->where("recipient_id", "=", $recipient["student_id"])
                   ->goAndGet();
  if ($sentRequest) {
    assertc($sentRequest["status"] != "0", '{"response": false, "message": "duplicate-request"}');
  }

  // 파라미터 체크
  assertc(isset($_POST["schedule"]), '{"response": false, "message": "not-enough-parameters"}');
  assertc(isset($_POST["message"]), '{"response": false, "message": "not-enough-parameters"}');

  // 요청 등록
  $response = $module->db->in('lunchmate_requests')
                         ->insert('sender_id', getUserId())
                         ->insert('recipient_id', $recipient["student_id"])
                         ->insert('message', strip_tags($_POST["message"]))
                         ->insert('schedule', $_POST["schedule"])
                         ->insert('final_schedule', "")
                         ->insert('status', "0")
                         ->go();
  assertc($response, '{"response": false, "message": "cannot-insert-record"}');

  // 알림 보내기
  if (intval($recipient["alarm_settings"]) % 7 == 0) {
    $content = "님으로부터 만남 요청이 들어왔습니다. 지금 확인해 보세요!";
    $name = (mb_substr($sender["name_korean"], 1, 10, "utf-8"))  . "#" . $sender["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone_number"])]);
  }
}


else if ($_POST["action"] == "accept" || $_POST["action"] == "decline") {

  // 변수 체크
  $sentRequest = $module->db->in("lunchmate_requests")
                   ->select("status")
                   ->select("no")
                   ->select("timestamp")
                   ->where("sender_id", "=", $recipient["student_id"])
                   ->where("recipient_id", "=", getUserId())
                   ->where("status", "=", "0")
                   ->goAndGet();
  //
  // 해당자에게 기존에 pending 중인 요청이 있는지 체크. 있어야 추후 진행 가능.
                   //var_dump($module->db->rq());
  assertc($sentRequest, '{"response": false, "message": "no-avaliable-request"}');
  assertc($sentRequest["status"] == "0", '{"response": false, "message": "no-avaliable-request"}');

  // 파라미터 체크
  assertc(isset($_POST["schedule"]), '{"response": false, "message": "not-enough-parameters"}');

  // 수락
  if ($_POST["action"] == "accept") {

    $LANDMARKS = array(
      0=>"G동 3층 소파 앞(치계 위)",
      1=>"2기숙사 1층 예배실 앞(치계 앞)",
      2=>"2기숙사 구 본뜰샘 앞(생협 후문 옆)",
      3=>"D동 택배보관실",
      4=>"D동E동 구름다리",
      5=>"델리코 앞 스피드게이트",
      6=>"A동 택배보관실",
      7=>"C동 로비",
      8=>"A동 로비 자판기 앞",
      9=>"언기도 Y플라자 Y갤러리 A 앞",
      10=>"Y플라자 우체국 앞"
      );

    // 시간 라벨
    $time = new DateTime(null, new DateTimeZone('Asia/Seoul'));
    $time->modify('+'.(intval($_POST["schedule"]) % 4).' day');
    $dayLabel = $widget->timetable->getDayLabel(intval($time->format("w")));
    $timeLabel = $widget->timetable->getTimeLabel(intval($_POST["schedule"]));

    // 랜드마크 취득

    // 해당 시간대의 모든 랜드마크 가져오기
    $landmarks = $module->db->in('lunchmate_landmarks')
                           ->select("landmark")
                           ->where("date", "=", $time->format("Ymd"))
                           ->where("time", "=", intval(intval($_POST["schedule"]) / 4))
                           ->goAndGetAll();

    //assertc($response, '{"response": false, "message": "cannot-fetch-landmarks"}');
    $landmarkList = $LANDMARKS;
    $landmarkExcludeList = array();
    foreach ($landmarks as $landmark) {
        array_push($landmarkExcludeList, intval($landmark["landmark"]));
    }
    $landmarkAvailableList = array_diff($landmarkList, $landmarkExcludeList);
    $selectedLandmark = array_rand($landmarkAvailableList);

    $meetingLocation = $LANDMARKS[$selectedLandmark];

    // 랜드마크 설정
    $response = $module->db->in('lunchmate_landmarks')
                         ->insert('landmark', $selectedLandmark)
                         ->insert('request_no', $sentRequest["no"])
                         ->insert('date', $time->format("Ymd"))
                         ->insert('time', intval(intval($_POST["schedule"]) / 4))
                         ->go();

    assertc($response, '{"response": false, "message": "cannot-insert-landmarks"}');

    // 요청 업데이트
    $response = $module->db->in('lunchmate_requests')
                           ->update('final_schedule', $_POST["schedule"])
                           ->update('location', $meetingLocation)
                           ->update('status', "1")
                           ->where("no", "=", $sentRequest["no"])
                           ->go();
    //var_dump($module->db->rq());
    assertc($response, '{"response": false, "message": "cannot-update-record"}');

    

    // 알림 보내기
    $content = "님과 (".$time->format("m/d").") ".$dayLabel."요일 ". $timeLabel." ".$meetingLocation;

    // 프로필 알림 설정 보고, sms 전송
    if (intval($sender["alarm_settings"]) % 7 == 0) {
      $name = (mb_substr($recipient["name_korean"], 1, 10, "utf-8")) . "#" . $recipient["no"];
      $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $sender["phone_number"])]);
    }

    if (intval($recipient["alarm_settings"]) % 7 == 0) {
      $name = (mb_substr($sender["name_korean"], 1, 10, "utf-8"))  . "#" . $sender["no"];
      $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone_number"])]);
    }
  } 

  // 거절
  else {
    // 요청 업데이트
    $response = $module->db->in('lunchmate_requests')
                           ->update('final_schedule', $_POST["schedule"])
                           ->update('status', "2")
                           ->where("no", "=", $sentRequest["no"])
                           ->go();
    assertc($response, '{"response": false, "message": "cannot-update-record"}');

    $content = "님이 요청을 거절하셨습니다.";
    if (intval($sender["alarm_settings"]) % 7 == 0) {
      $name = (mb_substr($recipient["name_korean"], 1, 10, "utf-8")) . "#" . $recipient["no"];
      $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $sender["phone_number"])]);
    }
  }
} 

// 요청 취소
else if ($_POST["action"] == "cancel") {

  // 변수 체크
  assertc(isset($_POST["request_no"]), '{"response": false, "message": "not-enough-parameters"}');

  $request = $module->db->in("lunchmate_requests")
                   ->select("status")
                   ->select("no")
                   ->select("timestamp")
                   //->where("sender_id", "=", getUserId(), "OR")  // 해킹 방지
                   //->where("recipient_id", "=", getUserId(), "AND") // 해킹 방지
                   ->where("no", "=", $_POST["request_no"])
                   ->goAndGet();
//var_dump($module->db->rq());
  // 해당자에게 기존에 pending 중인 요청이 있는지 체크. 있어야 추후 진행 가능.
  assertc($request, '{"response": false, "message": "no-avaliable-request"}');
  
  // 요청 업데이트
  $response = $module->db->in('lunchmate_requests')
                         ->update('status', "2")
                         ->where("no", "=", $request["no"])
                         ->go();
    //var_dump($module->db->rq());
  assertc($response, '{"response": false, "message": "cannot-update-record"}');

  // 랜드마크 삭제
  $response = $module->db->in('lunchmate_landmarks')
                         ->delete()
                         ->where("request_no", "=", $request["no"])
                         ->go();
  assertc($response, '{"response": false, "message": "cannot-delete-landmarks"}');

  // 프로필 알림 설정 보고, sms 전송

  $content = "님과의 약속이 취소되었습니다.";

  if (intval($sender["alarm_settings"]) % 7 == 0) {
    $name = (mb_substr($recipient["name_korean"], 1, 10, "utf-8")) . "#" . $recipient["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $sender["phone_number"])]);
  }

  if (intval($recipient["alarm_settings"]) % 7 == 0) {
    $name = (mb_substr($sender["name_korean"], 1, 10, "utf-8"))  . "#" . $sender["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone_number"])]);
  }

}


echo '{"response": true}';

?>
