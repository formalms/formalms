<?php
$title = array(	'index.php?r='.$base_link_course.'/show' => Lang::t('_COURSE', 'course'),
				Lang::t('_EDITIONS', 'course'));

echo getTitleArea($title);
?>
<div class="std_block">
<?php

$_columns = array(
	array('key' => 'code', 'label' => Lang::t('_CODE', 'course'), 'sortable' => true),
	array('key' => 'name', 'label' => Lang::t('_NAME', 'course'), 'sortable' => true),
	array('key' => 'status', 'label' => Lang::t('_STATUS', 'course'), 'sortable' => true, 'formatter' => 'statusFormatter'),
	array('key' => 'date_begin', 'label' => Lang::t('_DATE_BEGIN', 'course'), 'sortable' => true),
	array('key' => 'date_end', 'label' => Lang::t('_DATE_END', 'course'), 'sortable' => true),
	array('key' => 'students', 'label' => Lang::t('_STUDENTS', 'coursereport'), 'className' => 'img-cell')
);

if ($permissions['subscribe']) {
	$_columns[] = array('key' => 'subscription', 'label' => Get::img('course/subscribe.png', Lang::t('_SUBSCRIPTION', 'course')), 'className' => 'img-cell');
}

if ($permissions['mod']) {
	$_columns[] = array('key' => 'edit', 'label' => Get::img('standard/edit.png', Lang::t('_MOD', 'course')), 'className' => 'img-cell');
}

if ($permissions['del'] && !Get::cfg('demo_mode')) {
	$_columns[] = array('key' => 'del', 'label' => Get::img('standard/delete.png', Lang::t('_DEL', 'course')), 'formatter'=>'doceboDelete', 'className' => 'img-cell');
}

$_params = array(
	'id'			=> 'edition_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r='.$base_link_edition.'/geteditionlist&id_course='.$model->getIdCourse(),
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'columns'		=> $_columns,
	'fields'		=> array('id_edition', 'code', 'name', 'status', 'date_begin', 'date_end', 'students', 'subscription', 'edit', 'del'),
	'show'			=> 'table',
	'delDisplayField' => 'name'
);

if ($permissions['add']) {
	$_params['rel_actions'] = '<a class="ico-wt-sprite subs_add" href="index.php?r='.$base_link_edition.'/add&amp;id_course='.$model->getIdCourse().'"><span>'.Lang::t('_ADD', 'subscribe').'</span></a>';
}

$this->widget('table', $_params);

?>
</div>
<script type="text/javascript">
var StatusList = {
<?php
	$conds = array();
	$list = $model->getStatusForDropdown();
	foreach ($list as $id_status => $name_status) {
		$conds[] = 'status_'.$id_status.': "'.str_replace('"', '\\'.'"', $name_status).'"';
	}
	if (!empty($conds)) {
		echo implode(','."\n", $conds);
	}
?>
};

var statusFormatter = function(elLiner, oRecord, oColumn, oData) {
	var index = 'status_'+oData;
	elLiner.innerHTML = StatusList[index] || "";
}
</script>