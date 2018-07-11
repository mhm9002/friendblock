<?php
require(dirname(__FILE__) . '/includes/boot.php');
$userID = cr_is_logged_in();

if (isset($_POST['action']) && $_POST['action']=='getalbums') {
	
	$userID = cr_is_logged_in();
	$uID = $_POST['userID'];
	
	if (!$userID || !($userID['id'] == $uID)){
		cr_add_message(MSG_INVALID_LOGIN_INFO);
		return FALSE;
	}
	
	$albums = crAlbum::getAlbumsByUserId($uID);
	
	foreach ($albums as $alb){
	
		echo '<tr>
				<td>
					<a class="albumLink" id="albumID-'.$alb['albumID'].'">'.$alb['name'].'</a>
				</td>
				<td>'.$alb['photos'].' photos </td>	
				<td>'.$alb['created_date'].'</td>
			</tr>';
	
	}

} elseif (isset($_POST['action']) && $_POST['action']=='getphotos') {
	$albumID = $_POST['albumID'];
	
	$lastDate = isset($_POST['lastDate']) ? $_POST['lastDate']:null; 

	$albumPhotos = crAlbum::getAlbumPhotos($albumID, $lastDate);
	
	if (!$albumPhotos){
		echo '<tr><td colspan="7"><label>No more photos found</label></td></tr>';
		return false;
	}

	if (!$lastDate){
		echo '<tr>
			<td><span class="img-thumb-up" /></td>';
		$x=1;
	} else {
		echo '<tr>';
		$x=0;
	}
	
	$lastDate=$albumPhotos[0]['dateUploaded'];

	foreach ($albumPhotos as $photo){
		
		$photoFilename = DIR_IMG.'/users/'.$userID['id'].'/'.$photo['folder_token'].'/thumb-'.$photo['name'];
		$fullFilename = DIR_IMG.'/users/'.$userID['id'].'/'.$photo['folder_token'].'/'.$photo['name'];

		if (file_exists($photoFilename)){
			$x +=1;

			echo '<td>
				<span class="img-thumb" data-whatever="'.$fullFilename.'" style="background-image: url('.$photoFilename.');" id="pp-'.$photo['id'].'" />
			</td>';
			
			if ($lastDate>$photo['dateUploaded'])
				$lastDate=$photo['dateUploaded']; 

			if ($x == 7)
			{
				$x = 0;
				echo '</tr><tr>';
			}
		}
	}
	
	echo '</tr><tr><td colspan="7"><input type="button" 
		id="loadmore" 
		value="Load More" 
		data-date="'.$lastDate.'" 
		data-whatever="'.$albumID.'" /></td><tr>';

} elseif (isset($_POST['action']) && $_POST['action']=='setprofilephoto') {
	
	$photo = $_POST['photo'];
	list ($width, $height, $type) = getimagesize($photo);
	
	$type = image_type_to_mime_type($type);
		
	if (($width==IMAGE_THUMBNAIL_WIDTH && $height==IMAGE_THUMBNAIL_HEIGHT) || isset($_POST['cX'])){
		
		if (isset($_POST['cX']))
			$photo = cr_crop_for_thumbnail($_POST, $userID['id'], $type);
		
		while (!file_exists($photo)) sleep(1);
		
		$newThumbnail = crUser::updateUserProfileByPhotoURL($userID['id'], $photo);
		$thumb="";
	} else {
		
		$newThumbnail ="Resize";
		$thumb ="Resize";
		//cr_add_message("Photo shall resized",MSG_TYPE_NOTIFY);
	}
	
	render_result_xml(['status' => $newThumbnail ? 'success' : 'error', 'message' => cr_get_messages(), 'url' => $photo, 'thumb' => $thumb, 'width'=>$width, 'height'=>$height]);
	
}
?>