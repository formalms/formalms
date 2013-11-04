<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

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