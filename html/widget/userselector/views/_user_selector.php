<?php
/*
 * parameters:
 * $id = id of the user selector;
 * $initial_selection = initial selected users;
 * $use_form_input = boolean parameter, use teh widget as an input in a form;
 * $fieldlist = list of custom fields in the platform;
 * $use_suspended = show suspended users (and relative filter)
 */
//js global variable name
$_varname = 'UserSelector_'.$id;
$event = new \appLms\Events\Widget\UserSelectorRenderJSScriptEvent();
\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Widget\UserSelectorRenderJSScriptEvent::EVENT_NAME, $event);

?>

<script type="text/javascript">
	<?php echo $event->getPrependScript(); ?>
	var <?php echo $_varname; ?> = new UserSelector("<?php echo $id; ?>", {
		imgPath: '<?php echo Get::tmpl_path(); ?>',
		langs: {
			_SORT: "<?php echo Lang::t('_SORT', 'standard'); ?>",
			_ADVANCED_SEARCH: "<?php echo Lang::t('_ADVANCED_SEARCH', 'standard'); ?>",
			_BASIC_SEARCH: "<?php echo Lang::t('_BASIC_SEARCH', 'standard'); ?>",
			_ACTIVE: "<?php echo Lang::t('_ACTIVE', 'admin_directory'); ?>",
			_SUSPENDED: "<?php echo Lang::t('_SUSPENDED', 'admin_directory'); ?>"
		},
		filterText: "<?php echo $filter_text; ?>",
		fieldList: <?php echo $fieldlist_js; ?>,
		useSuspended: <?php echo $use_suspended ? 'true' : 'false'; ?>,
		useFormInput: <?php echo $use_form_input ? 'true' : 'false'; ?>,
		numFields: <?php echo $num_var_fields; ?>
	});

	function _userSelectorInitEvent() { //TO DO: move this function out of <script> tags
		<?php echo $_varname; ?>.setTable(this);
		<?php echo $_varname; ?>.initEvent.call(this);
		YAHOO.util.Dom.get("user_filter_text_<?php echo $id; ?>").value = <?php echo $_varname; ?>.filterText;
	}
</script>
<div class="quick_search_form">
	<?php
	if ($use_suspended) { ?>
		<div class="common_options">
		<?php
		echo Form::getInputCheckbox('user_show_suspended_' . $id, 'show_suspended', '1', ($show_suspended ? true : false), '')
			.' <label class="label_normal" for="user_show_suspended_' . $id . '">' . Lang::t('_SHOW_SUSPENDED', 'organization_chart') . '</label>';
		?>
	</div>
	<?php } ?>
	<div>
		<a id="userselector_filter_selector_<?php echo $id; ?>" href="javascript:;" class="advanced_search">
			<?php echo Lang::t('_ADVANCED_SEARCH', 'standard'); ?>
		</a>
		<br />
		<div class="simple_search_box" id="userselector_simple_filter_options_<?php echo $id; ?>" style="display: inline;">
			<?php
			echo Form::getInputTextfield("search_t", "user_filter_text_" . $id, "filter_text", $filter_text, '', 255, '');
			echo Form::getButton("user_filter_set_" . $id, "filter_set", Lang::t('_SEARCH', 'standard'), "search_b", '', true, false);
			echo Form::getButton("user_filter_reset_" . $id, "filter_reset", Lang::t('_RESET', 'standard'), "reset_b", '', true, false);
			echo '<div id="user_filter_text_' . $id . '_container"></div>';
			?>
		</div>
		<div class="overlay_menu advanced_search_box" id="userselector_advanced_filter_options_<?php echo $id; ?>">
			<?php
			$dyn_data = $dynamic_filter->get(true, true);
			echo $dyn_data['html'];
			echo $dyn_data['js'];
			echo Form::openButtonSpace();
			echo Form::getButton('user_apply_dyn_filter_' . $id, 'apply_dyn_filter', Lang::t('_SEARCH', 'admin_directory'), false, '', true, false);
			echo ' ';
			echo Form::getButton('user_reset_dyn_filter_' . $id, 'reset_dyn_filter', Lang::t('_RESET', 'admin_directory'), false, '', true, false);
			echo Form::closeButtonSpace();
			?>
		</div>
	</div>
