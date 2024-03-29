



var SelectorTree = function(id, config) {
	if (config.canSelectRoot === false) {
		this.canSelectRoot = false;
	}
	
	if (config.initialSelectorData) {
		this.oSelector.initSelection(config.initialSelectorData, true); //initialize nodes selection for radio, no events required
	}

	SelectorTree.superclass.constructor.call(this, id, config);
}

YAHOO.lang.extend(SelectorTree, FolderTree, {

	oSelector : new ElemSelector(),
	canSelectRoot: true,

	_setSelectedNode: function(node) {
		if (!node) return;
		var is_leaf =  node.isLeaf, dyn_load = node.dynamicLoadComplete;

		if (this._selectedNode != null) {
			YAHOO.util.Dom.removeClass(this._selectedNode.getContentEl(), "label_selected");
		}

		YAHOO.util.Dom.addClass(node.getContentEl(), "label_selected");
		this._selectedNode = node;
		this._selectedNodeId = this._getNodeById(node);
		YAHOO.util.Dom.get('current_selected_node_'+this.id).value = this.selectedNodeId;
		this.onSelectNode(node);

		if (dyn_load || is_leaf) {
			YAHOO.util.Connect.asyncRequest("post", this._serverUrl, {
				success: function (oResponse) {}//, failure: this.connectionFailure
			}, "command=set_selected_node&node_id="+this._getNodeId(node));
		}
	},

	

	_addNode : function(parentNode, id, label, isLeaf, isRoot, options, style, others) {
		var dd_id = 'dd_'+id;
		var params = {
			html: ( this._draggableNodes ? '<div id="'+dd_id+'">'+label+'</div>' : label ),
			checkable: (isRoot ? false : this._checkableNodes),
			radioButtons: (isRoot && this.canSelectRoot==false ? false : true),
			options: (options ? options : false),
			style: (style ? style : false),
                        simple: this._simple
		};
		if (others) params = params || others;

		var new_node = new YAHOO.widget.FolderNode(params, parentNode, isRoot, true);
		
		new_node.hasIcon = !isRoot;
		new_node.contentElId = this.id+'_'+id;
		new_node.label_name = label;
		new_node.isLeaf = isLeaf;
		new_node.nowrap = true;

		//assign events to radiobuttons
		var $E = YAHOO.util.Event;
		$E.addListener('sel_0_'+id, "click", this.radiobutton_listener_no, new_node, true);
		$E.addListener('sel_1_'+id, "click", this.radiobutton_listener_yes, new_node, true);
		$E.addListener('sel_2_'+id, "click", this.radiobutton_listener_inherit, new_node, true);

		if (this._draggableNodes) {
			if (!isRoot) {
				var dnode = new YAHOO.util.DDNode(dd_id, this.id+"_group");
				dnode.stree = this;
			}
			new YAHOO.util.DDTarget(dd_id, this.id+"_group");
		}

		return new_node;
	},


	_nodeClick: function(oClick) { //scope: treeview object
		var node = oClick.node;
		var target = YAHOO.util.Event.getTarget(oClick.event);
		var scope = this._containerObject;

		//if (node===scope._rootNode) { scope._setSelectedNode(node); return false; }

		var isEventToPropagate = true;

		switch (scope._checkClasses(target, ['ygtvhtml', 'ygtvroot', 'ygtvcheck0', 'ygtvcheck1', 'ygtvcheck2', 'ygtvradio'])) {
			case 'ygtvroot':
			case 'ygtvhtml': {
				scope._setSelectedNode(node);
				var o = scope._nodeClickEvent;
				o.eventFunction.call(o.eventScope, node);
				if (node!=scope.getRoot && node.expanded) isEventToPropagate = false;
				if (node==scope.getRoot()) isEventToPropagate = false;
				if (node.dynamicLoadComplete);
			} break;

			case 'ygtvradio': {
				//set the radiobutton and eventually the child nodes radiobuttons
				isEventToPropagate = false;
			} break;

			default: {
				// ... continue propagating event ...
			} break;
		}

		return isEventToPropagate;
   },

	//uncheck and enable all nodes
	unselectAll: function() {
		this.oSelector.reset();
		this._walkTree(this.getRoot(), function(node) {
			var el, id = this._getNodeId(node);
			node._radioStatus = 0;
			el = YAHOO.util.Dom.get('sel_0_'+id);
			el.checked = true;
			el.disabled = false;
			el = YAHOO.util.Dom.get('sel_1_'+id);
			el.checked = false;
			el.disabled = false;
			el = YAHOO.util.Dom.get('sel_2_'+id);
			el.checked = false;
			el.disabled = false;
		});
	},


	toString: function() { return "SelectorTree '"+this.id+"'"; },

	//radiobutton events

	_yes_formatter: function(value) {
		return value.split('_')[0]; //return value;
	},

	_inherit_formatter: function(value) {
		return value.split('_')[1]; //return value + "d";
	},


	radiobutton_listener_no: function(e) {
		var node = this;
		node._radioStatus = 0;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		var val_1 = tree._yes_formatter(id), val_2 = tree._inherit_formatter(id);
		if (tree.oSelector.isset(val_1)) tree.oSelector.remsel(val_1);
		if (tree.oSelector.isset(val_2)) {
			tree.oSelector.remsel(val_2);
			tree.revertRadioForNode(node, false);
		}
	},

	radiobutton_listener_yes: function(e) {
		var node = this;
		node._radioStatus = 1;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		var val_1 = tree._yes_formatter(id), val_2 = tree._inherit_formatter(id);
		if (tree.oSelector.isset(val_2)) {
			tree.oSelector.remsel(val_2);
			tree.revertRadioForNode(node, false);
		}
		tree.oSelector.addsel(val_1);
	},

	radiobutton_listener_inherit: function(e) {
		var node = this;
		node._radioStatus = 2;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		var val_1 = tree._yes_formatter(id), val_2 = tree._inherit_formatter(id);
		if (tree.oSelector.isset(val_1)) tree.oSelector.remsel(val_1);
		tree.oSelector.addsel(val_2);
		tree.revertRadioForNode(node, true);
	},

	revertRadioForNode: function(node, new_status) {
		var id, tree = node.tree._containerObject;
		for(var i=0; i<node.children.length; i++) {
			id = tree._getNodeId(node.children[i]);
			YAHOO.util.Dom.get('sel_0_'+id).disabled = new_status;
			YAHOO.util.Dom.get('sel_1_'+id).disabled = new_status;
			YAHOO.util.Dom.get('sel_2_'+id).disabled = new_status;
			if(!YAHOO.util.Dom.get('sel_2_'+id).checked) {
				this.revertRadioForNode(node.children[i], new_status);
			}
		}
	}

}
);
