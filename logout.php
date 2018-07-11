<?php
require(dirname(__FILE__) . '/includes/boot.php');

unset($_SESSION['id']);

if(SITE_USING_SSL == true){
	setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN, true, true);
	setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN, true, true);
	setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN, true, true);
}else{
	setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN);
	setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN);
	setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN);
}

cr_session_destroy();

cr_redirect('/index.php');