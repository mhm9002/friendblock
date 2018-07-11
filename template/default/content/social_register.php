<?php
if(!isset($SITE_GLOBALS)){
	die("Invalid Request!");
}

$network = $OauthType=="fb"?"facebook":"google";

?>

<script type="text/javascript">
    $(document).on('keyup','#social-username',function(){
        var field= $(this);
        var txt = field.val();

        $.ajax({
            type: "POST", 
            data: "username="+txt+"&action=checkUserName", 
            url: "/check_reg_form.php", 
            success: function (returnHTML){
                $('#msg').remove();
                if (returnHTML.indexOf('valid')>=0){
                    if (txt == "")
                        return;

                    if (validateUsername(txt)===false) {
                        field.parent().after('<div id="msg" class="fail">Username should not include @ or white spaces</div>');
                    } else {           
                        field.parent().after('<div id="msg" class="succ">'+returnHTML+'</div>');
                    }
                    //field.notify(returnHTML,'success');
                } else {
                    field.parent().after('<div id="msg" class="fail">'+returnHTML+'</div>');
                    //field.notify(returnHTML,'error');
                }
                
            }
        });

    })
</script>

<section id="main_social_section">
	<section id="wrapper">
		<div class="container social">
            <img width="100" style="border: 1px solid #000; box-shadow: 5px 5px 5px #ccc;" src="<?php echo $thumb; ?>" alt="<?php echo DIR_IMG.'/defaultProfileImage.png' ?>" />
            <br/><br/><b>Hi<?php echo ' '.$name ?></b>
            <hr>
            <?php if ($case=="link"){ ?>
                <p>You are about to link your <?php echo " ".$network." "; ?> account with Friendblock.net<br/>Press Continue to proceed</p>
                <form action="" method="post">
                    <hr>
                    <input type="hidden" name="id" value="<?php echo $id ?>">
                    <input type="hidden" name="social-key" value="<?php echo $OauthType.'-'.$sID ?>">
                    <input type="hidden" name="link" value="1">
                    <input type="hidden" name="return" value="<?php echo $returnURL ?>">
                    <div class="row"><input class="btn btn-primary" id="proceed" value="Continue" type="submit"/></div>
                </form>
            <?php } else { ?>
                <p>Welcome to Friendblock.net<br/>
                We will be creating a new account for you and link it to your <?php echo ' '.$network.' ' ?> account<br/>
                To proceed, please enter a valid username (with no spaces or @ charachters). And agree on the conditions and terms of our website
                </p>
                <form action="" method="post">
                    <div class="row">
                        <input type="text" name="username" id="social-username" maxlength="60" value="" autocomplete="off" placeholder="username" class="input col-sm-10" required/>
                    </div>
                    <div class="row checkbox-row">
                        <label><input type="checkbox" name="agree_terms" id="agree_terms" value="1" required style="height: unset;" /> I accept the <a href="/terms_of_service.php" target="_blank">Terms and Conditions</a>.</label>
                    </div>
                    <hr>
                    <input type="hidden" name="register" value="1">
                    <input type="hidden" name="social-key" value="<?php echo $OauthType.'-'.$sID ?>">
                    <input type="hidden" name="firstName" value="<?php echo $_POST['fname'] ?>">
                    <input type="hidden" name="lastName" value="<?php echo $_POST['lname'] ?>">
                    <input type="hidden" name="email" value="<?php echo $OauthEmail ?>">
                    <input type="hidden" name="img_url" value="<?php echo $thumb ?>">
                    <input type="hidden" name="social-key" value="<?php echo $OauthType.'-'.$sID ?>">
                    <input type="hidden" name="return" value="<?php echo $returnURL ?>">
                    
                    <div class="row"><input class="btn btn-primary" id="proceed" value="Continue" type="submit"/></div>
                </form>
            <?php } ?>
        </div>
		<div id="full-wrapper"></div>
	</section>
</section>