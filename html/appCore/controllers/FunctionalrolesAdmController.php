<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

Class FunctionalrolesAdmController extends AdmController {

	protected $model;
	protected $json;
	protected $permissions;

	/*
	 * initialize the class
	 */
	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->model = new FunctionalrolesAdm();
		$this->permissions = array(
			'view'		=> checkPerm('view', true, 'functionalroles'),					//view fncroles
			'add'		=> checkPerm('mod', true, 'functionalroles'),						//add new fncroles
			'mod'		=> checkPerm('mod', true, 'functionalroles'),						//edit fncroles
			'del'		=> checkPerm('mod', true, 'functionalroles'),						//delete fncroles
			'associate_user'	=> checkPerm('associate_user', true, 'functionalroles')	//add/remove users from fncroles
		);
	}


	//--- error messages management ----------------------------------------------

	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "no permission":				$message = "You don't have permission to do this."; break;
			case "invalid fncrole":			$message = Lang::t('_INVALID_FNCROLE', 'fncroles'); break;
			case "invalid group":				$message = Lang::t('_INVALID_GROUP', 'fncroles'); break;
			case "invalid user":				$message = Lang::t('_INVALID_USER', 'fncroles'); break;
			case "invalid competence":	$message = Lang::t('_INVALID_COMPETENCE', 'fncroles'); break;
			case "remove user":
			case "remove comeptence":
			case "create fncrole":
			case "create group":
			case "edit fncrole":
			case "edit group":					$message = Lang::t('_ERROR_WHILE_SAVING', 'fncroles'); break;
			case "delete fncrole":
			case "delete group":				$message = Lang::t('_ERROR_WHILE_DELETING', 'fncroles'); break;
			case "":										$message = ""; break;
			default:										$message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		return $message;
	}


	//--- tasks ------------------------------------------------------------------

	
	public function showTask() {
		if (!$this->permissions['view']) {
			echo 'You can\'t access.';
			return;
		}

		//tabview widget, used in role and group editing
		Yuilib::load('tabview');
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('adm').'/views/functionalroles/functionalroles.js', true, true);

		Util::get_css('base-folder-tree.css', false, true);

		//render view
		$this->render('show', array(
			'permissions' => $this->permissions,
			'selected_group' => 0, //unused, for the moment
			'filter_text' => ""
		));
	}


	public function gettabledataTask() {
		//read from input and prepare filter and pagination variables
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');
		$id_group = Get::req('id_group', DOTY_INT, 0);

		$searchFilter = array(
			'text' => $filter_text,
			'group' => $id_group
		);

		//get total from database and validate the results count
		$total = $this->model->getFunctionalRolesTotal($searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getFunctionalRolesList($pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//format description field
				$description = strip_tags($record->description);
				if (strlen($description) > 200) {
					$description = substr($description, 0, 197).'...';
				}

				$group_name = $record->id_group <= 0
					? '<i>('.Lang::t('NONE', 'fncroles').')</i>'
					: Layout::highlight($record->group_name, $filter_text);

				//prepare output record
				$output_results[] = array(
					'group' => $group_name,
					'id' => $record->id_fncrole,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($description, $filter_text),
					'users' => property_exists($record, 'users') ? $record->users : 0,
					'competences' => property_exists($record, 'competences') ? $record->competences : 0,
					//'courses' => property_exists($record, 'courses') ? $record->courses : 0,
					'mod'		=> 'ajax.adm_server.php?r=adm/functionalroles/mod_fncrole&id='.(int)$record->id_fncrole,
					'del'		=> 'ajax.adm_server.php?r=adm/functionalroles/del_fncrole&id='.(int)$record->id_fncrole,
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}



	public function show_groupsTask() {
		$this->render('show_groups', array(
			'permissions' => $this->permissions,
			'filter_text' => ""
		));
	}


	public function getgrouptabledataTask() {
		//read from input and prepare filter and pagination variables
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array('text' => $filter_text);

		//get total from database and validate the results count
		$total = $this->model->getGroupsTotal($searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getGroupsList($pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//format description field
				$description = strip_tags($record->description);
				if (strlen($description) > 200) {
					$description = substr($description, 0, 197).'...';
				}

				//prepare output record
				$output_results[] = array(
					'id' => $record->id_group,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($description, $filter_text),
					'mod'		=> 'ajax.adm_server.php?r=adm/functionalroles/mod_group&id='.(int)$record->id_group,
					'del'		=> 'ajax.adm_server.php?r=adm/functionalroles/del_group&id='.(int)$record->id_group,
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}



	public function add_fncroleTask() {
		//check permissions: we should have add privileges to create roles
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$this->render('fncrole_editmask', array(
			'title' => Lang::t('_ADD', 'fncroles'),
			'groups_list' => $this->model->getGroupsDropdownList(),
			'json' => $this->json
		));
	}




	public function mod_fncroleTask() {
		//check permissions: we should have mod privileges to edit roles
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$output = array(
				'success' => false,
				'message' => Lang::t('_INVALID_FNCROLE', 'fncroles')
			);
			echo $this->json->encode($output);
			return;
		}

		//retrieve category info (name and description
		$info = $this->model->getFunctionalRoleInfo($id_fncrole);

		$this->render('fncrole_editmask', array(
			'title' => Lang::t('_MOD', 'fncroles'),
			'id_fncrole' => $id_fncrole,
			'id_group' => $info->id_group,
			'fncrole_langs' => $info->langs,
			'groups_list' => $this->model->getGroupsDropdownList(),
			'json' => $this->json
		));
	}



	public function add_groupTask() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$this->render('group_editmask', array(
			'title' => Lang::t('_ADD', 'fncroles'),
			'json' => $this->json
		));
	}




	public function mod_groupTask() {
		//check permissions: we should have mod privileges to edit groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_group = Get::req('id', DOTY_INT, -1);
		if ($id_group <= 0) {
			$output = array(
				'success' => false,
				'message' => Lang::t('_INVALID_GROUP', 'fncroles')
			);
			echo $this->json->encode($output);
			return;
		}

		//retrieve category info (name and description
		$info = $this->model->getGroupInfo($id_group);

		$this->render('group_editmask', array(
			'title' => Lang::t('_MOD', 'fncroles'),
			'id_group' => $id_group,
			'group_langs' => $info->langs,
			'json' => $this->json
		));
	}


	public function add_fncrole_actionTask() {
		//check permissions: we should have add privileges to create roles
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}
		
		//set up the data to insert into DB
		$id_group = Get::req('id_group', DOTY_INT, 0);
		if ($id_group < 0) $id_group = 0;
		$names = Get::req('name', DOTY_MIXED, array());
		$descriptions = Get::req('description', DOTY_MIXED, array());
		$langs = array();

		//validate inputs
		if (is_array($names)) {
			//prepare langs array
			$lang_codes = Docebo::langManager()->getAllLangcode();
			foreach ($lang_codes as $lang_code) {
				$langs[$lang_code] = array(
					'name' => (isset($names[$lang_code]) ? $names[$lang_code] : ''),
					'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : '')
				);
			}
		}

		//insert data in the DB
		$res = $this->model->createFunctionalRole($id_group, $langs);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('create fncrole')
			);
		}
		echo $this->json->encode($output);
	}


	public function add_group_actionTask() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//set up the data to insert into DB
		$names = Get::req('name', DOTY_MIXED, array());
		$descriptions = Get::req('description', DOTY_MIXED, array());
		$langs = array();

		//validate inputs
		if (is_array($names)) {
			//prepare langs array
			$lang_codes = Docebo::langManager()->getAllLangcode();
			foreach ($lang_codes as $lang_code) {
				$langs[$lang_code] = array(
					'name' => (isset($names[$lang_code]) ? $names[$lang_code] : ''),
					'description' => (isset($descriptions[$lang_code]) ? $descriptions[$lang_code] : '')
				);
			}
		}

		//insert data in the DB
		$res = $this->model->createGroup($langs);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('create group')
			);
		}
		echo $this->json->encode($output);
	}


	public function mod_fncrole_actionTask() {
		//check permissions: we should have mod privileges to edit roles
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//read inputs
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('invalid fncrole')
			);
			echo $this->json->encode($output);
			return;
		}

		$id_group = Get::req('id_group', DOTY_INT, 0);
		$_lang_name = Get::req('name', DOTY_MIXED, array());
		$_lang_desc = Get::req('description', DOTY_MIXED, array());

		$_arr_langs = array();
		$arr = Docebo::langManager()->getAllLangcode();
		foreach ($arr as $lang_code) {
			$_arr_langs[$lang_code] = array(
				'name' => (isset($_lang_name[$lang_code]) ? $_lang_name[$lang_code] : ''),
				'description' => (isset($_lang_desc[$lang_code]) ? $_lang_desc[$lang_code] : '')
			);
		}

		//update data in DB
		if ($id_group < 0) $id_group = 0;
		$res = $this->model->updateFunctionalRole($id_fncrole, $id_group, $_arr_langs);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('edit fncrole')
			);
		}
		echo $this->json->encode($output);
	}


	public function mod_group_actionTask() {
		//check permissions: we should have mod privileges to edit groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//read inputs
		$id_group = Get::req('id_group', DOTY_INT, -1);
		if ($id_group <= 0) {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('invalid group')
			);
			echo $this->json->encode($output);
			return;
		}

		$_lang_name = Get::req('name', DOTY_MIXED, array());
		$_lang_desc = Get::req('description', DOTY_MIXED, array());

		$_arr_langs = array();
		$arr = Docebo::langManager()->getAllLangcode();
		foreach ($arr as $lang_code) {
			$_arr_langs[$lang_code] = array(
				'name' => (isset($_lang_name[$lang_code]) ? $_lang_name[$lang_code] : ''),
				'description' => (isset($_lang_desc[$lang_code]) ? $_lang_desc[$lang_code] : '')
			);
		}

		//update data in DB
		$res = $this->model->updateGroup($id_group, $_arr_langs);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('edit group')
			);
		}
		echo $this->json->encode($output);
	}



	public function del_fncrole() {
		//check permissions: we should have del privileges to remove roles
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('invalid fncrole')
			);
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->deleteFunctionalRole($id_fncrole);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('delete fncrole')
			);
		}
		echo $this->json->encode($output);
	}


	public function del_group() {
		//check permissions: we should have del privileges to remove role groups
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_group = Get::req('id', DOTY_INT, -1);
		if ($id_group <= 0) {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('invalid group')
			);
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->deleteGroup($id_group);
		if ($res) {
			$output = array(
				'success' => true
			);
		} else {
			$output = array(
				'success' => false,
				'message' => $this->_getErrorMessage('delete group')
			);
		}
		echo $this->json->encode($output);
	}


	//----------------------------------------------------------------------------


	public function man_usersTask() {
		$back_url = 'index.php?r=adm/functionalroles/show';

		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $back_url
			));
			return;
		}

		$title_arr = array(
			$back_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			Lang::t('_USERS', 'fncroles').': <b>'.$this->model->getFunctionalRoleName($id_fncrole).'</b>'
		);

		$result = Get::req('res', DOTY_ALPHANUM, '');
		$result_message = "";
		switch ($result) {
			case "ok_": $result_message = UIFeedback::info(Lang::t('_RESULT_USERS_OK', 'fncroles'), true); break;
			case "err_": $result_message = UIFeedback::error(Lang::t('_RESULT_USERS_ERR', 'fncroles'), true); break;
		}

		$this->render('show_users', array(
			'id_fncrole' => $id_fncrole,
			'title_arr' => $title_arr,
			'filter_text' => "",
			'result_message' => $result_message,
			'permissions' => $this->permissions
		));
	}


	public function man_competencesTask() {
		$back_url = 'index.php?r=adm/functionalroles/show';

		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $back_url
			));
			return;
		}

		$title_arr = array(
			$back_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			Lang::t('_COMPETENCES', 'fncroles').': '.$this->model->getFunctionalRoleName($id_fncrole)
		);

		$result = Get::req('res', DOTY_ALPHANUM, '');
		$result_message = "";
		switch ($result) {
			case "ok_competences": $result_message = UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'fncroles'), true); break;
			case "err_competences": $result_message = UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'fncroles'), true); break;
		}

		$this->render('show_competences', array(
			'id_fncrole' => $id_fncrole,
			'title_arr' => $title_arr,
			'filter_text' => "",
			'result_message' => $result_message,
			'count' => $this->model->getManageCompetencesTotal($id_fncrole, false),
			'permissions' => $this->permissions
		));
	}



	public function selectallusers() {
		//read from input and prepare filter and pagination variables
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		//TO DO: if $id_fncrole <= 0 ...

		$filter_text = Get::req('filter_text', DOTY_STRING, '');
		$searchFilter = array(
			'text' => $filter_text
		);

		$output = $this->model->getManageUsersAll($id_fncrole, $searchFilter);
		echo $this->json->encode($output);
	}



	public function getusertabledataTask() {
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall": {
				$this->selectallusers();
				return;
			} break;
		}

		//read from input and prepare filter and pagination variables
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		//TO DO: if $id_fncrole <= 0 ...

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array(
			'text' => $filter_text
		);

		//get total from database and validate the results count
		$total = $this->model->getManageUsersTotal($id_fncrole, $searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getManageUsersList($id_fncrole, $pagination, $searchFilter);

		//prepare the data for sending
		$acl_man = Docebo::user()->getAclManager();
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//prepare output record
				$output_results[] = array(
					'id' => $record->idst,
					'userid' => Layout::highlight($acl_man->relativeId($record->userid), $filter_text),
					'lastname' => Layout::highlight($record->lastname, $filter_text),
					'firstname' => Layout::highlight($record->firstname, $filter_text),
					'del'		=> 'ajax.adm_server.php?r=adm/functionalroles/del_user&id_user='.(int)$record->idst.'&id_fncrole='.(int)$id_fncrole,
					'is_group' => $record->is_group
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}


	public function getcompetencetabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		//TO DO: if $id_fncrole <= 0 ...

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array(
			'text' => $filter_text
		);

		//get total from database and validate the results count
		$total = $this->model->getManageCompetencesTotal($id_fncrole, $searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getManageCompetencesList($id_fncrole, $pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {

			$cmodel = new CompetencesAdm();
			$_typologies = $cmodel->getCompetenceTypologies();
			$_types = $cmodel->getCompetenceTypes();

			foreach ($list as $idst=>$record) {
				//prepare output record
				$_str = strip_tags($record->description);
				$_description = strlen($_str)>100 ? substr($_str, 0, 97).'...' : $_str;

				$output_results[] = array(
					'id' => $record->id_competence,
					'category' => Layout::highlight($record->category, $filter_text),
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($_description, $filter_text),
					'typology' => $_typologies[$record->typology],
					'type' => $_types[$record->type],
					'score' => ($record->type == 'flag' ? '-' : $record->score),
					'expiration' => $record->expiration > 0 ? $record->expiration : Lang::t('_NEVER', 'standard'),
					'del'		=> 'ajax.adm_server.php?r=adm/functionalroles/del_competence&id_competence='.(int)$record->id_competence.'&id_fncrole='.(int)$id_fncrole,
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}


	public function sel_usersTask() {
		require_once(_adm_.'/lib/lib.directory.php');
		require_once(_adm_.'/class.module/class.directory.php');

		$base_url = 'index.php?r=adm/functionalroles/show';

		//check permissions: we should have mod privileges to assign users to the role group
		if (!$this->permissions['mod']) Util::jump_to($base_url);

		//read inputs
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $base_url
			));
			return;
		}

		//navigation urls
		$back_url = 'index.php?r=adm/functionalroles/man_users&id='.(int)$id_fncrole;
		$jump_url = 'index.php?r=adm/functionalroles/sel_users&id_fncrole='.(int)$id_fncrole;

		//competence details
		$info = $this->model->getFunctionalRoleInfo($id_fncrole);
		$name = $this->model->getFunctionalRoleName($id_fncrole);

		//page_title
		$page_title_arr = array(
			$base_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			$back_url => Lang::t('_USERS', 'fncroles').': '.$name,
			Lang::t('_ASSIGN_USERS', 'fncroles')
		);

		if (isset($_POST['cancelselector'])) {

			//--- UNDO: return to catalogue list -------------------------------------
			Util::jump_to($back_url);

		} elseif (isset($_POST['okselector'])) {

			//--- SAVE: users selection has been done --------------------------------

			$acl_man = Docebo::user()->getAclManager();
			$user_selector = new UserSelector();
			$selection = $user_selector->getSelection();

			$members_existent = $this->model->getMembers($id_fncrole);
			
			//retrieve newly selected users
			$_common_members = array_intersect($members_existent, $selection);
			$_new_members = array_diff($selection, $_common_members); //new users to add
			$_old_members = array_diff($members_existent, $_common_members); //old users to delete
			unset($_common_members); //free some memory

			//insert newly selected users in database
			$res1 = $this->model->assignMembers($id_fncrole, $_new_members);
			$res2 = $this->model->deleteMembers($id_fncrole, $_old_members);

			// apply enroll rules
			$enrollrules = new EnrollrulesAlms();
			$enrollrules->applyRulesMultiLang('_LOG_USERS_TO_FNCROLE', $_new_members, false, $id_fncrole);

			//go back to main page, with result message
			Util::jump_to($back_url.'&res='.($res1 && $res2 ? 'ok_users' : 'err_users'));

		} else {

			//--- USER SELECTION IS IN PROGRESS: show selector -----------------------
			$user_selector = new UserSelector();

			$user_selector->show_user_selector = TRUE;
			$user_selector->show_group_selector = TRUE;
			$user_selector->show_orgchart_selector = TRUE;
			$user_selector->show_fncrole_selector = FALSE; //we can't assign functional roles to other functional roles ...
			//there should be a "role selector" too ...
			//$user_select->show_orgchart_simple_selector = TRUE;

			//filter selectable user by sub-admin permission
			$acl_man = Docebo::user()->getAclManager();
			$user_selector->setUserFilter('exclude', array($acl_man->getAnonymousId()));
			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				require_once(_base_.'/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
				$admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
				$user_selector->setUserFilter('user', $admin_users);
				$user_selector->setUserFilter('group', $admin_tree);
			}

			if (Get::req('is_updating', DOTY_INT, false)) {
				//...
			} else {
				//set initial selection
				$selection = $this->model->getMembers($id_fncrole);
				$user_selector->requested_tab = PEOPLEVIEW_TAB;
				$user_selector->resetSelection($selection);
			}

			$user_selector->addFormInfo(
				Form::getHidden('is_updating', 'is_updating', 1).
				Form::getHidden('id_fncrole', 'id_fncrole', $id_fncrole)
			);

			//draw selector
			$user_selector->loadSelector(
				Util::str_replace_once('&', '&amp;', $jump_url),
				$page_title_arr,
				Lang::t('_ASSIGN_USERS_TO_FNCROLE', 'fncroles'),
				true
			);
		}
	}





	public function sel_competencesTask() {
		$base_url = 'index.php?r=adm/functionalroles/show';
		
		//check permissions: we should have mod privileges to assign competences to the role group
		if (!$this->permissions['mod']) Util::jump_to($base_url);

		//read inputs
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $base_url
			));
			return;
		}

		//navigation urls
		$back_url = 'index.php?r=adm/functionalroles/man_competences&id='.(int)$id_fncrole;
		$jump_url = 'index.php?r=adm/functionalroles/sel_competences&id_fncrole='.(int)$id_fncrole;

		//selector commands
		$save = Get::req('save', DOTY_MIXED, false);
		$undo = Get::req('undo', DOTY_MIXED, false);
		
		if ($undo !== false) {

			Util::jump_to($back_url);

		} elseif ($save !== false) {

			$selection = Get::req('competences_selection', DOTY_MIXED, array());
			$selection_str = (is_array($selection) && isset($selection['fncroles_competences_selector']) ? $selection['fncroles_competences_selector'] : "") ;
			$competences_selected = $selection_str != "" ? explode(",", $selection_str) : array();
			$competences_existent = $this->model->getCompetences($id_fncrole);

			//retrieve newly selected users
			$_common_competences = array_intersect($competences_existent, $competences_selected);
			$_new_competences = array_diff($competences_selected, $_common_competences); //new competences to add
			$_old_competences = array_diff($competences_existent, $_common_competences); //old competences to delete
			unset($_common_competences); //free some memory

			//insert newly selected users in database
			$res1 = $this->model->assignCompetences($id_fncrole, $_new_competences);
			$res2 = $this->model->deleteCompetences($id_fncrole, $_old_competences);

			//go back to main page, with result message
			Util::jump_to($back_url.'&res='.($res1 && $res2 ? 'ok_competences' : 'err_competences'));

		} else {
			$title_arr = array(
				$base_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
				$back_url => Lang::t('_COMPETENCES', 'fncroles').': <b>'.$this->model->getFunctionalRoleName($id_fncrole).'</b>',
				Lang::t('_ASSIGN', 'fncroles')
			);

			//render the courses selector
			$this->render('competences_selector', array(
				'id_fncrole' => $id_fncrole,
				'title_arr' => $title_arr,
				'selection' => $this->model->getCompetences($id_fncrole)
			));
		}
	}


	public function del_user() {
		//check permissions: we should have mod privileges to remove users from the role group
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		$id_user = Get::req('id_user', DOTY_INT, -1);
		$output = array('success' => false);
		if ($id_fncrole <= 0) {
			$output['message'] = $this->_getErrorMessage('invalid fncrole');
			echo $this->json->encode($output);
			return;
		}
		if ($id_user <= 0) {
			$output['message'] = $this->_getErrorMessage('invalid user');
			echo $this->json->encode($output);
			return;
		}
		$res = $this->model->deleteMember($id_fncrole, $id_user);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = $this->_getErrorMessage('remove user');
		echo $this->json->encode($output);
	}


	public function del_users() {
		//check permissions: we should have mod privileges to remove users from the role group
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$output['message'] = $this->_getErrorMessage('invalid fncrole');
			echo $this->json->encode($output);
			return;
		}

		$users_str = Get::req('users', DOTY_STRING, "");
		if (!$users_str) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid input'));
			echo $this->json->encode($output);
			return;
		}

		$users = explode(",", $users_str);
		if (empty($users)) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid input'));
			echo $this->json->encode($output);
			return;
		}

		$users_first = $this->model->getUsers($id_fncrole);
		$res = $this->model->deleteMembers($id_fncrole, $users);
		$users_after = $this->model->getUsers($id_fncrole);
		$users_deleted = array_diff($users_first, $users_after);

		$output = array('success' => $res ? true : false);
		if (!$res) $output['message'] = $this->_getErrorMessage('server error');
		else $output['list'] = array_values($users_deleted);

		echo $this->json->encode($output);
	}


	public function del_competence() {
		//check permissions: we should have mod privileges to remove competences from the role group
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		$output = array('success' => false);
		if ($id_fncrole <= 0) {
			$output['message'] = $this->_getErrorMessage('invalid fncrole');
			echo $this->json->encode($output);
			return;
		}
		if ($id_competence <= 0) {
			$output['message'] = $this->_getErrorMessage('invalid competence');
			echo $this->json->encode($output);
			return;
		}
		$res = $this->model->deleteCompetence($id_fncrole, $id_competence);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = $this->_getErrorMessage('remove competence');
		echo $this->json->encode($output);
	}

	
	public function man_competences_properties() {
		$base_url = 'index.php?r=adm/functionalroles/show';

		//read inputs
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $base_url
			));
			return;
		}

		//navigation urls
		$back_url = 'index.php?r=adm/functionalroles/man_competences&id='.(int)$id_fncrole;

		$list = $this->model->getCompetences($id_fncrole);
		if (count($list) <= 0) Util::jump_to($back_url); //no competences to edit
		$cmodel = new CompetencesAdm();
		$cinfo = $cmodel->getCompetencesInfo($list);

		require_once(_base_.'/lib/lib.table.php');
		$table = new Table();
		$head_label = array(
			Lang::t('_NAME', 'standard'),
			Lang::t('_DESCRIPTION', 'standard'),
			Lang::t('_TYPOLOGY', 'competences'),
			Lang::t('_TYPE', 'competences'),
			Lang::t('_MIN_SCORE', 'comeptences'),
			Lang::t('_EXPIRATION_DAYS', 'competences')
		);
		$head_style = array(
			'',
			'',
			'img-cell',
			'img-cell',
			'img-cell',
			'img-cell'
		);

		$table->addHead($head_label, $head_style);

		$count_score = 0;
		$_std_score = 0;
		$_std_expiration = 0;
		$lang_code = getLanguage();
		$properties = $this->model->getCompetencesProperties($list);
		while (list($key, $value) = each($cinfo)) {
			$line = array();

			$line[] = $value->langs[$lang_code]['name'];
			$line[] = $value->langs[$lang_code]['description'];
			$line[] = $value->typology;
			$line[] = $value->type;
			$line[] = $value->type == 'score'
				? Form::getInputTextfield('textfield', 'score_assigned_'.$key, 'properties['.$key.'][score]', $properties[$key]->score, '', 255)
				: '-'.Form::getHidden('score_flag_'.$key, 'properties['.$key.'][score]', 1);
			$line[] = Form::getInputTextfield('textfield', 'expiration_'.$key, 'properties['.$key.'][expiration]', $properties[$key]->expiration, '', 255);

			$table->addBody($line);
			if ($value->type == 'score') $count_score++;
		}

		$foot = array(
			array('colspan' => $count_score>0 ? 4 : 5)
		);
		if ($count_score > 0) {
			//set score to all competences
			$foot[] = Form::getInputTextfield('textfield', 'score_value', false, $_std_score, '', 255).'<br />'
				.Form::getButton('set_score', 'set_score', Lang::t('_SET', 'standard'), false, '', true, false)
				.Form::getButton('reset_score', 'reset_score', Lang::t('_RESET', 'standard'), false, '', true, false);
		}
		//set expiration days to all competences
		$foot[] = Form::getInputTextfield('textfield', 'expiration_value', false, $_std_expiration, '', 255).'<br />'
				.Form::getButton('set_expiration', 'set_expiration', Lang::t('_SET', 'standard'), false, '', true, false)
				.Form::getButton('reset_expiration', 'reset_expiration', Lang::t('_RESET', 'standard'), false, '', true, false);

		$table->addFoot($foot);

		$title_arr = array(
			$base_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			$back_url => Lang::t('_COMPETENCES', 'fncroles').': '.$this->model->getFunctionalRoleName($id_fncrole),
			Lang::t('_PROPERTIES', 'fncroles')
		);

		$this->render('man_competences_properties', array(
			'id_fncrole' => $id_fncrole,
			'title_arr' => $title_arr,
			'table' => $table
		));

	}


	public function man_competences_properties_action() {
		$base_url = 'index.php?r=adm/functionalroles/show';

		//read inputs
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $base_url
			));
			return;
		}

		$save = Get::req('save', DOTY_MIXED, false);
		$undo = Get::req('undo', DOTY_MIXED, false);

		//navigation urls
		$back_url = 'index.php?r=adm/functionalroles/man_competences&id='.(int)$id_fncrole;
		if ($undo) Util::jump_to($back_url);

		$properties = Get::req('properties', DOTY_MIXED, false);
		if ($properties === false || empty($properties)) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid input'),
				'back_url' => $back_url
			));
			return;
		}

		//update data in DB
		$count = 0;
		foreach ($properties as $id_competence => $property) {
			$_score = $property['score'];
			$_expiration = $property['expiration'];
			$res = $this->model->updateCompetenceProperties($id_fncrole, $id_competence, $_score, $_expiration);
			if ($res) $count++;
		}

		Util::jump_to($back_url.'&res='.($count > 0 ? 'ok_competences' : 'err_competences'));
	}


	//courses details for competences
	public function show_coursesTask() {
		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => Lang::t('_INVALID_FNCROLE', 'fncroles'),
				'back_url' => $back_url
			));
			return;
		}

		$cmodel = new CompetencesAdm();
		$competences = $this->model->getCompetences($id_fncrole);
		$properties = $this->model->getCompetencesProperties($competences, $id_fncrole);
		$competences_info = $cmodel->getCompetencesInfo($competences);
		while (list($key, $value) = each($competences_info)) {
			$value->role_score = (isset($properties[$key]) ? $properties[$key]->score : 0);
			$value->role_expiration = (isset($properties[$key]) ? $properties[$key]->expiration : 0);
		}

		$courses_info = $this->model->getCompetencesCoursesInfo($id_fncrole);
		$title_arr = array(
			'index.php?r=adm/functionalroles/show' => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			Lang::t('_COURSES_FOR_COMPETENCES', 'course').': '.$this->model->getFunctionalRoleName($id_fncrole)
		);
		
		$this->render('competences_courses', array(
			'title' => $title_arr,
			'language' => getLanguage(),
			'competences_info' => $competences_info,
			'courses_info' => $courses_info,
			'json' => $this->json
		));
	}



	public function gap_analisysTask() {
		$back_url = 'index.php?r=adm/functionalroles/show';

		$id_fncrole = Get::req('id', DOTY_INT, -1);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $back_url
			));
			return;
		}

		$title_arr = array(
			$back_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			Lang::t('_GAP_ANALYSIS', 'fncroles').': '.$this->model->getFunctionalRoleName($id_fncrole)
		);

		$result = Get::req('res', DOTY_ALPHANUM, '');
		$result_message = "";
