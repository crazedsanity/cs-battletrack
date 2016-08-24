<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\character\Character;

class SpecialAbilityTest extends TestDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
//		$this->gfObj = new cs_globalFunctions;
//		$this->gfObj->debugPrintOpt=1;
		ToolBox::$debugPrintOpt = 1;
		
		parent::setUp();
		$this->reset_db();
//		$this->dbObj->load_schema($this->dbObj->get_dbtype(), $this->dbObj);
		$this->dbObj->run_sql_file(__DIR__ .'/../vendor/crazedsanity/database/setup/schema.pgsql.sql');
		$this->dbObj->run_sql_file(dirname(__FILE__) .'/../docs/sql/tables.sql');
		
		$this->char = new Character(__METHOD__, 1, $this->dbObj);
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_create_and_load() {
		$x = new csbt_specialAbility();
		$x->characterId = $this->char->characterId;
		
		$createThese = array(
			'Track', 'Wild Empathy', 'Nerd Herder',
		);
		
		$list = array();
		
		foreach($createThese as $n) {
			$xData = array(
				'character_id'			=> $x->characterId,
				'special_ability_name'	=> $n,
				'description'			=> __METHOD__ .": ". __LINE__,
				'book_reference'		=> "PHB ". __LINE__,
			);
			
			$id = $x->create($this->dbObj, $xData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			$this->assertFalse(isset($list[$id]));
			
			$data = $x->load($this->dbObj);
			
			$this->assertTrue(is_array($data));
			$this->assertTrue(count($data) >= count($xData));
			
			$list[$id] = $data;
		}
		
		$this->assertEquals(count($list), count($createThese));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update_and_delete() {
		$x = new csbt_specialAbility();
		$x->characterId = $this->char->characterId;
		
		$id = $x->create(
			$this->dbObj, 
			array(
				'character_id'	=> $x->characterId,
				'special_ability_name'	=> __METHOD__,
			)
		);
		
		$this->assertTrue(is_numeric($id));
		$this->assertTrue($id > 0);
		
		$data = $x->load($this->dbObj);
		
		$this->assertTrue(is_array($data));
		$this->assertTrue(count($data) > 0);
		
		$this->assertEquals(1, $x->delete($this->dbObj));
		
		$this->assertEquals(array(), $x->load($this->dbObj));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_get_all() {
		$x = new csbt_specialAbility();
		$x->characterId = $this->char->characterId;
		
		$created = array();
		
		for($i=0; $i<=10; $i++) {
			$xData = array(
				'character_id'	=> $x->characterId,
				'special_ability_name'	=> __METHOD__ ." #". $i,
			);
			$id = $x->create($this->dbObj, $xData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			
			$data = $x->load($this->dbObj);
			
			$this->assertFalse(isset($created[$id]));
			$this->assertTrue(is_array($data));
			$this->assertTrue(count($data) > 0);
			
			$created[$id] = $data;
		}
		
		$allRecs = $x->get_all($this->dbObj, $x->characterId);
		
		$this->assertEquals($i, count($allRecs));
		$this->assertEquals(count($created), count($allRecs));
		
		foreach($created as $id=>$data) {
			$this->assertTrue(isset($allRecs[$id]));
			$this->assertEquals($data, $allRecs[$id]);
		}
	}
	//--------------------------------------------------------------------------
}
