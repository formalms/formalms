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


/**
 * The check box marks a task complete.  It is a simulated form field
 * with three states ...
 * 0=unchecked, 1=some children checked, 2=all children checked
 * When a task is clicked, the state of the nodes and parent and children
 * are updated, and this behavior cascades.
 *
 * @extends YAHOO.widget.TextNode
 * @constructor
 * @param oData    {object}  A string or object containing the data that will
 *                           be used to render this node.
 * @param oParent  {Node}    This node's parent node
 * @param expanded {boolean} The initial expanded/collapsed state
 * @param checked  {boolean} The initial checked/unchecked state
 */

YAHOO.widget.FolderNode = function(oData, oParent, expanded, checked) {
	YAHOO.widget.FolderNode.superclass.constructor.call(this,oData,oParent,expanded);
	//other oData parameters ...
	if (oData.checkable) this.checkable=true;
	if (oData.radioButtons) this._useRadioButtons=true;
	if (oData.options) this._loadOptions(oData.options);
	if (oData.style) this._subStyle=oData.style;

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
	_radioStatus: 0,

	checkable: false,
	
	/**
     * True if checkstate is 1 (some children checked) or 2 (all children checked),
     * false if 0.
     * @type boolean
     */
	checked: false,

	/**
     * checkState
     * 0=unchecked, 1=some children checked, 2=all children checked
     * @type int
     */
	checkState: 0,

	taskNodeParentChange: function() {
	//this.updateParent();
	},

	setUpCheck: function(checked) {
		// if this node is checked by default, run the check code to update
		// the parent's display state
		if (checked && checked === true) {
			this.check();
		// otherwise the parent needs to be updated only if its checkstate
		// needs to change from fully selected to partially selected
		} else if (this.parent && 2 === this.parent.checkState) {
			this.updateParent();
		}

		/**
         * Custom event that is fired when the check box is clicked.  The
         * custom event is defined on the tree instance, so there is a single
         * event that handles all nodes in the tree.  The node clicked is
         * provided as an argument.  Note, your custom node implentation can
         * implement its own node specific events this way.
         *
         * @event checkClick
         * @for YAHOO.widget.TreeView
         * @param {YAHOO.widget.Node} node the node clicked
         */
		if (this.tree && !this.tree.hasEvent("checkClick")) {
			this.tree.createEvent("checkClick", this.tree);
		}

		this.tree.subscribe('clickEvent',this.checkClick);
		this.subscribe("parentChange", this.taskNodeParentChange);

	},

	/**
     * The id of the check element
     * @for YAHOO.widget.TaskNode
     * @type string
     */
	getCheckElId: function() {
		return "ygtvcheck" + this.index;
	},

	/**
     * Returns the check box element
     * @return the check html element (img)
     */
	getCheckEl: function() {
		return document.getElementById(this.getCheckElId());
	},

	/**
     * The style of the check element, derived from its current state
     * @return {string} the css style for the current check state
     */
	getCheckStyle: function() {
		return "ygtvcheck" + this.checkState;
	},

	/**
     * Invoked when the user clicks the check box
     */
	checkClick: function(oArgs) {
		var node = oArgs.node;
		var target = YAHOO.util.Event.getTarget(oArgs.event);
		if (YAHOO.util.Dom.hasClass(target,'ygtvspacer')) {
			node.logger.log("previous checkstate: " + node.checkState);
			if (node.checkState === 0) {
				node.check();
			} else {
				node.uncheck();
			}

			node.onCheckClick(node);
			this.fireEvent("checkClick", node);
			return false;
		}
	},

	/**
     * Override to get the check click event
     */
	onCheckClick: function() {
		this.logger.log("onCheckClick: " + this);
	},

	/**
     * Refresh the state of this node's parent, and cascade up.
     */
	updateParent: function() {
		var p = this.parent;

		if (!p || !p.updateParent) {
			this.logger.log("Abort udpate parent: " + this.index);
			return;
		}

		var somethingChecked = false;
		var somethingNotChecked = false;

		for (var i=0, l=p.children.length;i<l;i=i+1) {

			var n = p.children[i];

			if ("checked" in n) {
				if (n.checked) {
					somethingChecked = true;
					// checkState will be 1 if the child node has unchecked children
					if (n.checkState === 1) {
						somethingNotChecked = true;
					}
				} else {
					somethingNotChecked = true;
				}
			}
		}

		if (somethingChecked) {
			p.setCheckState( (somethingNotChecked) ? 1 : 2 );
		} else {
			p.setCheckState(0);
		}

		p.updateCheckHtml();
		p.updateParent();
	},

	/**
     * If the node has been rendered, update the html to reflect the current
     * state of the node.
     */
	updateCheckHtml: function() {
		if (this.parent && this.parent.childrenRendered) {
			this.getCheckEl().className = this.getCheckStyle();
		}
	},

	/**
     * Updates the state.  The checked property is true if the state is 1 or 2
     *
     * @param the new check state
     */
	setCheckState: function(state) {
		this.checkState = state;
		this.checked = (state > 0);
	},

	/**
     * Check this node
     */
	check: function() {
		this.logger.log("check");
		this.setCheckState(2);
		for (var i=0, l=this.children.length; i<l; i=i+1) {
			var c = this.children[i];
			if (c.check) {
				c.check();
			}
		}
		this.updateCheckHtml();
		this.updateParent();
	},

	/**
     * Uncheck this node
     */
	uncheck: function() {
		this.setCheckState(0);
		for (var i=0, l=this.children.length; i<l; i=i+1) {
			var c = this.children[i];
			if (c.uncheck) {
				c.uncheck();
			}
		}
		this.updateCheckHtml();
		this.updateParent();
	},

	_options: [],

	_loadOptions: function(options) {
		if (YAHOO.lang.isArray(options)) { this._options = options; return true; } else return false;
	},

    updateIcon: function() {
        if (this.hasIcon) {
            var el = this.getToggleEl();
            if (el) {
				el.className = el.className.replace(/\bygtv(([tl][pmn]h?)|(loading))/gi,this.getStyle());
            }
        }
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

		if (this.checkable) {
			sb[sb.length] = '<td';
			sb[sb.length] = ' id="' + this.getCheckElId() + '"';
			sb[sb.length] = ' class="' + this.getCheckStyle() + '"';
			sb[sb.length] = '><input type="checkbox" />&nbsp;</div></td>';
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
		var optionId, href, alt, output = '';
		for (var i=0; i<this._options.length; i++) {
			optionId = this.tree.id+'_'+this.tree._containerObject._getNodeId(this)+'_'+this._options[i].id;
			if (this._options[i].href) href = ' href="'+this._options[i].href+'"'; else href = ' href="javascript:;"';
			if (this._options[i].alt) alt = ' alt="'+this._options[i].alt+'"'; else alt = '';
			output += '<a id="'+optionId+'" class="node_option" '+href+'><img src="'+this.tree._containerObject._iconPath+this._options[i].icon+'"'+alt+' /></a>';
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
		var os = this.tree._containerObject.oSelector;
		var n = this.tree._containerObject._getNodeId(this), d = false;

		if (os.isset(n)) { this._radioStatus = 1; }
		if (os.isset(n+"d")) { this._radioStatus = 2; }

		var tnode = this;
		while (tnode.parent && !d) {
			if (tnode.parent._radioStatus==2) d = true;
			tnode = tnode.parent;
		}

		var output =  '<input class="radiosel" id="sel_0_'+n+'" type="radio" value="0" name="sel['+n+']" '
						+(this._radioStatus==0 ? 'checked="checked"' : '')+' '
						+( d ? 'disabled="disabled"' : '')+' />'
						+'<label for="sel_0_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_NO')+'</label>';
		output += '&nbsp;&nbsp;';
		output += '<input class="radiosel" id="sel_1_'+n+'" type="radio" name="sel['+n+']" '
						+'value="1" '+(this._radioStatus==1 ? 'checked="checked"' : '')+' '
						+( d ? 'disabled="disabled"' : '')+' />'+
						'<label for="sel_1_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_YES')+'</label>';
		output += '&nbsp;&nbsp;';
		output += '<input class="radiosel" id="sel_2_'+n+'" type="radio" name="sel['+n+']" '
						+'value="2" '+(this._radioStatus==2 ? 'checked="checked"' : '')+' '
						+( d ? 'disabled="disabled"' : '')+' />'
						+'<label for="sel_2_'+n+'">'+this.tree._containerObject._lang.get('_RADIO_INHERIT')+'</label>';
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