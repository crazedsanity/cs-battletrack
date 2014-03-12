<?php

class SavesTest extends testDbAbstract {
	
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
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_creation() {
		$x = new csbt_save($this->dbObj);
		$x->characterId = $this->id;
		$y = new csbt_ability($this->dbObj);
		
		$abilities = $y->get_all_abilities();
		$data = array(
			'character_id'	=> $this->id,
			'save_name'		=> 'test',
			'ability_id'	=> $abilities['str'],
			'base_mod'		=> 1,
			'magic_mod'		=> 2,
			'misc_mod'		=> 3,
			'temp_mod'		=> 4,
		);
		$res = $x->create($data);
		$this->assertTrue(is_numeric($res));
		
		$storedData = $x->load();
		
		foreach($storedData as $k=>$v) {
			$this->assertEquals($v, $storedData[$k], "Mismatched values for '". $k ."'... expected (". $v ."), got (". $storedData[$k] .")");
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	
	//--------------------------------------------------------------------------
	public function test_create_character_defaults() {
		$x = new csbt_save($this->dbObj);
		$x->characterId = $this->id;
		
		$numCreated = $x->create_character_defaults();
		$list = $x->get_all_character_saves();
		
		$this->assertEquals($numCreated, count($list));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_updates() {
		$x = new csbt_save($this->dbObj);
		$x->characterId = $this->id;
		
		$numCreated = $x->create_character_defaults();
		$list = $x->get_all_character_saves();
		
		$this->assertEquals($numCreated, count($list));
		
		$orig = $x->load();
		
		$changes = array(
			'base_mod'	=> 1,
			'magic_mod'	=> 2,
			'misc_mod'	=> 3,
			'temp_mod'	=> 4,
		);
		
		foreach($changes as $field=>$newVal) {
			$this->assertEquals($orig, $x->load());
			
			$x->update($field, $newVal);
			$this->assertTrue($x->save());
			
			$expectThis = $orig;
			$expectThis[$field] = $newVal;
			
			$this->assertEquals($expectThis, $x->load());
			
			foreach($orig as $f=>$v) {
				$x->update($f, $v);
			}
			$this->assertTrue($x->save());
		}
		
		$x->mass_update($orig);
		
		$this->assertTrue($x->save());
		$this->assertEquals($orig, $x->load());
		
		$x->mass_update($changes);
		$this->assertTrue($x->save());
		$afterMassUpdate = $x->load();
		
		foreach($changes as $k=>$v) {
			$this->assertEquals($v, $afterMassUpdate[$k]);
		}
	}
	//--------------------------------------------------------------------------
}
