<?php Get::title(array(
	'index.php?r=adm/groupmanagement/show' => Lang::t('_GROUPS', 'admin_directory'),
	Lang::t('_ASSIGN_USERS', 'admin_directory').': '.$groupid
)); ?>
<div class="std_block">
<?php

if (isset($result_message)) echo $result_message;

echo getBackUi('index.php?r='.$this->link.'/show', Lang::t('_BACK', 'standard'));

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'group_users',
	'filter_text' => $filter_text,
	'js_callback_set' => 'Users.setFilter',
	'js_callback_reset' => 'Users.resetFilter'
));


//--- TABLE -------

$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$columns = array(
	array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true),
	array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'sortable' => true),
	array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'sortable' => true)
);
if ($permissions['associate_user']) $columns[] = array('key' => 'del', 'label' => $icon_del, 'formatter' => 'Users.deleteFormatter', 'className' => 'img-cell');

$params = array(
	'id'			=> 'group_users_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r='.$this->link.'/getusertabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'userid',
	'dir'			=> 'asc',
	'generateRequest' => 'Users.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'userid', 'firstname', 'lastname', 'del', 'is_group'),
	'delDisplayField' => 'userid',
	'useStdDeleteFormatter' => true
);

if ($permissions['associate_user']) {
	$rel_action_over = '<a id="sel_users_over" class="ico-wt-sprite subs_add" '
		.'href="index.php?r='.$this->link.'/assignmembers&id_group='.(int)$id_group.'" title="'.Lang::t('_ASSIGN_USERS', 'standard').'">'
		.'<span>'.Lang::t('_ASSIGN_USERS', 'standard').'</span></a>';
	$rel_action_over .= '<a id="import_group_link_2" class="ico-wt-sprite subs_import" href="index.php?r=adm/groupmanagement/importusers_step1&amp;id_group='.(int)$id_group.'" title="'.Lang::t('_IMPORT', 'admin_directory').'"><span>'.Lang::t('_IMPORT', 'admin_directory').'</span></a>';

	$rel_action_bottom = '<a id="sel_users_bottom" class="ico-wt-sprite subs_add" '
		.'href="index.php?r='.$this->link.'/assignmembers&id_group='.(int)$id_group.'" title="'.Lang::t('_ASSIGN_USERS', 'standard').'">'
		.'<span>'.Lang::t('_ASSIGN_USERS', 'standard').'</span></a>';
	$rel_action_bottom .= '<a id="import_group_link_2" class="ico-wt-sprite subs_import" href="index.php?r=adm/groupmanagement/importusers_step1&amp;id_group='.(int)$id_group.'" title="'.Lang::t('_IMPORT', 'admin_directory').'"><span>'.Lang::t('_IMPORT', 'admin_directory').'</span></a>';

	$params['rel_actions'] = array($rel_action_over, $rel_action_bottom);
}

$this->widget('table', $params);

echo getBackUi('index.php?r='.$this->link.'/show', Lang::t('_BACK', 'standard'));

?>
</div>
<script type="text/javascript">
var Users = {
	idGroup: 0,
	filterText: "",

	init: function(oConfig) {
		if (oConfig.idGroup) this.idGroup = oConfig.idGroup;
		if (oConfig.filterText) this.filterText = oConfig.filterText;
	},


	setFilter: function() {
		Users.filterText = this.value;
		DataTable_group_users_table.refresh();
	},

	resetFilter: function() {
		Users.filterText = "";
		this.value = "";
		DataTable_group_users_table.refresh();
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
				"&dir=" + dir +
				"&id_group=" + Users.idGroup +
				"&filter_text=" + Users.filterText;
	},

	deleteFormatter: function(elLiner, oRecord, oColumn, oData) {
		if (oRecord.getData("is_group")) {
			elLiner.innerHTML = '';
		} else {
			YAHOO.widget.DataTable.Formatter.stdDelete.call(this, elLiner, oRecord, oColumn, oData);
		}
	}
}


Users.init({
	idGroup: <?php echo (int)$id_group; ?>,
	filterText: "<?php echo $filter_text; ?>"
});

</script>