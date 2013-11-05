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

var GroupManagement = {

	oLangs: new LanguageManager(),

	filterText: "",

	init: function(oConfig) {
		var add_links = ["add_group_link_1", "add_group_link_2"];
		YAHOO.util.Event.addListener(add_links, "click", function(e) {
			var oDialog = CreateDialog("addDialog", {
				//width: "500px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: true,
				constraintoviewport: true,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				callback: function() {
					this.destroy();
					DataTable_grouptable.refresh();
				}
			});
			oDialog.call(this, e);
		});

		YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
			switch (YAHOO.util.Event.getCharCode(e)) {
				case 13: {
					YAHOO.util.Event.preventDefault(e);
					GroupManagement.filterText = this.value;
					DataTable_grouptable.refresh();
				} break;
			}
		});

		YAHOO.util.Event.addListener("filter_set", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			GroupManagement.filterText = YAHOO.util.Dom.get("filter_text").value;
			DataTable_grouptable.refresh();
		});

		YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get("filter_text").value = "";
			GroupManagement.filterText = "";
			DataTable_grouptable.refresh();
		});
	},


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
				"&filter=" + GroupManagement.filterText;
	},


	assignFormatter: function(elLiner, oRecord, oColumn, oData) {
		var uc = oRecord.getData("usercount");
		var mc = oRecord.getData("membercount");
		elLiner.innerHTML = '<a class="nounder" id="group_assign_grouptable_'+oRecord.getData("id")+'" '
			+' href="index.php?r=adm/groupmanagement/show_users&id='+oRecord.getData("id")+'">'+(mc>uc ? "~" : "")+uc
			+'&nbsp;<span class="ico-sprite subs_'+(oRecord.getData("usercount")>0?'users':'notice')+'"><span>'
			+GroupManagement.oLangs.get('_ASSIGN_USERS')+'</span></span></a>'
	}
};
