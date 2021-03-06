<?php
/***
 * Rest API Configuration File
 */

if(!defined("CR_PUBLIC_API_KEY")){
    define("CR_PUBLIC_API_KEY", "quotes-for-life-21234343dfrf23w");
}   

if(!defined("CR_SITE_URL"))
    define("CR_SITE_URL", "http://".getHostByName(getHostName())); //"https://www.friendblock.net"); $_SERVER['REQUEST_SCHEME']."://".$_SERVER['REMOTE_ADDR']

//defentions
if (!defined('DOMAIN')){
	define ('DOMAIN', $_SERVER['HTTP_HOST']);
}

//True to show errors (for development)
if(!defined('DEVELOPER_MODE'))
	define('DEVELOPER_MODE', TRUE);

if (!defined('SITE_NAME')){
	define ('SITE_NAME', DOMAIN);
}

if (!defined('DIR_FUN')){
	define ('DIR_FUN', DIR_ROOT.'/functions');
}

if (!defined('DIR_CLASS')){
	define ('DIR_CLASS', DIR_ROOT.'/class');
}

// Database_info
if(!defined('DATABASE_HOST'))
    define('DATABASE_HOST', 'localhost');
if(!defined('DATABASE_USERNAME'))
    define('DATABASE_USERNAME', 'root');
if(!defined('DATABASE_PASSWORD'))
    define('DATABASE_PASSWORD', '');
if(!defined('DATABASE_NAME'))
    define('DATABASE_NAME', 'tayyeb');

//site related constants
if(!defined('NT_SITE_NAME'))
    define('NT_SITE_NAME', 'al-tayyeb');
if(!defined('SITE_USING_SSL'))
    define('SITE_USING_SSL', FALSE);

//Define Message Types
if(!defined('MSG_TYPE_SUCCESS'))
	define('MSG_TYPE_SUCCESS', 1);
if(!defined('MSG_TYPE_ERROR'))
	define('MSG_TYPE_ERROR', 0);
if(!defined('MSG_TYPE_NOTIFY'))
	define('MSG_TYPE_NOTIFY', 0);

if(!defined('TABLE_QUOTES'))
	define('TABLE_QUOTES', 'quotes');

if(!defined('TABLE_SOURCES'))
	define('TABLE_SOURCES', 'source');

if(!defined('TABLE_TOPICS'))
	define('TABLE_TOPICS', 'topics');

if(!defined('TABLE_PHOTOS'))
	define('TABLE_PHOTOS', 'photos');

if(!defined('TABLE_REPORTS'))
	define('TABLE_REPORTS', 'reports');


$SITE_GLOBALS = [];

function cr_api_get_error_result($errorMessage){
    return ['STATUS' => 'ERROR', 'ERROR' => $errorMessage];
}

function cr_api_format_date($userID, $date, $format = 'F j, Y'){
    global $SITE_GLOBALS;

    $timeOffset = 0;

    $userInfo = crUser::getUserBasicInfo($userID);

    $timeOffset = $SITE_GLOBALS['timezone'][$userInfo['timezone']];

    $strDate = "";

    $now = time();
    $today = date("Y-m-d");
    $cToday = date("Y-m-d", strtotime($date));

    if($cToday == $today){
        $h = floor(($now - strtotime($date)) / 3600);
        $m = floor((($now - strtotime($date)) % 3600) / 60);
        $s = floor((($now - strtotime($date)) % 3600) % 60);
        if($s > 40)
            $m++;
        if($h > 0)
            $strDate = $h . " hour" . ($h > 1 ? "s " : " ");
        if($m > 0)
            $strDate .= $m . " minute" . ($m > 1 ? "s " : " ");

        if($strDate == ""){
            if($s == 0)
                $s = 1;
            $strDate .= $s . " second" . ($s > 1 ? "s " : " ");
        }

        $strDate .= "ago";
    }else{
        $strDate = date($format, strtotime($date) + $timeOffset * 60 * 60);
    }

    return $strDate;
}

