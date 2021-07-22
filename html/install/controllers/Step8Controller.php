<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step8Controller extends StepController {

	var $step=8;

	
	public function validate() {
		return true;
	}

}


?>