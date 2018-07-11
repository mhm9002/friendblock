<?php
/**
 * User Account Links
 */
if(!isset($SITE_GLOBALS))
    die("Invalid Request!");

//Getting Current User ID if $userID is not set  
if(!isset($userID))
    $userID = cr_is_logged_in();

//If the user is logged in, show account links
if (!is_array($userID))
	$userID = crUser::getUserBasicInfo($userID);

if (!isset($tab))
    $tab= 'tweets';

if($userID){
    echo '<aside id="main_aside" class="col-sm-2"><br/>';
    render_account_info($userID);
    echo '</aside>';
}
?>
