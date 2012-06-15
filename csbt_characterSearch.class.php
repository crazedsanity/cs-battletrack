<?php
/*
 * Created on May 4, 2011
 * 
 * SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/csbt_characterSearch.class.php $
 * $Id: csbt_characterSearch.class.php 148 2011-05-09 23:18:02Z crazedsanity $
 * $LastChangedDate: 2011-05-09 18:18:02 -0500 (Mon, 09 May 2011) $
 * $LastChangedRevision: 148 $
 * $LastChangedBy: crazedsanity $
 */

class csbt_characterSearch extends csbt_character {
	
	protected $characterId;
	
	protected $dbObj;
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj, $uid=null) {
		$this->dbObj = $dbObj;
		$this->logger->logCategory = "Character Search";
		
		$this->uid = $uid;
		
		$this->gfObj = new cs_globalFunctions();
		
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function search(array $criteria) {
		if(is_array($criteria) && count($criteria)) {
			try {
				$tableHandler = new cs_dbTableHandler($this->dbObj, csbt_character::tableName, csbt_character::seqName, csbt_character::pkeyField, $this->cleanStringArr);
				//TODO: sanitize this a bit better...
				$filterSql = "";
				$orderBy = "";
				foreach($criteria as $field=>$val) {
					$addToOrder = true;
					if(strlen($val)) {
						switch($field) {
							case '__FILTER__':
								$extraFilter = $val;
								$addToOrder = false;
								break;
							default:
								$filterSql = $this->gfObj->create_list($filterSql, "lower(". $field .") LIKE '". strtolower($val) ."%'", " OR ");
								break;
						}
						if($addToOrder) {
							$orderBy = $this->gfObj->create_list($orderBy, $field, ", ");
						}
					}
					else {
						throw new exception(__METHOD__ .": invalid search criteria for '". $field ."' (". $val .")");
					}
				}
				if($extraFilter) {
					$filterSql = '('. $filterSql .') AND '. $extraFilter;
				}

				$retval = $tableHandler->get_records_using_custom_filter($filterSql, $orderBy);
			}
			catch(Exception $e) {
				throw new exception(__METHOD__ .": search failed::: ". $e->getMessage());
			}
		}
		else {
			throw new exception(__METHOD__ .": invalid or empty search criteria");
		}
		return ($retval);
	}//end search()
	//-------------------------------------------------------------------------
	
	
	
}
