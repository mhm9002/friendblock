<?php

require(dirname(__FILE__) . '/includes/boot.php');

if(!($userID = cr_is_logged_in()))
	cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);

if(isset($_POST['action'])) {
	//Check Token
	if(!cr_check_form_token()){
		cr_redirect("/account.php", MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
	}
	
	//Action Process
	switch ($_POST['action']){
		case 'submit-post':
			//Save Post
			$newTweet = crTweet::saveTweet($userID['id'], $_POST);
			
			if (!$newTweet){
				echo '<div class="err-div">';
				render_result_messages();
				echo '</div>';
				return;
			}

			while (!(isset($uploaded['ownerID']))){
				$uploaded = crTweet::getTweetById($newTweet);
			}
			
			$uploaded['likeID']= 0;
			
			echo '<div class="err-div">';
				render_result_messages();
			echo '</div>';

			echo render_new_tweet_box($userID['id']);
			echo cr_get_single_tweet_html($uploaded,$userID['id']);
			break;
		case 'edit-post':
			//Update Post
			$tID = $_POST['tID'];
			
			$updatedTweet = crTweet::updateTweet($userID['id'], $tID, $_POST);

			if (!$updatedTweet){
				echo '<div class="err-div">';
				render_result_messages();
				echo '</div>';
				return;
			}

			while (!(isset($uploaded['ownerID']))){
				$uploaded = crTweet::getTweetById($updatedTweet);
			}
			
			$uploaded['likeID']= 0;
			
			echo '<div class="err-div">';
				render_result_messages();
			echo '</div>';
			echo cr_get_single_tweet_html($uploaded,$userID['id']);
			break;
	}
}else if(isset($_GET['action'])){

	if ($_GET['action'] == 'delete-post'){
		//Delete Post
		echo ($userID['id'] != $_GET['userID'] || !crTweet::deleteTweet($userID['id'], $_GET['id']))?'Invalid Request':'success';
		
	}else if($_GET['action'] == 'unlikeTweet' || $_GET['action'] == 'likeTweet'){
		$tweet = crTweet::getTweetById($_GET['id']);

		$r = crTweet::likeTweet($userID['id'], $_GET['id'], $_GET['action']);
		$likes = crTweet::getTweetLikesCount($_GET['id']);

		render_result_xml(['status' => $r ? 'success' : 'error', 'message' => cr_get_messages(), 'likes' => $likes, 'id' => $_GET['id']]);
	}
}