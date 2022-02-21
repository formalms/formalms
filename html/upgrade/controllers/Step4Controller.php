<?php



require_once(dirname(__FILE__).'/StepController.php');

Class Step4Controller extends StepController {

	var $step=4; // Upgrade from 3.6.x to 4.0.4


	public function validate() {
		return true;
	}

}

?>