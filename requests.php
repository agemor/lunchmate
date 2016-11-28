<?php
include "session.php";
include "module.db.php";
include "widget.timetable.php";

$location = "requests.php";

// 로그인 체크
if (!assigned()) {
  header("Location: /");
  exit();
}

// request 리스트 불러오기
$requests = $module->db->in("lunchmate_requests")
                      ->select("no")
                      ->select("sender_id")
                      ->select("recipient_id")
                      ->select("message")
                      ->select("schedule")
                      ->select("final_schedule")
                      ->select("status")
                      ->select("timestamp")
                      ->where("recipient_id", "=", getUserId(), "OR")
                      ->where("sender_id", "=", getUserId())
                      ->limit("20")
                      ->goAndGetAll();


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

        <?php

        $receivedTag = '<span class="tag tag-primary tag-pill">받은 요청</span>';
        $sentTag = '<span class="tag tag-default tag-pill">보낸 요청</span>';
        $closedTag = '<span class="tag tag-default tag-pill">완료된 요청</span>';

        echo '<div class="list-group m-b-3">';

        if (!$requests){
          echo '<div class="card"><p class="text-xs-center" style="padding: 40px"><em>아직 주고받은 요청이 없습니다.</em></p></div>';
        }

        foreach ($requests as $request) {

          // 내가 보낸사람일경우
          if ($request["sender_id"] == getUserId()) {
            $targetUserId = $request["recipient_id"];
            $displayTag = $request["status"] == "0" ? $sentTag : $closedTag;
          } 

          // 내가 받은사람일경우
          else {
            $targetUserId = $request["sender_id"];
            $displayTag = $request["status"] == "0" ? $receivedTag : $closedTag;
          }

          // 이름 읽어오기
          $targetUser = $module->db->in("lunchmate_users")
                             ->select("no")
                             ->select("name_korean")
                             ->select("affiliation")
                             ->select("content")
                             ->select("interests_received")
                             ->select("warnings_received")
                             ->select("timestamp")
                             ->where("student_id", "=", $targetUserId)
                             ->goAndGet();
          $targetUserName = (mb_substr(base64_decode($targetUser["name_korean"]), 1, 10, "utf-8"));

          echo '<a href="#" class="list-group-item list-group-item-action requestList"
                data-schedule="'   .$request["schedule"].'" 
                data-status="'     .$request["status"].'" 
                data-message="'    .base64_decode($request["message"]).'" 
                data-mine="'       .($request["sender_id"] == getUserId()).'" 
                data-name="'       .$targetUserName.'" 
                data-affiliation="'.$targetUser["affiliation"].'" 
                data-content="'    .$targetUser["content"].'" 
                data-interests="'  .$targetUser["interests_received"].'" 
                data-warnings="'   .$targetUser["warnings_received"].'" 
                data-no="'         .$targetUser["no"].'">';
          echo '<p class="list-group-item-heading">';
          echo $displayTag;
                             
          // 이름 보여주기
          echo '  <strong>'.$targetUserName.'</strong>#'.$targetUser["no"].'';

          // 받은 시간 보여주기
          $now = new DateTime();
          $now->setTimezone(new DateTimeZone('Asia/Seoul'));
          $then = new DateTime($request["timestamp"], new DateTimeZone('Asia/Seoul'));
          $interval = $now->diff($then);

          // 하루 범위 안 - 몇시간 전
          if ($interval->days < 1) {

            // 분 범위 안
            if ($interval->h < 1) {
              echo '<span class="pull-xs-right">'.$interval->i.'분 전</span>';
            } else {
              echo '<span class="pull-xs-right">'.$interval->h.'시간 전</span>';
            }
          }

          // 일주일 범위 안 - 몇일 전
          else if ($interval->days < 7) {
            echo '<span class="pull-xs-right">'.$interval->days.'일 전</span>';
          }

          // 이상 - 날짜
          else {
            echo '<span class="pull-xs-right">'.$then->format('Y-m-d H:i').'</span>';
          }
          echo '</p>';

          // 메시지 보여주기
          echo '<p class="list-group-item-text text-muted">'.base64_decode($request["message"]).'</p>';
          echo '</a>';
        }

        echo '</div>';
        ?>


        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-block">
              <h4 ><a href="#" class="request-title-text">ㄱㅎㅈ#1225</a>님의 만남 요청</h4>
              <p class="request-message-text">저랑 함께 재밌는 공연 보러 가실래요? 이번에 재밌는거 한대요!</p>
              <button type="button" class="btn btn-secondary">수락하기</button>
              <button type="button" class="btn btn-link">거절하기</button>
              <?php
              echo $widget->timetable->get();
              ?>
            </div>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        // 유저 로드해서 띄워주기
        // 

        $(".requestList").click(function() {
          var requestData = {
            "no": $(this).data("no"),
            "name": $(this).data("name"),
            "status": $(this).data("status"),
            "mine": $(this).data("mine"),
            "affiliation": $(this).data("affiliation"),
            "content": $(this).data("content"),
            "interests": $(this).data("interests"),
            "warnings": $(this).data("warnings"),
            "message": $(this).data("message"),
            "schedule": $(this).data("schedule")
          }
          viewRequest(requestData);
        });

        function viewRequest(requestData) {
          $(".request-title-text").text(requestData["name"]);
          $(".request-message-text").text(requestData["message"]);
        }

      </script>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
