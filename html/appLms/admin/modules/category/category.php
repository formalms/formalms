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