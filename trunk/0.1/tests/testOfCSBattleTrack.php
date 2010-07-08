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
		
		//give the character some stats.
		
		$defaults = $char->get_character_defaults();
		$this->assertTrue(is_array($defaults));
		$this->assertTrue(count($defaults) > 0);
		$this->assertTrue(isset($defaults['skills']));
		$this->assertTrue(count($defaults['skills'])>0);
		
		$sheetData = $char->get_sheet_data();
		
		
		$findThese = array();
		//test finding of skills...
		{
			foreach($defaults['skills'] as $i=>$info) {
				$skillName = $info[0];
				$skillAbility = $info[1];
				
				$testData = $char->skillsObj->get_skill_by_name($skillName);
				
				$this->assertTrue(isset($testData['skill_name']));
				$this->assertEqual($testData['skill_name'], $skillName);
				
				$this->assertTrue(isset($testData['ability_name']));
				$this->assertEqual($testData['ability_name'], $skillAbility);
				
				//make sure other indexes are there.
				$this->assertTrue(isset($testData['skill_mod']));
				$this->assertTrue(isset($testData['is_class_skill']));
				$this->assertTrue(isset($testData['ranks']));
				$this->assertTrue(isset($testData['ability_mod']));
				
				
				$findKey = $char->create_sheet_id(csbt_skill::sheetIdPrefix, $skillName, $testData['ability_id']);
				
				#$this->gfObj->debug_print($testData);
				#exit;
			}
		}
		
		//test creation/updating of armor.
		{
			
		}
		
		$this->assertTrue(is_array($sheetData));
		$this->assertTrue(count($sheetData) > 0);
	}//end test_basics()
	//--------------------------------------------------------------------------
	
}

class csbt_tester extends csbt_battleTrackAbstract {
	//(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr)
	public function __construct($dbObj) {
		try {
			parent::__construct($dbObj, __METHOD__, __METHOD__, __METHOD__, array(__METHOD__ => __METHOD__));
		}
		catch(Exception $e) {
			$gf = new cs_globalFunctions();
			$gf->debug_print($dbObj,1);
		}
	}
	public function get_sheet_data() {
		return(parent::get_sheet_data());
	}
	public function get_character_defaults(){
		return(parent::get_character_defaults());
	}
}
?>
