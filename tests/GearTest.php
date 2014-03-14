<?php

class GearTest extends testDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new csbt_character($this->dbObj, __CLASS__, 1);
		$this->id = $this->char->id;
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_create() {
		$x = new csbt_gear($this->dbObj);
		$x->characterId = $this->id;
		
		$insertData = array(
			'character_id'	=> $x->characterId,
			'gear_name'		=> "Torch of coding +1",
			'weight'		=> 0.1,
			'quantity'		=> 10,
			'location'		=> "backpack",
		);
		
		try {
			$x->create();
			$this->fail("creation without any data succeeded");
		} catch (Exception $ex) {
			if(!preg_match('/create\(\) must be .+ array, none given/', $ex->getMessage())) {
				$this->fail("Malformed or unexpected error: ". $ex->getMessage());
			}
		}
		try {
			$x->create(null);
			$this->fail("creation with null data succeeded");
		} catch (Exception $ex) {
			if(!preg_match('/create\(\) must be .+ array, null given/', $ex->getMessage())) {
				$this->fail("Malformed or unexpected error: ". $ex->getMessage());
			}
		}
		try {
			$x->create(array());
			$this->fail("creation with empty array succeeded");
		} catch (Exception $ex) {
			if(!preg_match("/create: cannot create record in '". $x::tableName ."' with no data/", $ex->getMessage())) {
				$this->fail("Malformed or unexpected error: ". $ex->getMessage());
			}
		}
		
		$id = $x->create($insertData);
		
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		
		$data = $x->load();
		
		$this->assertTrue(is_array($data));
		$this->assertTrue(count($data) > count($insertData));
		
		foreach($insertData as $k=>$v) {
			$this->assertTrue(isset($data[$k]));
			$this->assertEquals($v, $data[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update() {
		$x = new csbt_gear($this->dbObj);
		$x->characterId = $this->id;
		
		$initial = array(
			'character_id'	=> $x->characterId,
			'gear_name'		=> ""
		);
		$id = $x->create($initial);
		
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		
		$afterCreate = $x->load();
		$this->assertTrue(is_array($afterCreate));
		$this->assertTrue(count($afterCreate) > count($initial));
		
		foreach($initial as $k=>$v) {
			$this->assertTrue(isset($afterCreate[$k]));
			$this->assertEquals($v, $afterCreate[$k]);
		}
		
		$updates = array(
			'gear_name'		=> "Item of Magickness",
			'weight'		=> 3,
			'quantity'		=> 20,
		);
		
		$this->assertEquals(null, $x->mass_update($updates));
		$this->assertTrue($x->save());
		
		$afterUpdate = $x->load();
		$this->assertTrue(is_array($afterUpdate));
		$this->assertTrue(count($afterUpdate) > count($updates));
		$this->assertEquals(count($afterUpdate), count($afterCreate));
		$this->assertNotEquals($afterUpdate, $afterCreate);
		
		foreach($updates as $k=>$v) {
			$this->assertTrue(isset($afterUpdate[$k]));
			$this->assertEquals($v, $afterUpdate[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_save_from_initial_load() {
		$initial = array(
			'character_id'	=> $this->id,
			'gear_name'		=> "Specially invalid",
		);
		$x = new csbt_gear($this->dbObj, $initial);
		
		$this->assertEquals(null, $x->id);
		
		$id = $x->save();
		
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		
		$data = $x->load();
		$this->assertTrue(is_array($data));
		$this->assertTrue(count($data) > 0);
		
		foreach($initial as $k=>$v) {
			$this->assertTrue(isset($data[$k]));
			$this->assertEquals($v, $data[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_delete() {
		$x = new csbt_gear($this->dbObj);
		$x->characterId = $this->id;
		
		$allGear = array('torch', 'silk rope', 'bullseye lantern');
		
		$createdGear = array();
		$numCreated = 0;
		foreach($allGear as $name) {
			$insert = array(
				'character_id'	=> $x->characterId,
				'gear_name'		=> $name,
				'weight'		=> __LINE__,
				'quantity'		=> $numCreated++,
			);
			$id = $x->create($insert);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			$this->assertFalse(isset($createdGear[$id]));
			
			$createdGear[$id] = $x->load();
			$this->assertTrue(isset($createdGear[$id]));
			$this->assertTrue(is_array($createdGear[$id]));
			$this->assertTrue(count($createdGear[$id]) > count($insert));
		}
		
		$this->assertEquals($numCreated, count($createdGear));
		
		foreach(array_keys($createdGear) as $id) {
			$x->id = $id;
			$this->assertEquals($createdGear[$id], $x->load());
			$this->assertEquals(1, $x->delete());
			
			$this->assertEquals($id, $x->id);
			$this->assertEquals(array(), $x->load());
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_all_character_gear() {
		$x = new csbt_gear($this->dbObj);
		$x->characterId = $this->id;
		
		$createThis = array('torches', 'silk rope', 'bullseye lantern');
		
		$list = array();
		foreach($createThis as $name) {
			$_createData = array(
				'character_id'	=> $x->characterId,
				'gear_name'		=> $name
			);
			$id = $x->create($_createData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			
			$list[$id] = $x->load();
		}
		$this->assertEquals(count($createThis), count($list));
		
		$allGear = $x->get_all_character_gear();
		
		$this->assertTrue(is_array($allGear));
		$this->assertTrue(count($allGear) > 0);
		
		foreach($allGear as $k=>$v) {
			$this->assertTrue(isset($list[$k]));
			$this->assertEquals($v, $list[$k]);
		}
	}
	//--------------------------------------------------------------------------
}
