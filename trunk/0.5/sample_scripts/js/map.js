function mapStorage() {
	var _selectedPiece = null;
	var _pieces = new Array();
	var _locations = new Array();
	
	// Identifiers...
	var _lastPieceId = null;
	
	/** Add a piece by name, get an ID. */
	this.AddPiece = function(pName, pLocation) {
		if(_lastPieceId == null) {
			_lastPieceId = 0;
		}
		else {
			_lastPieceId++;
		}
		_pieces[_lastPieceId] = pName;
		_locations[_lastPieceId] = pLocation;
		
		//add the piece to the map.
		$("#"+ pLocation).html(pName);
		return(_lastPieceId);
	}//end AddPiece()
	
	/** Retrieve a piece's name based on an ID. */
	this.GetPieceById = function(pId) {
		return(_pieces[pId]);
	}//end GetPieceById()
	
	/**  */
	this.GetPieceLocation = function(pId) {
		return(_locations[pId]);
	}//end GetPieceLocation()
	
	
	/** Move a piece. */
	this.MovePiece = function(pId, pNewLocation) {
		var tRetval = false;
		if(_pieces[pId] != undefined) {
			//move from the old location.
			this.MakeSpaceEmpty(_locations[pId]);
			
			// update to a new location.
			_locations[pId] = pNewLocation;
			
			$("#"+ _locations[pId]).html(_pieces[pId]);
			
			tRetval = true;
		}
		return(tRetval);
	}//end MovePiece()
	
	/** Make the space (pId == ID of cell on page) empty */
	this.MakeSpaceEmpty = function(pId) {
		$("#"+ pId).html("&nbsp;");
	}//end MakeSpaceEmpty()
	
	/**  */
	this.CalculateAddAlpha = function(pAddToThis) {
	}//end CalculateAddAlpha()
}

function _AlphaCalc() {
	
	var _AlphaArr = new Array();
	
	this.BuildArray = function() {
		if(_AlphaArr.length != 26) {
			_AlphaArr = new Array();
			var tStr = "abcdefghijklmnopqrstuvwxyz";
			var tBits = tStr.split("");
			for(i=0;i<tBits.length;i++) {
				var x = (i+1);
				var ttChar= tBits[i];
				_AlphaArr[ttChar] = x;
				//alert("i=("+ i +"), x=("+ x +"), ttChar=("+ ttChar +")");
			}
			//alert("done... test 'a'=("+ _AlphaArr['a'] +")");
		}
	}//end BuildArray()
	
	this.AlphaToNum = function(pStr) {
		this.BuildArray();
		tRetval = 0;
		var tBits = pStr.split("");
		
		for(var i=0; i<tBits.length;i++) {
			var tIndex = tBits[i];
			tRetval += _AlphaArr[tIndex];
		}
		return(tRetval);
	}//end AlphaToNum()
}

//create an object
_map = new mapStorage();
obj = new _AlphaCalc();


$(document).ready(function(){
	$("table.ttorp tr td").each(function(){
		var tMyId = $(this).attr("id");
		_map.MakeSpaceEmpty(tMyId);
	});
});
