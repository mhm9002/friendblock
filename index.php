<?php

require_once (dirname(__FILE__)).'/includes/boot.php';

$userID = cr_is_logged_in();

cr_enqueue_stylesheet('index.css');

cr_enqueue_meta("google-signin-scope", SITE_G_SIGN_SCOPE);
cr_enqueue_meta("google-signin-client_id", SITE_G_SIGN_C_ID);

//for login
cr_enqueue_javascript('https://apis.google.com/js/platform.js',TRUE,FALSE);
cr_enqueue_javascript('social.js');

cr_enqueue_javascript('index.js');

if ($userID) cr_redirect('/account.php');

$SITE_GLOBALS['content'] = "home";
$SITE_GLOBALS['title'] = SITE_NAME . " - Connect with your social network";
require(DIR_TEMPLATE . "/" . $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");

?>