<?php

use battletrack\campaign\Campaign;
use crazedsanity\core\ToolBox;

$campaign = new Campaign(null, $page->db);
if($_POST) {
	$urlAppend = "invalid";
	switch($_POST['action']) {
		case 'newCampaign':
			try {
				$createData = array(
					'owner_uid'		=> $_SESSION['uid'],
					'campaign_name'	=> $_POST['name'],
					'description'	=> $_POST['description'],
				);
				$res = $campaign->create($page->db, $createData);
			}
			catch(Exception $ex) {
				$res = $ex->getMessage();
			}
			$urlAppend = "newId=". $res;
			break;

		case 'addPlayer':
			try {
				$res = Campaign::add_player($page->db, $_POST['campaignId'], $_POST['playerId']);
				$urlAppend = $res;
			}
			catch(Exception $e) {
				$res = $e->getMessage();
				$urlAppend = "failed...";
				$page->set_message_wrapper(array(
					'title'		=> "Error",
					'message'	=> "Failed to add character, DETAILS::: ". $e->getMessage(),
					'type'		=> "error"
				));
			}
			break;
		default:
			$urlAppend .= ";action=". $_POST['action'];
	}
	ToolBox::conditional_header("#Posted;". $urlAppend);
	exit;
}
else {
	$campaignList = $campaign->get_all($page->db, $_SESSION['uid']);


	$x = $page->rip_all_block_rows();
	$rowName = 'noCampaignRow';
	$finalRow = $page->templateRows[$rowName];
	if(is_array($campaignList) && count($campaignList)) {
		$rowName = 'campaignRow';
		$finalRow = "";
		$idObfuscator = new cs_idObfuscator();
		foreach($campaignList as $cId=>$cInfo) {
			if(is_array($cInfo)) {
				$playerList = $cInfo['playerList'];
				unset($cInfo['playerList']);
				$tRow = $page->mini_parser($page->templateRows[$rowName], $cInfo);
				$playerRow = $page->templateRows['campaignRow_playerRow'];
				$pRow = "";
				if(is_array($playerList) && count($playerList)) {
					$playerRow = $page->templateRows['campaignRow_playerRow'];
					$pRow = "";
					foreach($playerList as $pId=>$pInfo) {
						$pInfo['character_id'] = $idObfuscator->encode($pInfo['character_id']);
						$pRow .= $page->mini_parser($playerRow, $pInfo);
					}
				}
				$tRow = $page->mini_parser($tRow, array('campaignRow_playerRow'=>$pRow), '{', '}');

				$finalRow .= $tRow;
			}
		}
		$page->add_template_var($rowName, $finalRow);
	}
	$page->add_template_var($rowName, $finalRow);
}
