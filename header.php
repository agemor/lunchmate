<nav class="navbar navbar-light bg-faded">
  <button class="navbar-toggler hidden-sm-up" type="button" data-toggle="collapse" data-target="#navigationBar" aria-controls="navigationBar" aria-expanded="false" aria-label="Toggle navigation">
  &#9776;
  </button>
  <div class="collapse navbar-toggleable-xs" id="navigationBar">
    <div class="container">
      <ul class="nav navbar-nav">
        <li class="nav-item <?php if($location == "peoples.php") { echo("active"); }?>">
          <a class="nav-link" href="peoples.php">사람들 <span class="sr-only">(current)</span></a>
        </li>

        <?php
        if (assigned()) {
          echo '<li class="nav-item '.($location == "requests.php" ? "active" : "" ).'"><a class="nav-link" href="requests.php">요청 현황</a></li>';
          echo '<li class="nav-item '.($location == "profile.php" ? "active" : "" ).'"><a class="nav-link" href="profile.php">내 프로필</a></li>';
        }
        ?>

        <li class="nav-item pull-xs-right">
          <?php
            if (!assigned()) {
                echo '<a class="nav-link" href="#" data-toggle="collapse" data-target="#loginCollapsePanel" aria-controls="loginCollapsePanel" aria-expanded="false" aria-label="Toggle navigation" id="loginToggleButton">로그인</a>';
            } else {
                echo '<a class="nav-link" href="signout.php">로그아웃</a>';
            }
          ?>


        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="collapse bg-faded" id="loginCollapsePanel">

  <div class="p-a-3">

    <div class=" container ">
      <h4>연세포탈 로그인</h4>
      <h6 class="text-muted">별도의 회원가입 과정 없이 포탈 로그인으로 안전한 이용이 가능합니다. <a href="#" style="color:grey"><u>추가 정보</u></a></h6>
      <form class="form-inline" action="./signin.php" method="post" style="margin-top: 30px">
        <div class="form-group">
          <input type="number" class="form-control" id="studentId" name="user-id" placeholder="학번" required>
        </div>
        <div class="form-group">
          <input type="password" class="form-control" id="studentPassword" name="user-password" placeholder="비밀번호" required>
        </div>
        <button type="submit" class="btn btn-secondary">로그인</button>
      </form>

    </div>
  </div>
</div>

<?php

// 로그인 성공 / 실패 메시지
if (isset($_GET["result"]) && $_GET["result"] != "signin-success" && $_GET["result"] != "signup-success") {
  echo '<div id="updateModal" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">';
  echo '<div class="modal-dialog modal-sm">';
  echo '<div class="modal-content container">';

  if ($_GET["result"] == "signin-fail") {
    echo '<p class="text-xs-center m-y-2">로그인에 실패하였습니다.<br/>학번과 비밀번호를 확인해 주세요.</p>';
  }

  else if ($_GET["result"] == "signup-fail") {
    echo '<p class="text-xs-center m-y-2">계정 생성에 실패하였습니다.</p>';
  }
  echo '</div></div></div>';
  echo '<script>$("#updateModal").modal()</script>';
}

?>
