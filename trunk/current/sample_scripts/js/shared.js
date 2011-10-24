/* SVN INFORMATION
 *  $Id$
 */

var _returnData = undefined;

function ajax_doGet(pType, pCallback) {
	var tBaseUrl = '/ajax/member/ttorp/';
	var isAsync = true;
	
	tMyUrl = tBaseUrl;
	if(pType.length > 0) {
		tMyUrl = tBaseUrl + pType;
	}
	
	$.ajax ({
		url			: tMyUrl,
		cache		: false,
		async		: isAsync,
		dataType	: 'text/xml',
		timeout		: (30 * 1000),
		success: function (returnJSON) {
			var tJson = $.parseJSON(returnJSON);
			_returnData = tJson;
			if(pCallback != undefined) {
				eval(pCallback +'(tJson)');
			}
		},
		error: function (returnXml) {
		}
	});
}


function test(pJsonObj) {
	alert(pJsonObj.returnData.character_name);
}//end test()



function ajax_doPost(formName, postData, msgTitle, msgBody, isAsync) {
	if(msgTitle != undefined && msgTitle != null && msgTitle.length) {
		$.growlUI(msgTitle, msgBody);
	}
	
	if(isAsync == undefined) {
		isAsync = true;
	}
	
	var myUrl = "/ajax/" + formName;
	var timestamp = Number(new Date());
	myUrl = myUrl + "?_=" + timestamp;
	
	$.ajax({
		url: myUrl,
		type	: "POST",	
		data	: postData,
		timeout	: (30 * 1000),
		success	: ajax_successCallback
	});
}

