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


var OrgFolderTree = function(id, oConfig) {

	OrgFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(this.clickNode, this);
	this.setOptionClickEvent(this.executeOption, this);
	this.setDragDropEvent(this.moveNode, this);

	this.setDragEnterEvent(this.dragEnter, this);
	this.setDragOutEvent(this.dragOut, this);
	this.setDragEvent(this.drag, this);
	this.setDragOverEvent(this.dragOver, this);

	if (oConfig.addFolderButton) {
		YAHOO.util.Event.addListener(oConfig.addFolderButton, "click", this.insertNewOrgBranch, this, true);
	}
};

YAHOO.lang.extend(OrgFolderTree, FolderTree, {

	executeOption: function(node, option) {
		//option == { command: "any command string", node: [node instance] }
		switch (option.command) {

			case "modify": {
				this.renameCategoryFolder(node);
			}break;

			case "delete": {
				this.deleteNodeEvent(node);
			}break;

			case "assignfields": {
				this.assignFields(node);
			}break;

			default: {
				//alert("option command: "+option.command);
				}

		}
	},


	clickNode: function(oNode) {
		var id = this._getNodeId(oNode), el = YAHOO.util.Dom.get("add_org_folder");
		if (el) el.href = el.href.replace(/id=[0-9]*/, 'id='+id);

		el = YAHOO.util.Dom.get("import_users_action");
		if (el) el.href = el.href.replace(/id=[0-9]*/, 'id='+id);

		UserManagement.selectedOrgBranch = id;
		DataTable_usertable.refresh();
	},


	moveNode: function(src, dest, oData) {
		var oScope = this;
		var par_src = this._getNodeId(src);
		var par_dest = this._getNodeId(dest);

		CreateDialog(this.id+"_move_orgbranch_dialog", {
			width: "500px",
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: false,
			header: this._lang.get('_AREYOUSURE'),
			body: function() {
				var ajaxUrl = this._serverUrl + '&command=movefolder&src='+par_src+'&dest='+par_dest;
				return '<form method="POST" id="move_category_form" action="'+ajaxUrl+'">'
					+'<p>'+this._lang.get('_MOVE_ORGBRANCH')+'</p>'
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
				DataTable_usertable.refresh();
			}
		}).call(this);

		oData.oDD.backToStart();
	},

	dragEnter: function(oNode, oArgs) {
		//...
	},

	dragOut: function(oNode, oArgs) {
		//...
	},

	drag: function(oArgs) {
		//var src = YAHOO.util.Dom.getXY(oArgs.source);
		//var tgt = YAHOO.util.Dom.getXY(oArgs.target);
		//var pxy = YAHOO.util.Dom.getXY(oArgs.proxy);
		//...
	},

	dragOver: function(src, dest) {
		//var src_id = this._getNodeId(src);
		//var dest_id = this._getNodeId(dest);
	},




	assignFields: function(node) {
		var loaded = false;
		var confirm = function() {if (loaded) this.submit();};
		var undo = function() {this.destroy();};

		var setDialogErrorMessage = function(message) {
			var el = YAHOO.util.Dom.get("assignfields_dialog_message");
			if (el) el.innerHTML = message;
		}

		var assignDialog = new YAHOO.widget.Dialog("assignfieldsDialog", {
			modal: true,
			close: true,
			visible: false,
			fixedcenter: false,
			constraintoviewport: false,
			draggable: true,
			hideaftersubmit: false,
			buttons: [
				{text: this._lang.get('_YES'), handler: confirm, isDefault: true},
				{text: this._lang.get('_NO'), handler: undo}
			]
		});

		assignDialog.hideEvent.subscribe(function(e, args) {
			YAHOO.util.Event.stopEvent(args[0]);
			this.destroy();
		}, assignDialog);

		assignDialog.callback = {
			success: function(oResponse) {
				var x, o = YAHOO.lang.JSON.parse(oResponse.responseText);
				if (o.success) {
					this.destroy();
				} else {
					setDialogErrorMessage(o.message ? o.message : this._lang.get('_OPERATION_FAILURE'));
				}
			},
			failure: function() {setDialogErrorMessage(this._lang.get('_CONNECTION_ERROR'));},
			scope: assignDialog
		};

		assignDialog.setHeader(this._lang.get('_LOADING'));
		assignDialog.setBody('<div id="assignfields_message"></div><div class="align_center"><img src="'+this._iconPath+'/standard/loadbar.gif" /></div>');

		var setCheckboxes = function() {
			var elList = YAHOO.util.Selector.query('input[id^=fields_use_]');
			YAHOO.util.Event.addListener(elList, "click", function() {
				var i, index = this.id.replace('fields_use_', '');
				var inputs = YAHOO.util.Dom.get(["fields_inherit_"+index, "fields_mandatory_"+index, "fields_invisible_"+index, "fields_userinherit_"+index]);
				for (i=0; i<inputs.length; i++) inputs[i].disabled = !this.checked;
			});
		};

		assignDialog.destroyEvent.subscribe(function() {
			var elList = YAHOO.util.Selector.query('input[id^=fields_use_]');
			YAHOO.util.Event.purgeElement(elList);
		});

		assignDialog.render(document.body);
		assignDialog.center();
		assignDialog.show();

		var oCallback = {
			success: function(o) {
				var res = YAHOO.lang.JSON.parse(o.responseText);
				if (res.success) {
					loaded = true;
					if (!res.header) res.header = "";
					if (!res.body) res.body = "";
					assignDialog.setHeader(res.header);
					assignDialog.setBody('<div id="assignfields_dialog_message"></div>'+res.body);
					assignDialog.center();
					setCheckboxes();
				} else {
					var message = (res.message ? res.message : this._lang.get('_OPERATION_FAILURE'));
					setDialogErrorMessage(message);
					assignDialog.center();
				}
			},
			failure: function() {setDialogErrorMessage(this._lang.get('_CONNECTION_ERROR'));}
		};

		YAHOO.util.Connect.asyncRequest("POST", this._serverUrl+'&command=assignfields', oCallback, 'id_node='+this._getNodeId(node));
	},



	renameCategoryFolder: function(nodeToRename) {
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

		dialog = new YAHOO.widget.Dialog(idDialog, {
			width: "600px",
			fixedcenter: false,
			visible: false,
			draggable: true,
			close: true,
			constraintoviewport: false,
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
		dialog.center();
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
		return "OrgFolderTree '"+this.id+"'";
	}
});


var DialogOrgFolderTree = function(id, oConfig) {
	DialogOrgFolderTree.superclass.constructor.call(this, id, oConfig);
};

YAHOO.lang.extend(DialogOrgFolderTree, FolderTree, {
	_nodeClickEvent: {
		eventFunction: function(o) { },
		eventScope: this
	},
	toString: function() {return "DialogOrgFolderTree '"+this.id+"'";}
});
