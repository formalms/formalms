<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step6Controller extends StepController {

	var $step=6;

	
	public function validate() {
		return true;
	}

}


?>