<?php

class csbt_data {
	
	protected $_dbTable;
	protected $_dbPkey;
	protected $_dbSeq;
	
	protected $_data = array();
	protected $id;
	
	public $booleanFields = array();
	
	//==========================================================================
	public function __construct($initial, $table, $seq, $pkey, cs_phpDB $dbObj = null) {
		$this->_dbTable = $table;
		$this->_dbPkey = $pkey;
		$this->_dbSeq = $seq;
		
		if(!is_null($initial)) {
			if(is_array($initial)) {
				$this->_data = $initial;
			}
			elseif(is_numeric($initial)) {
				$x = new csbt_basicRecord($dbObj);
				$x->id = $initial;
				$this->_data = $x->load();
			}
			else {
				throw new LogicException(__METHOD__ .": invalid type for initial: ". gettype($initial));
			}
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
			case 'datacache':
				$retval = $this->_data;
				break;
			
			case 'id':
				$retval = $this->id;
				break;
			
			default:
				if(array_key_exists($name, $this->_data)) {
					$retval = $this->_data[$name];
				}
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function __set($name, $value) {
		switch($name) {
			case 'id':
				$this->id = $value;
				break;
			
			case 'characterId':
			case 'character_id':
				$this->_data['character_id'] = $value;
				break;
		}
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function update($index, $value) {
		$this->mass_update(array($index=>$value));
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function mass_update(array $data) {
		foreach($data as $f=>$v) {
			$this->_data[$f] = $v;
		}
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function save(cs_phpDB $db) {
		if(is_array($this->_data) && count($this->_data) > 0) {
			//__construct(cs_phpDB $dbObj, $dbTable, $dbSeq, $dbPkey, array $initialData=array())
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey, $this->_data);
			$x->booleanFields = $this->booleanFields;
			$x->id = $this->id;
			
			$retval = $x->save();
			
			if(is_numeric($retval)) {
				$this->id = $retval;
			}
		}
		else {
			throw new ErrorException(__METHOD__ .": no data to save");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function load(cs_phpDB $db) {
		if(is_numeric($this->id)) {
			//__construct(cs_phpDB $dbObj, $dbTable, $dbSeq, $dbPkey, array $initialData=array())
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$x->booleanFields = $this->booleanFields;
			$x->id = $this->id;
			$this->_data = $x->load();
		}
		else {
			$boolVal = is_numeric($this->id);
			throw new ErrorException(__METHOD__ .": required ID not set (". $this->id ." [$boolVal][type=(". gettype($this->id) .")])... ");
		}
		
		return $this->_data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create(cs_phpDB $db, array $data) {
		if(is_array($data)) {
			//__construct(cs_phpDB $dbObj, $dbTable, $dbSeq, $dbPkey, array $initialData=array())
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$x->booleanFields = $this->booleanFields;
			$this->id = $x->create($data);
//			$this->load($db);
		}
		else {
			throw new ErrorException(__METHOD__ .": no data to load");
		}
		
		return $this->id;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function delete(cs_phpDB $db) {
		if(is_array($this->_data) && count($this->_data) > 0 && is_numeric($this->id)) {
			$x = new csbt_basicRecord($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$x->booleanFields = $this->booleanFields;
			$x->id = $this->id;
			$retval = $x->delete();
		}
		else {
			throw new ErrorException(__METHOD__ .": missing required ID");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function calculate_ability_modifier($score) {
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
	public function calculate_total_save_modifier(array $data) {
		$addThese = array('ability_mod', 'base_mod', 'misc_mod', 'magic_mod', 'temp_mod');
		if(is_array($data) && count($data) > count($addThese)) {
			$mod = 0;
			foreach($addThese as $idx) {
				if(isset($this->_data[$idx]) && is_numeric($this->_data[$idx])) {
					$mod += $this->_data[$idx];
				}
			}
		}
		else {
			throw new InvalidArgumentException(__METHOD__ .": missing indexes in array");
		}
		return $mod;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function calculate_skill_modifier(array $data) {
		$mod = 0;
		if(is_array($data) && count($data) > 0) {
			$bits = array('ability_mod', 'ranks', 'misc_mod');
			
			foreach($bits as $k) {
				if(isset($data[$k]) && is_numeric($data[$k])) {
					$mod += $data[$k];
				}
			}
		}
		return($mod);
	}
	//==========================================================================
}

