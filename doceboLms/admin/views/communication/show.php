<?php
echo getTitleArea(array(Lang::t('_COMMUNICATIONS', 'communication')));
?>
<div class="std_block">
<?php

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'communication',
	'filter_text' => $filter_text,
	'js_callback_set' => 'Communications.setFilter',
	'js_callback_reset' => 'Communications.resetFilter',
	'auxiliary_filter' => Form::getInputCheckbox('show_descendants', 'show_descendants', '1', ($show_descendants ? true : false), '')
		.' <label class="label_normal" for="show_descendants">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>'
));

?>
<div class="panel_left_small">
<span class="title"><?php echo(Lang::t('_ALL_CATEGORIES', 'communication')); ?></span>
<?php

//--- TREEVIEW -------

//Categories tree
$languages = array(
	'_ROOT' => Lang::t('_COMMUNICATIONS', 'communication'),
	'_NEW_FOLDER_NAME' => Lang::t('_NEW_CATEGORY', 'course'),
	'_MOD' => Lang::t('_MOD', 'course'),
	'_AREYOUSURE' => Lang::t('_AREYOUSURE', 'standard'),
	'_NAME' => Lang::t('_NAME', 'standardt'),
	'_MOD' => Lang::t('_MOD', 'standard'),
	'_DEL' => Lang::t('_DEL', 'standard'),
	'_SAVE' => Lang::t('_SAVE', 'standard'),
	'_CONFIRM' => Lang::t('_CONFIRM', 'standard'),
	'_UNDO' => Lang::t('_UNDO', 'standard'),
	'_ADD' => Lang::t('_ADD', 'standard'),
	'_YES'=> Lang::t('_YES', 'standard'),
	'_NO' => Lang::t('_NO', 'standard'),
	'_INHERIT' => Lang::t('_ORG_CHART_INHERIT', 'organization_chart'),
	'_NEW_FOLDER' => Lang::t('_NEW_FOLDER', 'organization_chart'),
	'_RENAMEFOLDER' => Lang::t('_MOD', 'standard'),
	'_DELETEFOLDER' => Lang::t('_DEL', 'standard'),
	'_AJAX_FAILURE' => Lang::t('_CONNECTION_ERROR', 'standard')
);


$_tree_params = array(
	'id' => 'category_tree',
	'ajaxUrl' => 'ajax.adm_server.php?r=alms/communication/gettreedata',
	'treeClass' => 'CommunicationsFolderTree',
	'treeFile' => Get::rel_path('lms').'/admin/views/communication/communicationfoldertree.js',
	'languages' => $languages,
	'initialSelectedNode' => $selected_category,
	'dragDrop' => true,
	'show' => 'tree'
);

if ($permissions['add_category']) {
	$rel_title = Lang::t('_NEW_CATEGORY', 'course');
	$rel_url = 'ajax.adm_server.php?r=alms/communication/add_category&id='.$selected_category;
	$rel_action = '<a class="ico-wt-sprite subs_add" id="add_category" href="'.$rel_url.'" '
		.' title="'.$rel_title.'"><span>'.$rel_title.'</span></a>';
	$_tree_params['rel_action'] = $rel_action;

	//Add category dialog
	$this->widget('dialog', array(
		'id' => 'add_category_dialog',
		'dynamicContent' => true,
		'ajaxUrl' => 'this.href',
		'dynamicAjaxUrl' => true,
		'callback' => 'Communications.addCategoryCallback',
		'renderEvent' => 'Communications.dialogRenderEvent',
		'callEvents' => array(
			array('caller' => 'add_category', 'event' => 'click')
		)
	));
}

$this->widget('tree', $_tree_params);

?>
</div>

<div class="panel_right_big">
<span class="title"><?php echo(Lang::t('_COMMUNICATIONS', 'communication')); ?></span>
<?php

//--- TABLE -------

$_columns = array(
	array('key' => 'title', 'label' => Lang::t('_TITLE', 'communication'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'communication'), 'sortable' => true),
	array('key' => 'publish_date', 'label' => Lang::t('_DATE', 'communication'), 'sortable' => true),
	array('key' => 'type_of', 'label' => Lang::t('_TYPE', 'communication'), 'sortable' => true)
);

