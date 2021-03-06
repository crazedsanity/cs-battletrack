<?php

namespace battletrack\basic;

use crazedsanity\database\Database;

use \battletrack\basic\Record;

use \LogicException;
use \ErrorException;

class Data {
	
	protected $_dbTable;
	protected $_dbPkey;
	protected $_dbSeq;
	protected $_sheetIdPrefix;
	protected $_useSheetIdSuffix=false;
	
	protected $_data = array();
	protected $id;
	
	public $booleanFields = array();
	
	public $version;
	
	//==========================================================================
	public function __construct($initial, $table, $seq, $pkey, Database $dbObj = null) {
		$this->_dbTable = $table;
		$this->_dbPkey = $pkey;
		$this->_dbSeq = $seq;
		
		if(!is_null($initial)) {
			if(is_array($initial)) {
				$this->_data = $initial;
			}
			elseif(is_numeric($initial)) {
				$x = new Record($dbObj);
				$x->id = $initial;
				$this->_data = $x->load();
			}
			else {
				throw new LogicException(__METHOD__ .": invalid type for initial: ". gettype($initial));
			}
		}
		
//		$this->version = new cs_version();
//		$this->version->set_version_file_location(dirname(__FILE__) .'/../VERSION');
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
	public function save(Database $db) {
		if(is_array($this->_data) && count($this->_data) > 0) {
			$x = new Record($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey, $this->_data);
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
	public function load(Database $db, $id=null) {
		if(is_numeric($this->id) || is_numeric($id)) {
			$x = new Record($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
			$x->booleanFields = $this->booleanFields;
			if(!is_null($id) && is_numeric($id)) {
				$this->id = $id;
			}
			$x->id = $this->id;
			$this->_data = $x->load();
		}
		else {
			throw new ErrorException(__METHOD__ .": required ID not set (". $this->id ."[type=(". gettype($this->id) .")])... ");
		}
		
		return $this->_data;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function create(Database $db, array $data) {
		if(is_array($data)) {
			$x = new Record($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
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
	public function delete(Database $db) {
		if(is_array($this->_data) && count($this->_data) > 0 && is_numeric($this->id)) {
			$x = new Record($db, $this->_dbTable, $this->_dbSeq, $this->_dbPkey);
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
	public static function calculate_ability_modifier($score) {
		if(is_numeric($score) && $score > 0) {
			$modifier = floor(($score -10)/2);
		}
		elseif(is_null($score)) {
			$modifier = null;
		}
		else {
			throw new ErrorException(__METHOD__ .":: invalid score (". $score .")");
		}
		return($modifier);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function calculate_total_save_modifier(array $data) {
		$addThese = array('ability_mod', 'base_mod', 'misc_mod', 'magic_mod', 'temp_mod');
		if(is_array($data) && count($data) > count($addThese)) {
			$mod = 0;
			foreach($addThese as $idx) {
				if(isset($data[$idx]) && is_numeric($data[$idx])) {
					$mod += $data[$idx];
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
	public static function calculate_skill_modifier(array $data) {
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
	
	
	
	//==========================================================================
	public static function calculate_list_weight($itemList) {
		$totalWeight = 0;
		if(is_array($itemList) && count($itemList)) {
			foreach($itemList as $k=>$v) {
				$totalWeight += self::calculate_weight($v);
 			}
		}
		
		return $totalWeight;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function calculate_weight(array $itemData) {
		$totalWeight = 0;
		
		$qty = 1;
		if (isset($itemData['weight']) && is_numeric($itemData['weight'])) {
			if (isset($itemData['quantity']) && is_numeric($itemData['quantity']) && $itemData['quantity'] > 0) {
				$qty = $itemData['quantity'];
			}
			$totalWeight += round(($qty * $itemData['weight']), 1);
		}
		
		return $totalWeight;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_sheet_data(Database $dbObj, $characterId, $recordId=null) {
		$retval = array();
		if(!is_null($this->_sheetIdPrefix)) {
			if(!is_null($recordId) && is_numeric($recordId)) {
				$myData = $this->load($dbObj, $recordId);
			}
			else {
				$myData = $this->get_all($dbObj, $characterId);
			}
			$retval = $this->_get_sheet_data($myData, is_numeric($recordId));
		}
		else {
			throw new LogicException(__METHOD__ .": missing required sheetIdPrefix");
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	/**
	 * Allows for pre-processing of data.
	 * 
	 * @param array $myData		see self::get_all()
	 * @return type
	 */
	protected function _get_sheet_data(array $myData, $isSingleRecord=false) {
		$retval = array();
		
		if($isSingleRecord) {
			$data = $this->_get_record_extras($myData);

			foreach($data as $k=>$v) {
				$myId = $this->_sheetIdPrefix . '__' . $k;
				if($this->_useSheetIdSuffix) {
					$myId .= '__'. $this->id;
				}
				$retval[$myId] = $v;
			}
		}
		else {
			foreach ($myData as $id => $data) {
				$tData = array();
				$data = $this->_get_record_extras($data);
				foreach ($data as $k => $v) {
					$myId = $this->_sheetIdPrefix . '__' . $k;
					$tData[$myId] = $v;
				}
				$retval[$id] = $tData;
			}
		}
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	/**
	 * If an extending class has extra attributes to add, it should simply 
	 * override this method.  Calls to get_sheet_data() will then automatically 
	 * get those extra indexes for single and multiple records.
	 * 
	 * @param array $recordData
	 * @return array
	 */
	public static function _get_record_extras(array $recordData) {
		return $recordData;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function update_and_get_changes(Database $dbObj, array $updateFields, $recordId) {
		$originalData = $this->_get_sheet_data($this->load($dbObj, $recordId), true);
		foreach($updateFields as $k=>$v) {
			$this->update($k, $v);
		}
		$this->save($dbObj);
		$newData = $this->_get_sheet_data($this->load($dbObj), true); //$this->_get_record_extras($this->load($dbObj));
		
		
		$unchangedData = array_intersect_assoc($newData, $originalData);
		
		$changesByKey = $newData;
		foreach($unchangedData as $k=>$v) {
			unset($changesByKey[$k]);
		}
		
		return $changesByKey;
	}
	//==========================================================================
}

