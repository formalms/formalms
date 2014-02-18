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

class Module_Item extends LmsModule {

	function hideLateralMenu() {
		
		if(isset($_SESSION['test_assessment'])) return true;
		if(isset($_SESSION['direct_play'])) return true;
		return false;
	}
	
	function loadHeader() {
		//EFFECTS: write in standard output extra header information
		global $op;
		
		switch($op) {
			case "additem" :
			case "insitem" :
			
			case "moditem" :
			case "upitem" : {
				loadHeaderHTMLEditor();
			};break;
			case "category" : {
				echo '<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />'."\n";
			};break;
		}
		return;
	}
}



?>