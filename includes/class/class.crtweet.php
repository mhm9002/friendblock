<?php

/**
 * Post Manage Class
 */
class crTweet{

	public static $tweets_per_page = 5;
	public static $images_per_page = 30;
	public static $IMAGES_PER_PAGE_FOR_MANAGE_PHOTOS_PAGE = 15;
	public static $COUNT_PER_PAGE_TEXT = 30;
	public static $COUNT_PER_PAGE_IMAGE = 60;
	public static $COUNT_PER_PAGE_VIDEO = 40;

	// When this post doesn't belong to a page, then the post's page ID will be 0;
	//const INDEPENDENT_TWEET_PAGE_ID = 0;

	/**
	 * Getting all posts that were published by the user's friends
	 *
	 * @param mixed $userID
	 * @param null $lastDate
	 * @return Array
	 */
	public static function getUserTweetsStream($userID, $lastDate = null, $startDate = null){
		global $db;

		$userID = intval($userID);

		//Page Limit Query
		$limit_query = ' LIMIT ' . crTweet::$tweets_per_page;

		$query = $db->prepare("SELECT p.*,s.* FROM " . TABLE_TWEETS . " AS p
			LEFT JOIN ".TABLE_SCRAPE." AS s on s.tweetID=p.id 
             WHERE (
                p.ownerID = %d 
                OR
                p.ownerID IN (SELECT followedID FROM " . TABLE_FOLLOWSHIP . " WHERE followerID=%d AND status=1)
			 ) ".  ($lastDate != null ? ' AND p.date < "' . $lastDate . '"' : '')  
			 .  ($startDate != null ? ' AND p.date > "' . $startDate . '"' : '')  . 
			 " ORDER BY p.date DESC " . $limit_query, $userID, $userID);

		//var_dump($query);
		$rows = $db->getResultsArray($query);

		$results=[];
		
		foreach($rows as $idx){

			$idx = crTweet::GetLikesCommentsRetweets($idx,$userID);
			
			array_push($results,$idx);
			
		}
		//var_dump($results);
		return $results;
	}
	
	/**
	 * Get Posts Or Post
	 *
	 * @param integer $userID : Poster
	 * @param integer $loggedUserID : viewer
	 * @param int $pageID
	 * @param boolean $canViewPrivate
	 * @param integer $postID
	 * @param null $lastDate
	 * @param string $postType
	 * @return Indexed
	 */
	public static function getTweetsByUserID($userID, $loggedUserID = null, $tweetID = null, $lastDate = null, $tweetType = 'user'){
		global $db;

		$limit = crTweet::$tweets_per_page;

		//Page Limit Query
		$limit_query = ' LIMIT ' . $limit;

		if(!cr_not_null($loggedUserID)) return; 
		
		//Get All posts that were posted by $userID
		$query = $db->prepare('SELECT * FROM '.TABLE_TWEETS.' AS t '.
			'LEFT JOIN '.TABLE_SCRAPE.' AS s ON t.id=s.tweetID WHERE t.ownerID=%d '. 
			($lastDate != null ? 'AND t.date < "' . $lastDate . '"' : '').
			' ORDER BY t.date DESC ' . $limit_query , $userID);
		
		$rows = $db->getResultsArray($query);
		$results=[];
		
		foreach($rows as $idx){
			$idx = crTweet::GetLikesCommentsRetweets($idx,$loggedUserID);
			array_push($results,$idx);	
		}
		
		return $results;
	}
	
	public static function GetLikesCommentsRetweets($tweetData, $loggedUserID) {
			global $db;
			
			//Getting Comments
			$query = $db->prepare("SELECT * FROM " . TABLE_COMMENTS . " WHERE tweetID=%d", $tweetData['id']);
			$comments = $db->getVar($query);
			$tweetData['comments'] = $comments;
			
			//Getting Likes
			$query = $db->prepare("SELECT * FROM " . TABLE_LIKES . " WHERE tweetID=%d", $tweetData['id']);
			$likes = $db->getVar($query);
			$tweetData['likes'] = $likes;
			
			//Getting Retweets
			$query = $db->prepare("SELECT * FROM " . TABLE_RETWEETS . " WHERE tweetID=%d" , $tweetData['id']);
			$retweets = $db->getVar($query);
			$tweetData['retweets'] = $retweets;
			
			$query= $db->prepare("SELECT likerID FROM ".TABLE_LIKES." WHERE tweetID=%d AND likerID=%d", $tweetData['id'], $loggedUserID);
			$liked = $db->getVar($query);
			$tweetData['likeID'] = cr_not_null($liked)? 1 : 0;
			
			//get tags
			$tweetData['tags'] = crTweet::getTweetTags($tweetData['id']);

			return $tweetData;
	}
	
	public static function getTweetOwner($tweetID){
		global $db;
		
		$query = $db->prepare('SELECT ownerID FROM '.TABLE_TWEETS.' WHERE id=%d',$tweetID);
		$id = $db->getVar($query);
		
		return $id;
	}

	/**
	 * Get photos
	 *
	 * @param int $userID
	 * @param int $loggedUserID
	 * @param int $pageID
	 * @param boolean $canViewPrivate
	 * @param int $postID
	 * @param int $albumID
	 * @param int $limit
	 * @param string $lastDate
	 * @return Indexed
	 * 
	 */
	 
	public static function getPhotosByUserID($userID, $loggedUserID = null, $tweetID = null, $albumID = null, $limit = null, $lastDate = null){
		global $db;

		$userID = intval($userID);

		//Getting Page Parameter
		if(isset($_GET['page']) && cr_not_null($_GET['page']))
			$page = intval($_GET['page']);else
			$page = 1;

		//Page Limit Query
		if($limit)
			$limit_query = ' LIMIT ' . (($page - 1) * $limit) . ", " . $limit;

		if(cr_not_null($loggedUserID)){
			//Get All posts that were posted by $userID
			$query = 'SELECT p.*, u.name AS posterFullName, pl.likeID, pa.album_id FROM ' . TABLE_TWEETS . ' AS p
                                LEFT JOIN ' . TABLE_USERS . ' AS u ON p.ownerID = u.id
                                LEFT JOIN ' . TABLE_ALBUMS_PHOTOS . ' AS pa ON pa.post_id = p.id
                                LEFT JOIN ' . TABLE_LIKES . ' AS pl ON pl.tweetID = p.id AND pl.likerID = ' . $userID . '
                                WHERE p.ownerID= ' . $userID;
		}else{
			//Get Only Public Posts
			$query = 'SELECT p.*, u.name AS posterFullName, pl.likeID, pa.album_id FROM ' . TABLE_TWEETS . ' AS p
                                LEFT JOIN ' . TABLE_USERS . ' AS u ON p.ownerID = u.id
                                LEFT JOIN ' . TABLE_ALBUMS_PHOTOS . ' AS pa ON pa.post_id = p.id
                                LEFT JOIN ' . TABLE_POSTS_LIKES . ' AS pl ON pl.tweetID = p.id AND pl.;likerID = ' . $userID . '
                                WHERE p.ownerID= ' . $userID;
		}
		$query .= ' AND p.type="image" ';

		//If postID is set, get only one post
		if($tweetID != null)
			$query .= $db->prepare(' AND p.id=%d', $postID);

		//AlbumID Query
		if($albumID != null){
			$aPhotos = crAlbum::getPhotos($albumID);

			$apIds = [0];
			foreach($aPhotos as $a)
				$apIds[] = $a['postID'];
			$query .= ' AND p.id in (' . implode(', ', $apIds) . ')';
		}

		if($lastDate != null){
			$lastDate = date('Y-m-d H:i:s', strtotime($lastDate));
			$query .= ' AND p.date < "' . $lastDate . '"';
		}

		$query .= ' ORDER BY p.date DESC ' . $limit_query;
		$rows = $db->getResultsArray($query);

		return $rows;
	}

