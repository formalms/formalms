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

function TableView(tableId, oConfig) {
	this._init(tableId, oConfig);
}

TableView.prototype = {

	id: null,

	serverUrl: '',

	imageUrl: '',

	baseUrl: '',

	_oLangs: null,

	_oSelector: null,

	_oDataSource: null,

	_oDatatable: null,

	_oPaginator: null,

	_oFilter: {},

	_oHeadSelectionFields: null,

	pageSize: null,

	//standard formatters
	_deleteDialog: null,
	
	_deleteDialogConfig: {
		idField: false,
		nameField: false
	},

	_deleteFormatterEvent: function(e, o) {
		
		var dialog, idDialog = o.scope.id+"_delete_dialog";

		var clickYes = function() {
			var tableView = o.scope, popup = this;
			YAHOO.util.Connect.asyncRequest("POST", tableView.serverUrl,
				{
					success: function(oResponse) {
						var data = YAHOO.lang.JSON.parse(oResponse.responseText);

						if (data.success) {
							popup.destroy();
							tableView.refresh();
						} else {
							//error
						}
					},
					failure: tableView.connectionFailure,
					scope: tableView
				},
				'command=del_row&idrow='+o.data.getData(tableView._deleteDialogConfig.idField)
			);
		};

		var clickNo = function() { this.destroy(); }

		var dialogEl = document.createElement("div");
		dialogEl.id = idDialog;
		document.body.appendChild(dialogEl);

		dialog = new YAHOO.widget.SimpleDialog(idDialog, {
			width: "400px",
			fixedcenter: true,
			visible: false,
			draggable: true,
			close: false,
			constraintoviewport: true,
			modal: true,
			icon: YAHOO.widget.SimpleDialog.ICON_WARN,
			buttons: [
				{ text: o.scope._oLangs.get('_YES'), handler: clickYes, isDefault:true },
				{ text: o.scope._oLangs.get('_NO'), handler: clickNo }
			]
		} );

		var content, name = o.scope._deleteDialogConfig.nameField;
		content = (!name ? o.scope._oLangs.get('_STD_DEL') : o.scope._oLangs.get('_DEL')+':&nbsp;<b>'+o.data.getData(name)+'</b>');

		dialog.setHeader(o.scope._oLangs.get('_AREYOUSURE'));
		dialog.setBody(content);

		dialog.render();
		dialog.show();
		YAHOO.util.Event.preventDefault(e);
	},

	deleteFormatter: function(elCell, oRecord, oColumn, oData) {
		
		if(oData == '' || oData == 0 ) return;

		var a = document.createElement('a');
		a.href = "javascript:;";//this.serverUrl+'&command=del_row&idrow='+oRecord.getData(this._deleteDialogConfig.idField);

		elCell.appendChild(a);
		
		if(oData == "1" || oData == 1) {
			var ico = document.createElement('img');
			ico.src = this.imageUrl+'/standard/delete.png';
			ico.alt = this._oLangs.get('_DEL');
			a.appendChild(ico);
		} else a.innerHTML = oData;
		
		elCell.appendChild(a);

		YAHOO.util.Event.addListener(a, "click", this._deleteFormatterEvent, {scope: this, data: oRecord});
	},

	_init: function(tableId, oConfig) {

		var oScope = this;

		this.id = tableId;

		this.serverUrl = oConfig.serverUrl;

		this.imageUrl = oConfig.imageUrl;

		this.baseUrl = oConfig.baseUrl;
		
		this.pageSize = oConfig.rowsPerPage;

		this._oLangs = new LanguageManager( ( oConfig.langs || {} )  );

		this._rowsPerPage = oConfig.rowsPerPage;

		//--------------------------------------------------------------------------

		if (oConfig.deleteDialog) {
			if (oConfig.deleteDialog.id) this._deleteDialogConfig.idField = oConfig.deleteDialog.id;
			if (oConfig.deleteDialog.name) this._deleteDialogConfig.nameField = oConfig.deleteDialog.name;
		}

		//--------------------------------------------------------------------------

		// Column definitions
		var oColumnDefs = oConfig.columns;

		// intial data configuration
		var ci = oConfig.initialData;

		//initial filter configuration
		if (oConfig.initialFilter) {
			for (var x in oConfig.initialFilter)
				this.setFilterParam(x, oConfig.initialFilter[x].operator, oConfig.initialFilter[x].value);
		}

		// DataSource instance
		var oDataSource = new YAHOO.util.DataSource(oConfig.serverUrl+'&command=get_rows&');
		oDataSource.connMethodPost = true;
		oDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
		oDataSource.responseSchema = {
			resultsList: "records",
			fields: oConfig.fields,
			metaFields: {
				totalRecords: "totalRecords" // Access to value in the server response
			}
		};
		
		// DataTable configuration
		var oTableConfigs = {
			initialRequest: "sort="+ci.sort+"&dir="+ci.dir+"&startIndex="+ci.startIndex+"&results="+ci.results+"&"+this._getFilterRequest(),
			generateRequest: this._getRequestString,
			dynamicData: true, // Enables dynamic server-driven data
			sortedBy : { // Sets UI initial sort arrow
				key: ci.sort,
				dir: this._convertDir(ci.dir)
			},
			paginator: new YAHOO.widget.Paginator({
				rowsPerPage : oConfig.rowsPerPage,
				template: "{FirstPageLink} {PreviousPageLink}"
						+ " {PageLinks} "
						+ "{NextPageLink} {LastPageLink}"
						+ " {RangeRecords} "+ this._oLangs.get('_OF')+" <strong>{TotalRecords}</strong>",

				pageLinks : 5, // configure the PageLinks UI Component

				firstPageLinkLabel:		"&laquo; "+this._oLangs.get('_START'),	//' Inizio',
				previousPageLinkLabel:	"&lsaquo; "+this._oLangs.get('_PREV'),	//'Precedente',
				nextPageLinkLabel:		this._oLangs.get('_NEXT')+" &rsaquo;", //'Successivo ',
				lastPageLinkLabel:		this._oLangs.get('_END')+" &raquo;"	//'Fine
			})

		};

		// Localize default string value
		if(oConfig.langs) {
			oTableConfigs.MSG_EMPTY = this._oLangs.get('MSG_EMPTY');
			oTableConfigs.MSG_ERROR = this._oLangs.get('MSG_ERROR');
			oTableConfigs.MSG_LOADING = this._oLangs.get('MSG_LOADING');
		}


		// DataTable instance
		this._oDataTable = new YAHOO.widget.DataTable(tableId, oColumnDefs, oDataSource, oTableConfigs);
		this._oPaginator = this._oDataTable.paginator;
		this._oDataSource = oDataSource;
		this._oDataTable._oTableView = this;

		// Update totalRecords on the fly with value from server
		this._oDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
			if(!oPayload) oPayload = this.getState();
			oPayload.totalRecords = oResponse.meta.totalRecords;
			return oPayload;
		}
		
		// Add listeners
		this._oDataTable.subscribe("rowMouseoverEvent", this._oDataTable.onEventHighlightRow);
		this._oDataTable.subscribe("rowMouseoutEvent", this._oDataTable.onEventUnhighlightRow);
		this._oDataTable.subscribe("cellClickEvent", this._oDataTable.onEventShowCellEditor);

	},

	_convertDir: function(dir) {
		var temp = '';
		switch (dir) {
			case 'asc': temp = YAHOO.widget.DataTable.CLASS_ASC; break;
			case 'desc': temp = YAHOO.widget.DataTable.CLASS_DESC; break;
			case YAHOO.widget.DataTable.CLASS_ASC: temp = 'asc'; break;
			case YAHOO.widget.DataTable.CLASS_DESC: temp = 'desc'; break;
		}
		return temp;
	},


	_sendTableRequest: function(oRequestData) {

		if(oRequestData.initialData) oRequestData = oRequestData.initialData;

		var istartIndex = oRequestData.startIndex;
		var iresults	= oRequestData.results;
		var isort		= oRequestData.sort;
		var idir		= oRequestData.dir;

		var request		= "sort="+isort+"&dir="+idir+"&startIndex="+istartIndex+"&results="+iresults+"&"+this._getFilterRequest();

		this._oDataTable.showTableMessage(this._oDataTable.get("MSG_LOADING"), YAHOO.widget.DataTable.CLASS_LOADING);
		this._oDataTable.getDataSource().sendRequest(request, {
			success: this._oDataTable.onDataReturnReplaceRows,
			failure: this.connectionFailure,
			scope: this._oDataTable
		});
	},


	_getRequestString: function(oState, oSelf) {

		// Get states or use defaults
		oState = oState || {pagination:null, sortedBy:null};
		var sort = (oState.sortedBy) ? oState.sortedBy.key : "myDefaultColumnKey";
		var dir = (oState.sortedBy) ? oState.sortedBy.dir : "asc";
		var startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		var results = (oState.pagination) ? oState.pagination.rowsPerPage : 20;

		// Build custom request
		var t = oSelf._oTableView;
		return  'command=get_rows&flat=1&'+
				'&startIndex=' + startIndex +
				'&results=' + results +
				'&sort=' + sort +
				'&dir=' + t._convertDir(dir)+
				"&"+t._getFilterRequest();
	},

	connectionFailure: function() { 
		alert(this._oLangs.get('_SERVER_CONNECTION_ERROR'));
	},


	refresh: function() {
		var state = this.getState();
		this._sendTableRequest({
				startIndex: 0,
				results: this.pageSize,
				sort: state.sortedBy.key,
				dir: this._convertDir(state.sortedBy.dir)
		});
	},

	toString: function() {
		return 'TableView instance ('+this.id+')';
	},

	//----------------------------------------------------------------------------

	getState: function() {
		return this._oDataTable.getState();
	},

	setFilter: function(oFilter) {
		this._oFilter = oFilter;
	},

	setFilterParam: function(name, operator, value) {
		this._oFilter[name] = {
			operator: operator,
			value:value
		};
	},

	unsetFilterParam: function (name) {
		this._oFilter = {};
	},

	_getFilterRequest: function() {
		var temp = [];
		for (var x in this._oFilter) {
			temp.push("filter["+x+"][operator]="+this._oFilter[x].operator);
			temp.push("filter["+x+"][value]="+this._oFilter[x].value);
		}
		return temp.join("&");
	}
}

/*******************************************************************************
 * Custom paginator template tags
 ******************************************************************************/

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
	p.subscribe('recordOffsetChange',this.update,this,true);
	p.subscribe('totalRecordsChange',this.update,this,true);
	p.subscribe('rowsPerPageChange', this.update,this,true);
	p.subscribe('beforeDestroy',this.destroy,this,true);
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

