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



var SelectorTree = function(id, config) {
	SelectorTree.superclass.constructor.call(this, id, config);	
}

YAHOO.lang.extend(SelectorTree, FolderTree, {

	oSelector : null,


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
			radioButtons: true,
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


	toString: function() { return "SelectorTree '"+this.id+"'"; },

	//radiobutton events

	radiobutton_listener_no: function(e) {
		var node = this;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		if (tree.oSelector.isset(id)) tree.oSelector.remsel(id);
		if (tree.oSelector.isset(id+"d")) {
			tree.oSelector.remsel(id+"d");
			tree.revertRadioForNode(node, false);
		}
	},

	radiobutton_listener_yes: function(e) {
		var node = this;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		if (tree.oSelector.isset(id+"d")) {
			tree.oSelector.remsel(id+"d");
			tree.revertRadioForNode(node, false);
		}
		tree.oSelector.addsel(id);
	},

	radiobutton_listener_inherit: function(e) {
		var node = this;
		var tree = this.tree._containerObject;
		var id = tree._getNodeId(node);
		if (tree.oSelector.isset(node.contentElId)) tree.oSelector.remsel(id);
		tree.oSelector.addsel(id+"d");
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
