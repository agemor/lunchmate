<?php
include "session.php";
include "module.db.php";

$location = "feedbacks.php";

if (!assigned()) {
  header("Location: /");
  exit();
}
//var_dump($_POST);
if (isset($_POST["feedback"]) && strlen($_POST["feedback"]) > 5) {

  $response = $module->db->in('lunchmate_feedbacks')
                         ->insert('content', $_POST["feedback"])
                         ->insert('author', getUserId())
                         ->go();
  if ($response) {
    header("Location: /feedbacks.php?result=feedback-sent");
    exit();
  }
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
      <div class="container">
        <div class="card m-y-2">

          <div class="card-block">
            <h4 class="card-title">피드백 보내기</h4>
            <br>
      <form method="post">
      <div class="form-group">
        <label for="feedbackTextarea">런치메이트에 대한 피드백을 입력해 주세요. 간단한 의견부터, 에러 제보까지 모두 좋습니다!</label>
        <textarea class="form-control" name="feedback" id="feedbackTextarea" rows="5"></textarea>
      </div>
      <button type="submit" class="btn btn-primary">제출하기</button>
      </form>
    </div></div>
    </div>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
