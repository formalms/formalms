<?php


//TODO INSTALL_vs_UPGRADE: please share what you can
include_once(_base_."/db/lib.docebodb.php");
Class StepController {

	public $step = 0;
	public $err = [];
	
	public function render() {
		include_once(_upgrader_.'/views/Step'.(int)$this->step.'.php');
	}

	public function ajax_validate() {
		$this->ajax_out(['success'=>false, 'err'=> [], 'ok'=> []]);
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
