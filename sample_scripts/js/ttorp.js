
var globalUpdateDelay = 2000;

/*======================================
       ++ INPUT MARKING...
  ====================================*/
function markDirtyInput(object) {
	clearUpdatedInput(object);
	$(object).addClass('dirtyInput');
	
	// now set a timer so the change gets logged.
	if($(object).data("timeout")) {
		clearTimeout($(object).data("timeout"));
	}
	
	if($(object).val() !== undefined && $(object).val() !== '') {
		$(object).data('timeout', setTimeout(function() {
			processChange(object);
		}, globalUpdateDelay));
	}
	else if(($(object).val() === undefined || $(object).val() === '') && ($(object).hasClass('nullable'))) {
		$(object).data('timeout', setTimeout(function() {
			processChange(object);
		}, globalUpdateDelay));
	}
	else {
		// there's something we didn't like... so we'll revisit it in a second.
		$(object).data('timeout', setTimeout(function() {
			markDirtyInput(object);
		}, 1000));
	}
}
function clearDirtyInput(object) {
	if($(object).hasClass('dirtyInput')) {
		$(object).removeClass('dirtyInput');
	}
	if($(object).attr("readonly") === true && !$(object).hasClass("derived")) {
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
	if(forceChange === undefined || forceChange === null) {
		forceChange=false;
	}
	
	var originalVal = $(object).val();
	var updateTextInstead = false;
	if($(object).is("span") || $(object).is("div")) {
		originalVal = $(object).text();
		updateTextInstead = true;
	}
	var myId = $(object).attr('id');
	
	clearDirtyInput(object);
	
	//TODO: this "if" statement causes a lot of timing issues (resetting values in one field when another finishes updating)
	if(originalVal !== newVal || forceChange) {
		if(updateTextInstead === true) {
			$(object).text(newVal);
		}
		else {
			$(object).val(newVal);
		}
		$(object).data("last_value", newVal);
		$(object).addClass("updatedInput");
		
		if($(object).attr('id')) {
			
			
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
//		$(object).attr("readonly",false);
//		$(object).attr("disabled",false);
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
	
	//TODO: see issue #42 (https://github.com/crazedsanity/cs-battletrack/issues/42)
	
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
			
			//NOTE::: the change MUST be submitted before marking it as being processed so "new" records will work properly.
			var id = $(object).attr('id');
//			$("#"+ id).attr('readonly', 'readonly');
			ajax_processChange(id);
			markProcessingInput(object);
		}
	}
}

function showNewRecordDialog(pDialogId) {
	var theTitle = "Create Record";
	if($("#"+ pDialogId +" div.hidden.title").text() != undefined) {
		theTitle = $("#"+ pDialogId +" div.hidden.title").text();
	}
	$("#"+ pDialogId).dialog({
		modal: true,
		title: theTitle,
		position: 'top'
	});
}

var xButton = null;

function submitNewRecordDialog(pButtonObj) {
	xButton = pButtonObj;
	
	// First, make sure we've got everything we need.
	var myData = $(pButtonObj).parents("div.form").find("input,textarea,checkbox,select").serialize();
	
	var sectionToReload = $(pButtonObj).parents("div.form").children("input[name='tableName']").val();
	var divToReloadInto = 'load__' + sectionToReload;
	
	
	if($('#'+ sectionToReload) && $("#" + divToReloadInto) && myData.length) {
		
		//change the URL we're using the proper AJAX one.
		var submitUrl = "/ajax" + window.location.pathname.replace(/\/sheet/, "_updates");
		var fetchUrl = window.location.href;
		
		if(myData.length > 0) {
			
			// Now send the update.
			$.ajax({
				type: "POST",
				url: submitUrl,
				data: myData,
				success: function(tData) {
					$("#"+ divToReloadInto).load(fetchUrl + " #"+ sectionToReload, function() {
						//alert("Content loaded, time to do stuff");
						bindInputMarking(divToReloadInto);
						bindInputSwitching();
					});
					
					$("#dialog__"+ sectionToReload).dialog('close');
					
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
	else {
		alert("ERROR:\n\nThere there is a section missing ("+ sectionToReload +") or ("+ divToReloadInto +")");
	}
}

function doHighlighting(object, mouseEvent) {
	if($(object).attr("class") != undefined) {
		var bits = $(object).attr("class").split(' ');
		var littleBits = undefined;
		for(var i=0; i<bits.length; i++) {
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
	
	if($("."+ id).hasClass("highlight")) {
		$("."+ id).removeClass("highlight");
	}
	else {
		$("."+ id).addClass("highlight");
	}
}

var xObj = null;
function swapCheckboxImg(pObj) {
	xObj = pObj;
	var mySrc = $(pObj).attr("src");
	var newSrc = mySrc;
	if(mySrc.match("checked.jpg")) {
		newSrc = mySrc.replace(/checked.jpg/, '.jpg');
	}
	else {
		newSrc = mySrc.replace(/checkbox.jpg/, 'checkboxchecked.jpg');
	}
	
	$(pObj).attr("src", newSrc);
	
	var hiddenChk = $(pObj).parent("td").children("input.hidden.chk");
	
	var xOldVal = $(hiddenChk).attr("checked");
	var xNewVal = xOldVal;
	
	
	if(xOldVal === false || xOldVal === undefined) {
		xNewVal = "checked";
		$(hiddenChk).attr('checked', 'checked');
	}
	else {
		xNewVal = "";
		$(hiddenChk).removeAttr('checked');
	}
	
	ajax_processChange($(hiddenChk).attr("id"));
}

function bindInputMarking(pId) {
	// if we've got an ID, add a prefix when selecting so it only applies to a subset; otherwise, apply it to everything.
	var tPrefix = "";
	if(pId != undefined && pId != null && pId.length > 1) {
		tPrefix = "#"+ pId + " ";
	}
	
	
	$(tPrefix + "input,select,textarea").each(function(i) {
		  $(this).data('last_value', $(this).val());
		  $(this).data('old_bgcolor', $(this).css("background-color"));
	});
	
			
	$(tPrefix + "input,textarea").not(".derived").keyup(function() {
		if($(this).attr("id")) {
			if ($(this).val() != $(this).data('last_value')) {
				markDirtyInput(this);
			}
		}
	});
	$(tPrefix + "input,textarea").not(".derived").bind('paste', function() {
		markDirtyInput(this);
	});
	$(tPrefix + "input[type=checkbox]").not(".newRecord").click(function() {
		markDirtyInput(this);
		processChange(this);
	});
	$(tPrefix + "select").not(".newRecord").change(function() {
		markDirtyInput(this);
		processChange(this);
	});
}

function bindInputSwitching() {
	$("tr.slot.data td").click(function() {
		switchToInput($(this));
	});
}

var xDebug;
function switchToInput(target) {
	if(!$(target).children("span").first().hasClass('derived') && $(target).data('inputSwitch') === undefined) {
		var useWidth = $(target).width();
		
		$(target).data('obj', $(target).children('span').first());
		var theSpan = $(target).children("span").first();
		xDebug = theSpan;
		
		var theInput = document.createElement("input");
		theInput.type="text";
		theInput.className=$(theSpan).attr("class");
		theInput.id = $(theSpan).attr("id");
		theInput.value = $(theSpan).text();
		$(theInput).css('width', useWidth);
		$(target).css('width', useWidth);
		
		$(target).data('inputSwitch', $(target).children("span").first());
		
		$(target).children("span").first().replaceWith(theInput);
		$(theInput).addClass("temp").focus();
		
		bindInputMarking();
	}
}

function togglePrintable() {
	return($(".templateSection").not("#main__content").toggle());
}

function showDeleteRecordDialog(pTitle, pId, pName, pReloadSection) {
	theTitle = "Deleting " + pTitle + " (ID #" + pId + ")";
	xDebug = this;
	
	$("#deleteRecordId").val(pId);
	$("#deleteRecordDescription").html(pName);
	$("#deleteRecord_reloadSection").val(pReloadSection);
	$("#dialog__delete").dialog({
		modal: true,
		title: theTitle,
		position: 'top'
	});
}

function deleteRecord() {
	$("#dialog__delete").dialog("close");
	
	var submitUrl = "/ajax" + window.location.pathname.replace(/\/sheet$/, "_updates");
	var fetchUrl = window.location.href;
	var sectionToReload = $("#deleteRecord_reloadSection").val();
	var divToReloadInto = 'load__'+ sectionToReload;
		
	var myData = {
		type:			'delete',
		recordType:		$("#deleteRecord_reloadSection").val(),
		character_id:	$("#form_character_id").val(),
		recordId:		$("#deleteRecordId").val()
	};
	
	$.ajax({
		type: "POST",
		url: submitUrl,
		data: myData,
		success: function(tData) {
			$("#" + divToReloadInto).load(fetchUrl + " #" + sectionToReload, function() {
				bindInputMarking(divToReloadInto);
				bindInputSwitching();
			});
		}
	});
}


$(document).ready(function() {
	togglePrintable();
	bindInputMarking(undefined);
	
	//Highlighting associated fields (i.e. highlight the main "base attack bonus" for melee/ranged so they know what to modify).
	$("input[class*='hl--']").mouseover(function(){
		doHighlighting(this);
	});
	$("input[class*='hl--']").mouseout(function(){
		doHighlighting(this);
	});
	
	$("img.chk").click(function() {
		swapCheckboxImg($(this));
	});
	
	bindInputSwitching();
	
});
