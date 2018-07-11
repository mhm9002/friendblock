<?php

class crAccountAPI {

    /**
     * Process Login from api
     *
     * @return userID, Email and Token
     */
    public function loginAction(){
        //The login request should be POST method
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $email = isset($request['email']) ? trim($request['email']) : null;
        $password = isset($request['password']) ? trim($request['password']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $info = cr_get_user_by_email($email);

        if(cr_not_null($info) && cr_validate_password($password, $info['password'])){
            if($info['status'] == 0){ //Account is not verified
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result(MSG_ACCOUNT_NOT_VERIFIED)];
            }else{
                //Remove Old Token
                crUsersToken::removeUserToken($info['id'], 'api');
                
                //Create New Token
                $token = crUsersToken::createNewToken($info['id'], 'api');

                $thumbnail = CR_SITE_URL.'/'.crUser::getProfileIcon($info['id']);
        
                $newNotificationCount = crActivity::getNumberOfNotifications($info['id']);        
                
                //echo json_encode (['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'TOKEN' => $token, 'EMAIL' => $info['email'], 'USERID' => $info['id']]]);
                return ['STATUS_CODE' => STATUS_CODE_OK, 
                    'DATA' => ['STATUS' => 'SUCCESS',  
                        'RESULT'=>['token' => $token, 
                            'email'     => $info['email'], 
                            'id'        => $info['id'], 
                            'thumbnail' =>$thumbnail, 
                            'name'      =>$info['name'],
                            'notifCount'=>$newNotificationCount
                        ]
                    ]
                ];
            }
        }else{
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS',  'RESULT'=>['message'=>cr_api_get_error_result('Email or password is not correct.')]]];
        }
    }