	/**
	 * Get Number of photos
	 *
	 * @param integer $profileID
	 * @param integer $pageID
	 * @param integer $albumID
	 * @return one
	*/
	 	 
	public static function getNumberOfPhotosByUserID($profileID, $albumID = null){
		global $db;

		$userID = cr_is_logged_in();

		$followed = crFollowship::isFollowed($userID,$profileID);

		if(cr_not_null($userID['id']) && ($userID['id'] == $profileID || $followed['status']==1)){
			$query = $db->prepare("SELECT count(DISTINCT(p.id)) FROM " . TABLE_TWEETS . " AS p LEFT JOIN " . TABLE_ALBUMS_PHOTOS . " AS pa ON pa.post_id = p.id WHERE p.type='image' AND p.ownerID=%d", $profileID);
		}else{
			$query = $db->prepare("SELECT count(DISTINCT(p.id)) FROM " . TABLE_TWEETS . " AS p LEFT JOIN " . TABLE_ALBUMS_PHOTOS . " AS pa ON pa.post_id = p.id WHERE p.type='image' AND p.ownerID=%d", $profileID);
		}

		if(cr_not_null($albumID))
			$query .= $db->prepare(" AND pa.album_id=%d", $albumID);

		$count = $db->getVar($query);

		return $count;
	}

	/**
	 * Check that the postID and PosterID are correct
	 *
	 * @param Int $postID
	 * @param Int $posterID
	 * @return bool
	 */
	public static function checkTweetID($tweetID, $ownerID = null){
		global $db;

		if($ownerID == null)
			$query = $db->prepare("SELECT id FROM " . TABLE_TWEETS . " WHERE id=%s", $tweetID);else
			$query = $db->prepare("SELECT id FROM " . TABLE_TWEETS . " WHERE id=%s AND ownerID=%s", $tweetID, $ownerID);

		$rs = $db->getVar($query);

		return $rs ? true : false;
	}

	/**
	 * Save Tweet
	 *
	 * @param $userID
	 * @param array $data
	 * @return bool|int|null|string
	 */
	public static function saveTweet($userID, $data){
		global $db;

		crAlbum::deletePhoto($userID,$data['rem1']);
		crAlbum::deletePhoto($userID,$data['rem2']);

		$now = date('Y-m-d H:i:s');
		$type = isset($data['type']) ? $data['type'] : 'text';

		if(!in_array($type, ['text', 'image', 'video']))
			$type = 'text';
		
		// Strip tags, and change url to clickable
		$data['content'] = strip_tags($data['content']);
	
		switch ($type) {			
			case 'text':
				if(trim($data['content']) == ''){
					cr_add_message(MSG_CONTENT_IS_EMPTY, MSG_TYPE_ERROR);
					return false;
				}
				$newId = $db->insertFromArray(TABLE_TWEETS, ['ownerID' => $userID, 'content' => $data['content'], 'type' => $type, 'image' =>'', 'youtube_url' => '', 'date' => $now, 'rtl' => $data['rtl']]);
				if(!$newId){
					cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
					return false;
				}
							
				break;
			
			case 'video':
				//Check Youtube URL is Valid or Not
				$validation = cr_validate_youtube_url($data['youtube_url']);
				
				if(!$validation){
					cr_add_message(MSG_INVALID_YOUTUBE_URL, MSG_TYPE_ERROR);
					return false;
				}

				$newId = $db->insertFromArray(TABLE_TWEETS, ['ownerID' => $userID, 'content' => $data['content'], 'type' => $type, 'image' =>'', 'youtube_url' => $data['youtube_url'], 'date' => $now, 'rtl' => $data['rtl']]);
				if(!$newId){
					cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
					return false;
				}
				
				break;
			case 'image':
	
				if (!isset($data['images']) || !cr_not_null($data['images']) ){
					cr_add_message(MSG_FILE_UPLOAD_ERROR, MSG_TYPE_ERROR);
					return false;
				}

				//move photos from temp folder to user folder and assiging them to the album
				cr_move_images($userID,$data['folder_token'],$data['images']);
				
				$newId = $db->insertFromArray(TABLE_TWEETS, ['ownerID' => $userID, 'content' => $data['content'], 'type' => 'image', 'date' => $now, 'image' => $data['images'], 'rtl' => $data['rtl']]);
				
				if(!$newId){
					cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
					return false;
				}
				cr_add_message(MSG_PHOTO_UPLOADED_SUCCESSFULLY,MSG_TYPE_SUCCESS);
				break;
		}

		if (isset($data['metaURL']))
			$metaID= $db->insertFromArray(TABLE_SCRAPE, ['tweetID' => $newId,'metaURL'=> $data['metaURL'], 'metaTitle'=> $data['metaTitle'],'metaDescription'=> $data['metaDescription'],'metaImage'=> $data['metaImage']]);

		$saved=[];
		
		while (!isset($saved['content'])){
			$saved = crTweet::getTweetById($newId);
		}

		switch($type){
			case 'image':
				// No message
				break;
			case 'video':
				cr_add_message(MSG_NEW_VIDEO_CREATED, MSG_TYPE_SUCCESS);
				break;
			case 'text':
				cr_add_message(MSG_NEW_POST_CREATED, MSG_TYPE_SUCCESS);
				break;
			default:
				break;
		}

		crTweet::addMentions($newId, $data['content']);
		crTweet::addHashtags($newId, $data['content']);

		return $newId;
	}


