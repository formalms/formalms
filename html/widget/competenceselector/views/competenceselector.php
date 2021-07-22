<script type="text/javascript">
YAHOO.util.Event.onDOMReady(function() {
	CompetenceSelector.init({
		id: "<?php echo $id; ?>",
		currentLanguage: "<?php echo getLanguage(); ?>"
	});
});
</script>
<div>

<?php echo Form::getHidden('competences_selection_'.$id, 'competences_selection['.$id.']', ''); ?>

<div class="quick_search_form">
	<div>
		<div class="common_options">
		<?php
			echo Form::getInputCheckbox($id.'_show_descendants', 'show_descendants', '1', ($show_descendants ? true : false), '')
				.' <label class="label_normal" for="'.$id.'_show_descendants">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>';
			?>
		</div>
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputTextfield("search_t", $id."_filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton($id."_filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton($id."_filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
			?>
		</div>
	</div>
</div>

<div class="panel_left_small">
<span class="title"><?php echo(Lang::t('_ALL_CATEGORIES', 'competences')); ?></span>
<?php

$_languages = array(
	'_ROOT' => Lang::t('_CATEGORY', 'competences'),
	'_YES' => Lang::t('_CONFIRM', 'organization_chart'),
	'_NO' => Lang::t('_UNDO', 'organization_chart'),
	'_LOADING' => Lang::t('_LOADING', 'standard'),
	'_AREYOUSURE'=> Lang::t('_AREYOUSURE', 'organization_chart'),
	'_NAME' => Lang::t('_NAME', 'standard')
);

$this->widget('tree', array(
	'id' => 'competenceselector_tree',
	'ajaxUrl' => 'ajax.adm_server.php?r=widget/competenceselector/gettreedata',
	'treeClass' => 'CompetenceSelectorFolderTree',
	'treeFile' => Get::rel_path('base').'/widget/competenceselector/competenceselector.js',
	'languages' => $_languages,
	'initialSelectedNode' => (int)$selected_node,
	'show' => 'tree',
	'dragDrop' => false
));

?>
</div>

<div class="panel_right_big">
<?php

$_columns = array(
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 'formatter' => 'CompetenceSelector.labelFormatter'),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true, 'formatter' => 'CompetenceSelector.labelFormatter'),
	array('key' => 'typology', 'label' => Lang::t('_TYPOLOGY', 'competences'), 'sortable' => true, 'formatter' => 'CompetenceSelector.labelFormatter'),
	array('key' => 'type', 'label' => Lang::t('_TYPE', 'standard'), 'sortable' => true, 'formatter' => 'CompetenceSelector.labelFormatter'),
	//array('key' => 'score', 'label' => Lang::t('_SCORE', 'standard'), 'sortable' => true),
	//array('key' => 'expiration', 'label' => Lang::t('_EXPIRATION', 'competences'), 'sortable' => true)
);

$rel_action_over = '<span class="ma_selected_competences">'
	.'<b id="num_competences_selected_over">'.(int)(count($selection)).'</b> '.Lang::t('_SELECTED', 'competences')
	.'</span>';

$rel_action_bottom = '<span class="ma_selected_competences">'
	.'<b id="num_competences_selected_bottom">'.(int)(count($selection)).'</b> '.Lang::t('_SELECTED', 'competences')
	.'</span>';

$this->widget('table', array(
	'id'			=> 'competenceselector_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=widget/competenceselector/gettabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'generateRequest' => 'CompetenceSelector.requestBuilder',
	'columns'		=> $_columns,
	'fields'		=> array('id', 'name', 'description', 'typology', 'type'/*, 'score', 'expiration'*/, 'mod', 'del'),
	'stdSelection' => true,
	'stdSelectionField' => '_checked',
	'initialSelection' => $selection,
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'events' => array('initEvent' => 'CompetenceSelector.initTableEvent')
));

?>
</div>

<div class="nofloat"></div>

</div>