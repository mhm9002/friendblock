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

if (!isset($profileTab))
	$profileTab='tweets';

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
                <li class="tab active" data-whatever="#accountSettings">
					<span class="nav-tab-span">Account<br/>Settings</span>
                </li>
                <li class="tab" data-whatever="#personalSettings">
					<span class="nav-tab-span">Personal<br/>Settings</span>
                </li>
                <li class="tab" data-whatever="#notifications">
					<span class="nav-tab-span">Notification<br/>Settings</span>
                </li>
            </ul>
        </div>
	</div>
<?php } ?>