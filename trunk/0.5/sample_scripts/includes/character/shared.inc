<?php
/*
 * Created on Jul 20, 2009
 */


$charId = null;
if(is_numeric($_GET['id'])) {
	$charId = $_GET['id'];
}

$playerUid = $_SESSION['auth']['userInfo']['uid'];
$userChar = new csbt_userCharacterList($page->db, $playerUid);
?>