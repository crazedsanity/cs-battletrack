<?php
/*
 * Created on Jul 18, 2009
 */


require_once(constant('LIBDIR') .'/cs-battletrack/characterSheet.class.php');

$char = new characterSheet('18');

if($_POST) {
	#$page->gfObj->debug_print($_POST,1);
	
	$_POST['character_id'] = 18;	
	if(is_numeric($_POST['character_id'])) {
		$char->set_character_id($_POST['character_id']);
		unset($_POST['character_id']);
	}
	else {
		//$_POST['main']['name']
		$this->gfObj->debug_print($char->create_character('Tester McTesterson', $_SESSION['auth']['userInfo']['uid']));
	}
	unset($_POST['main']);
	
	//now create all those attributes.
	$this->gfObj->debug_print($char->update_character_data($_POST),1);
	exit;
}
$page->add_template_var('wsNum', 'test');
$page->add_template_var('asNum', 'test');
$page->add_template_var('gsNum', 'test');
$page->add_template_var('skillNum', 'test');
?>
