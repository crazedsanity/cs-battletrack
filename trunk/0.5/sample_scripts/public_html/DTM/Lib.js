//Cookie information
var cookie;
if (online) cookie = CoreCookie("jDungeonTilesMapper");
var gridSize = online && cookie && cookie.Size? cookie.Size[2] : 1;
	gridSize = gridSize == 0? 8 : gridSize == 1? 16 : 32;

var Lib = { Version : { Major:1, Minor:1, Revision:1 } };
/*
	1.0.0 >> Initial
	1.1.0 >> Added Zoom capabilities and Option to allow the user to change the viewed size of the map.
	         Ctrl+1 = 100%;  Ctrl+2 = 200%;  Ctrl+5 = 50%.
	         Disabled Ctrl+f functionality.
	         Tiles now 'snap' to the half-way points on the grid (instead of the full grid).
	1.1.1 >> 2007.01.15 - Fixed a bug in shuffleAnchored().
	         Fixed a bug in attach().
	         Added zoomIn(), zoom(), zoomOut() for use with new buttons in ToolBox.
*/

//Set variables and functions
var aSets = new Array(); //Store set information
var aTilesUsed = new Array(); //Track tiles used
function CreateSet(sName, sCode) {
	var oSet = new Objects.Set(sName, sCode);
	aSets[aSets.length] = oSet;
	oSet.Count = online? cookie.getSetCount(aSets.length - 1, 0) : 0;
	aTilesUsed[aTilesUsed.length] = new Array();
	return oSet;
}
function SetCount(sSetCode, iCount, change) {
	for (var i = 0; i < aSets.length; i++) {
		if (aSets[i].Code == sSetCode) {
			if (online && change) cookie.setSetCount(i, iCount);
			aSets[i].Count = online? cookie.getSetCount(i, iCount) : iCount;
		}
	}
}
function SetFreeForm(value) { $("chkFreeForm").checked = value; handleIconText(); }
function SetMapIconText(value) { $("chkMapIconText").checked = value; handleIconText(); }
function SetTilesIconText(value) { $("chkTilesIconText").checked = value; handleIconText(); }
function SetGroupDeleteOption(value) { $("optGroupDelete").selected = value; $("optGroupDetach").selected = !value; }

//Variables for tile dragging
var image_object = null;
var focused = false;
var dragging = false;

//Settings variables
var borderFocusColor = "red";

//Event Captures
Event.observe(window, "load", onLoad, false);
Event.observe(window, "scroll", moveToolBox, false);
Event.observe(document, "click", onClick, false);
Event.observe(document, "keypress", handleKeyPress, false);

