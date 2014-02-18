/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

function ElemSelector(argument) {
	this.current_selection = new Object();
	this.num_selected = 0;
	this.base = argument || '_';
	this.counter = false;
	this._events = {};
}

ElemSelector.prototype.refreshCounter = function() {
	if(!this.counter) return;
	YAHOO.util.Dom.get(this.counter).innerHTML = this.num_selected;
}

ElemSelector.prototype.toString = function() {
	var ind, temp = [];//str = '';
	for(ind in this.current_selection) {
		if(ind.indexOf( this.base ) >= 0 )//&& this.current_selection[ind] != 0)
			temp.push( ind.substr(this.base.length) );//str = str + ',' + parseInt(ind.substr(this.base.length));
	}
	return temp.join(",");
}

ElemSelector.prototype.initSelection = function(arr_sel, no_events) {
	var i, ind;
	if (!YAHOO.lang.isArray(arr_sel))
		arr_sel = [];

	if (arr_sel.length)
    this.addElements(arr_sel, no_events ? no_events : true);//for (i=0; i<arr_sel.length; i++) this.addsel(arr_sel[i]);
  else
    for(ind in arr_sel) this.addsel(ind);
	return true;
}

ElemSelector.prototype.addsel = function(id_sel) {
/*
	if(this.current_selection[this.base+id_sel] != id_sel) {
		this.num_selected++;
	}
	this.current_selection[this.base+id_sel] = id_sel;
*/
  if (this.current_selection[this.base+id_sel]) return;
  this.current_selection[this.base+id_sel] = id_sel;
  this.num_selected++;
	this._fireEvent("add");
}

ElemSelector.prototype.remsel = function(id_sel) {
/*
	if(this.current_selection[this.base+id_sel] == id_sel) {
		this.num_selected--;
		this.current_selection[this.base+id_sel] = 0;
	}
*/
  if (this.current_selection[this.base+id_sel]) {
    delete this.current_selection[this.base+id_sel];
    this.num_selected--;
  }
	this._fireEvent("remove");
}

ElemSelector.prototype.addElements = function(o, no_events) {
	for (var i=0; i<o.length; i++) {
		if (!this.current_selection[this.base+o[i]]) {
			this.current_selection[this.base+o[i]] = o[i];
			this.num_selected++;
		}
	}
	if (!no_events) this._fireEvent("add");
}

ElemSelector.prototype.removeElements = function(o, no_events) {
	for (var i=0; i<o.length; i++) {
		if (this.current_selection[this.base+o[i]]) {
			delete this.current_selection[this.base+o[i]];
			this.num_selected--;
		}
	}
	if (!no_events) this._fireEvent("remove");
}

ElemSelector.prototype.isset = function(id_sel) {
	if (this.current_selection[this.base+id_sel] == id_sel) return true;
	return false;
}

ElemSelector.prototype.reset = function() {
	this.current_selection = {};
	this.num_selected = 0;
	this._fireEvent("reset");
}


ElemSelector.prototype.subscribe = function(event, action, scope) {
	if (!this._events[event])
		this._events[event] = [];
	var t = {};
	t.action = action;
	if (scope) t.scope = scope;
	this._events[event].push(t);
}

ElemSelector.prototype._fireEvent = function(event) {
	if (this._events[event]) {
		var i, scope, list = this._events[event];
		for (i=0; i<list.length; i++) {
			 scope = list[i].scope || this;
			 list[i].action.call(scope);
		}
	}
}