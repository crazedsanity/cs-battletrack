<?php
/*
 * Created on Jul 13, 2009
 *
 */

class characterSheet {
	
	private $characterId;
	
	private $dbObj;
	
	private $dataCache=array();
	
	//-------------------------------------------------------------------------
	public function __construct($characterId=null) {
		
		if(class_exists('cs_globalFunctions')) {
			$this->gfObj = new cs_globalFunctions;
			$this->gfObj->debugPrintOpt=1;
		}
		else {
			throw new exception(__METHOD__ .": missing required class 'cs_globalFunctions'");
		}
		
		$dbParams = array(
			'host'			=> constant('DB_PG_HOST'),
			'dbname'		=> constant('DB_PG_DBNAME'),
			'port'			=> constant('DB_PG_PORT'),
			'user'			=> constant('DB_PG_DBUSER'),
			'password'		=> constant('DB_PG_DBPASS')
		);
		$this->dbObj = new cs_phpDB('pgsql');
		$this->dbObj->connect($dbParams);
		
		$this->characterId = $characterId;
		
		if(is_numeric($this->characterId)) {
			$this->get_character_data();
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_character_id($id) {
		if(is_numeric($id)) {
			$this->characterId = $id;
			$this->get_character_data();
		}
		else {
			throw new exception(__METHOD__ .": invalid characterId (". $id .")");
		}
	}//end set_character_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_character($characterName, $uid) {
		if(strlen($characterName) && is_numeric($uid) && $uid > 0) {
			$sql = "INSERT INTO csbt_character_table ". 
				$this->gfObj->string_from_array(array(
					'character_name'	=> $characterName,
					'uid'				=> $uid
				), 'insert');
			$this->set_character_id($this->dbObj->run_insert($sql, 'csbt_character_table_character_id_seq'));
		}
		else {
			throw new exception(__METHOD__ .": invalid name (". $characterName .") or uid (". $uid .")");
		}
		
		return($this->characterId);
	}//end create_character()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT * FROM csbt_character_attribute_table ".
					"WHERE character_id=". $this->characterId, 'character_attribute_id');
			
			$this->dataCache = array();
			if(is_array($data)) {
				foreach($data as $id=>$attribs) {
					$this->dataCache[$this->get_attribute_key($attribs)] = array(
						'value'	=> $attribs['attribute_value'],
						'id'	=> $id
					);
				}
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid internal characterId");
		}
		
