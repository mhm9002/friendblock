<?php
if(!isset($SITE_GLOBALS)){
	die("Invalid Request!");
}

//prepare social media login/register code
//cr_get_panel('social_media_register');

?>

<section id="main_section">
	<section id="wrapper">
		<?php
		// For custom banner image
		if(isset($_GET['for']) && $_GET['for'] == 'usc'){
			echo '<img src="/images/register/usc.jpg" style="margin:10px 0px 10px 10px;display:block;">';
		}
		?>
		<div id="register-wrapper">
			<?php render_result_messages(); ?>
			<div id="new-account">
				<h2 class="titles">What is <?php echo SITE_NAME ?>?</h2>

				<div>
					&#x2713; &nbsp;Social platform for inspiring people
					<br/> &#x2713; &nbsp;Your portal to keep connected with your friends
					<br/> &#x2713; &nbsp;Share experiences, memories, feelings and ideas.
				</div>

				<hr>
				<h6>Log in with Social Media</h6>
				<div>
					
					<div id="fb-login"><span class="icon icon-facebook" style="padding: 5px;"></span>&nbsp;<label>Facebook</label></div>
					<div id="g-login"><span class="icon icon-google" style="padding: 5px;"></span>&nbsp;<label>Google</label></div>
					<!--<div style="padding: 5px;" class="fb-login" data-width="200px" data-max-rows="1" data-size="medium" data-button-type="login_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false" scope="public_profile,email" onlogin="checkLoginState();"></div>
					<div style="padding: 5px;" class="g-signin2" data-height="30px" data-width="200px" data-onsuccess="onSignIn" data-theme="light">Login in With Google</div>!-->
					
				</div>
				<hr>
				<h6>Or create new account</h6>
				<form name="newaccount" id="newaccount">
					<div class="row">
						<input type="text" name="firstName" id="firstName" maxlength="30" value="" autocomplete="off" placeholder="first name" class="input col-sm-5"/>
					
						<input type="text" name="lastName" id="lastName" maxlength="60" value="" autocomplete="off" placeholder="last name" class="input col-sm-5"/>
					</div>
					<div class="row">
						<input type="text" name="username" id="username" maxlength="60" value="" autocomplete="off" placeholder="username" class="input col-sm-10"/>
					</div>
					<div class="row">
						<input type="text" name="email" id="email" maxlength="60" value="" autocomplete="off" placeholder="Email" class="input col-sm-10"/>
					</div>
					<div class="row">
						<input type="password" name="password" id="password" autocomplete="off" maxlength="20" value="" placeholder="password" autocomplete="off" class="input col-sm-10"/>
					</div>
					<div class="row">
						<input type="password" name="password2" id="password2" autocomplete="off" placeholder="repeat password" maxlength="20" value="" autocomplete="off" class="input col-sm-10"/>
					</div>
					<div class="row checkbox-row">
						<label><input type="checkbox" name="agree_terms" id="agree_terms" value="1"/> I accept the <a href="/terms_of_service.php" target="_blank">Terms and Conditions</a>.</label>
					</div>

					<!-- Do not display the CAPTCHA in developer mode -->
					<?php if(!DEVELOPER_MODE){ ?>
						<div class="row captcha-row">
							<div class="g-recaptcha" style="margin: auto;" data-sitekey="<?php echo RECAPTCHA_PUBLIC_KEY; ?>"></div>
							<div class="clear"></div>
						</div>
					<?php } ?>

					<div class="row"><input class="btn btn-primary full-width" value="Register" type="submit"/>
					</div>
					<?php render_loading_wrapper(); ?>
				</form>
			</div>
			<div id="login-wrap">
				<h2 class="titles">Login</h2>

				<form id="loginform" action="/login.php" method="post" <?php echo $showForgotPwdForm ? 'style="display: none"' : '' ?>>
					<div class="row">
						<input type="text" class="input" maxlength="60" placeholder="Email" name="email" id="email"/>
					</div>
					<div class="row">
						<input type="password" class="input" maxlength="20" placeholder="Password" name="password" id="password" autocomplete="off"/>
					</div>
					<div class="row" style="padding:3px 0;">
						<a href="/register.php#forgotpwdform" class="goto-forgotpwdform">Forgot password?</a>
					</div>
					<div class="row">
						<input type="submit" value="Log In" class="btn btn-primary" name="login_submit">
					</div>
					<?php if($returnUrl){ ?>
						<input type="hidden" name="return" value="<?php echo $returnUrl ?>"/>
					<?php } ?>
				</form>
				<form id="forgotpwdform" action="/register.php"
				      method="post" <?php echo !$showForgotPwdForm ? 'style="display: none"' : '' ?>>
					<div class="row">
						<input type="text" class="input" maxlength="60" placeholder="Email" name="email" id="email"/>
					</div>
					<div class="row" style="padding:3px 0px;">
						<a href="/register.php#loginform" class="goto-loginform">Login</a>
					</div>
					<div class="row">
						 <input type="submit" value="Reset Password" class="btn btn-primary"/>
					</div>
					<input type="hidden" name="action" value="reset-password"/>
					<?php
					render_form_token();
					?>
				</form>
			</div>
			<div class="clear"></div>
		</div>
		<div id="full-wrapper"></div>
	</section>
</section>