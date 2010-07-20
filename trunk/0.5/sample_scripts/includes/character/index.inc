<?php
/*
 * Created on Jul 20, 2009
 */


require_once(constant('LIBDIR') .'/cs-battletrack/userCharacterList.class.php');


$userChar = new userCharacterList($_SESSION['auth']['userInfo']['uid']);

$charList = $userChar->get_character_list();

$page->rip_all_block_rows();

$rowName = 'characterList';
if(isset($page->templateRows[$rowName])) {
	if(is_array($charList)) {
		$parsedRows = "";
		foreach($charList as $id=>$name) {
			if(!strlen($name)) {
				$name = "<font color=\"red\">[Not Applicable]</font>";
			}
			$repArr = array(
				'id'	=> $id,
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
