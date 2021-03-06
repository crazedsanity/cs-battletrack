<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\character\Character;
use battletrack\character\Skill;
use battletrack\character\Ability;

class testOfCSBattleTrack extends TestDbAbstract {
	
	
	public $autoSkills = array();
	public $char;
	
	//--------------------------------------------------------------------------
	function setUp() {
		parent::setUp();
		$this->reset_db();
		$this->dbObj->run_sql_file(__DIR__ .'/../vendor/crazedsanity/database/setup/schema.pgsql.sql');
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new Character(__CLASS__, 1, $this->dbObj);
		
		
		// list of default skills...
		$this->autoSkills = array();
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
		$x = new Skill();
		$x->characterId = $this->char->characterId;
		
		$a = new Ability();
		$a->characterId = $this->char->characterId;
		$a->create_defaults($this->dbObj);
		$cache = $a->get_all($this->dbObj, $this->char->id);
		
		$createdSkills = array();
		
		
		$this->assertEquals(6, count($cache));
		
		$this->assertTrue(is_array($this->autoSkills));
		$this->assertTrue(count($this->autoSkills) > 10);
		
		foreach($this->autoSkills as $id=>$theData) {
			$name = $theData[0];
			$attribute = $theData[1];
			$this->assertTrue(isset($cache[$attribute]), "missing attribute '". $attribute ."' in cache... ". ToolBox::debug_print($cache,0));
			$this->assertTrue(isset($cache[$attribute]['ability_id']));
			
			$insertData = array(
				'character_id'	=> $x->characterId,
				'ability_id'	=> $cache[$attribute]['ability_id'],
				'skill_name'	=> $name,
			);
			
			$id = $x->create($this->dbObj, $insertData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			
			$testData = $x->load($this->dbObj);
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
	
	
	
	//--------------------------------------------------------------------------
	public function test_update_and_delete() {
		$x = new Skill();
		$x->characterId = $this->char->characterId;
		
		$a = new Ability();
		$a->characterId = $x->characterId;
		$a->create_defaults($this->dbObj);
		$cache = $a->get_all($this->dbObj, $this->char->id);
		
		$createdSkills = array();
		foreach($this->autoSkills as $i=>$data) {
			$name = $data[0];
			$ability = $data[1];
			
			$insertThis = array(
				'character_id'	=> $x->characterId,
				'ability_id'	=> $cache[$ability]['ability_id'],
				'skill_name'	=> $name,
			);
			
			$id = $x->create($this->dbObj, $insertThis);
			$createdSkills[$id] = $x->load($this->dbObj);
		}
		
		foreach($createdSkills as $id=>$data) {
			$x->id = $id;
			
			$this->assertEquals($data, $x->load($this->dbObj));
			$this->assertEquals($data, $x->data);
			
			$this->assertEquals(null, $x->update('skill_name', $data['skill_name'] .' -- '. __METHOD__));
			$this->assertEquals(1, $x->save($this->dbObj));
			$this->assertNotEquals($data, $x->load($this->dbObj));
			
			$data['skill_name'] .= ' -- '. __METHOD__;
			$this->assertEquals($data, $x->data);
			
			$this->assertEquals(1, $x->delete($this->dbObj));
			$this->assertEquals(array(), $x->load($this->dbObj));
			$this->assertEquals($id, $x->id);
			unset($createdSkills[$id]);
		}
		
		$this->assertEquals(0, count($createdSkills));
		$this->assertEquals(array(), $createdSkills);
		$this->assertEquals(array(), $x->get_all($this->dbObj, $this->char->id));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_calculate_skill_modifier() {
		$x = new Skill();
		
		$this->assertEquals(0, $x->calculate_skill_modifier(array()));
		$this->assertEquals(0, $x->calculate_skill_modifier(array('ability_mod'=>0)));
		
		$this->assertEquals(
				6,
				$x->calculate_skill_modifier(array(
					'ability_mod'	=> 1,
					'ranks'			=> 2,
					'misc_mod'		=> 3
				))
			);
		$this->assertEquals(
				5,
				$x->calculate_skill_modifier(array(
					'ranks'			=> 2,
					'misc_mod'		=> 3,
				))
			);
		$this->assertEquals(
				3,
				$x->calculate_skill_modifier(array(
					'misc_mod'		=> 3,
				))
			);
		$this->assertEquals(
				-6,
				$x->calculate_skill_modifier(array(
					'ability_mod'	=> -1,
					'ranks'			=> -2,
					'misc_mod'		=> -3
				))
			);
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_load() {
		$x = new Skill();
		$x->characterId = $this->char->characterId;
		$a = new Ability();
		$a->characterId = $x->characterId;
		$a->create_defaults($this->dbObj);
		
		$cache = $a->get_all($this->dbObj, $this->char->id);
		
		$createData = array(
			'character_id'		=> $x->characterId,
			'ability_id'		=> $cache['str']['ability_id'],
			'skill_name'		=> __METHOD__,
			'ranks'				=> 15,
			'is_class_skill'	=> true,
			'misc_mod'			=> -2,
		);
		
		$id = $x->create($this->dbObj, $createData);
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		
		$myData = $x->load($this->dbObj);
		
		$this->assertTrue(is_array($myData));
		$this->assertTrue(count($myData) > 0);
		
		foreach($createData as $k=>$v) {
			$this->assertEquals($v, $myData[$k], "Value mismatch for key '". $k ."'... (". $v ." != ". $myData[$k] .")");
		}
		$this->assertTrue(is_bool($myData['is_class_skill']));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_all() {
		$x = new Skill();
		$x->characterId = $this->char->characterId;
		
		$a = new Ability();
		$a->characterId = $this->char->characterId;
		$a->create_defaults($this->dbObj);
		
		$cache = $a->get_all($this->dbObj, $this->char->id);
		
		$byAbility = array(
			'str'	=> array(),
			'con'	=> array(),
			'dex'	=> array(),
			'int'	=> array(),
			'wis'	=> array(),
			'cha'	=> array()
		);
		
		$this->assertEquals(6, count($byAbility));
		
		foreach($this->autoSkills as $k=>$theData) {
			$name = $theData[0];
			$ability = $theData[1];
			$insertData = array(
				'character_id'	=> $x->characterId,
				'ability_id'	=> $cache[$ability]['ability_id'],
				'skill_name'	=> $name,
			);
			$id = $x->create($this->dbObj, $insertData);
			
			$this->assertTrue(isset($byAbility[$ability]));
			$this->assertFalse(isset($byAbility[$ability][$id]));
			
			$byAbility[$ability][$id] = $insertData;
		}
		
		$this->assertEquals(6, count($byAbility));
		
		foreach($byAbility as $ability=>$data) {
			
			$aId = $cache[$ability]['ability_id'];
			$testData = $x->get_all($this->dbObj, $x->characterId, $aId);
			
			$this->assertTrue(is_array($testData));
			
			foreach($testData as $k=>$v) {
				$this->assertTrue(is_array($v));
				$this->assertTrue(isset($v['ability_id']));
				$this->assertEquals($aId, $v['ability_id']);
			}
			$this->assertEquals(count($data), count($testData), "getting skills based on ability id (". $ability ."/". $aId .") failed...");
			
		}
	}
	//--------------------------------------------------------------------------
}
