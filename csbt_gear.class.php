<?php 

class csbt_gear extends csbt_data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	
	const sheetIdPrefix = 'gear';
	
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(cs_phpDB $dbObj, $characterId) {
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
	
	
	
	//==========================================================================
	public function get_sheet_data(cs_phpDb $dbObj, $characterId) {
		
		$myData = self::get_all($dbObj, $characterId);
		if(is_array($myData) && count($myData)) {
			foreach($myData as $id=>$data) {
				$myData[$id]['total_weight'] = self::calculate_weight($data);
			}
		}
		
		$retval = parent::_get_sheet_data($myData);
		
		return $retval;
	}
	//==========================================================================
}