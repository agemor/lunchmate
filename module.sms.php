<?php
include "module.sms.account.php";

/* 모듈 객체 초기화 */
if (!isset($module))
    $module = new class {};

/* 모듈 생성 */
$module->{"sms"} = new class {

    const API_URL = "https://api.bluehouselab.com/smscenter/v1.0/sendsms";

    /* 사용자에게 SMS를 전송한다 */
    public function send($content, $receivers) {

        $data = array(
            "sender" => SMS_SENDER,
            "receivers" => $receivers,
            "content" => $content,
        );

        $options = array(
            CURLOPT_URL => self::API_URL,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => SMS_APPID.":".SMS_APIKEY,
            CURLOPT_HTTPHEADER => Array("Content-Type: application/json; charset=utf-8"),
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_SSL_VERIFYPEER => false  /* DEBUG */
        );

        $session = curl_init();
        curl_setopt_array($session, $options);
        $result = curl_exec($session);

        curl_close($result);

        return $error;
    }
}
?>