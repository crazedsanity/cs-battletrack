//Helper functions for strings
var sFilterCaseSensitive = false;
String.prototype.startsWith = function(sString) {return sFilterCaseSensitive? this.substring(0, sString.length) == sString : this.substring(0, sString.length).toUpperCase() == sString.toUpperCase();}
String.prototype.endsWith = function(sString) {return sFilterCaseSensitive? this.substring(this.length - sString.length) == sString : this.substring(this.length - sString.length).toUpperCase() == sString.toUpperCase();}
//Helper functions for arrays
Array.prototype.contains = function(obj) {return 0 <= this.indexOf(obj);}

var Objects = { Version : { Major:1, Minor:1, Revision:1 } };
/*
	1.0.0 >> Initial
	1.1.0 >> Modified Tile.prototype.initialize to make arguments a single Object and add an Extension Option.
	         Modified Set.prototype.AddTile to have two arguments (TileCount, TileOptions).
	         Set.prototype.AddTile now allows for an Extension Option to use non .JPG tiles.
	         Added .Zoom to LayeringObject to allow the user to change the viewed size of the map.
	1.2.0 >> 2007.01.15 - Moved objects into Objects object.
	         Modified Objects.Tile to use ImageA and ImageB as arrays
	         Modified Objects.Tile to allow for TagB; which will let me reuse images (to reduce overall number of files)
	         Modified Objects.Layering.GetImageUnder to fix correct a bug
*/

//Tile Class
Objects.Tile = Class.create();
Objects.Tile.prototype = {
	initialize : function() {
		var Options = arguments[0] || {};
		if (!Options.Tag) return;
		this.Dimensions = Options.Dimensions;
		this.Height = Options.Height;
		this.Options = Options;
		this.Rotate = 0;
		this.Side = "A";
		this.Tag = Options.Tag
		this.TagB = Options.TagB? Options.TagB : false;
		this.Width = Options.Width;

		this.ImageA = [new Image(this.Width, this.Height), new Image(this.Height, this.Width), new Image(this.Width, this.Height), new Image(this.Height, this.Width)];
		this.ImageA[0].src = "./tiles/" + this.Tag + (Options.NameB? ".a" : "") + ".0." + Options.Extension;
		this.ImageA[1].src = "./tiles/" + this.Tag + (Options.NameB? ".a" : "") + ".90." + Options.Extension;
		this.ImageA[2].src = "./tiles/" + this.Tag + (Options.NameB? ".a" : "") + ".180." + Options.Extension;
		this.ImageA[3].src = "./tiles/" + this.Tag + (Options.NameB? ".a" : "") + ".270." + Options.Extension;
		this.NameA = Options.NameA;
		
		if (Options.NameB) {
			this.ImageB = [new Image(this.Width, this.Height), new Image(this.Height, this.Width), new Image(this.Width, this.Height), new Image(this.Height, this.Width)];
			this.ImageB[0].src = "./tiles/" + (this.TagB? this.TagB : this.Tag) + ".b.0." + Options.Extension;
			this.ImageB[1].src = "./tiles/" + (this.TagB? this.TagB : this.Tag) + ".b.90." + Options.Extension;
			this.ImageB[2].src = "./tiles/" + (this.TagB? this.TagB : this.Tag) + ".b.180." + Options.Extension;
			this.ImageB[3].src = "./tiles/" + (this.TagB? this.TagB : this.Tag) + ".b.270." + Options.Extension;
			this.NameB = Options.NameB;
		}		
	},
	Clockwise : function(img) {
		this.Rotate += 90;
		if (this.Rotate == 360) this.Rotate = 0;
		img.height = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (this.Rotate == 0 || this.Rotate == 180? this.Height : this.Width);
		img.width = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (this.Rotate == 0 || this.Rotate == 180? this.Width : this.Height);
		img.src = this.GetImage();
	},
	Clone : function() { return new Objects.Tile( this.Options ); },
	CounterClockwise : function(img) {
		this.Rotate -= 90;
		if (this.Rotate == -90) this.Rotate = 270;
		img.height = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (this.Rotate == 0 || this.Rotate == 180? this.Height : this.Width);
		img.width = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (this.Rotate == 0 || this.Rotate == 180? this.Width : this.Height);
		img.src = this.GetImage();
	},
	Flip : function(img) { if (!this.NameB) return; this.Side = this.Side == "A"? "B" : "A"; img.src = this.GetImage(); },
	GetImage : function() { return this.Side == "A"? this.ImageA[this.Rotate / 90].src : this.ImageB[this.Rotate / 90].src; },
	GetName : function() { return this.Side == "A"? this.NameA : this.NameB; }
}

