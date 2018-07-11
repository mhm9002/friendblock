<?php 
require(dirname(__FILE__) . '/includes/boot.php');

$output ="";
$totalLetterInRow=0;

if (isset($_POST) && isset($_FILES['image']) ){
    ob_start();
	$errors = array();
	
	$file_size = $_FILES['image']['size'];
	$file_tmp = $_FILES['image']['tmp_name'];
	$file_type= $_FILES['image']['type'];
    
    $par = explode('.',$_FILES['image']['name']);   
	$file_ext = strtolower(end($par));
	$extensions = $SITE_GLOBALS['imageTypes']; 		
    
    $targetPath = DIR_IMG_TMP; 
    $file_name = time().'-'.cr_generate_random_string(12);


    //define letter used and threshold
    $colorMode =$_POST['colorMode'];

    $lettersUsed = $_POST['lettersUsed'];
    
    if ($colorMode=='6')
        $lettersUsed = strrev($lettersUsed).$lettersUsed;

    $threshold = 1/strlen($lettersUsed);

    //check image errors
	if(in_array($file_ext, $extensions)=== false)
		$errors[]="extension not allowed, please choose a JPEG or PNG file.";
	
	if($file_size > 4048576)
		$errors[]='File size grater than 4 MB';
	
	if(empty($errors)==true){	
		$imageSize = getimagesize($file_tmp);

        //define total letter per row/col
        if (isset($_POST['pixelsPerLetter'])){
            $totalLetterInRow = intval($imageSize[0]/$_POST['pixelsPerLetter']);
            $totalLetterInCol = intval($imageSize[1]/$_POST['pixelsPerLetter']);
        } else {
            $r = intval($_POST['lettersPerRow']);
            $totalLetterInRow = $r;
            $totalLetterInCol = intval(($r*$imageSize[1])/$imageSize[0]);    
        }

        //generate mini photo
        cr_resize_image($file_tmp,$targetPath . "/" . $file_name ,$file_type,
            $totalLetterInRow,$totalLetterInCol,
            0,0,$imageSize[0],$imageSize[1]);
            
        $image = cr_image_open($targetPath.'/'.$file_name, $file_type);

        //$myfile = fopen($targetPath.'/'.$file_name.".txt", "w") or die("Unable to open file!");

        //write the output photo
        header('Content-type: image/jpeg');
        $jpg_image = imagecreatetruecolor(800,800*($totalLetterInCol/$totalLetterInRow)); //($totalLetterInRow*8,$totalLetterInCol*16);
        $white = imagecolorallocate($jpg_image, 255, 255, 255);

        //white background
        if ($colorMode=='1' || $colorMode=='3' || $colorMode=='5')
            imagefilledrectangle($jpg_image,0,0,800,800*($totalLetterInCol/$totalLetterInRow),$white);
        
        $font_path = 'monos.ttf';
        $font_size = 800/$totalLetterInRow;

        $x=0;
        $y=$font_size;

        for ($i=0; $i<$totalLetterInCol;$i++){
            for ($j=0; $j<$totalLetterInRow;$j++){
                $pixelColor = imagecolorat($image,$j,$i);
                if ($pixelColor===false)
                    $pixelColor='FFFFFF';

                $hslArry = indexToHsl($pixelColor);
                $lum =  floor($hslArry[2]/$threshold);

                if ($lum==strlen($lettersUsed))
                    $lum--;
                $letter = substr ($lettersUsed,$lum,1);
                //fwrite($myfile, $letter);
                //$output .= $letter;
                
                //coloring options
                $c=0;
                
                switch ($colorMode) {
                    case '1':
                        $c = hslToRgb(0,0, 240*$lum*$threshold);    
                        break;
                    case '2':
                        $c = hslToRgb(0,0, 240*(1-($lum*$threshold)));    
                        break;
                    case '3':
                        $c = hslToRgb(0,0, 0);    
                        break;
                    case '4':
                        $c = hslToRgb(0,0, 239);    
                        break;
                }

                if ($colorMode=='6' && $hslArry[2]>0.5){
                    imagefilledrectangle($jpg_image,$x,($y-$font_size),$x+$font_size,$y,$white);
                    
                    $c = hslToRgb(0,0, 240*(($lum-((1/$threshold)/2))*(2*$threshold)));    
                } else {
                    $c = hslToRgb(0,0, 240*($lum*(2*$threshold)));
                }

                $co = ($colorMode=='5')?$pixelColor:imagecolorallocate($jpg_image,$c[0],$c[1],$c[2]);

                //$c = hslToRgb(0,0, 240*$lum*$threshold); //240*(1-($lum*$threshold))); //0);
                //$co = imagecolorallocate($jpg_image,$c[0],$c[1],$c[2]);
                $f_size = ($colorMode=='6')?$font_size+(1-($lum*$threshold)):$font_size;
                
                imagettftext($jpg_image,$f_size,0,$x,$y,$co,$font_path,$letter);
    
                $x+=$font_size;
            }
            $x=0;
            $y+=$font_size;
            //fwrite($myfile, PHP_EOL);
            //$output.= PHP_EOL;
        }

        
        imagejpeg($jpg_image,$targetPath.'/'.$file_name.'.'.$file_ext,100);
        
        imagedestroy($jpg_image);
        imagedestroy($image);
        
        ob_end_clean();
        
        render_result_xhr(['status' => file_exists($targetPath.'/'.$file_name.'.'.$file_ext)?'success' : 'error', 'output' => $targetPath.'/'.$file_name.'.'.$file_ext]);
        
        exit;    
            //fclose($myfile);
        //Set the Content Type
        //header('Content-type: image/jpeg');

        // Create Image From Existing File
        //$jpg_image = imagecreatetruecolor($totalLetterInRow*8,$totalLetterInCol*16); //imagecreatefromjpeg();

        // Allocate A Color For The Text
        //$white = imagecolorallocate($jpg_image, 255, 255, 255);

        // Set Path to Font File
        //$font_path = 'monos.ttf';
        
        // Print Text On Image
        //imagettftext($jpg_image,8,0,0,0,$white,$font_path,$output);

        // Send Image to Browser
        //imagejpeg($jpg_image,$targetPath.'/'.$file_name.'.'.$file_ext,100);

        // Clear Memory
        //imagedestroy($jpg_image);
        
    }
}


?>