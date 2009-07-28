<?php
/*
 * Created on Jul 18, 2009
 */

if($_GET['id']) {
	$_SESSION['dnd']['characterId'] = $_GET['id'];
	$charId = $_GET['id'];
}
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
	}
	unset($_POST['main']);
	
	
	//now create all those attributes.
	$char->update_character_data($_POST);
	$page->gfObj->conditional_header($_SERVER['PHP_SELF'] .'?_='. time());
}

$page->rip_all_block_rows('content');

if(is_numeric($charId)) {
	$data = $char->get_character_data();
	foreach($data as $key=>$subData) {
		$page->add_template_var($key, $subData['value']);
	}
	foreach($char->get_main_character_data() as $key=>$value) {
		$page->add_template_var('main_'. $key, $value);
	}
}


build_row_padding($page, 'weaponSlot', 'wsNum', 2);
build_row_padding($page, 'armorSlot', 'asNum', 2);
build_row_padding($page, 'skillRow', 'skillNum', 40);
build_row_padding($page, 'gearSlot', 'gsNum', 22);
build_row_padding($page, 'featsAbilitiesRow', 'featAbNum');

function build_row_padding(cs_genericPage $page, $rowName, $numVar, $thisMany=20) {
	if(isset($page->templateRows[$rowName]) && is_numeric($thisMany) && strlen($numVar)) {
		$parsedRow = "";
		for($i=0;$i<$thisMany;$i++) {
			$thisRow = $page->templateRows[$rowName];
			$repArr = array(
				$numVar	=> $i
			);
			$thisRow = $page->mini_parser($thisRow, $repArr, '{', '}');
			$thisRow = $page->mini_parser($thisRow, $repArr, '%%', '%%');
			$parsedRow .= $thisRow;
		}
		$page->add_template_var($rowName, $parsedRow);
	}
	else {
		throw new exception(__METHOD__ .": failed to locate template row (". $rowName .") or missing arguments...");
	}
}//end build_row_padding()

?>
