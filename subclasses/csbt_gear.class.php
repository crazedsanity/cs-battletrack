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
	public function calculate_modfier($score) {
		if(is_numeric($score) && $score > 0) {
			$modifier = floor(($score -10)/2);
		}
		elseif(is_null($score)) {
			$modifier = null;
		}
		else {
			$this->_exception_handler(__METHOD__ .":: invalid score (". $score .")");
		}
		return($modifier);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_modifier() {
		return $this->calculate_modfier($this->_data['ability_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_temp_modifier() {
		return $this->calculate_modifier($this->_data['temporary_score']);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_weight() {
		$weight = $this->_data['weight'];
		
		if(isset($this->_data['quantity']) && is_numeric($this->_data['quantity']) && $this->_data['quantity'] > 0) {
			$weight = $this->_data['quantity'] * $this->_data['weight'];
		}
		
		return $weight;
	}
	//==========================================================================
}