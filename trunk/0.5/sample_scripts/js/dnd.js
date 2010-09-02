

var originalValue = null;
function markDirtyInput(object) {
	/*
	if($(object).hasClass('redFade')) {
		$(object).removeClass('redFade');
	}
	*/
	$(object).addClass('redBg');
}
function clearDirtyInput(object) {
	if($(object).hasClass('redBg')) {
		$(object).removeClass('redBg');
	}
	//$(object).addClass('redFade');
	
}
function isDirtyInput(object) {
	var retval = false;
	if($(object).hasClass('redBg')) {
		retval = true;
	}
	return(retval);
}

function ajax_processChange(divId) {
	var postArray = {
		'character_id'	: $("#form_character_id").val(),
		'name'			: divId,
		'value'			: $("#"+divId).val()
	}
	
	ajax_doPost("member/ttorp/character_updates", postArray);
}
function ajax_showUpdatedInput(xmlObj) {
	$("input").removeClass("yellowBg");
	if($(xmlObj).find('changesbykey').text()) {
		
		for (var iNode = 0; iNode < xmlObj.childNodes.length; iNode++) {
		   var node = xmlObj.childNodes.item(iNode);
		   for (i = 0; i < node.childNodes.length; i++) {
		      var sibling = node.childNodes.item(i);
		      for (x = 0; x < sibling.childNodes.length; x++) {
		         var sibling2 = sibling.childNodes.item(x);
		         if (sibling2.childNodes.length > 0) {
		            var sibling3 = sibling2.childNodes.item(0);
		            
		            $("#" + sibling2.nodeName).val(sibling3.data);
		            $("#" + sibling2.nodeName).addClass("yellowBg");
		         }
		      }
		   }
		}
	}
}


$(document).ready(function() {
	$("input").focus(function(event) {
		//store the original value so the AJAX is called only when something has actually changed 
		//	(protects against submitting non-changes when user presses <tab>, etc)
		originalValue = $(this).val();
	});
	$("textarea").focus(function(event) {
		originalValue = $(this).val();
	});
	$("input").keyup(function(event) {
		if($(this).val() != originalValue) {
			markDirtyInput(this);
		}
	});
	$("input").blur(function() {
		
		if(isDirtyInput(this)) {
			ajax_processChange($(this).attr('id'));
		}
		clearDirtyInput(this);
	});
	$("textarea").keypress(function(event) {
		if($(this).val() != originalValue) {
			markDirtyInput(this);
		}
	});
	$("textarea").blur(function() {
		if(isDirtyInput(this)) {
			ajax_processChange($(this).attr('id'));
		}
		clearDirtyInput(this);
	});
});
