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
        <div class="row">
          <div class="col-lg-6">
            <div class="card">
              <div class="card-block">
                <h4 class="card-title">소개하기</h4>
                <br>
                <div class="form-group">
                  <textarea class="form-control" rows="14" id="profileMessage" placeholder="자신에 대해 소개해 주세요. (최대 200자)"></textarea>
                </div>
                <div class="row">
                  <div class="col-xs-6">
                    <p id="wordsCount"></p>
                  </div>
                  <div class="col-xs-6">
                    <a href="#" class="btn btn-primary pull-xs-right">수정하기</a>
                  </div>
                </div>
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
      <td>12</td>
    </tr>
    <tr>
      <th scope="row">받은 관심</th>
      <td>131</td>
    </tr>
    <tr>
      <th scope="row">경고 횟수</th>
      <td>10</td>
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
<br/>

<label class="custom-control custom-checkbox">
  <input type="checkbox" class="custom-control-input">
  <span class="custom-control-indicator"></span>
  <span class="custom-control-description">요청이 들어왔을 때 알림</span>
</label><br/>
<label class="custom-control custom-checkbox">
  <input type="checkbox" class="custom-control-input">
  <span class="custom-control-indicator"></span>
  <span class="custom-control-description">쌍방 관심을 받았을 때 알림</span>
</label><br/>
<label class="custom-control custom-checkbox">
  <input type="checkbox" class="custom-control-input">
  <span class="custom-control-indicator"></span>
  <span class="custom-control-description">스케줄 확인 알림</span>
</label>

<button type="button" class="btn btn-secondary pull-xs-right" >비활성화하기</button>

                
              </div>
            </div>


</div>
        </div>
      </div>
      <script type="text/javascript">
        var maxCharacters = 200;
        $('#wordsCount').html(0 + '/' + maxCharacters);
        $('#profileMessage').keyup(function() {
          var textLength = $('#profileMessage').val().length;
          $('#wordsCount').html(textLength + '/' + maxCharacters);
        });

      </script>
    </main>
    <footer>
      <?php include "footer.php";?>
    </footer>
  </body>
</html>