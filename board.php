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

  <div class="row">
    <div class="col-sm-6 col-md-4 col-lg-3">
          <?php include "card-me.php";?>
</div>
    <div class="col-sm-6 col-md-4 col-lg-3">
    <?php include "card.php";?>
</div>
    <div class="col-sm-6 col-md-4 col-lg-3">
    <?php include "card.php";?>
</div>
    <div class="col-sm-6 col-md-4 col-lg-3">
    <?php include "card.php";?>
 </div>
    <div class="col-sm-6 col-md-4 col-lg-3">
    <?php include "card.php";?>
</div>
  </div>
</div>





    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>