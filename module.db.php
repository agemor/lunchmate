<?php
include "module.db.account.php";

/* 모듈 객체 초기화 */
if (!isset($module))
    $module = new class {};

/* 모듈 생성 */
$module->{"db"} = new class {

    public $connected = false;
    public $connection;
    public $recentError;

    public $queryBlocks = array();

    /* 데이터베이스에 접속한다 */
    public function connect() {

        // 연결은 하나만 생성한다
        if ($this->connected && isset($this->connection)) {
            return true;
        }

        $this->connection = new mysqli(SERVER_NAME, USER_NAME, USER_PASSWORD, DB_NAME);

        // 연결 상태 테스트
        if ($this->connection->connect_error){
            $this->recentError = $this->connection->connect_error;
            $this->connected = false;
        } else {
            $this->connected = true;
        }

        return $this->connected;
    }

    /* 응답 반환이 필요하지 않은 쿼리문을 수행한다 */
    public function go() {

        // 연결 상태 테스트
        if (!$this->connected) {
            $this->connect();
        }

        // 쿼리 수행
        $query = $this->formulate();
        $result = $this->connection->query($query);

        // 실패했을 경우
        if ($result === false) {
            $this->recentError = $this->connection->error;
            return false;
        }
        return true;
    }

    /* 다중 응답 반환이 필요한 쿼리문을 수행한다 */
    public function goAndGet() {

        // 연결 상태 테스트
        if (!$this->connected) {
            $this->connect();
        }

        // 쿼리 수행
        $query = $this->formulate();
        $result = $this->connection->query($query);

        // 실패했을 경우
        if ($result === false) {
            $this->recentError = $this->connection->error;
            return false;
        }

        // 응답 반환
        if ($result->num_rows > 0) {
            $returnList = array();
            while($row = $result->fetch_assoc()) {
                array_push($returnList, $row);
            }
            return $returnList;
        }
        return array();
    }

    /* 단일 응답 반환이 필요한 쿼리문을 수행한다 */
    public function goAndGetAll() {

        // 연결 상태 테스트
        if (!$this->connected) {
            $this->connect();
        }

        // 쿼리 수행
        $query = $this->formulate();
        $result = $this->connection->query($query);

        // 실패했을 경우
        if ($result === false) {
            $this->recentError = $this->connection->error;
            return false;
        }

        // 응답 반환
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } 
        return null;
    }

    public function in($table) {
        $this->queryBlocks["table"] = $table;
        return $this;
    }

    public function select($row) {
        $this->queryBlocks["type"] = "select";
        $this->addTarget($row);
        return $this;
    }

    public function update($row) {
        $this->queryBlocks["type"] = "update";
        $this->addTarget($row);
        return $this;
    }

    public function insert($row) {
        $this->queryBlocks["type"] = "insert";
        $this->addTarget($row);
        return $this;
    }

    public function where($row, $operator, $value) {
        $this->and($row, $operator, $value);
        return $this;
    }

    public function and($row, $operator, $value) {
        if (!isset($this->queryBlocks["conditions"])) {
            $this->queryBlocks["conditions"] = array();
        }
        array_push($this->queryBlocks["conditions"], array($row, $operator, $value, "and"));
        return $this;
    }

    public function or($row, $operator, $value) {
        if (!isset($this->queryBlocks["conditions"])) {
            $this->queryBlocks["conditions"] = array();
        }
        array_push($this->queryBlocks["conditions"], array($row, $operator, $value, "or"));
        return $this;
    }

    public function orderBy($orderFunction) {
        $this->queryBlocks["order"] = $orderFunction;
        return $this;
    }

    public function limit($value) {
        $this->queryBlocks["limit"] = $value;
        return $this;
    }

    private function formulate() {

        $query = "";

        // 쿼리 구성 블록은 비어있으면 안 됨
        if (empty($this->queryBlocks)){
            return $query;
        }

        // SELECT 문일 경우
        if ($this->queryBlocks["type"] == "select") {
            //$query = "SELECT `student_id` FROM `lunchmate_users` WHERE `student_id`='".$yonseiAccount["id"]."';";
            $query .= "SELECT ";
            foreach($this->queryBlocks["target"] as $target) {
                $query .= "`".mysql_real_escape_string($target)."`,";
            }
            rtrim($query, ",");
            $query .= " FROM `".$this->queryBlocks["table"]."`";
        }

        // where limit order by 등등

        mysql_real_escape_string()

        $this->queryBlocks = array();
        return sth
    }

    private function addTarget($target) {
        if (!isset($this->queryBlocks["target"])) {
            $this->queryBlocks["target"] = array();
        }
        array_push($this->queryBlocks["target"], $target);
    }
}
?>