//Set Class
Objects.Set = Class.create();
Objects.Set.prototype = {
	initialize : function(sName, sCode) {
		this.Code = sCode;
		this.Count = 1;
		this.Name = sName;
		this.Tiles = new Array();
		this.TileCount = new Array();
	},
	AddTile : function() {
		if (arguments.length == 1)  {
			return;
		}else if (arguments.length == 2) {
			var opts = arguments[1];
			opts.Tag = this.Code + '/' + opts.Tag;
			if (opts.TagB) opts.TagB = this.Code + '/' + opts.TagB;
			this.Tiles[this.Tiles.length] = new Objects.Tile( opts );
		}else {
			this.Tiles[this.Tiles.length] = new Objects.Tile( { Width:arguments[1]?arguments[1]:16, Height:arguments[2]?arguments[2]:16, Tag:this.Code+"/"+arguments[3], Dimensions:arguments[4]?arguments[4]:"1x1", NameA:arguments[5]?arguments[5]:"A", NameB:arguments[6]?arguments[6]:"", Extension:arguments[7]?arguments[7]:"jpg" } );
		}
		this.TileCount[this.TileCount.length] = arguments[0]? arguments[0] : 1;
	}
}

//Layering Object
Objects.Layering = {
	Images : new Array(),
	Add    : function(img) { this.Images[this.Images.length] = img.id; this.Layer(); },
	Back   : function(img) {
		var index = this.Images.indexOf(img.id);
		if (index == 0) return;
		for (var i = index; 0 < i; i--) this.Images[i] = this.Images[i - 1];
		this.Images[0] = img.id;
		this.SyncAnchor(img);
	},
	FindOffset : function() {
		var ret = [-1, -1];
		for (var left, top, i = 0; i < this.Images.length; i++) {
			left = parseInt($(this.Images[i]).style.left);
			ret[0] = ret[0] > left || ret[0] == -1? left : ret[0];

			top = parseInt($(this.Images[i]).style.top);
			ret[1] = ret[1] > top || ret[1] == -1? top : ret[1];
		}
		return ret;
	},
	Front  : function(img) {
		var index = this.Images.indexOf(img.id);
		if (index == this.Images.length - 1) return;
		for (var i = index; i < this.Images.length - 1; i++) this.Images[i] = this.Images[i + 1];
		this.Images[this.Images.length - 1] = img.id;
		this.SyncAnchor(img);
	},
	GetImageUnder : function(img) {
		var index = this.Images.indexOf(img.id);
		var xy = [parseInt(img.style.left), parseInt(img.style.top)];
		var other;
		for (var i = index - 1; -1 < i; i--) {
			other = $(this.Images[i]);
			if (Position.within($(this.Images[i]), xy[0], xy[1])) return other;
		}
		return null;
	},
	Layer  : function()    { for (var i = 0; i < this.Images.length; i++) $(this.Images[i]).style.zIndex = i + 5; },
	Lower  : function(img) {
		var index = this.Images.indexOf(img.id);
		if (index == 0) return;
		this.Images[index] = this.Images[index - 1];
		this.Images[index - 1] = img.id;
		this.SyncAnchor(img);
	},
	Raise  : function(img) {
		var index = this.Images.indexOf(img.id);
		if (index == this.Images.length - 1) return;
		this.Images[index] = this.Images[index + 1];
		this.Images[index + 1] = img.id;
		var other = $(this.Images[index]);
		if (other.Anchor && other.Anchor[0] == img.id) this.Raise(img);
		this.SyncAnchor(img);
	},
	Remove : function(img) { this.Images = this.Images.without(img.id); this.Layer(); },
	SyncAnchor : function(img) {
		if (img.IsAnchor) {
			var anchored = img.GetAnchored();
			for (var i = 0; i < anchored.length; i++) {
				this.Back($(anchored[i]));
				while (this.Images.indexOf($(anchored[i]).id) < this.Images.indexOf(img.id)) {
					this.Raise($(anchored[i]));
				}
			}
		}
		this.Layer();
	},
	Zoom : function(oldSize, newSize) {
		if (oldSize != newSize)
			for (var i = 0, img; i < this.Images.length; i++) {
				img = $(this.Images[i]);
				img.Draggable.options.snap = [newSize/2, newSize/2];
				img.height = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (img.Tile.Rotate == 0 || img.Tile.Rotate == 180? img.Tile.Height : img.Tile.Width);
				img.width = (gridSize == 16? 1 : gridSize == 8? 0.5 : 2) * (img.Tile.Rotate == 0 || img.Tile.Rotate == 180? img.Tile.Width : img.Tile.Height);
				img.style.left = (parseInt(img.style.left) * (newSize / oldSize)) + "px";
				img.style.top = (parseInt(img.style.top) * (newSize / oldSize)) + "px";
			}
	}
}