	/**
	 * Update Tweet
	 *
	 * @param int $userID
	 * @param int $tID
	 * @param array $data
	 * @return bool|int|null|string
	 */
	public static function updateTweet($userID, $tID, $data){
		global $db;

		$now = date('Y-m-d H:i:s');

		$currentTweet = crTweet::getTweetById($tID);
		
		$currentTweet['metaURL'] = isset($currentTweet['metaURL'])?$currentTweet['metaURL']:null;
		$currentTweet['metaTitle'] = isset($currentTweet['metaTitle'])?$currentTweet['metaTitle']:null;
		$currentTweet['metaDescription'] = isset($currentTweet['metaDescription'])?$currentTweet['metaDescription']:null;
		
		$data['metaURL'] = isset($data['metaURL'])?$data['metaURL']:null;
		$data['metaTitle'] = isset($data['metaTitle'])?$data['metaTitle']:null;
		$data['metaDescription'] = isset($data['metaDescription'])?$data['metaDescription']:null;
		
		$old =[];
		$changes =[];

		$type = isset($data['type']) ? $data['type'] : 'text';

		if(!in_array($type, ['text', 'image', 'video']))
			$type = 'text';

		if (!($type==$currentTweet['type'])){
			$changes['type']=	$type;
			
			if ($currentTweet['type']=='video')
				$changes['youtube_url']='';
			
			if ($currentTweet['type']=='image'){
				$changes['image']='';

				$currentImages = explode(';',$currentTweet['image']);
				if (sizeof($currentImages)>0){
					foreach ($currentImages as $img){
						crAlbum::deletePhoto($userID,$img,true);
					}
				}
			}
		}			

		//remove modified or deleted images
		crAlbum::deletePhoto($userID,$data['rem1']);
		crAlbum::deletePhoto($userID,$data['rem2']);
		
		$data['content'] = strip_tags($data['content']);
		if (!($data['content']==$currentTweet['content']))	
			$changes['content']	=	$data['content'];

		$old['tweetID']	=	$tID;
		$old['lType']	=	$currentTweet['type'];
		$old['lImage']	=	$currentTweet['image'];
		$old['lVideo']	=	$currentTweet['youtube_url'];
		$old['lContent']=	$currentTweet['content'];
		$old['lScrapeID']=	crTweet::getScrapeID($tID);
		$old['dateEdited']= $now;

		if (!isset($currentTweet['metaURL']))
			$currentTweet['metaURL']=null;

		if (!($data['metaURL']==$currentTweet['metaURL'])){
			//should work for scrape table - tricky !!
			if ($old['lScrapeID'])
				$db->updateFromArray(TABLE_SCRAPE, ['tweetID' => '0'],['scrapeID'=>$old['lScrapeID']]);

			//insert new scrape
			if (cr_not_null($data['metaURL']))
				$metaID= $db->insertFromArray(TABLE_SCRAPE, ['tweetID' => $tID,'metaURL'=> $data['metaURL'], 'metaTitle'=> $data['metaTitle'],'metaDescription'=> $data['metaDescription'],'metaImage'=> $data['metaImage']]);
		}

		switch ($type){
			case 'text':
				if(trim($data['content']) == ''){
					cr_add_message(MSG_CONTENT_IS_EMPTY, MSG_TYPE_ERROR);
					return false;
				}

				break;
			case 'video':
				//check if url changed
				if (!isset($currentTweet['youtube_url']))
					$currentTweet['youtube_url']='';
				
				if (!($data['youtube_url']==$currentTweet['youtube_url'])){
					//Check Youtube URL is Valid or Not
					$validation = cr_validate_youtube_url($data['youtube_url']);
					
					if(!$validation){
						cr_add_message(MSG_INVALID_YOUTUBE_URL, MSG_TYPE_ERROR);
						return false;
					}
					$changes['youtube_url']=$data['youtube_url'];
				}
				
				break;
			case 'image':
				if (!($data['images']==$currentTweet['image'])){
					$changes['image']=$data['images'];
					//move photos from temp folder to user folder and assiging them to the album
					cr_move_images($userID,$data['folder_token'],$data['images']);
				}

				break;
		}

		if(!cr_not_null($changes) && !isset($metaID)){
			cr_add_message(MSG_NO_CHANGES_ON_POST, MSG_TYPE_ERROR);
			return false;
		}

		$edit = $db->insertFromArray(TABLE_EDITS, $old);
		if (!($updated = $db->updateFromArray(TABLE_TWEETS, $changes, ['id'=>$tID]))){
			var_dump($changes);
			cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
			return false;
		}

		switch($type){
			case 'image':
				// No message
				break;
			case 'video':
			case 'text':
				cr_add_message(MSG_POST_EDITED, MSG_TYPE_SUCCESS);
				break;
			default:
				break;
		}

		crTweet::removeMentions($tID);
		crTweet::removeHashtags($tID);

		crTweet::addMentions($tID, $data['content']);
		crTweet::addHashtags($tID, $data['content']);

		return $tID;
	}

	/**
	 * Remove Post and Comment
	 *
	 * @param mixed $userID
	 * @param mixed $postID
	 * @return bool
	 */
	public static function deleteTweet($userID, $tweetID){
		global $db;

		$query = $db->prepare("SELECT id, type, ownerID, image FROM " . TABLE_TWEETS . " WHERE id=%s AND ownerID=%s", $tweetID, $userID);
		$row = $db->getRow($query);

		if($row){
			//Getting Comments and Likes
			$comments = $db->getVar('SELECT count(*) FROM ' . TABLE_COMMENTS . " WHERE tweetID=" . $row['id']);
			$likes = $db->getVar('SELECT count(*) FROM ' . TABLE_LIKES . " WHERE tweetID=" . $row['id']);
			$retweets = $db->getVar('SELECT count(*) FROM ' . TABLE_RETWEETS . " WHERE tweetID=" . $row['id']);
			
			//Update Stats
			//crUser::updateStats($row['ownerID'], 'comments', -1 * $comments);
			
			if ($row['image']){
				$photos = explode(';',$row['image']);
				
				$query= $db->prepare('SELECT folder_token FROM '.TABLE_PHOTOS.' WHERE id=%d',$photos[0]);
				$folder = DIR_IMG.'/users/'.$userID.'/'. ($db->getVar($query));
				cr_deleteDir($folder);
								
				$i=0;
				
				//var_dump($photos);
				//exit;
				
				while (isset($photos[$i]) && cr_not_null($photos[$i])){
					
					$db->query('DELETE FROM ' .TABLE_PHOTOS. ' WHERE id=' . $photos[$i]);	
					$i += 1;
				}
			}
			
			$db->query('DELETE FROM ' . TABLE_TWEETS . " WHERE id=" . $row['id']);
			$db->query('DELETE FROM ' . TABLE_COMMENTS . " WHERE tweetID=" . $row['id']);
			$db->query('DELETE FROM ' . TABLE_LIKES . " WHERE tweetID=" . $row['id']);
			$db->query('DELETE FROM' . TABLE_TWEETS. ' WHERE type="RETWEET" AND content="'.$row['ownerID'].'-'.$row['id'],'"'); 
					
			$db->query('DELETE FROM ' . TABLE_RETWEETS . " WHERE tweetID=" . $row['id']);

			$db->query('DELETE FROM ' . TABLE_MENTIONS . " WHERE tweetID=" . $row['id']);
			
			//Remove Image
			
			if($row['type'] == 'image'){
				$images = explode(';',$row['image']);

				foreach ($images as $img){
					/*
					$image = crAlbum::getPhotoByID($img);

					@unlink(DIR_IMG . "/users/" . $userID ."/".$image['folder_token']. "/" . $image['name']);
					@unlink(DIR_IMG . "/users/" . $userID ."/".$image['folder_token']. "/thumb-" . $image['name']);
					*/
					crAlbum::deletePhoto($userID,$img,true);
				}

				//REVISIT to decide if albums needed
				//Remove From Albums
				//$db->query('DELETE FROM ' . TABLE_ALBUMS_PHOTOS . ' WHERE post_id=' . $row['postID']);
				$user = crUser::getUserData($userID);

				//If current image is a profile image, remove it from the profile image
				if($user['thumbnail'] == $row['image']){
					crUser::updateUserFields($userID, ['thumbnail' => '']);
				}
			}
			return true;
		}else{
			return false;
		}
	}
		
