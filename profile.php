<?php
include "db.php";
include "session.php";

$location = "profile.php";
$success = true;
$message = "";
$profileChanged = false;

if (!assigned()) {
  header("Location: /");
  exit();
}

// 설정 모드
if (isset($_POST["setting-type"])) {

  // 알람 옵션 설정
  if ($_POST["setting-type"] == "alarm") {
      $settingsValue = (isset($_POST["mutual-alarm"]) ? 3 : 1) * (isset($_POST["schedule-fix-alarm"]) ? 5 : 1) * 7;
      $success = tell("UPDATE `lunchmate_users` SET  `alarm_settings`='".$settingsValue."' WHERE `student_id`='".getUserId()."';");
      if (!$success) {
        $message = "알림 설정을 업데이트하는데 오류가 발생했습니다.";
      }
  }

  // 소개
  else {
    $content = strip_tags($_POST["profile-content"]);
    $affiliation = strip_tags($_POST["profile-affiliation"]);
    if (mb_strlen($content, 'utf-8') > 210 || mb_strlen($affiliation, 'utf-8') > 30) {
      $success = false;
      $message = "프로필 소개글의 글자 수 제한을 초과하였습니다.";
    } else {
      $success = tell("UPDATE `lunchmate_users` SET `affiliation`='".base64_encode($affiliation)."', `content`='".base64_encode($content)."' WHERE `student_id`='".getUserId()."';");
      if (!$success) {
        $message = "프로필을 업데이트하는데 오류가 발생했습니다.";
      }else {
        $profileChanged = true;
      }
    }
  }

}

$response = askOne("SELECT * FROM `lunchmate_users` WHERE `student_id`='".getUserId()."';");


//echo($response['phone_number']);

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
      <div class="container m-y-2">

      <?php

        if(!$success) {
          echo '<div class="alert alert-danger" role="alert">';
          echo $message;
          echo '</div>';
        }

        if ($profileChanged) {

          echo '<div id="updateModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">';
          echo '<div class="modal-dialog modal-sm">';
          echo '<div class="modal-content container">';
          echo '<p class="text-xs-center m-y-2">프로필이 업데이트되었습니다.</p>';
          echo '</div></div></div>';

          echo '<script>$("#updateModal").modal()</script>';


        }
      ?>


        <div class="row">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-block">
                <h4 class="card-title">소개하기</h4>
                <br>
                <form id="contentForm" action="profile.php" method="post">
                  <input type="hidden" name="setting-type" value="content">

                <div class="form-group">
                    <label for="affiliationInput">소속</label>
                    <input maxlength="30" type="text" class="form-control" id="affiliationInput" name="profile-affiliation" placeholder="학과, 단과대, 동아리, 하우스 등" value=<?php echo('"'.base64_decode($response['affiliation']).'"');?>>
                  </div>
                  <div class="form-group">
                    <label for="profileMessage">소개글</label>
                    <textarea maxlength="200" class="form-control" rows="14" id="profileMessage" name="profile-content" placeholder="자신에 대해 소개해 주세요. (최대 200자)"><?php echo(base64_decode($response['content']));?></textarea>
                    <small class="form-text text-muted">상대방을 불쾌하게 하는 소개글은 차단될 수 있습니다.</small>

                  </div>
                  <div class="row">
                    <div class="col-xs-6">
                      <p id="wordsCount"></p>
                    </div>
                    <div class="col-xs-6">
                      <button type="submit" class="btn btn-primary pull-xs-right">수정하기</button >
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-block">
                <h4 class="card-title">통계</h4>
                <br/>
                <table class="table table-hover">
                  <tbody>
                    <tr>
                      <th scope="row">준 관심</th>
                      <td><?php echo($response['interests_sent']);?></td>
                    </tr>
                    <tr>
                      <th scope="row">받은 관심</th>
                      <td><?php echo($response['interests_received']);?></td>
                    </tr>
                    <tr>
                      <th scope="row">경고 횟수</th>
                      <td><?php echo($response['warnings_received']);?></td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="card">
              <div class="card-block">
                <h4 class="card-title">설정</h4>
                <br>
                <p data-toggle="tooltip" data-placement="top" title="연세포탈에 등록된 번호입니다.">알림을 받을 번호: <?php echo($response['phone_number']);?></p>
                <?php
                $alarmSettings = intval($response['alarm_settings']);
                $mutualAlarm = $alarmSettings % 3 == 0;
                $scheduleFixAlarm = $alarmSettings % 5 == 0;
                $requestAlarm = $alarmSettings % 7 == 0;
                ?>
                <form id="settingsForm" action="profile.php" method="post">
                  <input type="hidden" name="setting-type" value="alarm">
                  <label class="custom-control custom-checkbox">
                  <input type="checkbox" name="request-alarm" class="custom-control-input" onChange="$('#settingsForm').submit();" disabled checked>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">요청이 들어왔을 때 알림</span>
                  </label><br/>
                  <label class="custom-control custom-checkbox">
                  <input type="checkbox" name="mutual-alarm" class="custom-control-input" onChange="$('#settingsForm').submit();"
 <?php echo($mutualAlarm == true ? "checked" : "");?>>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">쌍방 관심을 받았을 때 알림</span>
                  </label><br/>
                  <label class="custom-control custom-checkbox">
                  <input type="checkbox" name="schedule-fix-alarm" class="custom-control-input" onChange="$('#settingsForm').submit();"
 <?php echo($scheduleFixAlarm == true ? "checked" : "");?>>
                  <span class="custom-control-indicator"></span>
                  <span class="custom-control-description">스케줄 확인 알림</span>
                  </label>
                  <button type="button" class="btn btn-secondary pull-xs-right" disabled>비활성화하기</button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        var maxCharacters = 200;
        $('#wordsCount').html(0 + '/' + maxCharacters);

        function countText() {
          var textLength = $('#profileMessage').val().length;
          $('#wordsCount').html(textLength + '/' + maxCharacters);
        }
        countText();
        $('#profileMessage').keyup(countText);

      </script>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
