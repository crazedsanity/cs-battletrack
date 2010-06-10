<?php

class upgradeTo_0_2_0 extends cs_webdblogger {
	
	private $logsObj;
	
	//=========================================================================
	public function __construct(cs_phpDB &$db) {
		if(!$db->is_connected()) {
			throw new exception(__METHOD__ .": database is not connected");
		}
		$this->db = $db;
		
		$newDb = new cs_phpDB();
		$newDb->connect($this->db->connectParams,true);
		$this->logsObj = new cs_webdblogger($newDb, 'Upgrade', false);
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt = 1;
	}//end __construct()
	//=========================================================================
	
	
	
	//=========================================================================
	public function run_upgrade() {
		
		$result = $this->do_schema_change();
		
		return($result);
	}//end run_upgrade()
	//=========================================================================
	
	
	
	//=========================================================================
	private function do_schema_change() {
		
		$isValid = null;
		try {
			$this->run_upgrade_sql_file('upgrade_to_0-2-0.sql');
			$this->fix_numbered_keys();
			$this->do_key_changes();
			
			$isValid = true;
		}
		catch(Exception $e) {
			$this->logsObj->log_by_class(__METHOD__ .":: failure during upgrade::: ". $e->getMessage(), 'Exception in code');
			$isValid=$e->getMessage();
		}
			
		return($isValid);
	}//end do_schema_change()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Execute the entire contents of the given file (with absolute path) as SQL.
	 * 
	 * Pulled from cs_webdblogger.class.php:::
	 * SVN URL::: https://cs-webapplibs.svn.sourceforge.net/svnroot/cs-webapplibs/trunk/0.3/cs_webdblogger.class.php
	 * ID:::::::: cs_webdblogger.class.php 156 2009-11-09 17:25:37Z crazedsanity
	 */
	final public function run_upgrade_sql_file($filename) {
		$fsObj = new cs_fileSystem(dirname(__FILE__) .'/../docs/sql');
		
		$this->lastSQLFile = $filename;
		
		$fileContents = $fsObj->read($filename);
		try {
			$this->db->run_update($fileContents, true);
			$retval = TRUE;
		}
		catch(exception $e) {
			$retval = FALSE;
		}
		
		return($retval);
	}//end run_upgrade_sql_file()
	//=========================================================================
	
	
	
	//=========================================================================
	private function fix_numbered_keys() {
		$keyStartsWith = array(
			'armorSlot-', 'featsAbilities-', 'gear-', 'skills-', 'weaponSlot-'
		);
		
		try{
			
			//TODO: this doesn't actually replace anything... LOG STUFF!!!!  MORE ERROR CHECKING!!!
			foreach($keyStartsWith as $start) {
				//Pull the appropriate keys...
				$sql = "SELECT * FROM csbt_attribute_table WHERE attribute LIKE "
					."'". $start ."%' ORDER BY attribute";
				$data = $this->db->run_query($sql, 'attribute_id', 'attribute');
				
				//get the new key name, and list of affected id's...
				$newKey = null;
				$attributeToKeys = array();
				$idList = "";
				foreach($data as $id=>$keyName) {
					$newKey = preg_replace('/-[0-9]{1,}/', '', $keyName);
					$newKey = preg_replace('/-{2,}/', '-', $newKey);
					$newKey = preg_replace('/-$/', '', $newKey);
					
					$attributeToKeys[$newKey] = $this->gfObj->create_list($attributeToKeys[$newKey], $id, ",", false);
				}
				$this->logsObj->log_by_class(__METHOD__ .":: handling ". count($data) ." records, using new key (". $newKey .")", 'Upgrade');
				
				foreach($attributeToKeys as $newKey => $idList) {
					//Now... create the new attribute...
					$sql = "INSERT INTO csbt_attribute_table (attribute) VALUES ('". $newKey ."')";
					$newId = $this->db->run_insert($sql, 'csbt_attribute_table_attribute_id_seq');
					$this->logsObj->log_by_class(__METHOD__ .":: created new attribute (". $newKey ."), id=(". $newId .")", 'Upgrade');
					
					//Replace all the old keys with the new one...
					$sql = "UPDATE csbt_character_attribute_table SET attribute_id=". $newId ." WHERE "
						."attribute_id IN (". $idList .")";
					$numUpdated = $this->db->run_update($sql);
					$this->logsObj->log_by_class(__METHOD__ .":: updated (". $numUpdated .") records", 'Upgrade');
					
					//Finally, delete the old keys that are no longer referenced.
					$sql = "DELETE FROM csbt_attribute_table WHERE attribute_id IN (". $idList .")";
					$numDeleted = $this->db->run_update($sql);
					$this->logsObj->log_by_class(__METHOD__ .":: removed (". $numDeleted .") keys", 'Deleted');
				}
			}
		}
		catch(Exception $e) {
			$this->logsObj->log_by_class(__METHOD__ .":: ERROR::: ". $e->getMessage(), 'Exception in Code');
		}
	}//end fix_numbered_keys()
	//=========================================================================
	
	
	
	//=========================================================================
	private function do_key_changes() {
		$oldToNew = array(
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
			""			=> "",
		);
	}//end do_key_changes()
	//=========================================================================
}

?>
