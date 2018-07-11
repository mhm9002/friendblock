<?php

class crQuoteApi {
	public function getListAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$lastDate = isset($request['lastDate']) ? $request['lastDate'] : null;
		$firstDate = isset($request['firstDate']) ? $request['firstDate'] : null;

		if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		if (!cr_not_null($lastDate) && !cr_not_null($firstDate))
			$lastDate = date('Y-m-d H:i:s');
	
		$req = $db->prepare("SELECT * FROM ". TABLE_QUOTES . " AS q 
			LEFT JOIN " . TABLE_SOURCES . " as s ON s.sID=q.sourceID 
			LEFT JOIN ". TABLE_TOPICS. " as t ON t.tID=q.topicID "
			.'WHERE q.date < "' . $lastDate . '" LIMIT 10' );
	
		$res = $db->getResultsArray($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'No data']]];
		}

	}
	
	public function getBySourceAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$lastDate = isset($request['lastDate']) ? $request['lastDate'] : null;
		$firstDate = isset($request['firstDate']) ? $request['firstDate'] : null;

		$sID = isset($request['sID']) ? $request['sID'] : null;
		
		if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		if (!$sID){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Invalid request')];
		}
		
		if (!cr_not_null($lastDate) && !cr_not_null($firstDate))
			$lastDate = date('Y-m-d H:i:s');
	
		$req = $db->prepare("SELECT * FROM ". TABLE_QUOTES . " AS q 
			LEFT JOIN " . TABLE_SOURCES . " as s ON s.sID=q.sourceID 
			LEFT JOIN ". TABLE_TOPICS. " as t ON t.tID=q.topicID "
			.'WHERE q.date < "' . $lastDate . '" AND q.sourceID=%d LIMIT 10', $sID );
	
		$res = $db->getResultsArray($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'No data']]];
		}

	}
	
	public function getByTopicAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$lastDate = isset($request['lastDate']) ? $request['lastDate'] : null;
		$firstDate = isset($request['firstDate']) ? $request['firstDate'] : null;

		$tID = isset($request['tID']) ? $request['tID'] : null;
		
		if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		if (!$tID){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Invalid request')];
		}
		
		if (!cr_not_null($lastDate) && !cr_not_null($firstDate))
			$lastDate = date('Y-m-d H:i:s');
	
		$req = $db->prepare("SELECT * FROM ". TABLE_QUOTES . " AS q 
			LEFT JOIN " . TABLE_SOURCES . " as s ON s.sID=q.sourceID 
			LEFT JOIN ". TABLE_TOPICS. " as t ON t.tID=q.topicID "
			.'WHERE q.date < "' . $lastDate . '" AND q.topicID=%d LIMIT 10', $tID );
	
		$res = $db->getResultsArray($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'No data']]];
		}

	}

	public function reportAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
        $report = isset($request['report']) ? $request['report']:null;
		$reportEmail = isset($request['reportEmail']) ? $request['reportEmail']:"";
		$qID = isset($request['qID']) ? $request['qID']:null;
	 
        if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}
		
		if (!$qID || !$report){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Missing data')];
		}
        
		$req= $db->insertFromArray(TABLE_REPORTS,['qID'=>$qID,'report'=>$report, 'email'=>$reportEmail]);
		
		if ($req) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => ['MESSAGE'=>'Report Submitted']]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "FAILED", "RESULT" => ['MESSAGE'=>'No data']]];
		}
	}
	
	public function addNewAction(){
		global $db;
	
		$given_token = "Herrro2324";
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$content = isset($request['content'])?trim($request['content']) : null;
		$topicID = isset($request['topic'])?trim($request['topic']) : null;
		$sourceID = isset($request['source'])?trim($request['source']) : null;
		
		if(!$token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != $given_token){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		$req = $db->insertFromArray(TABLE_QUOTES, 
			['content'=>$content,'sourceID'=>$sourceID, 'topicID'=>$topicID]);
		
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