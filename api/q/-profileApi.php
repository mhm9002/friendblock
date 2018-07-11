<?php

class crProfileApi {

    public function getInfoAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['lastDate']) ? $data['lastDate'] : null;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token should not be blank')]];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token is not valid.')]];
        }

        $userData = crUser::getUserData($profileID);

        unset($userData['email']);
        unset($userData['password']);

        if(!cr_not_null($profileID) || !cr_not_null($userData) || !crUser::checkUserID($profileID, true)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result(MSG_INVALID_REQUEST)]];
        }

        if ($userID==$profileID || $userData['privacy']==1){
            $canView= true;
        } else {
            $followStat =crFollowship::isFollowed($userID, $profileID);
            if (!$followStat || $followStat['status']!=1){
                $canView=false;
            } else {
                $canView=true;
            }
        }

        $result=[];

        if ($canView) {            
            $tweetType = "all";
            $tweets = crTweet::getTweetsByUserID($profileID, $userID,null,$lastDate,$tweetType);

            $result = JSON_encode_tweets($tweets,$userID);
            $privacy ="public";
        } else {
            $privacy ="private";
        }

        //user relation with profile
        $friendType = 0;

        if ($fr = crFollowship::isFollowed($profileID,$userID)){
            if ($fr['status']==0)
                $friendType=1;
        }
        
        if ($fr = crFollowship::isFollowed($userID,$profileID)){
            if ($fr['status']==0){
                $friendType+=2;
            } else {
                $friendType+=4;
            }
        } 

        $profileInfo = ["name" => $userData['name'], "thumbnail" => CR_SITE_URL .'/'. crUser::getProfileIcon($profileID), "description"=>$userData['description'],"friendType"=>$friendType,"privacy"=>$privacy];

        $basicInfo = [];
        
        foreach ($userData as $key=>$value){
            switch (strtolower($key)){
                case "username":
                case "birthday":
                case "gender":
                case "martial_stat":
                case "country":
                case "description":
                    $basicInfo[] = ["label"=> $key, "value"=>$value];
                    break;
            }
        }

        //Get Followed
        $followed = crFollowship::getAllFollowed($profileID);
        
        $followedUsers=[];
        
        if ($followed){
            foreach ($followed as $id){
                $user = crUser::getUserBasicInfo($id);

                if (!$user)
                    continue;
                    
                $user['thumbnail']= CR_SITE_URL.'/'.crUser::getProfileIcon($user['id']);
                
                /** 
                 * flagging Friendship types  
                 * 0 = No relations                             Follow
                 * 1 = person sent request (recieved by user)   Accept/Decline + Follow
                 * 2 = person recieved request (sent by user)   Cancel follow request
                 * 4 = person followed by user                  Unfollow
                 * 
                 * 3 = 1+2                                      Accept/Decline + Cancel sent follow request 
                 * 5 = 1+4                                      Accept/Decline + Unfollow
                 * x6x = 2+4 contradicting
                 * x7x = 1+2+4 contradicting
                 * 
                */

                $user['friendType']= 0;

                if ($fr = crFollowship::isFollowed($user['id'],$userID)){
                    if ($fr['status']==0)
                        $user['friendType']=1;
                }
                
                if ($fr = crFollowship::isFollowed($userID,$user['id'])){
                    if ($fr['status']==0){
                        $user['friendType']+=2;
                    } else {
                        $user['friendType']+=4;
                    }
                } 
                
                $followedUsers[]=$user;    
            }
        }
        //Get Followers
        $followers = crFollowship::getAllFollowers($profileID);
        
        $followerUsers=[];

        if ($followers){
            foreach ($followers as $id){
                $user = crUser::getUserBasicInfo($id);
                
                if (!$user)
                    continue;

                $user['thumbnail']= CR_SITE_URL.'/'.crUser::getProfileIcon($user['id']);
                $user['friendType']= 0;

                if ($fr = crFollowship::isFollowed($user['id'],$userID)){
                    if ($fr['status']==0)
                        $user['friendType']=1;
                }
                
                if ($fr = crFollowship::isFollowed($userID,$user['id'])){
                    if ($fr['status']==0){
                        $user['friendType']+=2;
                    } else {
                        $user['friendType']+=4;
                    }
                } 

                $followerUsers[]=$user;    
            }
        }
    
        $returnData = ["STATUS" => "SUCCESS", "FOLLOWING" => $followedUsers,"FOLLOWERS" => $followerUsers,
        "TWEETS" => $result, "INFO" => $profileInfo];

        if(count($basicInfo) > 0)
            $returnData['BASIC_INFO'] = $basicInfo;
        
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => $returnData];

    }

    public function getListAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['lastDate']) ? $data['lastDate'] : null;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $userData = crUser::getUserData($profileID);

        if(!cr_not_null($profileID) || !cr_not_null($userData) || !crUser::checkUserID($profileID, true)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $canView = $userID == $profileID || crFollowship::isFollowed($userID, $profileID) || $userData['privacy']==1;
        
        $result=[];

        if ($canView) {
            
            $tweetType = "all";
            $tweets = crTweet::getTweetsByUserID($profileID, $userID, null,$lastDate,$tweetType);

            $result = JSON_encode_tweets($tweets,$userID);
        } else {
            $result[] = ["message"=>"This profile is private. Send follow request"];
        }

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $result]];

    }

    public function getPhotosAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['lastDate']) ? $data['lastDate'] : null;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $userData = crUser::getUserData($profileID);

        if(!cr_not_null($profileID) || !cr_not_null($userData) || !crUser::checkUserID($profileID, true)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $canView = $userID == $profileID || crFollowship::isFollowed($userID, $profileID) || $userData['privacy']==1;

        if ($canView){
            $message = "This item is not ready at this moments";
        } else {
            $message = "This profile is private. Send him/her follow request";
        }

        //Getting Photos
        //        $photos = BuckysPost::getPhotosByUserID($profileID, $userID, BuckysPost::INDEPENDENT_POST_PAGE_ID, $canViewPrivate, null, null, 18, $lastDate);
        //        $resultPhotos = [];
        //        foreach($photos as $row){
        //            $resultPhotos[] = ["posted_date" => $row['post_date'], "thumbnail" => THENEWBOSTON_SITE_URL . DIR_WS_PHOTO . 'users/' . $row['poster'] . '/resized/' . $row['image'], "original" => THENEWBOSTON_SITE_URL . DIR_WS_PHOTO . 'users/' . $row['poster'] . '/original/' . $row['image']];
        //        }
        $resultPhotos = [];
        $resultPhotos[] = ['message'=>$message];

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "PHOTOS" => $resultPhotos

        ]];

    }

    public function getFollowsAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $page = isset($data['page']) ? $data['page'] : 1;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;
        $follow = isset($data['follow']) ? $data['follow']: "following";

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $userData = crUser::getUserData($profileID);

        if(!cr_not_null($profileID) || !cr_not_null($userData) || !crUser::checkUserID($profileID, true)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $canView = $userID == $profileID || crFollowship::isFollowed($userID, $profileID) || $userData['privacy']==1;

        //Getting Photos
        //Get Friends        
        
        $followUsers = $follow=="following" ? crFollowship::getAllFollowed($profileID,$page):crFollowship::getAllFollowers($profileID,$page);

        $resultFriends = [];
        foreach($followUsers as $id){
            $data = crUser::getUserBasicInfo($follow=="following"?$id['followedID']:$id['followerID']);

            $row['id'] = $data['id'];
            $row['name'] = $data['name'];
            $row['description'] = $data['description'];
            $row['thumbnail'] = CR_SITE_URL . crUser::getProfileIcon($data['id']);
            
            $row['friendType']= 0;

            if ($fr = crFollowship::isFollowed($data['id'],$userID)){
                if ($fr['status']==0)
                    $row['friendType']=1;
            }
            
            if ($fr = crFollowship::isFollowed($userID,$data['id'])){
                if ($fr['status']==0){
                    $row['friendType']+=2;
                } else {
                    $row['friendType']+=4;
                }
            } 

            $resultFriends[] = $row;
        }

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", strtoupper($follow) => $resultFriends]];
    }

    public function followAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $friendID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!isset($friendID) || !crUser::checkUserID($friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }
        if(crFollowship::isFollowed($userID, $friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }
        
        if($msg=crFollowship::follow($userID, $friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "MESSAGE" => $msg]];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($db->getLastError())];
        }
    }

    public function unfollowAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $friendID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!isset($friendID) || !crUser::checkUserID($friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        if(crFollowship::unfollow($userID, $friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => "SUCCESS", "MESSAGE" => MSG_FRIEND_REQUEST_REMOVED]];
        }else{
            ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(buckys_get_pure_messages())];
        }
    }

    public function deleteRequestAction(){
        global $db;

        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $friendID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!isset($friendID) || !crUser::checkUserID($friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        if(crFollowship::unfollow($userID, $friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => "SUCCESS", "MESSAGE" => MSG_FRIEND_REQUEST_REMOVED]];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result($db->getLastError())];
        }
    }

    public function acceptAction(){
        global $db;

        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $friendID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!isset($friendID) || !crUser::checkUserID($friendID)){
            return ['STATUS_CODE' => STATUS_CODE_NOT_FOUND, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $fID = crFollowship::isFollowed($friendID,$userID);

        if (!$fID)
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result("No follow request sent")];

        if ($fID['status']==1)
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result("User already followed")];

        if(crFollowship::acceptFollowRequest($fID['fID'], $friendID, $userID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => "SUCCESS", "MESSAGE" => MSG_FRIEND_REQUEST_APPROVED]];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result($db->getLastError())];
        }
    }

    public function declineAction(){
        global $db;

        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $friendID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!isset($friendID) || !crUser::checkUserID($friendID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        //reversed as the user is the second guy!
        if(crFollowship::unfollow($friendID,$userID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => "SUCCESS", "MESSAGE" => MSG_FRIEND_REQUEST_DECLINED]];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result($db->getLastError())];
        }
    }

    public function getProfileInfoAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['lastDate']) ? $data['lastDate'] : null;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token should not be blank')]];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token is not valid.')]];
        }

        $userData = crUser::getUserData($profileID);

        unset($userData['email']);
        unset($userData['password']);

        if(!cr_not_null($profileID) || !cr_not_null($userData) || !crUser::checkUserID($profileID, true)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result(MSG_INVALID_REQUEST)]];
        }

        $canView = $userID == $profileID || crFollowship::isFollowed($userID, $profileID) || $userData['privacy']==1;
        
        $userData['thumbnail']=CR_SITE_URL .'/'. crUser::getProfileIcon($profileID);
        $userData['canView'] = $canView;    


        $userData['friendType']= 0;

        if ($fr = crFollowship::isFollowed($userData['id'],$userID)){
            if ($fr['status']==0)
                $userData['friendType']=1;
        }
        
        if ($fr = crFollowship::isFollowed($userID,$userData['id'])){
            if ($fr['status']==0){
                $userData['friendType']+=2;
            } else {
                $userData['friendType']+=4;
            }
        } 


        $returnData = ["STATUS" => "SUCCESS",  "RESULT" => $userData];        
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => $returnData];

    }

    public function getFriendsAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['lastDate']) ? $data['lastDate'] : null;
        $profileID = isset($data['profileId']) ? $data['profileId'] : null;
        $option = isset($data['option'])?$data['option']:"0";

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token should not be blank')]];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => ['STATUS'=>'ERROR', 'ERROR'=>cr_api_get_error_result('Api token is not valid.')]];
        }

        //Get Followed
        $people = ($option=="0")?crFollowship::getAllFollowed($profileID):crFollowship::getAllFollowers($profileID);
        
        $users=[];
        
        if ($people){
            foreach ($people as $id){
                $user = crUser::getUserBasicInfo($id);
                $user['thumbnail']= CR_SITE_URL.'/'.crUser::getProfileIcon($user['id']);
                
                /** 
                 * flagging Friendship types  
                 * 0 = No relations                             Follow
                 * 1 = person sent request (recieved by user)   Accept/Decline + Follow
                 * 2 = person recieved request (sent by user)   Cancel follow request
                 * 4 = person followed by user                  Unfollow
                 * 
                 * 3 = 1+2                                      Accept/Decline + Cancel sent follow request 
                 * 5 = 1+4                                      Accept/Decline + Unfollow
                 * x6x = 2+4 contradicting
                 * x7x = 1+2+4 contradicting
                 * 
                */

                $user['friendType']= 0;

                if ($fr = crFollowship::isFollowed($user['id'],$userID)){
                    if ($fr['status']==0)
                        $user['friendType']=1;
                }
                
                if ($fr = crFollowship::isFollowed($userID,$user['id'])){
                    if ($fr['status']==0){
                        $user['friendType']+=2;
                    } else {
                        $user['friendType']+=4;
                    }
                } 
                
                $users[]=$user;    
            }
        }
        
        $returnData = ["STATUS" => "SUCCESS", "RESULT" => $users];

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => $returnData];

    }
}