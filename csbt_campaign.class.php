<?php 

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/csbt_campaign.class.php $
 * $Id: csbt_campaign.class.php 150 2011-05-10 22:45:16Z crazedsanity $
 * $LastChangedDate: 2011-05-10 17:45:16 -0500 (Tue, 10 May 2011) $
 * $LastChangedRevision: 150 $
 * $LastChangedBy: crazedsanity $
 */

class csbt_campaign extends csbt_battleTrackAbstract	 {
	
	protected $campaignId=null;
	protected $uid=null;
	protected $tableHandler;
	
	//-------------------------------------------------------------------------
	/**
	 * Basic creation of the campaign object.
	 */
	public function __construct(cs_phpDB $dbObj, $uid, $campaignId=null) {
		$cleanStringArr = array(
			'campaign_name'		=> "text",
			'description'		=> "text",
			'owner_uid'			=> "int",
			'is_active'			=> "bool"
		);
		parent::__construct($dbObj, 'csbt_campaign_table', 'csbt_campaign_table_campaign_id_seq', 'campaign_id', $cleanStringArr);
		if(!is_null($campaignId)) {
			$this->get_campaign($campaignId);
		}
		if(!is_null($uid) && is_numeric($uid)) {
			$this->uid = $uid;
		}
		else {
			throw new exception(__METHOD__ .": uid required (". $uid .")");
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_sheet_data(){
		return(false);
	}//end get_sheet_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_defaults() {
		return(false);
	}//end get_character_defaults()
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
	public function handle_update($fieldName, $recId=null, $newValue) {
		$retval = false;
		try {
			$res = $this->tableHandlerObj->update_record($this->campaignId, array($fieldName=>$newValue));
			if($res) {
				$retval = true;
			}
		}
		catch(Exception $ex) {
			$retval = false;
		}

		return($retval);
	}//end handle_update()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_campaign($campaignId) {
		try {
			$this->campaignId = $campaignId;
			$data = $this->tableHandlerObj->get_record_by_id($campaignId);
			
			$this->_sanity();
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to retrieve campaign (". $campaignId ."), DETAILS::: ". $e->getMessage());
		}
		return($data);
	}//end get_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_campaign($name, $description=null) {
		if(strlen($name) && is_numeric($this->uid)) {
			$insertData = array(
				'campaign_name'	=> $name,
				'owner_uid'		=> $this->uid,
				'description'	=> $description
			);
			try {
				$this->campaignId = $this->tableHandlerObj->create_record($insertData);
			}
			catch(Exception $ex) {
				throw new exception(__METHOD__ .": failed to create campaign, SQL error::: ". $ex->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": failed to create campaign, missing name (". $name .") or ownerUid (". $this->uid .")");
		}
		$this->_sanity();
		return($this->campaignId);
	}//end create_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_campaign($campaignName, $isActive=null) {
		$this->_sanity();
		
		$sqlArr = array(
			'campaign_name'		=> $campaignName
		);
		if(!is_null($isActive) && is_bool($isActive)) {
			$sqlArr['is_active'] = $isActive;
		}
		try {
			$retval = $this->tableHandlerObj->update_record($this->campaignId, $sqlArr, null, " AND owner_uid=". $this->uid);
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: failed to update campaign record, DETAILS::: ". $e->getMessage());
		}
		return($retval);
	}//end update_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function set_is_active($newSetting=true) {
		try {
			$newSetting = $this->gfObj->interpret_bool($newSetting, array('FALSE', 'TRUE'));
			$this->tableHandlerObj->update_record($this->campaignId, array('is_active'=>$newSetting), " AND owner_uid=". $this->uid);
			$retval = true;
		}
		catch(Exception $e) {
			$retval = false;
		}
		
		return($retval);
	}
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function add_players(array $characterIds) {
		$retval = 0;
		if(count($characterIds)) {
			try{
				foreach($characterIds as $id) {
					$charSheetObj = new csbt_character($this->dbObj, $id);
					$charSheetObj->update_main_character_data(array('campaign_id'=>$this->campaignId));
					$retval++;
				}
			}
			catch(Exception $ex) {
				throw new exception(__METHOD__ .": failed to add character #". $i);
			}
		}
		else {
			throw new exception(__METHOD__ .": no data in list");
		}
		
		return($retval);
	}//end add_players()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function add_player($id) {
		try {
			$char = new csbt_character($this->dbObj, $id);
			$retval = $char->update_main_character_data(array('campaign_id' => $this->campaignId));
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .": failed to update character::: ". $e->getMessage());
		}
		return($retval);
	}//end add_player()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_player_data($campaignId=null) {
		if(is_null($campaignId)) {
			$campaignId = $this->campaignId;
		}
		$charListObj = new csbt_campaignCharacterList($this->dbObj, $campaignId);
		return($charListObj->get_character_list());
	}//end get_player_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_campaigns() {
		$retval = array();
		if(!is_null($this->uid)) {
			try {
				//function get_records(array $filter=null, $orderBy=null, $limit=null, $offset=null)
				$retval = $this->tableHandlerObj->get_records(array('owner_uid'=>$this->uid));
				
				if(is_array($retval) && count($retval)) {
					foreach($retval as $id=>$info) {
						$playerList = $this->get_player_data($id);
						if(is_array($playerList)) {
							$retval[$id]['playerList'] = $playerList;
						}
						else {
							$retval[$id]['playerList'] = array();
						}
					}
				}
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .": error... ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": uid must be set (". $this->uid .")");
		}
		return($retval);
	}//end get_campaigns()
	//-------------------------------------------------------------------------
	
	
}

?>
