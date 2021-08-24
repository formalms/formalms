<?php
if(!$id_date && !$id_edition) {
	Get::title(array(
		'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSES', 'course'),
		Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name
	));
}
elseif($id_edition && !$id_date)
{
	Get::title(array(
		'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
		'index.php?r='.$this->link_edition.'/show&amp;id_course='.$id_course.'' => Lang::t('_EDITIONS', 'course'),
		Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name
	));
}
else {
	Get::title(array(
		'index.php?r='.$this->link_course.'/show' => Lang::t('_COURSE', 'course'),
		'index.php?r='.$this->link_classroom.'/classroom&amp;id_course='.$id_course.'' => Lang::t('_CLASSROOM', 'course'),
		Lang::t('_SUBSCRIBE', 'subscribe').' : '.$course_name
	));
}
?>
<div class="std_block">
<p id="fast_subscribe_result" class="container-feedback" style="visibility:hidden;"><span class="ico-wt-sprite fd_info"></span></p>
<?php echo $back_link; ?>
<div class="quick_search_form qsf_left">
	<div>
		<?php
			echo '<label for="fast_subscribe">'.Lang::t('_SUBSCRIBE', 'subscribe').'</label>:&nbsp;';
			echo Form::getInputTextfield("search_t", 'fast_subscribe', 'fast_subscribe', '', '', 255, '');
			echo Form::getButton("fast_subscribe_b", "fast_subscribe_b", Lang::t('_SUBSCRIBE', 'standard'), "plus_b");
			echo '<div id="fast_subscribe_container"></div>';
			echo Form::getHidden('fast_subscribe_idst', 'fast_subscribe_idst', '0');

			echo '&nbsp;&nbsp;&nbsp;';
			echo Form::getInputCheckbox('fast_subscribe_send_alert', 'send_alert', '1', false, '');
			echo '&nbsp;'.Lang::t('_SEND_ALERT', 'subscribe');
		?>
	</div>
