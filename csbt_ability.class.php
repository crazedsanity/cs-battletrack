<?php 

class csbt_ability extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_ability_table';
	const tableSeq  = 'csbt_character_ability_table_character_ability_id_seq';
	const pkeyField = 'character_ability_id';
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_modifier() {
		return $this->calculate_ability_modifier($this->_data['ability_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_temp_modifier() {
		return $this->calculate_ability_modifier($this->_data['temporary_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all_abilities() {
		$sql = "SELECT * FROM csbt_ability_table";
		
		try {
			$numrows = $this->dbObj->run_query($sql);
			
			if($numrows > 0) {
				$data = $this->dbObj->farray_nvp('ability_name', 'ability_id');
			}
			else {
				throw new LogicException(__METHOD__ .": no data available");
			}
		} catch (Exception $ex) {
			throw new ErrorException(__METHOD__ .": failed to retrieve cache, DETAILS::: ". $ex->getMessage());
		}
		
		return $data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all_character_abilities() {
		if(!is_null($this->characterId) && $this->characterId > 0) {

			$sql = "SELECT ca.*, a.ability_name FROM csbt_character_ability_table "
					. "AS ca INNER JOIN csbt_ability_table AS a USING (ability_id) "
					. "WHERE ca.character_id=:id";
			$params = array('id'=>$this->characterId);
			try {
				$this->dbObj->run_query($sql, $params);
				$data = $this->dbObj->farray_fieldnames('ability_name');
			} catch (Exception $ex) {
				throw new ErrorException(__METHOD__ .": failed to retrieve cache, DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": characterId required");
		}
		
		
		return $data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create_character_defaults($minScore = 6, $maxScore = 18) {
		$retval = 0;
		if (!is_null($this->characterId) && $this->characterId > 0) {
			if (!is_numeric($minScore) || $minScore < 1) {
				throw new InvalidArgumentException(__METHOD__ . ": invalid minimum score (" . $minScore . ")");
			}
			if (!is_numeric($maxScore) || $maxScore < 1) {
				throw new InvalidArgumentException(__METHOD__ . ": invalid max score (" . $maxScore . ")");
			}
			$abilityList = $this->get_all_abilities();
			
			foreach ($abilityList as $n => $v) {
				$data = array(
					'character_id'	=> $this->characterId,
					'ability_id'	=> $v,
					'ability_score'	=> rand($minScore, $maxScore),
				);
				$res = $this->create($data);
				$retval++;
			}
		} else {
			throw new ErrorException(__METHOD__ . ": characterId required");
		}
		return $retval;
	}
	//==========================================================================
}