<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function(e) {
	var langs = {_ASSIGN_USERS: "<?php echo Lang::t('_ASSIGN_USERS', 'admin_directory'); ?>"};
	GroupManagement.init(langs);
});
</script>
<?php echo getTitleArea(Lang::t('_GROUPS', 'admin_directory')); ?>
<div class="std_block">
<?php if (isset($result_message)) echo $result_message; ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="usermanagement_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>
<?php

$assign_label_title = Lang::t('_ASSIGN_USERS', 'admin_directory');
$mod_label_title = Lang::t('_MOD', 'standard');
$del_label_title = Lang::t('_DEL', 'standard');

$assign_label = '<a href="javascript:return false;" class="ico-sprite subs_users" title="'.$assign_label_title.'"><span>'.$assign_label_title.'</span></a>';
$mod_label = '<a href="javascript:return false;" class="ico-sprite subs_mod" title="'.$mod_label_title.'"><span>'.$mod_label_title.'</span></a>';
$del_label = '<a href="javascript:return false;" class="ico-sprite subs_del" title="'.$del_label_title.'"><span>'.$del_label_title.'</span></a>';

$columns = array(
	array('key' => 'groupid', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true)
	//array('key' => 'usercount', 'label' => Lang::t('_USERS', 'standard'), 'sortable' => true, 'className' => 'img-cell'),
);
if ($permissions['associate_user']) $columns[] = array('key' => 'assign', 'label' => $assign_label, 'formatter' => 'GroupManagement.assignFormatter', 'className' => 'img-cell');
if ($permissions['mod']) $columns[] = array('key' => 'mod', 'label' => $mod_label, 'formatter' => 'doceboModify', 'className' => 'img-cell');
if ($permissions['del']) $columns[] = array('key' => 'del', 'label' => $del_label, 'formatter' => 'doceboDelete', 'className' => 'img-cell');

$params = array(
	'id' => 'grouptable',
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/groupmanagement/getdata&',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'groupid',
	'dir' => 'asc',
	'checkableRows' => true,
	'columns' => $columns,
	'fields' => array('id', 'groupid', 'description', 'usercount', 'membercount', 'mod', 'del'),
	'generateRequest' => 'GroupManagement.requestBuilder',
	'delDisplayField' => 'groupid'
);

if ($permissions['add']) {
	$add_link_title = Lang::t('_NEW', 'admin_directory');
	$add_link_1 = '<a id="add_group_link_1" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=adm/groupmanagement/create" title="'.$add_link_title.'"><span>'.$add_link_title.'</span></a>';
	$add_link_2 = '<a id="add_group_link_2" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=adm/groupmanagement/create" title="'.$add_link_title.'"><span>'.$add_link_title.'</span></a>';
	$params['rel_actions'] = array($add_link_1, $add_link_2);
}

$this->widget('table', $params);

?>
</div>