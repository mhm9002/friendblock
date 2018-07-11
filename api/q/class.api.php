<?php

if(!defined('STATUS_CODE_OK'))
    define('STATUS_CODE_OK', 200);

if(!defined('STATUS_CODE_BAD_REQUEST'))
    define('STATUS_CODE_BAD_REQUEST', 400);

if(!defined('STATUS_CODE_UNAUTHORIZED'))
    define('STATUS_CODE_UNAUTHORIZED', 401);

if(!defined('STATUS_CODE_NOT_FOUND'))
    define('STATUS_CODE_NOT_FOUND', 404);

if(!defined('STATUS_CODE_INVALID_METHOD'))
    define('STATUS_CODE_INVALID_METHOD', 405);

if(!defined('STATUS_CODE_INTERNAL_SERVER_ERROR'))
    define('STATUS_CODE_INTERNAL_SERVER_ERROR', 500);

class crAPI {

    /**
     * REQUEST METHOD: GET, POST, PUT, DELETE
     *
     * @var mixed
     */
    protected $METHOD = '';

    /**
     * API TYPE: Authentication, POST, PAGE, FRIEND, ...
     *
     * @var mixed
     */
    protected $TYPE = '';

    /**
     * What to do: login, create or get posts, ...
     *
     * @var mixed
     */
    protected $ACTION = '';

    public function __construct($request){
        global $sessionTag;
        
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->METHOD = $_SERVER['REQUEST_METHOD'];
        if($this->METHOD == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)){
            if($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE'){
                $this->METHOD = 'DELETE';
            }else if($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT'){
                $this->METHOD = 'PUT';
            }else{
                cr_write_log($sessionTag,$_SERVER);
                throw new Exception("Unexpected Header");
            }
        }
        switch($this->METHOD){
            case 'DELETE':
            case 'POST':
                $this->TYPE = cr_escape_query_string($request['TYPE']);
                $this->ACTION = cr_escape_query_string($request['ACTION']);
                break;
            case 'PUT':
            case 'GET':
                $this->TYPE = cr_escape_query_string($request['TYPE']);
                $this->ACTION = cr_escape_query_string($request['ACTION']);
                break;
            default:
                $this->_response('Invalid Method', STATUS_CODE_INVALID_METHOD);

        }
    }

    private function _response($responseData, $statusCode = STATUS_CODE_OK){
        header("HTTP/1.1 " . $statusCode . " " . $this->_status($statusCode));
        return json_encode($responseData);
    }

    private function _status($code){
        $status = [200 => 'OK', 401 => 'Unauthorized', 404 => 'Not Found', 405 => 'Method Not Allowed', 500 => 'Internal Server Error'];

        return isset($status[$code]) ? $status[$code] : $status[500];
    }

    public function processAction(){
        global $sessionTag;
        
        if(!$this->TYPE || !$this->ACTION){
            return $this->_response("Not Found", STATUS_CODE_NOT_FOUND);
        }


        $classFile = dirname(__FILE__) . "/" . strtolower($this->TYPE) . "Api.php";

        if(!file_exists($classFile)){
            return $this->_response("Invalid Request", STATUS_CODE_INVALID_METHOD);
        }

        cr_write_log($sessionTag,"variables defined");

        require_once($classFile);
        $className = "cr" . ucfirst($this->TYPE) . "API";

        $actionName = $this->ACTION . "Action";

        $classObj = new $className();

        if(!method_exists($classObj, $actionName)){
            return $this->_response("Invalid Request", STATUS_CODE_INVALID_METHOD);
        }

        cr_write_log($sessionTag,"request class defined");

        $result = $classObj->$actionName();

        $status = $result['STATUS_CODE'];
        $data = $result['DATA'];

        echo $this->_response($data, $status);

        cr_write_log($sessionTag,"request rendered");
    }
}