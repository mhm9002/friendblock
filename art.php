<?php
require(dirname(__FILE__) . '/includes/boot.php');

cr_enqueue_stylesheet('art.css');
cr_enqueue_javascript('art.js');


$SITE_GLOBALS['content'] = "art";
$SITE_GLOBALS['title'] = SITE_NAME . " - Connect with your social network";
require(DIR_TEMPLATE . "/" . $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");

?>
