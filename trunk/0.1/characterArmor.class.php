<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

class characterArmor extends battleTrackAbstract	 {
	
	protected $characterId;
	protected $fields;
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_character_armor_table';
	const tableSeq  = 'csbt_character_armor_table_character_armor_id_seq';
	const pkeyField = 'character_armor_id';
	
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj, $characterId) {
		if(is_null($characterId) || !is_numeric($characterId)) {
			throw new exception(__METHOD__ .":: invalid character id (". $characterId .")");
		}
		$this->characterId = $characterId;
		$this->fields = array(
			'character_id'		=> 'int',
			'armor_name'		=> 'sql',
			'armor_type'		=> 'sql',
			'ac_bonus'			=> 'int',
			'check_penalty'		=> 'int',
			'max_dex'			=> 'int',
			'special'			=> 'sql',
			'weight'			=> 'sql',
			'spell_fail'		=> 'int',
			'max_speed'			=> 'int',
			'is_worn'			=> 'bool'
		);
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_armor_by_id($armorId) {
		return($this->get_record_by_id($armorId));
	}//end get_armor_by_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_all_armor() {
		
	}//end get_all_armor()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_armor($name, array $miscData=null) {
		$sqlArr = $miscData;
		if(!is_array($miscData) || !count($miscData)) {
			$sqlArr = array(
				'armor_name'	=> $name
			);
		}
		return($this->create_record($sqlArr));
	}//end create_armor()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_armor($armorId, array $updates) {
		return($this->update_record($armorId, $updates));
	}//end update_armor()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_armor() {
		return($this->get_records(array('character_id'=>$this->characterId)));
	}//end get_character_armor()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data() {
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		return(array());
	}//end get_character_defaults()
	//-------------------------------------------------------------------------
}

?>
