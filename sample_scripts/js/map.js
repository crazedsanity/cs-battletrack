//=============================================================================
function tokenStorage(pId, pName, pLocation) {
	//TODO: should this store the number of moves available?
	var _id = undefined;
	var _name = undefined;
	var _location = undefined;
	var _movement = Array();
	
	/** Like properties... */ {
		this.getId = function() {
			return(_id);
		}//end getId()
		this.setId = function(pId) {
			_id=pId;
			return(_id);
		}//end setId()
		
		this.getName = function() {
			return(_name);
		}//end getName()
		this.setName = function(pName) {
			_name = pName;
			return(_name);
		}//end setName()
		
		this.getHtml = function(pIsToken) {
			var tId = "token_"+ _id;
			if(pIsToken !== true) {
				tId = "tokenLegend_"+ _id;
			}
			var tHtml = "<img src='/images/icons/"+ _name +"' id='"+ tId +"'>";
			return(tHtml);
		}//end setHtml()
		
		this.getLocation = function() {
			return(_location);
		}//end getLocation()
		this.setLocation = function(pLocation) {
			if(pLocation != undefined) {
				_location = pLocation;
				_movement[0] = _location;
			}
			return(_location);
		}//end setLocation()
	}//end "properties"
	
	
	/** this part is like a constructor (after "properties" so they exist before they're called) ... */{
		this.setId(pId);
		this.setName(pName);
		this.setLocation(pLocation);
	}//end "constructor"
	
	
	/**  */
	this.move = function(pLocation) {
		var tRetval = undefined;
		var tMoveNum = _movement.length;
		if(tMoveNum != 0) {
			var tCheckMove = (tMoveNum -1);
			if(_movement[tCheckMove] != pLocation) {
				_movement[tMoveNum] = pLocation;
				tRetval = tMoveNum;
			}
		}
		return(tRetval);
	}//end move()
	
	/**  */
	this.getMovement = function() {
		return(_movement);
	}//end getMovement()
}//end tokenStorage{}
//=============================================================================