//Page functions
function onClick(e) { var el = e.srcElement? e.srcElement : e.target; if (el.tagName == "HTML" || el.id == "divGrid") loseFocus(); }
function onLoad() {
	new Draggable("divToolBox", {handle:"divToolBoxCaption",onEnd:toolBoxDraggableEnd});
	$("tdVersion").innerHTML = "Version " + Version.Major + "." + Version.Minor + "." + Version.Revision;

	if (online) {
		var count = 0; for (var i = 0; i < aSets.length; i++) { count += aSets[i].Count; } if (!count) SetCount("DT1", 1, true);
		$("chkFreeForm").checked = cookie.FreeForm;
		$("chkMapIconText").checked = cookie.MapIconText;
		$("chkTilesIconText").checked = cookie.TilesIconText;
		$("optGroupDelete").checked = cookie.GroupDeleteOption; $("optGroupDetach").checked = !cookie.GroupDeleteOption;
		$("chkRememberTab").checked = cookie.RememberTab;
		if ($("chkRememberTab").checked) showTab(cookie.Tab);
		handleIconText();

	}else {
		offlineOnLoad();
		$("divSaveDelete").style.display = "none";
		$("spanSaveDelete").style.display = "none";
		$("trRememberTab").style.display = "none";
	}

	var oSetCount = $("cmbSetCount");
	for (var i = 0; i < 11; i++) oSetCount.options[i] = new Option(i, i);
	
	var oSets = $("cmbSets"); oSets.options.length = 0;
	var oSSets = $("cmbSSets"); oSSets.options.length = 0;
	for (var i = 0; i < aSets.length; i++) { oSets.options[i] = new Option(aSets[i].Name, i); oSSets.options[i] = new Option(aSets[i].Name, i); }
	cmbSSets_OnChange();
	cmbSets_OnChange();
	cmbGrid_OnChange(online? cookie.Size && cookie.Size.length == 3? cookie.Size : [4, 2, 1] : aMapSize);

	loadMapList();

	$("divToolBox").style.display = "";
	$("divLoading").parentNode.removeChild($("divLoading"));
}
function cmbGrid_OnChange(size) {
	var oGrid = $("divGrid");
	var oW = $("cmbGridWidth");
	var oH = $("cmbGridHeight");
	var oZ = $("cmbGridZoom");
	if (size) {
		oW.selectedIndex = size[0];
		oH.selectedIndex = size[1];
		oZ.selectedIndex = size[2];
	}else if (online) {
		cookie.Size = [oW.selectedIndex, oH.selectedIndex, oZ.selectedIndex];
		cookie.store();
	}
	var old = gridSize;
	gridSize = oZ.selectedIndex == 0? 8 : oZ.selectedIndex == 1? 16 : 32;
	oGrid.className = "Grid" + parseInt(oZ.options[oZ.selectedIndex].text);
	oGrid.style.width = (oW.selectedIndex + 1) * 12 * gridSize;
	oGrid.style.height = (oH.selectedIndex + 1) * 12 * gridSize;
	Objects.Layering.Zoom(old, gridSize);
}
function cmbSSets_OnChange() { $("cmbSetCount").selectedIndex = aSets[$("cmbSSets").selectedIndex].Count; }
function cmbSetCount_OnChange() { SetCount(aSets[$("cmbSSets").selectedIndex].Code, $("cmbSetCount").selectedIndex, true); checkCount(); }
function cmbSets_OnChange() {
	var oSets = $("cmbSets"); oSets.blur();
	var oTiles = $("cmbTiles"); oTiles.options.length = 0;
	var text = "";
	for (var i = 0; i < aSets[oSets.selectedIndex].Tiles.length; i++) {
		text = aSets[oSets.selectedIndex].Tiles[i].NameA + " (" + aSets[oSets.selectedIndex].Tiles[i].Dimensions + ")";
		oTiles.options[i] = new Option(text, i);
	}
	cmbTiles_OnChange();
}
function cmbTiles_OnChange() {
	var oSets = $("cmbSets");
	var oTiles = $("cmbTiles");
	var oTile = aSets[oSets.selectedIndex].Tiles[oTiles.selectedIndex];
	$("spanSideB").innerHTML = oTile.NameB;
	var oA = $("imgPreviewA");
	oA.src = oTile.ImageA[0].src;
	oA.width = oTile.Width;
	oA.height = oTile.Height;
	var oB = $("imgPreviewB");
	if (oTile.NameB) {
		oB.src = oTile.ImageB[0].src;
		oB.width = oTile.Width;
		oB.height = oTile.Height;
		oB.style.display = $("trBText").style.display = "";
	}else {
		oB.style.display = $("trBText").style.display = "none";
	}
	checkCount();
}
function chkFreeForm_OnClick() { if (online) cookie.setFreeForm($("chkFreeForm").checked); }
function chkMapIconText_OnClick() { if (online) cookie.setMapIconText($("chkMapIconText").checked); handleIconText(); }
function chkTilesIconText_OnClick() { if (online) cookie.setTilesIconText($("chkTilesIconText").checked); handleIconText(); }
function chkRememberTab_OnClick() { if (online) cookie.setRememberTab($("chkRememberTab").checked); }
var oA, oB;
function checkCount() {
	var oSets = $("cmbSets");
	var oTiles = $("cmbTiles");
	
	var used = aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex];
		if (isNaN(used)) used = aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex] = 0;
	if (isNaN(aSets[oSets.selectedIndex].Count)) aSets[oSets.selectedIndex].Count = 0;
	var total = aSets[oSets.selectedIndex].TileCount[oTiles.selectedIndex] * aSets[oSets.selectedIndex].Count;
		if ($("chkFreeForm").checked) total = "Unlimited";
	$("spanUsed").innerHTML = used + " of " + total;
	
	if (!(oTiles.selectedIndex < aTilesUsed[oSets.selectedIndex].length)) aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex] = 0;
	if (!canAddTile() && !$("chkFreeForm").checked) {
		if (oA) oA.destroy(); if (oB) oB.destroy();
		new Effect.Opacity("imgPreviewA", {duration:0.0,from:1.0,to:0.25});
		new Effect.Opacity("imgPreviewB", {duration:0.0,from:1.0,to:0.25});
	}else {
		new Effect.Opacity("imgPreviewA", {duration:0.0,from:0.25,to:1.0});
		new Effect.Opacity("imgPreviewB", {duration:0.0,from:0.25,to:1.0});
		if (oA) oA.destroy(); oA = new Draggable("imgPreviewA", {ghosting:true,endeffect:function(){},onEnd:function(){droppedTile(false);}});
		if (oB) oB.destroy(); oB = new Draggable("imgPreviewB", {ghosting:true,endeffect:function(){},onEnd:function(){droppedTile(true);}});
	}
}
function droppedTile(sideB) {
	if (!canAddTile()) return;
	var oImg = $("imgPreview" + (sideB ? "B" : "A"));
	var pos = Position.cumulativeOffset(oImg);
	oImg.style.left = 0;
	oImg.style.top = 0;
	var img = addTile(sideB, true);
	img.style.left = pos[0] + (pos[0] % gridSize < 9 ? -1 * pos[0] % gridSize : gridSize - pos[0] % gridSize);
	img.style.top = pos[1] + (pos[1] % gridSize < 9 ? -1 * pos[1] % gridSize : gridSize - pos[1] % gridSize);
}
function handleKeyPress(ev) {
	if (ev.ctrlKey && ev.keyCode == 37) { turnCC(); return false; }
	if (ev.ctrlKey && ev.keyCode == 38) { raiseTile(); return false; }
	if (ev.ctrlKey && ev.keyCode == 39) { turnC(); return false; }
	if (ev.ctrlKey && ev.keyCode == 40) { lowerTile(); return false; }
	if (ev.charCode == 49 || ev.keyCode == 49) { $("cmbGridZoom").selectedIndex = 1; cmbGrid_OnChange(); return false; }
	if (ev.charCode == 50 || ev.keyCode == 50) { $("cmbGridZoom").selectedIndex = 2; cmbGrid_OnChange(); return false; }
	if (ev.charCode == 53 || ev.keyCode == 53) { $("cmbGridZoom").selectedIndex = 0; cmbGrid_OnChange(); return false; }
}

