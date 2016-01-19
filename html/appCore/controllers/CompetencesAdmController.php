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

Class CompetencesAdmController extends AdmController {

	protected $model;
	protected $json;

	protected $base_link_course;
	protected $base_link_competence;

	/*
	 * initialize the class
	 */
	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->json = new Services_JSON();
		$this->model = new CompetencesAdm();

		$this->base_link_course = 'alms/course';
		$this->base_link_competence = 'adm/competences';

		$this->permissions = array(
			'view'				=> checkPerm('view', true, 'competences'),			//view module
			'add'				=> checkPerm('mod', true, 'competences'),			//create competences
			'mod'				=> checkPerm('mod', true, 'competences'),			//edit competences, create/edit/remove categories
			'del'				=> checkPerm('mod', true, 'competences'),			//delete competences
			'associate_user'	=> checkPerm('associate_user', true, 'competences') //manage users for competence
		);
	}

	//--- internal private methods -----------------------------------------------
	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "no permission":		$message = "You don't have permission to do this."; break;
			case "invalid category":	$message = Lang::t('_INVALID_CATEGORY', 'competences'); break;
			case "invalid competence":	$message = Lang::t('_INVALID_COMPETENCE', 'fncroles'); break;
			case "invalid course":		$message = Lang::t('_INVALID_COURSE', 'competences'); break;
			case "no users":			$message = Lang::t('_NO_USERS', 'competences'); break;
			default:					$message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		return $message;
	}


	protected function _getNodeActions($node) {
		if (!is_array($node)) return false; //unrecognized type for node data
		$actions = array();
		$id_action = $node['id'];
		$is_root = ($id_action == 0);

		//permissions
		$can_mod = $this->permissions['mod'];
		$can_del = $this->permissions['mod'];

		//rename action
		if ($can_mod) {
			$actions[] = array(
				'id' => 'mod_'.$id_action,
				'command' => 'modify',
				'icon' => 'standard/edit.png',
				'alt' => Lang::t('_MOD', 'standard')
			);
		}

		//delete action
		if ($can_del) {
			if ($node['is_leaf'] && $node['count_objects']<=0 && !$is_root) {
				$actions[] = array(
					'id' => 'del_'.$id_action,
					'command' => 'delete',
					'icon' => 'standard/delete.png',
					'alt' => Lang::t('_DEL', 'standard')
				);
			} else {
				$actions[] = array(
					'id' => 'del_'.$id_action,
					'command' => false,
					'icon' => 'blank.png'
				);
			}
		}

		return $actions;
	}



	protected function _setCategoriesTreeArray(&$list, &$output, $id_category) {
		$t_arr = array();
		for ($i=0; $i<count($list); $i++) {
			if ($list[$i]->id_parent == $id_category) {
				$t_arr[$list[$i]->id_category] = $list[$i]->name;
			}
		}
		if (count($t_arr) <= 0) return;
		asort($t_arr);
		foreach ($t_arr as $key => $value) {
			$output[$key] = array(
				'id_category' => $key,
				'label' => $value,
				'children' => array()
			);
			$this->_setCategoriesTreeArray($list, $output[$key]['children'], $key);
		}
	}


	protected function _setCategoriesTreeDropdown(&$tree_arr, &$output, $level, $is_last) {
		$count = count($tree_arr);
		$i = 0;
		foreach ($tree_arr as $info) {
			$i++;
			$_is_last = ($i == $count);
			$_char = ($_is_last ? '&nbsp;' : '&nbsp;');//$_char = ($_is_last ? '└' : '├');
			$_label = "";
			for ($j=0; $j<$level; $j++) $_label .= '&nbsp;'.($is_last ? '&nbsp;' : '&nbsp;');//$_label .= '&nbsp;'.($is_last ? '&nbsp;' : '│');
			$_label .= '&nbsp;'.$_char.'&nbsp;';
			$output[$info['id_category']] = $_label.$info['label'];
			if (count($info['children']) > 0)
				$this->_setCategoriesTreeDropdown($info['children'], $output, $level+1, $_is_last);
		}
	}

	/*
	 * extract all categories and compose dropdown list with indentation
	 */
	protected function _getCategoriesDropdownList() {
		$output = array('0' => '(root)');

		$categories = $this->model->getAllCategories();
		$tree_arr = array();
		$this->_setCategoriesTreeArray($categories, $tree_arr, 0);
		$this->_setCategoriesTreeDropdown($tree_arr, $output, 0, false);
		//unset($tree_arr);
		//unset($categories);

		return $output;
	}


	protected function _assignActions(&$nodes) {
		if (!is_array($nodes)) return;
		for ($i=0; $i<count($nodes); $i++) {
			$nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
			if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
				$this->_assignActions($nodes[$i]['children']);
			}
		}
	}

	protected function _getFromSession($index, $default = null) {
		if (!isset($_SESSION['_competences_status'][$index])) $_SESSION['_competences_status'][$index] = $default;
		return $_SESSION['_competences_status'][$index];
	}

	protected function _setInSession($index, $value) {
		$_SESSION['_competences_status'][$index] = $value;
	}

	//--- Tasks ------------------------------------------------------------------

	/*
	 * Render the main page of the competences management module
	 */
	public function showTask() {
		//tabview widget, used in category editing
		Yuilib::load('tabview');

		//encode some data
		$arr_typologies = $this->model->getCompetenceTypologies();
		$arr_types = $this->model->getCompetenceTypes();

		$typologies_dropdown = '[';
		$first = true;
		foreach ($arr_typologies as $key => $value) {
			if (!$first) $typologies_dropdown .= ','; else $first = false;
			$typologies_dropdown .= '{value: "'.$key.'", label: "'.$value.'"}';
		}

		$types_dropdown = '[';
		$first = true;
		foreach ($arr_types as $key => $value) {
			if (!$first) $types_dropdown .= ','; else $first = false;
			$types_dropdown .= '{value: "'.$key.'", label: "'.$value.'"}';
		}
		$typologies_dropdown .= ']';
		$types_dropdown .= ']';


		//render view
		$this->render('show', array(
			'permissions' => $this->permissions,
			'selected_node' => $this->_getFromSession('selected_node', 0),
			'filter_text' => $this->_getFromSession('filter_text', ""),
			'show_descendants' => $this->_getFromSession('show_descendants', false),
			'language' => getLanguage(),
			'startIndex' => $this->_getFromSession('startIndex', 0),
			'results' => $this->_getFromSession('results', Get::sett('visuItem', 25)),
			'rowsPerPage' => $this->_getFromSession('rowsPerPage', Get::sett('visuItem', 25)),
			'sort' => $this->_getFromSession('sort', 0),
			'dir' => $this->_getFromSession('dir', 'asc'),
			'typologies' => $this->json->encode($arr_typologies),
			'types' => $this->json->encode($arr_types),
			'typologies_dropdown' => $typologies_dropdown,
			'types_dropdown' => $types_dropdown
		));
	}



	public function gettreedataTask() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {

			case "expand": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

				if ($initial) {
					//get selected category from session and set the expanded tree
					$node_id = $this->_getFromSession('selected_node', 0);
					$nodes = $this->model->getInitialCategories($node_id, false);

					//set nodes action recursively
					$this->_assignActions($nodes);

					//set output
					if (is_array($nodes)) {
						$output = array(
							'success' => true,
							'nodes' => $nodes,
							'initial' => $initial
						);
					} else {
						$output = array('success' => false);
					}
				} else {
					//extract node data
					$nodes = $this->model->getCategories($node_id);

					//if request is invalid, return error message ...
					if (!is_array($nodes)) {
						echo $this->json->encode(array('success' => false));
						return;
					}

					//create actions for every node
					for ($i=0; $i<count($nodes); $i++) {
						$nodes[$i]['options'] = $this->_getNodeActions($nodes[$i]);
					}
					//set output
					$output = array(
						'success' => true,
						'nodes' => $nodes,
						'initial' => $initial
					);
				}
				echo $this->json->encode($output);
			} break;

			case "set_selected_node": {
				$this->_setInSession('selected_node', Get::Req('node_id', DOTY_INT, 0));
			} break;

			case "delete": {
				//check permissions
				if (!$this->permissions['mod']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$output = array('success' => false);
				$id = Get::req('node_id', DOTY_INT, -1);
				if ($id > 0) $output['success'] = $this->model->deleteCategory($id);
				echo $this->json->encode($output);
			} break;

			case "movefolder": {
				//check permissions
				if (!$this->permissions['mod']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}
				
				$this->move_categoryTask();
			} break;
		}
	}


	public function addcategoryTask() {

	}


	public function renamecategoryTask() {
		$id = Get::req('id', DOTY_INT, -1);
		if ($id <= 0) {
			//...
		}
	}


	//----------------------------------------------------------------------------

	public function gettabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_category = Get::req('id_category', DOTY_INT, 0);
		$descendants = (Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
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
		$total = $this->model->getCompetencesTotal($id_category, $descendants, $searchFilter);
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

		//update session pagination and filter values
		$this->_setInSession('startIndex', $startIndex);
		$this->_setInSession('results', $results);
		$this->_setInSession('sort', $sort);
		$this->_setInSession('dir', $dir);
		$this->_setInSession('filter_text', $filter_text);
		$this->_setInSession('show_descendants', $descendants);
		$this->_setInSession('rowsPerPage', $rowsPerPage);

		//read records from database
		$list = $this->model->getCompetencesList($id_category, $descendants, $pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {

			$_typologies = $this->model->getCompetenceTypologies();
			$_types = $this->model->getCompetenceTypes();

			foreach ($list as $idst=>$record) {
				//format description field
				$description = strip_tags($record->description);
				if (strlen($description) > 100) {
					$description = substr($description, 0, 97).'...';
				}

				//prepare output record
				$output_results[] = array(
					'id' => $record->id_competence,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($description, $filter_text),
					'typology' => $_typologies[$record->typology],
					'type' => $_types[$record->type],
					'id_typology' => $record->typology,
					'id_type' => $record->type,
					'users' => $record->users,
					'del'		=> 'ajax.adm_server.php?r='.$this->base_link_competence.'/del_competence&id='.(int)$record->id_competence,
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


	public function add_categoryTask() {
		//check permissions
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_parent = Get::req('id', DOTY_INT, -1);
		if ($id_parent < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}

		$this->render('category_editmask', array(
			'title' => Lang::t('_NEW_CATEGORY', 'competences'),
			'id_parent' => $id_parent,
			'json' => $this->json
		));
	}


	public function mod_categoryTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_category = Get::req('id', DOTY_INT, -1);
		if ($id_category <= 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}

		//retrieve category info (name and description
		$info = $this->model->getCategoryInfo($id_category);

		$this->render('category_editmask', array(
			'title' => Lang::t('_MOD', 'competences'),
			'id_category' => $id_category,
			'category_langs' => $info->langs,
			'json' => $this->json
		));
	}


	public function add_competenceTask() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';

		//check permissions
		if (!$this->permissions['mod']) Util::jump_to($back_url);

		$id_category = Get::req('id', DOTY_INT, -1);
		if ($id_category < 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid category"),
				'back_url' => $back_url
			));
			return;
		}

		$this->render('competence_editmask', array(
			'id_category' => $id_category,
			'competence_typologies' => $this->model->getCompetenceTypologies(),
			'competence_types' => $this->model->getCompetenceTypes(),
			'competence_categories' => $this->_getCategoriesDropdownList()
		));
	}



	public function mod_competenceTask() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';
		
		//check permissions
		if (!$this->permissions['mod']) Util::jump_to($back_url);

		$id_competence = Get::req('id', DOTY_INT, -1);
		if ($id_competence <= 0) { //invalid competence
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $back_url
			));
			return;
		}
		
		//get competence info and prepare data to send to editmask
		$info = $this->model->getCompetenceInfo($id_competence);

		$this->render('competence_editmask', array(
			'id_competence' => $id_competence,
			'id_category' => $info->id_category,
			'competence_langs' => $info->langs,
			'competence_typology' => $info->typology,
			'competence_type' => $info->type,
			//'competence_score' => $info->score,
			//'competence_expiration' => $info->expiration,
			'competence_typologies' => $this->model->getCompetenceTypologies(),
			'competence_types' => $this->model->getCompetenceTypes(),
			'competence_categories' => $this->_getCategoriesDropdownList()
		));
	}


	public function add_category_actionTask() {
		//check permissions
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//set up the data to insert into DB
		$id_parent = Get::req('id_parent', DOTY_INT, -1);
		if ($id_parent < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}
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
		$res = $this->model->createCategory($id_parent, $langs);
		if ($res) {
			//return node data to add in the treeview of the page
			$nodedata = array(
				'id' => $res,
				'label' => $this->model->getCategoryName($res, getLanguage()),
				'is_leaf' => true,
				'count_objects' => 0
			);
			$nodedata['options'] = $this->_getNodeActions($nodedata);
			$output = array(
				'success' => true,
				'node' => $nodedata,
				'id_parent' => $id_parent
			);
		} else {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("create category"))
			);
		}
		echo $this->json->encode($output);
	}


	public function mod_category_actionTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//set up the data to insert into DB
		$id_category = Get::req('id_category', DOTY_INT, -1);
		if ($id_category < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}
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
		$res = $this->model->updateCategory($id_category, $langs);
		if ($res) {
			$_language = Get::req('lang', DOTY_ALPHANUM, getLanguage());
			$output = array(
				'success' => true,
				'new_name' => (isset($names[$lang_code]) ? $names[$lang_code] : '')
			);
		} else {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("edit category"))
			);
		}
		echo $this->json->encode($output);
	}



	public function move_categoryTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$src = Get::req('src', DOTY_INT, -1);
		$dest = Get::req('dest', DOTY_INT, -1);

		$output = array();

		if ($src <= 0 || $dest < 0) {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror($this->_getErrorMessage("invalid category"));
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->moveCategory($src, $dest);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = UIFeedback::perror($this->_getErrorMessage("move category"));
		echo $this->json->encode($output);
	}


	public function add_competence_actionTask() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';              
                
                if (isset($_POST['undo'])) {
			//--- UNDO: return to catalogue list -------------------------------------
			Util::jump_to($back_url);
                }

		//check permissions
		if (!$this->permissions['add']) Util::jump_to($back_url);

		//read inputs
		$id_category = Get::req('id_category', DOTY_INT, -1);
		if ($id_category < 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid category"),
				'back_url' => $back_url
			));
			return;
		}

		$params = new stdClass();
		$params->typology = Get::req('typology', DOTY_STRING, 'skill');
		$params->type = Get::req('type', DOTY_STRING, 'score');
		//$params->score = Get::req('score', DOTY_ALPHANUM, '0');
		//$params->expiration = Get::req('expiration', DOTY_INT, 0);

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
		$params->langs = $_arr_langs;

		$res = $this->model->createCompetence($id_category, $params);

		Util::jump_to($back_url.'&res='.($res ? 'ok_create' : 'err_create'));
	}


	public function mod_competence_actionTask() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';              
                
                if (isset($_POST['undo'])) {
			//--- UNDO: return to catalogue list -------------------------------------
			Util::jump_to($back_url);
                }

		//check permissions
		if (!$this->permissions['mod']) Util::jump_to($back_url);

		//read inputs
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $back_url
			));
			return;
		}

		$params = new stdClass();
		$params->id_category = Get::req('id_category', DOTY_INT, 0);
		$params->typology = Get::req('typology', DOTY_STRING, 'skill');
		$params->type = Get::req('type', DOTY_STRING, 'score');
		//$params->score = Get::req('score', DOTY_ALPHANUM, '0');
		//$params->expiration = Get::req('expiration', DOTY_INT, 0);

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
		$params->langs = $_arr_langs;

		$res = $this->model->updateCompetence($id_competence, $params);

		Util::jump_to($back_url.'&res='.($res ? 'ok_modify' : 'err_modify'));
	}



	public function del_competenceTask() {
		//check permissions
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//check associations with course
		if ($this->model->getCompetenceCoursesTotal(Get::req('id', DOTY_INT, -1)) > 0){
			$output = array('success' => false, 'message' => UIFeedback::perror(Lang::t('_WITH_COURSE_ASSOCIATIONS', 'competences')));
			echo $this->json->encode($output);
			return;
		}

		//check associations with fncrole
		if ($this->model->getCompetenceFncRolesTotal(Get::req('id', DOTY_INT, -1)) > 0){
			$output = array('success' => false, 'message' => UIFeedback::perror(Lang::t('_WITH_FNCROLE_ASSOCIATIONS', 'competences')));
			echo $this->json->encode($output);
			return;
		}

		//check associations with user
		if ($this->model->getCompetenceUsersTotal(Get::req('id', DOTY_INT, -1)) > 0){
			$output = array('success' => false, 'message' => UIFeedback::perror(Lang::t('_WITH_USER_ASSOCIATIONS', 'competences')));
			echo $this->json->encode($output);
			return;
		}
		
		$output = array('success' => false);
		$id = Get::req('id', DOTY_INT, -1);
		if ($id > 0) $output['success'] = $this->model->deleteCompetence($id);
		echo $this->json->encode($output);
	}



	public function show_usersTask() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';

		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to($back_url);

		//read inputs
		$id_competence = Get::req('id', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $back_url
			));
			return;
		}

		$result_message = "";
		switch (Get::req('res', DOTY_STRING, "")) {
			case 'no_users': {
				$result_message = UIFeedback::notice(Lang::t('_OPERATION_FAILURE', 'standard'), true);
			} break;
			case 'invalid_competence_type': {
				$result_message = UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard'), true);
			} break;
			case 'ok_assign': {
				$count = Get::req('count', DOTY_INT, -1);
				$result_message = UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard'), true);
			} break;
		}

		//render view
		$this->render('show_users', array(
			'filter_text' => "",
			'competence_info' => $this->model->getCompetenceInfo($id_competence),
			'result_message' => $result_message,
			'count_users' => $this->model->getCompetenceUsersTotal($id_competence)
		));
	}


	public function getuserstabledataTask() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_competence = Get::req('id_competence', DOTY_INT, -1);

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
		$total = $this->model->getCompetenceUsersTotal($id_competence, $searchFilter);
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

		$list = $this->model->getCompetenceUsersList($id_competence, $pagination, $searchFilter);

		$output_results = array();
		if (is_array($list)) {
			$acl_man = Docebo::user()->getAclManager();
			$required_users = $this->model->getRequiredUsers($id_competence);

			foreach ($list as $user) {
				$output_results[] = array(
					'idst' => $user->id_user,
					'userid' => Layout::highlight($acl_man->relativeId($user->userid), $filter_text),
					'firstname' => Layout::highlight($user->firstname, $filter_text),
					'lastname' => Layout::highlight($user->lastname, $filter_text),
					'last_assign_date' => Format::date($user->last_assign_date, 'datetime'),
					//'date_expire' => $_date_expire,
					//'is_required' => (in_array($user->id_user, $required_users) ? '1' : '0'),
					'score' => $user->score_got,
					'unassign' => 'ajax.adm_server.php?r='.$this->base_link_competence.'/unassign_user&id_competence='.(int)$id_competence.'&id_user='.(int)$user->id_user
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



	public function assign_users() {
		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to('index.php?r='.$this->base_link_competence.'/show');

		require_once(_adm_.'/lib/lib.directory.php');
		require_once(_adm_.'/class.module/class.directory.php');
		
		//read inputs
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		//navigation urls
		$back_url = 'index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence;
		$jump_url = 'index.php?r='.$this->base_link_competence.'/assign_users&id='.(int)$id_competence;

		//competence details
		$info = $this->model->getCompetenceInfo($id_competence);
		$name = $this->model->getCompetenceName($id_competence);

		//page_title
		$page_title_arr = array(
			$back_url => Lang::t('_COMPETENCES', 'competences'),
			$name,
			Lang::t('_ASSIGN_USERS', 'competences')
		);

		if (isset($_POST['cancelselector'])) {

			//--- UNDO: return to catalogue list -------------------------------------
			Util::jump_to($back_url);

		} elseif (isset($_POST['okselector'])) {

			//--- SAVE: users selection has been done --------------------------------

			$acl_man = Docebo::user()->getAclManager();
			$user_selector = new UserSelector();
			$selection = $user_selector->getSelection($_POST);
			$users_selected =& $acl_man->getAllUsersFromIdst($selection);
			$competence_users = $this->model->getCompetenceUsers($id_competence, true);
			$users_existent = array_keys($competence_users);

			//retrieve newly selected users
			$_common_users = array_intersect($users_existent, $users_selected);
			$_new_users = array_diff($users_selected, $_common_users);
			$_old_users = array_diff($users_existent, $_common_users);
			unset($_common_users); //free some memory

			//if no users to add: check removed users (if any) then go back
			if (empty($_new_users)) {
				$res = $this->model->removeCompetenceUsers($id_competence, $_old_users, true);
				$message = $res ? 'ok_assign' : 'err_assign';
				Util::jump_to('index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence.'&res='.$message);
			}

			//assign scores, if the competence type is 'score' (otherwise assign directly score 1 and go back)
			$type = $info->type;
			if ($type == 'score') {
				require_once(_base_.'/lib/lib.table.php');
				$table = new Table();

				$head_label = array();
				$head_style = array();

				$head_label[] = Lang::t('_USERNAME', 'standard');
				$head_label[] = Lang::t('_NAME');
				$head_label[] = Lang::t('_SCORE', 'competences');

				$head_style[] = '';
				$head_style[] = '';
				$head_style[] = 'img-cell';

				$table->addHead($head_label, $head_style);

				$user_model = new UsermanagementAdm();
				$_user_data = $user_model->getUsersDetails($_new_users, true, true);

				$_std_score = 0;
				foreach ($_new_users as $id_user) {
					if (isset($_user_data[$id_user]) && is_object($_user_data[$id_user])) {
						$line = array();

						$line[] = $acl_man->relativeId($_user_data[$id_user]->userid);
						$line[] = $_user_data[$id_user]->lastname." ".$_user_data[$id_user]->firstname;
						$line[] = Form::getInputTextfield('textfield', 'assign_score_'.$id_user, 'assign_score['.$id_user.']', $_std_score, '', 255, '');

						$table->addBody($line);
					}
				}

				$foot = array();
				$foot[] = array('label'=>'<b>'.Lang::t('_TOTAL', 'standard').': '.count($_new_users).'</b>', 'colspan'=>2);
				$foot[] = Form::getInputTextfield('textfield', '_score_', '_score_', $_std_score, '', 255, '').'<br />'
					.Form::getButton('set_score', false, Lang::t('_SET', 'standard'))
					.Form::getButton('reset_score', false, Lang::t('_RESET', 'standard'));

				$table->addFoot($foot);

				$this->render('users_assign', array(
					'id_competence' => $id_competence,
					'title' => $page_title_arr,
					'type' => $type,
					'form_url' => 'index.php?r='.$this->base_link_competence.'/assign_users_action',
					'table' => $table,
					'score_std_value' => $_std_score,
					'del_selection' => implode(",", $_old_users)
				));
			
			} else {
				$data = array();
				foreach ($_new_users as $id_user) {
					$data[$id_user] = 1;
				}
				$res1 = $this->model->assignCompetenceUsers($id_competence, $data, true);
				$res2 = $this->model->removeCompetenceUsers($id_competence, $_old_users, true);
				$message = $res1 && $res2 ? 'ok_assign' : 'err_assign';
				Util::jump_to('index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence.'&res='.$message);
			}
		} else {

			//--- USER SELECTION IS IN PROGRESS: show selector -----------------------
			$user_selector = new UserSelector();

			$user_selector->show_user_selector = TRUE;
			$user_selector->show_group_selector = TRUE;
			$user_selector->show_orgchart_selector = TRUE;
			$user_selector->show_fncrole_selector = TRUE;
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
				$selection = $this->model->getCompetenceUsers($id_competence);
				$user_selector->requested_tab = PEOPLEVIEW_TAB;
				$user_selector->resetSelection($selection);
			}

			$user_selector->addFormInfo(
				Form::getHidden('is_updating', 'is_updating', 1).
				Form::getHidden('id_competence', 'id_competence', $id_competence)
			);

			//draw selector
			$user_selector->loadSelector(
				Util::str_replace_once('&', '&amp;', $jump_url),
				$page_title_arr,
				false,
				true
			);
		}
	}


	public function assign_users_action() {
		$back_url = 'index.php?r='.$this->base_link_competence.'/show';

		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to($back_url);

		//read inputs
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $back_url
			));
			return;
		}
		$assign = Get::req('assign_score', DOTY_MIXED, array());
		$remove = Get::req('del_selection', DOTY_STRING, "");
		$del_selection = ($remove != "" ? explode(",", $remove) : array());

		$res1 = $this->model->assignCompetenceUsers($id_competence, $assign, true);
		$res2 = $this->model->removeCompetenceUsers($id_competence, $del_selection, true);
		//$tracked = $this->model->trackOperation();

		$message = $res1 && $res2 ? 'ok_assign' : 'err_assign';
		Util::jump_to('index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence.'&res='.$message);
	}


	public function user_history() {
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid user"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		$history = $this->model->getUserHistoryData($id_competence, $id_user);
		
	}


	public function unassign_userTask() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid competence"))
			);
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user <= 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getErrorMessage("invalid user"))
			);
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->removeCompetenceUsers($id_competence, $id_user, true);
		$output = array('success' => $res ? true : false);
		if (!$res) $output['message'] = Lang::t('_ERROR_WHILE_SAVING', 'standard');

		echo $this->json->encode($output);
	}




	public function mod_usersTask() {
		$base_url = 'index.php?r='.$this->base_link_competence.'/show';

		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to($base_url);

		//read inputs
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $base_url
			));
			return;
		}

		$back_url = 'index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence;

		//competence details
		$info = $this->model->getCompetenceInfo($id_competence);
		$name = $this->model->getCompetenceName($id_competence);

		//page_title
		$page_title_arr = array(
			$base_url => Lang::t('_COMPETENCES', 'competences'),
			$back_url => Lang::t('_USERS', 'competences').': '.$name,
			Lang::t('_MOD', 'competences')
		);

		$competence_users = $this->model->getCompetenceUsers($id_competence, true);
		$users = array_keys($competence_users);

		if (empty($users)) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("no users"),
				'back_url' => $back_url
			));
			return;
		}

		//assign scores, if the competence type is 'score' (otherwise assign directly score 1 and go back)
		$type = $info->type;
		if ($type == 'score') {
			require_once(_base_.'/lib/lib.table.php');
			$table = new Table();

			$head_label = array();
			$head_style = array();

			$head_label[] = Lang::t('_USERNAME', 'standard');
			$head_label[] = Lang::t('_NAME');
			$head_label[] = Lang::t('_SCORE', 'competences');

			$head_style[] = '';
			$head_style[] = '';
			$head_style[] = 'img-cell';

			$table->addHead($head_label, $head_style);

			$user_model = new UsermanagementAdm();
			$_user_data = $user_model->getUsersDetails($users, true, true);

			$_std_score = 0;
			$acl_man = Docebo::user()->getACLManager();
			foreach (array_keys($_user_data) as $id_user) {
				$line = array();

				$line[] = $acl_man->relativeId($_user_data[$id_user]->userid);
				$line[] = $_user_data[$id_user]->lastname." ".$_user_data[$id_user]->firstname;
				$line[] = Form::getInputTextfield(
					'textfield',
					'assign_score_'.$id_user,
					'assign_score['.$id_user.']',
					$competence_users[$id_user]->score_got,
					'',
					255,
					''
				);

				$table->addBody($line);
			}

			$foot = array();
			$foot[] = array('label'=>'<b>'.Lang::t('_TOTAL', 'standard').': '.count($users).'</b>', 'colspan'=>2);
			$foot[] = Form::getInputTextfield('textfield', '_score_', '_score_', $_std_score, '', 255, '').'<br />'
				.Form::getButton('set_score', false, Lang::t('_SET', 'standard'))
				.Form::getButton('reset_score', false, Lang::t('_RESET', 'standard'));

			$table->addFoot($foot);

			$this->render('users_mod', array(
				'id_competence' => $id_competence,
				'title' => $page_title_arr,
				'type' => $type,
				'form_url' => 'index.php?r='.$this->base_link_competence.'/mod_users_action',
				'table' => $table,
				'score_std_value' => $_std_score
			));

		} else {
			
			$message = '';
			Util::jump_to('index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence.'&res='.$message);
		}
	}


	public function mod_users_actionTask() {
		$base_url = 'index.php?r='.$this->base_link_competence.'/show';

		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to($base_url);

		//read inputs
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		if ($id_competence <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => $base_url
			));
			return;
		}

		$back_url = 'index.php?r='.$this->base_link_competence.'/show_users&id='.(int)$id_competence;

		//competence details
		$info = $this->model->getCompetenceInfo($id_competence);
		$competence_users = $this->model->getCompetenceUsers($id_competence, true);
		
		if (empty($competence_users)) Util::jump_to($back_url.'&res=no_users');
		if ($info->type == 'flag') Util::jump_to($back_url.'&res=invalid_competence_type');

		$count = 0;
		$success = 0;
		$values = Get::req('assign_score', DOTY_MIXED, array());
		foreach ($values as $id_user => $score) {
			if (isset($competence_users[$id_user])) {
				if ($score != $competence_users[$id_user]->score_got) {
					$count++;
					$params = new stdClass();
					$params->score_got = $score;
					$res = $this->model->modifyCompetenceUsers($id_competence, $id_user, $params, true);
					if ($res) $success++;
				}
			}
		}

		Util::jump_to($back_url.'&res=ok_assign&count='.(int)$count);
	}

	/*
	 *
	 */
	public function change_user_scoreTask() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		$id_competence = Get::req('id_competence', DOTY_INT, -1);
		$old_score = Get::req('old_score', DOTY_FLOAT, -1.0);
		$new_score = Get::req('new_score', DOTY_FLOAT, -1.0);

		$output = array('success' => true);

		if ($id_user <= 0) {
			$output['success'] = false;
			echo $this->json->encode($output);
			return;
		}

		if ($id_competence <= 0) {
			$output['success'] = false;
			echo $this->json->encode($output);
			return;
		}

		if ($new_score != $old_score) {
			$params = new stdClass();
			$params->score_got = $new_score;
			$res = $this->model->modifyCompetenceUsers($id_competence, $id_user, $params, true);
			if (!$res) {
				$output['success'] = false;
			} else {
				$output['date'] = Format::date(date("Y-m-d H:i:s"), 'datetime');
			}
		}

		echo $this->json->encode($output);
	}


	/*
	 * interface for competences management in course module
	 */
	public function man_courseTask() {
		$id_course = Get::req('id_course', DOTY_INT, 0);
		if ($id_course <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid course"),
				'back_url' => $base_url
			));
			return;
		}
		
		$cmodel = new CourseAlms();
		$course_info = $cmodel->getInfo($id_course);
		$course_name = ($course_info['code'] !== '' ? '['.$course_info['code'].'] ' : '').$course_info['name'];

		$title_arr = array(
			'index.php?r='.$this->base_link_course.'/show' => Lang::t('_COURSES', 'course'),
			Lang::t('_COMPETENCES', 'competences').' : '.$course_name
		);

		$res = Get::req('res', DOTY_ALPHANUM, '');
		$result_message = "";
		switch ($res) {
			case 'ok_competences': $result_message = Lang::t('_OPERATION_SUCCESSFUL', 'standard'); break;
			case 'err_competences': $result_message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		$this->render('man_course', array(
			'id_course' => $id_course,
			'has_scores' => $this->model->courseHasScoreCompetences($id_course),
			'title_arr' => $title_arr,
			'result_message' => $result_message,
			'filter_text' => "",
			'base_link_competence' => $this->base_link_competence
		));
	}

	/*
	 * competences selector for course
	 */
	public function assign_to_course() {
		$base_url = 'index.php?r='.$this->base_link_competence.'/show';

		$id_course = Get::req('id_course', DOTY_INT, 0);
		if ($id_course <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid course"),
				'back_url' => $base_url
			));
			return;
		}

		//navigation urls
		$back_url = 'index.php?r='.$this->base_link_competence.'/man_course&id_course='.(int)$id_course;
		$jump_url = 'index.php?r='.$this->base_link_competence.'/assign_to_course&id_course='.(int)$id_course;

		//selector commands
		$save = Get::req('save', DOTY_MIXED, false);
		$undo = Get::req('undo', DOTY_MIXED, false);

		if ($undo !== false) {
			Util::jump_to($back_url);

		} elseif ($save !== false) {

			$selection = Get::req('competences_selection', DOTY_MIXED, array());
			$selection_str = (is_array($selection) && isset($selection['course_competences_selector']) ? $selection['course_competences_selector'] : "") ;
			$competences_selected = $selection_str != "" ? explode(",", $selection_str) : array();
			$competences_existent = $this->model->getCourseCompetences($id_course);

			//retrieve newly selected competences
			$_common_competences = array_intersect($competences_existent, $competences_selected);
			$_new_competences = array_diff($competences_selected, $_common_competences); //new competences to add
			$_old_competences = array_diff($competences_existent, $_common_competences); //old competences to delete
			unset($_common_competences); //free some memory

			require_once(_base_.'/lib/lib.table.php');
			$table = new Table();
			$label_h = array(
				Lang::t('_NAME', 'standard'),
				Lang::t('_DESCRIPTION', 'standard'),
				Lang::t('_TYPOLOGY', 'competences'),
				Lang::t('_SCORE', 'competences')
			);
			$style_h = array(
				'',
				'',
				'img-cell',
				'img-cell'
			);
			$table->addHead($label_h, $style_h);

			$counter = 0; //how many score type competences
			$info = $this->model->getCompetencesInfo($_new_competences);
			$lang_code = getLanguage();
			$std_value = 0;
			foreach ($info as $id=>$competence) {
				if ($competence->type=='score') {
					$counter++;
					$line = array();
					$line[] = $competence->langs[$lang_code]['name'];
					$line[] = $competence->langs[$lang_code]['description'];
					$line[] = $competence->typology;
					$line[] = Form::getInputTextfield('textfield', 'score_assigned_'.$id, 'score_assigned['.$id.']', $std_value, '', 255, '');
					$table->addBody($line);
				}
			}

			if ($counter > 0) {
				$foot = array(
					array('colspan'=>3, 'label'=>''),
					Form::getInputTextfield('textfield', 'score_assigned', false, $std_value, '', 255, '').'<br />'
						.Form::getButton('set_score', 'set_score', Lang::t('_SET', 'standard'), false, '', true, false)
						.Form::getButton('reset_score', 'reset_score', Lang::t('_RESET', 'standard'), false, '', true, false)
				);
				$table->addFoot($foot);

				$title_arr = array(
					'index.php?r='.$this->base_link_course.'/show' => Lang::t('_COURSES', 'course'),
					$back_url => Lang::t('_COMPETENCES', 'competences'),
					Lang::t('_SELECT')
				);

				$this->render('course_assign_score', array(
					'id_course' => $id_course,
					'title_arr' => $title_arr,
					'table' => $table,
					'del_selection' => (count($_old_competences)>0 ? implode(',', $_old_competences) : ''),
					'new_selection' => (count($_new_competences)>0 ? implode(',', $_new_competences) : ''),
					'base_link_competence' => $this->base_link_competence
				));

			} else {
				$scores = array();
				foreach ($_new_competences as $id_competence) {
					//any competence in here is a flag type competence
					$scores[$id_competence] = 1;
				}

				//insert newly selected competences in database
				$res1 = $this->model->assignCourseCompetences($id_course, $scores);
				$res2 = $this->model->deleteCourseCompetences($id_course, $_old_competences);

				//go back to main page, with result message
				Util::jump_to($back_url.'&res='.($res1 && $res2 ? 'ok_competences' : 'err_competences'));
			}

		} else {
			$title_arr = array(
				'index.php?r='.$this->base_link_course.'/show' => Lang::t('_COURSES', 'course'),
				$back_url => Lang::t('_COMPETENCES', 'competences'),
				Lang::t('_SELECT', 'competences')
			);

			//render the courses selector
			$selection = $this->model->getCourseCompetences($id_course);

			$this->render('assign_to_course', array(
				'id_course' => $id_course,
				'title_arr' => $title_arr,
				'selection' => $selection,
				'base_link_competence' => $this->base_link_competence
			));
		}
	}


	public function assign_to_course_actionTask() {
		$id_course = Get::req('id_course', DOTY_INT, 0);
		if ($id_course <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid course"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		//set back url
		$back_url = 'index.php?r='.$this->base_link_competence.'/man_course&id_course='.(int)$id_course;

		//form commands
		$save = Get::req('save', DOTY_MIXED, false);
		$undo = Get::req('undo', DOTY_MIXED, false);

		if ($undo) Util::jump_to($back_url);

		//read and decode inputs
		$scores = Get::req('score_assigned', DOTY_MIXED, false);

		$new_selection_str = Get::req('new_selection', DOTY_STRING, '');
		$new_selection = ($new_selection_str == "" ? array() : explode(',', $new_selection_str));

		$del_selection_str = Get::req('del_selection', DOTY_STRING, '');
		$del_selection = ($del_selection_str == "" ? array() : explode(',', $del_selection_str));

		//prepare scores for DB insertion
		$_scores = array();
		foreach ($new_selection as $id_competence) {
			$_scores[$id_competence] = array_key_exists($id_competence, $scores) ? $scores[$id_competence] : 1;
		}

		//insert newly selected competences in database
		$res1 = $this->model->assignCourseCompetences($id_course, $_scores);
		$res2 = $this->model->deleteCourseCompetences($id_course, $_del_selection);

		//go back to main page, with result message
		Util::jump_to($back_url.'&res='.($res1 && $res2 ? 'ok_competences' : 'err_competences'));
	}


	public function getcoursetabledataTask() {
		//read from input and prepare filter and pagination variables
		$id_course = Get::req('id_course', DOTY_INT, -1);
		//TO DO: if $id_course <= 0 ...

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
		$total = $this->model->getCourseCompetencesTotal($id_course, $searchFilter);
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
		$list = $this->model->getCourseCompetencesList($id_course, $pagination, $searchFilter);

		//prepare the data for sending
		$output_results = array();
		if (is_array($list) && count($list)>0) {
			foreach ($list as $idst=>$record) {
				//prepare output record
				$output_results[] = array(
					'id' => $record->id_competence,
					'name' => Layout::highlight($record->name, $filter_text),
					'description' => Layout::highlight($record->description, $filter_text),
					'typology' => $record->typology,
					'type' => $record->type,
					'score' => $record->score,
					'del'		=> 'ajax.adm_server.php?r='.$this->base_link_competence.'/del_course_competence&id_course='.(int)$id_course.'&id_competence='.(int)$record->id_competence,
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



	/*
	 * modified course scores assigned to competences
	 */
	public function mod_course_competencesTask() {
		$id_course = Get::req('id_course', DOTY_INT, 0);
		if ($id_course <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid course"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		$back_url = 'index.php?r='.$this->base_link_competence.'/man_course&id_course='.(int)$id_course;
		$arr_competences = $this->model->getCourseCompetences($id_course);

		require_once(_base_.'/lib/lib.table.php');
		$table = new Table();
		$label_h = array(
			Lang::t('_NAME', 'standard'),
			Lang::t('_DESCRIPTION', 'standard'),
			Lang::t('_TYPOLOGY', 'competences'),
			Lang::t('_SCORE', 'competences')
		);
		$style_h = array(
			'',
			'',
			'img-cell',
			'img-cell'
		);
		$table->addHead($label_h, $style_h);

		$counter = 0; //how many score type competences
		$info = $this->model->getCompetencesInfo($arr_competences);
		$lang_code = getLanguage();
		$std_value = 0;
		foreach ($info as $id=>$competence) {
			if ($competence->type=='score') {
				$counter++;
				$line = array();
				$line[] = $competence->langs[$lang_code]['name'];
				$line[] = $competence->langs[$lang_code]['description'];
				$line[] = $competence->typology;
				$line[] = Form::getInputTextfield('textfield', 'score_assigned_'.$id, 'score_assigned['.$id.']', $std_value, '', 255, '');
				$table->addBody($line);
			}
		}

		if ($counter > 0) {
			$foot = array(
				array('colspan'=>3, 'label'=>''),
				Form::getInputTextfield('textfield', 'score_assigned', false, $std_value, '', 255, '').'<br />'
					.Form::getButton('set_score', 'set_score', Lang::t('_SET', 'standard'), false, '', true, false)
					.Form::getButton('reset_score', 'reset_score', Lang::t('_RESET', 'standard'), false, '', true, false)
			);
			$table->addFoot($foot);

			$title_arr = array(
				'index.php?r='.$this->base_link_course.'/show' => Lang::t('_COURSES', 'course'),
				$back_url => Lang::t('_COMPETENCES', 'competences'),
				Lang::t('_SCORE')
			);

			$this->render('mod_course_assign_score', array(
				'id_course' => $id_course,
				'title_arr' => $title_arr,
				'table' => $table
			));
		} else {
			//go back to main page, no score to assign
			Util::jump_to($back_url);
		}
	}


	public function mod_course_competences_actionTask() {
		$id_course = Get::req('id_course', DOTY_INT, 0);
		if ($id_course <= 0) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid course"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		//set back url
		$back_url = 'index.php?r='.$this->base_link_competence.'/man_course&id_course='.(int)$id_course;

		//form commands
		$save = Get::req('save', DOTY_MIXED, false);
		$undo = Get::req('undo', DOTY_MIXED, false);

		if ($undo) Util::jump_to($back_url);

		$scores = Get::req('score_assigned', DOTY_MIXED, array());
		$res = $this->model->updateCourseCompetences($id_course, $scores);

		//go back to main page, with result message
		
		Util::jump_to($back_url.'&res='.($res ? 'ok_competences' : 'err_competences'));
	}

	
	/*
	 * remove a score assignment for a competence
	 */
	public function del_course_competenceTask() {
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_competence = Get::req('id_competence', DOTY_INT, 0);

		$output = array();
		if ($id_course <= 0 || $id_competence <= 0) {
			$message_text = ($id_course <= 0 ? $this->_getErrorMessage("invalid course") : $this->_getErrorMessage("invalid competence"));
			$output['success'] = false;
			$output['message'] = UIFeedback::perror($message_text);
		} else {
			$res = $this->model->deleteCourseCompetences((int)$id_course, (int)$id_competence);
			$output['success'] = $res ? true : false;
			if (!$res) $output['message'] = UIFeedback::perror($this->_getErrorMessage("remove course competence"));
		}

		echo $this->json->encode($output);
	}



	public function view_competence_reportTask() {
		$id_competence = Get::req('id', DOTY_INT, -1);
		if ($id_competence <= 0) { //invalid competence
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage("invalid competence"),
				'back_url' => 'index.php?r='.$this->base_link_competence.'/show'
			));
			return;
		}

		$filter_text = "";
		$filter_set = Get::req('filter_set', DOTY_INT, -1);
		$filter_reset = Get::req('filter_reset', DOTY_INT, -1);
		if ($filter_set != -1) $filter_text = Get::req('filter_text', DOTY_STRING, "");
		if ($filter_reset != -1) $filter_text = "";

		$userdata = $this->model->getCompetenceUsers($id_competence, true, $filter_text);

		$umodel = new UsermanagementAdm();
		$uinfo = $umodel->getUsersDetails(array_keys($userdata), true, true);

		$icon_history = '<span class="ico-sprite subs_elem"><span>'.Lang::t('_HISTORY', 'standard').'</span></span>';

		$table = new Table();
		$label_h = array(
			Lang::t('_USERNAME', 'standard'),
			Lang::t('_LASTNAME', 'standard'),
			Lang::t('_FIRSTNAME', 'standard'),
			Lang::t('_SCORE', 'competences'),
			Lang::t('_DATE_LAST_COMPLETE', 'subscribe'),
			$icon_history
		);
		$style_h = array(
			'',
			'',
			'',
			'img-cell',
			'img-cell',
			'img-cell'
		);
		$table->addHead($label_h, $style_h);

		$type = $this->model->getCompetenceType($id_competence);
		$acl_man = Docebo::user()->getACLManager();
		foreach ($userdata as $id_user => $record) {
			$line = array();

			$line[] = Layout::highlight($acl_man->relativeId($uinfo[$id_user]->userid), $filter_text);
			$line[] = Layout::highlight($uinfo[$id_user]->lastname, $filter_text);
			$line[] = Layout::highlight($uinfo[$id_user]->firstname, $filter_text);
			$line[] = ($type == 'score' ? $userdata[$id_user]->score_got : '-');
			$line[] = Format::date($userdata[$id_user]->last_assign_date, 'datetime');
			$line[] = $icon_history;

			$table->addBody($line);
		}

		$this->render('competence_users', array(
			'id_competence' => $id_competence,
			'filter_text' => $filter_text,
			'table' => $table
		));

	}



	public function competences_autocompleteTask() {
		$query = Get::req('query', DOTY_STRING, '');
		$results = Get::Req('results', DOTY_INT, Get::sett('visuItem', 25));
		$output = array('competences' => array());
		if ($query != "") {
			$competences = $this->model->searchCompetencesByName($query, $results, true);
			foreach ($competences as $competence) {
				$output['competences'][] = array(
					'id_competence' => $competence->id_competence,
					'name' => $competence->name,
					'name_highlight' => Layout::highlight($competence->name, $query),
					'type' => $competence->type,
					'typology' => $competence->typology
				);
			}
		}
		echo $this->json->encode($output);
	}



	public function inline_editTask() {
		$id_competence = Get::req('id_competence', DOTY_INT, 0);

		if ($id_competence <= 0) {
			echo $this->json->encode(array('success' => true));
			return;
		}

		//Update info
		$new_value = Get::req('new_value', DOTY_MIXED, '');
		$old_value = Get::req('old_value', DOTY_MIXED, '');
		$column = Get::req('column', DOTY_STRING, '');
		$language = Get::req('language', DOTY_STRING, getLanguage());

		if ($new_value === $old_value) {
			echo $this->json->encode(array('success' => true));
		} else {

			switch ($column) {

				case 'name': {
					$res = $this->model->updateCompetenceName($id_competence, $new_value, $language);
					$output = array('success' => $res ? true : false);
					if ($res) $output['new_value'] = stripslashes($new_value);
					echo $this->json->encode($output);
				} break;

				case 'description': {
					$res = $this->model->updateCompetenceDescription($id_competence, $new_value, $language);
					$output = array('success' => $res ? true : false);
					if ($res) $output['new_value'] = stripslashes($new_value);
					echo $this->json->encode($output);
				} break;
				
				case 'typology': {
					$res = $this->model->updateCompetenceTypology($id_competence, $new_value);
					$output = array('success' => $res ? true : false);
					if ($res) {
						$typologies = $this->model->getCompetenceTypologies();
						$output['new_value'] = $typologies[$new_value];
					}
					echo $this->json->encode($output);
				} break;

				case 'type': {
					$res = $this->model->updateCompetenceType($id_competence, $new_value);
					$output = array('success' => $res ? true : false);
					if ($res) {
						$types = $this->model->getCompetenceTypes();
						$output['new_value'] = $types[$new_value];
					}
					echo $this->json->encode($output);
				} break;

				default: {
					echo $this->json->encode(array('success' => false));
				} break;
			}
		}
	}

}

?>