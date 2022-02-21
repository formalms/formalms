<?php defined("IN_FORMA") or die('Direct access is forbidden.');



class Module_Htmlpage extends LmsModule {
	
	function hideLateralMenu() {
		
		if(isset($_SESSION['test_assessment'])) return true;
		if(isset($_SESSION['direct_play'])) return true;
		return false;
	}
	
	function loadHeader() {
		//EFFECTS: write in standard output extra header information
		global $op;
		
		switch($op) {
			case "addpage" :
			case "inspage" :
			
			case "modpage" :
			case "uppage" : {
				loadHeaderHTMLEditor();
			};break;
		}
		return;
	}
}



?>