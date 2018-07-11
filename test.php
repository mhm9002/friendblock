<?php
		require_once('/includes/boot.php');

		$userID = ['id'=>'40'];
		
		
		$thumbFilename= '1.jpg';
		$thumbName = DIR_IMG.'/users/'.$userID['id'].'/'.cr_encrypt($userID['id']).'-resized/'.$thumbFilename;
		
		if (!is_dir(DIR_IMG.'/users/'.$userID['id'].'/'.cr_encrypt($userID['id']).'-resized')){
			mkdir(DIR_IMG.'/users/'.$userID['id'].'/'.cr_encrypt($userID['id']).'-resized',0777);
		}
		
		for ($x=2;file_exists($thumbName);$x++){			
			$thumbFilename = $x.'.jpg';
			$thumbName = DIR_IMG.'/users/'.$userID['id'].'/'.cr_encrypt($userID['id']).'-resized/'.$thumbFilename;
		}
				
				
		$cX = $_POST['cX'];
		$cY = $_POST['cY'];
		$h = $_POST['iH'];
		$w = $_POST['iW'];	
		
		$photo = imagecreatefromjpeg($_POST['photo']);

		//$photo = imagecreatefromjpeg(DIR_IMG.'/users/'.$userID['id'].'/d61CD6aDkRKc/Image4.jpg');
		
		$newPhoto = imagecreatetruecolor(200, 200);
		
		if (imagecopyresampled($newPhoto, $photo, 0, 0, $cX, $cY, 200,200, $w, $h)) {
		$saved = imagejpeg($newPhoto, $thumbName, 90);			
		var_dump($saved);
		}

		
		
?>