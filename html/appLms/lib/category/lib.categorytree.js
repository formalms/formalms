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

// Drag &Drop node class --------------------------------------------
YAHOO.util.DDNode = function(id, sGroup) {
	YAHOO.util.DDNode.superclass.constructor.apply(this, arguments);
};

YAHOO.extend(YAHOO.util.DDNode, YAHOO.util.DDProxy, {

	/**
	 * Event handler, is called after 2 sec of mousedown on the object or 3pixel drag
	 * @param (Event) the drag event
	 */
	startDrag: function(e) {
		this.startPos = YAHOO.util.Dom.getXY(this.id);
	},

	/**
	 * Event handler, is called when the dragged element is hover a target element
	 * @param (Event) the drag event
	 */
	onDragEnter: function(e, id) {
		if(id == this.id) return;
		this.highlight(id);
	},

	/**
	 * Event handler, is called when the dragged element is relased on a non target element
	 * @param (Event) the drag event
	 */
	onInvalidDrop: function(e) {
		this.backToStart();
	},

	/**
	 * Event handler, is called when the dragged element leave a target element
	 * @param (Event) the drag event
	 */
	onDragOut: function(e, id) {
		if(id == this.id) return;
		YAHOO.util.Dom.get(id).style.background = this.prev_bck;
	},

	/**
	 * Event handler, is called when the dragged element is released on a target element
	 * @param (Event) the drag event
	 */
	onDragDrop: function(e, id) {

		if(id != this.id) {
			// move the tree folder
			stree = src_node.tree.myManager;
			
			var src_node = stree.tree.getNodeByElement(YAHOO.util.Dom.get(this.id));
			var dest_node = stree.tree.getNodeByElement(YAHOO.util.Dom.get(id));
			var copy_from = src_node.parent;

			dest_node.isLeaf = false;
			var sid = src_node.contentElId;
			var label = src_node.label_name;

			stree.tree.removeNode(src_node, false);
			stree.addNode(dest_node, sid, label, true, false);

			if(copy_from.children.length == 0) copy_from.isLeaf = false;

			copy_from.refresh();
			dest_node.refresh();

			this.de_highlight(id);

			//re-set the drag handler
			this.setListenerForNode(stree.root);
		}

		this.backToStart();
	},

	/**
	 * Reset all the drag & drop listener for a node and his childrens nodes
	 * @private
	 */
	setListenerForNode: function(node) {

		if(!node.dd_obj) node.dd_obj = new YAHOO.util.DDNode('node_'+node.contentElId, "treegroup");
		if(!node.dt_obj) node.dt_obj = new YAHOO.util.DDTarget('node_'+node.contentElId, "treegroup");

		for(var i=0; i<node.children.length; i++) {

			if(!node.children[i].dd_obj) node.children[i].dd_obj = new YAHOO.util.DDNode('node_'+node.children[i].contentElId, "treegroup");
			if(!node.children[i].dt_obj) node.children[i].dt_obj = new YAHOO.util.DDTarget('node_'+node.children[i].contentElId, "treegroup");

			if(node.children[i].children.length != 0) {

				this.setListenerForNode(node.children[i]);
			}

		}

	},

	/**
	 * Higlight an element
	 * @param (string) id
	 */
	highlight: function(id) {

		this.prev_bck = YAHOO.util.Dom.get(id).style.background;
		YAHOO.util.Dom.get(id).style.background = '#efefef';
	},

	/**
	 * Remove the iglight from the element
	 * @param (string) id
	 */
	de_highlight: function(id) {

		YAHOO.util.Dom.get(id).style.background = this.prev_bck;
	},

	/**
	 * Animate the return of the dragged item to his original position
	 * @param (string) id
	 */
	backToStart: function() {

		new YAHOO.util.Motion(this.id,
			{ points: { to: this.startPos } },
			0.3,
			YAHOO.util.Easing.easeOut
		).animate();
	}

});

