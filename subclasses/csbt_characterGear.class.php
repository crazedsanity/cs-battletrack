<?php

class csbt_characterGear extends csbt_battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	public $updatesByKey = array();
	
	const tableName = 'csbt_character_gear_table';
	const tableSeq  = 'csbt_character_gear_table_character_gear_id_seq';
	const pkeyField = 'character_gear_id';
	const joinTable = 'csbt_ability_table';
	const joinTableField = 'ability_id';
	const sheetIdPrefix = 'gear';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			$this->_exception_handler(__METHOD__ .":: invalid character id (". $characterId .")");
		}
		$this->characterId = $characterId;
		$this->fields = array(
			'character_id'		=> 'int',
			'gear_name'			=> 'sql',
			'weight'			=> 'decimal',
			'quantity'			=> 'int',
			'location'			=> 'sql'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->logger->logCategory = "Character Gear";
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_gear($name, array $fields=null) {
		if(is_null($name) || !strlen($name)) {
			$name = "  ";//allow blank records (for now) so they can have their "pretty spacing".
		}
		$insertArr = array();
		if(is_array($fields) && count($fields) > 0) {
			$insertArr = $fields;
		}
		$insertArr['gear_name'] = $name;
		$insertArr['character_id'] = $this->characterId;
		
		try {
			$newId = $this->tableHandlerObj->create_record($insertArr);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to create character gear (". $name ."), DETAILS:::: ". $e->getMessage());
		}
		
		return($newId);
	}//end create_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_gear($gearId, array $updates) {
		if(is_numeric($gearId) && $gearId > 0 && is_array($updates) && count($updates) > 0) {
			try {
				$retval = $this->tableHandlerObj->update_record($gearId, $updates, true);
			}
			catch(Exception $e) {
				$this->_exception_handler(__METHOD__ .":: failed to perform update, details::: ". $e->getMessage());
			}
		}
		else {
				$this->_exception_handler(__METHOD__ .":: invalid gearId (". $gearId .") or invalid/not enough fields");
		}
		return($retval);
	}//end update_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_gear_by_name($name) {
		try {
			$filterArr = array(
				'character_id'	=> $this->characterId,
				'gear_name'		=> $name
			);
			$data = $this->tableHandlerObj->get_single_record($filterArr);
			$data = $this->calculate_gear_weight($data);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve gear, DETAILS::: ". $e->getMessage());
		}
		
		return($data);
	}//end get_gear_by_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_gear_by_id($gearId) {
		try {
			$filterArr = array(
				'character_id'	=> $this->characterId,
				self::pkeyField	=> $gearId
			);
			$data = $this->tableHandlerObj->get_single_record($filterArr);
			$data = $this->calculate_gear_weight($data);
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve gear, DETAILS::: ". $e->getMessage());
		}
		
		return($data);
	}//end get_gear_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_gear() {
		
		try {
			$retval = $this->tableHandlerObj->get_records(array('character_id'=>$this->characterId));
			if(is_array($retval) && count($retval) > 0) {
				$retval = $this->calculate_gear_weight($retval);
			}
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve character gear, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end get_character_gear()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
		try {
			$data = $this->get_character_gear();
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve data, DETAILS::: ". $e->getMessage());
		}
		
		$retval = array();
		if(is_array($data) && count($data) > 0) {
			
			$makeKeysFrom = $this->get_columns_for_sheet_keys();
			$makeKeysFrom[] = 'total_weight';
			foreach($data as $id=>$gearData) {
				foreach($makeKeysFrom as $indexName) {
					if(array_key_exists($indexName, $gearData)) {
						$sheetKey = $this->create_sheet_id(self::sheetIdPrefix, $indexName);
						$retval[$id][$sheetKey] = $data[$id][$indexName];
					}
					else {
						$this->_exception_handler(__METHOD__ .":: failed to create key for missing index '". $indexName ."'");
					}
				}
			}
			$retval[$this->create_sheet_id(self::sheetIdPrefix, 'total_weight', 'generated')] = $this->get_total_weight();
		}
		
		return($retval);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		return(array());
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function handle_update($updateBitName, $recordId=null, $newValue) {
		
		try {
			//now perform the update.
			$retval = $this->update_gear($recordId, array($updateBitName => $newValue));
			$this->updatesByKey[$this->create_sheet_id(self::sheetIdPrefix, $updateBitName, $recordId)] = $newValue;
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to handle update, DETAILS::: ". $e->getMessage());
		}
		
		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_total_weight() {
		try {
			$allGear = $this->get_character_gear();
			$totalWeight = 0;
			if(is_array($allGear) && count($allGear)) {
				foreach($allGear as $id=>$gearInfo) {
					$totalWeight += $gearInfo['total_weight'];
				}
			}
			
		}
		catch(Exception $e) {
			$this->_exception_handler(__METHOD__ .":: failed to retrieve all character gear, DETAILS::: ". $e->getMessage());
		}
		return($totalWeight);
	}//end get_total_weight()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function calculate_gear_weight(array $data) {
		if(is_array($data) && count($data) > 0) {
			$keyList = array_keys($data);
			
			$retval = $data;
			if(is_numeric($keyList[0])) {
				foreach($retval as $id=>$gearInfo) {
					if(isset($gearInfo['quantity']) && is_numeric($gearInfo['quantity']) && isset($gearInfo['weight']) && is_numeric($gearInfo['weight'])) {
						$retval[$id]['total_weight'] = round(($gearInfo['quantity'] * $gearInfo['weight']),1);
					}
					else {
						$this->_exception_handler(__METHOD__ .":: invalid quantity (". $gearInfo['quantity'] .") or weight (". $gearInfo['weight'] .") for item #". $id);
					}
				}
			}
			elseif(isset($data['quantity']) && is_numeric($data['quantity']) && isset($data['weight']) && is_numeric($data['weight'])) {
				$retval = $data;
				$retval['total_weight'] = round(($data['quantity'] * $data['weight']),1);
			}
			else {
				$this->_exception_handler(__METHOD__ .":: missing data from array or invalid format, cannot calculate weight");
			}
		}
		else {
			$this->_exception_handler(__METHOD__ .":: invalid data, cannot calculate weight");
		}
		
		return($retval);
	}//end calculate_gear_weight()
	//-------------------------------------------------------------------------
}

?>
