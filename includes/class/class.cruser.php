<?php

/**
 * User Class
 */
class crUser{

	const STATUS_USER_ACTIVE = 1; // User Active
	const STATUS_USER_BANNED = 0; // User Banned
	const STATUS_USER_DELETED = -1; // User Deleted

	public static function getUserEmail($userID){
		global $db;
		
		$query = $db->prepare('SELECT email FROM '.TABLE_USERS.' WHERE id=%d',$userID);
		$id = $db->getVar($query);
		
		return $id;
	}


	/**
	 * @param $user
	 * @return string
	 */
	public static function getProfileIcon($user){
		global $db;

		//Getting From DB
		if(gettype($user) != 'array'){
			$query = $db->prepare("SELECT thumbnail FROM " . TABLE_USERS . " WHERE id=%s", $user);
			$icon = $db->getVar($query);

			if(cr_not_null($icon)){
				return 'images/users/' . $user . '/'.cr_encrypt($user).'-resized/' . $icon;
			}else{
				return 'images/defaultProfileImage.png';
			}
		}else if(gettype($user) == 'array'){ //Getting From Array
			if(isset($user['thumbnail']) && cr_not_null($user['thumbnail'])){
				return 'images/users/' . $user['id'] . '/'.cr_encrypt($user['id']).'-resized/' . $user['thumbnail'];
			}else{
				return 'images/defaultProfileImage.png';
			}
		}
	}

/**
	 * @param $userID
	 * @return array|null
	 */
	public static function getUserData($userID){
		global $db;

		$query = $db->prepare("SELECT u.*, us.id FROM " . TABLE_USERS . " AS u LEFT JOIN " . TABLE_TWEETS . " AS us ON us.ownerID=u.id WHERE u.id=%d", $userID);
		
		$data = $db->getRow($query);

		if(!$data)
			return null;
		
		return $data;
	}



	/**
	 * Get User Basic Information by ID
	 *
	 * @param int $userID
	 * @return array
	 */
	public static function getUserBasicInfo($userID){
		global $db;
	
		$query = $db->prepare("SELECT id, name, username, thumbnail, timezone, martial_stat, gender, birthday, country, privacy, description FROM " . TABLE_USERS . " where id=%d", $userID);
		$data = $db->getRow($query);
		
		return $data;
	}

	/**
	 * Save User Basic Information
	 *
	 * @param Int $userID
	 * @param Array $data
	 * @return bool|null
	 */
	public static function saveUserBasicInfo($userID, $data){
		global $db;

		$rs = $db->updateFromArray(TABLE_USERS, ['name' => $data['name'], 'gender' => $data['gender'], 'location' => $data['location'], 'status' => $data['status'], 'id' => $userID]);

		return $rs;
	}

	public static function saveUserInfo($userID, $data){
		global $db;

		$rs = $db->updateFromArray(TABLE_USERS, $data, ['id'=>$userID]);

		return $rs;
	}

	/**
	 * Check if the email address exists or not
	 *
	 * @param mixed $email
	 * @param mixed $userID
	 * @return bool
	 */
	public static function checkEmailDuplication($email, $userID = null){
		global $db;

		if(!$userID)
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE `email`=%s", $email);else
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE `email`=%s AND id != %s", $email, $userID);

		$res = $db->getVar($query);

		return $res ? true : false;
	}


	/**
	 * Check if the username exists or not
	 *
	 * @param mixed $username
	 * @param mixed $userID
	 * @return bool
	 */
	public static function checkUsernameDuplication($username, $userID = null){
		global $db;

		$username = strtolower($username);

		if(!$userID)
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE LOWER(`username`)=%s", $username);else
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE LOWER(`username`)=%s AND id != %s", $username, $userID);

		$res = $db->getVar($query);

		return $res ? true : false;
	}

	/**
	 * Update User Fields
	 *
	 * @param Int $userID
	 * @param Array $data
	 * @return bool|null
	 */
	public static function updateUserFields($userID, $data){
		global $db;

		$res = $db->updateFromArray(TABLE_USERS, $data, ['id' => $userID]);

		return $res;
	}

