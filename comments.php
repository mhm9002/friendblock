<?php
/**
 * Add/Delete Comments
 */

require(dirname(__FILE__) . '/includes/boot.php');

$userID = cr_is_logged_in();

if(isset($_POST['action'])){

    //Save Comment
    if($_POST['action'] == 'save-comment'){
        if(!cr_check_form_token('request')){
            echo MSG_INVALID_REQUEST;
            exit;
        }

        if(!$userID){
            echo MSG_INVALID_REQUEST;
            echo 'this is the problem';
            exit;
        }
        $tweetID = $_POST['tweetID'];
        $comment = $_POST['comment'];
        $rtl = isset($_POST['rtl']) ? $_POST['rtl']:false;

        $image = isset($_POST['file']) ? $_POST['file'] : null;

        //If comment is empty, show error
        if(trim($comment) == '' && !$image){
            echo MSG_COMMENT_EMPTY;
            exit;
        }
        //if Post Id was not set, show error
        if(!$tweetID){
            echo MSG_INVALID_REQUEST;
            exit;
        }

        //Check the post id is correct
        if(!crTweet::checkTweetID($tweetID)){
            echo MSG_POST_NOT_EXIST;
            exit;
        }
		
        $tweet = crTweet::getTweetById($tweetID);
       	$followedStatus= crFollowship::isFollowed($userID['id'], $tweet['ownerID']);
       	$owner = crUser::getUserBasicInfo($tweet['ownerID']); 
       	
        if($owner['privacy']==0 && $userID['id'] != $tweet['ownerID'] && (!$followedStatus || $followedStatus['status']==0)){
            //Only Friends can leave comments to private post
            echo MSG_INVALID_REQUEST;
            echo 'only frineds!';
            exit;
        }
		
        //if(!crUsersDailyActivity::checkUserDailyLimit($userID['id'], "comments")){
        //    echo sprintf(MSG_DAILY_COMMENTS_LIMIT_EXCEED_ERROR, USER_DAILY_LIMIT_COMMENTS);
        //    exit;
        //}

        //If error, show it
        if(!($commentID = crComment::saveComments($userID['id'], $tweetID, $comment, $image, $rtl))){
            echo $db->getLastError();
            exit;
        }else{
            //Show Results
            header('Content-type: application/xml');
			
            $newComment = ['commenterID'=>$userID['id'],
            				'tweetID'=>$tweetID,
            				'content'=>$comment,
            				'image'=>$image,
            				'name'=>$userID['name'],
            				'date'=>date("Y-m-d H:i:s",time()),
            				'ownerID'=>$tweet['ownerID'],
                            'cID'=>$commentID,
                            'rtl'=>$rtl];
            $newCount = crComment::getTweetCommentsCount($tweetID);
            //var_dump($newComment);
            
            render_result_xml(['newcomment' => render_single_comment($newComment, $userID['id'], true), 'count' => $newCount]);
            exit;
        }
    }

    //Getting More Comments
    if($_POST['action'] == 'get-comments'){
        $tweetID = $_POST['tweetID'];
        $lastDate = $_POST['last'];
        $comments = crComment::getTweetComments($tweetID, $lastDate);
        //Show Results
        header('Content-type: application/xml');
        $commentsHTML = '';
        foreach($comments as $comment){
            $commentsHTML .= render_single_comment($comment, $userID['id'], true);
            if ($lastDate > $comment['date'])
                $lastDate = $comment['date'];
        }
        $result = ['comment' => $commentsHTML];

        render_result_xml(['comment' => $commentsHTML, 'lastdate' => $lastDate, 'hasmore' => ($commentsHTML != '' && crComment::hasMoreComments($tweetID, $lastDate)) ? 'yes' : 'no']);
    }
    
    //Getting tweetlikers
    if($_POST['action']=='get-likers'){	
		$tweetID = $_POST['tweetID'];
		$likedUsers = crTweet::getLikedUsers($tweetID);
            ?>
            <div class="modal fade" id="likedUsers-<?php echo $tweetID ?>" tabindex="-1" role="dialog" aria-labelledby="userLiked" aria-hidden="true" style="display: none;">
  				<div class="modal-dialog modal-lg" role="document">
	    			<div class="modal-content">
					    <div class="modal-header">
					        <h5 class="modal-title" id="userLiked">Likes</h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          <span aria-hidden="true">&times;</span>
					        </button>
					    </div>
					    
						<div>
                    		<ul>
                        		<?php 
                        		foreach($likedUsers as $l){
									echo "<li>";
										render_person_contact($l);
									echo "</li>";
								}
 								
                        		if($_POST['likesCount'] > 30){ ?>
                            		<li class="more-likes">+ <?php echo $_POST['likesCount'] - count($likedUsers) ?> more</li>
                 		   		<?php } ?>
                 		   </ul>
                		</div>      	
    				</div>
  				</div>
			</div>
			<?php
		}
		
}else if($_GET['action']){
    //Delete Post
    if($_GET['action'] == 'delete-comment'){
        if(!$userID){
            echo MSG_INVALID_REQUEST;
            exit;
        }
        $tweetID = $_GET['tweetID'];
        $commentID = $_GET['commentID'];
        $cUserID = $_GET['userID'];

        if(!cr_check_form_token('request') || !crComment::deleteComment($userID['id'], $commentID)){
            echo 'Invalid Request';
        }else{
            header('content-type: application/xml');
            $newCount = crComment::getTweetCommentsCount($tweetID);

            render_result_xml(['commentcount' => $newCount]);
        }
        exit;
    }
}
?>