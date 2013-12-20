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

class CommunicationAlmsController extends AlmsController {
	
	protected $model = null;
	protected $json = null;
	protected $permissions = null;
	
	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');

		$this->model = new CommunicationAlms();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view' => checkPerm('view', true, 'communication', 'lms'),
			'add' => checkPerm('mod', true, 'communication', 'lms'),
			'mod' => checkPerm('mod', true, 'communication', 'lms'),
			'del' => checkPerm('mod', true, 'communication', 'lms'),
			'subscribe' => checkPerm('subscribe', true, 'course', 'lms'),
			'add_category' => checkPerm('mod', true, 'communication', 'lms'),
			'mod_category' => checkPerm('mod', true, 'communication', 'lms'),
			'del_category' => checkPerm('mod', true, 'communication', 'lms')
		);
	}

	protected function _getSessionValue($index, $default = false) {
		if (!isset($_SESSION['communication'])) $_SESSION['communication'] = array();
		return isset($_SESSION['communication'][$index]) ? $_SESSION['communication'][$index] : $default;
	}

	protected function _setSessionValue($index, $value) {
		$_SESSION['communication'][$index] = $value;
	}

	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
		}
		return $message;
	}
	
	public function show() {

		if(isset($_GET['error'])) UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
		if(isset($_GET['success']))UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'communication'));

		$this->render('show', array(
			'selected_category' => 0,
			'show_descendants' => true,
			'filter_text' => "",
			'permissions' => $this->permissions
		));
	}

	public function getlist() {

		$id_category = Get::req('id_category', DOTY_INT, 0);
		$show_descendants = Get::req('descendants', DOTY_INT, 0) > 0;

		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'title');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');
		$filter_text	= Get::req('filter_text', DOTY_STRING, "");

		$filter = array('text' => $filter_text );

		$total_comm = $this->model->total($filter, $id_category, $show_descendants);
		$array_comm = $this->model->findAll($start_index, $results, $sort, $dir, $filter, $id_category, $show_descendants );


		$comm_id_arr =array();
		foreach($array_comm as $key => $value) {
			$type =$array_comm[$key]['type_of'];
			if ($type == 'file') {
				$comm_id_arr[]=$value['id_comm'];
			}
		}

		require_once(_lms_.'/lib/lib.kbres.php');
		$kbres =new KbRes();
		$categorized_file_items =$kbres->getCategorizedResources($comm_id_arr, "file", "communication", true);
		$categorized_file_items_id =(!empty($categorized_file_items) ? array_keys($categorized_file_items) : array());


		$list = array();
		foreach($array_comm as $key => $value) {
			$array_comm[$key]['id'] = $value['id_comm'];
			if($filter_text) {
				$array_comm[$key]['title'] = highlightText($value['title'], $filter_text);
				$array_comm[$key]['description'] = highlightText($value['description'], $filter_text);
			}
			$array_comm[$key]['publish_date'] = Format::date($value['publish_date'], 'date');
			$type =$array_comm[$key]['type_of'];
			if ($type == 'file' || $type == 'scorm') {
				if ($type == 'scorm' || in_array($value['id_comm'], $categorized_file_items_id)) {
					$array_comm[$key]['categorize'] = '<a class="ico-sprite subs_categorize" title="'.Lang::t('_CATEGORIZE', 'kb').'"
						href="index.php?r=alms/communication/categorize&id_comm='.$value['id_comm'].'"><span>'
						.Lang::t('_CATEGORIZE', 'kb').'</span></a>';
				}
				else {
					$array_comm[$key]['categorize'] = '<a class="ico-sprite fd_notice" title="'.Lang::t('_NOT_CATEGORIZED', 'kb').'"
						href="index.php?r=alms/communication/categorize&id_comm='.$value['id_comm'].'"><span>'
						.Lang::t('_NOT_CATEGORIZED', 'kb').'</span></a>';
				}
			}
			else {
				$array_comm[$key]['categorize'] = '';
			}
			if($value['access_entity']) {
				$array_comm[$key]['user'] = '<a class="ico-sprite subs_user" title="'.Lang::t('_ASSIGN_USERS', 'communication').'"
					href="index.php?r=alms/communication/mod_user&id_comm='.$value['id_comm'].'&load=1"><span>'
					.Lang::t('_ASSIGN_USERS', 'communication').'</span></a>';
			} else {
				$array_comm[$key]['user'] = '<a class="ico-sprite fd_notice" title="'.Lang::t('_NO_USER_SELECTED', 'communication').'"
					href="index.php?r=alms/communication/mod_user&id_comm='.$value['id_comm'].'&load=1"><span>'
					.Lang::t('_ASSIGN_USERS', 'communication').'</span></a>';
			}
			$array_comm[$key]['edit'] = '<a class="ico-sprite subs_mod" href="index.php?r=alms/communication/edit&id_comm='.$value['id_comm'].'"><span>'
				.Lang::t('_MOD', 'communication').'</span></a>';
			$array_comm[$key]['del'] = 'ajax.adm_server.php?r=alms/communication/del&id_comm='.$value['id_comm'];
		}

		$result = array(
			'totalRecords' => $total_comm,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($array_comm),
			'records' => $array_comm
		);

		$this->data = $this->json->encode($result);
		echo $this->data;
	}
	
	protected function add($data = false) {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		require_once(_base_.'/lib/lib.form.php');
		if(!$data) {
			$data = array(
				'title' => '',
				'description' => '',
				'publish_date' => Format::date( date('Y-m-d'), 'date' ),
				'type_of' => 'none',
				'id_course' => 0,
				'id_category' => Get::req('id', DOTY_INT, 0)
			);
		}

		$this->render('add', array(
			'data' => $data,
			'course_name' => ""
		));
	}

	protected function insert() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		if (Get::req('undo', DOTY_MIXED, false) !== false) Util::jump_to('index.php?r=alms/communication/show');

		$data = array();
		$data['title']			= Get::req('title', DOTY_MIXED, '');
		$data['publish_date']	= Get::req('publish_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['description']	= Get::req('description', DOTY_MIXED, '');
		$data['type_of']		= Get::req('type_of', DOTY_STRING, '');
		$data['publish_date']	= Format::dateDb($data['publish_date'], 'date');
		$data['id_category'] = Get::req('id_category', DOTY_INT, 0);
		$data['id_course'] = Get::req('id_course', DOTY_INT, 0);
		
		$id_comm = $this->model->save($data);
		if(!$id_comm) {
			UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
			$this->add($data);
		} elseif($data['type_of'] != 'none') {
			Util::jump_to('index.php?r=alms/communication/add_obj&id_comm='.$id_comm);
		} else {
			
			Util::jump_to('index.php?r=alms/communication/show&success=1');
		}
	}
	
	protected function add_obj() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		$data = $this->model->findByPk($id_comm);
		$back_url = 'index.php?r=alms/communication/insert_obj&id_comm='.$id_comm;

		switch($data['type_of']) {
			case "file" : {
				require_once(_lms_.'/class.module/learning.item.php');
				$l_obj = new Learning_Item();
				$l_obj->create( $back_url );
			};break;
			case "scorm" : {
				require_once(_lms_.'/class.module/learning.scorm.php');
				$l_obj = new Learning_ScormOrg();
				$l_obj->create( $back_url );
			};break;
			case "none" :
			default: {
				Util::jump_to('index.php?r=alms/communication/show');
			};break;
		}

	}

	protected function insert_obj() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$data['id_comm'] = Get::req('id_comm', DOTY_INT, 0);
		$data['id_resource'] = Get::req('id_lo', DOTY_INT, 0);
		$create_result = Get::req('create_result', DOTY_INT, 0);
		if($create_result >= 1) {
			
			if($this->model->save($data)) {
				$data =$this->model->findByPk($data['id_comm']);
				if ($data['type_of'] == 'file' || $data['type_of'] == 'scorm') { // Save resource as uncategorized
					require_once(_lms_.'/lib/lib.kbres.php');
					$kbres =new KbRes();
					$kbres->saveUncategorizedResource($data['title'], $data['id_resource'],
						$data['type_of'], 'communication', $data['id_comm']
					);
				}
				Util::jump_to('index.php?r=alms/communication/show&success=1');
			}
		}
        // destroy the empty game
        $this->model->delByPk($data['id_comm']);
		Util::jump_to('index.php?r=alms/communication/show&error=1');
	}

	protected function edit() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		require_once(_base_.'/lib/lib.form.php');
		
		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		$data = $this->model->findByPk($id_comm);

		$data['publish_date'] = Format::date($data['publish_date'], 'date');

		$course_model = new CourseAlms();
		$cinfo = $course_model->getCourseModDetails($data['id_course']);
		$course_name = /*($cinfo['code'] ? "[".$cinfo['code']."] " : "").*/$cinfo['name'];
		YuiLib::load('autocomplete');

		$this->render('mod', array(
			'data' => $data,
			'course_name' => $course_name
		));
	}

	protected function update() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		if (Get::req('undo', DOTY_MIXED, false) !== false) Util::jump_to('index.php?r=alms/communication/show');

		$data = array();
		$data['id_comm']		= Get::req('id_comm', DOTY_MIXED, '');
		$data['title']			= Get::req('title', DOTY_MIXED, '');
		$data['publish_date']	= Get::req('publish_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['description']	= Get::req('description', DOTY_MIXED, '');
		$data['type_of']		= Get::req('type_of', DOTY_STRING, '');
		$data['id_course']	= Get::req('id_course', DOTY_INT, 0);

		$data['publish_date'] = Format::dateDb($data['publish_date'], 'date');

		$id_comm = $this->model->save($data);
		if(!$id_comm) {
			UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'communication'));
			$this->add($data);
		} elseif($data['type_of'] != 'none') {
			Util::jump_to('index.php?r=alms/communication/mod_obj&id_comm='.$id_comm);
		} else {

			Util::jump_to('index.php?r=alms/communication/show&success=1');
		}
	}

	protected function mod_obj() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		$data = $this->model->findByPk($id_comm);
		$back_url = 'index.php?r=alms/communication/update_obj&id_comm='.$id_comm;

		switch($data['type_of']) {
			case "file" : {
				require_once(_lms_.'/class.module/learning.item.php');
				$l_obj = new Learning_Item();
				$l_obj->edit( $data['id_resource'], $back_url );
			};break;
			case "scorm" : {
				//cannot be modified
				Util::jump_to('index.php?r=alms/communication/show');
			};break;
			case "none" :
			default: {
				Util::jump_to('index.php?r=alms/communication/show');
			};break;
		}

	}

	protected function update_obj() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$data['id_comm'] = Get::req('id_comm', DOTY_INT, 0);
		$data['id_resource'] = Get::req('id_lo', DOTY_INT, 0);
		$mod_result = Get::req('mod_result', DOTY_INT, 0);
		if($mod_result >= 1) {

			if($this->model->save($data)) Util::jump_to('index.php?r=alms/communication/show&success=1');
		}
		Util::jump_to('index.php?r=alms/communication/show&error=1');
	}

	protected function del() {
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}
		
		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		$data = $this->model->findByPk($id_comm);

		if($data['id_resource']) {
			switch($data['type_of']) {
				case "file" : {
					require_once(_lms_.'/class.module/learning.item.php');
					$l_obj = new Learning_Item();
					$re = $l_obj->del($data['id_resource']);
				};break;
				case "scorm" : {
					require_once(_lms_.'/class.module/learning.scorm.php');
					$l_obj = new Learning_ScormOrg();
					$re = $l_obj->del($data['id_resource']);
				};break;
				case "none" :
				default: {
					$re = true;
				};break;
			}
		} else {
			$re = true;
		}
		if($re) {
			$output['success'] = $this->model->delByPk($id_comm);
			if ($output['success'] && ($data['type_of'] == 'file' || $data['type_of'] == 'scorm')) {
				require_once(_lms_.'/lib/lib.kbres.php');
				$kbres =new KbRes();
				$kbres->deleteResourceFromItem($data['id_resource'], $data['type_of'], 'communication');
			}
		}
		else
			$output['success'] = false;

		echo $this->json->encode($output);
	}

	/**
	 * Modify and save the users that can see a communication
	 */
	protected function mod_user() {
		if (!$this->permissions['subscribe']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		// undo selected
		if(isset($_POST['cancelselector'])) Util::jump_to('index.php?r=alms/communication/show');

		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		// instance of the user selector
		require_once(_adm_.'/class.module/class.directory.php');
		$user_selector = new UserSelector();
		$user_selector->show_user_selector = TRUE;
		$user_selector->show_group_selector = TRUE;
		$user_selector->show_orgchart_selector = TRUE;
		$user_selector->show_orgchart_simple_selector = FALSE;
		// save new setting
		if(isset($_POST['okselector'])) {
			
			//compute new selection
			$old_selection = $this->model->accessList($id_comm); //print_r($old_selection);
			$new_selection 	= $user_selector->getSelection($_POST); /*print_r($_POST);*/ //print_r($new_selection); die();
			//save
			if($this->model->updateAccessList($id_comm, $old_selection, $new_selection)) Util::jump_to('index.php?r=alms/communication/show&success=1');
			else Util::jump_to('index.php?r=alms/communication/show&error=1');
		}
		// load saved actions
		if(isset($_GET['load'])) {
			$selection = $this->model->accessList($id_comm);
			$user_selector->resetSelection($selection);
		}
		// render the user selector
		$this->render('mod_user', array(
			'id_comm' => $id_comm,
			'user_selector' => $user_selector
		));
	}


	public function categorize() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$id_comm = Get::req('id_comm', DOTY_INT, 0);
		//$r_data =

		require_once(_lms_.'/lib/lib.kbres.php');
		$kbres =new KbRes();

		if ($id_comm > 0) {
			$data =$this->model->findByPk($id_comm);
			$r_data =$kbres->getResourceFromItem($data['id_resource'], $data['type_of'], 'communication');
		}
		

		if (isset($_POST['subcategorize_switch'])) {
			$cat_sub_items =Get::pReq('subcategorize_switch', DOTY_INT);
			$res_id =(int)$r_data['res_id'];
			$r_env_parent_id =(int)$r_data['r_env_parent_id'];

			$kbres->saveResourceSubCategorizePref($res_id, $cat_sub_items);

			Util::jump_to('index.php?r=alms/communication/categorize&amp;id_comm='.$r_env_parent_id);
			die();
		}
		else if (isset($_POST['org_categorize_save'])) {

			$res_id =Get::req('res_id', DOTY_INT, 0);
			$name =Get::req('r_name', DOTY_STRING, "");
			$original_name =''; // won't update this field
			$desc =Get::req('r_desc', DOTY_STRING, "");
			$r_item_id =Get::req('r_item_id', DOTY_INT, 0);
			$type =Get::req('r_type', DOTY_STRING, "");
			$env =Get::req('r_env', DOTY_STRING, "");
			$env_parent_id =Get::req('r_env_parent_id', DOTY_INT, 0);
			$param =Get::req('r_param', DOTY_STRING, "");
			$alt_desc ='';
			$lang_id =Get::req('r_lang', DOTY_INT, "");
			$lang_arr =Docebo::langManager()->getAllLangCode();
			$lang =$lang_arr[$lang_id];
			$force_visible =Get::req('force_visible', DOTY_INT, 0);
			$is_mobile =Get::req('is_mobile', DOTY_INT, 0);
			$folders =Get::req('h_selected_folders', DOTY_STRING, "");
			$json_tags =Util::strip_slashes(Get::req('tag_list', DOTY_STRING, "[]"));
			
			$res_id =$kbres->saveResource($res_id, $name, $original_name, $desc, $r_item_id,
				$type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible,
				$is_mobile,	$folders, $json_tags
			);

			Util::jump_to('index.php?r=alms/communication/show');
		}
		else if (isset($_POST['org_categorize_cancel'])) {
			Util::jump_to('index.php?r=alms/communication/show');
		}
		else if ($data['type_of'] == 'scorm' && $r_data && $r_data['sub_categorize'] == 1) {
			$this->categorize_sco($id_comm, $data);
		}
		/* else if ($data['type_of'] == 'scorm' && $r_data && $r_data['sub_categorize'] == -1) {
			$this->subcategorize_ask($id_comm, $data, $r_data);
		} */
		else {
			$data =$this->model->findByPk($id_comm);
			$data['item_id']=$id_comm;

			$this->render('categorize', array(
				'id_comm' => $id_comm,
				'data'=>$data,
				'r_param'=>'',
				'back_url'=>'index.php?r=alms/communication/show',
				'form_url'=>'index.php?r=alms/communication/categorize&amp;id_comm='.$id_comm,
			));
		}
	}
	

	public function categorize_sco($id_comm, $data) {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/communication/show'
			));
			return;
		}

		$sco_id =Get::req('sco_id', DOTY_INT, 0);

		if ($sco_id > 0) {

			$qtxt ="SELECT idscorm_item, title, identifierref FROM
				".$GLOBALS['prefix_lms']."_scorm_items WHERE idscorm_item='".(int)$sco_id."'
				AND idscorm_organization='".(int)$data['id_resource']."'";
			$q =sql_query($qtxt);

			$row =mysql_fetch_assoc($q);

			$sco_data =array();
			$sco_data['item_id'] =$sco_id;
			$sco_data['title'] =$row['title'];
			$sco_data['type_of'] ='scoitem';
			$sco_data['id_resource'] =$sco_id;
			$this->render('categorize', array(
				'id_comm' => $id_comm,
				'data'=>$sco_data,
				'r_param'=>'chapter='.$row['identifierref'],
				'back_url'=>'index.php?r=alms/communication/categorize&amp;id_comm='.$id_comm,
				//'form_url'=>'index.php?r=alms/communication/save_sco_categorize',
				'form_url'=>'index.php?r=alms/communication/categorize&amp;id_comm='.$id_comm,
			));
		}
		else {
			$this->render('sco_table', array(
				'id_comm' => $id_comm,
				'id_resource' => $data['id_resource'],
				'comm_data' => $data,
			));
		}

	}

	
	public function save_sco_categorize() {

		$id_comm = Get::req('id_comm', DOTY_INT, 0);

		if (isset($_POST['org_categorize_cancel'])) {
			Util::jump_to('index.php?r=alms/communication/categorize&id_comm='.$id_comm);
		}
		else {
			$this->categorize();
		}

	}



	//--- TREE TASKS AND FUNCTIONS -----------------------------------------------


	protected function _getNodeActions($node) {
		if (!is_array($node)) return false; //unrecognized type for node data
		$actions = array();
		$id_action = $node['id'];
		$is_root = ($id_action == 0);

		//permissions
		$can_mod = $this->permissions['mod_category'];
		$can_del = $this->permissions['del_category'];

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

	protected function _assignActions(&$nodes) {
		if (!is_array($nodes)) return;
		for ($i=0; $i<count($nodes); $i++) {
			$nodes[$i]['node']['options'] = $this->_getNodeActions($nodes[$i]['node']);
			if (isset($nodes[$i]['children']) && count($nodes[$i]['children']) > 0) {
				$this->_assignActions($nodes[$i]['children']);
			}
		}
	}

	public function gettreedataTask() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {

			case "expand": {
				$node_id = Get::req('node_id', DOTY_INT, 0);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

				if ($initial) {
					//get selected category from session and set the expanded tree
					$node_id = $this->_getSessionValue('selected_node', 0);
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
				$this->_setSessionValue('selected_node', Get::Req('node_id', DOTY_INT, 0));
			} break;

			case "delete": {
				//check permissions
				if (!$this->permissions['mod']) {
					$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
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
					$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$this->move_categoryTask();
			} break;
		}
	}

	public function add_categoryTask() {
		//check permissions
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_parent = Get::req('id', DOTY_INT, -1);
		if ($id_parent < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}

		$this->render('category_editmask', array(
			'title' => Lang::t('_ADD', 'communication'),
			'id_parent' => $id_parent,
			'json' => $this->json
		));
	}


	public function mod_categoryTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_category = Get::req('id', DOTY_INT, -1);
		if ($id_category <= 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getMessage("invalid category"))
			);
			echo $this->json->encode($output);
			return;
		}

		//retrieve category info (name and description
		$info = $this->model->getCategoryInfo($id_category);

		$this->render('category_editmask', array(
			'title' => Lang::t('_MOD', 'communication'),
			'id_category' => $id_category,
			'category_langs' => $info->langs,
			'json' => $this->json
		));
	}


	public function add_category_actionTask() {
		//check permissions
		if (!$this->permissions['add']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//set up the data to insert into DB
		$id_parent = Get::req('id_parent', DOTY_INT, -1);
		if ($id_parent < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getMessage("invalid category"))
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
				'message' => UIFeedback::perror($this->_getMessage("create category"))
			);
		}
		echo $this->json->encode($output);
	}


	public function mod_category_actionTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		//set up the data to insert into DB
		$id_category = Get::req('id_category', DOTY_INT, -1);
		if ($id_category < 0) {
			$output = array(
				'success' => false,
				'message' => UIFeedback::perror($this->_getMessage("invalid category"))
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
				'message' => UIFeedback::perror($this->_getMessage("edit category"))
			);
		}
		echo $this->json->encode($output);
	}


	public function move_categoryTask() {
		//check permissions
		if (!$this->permissions['mod']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$src = Get::req('src', DOTY_INT, -1);
		$dest = Get::req('dest', DOTY_INT, -1);

		$output = array();

		if ($src <= 0 || $dest < 0) {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror($this->_getMessage("invalid category"));
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->moveCategory($src, $dest);
		$output['success'] = $res ? true : false;
		if (!$res) $output['message'] = UIFeedback::perror($this->_getMessage("move category"));
		echo $this->json->encode($output);
	}

	//----------------------------------------------------------------------------

}
