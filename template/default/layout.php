<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="description"
            content="Connect with your friends. Use your quill to inspire your people. Share experiences and ideas. Meet inspiring people">
        <meta name="keywords"
            content="tweet, social, post, network, quill, friend, block, share, inspiration">
    
    <?php
    cr_render_meta();
    ?>
    <title><?php echo isset($SITE_GLOBALS['title']) ? $SITE_GLOBALS['title'] : SITE_NAME ?></title>
    <?php cr_render_stylesheet(); ?>
    <!--[if lt IE 9]>
    <script src="<?php echo DIR_JS?>/html5shiv.js"></script><![endif]-->

    <?php cr_render_javascripts(false); ?>
</head>
<body>
<?php 
cr_get_panel('analyticstracking'); 
cr_get_panel('pusher'); 

$isMobile = cr_is_mobile();

if ($isMobile){
    ?>
    <style>
        .modal {
            font-size: 24px;
        }
    </style>
    <?php
}

?>

<!-- Preload Images
<div id="preload-wrapper">
    <img src="/images/loading.gif"/> <img src="/images/loading3.gif"/> <img src="/images/loading2.gif"/> <img
        src="/images/loading3.gif"/> <img src="/images/loading16.gif"/>
</div>
 !-->


<div id="wrapper">
    <?php require(dirname(__FILE__) . '/header.php') ?>
    <?php require(dirname(__FILE__) . '/content/' . $SITE_GLOBALS['content'] . '.php') ?>
    <?php require(dirname(__FILE__) . '/footer.php') ?>
</div>

<?php cr_render_javascripts(true); ?>
</body>
</html>