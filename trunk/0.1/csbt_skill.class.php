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

class csbt_skill extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	const joinTable = 'csbt_ability_table';
	const joinTableField = 'ability_id';
	const sheetIdPrefix = 'skills__';
	
	
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
			'skill_name'		=> 'sql',
			'ability_id'		=> 'int',
			'is_class_skill'	=> 'bool',
			'skill_mod'			=> 'int',
			'ability_mod'		=> 'int',
			'ranks'				=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_id($abilityName) {
		try {
			$retval = $this->abilityObj->get_ability_id($abilityName);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve abilityName (". $abilityName ."), DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end get_ability_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_name($abilityId) {
		if(is_numeric($abilityId) && $abilityId > 0) {
			try {
				$retval = $this->abilityObj->get_ability_name($abilityId);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to retrieve abilityName (". $abilityId ."), DETAILS::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .":: invalid abilityId (". $abilityId .")");
		}
		
		return($retval);
	}//end get_ability_name()
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
				$newId = $this->tableHandlerObj->create_record($insertArr);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to create character skill (". $name ."), DETAILS:::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .":: unable to create skill without name");
		}
		
		return($newId);
	}//end create_skill()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_skill($skillId, array $updates) {
		if(is_numeric($skillId) && $skillId > 0 && is_array($updates) && count($updates) > 0) {
			$sqlArr = array();
			foreach($updates as $key=>$val) {
				if(isset($this->fields[$key])) {
					$sqlArr[$key] = $val;
				}
			}
			if(count($sqlArr)) {
				$updateSql = 'UPDATE '. self::tableName .' SET '
					. $this->gfObj->string_from_array($sqlArr, 'update', null, $this->fields)
					. ' WHERE skill_id='. $skillId .' AND character_id='. $this->characterId;
				try {
					$retval = $this->dbObj->run_update($updateSql);
				}
				catch(Exception $e) {
					throw new exception(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
				}
			}
			else {
				throw new exception(__METHOD__ .":: no valid fields to update");
			}
		}
		else {
				throw new exception(__METHOD__ .":: invalid skillId (". $skillId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_skill()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_skill_by_name($name) {
		$data = $this->tableHandlerObj->get_single_record('skill_name', $name);
		$data['ability_name'] = $this->get_ability_name($data['ability_id']);
		
		return($data);
	}//end get_skill_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_skills() {
		$sql = 'SELECT t.*, t2.ability_name FROM '. self::tableName .' AS t INNER JOIN '. self::joinTable .' AS t2 '
			. ' USING ('. self::joinTableField .') WHERE character_id='. $this->characterId
			. ' ORDER BY skill_name';
		try {
			$retval = $this->dbObj->run_query($sql, 'character_skill_id');
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_skills()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		try {
			$data = $this->get_character_skills();
			$retval = array();
			
			$makeKeysFrom = array(
				'skill_name', 'ability_name', 'is_class_skill', 'skill_mod', 
				'ability_mod', 'ranks', 'misc_mod'
			);
			foreach($data as $id=>$skillData) {
				#$retval[$id]['sheetId'] = $this->create_sheet_id(self::sheetIdPrefix, $skillData['skill_name'], $id);
				foreach($makeKeysFrom as $indexName) {
					if(isset($skillData[$indexName])) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName, $skillData['character_skill_id']);
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
}

?>
