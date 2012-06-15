<?php

/*
 * 
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/subclasses/csbt_tableHandler.class.php $
 * $Id: csbt_tableHandler.class.php 101 2010-10-07 19:47:00Z crazedsanity $
 * $LastChangedDate: 2010-10-07 14:47:00 -0500 (Thu, 07 Oct 2010) $
 * $LastChangedRevision: 101 $
 * $LastChangedBy: crazedsanity $
 */


class csbt_tableHandler extends cs_singleTableHandlerAbstract {
	
	protected $characterId = null;
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr, $characterId) {
		if(class_exists('cs_globalFunctions')) {
			$this->gfObj = new cs_globalFunctions;
			$this->gfObj->debugPrintOpt=1;
		}
		else {
			throw new exception(__METHOD__ .": missing required class 'cs_globalFunctions'");
		}
		
		if(is_object($dbObj) && get_class($dbObj) == 'cs_phpDB') {
			$this->dbObj = $dbObj;
		}
		else {
			cs_debug_backtrace(1);
			throw new exception(__METHOD__ .":: invalid database object (". $dbObj .")");
		}
		
		if(is_numeric($characterId)) {
			$this->characterId = $characterId;
		}
		
		parent::__construct($this->dbObj, $tableName, $seqName, $pkeyField, $cleanStringArr);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	/**
	 * Magic method to call methods in cs_singleTableHandlerAbstract
	 */
	public function __call($methodName, $args) {
		if(method_exists($this, $methodName)) {
			//TODO: seems like it should be able to be called using "$this" or something...
			$retval = call_user_func_array('cs_singleTableHandlerAbstract::'. $methodName, $args);
			$this->do_log("Called (". $methodName ."), RETVAL=(". $retval ."), args:::". $this->gfObj->debug_print($args,0,1,false), 'debug');
		}
		else {
			throw new exception(__METHOD__ .':: unknown method ('. $methodName .')');
		}
		return($retval);
	}//end __call()	
	//-------------------------------------------------------------------------
}

?>
