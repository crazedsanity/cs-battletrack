<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/subclasses/csbt_skill.class.php $
 * $Id: csbt_skill.class.php 123 2010-11-11 02:09:32Z crazedsanity $
 * $LastChangedDate: 2010-11-10 20:09:32 -0600 (Wed, 10 Nov 2010) $
 * $LastChangedRevision: 123 $
 * $LastChangedBy: crazedsanity $
 */

class csbt_skill extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	public $updatesByKey = array();
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	const joinTable = 'csbt_ability_table';
	const joinTableField = 'ability_id';
	const sheetIdPrefix = 'skills';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			$this->_exception_handler(__METHOD__ .":: invalid character id (". $characterId .")");
		}
		$this->characterId = $characterId;
		$this->fields = array(
			'character_id'		=> 'int',
			'skill_name'		=> 'sql',
			'ability_id'		=> 'int',
			'is_class_skill'	=> 'bool',
			'ranks'				=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->logger->logCategory = "Character Skill";
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_skill($name, $ability, array $fields=null) {
		if(is_string($name) && strlen($name)) {
			if(is_array($fields) && count($fields) > 0) {
				$insertArr = $fields;
				$insertArr['skill_name'] = $name;
			}
			else {
				$insertArr = array('skill_name'=>$name);
			}
			$insertArr['ability_id'] = $this->abilityObj->get_ability_id($ability);
			$insertArr['character_id'] = $this->characterId;
			
			try {
				//get their ability modifier.
				$charAbilityObj = new csbt_characterAbility($this->dbObj, $this->characterId);
				
				$newId = $this->tableHandlerObj->create_record($insertArr);
				
				//add stuff to internal "updatesByKey", so the sheet can be updated properly.
				try {
				$myData = $this->get_skill_by_id($newId);
				foreach($myData as $k=>$v) {
					$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, $k, $newId)] = $v;
				}
				//$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'skill_mod', $recordId)] = $newSkillMod;
				}
				catch(Exception $e) {
					$this->_exception_handler(__METHOD__ .": failed to get data for newId (". $newId .")... ". $e->getMessage());
				}
			}
			catch(Exception $e) {
				$this->_exception_handler(__METHOD__ .":: failed to create character skill (". $name ."), DETAILS:::: ". $e->getMessage() ."\n\n". $this->gfObj->debug_print($fields,0));
			}
		}
		else {
			$this->_exception_handler(__METHOD__ .":: unable to create skill without name");
		}
		
		return($newId);
	}//end create_skill()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_skill($skillId, array $updates) {
		if(is_numeric($skillId) && $skillId > 0 && is_array($updates) && count($updates) > 0) {
			try {
				$retval = $this->tableHandlerObj->update_record($skillId, $updates, true);
				$data = $this->get_skill_by_id($skillId);
				foreach($data as $k=>$v) {
					$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, $k, $skillId)] = $v;
				}
			}
			catch(Exception $e) {
				$this->_exception_handler(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				$this->_exception_handler(__METHOD__ .":: invalid skillId (". $skillId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_skill()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_skill_by_name($name) {
		$filterArr = array(
			'character_id'	=> $this->characterId,
			'skill_name'	=> $name
		);
		$data = $this->tableHandlerObj->get_single_record($filterArr);
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_skill_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_skill_by_id($skillId) {
		$data = $this->tableHandlerObj->get_single_record(array(self::pkeyField => $skillId));
		$data['ability_name'] = $this->abilityObj->get_ability_name($data['ability_id']);
		$data['ability_mod'] = $this->abilityObj->get_ability_modifier($data['ability_name']);
		$skillMod = 0;
		try {
			$skillMod = $this->calculate_skill_mod($data);
		}
		catch(Exception $e) {
			$skillMod = 0;
		}
		if(!is_numeric($skillMod)) {
			$skillMod = 0;
		}
		$data['skill_mod'] = $skillMod;
		
		return($data);
	}//end get_skill_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_skills($byAbilityName=null) {
		
		$sql = 'SELECT t.*, t2.ability_name FROM '. self::tableName .' AS t INNER JOIN '. self::joinTable .' AS t2 '
			. ' USING ('. self::joinTableField .') WHERE character_id='. $this->characterId;
		
		if(!is_null($byAbilityName) && !is_numeric($byAbilityName) && strlen($byAbilityName)) {
			$sql .= " AND t.ability_id=". $this->abilityObj->get_ability_id($byAbilityName);
		}
		$sql .= ' ORDER BY skill_name';
		
		try {
			$retval = $this->dbObj->run_query($sql, 'character_skill_id');
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_skills()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data($byAbilityName=null) {
		try {
			$data = $this->get_character_skills($byAbilityName);
			$retval = array();
			
			$makeKeysFrom = array(
				'skill_name', 'ability_name', 'is_class_skill', 'ranks', 'misc_mod'
			);
			foreach($data as $id=>$skillData) {
				$skillModData = array();
				foreach($makeKeysFrom as $indexName) {
					if(isset($skillData[$indexName])) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName);
						$retval[$id][$sheetKey] = $data[$id][$indexName];
					}
					else {
						$this->_exception_handler(__METHOD__ .":: failed to create key for missing index '". $indexName ."'");
					}
					
					//add a key so the form can be checked (easier to run the form like this)
					$retval[$id][$this->create_sheet_id(self::sheetIdPrefix, 'is_class_skill_checked')] = $this->gfObj->interpret_bool($skillData['is_class_skill'], array('','checked'));
					$skillModData[$indexName] = $data[$id][$indexName];
				}
				
				//get ability modifier.
				$abilityMod = $this->abilityObj->get_ability_modifier($data[$id]['ability_name']);
				$retval[$id][$this->create_sheet_id(self::sheetIdPrefix, 'ability_mod')] = $abilityMod;
				$skillModData['ability_mod'] = $abilityMod;
				
				//set skill modifier...
				//TODO: remove "skill_mod" column!
				$retval[$id][$this->create_sheet_id(self::sheetIdPrefix, 'skill_mod')] = $this->calculate_skill_mod($skillModData);
			}
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve data, DETAILS::: ". $e->getMessage());
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
			$res = $this->create_skill($data[0], $data[1]);
		}
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function calculate_skill_mod(array $skillData) {
		$requiredIndexes = array('ability_mod', 'ranks', 'misc_mod');
		$skillMod = 0;
		foreach($requiredIndexes as $indexName) {
			if(isset($skillData[$indexName])) {
				$skillMod += $skillData[$indexName];
			}
			else {
				$this->_exception_handler(__METHOD__ .":: missing required index (". $indexName .")");
			}
		}
		return($skillMod);
	}//end calculate_skill_mod()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		
		try {
			$oldSkillVals = $this->get_skill_by_id($recordId);
			switch($updateBitName) {
				case 'skill_name':
				case 'ranks':
				case 'misc_mod':
				case 'is_class_skill':
				case 'ability_id':
					break;
				
				default:
					$this->_exception_handler(__METHOD__ .":: invalid updateBitName (". $updateBitName .")");
			}
			
			//now perform the update.
			$oldSkillVals[$updateBitName] = $newValue;
			$updatesArr = array(
				$updateBitName	=> $newValue,
			);
			$retval = $this->update_skill($recordId, $updatesArr);
			
			//Update the updatesByKey array.
			#$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'skill_mod', $recordId)] = $newSkillMod;
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
