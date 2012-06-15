/*
$Id: campaign.js 151 2011-05-11 00:55:19Z crazedsanity $
*/

var _activeDialog = undefined;
var _debugLastJson = undefined;

function addCampaignDialog(pIdOfDialog) {
	$("#"+ pIdOfDialog).dialog({
		buttons: {},
		modal: true
	});
	_activeDialog = pIdOfDialog;
	$(".closeForm").click(function(){
		$("#"+ pIdOfDialog).dialog("close");
		_activeDialog = undefined;
	});
	return(true);
}//end addCampaignDialog()

function addPlayerDialog(pIdOfCampaign) {
	$("#addPlayer").dialog({
		buttons: {},
		modal: true
	});
	_activeDialog = "addPlayer";
	$("#addPlayer_campaignName").text($("#campaignId_"+ pIdOfCampaign).text());
	$("#addPlayerCampaignId").val(pIdOfCampaign);
	$(".closeForm").click(function() {
		$("#addPlayer").dialog("close");
		_activeDialog = undefined;
	});
	$("#playerId").autocomplete({
		source: "/ajax/member/ttorp/campaign/charSearch",
		minLength: 2,
		select: function( event, ui ) {
			//alert("event: ("+ event +")");
			$("#addPlayerSubmit").removeAttr("disabled");
			$("#playerId").val(ui.item.id);
			$("#addPlayer form").submit();
		}
	});
}//end addPlayerDialog()


function getPlayerInfo(pId) {
	var tJson = ajax_doGet('campaign/charSearch/' + pId, 'updatePlayerName');
	_debugLastJson = tJson;
}//end getPlayerInfo()


function updatePlayerName(pJson) {
	if(pJson.status > 0 && pJson.returnData.character_name != undefined) {
		$("#addPlayer_name").text(pJson.returnData.character_name);
	}
}//end updatePlayerName()


