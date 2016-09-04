<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\character\Ability;
use battletrack\character\Character;

class AbilityTest extends TestDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
//		$this->gfObj = new cs_globalFunctions;
//		$this->gfObj->debugPrintOpt=1;
		ToolBox::$debugPrintOpt = 1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->run_sql_file(__DIR__ .'/../vendor/crazedsanity/database/setup/schema.pgsql.sql');
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new Character(__CLASS__, 1, $this->dbObj);
		$this->id = $this->char->characterId;
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_ability_list() {
		$myCache = Ability::get_all_abilities($this->dbObj);
		
		$requiredStats = array('str', 'con', 'dex', 'wis', 'int', 'cha');
		
		$passed = 0;
		foreach($requiredStats as $n) {
			$this->assertTrue(isset($myCache[$n]), "Missing required stat (". $passed ."/". count($requiredStats) .") '". $n ."'");
			$passed++;
		}
		
		foreach($myCache as $k=>$v) {
			$this->assertTrue(is_string($k));
			$this->assertTrue(is_numeric($v));
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function txest_creation() {
		$x = new Ability();
		$cache = $x->get_all_abilities($this->dbObj);
		
		$list = array();
		
		foreach($cache as $abilityName=>$abilityId) {
			$initialData = array(
				'character_id'	=> $this->id,
				'ability_id'	=> $abilityId,
			);
			$test = new Ability($initialData);
			
			$this->assertEquals($initialData, $test->data);
			$this->assertEquals(null, $test->id);
			
			$id = $test->save($this->dbObj);
			$this->assertTrue(is_numeric($id));
			$this->assertFalse(isset($list[$abilityName]));
			$list[$abilityName] = $test;
			
			$test->load($this->dbObj);
			
			$this->assertEquals(0, $test->get_modifier());
			$this->assertEquals(0, $test->get_temp_modifier());
		}
		
		$this->assertEquals(count($list), count($cache));
		foreach($cache as $an => $ai) {
			$this->assertEquals($ai, $list[$an]->ability_id, cs_global::debug_print($list[$an]));
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function txest_create_character_defaults() {
		$x = new Ability();
		$x->characterId = $this->id;
		
		$abCache = $x->get_all_abilities($this->dbObj);
		$res = $x->create_defaults($this->dbObj);
		
		$this->assertTrue(count($abCache) > 0);
		$this->assertTrue(is_numeric($res));
		$this->assertTrue($res > 0);
		$this->assertEquals(count($abCache), $res, "Failed to create all abilities for character (". count($abCache) ."/". $res.")");
		
		$list = $x->get_all($this->dbObj, $this->id);
		$uniq = array();
		foreach($list as $n=>$data) {
			$this->assertTrue(isset($data['character_id']));
			$this->assertTrue(isset($data['character_ability_id']));
			$this->assertTrue(isset($data['ability_score']));
			$this->assertTrue(isset($data['ability_id']));
			$this->assertTrue(isset($data['ability_name']));
			
			$this->assertEquals($n, $data['ability_name']);
			$this->assertFalse(isset($uniq[$n]));
			$uniq[$n] = $data;
			
			$this->assertTrue(array_key_exists('temporary_score', $data));
			$this->assertEquals(null, $data['temporary_score']);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function txest_create_character_defaults_specify_minmax() {
		$x = new Ability();
		$x->characterId = $this->id;
		
		$minScore = rand(1,10);
		$maxScore = rand(11,50);
		
		$abCache = $x->get_all_abilities($this->dbObj);
		$res = $x->create_defaults($this->dbObj, $minScore, $maxScore);
		
		$this->assertTrue(count($abCache) > 0);
		$this->assertTrue(is_numeric($res));
		$this->assertTrue($res > 0);
		$this->assertEquals(count($abCache), $res, "Failed to create all abilities for character (". count($abCache) ."/". $res.")");
		
		$list = $x->get_all($this->dbObj, $this->id);
		$uniq = array();
		foreach($list as $n=>$data) {
			$this->assertTrue(isset($data['character_id']));
			$this->assertTrue(isset($data['character_ability_id']));
			$this->assertTrue(isset($data['ability_score']));
			$this->assertTrue(isset($data['ability_id']));
			$this->assertTrue(isset($data['ability_name']));
			
			$this->assertEquals($n, $data['ability_name']);
			$this->assertFalse(isset($uniq[$n]));
			$uniq[$n] = $data;
			
			$this->assertTrue(array_key_exists('temporary_score', $data));
			$this->assertEquals(null, $data['temporary_score']);
			
			$this->assertTrue($data['ability_score'] >= $minScore, "score is too low? (". $data['ability_score'] .")");
			$this->assertTrue($data['ability_score'] <= $maxScore, "score is too high? (". $data['ability_score'] .")");
		}
	}
	//--------------------------------------------------------------------------
	
	
	//--------------------------------------------------------------------------
	public function texst_delete() {
		$x = new Ability();
		$x->characterId = $this->id;
		$x->create_defaults($this->dbObj);
		
		$allRecords = $x->get_all($this->dbObj, $this->id);
		$this->assertTrue(count($allRecords) > 0);
		
		$keys = array_keys($allRecords);
		
		$lastRec = count($allRecords);
		
		foreach($allRecords as $ability=>$data) {
			$this->assertEquals($lastRec, count($x->get_all($this->dbObj, $this->id)));
			
			$this->assertTrue(is_string($ability), "ID=(". $ability .")");
			$this->assertTrue(is_array($data));
			$this->assertTrue(count($data) > 0);
			
			$x->id = $data['character_ability_id'];
			$this->assertEquals(1, $x->delete($this->dbObj));
			$lastRec--;
		}
		
		$this->assertEquals(0, $lastRec);
		
		$allAbilities = $x->get_all($this->dbObj, $this->id);
		$this->assertEquals(0, count($allAbilities), cs_global::debug_print($allAbilities));
		$this->assertEquals(array(), $allAbilities);
	}
	//--------------------------------------------------------------------------
}

