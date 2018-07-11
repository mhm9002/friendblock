<?php

require_once (dirname(__FILE__)).'/includes/boot.php';

if (!$_GET['sec']=="qwfd232scfek9hhyferfdklocmuhuvdby78jwoc")
    exit;

//grouping users according to ID
crUserGrouping::updateGroups();
echo "Groups Updated<br/>";

//get the group in queue and mark it as done (requires DB)
$group = crUserGrouping::getNextGroup();
echo "Groups selected<br/>";

//get inactive users (liking/commenting/retweeting/following) or (posting)
foreach($group as $id){
    $lastAct = crActivity::getUserLastActivityDate($id);
    $lastTweet = crTweet::getUserLastTweetDate($id);

    $last = max($lastAct,$lastTweet);

    $threshold = date('Y-m-d H:i:s', time()-(60*60*24*3)); //inactive for more than three days

    if ($last >= $threshold) 
        continue;
    
    //get activities inactive users missed
    $stream = crTweet::getUserTweetsStream($id,null,$last);
    $notif = crActivity::getNotifications($id,5,null,null,$last);
    
    echo "Tweets & Streams loaded<br/>";

    if (sizeof($stream)==0 && sizeof($notif)==0)
        continue;

    $user = crUser::getUserBasicInfo($id);
    $email = crUser::getUserEmail($id);

    echo "for ".$email."<br/>";
    
    //format HTML
    $body = '<h1>Hey '.$user['name'].'!</h1><br/>
        <hr>
        It has been long time since you have participated in our website! We thought why not to brief you of what you have missed<br/>';

    if (sizeof($stream)>0){
        
        $body.= '<h2>Tweets</h2><br/>';

        foreach($stream as $tweet){
                $body .= cr_render_missed_tweet($tweet);
        }
    }

    if (sizeof($notif)>0){
        $body .= '<h2>Notifications</h2>';
            
        foreach($notif as $nName=>$note){
            
            if (is_array($note)) {
                            
                $nNameArr = explode('-',$nName);
                                                
                $actType=$nNameArr[0];
                $objID=$nNameArr[1];
                
                if (!cr_not_null($note['createdDate'])){
                    continue;
                }

                $nText = crActivity::getActivityHTML($note,$userID['id'],$actType, $objID);
                
                $class= $note['isNew'] ? 'notification-row new': 'notification-row';
                
                $body.= '<div class="'.$class.'">'.$nText.'
                    <input type="hidden" class="created-date" value="'.$note['createdDate'].'"></input>	
                </div>';
                                
            }
        }
    }

    $body = cr_to_absolute($body);

    //send emails
    $email = 'mohanajjar85@gmail.com';
    $subject = 'Friendblock.net - Long time '.$user['name'];

    cr_sendmail($email, $user['name'] , $subject, $body);
    
    echo "Email Sent<br/><br/>";
    //echo $body;

}

?>