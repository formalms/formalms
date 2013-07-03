<?php
echo getTitleArea(Lang::t('_COURSESTATS', 'menu_course'));
?>
<div class="std_block">

<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="usermanagement_simple_filter_options" style="display: block;">
			<?php
				echo '<label for="">'.Lang::t('_FILTER', 'standard').'</label>'
					.Form::getInputDropdown(
					'dropdown',
					'filter_selection',
					'filter_selection',
					array(
						0 => Lang::t('_ALL', 'standard'),
						1 => Lang::t('_USER_STATUS_SUBS', 'standard'),
						2 => Lang::t('_USER_STATUS_BEGIN', 'standard'),
						3 => Lang::t('_USER_STATUS_END', 'standard')
					),
					(int)$filter_selection,
					''
				);
				echo '&nbsp;&nbsp;'; //some space between status filter and text filter
				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
		<a id="advanced_search" class="advanced_search" href="javascript:;"><?php echo Lang::t("_ADVANCED_SEARCH", 'standard'); ?></a>
		<div id="advanced_search_options" class="advanced_search_options" style="display: <?php echo $is_active_advanced_filter ? 'block' : 'none'; ?>">
			<?php
				//filter inputs

				$orgchart_after = '<br />'.Form::getInputCheckbox('filter_descendants', 'filter_descendants', 1, $filter_descendants ? true : false, "")
					.'&nbsp;<label for="filter_descendants">'.Lang::t('_ORG_CHART_INHERIT', 'organization_chart').'</label>';
				echo Form::getDropdown(Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'), 'filter_orgchart', 'filter_orgchart', $orgchart_list, (int)$filter_orgchart, $orgchart_after);
				echo Form::getDropdown(Lang::t('_GROUPS', 'standard'), 'filter_groups', 'filter_groups', $groups_list, (int)$filter_groups);

				//buttons
				echo Form::openButtonSpace();
				echo Form::getButton('set_advanced_filter', false, Lang::t('_SEARCH', 'standard'));
				echo Form::getButton('reset_advanced_filter', false, Lang::t('_UNDO', 'standard'));
				echo Form::closeButtonSpace();
			?>
		</div>
	</div>
</div>
<?php

$columns = array(
	array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseStats.useridFormatter', 'className' => 'min-cell'),
	array('key' => 'fullname', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'CourseStats.fullnameFormatter', 'className' => 'min-cell'),
	array('key' => 'level', 'label' => Lang::t('_LEVEL', 'standard'), 'sortable' => true, 'className' => 'min-cell'),
	array('key' => 'status', 'label' => Lang::t('_STATUS', 'standard'), 'sortable' => true, 'className' => 'min-cell', 'editor' => 'CourseStats.statusEditor')
);

foreach ($lo_list as $lo) {
	$icon = '('.$lo->type.')';//'<img alt="'.$lo->type.'" title="'.$lo->type.'" src="'.getPathImage().'lobject/'.$lo->type.'.gif" />';
	$link = '';
	switch ($lo->type) {
		case "poll": $link = '<a title="" href="index.php?r=coursestats/show_object&id_lo='.(int)$lo->id.'">'.$lo->title.'</a>'; break;
		default: $link = $lo->title;
	}
	$columns[] = array('key' => 'lo_'.$lo->id, 'label' => $link.'<br />'.$icon, 'sortable' => false, 'formatter' => 'CourseStats.LOFormatter', 'className' => 'min-cell');
}

$columns[] = array('key' => 'completed', 'label' => Lang::t('_COMPLETED', 'course'), 'sortable' => false, 'formatter' => 'CourseStats.completedFormatter', 'className' => 'min-cell');

$fields = array('id', 'userid', 'firstname', 'lastname', 'level', 'status', 'status_id', 'completed');
foreach ($lo_list as $lo) {
	$fields[] = 'lo_'.$lo->id;
}

$rel_actions = '<a href="index.php?r=coursestats/export_csv" class="ico-wt-sprite subs_csv" title="'.Lang::t('_EXPORT_CSV', 'report').'">'
				.'<span>'.Lang::t('_EXPORT_CSV', 'report').'</span></a>';

$params = array(
	'id' => 'coursestats_table',
	'ajaxUrl' => 'ajax.server.php?r=coursestats/gettabledata',
	'sort' => 'userid',
	'columns' => $columns,
	'fields' => $fields,
	'generateRequest' => 'CourseStats.requestBuilder',
	'rel_actions' => $rel_actions,
	'events' => array(
		'initEvent' => 'CourseStats.initEvent'
	),
	'scroll_x' => "100%"
);

$this->widget('table', $params);

?>
</div>
<script type="text/javascript">
	CourseStats.init({
		langs: {
			_LO_NOT_STARTED: "<?php echo Lang::t('_NOT_STARTED', 'standard'); ?>",
			_COMPLETED: "<?php echo Lang::t('_COMPLETED', 'standard'); ?>",
			_PERCENTAGE: "<?php echo Lang::t('_PERCENTAGE', 'standard'); ?>"
		},
		idCourse: <?php echo (int)$id_course; ?>,
		filterText: "<?php echo $filter_text; ?>",
		filterSelection: <?php echo (int)$filter_selection; ?>,
		filterOrgChart: <?php echo (int)$filter_orgchart; ?>,
		filterGroups: <?php echo (int)$filter_groups; ?>,
		filterDescendants: <?php echo $filter_descendants ? 'true' : 'false'; ?>,
		countLOs: <?php echo count($lo_list); ?>,
		footerData: [<?php echo $lo_totals_js; ?>],
		statusList: <?php echo $status_list; ?>
	});
</script>