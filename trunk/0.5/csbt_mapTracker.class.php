<?php

/*
 *  SVN INFORMATION::::
 * --------------------------
 * $HeadURL$
 * $Id$
 * $LastChangedDate$
 * $LastChangedRevision$
 * $LastChangedBy$
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
