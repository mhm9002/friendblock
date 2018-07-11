<?php

if (!$adminID) {
    ?>
    <div id="login-wrap">
    <h2 class="titles">Login</h2>
        <form id="loginform" action="/admin_panel.php" method="post">
            <div class="row">
                <input type="text" class="input" maxlength="60" placeholder="Username" name="username" id="username"/>
            </div>
            <div class="row">
                <input type="password" class="input" maxlength="20" placeholder="Password" name="password" id="password" autocomplete="off"/>
            </div>
            <div class="row">
                <input type="submit" value="Log In" class="btn btn-primary" name="login_submit">
            </div>
            <?php if(isset($returnUrl)){ ?>
                <input type="hidden" name="return" value="<?php echo $returnUrl ?>"/>
            <?php } ?>
        </form>
    </div>
    <?php
} else {
    ?>
    <div  id="login-wrap">
        <label> Welecom <?php echo $adminID['name'] ?>!</label>
        <label> You've been logged in successfully!</label>
    </div>
    <?php
    cr_get_panel('admin_panel',$adminID);
}

?>