<?php 

class csbt_campaign extends csbt_data	 {
	
	const tableName = 'csbt_campaign_table';
	const seqName =  'csbt_campaign_table_campaign_id_seq';
	const pkeyField = 'campaign_id';
	
	//==========================================================================
	public function __construct(array $initialData=null, cs_phpDB $db=null) {
		parent::__construct($initialData, self::tableName, self::seqName, self::pkeyField, $db);
	}
	//==========================================================================
	
}

