<?php

require_once(dirname(__FILE__).'/StepController.php');

Class Step5Controller extends StepController {

	var $step=5;

	
	public function validate() {
		$_SESSION['adm_info'] =Get::pReq('adm_info');
		$_SESSION['lang_install'] =Get::pReq('lang_install');

		$this->saveConfig();

		return true;
	}


	private function saveConfig() {
		// ----------- Generating config file -----------------------------
		$config="";
		$fn = _installer_."/data/config_template.php";

		$config =generateConfig($fn);

		$save_fn=_base_."/config.php";
		$saved=FALSE;
		if (is_writeable($save_fn)) {

			$handle = fopen($save_fn, 'w');
			if (fwrite($handle, $config)) $saved=TRUE;
			fclose($handle);

			@chmod($save_fn, 0644);
		}

		
		$_SESSION["config_saved"] =$saved;
	}

}


?>