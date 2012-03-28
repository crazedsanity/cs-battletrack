<?php
/*
 * Created on Jul 20, 2009
 */



$charList = $userChar->get_character_list();

$page->rip_all_block_rows();

$rowName = 'characterList_unassigned';
$rowName_assigned = 'characterList_assigned';
$idObfuscator = new cs_idObfuscator();
if(isset($page->templateRows[$rowName])) {
	if(is_array($charList)) {
		$parsedRows = "";
		foreach($charList as $id=>$info) {
			$name = $info['character_name'];
			if(!strlen($name)) {
				$name = "<font color=\"red\">[Not Applicable]</font>";
			}
			$repArr = array(
				'id'	=> $idObfuscator->encode($id),
				'name'	=> $name
			);
			$parsedRows .= $page->gfObj->mini_parser($page->templateRows[$rowName], $repArr, '{', '}');
		}
		$page->add_template_var($rowName, $parsedRows);
	}
}
else {
	throw new exception(__METHOD__ .": missing template row");
}
?>
