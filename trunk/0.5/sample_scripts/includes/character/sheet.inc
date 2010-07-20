<?php
/*
 * Created on Jul 18, 2009
 */


$page->rip_all_block_rows('content');
$page->clear_content('header');
$page->clear_content('menu');

if(is_numeric($charId)) {
	$data = $char->get_character_data($charId);
	$skillInfo = array();
}
else {

	$charId = $char->create_character('Set character name...', $_SESSION['auth']['userInfo']['uid']);
	$page->conditional_header("?id=". $charId);
}
$page->gfObj->debug_print(array_keys($data),1);
foreach($data as $key=>$subData) {
	if(preg_match('/^skills-[0-9]{1,}-ability/', $key)) {
		$bits = explode('-', $key);
		//skills-44-ability__special == abilities-str-mod
		$t=$key .'__special';
		$v='abilities-'. strtolower($subData['value']) .'-mod';
		$page->add_template_var($t, $data[$v]['value']);
	}
	$page->add_template_var($key, $subData['value']);
}
foreach($char->get_main_character_data() as $key=>$value) {
	$page->add_template_var('main_'. $key, $value);
}

build_row_padding($page, 'weaponSlot', 'wsNum', 2);
build_row_padding($page, 'armorSlot', 'asNum', 2);
build_row_padding($page, 'skillRow', 'skillNum', 50);
build_row_padding($page, 'gearSlot', 'gsNum', 27);
build_row_padding($page, 'featsAbilitiesRow', 'featAbNum', 27);

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
