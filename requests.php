<?php
include "session.php";
include "widget.timetable.php";

$location = "requests.php";

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
        .request-title-text {
        color:black;
        text-decoration: none !important;
        }
        th { text-align: center; }
        td { text-align: center; }
        td * { display: inline; }
      </style>
      <div class="container m-y-2">
      <div class="row">
        <div class="col-lg-6">
          <div class="list-group m-b-3">
            <a href="#" class="list-group-item list-group-item-action">
              <p class="list-group-item-heading">
                <span class="tag tag-primary tag-pill">받은 요청</span>
                <strong>ㄱㅎㅈ#1225</strong> <span class="pull-xs-right">오늘</span>
              </p>
              <p class="list-group-item-text text-muted">같이 점심 드실래요?</p>
            </a>
            <a href="#" class="list-group-item list-group-item-action">
              <p class="list-group-item-heading"> <span class="tag tag-default tag-pill">보낸 요청</span>
                <strong>ㅇㅁㅇ#331</strong>  <span class="pull-xs-right">오늘</span>
              </p>
              <p class="list-group-item-text text-muted">탁구 한판 치실래요?</p>
            </a>
            <a href="#" class="list-group-item list-group-item-action disabled">
              <p class="list-group-item-heading">
                <span class="tag tag-default tag-pill">완료된 요청</span> ㄱㅇㅇ#4221</strong>  <span class="pull-xs-right">21일 전</span>
              </p>
              <p class="list-group-item-text text-muted">같이 스파게티 먹어요~</p>
            </a>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-block">
              <h4 ><a href="#" class="request-title-text">ㄱㅎㅈ#1225</a>님의 만남 요청</h4>
              <p>저랑 함께 재밌는 공연 보러 가실래요? 이번에 재밌는거 한대요!</p>
              <button type="button" class="btn btn-secondary">수락하기</button>
              <button type="button" class="btn btn-link">거절하기</button>
              <?php
              echo $widget->timetable->get();
              ?>
            </div>
          </div>
        </div>
      </div>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
