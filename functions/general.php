	<?php

/**
 * Including Common functions that will be used whole site
 */

/**
 * Add Javascript to $SITE_GLOBAL['javascripts']
 *
 * @param String  $script
 * @param Boolean $is_absolute_path
 * @param Int     $position
 */
function cr_enqueue_javascript($script, $is_absolute_path = FALSE, $is_footer = TRUE, $position = null){
    global $SITE_GLOBALS;

    if(!isset($SITE_GLOBALS['javascripts']))
        $SITE_GLOBALS['javascripts'] = [];

    if(!$is_absolute_path)
        $script = DIR_JS .'/'. $script;

    //Check already added or not
    foreach($SITE_GLOBALS['javascripts'] as $row){
        if($row['src'] == $script)
            return;
    }

    if($position === null || $position >= count($SITE_GLOBALS['javascripts'])){
        array_push($SITE_GLOBALS['javascripts'], ['src' => $script, 'is_footer' => $is_footer]);
    }else{
        $js = [];
        for($i = 0; $i < count($SITE_GLOBALS['javascripts']); $i++){
            if($i == $position)
                $js[] = ['src' => $script, 'is_footer' => $is_footer];
            $js[] = $SITE_GLOBALS['javascripts'][$i];
        }
        $SITE_GLOBALS['javascripts'] = $js;
    }
}

/**
 * Add Stylesheet to $SITE_GLOBAL['stylesheets']
 *
 * @param String  $stylesheet
 * @param Boolean $is_absolute_path
 * @param Int     $position
 */
function cr_enqueue_stylesheet($stylesheet, $is_absolute_path = FALSE, $position = null){
    global $SITE_GLOBALS;

    if(!isset($SITE_GLOBALS['stylesheets']))
        $SITE_GLOBALS['stylesheets'] = [];

    if(!$is_absolute_path)
        $stylesheet = DIR_CSS .'/'. $stylesheet;

    if($position === null || $position >= count($SITE_GLOBALS['stylesheets'])){
        array_push($SITE_GLOBALS['stylesheets'], $stylesheet);
    }else{
        $sh = [];
        for($i = 0; $i < count($SITE_GLOBALS['stylesheets']); $i++){
            if($i == $position)
                $sh[] = $stylesheet;
            $sh[] = $SITE_GLOBALS['stylesheets'][$i];
        }
        $SITE_GLOBALS['stylesheets'] = $sh;
    }
}

/**
 * Render Scripts from $SITE_GLOBALS['javascripts'] variable
 */
function cr_render_javascripts($is_footer = TRUE){
    global $SITE_GLOBALS;

    if(isset($SITE_GLOBALS['javascripts'])){
        if(!is_array($SITE_GLOBALS['javascripts']))
            $SITE_GLOBALS['javascripts'] = [$SITE_GLOBALS['javascripts']];
        //        $SITE_GLOBALS['javascripts'] = array_unique($SITE_GLOBALS['javascripts']);

        foreach($SITE_GLOBALS['javascripts'] as $row){
            if($row['is_footer'] != $is_footer)
                continue;

            echo "<script type='text/javascript' src='" . $row['src'] . "' ></script>" . PHP_EOL;
        }
    }
}

/**
 * Render Stylesheets from $SITE_GLOBALS['stylesheets'] variable
 */
function cr_render_stylesheet(){
    global $SITE_GLOBALS;

    if(isset($SITE_GLOBALS['stylesheets'])){
        if(!is_array($SITE_GLOBALS['stylesheets']))
            $SITE_GLOBALS['stylesheets'] = [$SITE_GLOBALS['stylesheets']];
        $SITE_GLOBALS['stylesheets'] = array_unique($SITE_GLOBALS['stylesheets']);
        foreach($SITE_GLOBALS['stylesheets'] as $src){
            echo "<link rel='stylesheet' type='text/css' href='" . $src . "' >" . PHP_EOL;
        }
    }
}

/**
 * Check if current user is logged in
 *
 * @return loggedin = TRUE, else FALSE
 */
function cr_is_logged_in(){
    global $db;

    if(isset($_SESSION['id'])){
        $userID = intval($_SESSION['id']);
        //Check the UserId exits in the database
        $query = $db->prepare("SELECT * FROM users WHERE id=%s AND status = 1", $userID);
        $urow = $db->getRow($query);

        if(!$urow) //If userid doesn't exist in the database, remove it from the session
        {
            $_SESSION['id'] = null;
            unset($_SESSION['id']);
            return FALSE;
        }else if($urow['status'] != 1){
            $_SESSION['id'] = null;
            unset($_SESSION['id']);
            cr_add_message(MSG_ACCOUNT_NOT_ACTIVE, MSG_TYPE_ERROR);
            return FALSE;
        }
        return $urow;
    }else{
        return cr_check_cookie_for_login();

    }
}

/**
 * Check Cookie values for keep me signed in

 */
function cr_check_cookie_for_login(){
    global $db;

    if(isset($_COOKIE['COOKIE_KEEP_ME_NAME1']) && isset($_COOKIE['COOKIE_KEEP_ME_NAME2']) && isset($_COOKIE['COOKIE_KEEP_ME_NAME3'])){
        $token1 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME1']);
        $token3 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME2']);
        $token2 = base64_decode($_COOKIE['COOKIE_KEEP_ME_NAME3']);

        $login_token = md5($token1 . $token2 . $token3);

        if(($userID = crUsersToken::checkTokenValidity($login_token, "auth"))){
            $query = $db->prepare("SELECT id FROM users WHERE id=%s AND status=Active", $userID);
            $userID = $db->getVar($query);

            if($userID){
                $_SESSION['id'] = $userID;
                //Init Some Session Values
                $_SESSION['converation_list'] = [];
                return $userID;
            }
        }

        //Remove Cookies
        setcookie('COOKIE_KEEP_ME_NAME1', null, time() - 1000, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME2', null, time() - 1000, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME3', null, time() - 1000, "/", DOMAIN);

    }

    return FALSE;
}

/**
 * Redirect to the url
 * If $msg is not null, set the message to the session
 *
 * @param String $url
 * @param String $msg
 * @param int    $msg_type : MSG_TYPE_SUCCESS(1)=success, MSG_TYPE_ERROR(0)=error, MSG_TYPE_NOTIFY(2)=notification
 */
