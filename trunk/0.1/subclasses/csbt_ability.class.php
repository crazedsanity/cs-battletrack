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
		//cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr
		parent::__construct($dbObj, self::tableName, self::tableSeq, self::pkeyField, $this->fields);
		$this->get_ability_list();
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function get_ability_list() {
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
	public function get_ability_modifier($abilityName, $isTemp=false) {
		//TODO: handle temporary modifiers ($isTemp)
		if(isset($this->dataCache['byName'][$abilityName])) {
			$score = $this->dataCache['byName'][$abilityName];
			$modifier = floor(($score -10)/2);
		}
		else {
			throw new exception(__METHOD__ .":: invalid ability (". $abilityName .")");
		}
	}//get_ability_modifier()
	//-------------------------------------------------------------------------
}

?>
