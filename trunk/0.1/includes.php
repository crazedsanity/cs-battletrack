<?php
/*
 * Created on Apr 1, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


define(BT_BASEDIR, dirname(__FILE__ .'/../'));

function __autoload($className) {
	$retval = FALSE;
	$possible = array(
		dirname(__FILE__) .'/'. $className .'.php',
		dirname(__FILE__) .'/'. $className .'Class.php',
		dirname(__FILE__) .'/'. $className .'.class.php',
		dirname(__FILE__) .'/abstractClass/'. $className .'.class.php'
	);
	
	foreach($possible as $fileName) {
		if(file_exists($fileName)) {
			require_once($fileName);
			$retval = TRUE;
			break;
		}
	}
	
	if($retval !== TRUE) {
		throw new exception(__FUNCTION__ .": unable to find class file for (". $className .")");
	}
	
	return($retval);
	
}//end __autoload()
?>
