function mapStorage() {
	var _selectedPiece = undefined;
	var _pieces = new Array();
	var _locations = new Array();
	
	// Identifiers...
	var _lastPieceId = null;
	
	
	
	/** Add a piece by name, get an ID. */
	this.AddPiece = function(pName, pLocation) {
		var tNewId = undefined;
		if(_lastPieceId == null) {
			_lastPieceId = 1;
		}
		else {
			_lastPieceId++;
		}
		tNewId = _lastPieceId;
		_pieces[tNewId] = pName;
		_locations[tNewId] = pLocation;
		this._DisplayPiece(tNewId);
		
		//add the piece to the map.
		$("#"+ pLocation).html(pName);
		this.ShowAllPieces();
		return(tNewId);
	}//end AddPiece()
	
	
	
	/** Retrieve a piece's name based on an ID. */
	this.GetPieceById = function(pId) {
		return(_pieces[pId]);
	}//end GetPieceById()
	
	
	
	/** Get the current location of a particular piece (based on ID) */
	this.GetPieceLocation = function(pId) {
		return(_locations[pId]);
	}//end GetPieceLocation()
		
	
		
	/**  */
	this._DisplayPiece = function(pId) {
		$("#"+ _locations[pId]).html(_pieces[pId]);
	}//end _DisplayPiece()
	
	
	
	/**  */
	this.IsSpaceOccupied = function(pCoords) {
		var tRetval = false;
		if(typeof this.GetPieceIdFromCoords(pCoords) == "number") {
			tRetval = true;
		}
		return(tRetval);
	}//end IsSpaceOccupied()
	
	
	
	/** Move a piece. */
	this.MovePiece = function(pId, pNewLocation) {
		var tRetval = false;
		if(_pieces[pId] != undefined && this.IsValidCoord(pNewLocation) && !this.IsSpaceOccupied(pNewLocation)) {
			//move from the old location.
			this.MakeSpaceEmpty(_locations[pId]);
			
			// update to a new location.
			_locations[pId] = pNewLocation;
			
			//o$("#"+ _locations[pId]).html(_pieces[pId]);
			this._DisplayPiece(pId);
			
			// Keep the piece selected...
			if(_selectedPiece == pId) {
				this.SelectPiece(_locations[pId]);
			}
			
			tRetval = true;
		}
		this.ShowAllPieces();
		return(tRetval);
	}//end MovePiece()
	
	
	
	/** Make the space (pId == ID of cell on page) empty */
	this.MakeSpaceEmpty = function(pId) {
		$("#"+ pId).html("&nbsp;");
	}//end MakeSpaceEmpty()
	
	
	
	/** Get the X coordinate of the given piece (by ID) */
	this.GetCoordX = function(pId) {
		var tRetval = undefined;
		if(_locations[pId] != undefined) {
			var tBits = _locations[pId].split("_");
			var tCoords = tBits[1].split("-");
			tRetval = tCoords[0];
		}
		return(tRetval);
	}//end GetCoordX()
	
	
	
	/** Get the Y coordinate of the given piece (by ID) */
	this.GetCoordY = function(pId) {
		var tRetval = undefined;
		if(_locations[pId] != undefined) {
			var tBits = _locations[pId].split("_");
			var tCoords = tBits[1].split("-");
			tRetval = tCoords[1];
		}
		return(tRetval);
	}//end GetCoordY()
	
	
	
	/**  */
	this.MakeIdFromCoordinates = function(pX, pY) {
		var tId = "coord_"+ pX +"-"+ pY;
		return(tId);
	}//end MakeIdFromCoordinates()
	
	
	
	/** check if coordinates are valid  */
	this.IsValidCoord = function(pCoord) {
		var tRetval = false;
		if($("#"+ pCoord).html() != null) {
			tRetval = true;
		}
		return(tRetval);
	}//end IsValidCoord()
	
	
	
	/** Move down 1 space... */
	this.MoveDown = function(pId) {
		var tRetval = false;
		if(_pieces[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates((parseInt(this.GetCoordX(pId))+1), this.GetCoordY(pId));
			tRetval = this.MovePiece(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move up 1 space... */
	this.MoveUp = function(pId) {
		var tRetval = false;
		if(_pieces[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates((parseInt(this.GetCoordX(pId))-1), this.GetCoordY(pId));
			tRetval = this.MovePiece(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move Left 1 space... */
	this.MoveLeft = function(pId) {
		var tRetval = false;
		if(_pieces[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates(this.GetCoordX(pId), (parseInt(this.GetCoordY(pId))-1));
			tRetval = this.MovePiece(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move Right 1 space... */
	this.MoveRight = function(pId) {
		var tRetval = false;
		if(_pieces[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates(this.GetCoordX(pId), (parseInt(this.GetCoordY(pId))+1));
			tRetval = this.MovePiece(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/**  */
	this.SelectPiece = function(pCoords) {
		var tRetval = false;
		var tId = this.GetPieceIdFromCoords(pCoords);
		this.UnselectPiece();
		if(tId !== false) {
			_selectedPiece = tId;
			//alert("SelectPiece("+ pCoords +"), tId=("+ tId +"), piece coords=("+ _locations[tId] +")");
			$("#"+ _locations[tId]).addClass("readyToMove");
		}
		return(tRetval);
	}//end SelectPiece()
	
	
	
	/**  */
	this.UnselectPiece = function(pId) {
		$("table.ttorp tr td.readyToMove").removeClass("readyToMove");
		if(pId != undefined) {
			_selectedPiece = undefined;
		}
	}//end UnselectPiece()
	
	
	
	/**  */
	this.GetPieceIdFromCoords = function(pCoords) {
		var tRetval = false;
		for(var i=0; i<_locations.length;i++) {
			if(_locations[i] == pCoords) {
				tRetval = i;
			}
		}
		return(tRetval);
	}//end GetPieceIdFromCoords
	
	
	
	/**  */
	this.IsSpaceEmpty = function(pCoords) {
		var tRetval = false;
		var tPId = this.GetPieceIdFromCoords(pCoords);
		if(tPId === false) {
			tRetval = true;
		}
		return(tRetval);
	}//end IsSpaceEmpty()
	
	
	
	/**  */
	this.ShowAllPieces = function() {
		if(_pieces.length > 0) {
			var tStr = "";
			for(var i=1; i<_pieces.length; i++) {
				if(i>0) {
					tStr += "<br />";
				}
				tStr += _pieces[i] +" ("+ i +"): "+ _locations[i] +" -- X="+ this.GetCoordX(i) +", y="+ this.GetCoordY(i);
			}
			$("#allPieces").html(tStr);
		}
	}//end ShowAllPieces()
	
	
	
	/**  */
	this.SelectOrMovePiece = function(pCoords) {
		var tRetval = false;
		if(_locations.length > 0 && this.IsValidCoord(pCoords)) {
			//alert("got valid coords ("+ pCoords +")");
			if(_selectedPiece !== undefined && pCoords == this.GetPieceLocation(_selectedPiece)) {
				this.UnselectPiece(_selectedPiece);
			}
			else if(this.IsSpaceEmpty(pCoords) == true) {
				//it's an empty space; move selected piece here (if there's one selected)
				if(_selectedPiece != undefined) {
					tRetval = this.MovePiece(_selectedPiece, pCoords);
				}
				else {
					//alert("no piece selected; can't move anything");
				}
			}
			else {
				//get the piece ID from the coordinates...
				var tPieceId = this.GetPieceIdFromCoords(pCoords);
				this.SelectPiece(pCoords);
			}
		}
		return(tRetval);
	}//end SelectOrMovePiece()
	
	
	
	/**  */
	this.HandleMovement = function (pKeyNum) {
		var tPieceMoved = true;
		if(_selectedPiece != undefined) {
			switch(pKeyNum) {
				case 37:
					this.MoveLeft(_selectedPiece);
					break;
				case 38:
					this.MoveUp(_selectedPiece);
					break;
				case 39:
					this.MoveRight(_selectedPiece);
					break;
				case 40:
					this.MoveDown(_selectedPiece);
					break;
				default:
					tPieceMoved = false;
					break;
			}
		}
		return(tPieceMoved);
	}//end HandleMovement
}

//create an object
_map = new mapStorage();


function createDialog(pIdOfDialog) {
	alert("yo!");
	$("#"+ pIdOfDialog).dialog({
		buttons: {
			"Ok": 		function() { 
				if($("#newPieceName")) {
					
					//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! HERE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!1
					_map.AddPiece();
				}
				$(this).dialog("close"); 
			},
			"Cancel":	function() { $(this).dialog("close"); }
		},
		modal: true,
		effect: 'slide'
	});
	return(true);
}//end createDialog()


$(document).ready(function(){
	$("table.ttorp tr td").each(function(){
		var tMyId = $(this).attr("id");
		_map.MakeSpaceEmpty(tMyId);
	});
	// Add a piece so people w/o FireBug can try it out.
	_map.AddPiece("X", "coord_1-1");
	_map.AddPiece("Y", "coord_3-1");
	$("table.ttorp tr td").click(function(){
		var tId = $(this).attr("id");
		return(_map.SelectOrMovePiece(tId));
	});
	$(".vertical-text").each(function(){
		var tStr = $(".vertical-text").text();
		var tBits = tStr.split("");
		var tFinalString = "";
		for(var i=0; i<tBits.length; i++) {
			tFinalString += tBits[i] + "<br />\n";
		}
		$(this).html(tFinalString);
	});
	$(document).keyup(function(event){
		_map.HandleMovement(event.which);
	});
});


