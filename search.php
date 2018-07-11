<?php

require(dirname(__FILE__) . '/includes/boot.php');

if (isset($_POST['searchText'])){
	if (!cr_not_null($_POST['searchText']))
		exit;

	$results=[];
	$id=0;
	
	$flag= isset($_POST['flag'])?$_POST['flag']:'3';
	//1 : people;
	//2 : tags;
	//3 : all;

	if (!($flag=='1')){
		$searchTags = crTweet::searchTag($_POST['searchText'],6,true);
		foreach ($searchTags as $tag){
			//echo '<div class="searchRow"><img src="'.DIR_IMG.'/hashtag.png'.'" class="searchIcons"/>&nbsp;<a href="/hashtag.php?tag='.$tag['value'].'">'.$tag['value'].'</a></div></br>';
			$results[] = ["id" => $id, 
				'label' => $tag['tag'], 
				'value' => '/hashtag.php?tag='.$tag['tag'], 
				'hash' => cr_encrypt_id($id),
				'thumb'=>DIR_IMG.'/hashtag.png',
				'username'=>null,
				'count'=>$tag['count']];
			$id++;
		}
	}
	
	//echo '<hr style="margin-top:2px; margin-bottom:2px;">';

	if (!($flag=='2')){
		$searchResult = crFollowship::searchPeople($_POST['searchText'],6);
		foreach ($searchResult as $uID){
			$user = crUser::getUserBasicInfo($uID);
			//echo '<div class="searchRow"><img src="'.crUser::getProfileIcon($user).'" class="searchIcons"/>&nbsp;<a href="/profile.php?userID='.$user['id'].'">'.$user['name'].'</a></div></br>';
			$results[] = ["id" => $id, 
				'label' => $user['name'], 
				'value' => "/profile.php?userID=".$user['id'], 
				'hash' => cr_encrypt_id($user['id']),
				'thumb'=>crUser::getProfileIcon($user),
				'username'=>$user['username']];
			$id++;
		}
	}
	echo json_encode($results);
	exit;
	
} else {
		
	if(!($userID = cr_is_logged_in())){
		cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
	}

	$searchResult = crFollowship::searchPeople($_GET['searchText']);
	$searchTags = crTweet::searchTag($_GET['searchText']);
	
	$pageLink = "/search.php?searchText=".$_GET['searchText'];

	cr_enqueue_stylesheet('contacts.css');

	cr_enqueue_javascript('bootstrap.js');
	cr_enqueue_javascript('posts.js');
	cr_enqueue_javascript('site.js');
	cr_enqueue_javascript('profile.js');

	//Set Content
	$SITE_GLOBALS['content'] = 'search';

	//Page Title
	$SITE_GLOBALS['title'] = 'Search twitters - ' . SITE_NAME;
	require(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/" . $SITE_GLOBALS['layout'] . ".php");
}
?>