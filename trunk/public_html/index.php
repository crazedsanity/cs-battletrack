<?php
phpinfo();
exit;
error_reporting(E_ALL);
ini_set('display_errors', 'on');
require(dirname(__FILE__) . "/../lib/cs-content/cs_globalFunctions.php");
$gf = new cs_globalFunctions;
$gf->conditional_header("/content/index.php");
exit;

?>
