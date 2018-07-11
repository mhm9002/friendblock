<?php
require(dirname(__FILE__) . '/includes/boot.php');

//Getting Current User ID
if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

//Getting UserData from Id
$userData = crUser::getUserData($userID);

//If Photo ID is empty, goto photo management page
if(!isset($photoID))
    cr_redirect('/photo_manage.php', MSG_INVALID_REQUEST, MSG_TYPE_ERROR);

//$photoId = $_REQUEST['photoID'];
$photo = crAlbum::getPhotoByID($photoID);

//Getting User Albums
$albums =  crAlbum::getAlbumsByUserId($userID);

//Getting Photo Albums
$photoAlbums = crAlbum::getAlbumsByPostId($photoID);

if(!$photoAlbums)
    $photoAlbums = [];

//If photo id is not correct or the owner is not the current user, goto photo management page
if(!$photo || $photo['ownerID'] != $userID)
    cr_redirect('/photo_manage.php', MSG_INVALID_REQUEST, MSG_TYPE_ERROR);

if(isset($action)){
    //Create New Album
    if($action == 'resize'){
        if($photo['ownerID'] != $userID){
            cr_redirect('/photo_manage.php', MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
        }

        //Update Photo Caption and Privacy
        //crTweet::updatePhoto($userID, $_POST);

        //Change user profile image
        if($_POST['photo_visibility'] == 2){
            if(!$photo['is_profile']){
                crTweet::createProfileImage($photo, $_POST);
            }
            //Update profile image with old one                
            BuckysUser::updateUserFields($userID, ['thumbnail' => $photo['image']]);

        }else if($userData['thumbnail'] == $photo['image']){ //If it was a profile image and now it is not, remove it from the profile image
            crUser::updateUserFields($userID, ['thumbnail' => '']);
        }

        //Save Album
        if(isset($_POST['album']) && $_POST['album'] != '' && isset($albums[$_POST['album']])){
            crAlbum::addPhotoToAlbum($_POST['album'], $photo['postID']);
        }
        cr_redirect('/photo_edit.php?photoID=' . $photo['postID'], MSG_PHOTO_UPDATED, MSG_TYPE_SUCCESS);
        exit;
    }
}

$set_profile = isset($_GET['set_profile']) ? $_GET['set_profile'] : null;

cr_enqueue_stylesheet('account.css');
cr_enqueue_stylesheet('posting.css');
cr_enqueue_stylesheet('jquery.Jcrop.css');

cr_enqueue_javascript('jquery.Jcrop.js');
cr_enqueue_javascript('jquery.color.js');
cr_enqueue_javascript('edit_photo.js');

$SITE_GLOBALS['content'] = 'photo_edit';

$SITE_GLOBALS['title'] = "Edit Photo - " . SITE_NAME;

require(DIR_TEMPLATE ."/". $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
