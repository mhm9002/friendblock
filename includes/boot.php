<?php
require_once (dirname(__FILE__).'/config.php');

function classAutoLoader($class){
	$class= strtolower ($class);
	$classFile = DIR_CLASS . '/class.' . $class . '.php';
	if (is_file($classFile)&&!class_exists($classFile)) include $classFile;
}

spl_autoload_register('classAutoLoader');

require_once(DIR_INC . '/messages.php');
require_once(DIR_INC . '/tables.php');

$db = new crDatabase(DATABASE_HOST, DATABASE_USERNAME,DATABASE_PASSWORD, DATABASE_NAME);

require_once(DIR_FUN . "/session.php");
require_once(DIR_FUN . "/general.php");
require_once(DIR_FUN . "/secure.php");
require_once(DIR_FUN . "/view.php");

cr_session_start();

cr_enqueue_javascript('jquery-1.9.1.js',FALSE,FALSE);
cr_enqueue_javascript('site.js');
cr_enqueue_javascript('bootstrap.js');

cr_enqueue_stylesheet('font.css');
cr_enqueue_stylesheet('main.css');
cr_enqueue_stylesheet('footer.css');
cr_enqueue_stylesheet('bootstrap.css');

if (cr_is_mobile())
	cr_enqueue_stylesheet('mobile_posts.css');

$SITE_GLOBALS['template'] = DEFAULT_THEME;
$SITE_GLOBALS['layout'] = 'layout';
$SITE_GLOBALS['headerType'] = 'default';


//Set User Data into Global Variable
if(!($userID = cr_is_logged_in())){
	$SITE_GLOBALS['user'] = ['id' => 0, 'user_type' => 'Public', 'thumbnail' =>''];
	
	cr_enqueue_javascript('notify.js');
}else{
	$SITE_GLOBALS['user'] = crUser::getUserBasicInfo($userID);
	
	cr_enqueue_stylesheet('jquery-ui/jquery-ui.css');	
	cr_enqueue_stylesheet('bootstrap-suggest.css');

	cr_enqueue_javascript('jquery-ui.min.js',false,false);
	cr_enqueue_javascript('jquery.contextMenu.js',false,false);
	cr_enqueue_javascript('bootstrap-suggest.js');

	if (cr_is_mobile())
		cr_enqueue_stylesheet('mobile_vendors.css');
	//cr_enqueue_javascript('private_messenger.js');
	
	cr_enqueue_javascript('notify.js');
}
?>