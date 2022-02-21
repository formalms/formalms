<?php defined("IN_FORMA") or die('Direct access is forbidden.');



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

		$this->render('_tabs', []);
	}

	public function allTask() {

		$this->render('calendar', []);
	}

	public function courseTask() {

		$this->render('calendar', []);
	}

	public function communicationTask() {

		$this->render('calendar', []);
	}

	public function videoconferenceTask() {

		$this->render('calendar', []);
	}

}
