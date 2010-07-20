<?php
/*
 * Created on June 21, 2010
 * 
 * FILE INFORMATION:
 * 
 * $HeadURL: https://cs-webapplibs.svn.sourceforge.net/svnroot/cs-webapplibs/trunk/0.3/tests/testOfCSGenericPermissions.php $
 * $Id: testOfCSGenericPermissions.php 175 2010-06-23 13:45:57Z crazedsanity $
 * $LastChangedDate: 2010-06-23 08:45:57 -0500 (Wed, 23 Jun 2010) $
 * $LastChangedBy: crazedsanity $
 * $LastChangedRevision: 175 $
 */

class testOfCSBattleTrack extends UnitTestCase {
	
	//--------------------------------------------------------------------------
	function setUp() {
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		if(!defined('CS_UNITTEST')) {
			throw new exception(__METHOD__ .": FATAL: constant 'CS_UNITTEST' not set, can't do testing safely");
		}
		$this->dbObj = $this->create_dbconn();
		$this->dbObj->beginTrans();
	}//end setUp()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function tearDown() {
		#$this->remove_tables();
		$this->dbObj->rollbackTrans();
	}//end tearDown()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	private function create_dbconn() {
		$dbParams = array(
			'host'		=> constant('cs_battletrack-DB_PG_HOST'),
			'dbname'	=> constant('cs_battletrack-DB_PG_DBNAME'),
			'user'		=> constant('cs_battletrack-DB_PG_DBUSER'),
			'password'	=> constant('cs_battletrack-DB_PG_DBPASS'),
			'port'		=> constant('cs_battletrack-DB_PG_PORT')
		);
		$db = new cs_phpDB(constant('DBTYPE'));
		$db->connect($dbParams);
		return($db);
	}//end create_dbconn()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	private function dependent_test_checker() {
		$okayToContinue = false;
		if($this->assertTrue(($this->_reporter->_exceptions == 0 && $this->_reporter->_fails == 0), "Failures detected, dependent tests not run")) {
			$okayToContinue=true;
		}
		return($okayToContinue);
	}//end dependent_test_checker()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	private function remove_tables() {
		$tableList = array(
			'csbt_attribute_table', 'csbt_campaign_table', 'csbt_character_armor_table',
			'csbt_character_attribute_table', 'csbt_character_feat_ability_table', 
			'csbt_character_gear_table', 'csbt_character_skill_table', 'csbt_character_table',
			'csbt_character_weapon_table', 'csbt_ability_table'
		);
		
		$db = $this->create_dbconn();
		foreach($tableList as $name) {
			try {
				$db->run_update("DROP TABLE ". $name ." CASCADE", true);
			}
			catch(exception $e) {
				//force an error.
				//$this->assertTrue(false, "Error while dropping (". $name .")::: ". $e->getMessage());
			}
		}
	}//end remove_tables()
	//--------------------------------------------------------------------------
	
	
	
	//--------------------------------------------------------------------------
	public function test_basics() {
		$playerName = __METHOD__;
		$playerUid = 101;
		$dbObj = $this->create_dbconn();
		
		$x = new csbt_tester($dbObj);
		$x->load_schema();
		$char = new csbt_character($dbObj, $playerName, true, $playerUid);
		
		$this->assertTrue(is_numeric($char->characterId));
		
		//sanity test for calculating modifiers.
		{
			for($i=1;$i<=50;$i++) {
				$calculatedModifier = (int)floor(($i - 10)/2);
				
				$modifierFromCall = $char->abilityObj->get_ability_modifier($i);
				$this->assertEqual($modifierFromCall, $calculatedModifier, "Failed to determine modifier for (". $i ."): expected (". $calculatedModifier ."), actual=(". $modifierFromCall .")");
			}
		}
#exit;
		
		//sanity testing for character abilities.
		if($this->dependent_test_checker()){
			$abilityList = $char->abilityObj->get_character_abilities();
			
			foreach($abilityList as $i=>$arr) {
				$this->assertEqual($arr['ability_score'], $char->abilityObj->get_ability_score($arr['ability_name']));
			}
		}
		
		//sanity testing for max load for given strength values.
		if($this->dependent_test_checker()){
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
				$this->assertEqual($derivedMaxLoad, $maxLoad, "Wrong max load for strength of (". $score ."), got (". $derivedMaxLoad .") instead of (". $maxLoad .")");
				
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
				
				if($this->assertEqual($foundIndexes, count($requiredIndexes), "Did not meet required number of fields for extra stats, not running further tests")) {
					//make sure the calculations are correct.
					$lightLoad = (int)floor($derivedMaxLoad/3);
					$medLoad = (int)floor($lightLoad*2);
					
					
					$this->assertEqual($extraStats['generated__load_light'], $lightLoad);
					$this->assertEqual($extraStats['generated__load_medium'], $medLoad);
					$this->assertEqual($extraStats['generated__load_heavy'], $derivedMaxLoad);
					
					//other stats.
					$this->assertEqual($extraStats['generated__lift_over_head'], $derivedMaxLoad);
					$this->assertEqual($extraStats['generated__lift_off_ground'], (int)floor($derivedMaxLoad *2));
					$this->assertEqual($extraStats['generated__push_pull_drag'], (int)floor($derivedMaxLoad *5));
				}
			}
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
				$this->assertEqual($newKey, $key, "Re-assembly of key (". $key .") failed, got (". $newKey .")");
			}
		}
		
		
		//test initially loaded skills for accuracy, and that they exist in the main sheet data as expected.
		if($this->dependent_test_checker()){
			$findThese = array();
			$testListOfSkillsByAbility = array();
			foreach($defaults['skills'] as $i=>$info) {
				$skillName = $info[0];
				$skillAbility = $info[1];
				
				$testData = $char->skillsObj->get_skill_by_name($skillName);
				
				$this->assertTrue(isset($testData['skill_name']));
				$this->assertEqual($testData['skill_name'], $skillName);
				
				$this->assertTrue(isset($testData['ability_name']));
				$this->assertEqual($testData['ability_name'], $skillAbility);
				
				@$testListOfSkillsByAbility[$testData['ability_name']] += 1;
				
				//make sure other indexes are there.
				$this->assertTrue(isset($testData['skill_mod']));
				$this->assertTrue(isset($testData['is_class_skill']));
				$this->assertTrue(isset($testData['ranks']));
				$this->assertTrue(isset($testData['ability_mod']));
				
				//make sure the ability modifier is correct.
				$this->assertEqual($testData['ability_mod'], $char->abilityObj->get_ability_modifier($testData['ability_name']), "Ability modifier on "
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
				$this->assertEqual($testData['skill_mod'], $calculatedSkillMod, "Incorrect skill mod, expecting (". $calculatedSkillMod ."), found (". $testData['skill_mod'] .")");
			}
		}
		
		//Retrieve skills based on their assigned ability, then test updating them.
		if($this->dependent_test_checker()) {
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
			if($this->assertEqual(count($skillsByAbility), 6, "Expecting 6 abilities, found (". count($skillsByAbility) .")")) {
				foreach($skillsByAbility as $abilityName=>$skillsData) {
					$countOfSkillsByAbility += count($skillsData);
					$this->assertTrue(is_array($skillsData));
					$this->assertTrue(count($skillsData));
					if($this->assertEqual($testListOfSkillsByAbility[$abilityName], count($skillsData))) {
						$passed++;
					}
					else {
						$failedItems = $this->gfObj->create_list($failedItems, $abilityName ."(". $skillsData['ability_name'] .")");
					}
					
					foreach($skillsData as $i=>$x) {
						$this->assertEqual($x['ability_name'], $abilityName, "Retrieval by abilityName=(". $abilityName .") failed, got (". $x['ability_name'] .") for (". $x['skill_name'] .")");
					}
				}
				$this->assertEqual($totalSkillCount, $countOfSkillsByAbility, "List of skills by ability (". $countOfSkillsByAbility .") does not match total number of skills (". $totalSkillCount .")");
				$this->assertEqual($passed, count($skillsByAbility), "Passed (". $passed .") abilities, list of failures::: ". $failedItems);
			}
		}
		
		
		//test updates to make sure they work properly.
		if($this->dependent_test_checker()) {
			//check to make sure the key for abilities is available.
			$sheetData = $char->get_sheet_data();
			if($this->assertTrue(isset($sheetData['characterAbility__str_score'])) && $this->assertTrue(isset($sheetData['characterAbility__str_modifier']))) {
				$oldValue = $sheetData['characterAbility__str_score'];
				$oldMod = $sheetData['characterAbility__str_modifier'];
				$newValue = $oldValue +2;
				$this->assertTrue(is_numeric($newValue));
				
				$updateRes = $char->handle_update('characterAbility__str_score', null, $newValue);
				$this->assertTrue($updateRes, "Failed to update strength from (". $oldValue .") to (". $newValue ."), update result was (". $updateRes .")");
				
				//now make sure the update actually happened.
				$sheetData = $char->get_sheet_data();
				
				$newMod = $sheetData['characterAbility__str_modifier'];
				
				$this->assertEqual($sheetData['characterAbility__str_score'], ($oldValue +2));
				$this->assertEqual($sheetData['characterAbility__str_modifier'], ($oldMod +1));
			}
			else {
				$this->gfObj->debug_print($sheetData);
			}
		}
		
		//now, the in-depth testing: ability changes must automatically cause affected skills to get updated.
		if($this->dependent_test_checker()) {
			$oldSkills = $char->skillsObj->get_character_skills();
			$this->assertTrue(count($oldSkills) > 0);
			
			$oldSkillsByAbility = array();
			foreach($oldSkills as $i=>$skillInfo) {
				$oldSkillsByAbility[$skillInfo['ability_name']][] = $skillInfo;
			}
			
			//make sure all the skills are still accounted for, and that the ability modifier is correct.
			$tCount=0;
			foreach($oldSkillsByAbility as $abilityName=>$info) {
				$tCount += count($info);
				
				foreach($info as $i=>$a) {
					$tOldAbilityMod = $a['ability_mod'];
					$tAbilityMod = $char->abilityObj->get_ability_modifier($abilityName);
					$this->assertEqual($tOldAbilityMod, $tAbilityMod, "Ability modifier for '". $a['skill_name'] ."' (". $tOldAbilityMod .") does not match true modifier (". $tAbilityMod ."), total score is (". $char->abilityObj->get_ability_score($abilityName) .")");
				}
			}
			$this->assertEqual($tCount, count($oldSkills));
			
//			//now do the in-depth checking.
//			if($this->dependent_test_checker()) {
//				foreach($char->skillsObj->abilityObj->get_ability_list() as $abilityName=>$id) {
//					
//				}
//			}
		}
	}//end test_basics()
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
