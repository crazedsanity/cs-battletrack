<?php 

class csbt_save extends csbt_basicRecord {
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_save_table';
	const tableSeq  = 'csbt_character_save_table_character_save_id_seq';
	const pkeyField = 'character_save_id';
	
	
	//==========================================================================
	public function __construct(cs_phpDB $dbObj, array $initialData=array()) {
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $initialData);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_modifier() {
		$mod = 0;
		$addThese = array('ability_mod', 'base_mod', 'misc_mod', 'magic_mod', 'temp_mod');
		foreach($addThese as $idx) {
			if(isset($this->_data[$idx]) && is_numeric($this->_data[$idx])) {
				$mod += $this->_data[$idx];
			}
		}
		return $mod;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load() {
		if(!is_null($this->characterId) && $this->characterId > 0) { 
			$sql = "SELECT s.*, a.ability_name FROM " . self::tableName . " AS s "
					. "INNER JOIN ". csbt_ability::tableName ." AS a USING ("
					. csbt_ability::pkeyField .") WHERE "
					. "s.character_id=:id";
			$params = array(
				'id' => $this->characterId,
			);
			
			$sql .= " ORDER BY save_name";
			
			try {
				$this->dbObj->run_query($sql, $params);
				$retval = $this->dbObj->farray_fieldnames($this->pkeyField);

			} catch (Exception $e) {
				$this->_exception_handler(__METHOD__ . ":: failed to retrieve character saves, DETAILS::: " . $e->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": cannot load without characterId");
		}
		return($retval);
	}
	//==========================================================================

}