<?php echo getTitleArea(Lang::t('_PRIVACYPOLICIES', 'privacypolicies')); ?>
<div class="std_block">
<?php

if (isset($result_message)) echo $result_message;

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'privacypolicies',
	'filter_text' => $filter_text,
	'js_callback_set' => 'PrivacyPolicis.setFilter',
	'js_callback_reset' => 'PrivacyPolicies.resetFilter'
));


//--- TABLE -------

$columns = array(
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true)
);
if ($permissions['mod']) $columns[] = array('key' => 'assign', 'label' => Get::sprite('subs_elem', Lang::t('_ASSIGN', 'standard')), 'formatter' => 'PrivacyPolicies.assignformatter', 'className' => 'img-cell');
if ($permissions['mod']) $columns[] = array('key' => 'mod', 'label' => Get::sprite('subs_mod', Lang::t('_MOD', 'standard')), 'formatter' => 'doceboModify', 'className' => 'img-cell');
if ($permissions['del']) $columns[] = array('key' => 'del', 'label' => Get::sprite('subs_del', Lang::t('_DEL', 'standard')), 'formatter' => 'doceboDelete', 'className' => 'img-cell');

$params = array(
	'id' => 'policies_table',
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/privacypolicy/gettabledata',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'name',
	'dir' => 'asc',
	//'checkableRows' => true,
	'columns' => $columns,
	'fields' => array('id', 'name', 'is_assigned', 'mod', 'del'),
	'generateRequest' => 'PrivacyPolicies.requestBuilder',
	'stdModifyRenderEvent' => 'PrivacyPolicies.dialogRenderEvent',
	'delDisplayField' => 'name',
	'events' => array(
		'beforeRenderEvent' => 'PrivacyPolicies.beforeRenderEvent',
		'postRenderEvent' => 'PrivacyPolicies.postRenderEvent'
	)
);

if ($permissions['add']) {
	$add_link_title = Lang::t('_ADD', 'standard');
	$add_link_1 = '<a id="add_policy_link_1" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=adm/privacypolicy/add" title="'.$add_link_title.'"><span>'.$add_link_title.'</span></a>';
	$add_link_2 = '<a id="add_policy_link_2" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=adm/privacypolicy/add" title="'.$add_link_title.'"><span>'.$add_link_title.'</span></a>';
	$params['rel_actions'] = array($add_link_1, $add_link_2);

	$this->widget('dialog', array(
		'id' => 'add_policy_dialog',
		'dynamicContent' => true,
		'ajaxUrl' => 'ajax.adm_server.php?r=adm/privacypolicy/add',
		'renderEvent' => 'PrivacyPolicies.dialogRenderEvent',
		'callback' => 'function() { this.destroy(); DataTable_policies_table.refresh(); }',
		'callEvents' => array(
			array('caller' => 'add_policy_link_1', 'event' => 'click'),
			array('caller' => 'add_policy_link_2', 'event' => 'click')
		)
	));
}

$this->widget('table', $params);


$this->widget('tree', array(
	'id' => 'assign_orgchart_tree',
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/usermanagement/gettreedata_create',
	'treeClass' => 'DialogOrgFolderTree',
	//'treeFile' => Get::rel_path('adm').'/views/usermanagement/orgchartfoldertree.js',
	'languages' => array(
		'_ROOT' => Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart') )
	),
	'initialSelectedNode' => 0,
	'show' => 'tree',
	'useCheckboxes' => 'true',
	'initialSelectorData' => array(),
	'setSelectedNodeOnServer' => false,
	'hiddenSelection' => 'assign_orgchart_hidden_selection',
	'runtime' => true
));


?>
</div>
<script type="text/javascript">

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



var PrivacyPolicies = {
	filterText: "",
	oLangs: new LanguageManager(),

	init: function(oConfig) {
		this.filterText = oConfig.filterText;
		this.oLangs.set(oConfig.langs || {});
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
				"&filter_text=" + PrivacyPolicies.filterText;
	},

	setFilter: function() {
		PrivacyPolicies.filterText = this.value;
		DataTable_policies_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		PrivacyPolicies.filterText = "";
		DataTable_policies_table.refresh();
	},

	dialogRenderEvent: function() {
		var tabView = new YAHOO.widget.TabView("policy_langs_tab");
	},

	assignformatter: function(elLiner, oRecord, oColumn, oData) {
		var id = oRecord.getData("id");
		var url = 'ajax.adm_server.php?r=adm/privacypolicy/assign&id=' + id;
		var str = PrivacyPolicies.oLangs.get('_ASSIGN');
		var style = !oRecord.getData("is_assigned") ? 'subs_notice' : 'subs_elem';
		elLiner.innerHTML = '<a href="' + url + '" title="' + str
			+ '" class="ico-sprite ' + style + '" id="assign_orgchart_' + id + '">'
			+ '<span>'+str+'</span></a>';
	},

	beforeRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=assign_orgchart_]');
		YAHOO.util.Event.purgeElement(elList);
	},

	postRenderEvent: function() {
		var elList = YAHOO.util.Selector.query('a[id^=assign_orgchart_]');
		YAHOO.util.Event.addListener(elList, "click", function(e) {
			var oDt = DataTable_policies_table;
			var oRecord = oDt.getRecord(this);
			CreateDialog("assign_orgchart_dialog", {
				//width: "700px",
				modal: true,
				close: true,
				visible: false,
				fixedcenter: false,
				constraintoviewport: false,
				draggable: true,
				hideaftersubmit: false,
				isDynamic: true,
				ajaxUrl: this.href,
				renderEvent: function(o) {
					var oScope = this;
					YAHOO.util.Event.onAvailable("assign_orgchart_tree", function() {
						var selection = o.selection || [], disabled = o.disabled || [];
						var elSel = YAHOO.util.Dom.get("assign_orgchart_hidden_selection");
						selection = elSel.value.split(",");
						disabled = YAHOO.util.Dom.get("already_assigned").value.split(",");
						YAHOO.runtimeWidgets["assign_orgchart_tree"]({
							initialSelectedNode: 0,
							initialSelectorData: selection,
							disabledNodes: disabled
						});
					});
					YAHOO.util.Event.onAvailable("assign_orgchart_container", function() {
						this.style.minWidth = "700px";
						oScope.center();
					});
				},
				beforeSubmitEvent: function() {
					var elSel = YAHOO.util.Dom.get("assign_orgchart_hidden_selection");
					if (elSel) elSel.value = TreeView_assign_orgchart_tree.oSelector.toString();
				},
				destroyEvent: function() {
					//free memory, DOM and resources
					TreeView_assign_orgchart_tree.destroy();
					TreeView_assign_orgchart_tree = null;
				},
				callback: function(o) {
					this.destroy();
					oDt.refresh();
				}
			}).call(this, e);
		});
	}
};

PrivacyPolicies.init({
	filterText: "<?php echo $filter_text; ?>",
	langs: {
		_ASSIGN: "<?php echo Lang::t('_ASSIGN_POLICY','privacypolicy'); ?>"
	}
});

</script>