<?php

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2010 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

require_once(dirname(__FILE__).'/StepController.php');

Class Step5Controller extends StepController {

	var $step=5; // Upgrade from version > 4040

	
	public function render() {
		$_SESSION['upgrade_ok']=true;
		$_SESSION['to_upgrade_arr']=getToUpgradeArray();
		parent::render();
	}
	
	
	public function validate() {
		return true;
	}

}

?>