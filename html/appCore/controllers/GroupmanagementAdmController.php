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

class GroupmanagementAdmController extends AdmController {

	protected $db = null;
	protected $model = null;
	protected $json = null;
	protected $permissions;
	public $link = 'adm/groupmanagement';

	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->db = DbConn::getInstance();
		$this->model = new GroupmanagementAdm();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view'						=> checkPerm('view', true, 'groupmanagement'),					//view module
			'add'							=> checkPerm('add', true, 'groupmanagement'),						//create groups
			'mod'							=> checkPerm('mod', true, 'groupmanagement'),						//edit groups
			'del'							=> checkPerm('del', true, 'groupmanagement'),						//delete groups
			'associate_user'	=> checkPerm('associate_user', true, 'groupmanagement')	//add users/orgbranches/fncroles to the group
		);
	}


	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "no permission":				$message = "You don't have permission to do this."; break;
			//...
			//case "server error":				$message = ""; break;
			case "invalid input":				$message = ""; break;
			default:										$message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		return $message;
	}

	/*
	 * load the groups management page
	 */
	public function show() {
		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('adm').'/views/groupmanagement/groupmanagement.js', true, true);
		Util::get_js(Get::rel_path('base').'/widget/dialog/dialog.js', true, true);

		$this->render('show', array(
			'permissions' => $this->permissions,
			'result_message' => "",
			'filter_text' => ''
		));
	}

	/*
	 * Format group description in order to strip html tags and to fit it in the table's limited size
	 */
	protected function _formatDescription($description, $length = 200) {
		$description = Util::purge($description); //strip html tags
		$description = stripslashes($description);
		$description = html_entity_decode($description, ENT_QUOTES);
		if (strlen($description)>$length) $description = substr($description, 0, $length);
		$description = htmlentities($description, ENT_QUOTES);
		return $description;
	}

	/*
	 * extract the data to load into groups table
	 */
	public function getdata() {
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		//get total from database and validate the results count
		$total = $this->model->getTotalGroups($filter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $results;
			}
		}

		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $results,
			'sort' => $sort,
			'dir' => $dir
		);

		$list = $this->model->getGroupsList($pagination, $filter);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$_groupid = $acl_man->relativeId($record->groupid);
				$_description = $this->_formatDescription($record->description);
				$records[] = array(
					'id' => (int)$record->idst,
					'groupid' => highlightText($_groupid, $filter),
					'description' => highlightText($_description, $filter),
					'usercount' => $record->usercount,
					'membercount' => $record->membercount,
					'mod' => 'ajax.adm_server.php?r=adm/groupmanagement/mod&id='.(int)$record->idst,
					'del' => 'ajax.adm_server.php?r=adm/groupmanagement/del&id='.(int)$record->idst
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,//$this->model->getTotalGroups($filter),
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}

	/*
	 * produces the message to load into delete dialog
	 */
	public function del() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id = Get::req('id', DOTY_INT, -1);
		$output['success'] = ($id > 0 ? $this->model->deleteGroup($id) : false);
		echo $this->json->encode($output);
	}


	/*
	 * produces the form to load into modify dialog
	 */
	public function mod() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$id = Get::req('id', DOTY_INT, -1);

		if ($id > 0) {
			$action = "ajax.adm_server.php?r=adm/groupmanagement/moddata&id=".$id;
			$output['success'] = true;
			$output['action'] = $action;
			$output['header'] = Lang::t('_MOD');
			$output['body'] = $this->_getEditMask($id);
		} else {
			$output['success'] = false;
		}

		echo $this->json->encode($output);
	}

	/*
	 * modify the data submitted from modify dialog
	 */
	public function moddata() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id = Get::req('id', DOTY_INT, -1);

		$output = array();
		if ($id > 0) {

			$types = $this->model->getGroupTypes(true);

			$groupid = Get::req('groupid', DOTY_STRING, "");
			$description = Get::req('description', DOTY_STRING, "");
			$type = Get::req('type', DOTY_ALPHANUM, "");
			if (!in_array($type, $types)) $type = $types['free'];
			$show_on_platform = false;

			$info = array(
				'groupid' => $groupid,
				'description' => $description,
				'type' => $type
			);
			if ($show_on_platform != false) {
				$info['show_on_platform'] = 'framework,lms,';
			}

			$res = $this->model->saveGroupInfo($id, $info);

			if ($res) {
				$output['success'] = true;
				$output['data'] = $info;
			} else {
				$output['success'] = false;
			}
		} else {
			$output['success'] = false;
		}

		echo $this->json->encode($output);
	}

	/*
	 * modify users assigned to the group
	 */
	public function assignmembers() {
		$id = Get::req('id_group', DOTY_INT, -1);

		$base_url = 'index.php?r=adm/groupmanagement/';
		$back_url = $base_url.'show_users&id='.(int)$id;
		$jump_url = $base_url.'assignmembers';
		$next_url = $base_url.'show_users&id='.(int)$id;

		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['associate_user']) Util::jump_to($back_url);

		if ($id > 0) {
			$acl = Docebo::user()->getAclManager();
			$selector = new UserSelector();

			$cancel = Get::req('cancelselector', DOTY_MIXED, false);
			$save = Get::req('okselector', DOTY_MIXED, false);

			if ($cancel) {
				Util::jump_to($back_url);
			} elseif ($save) {
				$selection = $selector->getSelection($_POST);
				$res = $this->model->saveGroupMembers($id, $selection);
				// apply rules
				$enrollrules = new EnrollrulesAlms();
				$enrollrules->applyRulesMultiLang('_LOG_USERS_TO_GROUP', $selection, false,$id);

				Util::jump_to($next_url.($res ? '&res=ok_assignuser' : '&res=err_assignuser'));
			} else {
				$selector->show_user_selector = TRUE;
				$selector->show_group_selector = TRUE;
				$selector->show_orgchart_selector = TRUE;
				$selector->show_fncrole_selector = FALSE;

				$group = $this->model->getGroupInfo($id, true);

				if (Get::req('is_updating', DOTY_INT, false)) {

				} else {
					$members = $this->model->getGroupMembers($id);
					$selector->requested_tab = PEOPLEVIEW_TAB;
					$selector->resetSelection($members);
				}
				$selector->addFormInfo(
					Form::getHidden('is_updating', 'is_updating', 1).
					Form::getHidden('id_group', 'id_group', $id)
				);
				$sel_title = array(
					'index.php?r=adm/groupmanagement/show' => Lang::t('_GROUPS', 'admin_directory'),
					'index.php?r=adm/groupmanagement/show_users&amp;id='.$id => Lang::t('_ASSIGN_USERS', 'admin_directory').': '.Docebo::aclm()->relativeId($group->groupid),
					Lang::t('_ADD', 'admin_directory')
				);
				$selector->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
					$sel_title,
					'',
					true);

			}

		} else {

		}
	}

	protected function _getEditMask($idst = false) {
		require_once(_base_.'/lib/lib.form.php');
		$group_types = $this->model->getGroupTypes(true);
		$acl = Docebo::user()->getAclManager();
		if ($idst > 0) {
			$group_info = $this->model->getGroupInfo($idst);
			$action = "ajax.adm_server.php?r=adm/groupmanagement/moddata&id=".$idst;
		} else {
			$action = "ajax.adm_server.php?r=adm/groupmanagement/creategroup";
			$group_info = array(
				'groupid' => '',
				'description' => '',
				'type' => 'free'
			);
		}
		$body = "";
		$body .= Form::openForm($idst > 0 ? 'modify_group_'.$idst : 'create_group', $action);
		if ($idst > 0) $body .= Form::getHidden('id', 'id', $idst);
		$body .= Form::getTextfield(Lang::t('_NAME', 'standard'), 'groupid', 'groupid', 255, ($group_info['groupid'] != "" ? $acl->relativeId($group_info['groupid']) : ""));
		$body .= Form::getSimpleTextarea(Lang::t('_DESCRIPTION', 'standard'), 'description', 'description', $group_info['description']);
		//$body .= Form::getDropdown(Lang::t('_DIRECTORY_GROUPTYPE', 'admin_directory'), 'type', 'type', $group_types, $group_info['type']);
		$body .= Form::closeForm();
		return $body;
	}

	public function create() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();

		//$action = "ajax.adm_server.php?r=adm/groupmanagement/creategroup";
		$action = "ajax.adm_server.phpr='.$this->link.'/creategroup";
		$output['success'] = true;
		$output['action'] = $action;
		$output['header'] = Lang::t('_ADD', 'standard');
		$output['body'] = $this->_getEditMask();

		echo $this->json->encode($output);
	}


	public function creategroup() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$types = $this->model->getGroupTypes(true);

		$groupid = Get::req('groupid', DOTY_STRING, "");
		$description = Get::req('description', DOTY_STRING, "");
		$type = Get::req('type', DOTY_ALPHANUM, "");
		if (!in_array($type, $types)) $type = $types['free'];
		$show_on_platform = true;//false;

		$info = array(
			'groupid' => $groupid,
			'description' => $description,
			'type' => $type
		);
		if ($show_on_platform != false) {
			$info['show_on_platform'] = 'framework,lms,';
		}

		$idst = $this->model->createGroup($info);

		if ($idst > 0) {
			$output['success'] = true;
			$output['data'] = $info;
		} else {
			$output['success'] = false;
		}

		echo $this->json->encode($output);
	}


	public function groups_autocompleteTask() {
		$query = Get::req('query', DOTY_STRING, '');
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$output = array('groups' => array());
		if ($query != "") {
			$groups = $this->model->searchGroupsByGroupid($query, $results, true);
			$acl_man = Docebo::user()->getAclManager();
			foreach ($groups as $group) {
				$_groupid = $acl_man->relativeId($group->groupid);
				$output['groups'][] = array(
					'idst' => $group->idst,
					'groupid' => $_groupid,
					'groupid_highlight' => Layout::highlight($_groupid, $query)
				);
			}
		}
		echo $this->json->encode($output);
	}






	public function importusers_step1Task() {
		$id_group = Get::req('id_group', DOTY_INT, 0);

		$back_url = "index.php?r=adm/groupmanagement/show";

		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => $back_url
			));
			return;
		}

		$this->render('importusers_step1', array('id_group' => $id_group));
	}


	public function importusers_step2Task() {

		$id_group = Get::req('id_group', DOTY_INT, 0);

		if(isset($_POST['import_groupcancel']))
			Util::jump_to('index.php?r=adm/groupmanagement/show_users&id='.$id_group);

		$separator = Get::req('import_separator', DOTY_STRING, ',');
		$first_row_header = Get::req('import_first_row_header', DOTY_STRING, 'false') == 'true';
		$import_charset = Get::req('import_charset', DOTY_STRING, 'UTF-8');
		if (trim($import_charset) === '') $import_charset = 'UTF-8';


		$csv_data= array();
		$file = fopen($_FILES['file_import']['tmp_name'], 'r');

		$first = true;
		while (($result = fgetcsv($file)) !== false) {
			if ($first_row_header != 'true' || !$first) {
				if (!empty($result)) $csv_data[] = $result[0];
			}
			$first = false;
		}
		fclose($file);

		$result = $this->model->importGroupMembers($csv_data, $id_group);

		$this->render(	'importusers_step2', array(
					'info' => $result,
					'id_group' => $id_group
		));

	}

	public function show_usersTask() {
		$id_group = Get::req('id', DOTY_INT, 0);

		if ($id_group <= 0) {

			return;
		}

		$res = Get::req('res', DOTY_STRING, '');
		switch ($res) {
			case 'ok_assignuser': $message = getResultUi(Lang::t('_OPERATION_SUCCESSFUL', 'admin_directory')); break;
			case 'err_assignuser': $message = getErrorUi(Lang::t('_GROUP_USERASSIGN_ERROR', 'admin_directory')); break;
			default: $message = "";
		}

		$group = $this->model->getGroupInfo($id_group, true);
		$this->render('show_users', array(
			'id_group' => $id_group,
			'groupid' => Docebo::aclm()->relativeId($group->groupid),
			'filter_text' => "",
			'result_message' => $message,
			'permissions' => $this->permissions
		));

	}


	public function getusertabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_group = Get::req('id_group', DOTY_INT, 0);
		//TO DO: if $id_group <= 0 ...

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
		$total = $this->model->getGroupUsersTotal($id_group, $searchFilter);
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
		$list = $this->model->getGroupUsersList($id_group, $pagination, $searchFilter);

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
					'del'		=> 'ajax.adm_server.php?r='.$this->link.'/del_user&id_user='.(int)$record->idst.'&id_group='.(int)$id_group,
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



	public function del_userTask() {
		//check permissions: we should have add privileges to create groups
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//read input and validate it
		$id_group = Get::req('id_group', DOTY_INT, 0);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		if (!$id_group || !$id_user) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('invalid input'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$res = $this->model->removeUsersFromGroup($id_group, $id_user);

		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = $this->_getErrorMessage ('server error');
		echo $this->json->encode($output);
	}

}
