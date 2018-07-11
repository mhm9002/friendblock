<?php
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

if ($isMobile=cr_is_mobile()){
	?>
	<style>
		#right_side{
			margin: auto; 
			margin-top: 90px;		
		}
	</style>
	<?php
} else {
	?>
	<style>
		#right_side {
			width: 60%;
			margin: auto; 
			margin-top: 90px;
		}
	</style>
	<?php
}

?>

<section id="main_section">
	<?php cr_get_panel('settings_top_bar'); ?>
	<section id="right_side" class="tinted" style="padding-bottom: 20px; margin-bottom: 5%; float: unset;">	
		<form method="post" action="/myprofile.php" id="profileUpdateForm">
    	
        	<?php render_result_messages() ?>
        	<div id="accountSettings" class="tabcontent show">
				<?php 
				$userID['fb']= cr_has_S_Profile('fb',$userID['email'],$userID['id']);
				$userID['g']= cr_has_S_Profile('g',$userID['email'],$userID['id']);

				render_profile_settings($userID, "account");
				
				?>
	        </div>
	        <div id="personalSettings" class="tabcontent">
	        	<?php render_profile_settings($userID, "personal") ?>
	        </div>
	        <div id="notifications" class="tabcontent">
	        	<?php render_profile_settings($userID, "notifications") ?>
	        </div>
	        <?php 
	        render_loading_wrapper();
	        render_form_token(); 
	        ?>
			<hr>
			<div style="margin-left: 20px;">
				<h4>Update Profile</h4>
				<hr>
				<a class="btn btn-primary full-width" role="button" href="/account.php" style="margin-bottom: 20px;">Cancel</a>
				<input class="btn btn-primary full-width" type="submit" value="Update Profile"></input>
				<input type="hidden" name="action" value="update-profile"/>
				<input type="hidden" name="userID" value="<?php echo $userID['id']?>"/>
			<div>
		</form>
	</section>
    <?php render_PWD_modal($userID['id']); ?>
</section>
