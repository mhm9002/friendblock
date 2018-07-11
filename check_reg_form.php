<?php
require(dirname(__FILE__) . '/includes/boot.php');

if (isset($_POST['link'])){
    $email = crUser::getUserEmail($_POST['id']);
    cr_update_S_Login($_POST['social-key'],$_POST['id'],$email);
    
    $param = explode('-',$_POST['social-key']);
    $sID = intVal($param[1]);

    cr_login($sID,true,cr_not_null($_POST['return'])?$_POST['return']:null);
}

if (isset($_POST['register'])){
    $pwd = "";
    
    while (!cr_check_password_strength($pwd))
        $pwd = cr_generate_random_string();

    $data = $_POST;
    $data['password']=$pwd;
    $data['password2']=$pwd;

    if ($nID = crUser::createNewAccount($data)){
        cr_login($nID,false,cr_not_null($_POST['return'])?$_POST['return']:null);
    }
}

if (isset($_POST['action'])){
    switch ($_POST['action']){
        case "checkUserName":
            $username = $_POST['username'];
            if (crUser::checkUsernameDuplication($username)){
                echo "Entered Username is already taken";
            } else {
                echo "Entered Username is valid";
            }
            break;
        case "checkEmail":
            $email = $_POST['email'];
            
                //Check Email Address
            if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)){
                echo MSG_INVALID_EMAIL;
                return false;
            }

            if (crUser::checkEmailDuplication($email)){
                echo "Entered email is already taken";
            } else {
                echo "Entered email is valid";
            }    
            break;
        case "checkSLogin":
            $OauthType = $_POST['type'];
            $OauthEmail = $_POST['email'];
            $OauthID = $_POST['OauthID'];
            $returnURL = base64_encode($_POST['return']);

            if ($sID = cr_check_S_Login($OauthType,$OauthEmail,$OauthID))
                cr_login($sID,true);

            $sID = cr_add_S_Login($OauthType,$OauthEmail,$OauthID);

            //return user data in case the user already register by the same email;
            $userID = cr_get_user_by_email($OauthEmail);

            if ($userID){
                $thumb = crUser::getProfileIcon($userID['id']);
                $name = $userID['name'];
                $id = $userID['id'];
                $case = "link";
            } else {
                $thumb = $_POST['image'];
                $name = $_POST['fname'].' '.$_POST['lname'];
                $case="register";
            }
            
            $SITE_GLOBALS['content'] = 'social_register';
            $SITE_GLOBALS['title'] = 'Social Register - ' . SITE_NAME;
            
            require(DIR_TEMPLATE . "/" . $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
            break;
    }
}

?>