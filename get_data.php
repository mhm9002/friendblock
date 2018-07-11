<?php
require(dirname(__FILE__) . '/includes/boot.php');
$userID = cr_is_logged_in();

if($_POST['page'] == 'account' || $_POST['page'] == 'profile'){
    if(!$userID)
        exit;
    
    if ($_POST['page']=='account') {
		$stream = crTweet::getUserTweetsStream($userID['id'], $_POST['lastDate']);
	} else {
		$stream = crTweet::getTweetsByUserID($_POST['owner'],$userID['id'],null,$_POST['lastDate']);
	}

	//var_dump($_POST['lastDate']);
	//var_dump($stream);
	if (sizeof($stream)==0) {
		$lastDate = $_POST['lastDate'];
	} else {
		$lastDate = $stream[sizeof($stream)-1]['date'];
	}

    foreach($stream as $tweet){
        if ($_POST['page']=='account') {
            if (!crTweet::isAlreadyRetweetedByUser($tweet,$userID['id']))
            	echo cr_get_single_tweet_html($tweet, $userID['id']);
		} else {
        	echo cr_get_single_tweet_html($tweet, $userID['id']);
		}
    }
	
	echo '<input type="hidden" class="lastDate" value="'.$lastDate.'"/>';

}else if($_POST['page'] == 'notification'){
    if(!$userID)
        exit;
    
	$notifications = crActivity::getNotifications($userID['id'],15, null, $_POST['lastDate']);
	
	//var_dump($_POST['lastDate']);
	
    foreach($notifications as $nName=>$note){
        
        if (is_array($note)) {
						
			$nNameArr = explode('-',$nName);
											
			$actType=$nNameArr[0];
			$objID=$nNameArr[1];
			
			if (!cr_not_null($note['createdDate'])){
				continue;
			}

			$nText = crActivity::getActivityHTML($note,$userID['id'],$actType, $objID);
			

				//$lastDate =
			
			$class= $note['isNew'] ? 'notification-row new': 'notification-row';
			
			echo '<div class="'.$class.'">'.$nText.'
				<input type="hidden" class="created-date" value="'.$note['createdDate'].'"></input>	
			</div>';
							
			crActivity::markReadNotifications($userID['id'],$actType,$objID);
		}
    }
}