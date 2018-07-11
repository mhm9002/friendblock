<?php

class crNotificationApi {

    public function getNotificationCountAction(){
        $request = $_GET;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $newNotificationCount = crActivity::getNumberOfNotifications($userID);        
        $results = [];
        $results['new_notification'] = $newNotificationCount;        

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }

    public function getNotificationAction(){
        global $SITE_GLOBALS, $db;

        $data = $_POST;
        
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $lastDate = isset($data['LASTDATE']) ? trim($data['LASTDATE']) : null;
        $newOnly = isset($data['NEW']) ? trim($data['NEW']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $notifications =  crActivity::getNotifications($userID,15,$newOnly,$lastDate);  //crActivity::getAppNotifications($userID);

        $results = [];

        foreach($notifications as $nName=>$note){
        
            if (is_array($note)) {
                            
                $nNameArr = explode('-',$nName);
                                                
                $actType=$nNameArr[0];
                $objID=$nNameArr[1];
                
                //remove notifs with no date
                if (!cr_not_null($note['createdDate'])){
                    continue;
                }
    
                foreach ($note['users'] as $uID){
                    $user = crUser::getUserBasicInfo($uID);
                    $user['thumbnail']= CR_SITE_URL .'/'. crUser::getProfileIcon($uID);
                    $note['actors'][] = $user;
                }

                unset($note['users']);

                $note['objID'] = $objID;
                $note['actType']= $actType;

                $results[]=$note;
                
                //check if we need to include it here, or it should be separate
                //crActivity::markReadNotifications($uID,$actType,$objID);
            }
        }

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }

    public function markReadNotificationAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if(crActivity::markReadNotifications($userID,$data['actType'], $data['objID'])){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS']];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('There was an error to mark read.')];
        }
    }
}