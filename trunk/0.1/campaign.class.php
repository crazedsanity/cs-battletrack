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

class campaign extends battleTrackAbstract	 {
	
	protected $campaignId;
	
	
	//-------------------------------------------------------------------------
	/**
	 * Basic creation of the campaign object.
	 */
	public function __construct($campaignId=null) {
		if(!is_null($campaignId)) {
			$this->get_campaign($campaignId);
		}
		parent::__construct();
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function _sanity($failOnInsane=true) {
		$isSane=false;
		if(is_numeric($this->campaignId)) {
			$isSane = true;
		}
		
		if($isSane == false && $failOnInsane == true) {
			throw new exception(__METHOD__ .": campaignId required but not set");
		}
		return($isSane);
	}//end _sanity()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_campaign($campaignId) {
		$sql = "SELECT * FROM csbt_campaign_table WHERE campaign_id=". $campaignId;
		try {
			$data = $this->dbObj->run_query($sql);
			$numRows = $this->dbObj->numRows();
			$dbError = $this->dbObj->errorMsg();
				
			if($numRows != 1 || strlen($dbError) !== 0) {
				//now set some internal items.
				$this->campaignId = $data['campaign_id'];
				$this->_sanity();
			}
			else {
				throw new exception(__METHOD__ .": invalid number of records (". $numRows() .") or" 
						." database error (". $dbError .")");
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve campaign (". $campaignId ."), DETAILS::: ". $e->getMessage());
		}
		return($data);
	}//end get_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_campaign($name, $ownerUid, array $players=null) {
		if(strlen($name) && is_numeric($ownerId)) {
			$sql = "INSERT INTO csbt_campaign_table (campaign_name, owner_uid) VALUES "
				."(". $this->gfObj->cleanString($name, 'name', true) .", ". $ownerUid .")";
			try {
				$this->campaignId = $this->dbObj->run_insert($sql, 'csbt_campaign_table_campaign_id_seq');
			}
			catch(Exception $ex) {
				throw new exception(__METHOD__ .": failed to create campaign, SQL error::: ". $ex->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": failed to create campaign, missing name (". $name .") or ownerUid (". $ownerUid .")");
		}
		$this->_sanity();
		return($this->campaignId);
	}//end create_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_campaign($campaignName, $ownerUid=null, $isActive=null) {
		$this->_sanity();
		
		$cleanString = array(
			'campaign_name'		=> 'sql',
			'owner_uid'			=> 'int',
			'is_active'			=> 'bool'
		);
		$sqlArr = array(
			'campaign_name'		=> $campaignName
		);
		if(!is_null($ownerUid) && is_numeric($ownerUid) && $ownerUid > 0) {
			$sqlArr['owner_uid'] = $ownerUid;
		}
		if(!is_null($isActive) && is_bool($isActive)) {
			$sqlArr['is_active'] = $isActive;
		}
		$sql = "UPDATE csbt_campaign_table SET ". $this->gfObj->string_from_array($sqlArr, 'update', null, $cleanString, true)
			."' WHERE campaign_id=". $this->campaignId;
		try {
			$retval = $this->dbObj->run_update($sql,true);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to update campaign record, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end update_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_is_active($newSetting=true) {
		$retval = false;
		$newSetting = $this->gfObj->interpret_bool($newSetting, array('FALSE', 'TRUE'));
		
		$sql = "UPDATE csbt_campaign_table SET is_active=". $newSetting;
		
		return($retval);
	}
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function add_players(array $characterSheets) {
		if(count($characterSheets)) {
			try{
				foreach($characterSheets as $i=>$sheetObj) {
					$this->add_player($sheetObj);
				}
			}
			catch(Exception $ex) {
				throw new exception(__METHOD__ .": failed to add character #". $i);
			}
		}
		else {
			throw new exception(__METHOD__ .": no data in list");
		}
		
	}//end add_players()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function add_player(characterSheet $sheetObj) {
		$sql = "UPDATE csbt_character_sheet_table WHERE character_id=". $sheetObj->characterId;
		try {
			$numUpdated = $this->dbObj->run_update($sql);
		}
		catch(Exception $ex) {
			throw new exception(__METHOD__ .": failed to add character::: ". $ex->getMessage());
		}
		return($numUpdated);
	}//end add_player
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_player_data() {
	}//end get_player_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	private function _do_update(array $updates) {
		
		$cleanStringArr = array(
			'campaign_name'	=> 'sql',
			'is_active'		=> 'bool'
		);
		
		$sql = 'UPDATE csbt_campaign_table SET '
			. $this->gfObj->string_from_array($updates, 'update', null, $allowedFieldsArr, false)
			. ' WHERE campaign_id='. $this->campaignId;
		
		try{
			$numAffected = $this->dbObj->run_update($sql, false);
		}
		catch(Exception $ex) {
			throw new exception(__METHOD__ .": failed to run SQL::: ". $ex->getMessage());
		}
		
		return($numAffected);
	}//end _do_update()
	//-------------------------------------------------------------------------
}

?>
