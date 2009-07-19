<?php
/*
 * Created on Jul 13, 2009
 *
 */

class characterSheet {
	
	private $characterId;
	
	private $dbObj;
	
	private $objects=array();
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $db, $characterId) {
		$this->dbObj = $db;
		$this->characterId = $characterId;
		
		$this->objects = array(
			'abilities'	=> new dndAbilities($db, $characterId),
			'saves'		=> new dndSaves($db, $characterId),
			'skills'	=> new dndSkills($db, $characterId),
			'feats'		=> new dndFeats($db, $characterId)
		);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function get_character_data() {
	}//end get_character_data()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function update_character() {
	}//end update_character()
	//-------------------------------------------------------------------------
	
}

?>
