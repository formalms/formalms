<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Glossary extends LmsModule {
	

	function hideLateralMenu() {
		
		if(isset($_SESSION['test_assessment'])) return true;
		if(isset($_SESSION['direct_play'])) return true;
		return false;
	}
	
}

?>