
<?php

if (isset($_POST['data']) ){

    $dir = "./xml";
    if(!is_dir($dir)){
        mkdir($dir, 0777);
        $fp = fopen($dir . "/index.html", "w");
        fclose($fp);
    }


    $pairs = explode(",",trim($_POST['data']));

    foreach($pairs as $p){
        $d = explode('!',$p);
        
        $xmlText = '<?xml version="1.0" encoding="utf-8"?>
        <font-icon
            xmlns:android="http://schemas.android.com/apk/res-auto"
            android:text="@string/ic_'.$d[0].'"
            android:textSize="@dimen/'.$_POST["size"].'dp"
            android:textColor="@color/'.$_POST["color"].'"/>';

        $filename = "ic_".$d[0]."_".$_POST["size"]."_".$_POST["color"].".xml";

        $f = fopen($dir."/".$filename,"w");
        fwrite($f,$xmlText);
    }
}

?>

<body>

<form method="POST" action="" >
    <div style="display:block; margin:auto; width:100%;">
        <textarea rows="8" style="margin: auto; display: block;" name="data" placeholder="enter icon name and value seprated by ! and pairs seprated by , comma"></textarea>
        <input style="margin: auto; display: block;" type="text" name="color" placeholder="enter the name of color param" /> 
        <input style="margin: auto; display: block;" type="text" name="size" placeholder="enter the name of size param" /> 
        <input style="margin: auto; display: block;" type="submit" value="Submit" />
    </div>
</form>

</body>
