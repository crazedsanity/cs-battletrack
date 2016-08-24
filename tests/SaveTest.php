<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\character\Character;

class SavesTest extends TestDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
//		$this->gfObj = new cs_globalFunctions;
//		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
//		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
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
	public function test_creation() {
		$x = new csbt_save();
		$x->characterId = $this->id;
		$y = new csbt_ability();
		
		$allSaves = $x->get_all_saves($this->dbObj);
		foreach($allSaves as $k=>$v) {
			$data = array(
				'character_id'	=> $this->id,
				'save_id'		=> $v['save_id'],
				'base_mod'		=> 1,
				'magic_mod'		=> 2,
				'misc_mod'		=> 3,
				'temp_mod'		=> 4,
			);
			$res = $x->create($this->dbObj, $data);
			$this->assertTrue(is_numeric($res));
		}
		
		$storedData = $x->load($this->dbObj);
		
		foreach($storedData as $k=>$v) {
			$this->assertEquals($v, $storedData[$k], "Mismatched values for '". $k ."'... expected (". $v ."), got (". $storedData[$k] .")");
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	
	//--------------------------------------------------------------------------
	public function test_create_character_defaults_and_modifier() {
		$x = new csbt_save();
		$y = new csbt_ability();
		
		$y->characterId = $this->id;
		$x->characterId = $this->id;
		
		$y->create_defaults($this->dbObj);
		
		$numCreated = $x->create_character_defaults($this->dbObj);
		$list = $x->get_all($this->dbObj, $x->characterId);
		
		$this->assertEquals($numCreated, count($list), ToolBox::debug_print($list));
		
		foreach($list as $k=>$data) {
			$data = $x->_get_record_extras($data);
			$this->assertEquals($data['total'], $x->calculate_total_save_modifier($data));
			
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_updates() {
		$x = new csbt_save();
		$x->characterId = $this->id;
		
		$y = new csbt_ability();
		$y->characterId = $this->id;
		$y->create_defaults($this->dbObj);
		
		$numCreated = $x->create_character_defaults($this->dbObj);
		$list = $x->get_all($this->dbObj, $x->characterId);
		
		$this->assertEquals($numCreated, count($list));
		
		$orig = $x->load($this->dbObj);
		
		$changes = array(
			'base_mod'	=> 1,
			'magic_mod'	=> 2,
			'misc_mod'	=> 3,
			'temp_mod'	=> 4,
		);
		
		foreach($changes as $field=>$newVal) {
			$this->assertEquals($orig, $x->load($this->dbObj));
			
			$x->update($field, $newVal);
			$this->assertTrue($x->save($this->dbObj));
			
			$expectThis = $orig;
			$expectThis[$field] = $newVal;
			
			$this->assertEquals($expectThis, $x->load($this->dbObj));
			
			foreach($orig as $f=>$v) {
				$x->update($f, $v);
			}
			$this->assertTrue($x->save($this->dbObj));
		}
		
		$x->mass_update($orig);
		
		$this->assertTrue($x->save($this->dbObj));
		$this->assertEquals($orig, $x->load($this->dbObj));
		
		$x->mass_update($changes);
		$this->assertTrue($x->save($this->dbObj));
		$afterMassUpdate = $x->load($this->dbObj);
		
		foreach($changes as $k=>$v) {
			$this->assertEquals($v, $afterMassUpdate[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_delete() {
		$x = new csbt_save();
		$x->characterId = $this->id;
		$y = new csbt_ability();
		$y->characterId = $this->id;
		
		$y->create_defaults($this->dbObj);
		$numCreated = $x->create_character_defaults($this->dbObj);
		
		$this->assertTrue(is_numeric($numCreated));
		$this->assertTrue($numCreated > 0);
		
		$allSaves = $x->get_all($this->dbObj, $x->characterId);
		$numLeft = count($allSaves);
		
		foreach($allSaves as $k=>$data) {
			
			$this->assertEquals($numLeft, count($x->get_all($this->dbObj, $x->characterId)));
			
			$this->assertTrue(is_array($data));
			$this->assertTrue(isset($data['character_save_id']));
			
			$x->id = $data['character_save_id'];
			$this->assertEquals(1, $x->delete($this->dbObj));
			
			$numLeft--;
		}
		
		$this->assertEquals(0, $numLeft);
		$this->assertEquals(array(), $x->get_all($this->dbObj, $x->characterId));
	}
	//--------------------------------------------------------------------------
}
