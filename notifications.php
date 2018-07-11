<?php
require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

//Getting Activity Stream
$notifications = crActivity::getNotifications($userID['id']);

cr_enqueue_javascript('notifications.js');
cr_enqueue_javascript('posts.js');

//Set Content
$SITE_GLOBALS['content'] = 'notifications';

//Page Title
$SITE_GLOBALS['title'] = $userID['name'].' - Notifications - ' . SITE_NAME;
require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