//ToolBox functions
function zoomIn() { $("cmbGridZoom").selectedIndex = 2; cmbGrid_OnChange(); }
function zoom() { $("cmbGridZoom").selectedIndex = 1; cmbGrid_OnChange(); }
function zoomOut() { $("cmbGridZoom").selectedIndex = 0; cmbGrid_OnChange(); }
function showTab(sTab) {
	$("divTB_About").style.display = "none";
	$("divTB_Map").style.display = "none";
	$("divTB_Settings").style.display = "none";
	$("divTB_Tiles").style.display = "none";
	$(sTab).style.display = "block";
	$("divToolBoxCaption").innerHTML = "&nbsp;ToolBox - " + sTab.split("_")[1];
	if (online) cookie.setTab(sTab);
}
function moveToolBox() {
	var div = $("divToolBox");
	var pos = Position.cumulativeOffset(div);
	if (!div._l) div._l = pos[0];
	if (!div._t) div._t = pos[1];
	div.style.left = div._l + document.body.scrollLeft;
	div.style.right = "";
	div.style.top = div._t + document.body.scrollTop;
}
function toolBoxDraggableEnd() {
	var div = $("divToolBox");
	var pos = Position.cumulativeOffset(div);
	div._l = pos[0] - document.body.scrollLeft;
	div._t = pos[1] - document.body.scrollTop;
}

