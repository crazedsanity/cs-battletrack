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
			$this->run_upgrade_sql_file('upgrade_to_0-5-0.sql');
			#$this->fix_numbered_keys();
			#$this->do_key_changes();
			$this->convert_characters();
			
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
				$this->logsObj->log_by_class(__METHOD__ .":: failed to retrieve data for (". $old .")", 'Exception in code');
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
	
	
	
	//=========================================================================
	private function get_character_attribs($id) {
		$sql = "SELECT a.attribute, ca.attribute_value FROM csbt_attribute_table "
			."AS a INNER JOIN csbt_character_attribute_table AS ca USING "
			."(attribute_id) WHERE character_id=". $id ." ORDER BY a.attribute";
		
		try {
			$records = $this->db->run_query($sql, 'attribute', 'attribute_value');
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": FATAL ERROR: unable to retrieve attributes for character_id=(". $id ."), DETAILS::: ". $e->getMessage());
		}
		return($records);
	}//end get_character_attribs()
	//=========================================================================
	
	
	
	//=========================================================================
	private function convert_characters() {
		$this->logsObj->log_by_class(__METHOD__ .": starting", 'debug');
		$cleanStringArr = array(
			'uid'				=> 'int',
			'character_id'		=> 'int',
			'character_name'	=> 'sql'
		);
		$tableObj = new csbt_tableHandler($this->db, 'csbt_character_table', 'csbt_character_table_character_id_seq', 'character_id', $cleanStringArr, null);
		
		$characterList = $tableObj->get_records(null, 'character_id');
		foreach($characterList as $characterId=>$info) {
			$this->logsObj->log_by_class(__METHOD__ .": starting on character_id=(". $characterId .")", 'debug');
			$charObj = new csbt_character($this->db, $info['character_name'], true, $info['uid']);
			
			$charAttribs = $this->get_character_attribs($characterId);
			
			$armorData = array();
			$weaponData = array();
			$specialAbilities = array();
			$gearData = array();
			$savesData = array();
			
			$numConverted = 0;
			foreach($charAttribs as $n=>$v) {
				$attribBits = explode('-', $n);
				switch($attribBits[0]) {
					case 'abilities':
						$abilityId = $charObj->abilityObj->get_ability_id($attribBits[1]);
						if($attribBits[1] == 'base') {
							$charObj->handle_update('characterAbility__'. $attribBits[1] .'_score', null, $v);
						}
						elseif($attribBits[1] == 'temp') {
							$charObj->handle_update('characterAbility__'. $attribBits[1] .'_temporary_score', null, $v);
						}
						break;
					
					case 'ac':
						//do nothing!
						break;
					
					case 'armorSlot':
						$armorData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'class':		//class-total-level
					case 'class_total':	//class_total-level
						$charObj->handle_update('main__character_level', null, $v);
						break;
					
					case 'current_class':
					case 'current':
						$charObj->update_main_character_data(array('character_level'=>$v));
						break;
					
					case 'featsAbilities':
						$charObj->specialAbilitiesObj->create_special_ability($v);
						break;
					
					case 'gear':
						$gearData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'generated':
						break;
					
					case 'generic':
						switch($attribBits[1]) {
							case 'bab':
							case 'base': //base-attack-bonus
							case 'base_attack_bonus':
								if(is_numeric($v)) {
									$charObj->update_main_character_data(array('base_attack_bonus'=>$v));
								}
								break;
							
							case 'action_points':
								$charObj->update_main_character_data(array('action_points'=>$v));
								break;
								
							case 'age':
								$charObj->update_main_character_data(array('character_age'=>$v));
								break;
								
							case 'alignment':
								$charObj->update_main_character_data(array('alignment'=>$v));
								break;
								
							case 'campaign':
								//TODO: create campaign (or find already-created one)
								break;
								
							case 'class':
								$charObj->update_main_character_data(array('character_level'=>$v));
								break;
								
							case 'deity':
								$charObj->update_main_character_data(array('deity'=>$v));
								break;
								
							case 'eyes':
								$charObj->update_main_character_data(array('eye_color'=>$v));
								break;
								
							case 'gender':
								$charObj->update_main_character_data(array('gender'=>$v));
								break;
								
							case 'hair':
								$charObj->update_main_character_data(array('hair_color'=>$v));
								break;
								
							case 'height':
								$charObj->update_main_character_data(array('height'=>$v));
								break;
								
							case 'hp':
								$charObj->update_main_character_data(array('hit_points_max'=>$v));
								break;
								
							case 'race':
								$charObj->update_main_character_data(array('race'=>$v));
								break;
								
							case 'size':
								$charObj->update_main_character_data(array('size'=>$v));
								break;
								
							case 'weight':
								$charObj->update_main_character_data(array('weight'=>$v));
								break;
								
							default:
								throw new exception(__METHOD__ .": unknown generic attribute (". $n .")");
						}
						break;
						
					case 'init':
						if($attribBits[1] == 'misc') {
							$charObj->update_main_character_data(array('initiative_misc'=>$v));
						}
						elseif($attribBits[1] == 'total') {
							//this value is now calculated.
						}
						else {
							throw new exception(__METHOD__ .": invalid initiative item (". $n .")");
						}
						break;
					
					case 'mainCharacter':
						break;
					
					case 'melee':
						if(preg_match('/^misc/', $attribBits[1])) {
							$charObj->update_main_character_data(array('melee_misc'=>$v));
						}
						elseif(preg_match('/^size/', $attribBits[1])) {
							$charObj->update_main_character_data(array('melee_size'=>$v));
						}
						elseif(preg_match('/^temp/', $attribBits[1])) {
							$charObj->update_main_character_data(array('melee_temp'=>$v));
						}
						elseif(preg_match('/^total/', $attribBits[1])) {
							$charObj->update_main_character_data(array('melee_total'=>$v));
						}
						else {
							throw new exception(__METHOD__ .": unknown melee attribute (". $n .")");
						}
						break;
						
					case 'misc':
						if($attribBits[1] == 'notes') {
							$charObj->update_main_character_data(array('notes'=>$v));
						}
						else {
							throw new exception(__METHOD__ .": unknown misc attribute (". $n .")");
						}
						break;
					
					case 'ranged':
						if($attribBits[1] == 'misc_mod') {
							$charObj->update_main_character_data(array('ranged_misc'=>$v));
						}
						elseif($attribBits[1] == 'size_mod') {
							$charObj->update_main_character_data(array('ranged_size'=>$v));
						}
						elseif($attribBits[1] == 'temp_mod') {
							$charObj->update_main_character_data(array('ranged_temp'=>$v));
						}
						elseif($attribBits[1] == 'total') {
							//don't worry about it.
						}
						else {
							throw new exception(__METHOD__ .": unknown ranged value (". $n .")");
						}
						break;
					
					case 'save':
						$savesData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'status':
						if(preg_match('/hp$/', $n)) {
							$charObj->update_main_character_data(array('hit_points_current'=>$v));
						}
						elseif(preg_match('/nonlethal/', $n)) {
							$charObj->update_main_character_data(array('nonlethal_damage'=>$v));
						}
						else {
							throw new exception(__METHOD__ .": unknown generic status (". $n .")");
						}
						break;
					
					case 'weaponSlot':
						$weaponData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					default:
						throw new exception(__METHOD__ .": unknown attribute (". $n .")");
				}
				$numConverted++;
				
				$this->logsObj->log_by_class(__METHOD__ .": finished with attribute (". $n .")", 'debug');
			}
			
			
			foreach($armorData as $i=>$v) {
				$name = $v['name'];
				unset($v['name']);
				$charObj->armorObj->create_armor($name, $v);
				$numConverted++;
			}
			
			foreach($weaponData as $i=>$v) {
				$name = $v['name'];
				unset($v['name']);
				$charObj->weaponObj->create_weapon($name, $v);
				$numConverted++;
			}
			
			foreach($specialAbilities as $i=>$v) {
				$name = $v['name'];
				unset($v['name']);
				$charObj->specialAbilityObj->create_special_ability($name, $v);
				$numConverted++;
			}
			
			foreach($gearData as $i=>$v) {
				$name = $v['name'];
				unset($v['name']);
				$charObj->gearObj->create_gear($name, $v);
				$numConverted++;
			}
			
			foreach($savesData as $i=>$v) {
				
			}
			
			$this->logsObj->log_by_class(__METHOD__ .": converted (". $numConverted .") for characterId (". $characterId .")", 'debug');
		}
	}//end convert_characters()
	//=========================================================================
}

?>
