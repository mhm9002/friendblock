<?php
/**
 * Notifications Page
 */

if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

$userID=cr_is_logged_in();

?>
<section id="main_section" class="tinted">

    <!-- 752px -->
    <section class="notif-box tinted">
        <?php render_result_messages();?> 

        		
        	<!--Notifications!-->
        	<div id="Notifications-content">
        	<?php
        	if (!is_array($notifications))
        		$notifications=[$notifications];
        	
        	if (cr_not_null($notifications)) {
        		
        		foreach ($notifications as $nName=>$note){
					if (is_array($note)) {
						
						$nNameArr = explode('-',$nName);
											
						$actType=$nNameArr[0];
						$objID=$nNameArr[1];
						
						if (!cr_not_null($note['createdDate'])){
							continue;
						}

						$nText = crActivity::getActivityHTML($note,$userID['id'],$actType, $objID);
						
						$class= $note['isNew'] ? 'notification-row new': 'notification-row';
						echo '<div class="'.$class.'">'.$nText.'
							<input type="hidden" class="created-date" value="'.$note['createdDate'].'"></input>	
						</div>';
							
						crActivity::markReadNotifications($userID['id'],$actType,$objID);
					}
				}

        		/*
        		foreach($notifications as $note){
                	$nText = crActivity::getActivityHTML($note,$userID['id']);	
  					$class= $note['isNew'] ? 'notification-row new': 'notification-row';
  					echo '<div class="'. $class . '">'.$nText.'</div>';
            		crActivity::markReadNotifications($userID['id'],$note['activityID']);
            	*/
  //          	}
			}
				
            	?>
            	<!-- View More Stream -->
            	<div class="clear"></div>
            	<div id="more-stream" data-page="notification" data-owner="<?php echo $userID['id'] ?>">
            		<img src="<?php echo DIR_IMG.'/' ?>loading3.gif" height="15"/>
            	</div>	
        		
        	</div>
    </section>
</section>