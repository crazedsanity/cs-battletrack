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
	public function get_campaign($campaignId) {
		if(is_numeric($campaignId) && $campaignId > 0) {
			$sql = "SELECT * FROM csbt_campaign_table WHERE campaign_id=". $campaignId;
			$data = $this->dbObj->run_query($sql);
			$numRows = $this->dbObj->numRows();
			$dbError = $this->dbObj->errorMsg();
			
			if($numRows != 1 || strlen($dbError) !== 0) {
				//now set some internal items.
				$this->campaignId = $data['campaign_id'];
				
			}
			else {
				throw new exception(__METHOD__ .": invalid number of records (". $numRows() .") or" 
					." database error (". $dbError .")");
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid campaignId (". $campaignId .")");
		}
		
	}//end get_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_campaign($name, $ownerUid, array $players=null) {
		
	}//end create_campaign()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function add_players() {
		
	}//end add_players()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_player_data() {
		
		
	}//end get_player_data()
	//-------------------------------------------------------------------------
}

?>