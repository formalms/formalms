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


var FunctionalRoles = {

	selectedGroup: 0,
	filterText: "",
	currentLanguage: "",
	oLangs: new LanguageManager(),

	init: function(oConfig) {
		this.oLangs.set(oConfig.langs || {});
		this.currentLanguage = oConfig.currentLanguage;
	},

	setFilter: function() {
		FunctionalRoles.filterText = this.value;
		DataTable_fncroles_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		FunctionalRoles.filterText = "";
		DataTable_fncroles_table.refresh();
	},

	dialogRenderEvent: function() {var tabView = new YAHOO.widget.TabView("fncrole_langs_tab");},

	usersFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/functionalroles/man_users&id=' + oRecord.getData("id");
		elLiner.innerHTML = '<a href="' + url +'" title="'+FunctionalRoles.oLangs.get('_USERS')+'">' + oData +
			'&nbsp;<span class="ico-sprite subs_users">' +
			'<span>'+FunctionalRoles.oLangs.get('_USERS')+'</span></span></a>';
	},

	competencesFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/functionalroles/man_competences&id=' + oRecord.getData("id");
		elLiner.innerHTML = '<a href="' + url +'" title="'+FunctionalRoles.oLangs.get('_COMPETENCES')+'">' + oData +
			'&nbsp;<span class="ico-sprite subs_competence">' +
			'<span>'+FunctionalRoles.oLangs.get('_COMPETENCES')+'</span></span></a>';
	},

	showCoursesFormatter: function(elLiner, oRecord, oColumn, oData) {
		var id = oRecord.getData("id");
		var url = 'index.php?r=adm/functionalroles/show_courses&id=' + id;
		elLiner.innerHTML = '<a href="' + url + '" class="ico-sprite subs_course" id="show_courses_' + id + '" ' +
			'title="'+FunctionalRoles.oLangs.get('_COURSES')+'">' +
			'<span>'+FunctionalRoles.oLangs.get('_COURSES')+'</span></a>';
	},

	gapAnalisysFormatter: function(elLiner, oRecord, oColumn, oData) {
		var url = 'index.php?r=adm/functionalroles/gap_analisys&id=' + oRecord.getData("id");
		elLiner.innerHTML = '<a href="' + url + '" class="ico-sprite subs_view" ' +
			'title="'+FunctionalRoles.oLangs.get('_GAP_ANALYSIS')+'">' +
			'<span>'+FunctionalRoles.oLangs.get('_GAP_ANALYSIS')+'</span></a>';
	},

	requestBuilder: function (oState, oSelf) {
		var sort, dir, startIndex, results;
		oState = oState || {pagination: null, sortedBy: null};

		startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
		results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
		sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
		dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";

		return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir+
				"&id_group=" + FunctionalRoles.selectedGroup +
				"&filter_text=" + FunctionalRoles.filterText;
	},


	beforeRenderEvent: function() {/*
		var elList = YAHOO.util.Selector.query('a[id^=show_courses_]');
		YAHOO.util.Event.purgeElement(elList);
	*/},

	postRenderEvent: function() {/*
		var elList = YAHOO.util.Selector.query('a[id^=show_courses_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			
			CreateDialog("test", {
				modal: true,
				close: true,
				visible: false,
				fixedcenter: true,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				confirmOnly: true,
				renderEvent: function() {
					var oDialog = this;
					YAHOO.util.Event.onAvailable("fncrole_show_courses", function() {
						var tree = new YAHOO.widget.TreeView(this);
						tree.render();
						oDialog.center();
					});
				},
				destroyEvent: function() {},
				callback: function() {
					this.destroy();
				}
			}).call(this, e);
			
		});
	*/}

}
