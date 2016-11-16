<?php

const SERVER_NAME = "db.lunchmate.co.kr";
const USER_NAME = "lunchmate";
const USER_PASSWORD = "lunchmateadmin16";
const DB_NAME = "dblunchmate";

// Create connection
$conn = new mysqli(SERVER_NAME, USER_NAME, USER_PASSWORD, DB_NAME);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 

/*

$sql = "INSERT INTO MyGuests (firstname, lastname, email)
VALUES ('John', 'Doe', 'john@example.com')";


*/
function tell($sql) {

global $conn;

    if ($conn->query($sql) === TRUE) {
        return true;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
        return false;
    }
}

/*

$sql = "SELECT id, firstname, lastname FROM MyGuests";


*/
function ask($sql) {

global $conn;

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {

        $returnResult = array();
        // output data of each row
        while($row = $result->fetch_assoc()) {
            array_push($returnResult, $row);

            //echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
        }

        return $returnResult;
    } else {
        //echo "0 results";
        return array();
    }
}

function askOne($sql) {

global $conn;

     $result = $conn->query($sql);

    if ($result->num_rows > 0) {

        return $result->fetch_assoc();
    } else {
        return null;
    }
}


?>