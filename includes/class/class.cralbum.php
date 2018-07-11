<?php

/**
 * Manage Album
 */
class crAlbum {

    /**
     * Getting Album Photos
     *
     * @param mixed $albumID
     * @param mixed $limit
     * @return Indexed
     */
    public static function getPhotos($albumID, $limit = null){
        global $db;

        $query = $db->prepare("SELECT p.* FROM " . TABLE_TWEETS . " AS p LEFT JOIN " . TABLE_ALBUMS_PHOTOS . " AS op ON op.post_id=p.id WHERE op.album_id=%d", $albumID);

        $rows = $db->getResultsArray($query, 'postID');

        return $rows;
    }

	/**
     * Getting Photo by ID
     *
     * @param mixed $photoID
     * @return Photo row
     */
    public static function getPhotoByID($photoID){
        global $db;

        $query = $db->prepare("SELECT * FROM " . TABLE_PHOTOS . " WHERE id=%d", $photoID);

        $row = $db->getRow($query);

        return $row;

    }

    /**
     * Getting Album Photos
     *
     * @param mixed $albumID
     * @param mixed $limit
     * @return Indexed
     */
    public static function getAlbumPhotos($albumID, $lastDate = null){
        global $db;

        $limit_query = ' LIMIT ' . crTweet::$images_per_page;

        $query = $db->prepare("SELECT p.* FROM " . TABLE_PHOTOS . " AS p 
        	LEFT JOIN " . TABLE_ALBUMS_PHOTOS . " AS op ON op.post_id=p.id  
            WHERE op.album_id=%d".($lastDate != null ? ' AND p.dateUploaded < "' . $lastDate . '"' : '').
            " ORDER BY p.dateUploaded DESC".$limit_query, $albumID);

        $rows = $db->getResultsArray($query, 'photo');

        return $rows;
    }

    /**
     * Create New Album
     *
     * @param Int    $userID
     * @param String $title
     * @return bool|int|null|string
     */
    public static function createAlbum($userID, $title, $visibility){
        global $db;

        $now = date('Y-m-d H:i:s');
        
        if ($existingAlbum = crAlbum::getAlbumByName($title, $userID))
        	return $existingAlbum;
        
        $newId = $db->insertFromArray(TABLE_ALBUMS, ['owner' => $userID, 'name' => $title, 'created_date' => $now, 'visibility' => $visibility]);

        if(!$newId) //Error
        {
            cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
            return false;
        }else{  //Success
            cr_add_message(MSG_NEW_ALBUM_CREATED, MSG_TYPE_SUCCESS);
            return $newId;
        }
    }

    /**
     * Getting User Albums
     *
     * @param Int $userID
     * @return Indexed
     */
    public static function getAlbumsByUserId($userID){
        global $db;

        $query = $db->prepare("SELECT a.*, count(ap.id) AS photos FROM " . TABLE_ALBUMS . " AS a LEFT JOIN " . TABLE_ALBUMS_PHOTOS . " AS ap ON a.albumID=ap.album_id WHERE OWNER=%s GROUP BY a.albumID ORDER BY `name`", $userID);
        $albums = $db->getResultsArray($query, 'albumID');

        return $albums;
    }

    /**
     * Check that $userID is a owner of $albumID
     *
     * @param int $albumID
     * @param int $userID
     * @return bool
     */
    public static function checkAlbumOwner($albumID, $userID){
        global $db;

        $query = $db->prepare("SELECT albumID FROM " . TABLE_ALBUMS . " WHERE OWNER=%s AND albumID= %s", $userID, $albumID);
        $rs = $db->getVar($query);

        return !$rs ? false : true;
    }

    /**
     * Getting Photo Albums
     *
     * @param Int $photoID
     * @return Indexed
     */
    public static function getAlbumsByPostId($photoID){
        global $db;

        $query = $db->prepare("SELECT a.* FROM " . TABLE_ALBUMS_PHOTOS . " AS ap LEFT JOIN " . TABLE_ALBUMS . " AS a ON a.albumID=ap.album_id WHERE ap.post_id=%s ORDER BY `name`", $photoID);
        $albums = $db->getResultsArray($query, 'albumID');

        return $albums;
    }

