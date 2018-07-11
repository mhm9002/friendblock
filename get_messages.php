<?php
require(dirname(__FILE__) . '/includes/boot.php');

if (!cr_is_logged_in())
    cr_redirect('/index.php',MSG_NOT_LOGGED_IN_USER);

if (isset($_POST['action']) && $_POST['action']=='getMessages')
    render_result_messages();
?>