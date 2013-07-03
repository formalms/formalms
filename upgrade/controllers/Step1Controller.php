<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

/**
 * The first step is only for language selection and info display
 */
Class Step1Controller extends StepController {

	public $step = 1;
	
	public function render() {
		include_once(_installer_.'/views/Step'.(int)$this->step.'.php');
	}

	public function validate() {

		$platform_arr = getPlatformArray();
		$_SESSION['platform_arr'] = $platform_arr;

		return true;
	}

}
