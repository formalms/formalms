<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

Class Step4Controller extends StepController {

	var $step=4; // Upgrade from 3.6.x to 4.0.4


	public function validate() {
		return true;
	}

}

?>