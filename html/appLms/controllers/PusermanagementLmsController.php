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
class PusermanagementLmsController extends UsermanagementAdmController {

	public $link = 'lms/pusermanagement';

	public function init() {
		require_once(_base_.'/lib/lib.json.php');
		$this->model = new UsermanagementAdm();
		$this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$this->numVarFields = 3;
		$this->sessionPrefix = 'pusermanagement';
		$this->permissions = array(
			'view'					=> checkPerm('view', true, 'pusermanagement', 'lms'),					//view the module
			'view_user'				=> checkPerm('view', true, 'pusermanagement', 'lms'),					//view the users list
			'add_user'				=> checkPerm('add', true, 'pusermanagement', 'lms'),					//create users
			'mod_user'				=> checkPerm('mod', true, 'pusermanagement', 'lms'),					//edit users
			'del_user'				=> checkPerm('del', true, 'pusermanagement', 'lms'),					//remove users
			'approve_waiting_user'	=> checkPerm('approve_waiting_user', true, 'pusermanagement', 'lms'),	//approve waiting users
			'view_org'				=> checkPerm('view', true, 'pusermanagement', 'lms'),					//view orgchart tree
			'add_org'				=> false,//checkPerm('mod_org', true, 'pusermanagement'),		//create orgchart branches
			'mod_org'				=> false,//checkPerm('mod_org', true, 'pusermanagement'),		//edit orgchart branches
			'del_org'				=> false,//checkPerm('mod_org', true, 'pusermanagement'),		//remove orgchart branches
			'associate_user'		=> checkPerm('mod', true, 'pusermanagement', 'lms') //checkPerm('mod_org', true, 'pusermanagement')			//associate users to orgbranches
		);
		$this->_mvc_name = 'usermanagement';
	}

	public function getPerm() {
		return array(
			'view'					=> 'standard/view.png',
			'add'					=> 'standard/add.png',
			'mod'					=> 'standard/edit.png',
			'del'					=> 'standard/delete.png',
			'approve_waiting_user'	=> 'standard/wait_alarm.png'
		);
	}

}