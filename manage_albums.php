<?php

require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in()))
	cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);

//Getting UserData from Id
$userData = crUser::getUserData($userID);
$albums = crAlbum::getAlbumsByUserId($userData['id']);

cr_enqueue_stylesheet('albums.css');

$SITE_GLOBALS['content'] = 'photo_albums';

$SITE_GLOBALS['title'] = "Manage Albums - " . $userData['name'] ." - ". SITE_NAME;

require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
?>