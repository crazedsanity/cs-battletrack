<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

class csbt_characterSave extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	public $updatesByKey = array();
	
	const tableName = 'csbt_character_save_table';
	const tableSeq  = 'csbt_character_save_table_character_save_id_seq';
	const pkeyField = 'character_save_id';
	const joinTable = 'csbt_ability_table';
	const joinTableField = 'ability_id';
	const sheetIdPrefix = 'saves';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			throw new exception(__METHOD__ .":: invalid character id (". $characterId .")");
		}
		$this->characterId = $characterId;
		$this->fields = array(
			'character_id'		=> 'int',
			'save_name'			=> 'sql',
			'ability_id'		=> 'int',
			'base_mod'			=> 'int',
			'magic_mod'			=> 'int',
			'misc_mod'			=> 'int',
			'temp_mod'			=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_save($name, $ability, array $fields=null) {
		if(is_string($name) && strlen($name)) {
			if(is_numeric($ability)) {
				//test to make sure it is valid.
				$this->abilityObj->get_ability_name($ability);
				$abilityId = $ability;
			}
			else {
				$abilityId = $this->abilityObj->get_ability_id($ability);
			}
			$insertData = array(
				'save_name'		=> $name,
				'character_id'	=> $this->characterId,
				'ability_id'	=> $abilityId
			);
			if(is_array($fields)) {
				$insertData = array_merge($fields, $insertData);
			}
			
			try {
				$newId = $this->tableHandlerObj->create_record($insertData);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to create record, DETAILS::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .":: unable to create save without name");
		}
		
		return($newId);
	}//end create_save()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_save($saveId, array $updates) {
		if(is_numeric($saveId) && $saveId > 0 && is_array($updates) && count($updates) > 0) {
			try {
				$retval = $this->tableHandlerObj->update_record($saveId, $updates, true);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				throw new exception(__METHOD__ .":: invalid saveId (". $saveId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_save()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_save_by_name($name) {
		$filterArr = array(
			'character_id'	=> $this->characterId,
			'save_name'	=> $name
		);
		$data = $this->tableHandlerObj->get_single_record($filterArr);
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_save_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_save_by_id($saveId) {
		$data = $this->tableHandlerObj->get_single_record(array(self::pkeyField => $saveId));
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_save_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_saves($byAbilityId=null) {
		
		$sql = "SELECT s.*, a.ability_name FROM ". self::tableName ." AS s INNER JOIN "
			. "csbt_ability_table AS a USING (ability_id) WHERE s.character_id="
			. $this->characterId ;
		
		if(is_numeric($byAbilityId)) {
			$sql .= " AND s.ability_id=". $byAbilityId;
		}
		
		$sql .= " ORDER BY save_name";
		
		try {
			$retval = $this->dbObj->run_query($sql, 'character_save_id');
			
			//TODO: get ability modifier (ability_mod) and total (save_total)
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve character saves, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_saves()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data($byAbilityId=null) {
		try {
			$data = $this->get_character_saves($byAbilityId);
			$retval = array();
			$makeKeysFrom = $this->get_columns_for_sheet_keys();
			$makeKeysFrom[] = 'ability_name';
			unset($makeKeysFrom[array_search('ability_id', $makeKeysFrom)]);
			
			if(is_array($data)) {
				foreach($data as $id=>$saveInfo) {
					$total=0;
					foreach($makeKeysFrom as $name) {
						$saveName = $saveInfo['save_name'] .'_'. $name;
						$sheetId = $this->create_sheet_id(self::sheetIdPrefix, $saveName);
						$retval[$sheetId] = $saveInfo[$name];
						
						if(preg_match('/_mod$/', $name)) {
							$total += $saveInfo[$name];
						}
					}
					
					//add ability modifier.
					$abilityMod = $this->abilityObj->get_ability_modifier($saveInfo['ability_name']);
					$retval[$this->create_sheet_id(self::sheetIdPrefix, $saveInfo['save_name'] .'_ability_mod')] = $abilityMod;
					$total += $abilityMod;
					
					$retval[$this->create_sheet_id(self::sheetIdPrefix, $saveInfo['save_name'] .'_total')] = $total;
				}
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve data, DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function calculate_save_mod(array $saveData) {
		$requiredIndexes = array('ability_mod', 'ranks', 'misc_mod');
		$saveMod = 0;
		foreach($requiredIndexes as $indexName) {
			if(isset($saveData[$indexName])) {
				$saveMod += $saveData[$indexName];
			}
			else {
				throw new exception(__METHOD__ .":: missing required index (". $indexName .")");
			}
		}
		return($saveMod);
	}//end calculate_save_mod()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		
		try {
			$oldSkillVals = $this->get_save_by_id($recordId);
			switch($updateBitName) {
				case 'ability_mod':
				case 'save_name':
				case 'ranks':
				case 'misc_mod':
				case 'is_class_save':
					break;
				
				default:
					throw new exception(__METHOD__ .":: invalid updateBitName (". $updateBitName .")");
			}
			
			//now perform the update.
			$oldSkillVals[$updateBitName] = $newValue;
			$updatesArr = array(
				$updateBitName	=> $newValue,
				'save_Mod'		=> $this->calculate_save_mod($oldSkillVals)
			);
			$this->update_save($recordId, $updatesArr);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to handle update, DETAILS::: ". $e->getMessage());
		}
		
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		$defaultNames = array('will', 'fort', 'reflex');
		$defaultSubItems = array('base', 'magic', 'misc', 'temp');
		
		$defaults = array();
		foreach($defaultNames as $name) {
			foreach($defaultSubItems as $sub) {
				$defaults[$name][$sub .'_mod'] = 0;
			}
		}
		$defaults['fort']['ability'] = 'con';
		$defaults['reflex']['ability'] = 'dex';
		$defaults['will']['ability'] = 'wis';
		
		return($defaults);
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_character_defaults() {
		$defaults = $this->get_character_defaults();
		
		foreach($defaults as $name=>$info) {
			$ability = $info['ability'];
			unset($info['ability']);
			$this->create_save($name, $ability, $info);
		}
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
}

?>
