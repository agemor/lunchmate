<?php
include "session.php";

$location = "about.php";

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
            <h4 class="card-title">연세포탈 로그인 연동</h4>
            <br>
            런치메이트는 이용자의 편의를 위해 연세대학교 포탈 로그인을 통한 인증을 지원하고 있습니다.<br>
            비밀번호 등의 로그인에 필요한 정보를 일체 수집하지 않으며 <strong>간접적으로</strong> 사용자의 로그인 성공 여부만을 확인합니다.<br><br>

            로그인 시, 첫 1회에 한하여 포탈 로그인 서버가 반환하는 <strong>학번, 한글 이름, 영어 이름, 전화번호</strong>값을 수집합니다.<br>
            이 개인정보들은 철저히 암호화되어 보관되며, 사용자 정보 확인 외로 일체 사용되지 않습니다.<br><br>

            원하실 경우, '프로필' 탭에서 이용중지를 통해 런치메이트 서비스를 탈퇴하실 수 있으며, 탈퇴와 동시에 저장된 모든 개인정보가 삭제됩니다.<br><br>

            문의사항: 글융공 15 김현준 010-7525-7343 (개발자)


    </div></div>
    </div>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>
