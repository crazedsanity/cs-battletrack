<?php

require_once(dirname(__FILE__) ."/abstractFileHandler.class.php");

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
	
	
	
	//=========================================================================
	private function checkin() {
		$filename = $this->make_filename('checkin');
		
		try {
			$xmlParser = new XMLParser($this->read_file($filename));
			$oldData = $xmlParser->get_tree(TRUE);
			
			$xmlCreator = new xmlCreator('checkin');
			$xmlCreator->add_tag('username', $this->username);
			$xmlCreator->add_tag('num_checkins', $oldData['CHECKIN']['NUM_CHECKINS']++);
			$xmlCreator->add_tag('last_checkin', microtime(TRUE));
			
			$this->create_file($filename, $xmlCreator->create_xml_string());
		}
		catch(exception $e) {
			throw new exception(__METHOD__ .": failed to create file::: ". $e->getMessage());
		}
	}//end checkin()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Create a filename based on the given type.
	 */
	protected function make_filename($type=NULL) {
		$retval = microtime(TRUE) .'.'.  $this->username .'.lock';
		if(!is_null($type)) {
			if($type == 'checkin') {
				$retval = $this->username .'.xml';
			}
			else {
				$retval = microtime(TRUE) . '.' . $this->username .'.xml';
			}
		}
		return($retval);
	}//end make_filename()
	//=========================================================================
}
?>