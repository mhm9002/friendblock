<?php

/**
 * Manage Post Comments
 */
class crComment {

    public static $COMMENT_LIMIT = 5;

    /**
     * Getting Post Comments
     *
     * @param      $postID
     * @param null $last_date
     * @return Indexed
     */
    public static function getTweetComments($tweetID, $last_date = null, $limit=5){
        global $db;

        $userID = cr_is_logged_in();

        if(!$last_date)
            $last_date = date('Y-m-d H:i:s');
        $query = $db->prepare("SELECT c.*, u.name, p.ownerID FROM " . TABLE_COMMENTS . " AS c " .
            "LEFT JOIN " . TABLE_USERS . " AS u ON u.id=c.commenterID " . 
            "LEFT JOIN " . TABLE_TWEETS . " AS p ON p.id=c.tweetID ".
            "WHERE c.tweetID=%s AND c.date < %s ORDER BY c.date DESC LIMIT %d ", $tweetID, $last_date, $limit);

        $rows = $db->getResultsArray($query);

        $rows= array_reverse($rows);

        return $rows;
    }

    /**
     * Getting All Post Comments
     *
     * @param $postID
     * @return Indexed
     */
    public function getTweetAllComments($tweetID){
        global $db;

        $userID = cr_is_logged_in();

        $query = $db->prepare("SELECT c.*, u.name, u.thumbnail AS commenterThumbnail, p.ownerID FROM " . TABLE_COMMENTS . " AS c " . "LEFT JOIN " . TABLE_USERS . " AS u ON u.id=c.commenterID " . "LEFT JOIN " . TABLE_TWEETS . " AS p ON p.id=c.tweetID WHERE c.tweetID=%s ORDER BY c.date DESC ", $tweetID);

        $rows = $db->getResultsArray($query);

        return $rows;
    }

    /**
     * Get Post Comments Count
     *
     * @param mixed $postID
     * @return Int
     */
    public static function getTweetCommentsCount($tweetID){
        global $db;

        $query = $db->prepare("SELECT commentsCount FROM " . TABLE_TWEETS . " WHERE id=%d", $tweetID);
        $c = $db->getVar($query);

        return $c;
    }

    /**
     * Save Comment
     *
     * @param Int    $userID
     * @param Int    $postID
     * @param String $comment
     * @return int|null|string
     */
    public static function saveComments($userID, $tweetID, $comment, $image = null, $rtl=false){
        global $db;

        $now = date("Y-m-d H:i:s");

        if($image != null){

            if(file_exists(DIR_IMG_TMP .'/'. $image)){
                list($width, $height, $type, $attr) = getimagesize(DIR_IMG_TMP .'/'. $image);

                if($width > MAX_COMMENT_IMAGE_WIDTH){
                    $height = $height * (MAX_COMMENT_IMAGE_WIDTH / $width);
                    $width = MAX_COMMENT_IMAGE_WIDTH;
                }
                if($height > MAX_COMMENT_IMAGE_HEIGHT){
                    $width = $width * (MAX_COMMENT_IMAGE_HEIGHT / $height);
                    $height = MAX_COMMENT_IMAGE_HEIGHT;
                }

                crTweet::moveFileFromTmpToUserFolder($userID, $image, $width, $height, 0, 0);
            }else{
                $image = null;
            }
        }

        $newId = $db->insertFromArray(TABLE_COMMENTS, ['tweetID' => $tweetID, 'commenterID' => $userID, 'content' => $comment, 'image' => $image, 'date' => $now, 'rtl'=>$rtl]);

        if(cr_not_null($newId)){
            $tweetData = crTweet::getTweetById($tweetID);
            //crUsersDailyActivity::addComment($userID);
            //Update comments on the posts table
            $query = $db->prepare('UPDATE ' . TABLE_TWEETS . ' SET `commentsCount`=`commentsCount` + 1 WHERE id=%d', $tweetID);
            $db->query($query);
            
            //Add Activity
            $activityID = crActivity::addActivity($userID, $tweetID, 'TWEET', 'COMMENT', $newId);
            
            //Add Notification
            if($tweetData['ownerID'] != $userID)
                crActivity::addNotification($tweetData['ownerID'], $activityID, crActivity::NOTIFICATION_TYPE_COMMENT_TO_TWEET);
            
            //Get Already Commented users which commentToComment is 1
            //$query = $db->prepare("SELECT DISTINCT(pc.commenterID), IFNULL(un.notifyCommentToMyComment, 1) AS notifyCommentToMyComment FROM " . TABLE_POSTS_COMMENTS . " AS pc LEFT JOIN " . TABLE_USERS_NOTIFY_SETTINGS . " AS un ON pc.commenter = un.userID WHERE pc.postID=%d AND pc.commenter != %d AND IFNULL(un.notifyCommentToMyComment, 1) > 0", $postID, $userID);
            //$rows = $db->getResultsArray($query);
            
            //foreach($rows as $row){
            //    crActivity::addNotification($row['commenter'], $activityID, BuckysActivity::NOTIFICATION_TYPE_COMMENT_TO_COMMENT);
            //}
            
        }
        return $newId;
    }

