<?php

class csbt_basicRecord {
	
	protected $_data = array();
	
	public $id;
	public $characterId;
	public $dbObj;
	
	protected $_dbTable;
	protected $_dbSeq;
	protected $_dbPkey;
	
	//==========================================================================
	/**
	 * 
	 * @param type $dbTable
	 * @param type $dbSeq
	 * @param type $dbPkey
	 */
	public function __construct(cs_phpDB $dbObj, $dbTable, $dbSeq, $dbPkey, array $initialData=array()) {
		$this->dbObj = $dbObj;
		$this->_dbTable = $dbTable;
		$this->_dbSeq = $dbSeq;
		$this->_dbPkey = $dbPkey;
		$this->_data = $initialData;
		
		$this->gfObj = new cs_globalFunctions;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function __get($name) {
		$retval = null;
		if(isset($this->$name)) {
			$retval = $this->$name;
		}
		elseif(isset($this->_data[$name])) {
			$retval = $this->_data[$name];
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function update($index, $value) {
		$this->_data[$index] = $value;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	protected function _clean_data_array() {
		$data = $this->_data;
		if(isset($data['character_id'])) {
			unset($data['character_id']);
		}
		if(isset($data['uid'])) {
			unset($data['uid']);
		}
		return $data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function save() {
		if(is_numeric($this->id)) {
			$updateSql = "";
			$params = $this->_clean_data_array($this->_data);
			foreach($this->_data as $k=>$v) {
				$updateSql = $this->gfObj->create_list($updateSql, $k .'=:'. $k, ',');
			}

			$sql = "UPDATE ". $this->_dbTable ." SET ". $updateSql ." WHERE ". $this->_dbPkey ."=:id";

			$params['id'] = $this->id;

			try {
				$this->dbObj->run_update($sql, $params);
			} catch (Exception $ex) {
				throw new LogicException(__METHOD__ .": unable to update table '". $this->_dbTable ."', DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			$this->create();
		}
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load(array $crit=null) {
		$retval = array();
		
		$params = array('id' => $this->id);
		if(!is_null($this->id) && is_numeric($this->id)) {
			$sql = "SELECT * FROM ". $this->_dbTable ." WHERE ";//. $this->_dbPkey ."=:id";
			
			if(!is_null($crit) && is_array($crit)) {
				$params = $crit;
				$updateStr = "";
				foreach($crit as $k=>$v) {
					$updateStr = $this->gfObj->create_list($updateStr, $k ."=:". $k, ", ");
				}
				$sql .= $updateStr;
			}
			else {
				$sql = $this->_dbPkey ."=:id";
			}
			
			try {
				$rows = $this->dbObj->run_query($sql, $params);
				
				if($rows == 1) {
					$retval = $this->dbObj->get_single_record();
				}
				else {
					$retval = $this->dbObj->farray_fieldnames($this->_dbPkey);
				}
			} catch (Exception $ex) {
				throw new ErrorException(__METHOD__ .": failed to load data for (". $this->id ."), DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			throw new LogicException(__METHOD__ .": invalid characterId (". $this->id .")");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create() {
		
		$fields = $this->_data;//array_keys($this->_clean_data_array());
		$values = $fields;
		
		foreach($values as $k=>$v) {
			$values[$k] = ':'. $v;
		}
		
		$sql = "INSERT INTO " . $this->_dbTable . " (". implode(', ', $fields) .") VALUES 
				(". implode(', ', $values) .")";
		try {
			$this->id = $this->dbObj->run_insert($sql, $fields, $this->_dbSeq);
		} catch (Exception $e) {
			throw new ErrorException(__METHOD__ .": error creating record in '". $this->_dbTable ."', DETAILS::: ". $e->getMessage());
		}
		
		return $this->id;
	}
	//==========================================================================
}
