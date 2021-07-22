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

Class QuestcategoryAlmsController extends AlmsController {
	protected $json;
	protected $model;
	protected $permissions;

	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->model = new QuestcategoryAlms();
		$this->permissions = array(
			'view'			=> checkPerm('view', true, 'questcategory', 'lms'),
			'add'				=> true,//checkPerm('add', true, 'questcategory', 'lms'),
			'mod'				=> checkPerm('mod', true, 'questcategory', 'lms'),
			'del'				=> true//checkPerm('del', true, 'questcategory', 'lms')
		);
	}


	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
			case "invalid input": $message = ""; break;
			case "category in use": $message = Lang::t('_CATEGORY_IN_USE', 'questcategory'); break;
			case "": $message = ""; break;
		}
		return $message;
	}

	public function showTask() {
		$this->render('show', array(
			'filter_text' => "",
			'permissions' => $this->permissions
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

		$searchFilter = array(
			'text' => $filter_text
		);

		//get total from database and validate the results count
		$total = $this->model->getQuestCategoriesTotal($searchFilter);
		if ($startIndex >= $total) {
			if ($total<$results) {
				$startIndex = 0;
			} else {
				$startIndex = $total - $rowsPerPage;
			}
		}

		//set pagination argument
		$pagination = array(
			'startIndex' => $startIndex,
			'results' => $rowsPerPage,
			'sort' => $sort,
			'dir' => $dir
		);

		//read records from database
		$list = $this->model->getQuestCategoriesList($pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {

			//check if categories are used in any test or poll
			$id_list = array();
			foreach ($list as $record) $id_list[] = $record->idCategory;
			$used_test_arr = $this->model->getUsedInTests($id_list);
			$used_poll_arr = $this->model->getUsedInPolls($id_list);

			foreach ($list as $record) {
				//format description field
				$description = strip_tags($record->description);
				if (strlen($description) > 100) {
					$description = substr($description, 0, 97).'...';
				}

				$used_test = isset($used_test_arr[$record->idCategory]) ? $used_test_arr[$record->idCategory] : 0;
				$used_poll = isset($used_poll_arr[$record->idCategory]) ? $used_poll_arr[$record->idCategory] : 0;

				$can_mod = $this->permissions['mod'];
				$can_del = ($used_test<=0 && $used_poll<=0 && $this->permissions['del']);

				//prepare output record
				$output_results[] = array(
					'id' => $record->idCategory,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($description, $filter_text),
					'used_test' => (int)$used_test,
					'used_poll' => (int)$used_poll,
					'mod'		=> $can_mod ? 'ajax.adm_server.php?r=alms/questcategory/edit&id='.(int)$record->idCategory : false,
					'del'		=> $can_del ? 'ajax.adm_server.php?r=alms/questcategory/delete&id='.(int)$record->idCategory : false,
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


	public function createTask() {
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$this->render('editmask', array(
			'json' => $this->json
		));
	}


	public function create_actionTask() {
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$name = Get::req('name', DOTY_STRING, "");
		$description = Get::req('description', DOTY_STRING, "");
		if ($name == "") {
			$output = array('success' => false, 'message' => $this->_getMessage("invalid input"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$info = new stdClass();
		$info->name = $name;
		$info->description = $description;
		$res = $this->model->createQuestCategory($info);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = $this->_getMessage("edit error"); else $output['new_id'] = (int)$res;
		echo $this->json->encode($output);
	}


	public function editTask() {
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$id_questcategory = Get::req('id', DOTY_INT, 0);
		if ($id_questcategory <= 0) {
			$output = array('success' => false, 'message' => $this->_getMessage("invalid input"));
			echo $this->json->encode($output);
			return;
		}

		$info = $this->model->getQuestCategoryInfo($id_questcategory);
		$this->render('editmask', array(
			'id_questcategory' => $id_questcategory,
			'name' => $info->name,
			'description' => $info->description,
			'json' => $this->json
		));
	}
	

	public function edit_actionTask() {
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$id_questcategory = Get::req('id', DOTY_INT, 0);
		$name = Get::req('name', DOTY_STRING, "");
		$description = Get::req('description', DOTY_STRING, "");
		if ($id_questcategory <= 0 || $name == "") {
			$output = array('success' => false, 'message' => $this->_getMessage("invalid input"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$info = new stdClass();
		$info->name = $name;
		$info->description = $description;
		$res = $this->model->editQuestCategory($id_questcategory, $info);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = $this->_getMessage("edit error");
		echo $this->json->encode($output);
	}


	public function deleteTask() {
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$id_questcategory = Get::req('id', DOTY_INT, 0);
		if ($id_questcategory <= 0) {
			$output = array('success' => false, 'message' => $this->_getMessage("invalid input"));
			echo $this->json->encode($output);
			return;
		}

		$used_test = $this->model->getUsedInTests($id_questcategory);
		$used_poll = $this->model->getUsedInPolls($id_questcategory);

		if ($used_poll > 0 || $used_test > 0) {
			$output = array('success' => false, 'message' => $this->_getMessage("category in use"));
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->deleteQuestCategory($id_questcategory);
		$output = array('success' => $res ? true : false);

		echo $this->json->encode($output);
	}

}


?>