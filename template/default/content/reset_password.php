<?php
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}
?>
<section id="main_section">
    <section id="wrapper">
        <div id="register-wrapper">
        <div id="new-account">
            <h2 class="titles">Reset Password</h2>
            <?php render_result_messages(); ?>
            <form id="resetpwdform" action="/reset_password.php" method="post">
                <div class="row">
                    <label for="password">Password:</label> <input type="password" class="input" maxlength="60" name="nPWD1" id="password" autocomplete="off"/>
                </div>
                <div class="row">
                    <label for="password2">Confirm Password:</label> <input type="password" class="input" maxlength="60" name="nPWD2" id="password2" autocomplete="off"/>
                </div>
                <div class="row">
                    <label></label><input type="submit" class="btn btn-primary" value="Save Password" class="redButton"/>
                </div>
                <input type="hidden" name="action" value="reset-password"/> 
                <input type="hidden" name="token" value="<?php echo $_GET['token'] ?>"/>
            </form>
        </div>
        </div>
    </section>
</section>