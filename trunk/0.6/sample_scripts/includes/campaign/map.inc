<?php

$page->add_template_var('menu', '');
$page->add_template_var('infobar', '');
$page->add_template_var('footer', '');

$page->add_template_var('html_title', 'Grid Movement Test');


$dataArr = array();
if(isset($_SESSION['TTORP'])) {
	$dataArr = $_SESSION['TTORP'];
}

$a2p = new cs_arrayToPath($dataArr);
if($_GET['mapSize']) {
	$mapSize = $_GET['mapSize'];
}
elseif($a2p->get_data($finalURL ."/size")) {
	$mapSize = $a2p->get_data($finalURL ."/size");
}
else {
	//Default map size... (HxW)
	$mapSize="8x8"; 
}

$a2p->set_data($finalURL ."/size", $mapSize);


$mapObj = new csbt_mapBuilder($mapSize);
$page->add_template_var('ttorpTable', $mapObj->build_grid());
$page->add_template_var('mapSizeX', $mapObj->get_size('x'));
$page->add_template_var('mapSizeY', $mapObj->get_size('y'));
