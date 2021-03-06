<?php

class testOfCSBattleTrack extends testDbAbstract {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		$this->reset_db(dirname(__FILE__) .'/../vendor/crazedsanity/cs-webapplibs/setup/schema.pgsql.sql');
		parent::setUp();
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		parent::tearDown();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_everything() {
		//TODO: put this into a big loop: create several characters, and add checks to ensure the records are attached to the correct character_id.
		$numCharacters = 0;
		$maxCharacters = 3;
		$dbObj = $this->dbObj;
		
		$x = new csbt_tester($dbObj);
		$x->load_schema();
		for($x=0; $x< $maxCharacters; $x++) {
			$characterSuffix = " Test #". $numCharacters;
			$playerName = __METHOD__;
			$playerUid = 1;
			$char = new csbt_character($dbObj, $playerName, true, $playerUid);
			
			$this->assertTrue(is_numeric($char->characterId));
			
			//sanity test for calculating modifiers.
			{
				for($i=1;$i<=50;$i++) {
					$calculatedModifier = (int)floor(($i - 10)/2);
					
					$modifierFromCall = $char->abilityObj->get_ability_modifier($i);
					$this->assertEquals($modifierFromCall, $calculatedModifier, "Failed to determine modifier for (". $i ."): expected (". $calculatedModifier ."), actual=(". $modifierFromCall .")");
				}
			}
			
			if($this->dependent_test_checker()) {
				$this->_check_ability_scores($char);
			}
			
			//sanity testing for max load for given strength values.
			if($this->dependent_test_checker()){
				$this->_check_strength_stats($char);
			}
			
			//pull list of defaults, with minor sanity checking to make sure it seems valid (before going into more in-depth stuff later).
			if($this->dependent_test_checker()){
				$defaults = $char->get_character_defaults();
				$this->assertTrue(is_array($defaults));
				$this->assertTrue(count($defaults) > 0);
				$this->assertTrue(isset($defaults['skills']));
				$this->assertTrue(count($defaults['skills'])>0);
			}
			
			//pull all sheet data, then do some minor sanity checking to ensure it seems valid.
			if($this->dependent_test_checker()){
				$this->_check_reassemble_keys($char);
			}
			
			
			//test updating main character data.
			if($this->dependent_test_checker()) {
				$this->_check_main_character_updates($char);
			}
			
			//test saves.
			if($this->dependent_test_checker()) {
				$this->_check_saves($char);
			}
			
			//test initially loaded skills for accuracy, and that they exist in the main sheet data as expected.
			if($this->dependent_test_checker()){
				$this->_check_skills($char);
			}
			
			//test updates to make sure they work properly.
			if($this->dependent_test_checker()) {
				$this->_check_ability_updates($char);
			}
			
			
			//test gear.
			if($this->dependent_test_checker()){
				$this->_check_gear($char);
			}
			
			
			
			//now test handling of armor.
			if($this->dependent_test_checker()) {
				$this->_check_armor($char);
			}
			
			
			
			//test handling of weapons.
			{
				$this->_check_weapons($char);
			}
			
			
			
			//test special abilities
			if($this->dependent_test_checker()) {
				$this->_check_special_abilities($char);
			}
			
			$numCharacters++;
		}
		
		$this->gfObj->debug_print($char->get_sheet_data());
		
	}//end test_everything()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function _check_ability_scores($char) {
		//sanity testing for character abilities.
		if($this->dependent_test_checker()){
			$abilityList = $char->abilityObj->get_character_abilities();
			
			foreach($abilityList as $i=>$arr) {
				$this->assertEquals($arr['ability_score'], $char->abilityObj->get_ability_score($arr['ability_name']));
				$this->assertEquals($arr['character_id'], $char->characterId, "Character ID mismatch, expected (". $char->characterId ."), got (". $arr['character_id'] .")");
			}
		}
	}//_check_ability_scores()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function _check_strength_stats($char) {
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
			$derivedMaxLoad = $char->abilityObj->get_max_load($score);
			$this->assertEquals($derivedMaxLoad, $maxLoad, "Wrong max load for strength of (". $score ."), got (". $derivedMaxLoad .") instead of (". $maxLoad .")");
			
			//now get **ALL** the extra strength stats & test them.
			$extraStats = $char->abilityObj->get_strength_stats($score);
			
			$requiredIndexes = array(
				'generated__load_light', 'generated__load_medium','generated__load_heavy', 'generated__lift_over_head',
				'generated__lift_off_ground', 'generated__push_pull_drag'
			);
			
			$foundIndexes = 0;
			foreach($requiredIndexes as $index) {
				$this->assertTrue(isset($extraStats[$index]), "Missing required field (". $index .")");
				$foundIndexes++;
			}
			
			if($this->assertEquals($foundIndexes, count($requiredIndexes), "Did not meet required number of fields for extra stats, not running further tests")) {
				//make sure the calculations are correct.
				$lightLoad = (int)floor($derivedMaxLoad/3);
				$medLoad = (int)floor($lightLoad*2);
				
				
				$this->assertEquals($extraStats['generated__load_light'], $lightLoad);
				$this->assertEquals($extraStats['generated__load_medium'], $medLoad);
				$this->assertEquals($extraStats['generated__load_heavy'], $derivedMaxLoad);
				
				//other stats.
				$this->assertEquals($extraStats['generated__lift_over_head'], $derivedMaxLoad);
				$this->assertEquals($extraStats['generated__lift_off_ground'], (int)floor($derivedMaxLoad *2));
				$this->assertEquals($extraStats['generated__push_pull_drag'], (int)floor($derivedMaxLoad *5));
			}
		}
	}//end _check_strength_stats()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function _check_reassemble_keys($char) {
		$sheetData = $char->get_sheet_data();
		
		$this->assertTrue(is_array($sheetData));
		$this->assertTrue(count($sheetData) > 0);
		
		
		//test that each of the id's can be split apart.
		foreach($sheetData as $key=>$val) {
			$bits = $char->parse_sheet_id($key);
			$this->assertTrue(count($bits) >= 2);
			$this->assertTrue(count($bits) <= 3);
			
			//make sure the key can be re-assembled.
			$bit3 = null;
			if(count($bits) == 3) {
				$bit3 = $bits[2];
			}
			
			$newKey = $char->create_sheet_id($bits[0], $bits[1], $bit3);
			$this->assertEquals($newKey, $key, "Re-assembly of key (". $key .") failed, got (". $newKey .")");
		}
	}//end _check_reassemble_keys()
	//--------------------------------------------------------------------------
	
	
	//--------------------------------------------------------------------------
	public function _check_main_character_updates($char) {
		$sheetData = $char->get_sheet_data();
		$this->assertEquals($char->characterId, $sheetData['main__character_id']);
		$testSheetData = $sheetData;
		$listOfChanges = array(
			'hair_color'			=> "purple",
			'ac_misc'				=> 88,
			'hit_points_max'		=> 222,
			'race'					=> "E.V.I.L.",
			'ac_size'				=> 1,
			'ac_natural'			=> 2,
			'action_points'			=> 33,
			'character_age'			=> 666,
			'alignment'				=> "Chaotic Nick",
			'base_attack_bonus'		=> 15,
			'deity'					=> "yermom",
			'eye_color'				=> "purple",
			'gender'				=> "Shemale",
			'height'				=> "6'6\"",
			'hit_points_current'	=> 111,
			'size'					=> "Colossal",
			'weight'				=> 256,
			'initiative_misc'		=> 17,
			'nonlethal_damage'		=> 12,
			'hit_dice'				=> "d100",
			'damage_reduction'		=> "+5/yermom",
			'speed'					=> 400
		);
		#$updateRes = $char->handle_update('main__hair_color', null, 'purple');
		#$this->assertTrue($updateRes);
		foreach($listOfChanges as $column=>$newValue) {
			$this->assertNotEqual($newValue, $sheetData['main__'. $column], "Values for (main__". $column .") unexpectedly equal (". $newValue .")");
			if($this->dependent_test_checker()) {
				$updateRes = $char->handle_update('main__'. $column, null, $newValue);
				$this->assertTrue($updateRes);
				$sheetData = $char->get_sheet_data();
				$this->assertEquals($newValue, $sheetData['main__'. $column]);
			}
		}
	}//end _check_main_character_updates()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function _check_skills($char) {
		$defaults = $char->get_character_defaults();
		$sheetData = $char->get_sheet_data();
		$findThese = array();
		$testListOfSkillsByAbility = array();
		foreach($defaults['skills'] as $i=>$info) {
			$skillName = $info[0];
			$skillAbility = $info[1];
			
			$testData = $char->skillsObj->get_skill_by_name($skillName);
			
			$this->assertTrue(isset($testData['skill_name']));
			$this->assertEquals($testData['skill_name'], $skillName);
			
			$this->assertEquals($char->characterId, $testData['character_id']);
			
			$this->assertTrue(isset($testData['ability_name']));
			$this->assertEquals($testData['ability_name'], $skillAbility);
			
			@$testListOfSkillsByAbility[$testData['ability_name']] += 1;
			
			//make sure other indexes are there.
			$this->assertTrue(isset($testData['skill_mod']));
			$this->assertTrue(isset($testData['is_class_skill']));
			$this->assertTrue(isset($testData['ranks']));
			$this->assertTrue(isset($testData['ability_mod']));
			
			//make sure the ability modifier is correct.
			$this->assertEquals($testData['ability_mod'], $char->abilityObj->get_ability_modifier($testData['ability_name']), "Ability modifier on "
					."skill '". $testData['skill_name'] ."' (". $testData['ability_mod'] .") does not match actual ability modifier "
					."(". $char->abilityObj->get_ability_modifier($testData['ability_name']) .") for ability (". $testData['ability_name'] ." "
					.", abilityScore=". $char->abilityObj->get_ability_score($testData['ability_name']) .")");
			
			//skills__climb__3
			/*
			 * EXAMPLE OF KEYS FOR 'Appraise', skill id #1:::
			 * Array
				(
				    [skills__skill_name__1]		=> Appraise
				    [skills__ability_name__1]	=> int
				    [skills__is_class_skill__1]	=> f
				    [skills__skill_mod__1]		=> 0
				    [skills__ability_mod__1]	=> 0
				    [skills__ranks__1]			=> 0
				    [skills__misc_mod__1]		=> 0
				)
			 */
			
			$findKeyBits = array('skill_name', 'ability_name', 'is_class_skill', 'skill_mod', 'ranks', 'misc_mod');
			foreach($findKeyBits as $bit) {
				$findKey = $char->create_sheet_id(csbt_skill::sheetIdPrefix, $bit, $testData['character_skill_id']);
				$this->assertTrue(isset($sheetData[$findKey]), "Missing expected key (". $findKey .")");
			}
			
			//verify that the 'skill_mod' is correct.
			$calculatedSkillMod = $testData['ranks'] + $testData['ability_mod'] + $testData['misc_mod'];
			$this->assertEquals($testData['skill_mod'], $calculatedSkillMod, "Incorrect skill mod, expecting (". $calculatedSkillMod ."), found (". $testData['skill_mod'] .")");
		}
		
		$totalSkillCount = count($defaults['skills']);
		
		$skillsByAbility = array(
			'str'	=> $char->skillsObj->get_character_skills('str'),
			'con'	=> $char->skillsObj->get_character_skills('con'),
			'dex'	=> $char->skillsObj->get_character_skills('dex'),
			'int'	=> $char->skillsObj->get_character_skills('int'),
			'wis'	=> $char->skillsObj->get_character_skills('wis'),
			'cha'	=> $char->skillsObj->get_character_skills('cha')
		);
		
		//make sure the total of these adds-up to the total number of skills.
		$countOfSkillsByAbility = 0;
		$passed=0;
		$failedItems = "";
		if($this->assertEquals(count($skillsByAbility), 6, "Expecting 6 abilities, found (". count($skillsByAbility) .")")) {
			foreach($skillsByAbility as $abilityName=>$skillsData) {
				$countOfSkillsByAbility += count($skillsData);
				$this->assertTrue(is_array($skillsData));
				$this->assertTrue(count($skillsData));
				if($this->assertEquals($testListOfSkillsByAbility[$abilityName], count($skillsData))) {
					$passed++;
				}
				else {
					$failedItems = $this->gfObj->create_list($failedItems, $abilityName ."(". $skillsData['ability_name'] .")");
				}
				
				foreach($skillsData as $i=>$x) {
					$this->assertEquals($x['ability_name'], $abilityName, "Retrieval by abilityName=(". $abilityName .") failed, got (". $x['ability_name'] .") for (". $x['skill_name'] .")");
				}
			}
			$this->assertEquals($totalSkillCount, $countOfSkillsByAbility, "List of skills by ability (". $countOfSkillsByAbility .") does not match total number of skills (". $totalSkillCount .")");
			$this->assertEquals($passed, count($skillsByAbility), "Passed (". $passed .") abilities, list of failures::: ". $failedItems);
		}
	}//end _check_skills()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	protected function _check_ability_updates($char) {
		$sheetData = $char->get_sheet_data();
		
		$abilityList = $char->skillsObj->abilityObj->get_ability_list();
		$abilities = $abilityList['byId'];
		
		$this->assertEquals(count($abilities), 6);
		$this->assertTrue(is_array($abilities));
		
		if($this->dependent_test_checker()) {
			foreach($abilities as $id=>$abilityName) {
				$sheetIdForScore = 'characterAbility__'. $abilityName .'_score';
				$sheetIdForModifier = 'characterAbility__'. $abilityName .'_modifier';
				
				if($this->assertTrue(isset($sheetIdForScore)) && $this->assertTrue(isset($sheetIdForModifier))) {
					$oldValue = $sheetData[$sheetIdForScore];
					$oldMod = $sheetData[$sheetIdForModifier];
					$newValue = $oldValue +2;
					$newMod = $oldMod +1;
					
					$this->assertTrue(is_numeric($newValue));
					
					//perform the update.
					$updateRes = $char->handle_update($sheetIdForScore, null, $newValue);
					$this->assertTrue($updateRes, "Failed to update '". $abilityName ."' from (". $oldValue .") to (". $newValue ."), update result was (". $updateRes .")");
					
					$sheetData = $char->get_sheet_data();
					$newMod = $sheetData[$sheetIdForModifier];
					
					$this->assertNotEqual($oldValue, $newValue);
					$this->assertEquals($sheetData[$sheetIdForScore], ($oldValue +2));
					$this->assertNotEqual($oldMod, $newMod);
					$this->assertEquals($sheetData[$sheetIdForModifier], ($oldMod +1));
					
					//now check to ensure that the ability modifiers for skills have been updated.
					$skillsByAbility = $char->skillsObj->get_character_skills($abilityName);
					$this->assertTrue(is_array($skillsByAbility));
					$this->assertTrue(count($skillsByAbility) > 0);
					
					foreach($skillsByAbility as $id=>$skillInfo) {
						$this->assertEquals($skillInfo['ability_name'], $abilityName);
						$this->assertEquals($skillInfo['ability_mod'], $newMod);
						$this->assertEquals($char->characterId, $skillInfo['character_id']);
					}
				}
				else {
					$this->gfObj->debug_print($sheetData);
				}
			}
		}
	}//end _check_ability_updates()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	protected function _check_gear($char) {
		$listOfGear = array(
			"Bag of Holding (type III)" => array(
				'weight'	=> 15,
				'location'	=> "on back",
				'quantity'	=> 1
			),
			"Torches" => array(
				'weight'	=> 1,
				'location'	=> "BoH",
				'quantity'	=> 3
			)
		);
		
		$expectedTotalWeight = 0;
		$idToName = array();
		foreach($listOfGear as $name=>$gearInfo) {
			$createRes = $char->gearObj->create_gear($name, $gearInfo);
			$idToName[$createRes] = $name;
			$this->assertTrue(is_numeric($createRes));
			
			$gearInfo = $char->gearObj->get_gear_by_id($createRes);
			$this->assertTrue(is_array($gearInfo));
			$this->assertTrue(count($gearInfo) > 0);
			$this->assertEquals($char->characterId, $gearInfo['character_id']);
			
			//
			$expectedRecordWeight = round(($gearInfo['quantity'] * $gearInfo['weight']),1);
			$this->assertEquals($gearInfo['total_weight'], $expectedRecordWeight);
			
			$expectedTotalWeight += $expectedRecordWeight;
		}
		
		$sheetData = $char->get_sheet_data();
		
		//make sure the total weight carried makes sense.
		$this->assertTrue(isset($sheetData['gear__total_weight__generated']));
		$this->assertEquals($sheetData['gear__total_weight__generated'], $expectedTotalWeight);
		
		//do a couple of updates to make sure it works.
		$addWeight = 5;
		$addQuantity = 3;
		$updatedExpectedTotalWeight = 0;
		foreach($idToName as $id=>$name) {
			$this->assertTrue($char->handle_update('gear__weight__'. $id, null, ($listOfGear[$name]['weight'] + $addWeight)));
			$this->assertTrue($char->handle_update('gear__quantity__'. $id, null, ($listOfGear[$name]['quantity'] + $addQuantity)));
			
			$expectedItemWeight = (($listOfGear[$name]['weight'] + $addWeight) * ($listOfGear[$name]['quantity'] + $addQuantity));
			$updatedExpectedTotalWeight += $expectedItemWeight;
			$itemData = $char->gearObj->get_gear_by_id($id);
			$this->assertEquals($expectedItemWeight, $itemData['total_weight']);
		}
		
		$sheetData = $char->get_sheet_data();
		$this->assertEquals($updatedExpectedTotalWeight, $sheetData['gear__total_weight__generated']);
	}//end check_gear()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	protected function _check_armor($char) {
		$listOfArmor = array(
			"Chainmail" => array(
				'armor_type'	=> "medium",
				'ac_bonus'		=> 1,
				'max_dex'		=> 2,
				'check_penalty'	=> 2,
				'weight'		=> 10,
				'max_speed'		=> 30
			),
			"Buckler" => array(
			'armor_type'	=> "medium",
				'ac_bonus'		=> 3,
				'max_dex'		=> 4,
				'weight'		=> 5,
				'spell_fail'	=> 10,
				'max_speed'		=> 40
			),
			"Ring of Protection +1"	=> array(
				'armor_type'	=> "medium",
				'ac_bonus'		=> 5,
				'max_dex'		=> 6,
				'spell_fail'	=> 0,
				'special'		=> "+1 AC bonus, cursed, flooglesparks"
			)
		);
		
		$allArmorRecords = $char->armorObj->get_character_armor();
		$this->assertTrue(count($allArmorRecords) == 0);
		
		$piecesCreated = 0;
		foreach($listOfArmor as $name=>$armorDetails) {
			$createResult = $char->armorObj->create_armor($name, $armorDetails);
			$this->assertTrue(is_numeric($createResult));
			$this->assertTrue($createResult > 0);
			
			$piecesCreated++;
			
			//make sure the proper number of pieces have been created so far.
			$allArmorRecords = $char->armorObj->get_character_armor();
			$this->assertEquals(count($allArmorRecords), $piecesCreated);
			
			//make sure the character_id is correct.
			foreach($allArmorRecords as $k=>$v) {
				$this->assertEquals($char->characterId, $v['character_id']);
			}
		}
		
		$allArmorRecords = $char->armorObj->get_character_armor();
		$this->assertEquals(count($listOfArmor), $piecesCreated);
		$this->assertEquals(count($allArmorRecords), count($listOfArmor));
		
		//make sure each record has the right details.
		$nameToKey = array();
		foreach($allArmorRecords as $i=>$armorDetails) {
			$this->assertEquals($i, $armorDetails['character_armor_id']);
			$nameToKey[$armorDetails['armor_name']] = $i;
		}
		
		//make sure the list of names to ID's is sane.
		$this->assertEquals(count($nameToKey), count($listOfArmor));
		
		foreach($nameToKey as $name=>$key) {
			$this->assertTrue(isset($allArmorRecords[$key]));
			$this->assertTrue(isset($listOfArmor[$name]));
			
			$createSpecs = $listOfArmor[$name];
			$recordData = $allArmorRecords[$key];
			
			//retrieve the individual record for testing!
			$singleRecordData = $char->armorObj->get_armor_by_id($key);
			$this->assertTrue(is_array($singleRecordData));
			$this->assertEquals($singleRecordData['character_armor_id'], $key);
			
			foreach($createSpecs as $columnName=>$expectedValue) {
				$this->assertEquals($recordData[$columnName], $expectedValue);
				$this->assertEquals($singleRecordData[$columnName], $expectedValue);
			}
		}
		
		//now, let's update some data & see what happens.
		foreach($listOfArmor as $name=>$armorDetails) {
			$armorDetails['ac_bonus']  += 1;
			$armorDetails['max_dex'] += 1;
			$updateThisId = $nameToKey[$name];
			
			$updateRes = $char->armorObj->update_armor($updateThisId, $armorDetails);
			
			//pull the single record & check that it matches.
			$recordData = $char->armorObj->get_armor_by_id($updateThisId);
			foreach($armorDetails as $columnName=>$expectedValue) {
				$this->assertEquals($recordData[$columnName], $expectedValue, "Failed to update recordId=(". $updateThisId ."), name=(". $name .")... column=(". $columnName ."), expectedValue=(". $expectedValue ."), actual=(". $recordData[$columnName] .")");
			}
			
			$this->assertNotEqual($armorDetails['ac_bonus'], $listOfArmor[$name]['ac_bonus']);
			$this->assertNotEqual($recordData['ac_bonus'], $listOfArmor[$name]['ac_bonus']);
			
			$this->assertNotEqual($armorDetails['max_dex'], $listOfArmor[$name]['max_dex']);
			$this->assertNotEqual($recordData['max_dex'], $listOfArmor[$name]['max_dex']);
			
			$listOfArmor[$name] = $armorDetails;
		}
		
		//TODO: make a few calls to "handle_update()" to make sure that works too, and that the records appear in the main sheet data.
		foreach($listOfArmor as $name=>$armorDetails) {
			$armorDetails['ac_bonus']  += 1;
			$armorDetails['max_dex'] += 1;
			$updateThisId = $nameToKey[$name];
			
			$this->assertTrue($char->handle_update('characterArmor__ac_bonus__'. $updateThisId, null, $armorDetails['ac_bonus']));
			$this->assertTrue($char->handle_update('characterArmor__max_dex__'. $updateThisId, null, $armorDetails['max_dex']));
			
			//pull the single record & check that it matches.
			$recordData = $char->armorObj->get_armor_by_id($updateThisId);
			foreach($armorDetails as $columnName=>$expectedValue) {
				$this->assertEquals($recordData[$columnName], $expectedValue, "Failed to update recordId=(". $updateThisId ."), name=(". $name .")... column=(". $columnName ."), expectedValue=(". $expectedValue ."), actual=(". $recordData[$columnName] .")");
			}
			
			$this->assertNotEqual($armorDetails['ac_bonus'], $listOfArmor[$name]['ac_bonus']);
			$this->assertNotEqual($recordData['ac_bonus'], $listOfArmor[$name]['ac_bonus']);
			
			$this->assertNotEqual($armorDetails['max_dex'], $listOfArmor[$name]['max_dex']);
			$this->assertNotEqual($recordData['max_dex'], $listOfArmor[$name]['max_dex']);
			
			
			$listOfArmor[$name] = $armorDetails;
		}
	}//end _check_armor()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	protected function _check_weapons($char) {
		$listOfWeapons = array(
			"Dagger" => array(
				'damage'				=> "1d4 +1",
				'total_attack_bonus'	=> "+5",
				'critical'				=> "20x2",
				'size'					=> "small",
				'weapon_type'			=> "piercing",
			),
			"Sword of Unit Testing +5" => array(
				'damage'				=> "10d10 + 5",
				'total_attack_bonus'	=> "+50",
				'critical'				=> "15-20x5",
				'size'					=> "38 bytes",
				'weapon_type'			=> "fictional"
			)
		);
		
		$useForUpdates = array();
		foreach($listOfWeapons as $name=>$weaponInfo) {
			$createRes = $char->weaponObj->create_weapon($name, $weaponInfo);
			$this->assertTrue(is_numeric($createRes));
			
			$useForUpdates[] = array(
				'recId'	=> $createRes,
				'name'	=> $name,
				'info'	=> $weaponInfo
			);
			
			$recordData = $char->weaponObj->get_weapon_by_id($createRes);
			foreach($weaponInfo as $f=>$v) {
				$this->assertEquals($recordData[$f], $v); //? WTF?
			}
			$this->assertEquals($char->characterId, $recordData['character_id'], "Character ID mismatch, expected (". $char->characterId ."), got (". $recordData['character_id'] .")");
		}
		
		$createdWeapons = $char->weaponObj->get_character_weapons();
		$this->assertEquals(count($listOfWeapons), count($createdWeapons));
		
		$sheetData = $char->get_sheet_data();
		
		//now do updates in a couple of different ways.
		$this->assertTrue(isset($sheetData['characterWeapon__damage__'. $useForUpdates[0]['recId']]));
		$updateRes = $char->handle_update('characterWeapon__damage__'. $useForUpdates[0]['recId'], null, "30d30, SUCK");
		$this->assertTrue(is_numeric($updateRes));
		
		$updatedSheetData = $char->get_sheet_data();
		$this->assertEquals($updatedSheetData['characterWeapon__damage__'. $useForUpdates[0]['recId']], "30d30, SUCK");
	}//end _check_weapons()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	protected function _check_special_abilities($char) {
		$listOfSpecialAbilities = array(
			"Blind Fighting" => array(
				'description'		=> "Yah, sure, you betcha",
				'book_reference'	=> "PHB145"
			),
			"Two-Weapon Fighting" => array(
				'description'		=> "Fighting with two weapons...",
				'book_reference'	=> "PHB192"
			)
		);
		
		foreach($listOfSpecialAbilities as $name=>$specialAbilityInfo) {
			$createRes = $char->specialAbilityObj->create_special_ability($name, $specialAbilityInfo);
			$this->assertTrue($createRes);
			
			//retrieve the record.
			$recordById = $char->specialAbilityObj->get_special_ability_by_id($createRes);
			$recordByName = $char->specialAbilityObj->get_special_ability_by_name($name);
			
			$this->assertTrue(is_array($recordById));
			$this->assertTrue(is_array($recordByName));
			
			$this->assertEquals($char->characterId, $recordById['character_id']);
			$this->assertEquals($char->characterId, $recordByName['character_id']);
		}
	}//end _check_special_abilities()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	private function _check_saves($char) {
		$savesData = $char->savesObj->get_character_saves();
		
		$this->assertEquals(count($savesData), 3);
		
	}//end _check_saves()
	//--------------------------------------------------------------------------
}

class csbt_tester extends csbt_battleTrackAbstract {
	//(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr)
	public function __construct($dbObj) {
		$this->dbObj = $dbObj;
	}
	public function get_sheet_data() {
		return(parent::get_sheet_data());
	}
	public function get_character_defaults(){
		return(parent::get_character_defaults());
	}
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		return(parent::handle_update($bits, $recordId, $newValue));
	}
}
?>
