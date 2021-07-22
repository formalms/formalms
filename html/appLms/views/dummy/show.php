<?php

	$languages = array(
		'_ROOT' => Lang::t('_CATEGORY'),
		'_YES' => Lang::t('_CONFIRM'),
		'_NO' => Lang::t('_UNDO'),
		'_NEW_FOLDER_NAME' => Lang::t('_NEW_CATEGORY'),
		'_MOD' => Lang::t('_MOD'),
		'_AREYOUSURE'=> Lang::t('_AREYOUSURE'),
		'_NAME' => Lang::t('_NAME'),
		'_MOD' => Lang::t('_MOD'),
		'_DEL' => Lang::t('_DEL')
	);

	$arguments = array(
		'id' => 'tree',
		'ajaxUrl' => 'ajax.adm_server.php?plf=lms&file=category_tree&sf=folder_tree',//'ajax.server.php?r=dummy/testdata',
		'treeClass' => 'CourseFolderTree',
		'treeFile' => Get::rel_path('base').'/widget/tree/coursefoldertree.js',
		'languages' => $languages,
		'show' => 'tree'
	);

	$this->widget('tree', $arguments);

?>