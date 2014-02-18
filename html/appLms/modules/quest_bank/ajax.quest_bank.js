
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

var quest_selection = null;
var do_not_submit =false;

function toggle_quest(e) {

	if(this.checked) {
		quest_selection.addsel(this.value);
	} else {
		quest_selection.remsel(this.value);
	}
	quest_selection.refreshCounter();
}

function select_page(e) {
	YAHOO.util.Event.stopEvent(e);

	var checkboxs = document.getElementsByTagName("input");
	for (var i = 0; i < checkboxs.length; i++) {

		var pos = checkboxs[i].name.indexOf( "quest[" );
		if(pos >= 0 ) {

			var id_quest = parseInt((checkboxs[i].name).replace(/quest\[/,""));
			quest_selection.addsel(id_quest);
			checkboxs[i].checked = true;
		}
	}
	quest_selection.refreshCounter();
}

function deselect_page(e) {
	YAHOO.util.Event.stopEvent(e);

	var checkboxs = document.getElementsByTagName("input");
	for (var i = 0; i < checkboxs.length; i++) {

		var pos = checkboxs[i].name.indexOf( "quest[" );
		if(pos >= 0 ) {

			var id_quest = parseInt((checkboxs[i].name).replace(/quest\[/,""));
			quest_selection.remsel(id_quest);
			checkboxs[i].checked = false;
		}
	}
	quest_selection.refreshCounter();
}

function select_all_filtered(e) {
	YAHOO.util.Event.stopEvent(e);

	// i must ask to the server for the id complete list
	var callback = { success: callback_selectallfilter, failure: callback_selectallfilter }
	var args = 'op=getselected'
		+ '&quest_category=' + YAHOO.util.Dom.get('quest_category').value
		+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value
		+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value;
	var transaction = YAHOO.util.Connect.asyncRequest('POST', "ajax.server.php?plf=lms&mn=quest_bank&"+args, callback, null);

	setTimeout("YAHOO.util.Connect.abort(transaction)",15000);
}

function callback_selectallfilter(oReq) {

	var response = YAHOO.lang.JSON.parse(oReq.responseText);
	// update the selction
	for (var i = 0; i < response.length; i++) {

		quest_selection.addsel(response[i]);
	}

	// update all the checkbox in the page
	var checkboxs = document.getElementsByTagName("input");
	for (var i = 0; i < checkboxs.length; i++) {

		if(checkboxs[i].name.indexOf("quest[") >= 0 ) {
			checkboxs[i].checked = true;
		}
	}
	quest_selection.refreshCounter();
}

function deselect_all(e) {
	YAHOO.util.Event.stopEvent(e);

	// update all the checkbox in the page
	var checkboxs = document.getElementsByTagName("input");
	for (var i = 0; i < checkboxs.length; i++) {

		if(checkboxs[i].name.indexOf("quest[") >= 0 ) {
			checkboxs[i].checked = false;
		}
	}
	quest_selection.reset();
	YAHOO.util.Dom.get('current_selected').innerHTML = 0;
}
/**
 * I'm expecting that the main page define three globals array that can contain the data needed to render the result table
 * QB_DEF -> with the lang definition that i need
 * QB_CATEGORIES -> with the category list
 * QB_DIFFICULT -> with the difficult request
 * QB_QTYPE -> the quest_type
 **/
var rem_callback = function(e, elCell)
{
	var id_quest = this.getData('id_quest');
	var row_quest = elCell.parentNode.id;

	var dialog_markup = document.createElement('div');
	dialog_markup.id = 'dialog_'+id_quest;
	dialog_markup.style.visibility = 'hidden';
	dialog_markup.innerHTML = '<div class="hd">'+QB_DEF.del_quest+'</div>'
		+ '<div class="bd">'
		+ QB_DEF.del_confirm
		+ '<form method="POST" action="'+QB_PATHS.del_req+'">'
		+ '<input type="hidden" id="row_quest_'+id_quest+'" name="row_quest" value="' + row_quest + '"  />'
		+ '<input type="hidden" id="id_quest_'+id_quest+'" name="id_quest" value="' + id_quest + '"  />'
		+ '</form>'
		+ '</div>';
	elCell.appendChild(dialog_markup);

	var myDialog = new YAHOO.widget.Dialog('dialog_'+id_quest, {
		width: "340px",
		fixedcenter:true,
		draggable:false,
		modal: true,
		visible:false });

	var handleSuccess = function(o)
	{
		var response = YAHOO.lang.JSON.parse(o.responseText);

		if(response.result) {
			//docebo_quest_bank.myDataTable.deleteRow(response.row_quest);
			docebo_quest_bank.refresh();
		} else {
			var panel2 = new YAHOO.widget.Panel("panel2", { width:"320px", visible:false, draggable:false, close:true } );
			panel2.setHeader("Error");
			panel2.setBody(response.error);
			panel2.render("dialog_container");
			panel2.show();
		}
	};
	var handleFailure = function(o) {
		alert("Submission failed: " + o.status);
	};

	myDialog.callback = { success: handleSuccess,
						failure: handleFailure };

	var handleYes = function() { this.submit(); }
	var handleNo = function() { this.hide(); }

	var myButtons = [ { text:QB_DEF.yes, handler:handleYes },
					  { text:QB_DEF.undo, handler:handleNo } ];
	myDialog.cfg.queueProperty("buttons", myButtons);
	myDialog.render('dialog_container');
	myDialog.show();
}

YAHOO.util.Event.addListener(window, "load", function() {
	quest_selection = new ElemSelector('quest_');
	quest_selection.counter = 'current_selected';

	docebo_quest_bank = new function() {

		// formatters
		this.formatCategoryQuest = function(elCell, oRecord, oColumn, oData) {
			elCell.innerHTML=QB_CATEGORIES[oData];
		}
		this.formatTypeQuest = function(elCell, oRecord, oColumn, oData) {
			elCell.innerHTML=QB_QTYPE[oData];
		}
		this.formatDifficultQuest = function(elCell, oRecord, oColumn, oData) {
			elCell.innerHTML=QB_DIFFICULT[oData];
		}

		this.formatCheckboxQuest = function(elCell, oRecord, oColumn, oData) {

			elCell.innerHTML = '';
			var id_quest 	= oRecord.getData('id_quest');
			var checkbox 	= document.createElement("input");
			checkbox.type 	= 'checkbox';
			checkbox.id 	= 'quest['+id_quest+']';
			checkbox.name 	= 'quest['+id_quest+']';
			checkbox.value 	= id_quest;
			if(quest_selection.isset(id_quest)) {
				checkbox.checked = true;
			}
			YAHOO.util.Event.addListener(checkbox, "click", toggle_quest);

			elCell.appendChild(checkbox);

		}

		this.formatTextQuest = function(elCell, oRecord, oColumn, oData) {

			var title_quest = oRecord.getData('title_quest');

			elCell.innerHTML = ( title_quest.length > 250 ? title_quest.slice(0,250)+' (...)' : title_quest );

		}

		this.formatModQuest = function(elCell, oRecord, oColumn, oData) {

			elCell.innerHTML = '';
			var id_quest = oRecord.getData('id_quest');

			var click = document.createElement("a");
			click.href 	= QB_PATHS.mod_link + id_quest;
			click.id 	= 'mod_quest_'+id_quest;

			var modimg = document.createElement("img");
			modimg.src 	= QB_PATHS.image + 'standard/edit.png';
			modimg.alt 	= QB_DEF.mod_quest_img;

			click.appendChild(modimg);
			elCell.appendChild(click);
		}

		this.formatDelQuest = function(elCell, oRecord, oColumn, oData) {

			elCell.innerHTML = '';
			var id_quest = oRecord.getData('id_quest');

			var click = document.createElement("a");
			click.href 	= '#';
			click.id 	= 'del_quest_'+id_quest;

			YAHOO.util.Event.addListener(click, "click", rem_callback, elCell, oRecord);

			var delimg = document.createElement("img");
			delimg.src 	= QB_PATHS.image + 'standard/delete.png';
			delimg.alt 	= QB_DEF.del_quest_img;

			click.appendChild(delimg);

			elCell.appendChild(click);
		}
		
		this.refresh = function() {
			oDt.refresh();
		}
		
		// --------------------------------------
		
		if(use_mod_action) {
			var myColumnDefs = [
				{key:"checkbox_sel",	label:QB_DEF.checkbox_sel,		formatter:this.formatCheckboxQuest },
				{key:"category_quest",	label:QB_DEF.quest_category, 	formatter:this.formatCategoryQuest },
				{key:"type_quest", 		label:QB_DEF.type_quest, 		formatter:this.formatTypeQuest },
				{key:"title_quest", 	label:QB_DEF.title_quest, 		formatter:this.formatTextQuest },
				{key:"difficult", 		label:QB_DEF.difficult, 		formatter:this.formatDifficultQuest },
				//{key:"stat_quest", 		label:QB_DEF.stat_quest_img, 	formatter:this.formatStatQuest},
				{key:"mod_quest", 		label:QB_DEF.mod_quest_img, 	formatter:this.formatModQuest},
				{key:"del_quest", 		label:QB_DEF.del_quest_img, 	title: 'Delete', formatter:this.formatDelQuest}
			];
		} else {
			var myColumnDefs = [
				{key:"checkbox_sel",	label:QB_DEF.checkbox_sel,		formatter:this.formatCheckboxQuest },
				{key:"category_quest",	label:QB_DEF.quest_category, 	formatter:this.formatCategoryQuest },
				{key:"type_quest", 		label:QB_DEF.type_quest, 		formatter:this.formatTypeQuest },
				{key:"title_quest", 	label:QB_DEF.title_quest, 		formatter:this.formatTextQuest },
				{key:"difficult", 		label:QB_DEF.difficult, 		formatter:this.formatDifficultQuest },
				//{key:"stat_quest", 		label:QB_DEF.stat_quest_img, 	formatter:this.formatStatQuest}
			];
		}
		
		if(use_mod_action) {
			var t1 = {key:"mod_quest", 		label:QB_DEF.mod_quest_img, 	formatter:this.formatModQuest};
			var t2 = {key:"del_quest", 		label:QB_DEF.del_quest_img, 	formatter:this.formatDelQuest};
			// myColumnDefs.push(t1, t2);
		}
		var buildQueryString = function (state,dt) {
			
			return 'quest_category=' + YAHOO.util.Dom.get('quest_category').value
					+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value
					+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value
					+ "&startIndex=" + state.pagination.recordOffset
					+ "&results=" + state.pagination.rowsPerPage;
		};
		
		var oConfig = {
			id: "markup",
			ajaxUrl: "ajax.server.php?plf=lms&mn=quest_bank&",
			columns: myColumnDefs,
			fields: ["id_quest","category_quest","type_quest",{key:"title_quest", parser:YAHOO.util.DataSource.parseString},"difficult"],
			sort: "category_quest",
			dir: "asc",
			usePaginator: true,
			paginatorParams: {
				containers 				: ['paginator_head','paginator_foot'],
				pageLinks 				: 5,
				rowsPerPage 			: 25,
				rowsPerPageOptions 		: [10,25,50,100], 
				template 				: "<strong>{CurrentPageReport}</strong> {FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} {RowsPerPageDropdown}"
			},
			generateRequest: buildQueryString
		};
		
		var oDs = new YAHOO.util.DataSource(oConfig.ajaxUrl);
		oDs.responseType = YAHOO.util.DataSource.TYPE_JSON;
		oDs.connMethodPost = true;
		oDs.responseSchema = {
			resultsList: "records",
			fields: ["id_quest","category_quest","type_quest",{key:"title_quest", parser:YAHOO.util.DataSource.parseString},"difficult"],
			metaFields: {
				startIndex: "startIndex",
				totalRecords: "totalRecords"
			}
		};
		var configs = {
			initialLoad: false,
			dynamicData: true,
			sortedBy : {key: oConfig.sort, dir: oConfig.dir}
		};

		configs.generateRequest = oConfig.generateRequest;
		configs.paginator = new YAHOO.widget.Paginator(oConfig.paginatorParams);

		var table_type = YAHOO.widget.DataTable;
		var oDt = new table_type(oConfig.id, myColumnDefs, oDs, configs);

		if (oConfig.usePaginator) {
			oDt.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
				oPayload.totalRecords = oResponse.meta.totalRecords;
				oPayload.pagination.recordOffset = oResponse.meta.startIndex;
				return oPayload;
			};
		}
		oDt.refresh = function() {
			var oDt = this;
			var oState = oDt.getState();
			var request = oDt.get("generateRequest")(oState, oDt);
			var oCallback = {
				success : function(oRequest, oResponse, oPayload) {
					oPayload.totalRecords = oResponse.meta.totalRecords;
					if (oConfig.usePaginator) oPayload.pagination.recordOffset = oResponse.meta.startIndex;
					this.onDataReturnSetRows(oRequest, oResponse, oPayload);
				},
				failure : oDt.onDataReturnSetRows,
				argument : oState,
				scope : oDt
			};
			oDt.showTableMessage(oDt.get("MSG_LOADING"), YAHOO.widget.DataTable.CLASS_LOADING);
			oDt.getDataSource().sendRequest(request, oCallback);
		};
		oDt.refresh();
		
		YAHOO.util.Event.addListener("quest_search-button", "click", function(e){
			YAHOO.util.Event.preventDefault(e);
			oDt.refresh();
		}, this, true);
		YAHOO.util.Event.addListener("quest_reset-button", "click", function(e){
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get("quest_search").reset();
			oDt.refresh();
		}, this, true);
		
/*
		// data source (XHR)
		this.myDataSource = new YAHOO.util.DataSource("ajax.server.php?plf=lms&mn=quest_bank&");
		this.myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.myDataSource.connXhrMode = "queueRequests";
		this.myDataSource.responseSchema = {
			resultsList: "records",
			totalRecords: 'totalRecords',

			fields: ["id_quest","category_quest","type_quest",{key:"title_quest", parser:YAHOO.util.DataSource.parseString},"difficult"]
		};

		// A custom function to translate the js paging request into a query
		// string sent to the XHR DataSource
		var buildQueryString = function (state,dt) {
			return 'quest_category=' + YAHOO.util.Dom.get('quest_category').value
					+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value
					+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value
					+ "&startIndex=" + state.pagination.recordOffset +
					"&results=" + state.pagination.rowsPerPage;
		};

		// Set up the Paginator instance.
		var myPaginator = new YAHOO.widget.Paginator({
			containers 				: ['paginator_head','paginator_foot'],
			pageLinks 				: 5,
			rowsPerPage 			: 25,
			rowsPerPageOptions 		: [10,25,50,100], //[parseInt(quest_per_page/2),quest_per_page,quest_per_page*2],
			alwaysVisible 			: false,
			template 				: "<strong>{CurrentPageReport}</strong> {FirstPageLink} {PreviousPageLink} {PageLinks} {NextPageLink} {LastPageLink} {RowsPerPageDropdown}"
		});

		var oConfigs = {
			initialRequest         : 'startIndex=0&results=' + 25,
			generateRequest        : buildQueryString,
			paginator              : myPaginator
			// paginationEventHandler : YAHOO.widget.DataTable.handleDataSourcePagination 
		};

		this.myDataTable = new YAHOO.widget.DataTable("markup", myColumnDefs, this.myDataSource, oConfigs);

		this.myDataTable.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {
		 oPayload.totalRecords = oResponse.meta.totalRecords;
		 oPayload.pagination.recordOffset = oResponse.meta.startIndex;
		 return oPayload;
		};

		// manage search filter -----
		this.updateTable = function (e) {
			do_not_submit =true;
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Event.stopEvent(e);
			var newRequest = 'quest_category=' + YAHOO.util.Dom.get('quest_category').value
							+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value
							+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value
							+ '&startIndex=0&results=' + 25;
			this.myDataSource.sendRequest(newRequest, this.myDataTable.onDataReturnInitializeTable, this.myDataTable);

			YAHOO.util.Dom.setStyle("quest_reset", 'visibility', 'visible');
		}
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_search"), "mouseover", function (e) { do_not_submit=true;});
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_search"), "mouseout", function (e) { do_not_submit=false;});
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_search"), "click", this.updateTable, this, true);

		// manage reset filter -----
		this.resetTable = function (e) {

			do_not_submit =true;
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Event.stopEvent(e);
			YAHOO.util.Dom.get('quest_category').value = 0;
			YAHOO.util.Dom.get('quest_difficult').value = 0;
			YAHOO.util.Dom.get('quest_type').value = 0;
			var newRequest = 'quest_category=0'
							+ '&quest_difficult=0'
							+ '&quest_type=0'
							+ '&startIndex=0&results=' + quest_per_page;
			this.myDataSource.sendRequest(newRequest, this.myDataTable.onDataReturnInitializeTable, this.myDataTable);

			YAHOO.util.Dom.setStyle("quest_reset", 'visibility', 'hidden');
		}
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_reset"), "mouseover", function (e) { do_not_submit=true;});
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_reset"), "mouseout", function (e) { do_not_submit=false;});
		YAHOO.util.Event.addListener(YAHOO.util.Dom.get("quest_reset"), "click", this.resetTable, this, true);

		this.submitForm = function(e) {
			if (do_not_submit) {
				YAHOO.util.Event.preventDefault(e);
				do_not_submit =false;
			}
		}

		YAHOO.util.Event.addListener("search_form", "submit", this.submitForm, this, true);

		this.refresh = function() {
			var newRequest = 'quest_category=0'
							+ '&quest_difficult=0'
							+ '&quest_type=0'
							+ '&startIndex=0&results=' + quest_per_page;
			this.myDataSource.sendRequest(newRequest, this.myDataTable.onDataReturnInitializeTable, this.myDataTable);
		}
*/
	};
});

YAHOO.util.Event.addListener(window, "load", function() {

	YAHOO.util.Event.addListener(YAHOO.util.Dom.get("search_form"), "submit",
		function(e) { YAHOO.util.Dom.get(hidden_for_selection).value = quest_selection.toString(); }
	);

	YAHOO.util.Event.addListener(YAHOO.util.Dom.get("select_all"), "click", select_all_filtered );
	YAHOO.util.Event.addListener(YAHOO.util.Dom.get("select_page"), "click", select_page );
	YAHOO.util.Event.addListener(YAHOO.util.Dom.get("deselect_all"), "click", deselect_all );
	YAHOO.util.Event.addListener(YAHOO.util.Dom.get("deselect_page"), "click", deselect_page );

	var oSplitExport = new YAHOO.widget.Button("export_quest", { type: "menu", menu: "export_quest_select" });
	var oPushImport = new YAHOO.widget.Button("import_quest");
	var oSplitAddQuest = new YAHOO.widget.Button("add_quest", { type: "menu", menu: "add_test_quest" });

});


YAHOO.util.Event.onDOMReady(function () {
	YAHOO.util.Dom.setStyle("quest_reset", 'visibility', 'hidden');
});