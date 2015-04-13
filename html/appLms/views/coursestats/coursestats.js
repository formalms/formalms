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

var CourseStats = {
	oLanguage: new LanguageManager(),
	footerData: false,
	statusEditor: null,

	idCourse: 0,
	filterText: "",
	filterSelection: 0,
	filterOrgChart: false,
	filterGroups: false,
	filterDescendants: false,
	countLOs: 0,

	init: function(oConfig) {
		if (!oConfig) oConfig = {};

		if (oConfig.idCourse) this.idCourse = oConfig.idCourse;
		if (oConfig.langs) this.oLanguage.set(oConfig.langs);
		if (oConfig.countLOs) this.countLOs = oConfig.countLOs;
		if (oConfig.filterText) this.filterText = oConfig.filterText;
		if (oConfig.filterSelection) this.filterSelection = oConfig.filterSelection;
		if (oConfig.filterOrgChart) this.filterOrgChart = oConfig.filterOrgChart;
		if (oConfig.filterGroups) this.filterGroups = oConfig.filterGroups;
		if (oConfig.filterDescendants) this.filterDescendants = oConfig.filterDescendants ? true : false;
		if (oConfig.footerData) this.footerData = oConfig.footerData;

		this.statusEditor = new YAHOO.widget.DropdownCellEditor({
			asyncSubmitter: CourseStats.asyncSubmitter,
			dropdownOptions: oConfig.statusList
		});

		YAHOO.util.Event.onDOMReady(function() {

			var E = YAHOO.util.Event, D = YAHOO.util.Dom, C = CourseStats;

			E.addListener('filter_text', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.preventDefault(e);
						C.filterText = this.value;
						DataTable_coursestats_table.refresh();
					} break;
				}
			});

			E.addListener("filter_set", "click", function(e) {
				E.preventDefault(e);
				C.filterText = D.get("filter_text").value;
				C.filterSelection = D.get("filter_selection").value;
				DataTable_coursestats_table.refresh();
			});

			E.addListener("filter_reset", "click", function(e) {
				E.preventDefault(e);
				D.get("filter_text").value = "";
				D.get("filter_selection").selectedIndex = 0;
				C.filterText = "";
				C.filterSelection = 0;
				DataTable_coursestats_table.refresh();
			});

			E.addListener("advanced_search", "click", function(e){
				var el = D.get("advanced_search_options");
				if (el.style.display != 'block') {
					el.style.display = 'block'
				} else {
					el.style.display = 'none'
				}
			});

			E.addListener("set_advanced_filter-button", "click", function(e) {
				C.filterSelection = D.get("filter_selection").value;
				C.filterOrgChart = D.get("filter_orgchart").value;
				C.filterGroups = D.get("filter_groups").value;
				C.filterDescendants = D.get("filter_descendants").checked;
				DataTable_coursestats_table.refresh();
			});

			E.addListener("reset_advanced_filter-button", "click", function(e) {
				D.get("filter_selection").selectedIndex = 0;
				D.get("filter_orgchart").value = 0;
				D.get("filter_groups").value = 0;
				D.get("filter_descendants").checked = false;

				C.filterSelection = 0;
				C.filterOrgChart = 0;
				C.filterGroups = 0;
				C.filterDescendants = false;
				DataTable_coursestats_table.refresh();
			});

		});
	},

	initEvent: function() { //this == DataTable_coursestats_table
		var C = CourseStats;
		if (C.countLOs > 0) {
			var i, id, td, tfoot = document.createElement("TFOOT");
			var tr1 = tfoot.appendChild(document.createElement('TR'));
			var tr2 = tfoot.appendChild(document.createElement('TR'));

			td = document.createElement('TD');
			td.id = 'footer_title_0';
			td.colSpan = 4;
			td.innerHTML = '<div class="yui-dt-liner"><b>'+C.oLanguage.get('_COMPLETED')+'</b></div>';
			tr1.appendChild(td);

			td = document.createElement('TD');
			td.id = 'footer_title_1';
			td.colSpan = 4;
			td.innerHTML = '<div class="yui-dt-liner"><b>'+C.oLanguage.get('_PERCENTAGE')+'</b></div>';
			tr2.appendChild(td);

			for (i=0; i<C.footerData.length; i++) {
				td = document.createElement('TD');
				td.id = 'lo_0_'+i;
				td.innerHTML = '<div class="yui-dt-liner">'+C.footerData[i].total+'</div>';
				tr1.appendChild(td);

				td = document.createElement('TD');
				td.id = 'lo_1_'+i;
				td.innerHTML = '<div class="yui-dt-liner">'+C.footerData[i].percent+'</div>';
				tr2.appendChild(td);
			}

			td = document.createElement('TD');
			td.id = 'footer_end_0';
			tr1.appendChild(td);

			td = document.createElement('TD');
			td.id = 'footer_end_1';
			tr2.appendChild(td);

			this.getTableEl().appendChild(tfoot);
		}

		this.doBeforeShowCellEditor = function(oEditor) {
			var key = oEditor.getColumn().getKey();
			switch (key) {
				case "status":  oEditor.value = oEditor.getRecord().getData("status_id"); break;
			}
			return true;
		};
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		return  "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir +
				"&filter_text=" + CourseStats.filterText +
				"&filter_selection=" + CourseStats.filterSelection +
				"&filter_orgchart=" + CourseStats.filterOrgChart +
				"&filter_group=" + CourseStats.filterGroups +
				"&filter_descendants=" + CourseStats.filterDescendants;
	},


	fullnameFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oRecord.getData("lastname") + " " + oRecord.getData("firstname");
	},

	useridFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=coursestats/show_user&amp;id_user='+oRecord.getData("id");
		elLiner.innerHTML = '<a href="'+url+'" title="">'+oData+'</a>';
	},

	completedFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = oData+' / '+CourseStats.countLOs;
	},

	LOFormatter: function(elLiner, oRecord, oColumn, oData) {
		var content;
		if (!oData) {
			content = '<i>'+CourseStats.oLanguage.get('_LO_NOT_STARTED')+'</i>';
		} else {
			var id_lo = oColumn.getKey().replace('lo_', ''); //extract LO id by column key
			var url = 'index.php?r=coursestats/show_user_object&amp;id_user='+oRecord.getData("id")+'&amp;id_lo='+id_lo;
			content = '<a href="'+url+'" title="">'+oData+'</a>';
		}
		elLiner.innerHTML = content;
	},

	asyncSubmitter: function(callback, newData) {
		var new_value, old_value;
		var col = this.getColumn().key;
		var id_user = this.getRecord().getData("id");
		new_value = newData
		old_value = this.value;

		var ajaxCallback = {
			success: function(o) {
				var r = YAHOO.lang.JSON.parse(o.responseText);
				if (r.success) {
					callback(true, stripSlashes(r.new_value));
				} else {
					callback(/*true, stripSlashes(r.old_value)*/false);
				}
			},
			failure: {}
		}

		var postdata = "&id_user=" + id_user
			+ "&id_course=" + CourseStats.idCourse
			+ "&col=" + col
			+ "&new_value=" + new_value
			+ "&old_value=" + old_value;

		var url = "ajax.server.php?r=coursestats/inline_editor";
		YAHOO.util.Connect.asyncRequest("POST", url, ajaxCallback, postdata);
	}
}