/*
		switch ($result) {
			case "ok_gap": $result_message = Lang::t('_RESULT_GAP_OK', 'fncroles'); break;
			case "err_gap": $result_message = Lang::t('_RESULT_GAP_ERR', 'fncroles'); break;
		}
*/
  
        require_once(_adm_ . '/lib/user_selector/lib.dynamicuserfilter.php');
        $dyn_filter = new DynamicUserFilter("user_dyn_filter");
        $dyn_filter->init();
        
        require_once(_adm_ . '/lib/lib.field.php');

        $fman = new FieldList();
        $fields = $fman->getFlatAllFields(array('framework', 'lms'));

        $f_list = array(
            'email' => Lang::t('_EMAIL', 'standard'),
            'lastenter' => Lang::t('_DATE_LAST_ACCESS', 'profile'),
            'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
            'language' => Lang::t('_LANGUAGE', 'standard'),
            'level' => Lang::t('_LEVEL', 'standard')
        );
        $f_list = $f_list + $fields;
        $f_selected = $this->json->decode(Docebo::user()->getPreference('ui.directory.custom_columns'));
        if ($f_selected == false) {
            $f_selected = array('email', 'lastenter', 'register_date');
        }

        $js_arr = array();
        foreach ($f_list as $key => $value)
            $js_arr[] = $key . ': ' . $this->json->encode($value);
        $f_list_js = '{' . implode(',', $js_arr) . '}';

        $this->render('gap_analisys', array(
			'id_fncrole' => $id_fncrole,
			'num_var_fields' => 1,
			'fieldlist' => $f_list,
			'fieldlist_js' => $f_list_js,
			'selected' => $f_selected,            
			'title_arr' => $title_arr,
			'filter_text' => "",
			'result_message' => $result_message,
			'advanced_filter' => array(
				'active' => false,
				'gap_filter' => 0,
				'expire_filter' => 0
			)
		));
	}


	public function getgaptabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		//TO DO: if $id_fncrole <= 0 ...

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter_text = Get::req('filter_text', DOTY_STRING, '');
		$show_gap = Get::req('gap', DOTY_INT, 0);
		$show_expired = Get::req('expired', DOTY_INT, 0);
		
		$searchFilter = array(
			'text' => $filter_text,
			'show_gap' => $show_gap,
			'show_expired' => $show_expired
		);
        
        //get total from database and validate the results count
		$total = $this->model->getGapTotal($id_fncrole, $searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

        $dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}        

        $var_fields = Get::req('_dyn_field', DOTY_MIXED, array());
		if (stristr($sort, '_dyn_field_') !== false) {
			$index = str_replace('_dyn_field_', '', $sort);
			$sort = $var_fields[(int)$index];
		}
        
		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getGapList($id_fncrole, $pagination, $searchFilter);
 
		//prepare the data for sending
		$acl_man = Docebo::user()->getAclManager();
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//prepare output record

				$_not_obtained = $record->last_assign_date == "";
				$_date_expire = $_not_obtained ? "" : date("Y-m-d H:i:s", fromDatetimeToTimestamp($record->last_assign_date) + $record->expiration * 86400);

				$base_output_results = array(
					'idst' => $record->idst,
					'userid' => Layout::highlight($acl_man->relativeId($record->userid), $filter_text),
					'firstname' => Layout::highlight($record->firstname, $filter_text),
					'lastname' => Layout::highlight($record->lastname, $filter_text),
					'last_assign_date' => $_not_obtained ? "" : Format::date($record->last_assign_date, 'datetime'),
					'score_req' => $record->score_requested,
					'score_got' => $record->score_got,
					'competence' => $record->competence_name,
					'id_competence' => $record->id_competence,
					'type' => $record->type,
					'expiration' => $record->expiration,
					'is_expired' => $_not_obtained ? false : ($_date_expire < date("Y-m-d H:i:s") && $record->expiration > 0),

					'date_expire' => $_not_obtained ? '' : ($record->expiration > 0 ? Format::date($_date_expire, 'datetime') : Lang::t('_NEVER', 'standard')),
					'gap' => $record->gap,
				);

                $dynamic_fields_value = $this->getDynamicFieldsValue($record, $var_fields);
                     
                // merge head, dynamic fields and tail part of the line
                $temp_output_results = $base_output_results;
                foreach ($dynamic_fields_value as $dynamic_field_value) {
                    $temp_output_results = array_merge($temp_output_results, $dynamic_field_value);
                }
                
                $output_results[] = $temp_output_results;
    
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}
    
    protected function _getDynamicFilter($input) {
		$output = false;
		if (is_string($input) && $input != "") {
			$dyn_data = $this->json->decode(urldecode(stripslashes($input))); //decode the filter json string
			$output = $dyn_data;
		}
		return $output;
	}

    private function getDynamicFieldsValue($obj, $var_fields) {
        $toReturn = array();
        foreach ($var_fields as $i => $value) {
            if (is_numeric($value)) {
                $name = '_custom_' . $value;
            } else {
                $name = $value;
            }

            //check if we must perform some post-format on retrieved field values
            $content = (isset($obj->$name) ? $obj->$name : '');
            if ($name == 'register_date')
                $content = Format::date($content, 'datetime');
            if ($name == 'lastenter')
                $content = Format::date($content, 'datetime');
            if ($name == 'level' && $content != '')
                $content = Lang::t('_DIRECTORY_' . $content, 'admin_directory');
            if (!empty($date_fields) && in_array($value, $date_fields))
                $content = Format::date(substr($content, 0, 10), 'date');

            $toReturn[] = array('_dyn_field_' . $i => $content);
        }
        return $toReturn;
    }

    public function export_gap() {
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, 0);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
        
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $back_url
			));
			return;
		}

        // manage dynamic filter
        $dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}        

        $var_fields = Get::req('_dyn_field', DOTY_MIXED, array());
		if (stristr($sort, '_dyn_field_') !== false) {
			$index = str_replace('_dyn_field_', '', $sort);
			$sort = $var_fields[(int)$index];
		}
        
		//prepare csv file
		require_once(_base_.'/lib/lib.download.php');
		$format = Get::req('format', DOTY_STRING, 'csv');

		$buffer = "";
		$filename = preg_replace('/[\W]/i', '_', $this->model->getFunctionalRoleName($id_fncrole)).'_'.date("Y_m_d").'.'.$format;

		$_CSV_SEPARATOR = ',';
		$_CSV_ENDLINE = "\r\n";
        $_XLS_STARTLINE = '<tr><td>';
        $_XLS_SEPARATOR = '</td><td>';
		$_XLS_ENDLINE = "</td></tr>";

		//retrieve data to export
		$filter = false;
		$pagination = array(
			'startIndex' => 0,
			'results' => $this->model->getGapTotal($id_fncrole, $filter),
			'sort' => $sort,
			'dir' => $dir
		);
		$list = $this->model->getGapList($id_fncrole, $pagination, $searchFilter);

		//prepare the data for exporting
		$acl_man = Docebo::user()->getAclManager();

		if (is_array($list) && count($list)>0) {
            if ($format=='xls'){
                $buffer .= "<head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"></head><style>td, th { border:solid 1px black; } </style><body><table>";
            }
			foreach ($list as $idst=>$record) {

				$_not_obtained = $record->last_assign_date == "";
				$_date_expire = $_not_obtained ? "" : date("Y-m-d H:i:s", fromDatetimeToTimestamp($record->last_assign_date) + $record->expiration * 86400);

				//json encoding used for string formatting with double quotes ""
				$line_head = array(
					$this->json->encode($record->competence_name),
					$this->json->encode($acl_man->relativeId($record->userid)),
					$this->json->encode($record->firstname),
					$this->json->encode($record->lastname)
                );
                $line_tail = array(
					(int)$record->score_got,
					(int)$record->score_requested,
					(int)$record->gap*(-1),
					($_not_obtained ? "" : $this->json->encode(Format::date($record->last_assign_date, 'datetime'))),
					($_not_obtained ? "" : $this->json->encode($record->expiration > 0 ? Format::date($_date_expire, 'datetime') : Lang::t('_NEVER', 'standard')))
				);
                
                $dynamic_fields_value = $this->getDynamicFieldsValue($record, $var_fields);
                     
                // merge head, dynamic fields and tail part of the line
                $line = $line_head;
                foreach ($dynamic_fields_value as $dynamic_field_value) {
                    $line = array_merge($line, $dynamic_field_value);
                }
                $line = array_merge($line, $line_tail);   
                
                if ($format=='xls'){
                    $buffer .= $_XLS_STARTLINE;
                    $buffer .= str_replace('"', '', implode($_XLS_SEPARATOR, $line)).$_CSV_ENDLINE;
                }else{
                    $buffer .= implode($_CSV_SEPARATOR, $line).$_CSV_ENDLINE;
                }
			}
            if ($format=='xls'){
                $buffer .= "</table></body>";
            }
		}

		$charset = false;
            sendStrAsFile($buffer, $filename, $charset);
        }
    
    public function export_user_gap() {
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, 0);
        $id_user = Get::req('id_user', DOTY_INT, 0);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");        
        
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $back_url
			));
			return;
		}
		if ($id_user <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid user'),
				'back_url' => $back_url
			));
			return;
		}        

 		//prepare csv file
		require_once(_base_.'/lib/lib.download.php');
		$format = Get::req('format', DOTY_STRING, 'csv');

        $acl_man = Docebo::user()->getAclManager();
        $user_info = $acl_man->getUser($id_user, false);
		$buffer = "";
		$filename = preg_replace('/[\W]/i', '_', $this->model->getFunctionalRoleName($id_fncrole)).'_'.preg_replace('/\//i', '',$user_info[1]).'_'.date("Y_m_d").'.'.$format;

		$_CSV_SEPARATOR = ',';
		$_CSV_ENDLINE = "\r\n";
        $_XLS_STARTLINE = '<tr><td>';
        $_XLS_SEPARATOR = '</td><td>';
		$_XLS_ENDLINE = "</td></tr>";        
       
		//retrieve data to export
		$filter =  array('user' => $id_user);
		$pagination = array(
			'startIndex' => 0,
			'results' => $this->model->getGapTotal($id_fncrole, $filter),
			'sort' => $sort,
			'dir' => $dir
		);
		$list = $this->model->getGapList($id_fncrole, $pagination, $filter);

		//prepare the data for exporting

		$output_results = array();
		if (is_array($list) && count($list)>0) {
            if ($format=='xls'){
                $buffer .= "<head><meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\"></head><style>td, th { border:solid 1px black; } </style><body><table>";
            }            
			foreach ($list as $idst=>$record) {

				$_not_obtained = $record->last_assign_date == "";
				$_date_expire = $_not_obtained ? "" : date("Y-m-d H:i:s", fromDatetimeToTimestamp($record->last_assign_date) + $record->expiration * 86400);

				//json encoding used for string formatting with double quotes ""
				$line = array(
					$this->json->encode($record->competence_name),
					(int)$record->score_got,
					(int)$record->score_requested,
					(int)$record->gap*(-1),
					($_not_obtained ? "" : $this->json->encode(Format::date($record->last_assign_date, 'datetime'))),
					($_not_obtained ? "" : $this->json->encode($record->expiration > 0 ? Format::date($_date_expire, 'datetime') : Lang::t('_NEVER', 'standard')))
				);

                if ($format == 'xls') {
                    $buffer .= $_XLS_STARTLINE;
                    $buffer .= str_replace('"', '', implode($_XLS_SEPARATOR, $line)) . $_CSV_ENDLINE;
                } else {
                    $buffer .= implode($_CSV_SEPARATOR, $line) . $_CSV_ENDLINE;
                }
			}
            if ($format=='xls'){
                $buffer .= "</table></body>";
            }            
		}

		$charset = false;
		sendStrAsFile($buffer, $filename, $charset);
	}    



	//----------------------------------------------------------------------------

	public function user_gapanalisysTask() {
		//check inputs validity
		$base_url = 'index.php?r=adm/functionalroles/show';
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, 0);
		if ($id_fncrole <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid fncrole'),
				'back_url' => $base_url
			));
			return;
		}

		$back_url = 'index.php?r=adm/functionalroles/man_users&id='.(int)$id_fncrole;
		$id_user = Get::req('id_user', DOTY_INT, 0);
		if ($id_user <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('invalid user'),
				'back_url' => $back_url
			));
			return;
		}

		//load proper js library
		YuiLib::load('charts');

		//prepare page title
		$acl_man = Docebo::user()->getACLManager();
		$title_arr = array(
			$base_url => Lang::t('_FUNCTIONAL_ROLE', 'fncroles'),
			$back_url => Lang::t('_USERS', 'fncroles').': '.$this->model->getFunctionalRoleName($id_fncrole),
			Lang::t('_GAP_ANALYSIS', 'fncroles').': <b>'.$acl_man->relativeId($acl_man->getUserid($id_user)).'</b>'
		);

		//retrieve data for chart filling, and elaborate it
		$raw_data = $this->model->getGapList($id_fncrole, array(
			'startIndex' => 0,
			'results' => $this->model->getGapTotal($id_fncrole, array('user' => $id_user)),
			'sort' => "competence",
			'dir' => "ASC"
		), array('user' => $id_user));
		$chart_data = array();
		foreach ($raw_data as $record) {

			$_percent = 0.0;

			if ($record->type == 'flag') {
				//adjust values for flag type competences
				$record->score = 1;
				$record->score_got = $record->score_got > 0 ? 1 : 0;
				$record->gap = $record->score_got - $record->score;
				$_percent = $record->gap > 0 ? -100.0 : 0.0;
			} else {
				$_percent = ($record->score != 0 ? (float)((-$record->gap / (float)$record->score)*100) : 0.0);
			}

			$chart_data[] = array(
				'competence' => $this->json->encode($record->competence_name),
				'type' => $this->json->encode($record->type),
				'score_got' => (int)$record->score_got,
				'gap' => $record->gap,
				'gap_percent' => $_percent,
				'gap_negative' => $record->gap > 0 ? $record->gap : 0,
				'gap_positive' => $record->gap <= 0 ? abs($record->gap) : 0
			);
		}

		//rendere chart + table
		$this->render('user_gap_analisys', array(
			'id_fncrole' => $id_fncrole,
			'id_user' => $id_user,
			'title_arr' => $title_arr,
			'chart_data' => $chart_data,
			'from_gap' => Get::req('from_gap', DOTY_INT, 0) > 0
		));

	}

	public function getusergaptabledata() {
		//read from input and prepare filter and pagination variables
		$id_fncrole = Get::req('id_fncrole', DOTY_INT, -1);
		$id_user = Get::req('id_user', DOTY_INT, -1);
		//TO DO: if $id_fncrole <= 0 ...

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		
		$searchFilter = array('user' => $id_user);

		//get total from database and validate the results count
		$total = $this->model->getGapTotal($id_fncrole, $searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getGapList($id_fncrole, $pagination, $searchFilter);

		//prepare the data for sending
		$acl_man = Docebo::user()->getAclManager();
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//prepare output record

				$_not_obtained = $record->last_assign_date == "";
				$_date_expire = $_not_obtained ? "" : date("Y-m-d H:i:s", fromDatetimeToTimestamp($record->last_assign_date) + $record->expiration * 86400);

				$output_results[] = array(
					'last_assign_date' => $_not_obtained ? "" : Format::date($record->last_assign_date, 'datetime'),
					'score_req' => $record->score_requested,
					'score_got' => $record->score_got,
					'competence' => $record->competence_name,
					'id_competence' => $record->id_competence,
					'type' => $record->type,
					'expiration' => $record->expiration,
					'is_expired' => $_not_obtained ? false : ($_date_expire < date("Y-m-d H:i:s") && $record->expiration > 0),

					'date_expire' => $_not_obtained ? '' : ($record->expiration > 0 ? Format::date($_date_expire, 'datetime') : Lang::t('_NEVER', 'standard')),
					'gap' => $record->gap,
				);
			}
		}

		$output = array(
			'totalRecords' => $total,
			'startIndex' => $startIndex,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $rowsPerPage,
			'results' => count($list),
			'records' => $output_results
		);

		echo $this->json->encode($output);
	}


	public function functionalroles_autocompleteTask() {
		$query = Get::req('query', DOTY_STRING, '');
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$output = array('fncroles' => array());
		if ($query != "") {
			$fncroles = $this->model->searchFunctionalRolesByName($query, $results, false, true);
			foreach ($fncroles as $fncrole) {
				$output['fncroles'][] = array(
					'id_fncrole' => $fncrole->id_fncrole,
					'name' => $fncrole->name,
					'name_highlight' => Layout::highlight($fncrole->name, $query)
				);
			}
		}
		echo $this->json->encode($output);
	}

}

?>