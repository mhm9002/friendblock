<?php
require(dirname(__FILE__) . '/includes/boot.php');

//Getting Current User ID
if(!($userID = cr_is_logged_in())){
    cr_redirect('/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

if (!isset($_POST['tweetID']))
	return FALSE;

if (isset($_POST['action']) && $_POST['action']=='remove-retweet'){
		
	$d = crTweet::deleteRetweet($_POST['tweetID'], $userID['id']);
	
	render_result_xml(['status' => $d ? 'success' : 'error', 'messages'=>cr_get_messages()]);

} else {	
		
	$tID = $_POST['tweetID'];
	$oID = $_POST['ownerID'];

	if ($oID==$userID['id']){
		cr_add_message('You cannot retweet your own tweet',MSG_TYPE_NOTIFY);	
		$r = FALSE;
	} elseif (!crTweet::isRetweeted($tID,$userID['id'])) {
		cr_add_message('Retweet successful',MSG_TYPE_SUCCESS);
		$r = crTweet::addRetweet($tID,$oID,$userID['id']);
	} else {
		cr_add_message('This tweet was already tweeted. If you want to remove the tweet, simply press "Delete"',MSG_TYPE_NOTIFY);	
		$r = FALSE;
	}

	render_result_xml(['status' => ($r === FALSE) ? 'error' : 'success', 'messages'=>cr_get_messages()]);
}

?>