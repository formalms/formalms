<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step7Controller extends StepController {

	var $step=7;

	
	public function validate() {
		return true;
	}

}


?>