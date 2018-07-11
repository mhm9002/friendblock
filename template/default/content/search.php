<?php
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

$userIns = new crUser();
$isMobile = cr_is_mobile();

$param = ['tab'=>'people', 'keyword'=>$_GET['searchText']];
cr_get_panel('search_top_bar',$param);

?>

<section id="main_section" class="tinted" style="margin-top:0px;">

    <!-- 752px -->
    <section id="right_side" class="tinted" style="<?php echo $isMobile ? 'width: 95%':'width:80%; margin-left: 10%; ' ?> margin-top: 5%; margin-bottom: 5%;">
        <?php render_result_messages();?>


        <div class="search-result-list tabcontent" id="searchPeople" role="tabpanel" aria-labelledby="peopleTab">
            <?php
            if(count($searchResult) > 0){
                foreach($searchResult as $data){
                    //Display user
                    $userData = $userIns->getUserBasicInfo($data['id']);
                    
                    if(empty($userData))
                        continue;

                    render_person_contact($userData, $pageLink);    
	        	} 
			}
			?>
        </div>

        <div class="search-result-list tabcontent" id="searchTags" role="tabpanel" aria-labelledby="tagsTab">
            <?php
            if(count($searchTags) > 0){
                foreach($searchTags as $data){
                    //Display user
                    $user=cr_is_logged_in();
                    $tweets= crTweet::getTweetsByTag($data['value'],$user['id']);
                    
                    if(empty($tweets))
                        continue;

                    echo "<div class=\"search-tag-head\">".$data['value']."</div>";
                    
                    foreach($tweets as $t){
                        echo cr_get_single_tweet_html($t,$user['id']);
                    }
	        	} 
			}
            ?>
            <div class=\"search-tag-head\">_</div>
        </div>

        <?php //$pagination->renderPaginate($view['page_base_url'], count($searchResult)); ?>
    </section>
</section>
 