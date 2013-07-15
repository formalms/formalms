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
class PcompetencesLmsController extends CompetencesAdmController {

	public $link = 'lms/pcompetences';

	public function init() {
		checkPerm('mod', false, 'pcourse', 'lms');
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->model = new CompetencesAdm();

		$this->base_link_course = 'lms/pcourse';
		$this->base_link_competence = 'lms/pcompetences';
		$this->permissions = array(
			'view'				=> checkPerm('mod', true, 'pcourse'),//checkPerm('view', true, 'competences'),			//view module
			'add'				=> false,//checkPerm('mod', true, 'competences'),			//create competences
			'mod'				=> false,//checkPerm('mod', true, 'competences'),			//edit competences, create/edit/remove categories
			'del'				=> false,//checkPerm('mod', true, 'competences'),			//delete competences
			'associate_user'	=> false//checkPerm('associate_user', true, 'competences') //manage users for competence
		);

		$this->_mvc_name = 'competences';
	}

	public function getPerm()
	{
		return array(
			'view'				=> 'standard/view.png',
			'add'				=> 'standard/add.png',
			'mod'				=> 'standard/edit.png',
			'del'				=> 'standard/delete.png',
			'associate_user'	=> 'standard/moduser.png');
	}

}