</div>
<?php
//result of the selection
if ($use_form_input && $separate_input)
	echo '<input type="hidden" id="userselector_input_' . $id . '_user" name="userselector_input[' . $id . '][user]" value="" />';

//table configuration's parameters
$dyn_labels = array();
$dyn_filter = array();
for ($i = 0; $i < $num_var_fields; $i++) {
	$label = '<select id="user_dyn_field_selector_' . $id . '_' . $i . '" name="_dyn_field_selector[' . $i . ']">';
	foreach ($fieldlist as $key => $value) {
		$label .= '<option value="' . $key . '"'
				. ( $selected[$i] == $key ? ' selected="selected"' : '' )
				. '>' . $value . '</option>';
	}
	$label .= '</select>';
	$label .= '<a id="user_dyn_field_sort_' . $id . '_' . $i . '" href="javascript:;">';
	$label .= '<img src="' . Get::tmpl_path() . 'images/standard/sort.png" title="' . Lang::t('_SORT', 'standard') . '" alt="' . Lang::t('_SORT', 'standard') . '" />';
	$label .= '</a>';
	$dyn_filter[$i] = $selected[$i];
	$dyn_labels[$i] = $label;
}

//set columns
$columns_arr = array();
$columns_arr[] = array('key' => 'userid', 'label' => Lang::t('_USERNAME'), 'sortable' => true, 'formatter' => $_varname . '.labelFormatter');
$columns_arr[] = array('key' => 'lastname', 'label' => Lang::t('_LASTNAME'), 'sortable' => true, 'formatter' => $_varname . '.labelFormatter');
$columns_arr[] = array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME'), 'sortable' => true, 'formatter' => $_varname . '.labelFormatter');
for ($i = 0; $i < $num_var_fields; $i++) {
	$columns_arr[] = array('key' => '_dyn_field_' . $i, 'label' => $dyn_labels[$i]);
}
if ($use_suspended) {
	$columns_arr[] = array('key' => 'valid', 'label' => '<span class="ico-sprite subs_unlocked"><span>' . Lang::t('_SUSPEND', 'standard') . '</span></span>', 'formatter' => $_varname . '.suspendFormatter', 'className' => 'img-cell');
}

$rel_action_over = '<span>'
	. '<b id="num_users_selected_top_' . $id . '">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
	. '</span>';

$rel_action_bottom = '<span>'
	. '<b id="num_users_selected_bottom_' . $id . '">' . (int) (isset($num_users_selected) ? $num_users_selected : '0') . '</b> ' . Lang::t('_SELECTED', 'admin_directory')
	. '</span>';

//render table
$id_org = isset($data_for_view['id_org'])?$data_for_view['id_org']:0;
$fields = array('id', 'userid', 'firstname', 'lastname', '_dyn_field_0', '_dyn_field_1', '_dyn_field_2', 'valid');

$event = new \appLms\Events\Widget\UserSelectorBeforeRenderEvent($id_org, $_varname, $columns_arr, $fields);
\appCore\Events\DispatcherManager::dispatch(\appLms\Events\Widget\UserSelectorBeforeRenderEvent::EVENT_NAME, $event);

$this->widget('table', array(
	'id' => 'user_selector_table_' . $id,
	'ajaxUrl' => 'ajax.adm_server.php?r=widget/userselector/getusertabledata&id_org='.$id_org.(isset($learning_filter) ? '&learning_filter='.$learning_filter : ''),
	'sort' => 'userid',
	'columns' => $event->getColumns(),
	'fields' => $event->getFields(),
	'stdSelection' => true,
	'stdSelectionField' => '_checked',
	'selectAllAdditionalFilter' => $_varname . '.selectAllAdditionalFilter()',
	'generateRequest' => $_varname . '.requestBuilder',
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'editorSaveEvent' => $_varname . '.editorSaveEvent',
	'events' => array(
		'initEvent' => '_userSelectorInitEvent',
		'beforeRenderEvent' => $_varname . '.beforeRenderEvent',
		'postRenderEvent' => $_varname . '.postRenderEvent'
	),
	'initialSelection' => (isset($initial_selection) && is_array($initial_selection) ? $initial_selection : array())
));
?>