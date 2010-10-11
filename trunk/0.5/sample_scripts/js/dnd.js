
var originalValue = [];

function markDirtyInput(object) {
	clearUpdatedInput(object);
	$(object).addClass('dirtyInput');
}
function clearDirtyInput(object) {
	if($(object).hasClass('dirtyInput')) {
		$(object).removeClass('dirtyInput');
	}
	if($(object).attr("readonly") == true) {
		$(object).attr("readonly",false);
	}
	clearUpdatedInput(object);
	clearProcessingInput(object);
}
function isDirtyInput(object) {
	var retval = false;
	if($(object).hasClass('dirtyInput')) {
		retval = true;
	}
	return(retval);
}
function markUpdatedInput(object, newVal, forceChange) {
	if(forceChange == undefined || forceChange == null) {
		forceChange=false;
	}
	clearDirtyInput(object);
	if($(object).val() != newVal || forceChange) {
		$(object).val(newVal);
		$(object).addClass("updatedInput");
	}
	if($(object).attr("id") == 'main__character_name') {
		//
		var bits = document.title.split(" - ");
		document.title = $("#main__character_name").val() + " - " + bits[1];
	}
}
function clearUpdatedInput(object) {
	if($(object).hasClass('updatedInput')) {
		$(object).removeClass('updatedInput');
	}
}
function isUpdatedInput(object) {
	var retval = false;
	if($(object).hasClass('updatedInput')) {
		retval = true;
	}
	return(retval);
}

function markProcessingInput(object) {
	if($(object).attr('id').match(/__new__/)) {
		//special handling for "NEW" records...
		//If the ID is "gear__new__stuff", this will remove the "stuff" table & replace it with a loading image.
		var bits = $(object).attr('id').split('__');
		$("#"+ bits[2]).slideUp();
		$("#"+ bits[2] +" input").attr("disabled", "disabled");
		$("#"+ bits[2]).html('<img src="/images/ajax-loader.gif">Loading new '+ bits[2]);
		$("#"+ bits[2]).slideDown();
	}
	else {
		$(object).addClass("processingInput");
	}
}
function isProcessingInput(object) {
	var retval = false;
	if($(object).hasClass("processingInput")) {
		retval = true;
	}
	return(retval);
}
function clearProcessingInput(object) {
	if($(object).hasClass("processingInput")) {
		$(object).removeClass("processingInput");
	}
}

function ajax_processChange(divId) {
	var postArray = {
		'character_id'	: $("#form_character_id").val(),
		'name'			: divId,
		'value'			: $("#"+divId).val()
	}
	
	ajax_doPost("member/ttorp/character_updates", postArray);
}
function ajax_processNewRecord(tableName) {
	var postArray = {
		'character_id'	: $("#form_character_id").val()
	}
	$("#" + tableName +" .newRecord").each(function(tIndex,tObject) {
		postArray[$(tObject).attr("id")] = $(tObject).val();
	});
	ajax_doPost("member/ttorp/character_updates", postArray);
}
function ajax_showUpdatedInput(xmlObj) {
	$("input").removeClass("updatedInput");
	var forceNameChange=null;
	if($(xmlObj).find('id_was').text()) {
		forceNameChange = $(xmlObj).find('id_was').text();
	}
	if($(xmlObj).find('changesbykey').text()) {
		
		for (var iNode = 0; iNode < xmlObj.childNodes.length; iNode++) {
			var node = xmlObj.childNodes.item(iNode);
			for (i = 0; i < node.childNodes.length; i++) {
				var sibling = node.childNodes.item(i);
				for (x = 0; x < sibling.childNodes.length; x++) {
					var forceChange = false;
					var sibling2 = sibling.childNodes.item(x);
					if(sibling2.nodeName == forceNameChange) {
						forceChange=true;
					}
					if (sibling2.childNodes.length > 0) {
						var sibling3 = sibling2.childNodes.item(0);
						markUpdatedInput($("#" + sibling2.nodeName), sibling3.data, forceChange);
					}
					else if(sibling2.nodeName.match(/^[aZ-zZ]/)) {
						//This handles clearing-out data.
						markUpdatedInput($("#" + sibling2.nodeName), "", forceChange);
					}
				}
			}
		}
	}
}

function processChange(object) {
	if(isDirtyInput(object) && $(object).not(".newRecord")) {
		//NOTE::: the change MUST be submitted before marking it as being processed so "new" records will work properly.
		$("#"+ $(object).attr('id')).attr('readonly', 'readonly');
		ajax_processChange($(object).attr('id'));
		markProcessingInput(object);
	}
}
function handlePendingChanges() {
	//this will submit changes for all pending (dirty) inputs (that are NOT new records).
	$("input.dirtyInput").not(".newRecord").each(function(item, object){
		//alert("found dirty input: "+ $(object).attr("id") +")");
		processChange(object);
	});
}


$(document).ready(function() {
	$("input,select,textarea").each(function(i) {
		  $(this).data('last_value', $(this).val());
	});
	
	$("input,select,textarea").keyup(function() {
		if ($(this).val() != $(this).data('last_value')) {
			markDirtyInput(this);
		}
	});
	$("input,select,textarea").bind('paste', function() {
		//alert("Something was pasted... (" + $(this).attr("id") + ")");
		markDirtyInput(this);
	});
	$("input,select,textarea").blur(function() {
		processChange(this);
	});
	
	/*
	$("input").focus(function(event) {
		//store the original value so the AJAX is called only when something has actually changed 
		//	(protects against submitting non-changes when user presses <tab>, etc)
		//originalValue[$(this).attr("id")] = $(this).val();
	});
	$("textarea").focus(function(event) {
		//originalValue[$(this).attr("id")] = $(this).val();
	});
	$("input").keyup(function(event) {
		setInputDirty(this);
	});
	//TODO: make this event work!!!
	$("select").keyup(function(event) {
		//ajax_processChange($(this).attr('id'));
		processChange(this);
	});
	$("input").blur(function() {
		processChange(this);
	});
	$("textarea").change(function(event) {
		markDirtyInput(this);
	});
	$("textarea").blur(function() {
		processChange(this);
	});
	//*/
});
