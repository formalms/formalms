<?php echo getTitleArea(Lang::t('_COMPETENCES', 'competences')); ?>
<script type="text/javascript">
<?php if (isset($result_message)) echo $result_message; ?>
YAHOO.util.Event.onDOMReady(function() {
	var oConfig = {
		selectedCategory: <?php echo (int)$selected_node; ?>,
		filterText: "<?php echo $filter_text; ?>",
		showDescendants: <?php echo $show_descendants ? 'true' : 'false'; ?>,
		currentLanguage: "<?php echo $language; ?>",
		typologies: <?php echo $typologies; ?>,
		types: <?php echo $types; ?>,
		langs: {
			_MOD: "<?php echo Lang::t('_MOD', 'standard'); ?>"
		}
	};
	Competences.init(oConfig);
});
</script>
<div class="std_block">

<div class="quick_search_form">
	<div>
		<div class="simple_search_box" id="competences_simple_filter_options" style="display: block;">
			<?php
				echo Form::getInputCheckbox('show_descendants', 'show_descendants', '1', ($show_descendants ? true : false), '')
				.' <label class="label_normal" for="show_descendants">'.Lang::t('_DIRECTORY_FILTER_FLATMODE', 'admin_directory').'</label>';

				echo '&nbsp;&nbsp;';

				echo Form::getInputTextfield("search_t", "filter_text", "filter_text", $filter_text, '', 255, '' );
				echo Form::getButton("filter_set", "filter_set", Lang::t('_SEARCH', 'standard'), "search_b");
				echo Form::getButton("filter_reset", "filter_reset", Lang::t('_RESET', 'standard'), "reset_b");
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
	'_NEW_FOLDER_NAME' => Lang::t('_ORGCHART_ADDNODE', 'organization_chart'),
	'_AREYOUSURE'=> Lang::t('_AREYOUSURE', 'organization_chart'),
	'_NAME' => Lang::t('_NAME', 'standard'),
	'_MOD' => Lang::t('_MOD', 'standard'),
	'_DEL' => Lang::t('_DEL', 'standard'),
	'_USERS' => Lang::t('_ASSIGN_USERS', 'standard')
);



$params = array(
	'id' => 'competences_tree',
	'ajaxUrl' => 'ajax.adm_server.php?r=adm/competences/gettreedata',
	'treeClass' => 'CompetencesFolderTree',
	'treeFile' => Get::rel_path('adm').'/views/competences/competences.js',
	'languages' => $_languages,
	'initialSelectedNode' => (int)$selected_node,
	//'rootActions' => $root_node_actions,
	'show' => 'tree',
	'dragDrop' => true
);

if ($permissions['add']) {

	$_tree_rel_action = '<a class="ico-wt-sprite subs_add" id="add_category" '
		.' href="ajax.adm_server.php?r=adm/competences/add_category&id='.(int)$selected_node.'" '
		.' title="'.Lang::t('_ADD', 'competences').'">'
		.'<span>'.Lang::t('_ADD', 'competences').'</span>'
		.'</a>';

	$params['rel_action'] = $_tree_rel_action;

	//Add category dialog
	$this->widget('dialog', array(
		'id' => 'add_category_dialog',
		'dynamicContent' => true,
		'ajaxUrl' => 'this.href',
		'dynamicAjaxUrl' => true,
		'callback' => 'Competences.addCategoryCallback',
		'renderEvent' => 'Competences.dialogRenderEvent',
		'callEvents' => array(
			array('caller' => 'add_category', 'event' => 'click')
		)
	));
}

$this->widget('tree', $params);

?>
</div>

<div class="panel_right_big">
<?php

$icon_users = '<span class="ico-sprite subs_users"><span>'.Lang::t('_ASSIGN_USERS', 'competences').'</span></span>';
$icon_mod = '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>';
$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$columns = array(
	array('key' => 'name', 'label' => Lang::t('_NAME', 'standard'), 'sortable' => true, 
			'editor' => 'new YAHOO.widget.TextboxCellEditor({asyncSubmitter: Competences.asyncSubmitter})'),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard'), 'sortable' => true, 
			'editor' => 'new YAHOO.widget.TextareaCellEditor({asyncSubmitter: Competences.asyncSubmitter})'),
	array('key' => 'typology', 'label' => Lang::t('_TYPOLOGY', 'competences'), 'sortable' => true, 'className' => 'img-cell', 
			'editor' => 'new YAHOO.widget.DropdownCellEditor({asyncSubmitter: Competences.asyncSubmitter, dropdownOptions: '.$typologies_dropdown.'})'),
	array('key' => 'type', 'label' => Lang::t('_TYPE', 'standard'), 'sortable' => true, 'className' => 'img-cell', 
			'editor' => 'new YAHOO.widget.DropdownCellEditor({asyncSubmitter: Competences.asyncSubmitter, dropdownOptions: '.$types_dropdown.'})')
);
if ($permissions['associate_user']) $columns[] = array('key' => 'users', 'label' => $icon_users, 'formatter'=>'Competences.usersFormatter', 'className' => 'img-cell');
if ($permissions['mod']) $columns[] = array('key' => 'mod', 'label' => $icon_mod, 'formatter'=>'Competences.modifyFormatter', 'className' => 'img-cell');
if ($permissions['del']) $columns[] = array('key' => 'del', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell');


$params = array(
	'id'			=> 'competences_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/competences/gettabledata',
	'rowsPerPage'	=> $rowsPerPage,//Get::sett('visuItem', 25),
	'startIndex'	=> $startIndex,
	'results'		=> $results,//Get::sett('visuItem', 25),
	'sort'			=> $sort,//'name',
	'dir'			=> $dir,//'asc',
	'generateRequest' => 'Competences.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('id', 'name', 'description', 'typology', 'type', 'id_typology', 'id_type', 'users', 'mod', 'del'),
	'delDisplayField' => 'name',
);

if ($permissions['add']) {
	$rel_action_over = '<a id="add_competence_over" class="ico-wt-sprite subs_add" '
		.'href="index.php?r=adm/competences/add_competence&id='.(int)$selected_node.'">'
		.'<span>'.Lang::t('_ADD_COMPETENCE', 'competences').'</span></a>';

	$rel_action_bottom = '<a id="add_competence_bottom" class="ico-wt-sprite subs_add" '
		.'href="index.php?r=adm/competences/add_competence&id='.(int)$selected_node.'">'
		.'<span>'.Lang::t('_ADD_COMPETENCE', 'competences').'</span></a>';

	$params['rel_actions'] = array($rel_action_over, $rel_action_bottom);
}

$this->widget('table', $params);

?>
</div>

<div class="nofloat"></div>

</div>