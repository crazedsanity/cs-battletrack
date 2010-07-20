<?php

class upgradeTo_0_5_0 extends cs_webdblogger {
	
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
			"ac-size_modifier"				=> "ac-mod-size",
			"current_class-hit_dice"		=> "class-hit_dice",
			"current-class-hit-dice"		=> "class-hit_dice",
			"class_total-level"				=> "class-total-level	",
			"featsAbilities"				=> "featsAbilities-name",
			"generic-class"					=> "class-name",
			"generic-action_points"			=> "mainCharacter-actionPoints",
			"generic-age"					=> "mainCharacter-age",
			"generic-alignment"				=> "mainCharacter-alignment",
			"generic-bab"					=> "mainCharacter-bab",
			"generic-base_attack_bonus"		=> "mainCharacter-bab",
			"generic-base-attack-bonus"		=> "mainCharacter-bab",
			"generic-campaign"				=> "generated-campaign",
			"generic-deity"					=> "mainCharacter-deity",
			"generic-eyes"					=> "mainCharacter-eyes",
			"generic-gender"				=> "mainCharacter-gender",
			"generic-hair"					=> "mainCharacter-hair",
			"generic-height"				=> "mainCharacter-height",
			"generic-hp"					=> "hp-total",
			"status-current_hp"				=> "hp-current",
			"status-current-hp"				=> "hp-current",
			"generic-race"					=> "mainCharacter-race",
			"generic-size"					=> "mainCharacter-size",
			"generic-skills-maxcc"			=> "skills-max-cc",
			"generic-skills-maxrank"		=> "skills-max-rank",
			"generic-weight"				=> "mainCharacter-weight",
			"init-misc"						=> "initiative-misc",
			"init-total"					=> "initiative-total",
			"melee-miscmod"					=> "melee-mods-misc",
			"MELEE--abilities-str-mod"		=> "melee-mods-str",
			"melee-size_mod"				=> "melee-mods-size",
			"melee-temp_mod"				=> "melee-mods-temp",
			"melee-temp-mod"				=> "melee-mods-temp",
			"ranged-misc_mod"				=> "ranged-mods-misc",
			"ranged-size_mod"				=> "ranged-mods-size",
			"ranged-temp_mod"				=> "ranged-mods-temp",
			"special-damage_reduction"		=> "hp-damage-reduction",
			"status-current_hp"				=> "hp-current",
			"status-current-hp"				=> "hp-current",
			"status-nonlethal_damage"		=> "hp-damage-nonlethal",
			"weaponSlot-critical_range"		=> "weaponSlot-critical",
			"weaponSlot-critical-range"		=> "weaponSlot-critical",
			"weight-lift_off_ground"		=> "weight-lift-off_ground",
			"weight-lift_over_head"			=> "weight-lift-over_head",
			"weight-lift_push_drag"			=> "weight-lift-push_drag",
			"weight-light_load"				=> "weight-load-light",
			"weight-medium_load"			=> "weight-load-medium",
			"weight-heavy_load"				=> "weight-load-heavy",
			"xp-current"					=> "xp-total"
		);
		
		//list of name => id, for those new attributes that had many old names (i.e. "generic-bab/generic-base_attack_bonus/generic-base-attack-bonus") 
		$newKeyList = array();
		
		foreach($oldToNew as $old => $new) {
			$sql = "SELECT * FROM csbt_attribute_table WHERE attribute='". $old ."'";
			$data = $this->db->run_query($sql);
			
			if(is_array($data) && isset($data['attribute_id']) && is_numeric($data['attribute_id'])) {
				$currentId = $data['attribute_id'];
			}
			else {
				$this->logObj->log_by_class(__METHOD__ .":: failed to retrieve data for (". $old ."), DETAILS:::: ". $e->getMessage(), 'Exception in code');
			}
			
			
			if(isset($newKeyList[$new])) {
				//The current key is a duplicate of an existing (already re-named) one: update existing character attributes & destroy it.
				$updateId = $newKeyList[$new];
				$sql = "UPDATE csbt_character_attribute_table SET attribute_id=". $updateId ." WHERE attribute_id=". $currentId;
				$numUpdates = $this->db->run_update($sql, true);
				
				$this->logsObj->log_by_class(__METHOD__ .":: updated (". $numUpdates .") references from '". $old ."' (id=". $currentId .") to '". $new ."'(id=". $updateId .")", 'Update');
				
				
				$numRemaining = $this->db->run_query("select * FROM csbt_character_attribute_table WHERE attribute_id=". $currentId);
				
				if($numRemaining == 0) {
					//now delete the old unused key.
					$sql = "DELETE FROM csbt_attribute_table WHERE attribute_id=". $currentId;
					$numUpdates = $this->db->run_update($sql);
					$this->logsObj->log_by_class(__METHOD__ .":: deleted (". $numUpdates .") to '". $old ."'", 'Delete');
				}
				else {
					$details = __METHOD__ .":: FATAL: ". count($numRemaining) ." records remain for (". $old .")";
					$this->logsObj->log_by_class($details, "Error");
					throw new exception($details);
				}
			}
			else {
				//update from old name to new name.
				$sql = "UPDATE csbt_attribute_table SET attribute='". $new ."' WHERE attribute_id=". $currentId;
				$numUpdates = $this->db->run_update($sql, true);
				$newKeyList[$new] = $currentId;
				
				$this->logsObj->log_by_class(__METHOD__ .":: renamed '". $old ."' to '". $new ."', attribute_id=(". $currentId .")", 'Update');
			}
		}
	}//end do_key_changes()
	//=========================================================================
}

?>
