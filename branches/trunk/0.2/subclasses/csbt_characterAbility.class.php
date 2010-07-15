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
	
	protected $dataCache=array();
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		
		if(is_numeric($characterId)) {
			$this->characterId = $characterId;
		}
		else {
			cs_debug_backtrace(1);
			throw new exception(__METHOD__ .":: invalid characterId (". $characterId .")");
		}
		
		$this->fields = array(
			'character_ability_id'		=> 'int',
			'character_id'				=> 'int',
			'ability_id'				=> 'int',
			'ability_score'				=> 'int',
			'temporary_score'			=> 'int'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->abilityObj = new csbt_ability($dbObj);
		$this->abilityObj->get_ability_list();
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_abilities() {
		try {
			$retval = $this->tableHandlerObj->get_records();
			
			foreach($retval as $i=>$arr) {
				$abilityName = $this->abilityObj->get_ability_name($arr['ability_id']);
				$retval[$i]['ability_name'] = $abilityName;
				
				$this->dataCache['abilities'][$abilityName] = array(
					'score'		=> $arr['ability_score'],
					'modifier'	=> $this->abilityObj->get_ability_modifier($arr['ability_score']),
					'temp'		=> $arr['temporary_score'],
					'temp_mod'	=> $this->abilityObj->get_ability_modifier($arr['temporary_score'])
				);
				$this->dataCache['idLinker'][$abilityName] = $arr['character_ability_id'];
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: unable to retrieve records, DETAILS:::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_abilities()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function create_ability($abilityId, $score, $temporaryScore=null) {
		$dataCache = $this->abilityObj->get_ability_list();
		if(isset($dataCache['byId'][$abilityId])) {
			$abilityName = $dataCache['byId'][$abilityId];
			$insertArr = array(
				'character_id'	=> $this->characterId,
				'ability_id'	=> $abilityId,
				'ability_score'	=> $score
			);
			
			$this->dataCache['abilities'][$abilityName] = array(
				'score'		=> $score,
				'modifier'	=> $this->abilityObj->get_ability_modifier($score)
			);
			
			if(!is_null($temporaryScore) && is_numeric($temporaryScore)) {
				$insertArr['temporary_score'] = $temporaryScore;
			}
			try {
				$recId = $this->tableHandlerObj->create_record($insertArr);
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
					$key = $this->create_sheet_id(self::sheetIdPrefix, $abilityName .'_'. $scoreIndex, $this->dataCache['idLinker'][$abilityName]);
					$retval[$key] = $scoreValue;
				}
			}
			
			//now get additional indexes for push/pull/drag/etc (base on strength)
			$strengthScore = $this->dataCache['abilities']['str']['score'];
			
			if($strengthScore <= 10) {
				$maxLoad = $strengthScore * 10;
			}
			else {
				//So a score of 17 would be::: 1.1487^(17-10)*100
				//	1.1487^7*100
				//	2.63904227623 * 100
				//	263.904227623  (or ~264) --> 265
				//
				//	19 would be::: 1.1487^(19-10)*100
				//	1.1487^9*100
				//	3.48224713389*100
				//	348.224713389  (or ~348) --> 350
				//	
				$maxLoad = (1.1487^($strengthScore - 10)) * 100;
			}
			$retval['TESTTHIS'] = $maxLoad;
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
			if($strScore <= 10) {
				$retval = $strScore * 10;
			}
			else {
				$firstNum = $strScore -10;
				$secondNum = pow(1.1487, $firstNum);
				$almostDone = (int)($secondNum * 100);
				
				
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
					//is this right?  Didn't find anything
					$roundingTo = 100;
				}
				$retval = round($almostDone/$roundingTo)*$roundingTo;
				
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
		$abilityList = $this->abilityObj->get_ability_list();
		return($abilityList['byId']);
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_character_defaults() {
		$defaults = $this->get_character_defaults();
		
		foreach($defaults as $id=>$name) {
			$this->create_ability($id, rand(6,20));
		}
		
		return($this->get_character_abilities());
	}//end load_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_modifier($mod) {
		return($this->abilityObj->get_ability_modifier($mod));
	}//end get_ability_modifier()
	//-------------------------------------------------------------------------
}

?>
