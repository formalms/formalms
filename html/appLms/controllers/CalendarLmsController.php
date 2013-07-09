<?php defined("IN_DOCEBO") or die('Direct access is forbidden.');

/* ======================================================================== \
| 	DOCEBO - The E-Learning Suite											|
| 																			|
| 	Copyright (c) 2008 (Docebo)												|
| 	http://www.docebo.com													|
|   License 	http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt		|
\ ======================================================================== */

class CalendarLmsController extends LmsController {

	public $name = 'calendar';

	protected $_default_action = 'show';

	public function isTabActive($tab_name) {
		return true;
	}

	public function init() {

		YuiLib::load('base,tabview');
		Lang::init('course');
	}

	public function showTask() {

		$this->render('_tabs', array());
	}

	public function allTask() {

		$this->render('calendar', array());
	}

	public function courseTask() {

		$this->render('calendar', array());
	}

	public function communicationTask() {

		$this->render('calendar', array());
	}

	public function videoconferenceTask() {

		$this->render('calendar', array());
	}

}
