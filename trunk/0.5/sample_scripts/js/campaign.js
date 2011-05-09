/*
$Id$
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
	$(".closeForm").click(function() {
		$("#addPlayer").dialog("close");
		_activeDialog = undefined;
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

$(document).ready(function() {
	$("#playerId").autocomplete(function() {
		source: 
	});
});

