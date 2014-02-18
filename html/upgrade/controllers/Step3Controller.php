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
 * Upgrade config file
 */
Class Step3Controller extends StepController {

	public $step = 3;

	public function validate() {

		return true;
	}

	public function getNextStep($current_step) {
		$version = $_SESSION['start_version'];
		if ( version_compare($version, '3600','>=')  &&
		     version_compare($version, '4000','<') ) {
			//docebo ce v 3.x.x =>  step 4: specific 3.x db upgrade
			$next_step = $current_step + 1;
		}
		else if ( version_compare($version, '4000','>=') &&
		          version_compare($version, '5000','<' )) {
			//docebo ce v 4.x.x => skip step 4
			$next_step = $current_step + 2;
		}
		else {
			// forma v1.x => skip step 4
			$next_step = $current_step + 2;
		}
		return ($next_step);
	}

}

?>