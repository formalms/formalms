<br />

<?php
	$array_title = array(	'index.php?r=adm/adminrules/show' => Lang::t('_ADMIN_RULES', 'adminrules'),
							'index.php?r=adm/adminrules/admin_manage&amp;idst='.$idst => Lang::t('_ADMIN_MANAGE', 'adminrules').' - '.$model->getGroupName($idst),
							Lang::t('_ADD_ADMIN', 'adminrules'));

	$user_selector->loadSelector(	'index.php?r=adm/adminrules/add_admin&amp;idst='.$idst.'&',
									$array_title,
									Lang::t('_CHOOSE_ADMIN', 'adminrules'),
									true);
?>