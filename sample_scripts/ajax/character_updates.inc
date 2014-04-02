<?php


if(count($_POST) && is_numeric($_POST['character_id'])) {
	$char = new csbt_characterSheet($page->db, $_POST['character_id']);
	#$char->characterObj->logger->log_by_class("Testing... ". $page->gfObj->debug_print($_POST,0), 'debug');
	
	$xmlData = array(
		'character_id_was'	=> $_POST['character_id'],
	);
	
	//okay, try to do the update.
	try {
		$type = 'update';
		if(isset($_POST['type'])) {
			$type = $_POST['type'];
		}
		$returned = array('error'=>"No valid data submitted");
		$xmlData['callback_success'] = 'callback_showUpdatedInput';
		switch($type) {
			case 'newRecord':
				//get the "new" items....
				$extraData = array();
				$recordType = $type;
				$returned = array('error'=>"No valid data");
				if(isset($_POST['nameField'])) {
					$name = $_POST[$_POST['nameField']];
					$bits = explode('__', $_POST['nameField']);
					$recordType = $bits[0];
					unset($_POST[$_POST['nameField']], $_POST['nameField']);
				}
				$removePrefix = null;
				if(isset($_POST['tableName'])) {
					$removePrefix = $_POST['tableName'];
				}
				foreach($_POST as $k=>$v) {
					if(preg_match('/__new$/', $k)) {
						$newKey = preg_replace('/__new$/', '', $k);
						
//						$newKey = preg_replace('/^characterWeapon__/', '', $newKey);
						if(!is_null($removePrefix)) {
							$newKey = preg_replace('/^'. $removePrefix .'__/', '', $newKey);
						}
						
						$v = preg_replace('/"/', "''", $v);
						$extraData[$newKey] = $v;
					}
				}
				if(!is_null($recordType) && !is_null($name)) {
					$name = preg_replace('/"/', "''", $name);
					$returned = $char->handle_new_record($recordType, $name, $extraData);
				}
				else {
					$returned['nodata__name'] = $name;
					$returned['nodata__type'] = $type;
				}
				$xmlData['callback_success'] = 'callback_processNewRecord';
				break;
				
			case 'delete':
				$xmlData['delete_result'] = $char->handle_delete($_POST['recordType'], $_POST['recordId']);
				break;

			case 'update':
			default:
				$xmlData['id_was'] = $_POST['name'];
				$xmlData['value_was'] = $_POST['value'];
				
				//Change values so a doublequote (") becomes two single quotes (''), so the HTML doesn't break.
				$_POST['value'] = preg_replace('/"/', "''", $_POST['value']);
				$returned = $char->handle_update($_POST['name'], $_POST['value']);
		}
		if(is_array($returned) && isset($returned['error'])) {
			//TODO: why...?
			unset($returned['error']);
		}
		if(is_array($returned)) {
			foreach($returned as $i=>$v) {
				$xmlData[$i] = $v;
			}
		}
	}
	catch(Exception $e) {
		$xmlData['result'] = $e->getMessage();
		
	}
}
else {
	$xmlData = array(
		'result'	=> "missing characterId (". $_POST['character_id'] .") or no data POSTed"
	);
}

?>
