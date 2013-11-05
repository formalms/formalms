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

var UserManagement = {
	oLangs: new LanguageManager(),

	selectedOrgBranch: 0,
	showDescendants: false,
	showSuspended: true,
	filterText: "",
	useAdvancedFilter: false,

	dynSelection: {},
	fieldList: [],
	numVarFields: 0,

	baseUrl: "",
	templatePath: "",
	perms: {},

	init: function(o) {
		if (o.langs) this.oLangs.set(o.langs);
		if (o.baseUrl) this.baseUrl = o.baseUrl;
		if (o.selectedOrgBranch) this.selectedOrgBranch = o.selectedOrgBranch;
		if (o.showDescendants) this.showDescendants = o.showDescendants;
		if (o.showSuspended) this.showSuspended = o.showSuspended;
		if (o.filterText) this.filterText = o.filterText;
		if (o.useAdvancedFilter) this.useAdvancedFilter = o.useAdvancedFilter;
		if (o.dynSelection) this.dynSelection = o.dynSelection;
		if (o.fieldList) this.fieldList = o.fieldList;
		if (o.numVarFields) this.numVarFields = o.numVarFields;
		if (o.templatePath) this.templatePath = o.templatePath;
		if (o.perms) this.perms = o.perms;

		YAHOO.util.Event.onDOMReady(function() {

			var D = YAHOO.util.Dom, E = YAHOO.util.Event, U = UserManagement;
			var L = U.oLangs;

			//filter behaviour events
			E.addListener('filter_text', "keypress", function(e) {
				switch (E.getCharCode(e)) {
					case 13: {
						E.preventDefault(e);
						U.filterText = this.value;
						DataTable_usertable.refresh();
					} break;
				}
			});

			E.addListener("filter_set", "click", function(e) {
				E.preventDefault(e);
				U.filterText = D.get("filter_text").value;
				DataTable_usertable.refresh();
			});

			E.addListener("filter_reset", "click", function(e) {
				E.preventDefault(e);
				D.get("filter_text").value = "";
				U.filterText = "";
				DataTable_usertable.refresh();
			});

			E.addListener("flatview", "click", function(e) {
				U.showDescendants = this.checked;
				DataTable_usertable.refresh();
			});

			E.addListener("show_suspended", "click", function(e) {
				U.showSuspended = this.checked;
				DataTable_usertable.refresh();
			});

			var createUserCallback = function(o) {
				//this.destroy();
					WriteDialogMessage(this, o.message || "");

					if (o.force_page_refresh) {
						window.location.reload();
					}

					var D = YAHOO.util.Dom;
					D.get("username").value = "";
					D.get("firstname").value = "";
					D.get("lastname").value = "";
					D.get("email").value = "";
					D.get("password").value = "";
					D.get("password_confirm").value = "";

					D.get("username").focus();
					DataTable_usertable.refresh();
			}

			//create user dialog
			var create_links = D.getElementsByClassName('ico-wt-sprite subs_add', 'a', 'usertable_table_container');
			E.addListener(create_links, "click", CreateDialog("usertable_createDialog", {
				modal: true,
				close: true,
				visible: false,
				fixedcenter: false,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: "ajax.adm_server.php?r="+U.baseUrl+"/create",
				renderEvent: function() {
					E.onAvailable("create_user_tabview", function() {
						var tabView = new YAHOO.widget.TabView("create_user_tabview");
					});

					E.onAvailable("createuser_orgchart_tree", function(){
						var sel = UserManagement.selectedOrgBranch;
						YAHOO.runtimeWidgets["createuser_orgchart_tree"]({
							initialSelectedNode: sel,
							initialSelectorData: [sel]
						});
					});

					E.onAvailable("username", function() {
						D.get("username").focus();
						var el = D.get("create_user_main_container");
						//el.style.width = (el.clientWidth+1)+"px";
					});
				},
				destroyEvent: function() {
					//free memory, DOM and resources
					TreeView_createuser_orgchart_tree.destroy();
					TreeView_createuser_orgchart_tree = null;
				},
				callback: createUserCallback,
				upload: createUserCallback
			}));

			//multi delete confirm dialog
			var multiDeleteEvent = function(s, e, u) {
				var body, count_sel = DataTableSelector_usertable.num_selected;
				if (count_sel > 0) {
					body = '<form method="POST" id="usertable_multidel_dialog_form" action="'+u+'">'
						+'<p>'+L.get('_DEL')+': '+count_sel+' '+L.get('_USERS')+'</p>'
						+'<input type="hidden" name="users" value="'+DataTableSelector_usertable.toString()+'" />'
						+'</form>';
				} else {
					body = '<p>'+L.get('_EMPTY_SELECTION')+'</p>';
				}
				var oDialog = CreateDialog("usertable_multiDeleteDialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (count_sel > 0 ? false : true),
					header: L.get('_AREYOUSURE'),
					body: body,
					callback: function(o) {
						if (o.list) {
							var i;
							for (i=0; i<o.list.length; i++)
								DataTableSelector_usertable.remsel(o.list[i]);
						}
						this.destroy();
						U.updateDeletedUsersTotal(o);
						DataTable_usertable.refresh();
					}
				});
				oDialog.call(this, e);
			};

			//multi unassoc/assoc
			var multiUnassocEvent = function(s, e, u) {
				var body, count_sel = DataTableSelector_usertable.num_selected;
				if (count_sel > 0) {
					var idOrg = U.selectedOrgBranch;
					var node = TreeView_usertree._getNodeById(idOrg);
					body = '<form method="POST" id="usertable_multiunassoc_dialog_form" action="'+u+'">'
						+'<p>'+L.get('_REMOVE_FROM_NODE')+': '+count_sel+' '+L.get('_USERS')+'</p>'
						+'<p>'+L.get('_DIRECTORY_MEMBERTYPETREE')+': '+(node ? node.getLabel() : '')+'</p>'
						+'<input type="hidden" name="users" value="'+DataTableSelector_usertable.toString()+'" />'
						+'<input type="hidden" name="id_org" value="'+idOrg+'" />'
						+'</form>';
				} else {
					body = '<p>'+L.get('_EMPTY_SELECTION')+'</p>';
				}
				var oDialog = CreateDialog("usertable_multiUnassocDialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (count_sel > 0 ? false : true),
					header: L.get('_AREYOUSURE'),
					body: body,
					callback: function(o) {
						if (o.list) {
							var i;
							for (i=0; i<o.list.length; i++)
								DataTableSelector_usertable.remsel(o.list[i]);
						}
						this.destroy();
						DataTable_usertable.refresh();
					}
				});
				oDialog.call(this, e);
			};

			//multi suspend
			var multiSuspendEvent = function(s, e, u) {
				var body, count_sel = DataTableSelector_usertable.num_selected;
				if (count_sel > 0) {
					var action = L.get(u.match('action=0') ? '_SUSPEND' : '_REACTIVATE');
					body = '<form method="POST" id="usertable_multisuspend_dialog_form" action="'+u+'">'
						+'<p>'+action+': '+count_sel+' '+L.get('_USERS')+'</p>'
						+'<input type="hidden" name="users" value="'+DataTableSelector_usertable.toString()+'" />'
						+'</form>';
				} else {
					body = '<p>'+L.get('_EMPTY_SELECTION')+'</p>';
				}
				var oDialog = CreateDialog("usertable_multiSuspendDialog", {
					width: "500px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: true,
					constraintoviewport: true,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: false,
					confirmOnly: (count_sel > 0 ? false : true),
					header: L.get('_AREYOUSURE'),
					body: body,
					callback: function() {
						this.destroy();
						DataTable_usertable.refresh();
					}
				});
				oDialog.call(this, e);
			};

			//multi mod
			var multiModifyEvent = function(s, e, u) {
				var body, oDs = DataTableSelector_usertable;
				var count_sel = oDs.num_selected;
				var oConfig = {
					modal: true,
					close: true,
					visible: false,
					fixedcenter: false,
					constraintoviewport: false,
					draggable: true,
					hideaftersubmit: false
				};
				if (count_sel > 0) {
					oConfig.isDynamic = true;
					oConfig.ajaxUrl = "ajax.adm_server.php?r="+U.baseUrl+"/multimod&users_count="+count_sel;
					oConfig.confirmOnly = false;
					oConfig.renderEvent = function() {
						E.onAvailable("multimod_users", function() {
							var el = D.get("multimod_users");
							if (el) el.value = oDs.toString();
						});
					};
					oConfig.callback = function(o) {
						this.destroy();
						DataTable_usertable.refresh();
					};
				} else {
					oConfig.width = "500px";
					oConfig.isDynamic = false,
					oConfig.header = L.get('_MOD');
					oConfig.body = '<p>'+L.get('_EMPTY_SELECTION')+'</p>';
					oConfig.confirmOnly = true;
				}
				CreateDialog("usertable_multimodDialog", oConfig).call(this, e);
			};


			var csvExportEvent = function() {
				var f = D.get("csv_form");
				var i = D.get("csv_input");
				i.value = DataTableSelector_usertable.toString();
				f.submit();
			};

			E.addListener("apply_dyn_filter-button", "click", function(e) { DataTable_usertable.refresh(); });
			E.addListener("reset_dyn_filter-button", "click", function(e) { YAHOO.dynFilter.resetFilter(); DataTable_usertable.refresh(); });

			var items = [];
			
			items.push({id:"opt0", text: L.get('_EXPORT_CSV'), onclick: { fn: csvExportEvent }});
			if (U.perms.associate_user) {
				items.push({id:"opt1", text: L.get('_REMOVE_FROM_NODE'), onclick: { fn: multiUnassocEvent, obj: "ajax.adm_server.php?r="+U.baseUrl+"/multiunassoc" }});
			}
			if (U.perms.mod_user) {
				items.push({id:"opt2", text: L.get('_SUSPEND'), onclick: { fn: multiSuspendEvent, obj: "ajax.adm_server.php?r="+U.baseUrl+"/multisuspend&amp;action=0" }});
				items.push({id:"opt3", text: L.get('_REACTIVATE'), onclick: { fn: multiSuspendEvent, obj: "ajax.adm_server.php?r="+U.baseUrl+"/multisuspend&amp;action=1" }});
				items.push({id:"opt4", text: L.get('_MOD'), onclick: { fn: multiModifyEvent, obj: "ajax.adm_server.php?r="+U.baseUrl+"/modmultiuser" }});
			}
			if (U.perms.del_user) {
				items.push({id:"opt5", text: L.get('_DEL'), onclick: { fn: multiDeleteEvent, obj: "ajax.adm_server.php?r="+U.baseUrl+"/delmultiuser" }});
			}

			if (items.length > 0) {
				var oMenu = new YAHOO.widget.Menu("ma_over_container", {visible: false});
				oMenu.addItems(items);
				//oMenu.render();
				var oButtonOver = new YAHOO.widget.Button("ma_over", {
					label: L.get('_MORE_ACTIONS'),
					type: "menu",
					menu: items
				});

				var oButtonBottom = new YAHOO.widget.Button("ma_bottom", {
					label: L.get('_MORE_ACTIONS'),
					type: "menu",
					menu: items
				});
			}

		}); //end onDOMready
	},

	orgbranchFormatter: function(elLiner, oRecord, oColumn, oData) {
		var output = "";
		if (oData > 0 && UserManagement.selectedOrgBranch > 0) {
			output += '<a id="user_unassoc_'+oRecord.getData("id")+'" class="ico-sprite subs_unassoc" '
				+' title="'+UserManagement.oLangs.get('_REMOVE_FROM_NODE')+'" '
				+' href="ajax.adm_server.php?r='+UserManagement.baseUrl+'/unassoc&id_user='+oRecord.getData("id")+'&id_org='+UserManagement.selectedOrgBranch+'">'
				+'<span>'+UserManagement.oLangs.get('_REMOVE_FROM_NODE')+'</span></a>';
		} else {
			output += '&nbsp;';
		}
		elLiner.innerHTML = output;
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="usertable_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	suspendFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a id="user_suspend_'+oRecord.getData("id")+'" class="ico-sprite subs_'+(oData>0 ? 'un' : '')+'locked" '
			+' title="'+UserManagement.oLangs.get(oData>0 ? '_SUSPEND' : '_REACTIVATE')+'" '
			+' href="ajax.adm_server.php?r='+UserManagement.baseUrl+'/suspend&id='+oRecord.getData("id")+'&action='+(oData>0 ? 0 : 1)+'">'
			+'<span>'+UserManagement.oLangs.get(oData>0 ? '_SUSPEND' : '_REACTIVATE')+'</span></a>';
	},

	profileFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a id="user_profile_'+oRecord.getData("id")+'" class="ico-sprite subs_view" '
			+' title="'+UserManagement.oLangs.get('_DETAILS')+'" '
			+' href="ajax.adm_server.php?r='+UserManagement.baseUrl+'/profile_dialog&id='+oRecord.getData("id")+'">'
			+'<span>'+UserManagement.oLangs.get('_PROFILE')+'</span></a>';
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;

		oState = oState || {pagination: null, sortedBy: null};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		var i, output = "&results=" + results
			+ "&startIndex=" + startIndex
			+ "&sort=" + sort
			+ "&dir=" + dir
			+	UserManagement.selectAllAdditionalFilter();

		for (i=0; i<UserManagement.numVarFields; i++) {
			output += "&_dyn_field["+i+"]=" + YAHOO.util.Dom.get("_dyn_field_selector_"+i).value
		}

		return output;
	},

	setDropDownRefreshEvent: function() {
		YAHOO.util.Event.addListener(this, "change", function() {
			UserManagement.dynSelection[this.id] = this.selectedIndex;
			DataTable_usertable.refresh();
		});
	},

	setSortButtonRefreshEvent: function() {
		var oDt = DataTable_usertable;
		YAHOO.util.Event.addListener(this, "click", function(e) {
			YAHOO.util.Event.preventDefault(e);

			var oColumn = oDt.getColumn(this);

			//load adjusted <select> into column label
			var index = this.id.replace('_dyn_field_sort_', '');
			var selected = YAHOO.util.Dom.get('_dyn_field_selector_'+index).value;
			oColumn.label = UserManagement.getDynLabelMarkup(index, selected);

			var oSortedBy = oDt.get("sortedBy"), sDir = oDt.CLASS_ASC;
			if (oSortedBy.key == oColumn.getKey()) {
				sDir = (oSortedBy.dir == oDt.CLASS_ASC ? oDt.CLASS_DESC : oDt.CLASS_ASC);
			}
			oDt.sortColumn(oColumn, sDir);
		});
	},

	updateDeletedUsersTotal: function(o) {
		var num = o.total_deleted_users;
		if (num !== null && num !== false) {
			var el = YAHOO.util.Dom.get("show_deleted_users");
			if (el) {
				var str = el.firstChild.innerHTML;
				el.firstChild.innerHTML = str.replace(/^(.*[\(])(\d)([\)].*$)/i, "$1"+num+"$3");
			}
		}
	},

	getDynLabelMarkup: function(index, selected) {
		var x, id = '_dyn_field_selector_'+index, sort_str = UserManagement.oLangs.get('_SORT');
		var output = '<select id="'+id+'" name="_dyn_field_selector['+index+']">';
		for (x in UserManagement.fieldList) {
			output += '<option value="'+x+'"'
			+( selected == x ? ' selected="selected"' : '' )
			+'>'+UserManagement.fieldList[x]+'</option>';
		}
		output += '</select>';

		output += '<a id="_dyn_field_sort_'+index+'" href="javascript:;">';
		output += '<img src="'+UserManagement.templatePath+'images/standard/sort.png" ';
		output += 'title="'+sort_str+'" alt="'+sort_str+'" />';
		output += '</a>';

		UserManagement.dynSelection[id] = selected;
		return output;
	},

	selectAllAdditionalFilter: function() {
		return "&id_org="	+ UserManagement.selectedOrgBranch +
				"&descendants=" + (UserManagement.showDescendants ? '1' : '0') +
				"&filter_text=" + (UserManagement.useAdvancedFilter ? "" : UserManagement.filterText) +
				"&suspended=" + (UserManagement.showSuspended ? '1' : '0') +
				"&dyn_filter=" + (UserManagement.useAdvancedFilter ? encodeURI(YAHOO.dynFilter.toString()) : "");
	},

	beforeRenderEvent: function() {
		var slist = YAHOO.util.Selector.query('select[id^=_dyn_field_selector_]');
		var blist = YAHOO.util.Selector.query('a[id^=_dyn_field_sort_]');
		var i;

		for (i=0; i<slist.length; i++) {
			slist[i].disabled = true;
			YAHOO.util.Event.purgeElement(slist[i]);
		}
		for (i=0; i<blist.length; i++) {
			YAHOO.util.Event.purgeElement(blist[i]);
		}

		var elList = YAHOO.util.Selector.query('a[id^=user_suspend_]');
		YAHOO.util.Event.purgeElement(elList);

		elList = YAHOO.util.Selector.query('a[id^=user_unassoc_]');
		YAHOO.util.Event.purgeElement(elList);

		elList = YAHOO.util.Selector.query('a[id^=user_profile_]');
		YAHOO.util.Event.purgeElement(elList);
	},

	postRenderEvent: function() {
		var slist = YAHOO.util.Selector.query('select[id^=_dyn_field_selector_]');
		var blist = YAHOO.util.Selector.query('a[id^=_dyn_field_sort_]');
		var i;

		for (i=0; i<slist.length; i++) {
			slist[i].disabled = false;
			UserManagement.setDropDownRefreshEvent.call(slist[i]);
		}
		for (i=0; i<blist.length; i++) {
			UserManagement.setSortButtonRefreshEvent.call(blist[i]);
		}

		//suspend/unsuspend user
		var elList = YAHOO.util.Selector.query('a[id^=user_suspend_]');
		YAHOO.util.Event.addListener(elList, "click", CreateDialog("usertable_suspenddialog", {
			width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: false,
			header: UserManagement.oLangs.get('_AREYOUSURE'),
			body: function() {
				var oRecord = DataTable_usertable.getRecord(this);
				return '<form method="POST" id="usertable_suspend_dialog_form" action="'+this.href+'">'
					+'<div id="usertable_suspend_dialog_message"></div>'
					+'<p>'+UserManagement.oLangs.get(this.href.match('action=0') ? '_SUSPEND' : '_REACTIVATE')
					+': '+oRecord.getData("userid")+"?"+'</p>'
					+'</form>';
			},
			callback: function(o) {
				YAHOO.util.Dom.get('ui_feedback_box').innerHTML =o.message;
				this.destroy();
				DataTable_usertable.refresh();
			}
		}));

		//assoc/unassoc user to/from org. branch events
		elList = YAHOO.util.Selector.query('a[id^=user_unassoc_]');
		YAHOO.util.Event.addListener(elList, "click", CreateDialog("usertable_unassocdialog", {
			width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: false,
			header: UserManagement.oLangs.get('_AREYOUSURE'),
			body: function() {
				var idOrg = UserManagement.selectedOrgBranch;
				var node = TreeView_usertree._getNodeById(idOrg);
				var oRecord = DataTable_usertable.getRecord(this);
				return '<form method="POST" id="usertable_unassoc_dialog_form" action="'+this.href+'">'
					+'<p>'+UserManagement.oLangs.get('_REMOVE_FROM_NODE')+': '+oRecord.getData("userid")+'?</p>'
					+'<p>'+UserManagement.oLangs.get('_DIRECTORY_MEMBERTYPETREE')+': '+(node ? node.getLabel() : '')+'</p>'
					+'</form>';
			},
			callback: function() {
				this.destroy();
				DataTable_usertable.refresh();
			}
		}));

		//user's profile pop-up
		elList = YAHOO.util.Selector.query('a[id^=user_profile_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			var oDialog = CreateDialog("usertable_profiledialog", {
				width: "700px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: false,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				confirmOnly: true,
				renderEvent: function() { var tabView = new YAHOO.widget.TabView("profile_dialog_tabview");  },
				destroyEvent: function() {},
				callback: function() {
					this.destroy();
					DataTable_usertable.refresh();
				}
			});
			oDialog.call(this, e);
		});

	},

	setNumUserSelected: function(num) {
		var prefix = "num_users_selected_", D = YAHOO.util.Dom;
		D.get(prefix+"top").innerHTML = num;
		D.get(prefix+"bottom").innerHTML = num;
	},

	initEvent: function() {
		var updateSelected = function() {
			UserManagement.setNumUserSelected(this.num_selected);
		};
		var ds = DataTableSelector_usertable;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	addFolderCallback: function(o) {
		if (o.node) {
			var parent = TreeView_usertree._getNodeById(o.id_parent);
			TreeView_usertree.appendNode(parent, o.node, false);
		}
		this.destroy();
	},

	toggleFolderCodes: function(show) {
		var i, codes = YAHOO.util.Selector.query('span[id^=orgchart_code_]');
		for (i=0; i<codes.length; i++) {
			codes[i].style.display = show ? 'inline' : 'none';
		}
	}
};