<?php 

class csbt_skill extends csbt_basicRecord {
	
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load(array $crit=null) {
		$sql = 'SELECT t.*, t2.ability_name FROM '. self::tableName .' AS t INNER JOIN '. csbt_character::tableName .' AS t2 '
			. ' USING ('. csbt_character::pkeyField .') WHERE character_id=:id';
		
		$abilityId = null;
		if(!is_null($byAbilityName) && !is_numeric($byAbilityName) && strlen($byAbilityName)) {
			$abilityId =  $this->abilityObj->get_ability_id($byAbilityName);
		}
		$params = array(
			'id'	=> $this->characterId,
		);
		
		$sql .= ' ORDER BY skill_name';
		
		try {
			$this->dbObj->run_query($sql, $params);
			$retval = $this->dbObj->farray_fieldnames($this->pkeyField);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function calculate_modifier() {
		#array('ability_mod', 'ranks', 'misc_mod')
		$mod = 0;
		
		$mod += $this->_data['ability_mod'];
		$mod += $this->_data['ranks'];
		$mod += $this->_data['misc_mod'];
		
		return($mod);
	}
	//==========================================================================
}
	
