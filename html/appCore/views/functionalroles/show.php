<?php echo getTitleArea(Lang::t('_FUNCTIONAL_ROLE', 'fncroles')); ?>
<div class="std_block">
<?php

//--- SEARCH FILTER -------

$this->widget('tablefilter', array(
	'id' => 'functionalroles',
	'filter_text' => $filter_text,
	'js_callback_set' => 'FunctionalRoles.setFilter',
	'js_callback_reset' => 'FunctionalRoles.resetFilter'
));


//--- TABLE -------

$icon_users = '<span class="ico-sprite subs_users"><span>'.Lang::t('_USERS', 'standard').'</span></span>';
$icon_competences = '<span class="ico-sprite subs_competence"><span>'.Lang::t('_COMPETENCES', 'competences').'</span></span>';
//$icon_courses = '<span class="ico-sprite subs_course"><span>'.Lang::t('_COURSES', 'standard').'</span></span>';
$icon_mod = '<span class="ico-sprite subs_mod"><span>'.Lang::t('_MOD', 'standard').'</span></span>';
$icon_del = '<span class="ico-sprite subs_del"><span>'.Lang::t('_DEL', 'standard').'</span></span>';

$icon_show = '<span class="ico-sprite subs_course"><span>'.Lang::t('_COURSES', 'fncroles').'</span></span>';
$icon_gap_analisys = '<span class="ico-sprite subs_view"><span>'.Lang::t('_GAP_ANALYSIS', 'fncroles').'</span></span>';

$columns = array(
	array('key' => 'group', 'label' => Lang::t('_GROUPS', 'standard'), 'sortable' => true),
	array('key' => 'name', 'label' => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'), 'sortable' => true),
	array('key' => 'description', 'label' => Lang::t('_DESCRIPTION', 'standard')),
	array('key' => 'users', 'label' => $icon_users, 'formatter'=>'FunctionalRoles.usersFormatter', 'className' => 'img-cell'),
	array('key' => 'competences', 'label' => $icon_competences, 'formatter'=>'FunctionalRoles.competencesFormatter', 'className' => 'img-cell'),
	//array('key' => 'courses', 'label' => $icon_courses, 'formatter'=>'FunctionalRoles.coursesFormatter', 'className' => 'img-cell'),
	
	array('key' => 'show_courses', 'label' => $icon_show, 'formatter'=>'FunctionalRoles.showCoursesFormatter', 'className' => 'img-cell'),
	array('key' => 'gap_analisys', 'label' => $icon_gap_analisys, 'formatter'=>'FunctionalRoles.gapAnalisysFormatter', 'className' => 'img-cell')
);

if ($permissions['mod']) $columns[] = array('key' => 'mod', 'label' => $icon_mod, 'formatter'=>'stdModify', 'className' => 'img-cell');
if ($permissions['del']) $columns[] = array('key' => 'del', 'label' => $icon_del, 'formatter'=>'stdDelete', 'className' => 'img-cell');

$rel_action_over = '<a id="man_groups_over" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r=adm/functionalroles/show_groups" title="'.Lang::t('_MANAGE_GROUPS', 'fncroles').'">'
	.'<span>'.Lang::t('_MANAGE_GROUPS', 'fncroles').'</span></a>'
	.($permissions['add'] ? '<a id="add_fncrole_over" class="ico-wt-sprite subs_add" '
	.'href="ajax.adm_server.php?r=adm/functionalroles/add_fncrole" title="'.Lang::t('_ADD', 'fncroles').'">'
	.'<span>'.Lang::t('_ADD', 'fncroles').'</span></a>' : '');

$rel_action_bottom = '<a id="man_groups_bottom" class="ico-wt-sprite subs_mod" '
	.'href="index.php?r=adm/functionalroles/show_groups" title="'.Lang::t('_MANAGE_GROUPS', 'fncroles').'">'
	.'<span>'.Lang::t('_MANAGE_GROUPS', 'fncroles').'</span></a>'
	.($permissions['add'] ? '<a id="add_fncrole_bottom" class="ico-wt-sprite subs_add" '
	.'href="ajax.adm_server.php?r=adm/functionalroles/add_fncrole" title="'.Lang::t('_ADD', 'fncroles').'">'
	.'<span>'.Lang::t('_ADD', 'fncroles').'</span></a>' : '');

if ($permissions['add']) {
	$this->widget('dialog', array(
		'id' => 'add_fncrole_dialog',
		'dynamicContent' => true,
		'ajaxUrl' => 'ajax.adm_server.php?r=adm/functionalroles/add_fncrole',
		'renderEvent' => 'FunctionalRoles.dialogRenderEvent',
		'callback' => 'function() { this.destroy(); DataTable_fncroles_table.refresh(); }',
		'callEvents' => array(
			array('caller' => 'add_fncrole_over', 'event' => 'click'),
			array('caller' => 'add_fncrole_bottom', 'event' => 'click')
		)
	));
}


$this->widget('table', array(
	'id'			=> 'fncroles_table',
	'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/functionalroles/gettabledata',
	'rowsPerPage'	=> Get::sett('visuItem', 25),
	'startIndex'	=> 0,
	'results'		=> Get::sett('visuItem', 25),
	'sort'			=> 'name',
	'dir'			=> 'asc',
	'generateRequest' => 'FunctionalRoles.requestBuilder',
	'columns'		=> $columns,
	'fields'		=> array('group', 'id', 'name', 'description', 'users', 'competences', 'courses', 'mod', 'del'),
	'rel_actions' => array($rel_action_over, $rel_action_bottom),
	'delDisplayField' => 'name',
	'stdModifyRenderEvent' => 'FunctionalRoles.dialogRenderEvent',
	'events' => array(
		'beforeRenderEvent' => 'FunctionalRoles.beforeRenderEvent',
		'postRenderEvent' => 'FunctionalRoles.postRenderEvent'
	)
));


?>
</div>
<script type="text/javascript">
FunctionalRoles.init({
	currentLanguage: "<?php echo getLanguage(); ?>",
	langs: {
		_MOD: "<?php echo Lang::t('_MOD', 'standard'); ?>",
		_USERS: "<?php echo Lang::t('_USERS', 'standard'); ?>",
		_COMPETENCES: "<?php echo Lang::t('_COMPETENCES', 'competences'); ?>",
		_COURSES: "<?php echo Lang::t('_COURSES', 'fncroles'); ?>",
		_GAP_ANALYSIS: "<?php echo Lang::t('_GAP_ANALYSIS', 'fncroles'); ?>"
	}
});
</script>