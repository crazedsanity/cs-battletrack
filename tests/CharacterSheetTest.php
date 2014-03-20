<?php

class CharacterSheetTest extends testDbAbstract {
	
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
	public function test_create_and_manually_load_defaults() {
		$x = new csbt_characterSheet($this->dbObj, __METHOD__, 1, false);
		
		$createdData = $x->create_defaults();
		$this->assertTrue(is_array($createdData));
		
		$hasRecords = array('abilities', 'skills', 'saves');
		$noRecords = array('weapons', 'armor', 'gear', 'specialAbilities');
		
		$totalIndexesCounted = 0;
		
		foreach($hasRecords as $idx) {
			$this->assertTrue(isset($createdData[$idx]), "no records created for ". $idx);
			$this->assertTrue(is_array($createdData[$idx]), "no records created/not array for ". $idx);
			$this->assertTrue(count($createdData[$idx]) > 0, "no records (zero count) for ". $idx);
			
			$totalIndexesCounted++;
		}
		
		foreach($noRecords as $idx) {
			$this->assertTrue(isset($createdData[$idx]), "no index for ". $idx);
			$this->assertTrue(is_array($createdData[$idx]), "no records created/not array for ". $idx);
			$this->assertTrue(count($createdData[$idx]) == 0, "found records created for ". $idx);
			
			$totalIndexesCounted++;
		}
		
		$this->assertEquals($totalIndexesCounted, count($createdData), "missed some data in the test");
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_total_weight() {
		$x = new csbt_characterSheet($this->dbObj, __METHOD__, 1);
		
		$this->assertTrue(is_numeric($x->characterId), cs_global::debug_print($x));
		
		$this->assertEquals(0, $x->get_total_weight(false));
		$this->assertEquals($x->get_total_weight(true), $x->get_total_weight(false));
		
		//now create some gear.
		$manualWeight = 0;
		$itemList = array();
		$createThis = array(
			//name					weight	quantity
			array('torches',		1,		10),
			array('lead nuggets',	10,		10),
			array('misc',			4,		200),
		);
		
		$g = new csbt_gear();
		$g->characterId = $x->characterId;
		
		foreach($createThis as $data) {
			$manualWeight += round(($data[1]*$data[2]),1);
			$xData = array(
				'character_id'	=> $x->characterId,
				'gear_name'		=> $data[0],
				'weight'		=> $data[1],
				'quantity'		=> $data[2],
			);
			$id = $g->create($this->dbObj, $xData);
			$itemList[$id] = $xData;
		}
		
		$this->assertEquals($manualWeight, csbt_gear::calculate_weight($itemList));
		
		//now, at first, this should be 0 because we haven't re-loaded the sheet.
		$this->assertEquals(0, $x->get_total_weight(false));
		$this->assertEquals(0, $x->get_total_weight(true));
		
		$x->load();
		
		$this->assertEquals($manualWeight, $x->get_total_weight(false));
		$this->assertEquals($manualWeight, $x->get_total_weight(true));
	}
	//--------------------------------------------------------------------------
}