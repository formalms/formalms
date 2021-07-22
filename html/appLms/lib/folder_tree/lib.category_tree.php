<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */






function getNodeOptions($id_category, $is_leaf) {
	$lang =& DoceboLanguage::CreateInstance('course', 'lms');
	$node_options = array();

	$node_options[] = array(
		'id' => 'mod_'.$id_category,
		'command' => 'modify',
		//'content' => '<img src="'.Get::tmpl_path().'images/standard/edit.png" alt="'.$lang->def('_MOD').'" title="'.$lang->def('_MOD').'" />'
		'icon' => 'standard/edit.png',
		'alt' => $lang->def('_MOD')
	);

	if ($is_leaf) {
		$node_options[] = array(
			'id' => 'del_'.$id_category,
			'command' => 'delete',
			//'content' => '<img src="'.Get::tmpl_path().'images/standard/delete.png" alt="'.$lang->def('_DEL').'" title="'.$lang->def('_DEL').'" />'
			'icon' => 'standard/delete.png',
			'alt' => $lang->def('_DEL')
		);
	} else {
		$node_options[] = array(
			'id' => 'del_'.$id_category,
			'command' => false,
			//'content' => '<img src="'.Get::tmpl_path().'images/blank.png" />'
			'icon' => 'blank.png'
		);
	}

	return $node_options;
}

?>