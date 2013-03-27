<?php
/*
 * Created on Jul 18, 2009
 */

$page->rip_all_block_rows('content');
$page->clear_content('header');
$page->clear_content('menu');

if(strlen($charId)) {
	$char = new csbt_characterSheet($page->db, $charId, $playerUid);
	$data = $char->get_sheet_data();
	
	if(!isset($contentTemplate)) {
		$contentTemplate = null;
	}
	$char->build_sheet($page, $contentTemplate);
	$page->add_template_var('html_title', $char->get_character_name() ." - TTORP [CrazedSanity.com]");
}
else {
	$char = new csbt_characterSheet($page->db, 'Set character name...', $playerUid);
	$charId = $char->characterId;
	
	
	$obfuscator = new cs_IdObfuscator();
	$newId = $obfuscator->encode($charId);
	
	$page->conditional_header("?id=". $newId);
}

?>
