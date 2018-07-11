<?php
require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

if (isset($_POST['action']) && $_POST['action']=="update-profile"){
	if(!cr_check_form_token()){
		cr_redirect("/account.php", MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
	}
	
	$data=[];
	
	$data['privacy'] = $_POST['privacy'];
	$data['martial_stat']=$_POST['martial_stat'];
	
	$data['description']=$_POST['description'];
	$data['gender']=$_POST['gender'];
	$data['birthday']=$_POST['birthdate_year'].'-'.$_POST['birthdate_month'].'-'.$_POST['birthdate_day'];
	$data['timezone']=$_POST['timezone'];
	$data['country']=$_POST['country'];
	
	$r = crUser::updateUserFields($_POST['userID'],$data);

	//if ($r) {
	//	render_result_xml($data);
	//}else{
	//	render_result_messages("Error");
	//}
	//exit;
	if (!$r){
		cr_add_message( MSG_USER_PROFILE_UPDATE_FAILED,MSG_TYPE_ERROR);
	}
}
	
cr_enqueue_stylesheet('contacts.css');
cr_enqueue_stylesheet('Jcrop.css');

cr_enqueue_javascript('posts.js');
cr_enqueue_javascript('bootstrap.js');
cr_enqueue_javascript('myprofile.js');
cr_enqueue_javascript('Jcrop.js');

cr_enqueue_meta("google-signin-scope", SITE_G_SIGN_SCOPE);
cr_enqueue_meta("google-signin-client_id", SITE_G_SIGN_C_ID);
//for login
cr_enqueue_javascript('https://apis.google.com/js/platform.js',TRUE,FALSE);
cr_enqueue_javascript('social.js');

//Set Content
$SITE_GLOBALS['content'] = 'myprofile';

//Page Title
$SITE_GLOBALS['title'] = 'My Profile Settings - ' . SITE_NAME;
require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
