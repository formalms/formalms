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

//--- USER SELECTOR ------------------------------------------------------------

var UserSelector = function (id, oConfig) {
	this.init(id, oConfig);
}

UserSelector.prototype = {

	id: '',
	oLangs: null,
	numFields: 3,
	fieldList: [],
	relationList: [],
	dynSelection: [],
	useSuspended: false,

	oTable: null,
	imgPath: '',

	filterText: "",
	showSuspended: false,
	useAdvancedFilter: false,

	setTable: function(oTable) {
		this.oTable = oTable;
	},
	//init function
	init: function(id, oConfig) {
		if (!id) return;

		this.id = id;
		this.oLangs = new LanguageManager(oConfig.langs || {});
		if (oConfig.numFields || oConfig.numFields == 0) this.numFields = oConfig.numFields;
		if (oConfig.fieldList) this.fieldList = oConfig.fieldList;

		if (oConfig.filterText) this.filterText = oConfig.filterText;
		if (oConfig.useSuspended) this.useSuspended = oConfig.useSuspended;
		if (oConfig.showSuspended) this.showSuspended = oConfig.showSuspended;
		if (oConfig.useAdvancedFilter) this.useAdvancedFilter = oConfig.useAdvancedFilter;

		this.imgPath = oConfig.imgPath;
		//operative functions

		var oScope = this;

		this.selectAllAdditionalFilter = function() {
			return "&filter_text=" + (oScope.useAdvancedFilter ? "" : oScope.filterText) +
				"&suspended=" + (oScope.showSuspended ? '1' : '0') +
				"&dyn_filter=" + (oScope.useAdvancedFilter ? encodeURI(YAHOO.dynFilter.toString()) : "");
		};

		this.requestBuilder = function(oState, oSelf) {
			var i, sort, dir, startIndex, results;
			oState = oState || {pagination: null, sortedBy: null};
			startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
			results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
			sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
			dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
			var request =  "&results=" 	+ results +
					"&startIndex=" 	+ startIndex +
					"&sort="		+ sort +
					"&dir="			+ dir +
					oScope.selectAllAdditionalFilter(); //recycle that function
			for (i=0; i<oScope.numFields; i++) {
				request += '&_dyn_field['+i+']=' + YAHOO.util.Dom.get('user_dyn_field_selector_'+oScope.id+'_'+i).value;
			}
			return request;
		};

		this.getDynLabelMarkup = function(index, selected) {
			var x, id= 'user_dyn_field_selector_'+oScope.id+'_'+index;
			var output = '<select id="'+id+'" name="_dyn_field_selector['+index+']">';
			for (x in oScope.fieldList) {
				output += '<option value="'+x+'"'
				+( selected == x ? ' selected="selected"' : '' )
				+'>'+oScope.fieldList[x]+'</option>';
			}
			output += '</select>';

			output += '<a id="user_dyn_field_sort_'+oScope.id+'_'+index+'" href="javascript:;">';
			output += '<img src="'+oScope.imgPath+'images/standard/sort.png" ';
			output += 'title="'+oScope.oLangs.get('_SORT')+'" alt="'+oScope.oLangs.get('_SORT')+'" />';
			output += '</a>';

			oScope.dynSelection[id] = selected;
			return output;
		};

		this.setDropDownRefreshEvent = function() {
			var oDt = oScope.oTable;
			YAHOO.util.Event.addListener(this, "change", function() {
				oScope.dynSelection[this.id] = this.selectedIndex ;
				oDt.refresh();
			});
		};

		this.setSortButtonRefreshEvent = function() {
			var oDt = oScope.oTable;
			YAHOO.util.Event.addListener(this, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);

				var oColumn = oDt.getColumn(this);

				//load adjusted <select> into column label
				var index = this.id.replace('user_dyn_field_sort_'+oScope.id+'_', '');
				var selected = YAHOO.util.Dom.get('user_dyn_field_selector_'+oScope.id+'_'+index).value;
				oColumn.label = oScope.getDynLabelMarkup(index, selected);

				var oSortedBy = oDt.get("sortedBy"), sDir = oDt.CLASS_ASC;;
				if (oSortedBy.key == oColumn.getKey()) {
					sDir = (oSortedBy.dir == oDt.CLASS_ASC ? oDt.CLASS_DESC : oDt.CLASS_ASC);
				}
				oDt.sortColumn(oColumn, sDir);
			});
		};

		this.suspendFormatter = function(elLiner, oRecord, oColumn, oData) {
			elLiner.innerHTML = '<span class="ico-sprite subs_'+(oData>0 ? 'un' : '')+'locked">'
				+'<span>'+(oData>0 ? oScope.oLangs.get('_ACTIVE') : oScope.oLangs.get('_SUSPENDED'))
				+'</span></span>';
		};

		this.setNumUserSelected = function(num) {
			var prefix = "num_users_selected_", D = YAHOO.util.Dom;
			D.get(prefix+"top_"+oScope.id).innerHTML = num;
			D.get(prefix+"bottom_"+oScope.id).innerHTML = num;
		};

		this.editorSaveEvent = function(oArgs) {
			var oEditor = oArgs.editor;
			var new_value = oArgs.newData;
			var old_value = oArgs.oldData;
			var id_user = oEditor.getRecord().getData("id");
			var col = oEditor.getColumn().getKey();
			var callback = {
				success: function(o) {},
				failure: function(o) {}
			};

			var checkbox = window.document.getElementById('user_selector_table_main_selector_sel_'+id_user);
			if ((!checkbox.checked && new_value != 'NULL') || (checkbox.checked && new_value == 'NULL')) {
				window.document.getElementById('user_selector_table_main_selector_sel_' + id_user).click();
			}
			var form = YAHOO.util.Selector.query('form[id^=main_selector_form]')[0];

			var okSelector = document.createElement("input");
			okSelector.setAttribute("type", "hidden");
			okSelector.setAttribute("name", "okselector");
			okSelector.setAttribute("value", "1");
			form.appendChild(okSelector);

			YAHOO.util.Connect.setForm(form);

			YAHOO.util.Connect.asyncRequest("POST", form.action+'&relation='+new_value+'&userselector_input[main_selector]='+id_user, callback);
		};

		//table events
		this.initEvent = function() {
			var updateSelected = function() {
				oScope.setNumUserSelected(this.num_selected);
			};
			var ds = oScope.oTable.innerSelector;
			ds.subscribe("add", updateSelected);
			ds.subscribe("remove", updateSelected);
			ds.subscribe("reset", updateSelected);
			ds.subscribe("dropdownChangeEvent", function(e) {
				YAHOO.util.Event.preventDefault(e);
			});
		};

		this.beforeRenderEvent = function() {
			var slist = YAHOO.util.Selector.query('select[id^=user_dyn_field_selector_'+oScope.id+'_]');
			var blist = YAHOO.util.Selector.query('a[id^=user_dyn_field_sort_'+oScope.id+'_]');

			for (var i=0; i<slist.length; i++) {
				slist[i].disabled = true;
				YAHOO.util.Event.purgeElement(slist[i]);
			}
			for (var i=0; i<blist.length; i++) {
				YAHOO.util.Event.purgeElement(blist[i]);
			}
		};

		this.postRenderEvent = function() {
			var slist = YAHOO.util.Selector.query('select[id^=user_dyn_field_selector_'+oScope.id+'_]');
			var blist = YAHOO.util.Selector.query('a[id^=user_dyn_field_sort_'+oScope.id+'_]');

			for (var i=0; i<slist.length; i++) {
				slist[i].disabled = false;
				oScope.setDropDownRefreshEvent.call(slist[i]);
			}
			for (var i=0; i<blist.length; i++) {
				oScope.setSortButtonRefreshEvent.call(blist[i]);
			}
		};

		//set some events
		YAHOO.util.Event.onDOMReady(function() {

			if (oConfig.useFormInput) {
				/*
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id+"_user");
				if (input) {
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						input.value = oScope.oTable.innerSelector.toString();
					});
				}
				*/
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id);
				if (input) {
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						var str = oScope.oTable.innerSelector.toString();
						if (str) input.value += (input.value != "" ? "," : "")+str;
					});
				}
			}

			if (oScope.useSuspended) {
				var el = YAHOO.util.Dom.get("user_show_suspended_"+oScope.id);
				el.checked = oScope.showSuspended;
				YAHOO.util.Event.addListener(el, "click", function(e) {
					oScope.showSuspended = this.checked;
					oScope.oTable.refresh();
				});
			}

			YAHOO.util.Event.addListener("userselector_filter_selector_"+oScope.id, "click", function(e) {
				var smp_menu = YAHOO.util.Dom.get("userselector_simple_filter_options_"+oScope.id);
				var adv_menu = YAHOO.util.Dom.get("userselector_advanced_filter_options_"+oScope.id);
				if (smp_menu.style.display != 'inline') {
					this.innerHTML = oScope.oLangs.get('_ADVANCED_SEARCH');
					smp_menu.style.display = 'inline';
					adv_menu.style.display = 'none';
					oScope.useAdvancedFilter = false;
				} else {
					this.innerHTML = oScope.oLangs.get('_BASIC_SEARCH');
					smp_menu.style.display = 'none';
					adv_menu.style.display = 'block';
					oScope.useAdvancedFilter = true;
				}
			});

			var text_id = 'user_filter_text_'+oScope.id;
			YAHOO.util.Event.addListener(text_id, "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.stopEvent(e);
						oScope.filterText = this.value;
						oScope.oTable.refresh();
					}break;
				}
			});

			YAHOO.util.Event.addListener("user_filter_set_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				oScope.filterText = YAHOO.util.Dom.get("user_filter_text_"+oScope.id).value;
				oScope.oTable.refresh();
			});

			YAHOO.util.Event.addListener("user_filter_reset_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				YAHOO.util.Dom.get("user_filter_text_"+oScope.id).value = "";
				oScope.filterText = "";
				oScope.oTable.refresh();
			});

			YAHOO.util.Event.addListener("user_apply_dyn_filter_"+oScope.id+"-button", "click", function(e) {oScope.oTable.refresh();});
			YAHOO.util.Event.addListener("user_reset_dyn_filter_"+oScope.id+"-button", "click", function(e) {YAHOO.dynFilter.resetFilter();oScope.oTable.refresh();});
            //*** forma 2.0 fix: the previous does not work. When yui is deleted, life will be better 
            YAHOO.util.Event.addListener("user_apply_dyn_filter_main_selector", "click", function(e) {oScope.oTable.refresh();});
            YAHOO.util.Event.addListener("user_reset_dyn_filter_main_selector", "click", function(e) {oScope.oTable.refresh();});

			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/usermanagement/users_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "userid_highlight", "idst", "name"]
			};
			
			var oAC = new YAHOO.widget.AutoComplete(text_id, text_id+"_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return oResultData.userid_highlight;
			};
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

		});

	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="'+this.getTableEl().parentNode.id+'_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	getSelection: function() {
		return this.oTable.innerSelector.toString();
	},

	toString: function() {return "Users Selector ["+this.id+"]";}
}



