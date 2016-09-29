<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="myModalLabel">ㄱㅎㅈ#331님</h4>
      </div>
      <div class="modal-body">
        <h6>메시지</h6>
        <div class="form-group">
                  <textarea class="form-control" rows="4" id="profileMessage" placeholder="간단한 인사말을 남겨 보세요."></textarea>
                </div>
<br/>
<h6>가능한 스케줄</h6>

<?php
$now = new DateTime();
$now->setTimezone(new DateTimeZone("Asia/Seoul"));

// 요일 추출
$days = array("일", "월", "화", "수", "목", "금", "토");
$showDays = 4;

$dayIndex = intval($now->format("w"));
$currentHour = intval($now->format("H"));
echo '<table class="table m-t-2 table-bordered" id="scheduleTable">';

$times = array(7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23);

echo '<thead><tr>';
echo '<th>#</th>';
for ($i = 0; $i < $showDays; $i++)
    echo '<th style="width: 25%" class="text-xs-center">'.$days[($dayIndex + $i) % 7].'</th>';
echo '</thead></tr>';

echo '<tbody>';
for ($i = 0; $i < count($times); $i++) {
    echo '<tr>';
    echo '<th scope="row">'.($times[$i] >= 12 ? "오후" : "오전")." ".($times[$i] > 12 ? ($times[$i] - 12) : $times[$i]).":00".'</th>';
    for ($j = 0; $j < $showDays; $j++) {

        // 만약 선택 불가능한 시간이라면
        if ($j == 0 && $times[$i] <= $currentHour) {
          echo '<td class="table-active"></td>';
        } else {
          echo '<td>';
          echo '</td>';
        }

        
    }
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
?>
<script type="text/javascript">
  $("#scheduleTable").on("click", "td", function() {
      if(!$(this).hasClass("table-active"))
      $(this).toggleClass("bg-primary");
   });


</script>


        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">취소하기</button>
        <button type="button" class="btn btn-primary">요청 보내기</button>
      </div>
    </div>
  </div>
</div>
