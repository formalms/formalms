<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Scorm extends LmsModule {

	//class constructor
	function __construct($module_name = '') {
		
		parent::__construct();
	}
	
	function loadBody() {
		
		include Forma::inc(_lms_ . '/modules/scorm/scorm.php');
	}
	
}
