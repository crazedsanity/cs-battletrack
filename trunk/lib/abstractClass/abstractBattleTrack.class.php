<?php

abstract class abstractBattleTrack extends abstractFileHandler {
	
	protected $fs;
	protected $gf;
	
	//=========================================================================
	public function __construct() {
		
		if(!is_null(constant('BT_BASEDIR'))) {
			throw new exception(__METHOD__ .": failed to locate basedir... something is broken");
		}
		
		$this->fs = new cs_filesystemClass(BT_BASEDIR);
		$this->gf = new cs_globalFunctions;
	}//end __construct()
	//=========================================================================
}
?>