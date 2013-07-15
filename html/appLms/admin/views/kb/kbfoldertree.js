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

var KbTree;

var KbFolderTree = function(id, oConfig) {

	KbFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(this.clickNode, this);
	this.setOptionClickEvent(this.executeOption, this);

	this.setDragDropEvent(this.moveNode, this);
	this.setDragEnterEvent(this.dragEnter, this);
	this.setDragOutEvent(this.dragOut, this);
	this.setDragEvent(this.drag, this);

};

YAHOO.lang.extend(KbFolderTree, FolderTree, {

	executeOption: function(node, option) {
		//option == { command: "any command string", node: [node instance] }
		switch (option.command) {
			case "modify": {
				this.renameFolder(node);
			} break;
			case "delete": {
				this.deleteNodeEvent(node);
			} break;
			default: {}
		}
	},

	clickNode: function(oNode) {
		if (!this._checkableNodes) {
			var id = this._getNodeId(oNode),
			el = YAHOO.util.Dom.get("add_kb_folder");
			if (el) el.href = el.href.replace(/id=[0-9]*/, 'id='+id);

			//YAHOO.util.Dom.get("addfolder_id_parent").value = id;

			KbManagement.selected_node = id;
			//this._setSelectedNode(this.getSelectedNode());

			DataTable_kb_table.refresh();
		}
	},

	moveNode: function(src, dest, oData) {
		var oScope = this;
		var par_src = this._getNodeId(src);
		var par_dest = this._getNodeId(dest);

		YAHOO.util.Connect.asyncRequest('POST', this._serverUrl, {
			success: function(oResponse) {
				var t = YAHOO.lang.JSON.parse(oResponse.responseText);
				if (t.success) {

					oScope._tree.popNode(src);
					if(!dest.isLeaf && dest.expanded) src.appendTo(dest);

					oScope.refresh();
					oScope._alternateLines();

				}
				oData.oDD.backToStart();
			},
			failure: function() {oData.oDD.backToStart();}
		}, 'command=movefolder&src='+par_src+'&dest='+par_dest);
	},

	dragEnter: function(oNode, oArgs) {},

	dragOut: function(oNode, oArgs) {},

	drag: function(oArgs) {},

	renameFolder: function(nodeToRename) {
		var dialog, idDialog = this.id+"_renamefolder_popup", oScope = this;

		var clickYes = function() { //"this" is the popup
			var node = oScope._getNodeId(nodeToRename);
			
			var form = YAHOO.util.Dom.get("modfolder_form");
			if (form) {
				YAHOO.util.Connect.setForm(form);
				YAHOO.util.Connect.asyncRequest("POST", form.action, {
					success: function(o) {
						var res = YAHOO.lang.JSON.parse(o.responseText);
						if (res.success) {
							nodeToRename.setLabel(res.new_name);
							this.destroy();
						}
					},
					scope: this
				});
			} else {
				//...
			}
		};
		var clickNo = function() {
			this.destroy();
		}

		//var dialogEl = document.createElement("div");
		//dialogEl.id = idDialog;
		//document.body.appendChild(dialogEl);

		dialog = new YAHOO.widget.Dialog(idDialog, {
			width: "600px",
			fixedcenter: true,
			visible: false,
			draggable: true,
			close: true,
			constraintoviewport: true,
			modal: true,
			buttons: [{
				text: this._lang.get('_YES'),
				handler: clickYes,
				isDefault:true
			}, {
				text: this._lang.get('_NO'),
				handler: clickNo
			}]
		} );

		dialog.setHeader(this._lang.get('_MOD'));
		dialog.setBody('<div class="align_center"><span>'+this._lang.get('_LOADING')+'</span>:&nbsp;<img src="'+this._iconPath+'/standard/loadbar.gif" title="" /></div>');

		dialog.hideEvent.subscribe(function(e, args) {
			YAHOO.util.Event.stopEvent(args[0]);
			this.destroy();
		}, dialog);

		dialog.render(document.body);
		dialog.show();


		YAHOO.util.Connect.asyncRequest("POST", this._serverUrl+'&command=getmodform', {
			success: function(o) {
				var res = YAHOO.lang.JSON.parse(o.responseText);
				if (res.success) {
					this.setBody(res.body);
					this.center();
				}
			},
			failure: function() {},
			scope: dialog
		}, 'node_id='+this._getNodeId(nodeToRename));
		
	},

	toString: function() {
		return "KbFolderTree '"+this.id+"'";
	}
	
});

YAHOO.util.Event.onDOMReady(function() {
	KbTree  = new KbFolderTree('kb_tree', KbTreeOptions);
});