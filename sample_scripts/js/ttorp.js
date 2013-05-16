/*
$Id: ttorp.js 127 2011-03-30 02:07:24Z crazedsanity $
*/
/*======================================
       ++ INPUT MARKING...
  ====================================*/
function markDirtyInput(object) {
	clearUpdatedInput(object);
	$(object).addClass('dirtyInput');
}
function clearDirtyInput(object) {
	if($(object).hasClass('dirtyInput')) {
		$(object).removeClass('dirtyInput');
	}
	if($(object).attr("readonly") == true && !$(object).hasClass("derived")) {
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
		$(object).data("last_value", newVal);
		$(object).addClass("updatedInput");
		
		if($(object).attr('id')) {
			var myId = $(object).attr('id');
			
			/* Special: update any input that has a class name that matches the given id: this 
			 * helps deal with things like the "base attack bonus" field for ranged/melee, as 
			 * they are technically repeats of the original/master value.
			//*/
			if(myId != undefined && $("."+ myId).length >0) {
				$("."+ myId).val(newVal).addClass("updatedInput");
			}
		}
	}
	if($(object).attr("id") == 'main__character_name') {
		//Character name changed, update the page title!
		var bits = document.title.split(" - ");
		document.title = $("#main__character_name").val() + " - " + bits[(bits.length -1)];
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
	clearDirtyInput(object);
	$(object).addClass("processingInput");
	$(object).attr("readonly",true);
	$(object).attr("disabled",true);
	
	//Update the "last_value" info, it doesn't attempt to get processed again WHILE it is being processed.
	$(object).data("last_value", $(object).val());
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
		$(object).attr("readonly",false);
		$(object).attr("disabled",false);
	}
}
/*======================================
       XX INPUT MARKING...
  ====================================*/


/*======================================
       ++ AJAX STUFF
	   TODO: the function "ajax_doPost()" is currently NOT shared... should be 
	   		copied from CrazedSanity.com's JS library to cs-content so it is 
			fully accessible.
  ====================================*/
function ajax_processChange(divId) {
	var myVal = $("#"+divId).val();
	if($("#"+ divId).attr("type") == "checkbox") {
		myVal = $("#"+ divId).attr("checked");
	}
	var postArray = {
		'character_id'	: $("#form_character_id").val(),
		'name'			: divId,
		'value'			: myVal
	};
	
	ajax_doPost("member/ttorp/character_updates", postArray);
}

