<?php

/*
 * 
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
 * 
 * No functionality is here for creating records in the "master" skills table (csbt_skills_table).  
 * They must be created already before a character can be linked to it.
 */


abstract class skillAbstract extends battleTrackAbstract {
	
	protected $characterId;
	
	//-------------------------------------------------------------------------
	public function __construct($characterId=NULL) {
		
		parent::__construct($characterId);
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function load_default_skills() {
		
	}//end load_default_skills()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	protected function add_skill($skillId) {
		
	}//end add_skill()
	//-------------------------------------------------------------------------
}

?>