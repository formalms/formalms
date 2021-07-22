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

define('_INSTALL_TOTAL_STEP', 8);
define('_UPDATE_TOTAL_STEP', 8);

class StepManager {

	public function __construct() {}

	public static function getCurrentStep() {
		if (!isset($GLOBALS['current_step'])) {
			$GLOBALS['current_step'] =Get::pReq('current_step', DOTY_INT, 1);
		}
		return (int)$GLOBALS['current_step'];
	}

	public static function getTotalStep() {
		if (INSTALL_ENV == 'install') {
			return _INSTALL_TOTAL_STEP;
		}
		else if (INSTALL_ENV == 'upgrade') {
			return _UPDATE_TOTAL_STEP;
		}
	}

	public static function loadStepController($step) {
		$cname ='Step'.(int)$step.'Controller';
		switch (INSTALL_ENV) {
			case 'upgrade': {
				$path =_upgrader_;
			} break;

			default:
			case 'install': {
				$path =_installer_;
			} break;
		}
		require_once($path.'/controllers/'.$cname.'.php');
		$res =new $cname;
		return $res;
	}

	public static function loadCurrentStep() {
		$current_step = self::getCurrentStep();
		$sc =self::loadStepController($current_step);
		echo('<script type="text/javascript">var current_step='.$current_step.';</script>');
		$sc->render();
	}

	public static function setCurrentStep($step) {
		$GLOBALS['current_step'] =(int)$step;
	}

	public static function goToNextStep() {
		$current_step = Get::pReq('current_step', DOTY_INT, 1);
		$sc =self::loadStepController($current_step);

		$next_step = $sc->getNextStep($current_step);

		if ($sc->validate()) {
			self::setCurrentStep($next_step);
		}
	}

	public static function checkFirstStep() {
		if (self::getCurrentStep() == 1 && empty($_SERVER['QUERY_STRING'])) {
			$_SESSION =array();
		}
	}

	public static function checkStep() {
		$ajax_validate =Get::gReq('ajax_validate', DOTY_INT, 0);
		$form_submit =Get::pReq('submit_form', DOTY_INT, 0);

		if ($ajax_validate) {
			$current_step =Get::pReq('step', DOTY_INT, 0);
			$sc =self::loadStepController($current_step);
			$sc->ajax_validate();
			die();
		}

		if ($form_submit == 1) {
			self::goToNextStep();
		}
		self::checkFirstStep();
	}

}
