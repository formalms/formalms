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

var Subscription = {

	idCourse: 0,
	idEdition: 0,
	idDate: 0,

	baseLink: "",

	filterText: "",
	filterOrgChart: 0,
	filterDescendants: false,
	filterDateValid: "",
	filterShow: 0,

	statusList: null,
	levelList: null,

	overbookingStatus: null,

	editor: null,
	oLangs: new LanguageManager(),

	init: function(idCourse, idEdition, idDate, oConfig) {
		this.idCourse = idCourse;
		if (idEdition) this.idEdition = idEdition;
		if (idDate) this.idDate = idDate;

		if (oConfig.baseLink) this.baseLink = oConfig.baseLink+"";

		this.levelList = oConfig.levelList || [];
		this.statusList = oConfig.statusList || [];
		if (oConfig.langs) this.oLangs.set(oConfig.langs);
		if (oConfig.filterText) S.filterText = oConfig.filterText;
		if (oConfig.filterOrgchart) S.filterOrgChart = oConfig.filterOrgchart;
		if (oConfig.filterDescendants) S.filterDescendants = oConfig.filterDescendants;
		if (oConfig.filterDateValid) S.filterDateValid = oConfig.filterDateValid;
		if (oConfig.filterShow) S.filterShow = oConfig.filterShow;

		if (oConfig.overbookingStatus) this.overbookingStatus = oConfig.overbookingStatus;
		if (oConfig.editor) this.editor = oConfig.editor;

    if (oConfig.dynSelection) this.dynSelection = oConfig.dynSelection;
		if (oConfig.fieldList) this.fieldList = oConfig.fieldList;
		if (oConfig.numVarFields) this.numVarFields = oConfig.numVarFields;
		if (oConfig.templatePath) this.templatePath = oConfig.templatePath;

		YAHOO.util.Event.onDOMReady(function(e) {
			var E = YAHOO.util.Event, D = YAHOO.util.Dom, S = Subscription;
			var oDt = DataTable_subscribed_table, oDtS = DataTableSelector_subscribed_table;

			E.addListener('filter_text', "keypress", function(e) {
				switch (E.getCharCode(e)) {
					case 13: {
						E.preventDefault(e);
						S.filterText = this.value;
						oDt.refresh();
					} break;
				}
			});

			E.addListener("filter_set", "click", function(e) {
				E.preventDefault(e);
				S.filterText = D.get("filter_text").value;
				oDt.refresh();
			});

			E.addListener("filter_reset", "click", function(e) {
				E.preventDefault(e);
				D.get("filter_text").value = "";
				S.filterText = "";
				oDt.refresh();
			});

			E.addListener("advanced_search", "click", function(e){
				var el = D.get("advanced_search_options");
				if (el.style.display != 'block') {
					el.style.display = 'block'
				} else {
					el.style.display = 'none'
				}
			});

			E.addListener("set_advanced_filter", "click", function(e) {
				S.filterOrgChart = D.get("filter_orgchart").value;
				S.filterDescendants = D.get("filter_descendants").checked;
				S.filterDateValid = D.get("filter_date_valid").value;
				S.filterShow = D.get("filter_show").value;
				oDt.refresh();
			});

			E.addListener("reset_advanced_filter", "click", function(e) {
				D.get("filter_orgchart").value = 0;
				D.get("filter_descendants").checked = false;
				D.get("filter_date_valid").value = "";
				D.get("filter_show").selectedIndex = 0;

				S.filterOrgChart = 0;
				S.filterDescendants = false;
				S.filterDateValid = 0;
				S.filterShow = 0;
				oDt.refresh();
			});


			var copy_links = D.getElementsByClassName('ico-wt-sprite subs_copy');
			E.addListener(copy_links, "click", function(e) {
				var count_sel = oDtS.num_selected;
				this.href = this.href + '&users=' + oDtS.toString();
			});
			
			var move_links = D.getElementsByClassName('ico-wt-sprite subs_move');
			E.addListener(move_links, "click", function(e) {
				var count_sel = oDtS.num_selected;
				this.href = this.href + '&users=' + oDtS.toString();
			});	
			
			//multi mod
			var multimod_links = D.getElementsByClassName('ico-wt-sprite subs_mod');
			E.addListener(multimod_links, "click", function(e) {
				var count_sel = oDtS.num_selected;
				CreateDialog("subscribe_table_multimodDialog", {
					//width: "700px",
					modal: true,
					close: true,
					visible: false,
					fixedcenter: false,
					constraintoviewport: false,
					draggable: true,
					hideaftersubmit: false,
					isDynamic: true, //count_sel > 0 ...
					ajaxUrl: this.href + "&count_sel=" + count_sel,
					confirmOnly: count_sel > 0 ? false : true,
					renderEvent: function() {
						E.onAvailable("mod_dialog_users", function() {
							this.value = oDtS.toString();
						});
						E.onAvailable("multimod_date_begin_set", function() {
							E.addListener(this, "click", function() {
								if (this.checked) {
									var el = D.get("multimod_date_begin_reset");
									if (el) el.checked = false;
								}
							});
						});
						E.onAvailable("multimod_date_expire_set", function() {
							E.addListener(this, "click", function() {
								if (this.checked) {
									var el = D.get("multimod_date_expire_reset");
									if (el) el.checked = false;
								}
							});
						});
						E.onAvailable("multimod_date_begin_reset", function() {
							E.addListener(this, "click", function() {
								if (this.checked) {
									var el = D.get("multimod_date_begin_set");
									if (el) el.checked = false;
								}
							});
						});
						E.onAvailable("multimod_date_expire_reset", function() {
							E.addListener(this, "click", function() {
								if (this.checked) {
									var el = D.get("multimod_date_expire_set");
									if (el) el.checked = false;
								}
							});
						});
					},
					destroyEvent: function() {
						//purge events...
					},
					callback: function(o) { try {
						if (o.success) {
							this.destroy();
							oDt.refresh();
						} else {
							WriteDialogMessage(this, o.message ? o.message : S.oLangs.get('_OPERATION_FAILURE'));
						} } catch(e) {alert(e)}
					}
				}).call(this, e);
			});



			//multi delete
			var multidel_links = YAHOO.util.Dom.getElementsByClassName('ico-wt-sprite subs_del');

			YAHOO.util.Event.addListener(multidel_links, "click", function(e) {
				var body = '', count_sel = oDtS.num_selected;

				if (count_sel > 0) {
					body += '<form method="POST" id="subscribe_table_multidel_dialog_form" action="'+this.href+'">'
						+'<p>'+S.oLangs.get('_DEL')+': '+count_sel+' '+S.oLangs.get('_USERS')+'</p>'
						+'<input type="hidden" name="users" value="'+oDtS.toString()+'" />'
						+'</form>';
				} else {
					body += '<p>'+S.oLangs.get('_EMPTY_SELECTION')+'</p>';
				}

				var oDialog = CreateDialog("subscribe_table_multidelDialog", {
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
					header: S.oLangs.get('_AREYOUSURE'),
					body: body,
					callback: function(o) {
						if (o.deleted) {
							var i;
							for (i=0; i<o.deleted.length; i++)
								oDtS.remsel(o.deleted[i]);
						}
						this.destroy();
						oDt.refresh();
					}
				});
				oDialog.call(this, e);
			});


			//autocomplete
			var url = "ajax.adm_server.php?r="+S.baseLink+"/fastadd"
				+"&id_course="+S.idCourse
				+"&id_edition="+S.idEdition
				+"&id_date="+S.idDate
				+"&filter=" + YAHOO.util.Dom.get('fast_subscribe').value;

			var oDS = new YAHOO.util.XHRDataSource(url);
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "id", "name"]
			};

			var oAC = new YAHOO.widget.AutoComplete("fast_subscribe", "fast_subscribe_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.minQueryLength = 3;
			oAC.maxResultsDisplayed = 15;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) { return oResultData.name; };
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) { YAHOO.util.Dom.get('fast_subscribe_idst').value = oArgs[2].id; });

			//YAHOO.fastSubscribe.autoComplete = {oDS: oDS, oAC: oAC};

			var fastSubscribeSendRequest = function() {
				var id_input = YAHOO.util.Dom.get('fast_subscribe_idst'), userid_input = YAHOO.util.Dom.get('fast_subscribe');

				if (id_input.value <= 0 && userid_input.value == "") return; //if nothing has been inserted, do nothing

				var postdata = "idst="+id_input.value
					+"&userid="+userid_input.value
					+"&id_course="+S.idCourse
					+"&id_edition="+S.idEdition
					+"&id_date="+S.idDate
					+"&send_alert="+(YAHOO.util.Dom.get('fast_subscribe_send_alert').checked ? '1' : '0');

				YAHOO.util.Connect.asyncRequest("POST", "ajax.adm_server.php?r="+S.baseLink+"/fastsubscribe", {
					success: function(o) {
						var res = YAHOO.lang.JSON.parse(o.responseText), res_el = YAHOO.util.Dom.get('fast_subscribe_result');
						if (res.success) {
							res_el.firstChild.innerHTML = res.message ? res.message : S.oLangs.get('_OPERATION_SUCCESSFUL');
							res_el.style.visibility = 'visible';
							oDt.refresh();
							id_input.value = "";
							userid_input.value = "";
						} else {
							res_el.firstChild.innerHTML = res.message ? res.message : S.oLangs.get('_OPERATION_FAILURE');
							res_el.style.visibility = 'visible';
						}
					},
					failure: function() {}
				}, postdata);
			}

			YAHOO.util.Event.addListener('fast_subscribe', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						YAHOO.util.Event.preventDefault(e);
						if (YAHOO.util.Dom.get('fast_subscribe') != "")	fastSubscribeSendRequest();
					} break;
				}
			});

			YAHOO.util.Event.addListener('fast_subscribe_b', "click", function(e) {
				YAHOO.util.Event.preventDefault(e);
				if (YAHOO.util.Dom.get('fast_subscribe') != "")	fastSubscribeSendRequest();
			});


		});

	},

	initEvent: function() {
		var updateSelected = function() {
			Subscription.setNumUserSelected(this.num_selected);
		};
		var ds = DataTableSelector_subscribed_table;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);

		this.doBeforeShowCellEditor = function(oEditor) {
			var key = oEditor.getColumn().getKey();
			var dt = "";
			switch (key) {
				case "date_begin":   
					var dt=oEditor.getRecord().getData("date_begin_timestamp")
					if (dt==0){
						oEditor.value = new Date();
					}
					else{
						dt = dt*1000
						oEditor.value = new Date( dt );
					}					
					break;
				case "date_expire":    
					var dt=oEditor.getRecord().getData("date_expire_timestamp")
					if (dt==0){
						oEditor.value = new Date();
					}
					else{
						dt = dt*1000
						oEditor.value = new Date( dt );
					}	
					break;
			}
			return true;
		};
	},


	beforeRenderEvent: function() {
		var elList = YAHOO.util.Selector.query("a[id^=_reset_dates_]");
		YAHOO.util.Event.purgeElement(elList);

		//dynamic field(s)
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
	},

	postRenderEvent: function() {
		var elList = YAHOO.util.Selector.query("a[id^=_reset_dates_]");
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			var oDt = DataTable_subscribed_table;
			oDt.showTableMessage(oDt.get("MSG_LOADING"), YAHOO.widget.DataTable.CLASS_LOADING);
			var oRecord = oDt.getRecord(this);
			YAHOO.util.Connect.asyncRequest("POST", this.href, {
				success: function(o) {
					var res;
					try { res = YAHOO.lang.JSON.parse(o.responseText); } catch(e) { res = {success: false} };
					if (res.success) {
						oDt.hideTableMessage();
						oDt.updateCell(oRecord, "date_begin", "-");
						oDt.updateCell(oRecord, "date_expire", "-");
					} else {
						oDt.showTableMessage(oDt.get("MSG_ERROR"), YAHOO.widget.DataTable.CLASS_LOADING);
					}
				},
				failure: function() {
					oDt.showTableMessage(oDt.get("MSG_ERROR"), YAHOO.widget.DataTable.CLASS_LOADING);
				}
			});
		});

		//dynamic field(s)
		var slist = YAHOO.util.Selector.query('select[id^=_dyn_field_selector_]');
		var blist = YAHOO.util.Selector.query('a[id^=_dyn_field_sort_]');
		var i;

		for (i=0; i<slist.length; i++) {
			slist[i].disabled = false;
			Subscription.setDropDownRefreshEvent.call(slist[i]);
		}
		for (i=0; i<blist.length; i++) {
			Subscription.setSortButtonRefreshEvent.call(blist[i]);
		}
	},


	selectAllAdditionalFilter: function() {
		return "&filter_text=" + Subscription.filterText +
		   "&filter_orgchart=" + Subscription.filterOrgChart +
		   "&filter_descendants=" + (Subscription.filterDescendants ? '1' : '0') +
		   "&filter_date_valid=" + Subscription.filterDateValid +
		        "&filter_show=" + Subscription.filterShow;
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};
		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
		var i, output = "&results=" 	+ results +
				"&startIndex=" 	+ startIndex +
				"&sort="		+ sort +
				"&dir="			+ dir +
				Subscription.selectAllAdditionalFilter();
		for (i=0; i<Subscription.numVarFields; i++) {
			output += "&_dyn_field["+i+"]=" + YAHOO.util.Dom.get("_dyn_field_selector_"+i).value
		}
		return output;
	},


	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<label for="subscribed_table_sel_'+oRecord.getData("id")+'">'+oData+'</label>';
	},

	levelFormatter: function(elLiner, oRecord, oColumn, oData) {
		var i, valid = false, list = Subscription.levelList;
		for (i=0; i<list.length; i++) {
			if (list[i].value == oData) {
				elLiner.innerHTML = list[i].label;
				valid = true;
				break;
			}
		}
		if (!valid) elLiner.innerHTML = '&nbsp;';
		//elLiner.innerHTML = (YAHOO.lang.isNumber(parseInt(oData)) ? oRecord.getData("level_tr") : oData);
	},

	statusFormatter: function(elLiner, oRecord, oColumn, oData) {
		var i, valid = false, list = Subscription.statusList;
		for (i=0; i<list.length; i++) {
			if (list[i].value == oData) {
				elLiner.innerHTML = list[i].label;
				valid = true;
				break;
			}
		}
		if (!valid) elLiner.innerHTML = '&nbsp;';
		//elLiner.innerHTML = (YAHOO.lang.isNumber(parseInt(oData)) ? oRecord.getData("status_tr") : oData);//oRecord.getData("status_tr");
	},

	dateFormatter: function(elLiner, oRecord, oColumn, oData) {
		if (!oData || oData == "00-00-00" || oData == "00-00-0000") {
			elLiner.innerHTML = '-';
		} else {
			elLiner.innerHTML = oData;
		}
	},

	resetDatesFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'ajax.adm_server.php?r='+Subscription.baseLink
			+'/reset_validity_dates&id_course='+Subscription.idCourse
			+(Subscription.idEdition>0 ? "&id_edition="+Subscription.idEdition : "")
			+(Subscription.idDate>0 ? "&id_date="+Subscription.idDate : "")
			+"&id_user="+oRecord.getData("id");
		var id = "_reset_dates_"+oRecord.getData("id");
		elLiner.innerHTML = '<a href="'+url+'" id="'+id+'" class="ico-sprite subs_cancel" title="'+Subscription.oLangs.get('_RESET_VALIDITY_DATES')+'"><span>'
			+Subscription.oLangs.get('_RESET_VALIDITY_DATES')+'</span></a>';
	},

	asyncSubmitter: function (callback, newData) {
		var new_value = newData;
		var col = this.getColumn().key;
		var old_value =  "";
		var id_user = this.getRecord().getData("id");

		switch (col) {
			case "date_begin": {
				var date = this.calendar.getSelectedDates();
				old_value = this.getRecord().getData("date_begin_timestamp");
				new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
			}break;

			case "date_expire": {
				var date = this.calendar.getSelectedDates();
				old_value = this.getRecord().getData("date_expire_timestamp");
				new_value = parseInt(date[0].getTime() / 1000); //we need time in seconds, not milliseconds
			}break;

			default: {
				old_value = this.value;
			}break;
		}

		var editorCallback = {
			success: function(o) {
				var r = YAHOO.lang.JSON.parse(o.responseText);
				if (r.success) {
					callback(true, r.new_value ? r.new_value : old_value);
				} else {
					callback(false);
				}
			},
			failure: {}
		}

		var _post = "id_course=" + Subscription.idCourse;
		if (Subscription.idEdition) _post += "&id_edition=" + Subscription.idEdition;
		if (Subscription.idDate) _post += "&id_date=" + Subscription.idDate;
		var post =	_post+"&id_user=" + id_user + "&col=" + col + "&new_value=" + new_value + "&old_value=" + old_value;
		var url = "ajax.adm_server.php?r="+Subscription.baseLink+"/show_inline_editor";
		YAHOO.util.Connect.asyncRequest("POST", url, editorCallback, post);
	},


	//dynamic table label management functions
	numVarFields: 0,
	fieldList: [],
	templatePath: "",
	dynSelection: [],

	getDynLabelMarkup: function(index, selected) {
		var x, id= '_dyn_field_selector_'+index, sort_str = Subscription.oLangs.get('_SORT');
		var output = '<select id="'+id+'" name="_dyn_field_selector['+index+']">';
		for (x in Subscription.fieldList) {
			output += '<option value="'+x+'"'
			+( selected == x ? ' selected="selected"' : '' )
			+'>'+Subscription.fieldList[x]+'</option>';
		}
		output += '</select>';

		output += '<a id="_dyn_field_sort_'+index+'" href="javascript:;">';
		output += '<img src="'+Subscription.templatePath+'images/standard/sort.png" ';
		output += 'title="'+sort_str+'" alt="'+sort_str+'" />';
		output += '</a>';

		Subscription.dynSelection[id] = selected;
		return output;
	},

	setDropDownRefreshEvent: function() {
		YAHOO.util.Event.addListener(this, "change", function() {
			Subscription.dynSelection[this.id] = this.selectedIndex ;
			DataTable_subscribed_table.refresh();
		});
	},

	setSortButtonRefreshEvent: function() {
		var oDt = DataTable_subscribed_table;
		YAHOO.util.Event.addListener(this, "click", function(e) {
			YAHOO.util.Event.preventDefault(e);

			var oColumn = oDt.getColumn(this);

			//load adjusted <select> into column label
			var index = this.id.replace('_dyn_field_sort_', '');
			var selected = YAHOO.util.Dom.get('_dyn_field_selector_'+index).value;
			oColumn.label = Subscription.getDynLabelMarkup(index, selected);

			var oSortedBy = oDt.get("sortedBy"), sDir = oDt.CLASS_ASC;
			if (oSortedBy.key == oColumn.getKey()) {
				sDir = (oSortedBy.dir == oDt.CLASS_ASC ? oDt.CLASS_DESC : oDt.CLASS_ASC);
			}
			oDt.sortColumn(oColumn, sDir);
		});
	},


	//----------

	setNumUserSelected: function(num) {
		var prefix = "num_users_selected_", D = YAHOO.util.Dom;
		D.get(prefix+"top").innerHTML = num;
		D.get(prefix+"bottom").innerHTML = num;
	},


	editorSaveEvent: function(oArgs) {
		var oEditor = oArgs.editor;
		var new_value = oArgs.newData;
		var old_value = oArgs.oldData;
		var id_user = oEditor.getRecord().getData("id");
		var col = oEditor.getColumn().getKey();
		var callback = {
			success: function(o) {
				var res = YAHOO.lang.JSON.parse(o.responseText);
				if (res.success) {
					//oEditor.getRecord().setData(col+"_id");
				}
			},
			failure: function() {}
		};

		var url = "ajax.adm_server.php?r="+Subscription.baseLink+"/update";
		var post = "id_course=" + Subscription.idCourse
					+"&id_edition=" + Subscription.idEdition
					+"&id_date=" + Subscription.idDate
					+"&id_user=" + id_user
					+"&col=" + col
					+"&new_value=" + new_value
					+"&old_value=" + old_value;

		YAHOO.util.Connect.asyncRequest("POST", url, callback, post);
	}

}