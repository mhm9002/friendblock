	<?php

function cr_write_log($sessionTag, $message){
    $file = DIR_ROOT.'/logs/'. $sessionTag.'.txt';
    // Open the file to get existing content
    //$current = file_get_contents($file);
    
    if(!file_exists($file))
        $myfile = fopen($file, "w");
    // Append a new person to the file
    // Write the contents back to the file
    file_put_contents($file, $message.PHP_EOL, FILE_APPEND | LOCK_EX);

} 


/**
 * @param $string
 * @return mixed
 */
function cr_escape_query_string($string){
    //global $db;

    $converts = ['<' => '&lt;', '>' => '&gt;', "'" => '&#039;', '"' => '&quot;'];

    $string = str_replace(array_keys($converts), array_values($converts), $string);

    return $string;

}


/**
 * check the value is null or not
 *
 * @param mixed $value
 * @return bool
 */
function cr_not_null($value){
    if(is_array($value)){
        if(sizeof($value) > 0){
            return TRUE;
        }else{
            return FALSE;
        }
    }else{
        if((is_string($value) || is_int($value)) && ($value != '') && (strlen(trim($value)) > 0)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
}

?>