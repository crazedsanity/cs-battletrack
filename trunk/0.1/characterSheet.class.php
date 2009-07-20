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
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_character_id($id) {
		if(is_numeric($id)) {
			$this->characterId = $id;
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
		$totalCount = count($attribs);
		$finalCount = 0;
		$this->dbObj->beginTrans();
		foreach($attribs as $type=>$subData) {
			if(is_array($subData)) {
				foreach($subData as $subtype=>$finalBit) {
					#$this->gfObj->debug_print(__METHOD__ .": type=(". $type ."), subtype=(". $subtype ."), finalBit::: "
					#		. $this->gfObj->debug_print($finalBit,0),1);
					if(is_array($finalBit)) {
						foreach($finalBit as $name=>$value) {
							$updateAttrib = $this->get_attrib($type, $subtype, $name, $value);
							$this->insert_attrib($type, $subtype, $name, $value);
							$finalCount++;
						}
					}
					else {
						$name = null;
						$updateAttrib = $this->get_attrib($type, $subtype, $name, $finalBit);
						$this->insert_attrib($type, $subtype, $name, $finalBit);
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
		$sql = "INSERT INTO csbt_character_attribute_table ".
			$this->gfObj->string_from_array(array(
				'character_id'		=> $this->characterId,
				'attribute_type'	=> $type,
				'attribute_subtype'	=> $subtype,
				'attribute_name'	=> $name,
				'attribute_value'	=> $value
			), 'insert');
			
		$retval = $this->dbObj->run_insert($sql, 'csbt_character_attribute_table_character_attribute_id_seq');
		if(!is_numeric($retval) || $retval < 1) {
			throw new exception(__METHOD__ .": failed to create attribute for data::: ". $this->gfObj->debug_print(func_get_args(),0));
		}
		return($retval);
	}//end insert_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function get_attrib($type, $subtype, $name, $value) {
		$dataArr = array(
			'character_id'		=> $this->characterId,
			'attribute_type'	=> $type,
			'attribute_subtype'	=> $subtype,
			'attribute_name'	=> $name
		);
		$sql = "SELECT * FROM csbt_character_attribute_table WHERE ".
			$this->gfObj->string_from_array($dataArr, 'select');
		
		try {
			$result = $this->dbObj->run_query($sql, 'character_attribute_id');
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve attribute::: ". $e->getMessage());
		}
		
		return($result);
	}//end get_attrib()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function get_attribute_key(array $data) {
		$key = $data['attribute_type'] .'-'. $data['attribute_subtype'] 
				.'-'. $data['attribute_name'];
		return($key);
	}//end get_attribute_key()
	//-------------------------------------------------------------------------
	
}

?>