	/**
	 * Save Photo
	 *
	 * @param $userID
	 * @param mixed $data
	 * @return bool|int|null|string
	 */
	public static function savePhoto($userID, $data){
		global $db, $SITE_GLOBALS;

		//$folder = DIR_IMG.'/users/'.$userID.'/'.$data['folder_token'];
		//$files = array();
		//$image_tab="";

		$now = date('Y-m-d H:i:s');
		$album = crAlbum::createAlbum($userID,'unclassified',1);

		/*
		foreach ($data as $file=>$val){
			if (substr($file,0,5)=="file-") {
				$name = explode('-',$file);
				$name = end($name);
				$ext = explode('.',$val);
				$ext = end($ext);
				$filename= $name.'.'.$ext;
				
				if(file_exists($folder .'/'. $filename) && !$filename==''){
					array_push($files, $folder.'/'.$filename);
					$nPhoto = $db->insertFromArray(TABLE_PHOTOS,['ownerID' => $userID, 'name' => $filename, 'folder_token' => $data['folder_token'],'dateUploaded' => $now,'title' => $val]);
					$image_tab .=  $nPhoto . ';';
					
					crAlbum::addPhotoToAlbum($album,$nPhoto);
					
					if ($nPhoto){
						$saved=[];
						
						while (!isset($saved['ownerID'])){
							$saved = crAlbum::getPhotoByID($nPhoto);			
						}
						unset($saved);	
					}
				}
			}
		}
		*/

		if (!isset($data['images']) || !cr_not_null($data['images']) ){
			cr_add_message(MSG_FILE_UPLOAD_ERROR, MSG_TYPE_ERROR);
			return false;
		}
		
		$newId = $db->insertFromArray(TABLE_TWEETS, ['ownerID' => $userID, 'content' => $data['content'], 'type' => 'image', 'date' => $now, 'image' => $data['images'], 'rtl' => $data['rtl']]);
		
		if(!$newId){
			cr_add_message($db->getLastError(), MSG_TYPE_ERROR);
			return false;
		}
		
		//REVISIT	
		//Assign Photo to Album
		//if(isset($data['album']) && $data['album'] != ''){
		//	if(!crAlbum::checkAlbumOwner($data['album'], $userID)){
		//		cr_add_message(MSG_INVALID_ALBUM_ID, MSG_TYPE_ERROR);
		//	}else{
		//		crAlbum::addPhotoToAlbum($data['album'], $newId);
		//	}
		// }
	
		cr_add_message(MSG_PHOTO_UPLOADED_SUCCESSFULLY,MSG_TYPE_SUCCESS);

		return $newId;
	}

	/**
	 * Create Profile image using already uploaded images
	 *
	 * @param Array $photo
	 * @param Array $data
	 */
	public static function createProfileImage($photo, $data){
		global $db;

		$orgFile = DIR_IMG . "/users/" . $photo['ownerID'] . "/original/" . $photo['thumbnail'];
		$targetFile = DIR_IMG . "/users/" . $photo['ownerID'] . "/resized/" . $photo['thumbnail'];

		list($width, $height, $type, $attr) = getimagesize($orgFile);

		//Calc Ratio using real image width
		$ratio = floatval($width / 576);
		$sourceWidth = ($data['x2'] - $data['x1']) * $ratio;

		crTweet::resizeImage($photo['ownerID'], $photo['image'], PROFILE_IMAGE_WIDTH, PROFILE_IMAGE_HEIGHT, $data['x1'] * $ratio, $data['y1'] * $ratio, $sourceWidth, $sourceWidth);

		$db->updateFromArray(TABLE_TWEETS, ['is_profile' => 1], ['id' => $photo['id']]);

	}

	/**
	 * Move uploaded file to the user folder from the tmp folder
	 * @param $userID
	 * @param $file
	 * @param $targetWidth
	 * @param $targetHeight
	 * @param null $sourceX
	 * @param null $sourceY
	 * @param null $sourceWidth
	 * @param null $sourceHeight
	 */
	 //REVISIT to check the HTML
	public static function moveFileFromTmpToUserFolder($userID, $file, $targetWidth, $targetHeight, $sourceX = null, $sourceY = null, $sourceWidth = null, $sourceHeight = null){
		$dir = DIR_IMG . "/users";
		if(!is_dir($dir)){
			mkdir($dir, 0777);
			$fp = fopen($dir . "/index.html", "w");
			fclose($fp);
		}

		$dir = $dir . "/" . $userID;
		if(!is_dir($dir)){
			mkdir($dir, 0777);
			$fp = fopen($dir . "/index.html", "w");
			fclose($fp);
		}

		$dir_org = $dir . "/original";
		if(!is_dir($dir_org)){
			mkdir($dir_org, 0777);
			$fp = fopen($dir_org . "/index.html", "w");
			fclose($fp);
		}

		$dir_resized = $dir . "/resized";
		if(!is_dir($dir_resized)){
			mkdir($dir_resized, 0777);
			$fp = fopen($dir_resized . "/index.html", "w");
			fclose($fp);
		}

		$dir_thumbnail = $dir . "/thumbnail";
		if(!is_dir($dir_thumbnail)){
			mkdir($dir_thumbnail, 0777);
			$fp = fopen($dir_thumbnail . "/index.html", "w");
			fclose($fp);
		}

		// Move File to the original folder
		$fp1 = fopen(DIR_IMG_TMP . $file, "r");
		$fp2 = fopen($dir_org . "/" . $file, "w");
		$buff = fread($fp1, filesize(DIR_IMG_TMP . $file));
		fwrite($fp2, $buff);
		fclose($fp1);
		fclose($fp2);

		// Remove Tmp File
		@unlink(DIR_IMG_TMP . $file);

		// Resize The Image
		crTweet::resizeImage($userID, $file, $targetWidth, $targetHeight, $sourceX, $sourceY, $sourceWidth, $sourceHeight);
	}

	/**
	 * Resize Image and create the file on the resized folder
	 *
	 * @param Int $userID
	 * @param String $file
	 * @param        $destWidth
	 * @param        $destHeight
	 * @param null $sourceX
	 * @param null $sourceY
	 * @param null $sourceWidth
	 * @param null $sourceHeight
	 * @return Resized File name
	 * @internal param Int $width
	 * @internal param Int $height
	 */
	public function resizeImage($userID, $file, $destWidth, $destHeight, $sourceX = null, $sourceY = null, $sourceWidth = null, $sourceHeight = null){

		// Get the image size for the current original photo
		list($currentWidth, $currentHeight, $destType) = getimagesize(DIR_FS_PHOTO . "users/" . $userID . "/original/" . $file);
		$destType = image_type_to_mime_type($destType);

		// Find the correct x/y offset and source width/height. Crop the image squarely, at the center.
		if(!$sourceWidth)
			$sourceWidth = $currentWidth;
		if(!$sourceHeight)
			$sourceHeight = $currentHeight;

		//Create Thumbnail;
		crTweet::createThumbnail($userID, $file);

		$destPath = DIR_IMG . "/users/" . $userID . "/resized/" . $file;
		return cr_resize_image(DIR_IMG . "/users/" . $userID . "/original/" . $file, $destPath, $destType, $destWidth, $destHeight, $sourceX, $sourceY, $sourceWidth, $sourceHeight);
	}

