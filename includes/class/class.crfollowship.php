<?php

class crFollowship {

    public static $COUNT_PER_PAGE = 15;

    /**
     * Get Total Count of friends
     *
     * @param Int $userID
     * @return Int
     */
     
    public static function getNumberOfUFollow($userID){
        global $db;

        $query = $db->prepare("SELECT count(*) FROM " . TABLE_FOLLOWSHIP . " WHERE followerID=%d AND status=1", $userID);
        $count = $db->getVar($query);

        return $count;
    }

    public static function getNumberOfFollowedU($userID){
        global $db;

        $query = $db->prepare("SELECT count(*) FROM " . TABLE_FOLLOWSHIP . " WHERE followedID=%d AND status=1", $userID);
        $count = $db->getVar($query);

        return $count;
    }

    /**
     * Search User Friends
     *
     * @param mixed  $userID
     * @param String $term
     * @return Indexed
     */
    public static function searchFriends($userID, $term){
        global $db;
        
        $query = "SELECT u.id FROM " . TABLE_USERS . " AS u LEFT JOIN ". TABLE_FOLLOWSHIP ." as F 
        WHERE ((F.followedID=u.id AND F.followerID=".$userID.") OR (F.followedID=".$userID." AND F.followerID=u.id)) 
        AND u.status=1 
        AND (u.name LIKE '%" . $db->escapeInput($term) . "%' OR u.username LIKE '%" . $db->escapeInput($term) . "%') 
        ORDER BY u.name". (cr_not_null($limit) ? " LIMIT " . $limit :"") ;

        $rows = $db->getResultsArray($query);
		
        return $rows;
    }

    /**
     * Search People
     *
     * @param String $term
     * @return Indexed
     */

	public static function searchPeople($term, $limit=NULL, $step=null){
        global $db;
        $query = "SELECT id FROM " . TABLE_USERS . " WHERE status=1 AND 
        (name LIKE '%" . $db->escapeInput($term) . "%' OR username LIKE '%" . 
        $db->escapeInput($term) . "%') ORDER BY name" ;

		if (cr_not_null($limit))
			$query .= " LIMIT " . $limit. (cr_not_null($step)&&intval($step)>0? " OFFSET ". (intval($step)*$limit):"");

        $rows = $db->getResultsArray($query);
		
        return $rows;
    }

    /**
     * Check that the two users is friend
     *
     * @param $userID
     * @param $userFriendID
     * @return one
     */
    public static function isFollowed($userID, $userFollowedID){
        global $db;

        $query = $db->prepare("SELECT * FROM " . TABLE_FOLLOWSHIP . " WHERE followerID=%d AND followedID=%d", $userID, $userFollowedID);
        $fid = $db->getRow($query);

        return $fid;
    }

    /**
     * Get Total Number Of Friends
     *
     * @param Int $userID
     * @return one
     */
     
    public static function getNumberOfPendingRequests($userID){
        global $db;

        $query = $db->prepare("SELECT count(f.followerID) FROM " . TABLE_FOLLOWSHIP . " AS f LEFT JOIN " . TABLE_USERS . " AS u ON u.id=f.followerID WHERE u.status=1 AND f.followedID=%d AND f.status='0' ", $userID);

        $count = $db->getVar($query);

        return $count;
    }

    /**
     * Unfollow
     *
     * @param Int   $userID
     * @param Array $ids
     * @return bool
     */
    public static function unfollow($userID, $ids){
        global $db;
        
		if ($userID==0 || $ids==0)
			return FALSE;

        if(!is_array($ids))
            $ids = [$ids];

        foreach($ids as $id){
            if (crFollowship::isFollowed($userID,$id)) {
            
            	$query = $db->prepare("SELECT fID FROM " . TABLE_FOLLOWSHIP . " WHERE followerID=%d AND followedID=%d", $userID, $id);
            	$fID = $db->getVar($query);
            	
            	$query = $db->prepare("DELETE FROM " . TABLE_FOLLOWSHIP . " WHERE fID=%d", $fID);
            	$db->query($query);
        	
        		crActivity::removeActivityAndNotification($fID,'FOLLOW','FR_RECEIVED');
        	
        	}
		}
        return true;
    }

    /**
     * Follow function
     *
     * @param Int   $userID
     * @param Array $ids
     * @return bool
     */
    public static function follow($userID, $id){
        global $db;

		if ($userID==0 || $id==0)
			return FALSE;

        if (cr_not_null(crFollowship::isFollowed($userID,$id)))
        	return false;
        	 
		$userData= crUser::getUserData($id);
		$userEmail = crUser::getUserEmail($id);
			
		if ($userData['privacy']==1) {
            $followshipID = $db->insertFromArray(TABLE_FOLLOWSHIP, ['followerID' => $userID, 'followedID' => $id, 'status' => 1]);
			
			$activityID =crActivity::addActivity($userID,$followshipID,'FOLLOW','FOLLOW',$followshipID);
			
			$notificationID = crActivity::addNotification($id,$activityID,crActivity::NOTIFICATION_TYPE_FOLLOWED,TRUE);
			return 'followed';
		} else {
			$followshipID = $db->insertFromArray(TABLE_FOLLOWSHIP, ['followerID' => $userID, 'followedID' => $id, 'status' => 0]);
			
			$activityID =crActivity::addActivity($userID,$followshipID,'FOLLOW','FR_RECEIVED',$followshipID);			
			$notificationID = crActivity::addNotification($id,$activityID,crActivity::NOTIFICATION_TYPE_FOLLOW_REQUEST_RECIEVED,TRUE);
			
			return 'follow request sent';
		}
    	
    	return false;
	}

    /**
     * Accept follow request
     *
     * @param Int   $fID
     * @param Int	$followerID
     * @param Int  	$followedID
     * @return bool
     */
     
    public static function acceptFollowRequest($fID, $follower, $followed){
        global $db;

        $query = $db->prepare("UPDATE " . TABLE_FOLLOWSHIP . " SET status=1 WHERE fID=%d AND followedID=%d AND followerID=%d", $fID, $followed, $follower);
        $ret = $db->query($query);

		if ($ret)
			return TRUE;
        
        return FALSE;
    }

	public static function getAllFollowed($userID, $page=1, $limit=null){
		global $db;
        
        
        if (!cr_not_null($limit)){
            $limit = crFollowship::$COUNT_PER_PAGE;
        }

        $limitQuery = " LIMIT " . ($page - 1) * $limit . ", " . $limit;

		$query = $db->prepare("SELECT followedID FROM " . TABLE_FOLLOWSHIP . " WHERE followerID=%d AND status=1".$limitQuery, $userID);
        $rows = $db->getResultsArray($query);
        
        return $rows;
		
	}

	public static function getAllFollowers($userID, $page=1, $limit=null){
		global $db;
        
        if (!cr_not_null($limit)){
            $limit = crFollowship::$COUNT_PER_PAGE;
        }

        $limitQuery = " LIMIT " . ($page - 1) * $limit . ", " . $limit;

		$query = $db->prepare("SELECT followerID FROM " . TABLE_FOLLOWSHIP . " WHERE followedID=%d AND status=1".$limitQuery, $userID);
        $rows = $db->getResultsArray($query);
        
        return $rows;
	
    }
    
    public static function checkRequest($fID){
        global $db;

        $q = $db->prepare ('SELECT fID FROM '.TABLE_FOLLOWSHIP.' WHERE fID=%d',$fID);
        $r = $db->getVar($q);

        if ($r)
            return true;
            
        return false;
    }
}