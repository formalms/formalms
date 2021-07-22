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

YAHOO.widget.FolderNode = function(oData, oParent, expanded, checked) {
	YAHOO.widget.FolderNode.superclass.constructor.call(this,oData,oParent,expanded);
	//other oData parameters ...
	if (oData.checkable) this.checkable = true;
	if (oData.radioButtons) this._useRadioButtons = true;
	if (oData.options) this._loadOptions(oData.options);
	if (oData.style) this._subStyle=oData.style;
	if (oData.simple) this._simple=oData.simple;

	if (oData.preExpanded) { this.expanded=true; this.dynamicLoadComplete=true; this._dynLoad=false; }
};

YAHOO.extend(YAHOO.widget.FolderNode, YAHOO.widget.HTMLNode, {

	/**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "FoldertNode"
     */
	_type: "FolderNode",

	_subStyle: false,
	
	_useRadioButtons: false,
	
	_simple: false,

	_radioStatus: 0,

	checkable: false,
	
	/**
     * True if checkstate is 1 (some children checked) or 2 (all children checked),
     * false if 0.
     * @type boolean
     */
	checked: false,

	_options: [],

	_loadOptions: function(options) {
		if (YAHOO.lang.isArray(options)) { this._options = options; return true; } else return false;
	},

	/**
     * Get the markup for the node.  Overridden.
     * @method getNodeHtml
     * @return {string} The HTML that will render this node.
     */
	getNodeHtml: function() {
		var sb = [];

		sb[sb.length] = '<table border="0" cellpadding="0" cellspacing="0" class="ygtvdepth' + this.depth + '">';
		sb[sb.length] = '<tr class="ygtvrow">';

		for (var i=0;i<this.depth;++i) {
			sb[sb.length] = '<td class="' + this.getDepthStyle(i) + '"><div class="ygtvspacer"></div></td>';
		}

		if (this.hasIcon) {
			sb[sb.length] = '<td';
			sb[sb.length] = ' id="' + this.getToggleElId() + '"';
			sb[sb.length] = ' class="' + this.getStyle() + (this._subStyle != false ? '_'+this._subStyle : '') + '"';
			sb[sb.length] = '><a href="#" class="ygtvspacer">&nbsp;</a></td>';
		}

		sb[sb.length] = '<td';
		sb[sb.length] = ' id="' + this.contentElId + '"';
		sb[sb.length] = ' class="' + this.contentStyle  + ' ygtvcontent" ';
		sb[sb.length] = (this.nowrap) ? ' nowrap="nowrap" ' : '';
		sb[sb.length] = ' >';
		sb[sb.length] = this.getContentHtml();
		sb[sb.length] = '</td>';

		if (this._options.length>0) {
			sb[sb.length] = '<td';
			sb[sb.length] = ' id="' + this.optionsElId + '"';
			sb[sb.length] = ' class="' + this.optionsStyle  + ' ygtvcontent" ';
			sb[sb.length] = (this.nowrap) ? ' nowrap="nowrap" ' : '';
			sb[sb.length] = ' >';
			sb[sb.length] = this.getOptionsHtml();
			sb[sb.length] = '</td>';
		}

		if (this._useRadioButtons) {
			sb[sb.length] = '<td';
			sb[sb.length] = ' id="' + this.radioElId + '"';
			sb[sb.length] = ' class="' + this.radioStyle  + ' ygtvcontent" ';
			sb[sb.length] = (this.nowrap) ? ' nowrap="nowrap" ' : '';
			sb[sb.length] = ' >';
			sb[sb.length] = this.getRadioHtml();
			sb[sb.length] = '</td>';
		}

		sb[sb.length] = '</tr>';
		sb[sb.length] = '</table>';

		return sb.join("");
	},

	optionsElId: '',
	optionsStyle: 'ygtvoptions',

	getOptionsHtml: function() {
		var optionId, href, alt, title, output = '';
		for (var i=0; i<this._options.length; i++) {
			optionId = this.tree.id+'_'+this.tree._containerObject._getNodeId(this)+'_'+this._options[i].id;
			if (this._options[i].href) href = ' href="'+this._options[i].href+'"'; else href = ' href="javascript:;"';
			if (this._options[i].alt) alt = ' alt="'+this._options[i].alt+'"'; else alt = '';
			if (this._options[i].alt) title = ' title="'+this._options[i].alt+'"'; else title = '';
			output += '<a id="'+optionId+'" class="node_option" '+href+' '+title+'><img src="'+this.tree._containerObject._iconPath+this._options[i].icon+'"'+alt+' /></a>';
		}
		return output;
	},

	getOptionsEl: function() {
		var container = this.getEl();
		var arr_option = YAHOO.util.Dom.getElementsByClassName('ygtvoptions', 'td', container);
		if (YAHOO.lang.isArray(arr_option))
			if (arr_option.length>0)
				return arr_option[0];
			else
				return false;
		else
			return false;
	},

	getOptionById: function(optionId) {
		for (var i=0; i<this._options.length; i++) {
			if (this._options[i].id == optionId) return this._options[i];
		}
		return false;
	},

	updateOptions: function(options) {
		if (this._loadOptions(options)) {
			this.getOptionsEl().innerHTML = this.getOptionsHtml(); //should not need listeners
		}
	},

	setLabel: function(label) {
		this.getContentEl().innerHTML = label;
	},

	getLabel: function() {
		var el = this.getContentEl();
		if (this.tree._containerObject._draggableNodes) return el.firstChild.innerHTML;
		return el.innerHTML;
	},

	//--------------------------------------------------------------------------

	radioElId: '',
	radioStyle: 'ygtvradio',

	getRadioHtml: function() {
		var oTree = this.tree._containerObject;
		var os = oTree.oSelector;
		var n = oTree._getNodeId(this);

		var val_1 = oTree._yes_formatter(n), val_2 = oTree._inherit_formatter(n);

		if (os.isset(val_1)) { this._radioStatus = 1; }
		if (os.isset(val_2)) { this._radioStatus = 2; }

		var is_descendant = false, tnode = this;
		while (tnode.parent && !is_descendant && !this.isRoot()) {
			if (tnode.parent._radioStatus==2) is_descendant = true;
			tnode = tnode.parent;
		}

		var output =  '<input class="radiosel" id="sel_0_'+n+'" type="radio" value="0" name="sel['+n+']" '
						+(this._radioStatus==0 ? 'checked="checked"' : '')+' '
						+( is_descendant ? 'disabled="disabled"' : '')+' />'
						+'<label for="sel_0_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_NO')+'</label>';
		output += '&nbsp;&nbsp;';
		if(this.depth != 0) {
			output += '<input class="radiosel" id="sel_1_'+n+'" type="radio" name="sel['+n+']" '
							+'value="1" '+(this._radioStatus==1 ? 'checked="checked"' : '')+' '
							+( is_descendant ? 'disabled="disabled"' : '')+' />'+
							'<label for="sel_1_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_YES')+'</label>';
			output += '&nbsp;&nbsp;';
		} else {
			if(this._radioStatus==1) this._radioStatus=2;
			output += '<input style="visibility:hidden" class="radiosel" id="sel_1_'+n+'" type="radio" name="sel['+n+']" '
							+'value="1" '+(this._radioStatus==1 ? 'checked="checked"' : '')+' '
							+( is_descendant ? 'disabled="disabled"' : '')+' />'+
							'<label style="visibility:hidden" for="sel_1_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_YES')+'</label>';
			output += '&nbsp;&nbsp;';
		}
                if(!this._simple){
                        output += '<input class="radiosel" id="sel_2_'+n+'" type="radio" name="sel['+n+']" '
                                                        +'value="2" '+(this._radioStatus==2 ? 'checked="checked"' : '')+' '
                                                        +( is_descendant ? 'disabled="disabled"' : '')+' />'
                                                        +'<label for="sel_2_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_INHERIT')+'</label>';
                }
		return output;
	},

	//--------------------------------------------------------------------------

	isVisible: function() {
		if (this.isRoot()) return true;
		var node = this.parent;
			
		while (!node.isRoot()) {
			if (node.expanded)
				node = node.parent;
			else
				return false;
		}
		return true;
	}

});