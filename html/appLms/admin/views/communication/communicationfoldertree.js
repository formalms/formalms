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

var CommunicationsFolderTree = function(id, oConfig) {

	CommunicationsFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(this.clickNode, this);
	this.setOptionClickEvent(this.executeOption, this);
	this.setDragDropEvent(this.moveNode, this);

};

YAHOO.lang.extend(CommunicationsFolderTree, FolderTree, {

	_getUrl: function(op) {return 'ajax.adm_server.php?r=alms/communication/'+op;},

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
		var links = ['add_category', 'add_link_1', 'add_link_2'];

		for (i=0; i<links.length; i++) {
			el = YAHOO.util.Dom.get(links[i]);
			if (el) el.href = el.href.replace(/id=[0-9]*/, 'id='+id);
		}

		Communications.selectedCategory = id;
		DataTable_communications_table.refresh(); } catch(e) { alert(e); }
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
			fixedcenter: false,
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
				DataTable_communications_table.refresh();
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
				//DataTable_communications_table.refresh();
			},
			renderEvent: Communications.dialogRenderEvent
		}).call(this);
	},

	toString: function() {
		return "CommunicationsCategoriesTree '"+this.id+"'";
	}
});