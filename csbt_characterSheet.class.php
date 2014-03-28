<?php


//TODO: consider optionally adding the logging system.

class csbt_characterSheet {
	
	protected $characterId;
	protected $ownerUid;
	
	public $dbObj;
	public $gfObj;
	
	protected $version;
	
	protected $_char = "TeST";
	protected $_abilities = array();
	protected $_armor = array();
	protected $_gear = array();
	protected $_saves = array();
	protected $_skills = array();
	protected $_specialAbilities = array();
	protected $_weapons = array();
	
	//==========================================================================
	/**
	 * 
	 * @param cs_phpDB $db
	 * @param type $characterIdOrName
	 * @param type $ownerUid
	 * @param type $createOrLoad
	 */
	public function __construct(cs_phpDB $db, $characterIdOrName, $ownerUid=null, $createOrLoad=true) {
		$this->dbObj = $db;
		
		$this->ownerUid = $ownerUid;
		
		$this->_char = new csbt_character($characterIdOrName, $ownerUid, $this->dbObj);
		$this->characterId = $this->_char->characterId;
		$this->id = $this->characterId;
		
		
		if($createOrLoad === true) {
			if(!is_numeric($characterIdOrName)) {
				$this->create_defaults();
			}
			else {
				$this->load();
			}
		}
		
		$this->version = new cs_version();
		$this->version->set_version_file_location(dirname(__FILE__) .'/VERSION');
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function __get($name) {
		$retval = null;
		
		switch($name) {
			case 'skills':
				$retval = $this->_skills;
				break;
			
			case 'char':
				$retval = $this->_char;
				break;
			
			case 'abilities':
				$retval = $this->_abilities;
				break;
			
			case 'armor':
				$retval = $this->_armor;
				break;
			
			case 'gear':
				$retval = $this->_gear;
				break;
			
			case 'saves':
				$retval = $this->_saves;
				break;
			
			case 'weapons':
				$retval = $this->_weapons;
				break;
			
			case 'specialAbilities':
			case 'specialabilities':
				$retval = $this->_specialAbilities;
				break;
			
			case 'character_id':
			case 'characterid':
			case 'characterId':
				$retval = $this->characterId;
				break;
			
			case 'character_name':
				$retval = $this->_char->character_name;
				break;
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create_defaults() {
		$abilities = new csbt_ability();
		$abilities->characterId = $this->characterId;
		
		$abilities->create_defaults($this->dbObj);
		
		
		$abilityCache = $abilities->get_all_abilities($this->dbObj);
		$skills = new csbt_skill();
		$skills->characterId = $this->characterId;
		foreach($this->get_default_skill_list() as $k=>$v) {
			$xData = array(
				'character_id'	=> $this->characterId,
				'skill_name'	=> $v[0],
				'ability_id'	=> $abilityCache[$v[1]]
			);
			$skills->create($this->dbObj, $xData);
		}
		
		$saves = new csbt_save();
		$saves->characterId = $this->characterId;
		$saves->create_character_defaults($this->dbObj);
		
		return $this->load();
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load() {
		$retval = array();
		
		if(is_numeric($this->characterId) && $this->characterId > 0) {
			$this->_char = new csbt_character($this->characterId, $this->ownerUid, $this->dbObj);
			$this->_char->load($this->dbObj);
			
			$this->_abilities = csbt_ability::get_all($this->dbObj, $this->characterId);
			$retval['abilities'] = $this->_abilities;
			
			$this->_armor = csbt_armor::get_all($this->dbObj, $this->characterId);
			$retval['armor'] = $this->_armor;
			
			$this->_gear = csbt_gear::get_all($this->dbObj, $this->characterId);
			$retval['gear'] = $this->_gear;
			
			$this->_saves = csbt_save::get_all($this->dbObj, $this->characterId);
			$retval['saves'] = $this->_saves;
			
			$this->_skills = csbt_skill::get_all($this->dbObj, $this->characterId);
			$retval['skills'] = $this->_skills;
			
			$this->_specialAbilities = csbt_specialAbility::get_all($this->dbObj, $this->characterId);
			$retval['specialAbilities'] = $this->_specialAbilities;
			
			//TODO: load weapons...
			$this->_weapons = csbt_weapon::get_all($this->dbObj, $this->characterId);
			$retval['weapons'] = $this->_weapons;
		}
		else {
			throw new ErrorException(__METHOD__ .": invalid character id");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	//==========================================================================
	public function get_worn_armor() {
		return csbt_armor::get_all($this->dbObj, $this->characterId);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_weight($includeWornItems=false) {
		$weight = 0;
		if(is_array($this->_gear) && count($this->_gear) > 0) {
			$weight = csbt_gear::calculate_list_weight($this->_gear);
		}
		
		//TODO: this accounts for ALL weapons + armor, whether it is_worn/in_use or not; see #41
		if($includeWornItems === true) {
			if(is_array($this->_weapons) && count($this->_weapons) > 0) {
				$weight += csbt_gear::calculate_list_weight($this->_weapons);
			}
			if(is_array($this->_armor) && count($this->_armor) > 0) {
				$weight += csbt_gear::calculate_list_weight($this->_armor);
			}
		}
		
		return $weight;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_default_skill_list() {
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
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_strength_stats() {
		if(is_array($this->_abilities) && isset($this->_abilities['str'])) {
			
			$maxLoad = $this->get_max_load($this->_abilities['str']['ability_score']);
			$minLoad = (int)floor($maxLoad /3);
			$medLoad = (int)floor($minLoad * 2);
			
			$stats = array(
				'load_light'		=> $minLoad,
				'load_medium'		=> $medLoad,
				'load_heavy'		=> $maxLoad,
				'lift_over_head'	=> $maxLoad,
				'lift_off_ground'	=> (int)floor($maxLoad *2),
				'push_pull_drag'	=> (int)floor($maxLoad *5),
			);
		}
		else {
			throw new ErrorException(__METHOD__ .": required stat (str) missing");
		}
		
		return $stats;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_max_load($strScore) {
		if(is_numeric($strScore) && $strScore > 0) {
			//for the most part, the formulas for calculating max load was pulled from http://www.superdan.net/download/mathformulas.rtf
			// For tremendous strength, clues to the formula were pulled from d20srd.org (http://www.d20srd.org/srd/carryingCapacity.htm) and
			// from http://www.superdan.net/download/superabilities.rtf
			
			$roundingSteps = array(
				1	=> 5,
				2	=> 10,
				3	=> 20,
				4	=> 40,
			);
			
			$useRoundingStep = ceil(($strScore -10)/5);
			
			if(isset($roundingSteps[$useRoundingStep])) {
				$roundingTo = $roundingSteps[$useRoundingStep];
			}
			else {
				//is this right?  Didn't find anything...
				$roundingTo = 100;
			}
			$firstNum = $strScore -10;
			$secondNum = pow(1.1487, $firstNum);
			$almostDone = (int)($secondNum * 100);
			$retval = round($almostDone/$roundingTo)*$roundingTo;
			
			if($strScore <= 10) {
				$retval = $strScore * 10;
			}
			elseif($strScore > 29) {
				$exp = ($strScore /10)-2;
				if($exp < 1) {
					$exp = 1;
				}
				$exp = floor($exp);
				
				$lastNum = substr($strScore, -1);
				$useScore = 20 + $lastNum;
				
				$multiplyThis = self::get_max_load($useScore);
				
				$retval = ($multiplyThis * pow(4, $exp));
			}
		}
		else {
			throw new InvalidArgumentException(__METHOD__ .": invalid strength score (". $strScore .")");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create_sheet_id($prefix, $name, $suffix=null) {
		$retval = $prefix .'__'. $name;
		if(!is_null($suffix)) {
			$retval .= '__'. $suffix;
		}
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_misc_data() {
		$retval = array();
		
		$mainCharData = $this->_char->data;
		
		$cName = "";
		$cDesc = "";
		
		if(is_numeric($this->_char->campaign_id)) {
			$campaign = new csbt_campaign();
			$campaign->id = $this->_char->campaign_id;
			$campaign->load($this->dbObj);
			
			$cName = $campaign->campaign_name;
			$cDesc = $campaign->description;
		}
		
		$retval[$this->create_sheet_id('main', 'campaign_name')] = $cName;
		$retval[$this->create_sheet_id('main', 'campaign_description')] = $cDesc;
		
		$retval[$this->create_sheet_id('main', 'total_ac')] = 10 + $this->get_total_ac_bonus('full');
		$retval[$this->create_sheet_id('main', 'total_ac_bonus')] = $this->get_total_ac_bonus(null);
		
		$retval[$this->create_sheet_id('generated', 'ac_touch')] = 10 + $this->get_total_ac_bonus('touch');
		$retval[$this->create_sheet_id('generated', 'ac_flatfooted')] = 10 + $this->get_total_ac_bonus('flat');
		
		$retval[$this->create_sheet_id('main', 'initiative_bonus')] = $this->get_initiative_bonus();
		$retval[$this->create_sheet_id('main', 'melee_total')] = $this->get_attack_bonus('melee');
		$retval[$this->create_sheet_id('main', 'ranged_total')] = $this->get_attack_bonus('ranged');
		$retval[$this->create_sheet_id('main', 'skills_max_cc')] = floor($mainCharData['skills_max'] / 2);
		
		$retval[$this->create_sheet_id('generated', 'campaign_name')] = $cName;
		$retval[$this->create_sheet_id('generated', 'campaign_description')] = $cDesc;
		
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_attack_bonus($type='melee') {
		$atkBonus = 0;
		
		if($type == 'melee' || $type == 'ranged') {
			$data = $this->_char->data;
			$addThese = array($type .'_misc', $type .'_size', $type .'_temp', 'base_attack_bonus');
//cs_global::debug_print(__METHOD__ .": data::: ". cs_global::debug_print($data,0),1);
			
			foreach($addThese as $colName) {
				if(isset($data[$colName]) && is_numeric($data[$colName])) {
					$atkBonus += $data[$colName];
//cs_global::debug_print(__METHOD__ .": type=(". $type ."), added ". $colName ." (". $data[$colName] ."), current=(". $atkBonus .")",1);
				}
				else {
					throw new ErrorException(__METHOD__ .": cannot calculate attack bonus for ". $type ." without ". $colName);
				}
			}
			
			$abilityName = 'str';
			if ($type == 'ranged') {
				$abilityName = 'dex';
			}
			$atkBonus += csbt_ability::calculate_ability_modifier($this->_abilities[$abilityName]['ability_score']);
		}
		else {
			throw new ErrorException(__METHOD__ .": invalid type (". $type .")");
		}
		
		return $atkBonus;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_initiative_bonus() {
		$c = $this->_char->data;
		$bonus = 0;
		
		$bonus += $c['initiative_misc'];
		$bonus += csbt_ability::calculate_ability_modifier($this->_abilities['dex']['ability_score']);
		
		return $bonus;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_ac_bonus($type=null) {
		$totalAc = 0;
		
		if(is_array($this->_armor)) {
			foreach($this->_armor as $v) {
				$totalAc += $v['ac_bonus'];
			}
		}
		
		$c = $this->_char->data;
		if(!is_null($type)) {
			if(is_numeric($c['ac_size'])) {
				$totalAc += $c['ac_size'];
			}
			
			if(is_numeric($c['ac_misc'])) {
				$totalAc += $c['ac_misc'];
			}
			
			if(is_numeric($c['ac_natural']) || preg_match('/^flat/i', $type)) {
				$totalAc += $c['ac_natural'];
			}
			
			if(is_null($type) || !preg_match('/^flat/i', $type)) {
				$totalAc += csbt_ability::calculate_ability_modifier($this->_abilities['dex']['ability_score']);
			}
		}
		
		return $totalAc;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_sheet_data() {
		//NOTE: there's a lot of unset() and renames that are present simply for comparing old functionality to new.
		
		if(!is_array($this->_abilities)) {
			$this->load();
		}
		
		$retval = array();
		
		foreach($this->_char->data as $idx=>$v) {
			$sheetId = $this->create_sheet_id('main', $idx);
			$retval[$sheetId] = $v;
		}
		
		$retval = array_merge($retval, $this->get_misc_data());
		
		
		$sk = new csbt_skill();
		$retval[csbt_skill::sheetIdPrefix] = $sk->get_sheet_data($this->dbObj, $this->characterId);
		
		// Saves...
		{
			$allSaves = csbt_save::get_all($this->dbObj, $this->characterId);
			$retval['saves'] = array();
			foreach($allSaves as $id=>$data) {
				$mySaves = array();


				unset(
						$data['ability_id'], $data['character_ability_id'], 
						$data['ability_id'], $data['character_id'],
						$data['character_save_id'], $data['temporary_score']
				);

				unset($data['total_mod']);

				foreach($data as $k=>$v) {
					$mySaves[$this->create_sheet_id('saves', $k)] = $v;
				}
				$displayName = strtoupper($data['save_name']);
				if($displayName == 'FORT') {
					$displayName = 'FORTITUDE';
				}
				$mySaves[$this->create_sheet_id('saves', 'display_name')] = $displayName;
				ksort($mySaves);

				$retval['saves'][$id] = $mySaves;
			}
		}
		
		$ab = new csbt_ability();
		$retval[csbt_ability::sheetIdPrefix] = $ab->get_sheet_data($this->dbObj, $this->characterId);
		foreach($this->_abilities as $k=>$data) {
			$retval[csbt_ability::sheetIdPrefix .'__'. $k .'_score'] = $data['ability_score'];
			$retval[csbt_ability::sheetIdPrefix .'__'. $k .'_modifier'] = csbt_ability::calculate_ability_modifier($data['ability_score']);
		}
		
		$armor = new csbt_armor;
		$retval['characterArmor'] = $armor->get_sheet_data($this->dbObj, $this->characterId);
		
		$wpn = new csbt_weapon;
		$retval[$wpn::sheetIdPrefix] = $wpn->get_sheet_data($this->dbObj, $this->characterId);
		
		$specialAbilities = new csbt_specialAbility();
		$retval[$specialAbilities::sheetIdPrefix] = $specialAbilities->get_sheet_data($this->dbObj, $this->characterId);
		
		$gear = new csbt_gear();
		$retval[$gear::sheetIdPrefix] = $gear->get_sheet_data($this->dbObj, $this->characterId);
		
		$retval[$gear::sheetIdPrefix .'__total_weight__generated'] = csbt_gear::calculate_list_weight($gear->get_all($this->dbObj, $this->characterId));
		
		
		foreach($this->get_strength_stats() as $k=>$v) {
			$retval[$this->create_sheet_id('generated', $k)] = $v;
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function build_sheet(cs_genericPage $page) {
		$data = $this->get_sheet_data();
		
		$blockRows = $page->rip_all_block_rows('content');
		$parsedSlots = array();
		
		foreach($page->templateRows as $n=>$garbage) {
			if(preg_match('/slot/i', $n)) {
				$parsedSlots[$n] = 0;
			}
		}
		
		$abilityList = csbt_ability::get_all_abilities($this->dbObj);
		
		foreach($data as $name=>$val) {
			if(is_array($val)) {
				$blockRowName = $name . 'Slot';
				if($name == 'saves') {
					// changed name of the saves row so it doesn't get an extra row automatically...
					$blockRowName = 'characterSaveRow';
				}
				if(!isset($page->templateRows[$blockRowName])) {
					throw new ErrorException(__METHOD__ .": failed to parse data for (". $name ."), missing block row '". $blockRowName ."'");
				}
				
				$parsedRows = '';
				$rowsParsed = 0;
				
				foreach($val as $id=>$subArray) {
					if(is_array($subArray)) {
						if($name == 'skills') {
							$subArray['abilityDropDown'] = $this->create_ability_select($page, $abilityList, $id, $subArray['skills__ability_name']);
						}
						
						$myBlockRow = $page->templateRows[$blockRowName];
						
						$subArray[$name .'_id'] = $id;
						
						$parsedRows .= cs_global::mini_parser($myBlockRow, $subArray, '{', '}');
						$rowsParsed++;
						$parsedSlots[$blockRowName] = $rowsParsed;
					}
					else {
						$page->add_template_var($id, $subArray);
					}
				}
				if($rowsParsed > 0) {
					$page->add_template_var($blockRowName, $parsedRows);
				}
			}
			else {
				$page->add_template_var($name, $val);
			}
		}
		
		$page->add_template_var('CSBT_project_name', $this->version->get_project());
		$page->add_template_var('CSBT_version', $this->version->get_version());
	}
	//==========================================================================
	
	
	//==========================================================================
	private function create_ability_select(cs_genericPage $page, array $abilityList, $skillId = null, $selectThis = null) {
		$abilityOptionList = cs_global::array_as_option_list($abilityList, $selectThis);
		if (is_null($skillId)) {
			$skillId = 'new';
		}
		$optionListRepArr = array(
			'skills__selectAbility__extra' => '',
			'skill_id' => $skillId,
			'optionList' => $abilityOptionList
		);
		
		if (is_numeric($skillId)) {
			
		} else {
			$optionListRepArr['skills__selectAbility__extra'] = 'class="newRecord"';
			$optionListRepArr['skillNum'] = 'new';
			$optionListRepArr['skill_id'] = 'new';
		}
		$retval = cs_global::mini_parser($page->templateRows['skills__selectAbility'], $optionListRepArr, '%%', '%%');
		return($retval);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function handle_update($name, $value) {
		
		//TODO: the $name should really be in the form of sheetIdPrefix__field_name__ID (where ID is optional & depends on context)
//cs_global::debug_print(__METHOD__ .": arguments::: ". cs_global::debug_print(func_get_args(),0),1);
//exit;
		$changesByKey = array();
		$result = 0;
		
		$bits = preg_split('/__/', $name);
		$prefix = $bits[0];
		$realName = $bits[1];
		
		$id=null;
		if(isset($bits[2])) {
			$id = $bits[2];
		}
		$debug = "realName=(". $realName ."), id=(". $id .")";
		switch($prefix) {
			case csbt_ability::sheetIdPrefix:
				
				if(is_numeric($id)) {
					
					$allAbilities = csbt_ability::get_all_abilities($this->dbObj, true);
					
					$obj = new csbt_ability();
					$obj->load($this->dbObj, $id);
					
					if(!is_null($value) && strlen($value) == 0) {
						$value = null;
					}
					
					$obj->update($realName, $value);
					$result = $obj->save($this->dbObj);
					$obj->load($this->dbObj);
					$this->load();
					
					$abilityName = $allAbilities[$obj->ability_id];
					
					{
						$changesByKey[$name] = $value;
						if(preg_match('/temp/', $realName)) {
							//update temporary modifier
							$changesByKey[$this->create_sheet_id($prefix, $abilityName .'_temporary_modifier')] = $obj->get_temp_modifier();
						}
						elseif(preg_match('/^ability/', $realName) || preg_match('/^score$/', $realName)) {
							//update (standard) modifier
							$changesByKey[$this->create_sheet_id($prefix, $abilityName .'_modifier')] = $obj->get_modifier();
							
							//Updating skill info is NEW.
							$depSkills = csbt_skill::get_all($this->dbObj, $this->characterId, $obj->ability_id);
							foreach($depSkills as $k=>$v) {
								$changesByKey[$this->create_sheet_id(csbt_skill::sheetIdPrefix, 'ability_mod', $k)] = $obj->get_modifier();
								$changesByKey[$this->create_sheet_id(csbt_skill::sheetIdPrefix, 'skill_mod', $k)] = csbt_skill::calculate_skill_modifier($v);
							}
							
							//TODO: update dependent saves
							$saveList = csbt_save::get_all($this->dbObj, $this->characterId, $obj->ability_id);
							foreach($saveList as $k=>$v) {
								$changesByKey[$this->create_sheet_id(csbt_save::sheetIdPrefix, 'ability_mod', $k)] = $v['ability_mod'];
								$changesByKey[$this->create_sheet_id(csbt_save::sheetIdPrefix, 'total', $k)] = $v['total_mod'];
							}
							
							//TODO: update misc fields...
							switch($abilityName) {
								case 'str':
									$miscUpdates = $this->get_strength_stats();
									foreach($miscUpdates as $k=>$v) {
										$changesByKey[$this->create_sheet_id('generated', $k)] = $v;
									}
									break;
							}
						}
						else {
							throw new InvalidArgumentException(__METHOD__ .": invalid field (". $realName .")");
						}
					}
				}
				
				$mData = $this->get_misc_data();
				foreach($mData as $k=>$v) {
					$changesByKey[$k] = $v;
				}
				
				break;
			
			case csbt_character::sheetIdPrefix:
				$char = new csbt_character($this->characterId, $this->ownerUid, $this->dbObj);
				$char->load($this->dbObj);
				if($realName == 'xp_change') {
					$xpCurrent = $char->xp_current;
//					if(is_numeric($xpCurrent)) {
					$debug .= " XP_CURRENT=(". $char->xp_current .")";
						$char->update('xp_current', ($xpCurrent + $value));
						$changesByKey[$name] = "";
//					}
				}
				else {
					$char->update($realName, $value);
				}
				$result = $char->save($this->dbObj);
				
				$char->load($this->dbObj);
				foreach($char->data as $idx=>$v) {
					$sheetId = $this->create_sheet_id($char::sheetIdPrefix, $idx);
					$changesByKey[$sheetId] = $v;
				}
				break;
				
			case csbt_save::sheetIdPrefix:
				$x = new csbt_save();
				$x->id = $id;
				$x->update($realName, $value);
				$result = $x->save($this->dbObj);
				
				//TODO: fix so there is no need to manually add the ID to the end of the key
				$_sheetData = $x->get_sheet_data($this->dbObj, $this->characterId);
				foreach($_sheetData[$id] as $k=>$v) {
					$changesByKey[$k . '__'. $id] = $v;
				}
				break;
			
			case csbt_skill::sheetIdPrefix:
				$x = new csbt_skill();
				$x->id = $id;
				
				if($realName == 'is_class_skill') {
					$value = cs_global::interpret_bool($value, array(false, true));
				}
				
				$x->update($realName, $value);
				$result = $x->save($this->dbObj);
				$x->load($this->dbObj);
				
				$changesByKey = $x->get_sheet_data($this->dbObj, $this->characterId, $id);
				break;
			
			case csbt_weapon::sheetIdPrefix:
				$x = new csbt_weapon();
				$x->id = $id;
				$x->update($realName, $value);
				$result = $x->save($this->dbObj);
				$changesByKey = $x->get_sheet_data($this->dbObj, $this->characterId, $id);
				break;

			case csbt_specialAbility::sheetIdPrefix:
				$x = new csbt_specialAbility();
				$x->id = $id;
				$x->update($realName, $value);
				$result = $x->save($this->dbObj);
				$changesByKey = $x->get_sheet_data($this->dbObj, $this->characterId, $id);
				break;
			
			case csbt_gear::sheetIdPrefix:
				$x = new csbt_gear();
				$x->id = $id;
				$x->update($realName, $value);
				$result = $x->save($this->dbObj);
				$changesByKey = $x->get_sheet_data($this->dbObj, $this->characterId, $id);
				break;
			
			default:
				throw new InvalidArgumentException(__METHOD__ .": invalid prefix (". $prefix .") or unable to update field (". $realName .")");
		}
		
		$retval = array(
			'debug'			=> $debug,
			'result'		=> $result,
			'changesbykey'	=> $changesByKey,
		);
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function handle_new_record($type, $name, array $extraData=null) {
		if(is_null($extraData) || !is_array($extraData)) {
			$extraData = array();
		}
		$extraData['character_id'] = $this->characterId;
		switch($type) {
			case csbt_weapon::sheetIdPrefix:
				$x = new csbt_weapon();
				$extraData['weapon_name'] = $name;
				
				$result = $x->create($this->dbObj, $extraData);
				break;
				
			case csbt_armor::sheetIdPrefix:
				$x = new csbt_armor();
				$extraData['armor_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			case csbt_specialAbility::sheetIdPrefix:
				$x = new csbt_specialAbility();
				$extraData['special_ability_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			case csbt_gear::sheetIdPrefix:
				$x = new csbt_gear();
				$extraData['gear_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			default:
				throw new InvalidArgumentException(__METHOD__ .": invalid type (". $type .")");
		}
		
		return $result;
	}
	//==========================================================================
}

