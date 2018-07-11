<?php
/**
 * User Account Links
 */
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}
//Getting Current User ID if $userID is not set  
if(!isset($userID))
    $userID = cr_is_logged_in();

//If the user is logged in, show account links
if (!is_array($userID))
	$userID = crUser::getUserBasicInfo($userID);

if (!isset($tab))
    $tab= 'tweets';

if($userID){
    ?>
    
    <div id="main_profilebar">
        
        <div class="profile-photo" style="background-image: url(<?php echo crUser::getProfileIcon($userID) ?>)" ></div>
        
        <div class="profile-detailsbar">
            <?php echo $userID['name'] ?>
            <label class="titles" style="color: #bbb;">@<?php echo $userID['username'] ?></label>
            <br/>
            <label style="font-size: 12px;"><?php echo $userID['description'] ?></label>
        </div>
        <div class="tab-bar">
            <ul class="nav-tab-ul">    
                <li class="tab <?php echo $tab=='tweets'? ' tmp':'' ?>" data-whatever="#profileTweets">
                    <span class="nav-tab-span">Tweets</span><br/>
                    <?php echo crTweet::GetNumberOfTweets($userID['id']) ?>
                </li>
                <li class="tab <?php echo $tab=='following'? ' tmp':'' ?>" data-whatever="#profileFollowing">
                    <span class="nav-tab-span">Following</span><br/>
                    <?php echo crFollowship::getNumberOfUFollow($userID['id']) ?>
                </li>
                <li class="tab <?php echo $tab=='followers'? ' tmp':'' ?>" data-whatever="#profileFollowers">
                    <span class="nav-tab-span">Followers</span><br/>
                    <?php echo crFollowship::getNumberOfFollowedU($userID['id']) ?>
                </li>
            </ul>
        </div>
        
        <div class="follow-button">
            <?php render_follow_button($userID['id'],"/profile.php?userID=".$userID['id']); ?>
        </div>
    </div>
    
    <?php
}
?>
