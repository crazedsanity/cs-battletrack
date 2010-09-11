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
	
	public $tableHandlerObj=null;
	protected $abilityObj = null;
	protected $charAbilityObj = null;
	protected $fields=null;
	protected $pkeyField=null;
	protected $characterId=null;
	
	
	abstract public function get_sheet_data();
	abstract public function get_character_defaults();
	abstract public function handle_update($sheetBitName, $recId=null, $newValue);
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr, $characterId=null, $createAbilityObj=true) {
		
		if(class_exists('cs_globalFunctions')) {
			$this->gfObj = new cs_globalFunctions;
			$this->gfObj->debugPrintOpt=1;
		}
		else {
			throw new exception(__METHOD__ .": missing required class 'cs_globalFunctions'");
		}
		
		if(!is_null($characterId) && is_numeric($characterId)) {
			$this->characterId = $characterId;
		}
		
		if(is_object($dbObj) && get_class($dbObj) == 'cs_phpDB') {
			$this->dbObj = $dbObj;
			$this->logger = new cs_webdblogger($this->dbObj, __METHOD__, false);
		}
		else {
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		
		parent::__construct(true);
		
		if(!defined('csbt__UPGRADE') && !defined('SIMPLE_TEST')) {
			$upgradeObj = new cs_webdbupgrade(dirname(__FILE__) .'/../VERSION', dirname(__FILE__) .'/../upgrades/upgrade.xml', $dbObj->connectParams, __CLASS__ .'.lock');
			define('csbt__UPGRADE', 1);
			$upgradeObj->check_versions(true);
		}
		$this->pkeyField = $pkeyField;
		$this->tableHandlerObj = new csbt_tableHandler($dbObj, $tableName, $seqName, $pkeyField, $cleanStringArr, $this->characterId);
		if($createAbilityObj===true) {
			$this->abilityObj = new csbt_characterAbility($this->dbObj, $this->characterId);
		}
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_sheet_id($prefix=null, $name, $id=null) {
		if(is_string($name) && strlen($name) >= 1) {
			if(!is_null($prefix) && strlen($prefix)) {
				$prefix = preg_replace('/_/', '', $prefix);
				$sheetId = preg_replace('/[^a-z0-9]/', '_', $name);
				$sheetId = preg_replace('/_{2,}/', '_', $sheetId);
				$sheetId = $prefix .'__'. strtolower($name);
			}
			else {
				$sheetId = strtolower($name);
			}
			
			if(!is_null($id) && is_numeric($id) && $id > 0) {
				$sheetId .=  '__'. $id;
			}
			elseif(!is_numeric($id) && preg_match('/^gen/', strtolower($id))) {
				$sheetId .= '__generated';
			}
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
	
	
	
	//-------------------------------------------------------------------------
	public function parse_sheet_id($sheetId) {
		if(strlen($sheetId) && preg_match('/__/', $sheetId)) {
			$bits = explode('__', $sheetId);
			if(count($bits) > 3 || count($bits) < 2) {
				throw new exception(__METHOD__ .":: invalid number of bits (". count($bits) .") [expecting 2-3] in sheetId (". $sheetId .")");
			}
		}
		else {
			throw new exception(__METHOD__ .":: invalid sheetId (". $sheetId .")");
		}
		return($bits);
	}//end parse_sheet_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_columns_for_sheet_keys() {
		//TODO: make all the subclasses & such use this when calling get_sheet_data().
		if(is_array($this->fields) && count($this->fields)) {
			$myFields = $this->fields;
			if(isset($myFields['character_id'])) {
				unset($myFields['character_id']);
			}
			if(isset($myFields[$this->pkeyField])) {
				unset($myFields[$this->pkeyField]);
			}
			$retval = array_keys($myFields);
		}
		else {
			throw new exception(__METHOD__ .":: failed to create list of columns for sheet keys, no list of fields available");
		}
		return($retval);
	}//end get_columns_for_sheet_keys()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function set_character_id($id) {
		$this->characterId = $id;
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function do_log($details, $type='update', array $xAttribs=null) {
		// log_by_class($details, $className="error", $uid=NULL, array $logAttribs=NULL)
		$retval = $this->logger->log_by_class($details, $type, $xAttribs);
		return($retval);
	}//end do_log()
	//-------------------------------------------------------------------------
}

?>