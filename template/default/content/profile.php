<?php
/**
 * Profile Detail Page
 */

if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

?>
<section id="main_section" class="tinted">

    <!-- Left Side -->
    <?php
    $param = ['userID'=>$profileID,'tab'=>$profileTab]; 
	
    cr_get_panel('account_links',$param);
    ?>

    <!-- 752px -->
    <section id="middle_side" class="tinted">
        <?php render_result_messages();?> 
        
    	    <div class="tab-content" id="profileTabContent">
        		
        		<!--Tweets Tab!-->
        		<div class="tabcontent" id="profileTweets" role="tabpanel" aria-labelledby="tweetTab">
        		<?php
        		if ($userTweets=='It is safe to load tweets') {
        			$tweetsCount=0;
            		$lastDate = date('Y-m-d H:i:s');
            
            		while ($tweetsCount<5){
        				$stream = crTweet::getTweetsByUserID($profileID, $userID['id'],null,$lastDate);
        		
      	        		if (!(sizeof($stream)==0)) {
							$lastDate = $stream[sizeof($stream)-1]['date'];
						} else {
							$tweetsCount=1000;
						}
		            
		            	foreach($stream as $tweet){
		                    $tweetItem = cr_get_single_tweet_html($tweet, $userID['id']);
							 if ($tweetItem) {
							 	echo $tweetItem;
							 	$tweetsCount += 1;
							 }
						}
		            	
		            	echo '<input type="hidden" class="lastDate" value="'.$lastDate.'"/>';    
					}
        					
				} else {
					echo $userTweets;
				}					
            	?>
            		<!-- View More Stream -->
            		<div class="clear"></div>
            		<div id="more-stream" data-page="profile" data-owner="<?php echo $tweet['ownerID'] ?>">
            			<img src="<?php echo DIR_IMG.'/' ?>loading3.gif" height="15"/>
            		</div>	
        		</div>
        
        		<!--Followed Tab!-->
        		<div class="tabcontent" id="profileFollowing" role="tabpanel" aria-labelledby="followedTab">
        		<?php
        			foreach($followed as $id){
                		$followedPerson = crUser::getUserBasicInfo($id);
                		render_person_contact($followedPerson,'/profile.php?userID='.$followedPerson['id'].'&tab=tweets');
            		}
            	?>
        		</div>
        
    		    <div class="tabcontent" id="profileFollowers" role="tabpanel" aria-labelledby="followersTab">
        		<?php
        			foreach($followers as $id){
                		$followerPerson = crUser::getUserBasicInfo($id);
                		render_person_contact($followerPerson,'/profile.php?userID='.$followerPerson['id'].'&tab=tweets');
            	}
            	?>
        		</div>
			</div>
    </section>
</section>