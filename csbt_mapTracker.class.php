<?php

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL: https://cs-battletrack.svn.sourceforge.net/svnroot/cs-battletrack/trunk/current/csbt_mapTracker.class.php $
 * $Id: csbt_mapTracker.class.php 148 2011-05-09 23:18:02Z crazedsanity $
 * $LastChangedDate: 2011-05-09 18:18:02 -0500 (Mon, 09 May 2011) $
 * $LastChangedRevision: 148 $
 * $LastChangedBy: crazedsanity $
 */

class csbt_mapTracker extends cs_webapplibsAbstract {
	
	private $dbObj;
	private $tokenTable;
	private $mapTable;
	
	//-------------------------------------------------------------------------
	public function __construct(cs_phpDB $dbObj) {
		
		// table handler for dealing with tokens.
		$cleanStringArr = array(
			'map_id'		=> "int",
			'token_name'	=> "text",
			'token_img'		=> "text",
			'location'		=> "text",
			'movement'		=> "text"
		);
		$this->tokenTable = new cs_dbTableHandler($this->dbObj, 'csbt_map_token_table', 'csbt_map_token_table_map_token_id_seq', 'map_token_id', $cleanStringArr);
		
		// table handler for dealing with maps.
		$cleanStringArr = array(
			'campaign_id'			=> "int",
			'map_name'				=> "text",
			'map_image_url'			=> "text",
			'creator_uid'			=> "int",
			'width'					=> "int",
			'height'				=> "int",
			'offset_left'			=> "int",
			'offset_top'			=> "int",
			'cell_size'				=> "int",
			'toolbox_offset_left'	=> "int",
			'toolbox_offset_top'	=> "int",
			'grid_shown'			=> "bool"
		);
		//__construct(cs_phpDB $dbObj, $tableName, $seqName, $pkeyField, array $cleanStringArr)
		$this->mapTable = new cs_dbTableHandler($this->dbObj, 'csbt_map_table', 'csbt_map_table_map_id_seq', 'map_id', $cleanStringArr);
	}//end __construct()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_map($campaignId, $mapName, $description=null) {
		$dataArr = array(
			'campaign_id'	=> $campaignId
		);
		$this->mapTable->create_record();
	}//end create_map()
	//-------------------------------------------------------------------------
	
	
	
	//-------------------------------------------------------------------------
	public function create_token() {
	}//end create_token()
	//-------------------------------------------------------------------------

}
