<?php 

class csbt_gear extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_all_character_gear() {
		if(isset($this->characterId) && is_numeric($this->characterId) && $this->characterId > 0) {
			$sql = "SELECT * FROM ". self::tableName ." WHERE character_id=:id";
			$params = array('id'=>$this->characterId);
			
			try {
				$this->dbObj->run_query($sql, $params);
				$retval = $this->dbObj->farray_fieldnames($this->_dbPkey);
			} catch (Exception $ex) {
				throw new ErrorException(__METHOD__ .": error while retrieving character gear, DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": characterId required");
		}
		
		return $retval;
	}
	//==========================================================================
}