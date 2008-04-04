<?php

class chat extends abstractBattleTrack {
	
	private $user;
	
	//=========================================================================
	/**
	 * Constructor.
	 */
	function __construct($username) {
		
		//be sure to check in...
		$this->checkin();
		
	}//end __construct()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * This writes a submitted bit of chat into a file.
	 */
	public function add_chat($data) {
		$filename = $this->make_filename('chat');
		$this->create_file($filename, $data);
	}//end add_chat()
	//=========================================================================
}
?>