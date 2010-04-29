<?php
/*
 * Created on Jul 30, 2009
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 */

require_once(dirname(__FILE__) .'/characterSheet.class.php');

class userCharacterList extends characterSheet {
	
	private $uid;
	
	//-------------------------------------------------------------------------
	public function __construct($uid) {
		parent::__construct();
		
		if(is_numeric($uid) && $uid > 0) {
			$this->uid = $uid;
		}
		else {
			throw new exception(__METHOD__ .": missing or invalid uid (". $uid .")");
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
		$sql = "SELECT * FROM csbt_character_table WHERE uid=". $this->uid;
		
		try {
			$characterList = $this->dbObj->run_query($sql, 'character_id', 'character_name');
		}
		catch(exception $e) {
			$this->exception_handler(__METHOD__ .": failed to retrieve character list::: ". $e->getMessage());
		}
		return($characterList);
	}//end get_character_list()
	//-------------------------------------------------------------------------
	
}

?>
