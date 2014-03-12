<?php

class CharacterTest extends testDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_creation() {
		$new = new csbt_character($this->dbObj, __METHOD__, 1);
		
		$x = new csbt_character($this->dbObj, $new->characterId, 1);
		
		$data = $x->data;
		
		$this->assertEquals($new->characterId, $x->characterId);
		$this->assertTrue(is_array($new->data));
		$this->assertEquals($new->data, $x->data);
	}//end test_everything()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_magicProperties() {
		$x = new csbt_character($this->dbObj, __METHOD__, 1);
		
		$data = $x->data;
		
		$this->assertEquals($x->data, $data);
		$this->assertEquals($x->data, $x->dataCache);
		$this->assertEquals($x->data, $x->datacache);
		
		foreach($data as $k=>$v) {
			$this->assertEquals($v, $x->$k, "magic property '". $k ."' mismatch...");
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_load() {
		$x = new csbt_character($this->dbObj, __METHOD__, 1);
		
		$this->assertEquals(array(), $x->data);
		
		$x->load();
		
		$this->assertTrue($x->characterId > 0);
		$this->assertTrue(is_array($x->data));
		$this->assertTrue(count($x->data) > 0, "characterId=(". $x->characterId ."), DATA::: ". cs_global::debug_print($x->data));
		
		$data = $x->data;
		
		$indexes = array(
			'character_id', 'uid', 'character_name', 'campaign_id', 'ac_misc', 'ac_size', 
			'ac_natural', 'action_points', 'character_age', 'character_level',
			'alignment', 'base_attack_bonus', 'deity', 'eye_color', 'gender', 
			'hair_color', 'height', 'hit_points_max', 'hit_points_current',
			'race', 'size', 'weight', 'initiative_misc', 'nonlethal_damage', 
			'hit_dice', 'damage_reduction', 'melee_misc', 'melee_size', 
			'melee_temp', 'ranged_misc', 'ranged_size', 'ranged_temp', 
			'skills_max', 'speed', 'xp_current', 'xp_next', 'notes', 'campaign_id',
		);
		foreach($indexes as $expectedIdx) {
			$this->assertTrue(
					array_key_exists($expectedIdx, $data), 
					"loaded data missing required index '". $expectedIdx ."'... ". cs_global::debug_print($data)
				);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update() {
		$x = new csbt_character($this->dbObj, __METHOD__, 1);
		$x->load();
		
		$data = $x->data;
		
		$this->assertTrue(is_numeric($x->characterId));
		$this->assertTrue(is_array($x->data));
		$this->assertTrue(isset($data['character_name']), cs_global::debug_print($data));
		
		$x->update('character_name', __FUNCTION__);
		$this->assertNotEquals($data, $x->data);
		
		$x->load();
		$this->assertEquals($data, $x->data);
		
		
		$x->update('character_name', __CLASS__);
		$this->assertNotEquals($data, $x->data);
		$this->assertTrue($x->save());
		$this->assertNotEquals($data, $x->load());
		$data['character_name'] = __CLASS__;
		$this->assertEquals($data, $x->data);
		
		
		$changes = array(
			'speed'			=> 999,
			'hair_color'	=> "GReEEN",
			'size'			=> "Medium Large-ish",
			'notes'			=> "MOREea STUFfl   qaewrqwe   \n\t",
		);
		$x->mass_update($changes);
		$this->assertTrue($x->save());
		
		$afterMassUpdate = $x->load();
		foreach($changes as $k=>$v) {
			$this->assertEquals($v, $afterMassUpdate[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update_after_removing_field() {
		$x = new _test_character($this->dbObj, __METHOD__, 1);
		$x->load();
		$this->assertEquals(__METHOD__, $x->_data['character_name']);
		
		unset($x->_data['character_name']);
		
		$x->save();
		$x->load();
		
		$this->assertEquals(__METHOD__, $x->_data['character_name']);
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_big_update() {
		$x = new csbt_character($this->dbObj, __METHOD__, 1);
		
		$this->assertEquals(array(), $x->data);
		
		$newData = array(
			'character_name'	=> 'Legolas',
			'ac_misc'			=> __LINE__,
			'ac_size'			=> __LINE__,
			'ac_natural'		=> __LINE__,
			'action_points'		=> __LINE__,
			'character_age'		=> __LINE__,
			'character_level'	=> 'Epic LOTR/'. __LINE__,
			'alignment'			=> 'Awesome',
			'base_attack_bonus'	=> __LINE__,
			'deity'				=> 'Someone...',
			'eye_color'			=> 'possibly green',
			'gender'			=> 'Dude',
			'hair_color'		=> 'white',
			'height'			=> '5\'2"',
			'hit_points_max'	=> __LINE__,
			'hit_points_current'=> __LINE__,
			'race'				=> 'elf',
			'size'				=> 'mEdium',
			'weight'			=> __LINE__,
			'initiative_misc'	=> __LINE__,
			'nonlethal_damage'	=> __LINE__,
			'hit_dice'			=> 'd9',
			'damage_reduction'	=> __LINE__,
			'melee_misc'		=> __LINE__,
			'ranged_misc'		=> __LINE__,
			'ranged_size'		=> __LINE__,
			'ranged_temp'		=> __LINE__,
			'skills_max'		=> 99,
			'speed'				=> 987654,
			'xp_current'		=> __LINE__,
			'xp_next'			=> __LINE__,
			'notes'				=> 'TesTing... Line #'. __LINE__,
			'character_id'		=> 98765432211,
			'uid'				=> 898234,
		);
		
		foreach($newData as $idx=>$val) {
			$x->update($idx, $val);
		}
		
		$this->assertTrue($x->save());
		
		$this->assertTrue(count($x->data) > 0);
		$this->assertNotEquals($x->characterId, $newData['character_id']);
		$this->assertEquals($x->uid, $newData['uid'], "extra data (uid) was unexpectedly removed...");
		
		$x->load();
		
		$this->assertNotEquals($x->uid, $newData['uid']);
	}
	//--------------------------------------------------------------------------
	
	
}

class _test_character extends csbt_character {
	public $_data;
	
	public function _clean_data_array() {
		return parent::_clean_data_array();
	}
}

