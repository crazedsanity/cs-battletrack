<?php

class csbt_campaignCharacterList extends csbt_basicRecord {
	
	private $campaignId;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	
	public $charList = array();
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $campaignId) {
		$this->dbObj = $dbObj;
		parent::__construct($this->dbObj, self::tableName, self::seqName, self::pkeyField);
		
		if(is_numeric($campaignId) && $campaignId > 0) {
			$this->campaignId = $campaignId;
		}
		else {
			throw new exception(__METHOD__ .": missing or invalid campaignId (". $campaignId .")");
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function __get($internalVar) {
		return($this->$internalVar);
	}//end __get()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public static function get_character_list(cs_phpDb $dbObj, $campaignId) {
		$characterList = array();
		try {
//			$characterList = $this->get_records(array('campaign_id'=>$this->campaignId), 'character_name');
			
			$sql = "SELECT * FROM csbt_character_table WHERE campaign_id=:id";
			
			$numrows = $dbObj->run_query($sql, array('id'=>$campaignId));
			
			if($numrows > 0) {
				$characterList = $dbObj->farray_fieldnames('character_id');
			}
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve character list::: ". $e->getMessage());
		}
		return($characterList);
	}//end get_character_list()
	//-------------------------------------------------------------------------
	
}
