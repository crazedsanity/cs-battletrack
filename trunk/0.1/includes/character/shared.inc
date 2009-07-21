<?php
/*
 * Created on Jul 20, 2009
 */


require_once(constant('LIBDIR') .'/cs-battletrack/characterSheet.class.php');

$charId = null;
if(isset($_SESSION['dnd']['characterId']) && is_numeric($_SESSION['dnd']['characterId'])) {
	$charId = $_SESSION['dnd']['characterId'];
}
$char = new characterSheet($charId);

$page->gfObj->debugPrintOpt=1;
?>
