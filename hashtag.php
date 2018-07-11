<?php
require(dirname(__FILE__) . '/includes/boot.php');

$userID = cr_is_logged_in();

if (!$userID) {
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

$tag = $_GET['tag'];

cr_enqueue_javascript('posts.js');
cr_enqueue_javascript('account.js');
	
//Set Content
$SITE_GLOBALS['content'] = 'hashtag';

//Page Title
$SITE_GLOBALS['title'] = $tag.' - ' . SITE_NAME;
require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
