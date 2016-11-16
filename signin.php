<?php
include "session.php";
include "module.db.php";
include "module.yonsei.php";

const HOST = "http://www.lunchmate.co.kr/";

/* 전송된 데이터 래핑 */
$userId = stripslashes($_POST["user-id"]);
$userPassword = $_POST["user-password"];

/* 연세포탈 로그인 시도 */
$yonseiAccount = $module->yonsei->signin($userId, $userPassword);

/* 로그인 실패 */
if (count($yonseiAccount) < 1) {

    // 실패 페이지로 이동
    header('Location: '.HOST.'?signin-fail');
    exit();
}

// 계정 조회
//$query = "SELECT `student_id` FROM `lunchmate_users` WHERE `student_id`='".$yonseiAccount["id"]."';";
// $response = $module->db->goAndGet($query);

$response = $module->db->in('lunchmate_users')
                       ->select('student_id')
                       ->where('student_id', '=', $yonseiAccount["id"])
                       ->goAndGet();


// 이미 존재하는 계정
if ($response) {

    // 연락처 업데이트
    //$query = "UPDATE `lunchmate_users` SET  `phone_number`='".$yonseiAccount["phone"]."' WHERE `student_id`='".$yonseiAccount["id"]."';";
    //$response = $module->db->go($query);
    $response = $module->db->in('lunchmate_users')
                           ->update('phone_number', $yonseiAccount["phone"])
                           ->where('student_id', '=', $yonseiAccount["id"])
                           ->go();

    // 세션 등록
    assign($yonseiAccount["id"]);

    // 성공 페이지로 이동
    header('Location: '.HOST.'?signin-success');
    exit();
} 

// 새로운 계정
else {

    // 계정 생성
    //$query = "INSERT INTO `lunchmate_users` (student_id, name_korean, name_english, phone_number) VALUES ('"
    //        .$yonseiAccount["id"] ."', '".base64_encode($yonseiAccount["korean"])."', '".$yonseiAccount["english"]."', '".$yonseiAccount["phone"]."')";
    //$response = $module->db->go($query);

    $response = $module->db->in('lunchmate_users')
                           ->insert('student_id', $yonseiAccount["id"])
                           ->insert('name_korean', base64_encode($yonseiAccount["korean"])))
                           ->insert('name_english', $yonseiAccount["english"])
                           ->insert('phone_number', $yonseiAccount["phone"])
                           ->go();

    // 회원가입 성공
    if ($response) {
        header('Location: '.HOST.'?signup-success'); 
    }

    // 회원가입 실패
    else {
        header('Location: '.HOST.'?signup-fail');
    }
    exit();
}
?>