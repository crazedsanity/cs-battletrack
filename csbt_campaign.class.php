<?php 

class csbt_campaign extends csbt_basicRecord	 {
	
	protected $campaignId=null;
	protected $uid=null;
	protected $tableHandler;
	
	const tableName = 'csbt_campaign_table';
	const seqName =  'csbt_campaign_table_campaign_id_seq';
	const pkeyField = 'campaign_id';
	
	//==========================================================================
	public function __construct(cs_phpDB $db) {
		parent::__construct($db, self::tableName, self::seqName, self::pkeyField);
	}
	//==========================================================================
	
	
	//==========================================================================
	public function create($name, $description, $ownerUid, $isActive=true) {
		if(is_null($isActive) || !is_bool($isActive)) {
			$isActive = true;
		}
		$data = array(
			'campaign_name'	=> $name,
			'description'	=> $description,
			'owner_uid'		=> $ownerUid,
			'is_active'		=> $isActive,
		);
		$this->campaignId = parent::create($data);
		
		return $this->campaignId;
	}
	//==========================================================================
	
}

