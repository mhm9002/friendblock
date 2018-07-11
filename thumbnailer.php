<?php

require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

$id= $_GET['id'];
$return = $_GET['return'];

if ($id==$userID['id']){
	thumbnailer($id);
}

cr_redirect('/'.$return.'.php');

?>