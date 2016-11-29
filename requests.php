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
                      ->select("location")
                      ->select("status")
                      ->select("timestamp")
                      ->where("recipient_id", "=", getUserId(), "OR")
                      ->where("sender_id", "=", getUserId())
                      ->orderBy("`timestamp` DESC")
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
        $sentTag = '<span class="tag tag-primary tag-pill">보낸 요청</span>';
        $closedTag = '<span class="tag tag-default tag-pill">완료된 요청</span>';
        $canceledTag = '<span class="tag tag-default tag-pill">닫힌 요청</span>';

        echo '<div class="list-group m-b-3">';

        if (!$requests){
          echo '<div class="card card-block"><p class="text-xs-center p-y-2"><em>아직 주고받은 요청이 없습니다</em></p></div>';
        }

        foreach ($requests as $request) {



          // 내가 보낸사람일경우
          if ($request["sender_id"] == getUserId()) {
            $targetUserId = $request["recipient_id"];
          }

          // 내가 받은사람일경우
          else {
            $targetUserId = $request["sender_id"];
          }

          // 태그 설정
          if ($request["status"] == "0") {
            if ($request["sender_id"] == getUserId()) {
              $displayTag = $sentTag;
            } else {
              $displayTag = $receivedTag;
            }
          } else if ($request["status"] == "1") {
            $displayTag = $closedTag;
          } else if ($request["status"] == "2") {
            $displayTag = $canceledTag;
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
          $targetUserName = (mb_substr($targetUser["name_korean"], 1, 10, "utf-8"));

          

          // 날짜에 맞게 스케줄 처리
          // 받은 시간 보여주기
          $now = new DateTime();
          $now->setTimezone(new DateTimeZone('Asia/Seoul'));
          $then = new DateTime($request["timestamp"], new DateTimeZone('Asia/Seoul'));
          $interval = $now->diff($then);

          // 날짜가 다르면, 스케줄 시프팅
          $schedules = $request["schedule"];
          if ($interval->d > 0) {
            $newScheduleList = array();
            $scheduleList = explode(",", $request["schedule"]);

            for ($i = 0; $i < count($scheduleList); $i++) {
              array_push($newScheduleList, intval($scheduleList[$i]));
            }

            $i = $interval->d;
            while (--$i >= 0) {
              $buffer = array();
              for ($j = 0; $j < count($newScheduleList); $j++) {
                $val = $newScheduleList[$j];
                if ($val % 4 != 0 && $val > 0) {
                  array_push($buffer, $val - 1);
                }
              }
              $newScheduleList = $buffer;
            }
            $schedules = implode(",", $newScheduleList);
          }

          echo '<a href="#" class="list-group-item list-group-item-action requestList"
                data-schedule="'   .$schedules.'"
                data-request-no="' .$request["no"].'"
                data-location="'   .$request["location"].'"
                data-status="'     .$request["status"].'"
                data-message="'    .$request["message"].'"
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
          echo '<p class="list-group-item-text">'.$request["message"].'</p>';
          echo '</a>';
        }

        echo '</div>';
        ?>


        </div>
        <div class="col-lg-6">
          <div class="card">
            <div class="card-block request-card">
              <a class="request-no-text" hidden></a>
              <h4 ><a class="request-name-text">이름</a>#<a class="request-target-no-text"></a><a class="request-title-text">님의 만남 요청</a></h4>
              <p class="request-message-text p-y-2">저랑 함께 재밌는 공연 보러 가실래요? 이번에 재밌는거 한대요!</p>

              <strong><p class="request-location-text">시간과 장소는 문자메시지를 참조해 주세요.</p></strong>

              <button type="button" class="btn btn-secondary" data-toggle="collapse" href="#namecard" aria-expanded="false" aria-controls="namecard">소개 보기</button>
              <button type="button" class="btn btn-primary request-accept-button">수락하기</button>
              <button type="button" class="btn btn-danger request-decline-button">거절하기</button>
              <button type="button" class="btn btn-danger request-cancel-button">취소하기</button>
              <div class="collapse" id="namecard">
                <div class="card m-t-2">
                  <div class="card-block">
                    <p class="text-muted user-content-text"></p>
                    <footer class="blockquote-footer user-affiliation-text"></footer>
                  </div>
                </div>
              </div>

              <?php
              echo $widget->timetable->get();
              ?>
            </div>
            <div class="card-block default-card">
              <p class="text-xs-center p-y-2"><em>선택된 요청이 없습니다</em></p>
            </div>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        // 유저 로드해서 띄워주기
        $(".default-card").show();
        $(".request-card").hide();
        $(".requestList").click(function() {
          var requestData = {
            "no": $(this).data("no"),
            "request-no": $(this).data("request-no"),
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
          $(".requestList").removeClass("active");
          $(this).addClass("active");
          viewRequest(requestData);
        });

        $(".request-decline-button").click(function() {
          var selectedNo = $(".request-target-no-text").text();
          $(".request-decline-button").text("처리 중...");
          sendRequest(selectedNo, 0, "decline", function() {
              $(".request-decline-button").text("거절하기");
          });
        });

        $(".request-cancel-button").click(function() {
          var selectedNo = $(".request-target-no-text").text();
          var requestNo = $(".request-no-text").text();
          $(".request-cancel-button").text("처리 중...");
          sendRequest(selectedNo, 0, "cancel", function() {
              $(".request-cancel-button").text("취소하기");
          },requestNo);
        });

        $(".request-accept-button").click(function() {

          var selectedNo = $(".request-target-no-text").text();
          var selectedScheduleIndex = -1;

          // 하이라이트 된 블록 찾기
          $("#scheduleTable tr").each(function () {
              $('td', this).each(function () {
                  var scheduleIndex = $(this).data("index");
                  if ($(this).hasClass("bg-primary")) {
                      selectedScheduleIndex = scheduleIndex;
                  }
              });
          });

          if (selectedScheduleIndex >= 0) {
            $(".request-accept-button").text("처리 중...");
            sendRequest(selectedNo, selectedScheduleIndex, "accept", function() {
                $(".request-cancel-button").text("수락하기");
            });
          }
        });

        function sendRequest(no, scheduleIndex, action, callback, requestNo) {
          var httpRequest = new XMLHttpRequest();
          var formData  = new FormData();
          formData.append("action", action);
          formData.append("target_no", no);
          formData.append("schedule", scheduleIndex);
          formData.append("request_no", requestNo);

          httpRequest.addEventListener('load', function(event) {
            console.log(httpRequest.responseText);
            var result = JSON.parse(httpRequest.responseText);
            if (result.response) {
              alert(action == "accept" ? "요청을 수락했습니다." : "요청을 취소했습니다.");
              window.location.reload();
            } else {
              
               showMessage("요청 처리에 실패했습니다.");
            }
            callback();
          });
          httpRequest.open('POST', './send-request.php');
          httpRequest.send(formData);
        }

        function viewRequest(requestData) {
          $(".default-card").hide();
          $(".request-card").show();

          $(".request-name-text").text(requestData["name"]);
          $(".request-target-no-text").text(requestData["no"]);
          $(".request-no-text").text(requestData["request-no"]);
          $(".request-message-text").text(requestData["message"]);
          $(".user-affiliation-text").text(requestData["affiliation"]);
          $(".user-content-text").text(requestData["content"]);

          if (requestData["mine"] == 1) {
            $(".request-decline-button").hide();
            $(".request-accept-button").hide();
            if (requestData["status"] == "2") {
              $(".request-cancel-button").hide();
            } else {
              $(".request-cancel-button").show();
            }
            $(".request-title-text").text("님에게 보낸 요청");
          } else {
            if (requestData["status"] == "0") {
              $(".request-decline-button").show();
              $(".request-accept-button").show();
              $(".request-cancel-button").hide();
            } else {
              $(".request-decline-button").hide();
              $(".request-accept-button").hide();
              if (requestData["status"] == "2") {
                $(".request-cancel-button").hide();
              } else {
                $(".request-cancel-button").show();
              }
              
            }
            $(".request-title-text").text("님께서 보낸 요청");
          }

          if (requestData["status"] == "0") {
            $("#scheduleTable").show();
            $(".request-location-text").hide();
          } else if (requestData["status"] == "1"){
            $(".request-location-text").show();
            $("#scheduleTable").hide();
          } else {
            $(".request-location-text").hide();
            $("#scheduleTable").hide();
          }

          // 체크박스 생성
          var schedules = [];
          if (requestData["schedule"] != undefined) {
            schedules = (requestData["schedule"] + "").split(",");
          }
          $("#scheduleTable tr").each(function () {
              $('td', this).each(function () {
                  var scheduleIndex = $(this).data("index") + "";
                  $(this).removeClass("bg-primary");
                  if (schedules.indexOf(scheduleIndex) >= 0 && !$(this).hasClass("table-active")) {
                    $(this).addClass("bg-warning");
                  } else {
                    $(this).removeClass("bg-warning");
                  }
               });
          });
        }

        $("#scheduleTable").on("click", "td", function() {
            $("#scheduleTable tr").each(function () {
              $('td', this).each(function () {
                  var scheduleIndex = $(this).data("index") + "";
                  if ($(this).hasClass("bg-primary")) {
                    $(this).toggleClass("bg-primary bg-warning");
                  }
               });
            });
            if(!$(this).hasClass("table-active") && ($(this).hasClass("bg-warning") || $(this).hasClass("bg-primary"))) {
              $(this).toggleClass("bg-primary bg-warning");
            }
         });

      </script>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
