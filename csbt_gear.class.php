<?php 

class csbt_gear extends csbt_data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all_character_gear(cs_phpDB $dbObj, $characterId) {
		$sql = "SELECT * FROM " . self::tableName . " WHERE character_id=:id";
		$params = array('id' => $characterId);
		
		try {
			$dbObj->run_query($sql, $params);
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
		} catch (Exception $ex) {
			throw new ErrorException(__METHOD__ . ": error while retrieving character gear, DETAILS::: " . $ex->getMessage());
		}
		
		return $retval;
	}
	//==========================================================================
}