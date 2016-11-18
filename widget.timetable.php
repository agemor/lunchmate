<?php

/* 위젯 객체 초기화 */
if (!isset($widget))
    $widget = new class {};

/* 모듈 생성 */
$widget->{"timetable"} = new class {

    const DAYS = array("일", "월", "화", "수", "목", "금", "토");
    const TIMES = array(
        14 => "오전 7:00",
        16 => "오전 8:00",
        18 => "오전 9:00",
        20 => "오전 10:00",
        22 => "오전 11:00",
        23 => "오전 11:30",
        24 => "오후 12:00",
        25 => "오후 12:30",
        26 => "오후 1:00",
        28 => "오후 2:00",
        30 => "오후 3:00",
        32 => "오후 4:00",
        34 => "오후 5:00",
        36 => "오후 6:00",
        37 => "오후 6:30",
        38 => "오후 7:00",
        39 => "오후 7:30",
        40 => "오후 8:00",
        42 => "오후 9:00",
        44 => "오후 10:00",
        46 => "오후 11:00"
        );

    const TIME_ZONE = "Asia/Seoul";
    const AVAILABLE_DAYS = 4;

    private $time;

    /* 시간표를 출력한다 */
    public function get() {

        $now = $this->getTime();

        $d = '<table class="table m-t-2 table-bordered" id="scheduleTable">';
        $d.= '<thead><tr>';
        $d.= '<th>#</th>';

        for ($i = 0; $i < self::AVAILABLE_DAYS; $i++)
            $d.= '<th style="width: 25%" class="text-xs-center">'.self::DAYS[($now["day"] + $i) % 7].'</th>';

        $d.= '</thead></tr>';
        $d.= '<tbody>';

        foreach (self::TIMES as $key => $value) {
            $d.= '<tr>';
            $d.= '<th scope="row">'.$value.'</th>';
            for ($j = 0; $j < self::AVAILABLE_DAYS; $j++) {
                // 만약 선택 불가능한 시간이라면
                if ($j == 0 && $key <= $now["hour"]) {
                  $d.= '<td class="table-active"></td>';
                } else {
                  $d.= '<td>';
                  $d.= '</td>';
                }
            }
            $d.= '</tr>';
        }
        $d.= '</tbody>';
        $d.= '</table>';
        return $d;
    }

    /* 현재 시간을 구한다 */
    private function getTime() {
        if (empty($this->time)) {
            $this->time = new DateTime();
            $this->time->setTimezone(new DateTimeZone(self::TIME_ZONE));
        }
        return array(
            "day" => intval($this->time->format("w")),
            "hour" => intval($this->time->format("H")) * 2 + (intval($this->time->format("i")) / 60));
    }

}

?>
