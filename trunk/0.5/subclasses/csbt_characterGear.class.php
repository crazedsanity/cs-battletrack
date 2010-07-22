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

class csbt_characterGear extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	const joinTable = 'csbt_ability_table';
	const joinTableField = 'ability_id';
	const sheetIdPrefix = 'gear';
	
	
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
			'gear_name'		=> 'sql',
			'ability_id'		=> 'int',
			'is_class_gear'	=> 'bool',
			'gear_mod'			=> 'int',
			'ability_mod'		=> 'int',
			'ranks'				=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_gear($name, $ability, array $fields=null) {
		if(is_string($name) && strlen($name)) {
			if(is_array($fields) && count($fields) > 0) {
				$insertArr = $fields;
				$insertArr['gear_name'] = $name;
			}
			else {
				$insertArr = array('gear_name'=>$name);
			}
			$insertArr['ability_id'] = $this->abilityObj->get_ability_id($ability);
			$insertArr['character_id'] = $this->characterId;
			
			try {
				//get their ability modifier.
				$charAbilityObj = new csbt_characterAbility($this->dbObj, $this->characterId);
				$insertArr['ability_mod'] = $charAbilityObj->get_ability_modifier($ability);

				$insertArr['gear_mod'] = $insertArr['ability_mod'];
				if(isset($insertArr['ranks'])) {
					$insertArr['gear_mod'] += $insertArr['ranks'];
				}
				if(isset($insertArr['misc_mod'])) {
					$insertArr['gear_mod'] += $insertArr['misc_mod'];
				}
				
				$newId = $this->tableHandlerObj->create_record($insertArr);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to create character gear (". $name ."), DETAILS:::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .":: unable to create gear without name");
		}
		
		return($newId);
	}//end create_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_gear($gearId, array $updates) {
		if(is_numeric($gearId) && $gearId > 0 && is_array($updates) && count($updates) > 0) {
			try {
				$retval = $this->tableHandlerObj->update_record($gearId, $updates, true);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				throw new exception(__METHOD__ .":: invalid gearId (". $gearId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_gear_by_name($name) {
		$data = $this->tableHandlerObj->get_single_record('gear_name', $name);
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_gear_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_gear_by_id($gearId) {
		$data = $this->tableHandlerObj->get_single_record(self::pkeyField, $gearId);
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_gear_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_gear($byAbilityName=null) {
		
		$sql = 'SELECT t.*, t2.ability_name FROM '. self::tableName .' AS t INNER JOIN '. self::joinTable .' AS t2 '
			. ' USING ('. self::joinTableField .') WHERE character_id='. $this->characterId;
		
		if(!is_null($byAbilityName) && !is_numeric($byAbilityName) && strlen($byAbilityName)) {
			$sql .= " AND t.ability_id=". $this->abilityObj->get_ability_id($byAbilityName);
		}
		$sql .= ' ORDER BY gear_name';
		
		try {
			$retval = $this->dbObj->run_query($sql, 'character_gear_id');
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve character gear, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		try {
			$data = $this->get_character_gear();
			$retval = array();
			
			$makeKeysFrom = array(
				'gear_name', 'ability_name', 'is_class_gear', 'gear_mod', 
				'ability_mod', 'ranks', 'misc_mod'
			);
			foreach($data as $id=>$gearData) {
				foreach($makeKeysFrom as $indexName) {
					if(isset($gearData[$indexName])) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName, $gearData['character_gear_id']);
						$retval[$sheetKey] = $data[$id][$indexName];
					}
					else {
						throw new exception(__METHOD__ .":: failed to create key for missing index '". $indexName ."'");
					}
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
	public function get_character_defaults() {
		
		$autoSkills = array();
		
		//Skills added as a numbered array so I don't have to manually renumber if an item is added or removed.
		{
		    $autoSkills[] = array("Appraise",			"int");
		    $autoSkills[] = array("Balance",			"dex");
		    $autoSkills[] = array("Bluff",				"cha");
		    $autoSkills[] = array("Climb",				"str");
		    $autoSkills[] = array("Concentration",		"con");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Decipher Script",	"int");
		    $autoSkills[] = array("Diplomacy",			"cha");
		    $autoSkills[] = array("Disable Device",		"int");
		    $autoSkills[] = array("Disguise",			"cha");
		    $autoSkills[] = array("Escape Artist",		"dex");
		    $autoSkills[] = array("Forgery",			"int");
		    $autoSkills[] = array("Gather Information",	"cha");
		    $autoSkills[] = array("Handle Animal",		"cha");
		    $autoSkills[] = array("Heal",				"wis");
		    $autoSkills[] = array("Hide",				"dex");
		    $autoSkills[] = array("intimidate",			"cha");
		    $autoSkills[] = array("Jump",				"str");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Listen",				"wis");
		    $autoSkills[] = array("Move Silently",		"dex");
		    $autoSkills[] = array("Open Lock",			"dex");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Profession ()",		"wis");
		    $autoSkills[] = array("Profession ()",		"wis");
		    $autoSkills[] = array("Ride",				"dex");
		    $autoSkills[] = array("Search",				"int");
		    $autoSkills[] = array("Sense Motive",		"wis");
		    $autoSkills[] = array("Sleight of Hand",	"dex");
		    $autoSkills[] = array("Spellcraft",			"int");
		    $autoSkills[] = array("Spot",				"wis");
		    $autoSkills[] = array("Survival",			"wis");
		    $autoSkills[] = array("Swim",				"str");
		    $autoSkills[] = array("Tumble",				"dex");
		    $autoSkills[] = array("Use Magic Device",	"cha");
		    $autoSkills[] = array("Use Rope",			"dex");
		}
		return($autoSkills);
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_character_defaults() {
		$autoSkills = $this->get_character_defaults();
		foreach($autoSkills as $i=>$data) {
			$res = $this->create_gear($data[0], $data[1]);
		}
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function calculate_gear_mod(array $gearData) {
		$requiredIndexes = array('ability_mod', 'ranks', 'misc_mod');
		$gearMod = 0;
		foreach($requiredIndexes as $indexName) {
			if(isset($gearData[$indexName])) {
				$gearMod += $gearData[$indexName];
			}
			else {
				throw new exception(__METHOD__ .":: missing required index (". $indexName .")");
			}
		}
		return($gearMod);
	}//end calculate_gear_mod()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		
		try {
			$oldSkillVals = $this->get_gear_by_id($recordId);
			switch($updateBitName) {
				case 'ability_mod':
				case 'gear_name':
				case 'ranks':
				case 'misc_mod':
				case 'is_class_gear':
					break;
				
				default:
					throw new exception(__METHOD__ .":: invalid updateBitName (". $updateBitName .")");
			}
			
			//now perform the update.
			$oldSkillVals[$updateBitName] = $newValue;
			$updatesArr = array(
				$updateBitName	=> $newValue,
				'gear_Mod'		=> $this->calculate_gear_mod($oldSkillVals)
			);
			$this->update_gear($recordId, $updatesArr);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to handle update, DETAILS::: ". $e->getMessage());
		}
		
	}//end handle_update()
	//-------------------------------------------------------------------------
}

?>
