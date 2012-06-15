<?php
/*
 * Created on Jul 30, 2009
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/csbt_userCharacterList.class.php $
 * $Id: csbt_userCharacterList.class.php 122 2010-11-11 01:54:37Z crazedsanity $
 * $LastChangedDate: 2010-11-10 19:54:37 -0600 (Wed, 10 Nov 2010) $
 * $LastChangedRevision: 122 $
 * $LastChangedBy: crazedsanity $
 */


class csbt_userCharacterList extends csbt_tableHandler {
	
	private $uid;
	
	
	protected $cleanStringArr = array(
			'uid'					=> 'int',
			'character_name'		=> 'sql'
		);
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $uid) {
		$this->dbObj = $dbObj;
		parent::__construct($this->dbObj, self::tableName, self::seqName, self::pkeyField, $this->cleanStringArr, null);
		
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
		
		try {
			$characterList = $this->get_records(array('uid'=>$this->uid));
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to retrieve character list::: ". $e->getMessage());
		}
		return($characterList);
	}//end get_character_list()
	//-------------------------------------------------------------------------
	
}

?>
