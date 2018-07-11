<?php

class crSearchApi {

    public function getSearchListAction(){
        global $SITE_GLOBALS, $db;
        $data = $_POST;

        $keyword = isset($data['keyword']) ? $data['keyword'] : null;
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        //$sort = "pop";
        //$page = isset($data['page']) ? $data['page'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            //echo $token;
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //Search Results
        //$searchIns = new crSearch();
        //$pageIns = new BuckysPage();
        //$pageFollowerIns = new BuckysPageFollower();
        $searchTags=[];
        $searchResult=[];

        $step = isset($data['step'])?intval($data['step']):0;

        if (isset($data['TagsOnly'])){
            $searchTags = crTweet::searchTag($keyword,12,true,$step);
        } elseif (isset($data['UsersOnly'])){
            $searchResult = crFollowship::searchPeople($keyword,12,$step);
        } else{
            $searchTags = crTweet::searchTag($keyword,12,true,$step);
            $searchResult = crFollowship::searchPeople($keyword,12,$step);
        }
        
        $results = [];

        foreach ($searchTags as $tag){
            $results[]= ["type"=>"tag","val"=>$tag];
        }
        
        foreach ($searchResult as $ID){
            $data =[];
            
            $uID = $ID['id'];

            $isFollowed = crFollowship::isFollowed($userID,$uID);
            $user = crUser::getUserBasicInfo($uID);
            
            //var_dump($uID);
            
            $user['thumbnail'] = CR_SITE_URL."/".crUser::getProfileIcon($uID); 
            $user['isFollowed']= $isFollowed ? "Yes":"No";

            $data["type"] = "user";
            $data["val"]= $user;
            $results[]= $data;
        }

        //echo json_encode(['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]]);
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }

    public function getListAction(){
        $data = $_POST;

        $keyword = isset($data['keyword']) ? $data['keyword'] : null;
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $date = isset($data['lastDate']) ? trim($data['lastDate']) : date("Y-m-d H:i:s",time());
        $fdate = isset($data['firstDate']) ? trim($data['firstDate']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }
        
        $search = crTweet::searchTweets($keyword,$userID,12,$date);
        
        $results = ($search!=null)?JSON_encode_tweets($search,$userID):null;
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }

    public function getFriendListAction(){
        global $SITE_GLOBALS, $db;
        $data = $_POST;

        $keyword = isset($data['keyword']) ? $data['keyword'] : null;
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        //$sort = "pop";
        //$page = isset($data['page']) ? $data['page'] : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //Search Results
        //$searchIns = new BuckysSearch();
        //$pageIns = new BuckysPage();
        //$pageFollowerIns = new BuckysPageFollower();

        $search = crFollowship::searchFriends($userID,$keyword);

        $results = [];

        foreach ($search as $id){
            $friend = crUser::getUserBasicInfo($id);
            $results[] = $friend;
        }

        /*
        $db_results = $searchIns->search($keyword, BuckysSearch::SEARCH_TYPE_USER_AND_PAGE, $sort, $page);

        $results = [];

        foreach($db_results as $item){

            if($item['type'] == "user"){

                //Getting Detail Information
                $query = $db->prepare("SELECT 
                                u.firstName, 
                                u.lastName, 
                                u.userID, 
                                u.thumbnail, 
                                u.current_city, 
                                u.current_city_visibility,
                                f.friendID 
                          FROM 
                                " . TABLE_USERS . " AS u
                          LEFT JOIN " . TABLE_FRIENDS . " AS f ON f.userID=%d AND f.userFriendID=u.userID AND f.status='1'
                          WHERE u.userID=%d", $userID, $item['userID']);
                $data = $db->getRow($query);

                if($data['friendID']){
                    $row = [];

                    $row['id'] = $item['userID'];
                    $row['name'] = $data['firstName'] . " " . $data['lastName'];
                    $row['description'] = $data['current_city_visibility'] ? $data['current_city'] : "";
                    $row['friendType'] = "user";
                    $row['thumbnail'] = THENEWBOSTON_SITE_URL . BuckysUser::getProfileIcon($data);

                    $results[] = $row;
                }
            }
        }
        */
        
        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $results]];
    }
}