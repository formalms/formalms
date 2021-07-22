<?php
echo getTitleArea(array(
	'index.php?modname=coursepath&amp;op=pathlist&amp;of_platform=lms' => Lang::t('_COURSEPATH', 'coursepath'),
	'index.php?r='.$this->link.'/show_coursepath&id_path='.(int)$id_path => Lang::t('_SUBSCRIBE', 'subscribe').' : '.$path_name,
	Lang::t('_ADD', 'subscribe')
));
?>
<div class="std_block">
<?php

echo Form::openForm('coursepath_subscriptions_form', 'index.php?r='.$this->link.'/sel_users_coursepath_action');

echo Form::getHidden('id_path', 'id_path', (int)$id_path);

$this->widget('userselector', array(
	'id' => 'coursepath_subscriptions',
	'show_user_selector' => true,
	'show_group_selector' => true,
	'show_orgchart_selector' => true,
	'show_fncrole_selector' => true,
	'initial_selection' => $user_selection,
	'admin_filter' => true
));

echo Form::openButtonSpace();
echo Form::getButton('okselector', 'okselector', Lang::t('_SAVE', 'standard'));
echo Form::getButton('cancelselector', 'cancelselector', Lang::t('_UNDO', 'standard'));
echo Form::closeButtonSpace();

echo Form::closeForm();

?>
</div>