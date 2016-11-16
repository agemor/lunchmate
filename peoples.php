<?php
include "db.php";
include "session.php";

$location = "peoples.php";

$response = ask("SELECT * FROM `lunchmate_users` ORDER BY RAND() LIMIT 20;");

// 내 프로필
$mesponse;

if(assigned()) {
  $mesponse = askOne("SELECT * FROM `lunchmate_users` WHERE `student_id`='".getUserId()."';");
}

/**
* http://zetawiki.com/wiki/UTF-8_%ED%95%9C%EA%B8%80_%EC%B4%88%EC%84%B1_%EC%B6%94%EC%B6%9C_(PHP)
*/
function utf8_strlen($str) { return mb_strlen($str, 'UTF-8'); }
function utf8_charAt($str, $num) { return mb_substr($str, $num, 1, 'UTF-8'); }
function utf8_ord($ch) {
  $len = strlen($ch);
  if($len <= 0) return false;
  $h = ord($ch{0});
  if ($h <= 0x7F) return $h;
  if ($h < 0xC2) return false;
  if ($h <= 0xDF && $len>1) return ($h & 0x1F) <<  6 | (ord($ch{1}) & 0x3F);
  if ($h <= 0xEF && $len>2) return ($h & 0x0F) << 12 | (ord($ch{1}) & 0x3F) << 6 | (ord($ch{2}) & 0x3F);          
  if ($h <= 0xF4 && $len>3) return ($h & 0x0F) << 18 | (ord($ch{1}) & 0x3F) << 12 | (ord($ch{2}) & 0x3F) << 6 | (ord($ch{3}) & 0x3F);
  return false;
}

function hangeulInitial($str) {
  $cho = array("ㄱ","ㄲ","ㄴ","ㄷ","ㄸ","ㄹ","ㅁ","ㅂ","ㅃ","ㅅ","ㅆ","ㅇ","ㅈ","ㅉ","ㅊ","ㅋ","ㅌ","ㅍ","ㅎ");
  $result = "";
  for ($i=0; $i<utf8_strlen($str); $i++) {
    $code = utf8_ord(utf8_charAt($str, $i)) - 44032;
    if ($code > -1 && $code < 11172) {
      $cho_idx = $code / 588;      
      $result .= $cho[$cho_idx];
    }
  }
  return $result;
}


?>

<!DOCTYPE html>
<html lang="ko">
  <head>
  <?php include "head.php";?>
  </head>
  <body>
    <header>
      <?php include "header.php";?>
    </header>
    <main>

<style type="text/css">
  .namecard-title-text {
    color: #000000;
    text-decoration: none !important;
  }
  .namecard-report-button {
    color: grey;
    font-size: 0.5em;
    margin:auto;
    position: relative;
    text-decoration: none !important;
  }
</style>

<div class="container">



<div class="input-group input-group-lg m-y-2">
      <input type="text" class="form-control" placeholder="검색 키워드">
      <span class="input-group-btn">
        <button class="btn btn-secondary" type="button">찾기</button>
      </span>
    </div>

<div class="alert alert-info alert-dismissible fade in" role="alert">
  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
  <strong>Lunchmate에 처음이신가요?</strong> 여기서 서비스 이용에 관한 사항을 숙지하세요.
</div>


          <?php include "request-modal.php";?>


<?php

  echo '<div class="row">';

  // self page
  if(isset($mesponse)) {
      echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-outline-secondary card-block ">';
      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.hangeulInitial(base64_decode($mesponse["name_korean"])).'<small>#'.$mesponse["no"].'</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($mesponse["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p class="card-text">'.base64_decode($mesponse["content"]).'</p>';
      echo '<a href="profile.php" class="btn btn-sm btn-outline-secondary ">프로필 수정하기</a>';
      echo '</div></div>';
  }

  for ($i = 0; $i < count($response); $i++) {
      $data = $response[$i];
      if (assigned()) {
          if($data["student_id"] == getUserId()) {
              continue;
          }
      }

      echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-block ">';
      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.hangeulInitial(base64_decode($data["name_korean"])).'<small>#'.$data["no"].'</small></a>';
      echo '<a class="pull-xs-right namecard-report-button" href="#"><small>신고</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($data["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p>'.base64_decode($data["content"]).'</p>';
      echo '<button type="button" class="btn btn-sm btn-outline-secondary">+ 관심</button>  ';
      echo '<button type="button" class="btn btn-sm btn-outline-info" data-toggle="modal" data-target="#myModal">안녕하세요</button>';
      echo '</div></div>';

  }

  echo '</div>';

?>


</div>





    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>