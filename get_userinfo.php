<?php
require(dirname(__FILE__) . '/includes/boot.php');
$userID = cr_is_logged_in();

if (isset($_POST['userID'])){
    $user = $_POST['userID'];

    $info = crUser::getUserBasicInfo($user);

    ob_start();
    ?>
    <div class="user-info">
        <?php render_account_info($info) ?>
    </div>
    <?php

    $html = ob_get_contents();
    ob_end_clean();

    render_result_xml(['status'=> $html ? 'success' : 'error', 'content'=>$html]);
}

?>