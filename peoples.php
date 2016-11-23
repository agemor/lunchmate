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
                      ->select("interests_received")
                      ->orderBy("RAND()")
                      ->limit("20")
                      ->goAndGetAll();

// 내 프로필
if(assigned()) {
    $me = $module->db->in("lunchmate_users")
                     ->select("*")
                     ->where("student_id", "=", getUserId())
                     ->goAndGet();

    $sentInterests = $module->db->in("lunchmate_interests")
                     ->select("*")
                     ->where("sender_id", "=", getUserId())
                     ->goAndGetAll();
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
<style type="text/css">

@media (min-width: 20em) {
  .card-columns {
    -webkit-column-count: 1;
    -moz-column-count: 1;
    column-count: 1;
  }
}

@media (min-width: 50em) {
  .card-columns {
    -webkit-column-count: 2;
    -moz-column-count: 2;
    column-count: 2;
    }
}

@media (min-width: 75em) {
  .card-columns {
    -webkit-column-count: 3;
    -moz-column-count: 3;
    column-count: 3;
  }
}

.card {
  display: inline-block;
  width: 100%;
}


</style>

<?php include "request-modal.php";?>


<?php

  echo '<div class="card-columns">';

  // self page
  if(isset($me)) {
      //echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-outline-secondary card-block ">';
      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.(mb_substr(base64_decode($me["name_korean"]), 1, 10, "utf-8")).'<small>#'.$me["no"].'</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($me["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p class="card-text">'.base64_decode($me["content"]).'</p>';
      echo '<button type="button" class="btn btn-sm btn-outline-secondary" id="interestButton" disabled>좋아요 '.$me["interests_received"].'</button>  ';
      echo '<a href="profile.php" class="btn btn-sm btn-outline-secondary ">프로필 수정하기</a>';
      echo '</div>';
      //echo '</div>';
  }
  for ($i = 0; $i < count($peoples); $i++) {
      $data = $peoples[$i];
      if (assigned()) {
          if($data["student_id"] == getUserId()) {
              continue;
          }
      }
      $userName = (mb_substr(base64_decode($data["name_korean"]), 1, 10, "utf-8"));

      //echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card card-block">';

      echo '<h4 class="card-title"><a class="namecard-title-text" href="#">'.$userName.'<small>#'.$data["no"].'</small></a>';
      echo '<a class="pull-xs-right namecard-report-button" href="#"><small>신고</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted">'.base64_decode($data["affiliation"]).'</h6>';
      echo '<hr>';
      echo '<p>'.base64_decode($data["content"]).'</p>';

      // 이미 interest 준 버튼은 class를 다르게 함
      $interested = false;
      foreach ($sentInterests as $sentInterest) {
          if ($sentInterest['recipient_id'] == $data["student_id"]) {
            $interested = true;
            break;
          }
      }

      echo '<button type="button" class="btn btn-sm '.($interested ? "btn-primary" : "btn-outline-secondary").' interest-button" data-no="'.$data["no"].'">좋아요 '.$data["interests_received"].'</button>  ';
      echo '<button type="button" class="btn btn-sm btn-outline-info request-button" data-toggle="modal" data-target="#requestModal" data-name="'.$userName.'" data-no="'.$data["no"].'">안녕하세요</button>';
      echo '</div>';
     //echo '</div>';

  }

  echo '</div>';

?>
<script type="text/javascript">

  $(".request-button").click(function(event) {
    setRequestModal($(this).data("name"), $(this).data("no"));
  });

  $(".request-send-button").click(function(event) {

    // 메시지 읽어오기
    var message = $("#requestModalMessageInput").val();

    // 스케줄 리스트 생성
    var schedule = [];
    $("#scheduleTable tr").each(function () {
        $('td', this).each(function () {
            if($(this).hasClass("bg-primary")) {
              schedule.push($(this).data("index"));
            }
         })
    });

    if (schedule.length < 1) return;

    sendRequest(userNo, message, schedule.join());
    $(".request-send-button").text("처리중...");
    //setRequestModal($(this).data("name"), $(this).data("no"));
  });

  $(".interest-button").click(function(event) {
    $(this).text("처리 중...");
    sendInterest($(this).data("no"), this);
  });

  $("#scheduleTable").on("click", "td", function() {
      if(!$(this).hasClass("table-active"))
      $(this).toggleClass("bg-primary");
   });

  var userName;
  var userNo;

  function setRequestModal(name, no) {
    userName = name;
    userNo = no;
    $('.modal-title').text(name + "님에게 요청 보내기");
    $("#requestModalMessageInput").val("");
    // 테이블 선택 초기화
    $("#scheduleTable tr").each(function () {
        $('td', this).each(function () {
            //console.log($(this).data("index"));
            $(this).removeClass("bg-primary");
         })
    });
  }

  function sendRequest(targetNo, message, schedule) {
    var httpRequest = new XMLHttpRequest();
    var formData  = new FormData();
    formData.append("target_no", targetNo);
    formData.append("message", message);
    formData.append("schedule", schedule);

    httpRequest.addEventListener('load', function(event) {
      //$("#requestModal").hide();
      $('#requestModal').modal('hide');
      alert(httpRequest.responseText)
      showMessage(userName + "님에게 요청을 보냈습니다!");
      //var result = JSON.parse(httpRequest.responseText);
    });
    httpRequest.open('POST', './send-request.php');
    httpRequest.send(formData);
  }

  function sendInterest(targetNo, buttonRef) {
    var httpRequest = new XMLHttpRequest();
    var formData  = new FormData();

    formData.append("target_no", targetNo);
    httpRequest.addEventListener('load', function(event) {
      var result = JSON.parse(httpRequest.responseText);
      if (result.response) {
        $(buttonRef).text("좋아요 " +result.interests);
        $(buttonRef).toggleClass("btn-primary btn-outline-secondary");
      } else {
        $(buttonRef).text("처리 실패");
      }
    });
    httpRequest.open('POST', './send-interest.php');
    httpRequest.send(formData);
  }

</script>

</div>





    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
