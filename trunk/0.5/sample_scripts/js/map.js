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
	
	/** Get the X coordinate of the given piece (by ID) */
	this.GetCoordX = function(pId) {
		var tRetval = null;
		if(_pieces[pId] != undefined) {
			var tBits = _locations[pId].split("_");
			var tCoords = tBits[1].split("-");
		}
		return(tRetval);
	}//end GetCoordX()
	
	/** Get the Y coordinate of the given piece (by ID) */
	this.GetCoordY = function(pId) {
		var tRetval = null;
		if(_pieces[pId] != undefined) {
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
	
	/** Move left 1 space... */
	this.MoveLeft = function(pId) {
		var tRetval = false;
		if(_pieces[pId]) {
			//add one to the X coordinate...
			var tCoord = this.GetCoordX(pId);
			alert("Checking coordinates ("+ tNewCoord +")");
			var tNewCoord = this.MakeIdFromCoordinates(tCoord, this.GetCoordY(pId));
			
			//make sure there's a space.
			if($("#"+ tNewCoord) != null) {
				this.MovePiece(pId, tNewCoord);
				tRetval = tCoord;
			}
		}
		return(tRetval);
	}//end MoveLeft()
}

//create an object
_map = new mapStorage();


$(document).ready(function(){
	$("table.ttorp tr td").each(function(){
		var tMyId = $(this).attr("id");
		_map.MakeSpaceEmpty(tMyId);
	});
});
