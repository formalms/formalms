<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2011 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class Module_Scorm extends LmsModule {

	//class constructor
	function Module_Scorm($module_name = '') {
		
		parent::LmsModule();
	}
	
	function loadBody() {
		
		include( Docebo::inc(_lms_.'/modules/scorm/scorm.php') );
	}
	
}
