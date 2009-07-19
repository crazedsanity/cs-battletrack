<?php
/*
 * Created on Jul 18, 2009
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


if($_POST) {
	$page->gfObj->debug_print($_POST,1);
	exit;
}
$page->add_template_var('wsNum', 'test');
$page->add_template_var('asNum', 'test');
$page->add_template_var('gsNum', 'test');
$page->add_template_var('skillNum', 'test');
?>
