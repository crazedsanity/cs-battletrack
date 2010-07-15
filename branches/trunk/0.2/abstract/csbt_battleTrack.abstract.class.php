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


abstract class csbt_battleTrackAbstract extends cs_webapplibsAbstract {
	
	protected $tableHandlerObj=null;
	protected $abilityObj = null;
	protected $charAbilityObj = null;
	
	abstract public function get_sheet_data();
	abstract public function get_character_defaults();
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr) {
		
		if(class_exists('cs_globalFunctions')) {
			$this->gfObj = new cs_globalFunctions;
			$this->gfObj->debugPrintOpt=1;
		}
		else {
			throw new exception(__METHOD__ .": missing required class 'cs_globalFunctions'");
		}
		
		if(is_object($dbObj) && get_class($dbObj) == 'cs_phpDB') {
			$this->dbObj = $dbObj;
		}
		else {
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		
		$this->set_version_file_location(dirname(__FILE__) .'/../VERSION');
		
		#parent::__construct(true);
		
		#$this->logger = new cs_webdblogger($dbObj, $this->get_project() .'::'. __CLASS__);
		
		#$upgradeObj = new cs_webdbupgrade(dirname(__FILE__) .'/../VERSION', dirname(__FILE__) .'/../upgrades/upgrade.xml', $dbObj->connectParams, __CLASS__ .'.lock');
		#$upgradeObj->check_versions(true);
		
		$this->tableHandlerObj = new csbt_tableHandler($dbObj, $tableName, $seqName, $pkeyField, $cleanStringArr);
		$this->abilityObj = new csbt_ability($this->dbObj);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_sheet_id($prefix, $name, $id) {
		if(is_string($name) && strlen($name) >= 1 && is_numeric($id) && $id > 0) {
			$sheetId = preg_replace('/[^a-z0-9]/', '_', strtolower($name));
			$sheetId = preg_replace('/_{2,}/', '_', $sheetId);
			$sheetId = $prefix . '__' . $sheetId . '__'. $id;
		}
		else {
			throw new exception(__METHOD__ .":: invalid name (". $name .") or id (". $id .")");
		}
		return($sheetId);
	}//end create_sheet_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_schema() {
		try {
			$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to load schema, ERROR::: ". $this->dbObj->errorMsg());
		}
	}//end load_schema()
	//-------------------------------------------------------------------------
}

?>