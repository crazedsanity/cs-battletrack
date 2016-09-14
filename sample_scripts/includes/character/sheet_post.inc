<?php
/*
 * Created on Jul 18, 2009
 */

use crazedsanity\core\ToolBox;

if($_POST) {
	
	if(!is_numeric($charId) && strtolower($charId) == 'new') {
		//create the character & store it's ID in the session for later.
		$characterName = "No Name Supplied";
		if(isset($_POST['main']['character_name']) && strlen($_POST['main']['character_name'])) {
			$characterName = $_POST['main']['character_name'];
		}
		unset($_POST['main']);
		$charId = $char->create_character($characterName, $_SESSION['auth']['userInfo']['uid']);
	}
	if(is_array($_POST['main'])) {
		$char->update_main_character_data($_POST['main']);
	}
	unset($_POST['main']);
	
	
	//now create all those attributes.
	$char->update_character_data($_POST);
}
ToolBox::conditional_header('sheet?id='. $charId);

