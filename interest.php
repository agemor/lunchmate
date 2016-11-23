<?php

exit("qegqe");
include "session.php";
include "module.db.php";
include "module.sms.php";

echo "qeg";

// 기본적인 assert 함수
function assert($condition) {
  if (!$condition) {
    echo 'fail';
    exit();
  }
}

// 로그인 체크
assert(assigned());

// 기여자 로드
$sender = $module->db->in("lunchmate_users")
                 ->select("no")
                 ->select("name_korean")
                 ->select("interests_credit")
                 ->select("interests_sent")
                 ->select("alarm_settings")
                 ->where("student_id", "=", getUserId())
                 ->goAndGet();
assert($sender);

// 수혜자 로드
$useTargetNo = false;
if (!isset($_POST["target_id"])) {
  // id가 없으면 no라도 있어야한다.
  assert(isset($_POST["target_no"]));
  $useTargetNo = true;
}

$recipient = $module->db->in("lunchmate_users")
                 ->select("no")
                 ->select("name_korean")
                 ->select("student_id")
                 ->select("interests_received")
                 ->select("alarm_settings")
                 ->where(($useTargetNo ? "no" : "student_id"), "=", ($useTargetNo ? $_POST["target_no"] : $_POST["target_id"]))
                 ->goAndGet();
assert($recipient);


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
  echo "no-credit";
  exit();
}

// 내 정보에서 크레딧 차감 + 준 interest 수 올리기 작업
$response = $module->db->in('lunchmate_users')
                       ->update('interests_credit', intval($sender["interests_credit"]) + ($minusFlag ? 0 : -1))
                       ->update('interests_sent', intval($sender["interests_sent"]) + ($minusFlag ? -1 : 1))
                       ->where('student_id', '=', getUserId())
                       ->go();
assert($response);


// 상대방 정보에서 interest 수 올리기
$response = $module->db->in('lunchmate_users')
                       ->update('interests_received', intval($recipient["interests_received"]) + ($minusFlag ? -1 : 1))
                       ->where('student_id', '=', getUserId())
                       ->go();
assert($response);

// interest record에 기록 남기기
$response = $module->db->in('lunchmate_interests')
                       ->insert('sender_id', getUserId())
                       ->insert('recipient_id', $recipient["student_id"])
                       ->go();
assert($response);

// 만약 상대방 interest record에 내가 있으면
$interestLog = $module->db->in("lunchmate_interests")
                 ->select("no")
                 ->where("recipient_id", "=", getUserId())
                 ->where("sender_id", "=", $recipient["student_id"])
                 ->goAndGet();

// 쌍방 선호
if ($interestLog) {

  $content = "님과 서로 관심을 주고받았습니다. 만남 요청을 보내보는 건 어떠신가요?";

  // 프로필 알림 설정 보고, sms 전송
  if (intval($sender["alarm_settings"]) % 3 == 0) {
    $name = base64_decode($recipient["name_korean"]) . "#" . $recipient["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $sender["phone"])]);
  }

  if (intval($recipient["alarm_settings"]) % 3 == 0) {
    $name = base64_decode($sender["name_korean"]) . "#" . $sender["no"];
    $module->sms->send("[런치메이트] ".$name.$content, [str_replace("-", "", $recipient["phone"])]);
  }
}


// 결과 리턴
echo 'success';
?>
