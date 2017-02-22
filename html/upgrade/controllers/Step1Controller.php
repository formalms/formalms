<?php

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

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
