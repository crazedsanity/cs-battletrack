<?php

namespace battletrack;

//TODO: consider optionally adding the logging system.

use crazedsanity\database\Database;
use battletrack\character\Character;
use battletrack\character\Ability;
use battletrack\character\Skill;
use battletrack\character\Save;
use battletrack\character\Armor;
use battletrack\character\Gear;
use battletrack\character\SpecialAbility;
use battletrack\character\Weapon;
use battletrack\character\Campaign;

use crazedsanity\core\ToolBox;

use ErrorException;
use InvalidArgumentException;

class CharacterSheet {
	
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
	 * @param Database $db
	 * @param type $characterIdOrName
	 * @param type $ownerUid
	 * @param type $createOrLoad
	 */
	public function __construct(Database $db, $characterIdOrName, $ownerUid=null, $createOrLoad=true) {
		$this->dbObj = $db;
		
		$this->ownerUid = $ownerUid;
		
		$this->_char = new Character($characterIdOrName, $ownerUid, $this->dbObj);
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
		
		//TODO: re-add version code.
//		$this->version = new cs_version();
//		$this->version->set_version_file_location(dirname(__FILE__) .'/VERSION');
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
		$abilities = new Ability();
		$abilities->characterId = $this->characterId;
		
		$abilities->create_defaults($this->dbObj);
		
		
		$abilityCache = $abilities->get_all_abilities($this->dbObj);
		$skills = new Skill();
		$skills->characterId = $this->characterId;
		foreach($this->get_default_skill_list() as $k=>$v) {
			$xData = array(
				'character_id'	=> $this->characterId,
				'skill_name'	=> $v[0],
				'ability_id'	=> $abilityCache[$v[1]]
			);
			$skills->create($this->dbObj, $xData);
		}
		
		$saves = new Save();
		$saves->characterId = $this->characterId;
		$saves->create_character_defaults($this->dbObj);
		
		//TODO: re-add logging
//		$log = new cs_webdblogger($this->dbObj, "Character", false);
//		$log->log_by_class("loaded defaults", "create");
		
		return $this->load();
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load() {
		$retval = array();
		
		if(is_numeric($this->characterId) && $this->characterId > 0) {
			$this->_char = new Character($this->characterId, $this->ownerUid, $this->dbObj);
			$this->_char->load($this->dbObj);
			
			$this->_abilities =Ability::get_all($this->dbObj, $this->characterId);
			$retval['abilities'] = $this->_abilities;
			
			$this->_armor = Armor::get_all($this->dbObj, $this->characterId);
			$retval['armor'] = $this->_armor;
			
			$this->_gear = Gear::get_all($this->dbObj, $this->characterId);
			$retval['gear'] = $this->_gear;
			
			$this->_saves = Save::get_all($this->dbObj, $this->characterId);
			$retval['saves'] = $this->_saves;
			
			$this->_skills = Skill::get_all($this->dbObj, $this->characterId);
			$retval['skills'] = $this->_skills;
			
			$this->_specialAbilities = SpecialAbility::get_all($this->dbObj, $this->characterId);
			$retval['specialAbilities'] = $this->_specialAbilities;
			
			$this->_weapons = Weapon::get_all($this->dbObj, $this->characterId);
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
		return Armor::get_all($this->dbObj, $this->characterId);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_weight($includeWornItems=false) {
		$weight = 0;
		if(is_array($this->_gear) && count($this->_gear) > 0) {
			$weight = Gear::calculate_list_weight($this->_gear);
		}
		
		//TODO: this accounts for ALL weapons + armor, whether it is_worn/in_use or not; see #41
		if($includeWornItems === true) {
			if(is_array($this->_weapons) && count($this->_weapons) > 0) {
				$weight += Gear::calculate_list_weight($this->_weapons);
			}
			if(is_array($this->_armor) && count($this->_armor) > 0) {
				$weight += Gear::calculate_list_weight($this->_armor);
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
			$campaign = new Campaign();
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
			
			foreach($addThese as $colName) {
				if(isset($data[$colName]) && is_numeric($data[$colName])) {
					$atkBonus += $data[$colName];
				}
				else {
					throw new ErrorException(__METHOD__ .": cannot calculate attack bonus for ". $type ." without ". $colName);
				}
			}
			
			$abilityName = 'str';
			if ($type == 'ranged') {
				$abilityName = 'dex';
			}
			$atkBonus += Ability::calculate_ability_modifier($this->_abilities[$abilityName]['ability_score']);
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
		$bonus += Ability::calculate_ability_modifier($this->_abilities['dex']['ability_score']);
		
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
				$totalAc += Ability::calculate_ability_modifier($this->_abilities['dex']['ability_score']);
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
		
		
		$sk = new Skill();
		$retval[Sskill::sheetIdPrefix] = $sk->get_sheet_data($this->dbObj, $this->characterId);
		
		$_saves = new Save();
		$retval[$_saves::sheetIdPrefix] = $_saves->get_sheet_data($this->dbObj, $this->characterId);
		
		$ab = new Ability();
		$retval[Ability::sheetIdPrefix] = $ab->get_sheet_data($this->dbObj, $this->characterId);
		foreach($this->_abilities as $k=>$data) {
			$retval[Ability::sheetIdPrefix .'__'. $k .'_score'] = $data['ability_score'];
			$retval[Ability::sheetIdPrefix .'__'. $k .'_modifier'] = Ability::calculate_ability_modifier($data['ability_score']);
		}
		
		$armor = new Armor;
		$retval['characterArmor'] = $armor->get_sheet_data($this->dbObj, $this->characterId);
		
		$wpn = new Weapon;
		$retval[$wpn::sheetIdPrefix] = $wpn->get_sheet_data($this->dbObj, $this->characterId);
		
		$specialAbilities = new SpecialAbility();
		$retval[$specialAbilities::sheetIdPrefix] = $specialAbilities->get_sheet_data($this->dbObj, $this->characterId);
		
		$gear = new Gear();
		$retval[$gear::sheetIdPrefix] = $gear->get_sheet_data($this->dbObj, $this->characterId);
		
		$retval[$gear::sheetIdPrefix .'__total_weight__generated'] = Gear::calculate_list_weight($gear->get_all($this->dbObj, $this->characterId));
		
		
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
							$subArray['abilityDropDown'] = $this->create_ability_select($page, $id, $subArray['skills__ability_name']);
						}
						
						$myBlockRow = $page->templateRows[$blockRowName];
						
						$subArray[$name .'_id'] = $id;
						
						$parsedRows .= ToolBox::mini_parser($myBlockRow, $subArray, '{', '}');
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
	}
	//==========================================================================
	
	
	//==========================================================================
	public function create_ability_select(cs_genericPage $page, $skillId = null, $selectThis = null) {
		$abilityList = Ability::get_all_abilities($this->dbObj, true);
		$abilityOptionList = ToolBox::array_as_option_list($abilityList, $selectThis);
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
		$retval = ToolBox::mini_parser($page->templateRows['skills__selectAbility'], $optionListRepArr, '%%', '%%');
		return($retval);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function handle_update($name, $value) {
		$changesByKey = array();
		$result = "not set";
		
		$bits = preg_split('/__/', $name);
		$prefix = $bits[0];
		$realName = $bits[1];
		
		$recordId=null;
		if(isset($bits[2])) {
			$recordId = $bits[2];
		}
		$fieldsToUpdate = array($realName=>$value);
		$debug = "realName=(". $realName ."), id=(". $recordId .")";
		
		
//		$log = new cs_webdblogger($this->dbObj, "Character");
		
		switch($prefix) {
			case Ability::sheetIdPrefix:
				
				if(is_numeric($recordId)) {
					
					$allAbilities = Ability::get_all_abilities($this->dbObj, true);
					
					$obj = new Ability();
					if(!is_null($value) && strlen($value) == 0) {
						$fieldsToUpdate[$realName] = null;
					}
					$changesByKey = $obj->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
					
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
							$depSkills = Skill::get_all($this->dbObj, $this->characterId, $obj->ability_id);
							foreach($depSkills as $k=>$v) {
								$changesByKey[$this->create_sheet_id(Skill::sheetIdPrefix, 'ability_mod', $k)] = $obj->get_modifier();
								$changesByKey[$this->create_sheet_id(Skill::sheetIdPrefix, 'skill_mod', $k)] = Skill::calculate_skill_modifier($v);
							}
							
							$saveList = Save::get_all($this->dbObj, $this->characterId, $obj->ability_id);
							foreach($saveList as $k=>$v) {
								$changesByKey[$this->create_sheet_id(Save::sheetIdPrefix, 'ability_mod', $k)] = $v['ability_mod'];
								$changesByKey[$this->create_sheet_id(Save::sheetIdPrefix, 'total', $k)] = $v['total_mod'];
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
			
			case Character::sheetIdPrefix:
				$char = new Character($this->characterId, $this->ownerUid, $this->dbObj);
				$char->load($this->dbObj);
				if($realName == 'xp_change') {
					$xpCurrent = $char->xp_current;
					$fieldsToUpdate = array('xp_current', ($xpCurrent + $value));
				}
				$changesByKey = $char->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				
				$this->load();
				if(preg_match('/^melee/', $realName)) {
					$changesByKey[$this->create_sheet_id('main', 'melee_total')] = $this->get_attack_bonus('melee');
				}
				elseif(preg_match('/^ranged/', $realName)) {
					$changesByKey[$this->create_sheet_id('main', 'ranged_total')] = $this->get_attack_bonus('ranged');
				}
				break;
				
			case Save::sheetIdPrefix:
				$x = new Save();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				break;
			
			case Skill::sheetIdPrefix:
				$x = new Skill();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				break;
			
			case Weapon::sheetIdPrefix:
				$x = new Weapon();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				break;
			
			case Armor::sheetIdPrefix:
				$x = new Armor();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				break;

			case SpecialAbility::sheetIdPrefix:
				$x = new SpecialAbility();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				break;
			
			case Gear::sheetIdPrefix:
				$x = new Gear();
				$changesByKey = $x->update_and_get_changes($this->dbObj, $fieldsToUpdate, $recordId);
				$changesByKey[$x::sheetIdPrefix .'__total_weight__generated'] = csbt_gear::calculate_list_weight($x->get_all($this->dbObj, $this->characterId));
				break;
			
			default:
				$details = __METHOD__ .": invalid prefix (". $prefix .") or unable to update field (". $realName .")";
//				$log->log_by_class($details, "exception in code");
				
				throw new InvalidArgumentException($details);
		}
		
//		$log->log_by_class("Updating characterId=". $this->characterId .", ". $name ."=(". $value .")", "update");
		
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
		
		$log = new cs_webdblogger($this->dbObj, "Character");
		
		switch($type) {
			case Weapon::sheetIdPrefix:
				$x = new Weapon();
				$extraData['weapon_name'] = $name;
				
				$result = $x->create($this->dbObj, $extraData);
				break;
				
			case Armor::sheetIdPrefix:
				$x = new Armor();
				$extraData['armor_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			case SpecialAbility::sheetIdPrefix:
				$x = new SpecialAbility();
				$extraData['special_ability_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			case Gear::sheetIdPrefix:
				$x = new Gear();
				$extraData['gear_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			case Skill::sheetIdPrefix:
				$x = new Skill();
				$extraData['skill_name'] = $name;
				$result = $x->create($this->dbObj, $extraData);
				break;
			
			default:
				$details = __METHOD__ .": invalid type (". $type .")";
				$log->log_by_class($details, "exception in code");
				throw new InvalidArgumentException($details);
		}
		
		$details = "New record, type=(". $type ."), name=(". $name .")";
		if(is_array($details) && count($extraData) > 0) {
			$details .= implode(", ", $extraData);
		}
		$log->log_by_class($details, "create");
		
		return $result;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function handle_delete($type, $recordId) {
		$retval = "Invalid section... type=(". $type ."), recordId=(". $recordId .")";
		
//		$log = new cs_webdblogger($this->dbObj, "Character");
		
		switch($type) {
			case 'weapon':
			case Weapon::sheetIdPrefix:
				$x = new Weapon();
				$x->load($this->dbObj, $recordId);
				$retval = $x->delete($this->dbObj);
				break;
			
			case 'armor':
			case Armor::sheetIdPrefix:
				$x = new Armor();
				$x->load($this->dbObj, $recordId);
				$retval = $x->delete($this->dbObj);
				break;
			
			case 'skill':
			case Skill::sheetIdPrefix:
				$x = new Skill();
				$x->load($this->dbObj, $recordId);
				$retval = $x->delete($this->dbObj);
				break;
			
			case 'feat':
			case 'specialAbility':
			case SpecialAbility::sheetIdPrefix:
				$x = new SpecialAbility();
				$x->load($this->dbObj, $recordId);
				$retval = $x->delete($this->dbObj);
				break;
			
			case 'gear':
			case Gear::sheetIdPrefix:
				$x = new Gear();
				$x->load($this->dbObj, $recordId);
				$retval = $x->delete($this->dbObj);
				break;
		}
		
//		$log->log_by_class("type=(". $type ."), recordId=(". $recordId ."), result::: ". $retval, "delete");
		
		return $retval;
	}
	//==========================================================================
}

