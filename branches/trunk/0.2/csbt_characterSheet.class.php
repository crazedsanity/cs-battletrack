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

class csbt_characterSheet extends csbt_battleTrackAbstract {
	
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
	
	protected $skillsObj;
	protected $characterObj;
	
	const tableName= 'csbt_character_attribute_table';
	const seqName =  'csbt_character_attribute_table_character_attribute_id_seq';
	const pkeyName = 'character_attribute_id';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $characterIdOrName=null, $playerUid=null) {
		parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyName, $this->cleanStringArr);
		$this->dbObj = $dbObj;
		$this->logger->logCategory = "Character Sheet";
		
		if(is_numeric($characterIdOrName) && $characterIdOrName >= 0) {
			$this->set_character_id($characterIdOrName, false, $playerUid);
			#$this->get_character_data();
		}
		elseif(is_string($characterIdOrName) && strlen($characterIdOrName) >= 2) {
			$this->characterObj = new csbt_character($dbObj, $characterIdOrName, true, $playerUid);
			$this->set_character_id($this->characterObj->characterId);
		}
		else {
			throw new exception(__METHOD__ .": characterId or name of new character is required, given invalid value (". $characterIdOrName .")");
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
			$this->skillObj = new csbt_skill($this->dbObj,$this->characterId);
			$this->characterObj = new csbt_character($this->dbObj, $this->characterId);
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_character($characterName, $uid) {
		try{
			$this->characterId = $this->characterObj->create_character($characterName, $uid);
		}
		catch(Exception $e) {
			$this->exception_handler(__METHOD__ .":: failed to create character, DETAILS::: ". $e->getMessage());
		}
		return($this->characterId);
	}//end create_character()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_data() {
		if(is_object($this->characterObj)) {
			$this->characterObj->get_character_data();
		}
		else {
			$this->exception_handler(__METHOD__ .":: failed to retrieve character data, not initialized");
		}
		
		return($this->characterObj->dataCache);
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_main_character_data() {
		try {
			$data = $this->characterObj->get_main_character_data();
		}
		catch(Exception $e) {
			$this->exception_handler(__METHOD__ .":: failed to retrieve character data, DETAILS::: ". $e->getMessage());
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
		throw new exception($message ." -- Logged (id #". $logId .")");
	}//end exception_handler()
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
		if(!is_numeric($this->characterId)) {
			throw new exception(__METHOD__ .":: characterId not set");
		}
		try {
			$retval = $this->characterObj->get_sheet_data();
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve sheet data, DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		return($this->characterObj->get_character_defaults());
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
}

?>
