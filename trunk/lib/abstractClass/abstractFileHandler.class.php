<?php


require_once(dirname(__FILE__) ."/../cs-content/contentSystemClass.php");


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
	 * @param $filename		(str) filename to create (can have existing dir + file, 
	 * 							such as "existingDir/filename.txt").
	 * @param $data			(str,optional) data to write into filename.
	 * 
	 * @return (int)		PASS: indicates number of bytes written (0 is okay if $data is null).
	 * @return (exception)	FAIL: exception indicates the problem.
	 */
	protected function create_file($filename, $data=NULL) {
		if($this->fs->create_file($filename, TRUE)) {
			$retval = 0;
			if(!is_null($data) && strlen($data)) {
				$retval = $this->fs->write($data);
			}
		}
		else {
			throw new exception(__METHOD__ .": failed to create (". $filename .")");
		}
		$this->fs->closeFile();
		return($retval);
	}//end create_file()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Read data from a file.
	 * 
	 * @param $filename		(str) filename to read.
	 * 
	 * @return (str)		PASS: Contains all data from within the given filename
	 * @return (exception)	FAIL: cs_fileSystemClass ran into a problem.
	 */
	protected function read_file($filename) {
		$retval = $this->fs->read($filename);
		$this->fs->closeFile();
		return($retval);
	}//end read_file()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Delete a file.
	 * 
	 * @param $filename		(str) filename to delete.
	 * 
	 * @return (NULL)		PASS: no news is good news.
	 * @return (exception)	FAIL: exception indicates error.
	 */
	protected function destroy_file($filename) {
		if($this->fs->rm($filename)) {
			$retval = TRUE;
		}
		else {
			throw new exception(__METHOD__ .": failed to destroy (". $filename .")");
		}
		
		return($retval);
	}//end destroy_file()
	//=========================================================================
}
?>