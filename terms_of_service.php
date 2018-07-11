<?php
require(dirname(__FILE__) . '/includes/boot.php');

$SITE_GLOBALS['content'] = "terms_of_service";

$SITE_GLOBALS['title'] = "Terms of Service - " . SITE_NAME;

require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
