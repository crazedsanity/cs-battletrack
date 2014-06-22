<?php
/*
 * Created on Jul 18, 2009
 */

$page->rip_all_block_rows('content');

//TODO: this may conflict with other systems if they have template variables that begin with "sheet__" and have block rows defined
foreach($page->templateVars as $k=>$v) {
	if(preg_match('/^sheet__/', $k)) {
		$page->rip_all_block_rows($k);
	}
}

$obfuscator = new cs_idObfuscator();

if(strlen($charId)) {
	$char = new csbt_characterSheet($page->db, $charId, $playerUid);
	$data = $char->get_sheet_data();
//cs_global::debug_print($data,1);
//exit;

	
	if(!isset($contentTemplate)) {
		$contentTemplate = null;
	}
	$char->build_sheet($page, $contentTemplate);
	$page->add_template_var('addSkill__selectAbility', $char->create_ability_select($page));
	$page->add_template_var('html_title', $char->character_name ." - TTORP [CrazedSanity.com]");
}
else {
	
	try {
		$page->db->beginTrans();
		$char = new csbt_characterSheet($page->db, 'Set character name...', $playerUid);
		$charId = $char->characterId;
		
		if(isset($_GET['campaignId']) && is_numeric($_GET['campaignId'])) {
			csbt_campaign::add_player($page->db, $_GET['campaignId'], $charId);
		} 
		
		$page->db->commitTrans();
		
		$newId = $obfuscator->encode($charId);
		$page->conditional_header("?id=". $newId);
	}
	catch(Exception $ex) {
		$page->db->rollbackTrans();
		
		cs_genericPage::set_message(
				"Error Creating Character", 
				"There was an error creating your character... ". $ex->getMessage(), 
				null, 
				"error"
		);
		
		$uriBits = preg_split('~/sheet~', $_SERVER['REQUEST_URI']);
		
		$page->conditional_header($uriBits[0]);
	}
}

