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
		
		$this->assertTrue(isset($createdData['abilities']));
		$this->assertTrue(is_array($createdData['abilities']));
		$this->assertTrue(count($createdData['abilities']) > 0);
		
		$this->assertTrue(isset($createdData['skills']));
		$this->assertTrue(is_array($createdData['skills']));
		$this->assertTrue(count($createdData['skills']) > 0);
		
		$this->assertTrue(isset($createdData['saves']));
		$this->assertTrue(is_array($createdData['saves']));
		$this->assertTrue(count($createdData['saves']) > 0);
//		
//cs_global::debug_print($x,1);
//		$this->assertTrue(is_object($x->char));
	}
	//--------------------------------------------------------------------------
}