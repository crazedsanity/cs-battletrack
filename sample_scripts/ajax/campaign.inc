<?php

#$page->gfObj->debug_print($sectionArr,1);

define('JSON_ENCODE', 1);

$xmlData['errorCode'] = "Invalid action";
$xmlStatus = 0;

//get the request & value.
if(count($sectionArr) >= 5) {
	$action = $sectionArr[4];
	
	$returnData = null;
	
	switch($action) {
		case "playerData":
			$tCharId = $sectionArr[5];
			try {
				$charObj = new csbt_characterSheet($page->db, $tCharId, $_SESSION['uid']);
				$sheetData = $charObj->get_sheet_data();
				$xmlStatus = 1;
				$returnData = array(
					'character_id'		=> $sheetData['main__character_id'],
					'character_name'	=> $sheetData['main__character_name'],
					'campaign_id'		=> $sheetData['main__campaign_id']
				);
			}
			catch(Exception $e) {
				$xmlData['errorCode'] = $e->getMessage();
			}
			break;
			
		case "addPlayer":
			$tCharId = $sectionArr[5];
				$charObj = new csbt_characterSheet($page->db, $tCharId, $_SESSION['uid']);
				$sheetData = $charObj->get_sheet_data();
				if(isset($sheetData['campaign_id']) && !is_numeric($sheetData['campaign_id'])) {
					$cObj = new csbt_campaign();
				}
				else {
					$xmlStatus = 0;
				}
			break;
			
		case "charSearch":
			$searchObj = new csbt_characterSearch($page->db);
			try {
				$searchCrit = array(
					'character_name'	=> $_GET['term'],
					'__FILTER__'			=> 'campaign_id IS NULL'
				);
				$res = $searchObj->search($searchCrit);
				if(is_array($res) && count($res)) {
					$xmlData = array();
					foreach($res as $id=>$cData) {
						$xmlData[] = array(
							'id'		=> $id,
							'value'		=> $id,
							'label'		=> $cData['character_name']. " [". $cData['character_level'] ."]"
						);
					}
				}
				print(json_encode($xmlData));
				exit;
			}
			catch(Exception $e) {
				$xmlData['errorCode'] = "Fatal error: ". $e->getMessage();
			}
			break;
			
		default:
			$xmlData['errorCode'] = "Unknown action (". $action .")";
	}
	
	if(!is_null($returnData)) {
		$xmlData['returnData'] = $returnData;
		unset($xmlData['errorCode']);
	}
	$xmlData['status'] = $xmlStatus;
}



