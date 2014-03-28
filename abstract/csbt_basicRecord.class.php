<?php

class csbt_basicRecord {
	
	protected $_data = array();
	
	public $id;
	public $characterId;
	public $dbObj;
	public $gfObj;
	public $booleanFields = array();
	
	protected $_dbTable;
	protected $_dbSeq;
	protected $_dbPkey;
	
	//==========================================================================
	/**
	 * 
	 * @param cs_phpDB $dbObj
	 * @param str $dbTable
	 * @param str $dbSeq
	 * @param str $dbPkey
	 * @param array $initialData (optional)
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
		elseif($name === 'data' || strtolower($name) == 'datacache') {
			$retval = $this->_data;
		}
		
		return $retval;
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
		$retval = false;
		if(is_numeric($this->id)) {
			$retval = $this->update();
		}
		else {
			$retval = $this->create($this->_data);
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load(array $crit=null, $sql=null) {
		$retval = array();
		
		$params = array('id' => $this->id);
		if(!is_null($this->id) && is_numeric($this->id)) {
			if(is_null($sql)) {
				$sql = "SELECT * FROM ". $this->_dbTable ." WHERE ";//. $this->_dbPkey ."=:id";
			}
			
			if(!is_null($crit) && is_array($crit)) {
				$params = $crit;
				$updateStr = "";
				foreach($crit as $k=>$v) {
					$updateStr = $this->gfObj->create_list($updateStr, $k ."=:". $k, ", ");
				}
				$sql .= $updateStr;
			}
			else {
				$sql .= $this->_dbPkey ."=:id";
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
			throw new LogicException(__METHOD__ .": invalid ID (". $this->id .")");
		}
		$this->_data = $retval;
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	/**
	 * 
	 * @param array $data
	 * @return int
	 * @throws ErrorException
	 */
	public function create(array $data) {
		
		if(is_array($data) && count($data) > 0) {
			$params = array();

			foreach($data as $k=>$v) {
				if(count($this->booleanFields) && in_array($k, $this->booleanFields)) {
					$data[$k] = $this->gfObj->interpret_bool($v, array('f', 't'));
				}
				$params[] = ':'. $k;
			}

			$sql = "INSERT INTO " . $this->_dbTable . " (". implode(', ', array_keys($data)) .") VALUES 
					(". implode(', ', $params) .")";
			try {
				$this->id = $this->dbObj->run_insert($sql, $data, $this->_dbSeq);
			} catch (Exception $e) {
				throw new ErrorException(__METHOD__ .": error creating record in '". $this->_dbTable ."', DETAILS::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": cannot create record in '". $this->_dbTable ."' with no data");
		}
		
		return $this->id;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function update() {
		$updateSql = "";
		$params = $this->_clean_data_array($this->_data);
		foreach ($params as $k => $v) {
			if (count($this->booleanFields) && in_array($k, $this->booleanFields)) {
				$params[$k] = $this->gfObj->interpret_bool($v, array('f', 't'));
			}
			$updateSql = cs_global::create_list($updateSql, $k . '=:' . $k, ',');
		}

		$sql = "UPDATE " . $this->_dbTable . " SET " . $updateSql . " WHERE " . $this->_dbPkey . "=:id";

		$params['id'] = $this->id;

		try {
			$this->dbObj->run_update($sql, $params);
			$retval = true;
		} catch (Exception $ex) {
			throw new LogicException(__METHOD__ . ": unable to update table '" . $this->_dbTable . "', DETAILS::: " . $ex->getMessage());
		}

		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function delete() {
		if(!is_null($this->id) && is_numeric($this->id)) {
			$sql = "DELETE FROM ". $this->_dbTable ." WHERE ". $this->_dbPkey ."=:id";
			$params = array('id'=>$this->id);
			
			try {
				$res = $this->dbObj->run_query($sql, $params);
			} catch (Exception $ex) {
				throw new ErrorException(__METHOD__ .": failed to delete record "
						. "with id=(". $this->id .") from table=("
						. $this->_dbTable ."), DETAILS::: ". $ex->getMessage());
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": missing ID to delete record from table '". $this->_dbTable ."'");
		}
		return $res;
	}
	//==========================================================================
}
