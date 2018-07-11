<?php
/***
 * Including functions related view
 */
//Display Single Post

function cr_get_single_tweet_html($tweet, $userID){

	//hide retweets of user's tweets. Can see this through notifications
	if ($tweet['type']=='RETWEET'){
		$param = explode('-',$tweet['content']);
		
		if ($param[0]==$userID)
			return FALSE;
		
		//if the original owner has private account and not followed by this user, then return false.
		$followship = crFollowship::isFollowed($userID,$param[0]);
		$orgOwner = crUser::getUserBasicInfo($param[0]);
		
		if ($orgOwner['privacy']==0 && (!$followship || $followship['status']==0))
			return FALSE;
	
	}
    
    ob_start(); ?>
	<div class="tweet-item col-sm-12" id="<?php echo $tweet['id']?>">
	<?php
	
	if ($tweet['type']=='RETWEET'){
		$retweeter = crUser::getUserBasicInfo($tweet['ownerID']);
		
		$removeRetweet='';
		
		if ($tweet['ownerID']==$userID){
			$removeRetweet = '<a href="#" class="remove-retweet" data-whatever="'.$tweet['id'].'" style="float: right;">Remove retweet</a>';
		}
		
		echo '<div class="retweet-bar"><span class="icon icon-loop"></span>&nbsp;<label>'.$retweeter['name'].' has retweet this;</label>'.$removeRetweet.'</div>';
					
		$tweet = crTweet::getTweetById($param[1]);
		$tweet = crTweet::GetLikesCommentsRetweets($tweet, $userID);
	}
                
        $author = crUser::getUserBasicInfo(intval($tweet['ownerID']));
        $tweet['author'] = $author;        

    ?>
    
    	<table>
        <tr>
            <td style="width: 40px;">
                <a href="/profile.php?userID=<?php echo $tweet['ownerID'] ?>" class="tweet-thumb"><img src="<?php echo crUser::getProfileIcon($tweet['ownerID']) ?>" class="tweetIcons"/></a>
            </td>
            <td>
                <div class="tweet-author">
                    <a href="profile.php?userID=<?php echo $tweet['ownerID'] ?>"><b><?php echo $tweet['author']['name'] ?></b></a> tweeted;
                    <span class="tweet-timestamp"><br/><?php echo cr_format_date($tweet['date'])?></span>
                </div>
            </td>
            <td>
                <div class="dropdown">
                    <a href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="icon icon-circle-down" id="tweet-dropdown" style="float: right; color: #888;"></span></a>
                    
                    <div class="dropdown-menu" aria-labelledby="tweet-dropdown">
                        <?php if(cr_not_null($userID) && $tweet['ownerID'] == $userID){ ?>
                            <a class="dropdown-item edit-post" data-whatever="<?php echo $tweet['id'] ?>" href="#">Edit</a>
                            <a class="dropdown-item remove-post-link" href="/manage_post.php?action=delete-post&userID=<?php echo $userID ?>&id=<?php echo $tweet['id'] ?>">Delete</a>
                        <?php } else { ?>
                            <a class="dropdown-item" href="#">Share via...</a>
                            <?php if (crFollowship::isFollowed($userID,$tweet['ownerID'])){ ?>
                                <a class="dropdown-item" href="#">Unfollow <?php echo " ".$tweet['author']['name'] ?></a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                
                <div>
            </td>
        </tr>
        </table>
        <div class="tweet-content">   
            <?php 
                echo cr_process_tweets_content($tweet); 
            ?>
            
            <div class="post-like-comment">
                <div>
                    <a href='/manage_post.php?action=<?php echo cr_not_null($tweet['likeID']) ? 'unlikeTweet' : 'likeTweet' ?>&id=<?php echo $tweet['id'] ?><?php echo cr_get_token_param() ?>' class="like-post-link"><?php echo cr_not_null($tweet['likeID']) ? '<span class="icon icon-star-full"></span>' : '<span class="icon icon-star-empty"></span>' ?></a>
                    <a href="#" class="likersCount" data-toggle="modal" data-target="#likedUsers-<?php echo $tweet['id'] ?>"><?php echo $tweet['likesCount'] ?></a>
                </div>

                <a href="#" class="tweetPage" data-whatever="<?php echo $tweet['ownerID'].'-'.$tweet['id'] ?>"><span class="icon icon-pen" ></span>&nbsp;<label><?php echo $tweet['commentsCount'] ?></label></a>
                
                <a href="#" class="retweetTweet" <?php echo crTweet::isRetweeted($tweet['id'],$userID) ? ' style="color:green;"':' ' ?> data-whatever="<?php echo $tweet['ownerID'].'-'.$tweet['id'] ?>"><span class="icon icon-loop"></span><label><?php echo $tweet['retweetsCount'] ?></label></a>                        
            </div>
            
            <?php if(cr_not_null($userID)){ ?>
                <div class="post-new-comment col-sm-12">
                    <form method="post" class="postcommentform" name="postcommentform" action="">
                        <table><tr>
                            <td style="width: 30px;"><a href="/profile.php?userID=<?php echo $userID ?>"><img src="<?php echo crUser::getProfileIcon($userID) ?>" class="commentIcons"/></a></td>

                            <td><input type="text" class="col-sm-12 newcomment" name="comment" required="True" autocomplete="off" multiple="multiple" dir="auto" placeholder="Write a comment..." /> 
                            <input type="hidden" name="tweetID" value="<?php echo $tweet['id'] ?>"/>
                            <input type="submit" value="Comment" id="submit_post_reply" style="display: none;"/></td>
                        </tr></table>
                        <div class="file-row">
                            <input type="file" class="comment-image" name="comment-image"
                                id="comment-image<?php echo $tweet['id'] ?>"/>
                        </div>
                        <div class="clear"></div>
                        <?php render_form_token(); ?>
                        <?php render_loading_wrapper(); ?>
                    </form>
                    <div class="clear"></div>
                </div>
        
            <?php }
            
            $comments = crComment::getTweetComments($tweet['id']);
            
            if(count($comments) > 0 && crComment::hasMoreComments($tweet['id'], $comments[0]['date'])){
                ?>
                <div class="comment-item">
                    <a href="#" class="show-more-comments" data-last-date="<?php echo $comments[0]['date']?>" data-post-id="<?php echo $tweet['id']?>">view older comments</a>
                </div>
                <?php
            }

            echo render_tweet_comments($comments, $userID);
            ?>
        </div>
        
    </div>
    <?php
    
    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}


// Render Single Post Comment

function render_single_comment($comment, $userID = null, $isReturn = false){
    global $SITE_GLOBALS;

    $timeOffset = 0;
    if(cr_not_null($userID)){
        $userInfo = crUser::getUserBasicInfo($userID);
        $timeOffset = $SITE_GLOBALS['timezone'][$userInfo['timezone']];
    }

    ob_start();
    ?>
    <div class="comment-item">
        <table>
            <tr>
                <td rowspan="3" style="width: 20px; margin-top: 5px; vertical-align: top;"><a href="/profile.php?userID=<?php echo $comment['commenterID']?>" class="thumb"><img
                src="<?php echo crUser::getProfileIcon($comment['commenterID'])?>" class="commentIcons"/></a></td>

        
                <td><a href="/profile.php?userID=<?php echo $comment['commenterID']?>"
                style="font-weight:bold"><?php echo $comment['name']?></a></td>
            </tr>
            <tr>
                <td><div class="comment-content" <?php echo $comment['rtl'] ? 'style="direction: rtl;"':'' ?> >
                    <?php if($comment['content']){ ?>
                        <?php echo $comment['content'] ?><br/>
                    <?php } ?>

                    <?php if($comment['image']){ ?>
                        <a href="/photos/users/<?php echo $comment['commenterID'] ?>/original/<?php echo $comment['image'] ?>"
                            target="_blank"><img src="/photos/users/<?php echo $comment['commenterID'] ?>/resized/<?php echo $comment['image'] ?>"/></a>
                        <br/>
                    <?php } ?>
                </div></td>
            </tr>
            <tr>
                <td>
                    <span class="comment-date"><?php echo cr_format_date($comment['date']) ?></span>

                    <?php if($comment['commenterID'] == $userID || $comment['ownerID'] == $userID){ ?>
                        &middot;
                        <a href="/comments.php?action=delete-comment&userID=<?php echo $userID ?>&commentID=<?php echo $comment['cID'] ?>&tweetID=<?php echo $comment['tweetID'] ?><?php echo cr_get_token_param() ?>" class="remove-comment-link">Delete</a>
                    <?php } ?>
                </td>
            </tr>
        </table>
    </div>
    <?php
    $html = ob_get_contents();
    ob_end_clean();
    if(!$isReturn) {
        echo $html;
	} else {	
        return $html;
	}
}

function cr_process_tweets_content($tweet, $pageData = null){
    
    $content = $tweet['content'];
    
    if(cr_not_null($content)){
        $content = crTweet::identifyMentions($content,$tweet['id']);
        $content = str_replace("\n", "<br />", $content);
        $content = cr_make_links_clickable($content);
        $content = "<div class='post-content-inner' ". ($tweet['rtl'] ? "style='direction: rtl;'>" :">") . 
            cr_make_links_clickable($content) . 
            (isset($tweet['metaTitle'])?"<div id='link-container' style='display:block;'><table><tr><td rowspan='2'><img width='150' src='".$tweet['metaImage']."' /></td><td><a href='".$tweet['metaURL']."'>".$tweet['metaTitle']."</a></td></tr><tr><td>".$tweet['metaDescription']."</td></tr></table></div>":"").
            "</div>";
    }
        
    if($tweet['type'] == 'video'){
        //Getting Youtube Video KEY
        $content .= '<iframe class="youtube_iframe" src="//www.youtube.com/embed/' . cr_get_youtube_video_id($tweet['youtube_url']) . '?wmode=transparent" frameborder="0" allowfullscreen></iframe>';
    }else if($tweet['type'] == 'image'){
    
        $images = explode(";",$tweet['image']);
            
        if (sizeof($images)>0){
            $content .= '<table>';
            foreach ($images as $imgRef){
                if ($imgRef=='')
                    continue;

                $imageData = cr_retrieve_image_data($imgRef);
				$src = DIR_IMG.'/users/'.$imageData['ownerID'].'/'.$imageData['folder_token'].'/'.$imageData['name'];
				$content .= '<tr><td style="text-align: center;"><a target="_blank" href="' . $src . '"><img class="tweet-image" src="' . $src . '" /></a></td></tr>';
            }
            $content .= '</table>';
		}    
    }
    return $content;
}

//Getting Videos For Index Page
function cr_get_video_from_content($content){
    //Getting Youtube Shortcodes
    $pattern = "/\[youtube.*\](.*)\[\/youtube\]/i";
    if(preg_match_all($pattern, $content, $matches)){
        foreach($matches[0] as $youtube){
            //Getting Width and height, url
            $pattern1 = "/\[youtube(.*)\](.*)\[\/youtube\]/i";
            if(preg_match($pattern1, $youtube, $matches1)){

                $videoContent = '<iframe width="238" height="134" src="' . $matches1[2] . '" frameborder="0" allowfullscreen></iframe>';
                return $videoContent;
            }
        }
    }

    return '';
}

//Display post comments
function render_tweet_comments($comments, $userID = null){
    foreach($comments as $row){
        render_single_comment($row, $userID);
    }
}


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

//Display BirthDate Select Boxes: Month, Day, Year
function render_birthdate_selectboxes($birthdate = null){
    global $SITE_GLOBALS;

    if(!$birthdate)
        $birthdate = date("Y-m-d");

    list($year, $month, $day) = explode('-', $birthdate);
    ?>
    <select name="birthdate_month" id="birthdate_month" class="select">
        <?php
        for($i = 0; $i < 13; $i++){
            if($i == 0)
                echo '<option value="">- Month -</option>';else
                echo '<option value="' . $i . '" ' . ($i == intval($month) ? 'selected="selected"' : '') . '>' . $SITE_GLOBALS['months'][$i - 1] . '</option>';
        }
        ?>
    </select>
    <select name="birthdate_day" id="birthdate_day" class="select">
        <?php
        for($i = 0; $i < 32; $i++){
            if($i == 0)
                echo '<option value="">- Day -</option>';else
                echo '<option value="' . $i . '" ' . ($i == intval($day) ? 'selected="selected"' : '') . '>' . str_pad($i, 2, '0', STR_PAD_LEFT) . '</option>';
        }
        ?>
    </select>
    <select name="birthdate_year" id="birthdate_year" class="select">
        <?php
        for($i = 1912; $i <= date("Y"); $i++){
            if($i == 1912)
                echo '<option value="">- Year -</option>';else
                echo '<option value="' . $i . '" ' . ($i == intval($year) ? 'selected="selected"' : '') . '>' . $i . '</option>';
        }
        ?>
    </select>
<?php
}

//Display Relationship Status Selectbox
function render_relationship_status_selectbox($relationship){
    global $SITE_GLOBALS;
    ?>
    <select name="relationship_status" id="relationship_status" class="select">
        <option value="0">--</option>
        <?php
        foreach($SITE_GLOBALS['relationShipStatus'] as $k => $v){
            echo '<option value="' . $k . '" ' . ($k == $relationship ? 'selected="selected"' : '') . '>' . $v . '</option>';
        }
        ?>
    </select>
<?php
}

//Display Gender Selectbox
function render_gender_selectbox($gender){
    global $SITE_GLOBALS;
    ?>
    <select name="gender" id="gender" class="select">
        <option value="">--</option>
        <?php
        foreach($SITE_GLOBALS['genders'] as $k => $v){
            echo '<option value="' . $k . '" ' . ($k == $gender ? 'selected="selected"' : '') . '>' . $v . '</option>';
        }
        ?>
    </select>
<?php
}

//Display Privacy_
function render_privacy_settings($privacy){
    
        echo '<div><input type="radio" name="privacy" value="0" ' . ($privacy==0 ? 'checked="checked"' : '') . '>Private</input>';
        echo '&nbsp;&nbsp;<input type="radio" name="privacy" value="1" ' . ($privacy==1 ? 'checked="checked"' : '') . '>Public</input></div>';
}


//Display Martial Selectbox
function render_martial_selectbox($martial_stat){
    global $SITE_GLOBALS;
    ?>
    <select name="martial_stat" id="martial_stat" class="select">
        <option value="">--</option>
        <?php
        foreach($SITE_GLOBALS['relationShipStatus'] as $k => $v){
            echo '<option value="' . $k . '" ' . ($k == $martial_stat ? 'selected="selected"' : '') . '>' . $v . '</option>';
        }
        ?>
    </select>
<?php
}


function render_timezone_selectbox($timezone = '(UTC) Coordinated Universal Time'){
    global $SITE_GLOBALS;
    ?>
    <select name="timezone" id="timezone" class="select">
        <?php
        foreach($SITE_GLOBALS['timezone'] as $k => $v){
            echo '<option value="' . $k . '" ' . ($k == $timezone ? 'selected="selected"' : '') . '>' . $k . '</option>';
        }
        ?>
    </select>
<?php
}

/**
 * Render Message from SESSION

 */
function render_result_messages(){
    if(isset($_SESSION['message']) && cr_not_null($_SESSION['message'])){
        for($i = 0; $i < sizeof($_SESSION['message']); $i++){
            switch($_SESSION['message'][$i]['type']){
                case MSG_TYPE_SUCCESS:
                    echo '<p class="message success">' . $_SESSION['message'][$i]['message'] . '</p>';
                    break;
                case MSG_TYPE_ERROR:
                    echo '<p class="message error">' . $_SESSION['message'][$i]['message'] . '</p>';
                    break;
                case MSG_TYPE_NOTIFY:
                    echo '<p class="message notification">' . $_SESSION['message'][$i]['message'] . '</p>';
                    break;

            }
        }
        unset($_SESSION['message']);
    }
}

//Display BirthDate Select Boxes: Month, Day, Year
function render_year_selectbox($name, $year = null, $id = null){
    if(!$id)
        $id = $name;
    if(!$year)
        $year = date('Y');
    ?>
    <select name="<?php echo $name?>" id="<?php echo $id?>" class="select">
        <?php
        for($i = 1912; $i <= date("Y"); $i++){
            if($i == 1912)
                echo '<option value="">- Year -</option>';else
                echo '<option value="' . $i . '" ' . ($i == intval($year) ? 'selected="selected"' : '') . '>' . $i . '</option>';
        }
        ?>
    </select>
<?php
}

//Display countries list
function render_countries_selectbox($country= null){
    global $db;
    
    ?>
    <select name="country" class="select">
        <option value="SELECT" <?php echo ($country == null ? 'selected="selected"' : '') ?>>SELECT</option>
        <?php
        
    	$query = $db->prepare('SELECT country_title FROM countries WHERE status=1');
    	$rows = $db->getResultsArray($query);
    	  
        foreach($rows as $row){
                
                echo '<option value="' . $row['country_title'] . '" ' . ($row['country_title'] == $country ? 'selected="selected"' : '') . '>' . $row['country_title'] . '</option>';
        }
        ?>
    </select>
<?php
}


//Render Processing Wrapper 
function render_loading_wrapper(){
    ?>
    <div class="loading-wrapper">
        <div></div>
        <img src='<?php echo DIR_IMG?>/loading.gif' alt="Loading..."/></div>
<?php
}

//Render Result XML From Array
function render_result_xml($data, $isReturn = false){
    ob_start();
    echo '<result>';
    foreach($data as $tag => $value){
        echo '<' . $tag . '><![CDATA[' . $value . ']]></' . $tag . '>';
    }
    echo '</result>';
    $content = ob_get_contents();
    ob_end_clean();
    if($isReturn)
        return $content;else
        echo $content;
}

//Render Result XHR From array
function render_result_xhr($data, $isReturn = false){
    ob_start();
    echo '<div>';
    foreach($data as $tag => $value){
        echo '<input type="hidden" id="'.$tag.'" value="'.$value.'" />';
    }
    echo '</div>';
    $content = ob_get_contents();
    ob_end_clean();
    if($isReturn)
        return $content;else
        echo $content;
}

function cr_format_date($date, $format = 'F j, Y'){
    global $SITE_GLOBALS;

    $timeOffset = 0;
    if($SITE_GLOBALS['user']['id'] != 0){
        $userInfo = crUser::getUserBasicInfo($SITE_GLOBALS['user']['id']);
        $timeOffset = $SITE_GLOBALS['timezone'][$userInfo['timezone']];
    }

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
        //        $strDate = date("F j, Y h:i A", strtotime($date));
    }

    return $strDate;
}

//Render Top videos tops page
function render_top_videos($videos){
    foreach($videos as $i => $row){

	    $url = "/posts.php?user=" . $row['userID'] . "&post=" . $row['postID'];
	    
        ?>
        <div class="index_singleListing <?php echo ($i + 1) % 4 == 0 ? 'index_singleListingLast' : ''?> ">
            <div class="index_singleListingContent">
                <a class="video" href="<?php echo $url?>">
                    <!--<img src="//img.youtube.com/vi/<?php //echo buckys_get_youtube_video_id($row['youtube_url'])
                    ?>/mqdefault.jpg" /> -->
                    <div class="videoThumb"
                        style="background-image: url('//img.youtube.com/vi/<?php echo buckys_get_youtube_video_id($row['youtube_url'])?>/mqdefault.jpg');">
                        <div style="width: 235px;">
                            <img src="https://www.thenewboston.com/images/youtube-play-button.png">
                        </div>
                    </div>
                </a>

                <div class="video-info">
                    <span class="index_timeOfPost">posted <?php echo buckys_format_date($row['post_date'])?>
                        <br/> by </span>
                    <?php
                    $authorUrl = "/profile.php?userID=" . $row['userID'];
                    
                    ?>
                    <a href="<?php echo $authorUrl?>"
                        class="smallBlue"><?php echo $row['userName']?></a> <br/>
                    <!--<a href="<?php echo $url?>"
                        class="index_LikesAndComments"><?php echo $row['likes']?> Like<?php echo $row['likes'] > 1 ? "s" : ""?></a> &middot;!-->
                    <a href="<?php echo $url?>"
                        class="index_LikesAndComments"><?php echo $row['comments']?> Comment<?php echo $row['comments'] > 1 ? 's' : ''?></a>
                </div>
            </div>
        </div>
    <?php } ?>
    <?php if(count($videos) < 1){ ?>
        <div class="index_singleListing index_singleListingEmpty"><?php echo MSG_NO_DATA_FOUND ?></div>
    <?php }
}

//Render Top Images on tops page
function render_top_images($images){

    foreach($images as $i => $row){
        if(!$row['pageID'])
            $url = "/posts.php?user=" . $row['userID'] . "&post=" . $row['postID'];else
            $url = "/page.php?pid=" . $row['pageID'] . "&post=" . $row['postID'];
        ?>
        <?php if($i % 6 == 0){ ?>
            <div class="clear"></div><?php } ?>
        <div class="index_singleListing">
            <a href="<?php echo $url?>"><img
                    src="<?php echo DIR_WS_PHOTO . "users/" . $row['userID'] . "/" . ($row['is_profile'] ? 'resized' : 'thumbnail') . "/" . $row['image']?>"
                    class="index_ImageIcons"></a>

            <div class="index_singleListingContent" style="padding-bottom:10px;">
                <span class="index_timeOfPost">posted <?php echo buckys_format_date($row['post_date'])?> <br/>by</span>
                <?php
                if(!$row['pageID'])
                    $authorUrl = "/profile.php?userID=" . $row['userID'];else
                    $authorUrl = "/page.php?pid=" . $row['pageID'];
                ?>
                <a href="<?php echo $authorUrl?>"
                    class="smallBlue"><?php echo !$row['pageID'] ? $row['userName'] : $row['pageTitle']?></a> <br/> <a
                    href="<?php echo $url?>"
                    class="index_LikesAndComments"><?php echo $row['likes']?> Like<?php echo $row['likes'] > 1 ? "s" : ""?></a> &middot;
                <a href="<?php echo $url?>"
                    class="index_LikesAndComments"><?php echo $row['comments']?> Comment<?php echo $row['comments'] > 1 ? 's' : ''?></a>
            </div>
        </div>
    <?php } ?>
    <?php if(count($images) < 1){ ?>
        <div class="index_singleListing index_singleListingEmpty"><?php echo MSG_NO_DATA_FOUND ?></div>
    <?php }

}

//Render Top Posts on tops page
function render_top_tweets($tweets){

    $page = isset($_GET['page']) ? $_GET['page'] : 1;
    $rankCounter = 1;

    foreach($tweets as $row){
            $url = "/posts.php?user=" . $row['userID'] . "&post=" . $row['postID'];
        ?>
        <div class="index_singleListing" style="border-bottom:1px solid #ebebeb; margin-top:8px; padding-bottom:8px;">

            <div class="postRank">
                <?php
                if($page > 1){
                    echo $rankCounter + (30 * ($page - 1));
                    $rankCounter++;
                }else{
                    echo $rankCounter;
                    $rankCounter++;
                }
                echo ".";
                ?>
            </div>

            <?php
            //render_profile_link($row, 'index_PostIcons');
            
                render_profile_link($row, 'index_PostIcons');
            ?>
            <div class="index_singleListingContent">
                <a href="<?php echo $url ?>"
                    class="index_singleListingTitles"><?php echo strlen($row['content']) > 600 ? substr($row['content'], 0, 600) . "..." : $row['content'];?></a>
                <br/> <span class="index_timeOfPost">posted <?php echo buckys_format_date($row['post_date'])?> by</span>
                <?php
                
                    $authorUrl = "/profile.php?userID=" . $row['userID'];
                
                ?>
                <!--<a href="<?php echo $authorUrl?>"
                    class="smallBlue"><?php echo !$row['pageID'] ? $row['userName'] : $row['pageTitle']?></a> <br/> <a
                    href="<?php echo $url ?>"
                    class="index_LikesAndComments"><?php echo $row['likes']?> Like<?php echo $row['likes'] > 1 ? "s" : ""?></a> &middot;!-->
                <a href="<?php echo $url ?>"
                    class="index_LikesAndComments"><?php echo $row['comments']?> Comment<?php echo $row['comments'] > 1 ? 's' : ''?></a>
            </div>
        </div>
    <?php } ?>
    <?php if(count($tweets) < 1){ ?>
        <div class="index_singleListing index_singleListingEmpty"><?php echo MSG_NO_DATA_FOUND ?></div>
    <?php
    }
}

/**
 * Change enter to html (<br> tag)
 *
 * @param mixed $content
 * @return mixed
 */
function render_enter_to_br($content){
    return str_replace("\n", "<br />", $content);
}

function render_form_token($isReturn = false){
    $html = '<input type="hidden" name="' . cr_get_form_token() . '" value="1" />';

    if($isReturn) {
	    return $html;
	}else {
        echo $html;
	}
}

function render_profile_settings($userData, $type){
	
	switch ($type){
		case "account":
	?>
	<table class="table">
		<tr>
			<td>Name</td>
			<td><input type="text" class="col-sm-10" disabled="True" placeholder="<?php echo $userData['name'] ?>"/></td>
		</tr>
		<tr>
			<td>Username</td>
			<td><input type="text" class="col-sm-10" disabled="True" placeholder="<?php echo $userData['username'] ?>"/></td>
		</tr>
		<tr>
			<td>Email</td>
			<td><input type="text" class="col-sm-10" disabled="True" placeholder="<?php echo $userData['email'] ?>"/></td>
		</tr>
		<tr>
			<td>Profile picture</td>
			<td>
                <img class="profilePicture" src="<?php echo crUser::getProfileIcon($userData['id']) ?>"/><br/>
				<button class="btn btn-primary full-width" id="userPhotosBtn" data-whatever="<?php echo $userData['id'] ?>">Change Profile picture</button><br/><br/>
                <a href="\thumbnailer.php?id=<?php echo $userData['id'] ?>&return=myprofile">Create Thumbnails for missing photos</a>
            </td>
		</tr>
		<tr>
			<td>Password</td>
			<td><a role="button" class="btn btn-primary full-width" name="change_password" data-toggle="modal" data-target="#changePassword">Change Password</a></td>
		</tr>
		<tr>
			<td>Privacy</td>
			<td><?php render_privacy_settings (isset($userData['privacy']) ? $userData['privacy'] : 1) ?></td>
		</tr>
        <tr>
			<td>Social Accounts</td>
			<td>	
                <table>
                    <tr>
                        <td><label>Facebook</label></td>
                        <td><label class="switch"><input id="fb-l" class="social-toggle" type="checkbox" <?php echo $userData['fb']?'checked':'' ?>><span class="slider round"></span></label></td>
                    </tr>
                    <tr>
                        <td><label>Google</label></td>
                        <td><label class="switch"><input id="g-l" class="social-toggle" type="checkbox" <?php echo $userData['g']?'checked':'' ?>><span class="slider round"></span></label></td>
                    </tr>
                </table>
                
                <div id="fb-login" style="display:none;"><span class="icon icon-facebook" style="padding: 5px;"></span>&nbsp;<label>Facebook</label></div>
			    <div id="g-login" style="display:none;"><span class="icon icon-google" style="padding: 5px;"></span>&nbsp;<label>Google</label></div>
			</td>
		</tr>
	</table>
<?php 
			break;
			
			case "personal":
?>
	<table class="table">
		<tr>
			<td>Description</td>
			<td><textarea name="description" style="height: 120px; width: 80%; resize: none;"><?php echo $userData['description'] ?></textarea></td>
		</tr>
		
		<tr>
			<td>Martial Status</td>
			<td><?php render_martial_selectbox(isset($userData['martial_stat']) ? $userData['martial_stat'] : "Single")	?></td>
		</tr>
		
		<tr>
			<td>Gender</td>
			<td><?php render_gender_selectbox(isset($userData['gender']) ? $userData['gender'] : "Male")	?></td>
		</tr>
		<tr>
			<td>Birthday</td>
			<td><?php render_birthdate_selectboxes(isset($userData['birthday']) ? $userData['birthday']: $date) ?></td>
		</tr>
		<tr>
			<td>Timezone</td>
			<td><?php render_timezone_selectbox($userData['timezone']) ?></td>
		</tr>
		<tr>
			<td>Country</td>
			<td><?php render_countries_selectbox($userData['country']) ?></td>
		</tr>
	</table>
<?php
		break;
		
		case "notifications":
?>
	<table class="table">
		<tr>
			<td>Notifications</td>
			<td>Will be provided sooner</td>
		</tr>
	</table>			
<?php
		break;
	}

}

function render_PWD_modal($userID){
	?>
    <!-- Change Password modal -->
    <div id="changePassword" class="modal fade" role="dialog">
        <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <label class="modal-title">Change Password</label>
            </div>
            <form action='/reset_password.php' method="post">
                <div class="modal-body">
            <table>
                <tr>
                    <td><label for="currentPWD">Current Password</label></td>
                    <td><input required="true" name="currentPWD" type="password" placeholder="Enter current password"></input></td>
                </tr>
                <tr>
                    <td><label for="nPWD1">New Password</label></td>
                    <td><input required="true" name="nPWD1" type="password" placeholder="Enter new password"></input></td>
                </tr>
                <tr>
                    <td><label for="nPWD2">Repeat Password</label></td>
                    <td><input required="true" name="nPWD2" type="password" placeholder="Repeat the new password"></input></td> 
                </tr>
            </table>
            </div>
            </form>
            <div class="modal-footer">
            <button type="submit" class="btn btn-default">Change Password</button>
            <input type="hidden" name="action" value="change-password"/>
            <input type="hidden" name="userid" value="<?php echo $userID ?>" />
            <input type="hidden" name="return" value="/myprofile.php" />
            <?php render_form_token() ?>
            
            </div>
        </div>

        </div>
    </div>
    <?php
}

function render_new_tweet_box($userID, $div_id=NULL, $isReturn=FALSE, $xTweet=0) {
    
    if (!$xTweet==0){
        $tweet= crTweet::getTweetById($xTweet);
        $textClass= ($tweet['type']=='text')?' selected':'';
        $imageClass= ($tweet['type']=='image')?' selected':'';
        $videoClass= ($tweet['type']=='video')?' selected':'';
    } else {
        $textClass = ' selected';
        $imageClass='';
        $videoClass='';
    }
    $img_new_id=1;

	ob_start(); ?>
	<div style="margin-top:10px; margin-bottom:10px; margin: auto;" id="<?php echo $div_id ?>">	
        <div class="new-post-row" style="margin-left: 10px; box-shadow: inset 0 0 15px #888; border-radius: 25px;">
            <a href="/account.php"><img src="<?php echo crUser::getProfileIcon($userID) ?>" class="postIcons" style="display: inline-block;"/></a>
            <form class="newpostform<?php echo (!$xTweet==0)?' editpostform':'' ?>">
                <!--<textarea style="display: none;" name="content" id="content"></textarea>!-->
                <textarea name="content" class="newPost" placeholder="Create a new post..." dir="auto"><?php echo ($xTweet==0)?'':$tweet['content'] ?></textarea>
                <div id="link-container"></div>

                <table class="new-post-nav <?php echo cr_is_mobile() ? '' : 'col-sm-6' ?>" style="display: <?php echo ($xTweet==0)?'none':'block' ?>; margin: 0 auto;">
                    <tr class="col-sm-6">
                    <th><a href="#" class="btn post-text  <?php echo $textClass  ?>"><span class="icon icon-text-color"></span></a></th>
                    <th><a href="#" class="btn post-image <?php echo $imageClass ?>"><span class="icon icon-image"></span></a></th>
                    <th><a href="#" class="btn post-video <?php echo $videoClass ?>"><span class="icon icon-play"></span></a></th>
                    <th><input type="submit" id="save-btn" class="btn btn-primary" value="<?php echo (!$xTweet==0)?'Edit':'Post' ?>"/></th>
                    </tr>
                </table></br>
                
                <div id="new-video-url" <?php echo (cr_not_null($videoClass))?'style="display:block;"':'' ?> >
                    <label for="video-url-label">YouTube URL:</label> 
                    <input type="text" name="youtube_url" id="youtube_url" class="input" value="<?php echo (cr_not_null($videoClass))?$tweet['youtube_url']:'' ?>"/>
                </div>      	

                <div class="file-row col-sm-12" id="photoUpload" <?php echo (cr_not_null($imageClass))?'style="display: block;"':'' ?>>
                    <?php if (cr_not_null($imageClass)){
                        $images= explode(';',$tweet['image']);
                        
                        foreach ($images as $img){
                            if (!cr_not_null($img))
                                continue;
                            
                            $i = crAlbum::getPhotoByID($img);
                            $folderToken = $i['folder_token'];
                            $d = explode('.',$i['name']);
                            $id = $d[0];
                            
                            //update the new box id
                            $img_new_id=($id>$img_new_id)?$id+1:$img_new_id;

                            ?>
                            <div class="btn-file" id="<?php echo $id ?>" data-whatever="loaded" style="background-image: url('<?php echo DIR_IMG.'/users/'.$i['ownerID'].'/'.$i['folder_token'].'/thumb-'.$i['name'] ?>'); ">
                                <?php render_loading_wrapper(); ?>
                                <div class="remove-photo<?php echo (cr_is_mobile())?' show':'' ?>"><span class="icon icon-cross"></span></div>	
                            </div>
                            <input class="imgInp" type="file" accept="image/gif, image/jpeg, image/jpg, image/png" id="file-<?php echo $id ?>" name="file-<?php echo $id ?>" data-whatever="<?php echo $img ?>" />
                            <?php
                        }
                    }
                    
                    ?>
                    <div class="btn-file" id="<?php echo $img_new_id ?>" data-whatever="notloaded">
                        <?php render_loading_wrapper(); ?>
                        <div class="remove-photo"><span class="icon icon-cross"></span></div>	
                    </div>
                    <input class="imgInp" type="file" accept="image/gif, image/jpeg, image/jpg, image/png" id="file-<?php echo $img_new_id ?>" name="file-<?php echo $img_new_id ?>"></input>			                                   	
                </div>
                <div class="clear"></div>
                    
                <div id="jcrop-row-wrapper">
                    <a href="#" class="cancel-photo"></a>
                    <div id="jcrop-row"></div>
                </div>
                
                <input type="hidden" name="action" value="<?php echo (!$xTweet==0)?'edit-post':'submit-post' ?>"/> 
                <input type="hidden" name="x1" id="x1" value="0"/> 
                <input type="hidden" name="x2" id="x2" value="0"/> 
                <input type="hidden" name="y1" id="y1" value="0"/> 
                <input type="hidden" name="y2" id="y2" value="0"/> 
                <input type="hidden" name="width" id="width" value="0"/> 
                <input type="hidden" name="type" id="type" value="<?php echo (!$xTweet==0)?$tweet['type']:'text' ?>"/>
                <input type="hidden" name="return" value="<?php echo base64_encode('/account.php') ?>"/>
                <input type="hidden" name="tID" value="<?php echo $xTweet ?>" />
                <input type="hidden" name="folder_token" id="folder_token" value="<?php echo (cr_not_null($imageClass))?$folderToken:cr_generate_random_string(12); ?>"/>
                <input type="hidden" name="rem1" id="rem1" value="" />
                <input type="hidden" name="rem2" id="rem2" value="" />

                <?php 
                render_form_token();
                render_loading_wrapper();
                ?>
            </form>
        </div>
    </div>

	<?php
	$html = ob_get_contents();
    ob_end_clean();
	
	if ($isReturn)
		return $html;
	
	echo $html;
	return;
}

function render_person_contact($userData, $return=null){
			
			$profileLink = '/profile.php?userID='.$userData['id'].'&tab=tweets';
            $sendMessageLink = '/messages_compose.php?to=' . $userData['id'];
            
            ?>
            <div class="contact_block">
            	<table>
                    <tr>
                        <td style="float: left; vertical-align: middle;">
                            <?php render_profile_link($userData, 'tweetIcons'); ?>
                            <div style="display: inline-block; position: relative; margin-top: 15px; margin-left: 10px;">        
                                <a href="<?php echo $profileLink; ?>" class="profile-link"><?php echo $userData['name'] ?></a>
                                <label style="font-weight: bold; font-size: 12px; color: #888;">@<?php echo $userData['username'] ?></label>
                            </div>
                        </td>
                        <!--
                        <td style="width: 15%">
                        <span class="stat-details">Followers: <?php //echo crFollowship::getNumberOfFollowedU($userData['id']) ?></span>
                        </td>
                        <td style="width: 15%">
                        <span class="stat-details">Following: <?php //echo crFollowship::getNumberOfUFollow($userData['id']) ?></span>
                        </td>
                        !-->
                        <td style="float: right;">               
                            <?php render_follow_button ($userData['id'],$return) ?>
                        </td>
                    </tr>
                    <tr><td><span class="stat-details"><?php echo $userData['description'] ?></span></td></tr>
                </table>
            </div>
            		
			<?php

}

function render_user_info($userID){
	?>   
    <label class="titles" style="margin-bottom: 0px;" ><b><?php echo $userID['name'] ?></b></label>
    <label class="titles" style="color: #CCC;">@<?php echo $userID['username'] ?></label>

    </br>
    
    <hr>
    <table class="col-sm-10 panel-userdata">
        <tr>
            <!--<td>Description</td>!-->
            <td><b><?php echo $userID['description'] ?></b></td>
        </tr>
        <!--
        <tr>
            <td>Username</td>
            <td><b><?php echo $userID['username'] ?></b></td>
        </tr>
        <tr>
            <td>Gender</td>
            <td><b><?php echo isset($userID['gender']) ? $userID['gender'] : "-"	?></b></td>
        </tr>
        <tr>
            <td>Birthday</td>
            <td><b><?php echo isset($userID['birthday']) ? $userID['birthday']: '-' ?></b></td>
        </tr>
        <tr>
            <td>Timezone</td>
            <td><b><?php echo $userID['timezone'] ?></b></td>
        </tr>
        <tr>
            <td>Country</td>
            <td><b><?php echo isset($userID['country']) ? $userID['country'] : '-' ?></b></td>
        </tr>
        <tr>
            </br>
        </tr>
        !-->	
    </table>

	<?php
}

function render_account_info($userID){
	?>   
    <table>
        <tr>
            <td rowspan="2">
            <a href="/profile.php?userID=<?php echo $userID['id'] ?>&tab=tweets" style="float: right;">
                <img src="<?php echo crUser::getProfileIcon($userID) ?>" class="tweetIcons"/>
            </a></td>
            <td><label class="titles" style="margin-bottom: 0px;" ><b><?php echo $userID['name'] ?></b></label></td>
        </tr>
        <tr><td><label class="titles" style="color: #CCC;">@<?php echo $userID['username'] ?></label></td></tr>
    </table>
    </br>
    
    <table class="col-sm-12" style="text-align: center;">
        <tr>
            <th class="panel-header">Tweets<s</th>
            <th class="panel-header">Following</th>
            <th class="panel-header">Followers</th>
        </tr>
        
        <tr>
            <td><a href="/profile.php?userID=<?php echo $userID['id'] ?>&tab=tweets"><?php echo crTweet::GetNumberOfTweets($userID['id']) ?></a></td>
            <td><a href="/profile.php?userID=<?php echo $userID['id'] ?>&tab=following"><?php echo crFollowship::getNumberOfUFollow($userID['id']) ?></a></td>
            <td><a href="/profile.php?userID=<?php echo $userID['id'] ?>&tab=followers"><?php echo crFollowship::getNumberOfFollowedU($userID['id']) ?></a></td>
        </tr>
    </table>
    <hr>
    <table class="col-sm-10 panel-userdata">
        <tr>
            <!--<td>Description</td>!-->
            <td><b><?php echo $userID['description'] ?></b></td>
        </tr>
        <!--
        <tr>
            <td>Username</td>
            <td><b><?php echo $userID['username'] ?></b></td>
        </tr>
        <tr>
            <td>Gender</td>
            <td><b><?php echo isset($userID['gender']) ? $userID['gender'] : "-" ?></b></td>
        </tr>
        <tr>
            <td>Birthday</td>
            <td><b><?php echo isset($userID['birthday']) ? $userID['birthday']: '-' ?></b></td>
        </tr>
        <tr>
            <td>Timezone</td>
            <td><b><?php echo $userID['timezone'] ?></b></td>
        </tr>
        <tr>
            <td>Country</td>
            <td><b><?php echo isset($userID['country']) ? $userID['country'] : '-' ?></b></td>
        </tr>
        <tr>
            </br>
        </tr>
        !-->	
    </table>

	<?php
}

//functions for inactive users

function cr_render_missed_tweet($tweet){

    $tweet['author'] = crUser::getUserBasicInfo(intval($tweet['ownerID']));
    ob_start();
    
	?>
	<div class="tweet-item col-sm-10" style="float: unset; padding; 30px;" id="<?php echo $tweet['id']?>">
        
        <table>
        <tr><td style="width: 40px;">
            <a href="/profile.php?userID=<?php echo $tweet['ownerID'] ?>" class="tweet-thumb">
                <img src="<?php echo crUser::getProfileIcon($tweet['ownerID']) ?>" width="30" height="30" style="border-radius:50%; border:1px sold #000;"/>
            </a>
        </td><td>
        <div class="tweet-author">
            <a href="/profile.php?userID=<?php echo $tweet['ownerID'] ?>">
                <b><?php echo $tweet['author']['name'] ?></b>
            </a> tweeted;
        </div>
        </td></tr>
        </table>

        <div class="tweet-content">
            
            <?php echo cr_process_tweets_content($tweet); ?>
            <div class="tweet-date">
                    <span class="lft">
                        <span><?php echo cr_format_date($tweet['date'])?></span>
                    </span>

                <div class="clear"></div>
            </div>
        </div>
    </div>

    <?php 

    $html = ob_get_contents();
    ob_end_clean();
    return $html;
}

function cr_to_absolute($html){
    $search = array('href="/','src="./','src="/');
    $replace = array('href="https://'.DOMAIN.'/','src="https://'.DOMAIN.'/','src="https://'.DOMAIN.'/');
    
    $html=str_replace($search,$replace,$html);

    $search = array('class="replyToPostIcons"');
    $replace = array('width="30" height="30" style="border-radius:50%; border:1px solid #000"');
    
    $html=str_replace($search,$replace,$html);

    return $html;
}

function render_popular_contacts($userID){
    $IDs = cr_get_popular_contacts();
 
    echo "<div class=\"retweet-bar\"><b>Hello there!</b><br/>As you're new to Friendblock.net, we recommend you to check the following contacts.<br/> If you saw any of them interesting, you can simply follow and be updated with thier news</div>";

    foreach ($IDs as $id){
        $user=crUser::getUserBasicInfo($id);
        render_person_contact($user);
    }
    
}

function render_follow_button ($profileID, $return=null){

    $loggedUser= cr_is_logged_in();
    $loggedUserID = $loggedUser['id'];

    if ($profileID != $loggedUserID) {
        //follow form
        $followshipStatus = crFollowship::isFollowed($loggedUserID,$profileID);
        $class = "";

        if (!cr_not_null($followshipStatus)){    
            $followAction="follow";
            $btnCaption="Follow";
        } else {
            $followAction="unfollow";
            $btnCaption = ($followshipStatus['status']==1)?"Following":"Cancel Follow Request";
            $class = "following";
        }
        ?>        
        <form style="display: inline-block" name="add-follow" action="/add_follow.php" method="post">
            <input type="hidden" name="follower" value="<?php echo $loggedUserID ?>"/>
            <input type="hidden" name="followed" value="<?php echo $profileID ?>"/>
            <input type="hidden" name="follow_action" value="<?php echo $followAction ?>"/>
            <input type="hidden" name="return" value="<?php echo $return ?>"/>
            <button type="submit" class="btn btn-primary <?php echo $class; ?>" id="follow-btn"><?php echo $btnCaption ?></button>
        </form>
        <?php
    }
}

function render_tweet_box($xTweet=0){
    $new= ($xTweet==0)?true:false;
    $user= cr_is_logged_in();

    ?>
    <!--tweet modal!-->
    <div class="modal fade" id="tweetBox<?php echo !$new?('-'.$xTweet):'' ?>" tabindex="-1" role="dialog" aria-labelledby="newTweet" aria-hidden="true" style="display: none;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTweet"><?php echo $new?'New Tweet':'Edit Tweet' ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <?php render_new_tweet_box($user['id'],'for-modal',false,$xTweet) ?>
                </div>
                <div class="modal-footer">
                            
                </div>
            </div>
        </div>
    </div>
    <?php
}

?>