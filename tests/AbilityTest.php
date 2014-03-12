<?php

class AbilityTest extends testDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new csbt_character($this->dbObj, __CLASS__, 1);
		$this->id = $this->char->characterId;
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
//		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_ability_list() {
		$x = new csbt_ability($this->dbObj);
		
		$myCache = $x->get_all_abilities();
		
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
	public function test_creation() {
		$x = new csbt_ability($this->dbObj);
		$cache = $x->get_all_abilities();
		
		$list = array();
		
		foreach($cache as $abilityName=>$abilityId) {
			$initialData = array(
				'character_id'	=> $this->id,
				'ability_id'	=> $abilityId,
			);
			$test = new csbt_ability($this->dbObj, $initialData);
			
			$this->assertEquals($initialData, $test->data);
			$this->assertEquals(null, $test->id);
			
//			$this->assertTrue($test->save());
			$id = $test->save();
			$this->assertTrue(is_numeric($id));
			$this->assertFalse(isset($list[$abilityName]));
			$list[$abilityName] = $test;
			
			$test->load();
			
			$this->assertEquals(0, $test->get_modifier());
			$this->assertEquals(0, $test->get_temp_modifier());
		}
		
		$this->assertEquals(count($list), count($cache));
		foreach($cache as $an => $ai) {
//			$this->assertEquals($an, $list[$an]->ability_name);
			$this->assertEquals($ai, $list[$an]->ability_id);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_create_character_defaults() {
		$x = new csbt_ability($this->dbObj);
		$x->characterId = $this->id;
		
		$abCache = $x->get_all_abilities();
		$res = $x->create_character_defaults();
		
		$this->assertTrue(count($abCache) > 0);
		$this->assertTrue(is_numeric($res));
		$this->assertTrue($res > 0);
		$this->assertEquals(count($abCache), $res, "Failed to create all abilities for character (". count($abCache) ."/". $res.")");
		
		$list = $x->get_all_character_abilities();
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
	public function test_create_character_defaults_specify_minmax() {
		$x = new csbt_ability($this->dbObj);
		$x->characterId = $this->id;
		
		$abCache = $x->get_all_abilities();
		$res = $x->create_character_defaults();
		
		$this->assertTrue(count($abCache) > 0);
		$this->assertTrue(is_numeric($res));
		$this->assertTrue($res > 0);
		$this->assertEquals(count($abCache), $res, "Failed to create all abilities for character (". count($abCache) ."/". $res.")");
		
		$minScore = rand(1,10);
		$maxScore = rand(11,50);
		
		$list = $x->get_all_character_abilities($minScore, $maxScore);
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
			
			$this->assertTrue($data['ability_score'] >= $minScore);
			$this->assertTrue($data['ability_score'] <= $maxScore);
		}
	}
	//--------------------------------------------------------------------------
}

