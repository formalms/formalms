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


var Competences = {

	selectedCategory: 0,
	showDescendants: false,
	filterText: "",
	currentLanguage: "",
	oLangs: new LanguageManager(),

	typologies: [],
	types: [],

	init: function(oConfig) {
		Competences.oLangs.set(oConfig.langs || {});

		this.selectedCategory = oConfig.selectedCategory || 0,
		this.showDescendants = oConfig.showDescendants || false,
		this.filterText = oConfig.filterText || "",
		this.currentLanguage = oConfig.currentLanguage || "",

		this.typologies = oConfig.typologies;
		this.types = oConfig.types;

		YAHOO.util.Dom.get("show_descendants").checked = this.showDescendants;

		YAHOO.util.Event.addListener('filter_text', "keypress", function(e) {
			switch (YAHOO.util.Event.getCharCode(e)) {
				case 13: {
					YAHOO.util.Event.preventDefault(e);
					Competences.filterText = this.value;
					DataTable_competences_table.refresh();
				} break;
			}
		});

		YAHOO.util.Event.addListener("filter_set", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			Competences.filterText = YAHOO.util.Dom.get("filter_text").value;
			DataTable_competences_table.refresh();
		});

		YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
			YAHOO.util.Event.preventDefault(e);
			YAHOO.util.Dom.get("filter_text").value = "";
			Competences.filterText = "";
			DataTable_competences_table.refresh();
		});

		YAHOO.util.Event.addListener("show_descendants", "click", function(e) {
			Competences.showDescendants = this.checked;
			DataTable_competences_table.refresh();
		});

	},

	viewFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a href="index.php?r=adm/competences/view_competence_report&id=' +
			oRecord.getData("id") + '" class="ico-sprite subs_view" ' +
			'title="'+Competences.oLangs.get('_VIEW')+'"><span>'+Competences.oLangs.get('_VIEW')+'</span></a>';
	},

	usersFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/competences/show_users&id=' +	oRecord.getData("id");
		elLiner.innerHTML = '<a href="' + url +'" title="'+Competences.oLangs.get('_USERS')+'">' + oData +
			'&nbsp;<span class="ico-sprite subs_users">' +
			'<span>'+Competences.oLangs.get('_USERS')+'</span></span></a>';
	},

	modifyFormatter: function(elLiner, oRecord, oColumn, oData) {
		elLiner.innerHTML = '<a href="index.php?r=adm/competences/mod_competence&id=' +
			oRecord.getData("id") + '" class="ico-sprite subs_mod" ' +
			'title="'+Competences.oLangs.get('_MOD')+'"><span>'+Competences.oLangs.get('_MOD')+'</span></a>';
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
				"&id_category=" + Competences.selectedCategory +
				"&descendants=" + (Competences.showDescendants ? '1' : '0') +
				"&filter_text=" + Competences.filterText;
	},

	addCategoryCallback: function(o) {
		if (o.node) {
			var parent = TreeView_competences_tree._getNodeById(o.id_parent);
			TreeView_competences_tree.appendNode(parent, o.node, false);
		}
		this.destroy();
	},

	dialogRenderEvent: function() {
		var tabs = new YAHOO.widget.TabView("category_langs_tab");
		var id = "name_"+Competences.currentLanguage;
		YAHOO.util.Event.onAvailable(id, function(o) {
			this.focus();
			o.center(); //TO DO: make this working ...
		}, this);
	},

	asyncSubmitter: function (callback, newData) {
		var newValue = newData;
		var oldValue =  "";
		var column = this.getColumn().key;
		var idCompetence = this.getRecord().getData("id");

		switch (column) {
			case "name": {
				oldValue = this.getRecord().getData("name");
				newValue = this.getInputValue();
			}break;

			case "description": {
				oldValue = this.getRecord().getData("description");
				newValue = this.getInputValue();
			}break;

			case "typology": {
				oldValue = this.getRecord().getData("id_typology");
				newValue = this.getInputValue();
			} break;

			case "type": {
				oldValue = this.getRecord().getData("id_type");
				newValue = this.getInputValue();
			} break;

			default: {
				oldValue = this.value;
			}break;
		}

		var editorCallback = {
			success: function(o) {
				var r = YAHOO.lang.JSON.parse(o.responseText);
				if (r.success) {
					callback(true, r.new_value ? r.new_value : oldValue);
				} else {
					callback(false);
				}
			},
			failure: {}
		}

		var post = "id_competence=" + idCompetence + "&column=" + column + "&new_value=" + newValue + "&old_value=" + oldValue;
		var url = "ajax.adm_server.php?r=adm/competences/inline_edit";
		YAHOO.util.Connect.asyncRequest("POST", url, editorCallback, post);
	}

}




var CompetencesFolderTree = function(id, oConfig) {

	CompetencesFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(this.clickNode, this);
	this.setOptionClickEvent(this.executeOption, this);
	this.setDragDropEvent(this.moveNode, this);

};

YAHOO.lang.extend(CompetencesFolderTree, FolderTree, {

	_getUrl: function(op) {return 'ajax.adm_server.php?r=adm/competences/'+op;},

	executeOption: function(node, option) {
		//option == { command: "any command string", node: [node instance] }
		switch (option.command) {

			case "modify": {
				this.modifyCategory(node);
			}break;

			case "delete": {
				this.deleteNodeEvent(node);
			}break;

			default: {
				//alert("option command: "+option.command);
			}

		}
	},


	clickNode: function(oNode) { try{
		var i, el, id = this._getNodeId(oNode);
		var links = ['add_category', 'add_competence_over', 'add_competence_bottom'];

		for (i=0; i<links.length; i++) {
			el = YAHOO.util.Dom.get(links[i]);
			if (el) el.href = el.href.replace(/id=[0-9]*/, 'id='+id);
		}

		Competences.selectedCategory = id;
		DataTable_competences_table.refresh(); } catch(e) { alert(e); }
	},


	moveNode: function(src, dest, oData) {
		var oScope = this;
		var par_src = this._getNodeId(src);
		var par_dest = this._getNodeId(dest);

		var body = '';

		CreateDialog(this.id+"_move_category_dialog", {
			width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: false,
			header: this._lang.get('_AREYOUSURE'),
			body: function() {
				var ajaxUrl = this._serverUrl + '&command=movefolder&src='+par_src+'&dest='+par_dest;
				return '<form method="POST" id="move_category_form" action="'+ajaxUrl+'">'
					+'<p>'+this._lang.get('_MOVE')+'</p>'
					+'</form>';
			},
			callback: function(o) {
				this.destroy();
				if (o.success) {
					oScope._tree.popNode(src);
					src.appendTo(dest);
					oScope.refresh();
					oScope._alternateLines();
				}
				DataTable_competences_table.refresh();
			}
		}).call(this);

		oData.oDD.backToStart();
	},

	modifyCategory: function(nodeToRename) {
		CreateDialog(this.id+"_modifyCategory_popup", {
			//width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: true,
			ajaxUrl: this._getUrl('mod_category&id='+this._getNodeId(nodeToRename)),//this.href,
			callback: function(o) {
				this.destroy();
				nodeToRename.getContentEl().innerHTML = o.new_name || "";
				DataTable_competences_table.refresh();
			},
			renderEvent: Competences.dialogRenderEvent
		}).call(this);
	},


	toString: function() {
		return "CompetencesCategoriesTree '"+this.id+"'";
	}
});