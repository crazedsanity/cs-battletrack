<?php
/*
 * Created on Jul 13, 2009
 */

//TODO: consider optionally adding the logging system.
/*
 * NOTE::: This class handles the main "character_attribute" table, along with doing "hand-offs" to other
 * 	classes when the need arises.
 */

class csbt_character extends csbt_basicRecord {
	
	public $characterId;
	protected $ownerUid;
	
	public $dbObj;
	public $gfObj;
	
	const tableName= 'csbt_character_table';
	const seqName =  'csbt_character_table_character_id_seq';
	const pkeyField = 'character_id';
	
	
	//==========================================================================
	/**
	 * 
	 * @param cs_phpDB $dbObj
	 * @param type $characterIdOrName
	 * @param type $ownerUid
	 * @throws InvalidArgumentException
	 */
	public function __construct(cs_phpDB $dbObj, $characterIdOrName, $ownerUid) {
		parent::__construct($dbObj, self::tableName, self::seqName, self::pkeyField);
		$this->ownerUid = $ownerUid;
		
		$this->gfObj = new cs_globalFunctions;
		
		if(!is_null($characterIdOrName)) {
			if(is_numeric($characterIdOrName)) {
				$this->characterId = $characterIdOrName;
			}
			else {
				$this->characterId = $this->create(
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
	public function create(array $data=null) {
		$this->characterId = parent::create($data);
		return $this->characterId;
	}//end create()
	//==========================================================================
}

