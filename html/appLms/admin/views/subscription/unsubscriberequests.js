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

var UnsubscribeRequests = {
	filterText: "",
	oLangs: new LanguageManager(),
	link: '',

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		return  "&results=" 	+ results +
				"&startIndex=" 	+ startIndex +
				"&sort="		+ sort +
				"&dir="			+ dir +
				"&filter=" + UnsubscribeRequests.filterText;
	},

	initEvent: function() {
		var updateSelected = function() {
			var num = this.num_selected;
			var prefix = "num_subs_selected_", D = YAHOO.util.Dom;
			D.get(prefix+"top").innerHTML = num;
			D.get(prefix+"bottom").innerHTML = num;
		};
		var ds = DataTableSelector_unsubscriberequests_table;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	beforeRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=unsubscribe_accept_]');
		YAHOO.util.Event.purgeElement(elList);
	},

	postRenderEvent: function() {

		var elList = YAHOO.util.Selector.query('a[id^=unsubscribe_accept_]');
		YAHOO.util.Event.addListener(elList, "click", CreateDialog("waitingtable_confirm_dialog", {
			width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: false,
			header: UnsubscribeRequests.oLangs.get('_AREYOUSURE'),
			body: function() {
				var oRecord = DataTable_unsubscriberequests_table.getRecord(this);
				return '<form method="POST" id="unsubscribe_accept_dialog_form" action="'+this.href+'">'
					+'<p>'+UnsubscribeRequests.oLangs.get('_CONFIRM')+': <b>'+oRecord.getData("userid")+'</b></p>'
					+'</form>';
			},
			callback: function(o) {
				//if (o.message) YAHOO.util.Dom.get('ui_feedback_box').innerHTML = o.message;
				this.destroy();
				DataTable_unsubscriberequests_table.refresh();
			}
		}));

	},

	confirmFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a id="unsubscribe_accept_'+oRecord.getData("id")+'" class="ico-sprite subs_unassoc" '
				+' href="ajax.adm_server.php?r=' + UnsubscribeRequests.link + '/accept_unsubscribe_request&id='+oRecord.getData("id")+'" '
				+' title="'+UnsubscribeRequests.oLangs.get('_CONFIRM')+': '+oRecord.getData("userid")+'">'
				+'<span>'+UnsubscribeRequests.oLangs.get('_CONFIRM')+': '+oRecord.getData("userid")+'</span></a>';
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="unsubscriberequests_table_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	selectAllAdditionalFilter: function() {
		return "&filter=" + UnsubscribeRequests.filterText;
	},

	init: function(oConfig) {
		this.filterText = oConfig.filterText || "";
		this.link = oConfig.link || "";
		this.oLangs.set(oConfig.langs || {});
		var oLangs = this.oLangs;


		YAHOO.util.Event.onDOMReady(function(e) {
			YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.preventDefault(e);
						UnsubscribeRequests.filterText = this.value;
						DataTable_unsubscriberequests_table.refresh();
					} break;
				}
			});

			YAHOO.util.Event.addListener("filter_set", "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				UnsubscribeRequests.filterText = YAHOO.util.Dom.get("filter_text").value;
				DataTable_unsubscriberequests_table.refresh();
			});

			YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				YAHOO.util.Dom.get("filter_text").value = "";
				UnsubscribeRequests.filterText = "";
				DataTable_unsubscriberequests_table.refresh();
			});

			var elListConfirm = ["confirm_multi_over", "confirm_multi_bottom"];
			var elListDelete = ["delete_multi_bottom", "delete_multi_over"];

			YAHOO.util.Event.addListener(elListConfirm, "click", function(e) {
				var body, o = DataTableSelector_unsubscriberequests_table;
				if (o.num_selected > 0) {
					body = '<form method="POST" id="confirm_dialog_form" action="'+this.href+'">'
						+'<p>'+oLangs.get('_CONFIRM')+': '+o.num_selected+' '+oLangs.get('_USERS')+'</p>'
						+'<input type="hidden" value="'+o.toString()+'" name="requests" />'
						+'</form>';
				} else {
					body = '<p>'+oLangs.get('_EMPTY_SELECTION')+'</p>';
				}
				CreateDialog("confirm_dialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (o.num_selected > 0 ? false : true),
					header: oLangs.get('_AREYOUSURE'),
					body: body,
					callback: function() {
						this.destroy();
						DataTable_unsubscriberequests_table.refresh();
					}
				}).call(this, e);
			});

			YAHOO.util.Event.addListener(elListDelete, "click", function(e) {
				var body, o = DataTableSelector_unsubscriberequests_table;
				if (o.num_selected > 0) {
					body = '<form method="POST" id="delete_dialog_form" action="'+this.href+'">'
						+'<p>'+oLangs.get('_DEL')+': '+o.num_selected+' '+oLangs.get('_USERS')+'</p>'
						+'<input type="hidden" value="'+o.toString()+'" name="requests" />'
						+'</form>';
				} else {
					body = '<p>'+oLangs.get('_EMPTY_SELECTION')+'</p>';
				}
				CreateDialog("delete_dialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (o.num_selected > 0 ? false : true),
					header: oLangs.get('_AREYOUSURE'),
					body: body,
					callback: function() {
						this.destroy();
						DataTable_unsubscriberequests_table.refresh();
					}
				}).call(this, e);
			});
		});
	}
}