function callback_showUpdatedInput(xmlObj) {
	//TODO: limit the call below to avoid removing the "updatedInput" status from places where it was just added...
	//$("input,select").removeClass("updatedInput");
	var forceNameChange=null;
	var i = 0;
	var x = 0;
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
function callback_processNewRecord(xmlObj) {
	alert("ERROR\n\nAttempted to process new record the OLD way.  Ick.");
}
/*======================================
       XX AJAX STUFF...
  ====================================*/

function processChange(object) {
	if(isDirtyInput(object)) {
		if(isNaN($(object).val()) && !$(object).hasClass('freestyle') && $(object).attr("type") != 'checkbox') {
			//let 'em know they did a stupid.
			alert("WRONG DATA TYPE\n\nThat input is numeric only; your value ("+ $(object).val() +") isn't really numeric, is it?");
			
			//reset the data.
			$(object).val($(object).data('last_value'));
			clearDirtyInput($(object));
		}
		else {
			//remove "updated" status from any updated inputs.
			$("input,select").removeClass("updatedInput");
			
			if($(object).hasClass("newRecord") || $(object).attr("id").match(/__new/)) {
				//only process the change if they're on a TEXT input whose ID ends in "__new"
				if($(object).attr("id").match(/__new/)) {
					//get the table name based on the ID.
					var bits = $(object).attr("id").split("__");
					var tableName = bits[0];
					var inputName = tableName +"__new";
					
					//this is a NEW RECORD; there's record cloning and ID changing to be done.
					//BEFORE the change, do some stuff so they know the change is pending...
					
					loaderId = tableName +"__loader";
					$("#"+ loaderId).show();
					
					//process the actual change...
					ajax_processNewRecord(tableName);
					
					//mark it as being processed.
					markProcessingInput(object);
				}
			}
			else {
				//NOTE::: the change MUST be submitted before marking it as being processed so "new" records will work properly.
				var id = $(object).attr('id');
				$("#"+ id).attr('readonly', 'readonly');
				ajax_processChange(id);
				markProcessingInput(object);
			}
		}
	}
}

function showNewRecordDialog(pDialogId) {
	$("#"+ pDialogId).dialog({
		modal:	true,
		close:	function(event,ui) {
			$(this).dialog('destroy');
		}
	});
	$("div.ui-dialog button.submit").click(function() {
		submitNewRecordDialog(this);
	});
}

function submitNewRecordDialog(pButtonObj) {
	
	// First, make sure we've got everything we need.
	var myData = $(pButtonObj).parents("div.form").children("input,textarea,checkbox,select").serialize();
	
	var sectionToReload = $(pButtonObj).parents("div.form").children("input[name='tableName']").val();
	var divToReloadInto = 'load__' + sectionToReload;
	
	if($('#'+ sectionToReload) && $("#" + divToReloadInto) && myData.length) {
		
		//change the URL we're using the proper AJAX one.
		var submitUrl = "/ajax" + window.location.pathname.replace(/\/sheet$/, "_updates");
		var fetchUrl = window.location.href;
		
		if(myData.length > 0) {
			// Make all the items readonly, so users can't try updating while the section reloads
			$("#"+ sectionToReload).find("input").attr("readonly", "readonly");
			
			
			// Now send the update.
			$.ajax({
				type: "POST",
				url: submitUrl,
				data: myData,
				success: function(tData) {
					//alert("GOT DATA BACK::: "+ tData);
					$("#"+ divToReloadInto).load(fetchUrl + " #"+ sectionToReload);
					$("#dialog__"+ sectionToReload).dialog('close').dialog('destroy');
					
					/// Without the next line, each additional attempt to add will cause it to submit multiple times... so the third will submit three, fourth will submit four, etc.
					$("#dialog__"+ sectionToReload +" button.submit").unbind('click');
					
					// NOTE::: the response actually needs to list these items... currently, it does not.
					callback_showUpdatedInput(tData);
				}
			});
		}
		else {
			alert("ERROR:\n\nNo data submitted... ");
		}
	} 
}

function handlePendingChanges() {
	//this will submit changes for all pending (dirty) inputs (that are NOT new records).
	//NOTE::: this is intended as a "fail-safe", and probably only for testing...
	$("input.dirtyInput").each(function(item, object){
		processChange(object);
	});
}

function doHighlighting(object, mouseEvent) {
	if($(object).attr("class") != undefined) {
		var bits = $(object).attr("class").split(' ');
		var littleBits = undefined;
		var i = 0;
		for(i=0; i<bits.length; i++) {
			if(bits[i].match(/^hl--/)) {
				littleBits = bits[i].split('--', 2);
				highlightField(littleBits[1]);
			}
		}
	}
}
function highlightField(id) {
	if($("#"+ id).hasClass("highlight")) {
		$("#"+ id).removeClass("highlight");
	}
	else {
		$("#"+ id).addClass("highlight");
	}
}


$(document).ready(function() {
	$("input,select,textarea").each(function(i) {
		  $(this).data('last_value', $(this).val());
		  $(this).data('old_bgcolor', $(this).css("background-color"));
	});
	
	$("input,textarea").not(".derived").keyup(function() {
		if($(this).attr("id")) {
			if ($(this).val() != $(this).data('last_value')) {
				markDirtyInput(this);
			}
		}
	});
	$("input,textarea").not(".derived").bind('paste', function() {
		markDirtyInput(this);
	});
	$("input,textarea").not(".derived").blur(function() {
		if(isDirtyInput(this)) {
			processChange(this);
		}
	});
	$("input[type=checkbox]").not(".newRecord").click(function() {
		markDirtyInput(this);
		processChange(this);
	});
	$("select").not(".newRecord").change(function() {
		markDirtyInput(this);
		processChange(this);
	});
	
	//Highlighting associated fields (i.e. highlight the main "base attack bonus" for melee/ranged so they know what to modify).
	$("input[class*='hl--']").mouseover(function(){
		doHighlighting(this);
	});
	$("input[class*='hl--']").mouseout(function(){
		doHighlighting(this);
	});
	
	//Disable non-name inputs for new record rows...
	$("#characterWeapon tr.newRecord input").not(".nameField").attr("readonly",true);
	$("#characterArmor tr.newRecord input").not(".nameField").attr("readonly",true);
	$("#skills tr.newRecord input").not(".nameField").attr("readonly",true);
	$("#specialAbility tr.newRecord input").not(".nameField").attr("readonly",true);
	$("#gear tr.newRecord input").not(".nameField").attr("readonly",true);
});
