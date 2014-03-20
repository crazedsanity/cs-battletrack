<?php


//TODO: consider optionally adding the logging system.

class csbt_characterSheet {
	
	protected $characterId;
	protected $ownerUid;
	
	public $dbObj;
	public $gfObj;
	
	
	protected $_char = "TeST";
	protected $_abilities = array();
	protected $_armor = array();
	protected $_gear = array();
	protected $_saves = array();
	protected $_skills = array();
	protected $_specialAbilities = array();
	protected $_weapons = array();
	
	//==========================================================================
	public function __construct(cs_phpDB $db, $characterIdOrName, $ownerUid, $createOrLoad=true) {
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
		foreach($this->_gear as $obj) {
			$weight += $obj->get_total_weight();
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
}

