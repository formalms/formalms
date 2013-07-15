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

var CompetenceSelector = {

	selectedCategory: 0,
	showDescendants: false,
	filterText: "",
	currentLanguage: "",
	oLangs: new LanguageManager(),

	init: function(oConfig) {
		this.oLangs.set(oConfig.langs || {});

		this.selectedCategory = oConfig.selectedCategory || 0;
		this.currentLanguage = oConfig.currentLanguage || "";

		YAHOO.util.Event.addListener(oConfig.id+"_filter_set", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			CompetenceSelector.filterText = YAHOO.util.Dom.get(oConfig.id + "_filter_text").value;
			DataTable_competenceselector_table.refresh();
		});

		YAHOO.util.Event.addListener(oConfig.id+"_filter_reset", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get(oConfig.id + "_filter_text").value = "";
			CompetenceSelector.filterText = "";
			DataTable_competenceselector_table.refresh();
		});

		YAHOO.util.Event.addListener(oConfig.id+"_show_descendants", "click", function(e) {
			CompetenceSelector.showDescendants = this.checked;
			DataTable_competenceselector_table.refresh();
		});

		var el = YAHOO.util.Dom.get("competences_selection_" + oConfig.id);
		if (el) {
			if (el.form) {
				YAHOO.util.Event.addListener(el.form, "submit", function(e) {
					el.value = DataTableSelector_competenceselector_table.toString();
				});
			}
		}
	},

	initTableEvent: function() {
		var updateSelected = function() {
			var num = this.num_selected, D = YAHOO.util.Dom;
			var prefix = "num_competences_selected_";
			D.get(prefix + "over").innerHTML = num;
			D.get(prefix + "bottom").innerHTML = num;
		};
		var ds = DataTableSelector_competenceselector_table;
		ds.subscribe("add", updateSelected);
		ds.subscribe("remove", updateSelected);
		ds.subscribe("reset", updateSelected);
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&id_category=" + CompetenceSelector.selectedCategory +
				"&descendants=" + (CompetenceSelector.showDescendants ? '1' : '0') +
				"&filter_text=" + CompetenceSelector.filterText;
	},

	labelFormatter: function(elLiner, oRecord, oColumn, oData) {
		var id = DataTable_competenceselector_table.getContainerEl().id+'_sel_'+oRecord.getData("id");
		elLiner.innerHTML = '<label for="'+id+'">'+oData+'</label>';
	}

}




var CompetenceSelectorFolderTree = function(id, oConfig) {
	CompetenceSelectorFolderTree.superclass.constructor.call(this, id, oConfig);
	this.setNodeClickEvent(this.clickNode, this);
};

YAHOO.lang.extend(CompetenceSelectorFolderTree, FolderTree, {

	_getUrl: function(op) {return 'ajax.adm_server.php?r=widget/competenceselector/'+op;},

	clickNode: function(oNode) {
		var id = this._getNodeId(oNode);
		CompetenceSelector.selectedCategory = id;
		DataTable_competenceselector_table.refresh();
	},

	toString: function() {
		return "CompetencesCategoriesTree '"+this.id+"'";
	}
});