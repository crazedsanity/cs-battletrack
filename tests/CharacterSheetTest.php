<?php

use crazedsanity\database\TestDbAbstract;
use crazedsanity\core\ToolBox;

use battletrack\CharacterSheet;
use battletrack\character\Gear;
use battletrack\character\Weapon;
use battletrack\character\Armor;

class CharacterSheetTest extends TestDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		ToolBox::$debugPrintOpt = 1;
		
		parent::setUp();
		$this->reset_db();
		$this->dbObj->run_sql_file(__DIR__ .'/../vendor/crazedsanity/database/setup/schema.pgsql.sql');
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
		$x = new CharacterSheet($this->dbObj, __METHOD__, 1, false);
		
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
		$x = new CharacterSheet($this->dbObj, __METHOD__, 1);
		
		$this->assertTrue(is_numeric($x->characterId), ToolBox::debug_print($x));
		
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
		
		$g = new Gear();
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
		
		$this->assertEquals($manualWeight, Gear::calculate_list_weight($itemList));
		
		//now, at first, this should be 0 because we haven't re-loaded the sheet.
		$this->assertEquals(0, $x->get_total_weight(false));
		$this->assertEquals(0, $x->get_total_weight(true));
		
		$x->load();
		
		$this->assertEquals($manualWeight, $x->get_total_weight(false));
		$this->assertEquals($manualWeight, $x->get_total_weight(true));
		
		$withWeapons = $manualWeight;
		
		$w = new Weapon();
		$w->characterId = $x->characterId;
		
		$wpns = array(
			'great_sword'	=> 5,
			'long_sword'	=> 2,
			'short_sword'	=> 1
		);
		
		foreach($wpns as $name=>$wgt) {
			$xData = array(
				'character_id'	=> $x->characterId,
				'weapon_name'	=> $name,
				'weight'		=> $wgt,
			);
			$id = $w->create($this->dbObj, $xData);
			$itemList[$id] = $xData;
			$withWeapons += $wgt;
		}
		
		$x->load();
		
		$this->assertNotEquals($manualWeight, $withWeapons);
		$this->assertTrue($withWeapons > $manualWeight);
		
		$this->assertEquals($manualWeight, $x->get_total_weight(false));
		$this->assertEquals($x->get_total_weight(), $x->get_total_weight(false));
		$this->assertEquals($withWeapons, $x->get_total_weight(true));
		
		$withArmor = $withWeapons;
		
		$a = new Armor();
		$rmr = array(
			'big'	=> 10,
			'small'	=> 1,
		);
		foreach($rmr as $name=>$wgt) {
			$xData = array(
				'character_id'	=> $x->characterId,
				'armor_name'	=> $name,
				'weight'		=> $wgt,
			);
			$id = $a->create($this->dbObj, $xData);
			$itemList[$id] = $xData;
			$withArmor += $wgt;
		}
		
		$x->load();
		
		$this->assertNotEquals($manualWeight, $withArmor);
		$this->assertTrue($withArmor > $withWeapons);
		$this->assertEquals($manualWeight, $x->get_total_weight(false));
		$this->assertEquals($x->get_total_weight(), $x->get_total_weight(false));
		$this->assertEquals($withArmor, $x->get_total_weight(true));
	}
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_strength_stats() {
		
		//These are directly out of PHB3.5
		$scoreToMaxLoad = array(
			1	=> 10,
			2	=> 20,
			3	=> 30,
			4	=> 40,
			5	=> 50,
			6	=> 60,
			7	=> 70,
			8	=> 80,
			10	=> 100,
			11	=> 115,
			12	=> 130,
			13	=> 150,
			14	=> 175,
			15	=> 200,
			16	=> 230,
			17	=> 260,
			18	=> 300,
			19	=> 350,
			20	=> 400,
			21	=> 460,
			22	=> 520,
			23	=> 600,
			24	=> 700,
			25	=> 800,
			26	=> 920,
			27	=> 1040,
			28	=> 1200,
			29	=> 1400,
			/*
			 * Tremendous Strength
			For Strength scores not shown on Table (PHB3.5, p162): find the Strength score between 
			20 and 29 that has the same number in the "ones" digit as the creature’s Strength score 
			does and multiply the numbers in that for by 4 for every ten points the creature’s 
			strength is above the score for that row. 
			
			Examples:::
					31, use 21.... max_load(21) == 460.... 460 x 4^1 == 1840
					32, use 22.... max_load(22) == 520.... 520 x 4^1 == 2080
					33, use 23.... max_load(23) == 600.... 600 x 4^1 == 2400
					34, use 24.... max_load(24) == 700.... 700 x 4^1 == 2800
			        35, use 25.... max_load(25) == 800.... 800 x 4^1 == 3200
			        36, use 26.... max_load(26) == 920.... 920 x 4^1 == 3680
			        37, use 27.... max_load(27) == 1040... 1040 x 4^1 = 4160
			        38, use 28.... max_load(28) == 1200... 1200 x 4^1 = 4800
					39, use 29.... max_load(29) == 1400... 1400 x 4^1 = 5600
					40, use 20.... max_load(20) == 400.... 400 x 4^2 == 6400
					41, use 21.... max_load(21) == 460.... 460 x 4^2 == 7360
					42, use 22.... max_load(22) == 520.... 520 x 4^2 == 8320
					52, use 22.... max_load(22) == 520.... 520 x 4^3 == 33280
					62, use 22.... max_load(22) == 520.... 520 x 4^4 == 133120
					72, use 22.... max_load(22) == 520.... 520 x 4^5 == 532580
					82, use 22.... max_load(22) == 520.... 520 x 4^6 == 2.12992e6 (2,129,920)
					92, use 22.... max_load(22) == 520.... 520 x 4^7 == 8.51968e6 (8,519,680)
					100,use 20.... max_load(20) == 400.... 400 x 4^8 == 2.62144e7 (26,214,400)
					102,use 22.... max_load(22) == 520.... 520 x 4^8 == 3.407872e7 (34,078,720)
				
				IMPORTANT NOTE:::
					All the given max load (maximum heavy load) are all calculated by hand, with 
					the help of Prophet (a mad-crazy mathematician genius).  None of this is 
					verified, as it seems there is no official formula/algorithm.  Until an 
					official example can be found, this will have to suffice.
			 */
			30	=> 1600,
			31	=> 1840,
			32	=> 2080,
			33	=> 2400,
			34	=> 2800,
			35	=> 3200,
			36	=> 3680,
			37	=> 4160,
			38	=> 4800,
			39	=> 5600,
			40	=> 6400,
			41	=> 7360,
			42	=> 8320,
			52	=> 33280,
			62	=> 133120,
			72	=> 532480,
			82	=> 2129920,
			92	=> 8519680,
			100	=> 26214400,
			102	=> 34078720,
		);
		
		foreach($scoreToMaxLoad as $score => $maxLoad) {
			$calculatedMaxLoad = CharacterSheet::get_max_load($score);
			$this->assertEquals($maxLoad, $calculatedMaxLoad, "for score ". $score .", max load should be (". $maxLoad ."), but was (". $calculatedMaxLoad .")");
		}
	}
	//--------------------------------------------------------------------------
}