//--- GROUP SELECTOR -----------------------------------------------------------

var GroupSelector = function(id, oConfig) {
	this.init(id, oConfig);
}

GroupSelector.prototype = {

	id: '',
	oLangs: null,
	oTable: null,

	filterText: "",

	init: function(id, oConfig) {
		if (!id) return false;

		this.id = id;
		this.oLangs = new LanguageManager(oConfig.langs || {});
		if (oConfig.filterText) this.filterText = oConfig.filterText;

		var oScope = this;

		this.requestBuilder = function (oState, oSelf) {
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
					"&filter=" + oScope.filterText;
		};

		this.setNumGroupSelected = function(num) {
			var prefix = "num_groups_selected_", D = YAHOO.util.Dom;
			D.get(prefix+"top_"+oScope.id).innerHTML = num;
			D.get(prefix+"bottom_"+oScope.id).innerHTML = num;
		};

		this.initEvent = function() {
			var updateSelected = function() {
				oScope.setNumGroupSelected(this.num_selected);
			};
			var ds = oScope.oTable.innerSelector;
			ds.subscribe("add", updateSelected);
			ds.subscribe("remove", updateSelected);
			ds.subscribe("reset", updateSelected);
		};

		this.selectAllAdditionalFilter = function() {
			return "&filter_text=" + oScope.filterText;
		};

		YAHOO.util.Event.onDOMReady(function() {

			if (oConfig.useFormInput) {
				/*
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id+"_group");
				if (input) {
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						input.value = oScope.oTable.innerSelector.toString();
					});
				}
				*/
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id);
				if (input) {					
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						var str = oScope.oTable.innerSelector.toString();
						if (str) input.value += (input.value != "" ? "," : "")+str;
					});
				}
			}

			var text_id = 'group_filter_text_'+oScope.id;
			YAHOO.util.Event.addListener(text_id, "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.stopEvent(e);
						oScope.filterText = this.value;
						oScope.oTable.refresh();
					}break;
				}
			});

			YAHOO.util.Event.addListener("group_filter_set_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				oScope.filterText = YAHOO.util.Dom.get("group_filter_text_"+id).value;
				oScope.oTable.refresh();
			});

			YAHOO.util.Event.addListener("group_filter_reset_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				YAHOO.util.Dom.get("group_filter_text_"+oScope.id).value = "";
				oScope.filterText = "";
				oScope.oTable.refresh();
			});

			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/groupmanagement/groups_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "groups",
				fields: ["groupid", "groupid_highlight", "idst"]
			};

			var oAC = new YAHOO.widget.AutoComplete(text_id, text_id+"_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return oResultData.groupid_highlight;
			};
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

		});

	},

	setTable: function(oTable) {
		this.oTable = oTable;
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="'+this.getTableEl().parentNode.id+'_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	getSelection: function() {
		return this.oTable.innerSelector.toString();
	},
	
	toString: function() {return "Groups Selector ["+this.id+"]";}
}


