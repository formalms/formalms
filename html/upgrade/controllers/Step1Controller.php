<?php



require_once(dirname(__FILE__).'/StepController.php');

/**
 * The first step is only for language selection and info display
 */
Class Step1Controller extends StepController {

	public $step = 1;

	public function validate() {

		$platform_arr = getPlatformArray();
		$_SESSION['platform_arr'] = $platform_arr;

		return true;
	}

}
