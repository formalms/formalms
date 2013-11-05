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
class PsubscriptionLmsController extends SubscriptionAlmsController {

	public $link = 'lms/psubscription';

	public function init() {
		checkPerm('subscribe', false, 'pcourse', 'lms');
		require_once(_base_ . '/lib/lib.json.php');

		//Course info
		$this->id_course = Get::req('id_course', DOTY_INT, 0);
		$this->id_edition = Get::req('id_edition', DOTY_INT, 0);
		$this->id_date = Get::req('id_date', DOTY_INT, 0);

		$this->model = new SubscriptionAlms($this->id_course, $this->id_edition, $this->id_date);
		$this->json = new Services_JSON();
		$this->acl_man = Docebo::user()->getAclManager();
		$this->db = DbConn::getInstance();

		$this->permissions = array(
			'subscribe_course' => checkPerm('subscribe', true, 'pcourse', 'lms'),
			'subscribe_coursepath' => false,
			'moderate' => checkPerm('moderate', true, 'pcourse')
		);

		$this->link				= 'lms/psubscription';
		$this->link_course		= 'lms/pcourse';
		$this->link_edition		= 'lms/pedition';
		$this->link_classroom	= 'lms/pclassroom';

		$this->_mvc_name = 'subscription';
		
		$this->checkAdminLimit();
	}

	public function getPerm() {
		return array(
			'subscribe_course'	=> 'standard/moduser.png'
		);
	}

}