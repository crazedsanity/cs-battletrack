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
	public function __construct($characterIdOrName, $ownerUid=null, cs_phpDB $dbObj=null) {
		parent::__construct(null, self::tableName, self::seqName, self::pkeyField);
		$this->ownerUid = $ownerUid;
		
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
		$this->id = $this->characterId;
	}//end __construct()
	//==========================================================================
	
	
	
	//==========================================================================
	public function create(cs_phpDB $db, array $data=null) {
		$this->characterId = parent::create($db, $data);
		return $this->characterId;
	}//end create()
	//==========================================================================
	
	
	
	//==========================================================================
	public function get_total_weight(cs_phpDB $dbObj) {
		$weight = 0;
		
		$allGear = csbt_gear::get_all($dbObj, $this->characterId);
		
		if(is_array($allGear) && count($allGear) > 0) {
			foreach($allGear as $k=>$data) {
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

