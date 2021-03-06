<?php


require_once(__DIR__ .'/../vendor/autoload.php');

// set a constant for testing...
define('UNITTEST__LOCKFILE', dirname(__FILE__) .'/files/rw/');
define('cs_lockfile-RWDIR', constant('UNITTEST__LOCKFILE'));
define('RWDIR', constant('UNITTEST__LOCKFILE'));
define('LIBDIR', dirname(__FILE__) .'/..');
define('SITE_ROOT', dirname(__FILE__) .'/..');
define('UNITTEST_ACTIVE', 1);

// set the timezone to avoid spurious errors from PHP
date_default_timezone_set("America/Chicago");


error_reporting(E_ALL);
