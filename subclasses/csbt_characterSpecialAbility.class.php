<?php 

/*
 * NOTE::: This class is for special abilities AND feats.  The only formal 
 * 	distinction between them is that a feat is *chosen* (usually) by the player 
 * 	at a certain level, and has prerequisites; special abilities, on the other 
 * 	hand, are not chosen, but rather given to the character from a class level 
 * 	or as a racial bonus... If there is a significant reason to, they can be 
 * 	separated into separate classes (and tables) later.
 */

class csbt_characterSpecialAbility extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	public $updatesByKey = array();
	
	//To fix a shortcoming in (PHP/PostgreSQL) with regard to length of sequences, part of it had to be shorted to "sa" instead of "special_ability".
	const tableName = 'csbt_character_sa_table';
	const tableSeq  = 'csbt_character_sa_table_character_sa_id_seq';
	const pkeyField = 'character_sa_id';
	const sheetIdPrefix = 'specialAbility';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			$this->_exception_handler(__METHOD__ .":: invalid character id (". $characterId .")");
		}
		$this->characterId = $characterId;
		$this->fields = array(
			'character_id'			=> 'int',
			'special_ability_name'	=> 'sql',
			'description'			=> 'sql',
			'book_reference'		=> 'sql'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->logger->logCategory = "Character Special Ability";
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_special_ability($name, array $fields=null) {
		if(is_null($name) || !strlen($name)) {
			$name = "    ";
		}
		$insertArr = array();
		if(is_array($fields) && count($fields) > 0) {
			$insertArr = $fields;
		}
		$insertArr['special_ability_name'] = $name;
		$insertArr['character_id'] = $this->characterId;
		
		try {
			$newId = $this->tableHandlerObj->create_record($insertArr);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to create character special_ability (". $name ."), DETAILS:::: ". $e->getMessage() ."\n\nDATA::: ". $this->gfObj->debug_print($insertArr,0) ."\n\nFIELDS::: ". $this->gfObj->debug_print($fields,0));
		}
		
		return($newId);
	}//end create_special_ability()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_special_ability($special_abilityId, array $updates) {
		if(is_numeric($special_abilityId) && $special_abilityId > 0 && is_array($updates) && count($updates) > 0) {
			try {
				$retval = $this->tableHandlerObj->update_record($special_abilityId, $updates, true);
			}
			catch(Exception $e) {
				$this->_exception_handler(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				$this->_exception_handler(__METHOD__ .":: invalid special_abilityId (". $special_abilityId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_special_ability()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_special_ability_by_name($name) {
		try {
			$filterArr = array(
				'character_id'	=> $this->characterId,
				'special_ability_name'		=> $name
			);
			$data = $this->tableHandlerObj->get_single_record($filterArr);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve special_ability, DETAILS::: ". $e->getMessage());
		}
		
		return($data);
	}//end get_special_ability_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_special_ability_by_id($special_abilityId) {
		try {
			$filterArr = array(
				'character_id'	=> $this->characterId,
				self::pkeyField	=> $special_abilityId
			);
			$data = $this->tableHandlerObj->get_single_record($filterArr);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve special_ability, DETAILS::: ". $e->getMessage());
		}
		
		return($data);
	}//end get_special_ability_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_special_abilities() {
		
		try {
			$retval = $this->tableHandlerObj->get_records(array('character_id'=>$this->characterId));
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve character special_ability, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_special_abilities()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		try {
			$data = $this->get_character_special_abilities();
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve data, DETAILS::: ". $e->getMessage());
		}
		
		$retval = array();
		if(is_array($data) && count($data) > 0) {
			
			$makeKeysFrom = $this->get_columns_for_sheet_keys();
			foreach($data as $id=>$special_abilityData) {
				foreach($makeKeysFrom as $indexName) {
					if(array_key_exists($indexName, $special_abilityData)) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName);
						$retval[$id][$sheetKey] = $data[$id][$indexName];
					}
					else {
						$this->_exception_handler(__METHOD__ .":: failed to create key for missing index '". $indexName ."'");
					}
				}
			}
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		return(array());
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		
		try {
			//now perform the update.
			if($updateBitName == 'new') {
				$retval = $this->create_special_ability($newValue);
			}
			else {
				$retval = $this->update_special_ability($recordId, array($updateBitName => $newValue));
			}
			$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, $updateBitName, $recordId)] = $newValue;
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to handle update, DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
}

?>
