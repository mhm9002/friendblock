<?php
/**
 * Include Google Analytics Code
 */
if(!isset($SITE_GLOBALS)){
    die("Invalid Request!");
}
?>


<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-109417617-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-109417617-1');
</script>
