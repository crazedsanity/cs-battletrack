<?php

/*
 * 
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */


abstract class battleTrackAbstract extends cs_versionAbstract {
	
	public function __construct() {
		
		if(class_exists('cs_globalFunctions')) {
			$this->gfObj = new cs_globalFunctions;
			$this->gfObj->debugPrintOpt=1;
		}
		else {
			throw new exception(__METHOD__ .": missing required class 'cs_globalFunctions'");
		}
		
		$this->set_version_file_location(dirname(__FILE__) .'/../VERSION');
		
		$dbParams = array(
			'host'			=> constant('DB_PG_HOST'),
			'dbname'		=> constant('DB_PG_DBNAME'),
			'port'			=> constant('DB_PG_PORT'),
			'user'			=> constant('DB_PG_DBUSER'),
			'password'		=> constant('DB_PG_DBPASS')
		);
		$this->dbObj = new cs_phpDB('pgsql');
		$this->dbObj->connect($dbParams);
		
		$this->logger = new cs_webdblogger($this->dbObj, $this->get_project() .'::'. __CLASS__);
		
		$upgradeObj = new cs_webdbupgrade(dirname(__FILE__) .'/../VERSION', dirname(__FILE__) .'/../upgrades/upgrade.xml', $dbParams, __CLASS__ .'.lock');
		$upgradeObj->check_versions(true);
		
	}//end __construct()
}

?>