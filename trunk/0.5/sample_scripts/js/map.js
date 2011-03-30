function mapStorage() {
	this.selectedPiece = null;
	this.pieces = new Array();
}

//create an object
_map = new mapStorage();

function space_makeEmpty(id) {
	$("#"+ id).html("&nbsp;");
}

function space_fill(pId, pHtml) {
	$("#"+ pId).html(pHtml);
}

function piece_add(pHtml, pId) {
	space_fill(pId, pHtml);
}

function piece_move(oldId, newId){
	var tMyData = $("#"+ oldId).html();
	space_makeEmpty(oldId);
	piece_add(tMyData, newId);
}

$(document).ready(function(){
	$("table.ttorp tr td").each(function(){
		var tMyId = $(this).attr("id");
		space_makeEmpty(tMyId);
	});
});
