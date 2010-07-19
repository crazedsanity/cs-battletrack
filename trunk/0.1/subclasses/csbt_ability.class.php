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

class csbt_ability extends cs_singleTableHandlerAbstract	 {
	
	protected $characterId;
	protected $fields;
	
	/** Did you notice "{tableName}_{pkeyField}_seq"? PostgreSQL makes that simple, others don't.*/
	const tableName = 'csbt_ability_table';
	const tableSeq  = 'csbt_ability_table_ability_id_seq';
	const pkeyField = 'ability_id';
	
	protected $dataCache=array();
	
	//-------------------------------------------------------------------------
	/**
	 */
	public function __construct(cs_phpDB $dbObj) {
		$this->fields = array(
			'ability_id'		=> 'int',
			'ability_name'		=> 'sql'
		);
		
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->get_ability_list();
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_list() {
		try {
			$data = $this->get_records();
			
			foreach($data as $id=>$info) {
				$this->dataCache['byId'][$id] = $info['ability_name'];
				$this->dataCache['byName'][$info['ability_name']] = $id;
			}
			
			if(count($this->dataCache['byId']) !== count($this->dataCache['byName'])) {
				$this->gfObj->debug_print($data,1);
				$this->gfObj->debug_print($this->dataCache,1);
				throw new exception(__METHOD__ .":: FATAL ERROR: couldn't get lists to line-up, so the kittens ate their mittens... ");
			}
		}
		catch(Exception $e) {
			throw new exception(__METHOD__ .":: FATAL ERROR::: ". $e->getMessage());
		}
		
		return($this->dataCache);
	}//end get_ability_list()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_name($id) {
		if(isset($this->dataCache['byId'][$id])) {
			$retval = $this->dataCache['byId'][$id];
		}
		else {
			throw new exception(__METHOD__ .":: invalid id (". $id .")");
		}
		
		return($retval);
	}//end get_ability_name()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_id($name) {
		
		//it is a long string... rip out the first 3 characters.
		if(strlen($name) > 3) {
			$name = substr($name,0,3);
		}
		
		if(isset($this->dataCache['byName'][$name])) {
			$retval = $this->dataCache['byName'][$name];
		}
		else {
			$this->gfObj->debug_print($this->dataCache,1);
			throw new exception(__METHOD__ .":: invalid name (". $name .")");
		}
		
		return($retval);
	}//end get_ability_id()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_ability_modifier($score) {
		//TODO: handle temporary modifiers ($isTemp)
		if(is_numeric($score) && $score > 0) {
			$modifier = floor(($score -10)/2);
		}
		elseif(is_null($score)) {
			$modifier = null;
		}
		else {
			throw new exception(__METHOD__ .":: invalid score (". $score .")");
		}
		return($modifier);
	}//get_ability_modifier()
	//-------------------------------------------------------------------------
}

?>