	/**
	 * @param $userID
	 * @param $photoID
	 * @return bool
	 */
	public static function updateUserProfileByPhotoURL($userID, $photoURL){
		global $db;
		
		$fileParts = explode('/', $photoURL);
		
		$fileName= $fileParts[sizeof($fileParts)-1];
		$fileFolder= $fileParts[sizeof($fileParts)-2];
		$fileOwner= $fileParts[sizeof($fileParts)-3];
		
		if (!($fileFolder=='tmp')) {
			
		
			if (!($fileOwner==$userID))
				return false;
		
			$query = $db->prepare('SELECT id, is_profile FROM ' . TABLE_PHOTOS . ' WHERE ownerID=%d AND folder_token=%s AND name=%s ',  $userID, $fileFolder, $fileName);
			$row = $db->getRow($query);

			if(!$row){
				cr_add_message(MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
				return false;
			}
			
		}
		//if(!$row['is_profile']){
		//	cr_redirect("/photo_edit.php?photoID=" . $row['id'] . "&set_profile=1");
		//	exit;
		//}
		
		
		//REVISIT as row[image] may be long path.. the file should be copied to resized DIR
		
		$thumbFilename= '1.jpg';
		$thumbName = DIR_IMG.'/users/'.$userID.'/'.cr_encrypt($userID).'-resized/'.$thumbFilename;
		
		if (!is_dir(DIR_IMG.'/users/'.$userID.'/'.cr_encrypt($userID).'-resized')){
			if (!is_dir(DIR_IMG.'/users/'.$userID)){
				mkdir(DIR_IMG.'/users/'.$userID,0777);
			}
			mkdir(DIR_IMG.'/users/'.$userID.'/'.cr_encrypt($userID).'-resized',0777);
		}
		
		for ($x=2;file_exists($thumbName);$x++){			
			$thumbFilename = $x.'.jpg';
			$thumbName = DIR_IMG.'/users/'.$userID.'/'.cr_encrypt($userID).'-resized/'.$thumbFilename;
		}
		
		copy($photoURL,$thumbName);
		
		while (!file_exists($thumbName)) sleep(1);
		
		$query = $db->updateFromArray(TABLE_USERS, ['thumbnail' => $thumbFilename], ['id' => $userID]);

		cr_add_message(MSG_PROFILE_PHOTO_CHANGED, MSG_TYPE_SUCCESS);
		return true;
	}

	/**
	 * @param $userID
	 * @param $photoID
	 * @return bool
	 */
	public function updateUserProfileThumbnail($userID, $photoID){
		global $db;

		$query = $db->updateFromArray(TABLE_USERS, ['thumbnail' => $photoID], ['id' => $userID]);

		cr_add_message(MSG_PROFILE_PHOTO_CHANGED, MSG_TYPE_SUCCESS);

		return true;
	}

	/**
	 * Create New Account
	 *
	 * @param Array $data
	 * @return bool|int|null|string
	 */
	public static function createNewAccount($data){
		global $db;

		$errors = 0;
		$data = array_map('trim', $data);

		$data['name'] = $data['firstName'].' '. $data['lastName'];

		//Check Email Address
		if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $data['email'])){
			cr_add_message(MSG_INVALID_EMAIL, MSG_TYPE_ERROR);
			$errors .= 1;
		}
  
		//Check Username Duplication
		if(crUser::checkUsernameDuplication($data['username']) || !$data['username']){

			cr_add_message(MSG_USERNAME_EXIST, MSG_TYPE_ERROR);
			$errors .=1;
		}

		//Check Email Duplication
		if(crUser::checkEmailDuplication($data['email'])){

			//If this one is banned?
			if(crUser::getUserStatus($data['email']) == crUser::STATUS_USER_DELETED){
				cr_add_message(MSG_EMAIL_BANNED, MSG_TYPE_ERROR);
			}else{											
				if (isset($data['social-key'])){
					cr_add_message(MSG_EMAIL_EXIST.'. 
					If you want to link this account with your social network profile, then please login and link it through My Profile page',
					MSG_TYPE_ERROR );
				} else {
					cr_add_message(MSG_EMAIL_EXIST, MSG_TYPE_ERROR);
				}
			}
			$errors .=1;	
		}

		if(!$data['password'] || !$data['password2']){
			cr_add_message(MSG_EMPTY_PASSWORD, MSG_TYPE_ERROR);
			$errors .=1;
		}
		if($data['password'] != $data['password2']){
			cr_add_message(MSG_NOT_MATCH_PASSWORD, MSG_TYPE_ERROR);
			$errors .=1;
		}
		if(!cr_check_password_strength($data['password'])){
			cr_add_message(MSG_PASSWORD_STRENGTH_ERROR, MSG_TYPE_ERROR);
			$errors .=1;
		}

		if ($errors)
			return false;

