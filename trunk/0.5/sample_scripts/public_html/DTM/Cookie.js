/*
Creates a cookie to be used for saving settings.
	document: object - the Document object the cookie is associated with
	name:     string - the name of the Cookie
	hours:    (optional) number  - number of hours till expiration
	path:     (optional) string  - the Cookie's path attribute
	domain:   (optional) string  - the Cookie's domain attribute
	secure:   (optional) boolean - value, that requests a secure cookie if 'true'
*/
var Cookie = Class.create();
Cookie.prototype = {
	initialize : function(document, name, hours, path, domain, secure) {
		this.$document = document;
		this.$name = name;
		this.$expiration = hours? new Date((new Date()).getTime() + hours * 3600000) : null;
		this.$path = path? path : null;
		this.$domain = domain? domain : null;
		this.$secure = secure? true : false;
	},
	store : function() {
		var cookieval = "";
		for (var prop in this) {
			if ((prop.charAt(0) == "$") || (typeof this[prop] == "function")) continue;
			if (cookieval != "") cookieval += "&";
			cookieval += prop + "=" + escape(typeof this[prop] == "array"? this[prop].join() : this[prop]);
		}

		var cookie = this.$name + "=" + cookieval;
		if (this.$expiration) cookie += "; expires=" + this.$expiration.toGMTString();
		if (this.$path) cookie += "; path=" + this.$path;
		if (this.$domain) cookie += "; domain=" + this.$domain;
		if (this.$secure) cookie += "; secure";

		this.$document.cookie = cookie;
	},
	load : function() {
		var allcookies = this.$document.cookie;
		if (allcookies == "") return false;

		var start = allcookies.indexOf(this.$name + "=");
		if (start == -1) return false;
		start += this.$name.length + 1;

		var end = allcookies.indexOf(";", start);
		if (end == -1) end = allcookies.length;

		var cookieval = allcookies.substring(start, end);

		var a = cookieval.split("&");
		for (var i = 0; i < a.length; i++) a[i] = a[i].split("=");
		for (var i = 0; i < a.length; i++) this[a[i][0]] = -1 < unescape(a[i][1]).indexOf(",")? unescape(a[i][1]).split(",") : unescape(a[i][1]);
	},
	remove : function() {
		var cookie = this.$name + "="
		if (this.$path) cookie += "; path=" + this.$path;
		if (this.$domain) cookie += "; domain=" + this.$domain;
		cookie += "; expires=Fri, 02-Jan-1970 00:00:00 GMT";

		this.$document.cookie = cookie;
	},
	Version : { Major:1, Minor:0, Revision:0 }
	/*
		1.0.0 >> Initial
		1.0.1 >> Create c.SetCount in function CoreCookie instead of later (bug fix)
	*/
}

function CoreCookie(name) {
	var c = new Cookie(document, name, 10000); c.load();
	if (!c.FreeForm) c.FreeForm = false; else c.FreeForm = eval(c.FreeForm);
	if (!c.MapIconText) c.MapIconText = true; else c.MapIconText = eval(c.MapIconText);
	if (!c.TilesIconText) c.TilesIconText = false; else c.TilesIconText = eval(c.TilesIconText);
	if (!c.GroupDeleteOption) c.GroupDeleteOption = true; else c.GroupDeleteOption = eval(c.GroupDeleteOption);
	if (!c.Maps) c.Maps = new Array(); if (!c.Maps.first) c.Maps = [c.Maps];
	if (!c.RememberTab) c.RememberTab = 0; else c.RememberTab = eval(c.RememberTab);
	if (!c.SetCount) c.SetCount = []; else c.SetCount = c.SetCount.toString().split(',');
	
	c.setRememberTab = function(value) { this.RememberTab = value; this.store(); }
	c.setTab = function(value) { this.Tab = value; this.store(); }
	c.setFreeForm = function(value) { this.FreeForm = value; this.store(); }
	c.setGroupDeleteOption = function(value) { this.GroupDeleteOption = value; this.store(); }
	c.setMapIconText = function(value) { this.MapIconText = value; this.store(); }
	c.setTilesIconText = function(value) { this.TilesIconText = value; this.store(); }
	c.getSetCount = function(index, def) { return this.SetCount.length && index < this.SetCount.length? this.SetCount[index] : def; }
	c.setSetCount = function(index, value) { this.SetCount[index] = value; this.store(); }
	c.getMap = function(name) { var map = new Cookie(document, "jDTM_" + name.replace(/ /g, ""), 10000); map.load(); return map.Data; }
	c.deleteMap = function(name) { var map = new Cookie(document, "jDTM_" + name.replace(/ /g, ""), 10000); map.load(); map.remove(); this.Maps = this.Maps.without(name); this.store(); }
	c.addMap = function(name, data) { var map = new Cookie(document, "jDTM_" + name.replace(/ /g, ""), 10000); map.Name = name; map.Data = data; map.store(); if (this.Maps.indexOf(name) < 0) this.Maps[this.Maps.length] = name; this.store(); }
	
	return c;
}
