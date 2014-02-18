<br />

<?php
	$array_title = array(	'index.php?r=adm/publicadminrules/show' => Lang::t('_PUBLIC_ADMIN_RULES', 'menu'),
							'index.php?r=adm/publicadminrules/admin_manage&amp;idst='.$idst => Lang::t('_ADMIN_MANAGE', 'adminrules').' - '.$model->getGroupName($idst),
							Lang::t('_ADD_ADMIN', 'adminrules'));

	$user_selector->loadSelector(	'index.php?r=adm/publicadminrules/add_admin&amp;idst='.$idst.'&',
									$array_title,
									Lang::t('_CHOOSE_ADMIN', 'adminrules'),
									true);
?>