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

class GamesAlmsController extends AlmsController {

	protected $model = null;
	protected $json = null;
	protected $permissions = null;

	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');

		$this->model = new GamesAlms();
		$this->json = new Services_JSON();
		$this->permissions = array(
			'view' => checkPerm('view', true, 'games', 'lms'),
			'add' => checkPerm('mod', true, 'games', 'lms'),
			'mod' => checkPerm('mod', true, 'games', 'lms'),
			'del' => checkPerm('mod', true, 'games', 'lms'),
			'subscribe' => checkPerm('subscribe', true, 'games', 'lms')
		);
	}



	protected function _getMessage($code) {
		$message = "";
		switch ($code) {
			case "no permission": $message = ""; break;
		}
		return $message;
	}


	public function show() {

		if(isset($_GET['error'])) UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'games'));
		if(isset($_GET['success']))UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'games'));

		$this->render('show', array(
			'permissions' => $this->permissions
		));
	}

	public function getlist() {

		$start_index	= Get::req('startIndex', DOTY_INT, 0);
		$results		= Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort			= Get::req('sort', DOTY_MIXED, 'title');
		$dir			= Get::req('dir', DOTY_MIXED, 'asc');
		$filter_text	= Get::req('filter_text', DOTY_STRING, "");

		$total_games = $this->model->total($filter_text);
		$array_games = $this->model->findAll($start_index, $results, $sort, $dir, array('text' => $filter_text ) );


		$games_id_arr =array();
		foreach($array_games as $key => $value) {
			$type =$array_games[$key]['type_of'];
			if ($type == 'file') {
				$games_id_arr[]=$value['id_game'];
			}
		}

		require_once(_lms_.'/lib/lib.kbres.php');
		$kbres =new KbRes();
		$categorized_file_items =$kbres->getCategorizedResources($games_id_arr, "file", "games", true);
		$categorized_file_items_id =(!empty($categorized_file_items) ? array_keys($categorized_file_items) : array());


		$list = array();
		foreach($array_games as $key => $value) {
			$array_games[$key]['id'] = $value['id_game'];
			if($filter_text) {
				$array_games[$key]['title'] = highlightText($value['title'], $filter_text);
				$array_games[$key]['description'] = highlightText($value['description'], $filter_text);
			}
			$array_games[$key]['start_date'] = Format::date($value['start_date'], 'date');
			$array_games[$key]['end_date'] = Format::date($value['end_date'], 'date');
			$type =$array_games[$key]['type_of'];
			if ($type == 'file' || $type == 'scorm') {
				if ($type == 'scorm' || in_array($value['id_game'], $categorized_file_items_id)) {
					$array_games[$key]['categorize'] = '<a class="ico-sprite subs_categorize" title="'.Lang::t('_CATEGORIZE', 'kb').'"
						href="index.php?r=alms/games/categorize&id_game='.$value['id_game'].'"><span>'
						.Lang::t('_CATEGORIZE', 'kb').'</span></a>';
				}
				else {
					$array_games[$key]['categorize'] = '<a class="ico-sprite fd_notice" title="'.Lang::t('_NOT_CATEGORIZED', 'kb').'"
						href="index.php?r=alms/games/categorize&id_game='.$value['id_game'].'"><span>'
						.Lang::t('_NOT_CATEGORIZED', 'kb').'</span></a>';
				}
			} else {
				$array_games[$key]['categorize'] = '';
			}
			if($value['access_entity']) {
				$array_games[$key]['user'] = '<a class="ico-sprite subs_user" title="'.Lang::t('_ASSIGN_USERS', 'games').'"
					href="index.php?r=alms/games/mod_user&id_game='.$value['id_game'].'&load=1"><span>'
					.Lang::t('_ASSIGN_USERS', 'games').'</span></a>';
			} else {
				$array_games[$key]['user'] = '<a class="ico-sprite fd_notice" title="'.Lang::t('_NO_USER_SELECTED', 'games').'"
					href="index.php?r=alms/games/mod_user&id_game='.$value['id_game'].'&load=1"><span>'
					.Lang::t('_ASSIGN_USERS', 'games').'</span></a>';
			}
			$array_games[$key]['edit'] = '<a class="ico-sprite subs_mod" href="index.php?r=alms/games/edit&id_game='.$value['id_game'].'"><span>'
				.Lang::t('_MOD', 'games').'</span></a>';
			$array_games[$key]['del'] = 'ajax.adm_server.php?r=alms/games/del&id_game='.$value['id_game'];
		}

		$result = array(
			'totalRecords' => $total_games,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($array_games),
			'records' => $array_games
		);

		$this->data = $this->json->encode($result);
		echo $this->data;
	}

	protected function add($data = false) {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		require_once(_base_.'/lib/lib.form.php');
		if(!$data) {
			$data = array(
				'title' => '',
				'description' => '',
				'start_date' => Format::date( date('Y-m-d'), 'date' ),
				'end_date' => Format::date( date('Y-m-d'), 'date' ),
				'type_of' => 'scorm'
			);
		}
		$this->render('add', array('data' => $data));
	}

	protected function insert() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$data = array();
		$data['title']			= Get::req('title', DOTY_MIXED, '');
		$data['start_date']		= Get::req('start_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['end_date']		= Get::req('end_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['description']	= Get::req('description', DOTY_MIXED, '');
		$data['type_of']		= Get::req('type_of', DOTY_STRING, '');
		$data['play_chance']	= Get::req('play_chance', DOTY_STRING, '');
		$data['start_date']	= Format::dateDb($data['start_date'], 'date');
		$data['end_date']	= Format::dateDb($data['end_date'], 'date');

		//print_r($data); die();


		$id_game = $this->model->save($data);
		if(!$id_game) {
			UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'games'));
			$this->add($data);
		} elseif($data['type_of'] != 'none') {
			Util::jump_to('index.php?r=alms/games/add_obj&id_game='.$id_game);
		} else {

			Util::jump_to('index.php?r=alms/games/show&success=1');
		}
	}

	protected function add_obj() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}
		
		$id_game = Get::req('id_game', DOTY_INT, 0);
		$data = $this->model->findByPk($id_game);
		$back_url = 'index.php?r=alms/games/insert_obj&id_game='.$id_game;

		switch($data['type_of']) {
			case "scorm" : {
				require_once(_lms_.'/class.module/learning.scorm.php');
				$l_obj = new Learning_ScormOrg();
				$l_obj->create( $back_url );
			};break;
		}

	}

	protected function insert_obj() {
		if (!$this->permissions['add']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}
		
		$data['id_game'] = Get::req('id_game', DOTY_INT, 0);
		$data['id_resource'] = Get::req('id_lo', DOTY_INT, 0);
		$create_result = Get::req('create_result', DOTY_INT, 0);
		if($create_result >= 1) {

			if($this->model->save($data)) {
				$data =$this->model->findByPk($data['id_game']);
				if ($data['type_of'] == 'file' || $data['type_of'] == 'scorm') { // Save resource as uncategorized
					require_once(_lms_.'/lib/lib.kbres.php');
					$kbres =new KbRes();
					$kbres->saveUncategorizedResource($data['title'], $data['id_resource'],
						$data['type_of'], 'games', $data['id_game']
					);
				}
				Util::jump_to('index.php?r=alms/games/show&success=1');
			}
		} else {
			// destroy the empty game
			$this->model->delByPk($data['id_game']);
		}
		Util::jump_to('index.php?r=alms/games/show&error=1');
	}

	protected function edit($data = false) {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		require_once(_base_.'/lib/lib.form.php');

		$id_game = Get::req('id_game', DOTY_INT, 0);
		$data = $this->model->findByPk($id_game);

		$data['start_date'] = Format::date($data['start_date'], 'date');
		$data['end_date']	= Format::date($data['end_date'], 'date');

		$this->render('mod', array('data' => $data));
	}

	protected function update() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$data = array();
		$data['id_game']		= Get::req('id_game', DOTY_MIXED, '');
		$data['title']			= Get::req('title', DOTY_MIXED, '');
		$data['start_date']		= Get::req('start_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['end_date']		= Get::req('end_date', DOTY_MIXED, Format::date( date('Y-m-d'), 'date' ));
		$data['play_chance']	= Get::req('play_chance', DOTY_STRING, '');
		$data['description']	= Get::req('description', DOTY_MIXED, '');
		$data['type_of']		= Get::req('type_of', DOTY_STRING, '');

		$data['start_date']	= Format::dateDb($data['start_date'], 'date');
		$data['end_date']	= Format::dateDb($data['end_date'], 'date');

		$id_game = $this->model->save($data);
		if(!$id_game) {
			UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'games'));
			$this->add($data);
		} elseif($data['type_of'] != 'none') {
			Util::jump_to('index.php?r=alms/games/mod_obj&id_game='.$id_game);
		} else {

			Util::jump_to('index.php?r=alms/games/show&success=1');
		}
	}

	protected function mod_obj() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$id_game = Get::req('id_game', DOTY_INT, 0);
		$data = $this->model->findByPk($id_game);
		$back_url = 'index.php?r=alms/games/update_obj&id_game='.$id_game;

		switch($data['type_of']) {
			case "file" : {
				require_once(_lms_.'/class.module/learning.item.php');
				$l_obj = new Learning_Item();
				$l_obj->edit( $data['id_resource'], $back_url );
			};break;
			case "scorm" : {
				//cannot be modified
				Util::jump_to('index.php?r=alms/games/show');
			};break;
			case "none" :
			default: {
				Util::jump_to('index.php?r=alms/games/show');
			};break;
		}

	}

	protected function update_obj() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$data['id_game'] = Get::req('id_game', DOTY_INT, 0);
		$data['id_resource'] = Get::req('id_lo', DOTY_INT, 0);
		$mod_result = Get::req('mod_result', DOTY_INT, 0);
		if($mod_result >= 1) {

			if($this->model->save($data)) Util::jump_to('index.php?r=alms/games/show&success=1');
		}
		Util::jump_to('index.php?r=alms/games/show&error=1');
	}

	protected function del() {
		if (!$this->permissions['del']) {
			$output = array('success' => false, 'message' => $this->_getMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_game = Get::req('id_game', DOTY_INT, 0);
		$data = $this->model->findByPk($id_game);

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
			$output['success'] = $this->model->delByPk($id_game);
			if ($output['success'] && ($data['type_of'] == 'file' || $data['type_of'] == 'scorm')) {
				require_once(_lms_.'/lib/lib.kbres.php');
				$kbres =new KbRes();
				$kbres->deleteResourceFromItem($data['id_resource'], $data['type_of'], 'games');
			}
		}
		else
			$output['success'] = false;
		echo $this->json->encode($output);
	}

	/**
	 * Modify and save the users that can see a games
	 */
	protected function mod_user() {
		if (!$this->permissions['subscribe']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		// undo selected
		if(isset($_POST['cancelselector'])) Util::jump_to('index.php?r=alms/games/show');

		$id_game = Get::req('id_game', DOTY_INT, 0);
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
			$old_selection = $this->model->accessList($id_game);
			$new_selection 	= $user_selector->getSelection($_POST);
			//save
			if($this->model->updateAccessList($id_game, $old_selection, $new_selection)) Util::jump_to('index.php?r=alms/games/show&success=1');
			else Util::jump_to('index.php?r=alms/games/show&error=1');
		}
		// load saved actions
		if(isset($_GET['load'])) {
			$selection = $this->model->accessList($id_game);
			$user_selector->resetSelection($selection);
		}
		// render the user selector
		$this->render('mod_user', array(
			'id_game' => $id_game,
			'user_selector' => $user_selector
		));
	}


	public function categorize() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$id_game = Get::req('id_game', DOTY_INT, 0);

		require_once(_lms_.'/lib/lib.kbres.php');
		$kbres =new KbRes();

		if ($id_game > 0) {
			$data =$this->model->findByPk($id_game);
			$r_data =$kbres->getResourceFromItem($data['id_resource'], $data['type_of'], 'games');
		}


		if (isset($_POST['subcategorize_switch'])) {
			$cat_sub_items =Get::pReq('subcategorize_switch', DOTY_INT);
			$res_id =(int)$r_data['res_id'];
			$r_env_parent_id =(int)$r_data['r_env_parent_id'];

			$kbres->saveResourceSubCategorizePref($res_id, $cat_sub_items);

			Util::jump_to('index.php?r=alms/games/categorize&amp;id_game='.$r_env_parent_id);
			die();
		}
		else if (isset($_POST['org_categorize_save'])) {
			require_once(_lms_.'/lib/lib.kbres.php');

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

			$kbres =new KbRes();
			$res_id =$kbres->saveResource($res_id, $name, $original_name, $desc, $r_item_id,
				$type, $env, $env_parent_id, $param, $alt_desc, $lang, $force_visible,
				$is_mobile, $folders, $json_tags
			);

			Util::jump_to('index.php?r=alms/games/show');
		} else if (isset($_POST['org_categorize_cancel'])) {

			Util::jump_to('index.php?r=alms/games/show');
		} else if ($data['type_of'] == 'scorm' && $r_data && $r_data['sub_categorize'] == 1) {
			$this->categorize_sco($id_game, $data);
		} else {

			$data =$this->model->findByPk($id_game);
			$data['item_id']=$id_game;

			$this->render('categorize', array(
				'id_game' => $id_game,
				'data'=>$data,
				'r_param'=>'',
				'back_url'=>'index.php?r=alms/games/show',
				'form_url'=>'index.php?r=alms/games/categorize&amp;id_game='.$id_game,
			));
		}
	}


	public function categorize_sco($id_game, $data) {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$sco_id =Get::req('sco_id', DOTY_INT, 0);

		if ($sco_id > 0) {

			$qtxt ="SELECT idscorm_item, title, identifierref FROM
				".$GLOBALS['prefix_lms']."_scorm_items WHERE idscorm_item='".(int)$sco_id."'
				AND idscorm_organization='".(int)$data['id_resource']."'";
			$q =sql_query($qtxt);

			$row =sql_fetch_assoc($q);

			$sco_data =array();
			$sco_data['item_id'] =$sco_id;
			$sco_data['title'] =$row['title'];
			$sco_data['type_of'] ='scoitem';
			$sco_data['id_resource'] =$sco_id;
			$this->render('categorize', array(
				'id_game' => $id_game,
				'data'=>$sco_data,
				'r_param'=>'chapter='.$row['identifierref'],
				'back_url'=>'index.php?r=alms/games/categorize&amp;id_game='.$id_game,
				//'form_url'=>'index.php?r=alms/games/save_sco_categorize',
				'form_url'=>'index.php?r=alms/games/categorize&amp;id_game='.$id_game,
			));
		}
		else {
			$this->render('sco_table', array(
				'id_game' => $id_game,
				'id_resource' => $data['id_resource'],
				'games_data' => $data,
			));
		}

	}


	public function save_sco_categorize() {
		if (!$this->permissions['mod']) {
			$this->render('invalid', array(
				'message' => $this->_getMessage('no permission'),
				'back_url' => 'index.php?r=alms/games/show'
			));
			return;
		}

		$id_game = Get::req('id_game', DOTY_INT, 0);

		if (isset($_POST['org_categorize_cancel'])) {
			Util::jump_to('index.php?r=alms/games/categorize&id_game='.$id_game);
		}
		else {
			$this->categorize();
		}

	}


}
