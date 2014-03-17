<?php 

class csbt_skill extends csbt_basicRecord {
	
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	
	public $booleanFields = array('is_class_skill');
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all_character_skills() {
		$sql = 'SELECT 
					cs.*, a.ability_name, ca.ability_score, 
					ca.temporary_score 
				FROM csbt_character_skill_table AS cs 
					INNER JOIN csbt_ability_table AS a 
						ON (cs.ability_id=a.ability_id) 
					INNER JOIN csbt_character_ability_table AS ca 
						ON (cs.character_id=ca.character_id AND a.ability_id=ca.ability_id) 
				WHERE 
					cs.character_id=:id
				ORDER BY cs.skill_name';
		
		$params = array(
			'id'	=> $this->characterId,
		);
		
		try {
			$this->dbObj->run_query($sql, $params);
			$retval = $this->dbObj->farray_fieldnames(self::pkeyField);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
}
	
