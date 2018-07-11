<?php
require(dirname(__FILE__) . '/includes/boot.php');

$SITE_GLOBALS['content'] = "privacy_policy";

$SITE_GLOBALS['title'] = "Privacy Policy - " . SITE_NAME;

require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
