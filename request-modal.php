<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="requestModalTitle">ㄱㅎㅈ#331님</h4>
      </div>
      <div class="modal-body">
        <h6>메시지</h6>
        <div class="form-group">
          <textarea class="form-control" rows="4" id="requestModalMessageInput" placeholder="간단한 인사말을 남겨 보세요."></textarea>
        </div>
        <br/>
        <h6>가능한 스케줄</h6>

        <?php
          include "widget.timetable.php";

          echo $widget->timetable->get();

          ?>
        <script type="text/javascript">
          function setRequestModalTitle(title) {
              $('#requestModalTitle').text(title);
          }


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
