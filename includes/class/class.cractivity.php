<?php

/**
* Manage user Activities
*/
class crActivity {
    /**
    *   1: CommentToTweet, 
    *   2: RetweetToTweet, 
    *   3: Like Tweet
    *   21: FOLLOWED
    *   22: MENTIONED
    * 	31: Follow Request
    * 	32: Follow Request Accpeted
    * 	33:	
    */
    
    const NOTIFICATION_TYPE_COMMENT_TO_TWEET    = 1;
    const NOTIFICATION_TYPE_RETWEET_TO_TWEET 	= 2;
    const NOTIFICATION_TYPE_LIKE_TWEET          = 3;
    
    const NOTIFICATION_TYPE_FOLLOWED       		= 21;
    const NOTIFICATION_TYPE_MENTIONED     		= 22;
    const NOTIFICATION_TYPE_FOLLOW_REQUEST_RECIEVED     = 31;
    const NOTIFICATION_TYPE_FOLLOW_REQUEST_ACCEPTED     = 32;
    
	public static  $COUNT_PER_PAGE = 20;
	
    /**
     * @param     $userID
     * @param     $objectID
     * @param     $objectType
     * @param     $activityType
     * @param int $actionID
     */
    public static function addActivity($userID, $objectID, $objectType, $activityType, $actionID = 0){
        global $db;
        
        //Remove Duplicated Like Action Activity
        if($activityType == 'like'){
            $query = $db->prepare("DELETE FROM " . TABLE_ACTIVITIES . " WHERE userID=%d AND objectID=%d AND objectType=%s AND activityType=%s", $userID, $objectID, $objectType, $activityType);
            $db->query($query);
        }
        
        $activityID = $db->insertFromArray(TABLE_ACTIVITIES, array(
            'userID' => $userID,
            'objectID' => $objectID,
            'objectType' => $objectType,
            'activityType' => $activityType,            
            'createdDate' => date('Y-m-d H:i:s'),            
            'isNew' => 1,            
            'activityStatus' => 1,            
            'actionID' => $actionID           
        ));
        
        return $activityID;
        
    }
    
    public static function addNotification($userID, $activityID, $notificationType, $isNew = 1, $objID=NULL)
    {
        global $db;

	  	$note = array('userID' => $userID, 'activityID' => $activityID, 'notificationType' => $notificationType, 'isNew' => $isNew, 'createdDate'=>date('Y-m-d H:i:s'));
        
        $db->insertFromArray(TABLE_NOTIFICATIONS, $note); 
	
    	require_once (DIR_INC.'/pusher/pusher.php');
		require_once (DIR_INC.'/pusher/pusherexception.php');
    	require_once (DIR_INC.'/pusher/pusherinstance.php');
    
	    $options = array('cluster' => 'ap2','encrypted' => true);
  		
  		$pusher = new Pusher\Pusher(PUSHER_AUTH, PUSHER_SECRET, PUSHER_APP, $options);
	  	
	  	$act = crActivity::getNotificationByActivityID($activityID);
	  	
	  	foreach ($act as $key=>$value){
			$param = explode ('-', $key);
			$actType = $param[0];
			$objID= $param[1];
			
			$message = crActivity::getActivityHTML($value,$userID,$actType,$objID);
			$message = preg_replace('#<[^>]+>#', ' ', $message);
			$message = strip_tags($message);
		}
	  	
	  	$data['message'] = $message;
	 	
	 	$pusher->trigger($userID, 'notifications' ,$data);
    }    
    
