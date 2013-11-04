<?php
Get::title(array(
	'index.php?r=alms/enrollrules/show' => Lang::t('_ENROLLRULES', 'enrollrules'),
	'index.php?r=alms/enrollrules/rule&amp;'.$rule->id_rule => Lang::t('_MANAGE', 'enrollrules').': '.$rule->title,
	$rule->rule_type_text
));
?>
<div class="std_block">
<?php
echo getBackUi('index.php?r=alms/enrollrules/rule&amp;'.$rule->id_rule, Lang::t('_BACK', 'standard'));

echo Form::openForm('enrollrule_form', 'index.php?r=alms/enrollrules/addentity')
	.Form::getHidden('id_rule', 'id_rule', $rule->id_rule);

$this->widget('userselector', array(
	'id' => 'entity_selection',
	'admin_filter' => true,
	'can_select_root' => false,
	'show_user_selector' => false,
	'show_fncrole_selector' => $fncrole,
	'show_orgchart_selector' => $orgchart,
	'show_group_selector' => $group,
	'initial_selection' => $init_selection
));
echo Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'))
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'))
	.Form::closeButtonSpace()
	.Form::closeForm()
?>
</div>