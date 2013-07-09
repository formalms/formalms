<?php
$title_area = array(
	'index.php?r=alms/communication/show' => Lang::t('_COMMUNICATIONS', 'communication'),
	Lang::t('_ASSIGN_USERS', 'communication')
);

$user_selector->loadSelector(	'index.php?r=alms/communication/mod_user&id_comm='.$id_comm,
								$title_area,
								'',
								true);
?>