</div>
<div class="quick_search_form">
	<div>
		<?php
			echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
			echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
			echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
		?>
	</div>
	<a id="advanced_search" class="advanced_search" href="javascript:;"><?php echo Lang::t("_ADVANCED_SEARCH", 'standard'); ?></a>
	<div id="advanced_search_options" class="advanced_search_options" style="display: <?php echo $is_active_advanced_filter ? 'block' : 'none'; ?>">
		<?php
			//filter inputs

			$_orgchart_after = '<br />'.Form::getInputCheckbox('filter_descendants', 'filter_descendants', 1, $filter_descendants ? true : false, "")
				.'&nbsp;<label for="filter_descendants">'.Lang::t('_ORG_CHART_INHERIT', 'organization_chart').'</label>';
			echo Form::getDropdown(Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'), 'filter_orgchart', 'filter_orgchart', $orgchart_list, (int)$filter_orgchart, $_orgchart_after);
			echo Form::getDatefield(Lang::t('_VALID_AT_DATE', 'subscribe'), 'filter_date_valid', 'filter_date_valid', $filter_date_valid);

			$arr_filter = array(
				0 => Lang::t('_ALL', 'standard'),
				1 => Lang::t('_ONLY_EXPIRED', 'subscribe'),
				2 => Lang::t('_NOT_EXPIRED_WITH_DATE', 'subscribe'),
				3 => Lang::t('_NOT_EXPIRED_WITHOUT_DATE', 'subscribe')
			);
			echo Form::getDropdown(Lang::t('_SHOW_ONLY', 'subscribe'), 'filter_show', 'filter_show', $arr_filter, $filter_show);

			//buttons
			echo Form::openButtonSpace();
			echo Form::getButton("set_advanced_filter", "set_advanced_filter", Lang::t('_SEARCH', 'standard'), false, '', false);
			echo Form::getButton("reset_advanced_filter", "reset_advanced_filter", Lang::t('_UNDO', 'standard'), false, '', false);
			echo Form::closeButtonSpace();
		?>
	</div>
</div>
<div class="nofloat"></div>
<?php

$add_url = 'index.php?r='.$this->link.'/add&amp;load=1&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';
$mod_url = 'ajax.adm_server.php?r='.$this->link.'/multimod_dialog&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';
$del_url = 'ajax.adm_server.php?r='.$this->link.'/multidel&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';
$imp_csv = 'index.php?r='.$this->link.'/import_csv&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';
$imp_course = 'index.php?r='.$this->link.'/import_course&amp;load=1&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';

$copy_course = 'index.php?r='.$this->link.'/copy_course&amp;load=1&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';
$move_course = 'index.php?r='.$this->link.'/copy_course&amp;load=1&amp;move=1&amp;id_course='.$id_course.'&amp;id_edition='.$id_edition.'&amp;id_date='.$id_date.'';

$rel_action = '<a class="ico-wt-sprite subs_add" href="'.$add_url.'"><span>'.Lang::t('_ADD', 'subscribe').'</span></a>'
	.'<a class="ico-wt-sprite subs_mod" href="'.$mod_url.'"><span>'.Lang::t('_MOD_SELECTED', 'subscribe').'</span></a>'
	.'<a class="ico-wt-sprite subs_del" href="'.$del_url.'"><span>'.Lang::t('_DEL_SELECTED', 'subscribe').'</span></a>'
	.($id_edition != 0 || $id_date != 0 ? '' : '<a class="ico-wt-sprite subs_dup" href="'.$imp_course.'"><span>'.Lang::t('_IMPORT_FROM_COURSE', 'subscribe').'</span></a>')
	.'<a class="ico-wt-sprite subs_import" href="'.$imp_csv.'"><span>'.Lang::t('_IMPORT', 'subscribe').'</span></a>'

	.($id_edition != 0 || $id_date != 0 ? '' : '<a class="ico-wt-sprite subs_copy" href="'.$copy_course.'"><span>'.Lang::t('_COPY_TO_COURSE', 'subscribe').'</span></a>')
	.($id_edition != 0 || $id_date != 0 ? '' : '<a class="ico-wt-sprite subs_move" href="'.$move_course.'"><span>'.Lang::t('_MOVE_TO_COURSE', 'subscribe').'</span></a>')

	.'&nbsp;&nbsp;&nbsp;&nbsp;';

$count_selected_over = '<span>'
	.'<b id="num_users_selected_top">'.(int)(isset($num_users_selected) ? $num_users_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
	.'</span>';

$count_selected_bottom = '<span>'
	.'<b id="num_users_selected_bottom">'.(int)(isset($num_users_selected) ? $num_users_selected : '0').'</b> '.Lang::t('_SELECTED', 'admin_directory')
	.'</span>';


$icon_unset = '<span class="ico-sprite subs_cancel" title="'.Lang::t('_RESET_VALIDITY_DATES', 'subscribe').'"><span>'.Lang::t('_RESET_VALIDITY_DATES', 'subscribe').'</span></span>';
$icon_delete = '';


$dyn_labels = array();
$dyn_filter = array();
for ($i=0; $i<$num_var_fields; $i++) {
	$label = '<select id="_dyn_field_selector_'.$i.'" name="_dyn_field_selector['.$i.']">';
	foreach ($fieldlist as $key => $value) {
		 $label .= '<option value="'.$key.'"'
			.( $selected[$i] == $key ? ' selected="selected"' : '' )
			.'>'.$value.'</option>';
	}
	$label .= '</select>';
	//$label .= '<a id="_dyn_field_sort_'.$i.'" href="javascript:;">';
	//$label .= '<img src="'.Get::tmpl_path().'images/standard/sort.png" title="'.Lang::t('_SORT', 'standard').'" alt="'.Lang::t('_SORT', 'standard').'" />';
	//$label .= '</a>';
	$dyn_filter[$i] = $selected[$i];
	$dyn_labels[$i] = $label;
}


$columns = array();
$columns[] = array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'subscribe'), 'sortable' => true, 'formatter' => 'Subscription.labelFormatter');
$columns[] = array('key' => 'fullname', 'label' => Lang::t('_FULLNAME', 'subscribe'), 'sortable' => true, 'formatter' => 'Subscription.labelFormatter');
for ($i=0; $i<$num_var_fields; $i++) {
	$columns[] = array('key' => '_dyn_field_'.$i, 'label' => $dyn_labels[$i]);
}
$columns[] = array('key' => 'level', 'label' => Lang::t('_LEVEL', 'subscribe'), 'sortable' => true,
			'formatter' => 'Subscription.levelFormatter',
			'editor' => 'new YAHOO.widget.DropdownCellEditor({dropdownOptions:'.$level_list_js.'})');
$columns[] = array('key' => 'status', 'label' => Lang::t('_STATUS', 'subscribe'), 'sortable' => true,
			'formatter' => 'Subscription.statusFormatter',
			'editor' => 'new YAHOO.widget.DropdownCellEditor({dropdownOptions:'.$status_list_js.'})');


$columns[] = array("key"=>"date_begin", "label"=>Lang::t("_DATE_BEGIN_VALIDITY", 'subscribe'), "sortable"=>true, "formatter" => 'Subscription.dateFormatter',
	"editor"=>'new YAHOO.widget.DateCellEditor({asyncSubmitter: Subscription.asyncSubmitter})', "className"=>'img-cell', "hidden"=>$hidden_validity);
