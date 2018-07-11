<?php
require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

cr_enqueue_javascript('posts.js');
cr_enqueue_javascript('account.js');
	
//Set Content
$SITE_GLOBALS['content'] = 'messages';

//Page Title
$SITE_GLOBALS['title'] = 'My Messages - ' . SITE_NAME;
require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");