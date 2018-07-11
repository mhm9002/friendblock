<?php
require(dirname(__FILE__) . '/includes/boot.php');

if(isset($_POST['login_submit'])){

    $returnUrl = isset($_POST['return']) ? $_POST['return'] : null;

    //social linking and login
    switch ($_POST['login_submit']){
        case 'link':
            if ($user = cr_is_logged_in())
                cr_update_S_Login($_POST['social-key'],$user['id'],$_POST['email']);    
            cr_redirect($returnUrl ? $returnUrl:'/index.php');        
            exit;
            break;
        case 'unlink':
            if ($user = cr_is_logged_in())
                cr_unlink_S($user['id'],$_POST['social-key']);
            cr_redirect($returnUrl ? $returnUrl:'/index.php');        
            exit;
            break;
        case 'social':
            cr_login($_POST['sID'],true);
            exit;
            break;
    }
    
    if(crTracker::getLoginAttemps() >= MAX_LOGIN_ATTEMPT){
        cr_redirect('/register.php', MSG_EXCEED_MAX_LOGIN_ATTEMPS, MSG_TYPE_ERROR);
    }

    crTracker::addTrack('login');

    //E-mail    
    if(!trim($_POST['email'])){
        $loginError = 1;
        cr_redirect('/register.php' . ($returnUrl ? "?return=$returnUrl" : ""), MSG_EMPTY_EMAIL, MSG_TYPE_ERROR);
    }else if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $_POST['email'])){
        cr_redirect('/register.php' . ($returnUrl ? "?return=$returnUrl" : ""), MSG_INVALID_EMAIL, MSG_TYPE_ERROR);
    }

    //Password
    if(empty($_POST['password'])){
        cr_redirect('/register.php', MSG_EMPTY_PASSWORD, MSG_TYPE_ERROR);
    }
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $info = cr_get_user_by_email($email);
    if(cr_not_null($info)){
        if(!cr_validate_password($password, $info['password'])) //Password Incorrect
        {
            cr_redirect('/register.php' . ($returnUrl ? "?return=$returnUrl" : ""), MSG_INVALID_LOGIN_INFO, MSG_TYPE_ERROR);
        }else if($info['status'] == 0){ //Account Not Verified or Banned            
            cr_redirect('/index.php', !$info['token'] ? MSG_ACCOUNT_BANNED : MSG_ACCOUNT_NOT_VERIFIED, MSG_TYPE_ERROR);
        }else{ //Login Success            
            //Clear Login Attempts
            crTracker::clearLoginAttemps();

            cr_login ($info['id'],false,$returnUrl ? $returnUrl: null);
        }
    }else{ //Email Incorrect
        cr_redirect('/register.php' . ($returnUrl ? "?return=$returnUrl" : ""), MSG_INVALID_LOGIN_INFO, MSG_TYPE_ERROR);
    }
}