//Tile Image functions
var iNew = 0;
function canAddTile() { var oSets = $("cmbSets"); var oTiles = $("cmbTiles"); return !(aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex] >= aSets[oSets.selectedIndex].TileCount[oTiles.selectedIndex] * aSets[oSets.selectedIndex].Count && !$("chkFreeForm").checked); }
function addTile(sideB, dropped) {
	if (!canAddTile()) return;
	var oSets = $("cmbSets");
	var oTiles = $("cmbTiles"); oTiles.blur();

	var img = createIMG(oSets.selectedIndex, oTiles.selectedIndex, dropped, iNew++);
	$("divGrid").appendChild(img);
	img.Draggable = new Draggable(img.id, {snap:[gridSize/2,gridSize/2],onDrag:tileDraggableDrag,onEnd:tileDraggableEnd}); //,onStart:tileDraggableStart
	Objects.Layering.Add(img);
	setFocus(img);
	if (sideB) flip();

	if (aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex]) aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex]++; else aTilesUsed[oSets.selectedIndex][oTiles.selectedIndex] = 1;
	checkCount();
	
	if (!dropped) { if (5 == iNew) iNew = 0; }
	return img;
}
function _addTile(iSet, iTile, sSide, iRotate, iLeft, iTop, iZ, aAnchor) {
	var img = createIMG(iSet, iTile, false, 0);
	$("divGrid").appendChild(img);
	img.Draggable = new Draggable(img.id, {snap:[gridSize/2,gridSize/2],onDrag:tileDraggableDrag,onEnd:tileDraggableEnd});//,onStart:tileDraggableStart
	Objects.Layering.Add(img);
	setFocus(img);
	if (sSide == "B") flip();
	while (img.Tile.Rotate != iRotate) turnC();
	img.style.left = iLeft * (gridSize == 16? 1 : gridSize == 8? 0.5 : 2);
	img.style.top = iTop * (gridSize == 16? 1 : gridSize == 8? 0.5 : 2);
	img.style.zIndex = iZ;
	img.Anchor = aAnchor;

	if (aTilesUsed[iSet][iTile]) aTilesUsed[iSet][iTile]++; else aTilesUsed[iSet][iTile] = 1;
	
	return img;
}
function createIMG(iSet, iTile, bDropped, iCascade) {
	var img = document.createElement("IMG");

	img.style.border = "0px";
	img.style.position = "absolute";
	img.style.zIndex = 500;
	
	img.SetIndex = iSet;
	img.TileIndex = iTile;
	img.id = "tile" + Objects.Layering.Images.length;
	if (!bDropped) img.style.left = iCascade * gridSize + (document.body? document.body.scrollLeft : 0);
	if (!bDropped) img.style.top = iCascade * gridSize + (document.body? document.body.scrollTop : 0);

	img.Tile = aSets[iSet].Tiles[iTile].Clone();
	img.src = img.Tile.GetImage();
	img.width = parseInt(img.Tile.Width * (gridSize == 16? 1 : gridSize == 8? 0.5 : 2));
	img.height = parseInt(img.Tile.Height * (gridSize == 16? 1 : gridSize == 8? 0.5 : 2));

	Event.observe(img, "mousedown", function(e) { setFocus(e.target? e.target : e.srcElement); }, "false");
	Event.observe(img, "dblclick", function(e) { flip(e.target? e.target : e.srcElement); }, "false");
	Event.observe(img, "contextmenu", function(e) { if (e.ctrlKey) turnCC(); else turnC(); return false; }, "false");
	img.IsAnchor = function() { for (var i = 0; i < Objects.Layering.Images.length; i++) if ($(Objects.Layering.Images[i]).Anchor && $(Objects.Layering.Images[i]).Anchor[0] == this.id) return true; }
	img.GetAnchored = function() { var ret = new Array(); for (var i = 0; i < Objects.Layering.Images.length; i++) if ($(Objects.Layering.Images[i]).Anchor && $(Objects.Layering.Images[i]).Anchor[0] == this.id) ret[ret.length] = Objects.Layering.Images[i]; return ret; }

	return img;
}