function cr_redirect($url, $msg = null, $msg_type = MSG_TYPE_SUCCESS){
    if($msg){
        cr_add_message($msg, $msg_type);
    }
    header("Location: " . $url);
    exit;
}

/**
 * check the value is null or not
 *
 * @param mixed $value
 * @return bool
 */
function cr_not_null($value){
    if(is_array($value)){
        if(sizeof($value) > 0){
            return TRUE;
        }else{
            return FALSE;
        }
    }else{
        if((is_string($value) || is_int($value)) && ($value != '') && (strlen(trim($value)) > 0)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}

/**
 * Get User Full Info By Email
 *
 * @param mixed $email
 * @return array
 */
function cr_get_user_by_email($email){
    global $db;

    $query = $db->prepare('SELECT * FROM users WHERE email=%s AND status=1', $email);
    $row = $db->getRow($query);

    return $row;
}

/**
 * Save the message to the session
 *
 * @param String $msg
 * @param int    $msg_type : MSG_TYPE_SUCCESS(1)=success, MSG_TYPE_ERROR(0)=error, MSG_TYPE_NOTIFY(2)=notification
 */
function cr_add_message($msg, $msg_type = MSG_TYPE_SUCCESS){

    if(!isset($_SESSION['message'])){
        $_SESSION['message'] = [];
    }
    
    $_SESSION['message'][] = ['type' => $msg_type, 'message' => htmlentities($msg, ENT_QUOTES)];
}

//Getting Result Messages 
/**
 * @return string
 */
function cr_get_messages(){
    ob_start();
    render_result_messages();
    $msg = ob_get_contents();
    ob_end_clean();
    return $msg;
}

/**
 * Getting pure message string from session
 * This will be used on API section

 */
function cr_get_pure_messages(){
    $message_string = "";

    if(isset($_SESSION['message']) && cr_not_null($_SESSION['message'])){
        for($i = 0; $i < sizeof($_SESSION['message']); $i++){
            if($message_string)
                $message_string .= "\n\r";

            $message_string .= $_SESSION['message'][$i]['message'];
        }
        unset($_SESSION['message']);
    }

    return $message_string;
}

//Create Image Object
/**
 * @param $file
 * @param $type
 * @return bool
 */
function cr_image_open($file, $type){
    // @rule: Test for JPG image extensions
    if(function_exists('imagecreatefromjpeg') && (($type == 'image/jpg') || ($type == 'image/jpeg') || ($type == 'image/pjpeg'))){
        $im = @imagecreatefromjpeg($file);

        if($im !== FALSE){
            return $im;
        }
    }

    // @rule: Test for png image extensions
    if(function_exists('imagecreatefrompng') && (($type == 'image/png') || ($type == 'image/x-png'))){
        $im = @imagecreatefrompng($file);

        if($im !== FALSE){
            return $im;
        }
    }

    // @rule: Test for png image extensions
    if(function_exists('imagecreatefromgif') && (($type == 'image/gif'))){
        $im = @imagecreatefromgif($file);

        if($im !== FALSE){
            return $im;
        }
    }

    if(function_exists('imagecreatefromgd')){
        # GD File:
        $im = @imagecreatefromgd($file);
        if($im !== FALSE){
            return TRUE;
        }
    }

    if(function_exists('imagecreatefromgd2')){
        # GD2 File:
        $im = @imagecreatefromgd2($file);
        if($im !== FALSE){
            return TRUE;
        }
    }

    if(function_exists('imagecreatefromwbmp')){
        # WBMP:
        $im = @imagecreatefromwbmp($file);
        if($im !== FALSE){
            return TRUE;
        }
    }

    if(function_exists('imagecreatefromxbm')){
        # XBM:
        $im = @imagecreatefromxbm($file);
        if($im !== FALSE){
            return TRUE;
        }
    }

    if(function_exists('imagecreatefromxpm')){
        # XPM:
        $im = @imagecreatefromxpm($file);
        if($im !== FALSE){
            return TRUE;
        }
    }

    // If all failed, this photo is invalid
    return FALSE;
}

//Resize Image
/**
 * @param     $srcPath
 * @param     $destPath
 * @param     $destType
 * @param     $destWidth
 * @param     $destHeight
 * @param int $sourceX
 * @param int $sourceY
 * @param int $currentWidth
 * @param int $currentHeight
 * @return bool
 */
function cr_resize_image($srcPath, $destPath, $destType, $destWidth, $destHeight, $sourceX = 0, $sourceY = 0, $currentWidth = 0, $currentHeight = 0, $imgQuality=320){
    //$imgQuality = 70; //320;
    $pngQuality = ($imgQuality - 100) / 11.111111;
    $pngQuality = round(abs($pngQuality));
	
    // See if we can grab image transparency    
    $image = cr_image_open($srcPath, $destType);

    // Create new image resource
    $image_p = ImageCreateTRUEColor($destWidth, $destHeight);
    
    try {
        $exif = exif_read_data($srcPath);
        
        if (!empty($exif['Orientation'])) {
            switch ($exif['Orientation']) {
                case 3:
                    $image = imagerotate($image, 90, 0);
                    break;
                case 6:
                    $image = imagerotate($image, -90, 0);
                    break;
                case 8:
                    $image = imagerotate($image, -180, 0);
                    break;
            } 
        }
    } catch (Exception $e) {
        //do nothing, just ignore
    }


	$transparentIndex = imagecolortransparent($image);
	$background = ImageColorAllocate($image_p, 255, 255, 255);
    
    // test if memory is enough
    if($image_p == FALSE){
        echo 'Image resize fail. Please increase PHP memory';
        return FALSE;
    }

    // Set the new image background width and height
    $resourceWidth = $destWidth;
    $resourceHeight = $destHeight;

    if(empty($currentHeight) && empty($currentWidth)){
        list($currentWidth, $currentHeight) = getimagesize($srcPath);
    }
    // If image is smaller, just copy to the center
    $targetX = 0;
    $targetY = 0;

    // If the height and width is smaller, copy it to the center.
    if($destType != 'image/jpg' && $destType != 'image/jpeg' && $destType != 'image/pjpeg'){
        if(($currentHeight < $destHeight) && ($currentWidth < $destWidth)){
            $targetX = intval(($destWidth - $currentWidth) / 2);
            $targetY = intval(($destHeight - $currentHeight) / 2);

            // Since the 
            $destWidth = $currentWidth;
            $destHeight = $currentHeight;
        }
    }
    $targetX = floor($targetX);
    $targetY = floor($targetY);
    $sourceX = floor($sourceX);
    $sourceY = floor($sourceY);
    $destWidth = floor($destWidth);
    $destHeight = floor($destHeight);
    $currentWidth = floor($currentWidth);
    $currentHeight = floor($currentHeight);
    
    // Resize GIF/PNG to handle transparency
    if($destType == 'image/gif'){
        $colorTransparent = imagecolortransparent($image);
        imagepalettecopy($image, $image_p);
        imagefill($image_p, 0, 0, $colorTransparent);
        imagecolortransparent($image_p, $colorTransparent);
        imageTRUEcolortopalette($image_p, TRUE, 256);
        imagecopyresized($image_p, $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth, $destHeight, $currentWidth, $currentHeight);
    }else if($destType == 'image/png' || $destType == 'image/x-png'){
        // Disable alpha blending to keep the alpha channel
        imagealphablending($image_p, FALSE);
        imagesavealpha($image_p, TRUE);
        $transparent = imagecolorallocatealpha($image_p, 255, 255, 255, 127);

        imagefilledrectangle($image_p, 0, 0, $resourceWidth, $resourceHeight, $transparent);
        imagecopyresampled($image_p, $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth, $destHeight, $currentWidth, $currentHeight);
    }else{
        // Turn off alpha blending to keep the alpha channel
        imagealphablending($image_p, FALSE);
        imagecopyresampled($image_p, $image, $targetX, $targetY, $sourceX, $sourceY, $destWidth, $destHeight, $currentWidth, $currentHeight);
    }

    // Test if type is png    
    if($destType == 'image/png' || $destType == 'image/x-png'){
        imagepng($image_p, $destPath);
    }elseif($destType == 'image/gif'){
        imagegif($image_p, $destPath);
    }else{
        // We default to use jpeg
        imagejpeg($image_p, $destPath, $imgQuality);
    }

    return true;
}

/**
 * @param      $content
 * @param null $length
 * @return mixed|string
 */
function cr_trunc_content($content, $length = null){
    //remove Youtube Url
    $pattern = "/\[youtube.*\](.*)\[\/youtube\]/i";
    $content = preg_replace($pattern, '$1', $content);
    if($length != null && strlen($content) > $length){
        return substr($content, 0, $length) . "...";
    }else{
        return $content;
    }
}

/**
 * @param $url
 * @return mixed
 */
function cr_get_youtube_video_id($url){
    $url = str_replace('&amp;', '&', $url);
    if(strpos($url, 'http://www.youtube.com/embed/') !== FALSE || strpos($url, 'https://www.youtube.com/embed/') !== FALSE) // If Embed URL
    {
        return str_replace(['http://www.youtube.com/embed/', 'https://www.youtube.com/embed/'], ['', ''], $url);
    }

    if(strpos($url, 'http://youtu.be/') !== FALSE || strpos($url, 'https://youtu.be/') !== FALSE) // If Embed URL
        {
            return str_replace(['http://youtu.be/', 'https://youtu.be/'], ['', ''], $url);
    }
    
    parse_str(parse_url($url, PHP_URL_QUERY), $array_of_vars);
    if (isset($array_of_vars['v'])){
        return $array_of_vars['v'];
    }
        
    return FALSE;
}

/**
 * @param $to
 * @param $toName
 * @param $subject
 * @param $body
 * @throws Exception
 * @throws phpmailerException
 */
function cr_sendmail($to, $toName, $subject, $body){
    //require_once(DIR_INC . "/phpMailer/class.exception.php");
	require_once(DIR_INC . "/phpMailer/class.phpmailer.php");
	require_once(DIR_INC . "/phpMailer/class.smtp.php");
	
    $mail = new PHPMailer();

	try {
		
		//tls or ssl
		//if(SITE_USING_SSL)
		//	$mail->SMTPSecure = 'ssl';
		//else
			$mail->SMTPSecure = 'tls';

		//$mail->SMTPDebug = 4;
	    //$mail->IsSMTP();
	    $mail->SMTPAuth = TRUE;
	    
	    $mail->Port = SMTP_PORT;
	    $mail->Host = SMTP_HOST;
	    $mail->Username = SMTP_USERNAME;
	    $mail->Password = SMTP_PASSWORD;

		$mail->IsHTML(TRUE);
	    $mail->AddAddress($to, $toName);
	    $mail->SetFrom(NT_ADMIN_EMAIL, SITE_NAME . ' - Support');
	    $mail->Subject = $subject;
	    $mail->Body = $body;

	    $mail->Send();
	} catch (Exception $e) {
		echo 'Message could not be sent.';
    	echo 'Mailer Error: ' . $mail->ErrorInfo;
		return FALSE;
	}

	return TRUE;
}

/**
 * Include Panel
 *
 * @param String $panel
 */
function cr_get_panel($panel, $params = []){
    global $SITE_GLOBALS;

    if(file_exists(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/panel/" . $panel . ".php")){
        if(!empty($params)){
            extract($params, EXTR_PREFIX_SAME, "var");
        }
        require_once(DIR_TEMPLATE .'/'. $SITE_GLOBALS['template'] . "/panel/" . $panel . ".php");
    }
}

/**
 * Validate the Youtube Video Id
 *
 * @param $youtubeURL
 * @return bool
 */
function cr_validate_youtube_url($youtubeURL){
    $youtubeID = trim(cr_get_youtube_video_id($youtubeURL));

    if(!$youtubeID) {
        return FALSE;
    }

    $url = 'http://gdata.youtube.com/feeds/api/videos/' . $youtubeID;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);
    
    if(strtolower(trim($result)) == 'invalid id')
        return FALSE;else
        return TRUE;
}

/**
 * Store Session before redirect

 */
function cr_exit(){
    cr_session_close();
    exit;
}

/**
 * Encrypt ID for security

 */
function cr_encrypt_id($gID){
    if(!isset($_SESSION['user_encrypt_salt'])){
        $salt = '';
        for($i = 0; $i < 20; $i++){
            $salt .= mt_rand() . time();
        }

        $salt = md5($salt);

        $_SESSION['user_encrypt_salt'] = $salt;
    }else{
        $salt = $_SESSION['user_encrypt_salt'];
    }

    $encrypted = md5($salt . $gID . $salt);

    return $encrypted;
}

/**
 * Check ID Encrypted Value
 */
function cr_check_id_encrypted($gID, $encrypted){
    if(!isset($_SESSION['user_encrypt_salt'])){
        return FALSE;
        /*if( $userID != $encrypted )
            return FALSE;
        else
            return TRUE;*/
    }else{
        if(cr_encrypt_id($gID) == $encrypted)
            return TRUE;else
            return FALSE;
    }
}

/**
 * Get country by ID
 *
 * @param integer $countryID
 */
function fn_cr_get_country_name($countryID){
    $countryIns = new crCountry();
    $countryData = $countryIns->getCountryById($countryID);

    if($countryData){
        return $countryData['country_title'];
    }
    return;
}

/**
 * @param      $str
 * @param bool $urlDecode
 * @return string
 */
function get_secure_string($str, $urlDecode = FALSE){

    if($urlDecode){
        return trim(urldecode(strip_tags($str)));
    }else{
        return trim(strip_tags($str));
    }
}

/**
 * @param $string
 * @return mixed
 */
function cr_escape_query_string($string){
    //global $db;

    $converts = ['<' => '&lt;', '>' => '&gt;', "'" => '&#039;', '"' => '&quot;'];

    $string = str_replace(array_keys($converts), array_values($converts), $string);

    return $string;

}

/**
 * @param $val
 * @return array|int|null
 */
function cr_escape_query_integer($val){
    if(is_array($val)){
        $nVal = [];
        foreach($val as $i => $v){
            if(is_numeric($v))
                $nVal[] = intval($v);
        }
        return $nVal;
    }else{
        if(is_numeric($val))
            return intval($val);else
            return null;
    }

}

/**
 * @param $str
 * @return int|null
 */
function get_secure_integer($str){
    if(is_numeric($str))
        return intval($str);else
        return null;
}

/**
 * @param      $string
 * @param      $length
 * @param bool $stripHTML
 * @return string
 */
function cr_truncate_string($string, $length, $stripHTML = TRUE){
    if($stripHTML == TRUE)
        $string = strip_tags($string);

    $offset = 3;

    if(strlen($string) < $length - $offset)
        return $string;

    return substr($string, 0, $length - $offset) . '...';
}

/**
 * Turn all URLs in clickable links.
 * 
 * @param string $value
 * @param array  $protocols  http/https, ftp, mail, twitter
 * @param array  $attributes
 * @param string $mode       normal or all
 * @return string
 */
function cr_make_links_clickable ($value, $protocols = array('http', 'mail','hashtag'), array $attributes = array())
{
    // Link attributes
    $attr = '';
    foreach ($attributes as $key => $val) {
        $attr = ' ' . $key . '="' . htmlentities($val) . '"';
    }
    
    $links = array();
    
    // Extract existing links and tags
    $value = preg_replace_callback('~(<a .*?>.*?</a>|<.*?>)~i', function ($match) use (&$links) { return '<' . array_push($links, $match[1]) . '>'; }, $value);
    
    // Extract text links for each protocol
    foreach ((array)$protocols as $protocol) {
        switch ($protocol) {
            case 'http':
            case 'https':   $value = preg_replace_callback('~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { if ($match[1]) $protocol = $match[1]; $link = $match[2] ?: $match[3]; return '<' . array_push($links, "<a $attr href=\"$protocol://$link\">$link</a>") . '>'; }, $value); break;
            case 'mail':    $value = preg_replace_callback('~([^\s<]+?@[^\s<]+?\.[^\s<]+)(?<![\.,:])~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"mailto:{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
            case 'twitter': $value = preg_replace_callback('~(?<!\w)[@#](\w++)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, "<a $attr href=\"https://twitter.com/" . ($match[0][0] == '@' ? '' : 'search/%23') . $match[1]  . "\">{$match[0]}</a>") . '>'; }, $value); break;
            case 'hashtag': $value = preg_replace_callback('~(^|\\s)#(\\w*[a-zA-Z0-9_]+\\w*)~', function ($match) use (&$links, $attr) { return '<' . array_push($links, ($match[0][0]==" "? "&nbsp;":"")."<a $attr href=\"/hashtag.php?tag=" . ($match[0][0]==" "? substr($match[0],2,strlen($match[0])-2):substr($match[0],1,strlen($match[0])-1)) . "\">".($match[0][0]==" "? substr($match[0],1,strlen($match[0])-1):$match[0])."</a>") . '>'; }, $value); break;
            default:        $value = preg_replace_callback('~' . preg_quote($protocol, '~') . '://([^\s<]+?)(?<![\.,:])~i', function ($match) use ($protocol, &$links, $attr) { return '<' . array_push($links, "<a $attr href=\"$protocol://{$match[1]}\">{$match[1]}</a>") . '>'; }, $value); break;
        }
    }
    
    // Insert all link
    return preg_replace_callback('/<(\d+)>/', function ($match) use (&$links) { return $links[$match[1] - 1]; }, $value);
}

/**
 * @param int  $length
 * @param bool $complicated
 * @return string
 */
function cr_generate_random_string($length = 8, $complicated = FALSE){
    $alphabets = 'abcdefghijklmnopqrstuvwABCDEFGHIJKLMKOPQRSTUVW1234567890';
    if($complicated)
        $alphabets .= '!@#$%^&*()';

    $str = '';
    for($i = 0; $i < $length; $i++)
        $str .= $alphabets[mt_rand(0, strlen($alphabets) - 1)];

    return $str;
}

/**
 * @param $password
 * @return bool
 */
function cr_check_password_strength($password){
    //Password should be more than 8 characters
    if(strlen($password) < 8){
        return FALSE;
    }

    //Should include at least 1 number
    if(!preg_match('/[0-9]+/', $password)){
        return FALSE;
    }

    return TRUE;
}

/**
 * Get Secure Token for the site security
 *
 * @param mixed $forceNew
 * @return null|string
 */
function cr_get_form_token($forceNew = FALSE){
    $token = isset($_SESSION['form.token']) ? $_SESSION['form.token'] : null;

    if($token === null || $forceNew){
        $token = cr_generate_random_string(12);
        $session_name = session_name();
        $token = md5($token . $session_name);
        $_SESSION['form.token'] = $token;
    }

    return $token;
}

/**
 * Check token exists or not
 *
 * @param mixed $method
 * @return bool
 */
function cr_check_form_token($method = 'post'){
    $token = cr_get_form_token();
    if($method == 'post'){
        if(isset($_POST[$token]) && $_POST[$token] == 1)
            return TRUE;
    }else if($method == 'get'){
        if(isset($_GET[$token]) && $_GET[$token] == 1)
            return TRUE;
    }else if($method == 'request'){
        if(isset($_REQUEST[$token]) && $_REQUEST[$token] == 1)
            return TRUE;
    }
    return FALSE;
}

/**
 * Token URL Param
 *
 * @param bool $forceNew
 * @return string
 */
function cr_get_token_param($forceNew = FALSE){
    return '&' . cr_get_form_token($forceNew) . "=1";
}

/**
 * @param $content
 * @return mixed
 */
function cr_remove_tags_inside_code($content){
    $pattern = "/\[code\](((?!\[code\]).)+)\[\/code\]/ims";
    $content = preg_replace_callback($pattern, '_cr_remove_html_tags', $content);

    return $content;
}

/**
 * @param $matches
 * @return string
 * @throws Exception
 */
function _cr_remove_html_tags($matches){
    //Convert HTML Codes
    $bbcodeParser = new crBBCodeNodeContainerDocument();
    $string = $matches[1];
    $html = $bbcodeParser->parse($string)->detect_links()->detect_emails()->detect_emoticons()->get_html();

    $string = _escape_brackets($string);

    return strip_tags('[code]' . $string . '[/code]');
}

/**
 * @param $content
 * @return mixed
 */
function _escape_brackets($content){
    $content = str_replace(["[", "]"], ["&#91;", "&#93;"], $content);
    return $content;
}

/**
 * @param $content
 * @return mixed
 */
function cr_remove_invalid_image_urls($content){
    $pattern = "/\[img\](((?!\[img\]).)+)\[\/img\]/im";

    $content = preg_replace_callback($pattern, '_cr_remove_invalid_image_urls', $content);

    return $content;
}

/**
 * @param $matches
 * @return string
 */
function _cr_remove_invalid_image_urls($matches){
    global $SITE_GLOBALS;

    $info = pathinfo($matches[1]);
    //Check image or not
    if(!in_array(strtolower($info['extension']), $SITE_GLOBALS['imageTypes'])){
        return '';
    }else{
        return $matches[0];
    }
}

/**
 * @param      $userID
 * @param bool $first_only
 * @return string
 */
function cr_get_user_name($userID){
    global $db;

    $query = $db->prepare("SELECT nick FROM " . TABLE_USERS . " WHERE userID=%d", $userID);
    $row = $db->getRow($query);

    if(!$row)
        return "";

    return $row['nick'];
}


/**
 * Retrieve image by ID 
 * @param      $imageRef
 * 
 * @return array
 */

function cr_retrieve_image_data($imageRef){
	global $db;
	
	$query = $db->prepare('SELECT * FROM '.TABLE_PHOTOS.' WHERE id=%d', $imageRef);
	$result = $db->getRow($query);
	
	return $result;
	
}

/**
 * Combine activities with the same object 
 * @param  $rows
 * 
 * @return $rows
 */


function cr_activities_combine($rows){
	
    $act = [];
    $missingRequests=[];
    $missingTweets =[];

	foreach ($rows as $row){
        //validate object
        if ($row['objectType']=="TWEET" && in_array($row['objectID'],$missingTweets)){
            continue;
        } elseif (in_array($row['objectID'],$missingRequests)){
            continue;
        }

        if ($row['objectType']=="TWEET"){
            if (!crTweet::getTweetById($row['objectID'])){
                $missingTweets[]=$row['objectID'];
                continue;
            }        
        } else {
            if (!crFollowship::checkRequest($row['objectID'])){
                $missingRequests[]=$row['objectID'];
                continue;
            }
        }

        if (!crUser::getUserBasicInfo($row['userID']))
            continue;

        //start combining
        $act_alias = $row['activityType'].'-'.$row['objectID'];

		if (!isset ($act[$act_alias])){
			$act[]= $act_alias;
			$act[$act_alias]['users'] =[];
			$act[$act_alias]['isNew'] = 0;
			$act[$act_alias]['createdDate']=$row['createdDate'];
		}
			
		if (!in_array($row['userID'],$act[$act_alias]['users']))
			array_push($act[$act_alias]['users'], $row['userID']);
		
		if ($row['createdDate']>$act[$act_alias]['createdDate'])
			$act[$act_alias]['createdDate']=$row['createdDate'];
		
		if ($row['isNew'])
                $act[$act_alias]['isNew']=1;
    }
    
    //validate objects
    /*
    foreach ($act as $k=>$v){
        $d = explode('-',$v);
        if (in_array($d[0],['LIKE','MENTION','RETWEET','COMMENT'])){
            //validate tweet
            if (!crTweet::getTweetById($d[1]))
                unset ($act[$v]);
        } else {
            //validate request
            if (!crFollowship::checkRequest($d[1]))
                unset ($act[$v]);
        }
    }
    */
    
	return $act;
}

function cr_crop_for_thumbnail($param, $userID, $type){
	$cX = $param['cX'];
	$cY = $param['cY'];
    $h = $param['iH'];
    $w = $param['iW'];	
    
    if (isset($param['save_folder'])){
        $save_folder = $param['save_folder'];
    } else {
        $save_folder = DIR_IMG_TMP;
    }

    if (isset($param['file_name'])){
        $filename = 'thumb-'.$param['file_name'];
        $thumb_type= $type;
    } else {
        $filename = cr_encrypt($userID).'.jpg';
        $thumb_type = 'image/jpg';
    }

    if (cr_resize_image($param['photo'],
        $save_folder.'/'.$filename,
        $thumb_type,
        IMAGE_THUMBNAIL_WIDTH,
        IMAGE_THUMBNAIL_HEIGHT,
        $cX,$cY,$w,$h))
        return $save_folder.'/'.$filename;
    
    return false;

}

function thumbnailer($userID){

    $userAlbums = crAlbum::getAlbumsByUserId($userID);

    foreach($userAlbums as $album){
        $photos = crAlbum::getAlbumPhotos($album['albumID']);

        foreach ($photos as $p){
            $path = DIR_IMG.'/users/'.$userID.'/'.$p['folder_token'];
            $file_name = $p['name'];
            $file_type = mime_content_type($path.'/'.$file_name);

            if (!file_exists($path.'/thumb-'.$file_name)) {
                
                $imageSize = getimagesize($path.'/'.$file_name);

                //protrait
                if ($imageSize[1]>$imageSize[0]){
                    $cY = ($imageSize[1]-$imageSize[0])/2;
                    $cX = 0;
                    $thumb_dim = $imageSize[0];
                } else {
                    $cX = ($imageSize[0]-$imageSize[1])/2;
                    $cY = 0;
                    $thumb_dim = $imageSize[1];
                }

				$param = ['cX'=>$cX,'cY'=>$cY,
                    'iW'=>$thumb_dim,
                    'iH'=>$thumb_dim,
                    'photo'=>$path . "/" . $file_name,
                    'save_folder'=> $path,
                    'file_name'=> $file_name];
            
                cr_crop_for_thumbnail($param, $userID, $file_type);
            
            }
        }
    }

}

function reduceQuotePhotos(){

    $folder = DIR_ROOT.'/quotePhotos';
    $photos = array_diff(scandir($folder,1), array('..', '.'));

    foreach($photos as $ph){
        $phSize = getimagesize($folder.'/'.$ph);
        $ratio = $phSize[1]/$phSize[0];

        $width = 320;
        $height = 320 * $ratio;

        $file_type = mime_content_type($folder.'/'.$ph);

        $image = cr_resize_image($folder.'/'.$ph,DIR_ROOT.'/quotePhotosThumbs/'.$ph,
            $file_type,$width,$height,0,0,$phSize[0],$phSize[1],90);
    }

}

function cr_deleteDir($dirPath) {
	    
	if (!is_dir($dirPath)) {
	    return FALSE;
	}
	if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
	    $dirPath .= '/';
	}
	$files = glob($dirPath . '*', GLOB_MARK);
	foreach ($files as $file) {
	    if (is_dir($file)) {
	        //self::deleteDir($file);
	    } else {
	        unlink($file);
		}
    }
    rmdir($dirPath);
}

/* 	Check reset password token
*	return UserID
*/

function cr_check_pwd_token($token){
	global $db;
	
	$query = $db->prepare('SELECT userID FROM '. TABLE_USERS_TOKEN. ' WHERE userToken=%s AND tokenType="password"',$token);
	$userID = $db->getVar($query);
	
	if (!$userID)
		return FALSE;
		
	return $userID;	
}

function cr_remove_pwd_token($userID, $token){
	global $db;
	
	$db->query('DELETE FROM '. TABLE_USERS_TOKEN. ' WHERE userID='.$userID.' AND userToken='.$token.' AND tokenType="password"');
	
	$data = crUser::getUserBasicInfo($userID);
	$email = crUser::getUserEmail($userID);
	
	$subject = SITE_NAME.' - Your password has been changed';
	$body = '<h2>Dear '.$data['name'].',</h2><br />Your password has been resetted as per your last visit<br />'.SITE_NAME;
	
	cr_sendmail($email,$data['name'],$subject,$body);
	
	return;
}

function cr_get_related_tags($tag){
    global $db;
    $related = [];

    $query = $db->prepare ('SELECT i.tID FROM '.TABLE_TAG_INDEX.' AS i 
        LEFT JOIN '.TABLE_HASHTAGS.' AS h ON h.hID=i.hID WHERE h.value=%s',strtolower($tag));
    
    $tIDs = $db->getResultsArray($query);

    if (!$tIDs)
        return;

    foreach ($tIDs as $tweet){
        $query = $db->prepare('SELECT hID FROM '.TABLE_TAG_INDEX.' WHERE tID=%d',$tweet); 
        $hashTags = $db->getResultsArray($query);

        foreach ($hashTags as $h){
            if (@isset($related[$h['hID']])){
                $related[$h['hID']] +=1;
            } else {
                $related[$h['hID']] =1;
            }
        }
    }

    arsort($related);
    
    $html = "";
    $x=0; 

    foreach ($related as $rTag=>$rel){
        
        $query = $db->prepare('SELECT value FROM '.TABLE_HASHTAGS.' WHERE hID=%d',$rTag);
        $val = $db->getVar($query);
        
        $html .= '<a href="/hashtag.php?tag='.$val.'">'.$val.'</a><br/>';
        
        $x+=1;

        if ($x==5)
            break;
    }

    return $html;
}

function cr_enqueue_meta($name, $content){
    global $SITE_GLOBALS;

    $meta = ['name'=>$name, 'content'=>$content];
    
    $SITE_GLOBALS['meta'][]= $meta;
}

function cr_render_meta(){
    global $SITE_GLOBALS;
    
    if (!isset($SITE_GLOBALS['meta']))
        return;

    if (sizeof($SITE_GLOBALS['meta'])==0)
        return;

    foreach ($SITE_GLOBALS['meta'] as $meta){
        echo '<meta name="'.$meta['name'].'" content="'.$meta['content'].'">';
    }

    $SITE_GLOBALS['meta'] = [];
}

function cr_check_S_Login($OauthType, $OauthEmail, $OauthID){
    global $db;

    $query = $db->prepare ('SELECT sID FROM '.TABLE_USERS_S_LOGIN.' 
    WHERE OauthType=%s AND OauthID=%s AND OauthEmail=%s AND uID>0',$OauthType,$OauthID, $OauthEmail);

    $id = $db->getVar($query);

    if (!$id)
        return false;

    return $id;
}

function cr_has_S_Profile($OauthType, $Email, $uID){
    global $db;

    $query = $db->prepare ('SELECT sID FROM '.TABLE_USERS_S_LOGIN.' 
    WHERE OauthType=%s AND uID=%d AND OauthEmail=%s AND uID>0',$OauthType,$uID, $Email);

    $id = $db->getVar($query);

    if (!$id)
        return false;

    return true;
}

function cr_add_S_Login($OauthType, $OauthEmail, $OauthID, $uID=null){
    global $db;

    $id = $db->insertFromArray(TABLE_USERS_S_LOGIN, 
        ['uID'=>'0','OauthType'=>$OauthType, 'OauthID'=>$OauthID, 'OauthEmail'=>$OauthEmail]);

    if (!$id)
        return false;
        
    return $id;
}

function cr_update_S_Login($socialKey, $uID, $email){
    global $db;
    
    $param = explode ('-',$socialKey);
    $sID = intval($param[1]);

    $query = $db->query('UPDATE ' . TABLE_USERS_S_LOGIN . ' SET uID='.$uID.' WHERE sID='.$sID);
    $query = $db->query('DELETE FROM ' . TABLE_USERS_S_LOGIN . ' WHERE uID=0 AND OauthEmail='.$email.' AND OauthType='.$param[0]);
}

function cr_unlink_S($uID, $socialKey){
    global $db;
    
    $param = explode ('-',$socialKey);
    $sType = intval($param[0]);

    $query = $db->query('DELETE FROM ' . TABLE_USERS_S_LOGIN . ' WHERE uID='.$uID.' AND OauthType='.$sType);

    if ($query)
        return true;

    return false;
}

function cr_is_mobile(){
    $useragent=$_SERVER['HTTP_USER_AGENT'];
    
    return preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4));
}

function cr_login($uID, $social=false, $returnUrl=null){
    
    if ($social)
        $uID = crUser::getIDbySID($uID);

    //Restart Session
    //session_regenerate_id(true);
    cr_session_recreate();

    $_SESSION['id'] = $uID;

    //Init Some Session Values
    $_SESSION['converation_list'] = [];

    //Create Login Cookie Token
    $login_token = hash('sha256', time() . cr_generate_random_string(20, true) . time());

    $login_token_secure = md5($login_token);

    //Store Login Token
    crUsersToken::removeUserToken($uID, "auth");
    crUsersToken::createNewToken($uID, "auth", $login_token_secure);

    //Slice the login token to three pieces
    $login_token_piece1 = substr($login_token, 0, 20);
    $login_token_piece2 = substr($login_token, 20, 20);
    $login_token_piece3 = substr($login_token, 40);

    //If website is using SSL, use secure cookies
    if(SITE_USING_SSL == true){
        setcookie('COOKIE_KEEP_ME_NAME1', base64_encode($login_token_piece1), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
        setcookie('COOKIE_KEEP_ME_NAME2', base64_encode($login_token_piece3), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
        setcookie('COOKIE_KEEP_ME_NAME3', base64_encode($login_token_piece2), time() + COOKIE_LIFETIME, "/", DOMAIN, true, true);
    }else{
        setcookie('COOKIE_KEEP_ME_NAME1', base64_encode($login_token_piece1), time() + COOKIE_LIFETIME, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME2', base64_encode($login_token_piece3), time() + COOKIE_LIFETIME, "/", DOMAIN);
        setcookie('COOKIE_KEEP_ME_NAME3', base64_encode($login_token_piece2), time() + COOKIE_LIFETIME, "/", DOMAIN);
    }

    //$sessionToken = cr_generate_random_string(20,TRUE);
    //setcookie(SESSION_NAME, $sessionToken, time() + COOKIE_LIFETIME, "/", DOMAIN);

    cr_redirect($returnUrl ? base64_decode($returnUrl) : '/account.php');
}

function cr_get_popular_contacts(){
    global $db;
    
    $ep_rate = rand(15,100);
    $vp_rate = rand(0,60);
    $p_rate = rand(0,40);

    $tot = $ep_rate + $vp_rate +$p_rate;

    $e = round($ep_rate*6/$tot);
    $v = round($vp_rate*6/$tot);
    $p = round($p_rate*6/$tot);

    $exterme_pop = $db->getResultsArray("SELECT id FROM ".TABLE_USERS." WHERE popularity>200");
    $very_pop= $db->getResultsArray("SELECT id FROM ".TABLE_USERS." WHERE popularity>100 AND popularity<201");
    $pop = $db->getResultsArray("SELECT id FROM ".TABLE_USERS." WHERE popularity>50 AND popularity<101");

    $rows=[];
    
    if ($exterme_pop){
        if (sizeof($exterme_pop)<$e) {
            foreach($exterme_pop as $id){$rows[]= $id;}
        } else {
            $x=0;
            while ($x<$e){
                $i = rand(0, sizeof($exterme_pop)-1);
                if (in_array($exterme_pop[$i],$rows)){
                    continue;
                }
                
                $rows[]=$exterme_pop[$i];
                $x++;
            }
        }
    }

    if ($very_pop){
        if (sizeof($very_pop)<$v) {
            foreach($very_pop as $id){$rows[]= $id;}
        } else {
            $x=0;
            while ($x<$v){
                $i = rand(0, sizeof($very_pop)-1);
                if (in_array($very_pop[$i],$rows)){
                    continue;
                }
                
                $rows[]=$very_pop[$i];
                $x++;
            }
        }
    }
    

    if ($pop){
        if (sizeof($pop)<$p) {
            foreach($pop as $id){$rows[]= $id;}
        } else {
            $x=0;
            while ($x<$p){
                $i = rand(0, sizeof($pop)-1);
                if (in_array($pop[$i],$rows)){
                    continue;
                }
                
                $rows[]=$pop[$i];
                $x++;
            }
        }
    }
    
    if (!$rows)
        return false;

    return $rows;
}

function cr_merge_to_array(&$main, $sub){
    foreach ($sub as $key=>$value){
        $main[]=[$key=>$value];
    }
}

function cr_update_user_popularity($userID){
    global $db;

    $query = $db->prepare("SELECT COUNT(*) FROM ".TABLE_TWEETS. " WHERE ownerID=%d",$userID);
    $r = $db->getVar($query);

    $query = $db->prepare("SELECT likesCount, commentsCount, retweetsCount FROM ".TABLE_TWEETS. " WHERE ownerID=%d",$userID);
    $rows = $db->getResultsArray($query);

    foreach ($rows as $row){
        $r += $row['likesCount']*2 + $row['commentsCount']*3 + $row['retweetsCount']*5;
    }

    $query = $db->prepare("SELECT COUNT(*) FROM ".TABLE_FOLLOWSHIP." WHERE followedID=%d", $userID);
    $r += $db->getVar($query)*10;

    $query = $db->prepare("SELECT COUNT(*) FROM ".TABLE_FOLLOWSHIP." WHERE followerID=%d", $userID);
    $r += $db->getVar($query)*2;
 
    $query = $db->prepare("SELECT COUNT(*) FROM ".TABLE_FOLLOWSHIP." WHERE followerID=%d", $userID);
    $r += $db->getVar($query)*2;
 
    $query = $db->prepare("SELECT privacy FROM ".TABLE_USERS." WHERE id=%d", $userID);
    $r += $db->getVar($query)*25;
 
    $db->updateFromArray(TABLE_USERS, ['popularity'=>$r],['id'=>$userID]);

}

function cr_update_all_users_popularity(){
    global $db;

    $query = $db->prepare("SELECT id FROM ".TABLE_USERS." WHERE 1=1");
    $rows = $db->getResultsArray($query);
 
    if (!$rows)
        return false;

    foreach ($rows as $user){
        cr_update_user_popularity($user['id']);
    }
}

function cr_redirect_cUrl($page, $param) {

    foreach($param as $key=>$value) { 
    $fields_string .= $key.'='.$value.'&'; 
    }
    rtrim($fields_string,'&');

    //open connection
    $ch = curl_init();

    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,count($param));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

    //execute post
    $result = curl_exec($ch);

    //close connection
    curl_close($ch);
}

function cr_move_images($userID,$folder,$images){
    $album = crAlbum::createAlbum($userID,'unclassified',1);
				
	$images = explode(';',$images);
        
    if (!is_dir(DIR_IMG . "/users/".$userID))
        mkdir(DIR_IMG . "/users/".$userID, 0777);

    $sourcePath = DIR_IMG_TMP 	. "/users/".$userID.'/'.$folder;
    $targetPath = DIR_IMG 		. "/users/".$userID.'/'.$folder;

    if(!is_dir($targetPath))
        mkdir($targetPath, 0777);	

    foreach ($images as $img){
        
        if (intval($img)<1)
            continue;
        
        $imageData=crAlbum::getPhotoByID($img);

        $source = $sourcePath.'/'.$imageData['name'];
        $destination= $targetPath.'/'.$imageData['name'];

        rename($source, $destination);

        $source =$sourcePath.'/thumb-'.$imageData['name'];
        $destination= $targetPath.'/thumb-'.$imageData['name'];

        rename($source, $destination);

        crAlbum::addPhotoToAlbum($album,$img);
    }		
}

function cr_write_log($sessionTag, $message){
    $file = DIR_ROOT.'/logs/'. $sessionTag.'.txt';
    // Open the file to get existing content
    $current = file_get_contents($file);
    
    if(!file_exists($file))
        $myfile = fopen($file, "w");
    // Append a new person to the file
    // Write the contents back to the file
    file_put_contents($file, $message.PHP_EOL, FILE_APPEND | LOCK_EX);
}

function indexToHsl($index) {

    $clrR = ($index >> 16) & 0xFF;
    $clrG = ($index >> 8) & 0xFF;
    $clrB = $index & 0xFF;

    $clrMin = min($clrR, $clrG, $clrB);
    $clrMax = max($clrR, $clrG, $clrB);
    $deltaMax = $clrMax - $clrMin;
    
    $L = ($clrMax + $clrMin) / 510;
        
    if (0 == $deltaMax){
        $H = 0;
        $S = 0;
    }
    else{
        if (0.5 > $L){
            $S = $deltaMax / ($clrMax + $clrMin);
        }
        else{
            $S = $deltaMax / (510 - $clrMax - $clrMin);
        }

        if ($clrMax == $clrR) {
            $H = ($clrG - $clrB) / (6.0 * $deltaMax);
        }
        else if ($clrMax == $clrG) {
            $H = 1/3 + ($clrB - $clrR) / (6.0 * $deltaMax);
        }
        else {
            $H = 2 / 3 + ($clrR - $clrG) / (6.0 * $deltaMax);
        }

        if (0 > $H) $H += 1;
        if (1 < $H) $H -= 1;
    }
    return array($H, $S,$L);
}

function hslToRgb ($h, $s, $l) { 
    if ($h>240 || $h<0) return array(0,0,0); 
    if ($s>240 || $s<0) return array(0,0,0); 
    if ($l>240 || $l<0) return array(0,0,0);     
    if ($h<=40) { 
        $R=255; 
        $G=(int)($h/40*256); 
        $B=0; 
    } 
    elseif ($h>40 && $h<=80) { 
        $R=(1-($h-40)/40)*256; 
        $G=255; 
        $B=0; 
    } 
    elseif ($h>80 && $h<=120) { 
        $R=0; 
        $G=255; 
        $B=($h-80)/40*256; 
    } 
    elseif ($h>120 && $h<=160) { 
        $R=0; 
        $G=(1-($h-120)/40)*256; 
        $B=255; 
    } 
    elseif ($h>160 && $h<=200) { 
        $R=($h-160)/40*256; 
        $G=0; 
        $B=255; 
    } 
    elseif ($h>200) { 
        $R=255; 
        $G=0; 
        $B=(1-($h-200)/40)*256; 
    } 
    $R=$R+(240-$s)/240*(128-$R); 
    $G=$G+(240-$s)/240*(128-$G); 
    $B=$B+(240-$s)/240*(128-$B); 
    if ($l<120) { 
        $R=($R/120)*$l; 
        $G=($G/120)*$l; 
        $B=($B/120)*$l; 
    } 
    else { 
        $R=$l*((256-$R)/120)+2*$R-256; 
        $G=$l*((256-$G)/120)+2*$G-256; 
        $B=$l*((256-$B)/120)+2*$B-256; 
    } 
    if ($R<0) $R=0; 
    if ($R>255) $R=255; 
    if ($G<0) $G=0; 
    if ($G>255) $G=255; 
    if ($B<0) $B=0; 
    if ($B>255) $B=255; 
    
    return array((int)$R,(int)$G,(int)$B); 
} 
?>