		return($this->dataCache);
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_character_data(array $attribs) {
		$this->get_character_data();
		$totalCount = count($attribs);
		$finalCount = 0;
		$this->dbObj->beginTrans();
		foreach($attribs as $type=>$subData) {
			if(is_array($subData)) {
				foreach($subData as $subtype=>$finalBit) {
					if(is_array($finalBit)) {
						foreach($finalBit as $name=>$value) {
							$this->handle_attrib($type, $subtype, $name, $value);
							$finalCount++;
						}
					}
					else {
						$name = null;
						$this->handle_attrib($type, $subtype, $name, $finalBit);
						$finalCount++;
					}
				}
			}
			else {
				#$this->gfObj->debug_print(__METHOD__ .": XXXXXXXXXXXtype=(". $type ."), subtype=(". $subData .")",1);
				throw new exception(__METHOD__ .": invalid data under (". $type ."):: ". $attribs);
			}
		}
		
		$this->get_character_data();
		
		$this->dbObj->commitTrans();
		
		return($finalCount);
	}//end update_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function insert_attrib($type, $subtype, $name, $value) {
		if(is_null($name) || !strlen($name)) {
			$name = "";
		}
		$sql = "INSERT INTO csbt_character_attribute_table ".
			$this->gfObj->string_from_array(array(
				'character_id'		=> $this->characterId,
				'attribute_type'	=> $type,
				'attribute_subtype'	=> $subtype,
				'attribute_name'	=> $name,
				'attribute_value'	=> $value
			), 'insert');
		try {
			$retval = $this->dbObj->run_insert($sql, 'csbt_character_attribute_table_character_attribute_id_seq');
			if(!is_numeric($retval) || $retval < 1) {
				throw new exception(__METHOD__ .": failed to create attribute for data::: ". $this->gfObj->debug_print(func_get_args(),0));
			}
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": error encountered::: ". $e->getMessage());
		}
		return($retval);
	}//end insert_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function get_attrib($type, $subtype, $name, $value) {
		//check the internal cache before going to the database (saves time)
		$dataArr = array(
			'character_id'		=> $this->characterId,
			'attribute_type'	=> $type,
			'attribute_subtype'	=> $subtype,
			'attribute_name'	=> $name
		);
		$cacheKey = $this->get_attribute_key($dataArr);
		$result = null;
		if(isset($this->dataCache[$cacheKey])) {
			$dataArr = array(
				'attribute_value'	=> $value
			);
			$result = $this->dataCache[$cacheKey];
		}
		else {
			unset($dataArr['attribute_value']);
			$sql = "SELECT * FROM csbt_character_attribute_table WHERE ".
				$this->gfObj->string_from_array($dataArr, 'select');
			
			try {
				$result = $this->dbObj->run_query($sql, 'character_attribute_id');
				$numrows = $this->dbObj->numRows();
				if($numrows > 1) {
					throw new exception(__METHOD__ .": multiple rows (". $numrows .") detected::: " .
							$this->gfObj->debug_print(func_get_args(),0) ."<br>SQL::: ". $sql);
				}
			}
			catch(exception $e) {
				throw new exception(__METHOD__ .": failed to retrieve attribute::: ". $e->getMessage());
			}
		}
		
		return($result);
	}//end get_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function get_attribute_key(array $data) {
		$key = $data['attribute_type'] .'-'. $data['attribute_subtype'];
		if(strlen($data['attribute_name'])) {
			$key .= '-'. $data['attribute_name'];
		}
		return($key);
	}//end get_attribute_key()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_attrib($id, array $updates) {
		if(is_array($updates) && count($updates) && is_numeric($id) && is_numeric($this->characterId)) {
			$this->gfObj->switch_force_sql_quotes(true);
			$sql = "UPDATE csbt_character_attribute_table SET " .
					$this->gfObj->string_from_array($updates, 'update', null, 'sql', true) .
					" WHERE character_id=". $this->characterId ." AND " .
					"character_attribute_id=". $id;
			$this->gfObj->switch_force_sql_quotes(false);
			$this->dbObj->run_update($sql);
		}
		else {
			throw new exception(__METHOD__ .": no updates");
		}
	}//end update_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_main_character_data() {
		if(is_numeric($this->characterId)) {
			$data = $this->dbObj->run_query("SELECT * FROM csbt_character_table " .
					"WHERE character_id=". $this->characterId);
		}
		else {
			throw new exception(__METHOD__ .": invalid characterId");
		}
		return($data);
	}//end get_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_main_character_data(array $data) {
		if(is_numeric($this->characterId)) {
			if(is_array($data) && count($data)) {
				$sql = "UPDATE csbt_character_table SET " .
						$this->gfObj->string_from_array($data, 'update', null, 'sql') .
						" WHERE character_id=". $this->characterId;
				$updateRes = $this->dbObj->run_update($sql);
			}
			else {
				throw new exception(__METHOD__ .": invalid data");
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid characterId");
		}
		
		return($updateRes);
	}//end update_main_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function delete_attrib($id) {
		if(is_numeric($this->characterId)) {
			if(is_numeric($id) && $id > 0) {
				$result = $this->dbObj->run_update("DELETE FROM csbt_character_attribute_table WHERE " .
						"character_id=". $this->characterId ." AND character_attribute_id=". $id);
			}
		}
		else {
			throw new exception(__METHOD__ .": characterId not set");
		}
		
		return($result);
	}//end delete_attrib();
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function handle_attrib($type, $subtype, $name, $value) {
		$attribData = $this->get_attrib($type, $subtype, $name, $value);
		$result = false;
		if(is_numeric($attribData['id']) && $value !== $attribData['value']) {
			if(is_null($value) || !strlen($value)) {
				$this->gfObj->debug_print(__METHOD__ .": deleting value (". $attribData['id'] .")". $this->gfObj->debug_print($attribData,0));
				$this->delete_attrib($attribData['id']);
			}
			else {
				$this->gfObj->debug_print(__METHOD__ .": updating...". $this->gfObj->debug_print($attribData,0));
				$this->update_attrib(
					$attribData['id'], 
					array(
						'attribute_type'	=> $type,
						'attribute_subtype'	=> $subtype,
						'attribute_name'	=> $name,
						'attribute_value'	=> $value
					)
				);
			}
		}
		elseif(!is_null($value) && strlen($value) && !is_array($attribData)) {
				$this->gfObj->debug_print(__METHOD__ .": inserting... ". $this->gfObj->debug_print($attribData,0));
			$this->insert_attrib($type, $subtype, $name, $value);
		}
		
		return($result);
	}//end handle_attrib()
	//-------------------------------------------------------------------------
	
}

?>