function sendToBack()   { if (!focused || image_object.Anchor) return; Objects.Layering.Back (image_object); }
function lowerTile()    { if (!focused || image_object.Anchor) return; Objects.Layering.Lower(image_object); }
function raiseTile()    { if (!focused || image_object.Anchor) return; Objects.Layering.Raise(image_object); }
function bringToFront() { if (!focused || image_object.Anchor) return; Objects.Layering.Front(image_object); }

function attach() {
	if (!focused || image_object.Anchor || image_object.IsAnchor()) return;
	var under = Objects.Layering.GetImageUnder(image_object);
	if (!under  || under.Anchor) return;
	var info = [under.id, (parseInt(image_object.style.left) - parseInt(under.style.left)) / gridSize, (parseInt(image_object.style.top) - parseInt(under.style.top)) / gridSize, image_object.Tile.Rotate];
	image_object.Anchor = info;
	image_object.Draggable.destroy();
	Objects.Layering.SyncAnchor(under);
}
function detach() {
	if (!focused || !image_object.Anchor) return;
	image_object.Anchor = null;
	image_object.Draggable = new Draggable(image_object.id, {snap:[gridSize/2,gridSize/2],onEnd:tileDraggableEnd});
}
function tileDraggableEnd() {
	if (!image_object.IsAnchor) return;
	var anchored = image_object.GetAnchored();
	for (var i = 0; i < anchored.length; i++) {
		shuffleAnchored($(anchored[i]));
	}
}
//function tileDraggableStart() {}
function tileDraggableDrag() {
	if (!image_object.IsAnchor) return;
	var anchored = image_object.GetAnchored();
	for (var i = 0; i < anchored.length; i++) {
		shuffleAnchored($(anchored[i]));
	}
}

