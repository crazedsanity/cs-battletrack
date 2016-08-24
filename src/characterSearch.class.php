<?php

namespace battletrack;

use \battletrack\character\Character;
use \battletrack\campaign\Campaign;

use \crazedsanity\database\Database;

use \Exception;

class CharacterSearch extends Character {
	
	public $characterId;
	
	
	
	//-------------------------------------------------------------------------
	public static function search(Database $dbObj, array $criteria) {
		if(is_array($criteria) && count($criteria)) {
			$retval = array();
			
			$sql = "SELECT c.*, ca.campaign_name FROM ". Character::tableName ." AS c
				LEFT OUTER JOIN ". Campaign::tableName ." AS ca 
				USING (campaign_id)
				WHERE 
				(LOWER(c.character_name) LIKE :character_name OR :character_name IS NULL)
				AND 
				(LOWER(ca.campaign_name) LIKE :campaign_name OR :campaign_name IS NULL)	
			AND
				(c.campaign_id IS NULL)
				ORDER BY c.character_name, ca.campaign_name";
			//Note that the campaign_id IS NULL part is arbitrary...
			
			if(!isset($criteria['character_name'])) {
				$criteria['character_name'] = null;
			}
			else {
				$criteria['character_name'] .= '%';
			}
			if(!isset($criteria['campaign_name'])) {
				$criteria['campaign_name'] = null;
			}
			else {
				$criteria['campaign_name'] .= '%';
			}
//			if(!isset($criteria['campaign']))
			try {
				$numRows = $dbObj->run_query($sql, $criteria);

				if($numRows > 0) {
					$retval = $dbObj->farray_fieldnames(Character::pkeyField);
				}
			}
			catch(Exception $e) {
				cs_debug_backtrace(1);
				throw new exception(__METHOD__ .": error while running search::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid or empty search criteria");
		}
		return ($retval);
	}//end search()
	//-------------------------------------------------------------------------
	
	
	
}
