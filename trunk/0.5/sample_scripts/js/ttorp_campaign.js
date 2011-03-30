/*
$Id$
*/

function createDialog(pIdOfDialog) {
	$("#"+ pIdOfDialog).dialog({
		buttons: {
			"Ok": 		function() { $(this).dialog("close"); },
			"Cancel":	function() { $(this).dialog("close"); }
		},
		modal: true,
		effect: 'slide'
	});
	return(true);
}//end createDialog()
