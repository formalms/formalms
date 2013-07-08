<?php Get::title(array(
	'index.php?r=adm/publicadminmanager/show' => Lang::t('_PUBLIC_ADMIN_MANAGER', 'menu'),
	Lang::t('_ASSIGN_USERS', 'adminmanager').' : '.$model->getAdminFullname($id_user)
)); ?>
<div class="std_block">
<?php
echo Form::openForm('main_selector_form', 'index.php?r=adm/publicadminmanager/users&amp;id_user='.$id_user.'&');

$this->widget('userselector', array(
	'id' => 'main_selector',
	'show_user_selector' => true,
	'show_group_selector' => true,
	'show_orgchart_selector' => true,
	'show_fncrole_selector' => true,
	'initial_selection' => $user_alredy_subscribed,
	'admin_filter' => true
));

echo Form::openButtonSpace()
	.Form::getButton('okselector', 'okselector', Lang::t('_NEXT', 'standard'))
	.Form::getButton('cancelselector', 'cancelselector', Lang::t('_UNDO', 'standard'))
	.Form::closeButtonSpace();

echo Form::closeForm();

?>
</div>