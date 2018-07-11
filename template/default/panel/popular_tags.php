<?php
/**
 * Related Tags
 */
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}

$related = cr_get_related_tags($tag);

if($related){
    echo '<aside id="main_aside" class="col-sm-2"><br />';
        echo "<h6>Related Tags</h6><br/>".$related;
    echo '<br /></aside>';
}
?>