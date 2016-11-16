<?php

/* 모듈 객체 초기화 */
if (!isset($module))
    $module = new class {};

/* 모듈 생성 */
$module->{"yonsei"} = new class {

    const YONSEI_LOGIN_URL = 'https://infra.yonsei.ac.kr/lauth/YLLOGIN.do';
    const YONSEI_WACTION = 'aW50bHBvcnRhbA==';
    const YONSEI_SCODE = 'bm9lbmNyeXB0';

    /* 연세포탈 로그인 체크 */
    public function signin($id, $password) {

        $data = array(
            'id' => $id,
            'pw' => $password,
            'waction' => self::YONSEI_WACTION,
            'sCode' => self::YONSEI_SCODE,
            'returl' => 'verification.php'
        );

        $result = $this->fetchUrl(self::YONSEI_LOGIN_URL, $data);
        $result = htmlspecialchars($result);

        if ($result === FALSE || strlen($result) < 20 || empty($result)) {
            return array();
        } else {

            $raw = explode("'", explode("gubun5' value='", $result)[1])[0];
            $decoded = urldecode(base64_decode($raw));
            $chunk = explode("|", $decoded);

            $nameKorean = $chunk[1];
            $nameEnglish = $chunk[2];
            $phoneNumber = $chunk[3];

            return array("id" => $id,
                         "korean" => $nameKorean, 
                         "english" => $nameEnglish,
                         "phone" => $phoneNumber);
        }
    }

    /* CURL을 이용하여 특정 URL으로부터 값을 읽어온다 */
    private function fetchUrl($url, $parameters) {
        
        $options = array(
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($parameters) ,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false /* DEBUG */
        );

        $session = curl_init();
        curl_setopt_array($session, $options);

        $result = curl_exec($session);
        curl_close($session);

        return $result;
    }
}
?>