		$password = cr_encrypt_password($data['password']);

		if (isset($data['social-key'])){
			$newId = $db->insertFromArray(TABLE_USERS, 
				['name' => $data['name'],
				 'username'=> $data['username'], 
				 'email' => $data['email'], 
				 'password' => $password, 
				 'thumbnail' => '', 
				 'dateJoined' => date('Y-m-d H:i:s'), 
				 'token' => '', 
				 'status'=>1,
				 'martial_stat'=>1]);
			
			//add userID to social details, no verification required.
			cr_update_S_Login($data['social-key'], $newId , $data['email']);

			//new user to follow me
			crFollowship::follow($newID,1);

			//create thumbnail for s_account photo
			$destFile = DIR_IMG_TMP.'/'.cr_encrypt($newId).'.jpg';
			copy($data['img_url'], $destFile);

			if (file_exists($destFile)){
				crUser::updateUserProfileByPhotoURL($newId, $destFile);
			}
			
		} else {
			//Create Token
			$token = md5(mt_rand(0, 99999) . time() . $data['email'] . mt_rand(0, 99999));

			//Create New Account
			$newId = $db->insertFromArray(TABLE_USERS, [
				'name' => $data['name'], 
				'username'=> $data['username'], 
				'email' => $data['email'], 
				'password' => $password, 
				'thumbnail' => '', 
				'dateJoined' => date('Y-m-d H:i:s'), 
				'token' => $token,
				'martial_stat'=>1]);
	
			if(!$newId){
				cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
				return false;
			}

			$url_protocol = "http://";
			if(SITE_USING_SSL == true)
				$url_protocol = "https://";

			//Send an email to new user with a validation link
			$link = $url_protocol . DOMAIN . "/register.php?action=verify&email=" . $data['email'] . "&token=" . $token;

			$title = DOMAIN." - Please verify your account.";
			$body = "<h4>Dear " . $data['name'] . "</h4><br /><br />" . "Welecome to ".DOMAIN."!<br /> Thanks for your registration. <br /><br />" . "To complete your registration, please verify your email address by clicking the below link:. <br /><a href='" . $link . "'>Verify Account</a><br /><br />" . DOMAIN;

			cr_sendmail($data['email'], $data['name'], $title, $body);

		}

