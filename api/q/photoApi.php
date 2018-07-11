<?php

class crPhotoApi {
	public function getDataAction(){
		global $db;
	
		$request = $_POST;

		$token = isset($request['TOKEN']) ? trim($request['TOKEN']) : null;
		$pID = isset($request['pID']) ? $request['pID'] : null;

		if(!$token || !$pID){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token should not be blank')];
		}

		if ($token != CR_PUBLIC_API_KEY){
			return ['STATUS_CODE' => STATUS_CODE_BAD_REQUEST, 'DATA' => cr_api_get_error_result('Api token does not match')];
		}

		$req = $db->prepare("SELECT * FROM ". TABLE_PHOTOS . " WHERE pID = %d", $pID );
	
		$res = $db->getRow($req);
		
		cr_write_log(time(),$req);

		if ($res) {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "SUCCESS", "RESULT" => $res]];
		} else {
			return ['STATUS_CODE' => STATUS_CODE_OK, 'DATA' => ["STATUS" => "FAILED", "RESULT" => ['MESSAGE'=>'No data']]];
		}
	}
}

?>