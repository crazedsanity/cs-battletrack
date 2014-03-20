<?php

class ArmorTest extends testDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new csbt_character(__CLASS__, 1, $this->dbObj);
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
		$x = new csbt_armor();
		$x->characterId = $this->id;
		
		$data = array(
			'character_id'	=> $this->id,
			'armor_name'	=> __METHOD__ ." +5 of holy awesomeness",
			'armor_type'	=> "light",
			'ac_bonus'		=> 5,
			'check_penalty'	=> 0,
			'max_dex'		=> 9,
			'special'		=> "Smells like good code",
			'weight'		=> 12,
			'max_speed'		=> 30,
			'is_worn'		=> 'f',
		);
		
		$id = $x->create($this->dbObj, $data);
		$this->assertTrue(is_numeric($id));
		
		$dbData = $x->load($this->dbObj);
		
		//make sure we understand how "interpret_bool()" works..
		$gf = new cs_globalFunctions();
		$this->assertFalse($gf->interpret_bool('f', array(false,true)));
		$this->assertTrue($gf->interpret_bool('t', array(false,true)));
		
		$this->assertTrue(is_array($dbData));
		$this->assertTrue(count($dbData) > 0);
		
		foreach($data as $f=>$v) {
			if($f == 'is_worn') {
				$expected = cs_global::interpret_bool($v, array(false, true));
				$this->assertEquals($expected, $dbData[$f], "field (". $f .") value doesn't match... expected (". $expected ."), got (". $dbData[$f] .")");
			}
			else {
				$this->assertEquals($v, $dbData[$f]);
			}
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update() {
		$x = new csbt_armor();
		$x->characterId = $this->id;
		
		$createData = array(
			'character_id'	=> $this->id,
			'armor_name'	=> __METHOD__ ." + 5",
		);
		$id = $x->create($this->dbObj, $createData);
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		$this->assertEquals($id, $x->id);
		$testData = $createData;
		
		unset($testData['character_id']);
		
		$dbData = $x->load($this->dbObj);
		foreach($testData as $k=>$v) {
			$this->assertEquals($v, $dbData[$k]);
		}
		
		$newVals = array(
			'ac_bonus'		=> 5,
			'armor_name'	=> "Balor Armor of Magickness +2",
		);
		$x->mass_update($newVals);
		$this->assertTrue($x->save($this->dbObj));
		
		$dbData = $x->load($this->dbObj);
		
		foreach($testData as $k=>$v) {
			$this->assertNotEquals($v, $dbData[$k]);
		}
		foreach($newVals as $k=>$v) {
			$this->assertEquals($v, $dbData[$k]);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_delete() {
		$x = new csbt_armor();
		$x->characterId = $this->id;
		
		$allArmor = array(
			array(
				'armor_name'	=> "first armor, +". __LINE__,
				'ac_bonus'		=> __LINE__,
			),
			array(
				'armor_name'	=> "second armor, +". __LINE__,
				'ac_bonus'		=> __LINE__,
			),
		);
		
		$myIds = array();
		foreach($allArmor as $data) {
			$data['character_id'] = $this->id;
			$thisId = $x->create($this->dbObj, $data);
			
			$this->assertTrue(is_numeric($thisId));
			
			$myIds[$thisId] = $data;
		}
		
		$numLeft = count($myIds);
		foreach($myIds as $i=>$insertData) {
			$x->id = $i;
			$testData = $x->load($this->dbObj);
			
			foreach($insertData as $k=>$v) {
				$this->assertEquals($v, $testData[$k]);
			}
		}
	}
	//--------------------------------------------------------------------------
}
