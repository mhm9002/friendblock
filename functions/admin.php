<?php

/**
 * Check if admin user is logged in
 *
 * @return loggedin = TRUE, else FALSE
 */
function admin_is_logged_in(){
    global $db;

    if(isset($_SESSION['admin_id'])){
        $userID = intval($_SESSION['admin_id']);
        //Check the UserId exits in the database
        $query = $db->prepare("SELECT * FROM admins WHERE admin_id=%s AND status = 1", $userID);
        $urow = $db->getRow($query);

        if(!$urow) //If userid doesn't exist in the database, remove it from the session
        {
            $_SESSION['admin_id'] = null;
            unset($_SESSION['admin_id']);
            return FALSE;
        }else if($urow['status'] != 1){
            $_SESSION['admin_id'] = null;
            unset($_SESSION['admin_id']);
            cr_add_message(MSG_ACCOUNT_NOT_ACTIVE, MSG_TYPE_ERROR);
            return FALSE;
        }
        unset ($urow['pwd']);
        return $urow;
    }else{
        return admin_check_cookie_for_login();

    }
}

/**
 * Check Cookie values for keep me signed in

 */
function admin_check_cookie_for_login(){
    global $db;

    if(isset($_COOKIE['COOKIE_KEEP_ME_NAME1']) && isset($_COOKIE['COOKIE_KEEP_ME_NAME2']) && isset($_COOKIE['COOKIE_KEEP_ME_NAME3'])){
        $token1 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME1']);
        $token3 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME2']);
        $token2 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME3']);

        $login_token = md5($token1 . $token2 . $token3);

        if(($adminID = crAdminToken::checkTokenValidity($login_token, "auth"))){
            $query = $db->prepare("SELECT admin_id FROM admins WHERE admin_id=%s AND status=1", $adminID);
            $adminID = $db->getVar($query);

            if($adminID){
                $_SESSION['admin_id'] = $adminID;
                //Init Some Session Values
                $_SESSION['converation_list'] = [];
                return $adminID;
            }
        }

        //Remove Cookies
        setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN);

    }

    return FALSE;
}


/**
 * log in admin
 *
 * @return loggedin = TRUE, else FALSE
 */

function admin_login($adminID){
    
    //Restart Session
    //session_regenerate_id(true);
    cr_session_recreate();

    $_SESSION['admin_id'] = $adminID;

    //Init Some Session Values
    $_SESSION['converation_list'] = [];

    //Create Login Cookie Token
    $login_token = hash('sha256', time() . cr_generate_random_string(20, true) . time());

    $login_token_secure = md5($login_token);

    //Store Login Token
    crAdminToken::removeAdminToken($adminID, "auth");
    crAdminToken::createNewToken($adminID, "auth", $login_token_secure);

    //Slice the login token to three pieces
    $login_token_piece1 = substr($login_token, 0, 20);
    $login_token_piece2 = substr($login_token, 20, 20);
    $login_token_piece3 = substr($login_token, 40);

    //If website is using SSL, use secure cookies
    if(SITE_USING_SSL == true){
        setcookie('COOKIE_KEEP_ME_NAME1', base64_encode($login_token_piece1), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
        setcookie('COOKIE_KEEP_ME_NAME2', base64_encode($login_token_piece3), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
        setcookie('COOKIE_KEEP_ME_NAME3', base64_encode($login_token_piece2), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
    }else{
        setcookie('COOKIE_KEEP_ME_NAME1', base64_encode($login_token_piece1), time() + COOKIE_LIFETIME, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME2', base64_encode($login_token_piece3), time() + COOKIE_LIFETIME, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME3', base64_encode($login_token_piece2), time() + COOKIE_LIFETIME, "/", DOMAIN);
    }

    //$sessionToken = cr_generate_random_string(20,TRUE);
    //setcookie(SESSION_NAME, $sessionToken, time() + COOKIE_LIFETIME, "/", DOMAIN);

    cr_redirect($returnUrl ? base64_decode($returnUrl) : '/admin_panel.php');
}

function validate_admin_login ($username, $pwd){
    global $db;

    $query= $db->prepare('SELECT admin_id from '. TABLE_ADMINS.' WHERE admin_username=%s AND pwd=%s AND status=1',$username,$pwd);
    $admin_id = $db->getVar($query);

    if ($admin_id)
        return $admin_id;

    return false;
}

?>