/**
 * 
 * function JSON_encode_tweets
 * prepare tweets for API use
 * 
 */

function JSON_encode_tweets($tweetStream ,$loggedUserID){
    //Format Result Data
    $result = [];

    foreach($tweetStream as $tweet){

        if ($tweet['type']=="RETWEET"){
            $par = explode('-',$tweet['content']);
            $t = crTweet::getTweetById($par[1]);
            $t = crTweet::GetLikesCommentsRetweets($t,$loggedUserID);

            $author = crUser::getUserBasicInfo ($tweet['ownerID']);

            $retweeter=[];
            $retweeter['id'] = $tweet['ownerID'];
            $retweeter['name']=$author['name'];
            $retweeter['date'] = $tweet['date'];
            $retweeter['tID'] = $tweet['id'];

            $tweet = $t;
            $tweet['retweeter']=$retweeter;
        }

        $tweet['thumbnail'] = CR_SITE_URL.'/'.crUser::getProfileIcon($tweet['ownerID']);
        
        $author = crUser::getUserBasicInfo ($tweet['ownerID']);
        
        $tweet['ownerName']= $author['name'];
        
        $tweet['postedDate'] = cr_api_format_date($loggedUserID, $tweet['date']);
        $tweet['purePostedDate'] = $tweet['date'];

        if($tweet['type'] == 'video'){
            $tweet['videoId'] = cr_get_youtube_video_id($tweet['youtube_url']);
        }else if($tweet['type'] == 'image'){
            //shall be REVISITED to have the accurate path and to allow for more than image
            $images = explode(";",$tweet['image']);
        
            if (sizeof($images)>0){                
                $i = 0;
                
                foreach ($images as $imgRef){
                    if ($imgRef=='')
                        continue;

                    $imageData = cr_retrieve_image_data($imgRef);
                    
                    if (!cr_not_null ($imageData['ownerID']))
                        continue;
                    
                    $src = CR_SITE_URL.'/images/users/'.$imageData['ownerID'].'/'.$imageData['folder_token'].'/'.$imageData['name'];
            
                    $tweet['articleImage'][$i] = $src;  
                    $i += 1;
                }
            }
        }
 
        $tweet['isRetweeted']=(crTweet::isRetweeted($tweet['id'],$loggedUserID))?"1":"0";
        
        $comments = crComment::getTweetComments($tweet['id'],null,3);
        
        for($i=0; $i<sizeof($comments);$i++){
            $commenter = crUser::getUserBasicInfo($comments[$i]['commenterID']);
            $comments[$i]['commenterName']= $commenter['name'];
            $comments[$i]['thumbnail'] = CR_SITE_URL.'/'.crUser::getProfileIcon($comments[$i]['commenterID']);
        } 

        if(count($comments) > 0 && crComment::hasMoreComments($tweet['id'], $comments[0]['date'])){
            $tweet['hasMoreComments']=1;
        } else {
            $tweet['hasMoreComments']=0;
        }

        $tweet['comments']=$comments;
        $tweet['isLiked'] = (isset($tweet['likeID']) && $tweet['likeID']) ? "1" : "0";
        $result[] = $tweet;
    }

    return $result;
}
/*
//Display Profile link with Profile Image
function render_profile_link($user, $class = ''){
    if(cr_not_null($user['thumbnail'])){
        ?>
        <a href="/profile.php?userID=<?php echo $user['id']?>"><img class="<?php echo $class?>" src="<?php echo DIR_IMG.'/users/'. $user['id'].'/'.cr_encrypt($user['id']).'-resized/'.$user['thumbnail']?>"></a>
    <?php
    }else{
        ?>
        <a href="/profile.php?userID=<?php echo $user['id']?>"><img class="<?php echo $class?>"
                src="<?php echo DIR_IMG?>/defaultProfileImage.png"></a>
    <?php
    }
}
*/