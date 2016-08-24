<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\character\Character;
use battletrack\character\Weapon;

class WeaponTest extends TestDbAbstract {
	
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
		
		$this->char = new Character(__CLASS__, 1, $this->dbObj);
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_create_and_get_all() {
		$x = new Weapon();
		$x->characterId = $this->char->characterId;
		
		$createdWeapons = array();
		$inUse = array();
		$notInUse = array();
		
		$testData = array(
			array(
				'weapon_name'			=> 'Magic Sword +1',
				'total_attack_bonus'	=> 6,
				'damage'				=> '2d6+9',
				'critical'				=> '20x3',
				'range'					=> 30,
				'special'				=> __METHOD__ ." gives a +1 bonus to geek",
				'ammunition'			=> 10,
				'weight'				=> 3,
				'size'					=> '[HS]',
				'weapon_type'			=> 'geek',
				'in_use'				=> false
			),
			array(
				'weapon_name'			=> 'Test #2...',
				'in_use'				=> true,
			),
		);
		
		foreach($testData as $cData) {
			$cData['character_id'] = $x->characterId;
			$id = $x->create($this->dbObj, $cData);

			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);

			$data = $x->load($this->dbObj);

			$this->assertTrue(is_array($data));
			$this->assertTrue(count($data) >= count($cData));
			
			$this->assertFalse(isset($createdWeapons[$id]));
			$createdWeapons[$id] = $data;
			
			if($cData['in_use']) {
				$inUse[$id] = $data;
			}
			else {
				$notInUse[$id] = $data;
			}
		}
		
		$allWeapons = $x->get_all($this->dbObj, $x->characterId);
		$this->assertTrue(is_array($allWeapons));
		$this->assertEquals(count($testData), count($allWeapons), ToolBox::debug_print($allWeapons));
		$this->assertTrue(isset($allWeapons[$id]));
		$this->assertTrue(is_array($allWeapons[$id]));
		$this->assertEquals($data, $allWeapons[$id]);
		
		
		$testUsedWpns = $x->get_all($this->dbObj, $x->characterId, true);
		$testNotUsedWpns = $x->get_all($this->dbObj, $x->characterId, false);
		
		$this->assertNotEquals($testNotUsedWpns, $testUsedWpns);
		
		foreach($testUsedWpns as $id=>$data) {
			$this->assertTrue(isset($createdWeapons[$id]));
			$this->assertEquals($createdWeapons[$id], $data);
		}
		
		foreach($testNotUsedWpns as $id=>$data) {
			$this->assertTrue(isset($createdWeapons[$id]));
			$this->assertEquals($createdWeapons[$id], $data);
		}
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_update_and_delete() {
		$x = new csbt_weapon();
		$x->characterId = $this->char->characterId;
		
		$wpns = array('long sword', 'short sword', 'testing');
		
		$createdList = array();
		
		foreach($wpns as $n) {
			$xData = array(
				'character_id'	=> $x->characterId,
				'weapon_name'	=> $n,
			);
			$id = $x->create($this->dbObj, $xData);
			
			$this->assertTrue(is_numeric($id));
			$this->assertTrue($id > 0);
			$this->assertFalse(isset($createdList[$id]));
			
			$data = $x->load($this->dbObj);
			
			$this->assertTrue(is_array($data));
			$this->assertTrue(count($data) > count($xData));
			
			$createdList[$id] = $data;
		}
		
		$allWpns = $x->get_all($this->dbObj, $x->characterId);
		
		$this->assertTrue(is_array($allWpns));
		$this->assertEquals(count($createdList), count($allWpns));
		
		foreach($createdList as $id=>$data) {
			$x->id = $id;
			
			$newData = $data;
			$newData['weapon_name'] .= __METHOD__;
			
			$this->assertNotEquals($data, $newData);
			
			$this->assertNull($x->mass_update($newData));
			$this->assertEquals(1, $x->save($this->dbObj));
			
			$this->assertEquals($newData, $x->load($this->dbObj));
			
			
			$this->assertEquals($id, $x->id);
			$this->assertEquals(1, $x->delete($this->dbObj));
		}
		
		$this->assertEquals(array(), $x->get_all($this->dbObj, $x->characterId));
	}
	//--------------------------------------------------------------------------
}
