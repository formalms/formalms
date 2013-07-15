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

var ListSelector = function(id, oConfig) {
	this.id = id;
	this._init(oConfig);
};


ListSelector.prototype = {

	ajaxUrl: '',

	imagePath: '',

	oLangs: null,

	elSelectionList: null,
	elInput: null,
	elContainer: null,
	elSelectionInput: null,

	oDataSource: null,
	oAutoComplete: null,

	itemsSelector: null,
	namesSelector: {},

	fieldToUse: '',
	maxResults: 100,

	_init: function(oConfig) {
		var $D = YAHOO.util.Dom;

		if (oConfig.imagePath) this.imagePath = oConfig.imagePath;

		this.oLangs = new LanguageManager((oConfig.langs ? oConfig.langs : {}));

		if (oConfig.useLocalData) {
			this.oDataSource = new YAHOO.util.FunctionDataSource(oConfig.matchFunction);
			this.localData = oConfig.localData;
		} else {
			this.ajaxUrl = oConfig.ajaxUrl;
			this.oDataSource = new YAHOO.util.XHRDataSource(this.ajaxUrl);
			this.oDataSource.connMethodPost = true;
			this.oDataSource.responseType = YAHOO.util.XHRDataSource.TYPE_JSON;
			this.oDataSource.responseSchema = {resultsList: "results", fields: oConfig.fields};
		}

		if (oConfig.fieldToUse) this.fieldToUse = oConfig.fieldToUse;

		this.elSelectionList = $D.get(oConfig.selectionList);
		this.elInput = $D.get(oConfig.input);
		this.elInput.value = "";
		this.elContainer = $D.get(oConfig.container);
		this.elSelectionInput = $D.get(oConfig.selectionInput);

		if (oConfig.maxResults) this.maxResults = oConfig.maxResults;
		var oAC = new YAHOO.widget.AutoComplete(this.elInput, this.elContainer, this.oDataSource);

		oAC.resultTypeList = false;
		oAC.alwaysShowContainer = true;
		oAC.minQueryLength = 0;
		oAC.autoHighlight = false;
		oAC.suppressInputUpdate = true;
		oAC.maxResultsDisplayed = this.maxResults;

		oAC.formatResult = (oConfig.formatResult ? oConfig.formatResult : this.formatResult);
		oAC.itemSelectEvent.subscribe(this.selectEventHandler);

		this.itemsSelector = new ElemSelector('list_');
		if (oConfig.initialSelection) {
			var x, id, arr_items = [];
			for (x in oConfig.initialSelection) {
				id = x.replace("item_", "");
				arr_items.push(id);
				this.namesSelector["n_"+id] = oConfig.initialSelection[x];
			}
			this.itemsSelector.initSelection(arr_items);
		}

		oAC._oContainerObject = this;
		this.oAutoComplete = oAC;

		if (!oConfig.useLocalData)
			oAC.generateRequest = function(sQuery) { return '&op=search&query='+sQuery+'&max='+this.maxResults;	}

		//form submit event
		YAHOO.util.Event.addListener(oConfig.form, "submit",function(e) {
			this.elSelectionInput.value = this.itemsSelector.toString();
		}, this, true);

		this.oAutoComplete.sendQuery("");
		this.showSelectionList();
	},

	//refresh selected items list
	showSelectionList: function() {
		var x, id_el, id_num, output = [], addname;
		for(x in this.namesSelector) {
			id_el = "remove_selected_"+x.replace("n_", "");
			addname = '<a id="'+id_el+'" title="'+this.oLangs.get('_CLICK_TO_REMOVE')+'" href="javascript:;">'+this.namesSelector[x]+'</a>';
			output.push(addname);
		}
		YAHOO.util.Event.purgeElement(this.elSelectionList, true);
		this.elSelectionList.innerHTML = (output.length>0 ? output.join(",&nbsp;") : this.oLangs.get('_NO_SELECTED_ITEMS'));
		for(x in this.namesSelector) {
			id_num = x.replace("n_", "");
			id_el = "remove_selected_"+id_num;
			YAHOO.util.Event.addListener(id_el, "click", this.removeDocsSingle, {scope: this, id: id_num}, true);
		}
	},

	//remove a selected item
	removeDocsSingle: function(e) {
		this.scope.itemsSelector.remsel(this.id);
		delete this.scope.namesSelector["n_"+this.id];
		var el = YAHOO.util.Dom.get("list_item_"+this.id);
		if (el) YAHOO.util.Dom.removeClass(el, "list_selected");
		this.scope.showSelectionList();
	},

	//select item function
	selectEventHandler: function(sType, aArgs) {
		var oAC = aArgs[0], elLI = aArgs[1], oData = aArgs[2];
		var scope = oAC._oContainerObject;
		scope.itemsSelector.addsel(oData.id);
		scope.namesSelector["n_"+oData.id] = oData[scope.fieldToUse];
		YAHOO.util.Dom.addClass(elLI.firstChild, "list_selected");
		scope.showSelectionList();
	},

	//highlith function
	highlightMatch: function(full, snippet, matchindex) {
		return full.substring(0, matchindex) + '<span class="highlight">' +
			full.substr(matchindex, snippet.length) +
			"</span>" + full.substring(matchindex + snippet.length);
	},

	//formatter function
	formatResult: function(oResultData, sQuery, sResultMatch) {
		var query = sQuery.toLowerCase(),
			title = oResultData.title,
			description = oResultData.description,
			titleMatchIndex = title.toLowerCase().indexOf(query),
			descriptionMatchIndex = description.toLowerCase().indexOf(query);

		var scope = this._oContainerObject;
		var display_title = title, display_description = description;

		if (titleMatchIndex>-1) { display_title = scope.highlightMatch(title, query, titleMatchIndex); }
		if (descriptionMatchIndex>-1) { display_description = scope.highlightMatch(description, query, descriptionMatchIndex); }

		var div_class = (scope.namesSelector["n_"+oResultData.id] ? ' class="list_selected"' : "");
		var lang = oResultData.language;
		var img = '<img src="'+scope.imagePath+'language/'+lang+'.png" alt="'+lang+'" title="'+lang+'" style="float:left" />';
		return '<div id="list_item_'+oResultData.id+'"'+div_class+'>'+img+'<h3>'+display_title+'</h3><p>'+display_description+'</p></div>';
	},

	toString: function() { return 'ListSelector '+this.id; }

};