		return $newId;
	}

	/**
	 * @param $email
	 * @param $token
	 * @return bool
	 */
	public static function verifyAccount($email, $token){
		global $db;

		$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE token=%s AND email=%s AND status=0", $token, $email);
		$userID = $db->getVar($query);
		if(!$userID){
			cr_add_message(MSG_INVALID_TOKEN, MSG_TYPE_ERROR);
			return false;
		}

		//Verify links
		$query = $db->prepare("UPDATE " . TABLE_USERS . " SET status=1, token='' WHERE id=%d", $userID);
		$db->query($query);
		cr_add_message(MSG_ACCOUNT_VERIFIED, MSG_TYPE_SUCCESS);

		return true;
	}

	/**
	 * Create new password and send it to user
	 *
	 * @param String $email
	 * @return bool|void
	 */
	public static function resetPassword($email, $redirect=true){
		global $db;

		$email = trim($email);
		if(!$email){
			if ($redirect){
				cr_redirect('/register.php?forgotpwd=1', MSG_EMPTY_EMAIL, MSG_TYPE_ERROR);
			} else {
				cr_add_message(MSG_EMPTY_EMAIL,MSG_TYPE_ERROR);
			}
			return false;
		}

		//Check Email Address
		if(!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)){
			if ($redirect){
				cr_redirect('/register.php?forgotpwd=1', MSG_INVALID_EMAIL, MSG_TYPE_ERROR);
			} else {
				cr_add_message(MSG_INVALID_EMAIL, MSG_TYPE_ERROR);
			}
			return false;
		}

		$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE email=%s", $email);
		$userID = $db->getVar($query);

		if(!$userID){
			if ($redirect){
				cr_redirect('/register.php?forgotpwd=1', MSG_EMAIL_NOT_FOUND, MSG_TYPE_ERROR);
			} else {
				cr_add_message(MSG_EMAIL_NOT_FOUND, MSG_TYPE_ERROR);
			}
			
			return false;
		}

		$data = crUser::getUserBasicInfo($userID);

		//Remove Old Token
		crUsersToken::removeUserToken($userID, 'password');

		//Create New Token
		$token = crUsersToken::createNewToken($userID, 'password');

		$linkPart = SITE_USING_SSL ? "https://" : "http://";

		$link = $linkPart . DOMAIN . "/reset_password.php?token=" . $token;

		//Send an email to user with the link
		$title = "Reset your password.";
		$body = "<h4>Dear " . $data['name'] . "</h4><br /><br />" . "Please reset your password by using the below link:<br /><a href='" . $link . "'>Reset Password</a><br /><br />".SITE_NAME;
		
		cr_sendmail($email, $data['name'], $title, $body);
		
		if($redirect)
			cr_redirect('/register.php', MSG_RESET_PASSWORD_EMAIL_SENT, MSG_TYPE_SUCCESS);

		return true;
	}

	/**
	 * Check UserID is correct or not
	 *
	 * @param Int $userID
	 * @param Boolean $onlyActived
	 * @return bool
	 */
	public static function checkUserID($userID, $onlyActived = true){
		global $db;

		if($onlyActived)
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE id=%d AND `status`=1", $userID);else
			$query = $db->prepare("SELECT id FROM " . TABLE_USERS . " WHERE id=%d", $userID);

		$id = $db->getVar($query);

		return cr_not_null($id) ? true : false;
	}

	/**
	 * Get a value from user attributes
	 *
	 * @param Int $userID
	 * @param String $key
	 * @param Mixed $default
	 * @return Mixed
	 */
	public function getAttribute($userID, $key, $default = null){
		global $db;

		$query = $db->query("SELECT attributes FROM " . TABLE_USERS . " WHERE id=%d", $userID);
		$attr = $db->getVar($query);

		if(!$attr)
			return $default;

		$attr = unserialize($attr);
		if(!isset($attr[$key]))
			return $default;

		return $attr[$key];
	}

	/**
	 * Save Attribute
	 *
	 * @param mixed $userID
	 * @param mixed $key
	 * @param mixed $value
	 * @return bool|null
	 */
	public function setAttribute($userID, $key, $value){
		global $db;

		$query = $db->query("SELECT attributes FROM " . TABLE_USERS . " WHERE id=%d", $userID);
		$attr = $db->getVar($query);

		if(!$attr)
			$attr = [];else
			$attr = unserialize($attr);

		$attr[$key] = $value;

		//Save Attribute
		return $db->update('UPDATE ' . TABLE_USERS . ' SET attributes="' . serialize($attr) . '" WHERE id=' . $userID);

	}

	/**
	 * Remove Account
	 */
	public static function deleteUserAccount($userID){
		global $db;

		$userID = intval($userID);

		//Don't delete user from the database, just update the user's status
		$db->query("DELETE FROM " . TABLE_USERS_TOKEN . " WHERE userID=" . $userID);
		//REVISIT to decide either to deactivate or delete 
		$db->query("DROP * FROM " . TABLE_USERS . " WHERE id=" . $userID);

		$content = "Your " . SITE_NAME . " account has been deleted.";

			//Send Email to User
		cr_sendmail($userInfo['email'], $userInfo['name'], SITE_NAME . ' Account has been Deleted', $content);
		
	}

	/**
	 * Search Users
	 *
	 * @param Int $term
	 * @param array $exclude
	 * @return Array
	 * @internal param Int $userID
	 */
	public static function searchUsers($term, $exclude = []){
		global $db;

		if(cr_not_null($exclude) && !is_array($exclude))
			$exclude = [$exclude];

		if(cr_not_null($exclude))
			$query = "SELECT DISTINCT(id), name AS fullName FROM " . TABLE_USERS . " WHERE status = Active AND id NOT IN(" . implode(", ", $db->escapeInput($exclude)) . ") AND name LIKE '%" . $db->escapeInput($term) . "%') ORDER BY fullName";else
			$query = "SELECT DISTINCT(id), name AS fullName FROM " . TABLE_USERS . " WHERE status = Active AND name LIKE '%" . $db->escapeInput($term) . "%') ORDER BY fullName";

		$rows = $db->getResultsArray($query);

		return $rows;
	}

	/**
	 * Get User Forum Settings
	 *
	 * @param Int $userID
	 * @return Array
	 */
	 //REVISIT to set notification settings
	public static function getUserNotificationSettings($userID){
		global $db, $SITE_GLOBALS;

		$query = $db->prepare("SELECT * FROM " . TABLE_USERS_NOTIFY_SETTINGS . " WHERE id=%s", $userID);
		$row = $db->getRow($query);

		if(!$row)
			$row = [];

		$row = array_merge($SITE_GLOBALS['notify_settings'], $row);

		return $row;
	}

	/**
	 * Save User Notification Settings
	 *
	 * @param mixed $userID
	 * @param mixed $data
	 * @return bool|null|string
	 */
	public static function saveUserNotificationSettings($userID, $data){
		global $db;

		$userID = intval($userID);

		$paramData = ['optOfferReceived' => isset($data['optOfferReceived']) ? 1 : 0, 'optOfferAccepted' => isset($data['optOfferAccepted']) ? 1 : 0, 'optOfferDeclined' => isset($data['optOfferDeclined']) ? 1 : 0, 'optFeedbackReceived' => isset($data['optFeedbackReceived']) ? 1 : 0, 'optProductSoldOnShop' => isset($data['optProductSoldOnShop']) ? 1 : 0,];

		$res = $db->updateFromArray(TABLE_TRADE_USERS, $paramData, ['userID' => $userID]);

		$notifyData = ['userID' => $userID, 'notifyRepliedToMyTopic' => isset($data['notifyRepliedToMyTopic']) ? 1 : 0, 'notifyRepliedToMyReply' => isset($data['notifyRepliedToMyReply']) ? 1 : 0, 'notifyMyPostApproved' => isset($data['notifyMyPostApproved']) ? 1 : 0];

		//Check if the forum notification exists or not
		$query = $db->prepare("SELECT settingID FROM " . TABLE_USERS_NOTIFY_SETTINGS . " WHERE userID=%d", $userID);
		$sID = $db->getVar($query);

		if(!$sID)
			$fr = $db->insertFromArray(TABLE_USERS_NOTIFY_SETTINGS, $notifyData);
		else
			$fr = $db->updateFromArray(TABLE_USERS_NOTIFY_SETTINGS, $notifyData, ['settingID' => $sID]);

		if($fr && $res)
			return true;else
			return $db->getLastError();

	}

	/**
	 * User Status By Email / UserID
	 *
	 * @param mixed $param
	 */
	public function getUserStatus($param){

		global $db;

		$query = '';
		if(is_numeric($param)){
			$query = $db->prepare("SELECT status FROM " . TABLE_USERS . " WHERE id=%d", $param);
		}else{
			$query = $db->prepare("SELECT status FROM " . TABLE_USERS . " WHERE email='%s'", $param);
		}

		$data = $db->getRow($query);

		return $data['status'];
	}

	/**
	 * @return bool
	 */
	public static function checkDailyUserLimit(){
		global $db;

		$date = date('Y-m-d');

		$query = $db->prepare("SELECT count(*) FROM " . TABLE_USERS . " WHERE DATE(`dateJoined`) = %s AND `ip_addr` = %s", $date, $_SERVER['REMOTE_ADDR']);
		$counts = $db->getVar($query);

		return $counts < USER_DAILY_LIMIT_ACCOUNTS;
	}

	/**
	* 
	* @return bool
	*/
	public static function checkPassword($PWD, $UserID){
		global $db;
		
		$PWD = cr_encrypt_password($PWD);
		$query = $db->prepare("SELECT id FROM ". TABLE_USERS . "WHERE id=%d AND password=%s", $UserID,$PWD);
		$result= $db->query($query);
		
		if ($result)
			return true; else
			return false;
	}

	/**
	* function getIDbySID
	* get UserID from Social_login table ID
	* return int ID
	*/
	public static function getIDbySID($sID){
		global $db;

		$query = $db->prepare ('SELECT uID FROM '.TABLE_USERS_S_LOGIN.' WHERE sID=%d',$sID);
		$uID = $db->getVar($query);
		
		return $uID;
	}

	//for API, to verify user is registered
	public static function getUserIDbySocialData($OauthType, $OauthEmail, $OauthID){
		global $db;
	
		$query = $db->prepare ('SELECT uID FROM '.TABLE_USERS_S_LOGIN.' 
		WHERE OauthType=%s AND OauthID=%s AND OauthEmail=%s AND uID>0',$OauthType,$OauthID, $OauthEmail);
	
		$id = $db->getVar($query);
	
		if (!$id)
			return false;
	
		return $id;
	}


	//for API, to verify user is registered
	public static function getIDbyEmail($email){
		global $db;
	
		$query = $db->prepare ('SELECT id FROM '.TABLE_USERS.' WHERE email=%s',$email);
	
		$id = $db->getVar($query);
	
		if (!$id)
			return false;
	
		return $id;
	}

}