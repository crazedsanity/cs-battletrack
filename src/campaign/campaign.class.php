<?php 

namespace battletrack\campaign;

use battletrack\CampaignCharacterList;
use battletrack\character\Character;


use crazedsanity\database\Database;

class Campaign extends \battletrack\basic\Data	 {
	
	const tableName = 'csbt_campaign_table';
	const seqName =  'csbt_campaign_table_campaign_id_seq';
	const pkeyField = 'campaign_id';
	
	//==========================================================================
	public function __construct(array $initialData=null, Database $db=null) {
		parent::__construct($initialData, self::tableName, self::seqName, self::pkeyField, $db);
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function get_all(Database $dbObj, $ownerUid) {
		$retval = array();
		
		$sql = "SELECT * FROM ". self::tableName ." WHERE owner_uid=:id ORDER BY campaign_name";
		$params = array('id'=>$ownerUid);
		
		$rows = $dbObj->run_query($sql, $params);
		
		if($rows > 0) {
			$retval = $dbObj->farray_fieldnames(self::pkeyField);
			
			//
			foreach(array_keys($retval) as $id) {
				$retval[$id]['playerList'] = CampaignCharacterList::get_character_list($dbObj, $id);
			}
		}
		
		return $retval;
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_player_data($campaignId) {
		$obj = new CampaignCharacterList();
		
		return $obj->get_character_list();
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public static function add_player(Database $dbObj, $campaignId, $playerId) {
		
		if(is_numeric($campaignId) && is_numeric($playerId)) {
			$playerObj = new Character($playerId);

			$playerObj->update('campaign_id', $campaignId);
			$playerObj->save($dbObj);
		}
		else {
			throw new ErrorException(__METHOD__ .": invalid campaign ID");
		}
	}
	//==========================================================================
}

