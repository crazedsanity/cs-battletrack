<?php

abstract class abstractFileHandler {
	
	protected $fs;
	
	//=========================================================================
	/**
	 * Constructor.
	 */
	function __construct($baseDir) {
		if(is_null($baseDir) || !strlen($baseDir)) {
			throw new exception(__METHOD__ .": no basedir");
		}
		
		$this->fs = new cs_fileSystemClass($baseDir);
	}//end __construct()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Write data into a file (or create an empty file, as in a lockfile).
	 * 
	 * @param $filename	(str) filename to create (can have existing dir + file, 
	 * 						such as "existingDir/filename.txt").
	 * @param $data		(str,optional) data to write into filename.
	 * 
	 * @return NULL		FAIL: no data written
	 * @return (int)	PASS: indicates number of bytes written (0 is okay if $data is null).
	 */
	protected function create_file($filename, $data=NULL) {
		$retval = NULL;
		if($this->fs->create_file($filename, TRUE)) {
			$retval = 0;
			if(!is_null($data) && strlen($data)) {
				$retval = $this->fs->write($data);
			}
		}
		else {
			throw new exception(__METHOD__ .": failed to create (". $filename .")");
		}
		return($retval);
	}//end create_file()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Read data from a file.
	 * 
	 * @param $filename		(str) filename to read.
	 * 
	 * @return (str)		Contains all data from within the given filename
	 */
	protected function read_file($filename) {
		return($this->fs->read($filename));
	}//end read_file()
	//=========================================================================
}
?>