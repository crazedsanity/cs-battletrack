<?php 

namespace battletrack\character;

use crazedsanity\database\Database;

use ErrorException;
use Exception;

class Gear extends \battletrack\basic\Data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	
	const sheetIdPrefix = 'gear';
	
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
		$this->_useSheetIdSuffix = true;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(Database $dbObj, $characterId) {
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
	public static function _get_record_extras(array $recordData) {
		$recordData['total_weight'] = self::calculate_weight($recordData);
		return $recordData;
	}
	//==========================================================================
}