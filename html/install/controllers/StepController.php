<?php

Class StepController {

	var $step=0;
	public $err=array();
	

	public function render() {
		include_once(_installer_.'/views/Step'.(int)$this->step.'.php');
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


