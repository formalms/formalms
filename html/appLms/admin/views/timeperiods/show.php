<?php echo getTitleArea(Lang::t('_TIME_PERIODS', 'time_periods')); ?>
<div class="std_block">
<?php

//Table

$columns = array(
	array('key' => 'title', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true),
	//array('key' => 'label', 'label' => Lang::t('_TAG'), 'sortable' => true, 'editor' => 'YAHOO.courseCellEditor()'),
	array('key' => 'start_date', 'label' => Lang::t('_DATE_BEGIN'), 'sortable' => true/*, 'className' => 'img-cell'*/),
	array('key' => 'end_date', 'label' => Lang::t('_DATE_END'), 'sortable' => true/*, 'className' => 'img-cell'*/)
);


if ($permissions['mod']) {
	$icon = Get::img('standard/edit.png', Lang::t('_MOD', 'standard'));
	$columns[] = array('key' => 'mod', 'label' => $icon, 'formatter'=>'doceboModify', 'className' => 'img-cell');
}
if ($permissions['del']) {
	$icon = Get::img('standard/delete.png', Lang::t('_DEL', 'standard'));
	$columns[] = array('key' => 'del', 'label' => $icon, 'formatter'=>'doceboDelete', 'className' => 'img-cell');
}


$params = array(
	'id'			=> 'timeperiods',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=alms/timeperiods/gettimeperiodslist',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'title',
	'dir'			=> 'asc',
	'columns'		=> $columns,
	'fields' => array('id', 'title', 'label', 'start_date', 'end_date', 'mod', 'del'),
	'show' => 'table',
	'generateRequest' => 'YAHOO.TimePeriods.requestBuilder',
	'delDisplayField' => 'title'
);

if ($permissions['add']) {
	$rel_actions = array(
		'<a id="add_over" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=alms/timeperiods/add"><span>'.Lang::t('_ADD', 'standard').'</span></a>',
		'<a id="add_bott" class="ico-wt-sprite subs_add" href="ajax.adm_server.php?r=alms/timeperiods/add"><span>'.Lang::t('_ADD', 'standard').'</span></a>'
	);
	$params['rel_actions'] = $rel_actions;
}

$this->widget('table', $params);
?>
</div>
<script type="text/javascript">
YAHOO.namespace("TimePeriods");

YAHOO.TimePeriods.filter = {
	text: ""
}

YAHOO.TimePeriods.requestBuilder = function(oState, oSelf) {
	var sort, dir, startIndex, results;
	oState = oState || {pagination: null, sortedBy: null};
	startIndex = (oState.pagination) ? oState.pagination.recordOffset : 0;
	results = (oState.pagination) ? oState.pagination.rowsPerPage : null;
	sort = (oState.sortedBy) ? oState.sortedBy.key : oSelf.getColumnSet().keys[0].getKey();
	dir = (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_DESC) ? "desc" : "asc";
	var filter_text = YAHOO.TimePeriods.filter.text;

	return "&results=" + results +
				"&startIndex=" + startIndex +
				"&sort=" + sort +
				"&dir=" + dir +
				(filter_text != "" ? "filter_text="+filter_text : "");
}

YAHOO.util.Event.onDOMReady(function() {
	YAHOO.util.Event.addListener(["add_over", "add_bott"], "click", function(e) {
		CreateDialog("periodstable_createDialog", {
			modal: true,
			close: true,
			visible: false,
			fixedcenter: true,
			constraintoviewport: true,
			draggable: true,
			hideaftersubmit: false,
			isDynamic: true,
			ajaxUrl: this.href,
			callback: function() {
				this.destroy();
				DataTable_timeperiods.refresh();
			}
		}).call(this, e);
	});
})
</script>