<?php
require(dirname(__FILE__) . '/includes/boot.php');

//Getting Current User ID
$userID = cr_is_logged_in();

if (isset($_POST['action']) && $_POST['action']=='reset-password'){
	if(!$_POST['nPWD1'] || !$_POST['nPWD2']){
        cr_add_message(MSG_EMPTY_PASSWORD, MSG_TYPE_ERROR);
    }else if($_POST['nPWD1'] != $_POST['nPWD2']){
        cr_add_message(MSG_NOT_MATCH_PASSWORD, MSG_TYPE_ERROR);
    }else{
		$uID =cr_check_pwd_token($_POST['token']); 
		
		if (!$uID){
			cr_redirect('/index.php', MSG_INVALID_TOKEN,MSG_TYPE_ERROR);		
		}
		
        $pwd = cr_encrypt_password($_POST['nPWD1']);
        
        crUser::updateUserFields($uID, ['password' => $pwd]);
    	
    	cr_remove_pwd_token($uID,$_POST['token']);
    	
        
        if (isset($_POST['return']))
			cr_redirect($_POST['return'], MSG_PASSWORD_UPDATED); else
        	cr_redirect('/index.php', MSG_PASSWORD_UPDATED);		
	
	}
	
}

//If the parameter is null, goto homepage 
if (!(isset($_POST['action']) && $_POST['action']=='change-password') && $userID)
    cr_redirect('/account.php');

$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
if(!$token){
    cr_redirect('/index.php', MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
}

//if(!($userID = crUsersToken::checkTokenValidity($token, 'password'))){
//    cr_redirect('/register.php?forgotpwd=1', MSG_USER_TOKEN_LINK_NOT_CORRECT, MSG_TYPE_ERROR);
//}

if(isset($_POST['action'])){
    if(!$_POST['nPWD1'] || !$_POST['nPWD2']){
        cr_add_message(MSG_EMPTY_PASSWORD, MSG_TYPE_ERROR);
    }else if($_POST['nPWD1'] != $_POST['nPWD2']){
        cr_add_message(MSG_NOT_MATCH_PASSWORD, MSG_TYPE_ERROR);
    }else{
        if ($_POST['action']=='change-password' && !$checkPWD = crUser::checkPassword($_POST['currentPWD'],$_POST['userid'])) {
			cr_add_message(MSG_CURRENT_PASSWORD_NOT_CORRECT, MSG_TYPE_ERROR);
			cr_redirect($_POST['return']);
		
		}
        
        if ($_POST['action']=='change-password')
        	$uID = $_POST['userid']; else
        	$uID = $userID['id'];
        	
        
        $pwd = cr_encrypt_password($_POST['nPWD1']);
        crUser::updateUserFields($uID, ['password' => $pwd]);
        
        if (isset($_POST['return']))
			cr_redirect($_POST['return'], MSG_PASSWORD_UPDATED); else
        	cr_redirect('/index.php', MSG_PASSWORD_UPDATED);
    
    }

}

cr_enqueue_stylesheet('register.css');
cr_enqueue_javascript('register.js');

$SITE_GLOBALS['content'] = 'reset_password';

require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
