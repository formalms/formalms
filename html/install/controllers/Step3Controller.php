<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step3Controller extends StepController {

	var $step=3;

	
	public function validate() {
		$agree =Get::pReq('agree', DOTY_INT, 0);
		if ($agree != 1 && !isset($_SESSION['license_accepted'])) {
			return false;
		}
		else {
			$_SESSION['license_accepted']=1;
			return true;
		}
	}

}


?>