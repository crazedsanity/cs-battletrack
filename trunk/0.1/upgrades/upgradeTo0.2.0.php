<?php

class upgradeTo_0_2_0 extends cs_webdblogger {
	
	private $logsObj;
	
	//=========================================================================
	public function __construct(cs_phpDB &$db) {
		if(!$db->is_connected()) {
			throw new exception(__METHOD__ .": database is not connected");
		}
		$this->db = $db;
		
		$this->logsObj = new cs_webdblogger($this->db, 'Upgrade', false);
		
		$this->gfObj = new cs_globalFunctions;
		$this->gfObj->debugPrintOpt = 1;
	}//end __construct()
	//=========================================================================
	
	
	
	//=========================================================================
	public function run_upgrade() {
		
		$this->db->beginTrans(__METHOD__);
		
		$this->do_schema_change();
		
		$this->db->commitTrans(__METHOD__);
		
		return(true);
	}//end run_upgrade()
	//=========================================================================
	
	
	
	//=========================================================================
	private function do_schema_change() {
		
		$isValid = null;
		try {
			$this->run_upgrade_sql_file('upgrade_to_0-2-0.sql');
			
			//now fix some duplicates...
			$sql = "select * FROM csbt_attribute_table AS a WHERE attribute like '%-' AND "
					."trim(trailing '-' FROM attribute) IN (SELECT attribute FROM "
					."csbt_attribute_table WHERE attribute=trim(trailing '-' from a.attribute));";
			$data = $this->db->run_query($sql, 'attribute_id', 'attribute');
			
			foreach($data as $id=>$val) {
				$sql = "UPDATE csbt_character_attribute_table SET attribute_id=". $id ." WHERE "
					."attribute_id=(SELECT attribute_id FROM csbt_attribute_table WHERE attribute "
					."='". $val ."-')";
				$this->db->run_update($sql);
				
				$this->db->run_update("DELETE FROM csbt_character_attribute_table WHERE attribute='". $val ."-'");
			}
			$isValid = true;
		}
		catch(Exception $e) {
			$isValid=false;
		}
			
		return($isValid);
	}//end do_schema_change()
	//=========================================================================
	
	
	
	//=========================================================================
	private function do_key_changes() {
		$retval = false;
		
		$sql = "select distinct ON (attribute_type, attribute_subtype, attribute_name) attribute_type, attribute_subtype, attribute_name FROM csbt_character_attribute_table ORDER BY attribute_type, attribute_subtype, attribute_name;";
		
		$changeList = array(
			'ac-dex-mod'		=> 'ac-abilitymod-dex',
			'ac-size_modifier'			=> 'ac-mod-size',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> '',
			''			=> ''
		);
		
		return($retval);
	}//end do_key_changes()
	//=========================================================================
	
	
	
	//=========================================================================
	/**
	 * Execute the entire contents of the given file (with absolute path) as SQL.
	 * 
	 * Pulled from cs_webdblogger.class.php:::
	 * SVN URL::: https://cs-webapplibs.svn.sourceforge.net/svnroot/cs-webapplibs/trunk/0.3/cs_webdblogger.class.php
	 * ID:::::::: cs_webdblogger.class.php 156 2009-11-09 17:25:37Z crazedsanity
	 */
	final public function run_upgrade_sql_file($filename) {
		$fsObj = new cs_fileSystem(dirname(__FILE__) .'/../docs/sql');
		
		$this->lastSQLFile = $filename;
		
		$fileContents = $fsObj->read($filename);
		try {
			$this->db->run_update($fileContents, true);
			$retval = TRUE;
		}
		catch(exception $e) {
			$retval = FALSE;
		}
		
		return($retval);
	}//end run_upgrade_sql_file()
	//=========================================================================
}

?>
