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


// 해당자에게 기존에 pending 중인 요청이 있는지 체크. 있으면 취소 후 다시 보내야 함
$sentRequest = $module->db->in("lunchmate_requests")
                 ->select("status")
                 ->where("sender_id", "=", getUserId())
                 ->where("recipient_id", "=", $recipient["student_id"])
                 ->goAndGet();
if ($sentRequest) {
  assertc($sentRequest["status"] != "0", '{"response": false, "message": "duplicate-request"}');
}
// 요청 등록
$response = $module->db->in('lunchmate_requests')
                       ->insert('sender_id', getUserId())
                       ->insert('recipient_id', $recipient["student_id"])
                       ->insert('message', base64_encode(strip_tags($_POST["message"])))
                       ->insert('schedule', $_POST["schedule"])
                       ->insert('final_schedule', "")
                       ->insert('status', "0")
                       ->go();
assertc($response, '{"response": false, "message": "cannot-insert-record"}');

// 알림 보내기
if (intval($recipient["alarm_settings"]) % 7 == 0) {
  $content = "님으로부터 만남 요청이 들어왔습니다. 지금 확인해 보세요!";
  $name = (mb_substr(base64_decode($sender["name_korean"]), 1, 10, "utf-8"))  . "#" . $sender["no"];
  $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone_number"])]);
}

echo '{"response": true}';

?>