    /**
     * Get Comment By ID
     *
     * @param $commentID
     * @return array
     */
    public static function getComment($commentID){
        global $db;

        $query = $db->prepare("SELECT c.*, u.name, p.ownerID FROM " . TABLE_COMMENTS . " AS c
                                    LEFT JOIN " . TABLE_USERS . " AS u ON u.id=c.commenterID
                                    LEFT JOIN " . TABLE_TWEETS . " AS p ON p.id=c.tweetID
                                    WHERE c.cID=%s", $commentID);
        $row = $db->getRow($query);

        return $row;
    }

    /**
     * @param      $postID
     * @param null $last_date
     * @return one
     */
    public static function hasMoreComments($tweetID, $last_date = null){
        global $db;

        if(!$last_date)
            $last_date = date('Y-m-d H:i:s');
        $query = $db->prepare("SELECT count(1) FROM " . TABLE_COMMENTS . " WHERE tweetID=%s AND date < %s ", $tweetID, $last_date);

        $c = $db->getVar($query);

        return $c;
    }

    /**
     * @param $userID
     * @param $commentID
     * @return bool
     */
    public static function deleteComment($userID, $commentID){
        global $db;

        $query = $db->prepare("SELECT c.cID, c.tweetID FROM " . TABLE_COMMENTS . " AS c LEFT JOIN " . TABLE_TWEETS . " AS p ON p.id=c.tweetID WHERE c.cID=%s AND (c.commenterID=%s OR p.ownerID=%s)", $commentID, $userID, $userID);
        $row = $db->getRow($query);

        if(!$row){
            return false;
        }else{
            $cID = $row['cID'];
            $tweetID = $row['tweetID'];

            $db->query('DELETE FROM ' . TABLE_COMMENTS . " WHERE cID=" . $cID);
            //Remove Activity
            $db->query( 'DELETE FROM ' . TABLE_ACTIVITIES . " WHERE actionID=" . $cID );
            

            //Update comments on the posts table
            $query = $db->prepare('UPDATE ' . TABLE_TWEETS . ' SET `commentsCount`=`commentsCount` - 1 WHERE id=%d', $tweetID);
            $db->query($query);

            $postData = crTweet::getTweetById($tweetID);
            //Update User Stats
            //crUser::updateStats($postData['poster'], 'comments', -1);

            return true;
        }
    }

    /**
     * @param $commendID
     * @return one
     */
    public function getTweetID($commentID){
        global $db;

        $query = $db->prepare("SELECT tweetID FROM " . TABLE_COMMENTS . " WHERE cID=%d", $commentID);

        return $db->getVar($query);
    }

    /**
     * @param $commendID
     * @return array
     */
    public static function getTweet($commentID){
        global $db;

        $query = $db->prepare("SELECT p.* FROM " . TABLE_COMMENTS . " AS c LEFT JOIN " . TABLE_TWEETS . " AS p ON p.id=c.tweetID WHERE c.cID=%d", $commentID);

        return $db->getRow($query);
    }

    /**
     * @param $userID
     * @return bool
     */
    public function checkUserDailyCommentsLimits($userID){
        global $db;

        if(buckys_check_user_acl(USER_ACL_MODERATOR) || buckys_check_user_acl(USER_ACL_ADMINISTRATOR)){
            return true;
        }

        //Get created posts on today
        $query = $db->prepare("SELECT count(*) FROM " . TABLE_COMMENTS . " WHERE commenter=%d AND DATE(`posted_date`) = %s", $userID, date("Y-m-d"));
        $comments = $db->getVar($query);

        if($comments > USER_DAILY_LIMIT_COMMENTS){
            return false;
        }

        return true;
    }

}