function setFocus(obj) {
	if (focused) loseFocus();
	image_object = obj;
	image_object.style.border = "1px solid " + borderFocusColor;
	image_object.width -= 2;
	image_object.height -= 2;
	if (obj.Anchor) {
	}
	focused = true;
}
function loseFocus() { if (!focused) return; focused = false; image_object.style.border = "0px"; image_object.width += 2; image_object.height += 2; image_object = null; }
function shuffleAnchored(img) {
	if (!img.Anchor) return;
	var delta = img.Tile.Rotate - img.Anchor[3]; if (delta < 0) delta += 360;
	var gridDelta = gridSize == 8? 0.5 : gridSize == 16? 1 : 2;
	var anchor = $(img.Anchor[0]);
	var h = img.Anchor[3] == 90 || img.Anchor[3] == 270? img.Tile.Width : img.Tile.Height;
	var w = img.Anchor[3] == 90 || img.Anchor[3] == 270? img.Tile.Height : img.Tile.Width;
	var H = anchor.Tile.Rotate == 90 || anchor.Tile.Rotate == 270? anchor.Tile.Width : anchor.Tile.Height;
	var W = anchor.Tile.Rotate == 90 || anchor.Tile.Rotate == 270? anchor.Tile.Height : anchor.Tile.Width;
	switch(delta) {
		default:
			img.style.left = (parseInt(anchor.style.left) + gridSize * img.Anchor[1]) + "px";
			img.style.top = (parseInt(anchor.style.top) + gridSize * img.Anchor[2]) + "px";
			break;
		case 90:
			img.style.left = (parseInt(anchor.style.left) + (W - h) * gridDelta - gridSize * img.Anchor[2]) + "px";
			img.style.top = (parseInt(anchor.style.top) + gridSize * img.Anchor[1]) + "px";
			break;
		case 180:
			img.style.left = (parseInt(anchor.style.left) + (W - w) * gridDelta - gridSize * img.Anchor[1]) + "px";
			img.style.top = (parseInt(anchor.style.top) + (H - h) * gridDelta - gridSize * img.Anchor[2]) + "px";
			break;
		case 270:
			img.style.left = (parseInt(anchor.style.left) + gridSize * img.Anchor[2]) + "px";
			img.style.top = (parseInt(anchor.style.top) + (H - w) * gridDelta - gridSize * img.Anchor[1]) + "px";
			break;
	}
}
function turnC() {
	if (!focused || image_object.Anchor) return;
	image_object.Tile.Clockwise(image_object);
	image_object.width -= 2;
	image_object.height -= 2;
	
	var anchored = image_object.GetAnchored();
	for (var img, i = 0; i < anchored.length; i++) {
		img = $(anchored[i]);
		img.Tile.Clockwise(img);
		shuffleAnchored(img);
	}
}
function turnCC() {
	if (!focused || image_object.Anchor) return;
	image_object.Tile.CounterClockwise(image_object);
	image_object.width -= 2;
	image_object.height -= 2;
	
	var anchored = image_object.GetAnchored();
	for (var img, i = 0; i < anchored.length; i++) {
		img = $(anchored[i]);
		img.Tile.CounterClockwise(img);
		shuffleAnchored(img);
	}
}
function flip() { if (!focused) return; image_object.Tile.Flip(image_object); }
function del(img) {
	if (!focused && !img) return;
	if (!img) img = image_object;
	if (img.IsAnchor()) {
		var anchored = img.GetAnchored();
		for (var i = 0; i < anchored.length; i++)
			if ($("optGroupDelete").checked) del($(anchored[i])); else $(anchored[i]).Anchor = null;
	}
	Objects.Layering.Remove(img);
	aTilesUsed[img.SetIndex][img.TileIndex]--;

	Element.remove(img);
	if (img.Draggable) img.Draggable.destroy();
	if (image_object == img) focused = false;
	image_object = img = null;

	checkCount();
}
function handleIconText() {
	var mapIconText = $("chkMapIconText").checked;
	$("divTB_Map_Text").style.display = mapIconText? "" : "none";
	$("divTB_Map_NoText").style.display = mapIconText? "none" : "";
	
	var tilesIconText = $("chkTilesIconText").checked;
	$("divTB_Tiles_Text").style.display = tilesIconText? "" : "none";
	$("divTB_Tiles_NoText").style.display = tilesIconText? "none" : "";
}
function clearMap() {
	loseFocus();

	for (var img, i = 0; i < Objects.Layering.Images.length; i++) {
		img = $(Objects.Layering.Images[i]);
		Element.remove(img);
		img.Draggable.destroy();
	}
	Objects.Layering.Images.clear();

	for (var i = 0; i < aTilesUsed.length; i++)
		for (var j = 0; j < aTilesUsed[i].length; j++)
			aTilesUsed[i][j] = 0;
}
//Save/Load functions
function loadMap(mapData) {
	if (!mapData) {
		var oMaps = $("cmbMaps");
		if (!oMaps.selectedIndex) return;
		var val = oMaps.options[oMaps.selectedIndex].text;
		if (online) {
			mapData = cookie.getMap(val);
		}else {
			for (var i = 0; i < aMaps.length; i++) { if (aMaps[i].startsWith(val)) { mapData = aMaps[i].split(","); break; } }
		}
	}
	
	clearMap();
	//$("cmbGridZoom").selectedIndex = 1;
	//cmbGrid_OnChange();
	
	//Load Map
	for (var aTileInfo, aAnchor, i = 1; i < mapData.length; i++) {
		aTileInfo = mapData[i].split(".");
		aAnchor = 7 < aTileInfo.length? [aTileInfo[7], aTileInfo[8], aTileInfo[9], aTileInfo[10]] : null;
		if (aAnchor && aAnchor.join(",") == ",,,") aAnchor = null;
		_addTile(aTileInfo[0], aTileInfo[1], aTileInfo[2], aTileInfo[3]? parseInt(aTileInfo[3]) : 0, aTileInfo[4], aTileInfo[5], aTileInfo[6], aAnchor);
	}
	loseFocus();
}
function saveMap(mapData) {
	if (!mapData) {
		var name = prompt("Enter a Map Name", $("cmbMaps").options.selectedIndex? $("cmbMaps").options[$("cmbMaps").options.selectedIndex].text : "New Map");
		if (!name) return;
		mapData = name + "," + buildSaveString();
	}
	if (aMaps.indexOf(mapData) < 0) aMaps[aMaps.length] = mapData;
	if (online) {
		cookie.addMap(mapData.split(",")[0], mapData);
		loadMapList();
	}
}
function loadMapList() {
	var oMaps = $("cmbMaps"); oMaps.options.length = 1;
	if (online) {
		if (cookie.Maps) for (var i = 0; i < cookie.Maps.length; i++) oMaps.options[i + 1] = new Option(cookie.Maps[i], i);
	}else {
		for (var i = 0; i < aMaps.length; i++) oMaps.options[i + 1] = new Option(aMaps[i].split(",")[0], i);
	}	
}
function buildSaveString() {
	var ret = new Array();
	var img;
	for (var i = 0; i < Objects.Layering.Images.length; i ++) {
		img = $(Objects.Layering.Images[i]);
		ret[i] = (img.SetIndex + "." + img.TileIndex + "." + img.Tile.Side + "." + img.Tile.Rotate + "." + (parseInt(img.style.left) * (16 / gridSize)) + "." + (parseInt(img.style.top) * (16 / gridSize)) + "." + img.style.zIndex + "." + (img.Anchor? img.Anchor.join(".") : "...")).replace(/px/g, "");
	}
	return ret;
}
function deleteMap() {
	var oMaps = $("cmbMaps"); if (!oMaps.selectedIndex) return;
	if (!confirm("Delete Map: " + oMaps.options[oMaps.selectedIndex].text)) return;
	if (online) {
		cookie.Maps = cookie.Maps.without(oMaps.options[oMaps.selectedIndex].text);
		cookie.deleteMap(oMaps.options[oMaps.selectedIndex].text);
	}else {
		for (var i = 0; i < aMaps.length; i++) { if (aMaps[i].startsWith(oMaps.options[oMaps.selectedIndex].text)) { aMaps = aMaps.without(aMaps[i]); break; } }
	}
	loadMapList();
}
function exportMap() {
	var name = prompt("Enter a Map Name", $("cmbMaps").options[$("cmbMaps").options.selectedIndex].text);
	if (!name) return;
	prompt("Here is your Map", name + "," + buildSaveString());
}
function importMap() {
	var mapData = prompt("Enter Map Data", "");
	if (!mapData) return;
	var aMapData = mapData.split(",");
	loadMap(aMapData);
	saveMap(mapData);
	var oMaps = $("cmbMaps");
	for (var i = 0; i < oMaps.options.length; i++)
		if (oMaps.options[i].text == aMapData[0]) {
			oMaps.selectedIndex = i;
			break;
		}
}
var aMaps = new Array();
function AddMap(mapData) { saveMap(mapData); }

