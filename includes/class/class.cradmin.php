<?php

/**
 * User Class
 */
class crAdmin{

	const STATUS_USER_ACTIVE = 1; // User Active
	const STATUS_USER_BANNED = 0; // User Banned
	const STATUS_USER_DELETED = -1; // User Deleted

	/**
	 * Get User Basic Information by ID
	 *
	 * @param int $userID
	 * @return array
	 */
	public static function getAdmiInfo($adminID){
		global $db;
	
		$query = $db->prepare("SELECT * FROM " . TABLE_ADMINS . " where admin_id=%d", $adminID);
		$data = $db->getRow($query);
        
        if ($data) {
            unset($data['pwd']);
            return $data;
        }
        
		return false;
    }
    
    public static function logout(){
        unset($_SESSION['admin_id']);
        
        if(SITE_USING_SSL == true){
            setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN, true, true);
            setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN, true, true);
            setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN, true, true);
        }else{
            setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN);
            setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN);
            setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN);
        }
        
        cr_session_destroy();
        
        cr_redirect('/admin_panel.php');
    }
}