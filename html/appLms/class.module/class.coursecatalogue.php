<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Coursecatalogue extends LmsModule {
	
	function loadBody() {
		
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		coursecatalogueDispatch($GLOBALS['op']);
	}
}

?>