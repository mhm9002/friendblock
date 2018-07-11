<?php

class crCommentApi {

    public function getListAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tweetID']) ? trim($data['tweetID']) : null;
        $iDate = isset($data['date'])? trim($data['date']):null;

        if(!$token || !$tweetID){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $comments = crComment::getTweetComments($tweetID,$iDate);

        $results = [];

        for($i=0; $i<sizeof($comments);$i++){
            $commenter = crUser::getUserBasicInfo($comments[$i]['commenterID']);
            $comments[$i]['commenterName']= $commenter['name'];
            $comments[$i]['thumbnail'] = CR_SITE_URL.'/'.crUser::getProfileIcon($comments[$i]['commenterID']);
        } 

        if(count($comments) > 0 && crComment::hasMoreComments($tweetID, $comments[0]['date'])){
            $hasMoreComments="1";
        } else {
            $hasMoreComments="0";
        }

        $results['comments'] = $comments;
        $results['hasMore'] = $hasMoreComments;

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }

    public function createAction(){
        global $SITE_GLOBALS;

        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tweetId']) ? trim($data['tweetId']) : null;

        $image = null;

        if(!$token || !$tweetID){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //First Upload File
        if(isset($_FILES['image'])){
            //Upload photo if it is image type
            $tempFile = $_FILES['image']['tmp_name'];

            $targetPath = DIR_IMG_TMP;

            if(!is_dir($targetPath)){
                mkdir($targetPath, 0777);
                //Create Index file
                $fp = fopen($targetPath . "/index.html", "w");
                fclose($fp);
            }

            // Validate the file type
            $fileParts = pathinfo($_FILES['image']['name']);

            //Check the file extension
            if(in_array(strtolower($fileParts['extension']), $TNB_GLOBALS['imageTypes'])){

                //Check Image Size
                list($width, $height, $type, $attr) = getimagesize($tempFile);
                //Check Image Type
                if(!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_JPEG2000, IMAGETYPE_PNG])){
                    return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_PHOTO_TYPE)];
                }
                if($width * $height > MAX_IMAGE_WIDTH * MAX_IMAGE_HEIGHT){
                    return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_PHOTO_MAX_SIZE_ERROR)];
                }else{
                    $targetFileName = md5(uniqid()) . "." . $fileParts['extension'];
                    $targetFile = $targetPath . '/' . $targetFileName;

                    move_uploaded_file($tempFile, $targetFile);

                    $image = $targetFileName;
                }
            }else{
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_PHOTO_TYPE)];
            }
        }

        $comment = $data['comment'];
        if(trim($comment) == '' && !$image){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_COMMENT_EMPTY)];
        }
        //if Post Id was not set, show error
        if(!$tweetID){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }

        //Check the post id is correct
        if(!crTweet::checkTweetID($tweetID)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_POST_NOT_EXIST)];
        }

        $tweet = crTweet::getTweetById($tweetID);
        $ownerData = crUser::getUserBasicInfo($tweet["ownerID"]);
        
        $canView = $userID == $tweet["ownerID"] || crFollowship::isFollowed($userID, $tweet["ownerID"]) || $ownerData['privacy']==1;
        
        if(!$canView){
            //Only Friends can leave comments to private post
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_INVALID_REQUEST)];
        }
        
        if(!($commentID = crComment::saveComments($userID, $tweetID, $comment, $image))){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($db->getLastError())];
        }else{
            $newComment = crComment::getComment($commentID);
            $newCount = crComment::getTweetCommentsCount($tweetID);

            $newComment['thumbnail'] = CR_SITE_URL .'/'. crUser::getProfileIcon($newComment['commenterID']);
            $newComment['commenterName']= $newComment['name'];

            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "COMMENTS" => $newCount, "NEWCOMMENT" => $newComment]];
        }
    }
}