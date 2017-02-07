<?php
include "session.php";
include "module.db.php";
include "module.sms.php";

$location = "peoples.php";


// 키워드가 있으면
function fetchAll() {
  global $module;
  return $module->db->in("lunchmate_users")
                        ->select("no")
                        ->select("student_id")
                        ->select("name_korean")
                        ->select("affiliation")
                        ->select("content")
                        ->select("interests_received")
                        ->orderBy("RAND()")
                        ->limit("20")
                        ->goAndGetAll();
}

function fetchKeyword($rawKeywords) {
  global $module;
  $keywords = explode(" ", $rawKeywords);
  $query = $module->db->in("lunchmate_users")
                      ->select("no")
                      ->select("student_id")
                      ->select("name_korean")
                      ->select("affiliation")
                      ->select("content")
                      ->select("interests_received");

  for ($i = 0; $i < count($keywords); $i++) {
      if (strlen($keywords[$i]) < 2) {
          continue;
      }
      $query = $query->where("name_korean", "LIKE", "%".$keywords[$i]."%", "OR")
                     ->where("affiliation", "LIKE", "%".$keywords[$i]."%", "OR")
                     ->where("content", "LIKE", "%".$keywords[$i]."%", "OR");
  }
  $result = $query->limit("20")
                  ->orderBy("RAND()")
                  ->goAndGetAll();
  return $result;
}

$peoples = isset($_GET["keyword"]) ? fetchKeyword($_GET["keyword"]) : fetchAll();


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

<div class="card m-y-2"><div class="card-block">
<h4 class="card-title">런치메이트에 처음이신가요?</h4>

<a type="button" class="btn btn-lg btn-primary" href="./about.php">사용설명서 읽기</a>

</div></div>


<div class="card "><div class="card-block">
<h4 class="card-title">검색 상자</h4>
<form action="./peoples.php" method="get">
<div class="md-form input-group m-y-0">
    <input type="text" class="form-control" name="keyword" placeholder="이름, 소속, 자기소개" value="<?php echo (isset($_GET["keyword"]) ? $_GET["keyword"] : "");?>">
    <span class="input-group-btn">
        <button type="submit" class="btn btn-primary btn-lg" type="button"><i class="fa fa-search"></i></button>
    </span>
</div>
</form>
</div></div>

<p style="margin-top: 4em; margin-bottom: 4em;" class="text-xs-center text-muted">  검색하지 않을 경우 무작위로 20명의 정보가 보여집니다. <strong>새로 보시려면 새로고침하세요.</strong> 
 </p>


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
      echo '<div class="card card-outline-secondary card-block m-t-1">';
      echo '<h4 class="card-title"><span class="namecard-title-text">'.(mb_substr($me["name_korean"], 1, 10, "utf-8")).'<small>#'.$me["no"].'</small></span></h4>';
      echo '<h6 class="card-subtitle text-muted m-t-1">'.$me["affiliation"].'</h6>';
      echo '<hr>';
      echo '<p class="card-text">'.$me["content"].'</p>';
      echo '<button type="button" class="btn btn-sm btn-info p-x-1" id="interestButton" disabled><i class="fa fa-star-o"></i>&nbsp;&nbsp;'.$me["interests_received"].'</button>  ';
      echo '<a href="profile.php" class="btn btn-sm btn-primary p-x-1">프로필 수정하기</a>';
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
      $userName = (mb_substr($data["name_korean"], 1, 10, "utf-8"));

      //echo '<div class="col-sm-6 col-md-4 col-lg-3">';
      echo '<div class="card m-y-1"><div class="card-block">';

      echo '<h4 class="card-title"><span class="namecard-title-text">'.$userName.'<small>#'.$data["no"].'</small></span>';
      echo '<a class="pull-xs-right namecard-report-button" href="#"><small>신고</small></a></h4>';
      echo '<h6 class="card-subtitle text-muted  m-t-1">'.$data["affiliation"].'</h6>';
      echo '<hr>';
      echo '<p class="card-text">'.$data["content"].'</p>';


      if (assigned()) {
        // 이미 interest 준 버튼은 class를 다르게 함
        $interested = false;
        foreach ($sentInterests as $sentInterest) {
            if ($sentInterest['recipient_id'] == $data["student_id"]) {
              $interested = true;
              break;
            }
        }
        echo '<button type="button" class="btn btn-sm interest-button p-x-1 '.($interested ? "btn-indigo" : "btn-info").'" data-no="'.$data["no"].'"><i class="fa fa-star-o"></i>&nbsp;&nbsp;<span class="interest-text">'.$data["interests_received"].'</span></button>  ';
        echo '<button type="button" class="btn btn-sm btn-primary request-button p-x-1" data-toggle="modal" data-target="#requestModal" data-name="'.$userName.'" data-no="'.$data["no"].'">만나요!</button>';
      } else {
        echo '<button type="button" class="btn btn-sm btn-info" disabled><i class="fa fa-star-o"></i>&nbsp;&nbsp;'.$data["interests_received"].'</button>  ';
      }
      echo '</div></div>';
  }

  echo '</div>';

?>
<script type="text/javascript">

  <?php
    if (assigned()) {
          if(strlen($me["content"]) < 5 ) {
              echo 'showMessage("프로필 내용이 부족합니다. 조금 더 채워 주세요!");';
          }
      }
  ?>

  $(".request-button").click(function(event) {
    //$('#requestModal').modal('show');
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
    $(this).prop('disabled', true);
    $(this).find(".interest-text").text("처리중...");
    //$("input").

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
    formData.append("action", "request");
    formData.append("target_no", targetNo);
    formData.append("message", message);
    formData.append("schedule", schedule);

    httpRequest.addEventListener('load', function(event) {
      $(".request-send-button").text("요청 보내기");
      $('#requestModal').modal('hide');

      var result = JSON.parse(httpRequest.responseText);
      if (result.response) {
        showMessage(userName + "님에게 요청을 보냈습니다!");
      } else {
        if (result.message == "duplicate-request") {
          showMessage("이미 요청을 보낸 상태입니다. 다시 요청하시려면 보낸 요청을 취소해 주세요.");
        } else {
          showMessage("요청을 보내지 못했습니다. 다시 시도해 주세요.");
        }
      }
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
        $(buttonRef).prop('disabled', false);
        $(buttonRef).find(".interest-text").text(result.interests);
        //$(buttonRef).text(result.interests);
        $(buttonRef).toggleClass("btn-info btn-indigo");
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
