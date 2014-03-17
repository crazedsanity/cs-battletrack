<?php

class testOfCSBattleTrack extends testDbAbstract {
	
	
	public $autoSkills = array();
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new csbt_character($this->dbObj, __CLASS__, 1);
		
		
		// list of default skills...
		{
		    $this->autoSkills[] = array("Appraise",			"int");
		    $this->autoSkills[] = array("Balance",			"dex");
		    $this->autoSkills[] = array("Bluff",				"cha");
		    $this->autoSkills[] = array("Climb",				"str");
		    $this->autoSkills[] = array("Concentration",		"con");
		    $this->autoSkills[] = array("Craft ()",			"int");
		    $this->autoSkills[] = array("Craft ()",			"int");
		    $this->autoSkills[] = array("Craft ()",			"int");
		    $this->autoSkills[] = array("Decipher Script",	"int");
		    $this->autoSkills[] = array("Diplomacy",			"cha");
		    $this->autoSkills[] = array("Disable Device",		"int");
		    $this->autoSkills[] = array("Disguise",			"cha");
		    $this->autoSkills[] = array("Escape Artist",		"dex");
		    $this->autoSkills[] = array("Forgery",			"int");
		    $this->autoSkills[] = array("Gather Information",	"cha");
		    $this->autoSkills[] = array("Handle Animal",		"cha");
		    $this->autoSkills[] = array("Heal",				"wis");
		    $this->autoSkills[] = array("Hide",				"dex");
		    $this->autoSkills[] = array("intimidate",			"cha");
		    $this->autoSkills[] = array("Jump",				"str");
		    $this->autoSkills[] = array("Knowledge ()",		"int");
		    $this->autoSkills[] = array("Knowledge ()",		"int");
		    $this->autoSkills[] = array("Knowledge ()",		"int");
		    $this->autoSkills[] = array("Knowledge ()",		"int");
		    $this->autoSkills[] = array("Listen",				"wis");
		    $this->autoSkills[] = array("Move Silently",		"dex");
		    $this->autoSkills[] = array("Open Lock",			"dex");
		    $this->autoSkills[] = array("Perform ()",			"cha");
		    $this->autoSkills[] = array("Perform ()",			"cha");
		    $this->autoSkills[] = array("Perform ()",			"cha");
		    $this->autoSkills[] = array("Profession ()",		"wis");
		    $this->autoSkills[] = array("Profession ()",		"wis");
		    $this->autoSkills[] = array("Ride",				"dex");
		    $this->autoSkills[] = array("Search",				"int");
		    $this->autoSkills[] = array("Sense Motive",		"wis");
		    $this->autoSkills[] = array("Sleight of Hand",	"dex");
		    $this->autoSkills[] = array("Spellcraft",			"int");
		    $this->autoSkills[] = array("Spot",				"wis");
		    $this->autoSkills[] = array("Survival",			"wis");
		    $this->autoSkills[] = array("Swim",				"str");
		    $this->autoSkills[] = array("Tumble",				"dex");
		    $this->autoSkills[] = array("Use Magic Device",	"cha");
		    $this->autoSkills[] = array("Use Rope",			"dex");
		}
		
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_create() {
		$x = new csbt_skill($this->dbObj);
		$x->characterId = $this->char->characterId;
		
		$a = new csbt_ability($this->dbObj);
		$a->characterId = $this->char->characterId;
		$a->create_character_defaults();
		$cache = $a->get_all_character_abilities();
		
		$createdSkills = array();
		
		
		$this->assertEquals(6, count($cache));
		
		$this->assertTrue(is_array($this->autoSkills));
		$this->assertTrue(count($this->autoSkills) > 10);
		
		foreach($this->autoSkills as $id=>$theData) {
			$name = $theData[0];
			$attribute = $theData[1];
			$this->assertTrue(isset($cache[$attribute]), "missing attribute '". $attribute ."' in cache... ". cs_global::debug_print($cache,0));
			$this->assertTrue(isset($cache[$attribute]['ability_id']));
			
			$insertData = array(
				'character_id'	=> $x->characterId,
				'ability_id'	=> $cache[$attribute]['ability_id'],
				'skill_name'	=> $name,
			);
			
			$id = $x->create($insertData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			
			$testData = $x->load();
			$this->assertTrue(is_array($testData));
			$this->assertTrue(count($testData) > 0);
			
			foreach($insertData as $k=>$v) {
				$this->assertEquals($v, $testData[$k]);
			}
			
			$this->assertFalse(isset($createdSkills[$id]));
			$createdSkills[$id] = $testData;
		}
	}
	//--------------------------------------------------------------------------
}