function CourseCategoryTree(tree_id, oConfig) {

	this.tree_id = tree_id;
	this.config = oConfig;
  this.oLangs = oConfig.langs;

	this.orgSelector = new ElemSelector('orgsel_');
	this.orgSelector.initSelection(this.config.initial_selection);

	this.tree = new YAHOO.widget.TreeView(this.tree_id);
	this.root = this.tree.getRoot();

	if(this.config.use_form_input) {
		//an input hidden with selected checkboxes or radiobuttons
		var selection = YAHOO.util.Dom.get(this.tree_id+'_input');
		YAHOO.util.Event.addListener(selection.form, 'submit', function(e) {
			YAHOO.util.Dom.get(this.tree_id+'_input').value = this.orgSelector.toString();
		}, this, true);
	}
	this.tree.myManager = this;
	this.tree.setDynamicLoad(this.dynamicLoadTreeNodeData, 1);

	// load a visual representation of the root node of the tree
	var rootNode = this.addNode(this.root, 0, this.oLangs._ROOT, false, true);

	// and expand it in order to have the first level loaded
	rootNode.expand();

	// draw the tree
	this.tree.draw();
}

CourseCategoryTree.prototype = {

	_USE_RADIOBUTTONS: "radiobutton",

	_USE_CHECKBOXES:  "checkbox",

	_LOAD_FAILURE: "Load failure.", //this.oLangs._AJAX_FAILURE ...

	/**
	 * Keeps the id of the markup element
	 * @property tree_id
	 * @type String
	 */
	tree_id: null,

	/**
	 * The YAHOO TreeView istance
	 * @property tree
	 * @type YAHOO.widget.TreeView
	 */
	tree: null,

	/**
	 * The YAHOO Root node istance
	 * @property root
	 * @type YAHOO.widget.RootNode
	 */
	root: null,

	/**
	 * Keeps the configuration data
	 * @property id
	 * @type String
	 */
	config: {

		/**
		 * The ajax server url
		 * @property request_url
		 * @type String
		 */
		request_url: null,

		/**
		 * @property selector_columns_type
		 * @type Array
		 */
		selector_columns_type: null,

		/**
		 * If true create the hidden input field that will contain the current selection
		 * @property use_form_input
		 * @type Boolean
		 */
		use_form_input: false,

		/**
		 * If true make the nodes draggable
		 * @property use_form_input
		 * @type Boolean
		 */
		draggable_nodes: false,

		/**
		 * If true use the context menu (right click) in order to allow the mod of the tree
		 * @property use_form_input
		 * @type Boolean
		 */
		use_context_menu: false,

		/**
		 * The elements selected at start
		 * @property
		 * @type Array
		 */
		initial_selection: []

	},

	radiobutton_listener_no: function(e, man_tree) {
		var node = man_tree.tree.getNodeByElement(this);
		if (man_tree.orgSelector.isset(node.contentElId)) man_tree.orgSelector.remsel(node.contentElId);
		if (man_tree.orgSelector.isset(node.contentElId+"d")) {
			man_tree.orgSelector.remsel(node.contentElId+"d");
			man_tree.revertRadioForNode(node, false);
		}
	},

	radiobutton_listener_yes: function(e, man_tree) {
		var node = man_tree.tree.getNodeByElement(this);
		if (man_tree.orgSelector.isset(node.contentElId+"d")) {
			man_tree.orgSelector.remsel(node.contentElId+"d");
			man_tree.revertRadioForNode(node, false);
		}
		man_tree.orgSelector.addsel(node.contentElId);
	},

	radiobutton_listener_inherit: function(e, man_tree) {
		var node = man_tree.tree.getNodeByElement(this);
		if (man_tree.orgSelector.isset(node.contentElId)) man_tree.orgSelector.remsel(node.contentElId);
		man_tree.orgSelector.addsel(node.contentElId+"d");
		man_tree.revertRadioForNode(node, true);
	},

	revertRadioForNode: function(node, new_status) {
		for(var i=0; i<node.children.length; i++) {
			YAHOO.util.Dom.get('sel_0_'+node.children[i].contentElId).disabled = new_status;
			YAHOO.util.Dom.get('sel_1_'+node.children[i].contentElId).disabled = new_status;
			YAHOO.util.Dom.get('sel_2_'+node.children[i].contentElId).disabled = new_status;
			if(!YAHOO.util.Dom.get('sel_2_'+node.children[i].contentElId).checked) {
				this.revertRadioForNode(node.children[i], new_status);
			}
		}
	},

	radioButton: {
		setListeners: function(node_id, tree) {
			YAHOO.util.Event.addListener('sel_0_'+node_id, 'click', tree.radiobutton_listener_no, tree);
			YAHOO.util.Event.addListener('sel_1_'+node_id, 'click', tree.radiobutton_listener_yes, tree);
			YAHOO.util.Event.addListener('sel_2_'+node_id, 'click', tree.radiobutton_listener_inherit, tree);
		},
		cols: [{
			name: '',
			formatter: function(oTree, n, l, d, os) {
				return '<input class="radiosel" id="sel_0_'+n+'" type="radio" value="0" name="sel['+n+']" '
						+((!os.isset(n) && !os.isset(n+"d")) ? 'checked="checked"' : '')+' '
						+( d ? 'disabled="disabled"' : '')+' />'
						+'<label for="sel_0_'+n+'">'+oTree.oLangs._NO+'</label>';
			}
		}, {
			name: '',
			formatter: function(oTree, n, l, d, os) {
				return '<input class="radiosel" id="sel_1_'+n+'" type="radio" name="sel['+n+']" value="1" '+(os.isset(n) ? 'checked="checked"' : '')+' '+( d ? 'disabled="disabled"' : '')+' /><label for="sel_1_'+n+'">'+oTree.oLangs._YES+'</label>';
			}
		}, {
			name: '',
			formatter: function(oTree, n, l, d, os) {
				return '<input class="radiosel" id="sel_2_'+n+'" type="radio" name="sel['+n+']" value="2" '+(os.isset(n+"d") ? 'checked="checked"' : '')+' '+( d ? 'disabled="disabled"' : '')+' /><label for="sel_2_'+n+'">'+oTree.oLangs._INHERIT+'</label>';
			}
		}]
	},

	checkbox_listener: function(e, man_tree) {
		var node = man_tree.tree.getNodeByElement(this);
		if (this.checked)
			man_tree.orgSelector.addsel(node.contentElId);
		else
			man_tree.orgSelector.remsel(node.contentElId);
	},

	checkBox: {
		setListeners: function(node_id, tree) {
			YAHOO.util.Event.addListener('sel_'+node_id, 'click', tree.checkbox_listener, tree);
		},
		cols: [ {
			name: '',
			formatter: function(oTree, n, l, d, os) {
				return '<input type="checkbox" name="sel['+n+']" id="sel_'+n+'" value="'+n+'" '+( os.isset(n) ? 'checked="checked"' : '')+' />';
			}
		} ]
	},
	
	alternateLines: function(node) {
		var tables = YAHOO.util.Dom.get(this.tree_id).getElementsByTagName('table');
		for(var i=0;i < tables.length;i++) {

			if(i%2) tables[i].className = 'table_odd';
			else tables[i].className = '';
		}
	},



	clearSelection: function() {
		this.orgSelector.reset();
		var rootNode = this.root;//tree.getRoot();
		rootId=0;
		YAHOO.util.Dom.get('sel_0_'+rootId).checked = true;
		YAHOO.util.Dom.get('sel_1_'+rootId).checked = false;
		YAHOO.util.Dom.get('sel_2_'+rootId).checked = false;

		var clearNode = function(node) {
			for(var i=0; i<node.children.length; i++) {
				YAHOO.util.Dom.get('sel_0_'+node.children[i].contentElId).checked = true;
				YAHOO.util.Dom.get('sel_1_'+node.children[i].contentElId).checked = false;
				YAHOO.util.Dom.get('sel_2_'+node.children[i].contentElId).checked = false;
				clearNode(node.children[i]);
			}
		}

		clearNode(rootNode)
	},

	/**
	 * This function will add a node with all the other listenere and so on that we need
	 * @param (add_to_node) the parent node
	 * @param (node_id) the id of the node
	 * @param (label) the label of the node
	 * @param (is_leaf) if the node is a leaf
	 * @param (is_root) if the node is the represtation of the root node
	 */
	addNode: function(add_to_node, node_id, label, is_leaf, is_root) {
		var temp= '',
			subColumns = null,
			nodelabel = '',
			floatdir = 'left',
			disable = false;

		if(is_root == undefined) is_root = false;
		else is_root = true;

		switch (this.config.selector_columns_type) {
			case this._USE_CHECKBOXES: {
				subColumns = this.checkBox;
				nodelabel = '<label id="label_'+node_id+'" for="sel_'+node_id+'">'+label+'</label>';
				floatdir = 'left';
			}break;
			case this._USE_RADIOBUTTONS: {
				subColumns = this.radioButton;
				nodelabel = '<span id="label_'+node_id+'">'+label+'</span>';
				floatdir = 'right';
				if(!is_root) {
					if(add_to_node && add_to_node.contentElId != undefined
						&& ( YAHOO.util.Dom.get('sel_2_'+add_to_node.contentElId).disabled
						|| YAHOO.util.Dom.get('sel_2_'+add_to_node.contentElId).checked
						) ) {

						disable = true;
					}
				}
			}break;
			default: {
				subColumns = [];
				nodelabel = '<span id="label_'+node_id+'">'+label+'</span>';
				floatdir = 'right';
			}
		}
		if (subColumns.cols)
		if (subColumns.cols.length>0) {
			temp += '<div id="subcolumns_'+node_id+'" style="float:'+floatdir+';">'
				+ '<ul class="selectionlist">';
			for (var i=0; i<subColumns.cols.length; i++) {
				temp += '<li>'+subColumns.cols[i].formatter(this, node_id, is_leaf, disable, this.orgSelector)+'</li>';
			}
			temp += '</ul>'
				+ '</div>';
		}
		var new_node = new YAHOO.widget.HTMLNode('<div id="node_'+node_id+'">'+temp+nodelabel+'</div>', add_to_node, false, (is_root?false:true));
		new_node.contentElId = node_id;
		new_node.label_name = label;
		new_node.isLeaf = is_leaf;
		new_node.nowrap = true;

		if (subColumns.setListeners) subColumns.setListeners(node_id, this);

		if (this.config.draggable_nodes) {
			if(!is_root) new YAHOO.util.DDNode('node_'+node_id, "treegroup");
			new YAHOO.util.DDTarget('node_'+node_id, "treegroup");
		}

		return new_node;
	},
	
	/**
	 * Callback function that load the childrens nodes
	 * @property dynamicLoadTreeNodeData
	 * @type function
	 * @private
	 */
	dynamicLoadTreeNodeData: function(node, fnLoadComplete)  {

		var tree = this.tree.myManager;
		var callback = {
			success: function(o) {
				try {
					var oResults = YAHOO.lang.JSON.parse(o.responseText);
				} catch(e) {
					var tempNode = new YAHOO.widget.HTMLNode(this._LOAD_FAILURE, node, false, false);
					tempNode.isLeaf = true;
					o.argument.fnLoadComplete();
					var oResults = [];
				}
				for (var i=0, j=oResults.length; i<j; i++) {
					this.addNode(node, oResults[i].id, oResults[i].label, oResults[i].is_leaf);
				}

				//When we're done creating child nodes, we execute the node's loadComplete callback method
				o.argument.fnLoadComplete();
			},
			failure: tree.ajaxFailure,
			argument: {
				"node": node,
				"fnLoadComplete": fnLoadComplete
			},
			timeout: 7000,
			scope: tree
		};

		// Get the node's label and urlencode it; this is the word/s on which we'll search for related words:
		YAHOO.util.Connect.asyncRequest('GET', tree.config.request_url + "&op=expand&query=" + encodeURI(node.contentElId), callback);
	},

	ajaxFailure: function(o) {
		YAHOO.util.Dom.get(this.tree_id+'_status').innerHTML = this.oLangs._AJAX_FAILURE;
	}
}