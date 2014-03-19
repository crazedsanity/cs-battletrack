<?php
/*
 * Created on Jul 13, 2009
 */

//TODO: consider optionally adding the logging system.
/*
 * NOTE::: This class handles the main "character_attribute" table, along with doing "hand-offs" to other
 * 	classes when the need arises.
 */

class csbt_character extends csbt_data {
	
	public $characterId;
	protected $ownerUid;
	
	public $dbObj;
	public $gfObj;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	public $gear = array();
	//==========================================================================
	/**
	 * 
	 * @param type $characterIdOrName
	 * @param type $ownerUid
	 * @param cs_phpDB $dbObj
	 * @throws InvalidArgumentException
	 */
	public function __construct($characterIdOrName, $ownerUid, cs_phpDB $dbObj=null) {
		parent::__construct(null, self::tableName, self::seqName, self::pkeyField);
		$this->ownerUid = $ownerUid;
		
		$this->gfObj = new cs_globalFunctions;
		
		if(!is_null($characterIdOrName)) {
			if(is_numeric($characterIdOrName)) {
				$this->characterId = $characterIdOrName;
			}
			else {
				$this->characterId = $this->create(
						$dbObj,
						array(
							'character_name'=>$characterIdOrName,
							'uid'			=> $this->ownerUid
						)
					);
			}
		}
		else {
			throw new InvalidArgumentException();
		}
	}//end __construct()
	//==========================================================================
	
	
	
	//==========================================================================
	public function create(cs_phpDB $db, array $data=null) {
		$this->characterId = parent::create($db, $data);
		return $this->characterId;
	}//end create()
	//==========================================================================
	
	
	
	//==========================================================================
	public function load_all(cs_phpDB $dbObj) {
		$x = new csbt_gear();
		$x->characterId = $this->characterId;
		
		$myData = $x->get_all_character_gear($dbObj);
		foreach($myData as $k=>$v) {
			$this->gear[$k] = new csbt_gear($v);
		}
	}
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_weight() {
		$weight = 0;
		
		if(is_array($this->gear) && count($this->gear) > 0) {
			foreach($this->gear as $k=>$obj) {
				$data = $obj->data;
				$itemWeight = 0;
				if(isset($data['weight']) && is_numeric($data['weight']) && $data['weight'] > 0) {
					if(isset($data['quantity']) && is_numeric($data['quantity']) && $data['quantity'] > 0) {
						$itemWeight = ($itemWeight * $data['quantity']);
					}
					else {
						$itemWeight = $data['weight'];
					}
				}
				$weight += $itemWeight;
			}
		}
		
		return $weight;
	}
	//==========================================================================
}

