<?php 

class csbt_skill extends csbt_data {
	
	
	const tableName = 'csbt_character_skill_table';
	const tableSeq  = 'csbt_character_skill_table_character_skill_id_seq';
	const pkeyField = 'character_skill_id';
	
	public $booleanFields = array('is_class_skill');
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(cs_phpDB $dbObj, $characterId) {
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
			'id'	=> $characterId,
		);
		
		try {
			$dbObj->run_query($sql, $params);
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
			
			foreach($retval as $id=>$data) {
				$retval[$id]['ability_mod'] = csbt_ability::calculate_ability_modifier($data['ability_score']);
				$retval[$id]['skill_mod'] = self::calculate_skill_modifier($data);
			}
		}
		catch(Exception $e) {
			throw new ErrorException(__METHOD__ .":: failed to retrieve character skills, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
}
	
