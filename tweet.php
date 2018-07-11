<?php
/**
 * Get single tweet window
 */

require(dirname(__FILE__) . '/includes/boot.php');

$userID = cr_is_logged_in();

if(!$userID){
    echo MSG_INVALID_REQUEST;
    exit;
}

if(isset($_POST['action'])){

    if($_POST['action'] == 'get_tweet_edit'){
        $tweetID = $_POST['tweetID'];
        render_tweet_box($tweetID);
        exit;
    }

    //Get single tweet
    if($_POST['action'] == 'get_single_tweet'){
        
        $tweetID = $_POST['tweetID'];
        $ownerID = $_POST['userID'];

		$tweet = crTweet::getTweetById($tweetID);
		$owner = crUser::getUserBasicInfo($ownerID);
		
        //if Post Id was not set, show error
        if(!$tweetID || !($tweet['ownerID']==$ownerID)){
            var_dump ($tweet['ownerID']);
            var_dump($ownerID);
            echo MSG_INVALID_REQUEST;
            exit;
        }
			
        //Check the post id is correct
        if(!crTweet::checkTweetID($tweetID)){
            echo MSG_POST_NOT_EXIST;
            exit;
        }

		$followedStatus = crFollowship::isFollowed($userID['id'],$ownerID);

		if ((!$followedStatus || $followedStatus['status']==0) && $owner['privacy']==0 && $userID['id'] != $tweet['ownerID']){
			echo ' This tweet is private. You don\'t have access to it. Send tweet onwer a follow request to see this tweet ';
		}
		
        //If error, show it
        $tweet = crTweet::GetLikesCommentsRetweets($tweet,$userID['id']);
        $tweet['author'] = $owner;
        
        if(!($twtHtml = cr_get_single_tweet_html($tweet,$userID['id']))){
            echo $db->getLastError();
            exit;
        }else{
            //Show Results
            ?>
        <div class="modal fade" id="<?php echo $ownerID.'-'.$tweetID; ?>" tabindex="-1" role="dialog" aria-labelledby="single-tweet-title" aria-hidden="true" style="display: none;">
	  		<div class="modal-dialog modal-lg" role="document">
		    	<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title" id="single-tweet-title">Tweet</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body"><?php echo $twtHtml ?></div>             
	    			<div class="modal-footer"></div>
	   	 		</div>
	    	</div>
	    </div>
        	<?php    
            
            exit;
        }
    }
}