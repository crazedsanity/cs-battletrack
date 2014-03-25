<?php
/*
 * Created on Jul 20, 2009
 */


$charId = null;

$idObfuscator = new cs_idObfuscator();

if(isset($_GET['id']) && is_numeric($_GET['id'])) {
	//TODO: stop allowing the "id" number to be passed directly, to avoid snooping.
	$charId = $_GET['id'];
}
elseif(isset($_GET['id'])) {
	$charId = $idObfuscator->decode($_GET['id']);
}

