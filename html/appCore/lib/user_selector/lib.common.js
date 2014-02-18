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
//datatable paginator settings

if (YAHOO.widget.Paginator) {

YAHOO.widget.Paginator.ui.TotalRecords = function (p) {
	this.paginator = p;
  p.subscribe('recordOffsetChange',this.update,this,true);
  p.subscribe('totalRecordsChange',this.update,this,true);
  p.subscribe('rowsPerPageChange', this.update,this,true);
  p.subscribe('beforeDestroy',this.destroy,this,true);
};



YAHOO.widget.Paginator.ui.TotalRecords.prototype = {

  span: null,

	render: function (id_base) {
    this.span           = document.createElement('span');
    this.span.id        = id_base + '-total-records-span';
    this.span.innerHTML = this.paginator.getTotalRecords();
		return this.span;
 	},
 	
	update: function (e) {
    //if (e && e.prevValue === e.newValue) { return; }
    var recs = this.paginator.getPageRecords();
      this.span.innerHTML = this.paginator.getTotalRecords();
  },
  
  destroy : function () {}
};




YAHOO.widget.Paginator.ui.RangeRecords = function (p) {
	this.paginator = p;
	p.subscribe('recordOffsetChange',this.update,this,true);
  p.subscribe('totalRecordsChange',this.update,this,true);
  p.subscribe('rowsPerPageChange', this.update,this,true);
  p.subscribe('beforeDestroy',this.destroy,this,true);
};




YAHOO.widget.Paginator.ui.RangeRecords.prototype = {

  span : null,

	render : function (id_base) {
	  var recs = this.paginator.getPageRecords();
	  this.span = document.createElement('span');
	  this.span.id = id_base + '-range-records-span';
    if(recs) this.span.innerHTML = (recs[0] + 1) + ' - ' + (recs[1] + 1);
	  else this.span.innerHTML = '0';
	  return this.span;
	},
	
	update : function (e) {
    //if (e && e.prevValue === e.newValue) { return; }
		var recs = this.paginator.getPageRecords();
		if(recs) this.span.innerHTML = (recs[0] + 1) + ' - ' + (recs[1] + 1);
		else this.span.innerHTML = '0';
  },
  
  destroy : function () {}
}

}

//utils functions

function addSlashes(str) {
  str=str.replace(/\'/g,'\\\'');
  str=str.replace(/\"/g,'\\"');
  str=str.replace(/\\/g,'\\\\');
  str=str.replace(/\0/g,'\\0');
  return str;
}

function stripSlashes(str) {
  str=str.replace(/\\'/g,'\'');
  str=str.replace(/\\"/g,'"');
  str=str.replace(/\\\\/g,'\\');
  str=str.replace(/\\0/g,'\0');
  return str;
}




//client side text keys manager, text keys provided by server during initialization


LangManager = function(langs) {
  if (langs) this._oKeys = langs;
}


LangManager.prototype = {

  _oKeys: {},

  def: function(textKey) { if (this._oKeys[textKey]) return this._oKeys[textKey]; else return textKey; }

}