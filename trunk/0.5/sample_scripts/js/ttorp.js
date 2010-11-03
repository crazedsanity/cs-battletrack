
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
	}
	
	ajax_doPost("member/ttorp/character_updates", postArray);
}
function ajax_processNewRecord(tableName) {
	var postArray = {
		'type'			: "newRecord",
		'tableName'		: tableName,
		'character_id'	: $("#form_character_id").val()
	}
	var numItems = 0;
	var numWithVals = 0;
	//$("#" + tableName +" input.newRecord, #"+ tableName +" text.newRecord, #"+ tableName +" select.newRecord").each(function(tIndex,tObject) {
	$("#"+ tableName +" tr.newRecord input, #"+ tableName +" tr.newRecord text, #"+ tableName +" tr.newRecord select").each(function(tIndex,tObject) {
		numItems++;
		if($(tObject).val().length > 0) {
			var myVal = $(tObject).val();
			if($(tObject).attr("type") == "checkbox") {
				myVal = $(tObject).attr("checked");
			}
			postArray[$(tObject).attr("id")] = myVal;
			numWithVals++;
			if($(tObject).hasClass('nameField')) {
				postArray['nameField'] = $(tObject).attr('id');
			}
		}
	});
	if(numItems > 0) {
		ajax_doPost("member/ttorp/character_updates", postArray);
	}
	else {
		alert("No items to process in ("+ tableName +")");
	}
}

function callback_showUpdatedInput(xmlObj) {
	//TODO: limit the call below to avoid removing the "updatedInput" status from places where it was just added...
	//$("input,select").removeClass("updatedInput");
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
function callback_processNewRecord(xmlObj) {
	//All we really need is the new ID...
	if($(xmlObj).find('newRecordId').val() != null && $(xmlObj).find('tableName') != null) {
		var newId = $(xmlObj).find('newRecordId').text();
		var tableName = $(xmlObj).find('tableName').text();
		cloneRow(tableName, newId);
		
		//Re-enable the "nameField" in the new record...
		$("#"+ tableName +" .newRecord .nameField").attr("readonly",false).each(function() {
			clearDirtyInput(this);
		});
		callback_showUpdatedInput(xmlObj);
	}
	else {
		//TODO: handle this more elegantly, like by putting it in a "growl" box or something.
		alert("unable to finalize new record, could not find newRecordId...");
	}
}
/*======================================
       XX AJAX STUFF...
  ====================================*/

function processChange(object) {
	if(isDirtyInput(object)) {
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
function handlePendingChanges() {
	//this will submit changes for all pending (dirty) inputs (that are NOT new records).
	//NOTE::: this is intended as a "fail-safe", and probably only for testing...
	$("input.dirtyInput").each(function(item, object){
		processChange(object);
	});
}

function doHighlighting(object, mouseEvent) {
	var myMouseEvent = mouseEvent;
	if(mouseEvent != 'over' && mouseEvent != 'out') {
		myMouseEvent = 'out';
	}
	if($(object).attr("class") != undefined) {
		var bits = $(object).attr("class").split(' ');
		var littleBits = undefined;
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

function cloneRow(tableName, newId) {
	//create a clone of an existing row...
	var newRow = $("#"+ tableName +" tr.newRecord");
	var copiedNewRow = $("#"+ tableName +" tr.newRecord").clone(true);
	
	//now change ID's of inputs for that existing record...
	newRow.find("input,select").each(function() {
		//update the ID properly.
		var currentId = $(this).attr("id");
		var updatedId = currentId.replace(/__new$/, "__"+ newId);
		$(this).attr("id", updatedId);
		
		//if there is a "title" on the input with an ID, fix it.
		if($(this).attr("title") && $(this).attr("title").match(/ID #new/)) {
			var myTitle = $(this).attr("title").replace(/ID #new/, "ID #"+ newId);
			$(this).attr("title", myTitle);
		}
		
		//show that it has been updated...
		markUpdatedInput(this);
	});
	
	//minor changes for title tags...
	newRow.find("td").each(function() {
		if($(this).attr("title").match(/ID #new/)) {
			var myTitle = $(this).attr("title").replace(/ID #new/, "ID #"+ newId);
			$(this).attr("title", myTitle);
		}
	});
	
	//remove the "newRecord" class from everything BEFORE appending the copied record.
	$("#"+ tableName +" .newRecord").removeClass("newRecord").removeClass("footer");
	
	//append it to the bottom of the table.
	$("#"+ tableName).append(copiedNewRow);
	
	//TODO: move the "footer" row to the bottom...
	$("#"+ tableName).append($("#"+ tableName +" tr.footer"));
	$("#"+ tableName +" tr.newRecord").find("input[type='text']").attr("value", "");
}


$(document).ready(function() {
	$("input,select,textarea").each(function(i) {
		  $(this).data('last_value', $(this).val());
		  $(this).data('old_bgcolor', $(this).css("background-color"));
	});
	
	$("input,textarea").not(".derived").keyup(function() {
		if ($(this).val() != $(this).data('last_value')) {
			markDirtyInput(this);
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
