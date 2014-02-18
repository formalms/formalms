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

var glob_tree, glob_table;

var updateTable = function(node) {
	var $D = YAHOO.util.Dom;
	glob_table.setFilterParam('c_category', '', glob_tree._getNodeId(node));
	glob_table.setFilterParam('c_filter', '', $D.get('c_filter').value);
	glob_table.setFilterParam('c_flatview', '', $D.get('c_flatview').checked);
	glob_table.setFilterParam('c_waiting', '', $D.get('c_waiting').checked);
	
	var state = glob_table.getState();
	glob_table._sendTableRequest({
		startIndex: 0,
		results: glob_table.pageSize,
		sort: state.sortedBy.key,
		dir: glob_table._convertDir(state.sortedBy.dir)
	});
	
}

var filterEvent = function() {
	updateTable(glob_tree.getSelectedNode());
}

var CourseTableView = function(id, oConfig) {

	CourseTableView.superclass.constructor.call(this, id, oConfig);
	var oDt = this._oDataTable, oScope = this;

	oDt.subscribe("beforeRenderEvent", function() {
		var elList = YAHOO.util.Selector.query('a[id^=dup_]');
		YAHOO.util.Event.purgeElement(elList);
	});

	oDt.subscribe("postRenderEvent", function(e) {
		var elList = YAHOO.util.Selector.query('a[id^=dup_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			var el = this;
			var oRecord = oDt.getRecord(this);
			var body = '<form id="coursetable_dup_dialog_form" action="ajax.adm_server.php?plf=lms&amp;mn=course&amp;op=dup_course'
				+'&amp;id_course='+oRecord.getData('idCourse')+'" method="GET" name="coursetable_dup_dialog_form">'
				+'<p>'+oScope._oLangs.get('_DUPLICATE_COURSE')+': '+oRecord.getData("name")+'</p>'
				+'</form>';

			CreateDialog("coursetable_dup_dialog", {
				width: "500px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: true,
				constraintoviewport: true,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: false,
				//directSubmit: true,
				header: oScope._oLangs.get('_AREYOUSURE'),
				body: body,
				renderEvent: function() { 
				},
				callback: function(o) {
					this.destroy();
					updateTable(glob_tree.getSelectedNode());
				}
			}).call(this, e);
		});
	});

};

YAHOO.lang.extend(CourseTableView, TableView, {
	courseFormatters: {
		man_subscr: function(elLiner, oRecord, oColumn, oData) {
			
/*			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?r=alms/subscription/show&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/subscribe.png';
			ico.alt = this._oLangs.get('_MOD_SUBSCRIBE');
			elCell.appendChild(a); a.appendChild(ico);
*/
			if (oRecord.getData("subscriptions") != '--') { // we receive '--' if the current one is a course with editions
				elLiner.innerHTML = '<a class="nounder"'
					+' href="index.php?r=alms/subscription/show&id_course='+oRecord.getData('idCourse')+'">'+oRecord.getData("subscriptions")
					+' <span class="ico-sprite subs_'+(oRecord.getData("subscriptions")>0?'users':'notice')+'"><span>'+ this._oLangs.get('_MOD_SUBSCRIBE')+'</span></span></a>'
			}
			else {
				elLiner.innerHTML = '&nbsp;';
			}
		},
		p_man_subscr: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?r=subscription/show&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/subscribe.png';
			ico.alt = this._oLangs.get('_MOD_SUBSCRIBE');
			elCell.appendChild(a); a.appendChild(ico);
		},
		classroom: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?modname=course&op=classroom&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/classroom-cal.png';
			ico.alt = this._oLangs.get('_MANAGE_CERTIFICATIONS');
			elCell.appendChild(a); a.appendChild(ico);
		},
		certificate: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?modname=course&op=certifications&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/certificate.png';
			ico.alt = this._oLangs.get('_MANAGE_CERTIFICATIONS');
			elCell.appendChild(a); a.appendChild(ico);
		},
		competence: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?r=adm/competences/man_course&id_course='+oRecord.getData('idCourse');//this.baseUrl+'?modname=course&op=competences&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/competences.png';
			ico.alt = this._oLangs.get('_MANAGE_COMPETENCES');
			elCell.appendChild(a); a.appendChild(ico);
		},
		menu: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?modname=course&op=assignMenu&id_course='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/course/menu.png';
			ico.alt = this._oLangs.get('_ASSIGN_MENU');
			elCell.appendChild(a); a.appendChild(ico);
		},
		mod: function(elCell, oRecord, oColumn, oData) {
			if(oData == false) return;
			var a = document.createElement('a'), ico = document.createElement('img');
			a.href = this.baseUrl+'?modname=course&op=mod_course&idCourse='+oRecord.getData('idCourse');
			ico.src = this.imageUrl+'/standard/edit.png';
			ico.alt = this._oLangs.get('_MOD');
			elCell.appendChild(a); a.appendChild(ico);
		}
	},

	courseEditors: {

	}

});

var CourseFolderTree = function(id, oConfig) {
	
	CourseFolderTree.superclass.constructor.call(this, id, oConfig);

	this.setNodeClickEvent(updateTable, this);
	this.setOptionClickEvent(this.executeOption, this);
	this.setDragDropEvent(this.moveNode, this);

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
			fixedcenter: true,
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
				updateTable();
			}
		}).call(this);

		oData.oDD.backToStart();
	},


	toString: function() { 
		return "CourseFolderTree '"+this.id+"'";
	}
});

YAHOO.util.Event.onDOMReady(function() {
	var course_tree  = new CourseFolderTree('courses_categories', course_tree_options);
	var course_table = new CourseTableView('courses_list', course_table_options);

	//course_tree.setNodeClickEvent(updateTable, course_tree);
	//course_tree._tree.subscribe('clickEvent',updateTable);
	//course_tree.setOptionClickEvent(course_tree.executeOption, course_tree);

	glob_tree = course_tree;
	glob_table = course_table;
});