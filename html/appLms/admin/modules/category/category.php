<?php defined("IN_FORMA") or die('Direct access is forbidden.');



function categoryDispatch($op, &$treeView) {
	switch($op) {
		case "newfolder" : {
			 $GLOBALS['page']->add($treeView->loadNewFolder(), 'content');
		};break;
		case "renamefolder" : {
			$GLOBALS['page']->add($treeView->loadRenameFolder(), 'content');
		};break;
		case "movefolder" : {
			$GLOBALS['page']->add($treeView->loadMoveFolder(), 'content');
		};break;
		case "deletefolder" : {
			$GLOBALS['page']->add($treeView->loadDeleteFolder(), 'content');
		};break;
	}
}

?>