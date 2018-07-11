<?php

class crStreamAPI {

    public function getListAction(){
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $lastDate = isset($request['lastDate']) ? $request['lastDate'] : null;
        $firstDate = isset($request['firstDate']) ? $request['firstDate'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if (!$lastDate && cr_not_null($firstDate))
            $lastDate = date('Y-m-d H:i:s');

        $stream = crTweet::getUserTweetsStream($userID, $lastDate, $firstDate, $userID);
        $result = JSON_encode_tweets($stream,$userID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $result]];

    }
}