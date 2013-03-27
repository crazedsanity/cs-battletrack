<?php


//TODO: consider optionally adding the logging system.

class csbt_characterSheet extends csbt_tableHandler {
	
	protected $characterId;
	
	protected $dbObj;
	
	protected $dataCache=array();
	
	protected $id2key=array();
	
	protected $logger;
	
	protected $changesByKey=array();
	
	protected $cleanStringArr = array(
			'character_id'		=> 'int',
			'attribute_id'		=> 'int'
		);
	
	protected $skillsObj;
	protected $characterObj;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyName = 'character_id';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $characterIdOrName=null, $playerUid=null) {
		$this->dbObj = $dbObj;
		$this->logger = new cs_webdblogger($this->dbObj, "Character Sheet");
		
		$this->playerUid = $playerUid;
		
		if(is_numeric($characterIdOrName) && $characterIdOrName >= 0) {
			$this->set_character_id($characterIdOrName, false, $playerUid);
			#$this->get_character_data();
		}
		elseif(is_string($characterIdOrName) && strlen($characterIdOrName) >= 2) {
			$this->characterObj = new csbt_character($dbObj, $characterIdOrName, true, $playerUid);
			$this->set_character_id($this->characterObj->characterId);
		}
		else {
			throw new exception(__METHOD__ .": characterId or name of new character is required, given invalid value (". $characterIdOrName .")");
		}
		
		$this->gfObj = new cs_globalFunctions();
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_character_id($id) {
		if(is_numeric($id)) {
			if(is_numeric($this->characterId) && $id != $this->characterId) {
				$this->logger->log_by_class("Changed character from id=(". $this->characterId .") to (". $id .")", 'debug');
			}
			$this->characterId = $id;
			$this->characterObj = new csbt_character($this->dbObj, $this->characterId);
		}
		else {
			throw new exception(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function exception_handler($message) {
		$logId = $this->logger->log_by_class($message, 'exception in code');
		$this->characterObj->_exception_handler($message ." -- Logged (id #". $logId .")");
	}//end exception_handler()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function __get($var) {
		if(isset($this->$var)) {
			$returnThis = $this->$var;
		}
		else {
			$this->characterObj->_exception_handler(__METHOD__ .": unknown var (". $var .")");
		}
		return($returnThis);
	}//end __get()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		return($this->characterObj->get_sheet_data());
	}
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function build_sheet(cs_genericPage $page) {
		$data = $this->get_sheet_data();
		
		$blockRows = $page->rip_all_block_rows('content');
		$parsedSlots = array();
		foreach($page->templateRows as $n=>$garbage) {
			if(preg_match('/slot/i', $n)) {
				$parsedSlots[$n] = 0;
			}
		}
		
		$abilityList = $this->characterObj->abilityObj->get_ability_list();
		$abilityList = $abilityList['byId'];
		
		foreach($data as $name=>$val) {
			if(is_array($val)) {
				//there should be a template row named after the "$name"...
				$blockRowName = $name .'Slot';
				if($name == 'saves') {
					//changed name of the saves row so it doesn't get an extra row automatically...
					$blockRowName = 'characterSaveRow';
				}
				if(!isset($page->templateRows[$blockRowName])) {
					$this->characterObj->_exception_handler(__METHOD__ .": failed to parse data for (". $name ."), missing block row '". $blockRowName ."'");;
				}
				
				$parsedRows = '';
				$rowsParsed = 0;
				foreach($val as $id=>$subArray) {
					if(is_array($subArray)) {
						if($name == 'skills') {
							$subArray['abilityDropDown'] = $this->create_ability_select($page, $abilityList, $id, $subArray['skills__ability_name']);
						}
						
						$myBlockRow = $page->templateRows[$blockRowName];
						
						$subArray[$name .'_id'] = $id;
						
						$parsedRows .= $page->gfObj->mini_parser($myBlockRow, $subArray, '{', '}');
						$rowsParsed++;
						$parsedSlots[$blockRowName] = $rowsParsed;
					}
					else {
						$page->add_template_var($id, $subArray);
					}
				}
				if($rowsParsed > 0) {
					if(preg_match('/slot$/i', $blockRowName)) {
						//ends in "[sS]lot", add another row.
						$subArray = array();
						$subArray[$name .'_id'] = 'new';
						$subArray['addClassName'] = 'newRecord';
						
						if($name == 'skills') {
							$subArray['abilityDropDown'] = $this->create_ability_select($page, $abilityList, 'new');
						}
						$parsedRows .= $page->gfObj->mini_parser($myBlockRow, $subArray, '{', '}');
						$rowsParsed++;
					}
					
					//$parsedRows .= '{'. $blockRowName .'__newRecordRow}';
					$page->add_template_var($blockRowName, $parsedRows);
				}
			}
			else {
				$page->add_template_var($name, $val);
			}
		}
		foreach($parsedSlots as $name=>$numRecords) {
			if($numRecords== 0) {
				//get the "id" based on the name of the block row.
				$idPrefix = preg_replace('/slot$/i','', $name);
				
				$subArray = array(
					$idPrefix .'_id'	=> "new",
					'addClassName'		=> "newRecord"
				);
				if($name == 'skills') {
					$subArray['abilityDropDown'] = $this->create_ability_select($page, $abilityList, 'new');
				}
				$page->add_template_var($name, $page->gfObj->mini_parser($page->templateRows[$name], $subArray, '{', '}'));
			}
		}
		
		//build an ability list for adding new skills.
		#$page->add_template_var('newSkill__abilityDropDown', $this->create_ability_select($page, $abilityList));
		
		//add some version info & stuff.
		$page->add_template_var('CSBT_project_name', $this->characterObj->get_project());
		$page->add_template_var('CSBT_version', $this->characterObj->get_version());
	}//end build_sheet()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($sheetId, $val) {
		$result = $this->characterObj->handle_update($sheetId, null, $val);
		
		#return($this->characterObj->changesByKey);
		$retval = array(
			'result'	=> $result,
			'changesbykey'	=> $this->characterObj->changesByKey
		);
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Handles creation of new records; returns new data to populate page sections.
	 */
	public function handle_new_record($type, $name, array $extraData=null) {
		$retval = array();
		switch($type) {
			case 'gear':
				$retval['newRecordId'] = $this->characterObj->gearObj->create_gear($name, $extraData);
				break;

			case 'skills':
				if(isset($extraData['skills__ability_id'])) {
					$abilityId = $extraData['skills__ability_id'];
					unset($extraData['skills__ability_id']);
					$myExtraData = array();
					foreach($extraData as $k=>$v) {
						$k = preg_replace('/^'. $type .'__/', '', $k);
						$myExtraData[$k] = $v;
					}
					$abilityName = $this->characterObj->abilityObj->get_ability_name($abilityId);
					$retval['newRecordId'] = $this->characterObj->skillsObj->create_skill($name, $abilityName, $myExtraData);
				}
				break;

			case 'specialAbility':
				$retval['newRecordId'] = $this->characterObj->specialAbilityObj->create_special_ability($name, $extraData);
				break;
			
			case 'characterWeapon':
				$retval['newRecordId'] = $this->characterObj->weaponObj->create_weapon($name, $extraData);
				break;
			
			case 'characterArmor':
				$retval['newRecordId'] = $this->characterObj->armorObj->create_armor($name, $extraData);
				break;

			default:
				$this->characterObj->_exception_handler(__METHOD__ .": invalid type (". $type .")");
		}
		//update changes list so it can get to the sheet.
		$this->characterObj->process_updates_by_key();
		
		$retval['tableName'] = $type;
		$retval['hnr__type'] = $type;
		$retval['hnr__name'] = $name;
		$retval['changesbykey'] = $this->characterObj->changesByKey;
		
		if(is_array($extraData) && count($extraData)) {
			$xText = "";
			foreach($extraData as $k=>$v) {
				$xText = $this->gfObj->create_list($xText, $k ."==". $v);
			}
			$retval['hnr__extra'] = $xText;
		}
		return($retval);
	}//end handle_new_record()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_name() {
		$data = $this->characterObj->get_character_data();
		return($data['character_name']);
	}//end get_character_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function create_ability_select(cs_genericPage $page, array $abilityList, $skillId=null, $selectThis=null) {
		$abilityOptionList = $page->gfObj->array_as_option_list($abilityList, $selectThis);
		if(is_null($skillId)) {
			$skillId = 'new';
		}
		$optionListRepArr = array(
			'skill_id'						=> $skillId,
			'optionList'					=> $abilityOptionList
		);
		
		if(is_numeric($skillId)) {
		}
		else {
			$optionListRepArr['skills__selectAbility__extra'] = 'class="newRecord"';
			$optionListRepArr['skillNum'] = 'new';
			$optionListRepArr['skill_id'] = 'new';
		}
		$retval = $page->gfObj->mini_parser($page->templateRows['skills__selectAbility'], $optionListRepArr, '%%', '%%');
		return($retval);
	}//end create_ability_select()
	//-------------------------------------------------------------------------
}

?>
