<?php
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

?>
<section id="main_section">
    <?php cr_get_panel('account_info'); ?>
    <section id="right_side" class="tinted">

        <section id="stream">
        	<?php render_result_messages();
            render_new_tweet_box($userID['id']);
            

            $tweetsCount=0;
            $lastDate = date('Y-m-d H:i:s');
            
            while ($tweetsCount<5){
	        
	            //Getting Activity Stream
				$stream = crTweet::getUserTweetsStream($userID['id'],$lastDate);
				
            	if (sizeof($stream)==0) {
					$tweetsCount=1000;
					render_popular_contacts($userID['id']);
				} elseif (sizeof($stream)<5){
					$tweetsCount=1000;
					$lastDate = $stream[sizeof($stream)-1]['date'];
					render_popular_contacts($userID['id']);
				} else {
					$lastDate = $stream[sizeof($stream)-1]['date'];
				}
            
            	foreach($stream as $tweet){
                	//avoid viewing retweets again -which already retweeted by the user
                	if (!crTweet::isAlreadyRetweetedByUser($tweet,$userID['id'])){
                    	$tweetItem = cr_get_single_tweet_html($tweet, $userID['id']);
					 	if ($tweetItem) {
					 		echo $tweetItem;
					 		$tweetsCount += 1;
					 	}
					}
				}
            
            	echo '<input type="hidden" class="lastDate" value="'.$lastDate.'"/>';
			
			}     
			
            ?>
            <!-- View More Stream -->
            <div class="clear"></div>
            <div id="more-stream" data-page="account"><img src="<?php echo DIR_IMG.'/' ?>loading3.gif" height="15"/>
            </div>
        </section>
    </section>
</section>
