<?php

class crTweetApi {

    public function uploadAction(){
        $data = $_POST;
        $fileData = $_FILES;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $pID = isset($data['pID']) ? trim($data['pID']) : null;
        $folder_token = isset($data['folder']) ? trim($data['folder']) : null;

        if(!$token)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
    
        if(!($userID = crUsersToken::checkTokenValidity($token, "api")))
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        
        if(!$pID)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Missing information')];
    
        if ($pDB=crTweet::uploadPhoto($fileData,$userID,$folder_token,$pID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'RESULT'=>['MESSAGE' => 'Photo uploaded successfully', 'IMGID'=>$pDB]]];
        } else {
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'FAILED', 'RESULT'=>['MESSAGE' => 'Failed to Upload']]];
        }

    }

    public function createAction(){
        //global $SITE_GLOBALS;
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $type = isset($data['type']) ? trim($data['type']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $data['rem1']=null;
        $data['rem2']=null;

        if($nID = crTweet::saveTweet($userID, $data)){ //Success
            $message = cr_get_pure_messages();
            $newTweet = [];
            $newTweet[]= crTweet::getTweetById($nID);
            $newTweet = JSON_encode_tweets($newTweet,$userID);

            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'RESULT'=> ['MESSAGE' => $message, 'TWEET'=>$newTweet]]];
        }else{
            $error = cr_get_pure_messages();

            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($error)];
        }
    }

    public function changeProfileImageAction(){
        global $SITE_GLOBALS;
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }
        //Upload photo if it is image type
        $photo = $_FILES['image']['tmp_name'];

        list ($width, $height, $type) = getimagesize($photo);
        
        $type = image_type_to_mime_type($type);
            
        if ($width!=IMAGE_THUMBNAIL_WIDTH || $height!=IMAGE_THUMBNAIL_HEIGHT){
            $attr = ['cX'=>0,'cY'=>0,'iW'=>$width,'iH'=>$height,'photo'=>$photo];
            $photo = cr_crop_for_thumbnail($attr, $userID, $type);        
        }
            
        while (!file_exists($photo)) sleep(1);
        
        $newThumbnail = crUser::updateUserProfileByPhotoURL($userID, $photo);
        $thumb="";
    
        if (!$newThumbnail){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_PHOTO_MAX_SIZE_ERROR)];
        }else{
            $message = cr_get_messages();
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'MESSAGE' => $message]];
        }
    }

    public function likeTweetAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tID']) ? $data['tID'] : null;
        $actionType = isset($data['actionType']) ? $data['actionType'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(!$tweetID || !$actionType){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $tweet = crTweet::getTweetById($tweetID);

        if(!$tweet){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
            exit;
        }

        $r = crTweet::likeTweet($userID, $tweetID, $actionType, false);
        $message = cr_get_pure_messages();

        if(!$r){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($message)];
            exit;
        }else{
            $likes = crTweet::getTweetLikesCount($tweetID);
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'MESSAGE' => $message, 'LIKES' => $likes, 'isLiked' => $actionType == 'likeTweet' ? 'yes' : 'no']];
        }

    }

    public function getTweetByIDAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tID']) ? $data['tID'] : null;

        
        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }
        

        if(!$tweetID){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        $tweet = crTweet::getTweetById($tweetID);

        if(!$tweet){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
            exit;
        }

        $tweet = crTweet::GetLikesCommentsRetweets($tweet,$userID);			
        
        $stream = [];
        $stream[]=$tweet;

        $result = JSON_encode_tweets($stream,$userID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $result]];
        
    }

    public function retweetAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tID']) ? $data['tID'] : null;
        $action = isset($data['action']) ? $data['action'] : "retweet";
        $retweetID = isset($data['retweetID']) ? $data['retweetID'] : null;

        if(!$token)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        
        if(!($userID = crUsersToken::checkTokenValidity($token, "api")))
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        
        if(!$tweetID)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        
        $tweetData = crTweet::getTweetById($tweetID);
        
        if(!$tweetData)
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
          
        $isRetweeted = crTweet::isRetweeted($tweetID,$userID);

        if ($isRetweeted && $action=="retweet" ) {
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result("This tweet is already retweeted")];
        } elseif (!$isRetweeted && $action!="retweet") {
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result("This tweet is not retweeted")];
        }

        $ret = ($action=="retweet")?crTweet::addRetweet($tweetID,$tweetData['ownerID'],$userID):crTweet::deleteRetweet($retweetID,$userID);
        
        $dataOut = (!$ret)?cr_api_get_error_result(MSG_INVALID_REQUEST):['STATUS' => 'SUCCESS'];
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => $dataOut];
    }

    public function deleteAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tID']) ? $data['tID'] : null;

        if(!$token)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        
        if(!($userID = crUsersToken::checkTokenValidity($token, "api")))
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        
        if(!$tweetID)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        
        if (!crTweet::deleteTweet($userID,$tweetID)){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        } else {
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS'=>'SUCCESS','RESULT'=> ['MESSAGE'=>'Tweet deleted']]];
        }
        
    }

}

?>