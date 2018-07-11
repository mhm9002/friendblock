<?php
require(dirname(__FILE__) . '/includes/boot.php');
$returnURL= isset($_POST['return'])?$_POST['return']:null;

if(!($userID = cr_is_logged_in())){
    cr_redirect(cr_not_null($returnURL)?$returnURL:'/index.php', MSG_NOT_LOGGED_IN_USER, MSG_TYPE_ERROR);
}

if (isset($_POST['follower']) && isset($_POST['followed'])) {
	if ($_POST['follow_action']=='follow') {
		$ret = crFollowship::follow($_POST['follower'],$_POST['followed']);
	
		if ($ret=='followed'){
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/account.php", MSG_FOLLOW_PAGE_SUCCESS, MSG_TYPE_SUCCESS );
		} elseif ($ret=='followship request sent') {
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/account.php", MSG_FRIEND_REQUEST_SENT, MSG_TYPE_SUCCESS );
		} else {
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/index.php",MSG_FOLLOW_PAGE_FAIL,MSG_TYPE_ERROR);
		}
	} elseif($_POST['follow_action']=='unfollow') {
		$ret = crFollowship::unfollow($_POST['follower'],$_POST['followed']);
		
		if ($ret){
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/account.php", MSG_UNFOLLOW_PAGE_SUCCESS, MSG_TYPE_SUCCESS );
		} else {
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/index.php",MSG_INVALID_REQUEST,MSG_TYPE_ERROR);
		}
	} elseif ($_POST['follow_action']=='accept_frequest') {
		
		if (!cr_check_form_token()) {
			cr_redirect(cr_not_null($returnURL)?$returnURL:"/index.php",MSG_INVALID_REQUEST,MSG_TYPE_ERROR);
		}
			
		
		$fID = $_POST['actionID'];
		$ret = crFollowship::acceptFollowRequest($fID, $_POST['follower'],$_POST['followed']);
		
		if ($ret){
			$removed = crActivity::removeActivityAndNotification($fID,'FOLLOW','FR_RECEIVED');
			if ($removed) {
			
				$newAct= crActivity::addActivity($_POST['followed'],$fID,'FOLLOW','FR_ACCEPTED',$fID);
				crActivity::addNotification($_POST['follower'],$newAct,crActivity::NOTIFICATION_TYPE_FOLLOW_REQUEST_ACCEPTED,1);
			}
		
		}
		
		render_result_xml(['status' => $ret ? 'success' : 'error', 'message' => cr_get_messages()]);
	} 
	
	
} else {

	cr_redirect(cr_not_null($returnURL)?$returnURL:"/index.php",MSG_INVALID_REQUEST,MSG_TYPE_ERROR);
}	
?>