//--- ORGCHART SELECTOR --------------------------------------------------------

/*
var OrgchartSelectorFolderTree = function(id, oConfig) {
	OrgchartSelectorFolderTree.superclass.constructor.call(this, id, oConfig);
	this.setNodeClickEvent(this.clickNode, this);
};

YAHOO.lang.extend(OrgchartSelectorFolderTree, FolderTree, {

	clickNode: function(oNode) {
		var id = this._getNodeId(oNode);
		OrgchartSelector.selectedCategory = id;
	},

	toString: function() { return "Orgchart Selector ["+this.id+"]"; }
});
*/

//--- FNCROLE SELECTOR ---------------------------------------------------------

var FncroleSelector = function(id, oConfig) {
	this.init(id, oConfig);
}

FncroleSelector.prototype = {

	id: '',
	oLangs: null,
	oTable: null,

	filterText: "",

	init: function(id, oConfig) {
		if (!id) return false;

		this.id = id;
		this.oLangs = new LanguageManager(oConfig.langs || {});
		if (oConfig.filterText) this.filterText = oConfig.filterText;

		var oScope = this;

		this.requestBuilder = function (oState, oSelf) {
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
					"&filter=" + oScope.filterText;
		};

		this.setNumGroupSelected = function(num) {
			var prefix = "num_fncroles_selected_", D = YAHOO.util.Dom;
			D.get(prefix+"top_"+oScope.id).innerHTML = num;
			D.get(prefix+"bottom_"+oScope.id).innerHTML = num;
		};

		this.initEvent = function() {
			var updateSelected = function() {
				oScope.setNumGroupSelected(this.num_selected);
			};
			var ds = oScope.oTable.innerSelector;
			ds.subscribe("add", updateSelected);
			ds.subscribe("remove", updateSelected);
			ds.subscribe("reset", updateSelected);
		};

		this.selectAllAdditionalFilter = function() {
			return "&filter_text=" + oScope.filterText;
		};

		YAHOO.util.Event.onDOMReady(function() {

			if (oConfig.useFormInput) {
				/*
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id+"_fncrole");
				if (input) {
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						input.value = oScope.oTable.innerSelector.toString();
					});
				}
				*/
				var input = YAHOO.util.Dom.get("userselector_input_"+oScope.id);
				if (input) {
					YAHOO.util.Event.addListener(input.form, "submit", function(e) {
						var str = oScope.oTable.innerSelector.toString();
						if (str) input.value += (input.value != "" ? "," : "")+str;
					});
				}
			}

			var text_id = 'fncrole_filter_text_'+oScope.id;
			YAHOO.util.Event.addListener(text_id, "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.stopEvent(e);
						oScope.filterText = this.value;
						oScope.oTable.refresh();
					}break;
				}
			});

			YAHOO.util.Event.addListener("fncrole_filter_set_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				oScope.filterText = YAHOO.util.Dom.get("fncrole_filter_text_"+id).value;
				oScope.oTable.refresh();
			});

			YAHOO.util.Event.addListener("fncrole_filter_reset_"+oScope.id, "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				YAHOO.util.Dom.get("fncrole_filter_text_"+oScope.id).value = "";
				oScope.filterText = "";
				oScope.oTable.refresh();
			});

			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/functionalroles/functionalroles_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "fncroles",
				fields: ["name", "name_highlight", "id_fncrole"]
			};

			var oAC = new YAHOO.widget.AutoComplete(text_id, text_id+"_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return oResultData.name_highlight;
			};
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

		});

	},

	setTable: function(oTable) {
		this.oTable = oTable;
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="'+this.getTableEl().parentNode.id+'_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	getSelection: function() {
		return this.oTable.innerSelector.toString();
	},
	
	toString: function() {return "Functional Roles Selector ["+this.id+"]";}
}