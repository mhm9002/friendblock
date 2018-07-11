<?php
/**
 * Footer
 */

$isMobile = cr_is_mobile();

?>
<footer>
	<?php if(!$userID){ 
        if(!$isMobile){    
    ?>
	
    <div id="main_footer">
        <a href="/register.php" class="headerLinks">Register</a> |
        <a href="/register.php?forgotpwd=1" class="headerLinks">Forgot Password</a> |
        <a href="/privacy_policy.php" class="headerLinks">Privacy Policy</a>
    </div>
    <?php   } 
    } ?>
</footer>
<?php
//cr_get_panel('footer_panel');
?>