function nodeClickEventFunction() {
	//...
}

var CourseFolderTree = function(id, oConfig) {

	CourseFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(nodeClickEventFunction, this);
	this.setOptionClickEvent(this.executeOption, this);

	if (oConfig.addFolderButton) {
		YAHOO.util.Event.addListener(oConfig.addFolderButton, "click", this.insertNewCategoryFolder, this, true);
	}
};

YAHOO.lang.extend(CourseFolderTree, FolderTree, {

	executeOption: function(node, option) {
		//option == { command: "any command string", node: [node instance] }
		switch (option.command) {

			case "modify": {
				this.renameCategoryFolder(node);
			} break;

			case "delete": {
				this.deleteNodeEvent(node);
			} break;

			default: {
				//alert("option command: "+option.command);
				}

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

	toString: function() {
		return "CourseFolderTree '"+this.id+"'";
	}
});