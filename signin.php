<?php
include "session.php";
include "module.db.php";
include "module.yonsei.php";

const HOST = "/";

/* 전송된 데이터 래핑 */
$userId = stripslashes($_POST["user-id"]);
$userPassword = $_POST["user-password"];

/* 연세포탈 로그인 시도 */
$yonseiAccount = $module->yonsei->signin($userId, $userPassword);

/* 로그인 실패 */
if (count($yonseiAccount) < 1) {

    // 실패 페이지로 이동
    header('Location: '.HOST.'?result=signin-fail');
    exit();
}

// 계정 조회
$response = $module->db->in('lunchmate_users')
                       ->select('student_id')
                       ->where('student_id', '=', $yonseiAccount["id"])
                       ->goAndGet();


// 이미 존재하는 계정
if ($response) {

    // 연락처 업데이트
    $response = $module->db->in('lunchmate_users')
                           ->update('phone_number', $yonseiAccount["phone"])
                           ->where('student_id', '=', $yonseiAccount["id"])
                           ->go();

    // 세션 등록
    assign($yonseiAccount["id"]);

    // 성공 페이지로 이동
    header('Location: '.HOST.'?result=signin-success');
    exit();
}

// 새로운 계정
else {

    // 계정 생성
    $response = $module->db->in('lunchmate_users')
                           ->insert('student_id', $yonseiAccount["id"])
                           ->insert('name_korean', base64_encode($yonseiAccount["korean"]))
                           ->insert('name_english', $yonseiAccount["english"])
                           ->insert('phone_number', $yonseiAccount["phone"])
                           ->insert('alarm_settings', "105")
                           ->go();

    // 회원가입 성공
    if ($response) {

        // 세션 등록
        assign($yonseiAccount["id"]);

        header('Location: '.HOST.'?result=signup-success');
    }

    // 회원가입 실패
    else {
        header('Location: '.HOST.'?result=signup-fail');
    }
    exit();
}
?>