if ($permissions['mod'])
	$_columns[] = array('key' => 'categorize', 'label' => '<span class="ico-sprite subs_categorize"><span>'.Lang::t('_CATEGORIZE', 'kb').'</span></span>', 'className' => 'img-cell');

if ($permissions['subscribe'])
	$_columns[] = array('key' => 'user', 'label' => '<span class="ico-sprite subs_user"><span>'.Lang::t('_ASSIGN_USERS', 'communication').'</span></span>', 'className' => 'img-cell');

if ($permissions['mod'])
	$_columns[] = array('key' => 'edit', 'label' => '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'communication').'</span></span>', 'className' => 'img-cell');

if ($permissions['del'])
	$_columns[] = array('key' => 'del', 'label' => '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'communication').'</span></span>', 'formatter'=>'doceboDelete', 'className' => 'img-cell');

$_params = array(
	'id'			=> 'communications_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/communication/getlist',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'title',
	'dir'			=> 'asc',
	'columns'		=> $_columns,
	'fields'		=> array('id', 'id_comm', 'title', 'description', 'publish_date', 'type_of', 'categorize', 'id_resource', 'user', 'edit', 'del'),
	'generateRequest' => 'Communications.requestBuilder',
	'delDisplayField' => 'title'
);

if ($permissions['add']) {
	$_params['rel_actions'] = array(
		'<a class="ico-wt-sprite subs_add" id="add_link_1" '
			.'href="index.php?r=alms/communication/add&amp;id='.$selected_category.'" title="'.Lang::t('_ADD', 'communication').'">'
			.'<span>'.Lang::t('_ADD', 'communication').'</span></a>',
		'<a class="ico-wt-sprite subs_add" id="add_link_2" '
			.'href="index.php?r=alms/communication/add&amp;id='.$selected_category.'" title="'.Lang::t('_ADD', 'communication').'">'
			.'<span>'.Lang::t('_ADD', 'communication').'</span></a>',
	);
}

$this->widget('table', $_params);

?>
</div>

<div class="nofloat"></div>

</div>
<script type="text/javascript">
var Communications = {
	selectedCategory: 0,
	showDescendants: false,
	filterText: "",
	currentLanguage: "",

	init: function(oConfig) {
		if (oConfig.selectedCategory) this.selectedCategory = oConfig.selectedCategory;
		if (oConfig.showDescendants) this.showDescendants = oConfig.showDescendants;
		if (oConfig.filterText) this.filterText = oConfig.filterText;
		if (oConfig.currentLanguage) this.currentLanguage = oConfig.currentLanguage;

		var E = YAHOO.util.Event;

		E.onDOMReady(function() {
			var el = YAHOO.util.Dom.get("show_descendants");
			el.checked = Communications.showDescendants;
			E.addListener(el, "click", function(e) {
				Communications.showDescendants = this.checked;
				DataTable_communications_table.refresh();
			});
		});
	},

	setFilter: function() {
		Communications.filterText = this.value;
		DataTable_communications_table.refresh();
	},

	resetFilter: function() {
		this.value = "";
		Communications.filterText = "";
		DataTable_communications_table.refresh();
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
				"&id_category=" + Communications.selectedCategory +
				"&descendants=" + (Communications.showDescendants ? '1' : '0') +
				"&filter_text=" + Communications.filterText;
	},

	addFolderCallback: function() {
		
	},

	addCategoryCallback: function(o) {
		if (o.node) {
			var parent = TreeView_category_tree._getNodeById(o.id_parent);
			TreeView_category_tree.appendNode(parent, o.node, false);
		}
		this.destroy();
	},

	dialogRenderEvent: function() {
		var tabs = new YAHOO.widget.TabView("category_langs_tab");
		var id = "name_"+Communications.currentLanguage;
		YAHOO.util.Event.onAvailable(id, function(o) {
			this.focus();
			o.center(); //TO DO: make this working ...
		}, this);
	}
}

Communications.init({
	selectedCategory: <?php echo (int)$selected_category; ?>,
	showDescendants: <?php echo $show_descendants ? 'true' : 'false'; ?>,
	filterText: "<?php echo $filter_text; ?>",
	currentLanguage: "<?php echo getLanguage(); ?>"
});
</script>