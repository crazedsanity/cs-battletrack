<?php

#$page->gfObj->debug_print($_POST,1);
$campaign = new csbt_campaign($page->db, $_SESSION['uid'], null);
if($_POST) {
	$urlAppend = "invalid";
	switch($_POST['action']) {
		case 'newCampaign':
			try {
				$res = $campaign->create_campaign($_POST['name'], $_POST['description']);
			}
			catch(Exception $ex) {
				$res = $ex->getMessage();
			}
			$urlAppend = "newId=". $res;
			break;

		case 'addPlayer':
			try {
				$campaign->get_campaign($_POST['campaignId']);
				$res = $campaign->add_player($_POST['playerId']);
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
	$page->gfObj->conditional_header("#Posted;". $urlAppend);
	exit;
}
else {
	$campaignList = $campaign->get_campaigns();
	#$page->gfObj->debug_print($campaignList,1);


	$x = $page->rip_all_block_rows();
	#$page->gfObj->debug_print(array_keys($x),1);
	$rowName = 'noCampaignRow';
	$finalRow = $page->templateRows[$rowName];
	$idObfuscator = new cs_idObfuscator();
	if(is_array($campaignList) && count($campaignList)) {
		$rowName = 'campaignRow';
		$finalRow = "";
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