function printView() {
	var offset = Objects.Layering.FindOffset();
	var html = "<html><head><title>Print View</title><style media='print' type='text/css'>INPUT {display:none;}</style></head><body>";
	for (var img, i = 0; i < Objects.Layering.Images.length; i++) {
		img = $(Objects.Layering.Images[i]);
		html += "<IMG src='" + img.src + "' width='" + (img.width-2) + "' height='" + (img.height-2) + "' style='border:1px solid red;position:absolute;left:" + (parseInt(img.style.left) - offset[0]) + "px;top:" + (parseInt(img.style.top) - offset[1]) + "px;z-index:" + img.style.zIndex + ";'/>";
	}
	html += "<input type='button' value='Print' onclick='window.print();' /></body></html>";

	var win = window.open("", "jDungeonTilesPrintView", "width=640,height=480,scrollbars=yes,resizable=yes");
	win.document.open();
	win.document.write(html);
	win.document.close();
}

//Misc functions
function _calculateCell(iValue) { return Math.floor((iValue - 1) / gridSize) + 1; }
function calculateCell(oEvent) {
	var sXY = "", x = 0, y = 0;
	if (oEvent.offsetX) {
		sXY = _calculateCell(oEvent.offsetX + x) + ", " + _calculateCell(oEvent.offsetY + y);
	}else {
		sXY = _calculateCell(oEvent.layerX + x) + ", " + _calculateCell(oEvent.layerY + y);
	}
	return sXY;
}
