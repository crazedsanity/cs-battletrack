<?php


if(count($_POST) && is_numeric($_POST['character_id'])) {
	$char = new csbt_characterSheet($page->db, $_POST['character_id']);
	#$char->characterObj->logger->log_by_class("Testing... ". $page->gfObj->debug_print($_POST,0), 'debug');
	
	$xmlData = array(
		'test'	=> "Got it",
		'character_id_was'	=> $_POST['character_id'],
		'id_was'	=> $_POST['name'],
		'value_was'	=> $_POST['value']
	);
	
	//okay, try to do the update.
	try {
		#$xmlData['result'] = $char->handle_update($_POST['name'], $_POST['value']);
		$returned = $char->handle_update($_POST['name'], $_POST['value']);
		foreach($returned as $i=>$v) {
			$xmlData[$i] = $v;
		}
		$xmlData['callback_success'] = 'ajax_showUpdatedInput';
	}
	catch(Exception $e) {
		$xmlData['result'] = $e->getMessage();
		
	}
}
else {
	$xmlData = array(
		'result'	=> "missing characterId (". $_POST['character_id'] .")"
	);
}

?>