    public function registerAction(){
        $request = $_POST; //email, firstName, lastName, email, password, password2

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //Validate Input Data
        $newID = crUser::createNewAccount($request);

        if(!$newID){
            //Getting Error Message
            $error = cr_get_pure_messages();

            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($error)];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'USERID' => $newID, 'MESSAGE' => MSG_NEW_ACCOUNT_CREATED]];
        }
    }

    /**
     * Get User Basic Info

     */
    public function getBasicInfoAction(){
        $data = $_POST;
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $userData = crUser::getUserBasicInfo($userID);
        $userData['thumbnail'] = CR_SITE_URL.'/'.crUser::getProfileIcon($userID);
        $userData['token'] = $token;
        $userData['notifCount'] = crActivity::getNumberOfNotifications($userID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'RESULT' => $userData]];
    }

    public function saveInfoAction(){
        $data = $_POST;
        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //$userData = crUser::getUserData($userID);

        //This part shall be revisited once the other HttpClient prepared!
        /*
        if($data['birthdate_year'] == '-')
            $data['birthdate_year'] = '';
        if($data['birthdate_month'] == '-')
            $data['birthdate_month'] = '';
        if($data['birthdate_day'] == '-')
            $data['birthdate_day'] = '';

        switch($data['relationship_status']){
            case 'Single':
                $data['relationship_status'] = 1;
                break;
            case 'In a Relationship':
                $data['relationship_status'] = 2;
                break;
            case '-':
            default:
                $data['relationship_status'] = 0;
                break;
        }
        */

        unset($data['TOKEN']);
        unset($data['ACTION']);
        unset($data['TYPE']);

        //$data['timezone'] = $userData['timezone'];

        if(crUser::saveUserInfo($userID, $data)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS','RESULT'=>['Message'=>'Changes have been successfully saved']]];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result('There was an error to saving your information.')];
        }
    }

    /*
    public function getLinkInfoAction(){
        $request = $_GET;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $linkInfo = crUser::getUserLinks($userID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'RESULT' => $linkInfo]];
    }

    public function saveLinkInfoAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = BuckysUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $count = isset($data['COUNT']) ? $data['COUNT'] : 0;

        $info = [];

        for($i = 0; $i < $count; $i++){
            $row = [];

            $row['title'] = $data['TITLE' . $i];
            $row['url'] = $data['URL' . $i];
            $row['visibility'] = $data['VISIBILITY' . $i];

            $info[] = $row;
        }

        if(BuckysUser::updateUserLinks($userID, $info)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS']];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('There was an error to saving your information.')];
        }

        exit;
    }

    public function getContactInfoAction(){
        $request = $_GET;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = BuckysUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $contactInfo = crUser::getUserContactInfo($userID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS', 'RESULT' => $contactInfo]];
    }

    public function saveContactInfoAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = BuckysUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $header = [];
        $header['email'] = $data['email'];
        $header['work_phone'] = $data['work_phone'];
        $header['home_phone'] = $data['home_phone'];
        $header['cell_phone'] = $data['cell_phone'];
        $header['email_visibility'] = $data['email_visibility'];
        $header['home_phone_visibility'] = $data['home_phone_visibility'];
        $header['work_phone_visibility'] = $data['work_phone_visibility'];
        $header['cell_phone_visibility'] = $data['cell_phone_visibility'];

        $count = isset($data['COUNT']) ? $data['COUNT'] : 0;

        $info = [];

        for($i = 0; $i < $count; $i++){
            $row = [];

            $row['name'] = $data['CONTACT_NAME' . $i];
            $row['type'] = $data['CONTACT_TYPE' . $i];
            $row['visibility'] = $data['VISIBILITY' . $i];

            $info[] = $row;
        }

        if(BuckysUser::updateUserFields($userID, $header) && BuckysUser::updateUserMessengerInfo($userID, $info)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS']];
        }else{
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('There was an error to saving your information.')];
        }

        exit;
    }
    */

    public function forgotAction(){
        $request = $_POST; //email

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $email = isset($request['email'])? trim($request['email']): null;


        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        if (!$email){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('No email address specified')];
        }

        if(crUser::resetPassword($email,false)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS']];
        }else{
            //Getting Error Message
            $error = cr_get_pure_messages();
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result($error)];
        }        
    }

    public function changePasswordAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $current = crUser::getUserData($userID);

        if(!cr_validate_password($data['current_password'], $current['password'])){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result('Current password is incorrect.')];
        }else{
            $pwd = cr_encrypt_password($data['new_password']);

            if(crUser::updateUserFields($userID, ['password' => $pwd])){
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS','RESULT'=>['Message'=>'Password has been changed successfully']]];
            }else{
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result('Error saving new password')];
            }
        }
    }

    public function deleteAccountAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $current = crUser::getUserData($userID);

        if(!cr_validate_password($data['password'], $current['password'])){
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => cr_api_get_error_result('Current password is incorrect.')];
        }else{
            if(crUser::deleteUserAccount($userID)){
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS']];
            }else{
                return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('There was an error to saving your information.')];
            }
        }

        exit;
    }
    
    public function LoginBySocialDataAction(){
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $oEmail = isset($request['email']) ? trim($request['email']) : null;
        $oID = isset($request['oid']) ? trim($request['oid']) : null;
        $oNetwork = isset($request['network']) ? trim($request['network']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        $uID = crUser::getUserIDbySocialData($oNetwork,$oEmail,$oID);

        if (!$uID){
            $uID = crUser::getIDbyEmail($oEmail);
            
            if(!$uID)
                return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' =>['STATUS'=>'SUCCESS', 'RESULT'=>['MESSAGE'=>'User not registered']]];

            $userData = crUser::getUserBasicInfo($uID);

            $thumbnail = CR_SITE_URL.'/'.crUser::getProfileIcon($uID);

            return ['STATUS_CODE' => STATUS_CODE_OK, 
                    'DATA'  =>['STATUS'=>'SUCCESS', 
                    'RESULT'=>[
                        'MESSAGE'   => "Link is required",
                        'email'     => $oEmail, 
                        'id'        => $uID, 
                        'thumbnail' =>$thumbnail, 
                        'name'      =>$userData['name']
                        ]]];
        }

        $userData = crUser::getUserBasicInfo($uID);

        //Remove Old Token
        crUsersToken::removeUserToken($uID, 'api');
                        
        //Create New Token
        $token = crUsersToken::createNewToken($uID, 'api');
        $thumbnail = CR_SITE_URL.'/'.crUser::getProfileIcon($uID);
        $notifCount = crActivity::getNumberOfNotifications($uID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 
                'RESULT'=>[
                    'token'     => $token, 
                    'email'     => $oEmail, 
                    'id'        => $uID, 
                    'thumbnail' =>$thumbnail, 
                    'name'      =>$userData['name'],
                    'notifCount'=>$notifCount
                    ]]];
    }

    public function getSettingsAction(){
        $data = $_POST;

        $token = isset($data['TOKEN']) ? trim($data['TOKEN']) : null;
        $tweetID = isset($data['tID']) ? $data['tID'] : null;

        if(!$token)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        
        if(!($userID = crUsersToken::checkTokenValidity($token, "api")))
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        
        $userData = crUser::getUserBasicInfo($userID);
        $userData['email'] = crUser::getUserEmail($userID);
        $userData['fb']= cr_has_S_Profile('fb',$userData['email'],$userID)?"1":"0";
        $userData['g']= cr_has_S_Profile('g',$userData['email'],$userID)?"1":"0";

        return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ['STATUS' => 'SUCCESS','RESULT'=>$userData]];
    }

    public function socialUnlinkAction(){
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $oNetwork = isset($request['network']) ? trim($request['network']) : null;
        
        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if(!($userID = crUsersToken::checkTokenValidity($token, "api"))){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }

        //only network needed for the unlink, not the whole key
        $socialKey = $oNetwork.'-0';
        if (cr_unlink_S($userID,$socialKey)){
            return ['STATUS_CODE' => STATUS_CODE_OK, 
            'DATA'  =>['STATUS'=>'SUCCESS', 
            'RESULT'=>['Message'=>'Account unLinked successfully']]];
        } else {
            return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA'  =>cr_api_get_error_result(cr_get_messages())];
        }
    }


    public function socialRegisterAction(){
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $oEmail = isset($request['oEmail']) ? trim($request['oEmail']) : null;
        $oID = isset($request['oID']) ? trim($request['oID']) : null;
        $oNetwork = isset($request['network']) ? trim($request['network']) : null;
        $action = isset($request['action']) ? trim($request['action']) : null;
        $linkOnly = isset($request['linkOnly'])?true:false;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }
        
        $sID = cr_add_S_Login($oNetwork,$oEmail,$oID);
        $socialKey = $oNetwork.'-'.$sID;

        if ($action == 'Link'){
            $uID = crUser::getIDbyEmail($oEmail);
            cr_update_S_Login($socialKey,$uID,$oEmail);

            if ($linkOnly){
                return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 
                'RESULT'=>['Message'=>'Account Linked successfully']]];
            }

        } else {
            $pwd = "";
    
            while (!cr_check_password_strength($pwd))
                $pwd = cr_generate_random_string();
        
            $accountData =[
                'firstName' =>$request['name'],
                'lastName'  =>'',
                'username'  =>$request['username'],
                'password'  =>$pwd,
                'password2' =>$pwd,
                'img_url'   =>$request['thumbnail'],
                'email'     =>$oEmail,
                'social-key'=>$socialKey
            ];

            $uID = crUser::createNewAccount($accountData);
        }
        
        if (!$uID)
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Registration failed')];

        //do login
        $userData = crUser::getUserBasicInfo($uID);

        //Remove Old Token
        crUsersToken::removeUserToken($uID, 'api');
                        
        //Create New Token
        $token = crUsersToken::createNewToken($uID, 'api');

        $thumbnail = CR_SITE_URL.'/'.crUser::getProfileIcon($uID);

        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 
                'RESULT'=>[
                    'token'     => $token, 
                    'email'     => $oEmail, 
                    'id'        => $uID, 
                    'thumbnail' =>$thumbnail, 
                    'name'      =>$userData['name']
                    ]]];

    }

    public function checkUsernameAction(){
        $request = $_POST;

        $token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $username = isset($request['username']) ? trim($request['username']) : null;

        if(!$token){
            return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
        }

        if($token != CR_PUBLIC_API_KEY){
            return ['STATUS_CODE' => STATUS_CODE_UNAUTHORIZED, 'DATA' => cr_api_get_error_result('Api token is not valid.')];
        }
        
        $av = crUser::checkUsernameDuplication($username)?"0":"1";

        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 'RESULT'=>['AVAILABILITY'=>$av,'USERNAME'=>$request['username']]]];
                
    }

    public function getCountriesAction(){
        global $db;

    	$query = $db->prepare('SELECT country_title FROM countries WHERE status=1');
    	$rows = $db->getResultsArray($query);
    	

        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 'RESULT'=>$rows]];
    }

    public function getTimezonesAction(){
        
        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 'RESULT'=>['(UTC-12:00) International Date Line West' => -12, 
                                                        '(UTC-11:00) Coordinated Universal Time-11' => -11, 
                                                        '(UTC-11:00) Samoa' => -11, 
                                                        '(UTC-10:00) Hawaii' => -10, 
                                                        '(UTC-09:00) Alaska' => -9, 
                                                        '(UTC-08:00) Baja California' => -8, 
                                                        '(UTC-08:00) Pacific Time (US & Canada)' => -8, 
                                                        '(UTC-07:00) Arizona' => -7, 
                                                        '(UTC-07:00) Chihuahua, La Paz, Mazatlan' => -7, 
                                                        '(UTC-07:00) Mountain Time(US & Canada)' => -7, 
                                                        '(UTC-06:00) Central America' => -6, 
                                                        '(UTC-06:00) Central Time(US & Canada)' => -6, 
                                                        '(UTC-06:00) Guadalajara, Mexico City, Monterrey' => -6, 
                                                        '(UTC-06:00) Saskatchewan' => -6, 
                                                        '(UTC-05:00) Bogota, Lima, Quito' => -6, 
                                                        '(UTC-05:00) Eastern Time(US & Canada)' => -6, 
                                                        '(UTC-05:00) Indiana(East)' => -6, 
                                                        '(UTC-04:30) Caracas' => -4.5, 
                                                        '(UTC-04:00) Asuncion' => -4, 
                                                        '(UTC-04:00) Atlantic Time(Canada)' => -4, 
                                                        '(UTC-04:00) Cuiaba' => -4, 
                                                        '(UTC-04:00) Georgetown, La Paz, Manaus, Sna Juan' => -4, 
                                                        '(UTC-04:00) Santiago' => -4, 
                                                        '(UTC-03:30) Newfoundland' => -3.5, 
                                                        '(UTC-03:00) Brasilia' => -3, 
                                                        '(UTC-03:00) Buenos Aires' => -3, 
                                                        '(UTC-03:00) Cayenne, Fortaleza' => -3, 
                                                        '(UTC-03:00) Greenland' => -3, 
                                                        '(UTC-03:00) Montevideo' => -3, 
                                                        '(UTC-02:00) Coordinated Universal Time-02' => -2, 
                                                        '(UTC-02:00) Mid-Atlantic' => -2, 
                                                        '(UTC-02:00) Azores' => -2, 
                                                        '(UTC-01:00) Cape Verde Is.' => -1, 
                                                        '(UTC) Casablanca' => 0, 
                                                        '(UTC) Coordinated Universal Time' => 0, 
                                                        '(UTC) Dublin, Edinburgh, Lisbon, London' => 0, 
                                                        '(UTC) Monrovia, Reykjavik' => 0, 
                                                        '(UTC+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna' => 1, 
                                                        '(UTC+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague' => 1, 
                                                        '(UTC+01:00) Brussels, Copenhagen, Madrid, Paris' => 1, 
                                                        '(UTC+01:00) Sarajevo, Skopje, Warsaw, Zagreb' => 1, 
                                                        '(UTC+01:00) West Central Africa' => 1, 
                                                        '(UTC+01:00) Windhoek' => 1, 
                                                        '(UTC+02:00) Amman' => 2, 
                                                        '(UTC+02:00) Athens, Bucharest, Istanbul' => 2, 
                                                        '(UTC+02:00) Beirut' => 2, 
                                                        '(UTC+02:00) Cairo' => 2, 
                                                        '(UTC+02:00) Damascus' => 2, 
                                                        '(UTC+02:00) Harare, Pretoria' => 2, 
                                                        '(UTC+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius' => 2, 
                                                        '(UTC+02:00) Jerusalem' => 2, 
                                                        '(UTC+02:00) Minsk' => 2, 
                                                        '(UTC+03:00) Baghdad' => 3, 
                                                        '(UTC+03:00) Kuwait, Riyadh' => 3, 
                                                        '(UTC+03:00) Moscow, ST. Petersburg, Volgograd' => 3, 
                                                        '(UTC+03:00) Nairobi' => 3, 
                                                        '(UTC+03:30) Tehran' => 3.5, 
                                                        '(UTC+04:00) Abu Dhabi, Muscat' => 4, 
                                                        '(UTC+04:00) Baku' => 4, 
                                                        '(UTC+04:00) Port Louis' => 4, 
                                                        '(UTC+04:00) Tbilisi' => 4, 
                                                        '(UTC+04:00) Yerevan' => 4, 
                                                        '(UTC+04:30) Kabul' => 4.5, 
                                                        '(UTC+05:00) Tashkent' => 5, 
                                                        '(UTC+05:30) Chennai, Kolkata, Mumbai, New Delhi' => 5.5, 
                                                        '(UTC+05:30) Sri Jayawardenepura' => 5.5, 
                                                        '(UTC+05:45) Kathmandu' => 5.75, 
                                                        '(UTC+06:00) Astana' => 6, 
                                                        '(UTC+06:00) Dhaka' => 6, 
                                                        '(UTC+06:00) Novosibirsk' => 6, 
                                                        '(UTC+06:30) Yangon (Rangoon)' => 6.5, 
                                                        '(UTC+07:00) Bangkok, Hanoi, Jakarta' => 7, 
                                                        '(UTC+07:00) Krasnoyarsk' => 7, 
                                                        '(UTC+08:00) Beijing, Chongqing, Hongkong' => 8, 
                                                        '(UTC+08:00) Irkutsk' => 8, 
                                                        '(UTC+08:00) Kuala Lumpur, Singapore' => 8, 
                                                        '(UTC+08:00) Perth' => 8, 
                                                        '(UTC+08:00) Taipei' => 8, 
                                                        '(UTC+08:00) Ulaanbaatar' => 8, 
                                                        '(UTC+08:00) Osaka, Sapporo, Tokyo' => 8, 
                                                        '(UTC+09:00) Seoul' => 9, 
                                                        '(UTC+09:00) Yakutsk' => 9, 
                                                        '(UTC+09:30) Adelaide' => 9.5, 
                                                        '(UTC+09:30) Darwin' => 9.5, 
                                                        '(UTC+10:00) Brisbane' => 10, 
                                                        '(UTC+10:00) Canberra, Melbourne, Sydney' => 10, 
                                                        '(UTC+10:00) Guam, Port Moresby' => 10, 
                                                        '(UTC+10:00) Hobart' => 10, 
                                                        '(UTC+10:00) Vladivostok' => 10, 
                                                        '(UTC+11:00) Magadan' => 11, 
                                                        '(UTC+11:00) Solomon Is., New Caledonia' => 11, 
                                                        '(UTC+12:00) Auckland, Wellington' => 12, 
                                                        '(UTC+12:00) Coordinated Universal Time+12' => 12, 
                                                        '(UTC+12:00) Fiji' => 12, 
                                                        '(UTC+13:00) Nukualofa' => 13] ]];
    }

    public function getMartialStatAction(){
        
        return ['STATUS_CODE' => STATUS_CODE_OK, 
                'DATA'  =>['STATUS'=>'SUCCESS', 'RESULT'=>['Single'=>1, 'In a Relationship'=>2, 'Married'=>3] ]];
    }


}
