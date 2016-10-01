<?php
include "db.php";
include "session.php";

/*
 * 연세포탈 로그인 인증 수행 모듈
 */
const YONSEI_LOGIN_URL = 'https://infra.yonsei.ac.kr/lauth/YLLOGIN.do';
const YONSEI_WACTION = 'aW50bHBvcnRhbA==';
const YONSEI_SCODE = 'bm9lbmNyeXB0';

function getYonseiAuth($id, $password) {
    
    $data = array(
        'id' => $id,
        'pw' => $password,
        'waction' => YONSEI_WACTION,
        'sCode' => YONSEI_SCODE,
        'returl' => 'verification.php'
    );
    
    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data)
        )
    );
    $context = stream_context_create($options);
    $result  = htmlspecialchars(file_get_contents(YONSEI_LOGIN_URL, false, $context));
    
    if ($result === FALSE || strlen($result) < 20 || empty($result)) {
        return array();
    } else {

        $raw = explode("'", explode("gubun5' value='", $result)[1])[0];
        $decoded = urldecode(base64_decode($raw));
        $chunk = explode("|", $decoded);

        $nameKorean = $chunk[1];
        $nameEnglish = $chunk[2];
        $phoneNumber = $chunk[3];

        //echo "국문: ".$nameKorean. "<br/>";
        //echo "영문: ".$nameEnglish. "<br/>";
        //echo "번호: ".$phoneNumber. "<br/>";
        return array($nameKorean, $nameEnglish, $phoneNumber);
    }
}

$userId = stripslashes($_POST["student-id"]);
$userPassword = $_POST["student-password"];
//$location = isset($_POST["page-location"]) ? $_POST["page-location"] : "/";

$authResult = getYonseiAuth($userId, $userPassword);

if (count($authResult) < 1) {
    header('Location: /?fail');
    exit();
}

$response = askOne("SELECT `student_id` FROM `lunchmate`.`lunchmate_users` WHERE `student_id`='".$userId."';");

// 이미 있는 계정
if ($response != null) {

    // 정보 업데이트
    if (strlen($authResult[2]) > 5) {
        $success = tell("UPDATE `lunchmate`.`lunchmate_users` SET  `phone_number`='".$authResult[2]."' WHERE `student_id`='".$userId."';");
    } else {
        $success = true;
    }

    if ($success) {
        header('Location: /?success');
    } else {
        header('Location: /?success-noupdate');
    }

    assign($userId);

    exit();
} 

// 없는 계정
else {
    // 계정 생성
    $success = tell("INSERT INTO `lunchmate`.`lunchmate_users` (student_id, name_korean, name_english, phone_number) VALUES ('".$userId ."', '".base64_encode($authResult[0])."', '".$authResult[1]."', '".$authResult[2]."')");
    if ($success) {
        header('Location: /?new');
        exit();
    } else {
        header('Location: /?new-fail');
        exit();
    }
}
?>