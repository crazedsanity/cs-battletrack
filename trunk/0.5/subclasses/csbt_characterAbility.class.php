<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

class csbt_characterAbility extends csbt_battleTrackAbstract {
	
	protected $characterId;
	protected $fields;
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_ability_table';
	const tableSeq  = 'csbt_character_ability_table_character_ability_id_seq';
	const pkeyField = 'character_ability_id';
	const sheetIdPrefix = 'characterAbility';
	
	private $lastCall=null;
	
	protected $dataCache=array();
	
	protected $updatesByKey = array();
	
	protected $baseAbilityCache = null;
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId=null) {
		
		if(is_numeric($characterId)) {
			$this->characterId = $characterId;
		}
		
		$this->fields = array(
			'character_ability_id'		=> 'int',
			'character_id'				=> 'int',
			'ability_id'				=> 'int',
			'ability_score'				=> 'int',
			'temporary_score'			=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		//NOTE::: the call to 'parent::__construct(...)' was removed because it was causing segmentation faults & memory issues... yuck.
		#parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt=1;
		
		if(is_object($dbObj) && get_class($dbObj) == 'cs_phpDB') {
			$this->dbObj = $dbObj;
		}
		else {
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		
		$this->pkeyField = self::pkeyField;
		$this->tableHandlerObj = new csbt_tableHandler($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields, $this->characterId);
		
		$baseAbilityFields = array(
			'ability_id'		=> 'int',
			'ability_name'		=> 'sql'
		);
		$this->baseAbilityObj = new csbt_ability($this->dbObj);
		$this->baseAbilityObj->get_ability_list();
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_abilities() {
		try {
			$retval = $this->tableHandlerObj->get_records(array('character_id' => $this->characterId));
			
			if(is_array($retval)) {
				foreach($retval as $i=>$arr) {
					$abilityName = $this->baseAbilityObj->get_ability_name($arr['ability_id']);
					$retval[$i]['ability_name'] = $abilityName;
					
					$this->dataCache['abilities'][$abilityName] = array(
						'score'		=> $arr['ability_score'],
						'modifier'	=> $this->baseAbilityObj->get_ability_modifier($arr['ability_score']),
						'temp'		=> $arr['temporary_score'],
						'temp_mod'	=> $this->baseAbilityObj->get_ability_modifier($arr['temporary_score'])
					);
					$this->dataCache['idLinker'][$abilityName] = $arr['character_ability_id'];
				}
			}
			else {
				cs_debug_backtrace(1);
				$this->gfObj->debug_print($this->tableHandlerObj,1);
				throw new exception(__METHOD__ .":: unable to retrieve records, DETAILS:::: ". $e->getMessage());
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: unable to retrieve records, DETAILS:::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_abilities()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_ability($abilityId, $score, $temporaryScore=null) {
		$dataCache = $this->baseAbilityObj->get_ability_list();
		if(isset($dataCache['byId'][$abilityId])) {
			$abilityName = $dataCache['byId'][$abilityId];
			$insertArr = array(
				'character_id'	=> $this->characterId,
				'ability_id'	=> $abilityId,
				'ability_score'	=> $score
			);
			
			$this->dataCache['abilities'][$abilityName] = array(
				'score'		=> $score,
				'modifier'	=> $this->baseAbilityObj->get_ability_modifier($score)
			);
			
			if(!is_null($temporaryScore) && is_numeric($temporaryScore)) {
				$insertArr['temporary_score'] = $temporaryScore;
			}
			try {
				$recId = $this->tableHandlerObj->create_record($insertArr);
				
				//TODO: should this store an entry into $this->updatesByKey?
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .":: failed to create record::: ". $e->getMessage());
			}
			
		}
		else {
			throw new exception(__METHOD__ .":: invalid abilityId (". $abilityId .")");
		}
		
		return($recId);
	}//end create_ability()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		$this->get_character_abilities();
		if(is_array($this->dataCache['abilities'])) {
			$retval = array();
			foreach($this->dataCache['abilities'] as $abilityName=>$arr) {
				foreach($arr as $scoreIndex => $scoreValue) {
					$key = $this->create_sheet_id(self::sheetIdPrefix, $abilityName .'_'. $scoreIndex);
					$retval[$key] = $scoreValue;
				}
			}
			
			$extraStats = $this->get_strength_stats($this->dataCache['abilities']['str']['score']);
			$retval = array_merge($extraStats, $retval);
			
		}
		else {
			throw new exception(__METHOD__ .":: missing internal data cache");
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_max_load($strScore) {
		
		//for the most part, the formulas for calculating max load was pulled from http://www.superdan.net/download/mathformulas.rtf
		//	For tremendous strength, clues to the formula were pulled from d20srd.org (http://www.d20srd.org/srd/carryingCapacity.htm) and
		//	from http://www.superdan.net/download/superabilities.rtf
		
		if(is_numeric($strScore) && $strScore > 0) {
			
			//round UP to the nearest 5, 10, 20, or 40 (in steps of 5 strength)
			$roundingSteps = array(
				1	=> 5,
				2	=> 10,
				3	=> 20,
				4	=> 40
			);
			
			$useRoundingStep = ceil(($strScore -10)/5);
				
			if(isset($roundingSteps[$useRoundingStep])) {
				$roundingTo = $roundingSteps[$useRoundingStep];
			}
			else {
				//is this right?  Didn't find anything...
				$roundingTo = 100;
			}
			$firstNum = $strScore -10;
			$secondNum = pow(1.1487, $firstNum);
			$almostDone = (int)($secondNum * 100);
			$retval = round($almostDone/$roundingTo)*$roundingTo;
			
			if($strScore <= 10) {
				$retval = $strScore * 10;
			}
			elseif($strScore > 29) {
				$exp = ($strScore/10)-2;
				if($exp < 1) {
					$exp = 1;
				}
				$exp = floor($exp);
				
				$lastNum = substr($strScore, -1);
				$useScore = 20 + $lastNum;
				
				$this->lastCall = __METHOD__;
				$multiplyThis = $this->get_max_load($useScore);
				$this->lastCall = null;
				
				$multiplyBy = pow(4, $exp);
				$retval = ($multiplyThis * pow(4, $exp));
				
			}
		}
		else {
			throw new exception(__METHOD__ .":: invalid strength score (". $strScore .")");
		}
		return($retval);
	}//end get_max_load()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		$abilityList = $this->baseAbilityObj->get_ability_list();
		return($abilityList['byId']);
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_character_defaults() {
		$defaults = $this->get_character_defaults();
		
		foreach($defaults as $id=>$name) {
			if($this->baseAbilityObj->get_ability_name($id) == 'str') {
				$abilityScore = 16;
			}
			elseif($this->baseAbilityObj->get_ability_name($id) == 'int') {
				$abilityScore = 6;
			}
			else {
				$abilityScore = rand(6,20);
			}
			$this->create_ability($id, $abilityScore);
		}
		
		return($this->get_character_abilities());
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_modifier($abilityName) {
		if(is_numeric($abilityName)) {
			//trying to get it based on a number...
			$retval = $this->baseAbilityObj->get_ability_modifier($abilityName);
		}
		else {
			if(!is_array($this->dataCache) || !count($this->dataCache)) {
				if($this->lastCall == __METHOD__) {
					throw new exception(__METHOD__ .":: failed to create cache, stopping to avoid recursion");
				}
				else {
					$this->lastCall = __METHOD__;
					$this->get_character_abilities();
					$this->lastCall = null;
					$retval = $this->get_ability_modifier($abilityName);
				}
			}
			else {
				if(strlen($abilityName) >= 3 && is_array($this->dataCache) && isset($this->dataCache['abilities'][$abilityName]['score'])) {
					$retval = $this->baseAbilityObj->get_ability_modifier($this->dataCache['abilities'][$abilityName]['score']);
				}
				else {
					throw new exception(__METHOD__ .":: cannot find cached value for (". $abilityName .")");
				}
			}
		}
		return($retval);
	}//end get_ability_modifier()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_list() {
		return($this->baseAbilityObj->get_ability_list());
	}
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_strength_stats($strengthScore) {
		$retval = array();
		
		//now get additional indexes for push/pull/drag/etc (base on strength)
		if(is_numeric($strengthScore) && $strengthScore > 0) {
			
			$maxLoad = $this->get_max_load($strengthScore);
			$minLoad = (int)floor($maxLoad /3);		// one third (1/3) heavy load
			$medLoad = (int)floor($minLoad * 2);		// two thirds (2/3) heavy load
			
			
			
			$retval[$this->create_sheet_id('generated', 'load_light')] = $minLoad;
			$retval[$this->create_sheet_id('generated', 'load_medium')] = $medLoad;
			$retval[$this->create_sheet_id('generated', 'load_heavy')] = $maxLoad;
			
			//other things...
			$retval[$this->create_sheet_id('generated', 'lift_over_head')] = $maxLoad;
			$retval[$this->create_sheet_id('generated', 'lift_off_ground')] = (int)floor($maxLoad *2);
			$retval[$this->create_sheet_id('generated', 'push_pull_drag')] = (int)floor($maxLoad *5);
		}
		else {
			throw new exception(__METHOD__ .":: invalid strength score (". $strengthScore .")");
		}
		return($retval);
	}//end get_strength_stats()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_score($abilityName, $isTemp=false) {
		if(is_array($this->dataCache['abilities']) && isset($this->dataCache['abilities'][$abilityName])) {
			
			$retval = $this->dataCache['abilities'][$abilityName]['score'];
			if($isTemp === true) {
				$retval = $this->dataCache['abilities'][$abilityName]['temp'];
			}
		}
		else {
			if($this->lastCall == __METHOD__) {
				throw new exception(__METHOD__ .":: failed to retrieve cached score");
			}
			else {
				$this->get_character_abilities();
				$this->lastCall = __METHOD__;
				$retval = $this->get_ability_score();
				$this->lastCall = null;
			}
		}
		
		return($retval);
	}//end get_ability_score()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Handles ability updates.
	 * 
	 * @see abstract/csbt_battleTrackAbstract#handle_update($sheetBitName, $recId, $newValue)
	 */
	public function handle_update($updateBitName, $id=null, $newValue) {
		if(func_num_args() == 3) {
			$retval = false;
			
			$updateBits = explode('_', $updateBitName);
			if(count($updateBits) == 2) {
				try {
					$abilityId = $this->baseAbilityObj->get_ability_id($updateBits[0]);
					$abilityName = $this->baseAbilityObj->get_ability_name($abilityId);
				}
				catch(Exception $e) {
					throw new exception(__METHOD__ .":: unable to locate ability_id for (". $updateBits[0] ."), DETAILS::: ". $e->getMessage());
				}
				
				//got the ID, keep going.
				if(!preg_match('/mod/', $updateBits[1])) {
					try {
						if(preg_match('/^temp/', $updateBits[1])) {
							$fieldToUpdate = 'temporary_score';
						}
						elseif(preg_match('/^ability/', $updateBits[1]) || preg_match('/^score$/', $updateBits[1])) {
							if(!is_null($newValue) && is_numeric($newValue)) {
								$fieldToUpdate = 'ability_score';
							}
							else {
								cs_debug_backtrace(1);
								throw new exception("invalid new value (". $newValue .")");
							}
						}
						else {
							//don't add method name, as it will get caught inside this method anyway.
							throw new exception("unknown field (". $updateBits[1] .")");
						}
						
						//attempt the update.
						$recordId = $this->dataCache['idLinker'][$updateBits[0]];
						$retval = $this->tableHandlerObj->update_record($recordId, array($fieldToUpdate=>$newValue), false);
						
						//TODO: add entr(ies) to $this->updateByKeys[{sheetIdKey}]
					}
					catch(Exception $e) {
						throw new exception(__METHOD__ .":: error while attempting update, DETAILS::: ". $e->getMessage());
					}
				}
				else {
					throw new exception(__METHOD__ .":: FATAL: cannot update modifiers (". $updateBits[1] .") directly");
				}
			}
			else {
				throw new exception(__METHOD__ .":: wrong number of bits in (". $updateBitName ."), expecting 2 but found (". count($updateBits) .")");
			}
		}
		else {
			throw new exception(__METHOD__ .":: not enough arguments to continue::: ". $this->gfObj->debug_var_dump(func_get_args(),0));
		}
		
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function get_ability_id($name) {
		
		if(is_null($this->dataCache)) {
			$this->baseAbilityObj->get_ability_list();
		}
		
		//it is a long string... rip out the first 3 characters.
		if(strlen($name) > 3) {
			$name = substr($name,0,3);
		}
		
		if(isset($this->baseAbilityObj->dataCache['byName'][$name])) {
			$retval = $this->baseAbilityObj->dataCache['byName'][$name];
		}
		else {
			$this->gfObj->debug_print($this->baseAbilityObj->dataCache,1);
			throw new exception(__METHOD__ .":: invalid name (". $name .")");
		}
		
		return($retval);
	}//end get_ability_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * @param $id	ability_id
	 */
	public function get_ability_name($id) {
		if(isset($this->baseAbilityObj->dataCache['byId'][$id])) {
			$retval = $this->baseAbilityObj->dataCache['byId'][$id];
		}
		else {
			throw new exception(__METHOD__ .":: invalid id (". $id .")");
		}
		
		return($retval);
	}
	//-------------------------------------------------------------------------
}

?>
