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


var E = YAHOO.util.Event, D = YAHOO.util.Dom;
var Dashboard = {

	createUserRenderEvent: function() {
		//YAHOO.util.Dom.get("username").focus();
		E.onAvailable("create_user_tabview", function() {
			var tabView = new YAHOO.widget.TabView("create_user_tabview");
		});

		E.onAvailable("createuser_orgchart_tree", function(){
			try {
				YAHOO.runtimeWidgets["createuser_orgchart_tree"]();
			} catch(e) { alert(e); }
		});

		E.onAvailable("username", function() {
			D.get("username").focus();
			var el = D.get("create_user_main_container");
			el.style.width = el.clientWidth+"px";
		});
	},

	createUserDestroyEvent: function() {
		//free memory, DOM and resources
		TreeView_createuser_orgchart_tree.destroy();
		TreeView_createuser_orgchart_tree = null;
	},

	createUserCallback: function(o) {
		//this.destroy();
		WriteDialogMessage(this, o.message || "");

		var D = YAHOO.util.Dom;
		D.get("username").value = "";
		D.get("firstname").value = "";
		D.get("lastname").value = "";
		D.get("email").value = "";
		D.get("password").value = "";
		D.get("password_confirm").value = "";

		D.get("username").focus();
		
		if (o.total_users) {
			var el = YAHOO.util.Dom.get("total_users_count");
			if (el) el.innerHTML = o.total;
		}
	},


	changePasswordRenderEvent: function() {
		YAHOO.util.Event.onAvailable("changepwd_userid", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/usermanagement/users_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "userid_highlight", "idst", "name"]
			};

			var oAC = new YAHOO.widget.AutoComplete("changepwd_userid", "changepwd_userid_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return oResultData.userid_highlight;
			};
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
				var D = YAHOO.util.Dom.get('changepwd_idst').value = oArgs[2].idst;
			});
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

			YAHOO.util.Event.addListener('changepwd_userid', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});
	},

	changePasswordCallback: function(o) {
		//this.destroy();
		if (o.success) {
			WriteDialogMessage(this, o.message || "");
			var D = YAHOO.util.Dom;
			var el = D.get('changepwd_userid');
			if (el) {
				D.get('changepwd_new_password').value = "";
				D.get('changepwd_confirm_password').value = "";
				el.value = "";
				el.focus();
			}
		}
	},

	certificateRenderEvent: function() {
		//courses autocomplete
		YAHOO.util.Event.onAvailable("certificate_course", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?mn=course&plf=lms&op=course_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "courses",
				fields: ["cname", "id_course", "code", "name", "code_highlight", "name_highlight"]
			};

			var oAC = new YAHOO.widget.AutoComplete("certificate_course", "certificate_course_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return (oResultData.code_highlight != "" ? '['+oResultData.code_highlight+'] ' : '')+oResultData.name_highlight;
			};
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
				YAHOO.util.Dom.get('certificate_id_course').value = oArgs[2].id_course;
			});
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

			YAHOO.util.Event.addListener('certificate_course', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});

		//users autocomplete
		YAHOO.util.Event.onAvailable("certificate_userid", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/usermanagement/users_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "userid_highlight", "idst", "name"]
			};

			var oAC = new YAHOO.widget.AutoComplete("certificate_userid", "certificate_userid_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {return oResultData.userid_highlight;};
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {YAHOO.util.Dom.get('certificate_id_user').value = oArgs[2].idst;});
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

			YAHOO.util.Event.addListener('certificate_userid', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});
	},

	certificateCallback: function(o) {
		if (o.success) { WriteDialogMessage(this, o.message); }
		var D = YAHOO.util.Dom;
		//D.get("subscr_course").value = "";
		var el = D.get("subscr_userid");
		if (el) {
			el.value = "";
			el.focus();
		}
	},

	subscribeToCourseRenderEvent: function() {
		//courses autocomplete
		YAHOO.util.Event.onAvailable("subscr_course", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?mn=course&plf=lms&op=course_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "courses",
				fields: ["cname", "id_course", "code", "name", "code_highlight", "name_highlight", "has_editions", "editions", "has_classrooms", "classrooms"]
			};

			var appendOption = function(elSel, id, label) {
				var elOpt = document.createElement('option');
				elOpt.text = label;
				elOpt.value = id;
				try {
					elSel.add(elOpt, null); // standards compliant; doesn't work in IE
				} catch(ex) {
					elSel.add(elOpt); // IE only
				}
			};
			var clearSelection = function(elSel) {
				while (elSel.length > 0)
					elSel.remove(elSel.length - 1);
			};

			var oAC = new YAHOO.widget.AutoComplete("subscr_course", "subscr_course_container", oDS);
			oAC.forceSelection = true;
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {
				return (oResultData.code_highlight != "" ? '['+oResultData.code_highlight+'] ' : '')+oResultData.name_highlight;
			};
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
				var D = YAHOO.util.Dom;
				D.get('subscr_id_course').value = oArgs[2].id_course;

				//check editions/classrooms
				var i, t, elSel, elEditions = D.get("editions_div"), elClassrooms = D.get("classrooms_div");
				if (oArgs[2].has_editions) {
					elSel = D.get('editions_sel');
					clearSelection(elSel);
					for (i=0; i<oArgs[2].editions.length; i++) {
						t = oArgs[2].editions[i];
						appendOption(elSel, t.id, t.display_name);
					}
					elEditions.style.display = 'block';
					elClassrooms.style.display = 'none';
				} else if (oArgs[2].has_classrooms) {
					elSel = D.get('classrooms_sel');
					clearSelection(elSel);
					for (i=0; i<oArgs[2].classrooms.length; i++) {
						t = oArgs[2].classrooms[i];
						appendOption(elSel, t.id, t.display_name);
					}
					elEditions.style.display = 'none';
					elClassrooms.style.display = 'block';
				} else {
					clearSelection(D.get('editions_sel'));
					elEditions.style.display = 'none';
					clearSelection(D.get('classrooms_sel'));
					elClassrooms.style.display = 'none';
				}

			});
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

			YAHOO.util.Event.addListener('subscr_course', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});

		//users autocomplete
		YAHOO.util.Event.onAvailable("subscr_userid", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/usermanagement/users_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "userid_highlight", "idst", "name"]
			};

			var oAC = new YAHOO.widget.AutoComplete("subscr_userid", "subscr_userid_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {return oResultData.userid_highlight;};
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {YAHOO.util.Dom.get('subscr_id_user').value = oArgs[2].idst;});
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };

			YAHOO.util.Event.addListener('subscr_userid', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});
	},

	subscribeToCourseCallback: function(o) {
		if (o.success) { WriteDialogMessage(this, o.message); }
		var D = YAHOO.util.Dom;
		//D.get("subscr_course").value = "";
		var el = D.get("subscr_userid");
		if (el) {
			el.value = "";
			el.focus();
		}
	},


	userStatusCallback: function(o) {
		var el = YAHOO.util.Dom.get("user_status_viewport");
		if (o.success) {
			ResetDialogMessage(this);
		} else {
			el.innerHTML = "";
		}
		if (el) {
			el.innerHTML = o.body;
			var tabView = new YAHOO.widget.TabView("profile_dialog_tabview");
			this.center();
		}
	},
	
	userStatusRenderEvent: function() {
		var oDialog = this;

		YAHOO.util.Event.onAvailable("status_userid", function() {
			var oDS = new YAHOO.util.XHRDataSource('ajax.adm_server.php?r=adm/usermanagement/users_autocomplete');
			oDS.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			oDS.responseSchema = {
				resultsList : "users",
				fields: ["userid", "userid_highlight", "idst", "name"]
			};

			var oAC = new YAHOO.widget.AutoComplete("status_userid", "status_userid_container", oDS);
			oAC.useShadow = true;
			oAC.resultTypeList = false;
			oAC.formatResult = function(oResultData, sQuery, sResultMatch) {return oResultData.userid_highlight;};
			oAC.generateRequest = function(sQuery) { return "&results=20&query=" + sQuery; };
			oAC.itemSelectEvent.subscribe(function(sType, oArgs) {
				//...
			});

			YAHOO.util.Event.addListener('status_userid', "keypress", function(e) {
				switch (YAHOO.util.Event.getCharCode(e)) {
					case 13: {
						this.submit();
					}break;
				}
			}, this, true);

		});

		YAHOO.util.Event.onAvailable("user_status_button", function() {
			var onButtonClick = function() {
				oDialog.submit();
				if (YAHOO.dialogConstants) {
					var el = YAHOO.util.Dom.get("user_status_viewport"), c = YAHOO.dialogConstants;
					el.innerHTML = '<img src="'+c._loadingIcon+'" alt="'+c._LOADING+'"/>'+c._LOADING+'...';
				}
			};
			/*var b = YAHOO.widget.Button(this.id, {type:"button"});
			b.on("click", onButtonClick);
			*/
		 YAHOO.util.Event.addListener(this, "click", onButtonClick);
		});
	},



	diagnosticCallback: function(o) {
		this.destroy();
	},

	diagnosticRenderEvent: function() {
		var oDialog = this;
		YAHOO.util.Event.onAvailable("tech_info_dialog_content", function() {
			oDialog.center();
		});
	},


	//----------------------------------------------------------------------------

	exportCallback: function(o) {
		this.destroy();
	},

	//--- charts -----------------------------------------------------------------

	drawTabView: function(id, tabs) {
		//Create a TabView
		var params, tabView = new YAHOO.widget.TabView();

		for (var i=0; i<tabs.length; i++) {
			params = {
				label: tabs[i].label,
				content: '<div class="chart" id="'+tabs[i].content+'"></div>'
			};
			if (tabs[i].active == true) params.active = true;
			tabView.addTab(new YAHOO.widget.Tab(params));
		}

		//Append TabView to its container div
		tabView.appendTo(id);
	},

	drawChart: function(id, data, tooltip, direction) {
		if(direction == 'rtl') data.reverse();

		var dataSource = new YAHOO.util.DataSource( data );
		dataSource.responseType = YAHOO.util.DataSource.TYPE_JSARRAY;
		//x_axis: always a date, y_axis: always a number
		dataSource.responseSchema = {fields: [ "x_axis", "y_axis" ]};

		var toolTipText = function(item, index, series) {
			var text = tooltip.replace('{x_axis}', item.x_axis);
			text = text.replace('{y_axis}', item.y_axis);
			return text;
		};
		YAHOO.util.Dom.get(id).style.height = "120px";

		var axe = new YAHOO.widget.NumericAxis();
		axe.alwaysShowZero = true;
		axe.calculateByLabelSize = true;
		axe.position = ( direction == 'rtl' ? "right" : "left" );
		//axe.minorUnit = 1;
		//axe.majorUnit = 1;
		axe.snapToUnits = true;

		var myChart = new YAHOO.widget.ColumnChart( id, dataSource, {
			xField: "x_axis",
			yField: "y_axis",
			wmode: "opaque",
			yAxes: [axe]
		});
	}

}
