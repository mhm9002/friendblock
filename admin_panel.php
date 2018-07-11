<?php

require_once (dirname(__FILE__)).'/includes/boot.php';
require_once DIR_FUN .'/admin.php';

$SITE_GLOBALS['admin'] = admin_is_logged_in();

if (isset($_POST['username'])){
    var_dump ($_POST);
    $admin = isset($_POST['username']) ? $_POST['username']:'';
    $pwd = isset($_POST['password']) ? $_POST['password']:'';
    if ($adminID = validate_admin_login($admin,$pwd)){
        admin_login($adminID);
        
        cr_redirect('/admin_panel.php');
    } else {
        cr_add_message(MSG_INVALID_LOGIN_INFO, MSG_TYPE_ERROR);
    }
    unset ($_POST);
    exit;
}

if (isset($_GET['action'])){
    if ($_GET['action']=='logout'){
        crAdmin::logout();
        cr_redirect('/admin_panel.php');
    }
    exit;
}

$adminID = admin_is_logged_in();

cr_enqueue_stylesheet('index.css');
cr_enqueue_stylesheet('admin.css');

cr_enqueue_javascript('index.js');

//if ($userID) cr_redirect('/admin_panel.php');

$SITE_GLOBALS['content'] = "admin_panel";
$SITE_GLOBALS['title'] = SITE_NAME . " - Admin Panel";
require(DIR_TEMPLATE . "/" . $SITE_GLOBALS['template'] . "/admin-" . $SITE_GLOBALS['layout'] . ".php");

?>