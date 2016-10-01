<?php

session_start();

function assign($userId) {
	$_SESSION['user_id'] = $userId;
	$_SESSION['assigned'] = true;
}

function release() {
	session_destroy();
}

function assigned() {
	return isset($_SESSION['assigned']) && $_SESSION['assigned'] == true;
}


?>