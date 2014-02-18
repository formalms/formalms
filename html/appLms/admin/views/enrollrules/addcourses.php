<?php
echo getTitleArea(array(
	'index.php?r=alms/enrollrules/show' => Lang::t('_ENROLLRULES', 'enrollrules'),
	'index.php?r=alms/enrollrules/'.( $rule->rule_type == 'base' ? 'modbaseelem' : 'rule' ).'&amp;id_rule='.$rule->id_rule => Lang::t('_MANAGE', 'enrollrules').': '.$rule->title,
	Lang::t('_COURSES', 'enrollrules')
));
?>
<div class="std_block">
<?php
echo Form::openForm('course_enrollrules', 'index.php?r=alms/enrollrules/addcourses')
	.Form::getHidden('id_rule', 'id_rule', $rule->id_rule)
	.$course_selector->loadSelector(true, true)
	.Form::openButtonSpace()
	.Form::getButton('save', 'save', Lang::t('_SAVE', 'standard'))
	.Form::getButton('undo', 'undo', Lang::t('_UNDO', 'standard'))
	.Form::closeButtonSpace()
	.Form::closeForm();
?>
</div>