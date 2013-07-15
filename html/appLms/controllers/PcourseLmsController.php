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

/**
 * This class is the controller for the public admin mvc that allow them to manage the users assigned.
 * In order to avoid a double definition of this module we can extend the admin module and change
 * only what we need in order to fit the lms environment
 */
class PcourseLmsController extends CourseAlmsController {

	public $link = 'lms/pcourse';

	public function init() {
		AlmsController::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->acl_man =& Docebo::user()->getAclManager();
		$this->model = new CourseAlms();

		$this->base_link_course = 'lms/pcourse';
		$this->base_link_classroom = 'lms/pclassroom';
		$this->base_link_edition = 'lms/pedition';
		$this->base_link_subscription = 'lms/psubscription';
		$this->base_link_competence = 'lms/pcompetences';

		$this->permissions = array(
			'view'			=> checkPerm('view', true, 'pcourse'),
			'add'			=> checkPerm('add', true, 'pcourse'),
			'mod'			=> checkPerm('mod', true, 'pcourse'),
			'del'			=> checkPerm('del', true, 'pcourse'),
			'moderate'		=> checkPerm('moderate', true, 'pcourse'),
			'subscribe'		=> checkPerm('subscribe', true, 'pcourse'),
			'add_category'	=> false, //checkPerm('add', true, 'pcourse', 'lms'),
			'mod_category'	=> false, //checkPerm('mod', true, 'pcourse', 'lms'),
			'del_category'	=> false, //checkPerm('del', true, 'pcourse')
		);
		$this->_mvc_name = 'course';
	}

	public function getPerm() {
		return array(
			'view'		=> 'standard/view.png',
			'add'		=> 'standard/add.png',
			'mod'		=> 'standard/edit.png',
			'del'		=> 'standard/delete.png',
			'moderate'	=> 'standard/wait_alarm.png',
			'subscribe'	=> 'standard/moduser.png'
		);
	}

}