    /**
     * @param     $userID
     * @param int $limit
     * @return Indexed
     */
    public static function getActivities($userID, $limit = 15){    
        global $db;
        
        $query = $db->prepare("SELECT a.*,p.*, pc.content as comment_content FROM " . TABLE_ACTIVITIES . " AS a 
                    INNER JOIN " . TABLE_FOLLOWSHIP . " as f ON a.userID=f.followedID AND f.followerID=%d
                    LEFT JOIN " . TABLE_TWEETS . " as p ON a.objectID=p.id
                    LEFT JOIN " . TABLE_COMMENTS . " as pc ON a.activityType='comment' AND pc.id=a.actionID 
                    WHERE a.userID != %d AND p.ownerID != %d ORDER BY a.createdDate desc LIMIT %d", $userID, $userID, $userID, $limit);
        
        $rows = $db->getResultsArray($query);
        
        return $rows;
    }
    
    /**
     * @param $row
     * @param $userID
     * @return string
     */
    public static function getActivityHTML($row, $userID, $activityType, $objectID){
        //var_dump($row);
        
        ob_start();
        
        ?>
        <div class="activityComment">
        <?php
        
        if (!is_array($row))
        	return;
	
		if (in_array($activityType, ['LIKE','COMMENT','MENTION','RETWEET'])){
			$tweet= crTweet::getTweetById($objectID);
			$owner = crUser::getUserBasicInfo($tweet['ownerID']);
			$objectLink = '/post.php?uID='. $userID .'&tID='. $objectID;
			
			foreach ($row['users'] as $user){
				$u = crUser::getUserBasicInfo($user);
				render_profile_link($u, 'replyToPostIcons');
			}
			
			$userLink=[];
			
			foreach ($row['users'] as $user){
				$u = crUser::getUserBasicInfo($user);
				
				$usersLinks []= '<a href="/profile.php?userID='. $u['id'].'" class="userName">'.$u['name'].'</a>';
			
			}
		
			$userText = implode(',',$usersLinks);
			
		} elseif (in_array($activityType,['FOLLOW','FR_ACCEPTED','FR_RECEIVED'])) {
				$user = crUser::getUserBasicInfo($row['users'][0]);
				$userLink = '/profile.php?userID=' . $row['users'][0];				
				render_profile_link($user, 'replyToPostIcons');	
		}
	    
		switch ($activityType){
			case 'LIKE':
				?>
				<span class="notification-text">
				<?php echo $userText.'&nbsp;'; ?>
				liked <?php echo $owner['id'] == $userID ? 'your' : ("<a href='/profile.php?user=" . $owner['id'] . "' class=\"userName\">" . $owner['name']  . "'s</a>") ?>
						<?php 
						switch($tweet['type']){
							case "image":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>photo</a>";
								break;
							case "video":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>video</a>";
								break;
							case "text":
							default:
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>tweet</a> ";
								if(strlen(cr_trunc_content($tweet['content'], 60)) > 0){
										echo '&#8220;' . cr_trunc_content($tweet['content'], 60) . '&#8221;' ;
								}
						}
				?>
				</span>
				<?php   
										
				break;
			case 'COMMENT':
				?>
				<span class="notification-text">
				<?php echo $userText.'&nbsp;'; ?>
				commented on <?php echo $owner['id'] == $userID ? 'your' : ("<a href='/profile.php?user=" . $owner['id'] . "' class=\"userName\">" . $owner['name']  . "'s</a>") ?>
						<?php 
						switch($tweet['type']){
							case "image":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>photo</a>";
								break;
							case "video":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>video</a>";
								break;
							case "text":
							default:
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>tweet</a> ";
								if(strlen(cr_trunc_content($tweet['content'], 60)) > 0){
										echo '&#8220;' . cr_trunc_content($tweet['content'], 60) . '&#8221;' ;
								}
						}
				
				?>
				</span>
				<?php   
										
				break;			     
			case 'MENTION':
				?>
				<span class="notification-text">
				<?php echo $userText.'&nbsp;'; ?>
				mentioned you in <?php 
						switch(strtolower($owner['gender'])){
							case 'male':
								echo 'his';
								break;
							case 'female':
								echo 'her';		                                    								break;
							default:
								echo 'their';
								break;
						}
						echo '&nbsp;';
						switch($tweet['type']){
							case "image":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>photo</a>";
								break;
							case "video":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>video</a>";
								break;
							case "text":
							default:
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>tweet</a> ";                                
								break;    
						}
				?>
				</span>
				<?php   
										
				break;
			case 'RETWEET':
				?>
				<span class="notification-text">
				<?php echo $userText.'&nbsp;'; ?>
				retweeted your
						<?php 
						switch($tweet['type']){
							case "image":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>photo</a>";
								break;
							case "video":   
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>video</a>";
								break;
							case "text":
							default:
								echo "<a href='#' class='tweetPage' data-whatever='".$owner['id'].'-'.$objectID."'>tweet</a> ";
								if(strlen(cr_trunc_content($tweet['content'], 60)) > 0){
										echo '&#8220;' . cr_trunc_content($tweet['content'], 60) . '&#8221;' ;
								}
						}
				
				?>
				</span>
				<?php   
										
				break;
			
			case 'FOLLOW':	
			
				?>
				<span class="notification-text">
					<a href="<?php echo $userLink ?>"><?php echo $user['name'] ?></a>&nbsp;followed you
				</span>
	
				<?php
				break;
			case 'FR_ACCEPTED':
				?>
				<span>
					<a href="<?php echo $userLink ?>"><?php echo $user['name'] ?></a>&nbsp;has accepted your follow request
				</span>
	
				<?php
				break;
			case 'FR_RECEIVED':
				$follower = $row['users'][0];
				$followed= $userID;
			
				?>
				<span class="notification-text">
					<a href="<?php echo $userLink ?>"><?php echo $user['name']?></a>&nbsp;sent you a follow request.
					<form id="accept_frequest" style="display: inline-block;">
						<button class="btn btn-primary">Accept Follow Request</button>
						<?php render_form_token() ?>
						<input type="hidden" name="follow_action" value="accept_frequest"></input>
						<input type="hidden" name="actionID" value="<?php echo $objectID ?>"></input>				
						<input type="hidden" name="follower" value="<?php echo $follower ?>"></input>
						<input type="hidden" name="followed" value="<?php echo $followed ?>"></input>	
						<?php render_loading_wrapper(); ?>
					</form>
				</span>
	
				<?php
				break;
			}
	
        ?></div><?php
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }
    
    /**
     * @param      $userID
     * @param int  $page
     * @param null $status
     * @return Indexed
     */
    public static function getAppNotifications($userID, $page = 1, $status = null){
        global $db;
        $query = $db->prepare("SELECT activityID FROM " . TABLE_MAIN_NOTIFICATIONS . " WHERE userID = %d " . ($status != null ? " AND isNew=" . $status : "") . " ORDER BY createdDate DESC ", $userID);        
        $query .= " LIMIT " . ($page - 1) * BuckysActivity::$COUNT_PER_PAGE . ", " . BuckysActivity::$COUNT_PER_PAGE;
        
        $arows = $db->getResultsArray($query);
        $rows = array();
        foreach($arows as $aid){
            $query = $db->prepare("SELECT a.*,p.*, pc.content AS comment_content FROM " . TABLE_MAIN_ACTIVITIES . " AS a " . " LEFT JOIN " . TABLE_POSTS . " AS p ON a.objectID=p.postID " . " LEFT JOIN " . TABLE_POSTS_COMMENTS . " AS pc ON a.activityType='comment' AND pc.commentID=a.actionID " . " WHERE a.activityID=%d", $aid['activityID']);
            $row = $db->getRow($query);
            $rows[] = $row;
        }
        
        return $rows;
    }
	
    /**
     * @param      $userID
     * @param int  $limit
     * @param null $status
     * @return Indexed
     */
    public static function getNotifications($userID, $limit = 15, $status = null, $lastDate=null, $startDate=null){
        global $db;
        
		$query = $db->prepare('SELECT activityID FROM ' . TABLE_NOTIFICATIONS . ' WHERE userID = %d ' 
			. ($status != null ? ' AND isNew=' . $status : '') 
			. ($lastDate != null ? ' AND createdDate < "' . $lastDate . '"' : '')
			. ($startDate != null ? ' AND createdDate > "' . $startDate . '"' : '')
			.' ORDER BY createdDate DESC LIMIT %d', $userID, $limit);        
        
        $arows = $db->getResultsArray($query);
        
        $rows = array();
        foreach($arows as $aid){
            $query = $db->prepare("SELECT * FROM " . TABLE_ACTIVITIES . " WHERE activityID=%d", $aid['activityID']);
            $row = $db->getRow($query);
            
            $rows []= $row;
        }
        
        $rs = cr_activities_combine($rows);
		
		//load more notifications if the loaded ones are invalid
		if (sizeof($rows)>0 && !$rs){
			for ($u = sizeof($rows)-1; $u>-1;$u--)
				if ($date = $rows[$u]['createdDate']) 
					break;
			
			if ($startDate==null){
				$rs = crActivity::getNotifications($userID,15,$status, $rows[sizeof($rows)-1]['createdDate'] ,null);
			} else {
				$rs = crActivity::getNotifications($userID,15,$status, $lastDate, $rows[sizeof($rows)-1]['createdDate']);
			}
		}
		
        return $rs;
    }
    
    /**
     * @param      $ActivityID
     * @return Indexed
     */
    public static function getNotificationByActivityID($ActivityID){
        global $db;
        
        $query = $db->prepare("SELECT * FROM " . TABLE_ACTIVITIES . " WHERE activityID=%d", $ActivityID);
        $row = $db->getRow($query);
        
        if ($row){
			$alias = $row['activityType'].'-'.$row['objectID'];
			$ret[$alias]['users']=[$row['userID']];
			$ret[$alias]['isNew']=$row['isNew'];
			$ret[$alias]['createdDate']=$row['createdDate'];
			return $ret;
		}
        
        //$row = cr_activities_combine([$row]);
        
        return FALSE;
    }
    
    /**
    * Get the number of notifications
    * 
    * @param Int $userID
    * @return Int
    */
    public static function getNumberOfNotifications($userID, $isNew = 1){    
		$n = crActivity::getNotifications($userID,100,$isNew,null,null);
		return sizeof($n)/2;
    }
    
    /**
    * Make notifications to read
    * 
    * @param mixed $userID
    * @param mixed $noteID
    */
    public static function markReadNotifications($userID, $actType=null , $objID = null){
        global $db;
        
        if($actType AND $objID){
        
            $query = $db->prepare("UPDATE " . TABLE_NOTIFICATIONS . " SET isNew=0 WHERE userID=%d AND activityID IN (SELECT activityID FROM " . TABLE_ACTIVITIES . " WHERE activityType=%s AND objectID=%d AND isNew=1)", $userID, $actType, $objID);            
            $db->query($query);
            
            $query = $db->prepare("UPDATE " . TABLE_ACTIVITIES . " SET isNew=0 WHERE activityType=%s AND objectID=%d", $actType, $objID);            
            $db->query($query);
        
        }else{
        	$query = $db->prepare("UPDATE " . TABLE_ACTIVITIES . " SET isNew=0 WHERE activityID IN (SELECT activityID FROM " . TABLE_NOTIFICATIONS . " WHERE userID=%d)", $userID);            
            $db->query($query);
        	
            $query = $db->prepare("UPDATE " . TABLE_NOTIFICATIONS . " SET isNew=0 WHERE userID=%d", $userID);            
            $db->query($query);
        }
        return true;
    }
    
	public static function removeActivityAndNotification($objID, $objType, $actType){
		global $db;
		
		$query = $db->prepare('SELECT activityID FROM '.TABLE_ACTIVITIES.' WHERE objectID=%d AND objectType=%s AND activityType=%s', $objID,$objType,$actType);
		$actID = $db->getVar($query);
		
		if ($actID) {
			$query= $db->prepare('DELETE FROM '.TABLE_NOTIFICATIONS. ' WHERE activityID=%d',$actID);
			$ret1 = $db->query($query);
			
			$query= $db->prepare('DELETE FROM '.TABLE_ACTIVITIES. ' WHERE activityID=%d',$actID);
			$ret2 = $db->query($query);
			
			if ($ret1 AND $ret2)
				return TRUE;
			
		}
	return FALSE;	
	}
	

	/**
	 * Return last time the user participates in Comment/like/retweet/follow 
	 * 
	 * @param int $userID
	 * @return bool
	 */
	public static function getUserLastActivityDate($userID){
		global $db;

		$query = $db->prepare('SELECT createdDate FROM '.TABLE_ACTIVITIES.' WHERE userID=%d  ORDER BY createdDate desc LIMIT 1',$userID);
		$date = $db->getVar($query);

		if ($date)
			return $date;

		return false;

	}
}
?>