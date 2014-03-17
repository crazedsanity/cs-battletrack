<?php 

class csbt_specialAbility extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_sa_table';
	const tableSeq  = 'csbt_character_sa_table_character_sa_id_seq';
	const pkeyField = 'character_sa_id';
	
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all() {
		$sql = 'SELECT * FROM '. self::tableName .' WHERE character_id=:id';
		$params = array('id'=>$this->characterId);
		
		try {
			$this->dbObj->run_query($sql, $params);
			$retval = $this->dbObj->farray_fieldnames(self::pkeyField);
		}
		catch(Exception $e) {
			throw new ErrorException(__METHOD__ .":: failed to retrieve character weapons, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
}