//=============================================================================
function mapStorage() {
	//TODO: account for tile positions that have an off-by-one problem (i.e. position must increase 1xgrid[x/]coord)
	var _selectedToken = undefined;
	var _tokens = new Array();
	var _locations = new Array();
	
	// Identifiers...
	var _lastTokenId = null;
	
	
	
	
	/** Add a token by name, get an ID. */
	this.AddToken = function(pName, pLocation) {
		var tNewId = undefined;
		if(_lastTokenId == null) {
			_lastTokenId = 1;
		}
		else {
			_lastTokenId++;
		}
		tNewId = _lastTokenId;
		_tokens[tNewId] = new tokenStorage(tNewId, pName, pLocation);
		if(pLocation != undefined) {
			_locations[tNewId] = pLocation;
			this._DisplayToken(tNewId);
		}
		
		//add the token to the map.
		this.ShowTokenLegend();
		return(tNewId);
	}//end AddToken()
	
	
	
	/** Retrieve a token's name based on an ID. */
	this.GetTokenById = function(pId) {
		return(_tokens[pId]);
	}//end GetTokenById()
	
	
	
	/** Get the current location of a particular token (based on ID) */
	this.GetTokenLocation = function(pId) {
		return(_locations[pId]);
	}//end GetTokenLocation()
		
	
		
	/** Puts the token's HTML into the appropriate coordinates. */
	this._DisplayToken = function(pId) {
		var tHtml = this.GetTokenById(pId).getHtml(true);
		$("#"+ _locations[pId] +" div.inner").html(tHtml);
		return(tHtml);
	}//end _DisplayToken()
	
	
	
	/** Determines if the given coordinates have a token there or not. */
	this.IsSpaceOccupied = function(pCoords) {
		var tRetval = false;
		if(typeof this.GetTokenIdFromCoords(pCoords) == "number") {
			tRetval = true;
		}
		return(tRetval);
	}//end IsSpaceOccupied()
	
	
	
	/** Move the given token ID to the given coordinates. */
	this.MoveToken = function(pId, pNewLocation) {
		var tRetval = false;
		if(_tokens[pId] != undefined && this.IsValidCoord(pNewLocation) && !this.IsSpaceOccupied(pNewLocation)) {
			//remove from the old location.
			this.MakeSpaceEmpty(_locations[pId]);
			
			// update to a new location.
			_locations[pId] = pNewLocation;
			
			_tokens[pId].move(pNewLocation);
			
			this._DisplayToken(pId);
			
			// Keep the token selected...
			if(_selectedToken == pId) {
				this.SelectToken(_locations[pId]);
			}
			
			tRetval = true;
		}
		this.ShowTokenLegend();
		return(tRetval);
	}//end MoveToken()
	
	
	
	/** Make the space (pId == ID of cell on page) empty */
	this.MakeSpaceEmpty = function(pId) {
		$("#"+ pId +" div.inner").html("&nbsp;");
	}//end MakeSpaceEmpty()
	
	
	
	/** Get the X coordinate of the given token (by ID) */
	this.GetCoordX = function(pId) {
		var tRetval = undefined;
		if(_locations[pId] != undefined) {
			var tBits = _locations[pId].split("_");
			var tCoords = tBits[1].split("-");
			tRetval = tCoords[0];
		}
		return(tRetval);
	}//end GetCoordX()
	
	
	
	/** Get the Y coordinate of the given token (by ID) */
	this.GetCoordY = function(pId) {
		var tRetval = undefined;
		if(_locations[pId] != undefined) {
			var tBits = _locations[pId].split("_");
			var tCoords = tBits[1].split("-");
			tRetval = tCoords[1];
		}
		return(tRetval);
	}//end GetCoordY()
	
	
	
	/** Create the ID from the X/Y values ("1","2" == "coord_1-2") */
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
		if(_tokens[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates(this.GetCoordX(pId), (parseInt(this.GetCoordY(pId))+1));
			tRetval = this.MoveToken(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move up 1 space... */
	this.MoveUp = function(pId) {
		var tRetval = false;
		if(_tokens[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates(this.GetCoordX(pId), (parseInt(this.GetCoordY(pId))-1));
			tRetval = this.MoveToken(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move Left 1 space... */
	this.MoveLeft = function(pId) {
		var tRetval = false;
		if(_tokens[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates((parseInt(this.GetCoordX(pId))-1), this.GetCoordY(pId));
			tRetval = this.MoveToken(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Move Right 1 space... */
	this.MoveRight = function(pId) {
		var tRetval = false;
		if(_tokens[pId]) {
			var tNewCoord = this.MakeIdFromCoordinates((parseInt(this.GetCoordX(pId))+1), this.GetCoordY(pId));
			tRetval = this.MoveToken(pId, tNewCoord);
		}
		return(tRetval);
	}//end MoveLeft()
	
	
	
	/** Mark the token stored at the given coordinates as being selected. */
	this.SelectToken = function(pCoords) {
		var tRetval = false;
		var tId = this.GetTokenIdFromCoords(pCoords);
		this.UnselectToken();
		if(tId !== false) {
			_selectedToken = tId;
			//alert("SelectToken("+ pCoords +"), tId=("+ tId +"), token coords=("+ _locations[tId] +")");
			$("#"+ _locations[tId]).addClass("readyToMove");
		}
		return(tRetval);
	}//end SelectToken()
	
	
	
	/** Removes the class that indicates a coordinate is selected from EVERYTHING. */
	this.UnselectToken = function(pId) {
		$("#map div.readyToMove").removeClass("readyToMove");
		if(pId != undefined) {
			_selectedToken = undefined;
		}
	}//end UnselectToken()
	
	
	
	/** Returns the ID (number) of a token from the coordinates. */
	this.GetTokenIdFromCoords = function(pCoords) {
		var tRetval = false;
		for(var i=0; i<_locations.length;i++) {
			if(_locations[i] == pCoords) {
				tRetval = i;
			}
		}
		return(tRetval);
	}//end GetTokenIdFromCoords
	
	
	
	/** Determines if the coordinates contain a token or not. */
	this.IsSpaceEmpty = function(pCoords) {
		var tRetval = false;
		var tPId = this.GetTokenIdFromCoords(pCoords);
		if(tPId === false) {
			tRetval = true;
		}
		return(tRetval);
	}//end IsSpaceEmpty()
	
	
	
	/** Displays list of the tokens. */
	this.ShowTokenLegend = function() {
		if(_tokens.length > 0) {
			var tStr = "";
			for(var i=1; i<_tokens.length; i++) {
				if(i>0) {
					tStr += "<br />";
				}
				var tHtml = _tokens[i].getHtml(false);
				var tLocationString = "(Not placed yet...)";
				if(_locations[i]) {
					tLocationString = _locations[i] +" -- X="+ this.GetCoordX(i) +", y="+ this.GetCoordY(i);
				}
				tStr += tHtml +" ("+ i +"): "+ tLocationString;
			}
			$("#allTokens").html(tStr);
		}
	}//end ShowTokenLegend()
	
	
	
	/** Either selects the given token or moves the already selected token to the new coordinates (or nothing). */
	this.SelectOrMoveToken = function(pCoords) {
		var tRetval = false;
		if(_locations.length > 0 && this.IsValidCoord(pCoords)) {
			//alert("got valid coords ("+ pCoords +")");
			if(_selectedToken !== undefined && pCoords == this.GetTokenLocation(_selectedToken)) {
				this.UnselectToken(_selectedToken);
			}
			else if(this.IsSpaceEmpty(pCoords) == true) {
				//it's an empty space; move selected token here (if there's one selected)
				if(_selectedToken != undefined) {
					tRetval = this.MoveToken(_selectedToken, pCoords);
				}
				else {
					//alert("no token selected; can't move anything");
				}
			}
			else {
				//get the token ID from the coordinates...
				var tTokenId = this.GetTokenIdFromCoords(pCoords);
				this.SelectToken(pCoords);
			}
		}
		return(tRetval);
	}//end SelectOrMoveToken()
	
	
	
	/** If a token is selected, this will move a token up, down, left, or right if the corresponding arrow key is pressed. */
	this.HandleMovement = function (pKeyNum) {
		var tTokenMoved = true;
		var tMoveResult = undefined;
		if(_selectedToken != undefined) {
			switch(pKeyNum) {
				case 37:
					tMoveResult = this.MoveLeft(_selectedToken);
					break;
				case 38:
					tMoveResult = this.MoveUp(_selectedToken);
					break;
				case 39:
					tMoveResult = this.MoveRight(_selectedToken);
					break;
				case 40:
					tMoveResult = this.MoveDown(_selectedToken);
					break;
				default:
					tTokenMoved = false;
					break;
			}
		}
		return(tTokenMoved);
	}//end HandleMovement
	
	
	
	/**  */
	this.getTokenMovements = function(pId) {
		return(_tokens[pId].getMovement());
	}//end getTokenMovements()
	
	
	
	/** POST changes to the server */
	this.sendChanges = function(pChanges) {
		//nction ajax_doPost(formName, postData, msgTitle, msgBody, isAsync) {
		ajax_doPost('/ajax/ttorp/map', pChanges);
	}//end sendChanges()
	
	
	
	/** Callback for handling the response. */
	this.sendChanges_response = function(pXmlResponse) {
	}//end sendChanges_response()

}//end mapStorage{}
//=============================================================================


//create required objects...
var mapObj = new mapStorage();




function createDialog(pIdOfDialog) {
	$("#"+ pIdOfDialog).dialog({
		buttons: {
			"Add": 		
				function() { 
					if($("#newTokenName").val().length) {
						mapObj.AddToken($("#newTokenName").val());
						$("#newTokenName").val("");
					}
					$(this).dialog("close"); 
				},
			"Cancel":	
				function() { $(this).dialog("close"); }
		},
		modal: true,
		open: 
			//TODO: make this do the same thing as "Add"...
			//TODO: keep this from submitting mutliple times (manifests after submitting at least once).
			function(){
			/*$(this).keyup(
				function(e) {
					if (e.keyCode == 13) {
						$(this).dialog("close");
					}
				}
			)*/
			},
		effect: 'slide'
	});
	return(true);
}//end createDialog()

function handleDialogAdd(obj) {
}


function btnClick_sendChanges() {
	var tChanges = Array();
	// get a list of tokens and associate their id's to their list of movements...
	
	// check for background map settings, grid changes (placement, size, progressive padding, etc)
	
	// send all data to the server.
	mapObj.sendChanges(tChanges);
}//end btnClick_sendChanges()


$(document).ready(function(){
	// Add a token so people w/o FireBug can try it out.
	mapObj.AddToken("16-tool-a.png", "coord_1-1");
	mapObj.AddToken("16-em-plus.png", "coord_2-1");
	
	
	$("#map div.tile").click(function(){
		var tId = $(this).attr("id");
		return(mapObj.SelectOrMoveToken(tId));
	});
	$("#input_loadMap").click(function(){
		$("#map").css("background-image", "url("+ $("#input_mapUrl").val() +")");
	});
	$("#resetMap").click(function(){
		$("#map").css("background-image", "");
	});
	$(document).keyup(function(event){
		mapObj.HandleMovement(event.which);
	});
	$(".draggable").draggable();
	$(".resizable").resizable();
	$("#showGrid").click(function(){
		$("div.tile div.inner").addClass("gridShown");
		$(this).toggle();
		$("#hideGrid").toggle();
	});
	$("#hideGrid").click(function(){
		$("div.tile div.inner").removeClass("gridShown");
		$(this).toggle();
		$("#showGrid").toggle();
	});
});

var _responseXML = undefined;

