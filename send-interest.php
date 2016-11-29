<?php
include "session.php";
include "module.db.php";
include "module.sms.php";

// 기본적인 assert 함수
function assertc($condition, $text) {
  if (!$condition) {
    echo $text;
    exit();
  }
}

// 로그인 체크
assertc(assigned(), '{"response": false, "message": "signin-required"}');

// 기여자 로드
$sender = $module->db->in("lunchmate_users")
                 ->select("no")
                 ->select("name_korean")
                 ->select("phone_number")
                 ->select("interests_credit")
                 ->select("interests_sent")
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
                 ->select("interests_received")
                 ->select("alarm_settings")
                 ->where(($useTargetNo ? "no" : "student_id"), "=", ($useTargetNo ? $_POST["target_no"] : $_POST["target_id"]))
                 ->goAndGet();
assertc($recipient, '{"response": false, "message": "cannot-load-recipient"}');

// 자기 자신에 대한 수혜는 금지
assertc($recipient["no"] != $sender["no"], '{"response": false, "message": "self"}');

// 이미 interest 준 사람인지 체크 -> 감소 작업 플래그 on
$interestLog = $module->db->in("lunchmate_interests")
                 ->select("no")
                 ->where("sender_id", "=", getUserId())
                 ->where("recipient_id", "=", $recipient["student_id"])
                 ->goAndGet();

$minusFlag = false;

// 이미 준 사람
if ($interestLog) {
  $minusFlag = true;
}

// 증가형일 경우 크레딧 필요
if (!$minusFlag && intval($sender["interests_credit"]) < 1 ) {
  echo '{"response": false, "message": "no-credit"}';
  exit();
}

// 내 정보에서 크레딧 차감 + 준 interest 수 올리기 작업
$response = $module->db->in('lunchmate_users')
                       ->update('interests_credit', intval($sender["interests_credit"]) + ($minusFlag ? 0 : -1))
                       ->update('interests_sent', intval($sender["interests_sent"]) + ($minusFlag ? -1 : 1))
                       ->where('student_id', '=', getUserId())
                       ->go();
assertc($response, '{"response": false, "message": "cannot-update-sender"}');


// 상대방 정보에서 interest 수 올리기
$response = $module->db->in('lunchmate_users')
                       ->update('interests_received', intval($recipient["interests_received"]) + ($minusFlag ? -1 : 1))
                       ->where('student_id', '=', $recipient["student_id"])
                       ->go();
assertc($response, '{"response": false, "message": "cannot-update-recipient"}');

// 레코드 지우기
if ($minusFlag) {

  $response = $module->db->in('lunchmate_interests')
                         ->delete()
                         ->where("sender_id", "=", getUserId())
                         ->where("recipient_id", "=", $recipient["student_id"])
                         ->go();
  assertc($response, '{"response": false, "message": "cannot-delete-record"}');

  echo '{"response": true, "type": "decrease", "interests": '.(intval($recipient["interests_received"]) - 1).'}';
  exit();
}

// interest record에 기록 남기기
else {
  $response = $module->db->in('lunchmate_interests')
                         ->insert('sender_id', getUserId())
                         ->insert('recipient_id', $recipient["student_id"])
                         ->go();
  assertc($response, '{"response": false, "message": "cannot-insert-record"}');
}

// 만약 상대방 interest record에 내가 있으면
$interestLog = $module->db->in("lunchmate_interests")
                 ->select("no")
                 ->where("recipient_id", "=", getUserId())
                 ->where("sender_id", "=", $recipient["student_id"])
                 ->goAndGet();

// 쌍방 선호
if ($interestLog) {

  $content = "님과 서로 관심을 주고받았습니다. 만남 요청을 보내보세요!";
  // 프로필 알림 설정 보고, sms 전송
  if (intval($sender["alarm_settings"]) % 3 == 0) {
    $name = (mb_substr($recipient["name_korean"], 1, 10, "utf-8")) . "#" . $recipient["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $sender["phone_number"])]);
  }

  if (intval($recipient["alarm_settings"]) % 3 == 0) {
    $name = (mb_substr($sender["name_korean"], 1, 10, "utf-8"))  . "#" . $sender["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone_number"])]);
  }
}


// 결과 리턴
echo '{"response": true, "type": "increase", "interests": '.(intval($recipient["interests_received"]) + 1).'}';
?>
