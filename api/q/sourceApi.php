<?php

class crSourceApi {
	public function getListAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $page = isset($request['page']) ? $request['page']:0;
        
        if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}
        
        $req = $db->prepare("SELECT * FROM ". TABLE_SOURCES . " WHERE 1=1 ORDER BY source LIMIT 10 ".($page>0?" OFFSET %d":""), $page);
		$res = $db->getResultsArray($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'No data']]];
		}
	}

	public function getFullListAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        
        if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}
        
        $req = $db->prepare("SELECT * FROM ". TABLE_SOURCES . " WHERE 1=1");
		$res = $db->getResultsArray($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'No data']]];
		}
	}

	
	public function addNewAction(){
		global $db;
	
		$given_token = "Herrro2324";
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$source = isset($request['source'])?trim($request['source']) : null;
		
		if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != $given_token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		$req = $db->insertFromArray(TABLE_SOURCES, 
			['source'=>$source, 'misc'=>'']);
		
		cr_write_log(time(),$req);

		if ($req) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => 
				["STATUS" => "SUCCESS", "RESULT" => 
					['MESSAGE'=>'Done']
				]
			];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => 
				["STATUS" => "SUCCESS", "RESULT" => 
					['MESSAGE'=>'Error']
				]
			];
		}

	}

}

?>