    /**
     * Add photo to album
     *
     * @param mixed $albumID
     * @param mixed $photoID
     * @return int|null|string
     */
    public static function addPhotoToAlbum($albumID, $photoID){
        global $db;

        //Remove Old Entries
        $query = $db->prepare("DELETE FROM " . TABLE_ALBUMS_PHOTOS . " WHERE post_id=%s", $photoID);
        $db->query($query);

        //Insert New Entry
        $query = $db->prepare("INSERT INTO " . TABLE_ALBUMS_PHOTOS . "(album_id, post_id)VALUES(%s, %s)", $albumID, $photoID);
        $newId = $db->insert($query);

        return $newId;
    }

    /**
     * Remove photo from album
     *
     * @param mixed $albumID
     * @param mixed $photoID
     */
    public static function removePhotoFromAlbum($albumID, $photoID){
        global $db;

        //Remove Old Entries
        $query = $db->prepare("DELETE FROM " . TABLE_ALBUMS_PHOTOS . " WHERE album_id=%s AND post_id=%s", $albumID, $photoID);
        $db->query($query);

        return $newId;
    }

    /**
     * Remove Album
     *
     * @param mixed $albumID
     * @param mixed $userID
     * @return bool
     */
    public static function deleteAlbum($albumID, $userID){
        global $db;

        if(BuckysAlbum::checkAlbumOwner($albumID, $userID)){
            //Remove Album
            $query = $db->prepare("DELETE FROM " . TABLE_ALBUMS . " WHERE albumID=%s AND OWNER=%s", $albumID, $userID);
            $db->query($query);
            //Remove Assigned Photos
            $query = $db->prepare("DELETE FROM " . TABLE_ALBUMS_PHOTOS . " WHERE albumID=%s", $albumID);
            $db->query($query);
            return true;
        }
        return false;
    }

    /**
     * Get Album Detail
     *
     * @param int $albumID
     * @return array
     */
    public static function getAlbum($albumID){
        global $db;

        $query = $db->prepare("SELECT a.*, u.name FROM " . TABLE_ALBUMS . " AS a LEFT JOIN " . TABLE_USERS . " AS u ON u.id=a.owner WHERE a.albumID=%s", $albumID);
        $row = $db->getRow($query);

        return $row;
    }

    /**
     * @param $albumID
     * @param $title
     * @param $visibility
     * @param $photos
     */
    public static function updateAlbum($albumID, $title, $visibility, $photos){
        global $db;

        //Update Album Title
        $query = $db->prepare("UPDATE " . TABLE_ALBUMS . " SET name=%s, visibility=%s WHERE albumID=%s", $title, $visibility, $albumID);
        $db->query($query);

        return;
    }
    
    public static function getAlbumByName($title, $userID){
		global $db;
		
		$query = $db->prepare("SELECT albumID FROM ".TABLE_ALBUMS. " WHERE owner=%d AND name=%s",$userID,$title);
		$albumID = $db->getVar($query);
		
		if ($albumID)
			return $albumID;
			
		return FALSE;	
	}
  
    public static function deletePhoto($userID,$pID,$removeFile=false){
        global $db;

        $images = explode(';',$pID);

        foreach ($images as $image){
            if (!cr_not_null($image) || intval($image)<1)
                continue;

            if ($removeFile){
                
                $img = crAlbum::getPhotoByID($image);

                if (!$img)
                    break;

                $filename   = DIR_IMG.'/users/'.$userID.'/'.$img['folder_token'].'/'.$img['name'];
                $thumb      = DIR_IMG.'/users/'.$userID.'/'.$img['folder_token'].'/thumb-'.$img['name'];

                if (file_exists($filename))
                    unlink($filename);
                
                if (file_exists($thumb))
                    unlink($thumb);
            }
            
            $db->query('DELETE FROM '.TABLE_PHOTOS. ' WHERE id='.$image);
        }

        return true;
    }

    public static function savePhoto($userID,$folder,$filename,$title){
        global $db;

        $now = date('Y-m-d H:i:s');
        $nPhoto = $db->insertFromArray(TABLE_PHOTOS,['ownerID' => $userID, 'name' => $filename, 'folder_token' => $folder,'dateUploaded' => $now,'title' => $title]);

        return $nPhoto;
    }
}