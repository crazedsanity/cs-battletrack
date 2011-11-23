<?php
/*
 * Created on Jul 18, 2009
 */


$page->rip_all_block_rows('content');
$page->clear_content('header');
$page->clear_content('menu');

if(is_numeric($charId)) {
	$char = new csbt_characterSheet($page->db, $charId, $playerUid);
	$data = $char->get_sheet_data();
#$page->gfObj->debug_print($data,1);
	
	$char->build_sheet($page, $contentTemplate);
	$page->add_template_var('html_title', $char->get_character_name() ." - TTORP [CrazedSanity.com]");
}
else {
	$char = new csbt_characterSheet($page->db, 'Set character name...', $playerUid);
	$charId = $char->characterId;
	$page->conditional_header("?id=". $charId);
}

?>