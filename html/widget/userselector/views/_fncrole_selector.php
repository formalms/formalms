<?php
	/*
	 * parameters:
	 * $id = the id of the selector
	 * $initial_selection = array of selected groups' idst
	 *
	 */

	$_varname = 'FncroleSelector_'.$id;
?>
<script type="text/javascript">
var <?php echo $_varname; ?> = new FncroleSelector("<?php echo $id; ?>", {
	langs: {},
	filterText: "<?php echo $filter_text; ?>",
	useFormInput: <?php echo $use_form_input ? 'true' : 'false'; ?>
});

function _fncroleSelectorInitEvent() {
	<?php echo $_varname; ?>.setTable(this); //this = DataTable object
	<?php echo $_varname; ?>.initEvent.call(this);
	YAHOO.util.Dom.get("fncrole_filter_text_<?php echo $id; ?>").value = <?php echo $_varname; ?>.filterText;
}
</script>
<?php if ($use_form_input && $separate_input) echo '<input type="hidden" id="userselector_input_'.$id.'_fncrole" name="userselector_input['.$id.'][fncrole]" value="" />'; ?>
<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="fncroleselector_simple_filter_options">
			<?php
				echo Form::getInputTextfield("search_t", "fncrole_filter_text_".$id, "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("fncrole_filter_set_".$id, "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("fncrole_filter_reset_".$id, "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
				echo '<div id="fncrole_filter_text_'.$id.'_container"></div>';
			?>
		</div>
	</div>
</div>
<?php

$rel_action_over = '<span>'
	.'<b id="num_fncroles_selected_top_'.$id.'">'.(int)(isset($num_fncroles_selected) ? $num_fncroles_selected : '0').'</b> '.Lang::t('_SELECTED', 'fncroles')
	.'</span>';

$rel_action_bottom = '<span>'
	.'<b id="num_fncroles_selected_bottom_'.$id.'">'.(int)(isset($num_fncroles_selected) ? $num_fncroles_selected : '0').'</b> '.Lang::t('_SELECTED', 'fncroles')
	.'</span>';

$columns = array(
	array('key' => 'name', 'label' => Lang::t('_NAME', 'fncroles'), 'sortable' => true, 'formatter' => $_varname.'.labelFormatter'),
	array('key' => 'group', 'label' => Lang::t('_GROUPS', 'standard'), 'sortable' => true, 'formatter' => $_varname.'.labelFormatter'),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true, 'formatter' => $_varname.'.labelFormatter'),
	array('key' => 'users', 'label' => Lang::t('_USERS', 'standard'), 'className' => 'img-cell'),
);

$params = array(
	'id' => 'fncrole_selector_table_'.$id,
	'ajaxUrl' => 'ajax.adm_server.php?r=widget/userselector/getfncroletabledata',
	'rowsPerPage' => Get::sett('visuItem', 25),
	'startIndex' => 0,
	'results' => Get::sett('visuItem', 25),
	'sort' => 'name',
	'dir' => 'asc',
	'columns' => $columns,
	'fields' => array('id', 'name', 'description', 'group', 'users'),
	'generateRequest' => $_varname.'.requestBuilder',
	'stdSelection' => true,
	'stdSelectionField' => '_checked',
	'selectAllAdditionalFilter' => $_varname.'.selectAllAdditionalFilter',
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'events' => array(
		'initEvent' => '_fncroleSelectorInitEvent'
	),
	'initialSelection' => (isset($initial_selection) && is_array($initial_selection) ? $initial_selection : array())
);

$this->widget('table', $params);

?>