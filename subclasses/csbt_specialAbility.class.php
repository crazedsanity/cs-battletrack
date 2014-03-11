<?php 

class csbt_ability extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_ability_table';
	const tableSeq  = 'csbt_ability_table_ability_id_seq';
	const pkeyField = 'ability_id';
	
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
}