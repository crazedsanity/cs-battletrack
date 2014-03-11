<?php

class testOfCSBattleTrack extends testDbAbstract {
	
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
		
		$this->assertEquals($new->characterId, $x->characterId);
		$this->assertTrue(is_array($new->data));
		$this->assertEquals($new->data, $x->data);
		
	}//end test_everything()
	//--------------------------------------------------------------------------
	
	
}

