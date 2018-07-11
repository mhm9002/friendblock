<?php
require(dirname(__FILE__) . '/includes/boot.php');

// Getting Current User ID
$userID = cr_is_logged_in();

// If the parameter is null, goto homepage
if($userID)
	cr_redirect('/account.php');

if(isset($_GET['action']) && $_GET['action'] == 'verify'){
	$token = trim($_GET['token']);
	$email = trim($_GET['email']);
	if(!$token || !$email){
		cr_redirect("/index.php", MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
	}
	crUser::verifyAccount($email, $token);
	cr_redirect("/index.php");
}

// Check CAPTCHA and create new account
if(isset($_POST['action']) && $_POST['action'] == 'create-account'){

	if(!DEVELOPER_MODE){
		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$data = array(
			'secret' => RECAPTCHA_PRIVATE_KEY,
			'response' => $_POST["g-recaptcha-response"]
		);
		$options = array(
			'http' => array (
				'method' => 'POST',
				'content' => http_build_query($data)
			)
		);
	
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		
		$captcha=json_decode($verify);
	} 
	
	if(isset($capthca) && $captcha->success == false){
		render_result_xml(['status' => 'error', 'message' => ($captcha->error-codes == 'incorrect-captcha-sol' ? 'The captcha input is not correct!' : $captcha->error-codes)]);
	}else{
		if(!crUser::checkDailyUserLimit()){
			render_result_xml(['status' => 'error', 'message' => sprintf(MSG_DAILY_ACCOUNTS_LIMIT_EXCEED_ERROR, USER_DAILY_LIMIT_ACCOUNTS)]);
			exit;
		}
		$newID = crUser::createNewAccount($_POST);
		
		if (isset($_POST['social-key'])){
			$msg = 'Thank you for registration, You will be redirected to your account page';
			
		} else {
			$msg = MSG_NEW_ACCOUNT_CREATED; 
		}

		render_result_xml(['status' => !$newID ? 'error' : 'success', 'message' => !$newID ? cr_get_messages() : $msg]);
		
	}
	exit;
}else if(isset($_POST['action']) && $_POST['action'] == 'reset-password'){
	if(!cr_check_form_token()){
		exit;
	}
	crUser::resetPassword($_POST['email']);
}

$returnUrl = isset($_GET['return']) ? $_GET['return'] : null;
$showForgotPwdForm = isset($_GET['forgotpwd']) && $_GET['forgotpwd'];


cr_enqueue_meta("google-signin-scope", SITE_G_SIGN_SCOPE);
cr_enqueue_meta("google-signin-client_id", SITE_G_SIGN_C_ID);

//for login
cr_enqueue_javascript('https://apis.google.com/js/platform.js',TRUE,FALSE);
cr_enqueue_javascript('social.js');

cr_enqueue_stylesheet('register.css',false,0);
cr_enqueue_javascript('register.js');

//for recaptcha
cr_enqueue_javascript('https://www.google.com/recaptcha/api.js',TRUE ,FALSE);

$SITE_GLOBALS['content'] = 'register';
$SITE_GLOBALS['title'] = 'Register - ' . SITE_NAME;

require(DIR_TEMPLATE . "/" . $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
