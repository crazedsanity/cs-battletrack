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
		$this->logger->logCategory = "Character Sheet";
		
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
			$this->exception_handler(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function exception_handler($message) {
		$logId = $this->logger->log_by_class($message, 'exception in code');
		throw new exception($message ." -- Logged (id #". $logId .")");
	}//end exception_handler()
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
	public function get_sheet_data() {
		return($this->characterObj->get_sheet_data());
	}
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function build_sheet(cs_genericPage $page, $templateFile) {
		$data = $this->get_sheet_data();
		
		$blockRows = $page->rip_all_block_rows('content');
		
		$abilityList = $this->characterObj->abilityObj->get_ability_list();
		$abilityList = $abilityList['byId'];
		
		$page->gfObj->debug_print($data,1);
		
		
		foreach($data as $name=>$val) {
			if(is_array($val)) {
				//there should be a template row named after the "$name"...
				$blockRowName = $name .'Slot';
				if(!isset($page->templateRows[$blockRowName])) {
					throw new exception(__METHOD__ .": failed to parse data for (". $name ."), missing block row '". $blockRowName ."'");;
				}
				
				$parsedRows = '';
				foreach($val as $id=>$subArray) {
					if(is_array($subArray)) {
						if($name == 'skills') {
							#$page->gfObj->debug_print($subArray,1);
							$abilityOptionList = $page->gfObj->array_as_option_list($abilityList, $subArray['skills__ability_name']);
							
							$optionListRepArr = array(
								'skillNum'		=> $id,
								'optionList'	=> $abilityOptionList
							);
							$subArray['abilityDropDown'] = $page->gfObj->mini_parser($page->templateRows['skills__selectAbility'], $optionListRepArr, '%%', '%%');
						}
						
						$myBlockRow = $page->templateRows[$blockRowName];
						
						$subArray[$name .'_id'] = $id;
						
						$parsedRows .= $page->gfObj->mini_parser($myBlockRow, $subArray, '{', '}');
					}
					else {
						$blockRowName = $id;
						$parsedRows = $subArray;
					}
				}
				$page->add_template_var($blockRowName, $parsedRows);
			}
			else {
				$page->add_template_var($name, $val);
			}
		}
		
		
	}//end build_sheet()
	//-------------------------------------------------------------------------
	
	
	
}

?>
