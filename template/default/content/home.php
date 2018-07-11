<?php
/**
 * Index Page Layout
 */

if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

$isMobile = cr_is_mobile();

?>
<section id="main_home_section">
	<?php if (!$isMobile) { ?>
	<div class="container" style="margin-top : 150px; display: grid; grid-template-columns: 60% 40%; grid-template-rows: 100%;">
		<div style="grid-column: 1; margin: 20px; background: rgba(255,255,255,0.7); box-shadow: 0 0 50px #000; padding:20px">
			<h1>Welcome to <b>FriendBlock.net</b>!</h1>
  			<p class="lead" style="font-weight:600;">Connect with your friends here. Build your social block! Exchange information, experiences and leisure.</p>
  			<hr class="my-4">	
  			<p style="font-weight:600;">For more information about FriendBlock.net, visit About page.</p>
  		</div>

		<div style="grid-column: 2; margin: 20px;  background: rgba(255,255,255,0.7); box-shadow: 0 0 50px #000; padding: 20px;">
			<div style="margin: auto; text-align: center;">
				<label style="font-weight:600;">Log in your account: Enter your email and password in the top menu </label><br/><b>OR</b><br/><label style="font-weight:600;">Log in with Social Media</label>
				<div>
					<div id="fb-login"><span class="icon icon-facebook" style="padding: 5px;"></span>&nbsp;<label>Facebook</label></div>
					<div id="g-login"><span class="icon icon-google" style="padding: 5px;"></span>&nbsp;<label>Google</label></div>
				</div>
			</div>
			
			<hr>
			<label style="font-weight:600;">Don't have account? <a href="/register.php" class="headerLinks">Register now!</a></label>
		</div>
	</div>
	
	<?php } else { ?>
	<style>
		#mainmnu {
			display: none;
		}
	</style>
	<div class="container mobile" style="height: unset; margin-top : 300px; width: 90%;">
		<label style="font-size: 72px;"><span class="icon icon-quill" style="border: 2px solid #000; border-radius: 20%; padding: 10px;"></span><b>&nbsp;FriendBlock.net</b></label>
		<div style="margin: auto; margin-top:50px; text-align: center;">
			
			<label style="font-weight:600;">Log in </label>
			<form class="form-inline" id="login" style="padding-right: 10px;" action="/login.php" method="post">
      			<input class="input" style="width: 90% !important; height: 80px; font-size: 50px; margin: auto; margin-bottom: 50px;" type="email" required="true" placeholder="Email" name="email" id="email"/>
    			<input class="input" style="width: 90% !important; height: 80px; font-size: 50px; margin: auto; margin-bottom: 50px;" type="password" required="true" placeholder="Password" name="password" id="password"/>
      			<button type="submit" class="btn btn-primary full-width" style="height: 90px !important; background: rgba(255,255,255,0.4);" name="login_submit"><b>Log in</b></button>
    		</form>
			<hr><b>OR</b><hr><label style="font-weight:600;">Log in with Social Media</label>
			<div>
				<div id="fb-login"><span class="icon icon-facebook" style="padding: 5px;"></span>&nbsp;<label>Facebook</label></div>
				<div id="g-login"><span class="icon icon-google" style="padding: 5px;"></span>&nbsp;<label>Google</label></div>
			</div>
			<hr>
			<label style="font-weight:600;">Don't have account? <a href="/register.php" class="headerLinks">Register now!</a></label>
			
		</div>	
	</div>
	<?php } ?>
	<div id="full-wrapper"></div>
</section>