<?php
include "session.php";
include "module.db.php";
include "module.sms.php";

$location = "peoples.php";

$peoples = $module->db->in("lunchmate_users")
                      ->select("no")
                      ->select("student_id")
                      ->select("name_korean")
                      ->select("affiliation")
                      ->select("content")
                      ->orderBy("RAND()")
                      ->limit("20")
                      ->goAndGetAll();

// 내 프로필
if(assigned()) {
    $me = $module->db->in("lunchmate_users")
                     ->select("*")
                     ->where("student_id", "=", getUserId())
                     ->goAndGet();
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
  if(isset($me)) {
      echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-outline-secondary card-block ">';
      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.(mb_substr(base64_decode($me["name_korean"]), 1, 10, "utf-8")).'<small>#'.$me["no"].'</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($me["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p class="card-text">'.base64_decode($me["content"]).'</p>';
      echo '<a href="profile.php" class="btn btn-sm btn-outline-secondary ">프로필 수정하기</a>';
      echo '</div></div>';
  }

  for ($i = 0; $i < count($peoples); $i++) {
      $data = $peoples[$i];
      if (assigned()) {
          if($data["student_id"] == getUserId()) {
              continue;
          }
      }
      $userName = (mb_substr(base64_decode($data["name_korean"]), 1, 10, "utf-8")).'<small>#'.$data["no"].'</small>';

      echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-block ">';
      echo '<input type="hidden" name="no" value="'.$data["no"].'"';

      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.(mb_substr(base64_decode($data["name_korean"]), 1, 10, "utf-8")).'<small>#'.$data["no"].'</small></a>';
      echo '<a class="pull-xs-right namecard-report-button" href="#"><small>신고</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($data["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p>'.base64_decode($data["content"]).'</p>';
      echo '<button type="button" class="btn btn-sm btn-outline-secondary" id="interestButton">+ 관심</button>  ';
      echo '<button type="button" class="btn btn-sm btn-outline-info" id="requestButton" data-toggle="modal" data-target="#myModal"">안녕하세요</button>';
      echo '</div></div>';

  }

  echo '</div>';

?>
<script type="text/javascript">
  $("#requestButton").click(function(event) {
    console.log($(event.target).parent());
  })

</script>

</div>





    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
