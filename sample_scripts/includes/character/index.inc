<?php
/*
 * Created on Jul 20, 2009
 */

use battletrack\UserCharacterList;
use crazedsanity\core\ToolBox;


$userChar = new UserCharacterList($page->db, $playerUid);
$charData = $userChar->get_character_list();

$page->rip_all_block_rows();
$outerTemplate = 'campaignListing';
$rowName = 'characterList';
if(isset($page->templateRows[$rowName])) {
	if(is_array($charData)) {
		
		// put into an array based on campaign.
		//TODO: put unassigned characters at top or bottom of the list
		$byCampaign = array();
		$unassigned = array();
		foreach($charData as $id=>$info) {
			if(strlen($info['campaign_name'])) {
				$byCampaign[$info['campaign_name']][$id] = $info;
			}
			else {
				$unassigned[$id] = $info;
			}
		}
		if(count($unassigned)) {
			$byCampaign['*NOT ASSIGNED'] = $unassigned;
		}
		
		$campaignRow = "";
		foreach($byCampaign as $campaignName => $charList) {
			if(!strlen($campaignName)){
				$campaignName = '<i>Unassigned Characters</i>';
			}
			
			$parsedRows = "";
			foreach($charList as $id=>$info) {
				$name = $info['character_name'];
				if(!strlen($name)) {
					$name = "<font color=\"red\">[Not Applicable]</font>";
				}
				$repArr2 = $info;
				$repArr2['id']	= $idObfuscator->encode($id);
				$parsedRows .= ToolBox::mini_parser($page->templateRows[$rowName], $repArr2, '{', '}');
			}
			
			
			$repArr = array(
				'campaign_name'	=> $campaignName,
				$rowName		=> $parsedRows
			);
			$campaignRow .= ToolBox::mini_parser($page->templateRows[$outerTemplate], $repArr, '{', '}');
			
		}
		
		#$page->add_template_var($rowName, $parsedRows);
		
		$page->add_template_var($outerTemplate, $campaignRow);
	}
}
else {
	throw new exception(__METHOD__ .": missing template row");
}
