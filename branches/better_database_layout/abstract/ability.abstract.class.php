<?php

/*
 * 
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */


abstract class abilityAbstract extends battleTrackAbstract {
	
	protected $allowedNames = array('str', 'con', 'dex', 'int', 'wis', 'cha');
	private $table = 'csbt_character_ability_table';
	private $sequence = 'csbt_character_ability_table_character_ability_id_seq';
	private $cache = array();
	
	//-------------------------------------------------------------------------
	public function __construct($characterId) {
		if(!is_numeric($characterId) || $characterId < 0) {
			throw new exception(__METHOD__ .": invalid character ID (". $characterId .")");
		}
		parent::__construct($characterId);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function update_ability($abilityName, $newValue) {
		if(in_array($abilityName, $this->allowedNames) && is_numeric($newValue)) {
			
			$sql = 'UPDATE '. $this->table .' SET '. $abilityName .'='. $newValue;
			try {
				$retval = $this->dbObj->run_update($sql, false);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .": error while updating ability::: ". $e->getMessage);
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid ability (". $abilityName .") or value (". $newValue .")");
		}
		
		return($retval);
	}//end update_ability()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function create_record($str, $con, $dex, $int, $wis, $cha) {
		$fields = array(
			'str' => $str,
			'con' => $con,
			'dex' => $dex,
			'int' => $int,
			'wis' => $wis,
			'cha' => $cha
		);
		
		foreach($fields as $name=>$value) {
			if(!is_numeric($value)) {
				throw new exception(__METHOD__ .": non-numeric value for '". $name ."': (". $value .")");
			}
			elseif($value < 0) {
				throw new exception(__METHOD__ .": invalid value for '". $name ."': (". $value .")");
			}
		}
		$fields['character_id'] = $this->characterId;
		
		$sql = 'INSERT INTO '. $this->table .' '. $this->gfObj->string_from_array($fields, 'sql_insert', NULL, 'integer');
		try {
			$retval = $this->dbObj->run_insert($sql, $this->sequence);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": error while creating record::: ". $e->getMessage());
		}
		return($retval);
	}//end create_record()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function get_abilities() {
		$sql = "SELECT * FROM ". $this->table ." WHERE character_id=". $this->characterId;
		try {
			$data = $this->dbObj->run_query($sql);
			$numrows = $this->dbObj->numrows();
			if($numrows == 1) {
				$this->cache = $data;
			}
			elseif($numrows == 0) {
				throw new exception(__METHOD__ .": no records returned (". $numrows .")");
			}
			else {
				throw new exception(__METHOD__ .": too many/invalid number of rows returned (". $numrows .")");
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": failure while running query::: ". $e->getMessage());
		}
		
		return($this->cache);
	}//end get_abilities()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function get_modifier($score) {
		if(is_numeric($score) && $score > 0) {
			$retval = floor(($score - 10)/2);
		}
		else {
			throw new exception(__METHOD__ .": score (". $score .") was invalid");
		}
		return($retval);
	}//end get_modifier()
	//-------------------------------------------------------------------------
}

?>