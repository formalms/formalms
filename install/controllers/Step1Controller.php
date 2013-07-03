<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step1Controller extends StepController {

	var $step=1;
	
	public function validate() {

		$platform_arr=getPlatformArray();
		$_SESSION['platform_arr'] =$platform_arr;

		return true;
	}

}


?>