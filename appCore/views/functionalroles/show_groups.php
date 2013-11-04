<?php

echo getTitleArea(array(
	'index.php?r=adm/functionalroles/show' => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
	Lang::t('_MANAGE_GROUPS', 'fncroles')
));

?>
<script type="text/javascript">
var Groups = {
	filterText: "",
	currentLanguage: "<?php echo getLanguage(); ?>",

	dialogRenderEvent: function() { var tabView = new YAHOO.widget.TabView("group_langs_tab"); },

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
				"&filter_text=" + Groups.filterText;
	}
}

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener("filter_set", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		Groups.filterText = YAHOO.util.Dom.get("filter_text").value;
		DataTable_groups_table.refresh();
	});

	YAHOO.util.Event.addListener("filter_reset", "click", function(e) {
		YAHOO.util.Event.preventDefault(e);
		YAHOO.util.Dom.get("filter_text").value = "";
		Groups.filterText = "";
		DataTable_groups_table.refresh();
	});
});
</script>
<div class="std_block">
<?php if (isset($result_message)) echo $result_message; ?>
<?php echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard')); ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<?php

$icon_mod = '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>';
$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$columns = array(
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true)
);
if ($permissions['mod']) $columns[] = array('key' => 'mod', 'label' => $icon_mod, 'formatter'=>'stdModify', 'className' => 'img-cell');
if ($permissions['del']) $columns[] = array('key' => 'del', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell');



$params = array(
	'id'			=> 'groups_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/getgrouptabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'generateRequest' => 'Groups.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'name', 'description', 'mod', 'del'),
	'delDisplayField' => 'name',
	'stdModifyRenderEvent' => 'Groups.dialogRenderEvent'
);

if ($permissions['add']) {
	$rel_action_over = '<a id="add_group_over" class="ico-wt-sprite subs_add" '
		.'href="index.php?r=adm/functionalroles/add_group">'
		.'<span>'.Lang::t('_ADD', 'fncroles').'</span></a>';

	$rel_action_bottom = '<a id="add_group_bottom" class="ico-wt-sprite subs_add" '
		.'href="index.php?r=adm/functionalroles/add_group">'
		.'<span>'.Lang::t('_ADD', 'fncroles').'</span></a>';

	$params['rel_actions'] = array($rel_action_over, $rel_action_bottom);

	$this->widget('dialog', array(
		'id' => 'add_group_dialog',
		'dynamicContent' => true,
		'ajaxUrl' => 'ajax.adm_server.php?r=adm/functionalroles/add_group',
		'renderEvent' => 'Groups.dialogRenderEvent',
		'callback' => 'function() { this.destroy(); DataTable_groups_table.refresh(); }',
		'callEvents' => array(
			array('caller' => 'add_group_over', 'event' => 'click'),
			array('caller' => 'add_group_bottom', 'event' => 'click')
		)
	));
}

$this->widget('table', $params);

?>
<?php echo getBackUi('index.php?r=adm/functionalroles/show', Lang::t('_BACK', 'standard')); ?>
</div>