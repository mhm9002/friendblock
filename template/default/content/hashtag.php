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
    $param = ['tag'=>$tag]; 
    
    cr_get_panel('popular_tags',$param);
    ?>

    <!-- 752px -->
    <section id="right_side" class="tinted">
        <?php render_result_messages();?> 
        
    	<div class="tag_stream">
        		
        <?php
            
            $tweetsCount=0;
            $lastDate = date('Y-m-d H:i:s');
    
            while ($tweetsCount<5){
                $stream = crTweet::getTweetsByTag($tag, $userID['id'], $lastDate);
        
                //check if any tweets left
                if (!(sizeof($stream)==0)) {
                    $lastDate = $stream[sizeof($stream)-1]['date'];
                } else {
                    $tweetsCount=1000;
                    return;
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
                                
            ?>
            
            <!-- View More Stream -->
            <div class="clear"></div>
            <div id="more-stream" data-page="profile" data-tag="<?php echo $tag ?>">
                <img src="<?php echo DIR_IMG.'/' ?>loading3.gif" height="15"/>
            </div>	
    
		</div>
    </section>
</section>