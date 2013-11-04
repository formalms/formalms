<br />

<?php
	$array_title = array(	'index.php?r=adm/adminrules/show' => Lang::t('_ADMIN_RULES', 'adminrules'),
							Lang::t('_ADMIN_MANAGE', 'adminrules').' - '.$model->getGroupName($idst));

	echo	getTitleArea($array_title)
			.'<div class="std_block">';

	$add_url = 'index.php?r=adm/adminrules/add_admin&amp;idst='.$idst.'&amp;load=1';

	$rel_action = '<a class="ico-wt-sprite subs_add" href="'.$add_url.'"><span>'.Lang::t('_ADD', 'adminrules').'</span></a>';


	$this->widget('table', array(
		'id'			=> 'admin_rules_table',
		'ajaxUrl'		=> 'ajax.adm_server.php?r=adm/adminrules/getAdmins&idst='.$idst.'&',
		'rowsPerPage'	=> Get::sett('visuItem', 25),
		'startIndex'	=> 0,
		'results'		=> Get::sett('visuItem', 25),
		'sort'			=> 'userid',
		'dir'			=> 'asc',
		'columns'		=> array(
			array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'adminrules'), 'sortable' => true),
			array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'adminrules'), 'sortable' => true),
			array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'adminrules'), 'sortable' => true),
			array('key' => 'del', 'label' => Get::img('standard/delete.png', Lang::t('_DEL', 'adminrules')), 'formatter'=>'doceboDelete', 'className' => 'img-cell')
		),
		'fields'		=> array('id_user', 'userid', 'firstname', 'lastname', 'del'),
		'stdSelection' => false,
		'rel_actions' => $rel_action,
		'delDisplayField' => 'groupid'
	));
?>

</div>