$columns[] = array("key"=>"date_expire", "label"=>Lang::t("_DATE_EXPIRE_VALIDITY", 'subscribe'), "sortable"=>true, "formatter" => 'Subscription.dateFormatter',
	"editor"=>'new YAHOO.widget.DateCellEditor({asyncSubmitter: Subscription.asyncSubmitter})', "className"=>'img-cell', "hidden"=>$hidden_validity);
$columns[] = array("key"=>"date_unset", "label"=>$icon_unset, 'formatter' => 'Subscription.resetDatesFormatter', 'className' => 'img-cell', "hidden"=>$hidden_validity);

$columns[] = array('key' => 'del', 'label' => Get::img('standard/delete.png', Lang::t('_DEL', 'subscribe')), 'formatter'=>'doceboDelete', 'className' => 'img-cell');

$event = new \appCore\Events\Core\User\UsersShowEvent();

$event->setColumns($columns);

$event->setHiddenValidity($hidden_validity);


$tfields = array('id', 'userid', 'fullname', 'level', 'status', 'date_begin', 'date_expire', 'date_begin_timestamp', 'date_expire_timestamp', 'del', 'overbooking');
for ($i=0; $i<$num_var_fields; $i++) {
	$tfields[] = '_dyn_field_'.$i;
}

$event->setFields($tfields);

\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersShowEvent::USERS_SHOW_COLUMNS, $event);


$this->widget('table', array(
	'id'			=> 'subscribed_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r='.$this->link.'/getlist&id_course='.$id_course.'&id_edition='.$id_edition.'&id_date='.$id_date.'&',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'userid',
	'dir'			=> 'asc',
    'columns'		=> $event->getColumns(),
    'fields'		=> $event->getFields(),
    'stdSelection' => true,
	'selectAllAdditionalFilter' => 'Subscription.selectAllAdditionalFilter()',
	'rel_actions' => array($rel_action.$count_selected_over, $rel_action.$count_selected_bottom),
	'delDisplayField' => 'userid',
	'generateRequest' => 'Subscription.requestBuilder',
	'editorSaveEvent' => 'Subscription.editorSaveEvent',//'YAHOO.fastSubscribe.editorSaveEvent',
	'events' => array(
		'initEvent' => 'Subscription.initEvent',
		'beforeRenderEvent' => 'Subscription.beforeRenderEvent',
		'postRenderEvent' => 'Subscription.postRenderEvent'
	)
));

echo $back_link;
?>

</div>
<script type="text/javascript">

Subscription.init(<?php echo (int)$id_course;?>, <?php echo (int)$id_edition; ?>, <?php echo (int)$id_date; ?>, {
	baseLink: "<?php echo $this->link; ?>",
	levelList: <?php echo $level_list_js; ?>,
	statusList: <?php echo $status_list_js; ?>,
	filterText: "<?php echo $filter_text; ?>",
	filterOrgChart: <?php echo (int)$filter_orgchart; ?>,
	filterDescendants: <?php echo $filter_descendants ? 'true' : 'false'; ?>,
	filterDateValid: '<?php echo $filter_date_valid; ?>',
	filterShow: <?php echo (int)$filter_show; ?>,
	overbookingStatus: <?php echo _CUS_OVERBOOKING; ?>,
	editor: '<?php //echo getSelectedHtmlEditor(); ?>',
	langs: {
		_RESET_VALIDITY_DATES: "<?php echo Lang::t('_RESET_VALIDITY_DATES', 'subscribe'); ?>",
		_OVERBOOKING: "<?php echo Lang::t('_USER_STATUS_OVERBOOKING', 'subscribe'); ?>",
		_AREYOUSURE: "<?php echo Lang::t('_AREYOUSURE', 'standard'); ?>",
		_EMPTY_SELECTION: "<?php echo Lang::t('_EMPTY_SELECTION', 'admin_directory'); ?>",
		_DEL: "<?php echo Lang::t('_DEL', 'standard'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>",
		_OPERATION_SUCCESSFUL: "<?php echo Lang::t('_OPERATION_SUCCESSFUL', 'standard'); ?>",
		_OPERATION_FAILURE: "<?php echo Lang::t('_OPERATION_FAILURE', 'subscribe'); ?>"
	},
	templatePath: "<?php echo Get::tmpl_path(); ?>",
	dynSelection: {},
	fieldList: <?php echo $fieldlist_js; ?>,
	numVarFields: <?php echo $num_var_fields; ?>
});

</script>