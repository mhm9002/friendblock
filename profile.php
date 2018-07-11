<?php

require(dirname(__FILE__) . '/includes/boot.php');

//Getting Current User ID
$userID = cr_is_logged_in();

if (!$userID) {
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

//Getting User ID from Parameter
$profileID = cr_escape_query_integer(isset($_GET['userID']) ? $_GET['userID'] : null);

//If the parameter is null, goto homepage 
if(!$profileID)
    cr_redirect('/index.php');

//Getting UserData from Id
$userData = crUser::getUserBasicInfo($profileID);

//Goto Homepage if the userID is not correct
if(!cr_not_null($userData) || (!crUser::checkUserID($profileID, true))){
    cr_redirect('/index.php');
}

$profileData = crUser::getUserBasicInfo($profileID);

$profileTab = isset($_GET['tab']) ? $_GET['tab'] : 'tweets';
if(!in_array($profileTab, ['tweets', 'followers', 'following'])){
    $profileTab = 'tweets';
}

$followed = crFollowship::getAllFollowed($profileID);
$followers = crFollowship::getAllFollowers($profileID);

$followedStatus = crFollowship::isFollowed($userID['id'],$profileID);

if (($followedStatus && $followedStatus['status']==1) || $profileData['privacy'] ||$userID['id']==$profileID ) {
	$userTweets = 'It is safe to load tweets';
} else {
    $text = ($followedStatus)? 'You have sent a follow request to this profile owner. If the follow request is no more needed just click Cancel Follow Request':'To see profile\'s tweets, send follow request';
	$userTweets = "<div style=\"padding: 20px;\"><h5>This profile is private&nbsp;<span class='icon icon-lock'></span></h5></br>".$text."</div>";
}

cr_enqueue_stylesheet('contacts.css');

cr_enqueue_javascript('posts.js');
cr_enqueue_javascript('profile.js');

$SITE_GLOBALS['content'] = 'profile';

//Page title
$SITE_GLOBALS['title'] = $userData['name'] . ' - ' . SITE_NAME;

require(DIR_TEMPLATE ."/". $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
