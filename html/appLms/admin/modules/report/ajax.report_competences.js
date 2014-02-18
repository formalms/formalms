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

//datatable for competence selector

var ajax_path = 'ajax.adm_server.php?mn=report&plf=lms&rep_cat=competences';

var competences_per_page=20;
var RC_DEF = {
	check : '',
	category : 'categoria',
	competence : 'competenza', 
	type : 'tipo',
	min_score : 'punteggio minimo',
	max_score : 'punteggio massimo'
}

YAHOO.util.Event.addListener(window, "load", function() {
	
	var docebo_competence_selection = new function() {
		
		var mySelf = this;
		var cur_selection = new ElemSelector('comp_');
		
		this.initialSelected = new Array();
		
		// formatters
		this.formatCategory = function(elCell, oRecord, oColumn, oData) {
			elCell.innerHTML=oRecord.getData('category');
			//rowspan oColumns ?!?
		}
		
		this.formatCheckbox = function(elCell, oRecord, oColumn, oData) {
		
			var sel = oRecord.getData('selected');
		
			if (sel) {
				var _dt = docebo_competence_selection.myDataTable; 
				_dt.selectRow(oRecord);
				//YAHOO.util.Dom.addClass(_dt.getTrEl(oRecord), _dt.CLASS_SELECTED);
				mySelf.initialSelected.push(oRecord.getId());
			}
		
			elCell.innerHTML = '';
			var id 	= oRecord.getData('id');
			var checkbox 	= document.createElement("input");
			checkbox.type 	= 'checkbox';
			checkbox.id 	= 'competence['+id+']';
			checkbox.name 	= 'competence['+id+']';
			checkbox.value 	= id;
			checkbox.checked = sel; //false; //check selected rows (maybe keep them in $_SESSION on server) 

			
			YAHOO.util.Event.addListener(checkbox, "click", function(e) {
				//TO DO: disable checkbox while waiting for response
				var oSelf = this.myDataTable; //datatable istance
				var f1 = function(o) {	oSelf.selectRow(oRecord); };
				var f2 = function(o) {	oSelf.unselectRow(oRecord); };
				var t;
				if (elCell.firstChild.checked) {
					/*t = YAHOO.util.Connect.asyncRequest('POST', ajax_path+'&op=selectrow&idsel='+id, {
						success : f1,
						failure : function(o) { alert('failure'); }
						}, null);*/
					cur_selection.addsel(.oRecord.getData('id'));
					oSelf.selectRow(oRecord);
				} else {
					/*t = YAHOO.util.Connect.asyncRequest('POST', ajax_path+'&op=unselectrow&idsel='+id, {
						success : f2,
						failure : function(o) { alert('failure'); }
						}, null);*/
					if (cur_selection.isset(oRecord.getData('id'))) {
						cur_selection.addsel(oRecord.getData('id'));
						oSelf.unselectRow(oRecord);
					}
				}
			}, docebo_competence_selection, true);
			
			elCell.appendChild(checkbox);
			//elCell.innerHTML += '<span>'+docebo_competence_selection.myDataTable.getSelectedRows().join(', ')+'</span>';
		}
				
		// --------------------------------------
		
		var myColumnDefs = [
			{key:"checkbox_sel",	label:RC_DEF.check,		formatter:this.formatCheckbox },
			{key:"category",	label:RC_DEF.category, 	formatter:this.formatCategory },
			{key:"competence", 		label:RC_DEF.competence },
			{key:"type", 	label:RC_DEF.type },
			{key:"min_score", 		label:RC_DEF.min_score },
			{key:"max_score", 		label:RC_DEF.max_score }
		];
			
		// data source (XHR)
		this.myDataSource = new YAHOO.util.DataSource(ajax_path+'&');//"../appLms/admin/modules/report/ajax.report_competences.php?");
		this.myDataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;
		this.myDataSource.connXhrMode = "queueRequests";
		this.myDataSource.responseSchema = {
			resultsList: "records",
			totalRecords: 'totalRecords',
			fields: ["id","id_cat","category","competence","type","min_score","max_score"]
		};

		// A custom function to translate the js paging request into a query
		// string sent to the XHR DataSource
		var buildQueryString = function (state,dt) {
			/*return 'quest_category=' + YAHOO.util.Dom.get('quest_category').value 
					+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value 
					+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value 
					+ "&startIndex=" + state.pagination.recordOffset +
					"&results=" + state.pagination.rowsPerPage;*/
					
			return "startIndex=" + state.pagination.recordOffset +
						 "&results=" + state.pagination.rowsPerPage;
		};
		
		// Set up the Paginator instance.  
		var myPaginator = new YAHOO.widget.Paginator({
			containers 				: ['paginator_head','paginator_foot'],
			pageLinks 				: 5,
			rowsPerPage 			: competences_per_page,
			rowsPerPageOptions 		: [parseInt(competences_per_page/2),competences_per_page,competences_per_page*2],
			alwaysVisible 			: false,
			template 				: "<strong>{CurrentPageReport}</strong> {PreviousPageLink} {PageLinks} {NextPageLink} {RowsPerPageDropdown}"
		});
		
		var oConfigs = {
			initialRequest         : 'startIndex=0&results=' + competences_per_page,
			generateRequest        : buildQueryString,
			paginator              : myPaginator, 
			paginationEventHandler : YAHOO.widget.DataTable.handleDataSourcePagination,
			selectionMode          : 'standard' 
		};
	
		this.myDataTable = new YAHOO.widget.DataTable("datatable", myColumnDefs, this.myDataSource, oConfigs);

		/*this.selectInitialRows = function(e) { alert(mySelf.initialSelected.length);
			for (var x=0; x<mySelf.initialSelected.length; x++) {
				mySelf.myDataTable.selectRow(mySelf.initialSelected[x]);
			}
		};
		
		this.myDataTable.subscribe("updateOnChangeChange", this.selectInitialRows);*/

		this.myDataTable.subscribe("rowMouseoverEvent", this.myDataTable.onEventHighlightRow); 
		this.myDataTable.subscribe("rowMouseoutEvent", this.myDataTable.onEventUnhighlightRow); 
		//this.myDataTable.subscribe("rowClickEvent", this.myDataTable.onEventSelectRow); 
		
		for (var x=0; x<this.initialSelected.length; x++) {
			this.myDataTable.selectRow(this.myDataTable.getTrEl(this.initialSelected[x]));
		}
		 
		
		// manage search filter -----
		this.updateTable = function (e) {
			
			YAHOO.util.Event.stopEvent(e);
			var newRequest = /*'quest_category=' + YAHOO.util.Dom.get('quest_category').value 
							+ '&quest_difficult=' + YAHOO.util.Dom.get('quest_difficult').value 
							+ '&quest_type=' + YAHOO.util.Dom.get('quest_type').value 
							+ '&*/
							'startIndex=0&results=' + competences_per_page;
			this.myDataSource.sendRequest(newRequest, this.myDataTable.onDataReturnInitializeTable, this.myDataTable);
			
			//YAHOO.util.Dom.setStyle("quest_reset", 'visibility', 'visible'); 
		}
		
		
		// manage reset filter -----
		this.resetTable = function (e) {
			
		}
		
	};
});

YAHOO.util.Event.addListener(window, "load", function() {
	/*
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
	*/
});