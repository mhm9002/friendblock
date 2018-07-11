<?php
/**
 * Upload Photo Using Jquery uploadify plugin
 */
require(dirname(__FILE__) . '/includes/boot.php');

$userID = cr_is_logged_in();

if(isset($_POST) == true){

	$targetPath = DIR_IMG_TMP . "/users/".$userID['id'].'/'.$_POST['folder'];
	
	if (!is_dir(DIR_IMG_TMP . "/users/".$userID['id'])){
		mkdir(DIR_IMG_TMP . "/users/".$userID['id'], 0777);
	}
		
	if(!is_dir($targetPath)){
		mkdir($targetPath, 0777);
			//Create Index file
		//$fp = fopen($targetPath . "/index.html", "w");
		//fclose($fp);
	}

	$errors = array();
	
	$file_size = $_FILES['image']['size'];
	$file_tmp = $_FILES['image']['tmp_name'];
	$file_type= $_FILES['image']['type'];
	$par = explode('.',$_FILES['image']['name']);   
	$file_ext = strtolower(end($par));
	$extensions = $SITE_GLOBALS['imageTypes']; 		
	$file_name = $_POST['pID'].'.'.$file_ext;

	if(in_array($file_ext, $extensions)=== false)
		$errors[]="extension not allowed, please choose a JPEG or PNG file.";
	
	if($file_size > 4048576)
		$errors[]='File size grater than 4 MB';
	
	if(empty($errors)==true){
		
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
			
			cr_crop_for_thumbnail($param, $userID['id'],$file_type);
			
		} else {
			move_uploaded_file($file_tmp, $targetPath . "/" . $file_name);
		
			$param = ['cX'=>0,'cY'=>0,
				'iW'=>$imageSize[1],
				'iH'=>$imageSize[1],
				'photo'=>$targetPath . "/" . $file_name,
				'save_folder'=> $targetPath,
				'file_name'=> $file_name];
				
			cr_crop_for_thumbnail($param, $userID['id'],$file_type);
		}

		//adding photo to DB
		$pID= crAlbum::savePhoto($userID['id'],$_POST['folder'],$file_name,$targetPath."/".$file_name);
		
		render_result_xhr(['status'=>'success', 'photo'=>$pID]);
	}else{
		$myfile = fopen("log.txt", "w") or die("Unable to open file!");
		$txt = implode("\n", $errors);
		fwrite($myfile, $txt);
		fclose($myfile);
	}
}
