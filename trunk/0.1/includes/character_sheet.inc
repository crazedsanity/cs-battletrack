<?php
/*
 * Created on Jul 18, 2009
 */


require_once(constant('LIBDIR') .'/cs-battletrack/characterSheet.class.php');

$charId = null;
if(isset($_SESSION['dnd']['characterId']) && is_numeric($_SESSION['dnd']['characterId'])) {
	$charId = $_SESSION['dnd']['characterId'];
}
$char = new characterSheet($charId);

$page->gfObj->debugPrintOpt=1;
if($_POST) {
	
	if(!is_numeric($charId)) {
		//create the character & store it's ID in the session for later.
		$characterName = "No Name Supplied";
		if(isset($_POST['main']['character_name']) && strlen($_POST['main']['character_name'])) {
			$characterName = $_POST['main']['character_name'];
		}
		unset($_POST['main']);
		$_SESSION['dnd']['characterId'] = $char->create_character($characterName, $_SESSION['auth']['userInfo']['uid']);
	}
	if(is_array($_POST['main'])) {
		$char->update_main_character_data($_POST['main']);
		exit;
	}
	unset($_POST['main']);
	
	//now create all those attributes.
	$this->gfObj->debug_print($char->update_character_data($_POST),1);
	$this->gfObj->debug_print($char,1);
	exit;
}
$page->add_template_var('wsNum', 'test');
$page->add_template_var('asNum', 'test');
$page->add_template_var('gsNum', 'test');
$page->add_template_var('skillNum', 'test');

if(is_numeric($charId)) {
	$data = $char->get_character_data();
	foreach($data as $key=>$subData) {
		$page->add_template_var($key, $subData['value']);
	}
	foreach($char->get_main_character_data() as $key=>$value) {
		$page->add_template_var('main_'. $key, $value);
	}
}
?>
