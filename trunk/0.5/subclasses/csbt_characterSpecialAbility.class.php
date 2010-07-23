<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 * 
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
	
	//To fix a shortcoming in (PHP/PostgreSQL) with regard to length of sequences, part of it had to be shorted to "sa" instead of "special_ability".
	const tableName = 'csbt_character_sa_table';
	const tableSeq  = 'csbt_character_sa_table_character_sa_id_seq';
	const pkeyField = 'character_sa_id';
	const sheetIdPrefix = 'special_ability';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			throw new exception(__METHOD__ .":: invalid character id (". $characterId .")");
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
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_special_ability($name, array $fields=null) {
		if(is_string($name) && strlen($name)) {
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
				throw new exception(__METHOD__ .":: failed to create character special_ability (". $name ."), DETAILS:::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .":: unable to create special_ability without name");
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
				throw new exception(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				throw new exception(__METHOD__ .":: invalid special_abilityId (". $special_abilityId .") or invalid/not enough fields");
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
			$data = $this->calculate_special_ability_weight($data);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve special_ability, DETAILS::: ". $e->getMessage());
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
			$data = $this->calculate_special_ability_weight($data);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve special_ability, DETAILS::: ". $e->getMessage());
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
			throw new exception(__METHOD__ .":: failed to retrieve character special_ability, DETAILS::: ". $e->getMessage());
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
			throw new exception(__METHOD__ .":: failed to retrieve data, DETAILS::: ". $e->getMessage());
		}
		
		$retval = array();
		if(is_array($data) && count($data) > 0) {
			
			$makeKeysFrom = $this->get_columns_for_sheet_keys();
			foreach($data as $id=>$special_abilityData) {
				foreach($makeKeysFrom as $indexName) {
					if(isset($special_abilityData[$indexName])) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName, $special_abilityData[self::pkeyField]);
						$retval[$sheetKey] = $data[$id][$indexName];
					}
					else {
						throw new exception(__METHOD__ .":: failed to create key for missing index '". $indexName ."'");
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
			$retval = $this->update_special_ability($recordId, array($updateBitName => $newValue));
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to handle update, DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
}

?>
