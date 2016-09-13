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
include_once(_base_."/db/lib.docebodb.php");
Class StepController {

	public $step = 0;
	public $err = array();
	
	public function render() {
		include_once(_upgrader_.'/views/Step'.(int)$this->step.'.php');
	}

	public function ajax_validate() {
		$this->ajax_out(array('success'=>false, 'err'=>array(), 'ok'=>array()));
	}

	protected function ajax_out($res_arr) {
		require_once(_base_.'/lib/lib.json.php');
		$json =new Services_JSON();
		$array_j =$json->encode($res_arr);

		ob_clean();
		echo $array_j;
	}

	public function getNextStep($current_step) {

		return ($current_step + 1);
	}
	
	public function validate() {
		return false;
	}

}
