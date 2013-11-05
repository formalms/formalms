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

var CourseFolderTree = function(id, oConfig) {

	CourseFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(this.refreshTable, this);
	this.setOptionClickEvent(this.executeOption, this);
	this.setDragDropEvent(this.moveNode, this);

	if (oConfig.addFolderButton) {
		YAHOO.util.Event.addListener(oConfig.addFolderButton, "click", this.insertNewCategoryFolder, this, true);
	}
};

YAHOO.lang.extend(CourseFolderTree, FolderTree, {

	refreshTable: function(oNode) {
		YAHOO.Classroom.selectedCategory = this.getSelectedNodeId();
		var oDt = DataTable_classroom_table;
		var oState = oDt.getState();
		var request = oDt.get("generateRequest")(oState, oDt);
		var oCallback = {
			success : oDt.onDataReturnSetRows,
			failure : oDt.onDataReturnSetRows,
			argument : oState,
			scope : oDt
		};
		oDt.getDataSource().sendRequest(request, oCallback);
	},

	executeOption: function(node, option) {
		//option == { command: "any command string", node: [node instance] }
		switch (option.command) {

			case "modify": {
				this.renameCategoryFolder(node);
			} break;

			case "delete": {
				this.deleteNodeEvent(node);
			} break;

			default: {}

		}
	},

	insertNewCategoryFolder: function() {
		var dialog, idDialog = this.id+"_addfolder_popup", oScope = this;

		var clickYes = function() { //this is the popup
			var node = oScope.getSelectedNodeId();
			var name = YAHOO.util.Dom.get('newfolder_name').value;

			oScope.createFolder({
				idNode: node,
				nodeName: name,
				popupToClose: this
			});
		};
		var clickNo = function() {
			this.destroy();
		}

		var dialogEl = document.createElement("div");
		dialogEl.id = idDialog;
		document.body.appendChild(dialogEl);

		dialog = new YAHOO.widget.SimpleDialog(idDialog, {
			width: "500px",
			fixedcenter: true,
			visible: false,
			draggable: true,
			close: false,
			constraintoviewport: true,
			modal: true,
			icon: YAHOO.widget.SimpleDialog.ICON_WARN,
			buttons: [{
				text: this._lang.get('_YES'),
				handler: clickYes,
				isDefault:true
			}, {
				text: this._lang.get('_NO'),
				handler: clickNo
			}]
		} );


		dialog.setHeader(this._lang.get('_NEW_FOLDER_NAME'));
		dialog.setBody(this._lang.get('_NAME')+':&nbsp;<input type="text" id="newfolder_name" />');

		dialog.render();
		dialog.show();

		YAHOO.util.Dom.get("newfolder_name").focus();
	},


	renameCategoryFolder: function(nodeToRename) {
		var dialog, idDialog = this.id+"_renamefolder_popup", oScope = this;

		var clickYes = function() { //this is the popup
			var node = oScope._getNodeId(nodeToRename);
			var name = YAHOO.util.Dom.get('changefolder_name').value;

			oScope.modifyFolder({
				idNode: node,
				nodeName: name,
				popupToClose: this
			});
		};
		var clickNo = function() {
			this.destroy();
		}

		var dialogEl = document.createElement("div");
		dialogEl.id = idDialog;
		document.body.appendChild(dialogEl);

		dialog = new YAHOO.widget.SimpleDialog(idDialog, {
			width: "500px",
			fixedcenter: true,
			visible: false,
			draggable: true,
			close: false,
			constraintoviewport: true,
			modal: true,
			icon: YAHOO.widget.SimpleDialog.ICON_WARN,
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
		dialog.setBody(this._lang.get('_NAME')+':&nbsp;<input type="text" id="changefolder_name" value="'+nodeToRename.getLabel()+'"/>');

		dialog.render();
		dialog.show();

		YAHOO.util.Dom.get("changefolder_name").focus();
	},


	moveNode: function(src, dest, oData) {
		YAHOO.util.Dom.get
		/*
		var oScope = this;
		var par_src = this._getNodeId(src);
		var par_dest = this._getNodeId(dest)
		YAHOO.util.Connect.asyncRequest('POST', this._serverUrl, {
			success: function(oResponse) {
				var t = YAHOO.lang.JSON.parse(oResponse.responseText);
				if (t.success) {

					oScope._tree.popNode(src);
					src.appendTo(dest);

					oScope.refresh();
					oScope._alternateLines();

				}
				oData.oDD.backToStart();
			},
			failure: function() {oData.oDD.backToStart();}
		}, 'command=dragdrop&src='+par_src+'&dest='+par_dest);
		*/
	},

	toString: function() {
		return "CourseFolderTree '"+this.id+"'";
	}
});