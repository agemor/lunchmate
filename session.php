<?php

session_start();

function assign($userId) {
	$_SESSION['user_id'] = $userId;
	$_SESSION['assigned'] = true;
}

function release() {
	session_destroy();
}

function getUserId() {
	return $_SESSION['user_id'];
}

function assigned() {
	return isset($_SESSION['assigned']) && $_SESSION['assigned'] == true;
}


?>