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

var oTextNodeMap = new Array();
var oCurrentTextNode = null;
var FolderTree = function(id, config) {
	this._init(id, config);
}

FolderTree.prototype = {

	_serverUrl : '',
	_iconPath : '',

	//id of the tree object
	id : null,

	//tree instance
	_tree : null,
	
	_rootNode : null,

	_selectedNode : null,
	_selectedNodeId : false,
	
	//language manager
	_lang : null,

	//properties (set in config parameter
	_draggableNodes : false,
	_checkableNodes : false,
	_canRenameNodes : false,
	_canCreateNodes : false,
	_canDeleteNodes : false,

	_initialRequest : true,

	//tree state variable is a collection of expanded nodes (array of node ids)
	_treeState: [],
        
        
        _simple : false,


	//some events
	onSelectNode : function(o) {},

	//initialize the tree
	_init : function(id, oConfig) {
		this.id = id;
		this._serverUrl = oConfig.ajax_url;
		this._lang = new LanguageManager(oConfig.langs);

		this._draggableNodes = oConfig.dragdrop || false;
		this._checkableNodes = oConfig.useCheckboxes || false;
		this._iconPath = oConfig.iconPath || null;
		this._selectedNodeId = oConfig.initialSelectedNode || false;

		//set input hidden
		var temp = document.createElement('input');
		temp.id = "current_selected_node_"+id;
		temp.type = "hidden";
		YAHOO.util.Dom.get(id).parentNode.appendChild(temp);

		if (oConfig.initialTreeStatus) {
			this._tree = new YAHOO.widget.MyTreeView(id, oConfig.initialTreeStatus);
		} else { this._tree = new YAHOO.widget.MyTreeView(id); }

		this.oSelector = new ElemSelector('news_');
		if (oConfig.initialSelectorData) {
			this.oSelector.initSelection(oConfig.initialSelectorData);
		}

		this._tree._containerObject = this;
		var root = this._tree.getRoot();

		this._rootNode = this._addNode(root, 0, '<span>'+this._lang.get('_ROOT')+'</span>', false, true);
		this._rootNode.contentStyle = "ygtvroot";

		//click on branch : branch icon: expand event, label: custom event
		this._tree.subscribe('clickEvent', this._nodeClick);

		//alternate lines refresh events
		var refreshLines = function(o) { o.tree._containerObject._alternateLines(); this._containerObject._updateTreeState(); };
		this._tree.subscribe('expandComplete', refreshLines);
		this._tree.subscribe('collapseComplete', refreshLines);

		//expandnode function
		this._tree.setDynamicLoad(this._loadNodeData, 1); //0 = collpase, 1 = leaf

		this._tree.render();
		this._alternateLines();
	},

	getRoot: function() { return this._rootNode; },

	_setRootNode: function() {},

	_getNodeById : function(id, rootIfFail) {
		var node = false, el = YAHOO.util.Dom.get(this.id+'_'+id);
		if (el) node = this._tree.getNodeByElement(el);
		//var node = this._tree.getNodeByProperty("contentElId", this.id+'_'+id);
		if (!node)
			if (rootIfFail)
				node = this._rootNode;
			else
				node = false;
		return node;
	},

	_getNodeId : function(node) { return node.contentElId.replace(this.id+'_', ''); },

	getSelectedNode: function() { return this._selectedNode; },
	
	getSelectedNodeId: function() { return this._getNodeId(this._selectedNode); },

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

	getOtherRequestParams: function(node) { return ''; },

	_loadNodeData : function(node, fnLoadComplete) {

		var scope = this.tree._containerObject;
		var serverUrl = scope._serverUrl+'&command=expand&node_id='+scope._getNodeId(node)+(scope._initialRequest ? '&initial=1' : '')+scope.getOtherRequestParams(node);
		var oCallback = {

			success: function(oResponse) {
				var oResults = YAHOO.lang.JSON.parse(oResponse.responseText);
				if (oResults.success) {
					scope._populateTree(node, oResults.nodes);
				} else {
					//... handle error ...
				}
				oResponse.argument.fnLoadComplete();
				if(oResponse.argument.initial && scope._selectedNode == null) {
					if(scope._selectedNodeId != false) {
						var iNode = scope._getNodeById(scope._selectedNodeId, true);
						scope._setSelectedNode(iNode);
					} else {
						var iNode = (oResults.set_selected ? scope._getNodeById(oResults.set_selected) : scope.getRoot());
						scope._setSelectedNode(iNode);
					}
				}
			},

			failure: function(oResponse) {
				oResponse.argument.fnLoadComplete();
			},

			argument: {
				"node": node,
				"fnLoadComplete": fnLoadComplete,
				"initial": scope._initialRequest
			},

			timeout: 10000
		};
		if (scope._initialRequest) scope._initialRequest = false;

		YAHOO.util.Connect.asyncRequest('POST', serverUrl, oCallback);
	},

	_addNode : function(parentNode, id, label, isLeaf, isRoot, options, style, others) {
		var dd_id = 'dd_'+id;
		var params = {
			html: ( this._draggableNodes ? '<div id="'+dd_id+'">'+label+'</div>' : label ),
			checkable: (isRoot ? false : this._checkableNodes),
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

		if (this._draggableNodes) {
			if (!isRoot) {
				var dnode = new YAHOO.util.DDNode(dd_id, this.id+"_group");
				dnode.stree = this;
			}
			new YAHOO.util.DDTarget(dd_id, this.id+"_group");
		}

		return new_node;
	},
	
	_populateTree: function(parentNode, nodes) {
		var options, style;
		if (nodes.length <= 0) return;

		if (YAHOO.lang.isArray(nodes)) {

			for (var i=0; i<nodes.length; i++) {

				var data = ( nodes[i].node !== undefined ? nodes[i].node : nodes[i] );

				if (data.options) options = data.options; else options = [];
				if (data.style) style = data.style; else style = false;
				var createdNode = this._addNode(parentNode, data.id, data.label, data.is_leaf, false, options, style);
				if (nodes[i].children !== undefined) {

					createdNode.dynamicLoadComplete = true;
					createdNode.expanded = true;
					this._populateTree(createdNode, nodes[i].children);

					oTextNodeMap[data.id] = createdNode;
				}
			}
		}
	},

	_nodeClickEvent: {
		eventFunction: function(o) { },
		eventScope: this
	},

	_optionClickEvent: {
		eventFunction: function(o) { },
		eventScope: this
	},

	_dragdropEvent: {
		eventFunction: function(o) { },
		eventScope: this
	},

	setNodeClickEvent: function(e, scope) {
		var o = this._nodeClickEvent;
		o.eventFunction = e;
		if (scope) o.eventScope = scope; else o.eventScope = this;
	},

	setOptionClickEvent: function(e, scope) {
		var o = this._optionClickEvent;
		o.eventFunction = e;
		if (scope) o.eventScope = scope; else o.eventScope = this;
	},

	setDragDropEvent: function(e, scope) {
		var o = this._dragdropEvent;
		o.eventFunction = e;
		if (scope) o.eventScope = scope; else o.eventScope = this;
	},

	_checkClasses: function(el, classes) {
		if (YAHOO.lang.isArray(classes)) {
			for (var i=0; i<classes.length; i++) {
				//if (YAHOO.util.Dom.hasClass(el, classes[i])) return true;
				if (checkClassDomain(el, classes[i])) return classes[i];
			}
		}
		return false;
	},

	_nodeClick: function(oClick) { //scope: treeview object
		var node = oClick.node;
		var target = YAHOO.util.Event.getTarget(oClick.event);
		var scope = this._containerObject;

		//if (node===scope._rootNode) { scope._setSelectedNode(node); return false; }

		var isEventToPropagate = true;

		switch (scope._checkClasses(target, ['ygtvhtml', 'ygtvroot', 'ygtvcheck0', 'ygtvcheck1', 'ygtvcheck2', 'ygtvoptions'])) {
			case 'ygtvroot':
			case 'ygtvhtml': {
				scope._setSelectedNode(node);
				var o = scope._nodeClickEvent;
				o.eventFunction.call(o.eventScope, node);
				if (node!=scope.getRoot && node.expanded) isEventToPropagate = false;
				if (node==scope.getRoot()) isEventToPropagate = false;
				if (node.dynamicLoadComplete);
			} break;

			case 'ygtvoptions': {
				//detect which option has been clicked and call the optoinclick function with associated command
				var idToCheck, optionToUse = false;
				var checkIdFunction = function(el) { if (el.id) { if (el.id==idToCheck) return true; } return false; };
				for (var i=0; i<node._options.length; i++) {
					idToCheck = this.id+'_'+scope._getNodeId(node)+'_'+node._options[i].id;
					if (YAHOO.util.Dom.getAncestorBy(target, checkIdFunction)) {
						optionToUse = node._options[i];
						break;
					}
				}
				var o = scope._optionClickEvent;
				o.eventFunction.call(o.eventScope, node, optionToUse);
				isEventToPropagate = false;
			} break;

			default: {
				// ... continue propagating event ...
			} break;
		}

		return isEventToPropagate;
   },

	_alternateLines: function() {
		var el, i = 0;
		var checkNode = function (node) {
			if (node.isVisible()) {
				el = node.getContentEl().parentNode; //the table container
				if (i%2)
					YAHOO.util.Dom.addClass(el, 'table_odd');
				else
					YAHOO.util.Dom.removeClass(el, 'table_odd');
				i++;
			}
		};
		this._walkTree(this._rootNode, checkNode, false);
	},

	_walkTree: function(node, toCall, up) {

		//walk through children
		var checkChildren = function(node) {
			toCall.call(this, node);
			if (!node.hasChildren()) return;
			for (var i=0; i<node.children.length; i++) {
				checkChildren.call(this, node.children[i]);
			}
		};

		//walk through parents
		var checkParents = function(node) {
			if (node.isRoot()) return;
			toCall.call(this, node);
			checkParents.call(this, node.parent);
		};

		//perform tree walking
		checkChildren.call(this, node);
		if (up) checkParents.call(this, node);
	},

	/**
	 * Tree state management functions
	 */
	getTreeState: function() { return this._treeState; },

	//refresh tree state
	_updateTreeState: function() {

		var update = function(node) {
			if (node.expanded) {
				this._treeState.push(this._getNodeId(node));
			}
		};

		this._treeState = [];
		this._walkTree(this._rootNode, update, false);
	},

	toString: function() { return "FolderTree '"+this.id+"'"; },

	/**
	 * Node actions such as delete and add funzion
	 */
	_updateNodeOptions: function(node) {
		if (node != this._rootNode) {
			YAHOO.util.Connect.asyncRequest("POST", this._serverUrl,
				{
					success: function(oResponse) {
						var data = YAHOO.lang.JSON.parse(oResponse.responseText);
						if (data.success){ node.updateOptions(data.options); }
					},
					failure: this.connectionFailure
				},
				'command=options&node_id='+this._getNodeId(node)
			);
		}
	},

	connectionFailure: function() {
		alert(this._lang.get('_SERVER_CONNECTION_ERROR'));
	},

	refresh: function() {
		this._tree.render();
		this._alternateLines();
		this._setSelectedNode(this.getSelectedNode());
	},

	//insert new folder
	createFolder: function(data) {
		var postdata = 'command=create', otherdata = '', oScope = this;

		if (data.otherParams) {
			for (var x in data.otherParams) otherdata += '&'+x+'='+data.otherParams[x];
		}

		//compose post data string
		if (data.idNode) {
			postdata += '&node_id='+data.idNode;
			if (data.multiLanguage) {
				if (YAHOO.lang.isArray(data.nodeName)) {
					for (var i=0; i<data.nodeName.length; i++) {
						postdata += '&name['+data.nodeName[i].language+']='+data.nodeName[i].name;
					}
				}
			} else {
				postdata += '&name='+data.nodeName+otherdata;
			}
		} else {
			return false;
		}

		YAHOO.util.Connect.asyncRequest("POST", this._serverUrl,
			{
				success: function(oResponse) {
					var res = YAHOO.lang.JSON.parse(oResponse.responseText);

					if (res.success) {
						if (data.popupToClose) {
							data.popupToClose.destroy();
						}
						//refresh treeview

						var node = res.node;
						var parent = this._getNodeById(data.idNode);
						/*
						this._addNode(parent, node.id, node.label, node.is_leaf, false, node.options);
						if (parent.isLeaf || (parent.contentStyle.indexOf("ygtvroot") >= 0 && parent.children.length == 1)) {
							parent.isLeaf = false;
							parent.expanded = false;
							this._updateNodeOptions(parent);
						}
						parent.refresh();
						parent.expand();
						this._alternateLines();
						if (data.select) oScope._setSelectedNode(oScope._getNodeById(node.id));
						*/
						oScope.appendNode(parent, node, data.select);
					} else {
						//...
					}
				},
				failure: this.connectionFailure,
				scope: this
			},
			postdata
		);

		return true;
	},


	appendNode: function(parent, node, select) {
		this._addNode(parent, node.id, node.label, node.is_leaf, false, node.options);
		if (parent.isLeaf || (parent.contentStyle.indexOf("ygtvroot") >= 0 && parent.children.length == 1)) {
			parent.isLeaf = false;
			parent.expanded = false;
			this._updateNodeOptions(parent);
		}
		parent.refresh();
		parent.expand();
		this._alternateLines();
		if (select) this._setSelectedNode(this._getNodeById(node.id));
	},

	//modifiy folder
	modifyFolder: function(data) {
		var postdata = 'command=modify', oScope = this;

		//compose post data string
		if (data.idNode) {
			postdata += '&node_id='+data.idNode;
			if (data.multiLanguage) {
				if (YAHOO.lang.isArray(data.nodeName)) {
					for (var i=0; i<data.nodeName.length; i++) {
						postdata += '&name['+data.nodeName[i].language+']='+data.nodeName[i].name;
					}
				}
			} else {
				postdata += '&name='+data.nodeName;
			}
		} else {
			return false;
		}

		YAHOO.util.Connect.asyncRequest("POST", this._serverUrl,
			{
				success: function(oResponse) {
					var res = YAHOO.lang.JSON.parse(oResponse.responseText);

					if (res.success) {
						if (data.popupToClose) {
							data.popupToClose.destroy();
						}
						//refresh node name
						var node = this._getNodeById(data.idNode);
						node.setLabel(res.new_name);//node.setLabel(data.nodeName);
					} else {
						//...
					}
				},
				failure: this.connectionFailure,
				scope: this
			},
			postdata
		);

		return true;
	},

	//default events handling
	deleteNodeEvent: function(node) {

		var dialog, idDialog = this.id+"_delete_dialog", oScope = this;

		var clickYes = function() {
			var treeView = oScope, popup = this;
			YAHOO.util.Connect.asyncRequest("POST", oScope._serverUrl,
				{
					success: function(oResponse) {
						var data = YAHOO.lang.JSON.parse(oResponse.responseText);

						if (data.success) {
							popup.destroy();
							//TO DO: check if parent node becomes a leaf!
							var parent = node.parent;
							treeView._tree.popNode(node);
							treeView.refresh();
							if (parent!=treeView.getRoot() && parent.children.length<=0) {
								//make request to obtain delete option markup
								parent.isLeaf = true;
								treeView._updateNodeOptions(parent);
							}
						} else {
							//error
						}
					},
					failure: treeView.connectionFailure,
					scope: oScope
				},
				'command=delete&node_id='+treeView._getNodeId(node)
			);
		};

		var clickNo = function() { this.destroy(); }


		var dialogEl = document.createElement("div");
		dialogEl.id = idDialog;
		document.body.appendChild(dialogEl);

		dialog = new YAHOO.widget.SimpleDialog(idDialog, {
			width: "400px",
			fixedcenter: true,
			visible: false,
			draggable: true,
			close: false,
			constraintoviewport: true,
			modal: true,
			icon: YAHOO.widget.SimpleDialog.ICON_WARN,
			buttons: [
				{ text: this._lang.get('_YES'), handler: clickYes, isDefault:true },
				{ text: this._lang.get('_NO'), handler: clickNo }
			]
		} );

		var text = node.getContentHtml();

		dialog.setHeader(this._lang.get('_AREYOUSURE'));
		dialog.setBody(this._lang.get('_DEL')+':&nbsp;<b>'+text+'</b>');

		//dialog.subscribe("cancelEvent", clickNo);
		dialog.render();
		dialog.show();
	}

}


YAHOO.widget.MyTreeView = function(id, oConfig) {

	YAHOO.widget.MyTreeView.superclass.constructor.call(this, id, oConfig);
};

YAHOO.lang.extend(YAHOO.widget.MyTreeView, YAHOO.widget.TreeView, {

    _getEventTargetTdEl: function (ev) {
		var Dom = YAHOO.util.Dom,
			Event = YAHOO.util.Event,
			Lang = YAHOO.lang;
		
		var target = Event.getTarget(ev);
        // go up looking for a TD with a className with a ygtv prefix
        while (target && !(target.tagName.toUpperCase() == 'TD' && Dom.hasClass(target.parentNode,'ygtvrow'))) {
            target = Dom.getAncestorByTagName(target,'td');
        }
        if (Lang.isNull(target)) { return null; }
        // If it is a spacer cell, do nothing
        if (/\bygtv(blank)?depthcell/.test(target.className)) { return null;}
        // If it has an id, search for the node number and see if it belongs to a node in this tree.

		return target;
    }

});

/*
    "contextmenu" event handler for the element(s) that
    triggered the display of the ContextMenu instance - used
    to set a reference to the TextNode instance that triggered
    the display of the ContextMenu instance.
*/

function onTriggerContextMenu(p_oEvent) {

	var oTarget = this.contextEventTarget,
		Dom = YAHOO.util.Dom;

	
	if (oTarget.id.indexOf("dd_") >= 0) {

		//if(oCurrentTextNode == oTextNodeMap[oTarget.id])
		//	this.cancel();
		oCurrentTextNode = oTextNodeMap[oTarget.id];
	} else {

		this.cancel();

	}

}
YAHOO.util.Event.onAvailable("page_tree", function () {



var oContextMenu = new YAHOO.widget.ContextMenu("mytreecontextmenu", {
                                                trigger: "page_tree",
                                                lazyload: true,
                                                itemdata: [

													[ { text: "Pubblicata", checked: true } ],
													[ { text: "Muovi su" },
													{ text: "Muovi giù" } ],
													[ { text: "Modifica contenuti"} ],
													[ { text: "Modifica pagina"},
													{ text: "Cancella pagina"} ]
                                                ] });



oContextMenu.subscribe("triggerContextMenu", onTriggerContextMenu);

});