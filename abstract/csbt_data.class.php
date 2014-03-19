<?php

class csbt_data {
	
	protected $_dbTable;
	protected $_dbPkey;
	protected $_dbSeq;
	
	protected $_data = array();
	
	
	//==========================================================================
	public function __construct($initial, $table, $pkey, $seq, cs_phpDB $dbObj = null) {
		$this->_dbTable = $table;
		$this->_dbPkey = $pkey;
		$this->_dbSeq = $seq;
		
		if(!is_null($initial)) {
			if(is_array($initial) && count($initial)) {
				$this->_data = $initial;
			}
			elseif(is_numeric($initial)) {
				$x = new csbt_basicRecord($dbObj);
				$x->id = $initial;
				$this->_data = $x->load();
			}
		}
		else {
			throw new InvalidArgumentException(__METHOD__ .": required ID or data not given");
		}
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function __get($name) {
		
		$retval = null;
		switch(strtolower($name)) {
			case 'characterid':
			case 'character_id':
				if(is_array($this->_data) && isset($this->_data['character_id'])) {
					$retval = $this->_data['character_id'];
				}
				break;
				
			case 'data':
				$retval = $this->_data;
				break;
			
			case 'id':
				if(is_array($this->_data) && isset($this->_dbPkey) && isset($this->_data[$this->_dbPkey])) {
					$retval = $this->_data[$this->_dbPkey];
				}
				break;
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function save(cs_phpDB $db) {
		if(is_array($this->_data) && count($this->_data) > 0) {
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey, $this->_data);
			$x->id = $this->id;
			$retval = $x->save();
		}
		else {
			throw new ErrorException(__METHOD__ .": no data to save");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load(cs_phpDB $db) {
		if(is_array($this->_data) && count($this->_data) > 0) {
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$x->id = $this->id;
			$this->_data = $x->load();
		}
		else {
			throw new ErrorException(__METHOD__ .": no data to load");
		}
		
		return $this->_data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create(cs_phpDB $db, array $data) {
		if(is_array($data) && count($data) > 0) {
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$retval = $x->create($data);
			$this->load($db);
		}
		else {
			throw new ErrorException(__METHOD__ .": no data to load");
		}
		
		return $retval;
	}
	//==========================================================================
}

