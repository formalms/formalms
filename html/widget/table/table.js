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

// custom formatters

YAHOO.widget.DataTable.Formatter.stdSelect = function(elLiner, oRecord, oColumn, oData) {
	var value = oRecord.getData("id");
	var id = this.getTableEl().parentNode.id+'_sel_'+value;
	var checked = this.innerSelector.isset(value) ? ' checked="checked"' : '';
	elLiner.innerHTML = '<input type="checkbox" id="'+id+'" value="'+value+'"'+checked+'/>';
}

YAHOO.widget.DataTable.Formatter.stdModify = function(elLiner, oRecord, oColumn, oData) {
	var translation = ( YAHOO.DataTableLangManager ? YAHOO.DataTableLangManager.get('_EDIT') : 'Edit' );
	var id = this.getTableEl().parentNode.id+'_mod_'+oRecord.getData("id");
	if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite subs_mod" title="'+translation+'"><span>'+translation+'</span></a>';
	else elLiner.innerHTML = '';
}

YAHOO.widget.DataTable.Formatter.stdDelete = function(elLiner, oRecord, oColumn, oData) {
	var translation = ( YAHOO.DataTableLangManager ? YAHOO.DataTableLangManager.get('_DELETE') : 'Delete' );
	var id = this.getTableEl().parentNode.id+'_del_'+oRecord.getData("id");
	if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite subs_del" title="'+translation+'"><span>'+translation+'</span></a>';
	else elLiner.innerHTML = '';
}

YAHOO.widget.DataTable.Formatter.dup = function(elLiner, oRecord, oColumn, oData) {
	var translation = ( YAHOO.DataTableLangManager ? YAHOO.DataTableLangManager.get('_MAKE_A_COPY') : 'Make a copy' );
	var id = this.getTableEl().parentNode.id+'_dup_'+oRecord.getData("id");
	if(oData) elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite subs_dup" title="'+translation+'"><span>'+translation+'</span></a>';
	else elLiner.innerHTML = '';
}

YAHOO.widget.DataTable.Formatter.stdDialog = function(elLiner, oRecord, oColumn, oData) {
	var key = oColumn.getKey();
	var id = this.getTableEl().parentNode.id+'_frm_'+key+'_'+oRecord.getData("id");
	var subClass = (this.stdDialogIcons && this.stdDialogIcons[key]) ? " "+this.stdDialogIcons[key] : "";
	elLiner.innerHTML = '<a id="'+id+'" href="'+oData+'" class="ico-sprite'+subClass+'" title=""><span></span></a>';
}

//cell highlight
var highlightEditableCell = function(oArgs) {
	var elCell = oArgs.target;
	if(YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {
		this.highlightCell(elCell);
	}
};

// Custom paginator template tags 
// Total Records ui component
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
		this.span = document.createElement('span');
		this.span.id = id_base + '-total-records-span';
		this.span.innerHTML = this.paginator.getTotalRecords();
		return this.span;
	},

	update: function (e) {
		this.span.innerHTML = this.paginator.getTotalRecords();
	},

	destroy : function () {}
};

// Range Records ui component
YAHOO.widget.Paginator.ui.RangeRecords = function (p) {
	this.paginator = p;
	p.subscribe('recordOffsetChange', this.update, this, true);
	p.subscribe('totalRecordsChange', this.update, this, true);
	p.subscribe('rowsPerPageChange', this.update, this, true);
	p.subscribe('beforeDestroy', this.destroy, this, true);
};

YAHOO.widget.Paginator.ui.RangeRecords.prototype = {

	span : null,

	init: function(p) {},

	render : function (id_base) {
		var recs = this.paginator.getPageRecords();
		this.span = document.createElement('span');
		this.span.id = id_base + '-range-records-span';
		if(recs) this.span.innerHTML = (recs[0] + 1) + ' - ' + (recs[1] + 1);
		else this.span.innerHTML = '0';
		return this.span;
	},

	update : function (e) {
		var recs = this.paginator.getPageRecords();
		if(recs) this.span.innerHTML = (recs[0] + 1) + ' - ' + (recs[1] + 1);
		else this.span.innerHTML = '0';
	},

	destroy : function () {}
}
