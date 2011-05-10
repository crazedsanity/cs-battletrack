<?php
/*
 * Created on April 29, 2011
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */


class csbt_campaignCharacterList extends csbt_tableHandler {
	
	private $campaignId;
	
	
	protected $cleanStringArr = array(
			'campaign_id'					=> 'int',
			'character_name'		=> 'sql'
		);
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $campaignId) {
		$this->dbObj = $dbObj;
		parent::__construct($this->dbObj, self::tableName, self::seqName, self::pkeyField, $this->cleanStringArr, null);
		
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
	public function get_character_list() {
		
		try {
			$characterList = $this->get_records(array('campaign_id'=>$this->campaignId), 'character_name');
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve character list::: ". $e->getMessage());
		}
		return($characterList);
	}//end get_character_list()
	//-------------------------------------------------------------------------
	
}

?>
