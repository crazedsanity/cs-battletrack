<?php 

namespace battletrack\character;

use crazedsanity\database\Database;
use ErrorException;

class SpecialAbility extends \battletrack\basic\Data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_sa_table';
	const tableSeq  = 'csbt_character_sa_table_character_sa_id_seq';
	const pkeyField = 'character_sa_id';
	
	const sheetIdPrefix = 'specialAbility';
	
	
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
		$this->_useSheetIdSuffix = true;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(Database $dbObj, $characterId) {
		$sql = 'SELECT * FROM '. self::tableName .' WHERE character_id=:id ORDER BY '. self::pkeyField;
		$params = array('id'=>$characterId);
		
		try {
			$dbObj->run_query($sql, $params);
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
		}
		catch(Exception $e) {
			throw new ErrorException(__METHOD__ .":: failed to retrieve character weapons, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}
	//==========================================================================
}