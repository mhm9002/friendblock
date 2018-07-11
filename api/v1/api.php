<?php
header("Access-Control-Allow-Origin: *");

define ('DIR_ROOT', dirname(dirname(dirname(__FILE__))));

$sessionTag = time();
//Include Config file (orginal config)
require_once(DIR_ROOT . "/includes/config.php");

//Auto load classes
function __autoload($className){
    $className = strtolower($className);
    if(file_exists(DIR_CLASS . "/class." . $className . ".php"))
        include DIR_CLASS . "/class." . $className . ".php";
}

require_once(DIR_INC . "/messages.php");
require_once(DIR_INC . "/tables.php");

$db = new crDatabase(DATABASE_HOST, DATABASE_USERNAME,DATABASE_PASSWORD, DATABASE_NAME);

require_once(DIR_FUN . "/session.php");
require_once(DIR_FUN . "/view.php");
require_once(DIR_FUN . "/general.php");
require_once(DIR_FUN . "/secure.php");

cr_write_log($sessionTag,"functions defined");

//Include Site Configuration File
require_once(dirname(__FILE__) . "/config.php");
require_once(dirname(__FILE__) . "/class.api.php");

if(!array_key_exists('HTTP_ORIGIN', $_SERVER)){
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

cr_write_log($sessionTag,"config defined");

try{
    $crApi = new crAPI($_REQUEST);
    cr_write_log($sessionTag,"Api requested");
    $crApi->processAction();
}catch(Exception $e){
    cr_write_log($sessionTag, $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

?>