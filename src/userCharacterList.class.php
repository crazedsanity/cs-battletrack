<?php

namespace battletrack;
/*
 * Created on Jul 30, 2009
 */

use battletrack\campaign\Campaign;

use crazedsanity\database\Database;

use \Exception;

class UserCharacterList extends \battletrack\basic\Record {
	
	private $uid;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	//-------------------------------------------------------------------------
	public function __construct(Database $dbObj, $uid) {
		$this->dbObj = $dbObj;
		parent::__construct($this->dbObj, self::tableName, self::seqName, self::pkeyField);
		
		if(is_numeric($uid) && $uid > 0) {
			$this->uid = $uid;
		}
		else {
			throw new exception(__METHOD__ .": missing or invalid uid (". $uid .")");
		}
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_list() {
		
		try {
			//$characterList = $this->get_records(array('uid'=>$this->uid));
			
			$sql = 'SELECT t.*, t2.campaign_name FROM '. self::tableName 
					.' AS t LEFT OUTER JOIN '. Campaign::tableName .' AS t2
					USING (campaign_id) WHERE (uid=:uid OR :uid IS NULL)';
			$params = array('uid' => $this->uid);
			$this->dbObj->run_query($sql, $params);
			
			$characterList = $this->dbObj->farray_fieldnames(self::pkeyField);
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve character list::: ". $e->getMessage());
		}
		return($characterList);
	}//end get_character_list()
	//-------------------------------------------------------------------------
	
}

?>
