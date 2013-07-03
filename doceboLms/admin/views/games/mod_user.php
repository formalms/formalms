<?php
$title_area = array(
	'index.php?r=alms/games/show' => Lang::t('_CONTEST', 'games'),
	Lang::t('_ASSIGN_USERS', 'games')
);

$user_selector->loadSelector(	'index.php?r=alms/games/mod_user&id_game='.$id_game,
								$title_area,
								'',
								true);
?>