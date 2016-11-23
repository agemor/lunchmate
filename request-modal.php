<!-- Modal -->
<div class="modal fade" id="requestModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title"></h4>
      </div>
      <div class="modal-body">
        <h6>메시지</h6>
        <div class="form-group">
          <textarea class="form-control" rows="4" id="requestModalMessageInput" placeholder="간단한 인사말을 남겨 보세요." required></textarea>
        </div>
        <br/>
        <h6>가능한 스케줄</h6>

        <?php
          include "widget.timetable.php";

          echo $widget->timetable->get();

          ?>
        <script type="text/javascript">



        </script>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary request-cancel-button" data-dismiss="modal">취소하기</button>
        <button type="button" class="btn btn-primary request-send-button">요청 보내기</button>
      </div>
    </div>
  </div>
</div>