	/**
	 * Create thumbnail
	 *
	 * @param mixed $userID
	 * @param mixed $file
	 */
	public function createThumbnail($userID, $file){
		list($currentWidth, $currentHeight, $destType) = getimagesize(DIR_IMG . "/users/" . $userID . "/original/" . $file);
		$destType = image_type_to_mime_type($destType);

		if($currentWidth == $currentHeight){
			$sourceX = 0;
			$sourceY = 0;
		}else if($currentWidth > $currentHeight){
			$sourceX = intval(($currentWidth - $currentHeight) / 2);
			$sourceY = 0;
			$currentWidth = $currentHeight;
		}else{
			$sourceX = 0;
			$sourceY = intval(($currentHeight - $currentWidth) / 2);
			$currentHeight = $currentWidth;
		}

		$destPath = DIR_IMG . "/users/" . $userID . "/thumbnail/" . $file;
		cr_resize_image(DIR_IMG . "/users/" . $userID . "/original/" . $file, $destPath, $destType, IMAGE_THUMBNAIL_WIDTH, IMAGE_THUMBNAIL_HEIGHT, $sourceX, $sourceY, $currentWidth, $currentHeight);
	}

	/**
	 * Get Post By Id
	 *
	 * @param      $id
	 * @param null $pageID
	 * @return array
	 */
	public static function getTweetById($id){
		global $db;

			$query = $db->prepare("SELECT * FROM " . TABLE_TWEETS . " AS t
			LEFT JOIN ".TABLE_SCRAPE." AS s ON s.tweetID=t.id WHERE id=%d", $id);

		$row = $db->getRow($query);

		return $row;
	}

	public static function searchTweets($keyword,$userID,$limit=6,$lastDate){
		global $db;

		$query = "SELECT DISTINCT t.id FROM ".TABLE_TWEETS. " AS t
			LEFT JOIN ". TABLE_USERS." AS u ON u.id = t.ownerID 
			LEFT JOIN ". TABLE_FOLLOWSHIP. " AS f ON f.followedID = t.ownerID
			WHERE (u.privacy=1 OR f.followerID = ". $db->escapeInput($userID) .") AND 
			(t.content LIKE '%" . $db->escapeInput($keyword) . "%') AND 
			t.date < '".$db->escapeInput($lastDate)."' 
			ORDER BY t.date DESC LIMIT ". $db->escapeInput($limit);
		
		$results = $db->getResultsArray($query);

		if (!$results)
			return;

		$ret=[];
			
		foreach ($results as $t){
			$tweet = crTweet::getTweetById($t);
			$tweet = crTweet::GetLikesCommentsRetweets($tweet,$userID);
			$ret[] = $tweet;
		}

		return $ret;
	}

	/**
	 * Update Photo Data
	 *
	 * @param Int $userID
	 * @param Array $data
	 * @return bool
	 */
	public static function updatePhoto($userID, $data){
		global $db;

		$photo = crTweet::getTweetById($data['id']);

		//Update Content And visibility
		$db->updateFromArray(TABLE_TWEETS, ['content' => $data['content'], ['id' => $data['id']]]);

		return true;
	}

	/**
	 * Like Post
	 *
	 * @param int $userID
	 * @param int $postID
	 * @param $action
	 * @param bool $checkToken
	 * @return bool|int|null|string
	 */
	public static function likeTweet($userID, $tweetID, $action, $checkToken = true){
		global $db;

		$tweet = crTweet::getTweetById($tweetID);

		if($checkToken && !cr_check_form_token('request')){
			cr_add_message(MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
			return false;
		}

		if(!$tweet){
			cr_add_message(MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
			return false;
		}

		/*
				if(!crUsersDailyActivity::checkUserDailyLimit($userID, 'likes')){
					cr_add_message(sprintf(MSG_DAILY_LIKES_LIMIT_EXCEED_ERROR, USER_DAILY_LIMIT_LIKES), MSG_TYPE_ERROR);
					return false;
				}
		*/

		//Check already like it or not
		$query = $db->prepare("SELECT * FROM " . TABLE_LIKES . " WHERE likerID=%s AND tweetID=%s", $userID, $tweetID);
		$likeId = $db->getVar($query);

		if($action == 'likeTweet'){
			if($likeId){
				cr_add_message(MSG_ALREADY_LIKED_POST, MSG_TYPE_ERROR);
				return false;
			}

			//crUsersDailyActivity::addLikes($userID);

			//Like This post
			$rs = $db->insertFromArray(TABLE_LIKES, ['likerID' => $userID, 'tweetID' => $tweetID]);
			
			
			//Update likes on the posts table
			$query = $db->prepare('UPDATE ' . TABLE_TWEETS . ' SET `likesCount`=`likesCount` + 1 WHERE id=%d', $tweetID);
			$db->query($query);

			
			//Add Activity
			$activityId = crActivity::addActivity($userID, $tweetID, 'TWEET', 'LIKE', $rs);

			//Add Notification
			if($tweet['ownerID'] != $userID)
				crActivity::addNotification($tweet['ownerID'], $activityId, crActivity::NOTIFICATION_TYPE_LIKE_TWEET);

			return true;
		}else if($action == 'unlikeTweet'){
			if(!$likeId){
				cr_add_message(MSG_INVALID_REQUEST, MSG_TYPE_ERROR);
				return false;
			}

			//REVISIT as well
			//crUsersDailyActivity::addLikes($userID);

			$query = $db->prepare("DELETE FROM " . TABLE_LIKES . " WHERE likerID=%d AND tweetID=%d", $userID, $tweetID);
			$db->query($query);

			//Update likes on the posts table
			$query = $db->prepare('UPDATE ' . TABLE_TWEETS . ' SET `likesCount`=`likesCount` - 1 WHERE id=%d', $tweetID);
			$db->query($query);

			//Increase Hits
			//BuckysHit::removeHit($postID, $userID);

			//Update User Stats
			//BuckysUser::updateStats($post['poster'], 'likes', -1);

			return true;
		}
	}

	/**
	 * Post Likes count
	 *
	 * @param integer $postID
	 * @return one
	 */
	public static function getTweetLikesCount($id){
		global $db;

		$query = $db->prepare("SELECT likesCount FROM " . TABLE_TWEETS . " WHERE id=%d", $id);
		$count = $db->getVar($query);

		return $count;
	}

	/**
	 * Get Top Posts or Images
	 *
	 * @param String $period
	 * @param String $type
	 * @return Indexed
	 */
	public static function getTopTweets($period = 'today', $type = 'text', $page = 1, $limit = null){
		global $db;

		if($limit == null)
			$limit = crTweet::${COUNT_PER_PAGE . strtoupper("_$type")};

		$limit = ($page - 1) * $limit . ", " . $limit;

		switch($period){
			case 'today':
				$query = "
                    SELECT DISTINCT(p.postID), p.pageID, p.content, p.image, p.comments, p.post_date, p.is_profile, page.title AS pageTitle, page.logo AS pageLogo, page.userID AS pageOwnerID, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster
					LEFT JOIN " . TABLE_PAGES . " AS page ON page.pageID = p.pageID                    
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date > '" . date("Y-m-d 00:00:00") . "'                    
                    ORDER BY p.likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
			case 'this-week':
				$cw = date("w");
				$sDate = date("Y-m-d 00:00:00", time() - $cw * 60 * 60 * 24);
				$query = "
                    SELECT p.postID, p.pageID, p.content, p.image, p.post_date, p.comments, p.is_profile, page.title AS pageTitle, page.logo AS pageLogo, page.userID AS pageOwnerID, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster   
					LEFT JOIN " . TABLE_PAGES . " AS page ON page.pageID = p.pageID
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date > '" . $sDate . "'
                    GROUP BY p.postID
                    ORDER BY likes DESC, p.post_date
                    LIMIT $limit
                ";

				break;
			case 'this-month':
				$sDate = date("Y-m-01 00:00:00");
				$query = "
                    SELECT p.postID, p.pageID, p.content, p.image, p.post_date, p.comments, p.is_profile, page.title AS pageTitle, page.logo AS pageLogo, page.userID AS pageOwnerID, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster
					LEFT JOIN " . TABLE_PAGES . " AS page ON page.pageID = p.pageID					
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date > '" . $sDate . "'                    
                    GROUP BY p.postID
                    ORDER BY likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
			case 'all':
				$query = "
                    SELECT p.postID, p.pageID, p.content, p.image, p.post_date, p.comments, p.is_profile, page.title AS pageTitle, page.logo AS pageLogo, page.userID AS pageOwnerID, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster
					LEFT JOIN " . TABLE_PAGES . " AS page ON page.pageID = p.pageID					
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1)
                    GROUP BY p.postID
                    ORDER BY likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
		}

		$rows = $db->getResultsArray($query);

		return $rows;
	}

	/**
	 * Getting Top Posts for Homepage
	 *
	 * @param mixed $period
	 * @param mixed $type
	 * @param mixed $base
	 * @param mixed $page
	 * @param BuckysPost $limit
	 * @return Indexed
	 */
	public static function getTopPostsForHomepage($period = 'today', $type = 'text', $base = 1.04, $page = 1, $limit = null){
		global $db;

		if($limit == null)
			$limit = BuckysPost::${COUNT_PER_PAGE . strtoupper("_$type")};

		$limit = ($page - 1) * $limit . ", " . $limit;

		switch($period){
			case 'today':
				$query = "
                    SELECT DISTINCT(p.postID), p.content, p.image, p.comments, p.post_date, p.is_profile, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url
                    ,(p.`likes` + p.`comments`) - POW(" . $base . ", TIMESTAMPDIFF(MINUTE, p.`post_date`, '" . date("Y-m-d H:i:s") . "')) AS rating
                    FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster                    
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date >= '" . date("Y-m-d 00:00:00") . "'                    
                    ORDER BY rating DESC, p.likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
			case 'this-week':
				$cw = date("w");
				$sDate = date("Y-m-d 00:00:00", time() - $cw * 60 * 60 * 24);
				$query = "
                    SELECT p.postID, p.content, p.image, p.post_date, p.comments, p.is_profile, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url 
                    ,(p.`likes` + p.`comments`) - POW(" . $base . ", TIMESTAMPDIFF(MINUTE, p.`post_date`, '" . date("Y-m-d H:i:s") . "')) AS rating
                    FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster   
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date < '" . date("Y-m-d 00:00:00") . "' AND p.post_date >= '" . $sDate . "'
                    GROUP BY p.postID
                    ORDER BY rating DESC, likes DESC, p.post_date
                    LIMIT $limit
                ";

				break;
			case 'this-month':
				$sDate = date("Y-m-01 00:00:00");
				$cw = date("w");
				$eDate = date("Y-m-d 00:00:00", time() - $cw * 60 * 60 * 24);
				$query = "
                    SELECT p.postID, p.content, p.image, p.post_date, p.comments, p.is_profile, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url 
                    ,(p.`likes` + p.`comments`) - POW(" . $base . ", TIMESTAMPDIFF(MINUTE, p.`post_date`, '" . date("Y-m-d H:i:s") . "')) AS rating
                    FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND p.post_date < '" . $eDate . "' AND p.post_date >= '" . $sDate . "'                    
                    GROUP BY p.postID
                    ORDER BY rating DESC, likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
			case 'all':
				$query = "
                    SELECT p.postID, p.content, p.image, p.post_date, p.comments, p.is_profile, u.thumbnail, u.userID, CONCAT(u.firstName, ' ', u.lastName) as userName, p.likes, p.youtube_url 
                    ,(p.`likes` + p.`comments`) - POW(" . $base . ", TIMESTAMPDIFF(MINUTE, p.`post_date`, '" . date("Y-m-d H:i:s") . "')) AS rating
                    FROM " . TABLE_POSTS . " as p
                    LEFT JOIN " . TABLE_USERS . " AS u ON u.userID = p.poster
                    WHERE p.post_status=1 AND p.type='" . $type . "' AND (p.visibility=1) AND TIMESTAMPDIFF(MINUTE, p.`post_date`, '" . date("Y-m-d H:i:s") . "') < 450000
                    GROUP BY p.postID
                    ORDER BY rating DESC, likes DESC, p.post_date
                    LIMIT $limit
                ";
				break;
		}

		$rows = $db->getResultsArray($query);

		return $rows;
	}

	/**
	 * Get number of top posts, videos or images
	 *
	 * @param string $period
	 * @param string $type
	 * @return one
	 */
	public static function getNumberOfTweets($user){
		global $db;

		$query = $db->prepare("SELECT count(*) FROM " . TABLE_TWEETS . " WHERE ownerID=%d", $user);
		$count = $db->getVar($query);

		return $count;
	}

	/**
	 * Get Liked User Data
	 *
	 * @param mixed $postID
	 * @return Indexed
	 */
	public static function getLikedUsers($tweetID){
		global $db;

		$query = $db->prepare("SELECT u.id, u.name, u.username, u.thumbnail, u.description FROM " . TABLE_LIKES . " AS pl LEFT JOIN " . TABLE_USERS . " AS u ON u.id=pl.likerID WHERE pl.tweetID=%d ORDER BY pl.dateLiked DESC LIMIT 30", $tweetID);
		$likes = $db->getResultsArray($query);
		
		return $likes;
	}
	
	public function getTweetsCountByUserID ($id){
		global $db;
		
		$query = $db->prepare('SELECT count(*) FROM '.TABLE_TWEETS.' WHERE ownerID=%d',$id);
		$count= $db->getVar($query);
		
		return $query;
	}

	public static function addMentions($tweetID, $content){
		global $db;
		$needle = [" ","\n","\r\n"]; 
		
		$start = 0;
		while (($pos = strpos($content, '@', $start)) !== FALSE) {
  			$endpos = strlen($content);
  			foreach ($needle as $n){
				$ep = strpos($content,$n, $pos);	
				if ($ep && $ep<$endpos)
					$endpos = $ep;
			}
  			
  			$mentioned = strtolower(substr($content,$pos+1, $endpos-$pos-1));
  			
			$query = $db->prepare('SELECT id FROM '. TABLE_USERS.' WHERE LCASE(username)=%s',$mentioned);				
			$id = $db->getVar($query);
				
			if ($id){
				$newMention = $db->insertFromArray(TABLE_MENTIONS, ['tweetID'=>$tweetID,'mentionedID'=>$id]);	
				
				$tweetOwner = crTweet::getTweetOwner($tweetID);

				$activityID = crActivity::addActivity($tweetOwner,$tweetID,"TWEET","MENTION",$newMention);
				$notificationID = crActivity::addNotification($id,$activityID,crActivity::NOTIFICATION_TYPE_MENTIONED,TRUE);
			}
  				
  			$start = $endpos;
			
		}
	}

	public static function identifyMentions($content, $tweetID){
		global $db;
		
		$start = 0;
		
		$query = $db->prepare('SELECT mentionedID FROM '.TABLE_MENTIONS.' WHERE tweetID=%d',intval($tweetID));
		$rows = $db->getResultsArray($query);
		
		foreach ($rows as $id) {
			$info = crUser::getUserBasicInfo($id['mentionedID']);
			$username='@'.$info['username'];
			$len = strlen($username)+1;
			
  			$html = '<a href="/profile.php?userID='.$info['id'].'">'.$username.'</a>';
  			
  			$lContent = strtolower($content);
  			
  			$pos = strpos($lContent,strtolower($username));
  			
  			$content = substr_replace($content, $html,$pos, strlen($username));
		}
		return $content;		
	}

	public static function removeMentions($tweetID){
		global $db;
		$db->query('DELETE FROM '.TABLE_MENTIONS.' WHERE tweetID='.$tweetID);
	}

	public static function addRetweet($tweetID,$ownerID,$userID){
		global $db;
		
		$query = $db->prepare('SELECT id FROM '.TABLE_TWEETS.' WHERE id=%d AND ownerID=%d',$tweetID, $ownerID);
		
		$result = $db->getVar($query);
		
		if (!$result)
			return FALSE;
		
		$now = date('Y-m-d H:i:s');
		
		$retweet = $db->insertFromArray(TABLE_RETWEETS, ['tweetID'=>$tweetID,'ownerID'=>$ownerID,'retweeterID'=>$userID,'retweetDate'=>$now]);
		
		$retweetRef = $db->insertFromArray(TABLE_TWEETS, ['ownerID' => $userID, 'content' => $ownerID.'-'.$tweetID, 'type' => 'RETWEET', 'image' =>'', 'youtube_url' => '', 'date'=>$now]);
		
		if (!$retweet || !$retweetRef)
			return FALSE;
		
		//Update likes on the posts table
		$query = $db->prepare('UPDATE ' . TABLE_TWEETS . ' SET `retweetsCount`=`retweetsCount` + 1 WHERE id=%d', $tweetID);
		$db->query($query);
		
		$rActivity = crActivity::addActivity($userID,$tweetID,'TWEET','RETWEET',$retweet);
		
		crActivity::addNotification($ownerID,$rActivity,crActivity::NOTIFICATION_TYPE_RETWEET_TO_TWEET, TRUE);
		
		return $retweetRef;
		
	}
	
	public static function isRetweeted($tweetID, $userID){
		global $db;
		
		$query = $db->prepare('SELECT rID FROM '.TABLE_RETWEETS.' WHERE tweetID=%d and retweeterID=%d', $tweetID, $userID);
		$results = $db->getVar($query);
		
		if (!$results){
			return FALSE;
		} else {
			return TRUE;
		}
		
	}
	
	public static function deleteRetweet($retweetID, $retweeterID){
		global $db;
		
		$rtData = crTweet::getTweetById($retweetID);
		
		if (!($rtData['ownerID']==$retweeterID))
			return FALSE;
			
		$param = explode('-', $rtData['content']);
		$orgTweet = $param[1];
		$orgOwner = $param[0];
		
		$res1 = crTweet::deleteTweet($retweeterID, $retweetID);
		
		$query = $db->prepare('DELETE FROM '.TABLE_RETWEETS.' WHERE tweetID=%d AND ownerID=%d AND retweeterID=%d', $orgTweet, $orgOwner, $retweeterID);
		$res2 = $db->query($query);
		
		$query= $db->prepare('UPDATE '.TABLE_TWEETS.' SET `retweetsCount`=`retweetsCount`-1 WHERE id=%d', $orgTweet);
		$db->query($query);
		
		if (!$res1 || !$res2) {
			return FALSE;
		} else{
			return TRUE;
		}
	}

	//check if Retweeted By the User and no need to show again in account page
	public static function isAlreadyRetweetedByUser($tweet, $userID){
		
		//not yet shown for the first time by the user
		if ($userID == $tweet['ownerID'])
			return FALSE;
		
		$tID = $tweet['id'];
		
		if ($tweet['type']=='RETWEET'){
			$param = explode('-',$tweet['content']);
			$tID = $param[1];
		}
                
        if (crTweet::isRetweeted($tID,$userID))
            return TRUE;
        
        return FALSE;
	}

	/*
	*
	* extract hashtags from tweet
	* int $tweetID
	* string $content
	*/
	public static function addHashtags($tweetID, $content){
		global $db;
		$needle = [" ","\n","\r\n"]; 
		
		$tags = [];

		$start = 0;
		while (($pos = strpos($content, '#', $start)) !== FALSE) {
  			$endpos = strlen($content);
  			foreach ($needle as $n){
				$ep = strpos($content,$n, $pos);	
				if ($ep && $ep<$endpos)
					$endpos = $ep;
			}
  			
  			$tag = strtolower(substr($content,$pos+1, $endpos-$pos-1));
			  
			$start = $endpos;
			  
			if (in_array($tag,$tags)){
				continue;	
			} 

			array_push($tags,$tag);

			$query = $db->prepare('SELECT hID FROM '. TABLE_HASHTAGS.' WHERE LCASE(value)=%s',$tag);				
			$id = $db->getVar($query);
				
			if ($id) {
				$query = $db->prepare("UPDATE " . TABLE_HASHTAGS . " SET `timesUsed`=`timesUsed` + 1 WHERE hID=%d ", $id);
				$db->query($query);
			} else {
				$id = $db->insertFromArray(TABLE_HASHTAGS, ['value'=>$tag,'firstUsed'=>date("Y-m-d H:i:s",time()), 'timesUsed'=>1, 'popularity'=>1]);	
			}
			  
			$db->insertFromArray(TABLE_TAG_INDEX,['hID'=>$id, 'tID'=>$tweetID]);
			
		}
	}

	public static function removeHashtags ($tweetID){
		global $db;
		$db->query('DELETE FROM '.TABLE_TAG_INDEX.' WHERE tID='.$tweetID);
	}

	public static function getTweetsByTag($tag, $loggedUserID, $lastDate=null){
		global $db;

		$query = $db->prepare('SELECT hID FROM '.TABLE_HASHTAGS.' WHERE value=%s',strtolower($tag));
		$hID = $db->getVar($query);
		
		if ($hID){
			$limit_query = ' LIMIT ' . crTweet::$tweets_per_page;

			$query = $db->prepare('SELECT t.*,s.* FROM ' . TABLE_TAG_INDEX . ' AS ta
			LEFT JOIN ' . TABLE_TWEETS . ' AS t ON t.id = ta.tID 
			LEFT JOIN ' . TABLE_SCRAPE . ' AS s ON t.id = s.tweetID 
			WHERE ta.hID=%d'.  ($lastDate != null ? ' AND t.date < "' . $lastDate . '"' : '')  . " ORDER BY t.date DESC" . $limit_query ,  $hID);

			$rows= $db->getResultsArray($query);
		
			$results=[];
			
			foreach($rows as $idx){
	
				$owner = crUser::getUserBasicInfo($idx['ownerID']);
				
				if ($owner['privacy']==0) {
				
					$rel= crFollowship::isFollowed($loggedUserID, $idx['ownerID']);
					if (!$rel || $rel['status']==0)
						continue;
				}

				$idx = crTweet::GetLikesCommentsRetweets($idx,$loggedUserID);
				
				array_push($results,$idx);		
			}
			
			return $results;
		}

		return false;
	}
	
    /**
     * Search Tags
     *
     * @param String $term
     * @return Indexed
     */

	public static function searchTag($term, $limit=NULL, $withCount=false, $step=null){
        global $db;

        $query = "SELECT value FROM " . TABLE_HASHTAGS . " WHERE (value LIKE '%" . $db->escapeInput($term) . "%') ORDER BY timesUsed" ;
		
		if (cr_not_null($limit))
			$query .= " LIMIT " . $limit. (cr_not_null($step)&&intval($step)>0? " OFFSET ". (intval($step)*$limit):"");

		$rows = $db->getResultsArray($query);
		
		if (!$withCount)
			return $rows;
		
		$return = [];
		foreach ($rows as $r){
			$query = 'SELECT timesUsed FROM '. TABLE_HASHTAGS. ' WHERE value="'.$r['value'].'"';
			$count = $db->getVar($query);

			if (!$count)
				$count = 0; else
				$count = (int) $count;

			$return[] = ['tag'=>$r['value'], 'count'=>$count]; 
		}

		return $return;
	}

	public static function getTweetTags($tID){
		global $db;

		$query = $db->prepare("SELECT t.value FROM ".TABLE_HASHTAGS. " AS t 
			LEFT JOIN ".TABLE_TAG_INDEX." AS i ON i.hID = t.hID WHERE i.tID=%d",$tID);

		$res = $db->getResultsArray($query);

		if (!$res)
			return false;

		return $res;
	}

    /**
     * Get user last tweet date
     *
     * @param int $userID
     * @return $date
     */

	public static function getUserLastTweetDate($userID){
		global $db;

        $query = $db->prepare('SELECT date FROM '.TABLE_TWEETS.' WHERE ownerID=%d ORDER BY date desc LIMIT 1', $userID);
        $date = $db->getVar($query);
		
		if ($date)
			return $date;
		
		return false;
	}

	public static function getScrapeID($tID){
		global $db;
		
		$query = $db->prepare('SELECT scrapeID FROM '.TABLE_SCRAPE.' WHERE tweetID=%d', $tID);
        $id = $db->getVar($query);
		
		if ($id)
			return $id;
		
		return false;
	}

	public static function uploadPhoto($fileData,$userID,$folder,$pID){

		$targetPath = DIR_IMG_TMP . "/users/".$userID.'/'.$folder;
		
		if (!is_dir(DIR_IMG_TMP . "/users/".$userID))
			mkdir(DIR_IMG_TMP . "/users/".$userID, 0777);
			
		if(!is_dir($targetPath))
			mkdir($targetPath, 0777);
			
		$errors = array();
		
		$file_size = $fileData['image']['size'];
		$file_tmp = $fileData['image']['tmp_name'];
		$file_type= $fileData['image']['type'];
		$par = explode('.',$fileData['image']['name']);   
		$file_ext = strtolower(end($par));
		$extensions = ['jpg', 'jpeg', 'png', 'gif']; 		
		$file_name = $pID.'.'.$file_ext;

		if(in_array($file_ext, $extensions)=== false)
			$errors[]="extension not allowed, please choose a JPEG or PNG file.";
		
		if($file_size > 4048576)
			$errors[]='File size grater than 4 MB';
		
		if(empty($errors)==false)
			return false;

		$imageSize = getimagesize($file_tmp);
		
		$exif = exif_read_data($file_tmp);

		if (!empty($exif['Orientation']) && ($exif['Orientation']==3 || $exif['Orientation'] ==6)){
			$width = $imageSize[1];
			$imageSize[1] = $imageSize[0];
			$imageSize[0] = $width;
		} 		
	
		if($imageSize[0]>MAX_IMAGE_WIDTH || $imageSize[1]>MAX_IMAGE_HEIGHT){
			if ($imageSize[0]>$imageSize[1]){
				$width = MAX_IMAGE_WIDTH;
				$height = MAX_IMAGE_WIDTH *($imageSize[1]/$imageSize[0]); 
			} else {
				$height = MAX_IMAGE_HEIGHT;
				$width = MAX_IMAGE_HEIGHT * ($imageSize[0]/$imageSize[1]);
			}
			
			cr_resize_image($file_tmp,$targetPath . "/" . $file_name ,$file_type,$width,$height,0,0,$imageSize[0],$imageSize[1]);
			
			$param = ['cX'=>0,'cY'=>0,
				'iW'=>$height,
				'iH'=>$height,
				'photo'=>$targetPath . "/" . $file_name,
				'save_folder'=> $targetPath,
				'file_name'=> $file_name];
			
			cr_crop_for_thumbnail($param, $userID,$file_type);
			
		} else {
			move_uploaded_file($file_tmp, $targetPath . "/" . $file_name);
		
			$param = ['cX'=>0,'cY'=>0,
				'iW'=>$imageSize[1],
				'iH'=>$imageSize[1],
				'photo'=>$targetPath . "/" . $file_name,
				'save_folder'=> $targetPath,
				'file_name'=> $file_name];
				
			cr_crop_for_thumbnail($param, $userID,$file_type);
		}

		//adding photo to DB
		$pDB= crAlbum::savePhoto($userID,$folder,$file_name,$targetPath."/".$file_name);
		
		return $pDB?$pDB:false;
	}
}