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
			$charObj = new csbt_character($this->db, $characterId, false, $info['uid']);
			
			$charAttribs = $this->get_character_attribs($characterId);
			
			$armorData = array();
			$weaponData = array();
			$specialAbilities = array();
			$gearData = array();
			$savesData = array();
			$skillsData = array();
			$mainCharData = array();
			$charAbilities = array();
			
			$numConverted = 0;
			foreach($charAttribs as $n=>$v) {
				$attribBits = explode('-', $n);
				switch($attribBits[0]) {
					case 'abilities':
						if($attribBits[2] == 'base') {
							#$charObj->handle_update('characterAbility__'. $attribBits[1] .'_score', null, $v);
							$charAbilities['create'][$attribBits[1]] = $v;
						}
						elseif($attribBits[2] == 'temp' && $v > 0) {
							#$charObj->handle_update('characterAbility__'. $attribBits[1] .'_temporary_score', null, $v);
							$charAbilities['update'][$attribBits[1]] = $v;
						}
						$this->logsObj->log_by_class(__METHOD__ .": attribute name = (". $n ."), charAbilities::: ". $this->gfObj->debug_print($charAbilities,0), 'debug');
						break;
					
					case 'ac':
						//do nothing!
						break;
					
					case 'armorSlot':
						if($attribBits[2] == 'acbonus') {
							$attribBits[2] = 'ac_bonus';
						}
						if($attribBits[2] == 'checkpenalty') {
							$attribBits[2] = 'check_penalty';
						}
						if($attribBits[2] == 'dexbonus') {
							$attribBits[2] = 'max_dex';
						}
						if($attribBits[2] == 'type') {
							$attribBits[2] = 'armor_type';
						}
						if($attribBits[2] == 'spellfail') {
							$attribBits[2] = 'spell_fail';
						}
						if($attribBits[2] == 'speed') {
							$attribBits[2] = 'max_speed';
						}
						$armorData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'class':		//class-total-level
					case 'class_total':	//class_total-level
					case 'current_class':
					case 'current':
						$mainCharData['character_level'] = $v;
						break;
					
					case 'featsAbilities':
						$charObj->specialAbilityObj->create_special_ability($v);
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
									$mainCharData['base_attack_bonus'] = $v;
								}
								break;
							
							case 'action_points':
								$mainCharData['action_points'] = $v;
								break;
								
							case 'age':
								$mainCharData['character_age'] = $v;
								break;
								
							case 'alignment':
								$mainCharData['alignment'] = $v;
								break;
								
							case 'campaign':
								//TODO: create campaign (or find already-created one)
								break;
								
							case 'class':
								$mainCharData['character_level'] = $v;
								break;
								
							case 'deity':
								$mainCharData['deity'] = $v;
								break;
								
							case 'eyes':
								$mainCharData['eye_color'] = $v;
								break;
								
							case 'gender':
								$mainCharData['gender'] = $v;
								break;
								
							case 'hair':
								$mainCharData['hair_color'] = $v;
								break;
								
							case 'height':
								$mainCharData['height'] = $v;
								break;
								
							case 'hp':
								$mainCharData['hit_points_max'] = $v;
								break;
								
							case 'race':
								$mainCharData['race'] = $v;
								break;
								
							case 'size':
								$mainCharData['size'] = $v;
								break;
							
							case 'skills':
								if($attribBits[2] == 'maxrank') {
									$mainCharData['skills_max'] = $v;
								}
								elseif($attribBits[2] == 'maxcc') {
									//do nothing: it is calculated
								}
								else {
									throw new exception(__METHOD__ .": unknown generic skill (". $n .")");
								}
								break;
								
							case 'weight':
								$mainCharData['weight'] = $v;
								break;
								
							default:
								throw new exception(__METHOD__ .": unknown generic attribute (". $n .")");
						}
						break;
						
					case 'init':
						if($attribBits[1] == 'misc') {
								$mainCharData['initiative_misc'] = $v;
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
					
					case 'main':
						if($n == 'main-character-name') {
							$mainCharData['character_name'] = $v;
						}
						else {
							throw new exception(__METHOD__ .": unknown 'main' attribute (". $n .")");
						}
						break;
					
					case 'MELEE__abilities':
					case 'MELEE':
						//this is calculated, so just ignore them.
						break;
					
					case 'melee':
						if(preg_match('/^misc/', $attribBits[1])) {
							if(strlen($v) && is_numeric($v)) {
								$mainCharData['melee_misc'] = $v;
							}
						}
						elseif(preg_match('/^size/', $attribBits[1])) {
							$mainCharData['melee_size'] = $v;
						}
						elseif(preg_match('/^temp/', $attribBits[1])) {
							$mainCharData['melee_temp'] = $v;
						}
						elseif(preg_match('/^total/', $attribBits[1])) {
							//calculated value
						}
						else {
							throw new exception(__METHOD__ .": unknown melee attribute (". $n .")");
						}
						break;
						
					case 'misc':
						if($attribBits[1] == 'notes') {
							$mainCharData['notes'] = $v;
						}
						else {
							throw new exception(__METHOD__ .": unknown misc attribute (". $n .")");
						}
						break;
					
					case 'ranged':
						if($attribBits[1] == 'misc_mod') {
							$mainCharData['ranged_misc'] = $v;
						}
						elseif($attribBits[1] == 'size_mod') {
							$mainCharData['ranged_size'] = $v;
						}
						elseif($attribBits[1] == 'temp_mod') {
							$mainCharData['ranged_temp'] = $v;
						}
						elseif($attribBits[1] == 'total') {
							//don't worry about it (now it is calculated)
						}
						else {
							throw new exception(__METHOD__ .": unknown ranged value (". $n .")");
						}
						break;
					
					case 'save':
						if(!is_numeric($v)) {
							$v = 0;
						}
						$savesData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'skills':
						if($attribBits[2] == 'is_class') {
							$attribBits[2] = true;
						}
						if($attribBits[2] == 'miscmod') {
							$attribBits[2] = 'misc_mod';
						}
						$skillsData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'special':
						if($attribBits[1] == 'damage_reduction') {
							$mainCharData['damage_reduction'] = $v;
						} 
						else {
							throw new exception(__METHOD__ .": unknown special attribute (". $n .")");
						}
						break;
					
					case 'speed':
						if($attribBits[1] == 'total') {
							$mainCharData['speed'] = $v;
						}
						else {
							throw new exception(__METHOD__ .": invalid speed attribute (". $n .")");
						}
						break;
					
					case 'status':
						if(preg_match('/hp$/', $n)) {
							$mainCharData['hit_points_current'] = $v;
						}
						elseif(preg_match('/nonlethal/', $n)) {
							$mainCharData['nonlethal_damage'] = $v;
						}
						else {
							throw new exception(__METHOD__ .": unknown generic status (". $n .")");
						}
						break;
					
					case 'weaponSlot':
						if($attribBits[2] == 'totalatk') {
							$attribBits[2] = 'total_attack_bonus';
							if(!is_numeric($v)) {
								//NOTE: this is because one of my test characters had this value as '+e'
								$v = 0;
							}
						}
						if($attribBits[2] == 'ranged') {
							$attribBits[2] = 'range';
						}
						if($attribBits[2] == 'critical_range') {
							$attribBits[2] = 'critical';
						}
						if($attribBits[2] == 'ammo') {
							$attribBits[2] = 'ammunition';
						}
						if($attribBits[2] == 'type') {
							$attribBits[2] = 'weapon_type';
						}
						$weaponData[$attribBits[1]][$attribBits[2]] = $v;
						break;
					
					case 'weight':
						//All these values are calculated automatically now.
						break;
					
					case 'xp':
						//These are not used...
						break;
					
					default:
						throw new exception(__METHOD__ .": unknown attribute (". $n .")");
				}
				
				#$this->logsObj->log_by_class(__METHOD__ .": finished with attribute (". $n .")", 'debug');
			}
			
			$this->logsObj->log_by_class(__METHOD__ .": finished with attributes, starting to do the major updates...", 'debug');
			
			try {
				//handle main character updates in one call.
				try {
					$charObj->update_main_character_data($mainCharData);
					$numConverted += count($mainCharData);
					unset($mainCharData);
				}
				catch(Exception $e) {
					throw new exception(__METHOD__ .": an error occurred while updating main characterData for characterId=(". $characterId ."): ". $e->getMessage() ."<hr>DATA::: ". $this->gfObj->debug_print($mainCharData,0));
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished main_character updates, numConverted=(". $numConverted .")", 'debug');
				
				
				if(is_array($charAbilities ) && count($charAbilities) && count($charAbilities['create']) == 6) {
					foreach($charAbilities['create'] as $abilityName=>$value) {
						$tempScore = null;
						if(isset($charAbilities['update'][$abilityName])) {
							$tempScore = $charAbilities['update'][$abilityName];
							unset($charAbilities['update'][$abilityName]);
						}
						$charObj->abilityObj->create_ability($charObj->abilityObj->baseAbilityObj->get_ability_id($abilityName), $value, $tempScore);
					}
					if(isset($charAbilities['update']) && count($charAbilities['update'])) {
						foreach($charAbilities as $updateIndex=>$updateValue) {
							$charObj->handle_update($updateIndex, null, $updateValue);
							$numConverted++;
						}
					}
				}
				else {
					throw new exception(__METHOD__ .": FATAL: character (". $characterId .") has no abilities... ". $this->gfObj->debug_print($charAttribs,0));
				}
				
				foreach($armorData as $i=>$v) {
					$name = $v['name'];
					unset($v['name']);
					if(!isset($v['armor_type'])) {
						$v['armor_type'] = __METHOD__ .": set me";
					}
					if(!isset($v['max_dex'])) {
						$v['max_dex'] = 0;
					}
					if(!isset($v['ac_bonus'])) {
						$v['ac_bonus'] = 0;
					}
					$charObj->armorObj->create_armor($name, $v);
					$numConverted++;
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished armor updates, numConverted=(". $numConverted .")", 'debug');
				
				try {
					foreach($weaponData as $i=>$v) {
						$name = $v['name'];
						unset($v['name']);
						if(!isset($v['damage'])) {
							$v['damage'] = __METHOD__ .": set me";
						}
						if(!isset($v['critical'])) {
							$v['critical'] = __METHOD__ .": set me";
						}
						if(!isset($v['weapon_type'])) {
							$v['weapon_type'] = __METHOD__ .": set me";
						}
						$charObj->weaponObj->create_weapon($name, $v);
						$numConverted++;
					}
				}
				catch(Exception $e) {
					throw new exception(__METHOD__ .": an error occurred while handling weapons: ". $e->getMessage() ."<hr> DATA::: ". $this->gfObj->debug_print($weaponData,0));
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished weapon updates, numConverted=(". $numConverted .")", 'debug');
				
				foreach($specialAbilities as $i=>$v) {
					$name = $v['name'];
					unset($v['name']);
					$charObj->specialAbilityObj->create_special_ability($name, $v);
					$numConverted++;
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished special abilities, numConverted=(". $numConverted .")", 'debug');
				
				foreach($gearData as $i=>$v) {
					$name = $v['name'];
					unset($v['name']);
					if(!isset($v['weight']) || !is_numeric($v['weight'])) {
						$v['weight'] = 1;
					}
					$charObj->gearObj->create_gear($name, $v);
					$numConverted++;
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished gear, numConverted=(". $numConverted .")", 'debug');
				
				try {
					$saveToAbility = array(
						'fort'		=> 'con',
						'will'		=> 'wis',
						'reflex'	=> 'dex'
					);
					$addSuffixTo=array('base', 'magic', 'misc', 'temp');
					foreach($savesData as $i=>$v) {
						if(isset($saveToAbility[$i])) {
							unset($v['total']);
							foreach($addSuffixTo as $fixThis) {
								if(isset($v[$fixThis])) {
									$v[$fixThis .'_mod'] = $v[$fixThis];
									unset($v[$fixThis]);
								}
							}
							$createSaveRes = $charObj->savesObj->create_save($i, $saveToAbility[$i], $v);
						}
						else {
							throw new exception(__METHOD__ .": cannot create save for '". $i ."' without ability");
						}
					}
				}
				catch(Exception $e) {
					throw new exception(__METHOD__ .": error while handling saves (". $i .")::: ". $e->getMessage() ."<hr>DATA::: ". $this->gfObj->debug_print($v,0));
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished saves, numConverted=(". $numConverted .")", 'debug');
				
				foreach($skillsData as $i=>$v) {
					if(isset($v['abilitymod'])) {
						unset($v['abilitymod']);
					}
					if(isset($v['total'])) {
						unset($v['total']);
					}
					$name = $v['name'];
					if(!isset($v['ability'])) {
						$ability = 'int';
					}
					else {
						$ability = strtolower($v['ability']);
					}
					unset($v['name'], $v['ability']);
					#$createSkillRes = $charObj->skillsObj->create_skill($name, $ability, $v);
					$dataArr = $v;
					$dataArr['character_id'] = $charObj->characterId;
					$dataArr['skill_name'] = $name;
					$dataArr['ability_id'] = $charObj->abilityObj->get_ability_id($ability);
					
					//TODO: calling skillsObj->create_skill() for some reason causes a segmentation fault... this could crop-up in the future!!!
					$createSkillRes = $charObj->skillsObj->tableHandlerObj->create_record($dataArr);
					$numConverted++;
				}
				$this->logsObj->log_by_class(__METHOD__ .": finished skill, numConverted=(". $numConverted .")", 'debug');
			}
			catch(Exception $e) {
				$this->logsObj->log_by_class(__METHOD__ .": unable to finish characterId=(". $characterId ."), attempting to continue... error was::: ". $e->getMessage(), 'POSSIBLE FATAL ERROR (Exception)');
				$attribTableObj = new csbt_tableHandler($this->db, 'csbt_character_attribute_table', 'csbt_character_attribute_table_character_attribute_id_seq', 'character_id', $cleanStringArr, null);
				$deleteAttribs = $attribTableObj->delete_record($characterId);
				$deleteResult = $tableObj->delete_record($characterId);
				$this->logsObj->log_by_class(__METHOD__ .": deleted invalid characterId=(". $characterId ."), deleted attribs=(". $deleteAttribs ."), with result=(". $deleteResult .")");
			}
			
			$this->logsObj->log_by_class(__METHOD__ .": converted (". $numConverted .") for characterId (". $characterId .")", 'debug');
		}
		
		//make sure it all works out as expected...
		#throw new exception(__METHOD__ .": not finished");
	}//end convert_characters()
	//=========================================================================
}

?>
