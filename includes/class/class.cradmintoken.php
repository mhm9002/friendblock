<?php

class crAdminToken {

    /**
     * Remove User Token
     *
     * @param Int    $userID
     * @param String $tokenType = password, ...
     */
    public static function removeAdminToken($adminID, $tokenType){
        global $db;

        $query = $db->prepare("DELETE FROM " . TABLE_ADMINS_TOKEN . " WHERE admin_id=%s AND tokenType=%s", $adminID, $tokenType);
        $db->query($query);

        return;
    }

    /**
     * @param      $userID
     * @param      $tokenType
     * @param null $token
     * @return null|string
     */
    public static function createNewToken($adminID, $tokenType, $token = null){
        global $db;

        $info = crAdmin::getAdmiInfo($adminID);

        if(!$token){
            $token = md5(mt_rand(0, 99999) . time() . mt_rand(0, 99999) . $info['username'] . mt_rand(0, 99999));
        }

        $newID = $db->insertFromArray(TABLE_ADMINS_TOKEN, ['admin_id' => $userID, 'adminToken' => $token, 'tokenDate' => time(), 'tokenType' => $tokenType]);

        return $token;
    }

    /**
     * @param $token
     * @param $tokenType
     * @return bool|one
     */
    public static function checkTokenValidity($token, $tokenType){
        global $db;

        if($tokenType == 'password'){
            $query = $db->prepare('SELECT admin_id FROM ' . TABLE_ADMINS_TOKEN . ' WHERE adminToken=%s AND tokenType=%s AND tokenDate > %s', $token, $tokenType, time() - PASSWORD_TOKEN_EXPIRY_DATE * 60 * 60 * 24);
        }else{
            $query = $db->prepare('SELECT admin_id FROM ' . TABLE_ADMINS_TOKEN . ' WHERE adminToken=%s AND tokenType=%s', $token, $tokenType);
        }
        $adminID = $db->getVar($query);
        if(!$adminID){
            return false;
        }
        return $adminID;

        return false;
    }
    
}