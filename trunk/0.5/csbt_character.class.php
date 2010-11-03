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
	
	public $changesByKey=array();
	
	protected $cleanStringArr = array(
			'uid'					=> 'int',
			'character_name'		=> 'sql',
			'campaign_id'			=> 'int',
			'ac_misc'				=> 'int',
			'ac_size'				=> 'int',
			'ac_natural'			=> 'int',
			'action_points'			=> 'int',
			'character_age'			=> 'int',
			'character_level'		=> 'sql',
			'alignment'				=> 'sql',
			'base_attack_bonus'		=> 'int',
			'deity'					=> 'sql',
			'eye_color'				=> 'sql',
			'gender'				=> 'sql',
			'hair_color'			=> 'sql',
			'height'				=> 'sql',
			'hit_points_max'		=> 'int',
			'hit_points_current'	=> 'int',
			'race'					=> 'sql',
			'size'					=> 'sql',
			'weight'				=> 'int',
			'initiative_misc'		=> 'int',
			'nonlethal_damage'		=> 'int',
			'hit_dice'				=> 'sql',
			'damage_reduction'		=> 'sql',
			'melee_misc'			=> 'int',
			'melee_size'			=> 'int',
			'melee_temp'			=> 'int',
			'melee_total'			=> 'int',
			'ranged_misc'			=> 'int',
			'ranged_size'			=> 'int',
			'ranged_temp'			=> 'int',
			'ranged_total'			=> 'int',
			'skills_max'			=> 'int',
			'speed'					=> 'int',
			'notes'					=> 'sql'
		);
	
	public $skillsObj;
	public $armorObj;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	const sheetIdPrefix = 'main';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $characterIdOrName, $create=false, $playerUid=null) {
		if(!is_object($dbObj) || get_class($dbObj) != 'cs_phpDB') {
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		$this->logger->logCategory = "Character";
		parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyField, $this->cleanStringArr);
		
		if($create===false && is_numeric($characterIdOrName) && $characterIdOrName >= 0) {
			#$this->get_character_data();
			$this->set_character_id($characterIdOrName);
			parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyField, $this->cleanStringArr, $characterIdOrName);
		}
		elseif($create===true && is_string($characterIdOrName) && strlen($characterIdOrName) >= 2 && is_numeric($playerUid)) {
			$newId = $this->create_character($characterIdOrName, $playerUid);
			$this->set_character_id($newId);
			parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyField, $this->cleanStringArr, $newId);
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
			#if(is_numeric($this->characterId) && $id != $this->characterId) {
			#	$this->logger->log_by_class("Changed character from id=(". $this->characterId .") to (". $id .")", 'debug');
			#}
			parent::set_character_id($id);
			$this->characterId = $id;
			$this->abilityObj = new csbt_characterAbility($this->dbObj, $this->characterId);
			$this->abilityObj->_sheetIdPrefix = csbt_characterAbility::sheetIdPrefix;
			
			$this->skillsObj = new csbt_skill($this->dbObj,$this->characterId);
			$this->skillsObj->_sheetIdPrefix = csbt_skill::sheetIdPrefix;
			
			$this->armorObj = new csbt_characterArmor($this->dbObj, $this->characterId);
			$this->armorObj->_sheetIdPrefix = csbt_characterArmor::sheetIdPrefix;
			
			$this->weaponObj = new csbt_characterWeapon($this->dbObj, $this->characterId);
			$this->weaponObj->_sheetIdPrefix = csbt_characterWeapon::sheetIdPrefix;
			
			$this->gearObj = new csbt_characterGear($this->dbObj, $this->characterId);
			$this->gearObj->_sheetIdPrefix = csbt_characterGear::sheetIdPrefix;
			
			$this->specialAbilityObj = new csbt_characterSpecialAbility($this->dbObj, $this->characterId);
			$this->specialAbilityObj->_sheetIdPrefix = csbt_characterSpecialAbility::sheetIdPrefix;
			
			$this->savesObj = new csbt_characterSave($this->dbObj, $this->characterId);
			$this->savesObj->_sheetIdPrefix = csbt_characterSave::sheetIdPrefix;
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
			try {
				$data = $this->tableHandlerObj->get_single_record(array(self::pkeyField=>$this->characterId));
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to retrieve main record, DETAILS::: ". $e->getMessage());
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid internal characterId (". $this->characterId .")");
		}
		
		return($data);
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_main_character_data() {
		return($this->get_character_data());
	}//end get_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_main_character_data(array $data) {
		if(is_numeric($this->characterId)) {
			if(is_array($data) && count($data)) {
				$updateRes = $this->tableHandlerObj->update_record($this->characterId, $data);
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
		//load abilities.
		$this->abilityObj->load_character_defaults();
		
		//now load skills.
		$this->skillsObj->load_character_defaults();
		
		//load saves.
		$this->savesObj->load_character_defaults();
		
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		
		try{
			$mainCharData = $this->get_character_data();
			if(is_array($mainCharData)) {
				$retval = array();
				foreach($mainCharData as $field=>$val) {
					$sheetId = $this->create_sheet_id(self::sheetIdPrefix, $field);
					$retval[$sheetId] = $val;
				}
				
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'total_ac')] = $this->get_total_ac();
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'total_ac_bonus')] = $this->get_total_ac_bonus();
				$retval[$this->create_sheet_id('generated', 'ac_touch')] = $this->get_total_ac_touch();
				$retval[$this->create_sheet_id('generated', 'ac_flatfooted')] = $this->get_total_ac_flatfooted();
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'initiative_bonus')] = $this->get_initiative_bonus();
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'melee_total')] = $this->get_attack_bonus('melee');
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'ranged_total')] = $this->get_attack_bonus('ranged');
				$retval[$this->create_sheet_id(self::sheetIdPrefix, 'skills_max_cc')] = floor($mainCharData['skills_max'] / 2);
			}
			else {
				throw new exception("no main character data");
			}
			
			$skillsData = $this->skillsObj->get_sheet_data();
			if(is_array($skillsData) && count($skillsData)) {
				$retval[$this->skillsObj->_sheetIdPrefix] = $skillsData;
			}
			
			$savesData = $this->savesObj->get_sheet_data();
			if(is_array($savesData) && count($savesData)) {
				$retval = array_merge($retval, $savesData);
			}
			else {
				throw new exception(__METHOD__ .": no saves data");
			}
			
			
			$armorData = $this->armorObj->get_sheet_data();
			if(is_array($armorData) && count($armorData)) {
				$retval[$this->armorObj->_sheetIdPrefix] = $armorData;
			}
			
			$charAbilities = $this->abilityObj->get_sheet_data();
			if(is_array($charAbilities) && count($charAbilities)) {
				$retval = array_merge($retval, $charAbilities);
			}
			else {
				throw new exception(__METHOD__ .": no abilities");
			}
			
			$weaponData = $this->weaponObj->get_sheet_data();
			if(is_array($weaponData) && count($weaponData)) {
				$retval[$this->weaponObj->_sheetIdPrefix] = $weaponData;
			}
			
			$gearData = $this->gearObj->get_sheet_data();
			if(is_array($gearData) && count($gearData)) {
				$retval[$this->gearObj->_sheetIdPrefix] = $gearData;
			}
			
			$specialAbilityData = $this->specialAbilityObj->get_sheet_data();
			if(is_array($specialAbilityData) && count($specialAbilityData)) {
				$retval[$this->specialAbilityObj->_sheetIdPrefix] = $specialAbilityData;
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
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($sheetId, $recordId=null, $newValue) {
		$bits = $this->parse_sheet_id($sheetId);
		$updateType = array_shift($bits);
		
		$sheetIdBit = $bits[0];
		if(preg_match('/"/', $newValue)) {
			$newValue = preg_replace('/"/', '\'\'', $newValue);
		}
		
		//Record ID (usually only supplied for lists of things like skills, skipped for unique items like abilities).
		$recordId = null;
		if(isset($bits[1])) {
			$recordId = $bits[1];
		}
		$this->do_log(__METHOD__ .": handling update, updateType=(". $updateType ."), sheetIdBit=(". $sheetIdBit ."), newValue=(". $newValue .")", 'debug');
		
		switch($updateType) {
			case 'main':
			case 'mainCharacter':
				if(!strlen($newValue)) {
					$newValue = NULL;
				}
				$showUpdatedValue = $newValue;
				if($sheetIdBit == 'xp_change') {
					//add this to xp_current.
					$info = $this->get_character_data();
					$oldVal = $info['xp_current'];
					$sheetIdBit = 'xp_current';
					$newValue = ($oldVal + $newValue);
					$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'xp_current')] = $newValue;
					$showUpdatedValue = "";
				}
				$retval = $this->update_main_character_data(array($sheetIdBit=>$newValue));
				$this->changesByKey[$sheetId] = $showUpdatedValue;
				$this->handle_automatic_updates($sheetIdBit, $newValue);
				break;
			
			
			case 'characterAbility':
			case 'characterAbilities':
				//TODO: this call should return the affected ability so automatic updates work; for temporary, don't return anything.
				$retval = $this->abilityObj->handle_update($sheetIdBit, $recordId, $newValue);
				
				//TODO: handle automatic updates for skills.
				$abilityName = substr($sheetIdBit, 0, 3);
				$affectedSkills = $this->skillsObj->get_character_skills($abilityName);
				
				//update internal ability cache.
				$this->abilityObj->get_character_abilities();
				
				if(is_array($affectedSkills) && count($affectedSkills) > 0) {
					foreach($affectedSkills as $skillId=>$skillData) {
						foreach($skillData as $n=>$v) {
							//create_sheet_id($prefix=null, $name, $id=null)
							$k = $this->skillsObj->create_sheet_id(csbt_skill::sheetIdPrefix, $n, $skillId);
							$this->changesByKey[$k] = $v;
						}
					}
				}

				//now get affected saves, if any...
				try {
					$abilityId = $this->abilityObj->get_ability_id($abilityName);
					$affectedSaves = $this->savesObj->get_sheet_data($abilityId);
					
					if(is_array($affectedSaves)) {
						foreach($affectedSaves as $k=>$v) {
							$this->changesByKey[$k] = $v;
						}
					}
				}
				catch(Exception $e) {
					//that's okay (probably trying to use an ability that doesn't have an associated save).
				}
				
				//now get updated melee/ranged stuff.

				$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'melee_total')] = $this->get_attack_bonus('melee');
				$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'ranged_total')] = $this->get_attack_bonus('ranged');
				break;
			
			case 'characterArmor':
			case 'armor':
				$retval = $this->armorObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			
			case 'characterWeapon':
			case 'weapon':
				$retval = $this->weaponObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			
			case 'characterGear':
			case 'gear':
				$retval = $this->gearObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			
			case 'skills':
			case 'characterSkills':
				$retval = $this->skillsObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			case 'saves':
			case 'save':
				$retval = $this->savesObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			
			case 'specialAbility':
			case 'specialAbilities':
				$retval = $this->specialAbilityObj->handle_update($sheetIdBit, $recordId, $newValue);
				break;
			
			default:
				throw new exception(__METHOD__ .":: invalid update type (". $updateType .") for sheetId (". $sheetId ."), no class to handle it");
		}
		
		$resultString = $this->gfObj->interpret_bool($retval, array('FAILURE', 'SUCCESS'));
		$this->do_log(__METHOD__ .": updateType=(". $updateType ."), sheetIdBit=(". $sheetIdBit ."), recordId=(". $recordId ."), newValue=(". $newValue ."), RESULT::: (". $resultString .")", 'update');
		
		$this->process_updates_by_key();
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function handle_automatic_updates($updatedColumn, $newValue) {
		$retval = false;
		switch($updatedColumn) {
			case 'base_attack_bonus':
				//TODO: consider updating weapon attack bonus (need to know old value, too)
				$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'melee_total')] = $this->get_attack_bonus('melee');
				$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'ranged_total')] = $this->get_attack_bonus('ranged');
				break;
				
				
			case 'hit_points_max':
				break;
				
				
			case 'hit_points_current':
				//TODO: update to match hit_points_max if it was previously maxed?
				break;
			
			case 'initiative_misc':
				//show other updates...
				$this->changesByKey[$this->create_sheet_id(self::sheetIdPrefix, 'initiative_bonus')] = $this->get_initiative_bonus();
				break;
		}
		return($retval);
	}//end handle_automatic_updates()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_total_ac_bonus($type=null) {
		//touch is its (full AC) - (any armor or natural armor bonus), and flatfooted is (full AC) - (any dex bonus).
		try {
			$totalAc = 0;
			$characterInfo = $this->get_main_character_data();

			if(is_null($type) || preg_match('/^flat/i', $type)) {
				//full=yes; touch=no; flat=yes;
				$totalAc = $this->armorObj->get_ac_bonus();
			}
			
			if(is_numeric($characterInfo['ac_size'])) {
				//full=yes; touch=yes; flat=yes;
				$totalAc += $characterInfo['ac_size'];
			}
			if(is_numeric($characterInfo['ac_misc'])) {
				//full=yes; touch=yes; flat=yes
				$totalAc += $characterInfo['ac_misc'];
			}
			if(is_numeric($characterInfo['ac_natural']) && preg_match('/^flat/i', $type)) {
				//full=yes; touch=no; flat=yes
				$totalAc += $characterInfo['ac_natural'];
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve data for ac bonus, DETAILS::: ". $e->getMessage());
		}
		return($totalAc);
	}//end get_total_ac_bonus()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_total_ac($type=null) {
		$retval = (10 + $this->get_total_ac_bonus($type) + $this->abilityObj->get_ability_modifier('dex'));
		return($retval);
	}//end get_total_ac()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_total_ac_flatfooted() {
		//flatfooted is (full AC) - (any dex bonus)
		$retval = (10 + $this->get_total_ac_bonus('flat'));
		return($retval);
	}//end get_total_ac_flatfooted()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_total_ac_touch() {
		//touch is its (full AC) - (any armor or natural armor bonus)
		$retval = $this->get_total_ac('touch');
		return($retval);
	}//end get_total_ac_touch()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_initiative_bonus() {
		try {
			//NOTE: for additional modifiers (i.e. from feats/abilities), those bonuses must be added to the "initiative_misc" field.
			$characterInfo = $this->get_main_character_data();
			$initBonus = $characterInfo['initiative_misc'];
			$initBonus += $this->abilityObj->get_ability_modifier('dex');
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve data for initiative, DETAILS::: ". $e->getMessage());
		}
		return($initBonus);
	}//end get_initiative_bonus()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_attack_bonus($type='melee') {
		$type = strtolower($type);
		if($type == 'melee' || $type == 'ranged') {
			$data = $this->get_character_data();
			
			$columns = array($type .'_misc', $type .'_size', $type .'_temp', 'base_attack_bonus');
			$atkBonus = 0;
			foreach($columns as $colName) {
				if(isset($data[$colName])) {
					$atkBonus += $data[$colName];
				}
				else {
					throw new exception(__METHOD__ .": cannot calculate attack bonus for '". $type ."' without (". $colName .")");
				}
			}
			$abilityName = 'str';
			if($type == 'ranged') {
				$abilityName = 'dex';
			}
			$atkBonus += $this->abilityObj->get_ability_modifier($abilityName);
		}
		else {
			throw new exception(__METHOD__ .": invalid type (". $type .")");
		}
		return($atkBonus);
	}//end get_attack_bonus()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function process_updates_by_key() {
		$objectList = array('skillsObj', 'abilityObj', 'armorObj', 'weaponObj', 'gearObj', 'savesObj', 'specialAbilityObj');
		foreach($objectList as $object) {
			if(is_object($this->$object)) {
				if(is_array($this->$object->updatesByKey)) {
					foreach($this->$object->updatesByKey as $k=>$v) {
						$this->changesByKey[$k] = $v;
					}
				}
				else {
					throw new exception(__METHOD__ .": attempted to access updatesByKey in (". get_class($this->$object) ."), but found non-array");
				}
			}
			else {
				throw new exception(__METHOD__ .": attempt to access member of non-object (". $this->$object .")");
			}
		}
	}//end process_updates_by_key()
	//-------------------------------------------------------------------------
}

?>
