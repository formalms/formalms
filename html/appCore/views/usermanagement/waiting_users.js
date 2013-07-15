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

var WaitingManagement = {
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
				"&filter=" + WaitingManagement.filterText;
	},

	initEvent: function() {
		var updateSelected = function() {
			var num = this.num_selected;
			var prefix = "num_users_selected_", D = YAHOO.util.Dom;
			D.get(prefix+"top").innerHTML = num;
			D.get(prefix+"bottom").innerHTML = num;
		};
		var ds = DataTableSelector_waitingtable;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	beforeRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=user_details_]');
		YAHOO.util.Event.purgeElement(elList);

		var elList = YAHOO.util.Selector.query('a[id^=user_confirm_]');
		YAHOO.util.Event.purgeElement(elList);
	},

	postRenderEvent: function() {
		elList = YAHOO.util.Selector.query('a[id^=user_details_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			var oDialog = CreateDialog("waitingtable_details_dialog", {
				width: "700px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: true,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				confirmOnly: true
			});
			oDialog.call(this, e);
		});

		var elList = YAHOO.util.Selector.query('a[id^=user_confirm_]');
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
			header: WaitingManagement.oLangs.get('_AREYOUSURE'),
			body: function() {
				var oRecord = DataTable_waitingtable.getRecord(this);
				return '<form method="POST" id="waitingtable_confirm_dialog_form" action="'+this.href+'">'
					+'<p>'+WaitingManagement.oLangs.get('_CONFIRM')+': <b>'+oRecord.getData("userid")+'</b></p>'
					+'</form>';
			},
			callback: function(o) {
				//if (o.message) YAHOO.util.Dom.get('ui_feedback_box').innerHTML = o.message;
				this.destroy();
				DataTable_waitingtable.refresh();
			}
		}));
	},

	detailsFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a id="user_details_'+oRecord.getData("id")+'" class="ico-sprite subs_view" '
				+' href="ajax.adm_server.php?r=' + WaitingManagement.link + '/waiting_user_details&id_user='+oRecord.getData("id")+'" '
				+' title="'+WaitingManagement.oLangs.get('_DETAILS')+': '+oRecord.getData("userid")+'">'
				+'<span>'+WaitingManagement.oLangs.get('_DETAILS')+': '+oRecord.getData("userid")+'</span></a>';
	},

	confirmFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a id="user_confirm_'+oRecord.getData("id")+'" class="ico-sprite subs_actv" '
				+' href="ajax.adm_server.php?r=' + WaitingManagement.link + '/confirm_waiting&id_user='+oRecord.getData("id")+'" '
				+' title="'+WaitingManagement.oLangs.get('_CONFIRM')+': '+oRecord.getData("userid")+'">'
				+'<span>'+WaitingManagement.oLangs.get('_CONFIRM')+': '+oRecord.getData("userid")+'</span></a>';
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="waitingtable_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	selectAllAdditionalFilter: function() {
		return "&filter=" + WaitingManagement.filterText;
	},

	init: function(oConfig) {
		this.filterText = oConfig.filterText || "";
		this.link = oConfig.link || "adm/usermanagement";
		this.oLangs.set(oConfig.langs || {});
		var oLangs = this.oLangs;

		YAHOO.util.Event.onDOMReady(function(e) {
			YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.preventDefault(e);
						WaitingManagement.filterText = this.value;
						DataTable_waitingtable.refresh();
					} break;
				}
			});

			YAHOO.util.Event.addListener("filter_set", "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				WaitingManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
				DataTable_waitingtable.refresh();
			});

			YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				YAHOO.util.Dom.get("filter_text").value = "";
				WaitingManagement.filterText = "";
				DataTable_waitingtable.refresh();
			});

			var elListConfirm = ["confirm_multi_over", "confirm_multi_bottom"];
			var elListDelete = ["delete_multi_bottom", "delete_multi_over"];

			YAHOO.util.Event.addListener(elListConfirm, "click", function(e) {
				var body, o = DataTableSelector_waitingtable;
				if (o.num_selected > 0) {
					body = '<form method="POST" id="confirm_dialog_form" action="'+this.href+'">'
						+'<p>'+oLangs.get('_CONFIRM')+': '+o.num_selected+' '+oLangs.get('_USERS')+'</p>'
						+'<input type="hidden" value="'+o.toString()+'" name="users" />'
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
						DataTable_waitingtable.refresh();
					}
				}).call(this, e);
			});

			YAHOO.util.Event.addListener(elListDelete, "click", function(e) {
				var body, o = DataTableSelector_waitingtable;
				if (o.num_selected > 0) {
					body = '<form method="POST" id="delete_dialog_form" action="'+this.href+'">'
						+'<p>'+oLangs.get('_DEL')+': '+o.num_selected+' '+oLangs.get('_USERS')+'</p>'
						+'<input type="hidden" value="'+o.toString()+'" name="users" />'
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
						DataTable_waitingtable.refresh();
					}
				}).call(this, e);
			});
		});
	}
}