<?php 
if(!session_id()) session_start();

if(!isset($_SESSION['current_user'])) {
	$current_user = array();
    $_SESSION['current_user'] = $current_user;
}
?>