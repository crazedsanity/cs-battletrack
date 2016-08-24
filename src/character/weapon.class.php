<?php 

namespace battletrack\character;

use crazedsanity\database\Database;
use crazedsanity\core\ToolBox;

use ErrorException;

class Weapon extends \battletrack\basic\Data {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_weapon_table';
	const tableSeq  = 'csbt_character_weapon_table_character_weapon_id_seq';
	const pkeyField = 'character_weapon_id';
	const sheetIdPrefix = 'characterWeapon';
	
	public $booleanFields = array('in_use');
	//==========================================================================
	public function __construct(array $initialData=array()) {
		parent::__construct($initialData, self::tableName, self::tableSeq, self::pkeyField);
		$this->_sheetIdPrefix = self::sheetIdPrefix;
		$this->_useSheetIdSuffix = true;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	/**
	 * 
	 * @param bool/optional $onlyInUse	If specified, returns only those with the
	 *										given value in the "in_use" column.
	 * 
	 * @return type
	 * @throws ErrorException
	 */
	public static function get_all(Database $dbObj, $characterId, $onlyInUse=null) {
		$sql = 'SELECT * FROM '. self::tableName .' WHERE ';//'character_id=:id';
		
		$params = array(
			'character_id'	=> $characterId,
		);
		
		if(!is_null($onlyInUse) && is_bool($onlyInUse)) {
			$params['in_use'] = ToolBox::interpret_bool($onlyInUse, array('f', 't'));
		}
		
		$addThis = "";
		foreach(array_keys($params) as $n) {
			$addThis = ToolBox::create_list($addThis, $n .'=:'. $n, ' AND ');
		}
		$sql .= $addThis;
		
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