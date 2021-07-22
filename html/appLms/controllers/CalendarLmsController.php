<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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
