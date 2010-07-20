<?php
/*
 * Created on Jul 20, 2009
 */


require_once(constant('LIBDIR') .'/cs-battletrack/characterSheet.class.php');

$charId = null;
if($_GET['id']) {
	$charId = $_GET['id'];
}
$char = new characterSheet($charId);

$page->gfObj->debugPrintOpt=1;
?>
