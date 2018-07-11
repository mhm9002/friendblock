<?php
require(dirname(__FILE__) . '/includes/boot.php');

//Getting Current User ID
if(!($userID = cr_is_logged_in()))
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);

//Getting UserData from Id
$userData = crUser::getUserData($userID);

if(isset($_REQUEST['action'])){
    $action = $_REQUEST['action'];
    if($action == 'set-profile-photo'){
        crUser::updateUserProfilePhoto($userID['id'], $_REQUEST['photoID']);
        cr_redirect('/photo_manage.php');
    }else if($action == 'delete-photo'){

        crAlbum::deletePhoto($userID['id'], $_REQUEST['pID']);
            
    }else if($action == 'remove-profile-photo'){
        crUser::updateUserFields($userID['id'], ['thumbnail' => '']);
        cr_redirect('/photo_manage.php');
    }
}

//Getting Album ID
$albumID = isset($_REQUEST['albumID']) ? $_REQUEST['albumID'] : null;

//Getting Current Page
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$totalCount = crTweet::getNumberOfPhotosByUserID($userID['id'],  $albumID);

/*if (!$totalCount)
*	crAlbum::createAlbum($userID['id'],'Profile pictures');
*	crAlbum::addPhotoToAlbum(,)
*	cr_redirect('/photo_album_edit.php');
*/

$pagination = new Pagination($totalCount, crTweet::$IMAGES_PER_PAGE_FOR_MANAGE_PHOTOS_PAGE, $page);
$page = $pagination->getCurrentPage();

$photos = crTweet::getPhotosByUserID($userID['id'], $userID['id'], null, $albumID, crTweet::$IMAGES_PER_PAGE_FOR_MANAGE_PHOTOS_PAGE);

$albums = crAlbum::getAlbumsByUserId($userID['id']);

cr_enqueue_stylesheet('account.css');
cr_enqueue_stylesheet('posting.css');
cr_enqueue_stylesheet('info.css');

$SITE_GLOBALS['content'] = 'photo_manage';

$SITE_GLOBALS['title'] = "Manage Photos - " . SITE_NAME;

require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
