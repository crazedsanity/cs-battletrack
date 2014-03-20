<?php 

class csbt_armor extends csbt_data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_armor_table';
	const tableSeq  = 'csbt_character_armor_table_character_armor_id_seq';
	const pkeyField = 'character_armor_id';
	
	public $booleanFields = array('is_worn');
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(cs_phpDB $dbObj, $characterId) {
		
		$retval = array();
		
		$sql = "SELECT * FROM ". self::tableName ." WHERE character_id=:id";
		$params = array('id'=>$characterId);
		
		$rows = $dbObj->run_query($sql, $params);
		
		if($rows > 0) {
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
		}
		
		return $retval;
	}
	//==========================================================================
}