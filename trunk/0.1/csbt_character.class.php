<?php
/*
 * Created on Jul 13, 2009
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

//TODO: consider optionally adding the logging system.
/*
 * NOTE::: This class handles the main "character_attribute" table, along with doing "hand-offs" to other
 * 	classes when the need arises.
 */

class csbt_character extends csbt_battleTrackAbstract {
	
	protected $characterId;
	
	protected $dbObj;
	
	protected $dataCache=array();
	
	protected $id2key=array();
	
	protected $logger;
	
	protected $changesByKey=array();
	
	protected $cleanStringArr = array(
			'character_id'		=> 'int',
			'attribute_id'		=> 'int'
		);
	
	public $skillsObj;
	public $armorObj;
	
	const tableName= 'csbt_character_attribute_table';
	const seqName =  'csbt_character_attribute_table_character_attribute_id_seq';
	const pkeyName = 'character_attribute_id';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $characterIdOrName, $create=false, $playerUid=null) {
		if(!is_object($dbObj) || get_class($dbObj) != 'cs_phpDB') {
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyName, $this->cleanStringArr);
		$this->logger->logCategory = "Character";
		
		if($create===false && is_numeric($characterIdOrName) && $characterIdOrName >= 0) {
			#$this->get_character_data();
			$this->set_character_id($characterIdOrName);
		}
		elseif($create===true && is_string($characterIdOrName) && strlen($characterIdOrName) >= 2 && is_numeric($playerUid)) {
			$newId = $this->create_character($characterIdOrName, $playerUid);
			$this->set_character_id($newId);
		}
		else {
			cs_debug_backtrace(1);
			throw new exception(__METHOD__ .": not enough information to create new character or initialize existing... create=(". $create ."), characterIdOrName=(". $characterIdOrName ."), playerUid=(". $playerUid .")");
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_character_id($id) {
		if(is_numeric($id)) {
			if(is_numeric($this->characterId) && $id != $this->characterId) {
				$this->logger->log_by_class("Changed character from id=(". $this->characterId .") to (". $id .")", 'debug');
			}
			$this->characterId = $id;
			$this->skillsObj = new csbt_skill($this->dbObj,$this->characterId);
			$this->armorObj = new csbt_characterArmor($this->dbObj, $this->characterId);
			$this->abilityObj = new csbt_ability($this->dbObj, $this->characterId);
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_character($characterName, $uid) {
		if(strlen($characterName) && is_numeric($uid) && $uid > 0) {
			$sql = "INSERT INTO csbt_character_table ". 
				$this->gfObj->string_from_array(array(
					'character_name'	=> $characterName,
					'uid'				=> $uid
				), 'insert');
			try {
				$newId = $this->dbObj->run_insert($sql, 'csbt_character_table_character_id_seq');
				
				#$this->logger->log_by_class("New character (id=". $newId ."),: '". $characterName ."'", 'created character');
				$this->set_character_id($newId);
			}
			catch(Exception $e) {
				//check if it says something like 'relation "csbt_x_table" does not exist'
				if(preg_match('/ relation "[a-z0-9_]{12,}" does not exist/', $e->getMessage())) {
					$this->load_schema();
				}
				else {
					$details = __METHOD__ .":: error inserting, schema appears to be loaded, DETAILS::: ";
					if(strlen($this->dbObj->errorMsg())) {
						$details .= $this->dbObj->errorMsg();
					}
					else {
						$details .= $e->getMessage();
					}
					$this->exception_handler($details);
				}
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid name (". $characterName .") or uid (". $uid .")");
		}
		$this->load_character_defaults();
		return($this->characterId);
	}//end create_character()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT ca.*, a.attribute FROM csbt_character_attribute_table "
					."AS ca INNER JOIN csbt_attribute_table AS a USING (attribute_id) "
					."WHERE ca.character_id=". $this->characterId, 'character_attribute_id');
			
			$this->dataCache = array();
			$this->id2key = array();
			if(is_array($data)) {
				foreach($data as $id=>$attribs) {
					$key = $attribs['attribute'];
					$this->dataCache[$key] = array(
						'value'	=> $attribs['attribute_value'],
						'id'	=> $id
					);
					$this->id2key[$id] = $key;
				}
				$this->logger->log_by_class("Retrieved ". count($this->dataCache) ." attributes for id=(". $this->characterId .")", 'debug');
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid internal characterId (". $this->characterId .")");
		}
		
		return($this->dataCache);
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_main_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT * FROM csbt_character_table " .
					"WHERE character_id=". $this->characterId);
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId");
		}
		return($data);
	}//end get_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_main_character_data(array $data) {
		if(is_numeric($this->characterId)) {
			if(is_array($data) && count($data)) {
				$sql = "UPDATE csbt_character_table SET " .
						$this->gfObj->string_from_array($data, 'update', null, 'sql') .
						" WHERE character_id=". $this->characterId;
				$updateRes = $this->dbObj->run_update($sql);
			}
			else {
				$this->exception_handler(__METHOD__ .": invalid data");
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId");
		}
		
		return($updateRes);
	}//end update_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function exception_handler($message) {
		$logId = $this->logger->log_by_class($message, 'exception in code');
cs_debug_backtrace(1);
		throw new exception($message ." -- Logged (id #". $logId .")");
	}//end exception_handler()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		$defaults = array();
		
		//TODO: consider calling get_character_defaults() on all sub-objects (i.e. skills, armor, etc)
		$defaults['skills'] = $this->skillsObj->get_character_defaults();
		$defaults['armor'] = $this->armorObj->get_character_defaults();
		
		return($defaults);
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function load_character_defaults() {
		$autoSkills = $this->skillsObj->get_character_defaults();
		foreach($autoSkills as $i=>$data) {
			$res = $this->skillsObj->create_skill($data[0], $data[1]);
		}
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function __get($var) {
		if(isset($this->$var)) {
			$returnThis = $this->$var;
		}
		else {
			throw new exception(__METHOD__ .": unknown var (". $var .")");
		}
		return($returnThis);
	}//end __get()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		
		try{
			$retval = $this->get_character_data();
			if(!is_array($retval)) {
				$retval = array();
			}
			
			$skillsData = $this->skillsObj->get_sheet_data();
			$retval = $skillsData;
			
			
			$armorData = $this->armorObj->get_sheet_data();
			if(is_array($armorData)) {
				$retval = array_merge($retval, $armorData);
			}
			
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve sheet data, DETAILS::: ". $e->getMessage());
		}
		
		if(!is_array($retval) || !count($retval)) {
			$this->gfObj->debug_print($this->dbObj,1);
			throw new exception(__METHOD__ .":: invalid data or no data returned (". $retval .")");
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
}

?>
