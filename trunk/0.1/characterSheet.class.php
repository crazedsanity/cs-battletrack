<?php
/*
 * Created on Jul 13, 2009
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

//TODO: consider optionally adding the logging system.

class characterSheet extends battleTrackAbstract {
	
	protected $characterId;
	
	protected $dbObj;
	
	protected $dataCache=array();
	
	protected $id2key=array();
	
	protected $logger;
	
	protected $changesByKey=array();
	
	//-------------------------------------------------------------------------
	public function __construct($characterId=null) {
		parent::__construct();
		$this->logger->logCategory = "Character Sheet";
		
		if(is_numeric($characterId) && $characterId >= 0) {
			$this->set_character_id($characterId);
			$this->get_character_data();
		}
		else {
			#throw new exception(__METHOD__ .": characterId is required");
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_character_id($id) {
		if(is_numeric($id)) {
			if(is_numeric($this->characterId) && $id != $this->characterId) {
				$this->logger->log_by_class("Changed character from id=(". $this->characterId .") to (". $id .")", 'debug');
			}
			$this->characterId = $id;
			$this->get_character_data();
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_character($characterName, $uid) {
		if(strlen($characterName) && is_numeric($uid) && $uid > 0) {
			$sql = "INSERT INTO csbt_character_table ". 
				$this->gfObj->string_from_array(array(
					'character_name'	=> $characterName,
					'uid'				=> $uid
				), 'insert');
			$newId = $this->dbObj->run_insert($sql, 'csbt_character_table_character_id_seq');
			
			$this->logger->log_by_class("New character (id=". $newId ."),: '". $characterName ."'", 'created character');
			$this->set_character_id($newId);
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid name (". $characterName .") or uid (". $uid .")");
		}
		$this->load_character_defaults();
		return($this->characterId);
	}//end create_character()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT * FROM csbt_character_attribute_table ".
					"WHERE character_id=". $this->characterId, 'character_attribute_id');
			
			$this->dataCache = array();
			$this->id2key = array();
			if(is_array($data)) {
				foreach($data as $id=>$attribs) {
					$key = $this->get_attribute_key($attribs);
					$this->dataCache[$key] = array(
						'value'	=> $attribs['attribute_value'],
						'id'	=> $id
					);
					$this->id2key[$id] = $key;
				}
			}
			$this->logger->log_by_class("Retrieved ". count($this->dataCache) ." attributes for id=(". $this->characterId .")", 'debug');
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid internal characterId (". $this->characterId .")");
		}
		
		return($this->dataCache);
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_character_data(array $attribs) {
		$this->get_character_data();
		$totalCount = count($attribs);
		$finalCount = 0;
		$changeList = array();
		$this->dbObj->beginTrans();
		foreach($attribs as $type=>$subData) {
			if(is_array($subData)) {
				foreach($subData as $subtype=>$finalBit) {
					if(is_array($finalBit)) {
						foreach($finalBit as $name=>$value) {
							$changeList[$this->handle_attrib($type, $subtype, $name, $value)]++;
							$finalCount++;
						}
					}
					else {
						$name = null;
						$changeList[$this->handle_attrib($type, $subtype, $name, $finalBit)]++;
						$finalCount++;
					}
				}
			}
			else {
				#$this->gfObj->debug_print(__METHOD__ .": XXXXXXXXXXXtype=(". $type ."), subtype=(". $subData .")",1);
				$this->exception_handler(__METHOD__ .": invalid data under (". $type ."):: ". $attribs);
			}
		}
		if(isset($changeList[null])) {
			unset($changeList[null]);
		}
		$logThis = $this->gfObj->string_from_array($changeList, 'update');
		$this->logger->log_by_class("Character update result: ". $logThis, 'update');
		
		$this->get_character_data();
		
		$this->dbObj->commitTrans();
		
		return($finalCount);
	}//end update_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function insert_attrib($type, $subtype, $name, $value) {
		if(is_null($name) || !strlen($name)) {
			$name = "";
		}
		$insertData = array(
				'character_id'		=> $this->characterId,
				'attribute_type'	=> $type,
				'attribute_subtype'	=> $subtype,
				'attribute_name'	=> $name,
				'attribute_value'	=> $value
			);
		$sql = "INSERT INTO csbt_character_attribute_table ".
			$this->gfObj->string_from_array($insertData, 'insert');
		try {
			$retval = $this->dbObj->run_insert($sql, 'csbt_character_attribute_table_character_attribute_id_seq');
			$key = $this->get_attribute_key($insertData);
			$this->logger->log_by_class("Created attribute (". $key .") with value '". $value ."'", 'create attribute');
			if(!is_numeric($retval) || $retval < 1) {
				$this->exception_handler(__METHOD__ .": failed to create attribute for data::: ". $this->gfObj->debug_print(func_get_args(),0));
			}
		}
		catch(exception $e) {
			$this->exception_handler(__METHOD__ .": error encountered::: ". $e->getMessage());
		}
		return($retval);
	}//end insert_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function get_attrib($type, $subtype, $name, $value) {
		
		//check the internal cache before going to the database (saves time)
		$dataArr = array(
			'character_id'		=> $this->characterId,
			'attribute_type'	=> $type,
			'attribute_subtype'	=> $subtype,
			'attribute_name'	=> $name
		);
		$cacheKey = $this->get_attribute_key($dataArr);
		$result = null;
		if(isset($this->dataCache[$cacheKey])) {
			$dataArr = array(
				'attribute_value'	=> $value
			);
			$result = $this->dataCache[$cacheKey];
		}
		else {
			unset($dataArr['attribute_value']);
			$sql = "SELECT * FROM csbt_character_attribute_table WHERE ".
				$this->gfObj->string_from_array($dataArr, 'select');
			
			try {
				$result = $this->dbObj->run_query($sql, 'character_attribute_id');
				$numrows = $this->dbObj->numRows();
				if($numrows > 1) {
					$this->exception_handler(__METHOD__ .": multiple rows (". $numrows .") detected::: " .
							$this->gfObj->debug_print(func_get_args(),0) ."<br>SQL::: ". $sql);
				}
			}
			catch(exception $e) {
				$this->exception_handler(__METHOD__ .": failed to retrieve attribute::: ". $e->getMessage());
			}
		}
		
		return($result);
	}//end get_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function get_attribute_key(array $data) {
		$key = $data['attribute_type'] .'-'. $data['attribute_subtype'];
		if(strlen($data['attribute_name'])) {
			$key .= '-'. $data['attribute_name'];
		}
		return($key);
	}//end get_attribute_key()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function update_attrib($id, array $updates, $logPrefix=NULL, $logClass=NULL) {
		if(is_array($updates) && count($updates) && is_numeric($id) && is_numeric($this->characterId)) {
			$this->gfObj->switch_force_sql_quotes(true);
			$sql = "UPDATE csbt_character_attribute_table SET " .
					$this->gfObj->string_from_array($updates, 'update', null, 'sql', true) .
					" WHERE character_id=". $this->characterId ." AND " .
					"character_attribute_id=". $id;
			$this->gfObj->switch_force_sql_quotes(false);
			$this->dbObj->run_update($sql);
			
			$key = $this->get_attribute_key($updates);
			$logThis = "Updated attribute (". $key .") with value '". $updates['attribute_value'] ."'";
			if(!is_null($logPrefix) && strlen($logPrefix)) {
				$logThis = $logPrefix .' - '. $logThis;
			}
			if(is_null($logClass) || !strlen($logClass)) {
				$logClass = 'update attribute';
			}
			$this->logger->log_by_class($logThis, $logClass);
			
			//keep track of changes, so other code (i.e. AJAX calls) know what has changed.
			$this->changesByKey[$key] += 1;
			
			//update internal data cache.
			if(isset($updates['attribute_value'])) {
				//handle automatic updates BEFORE touching internal cache.
				try {
					$this->_handle_auto_updates($key, $this->dataCache[$key]['value'], $updates['attribute_value']);
				}
				catch(Exception $ex) {
					$this->logger->log_by_class($ex->getMessage());
				}
				$this->dataCache[$key] = $this->dataCache[$key];
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": no updates");
		}
	}//end update_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_main_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT * FROM csbt_character_table " .
					"WHERE character_id=". $this->characterId);
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId");
		}
		return($data);
	}//end get_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_main_character_data(array $data) {
		if(is_numeric($this->characterId)) {
			if(is_array($data) && count($data)) {
				$sql = "UPDATE csbt_character_table SET " .
						$this->gfObj->string_from_array($data, 'update', null, 'sql') .
						" WHERE character_id=". $this->characterId;
				$updateRes = $this->dbObj->run_update($sql);
			}
			else {
				$this->exception_handler(__METHOD__ .": invalid data");
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": invalid characterId");
		}
		
		return($updateRes);
	}//end update_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function delete_attrib($id) {
		if(is_numeric($this->characterId)) {
			if(is_numeric($id) && $id > 0) {
				$result = $this->dbObj->run_update("DELETE FROM csbt_character_attribute_table WHERE " .
						"character_id=". $this->characterId ." AND character_attribute_id=". $id);
				
				$key = $this->id2key[$id];
				$value = $this->dataCache[$key]['value'];
				$this->logger->log_by_class("Deleted attribute (". $key ."), old value='". $value ."'", 'delete attribute');
			}
		}
		else {
			$this->exception_handler(__METHOD__ .": characterId not set");
		}
		
		return($result);
	}//end delete_attrib();
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_attrib($type, $subtype, $name, $value, $logPrefix, $logClass) {
		$attribData = $this->get_attrib($type, $subtype, $name, $value);
		$result = null;
		if(is_numeric($attribData['id']) && $value !== $attribData['value']) {
			if(is_null($value) || !strlen($value)) {
				$this->delete_attrib($attribData['id']);
				$result = 'delete';
			}
			else {
				$this->update_attrib(
					$attribData['id'], 
					array(
						'attribute_type'	=> $type,
						'attribute_subtype'	=> $subtype,
						'attribute_name'	=> $name,
						'attribute_value'	=> $value
					), 
					$logPrefix, 
					$logClass
				);
				$result = 'update';
			}
		}
		elseif(!is_null($value) && strlen($value) && !is_array($attribData)) {
			$this->insert_attrib($type, $subtype, $name, $value);
			$result = 'insert';
		}
		
		return($result);
	}//end handle_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function exception_handler($message) {
		$logId = $this->logger->log_by_class($message, 'exception in code');
		throw new exception($message ." -- Logged (id #". $logId .")");
	}//end exception_handler()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_attrib_by_key($key, $val, $logPrefix=NULL, $logClass=NULL) {
		$separator = "-";
		$bits = explode($separator, $key);
		if(count($bits) >= 2) {
			return($this->handle_attrib($bits[0], $bits[1], $bits[2], $val, $logPrefix, $logClass));
		}
		else {
			throw new exception(__METHOD__ .": invalid number of bits (". count($bits) .") in key (". $key .")");
		}
	}//end handle_attrib_by_key()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function load_character_defaults() {
		
		$autoSkills = array();
		
		//Skills added as a numbered array so I don't have to manually renumber if an item is added or removed.
		{
		    $autoSkills[] = array("Appraise",			"int");
		    $autoSkills[] = array("Balance",			"dex");
		    $autoSkills[] = array("Bluff",				"cha");
		    $autoSkills[] = array("Climb",				"str");
		    $autoSkills[] = array("Concentration",		"con");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Craft ()",			"int");
		    $autoSkills[] = array("Decipher Script",	"int");
		    $autoSkills[] = array("Diplomacy",			"cha");
		    $autoSkills[] = array("Disable Device",		"int");
		    $autoSkills[] = array("Disguise",			"cha");
		    $autoSkills[] = array("Escape Artist",		"dex");
		    $autoSkills[] = array("Forgery",			"int");
		    $autoSkills[] = array("Gather Information",	"cha");
		    $autoSkills[] = array("Handle Animal",		"cha");
		    $autoSkills[] = array("Heal",				"wis");
		    $autoSkills[] = array("Hide",				"dex");
		    $autoSkills[] = array("intimidate",			"cha");
		    $autoSkills[] = array("Jump",				"str");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Knowledge ()",		"int");
		    $autoSkills[] = array("Listen",				"wis");
		    $autoSkills[] = array("Move Silently",		"dex");
		    $autoSkills[] = array("Open Lock",			"dex");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Perform ()",			"cha");
		    $autoSkills[] = array("Profession ()",		"wis");
		    $autoSkills[] = array("Profession ()",		"wis");
		    $autoSkills[] = array("Ride",				"dex");
		    $autoSkills[] = array("Search",				"int");
		    $autoSkills[] = array("Sense Motive",		"wis");
		    $autoSkills[] = array("Sleight of Hand",	"dex");
		    $autoSkills[] = array("Spellcraft",			"int");
		    $autoSkills[] = array("Spot",				"wis");
		    $autoSkills[] = array("Survival",			"wis");
		    $autoSkills[] = array("Swim",				"str");
		    $autoSkills[] = array("Tumble",				"dex");
		    $autoSkills[] = array("Use Magic Device",	"cha");
		    $autoSkills[] = array("Use Rope",			"dex");
		}
		
		foreach($autoSkills as $i=>$data) {
			$n = $data[0];
			$v = $data[1];
			$namId = $this->handle_attrib('skills', $i, 'name', $n);
			$abilId = $this->handle_attrib('skills', $i, 'ability', $v);
			$namKey = 'skills-'. $i .'-name';
			$abilKey = 'skills-'. $i .'-ability';
			#$this->gfObj->debug_print(__METHOD__ .": #". $i ." <b>". $namKey ."</b>=(". $n .")[". $namId ."], <b>". $abilKey ."</b>=(". $v .")[". $abilId ."]",1);
		}
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function __get($var) {
		if(isset($this->$var)) {
			$returnThis = $this->$var;
		}
		else {
			throw new exception(__METHOD__ .": unknown var (". $var .")");
		}
		return($returnThis);
	}//end __get()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function _handle_auto_updates($key, $oldVal=NULL, $newVal=NULL) {
		
		$bits = explode('-', $key);
		if((!is_null($oldVal) && !is_numeric($oldVal)) || (!is_null($newVal) && !is_numeric($newVal))) {
			throw new exception(__METHOD__ .": FAIL, oldVal=(". $oldVal ."), newVal=(". $newVal .")");
		}
		elseif(count($bits) >= 2 && count($bits) < 4) {
			$type = $bits[0];
			$subType = $bits[1];
			$name = $bits[2];
			
			
			//for the key 'skills-0-ranks', $type='skills', $subType=0, $name='ranks'
			$logThis = NULL;//only logs if this is NOT null.
			switch(strtolower($type)) {
				case 'abilities':
					switch(strtolower($name)) {
						//.........................................................
						case 'base':
							//TODO: if this is strength, update all the 'weight-*' values...
							//TODO: for strength or dex, tell 'em to check their attack/damage stuff
							//TODO: for dex, update init.
							
							//so if $type='abilities' and $name='dex', this will update 'abilities-dex-mod' appropriately.
							$modKeyName = $type .'-'. $subType .'-mod';
							try{
								$newAbilityModifier = $this->_get_ability_mod($subType, $newVal);
								$this->handle_attrib_by_key($modKeyName, $newAbilityModifier, 'Auto-update from key ('. $key .')', 'auto-update');
								#$logThis = "auto-updated key (". $modKeyName ."), oldVal=(". $oldVal ."), newVal=(". $newVal ."), calculated value was (". $newAbilityModifier .")";
								
								//TODO: update associated skills... find 'skills-($x)-ability' where value == $subType... update 'skills-($x)-abilitymod' (will cause the whole thing to get re-calculated)
								
								$numUpdated = 0;
								foreach($this->dataCache as $i=>$subData) {
									if(preg_match('/skills-/', $i) && preg_match('/-ability$/', $i)) {
										if(strtolower($subData['value']) == $subType) {
											//(if it's dex) found 'skills-($i)-ability' with value of 'dex', update 'skills-($i)-abilitymod'
											$keyBits = $this->get_bits_from_key($i);
											$updateKeyName = 'skills-'. $keyBits['subtype'] .'-abilitymod';
											#$this->logger->log_by_class('Auto-updating based on key ('. $i .')';
											$this->handle_attrib_by_key($updateKeyName, $newAbilityModifier, 'Auto-update from key ('. $i .')', 'auto-update');
											$numUpdated++;
											
										}
									}
								}
							}
							catch(Exception $ex) {
								
$this->logger->log_by_class('**** EXCEPTION!!!! *** '. $ex->getMessage());
							}
							break;
						//.........................................................
						
							
						//.........................................................
						case 'mod':
							//TODO: change template to NOT allow changes to modifiers.
							//TODO: don't make changes if they have something in 'abilities-$subType-temp'
							#throw new exception(__METHOD__ .": cannot change ability modifier manually, set base value instead");
							break;
						//.........................................................
						
							
						//.........................................................
						default:
							
						//.........................................................
					}//end switch($name)
					break;
				
				case 'skills':
					$prefix = 'skills-'. $subType .'-';
					//TODO: handle the "max dex bonus" thing from armor (account for all armor, disregard stuff not worn)
					switch(strtolower($name)) {
						//.........................................................
						case 'miscmod':
						case 'ranks':
						case 'abilitymod':
							$newTotal = $this->get_skill_modifier($subType, $name, $newVal);
							#$this->logger->log_by_class('Automatically updating ('. $prefix .'total), newVal=('. $newVal .')', 'auto update...');
							$this->handle_attrib_by_key($prefix .'total', $newTotal, 'Auto-update from key ('. $key .')', 'auto-update');
							
							//TODO: for 'ranks', total-up all the ranks & update the (not implemented) total skills value...
							#$logThis = 'updated '. $name .', newTotal=('. $newTotal .')';
							break;
						//.........................................................
						
						
						//.........................................................
						default:
							
						//.........................................................
					}//end switch('skills')
					break;
				
				default:
			}//end switch($type)
			
		}
		else {
			throw new exception(__METHOD__ .": too many bits in key (". $key ."), key is malformed");
		}
		
		if(!is_null($logThis)) {
			$this->logger->log_by_class($logThis, 'DEBUG');
		}
	}//_handle_auto_updates()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function get_skill_modifier($skillNum, $changedItem=null, $newVal=null) {
		$tempNum = 0;
		$algo = '';
		try {
			if($changedItem == 'abilitymod') {
				$tempNum += $newVal;
			}
			else {
				$tempNum += $this->_clean_value_as_numeric('skills-'. $skillNum .'-abilitymod');
			}
			if($changedItem == 'ranks') {
				$tempNum += $newVal;
			}
			else {
				$tempNum += $this->_clean_value_as_numeric('skills-'. $skillNum .'-ranks');
			}
			if($changedItem == 'miscmod') {
				$tempNum += $newVal;
			}
			else {
				$tempNum += $this->_clean_value_as_numeric('skills-'. $skillNum .'-miscmod');
			}
		}
		catch(Exception $ex) {
			throw new exception(__METHOD__ .": failed to calculate value for skill #". $skillNum .", details::: ". $ex->getMessage());
		}
		return($tempNum);
	}//end get_skill_modifier()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function _clean_value_as_numeric($key) {
		
		if(is_numeric(preg_replace('/[^0-9]/', '', $this->dataCache[$key]['value']))) {
			$retval = preg_replace('/[^0-9]/', '', $this->dataCache[$key]['value']);
			settype($retval, 'int');
		}
		else {
			throw new exception(__METHOD__ .": invalid data for '". $key ."' (". $this->dataCache['$key'] .")". cs_debug_backtrace(0) ."\n". $this->gfObj->debug_print($this->dataCache,0));
		}
		return($retval);
	}//end _clean_value_as_numeric()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function _get_ability_mod($abilityName, $useThisNum=null) {
		try {
			//for 'str', it would be passing 'abilities-str'...
			if(!is_null($useThisNum)) {
				$baseVal = $useThisNum;
			} 
			else {
				$baseVal = $this->_clean_value_as_numeric('abilities-'. $abilityName .'-base');
			}
			$modifier = floor((($baseVal - 10)/2));
		}
		catch(Exception $ex) {
			throw new exception(__METHOD__ .": invalid base value for '". $abilityName ."', details::: ". $ex->getMessage());
		}
		return($modifier);
	}//end _get_ability_mod()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_bits_from_key($key) {
		$bits = explode('-', $key);
		if(count($bits) < 2 || count($bits) > 3) {
			throw new exception(__METHOD__ .": invalid key bits, malformed key (". $key .")");
		}
		$retval = array(
			'type'		=> $bits[0],
			'subtype'	=> $bits[1],
			'name'		=> $bits[2]
		);
		return($retval);
	}//end get_bits_from_key()
	//-------------------------------------------------------------------------
	
}

?>
