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

class EnrollrulesAlmsController extends AlmsController {

	public $name = 'classroom';

	protected $json;
	protected $acl_man;

	protected $data;

	public function __construct($mvc_name) {
		parent::__construct($mvc_name);

		require_once(_base_.'/lib/lib.json.php');

		$this->json = new Services_JSON();
		$this->model = new EnrollrulesAlms();
	}

	protected function show() {
		checkPerm('view', true, 'enrollrules', 'lms');

		if(isset($_GET['result'])) {
			if($_GET['result'] == 'true') UIFeedback::notice(Lang::t('_OPERATION_SUCCESSFUL', 'enrollrules'));
			else UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'enrollrules'));
		}
		$this->render('show', array('model' => $this->model));
	}

	/**
	 * ajax for the main table with rules listing
	 */
	protected function get() {
		checkPerm('view', true, 'enrollrules', 'lms');
		
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'title');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');
		if ($dir != 'asc' && $dir != 'desc') {
			$dir = 'asc';
		}
		$rules = $this->model->getRules($start_index, $results, $sort, $dir);
		$total_rules = $this->model->getTotalRulesCount();
		$types = $this->model->ruleTypes();
		
		foreach ($rules as $id => $rule) {

			$rules[$id]->rule_type_text = $types[$rules[$id]->rule_type];
			if($rule->id_rule != 0) {

				$rules[$id]->mod_elem = '<a class="ico-sprite subs_elem" href="index.php?r=alms/enrollrules/modelem&amp;id_rule='.$rule->id_rule.'" title="'.Lang::t('_MANAGE', 'enrollrules').'">'
					.'<span>'.Lang::t('_MANAGE', 'enrollrules').'</span></a>';
				$rules[$id]->mod = '<a id="mod_rules_'.$rule->id_rule.'" class="ico-sprite subs_mod" href="ajax.adm_server.php?r=alms/enrollrules/mod&amp;id_rule='.$rule->id_rule.'" title="'.Lang::t('_MOD', 'enrollrules').'">'
					.'<span>'.Lang::t('_MOD', 'enrollrules').'</span></a>';
				$rules[$id]->del = 'ajax.adm_server.php?r=alms/enrollrules/del&amp;id_rule='.$rule->id_rule.'';
			} else {

				$rules[$id]->mod_elem = '<a class="ico-sprite subs_elem" href="index.php?r=alms/enrollrules/modbaseelem&amp;id_rule='.$rule->id_rule.'" title="'.Lang::t('_MANAGE', 'enrollrules').'">'
					.'<span>'.Lang::t('_MANAGE', 'enrollrules').'</span></a>';
				$rules[$id]->mod = '';
				$rules[$id]->del = '';
			}
		}

		$result = array('totalRecords' => $total_rules,
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($rules),
						'records' => $rules);

		echo $this->json->encode($result);
	}

	/**
	 * Switch activation status for a rule
	 */
	protected function activate() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$result = array(
			'success' => ( $this->model->changeActivationState($id_rule) ? 'true' : 'false' )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Show the logs interface
	 */
	protected function showlog() {
		checkPerm('view', true, 'enrollrules', 'lms');

		$this->render('showlog');
	}

	/**
	 * Return the logs data for the DataTable
	 */
	protected function getlog() {
		checkPerm('view', true, 'enrollrules', 'lms');

		//read input data
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'log_time');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');
		if ($dir != 'asc' && $dir != 'desc') {
			$dir = 'asc';
		}

		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$logs = $this->model->getLogs($start_index, $results, $sort, $dir);
		$total_logs = $this->model->getTotalLogs();

		while(list($i, $log) = each($logs)) {
			$log->log_action  = Lang::t($log->log_action, 'enrollrules');
			$log->log_detail = 'index.php?r=alms/enrollrules/logdetails&amp;id_log='.$log->id_log;
			$log->rollback = 'ajax.adm_server.php?r=alms/enrollrules/logrollback&amp;id_log='.$log->id_log;
			$logs[$i] = $log;
		}

		//produce output for datatable
		$output = array(
			'totalRecords' => $total_logs,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($logs),
			'records' => $logs
		);

		echo $this->json->encode($output);
	}

	protected function logdetails() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_log = Get::req('id_log', DOTY_INT, 0);

		$data = $this->model->logInfo($id_log);

		$this->render('logdetail', array(
			'data' => $data
		));
	}

	protected function logrollback() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_log = Get::req('id_log', DOTY_INT, 0);
		$result = array(
			'success' => ( $this->model->rollbackLog($id_log) ? 'true' : 'false' )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Create a new rule
	 */
	protected function add() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$languages = Docebo::langManager()->getAllLangCode();
		array_unshift($languages, Lang::t('_ALL', 'enrollrules'));
		$types = $this->model->ruleTypes();
		unset($types['base']);
		$this->render('add', array(
			'model' => $this->model,
			'languages' => $languages,
			'types' => $types
		));
	}

	/**
	 * Save a rule
	 */
	protected function insert() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$data = array(
			'title' => Get::req('title', DOTY_MIXED, ''),
			'lang_code' => Get::req('lang_code', DOTY_MIXED, ''),
			'rule_type' => Get::req('rule_type', DOTY_MIXED, '')
		);
		$result = array(
			'success' => ( $this->model->createRule($data) ? 'true' : 'false' )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Modify a rule
	 */
	protected function mod() {
		checkPerm('view', true, 'enrollrules', 'lms');
		
		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$rule = $this->model->getRule($id_rule);
		$languages = Docebo::langManager()->getAllLangCode();
		array_unshift($languages, Lang::t('_ALL', 'enrollrules'));
		$this->render('mod', array(
			'model' => $this->model,
			'languages' => $languages,
			'rule' => $rule
		));
	}

	/**
	 * Update an rule
	 */
	protected function update() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$data = array(
			'id_rule' => Get::req('id_rule', DOTY_INT, 0),
			'title' => Get::req('title', DOTY_MIXED, ''),
			'lang_code' => Get::req('lang_code', DOTY_MIXED, '')
		);
		$result = array(
			'success' => ( $this->model->updateRule($data) ? 'true' : 'false' )
		);
		echo $this->json->encode($result);
	}

	/**
	 * completly delete a rule
	 */
	protected function del() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$result = array(
			'success' => ( $this->model->deleteRule($id_rule) ? 'true' : 'false' )
		);
		echo $this->json->encode($result);
	}

	/**
	 * Modify a standard rule
	 */
	protected function modelem() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_rule = Get::req('id_rule', DOTY_INT, 0);

		$rule = $this->model->getRule($id_rule);
		$types = $this->model->ruleTypes();
		$rule->rule_type_text = $types[$rule->rule_type];
		
		$course_selection = $this->json->decode($rule->course_list);
		
		$courselist = array();
		$coursepath = array();

		require_once(_lms_.'/lib/lib.course.php');
		$man_c = new Man_Course();
		if(isset($course_selection))
			$courselist = $man_c->arrCourseName( $course_selection );
		
		$columns = array(
			array('key' => 'entity', 'label' => $rule->rule_type_text),
		);
		$keys = array('id_entity', 'entity');
		while(list($id_course, $coursename) = each($courselist)) {
			$keys[] = 'course_'.$id_course;
			$columns[] = array('key' => 'course_'.$id_course, 'label' => $coursename, 'formatter' => 'coursecheckbox');
		}
		$this->render('rule', array(
			'id_rule'	=> $id_rule,
			'keys'		=> $keys,
			'columns'	=> $columns,
			'rule'		=> $rule
		));
	}

	/**
	 * Return the entities and course matrix for standard rule
	 */
	protected function getrule() {
		checkPerm('view', true, 'enrollrules', 'lms');

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'title');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$rule = $this->model->getRule($id_rule);
		$course_selection = $this->json->decode($rule->course_list);

		$courselist = array();
		if(isset($course_selection))
		foreach($course_selection as $i => $idc) {
			$courselist['course_'.$idc] = 0;
		}

		$i = 0;
		$rules = array();
		$entities = $this->model->getEntityRule($id_rule);
		$id_entities = array_keys($entities);
		$entities_name = $this->model->convertEntity($id_entities, $rule->rule_type);
		
		while(list(, $entity) = each($entities)) {
			
			$rules[$i] = array(
				'id_entity' => $entity->id_entity,
				'entity' => ( isset($entities_name[$entity->id_entity]) ? $entities_name[$entity->id_entity] : '' )
			) + $courselist;
			
			if(is_array($entity->course_list))
			foreach($entity->course_list as $j => $idc) {
				$rules[$i]['course_'.$idc] = 1;
			}
			$i++;
		}

		$result = array('totalRecords' =>  count($rules),
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($rules),
						'records' => $rules);

		echo $this->json->encode($result);
	}

	protected function saverule() {
		checkPerm('view', true, 'enrollrules', 'lms');

		if(isset($_POST['undo'])) Util::jump_to('index.php?r=alms/enrollrules/show');

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$prev_entities = $this->model->getEntityRule($id_rule);

		$re = true;
		while(list($id_entity, $courses) = each($_POST['entity_course'])) {
			
			$course_list = array();
			while(list($id_c, ) = each($courses)) $course_list[] = (int)str_replace('course_', '', $id_c);
			
			if(isset($prev_entities[$id_entity])) $re &= $this->model->saveEntityRule($id_rule, $id_entity, $course_list);
			else $re &= $this->model->insertEntityRule($id_rule, $id_entity, $course_list);
		}
		Util::jump_to('index.php?r=alms/enrollrules/show&amp;result='.($re ? 'true' : 'false'));
	}

	protected function addcourses() {
		checkPerm('view', true, 'enrollrules', 'lms');
		
		require_once(_lms_.'/lib/lib.course_managment.php');
		$course_selector = new Course_Manager();
		$course_selector->show_coursepath_selector = false;
		$course_selector->show_catalogue_selector = false;

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$rule = $this->model->getRule($id_rule);
		
		if(isset($_POST['undo'])) Util::jump_to('index.php?r=alms/enrollrules/'.( $rule->rule_type == 'base' ? 'modbaseelem' : 'modelem' ).'&amp;id_rule='.$id_rule);
		if(isset($_POST['save'])) {
			
			// Save the new course in the list
			$course_list = $course_selector->getCourseSelection($_POST);
			
			$course_selection = array_keys($course_list);
			
			$re = $this->model->updateRuleCourseSelection($id_rule, $course_selection);
			Util::jump_to('index.php?r=alms/enrollrules/'.( $rule->rule_type == 'base' ? 'modbaseelem' : 'modelem' ).'&amp;id_rule='.$id_rule.'&amp;result='.($re ? 'true' : 'false'));
		}
		if(isset($_GET['load'])) {
			
			// Load old data
			$course_selection = $this->json->decode($rule->course_list);
			if(isset($course_selection)&& is_array($course_selection)) $course_selector->resetCourseSelection( array_flip($course_selection) );
		}

		$this->render('addcourses', array(
			'rule' => $rule,
			'course_selector' => $course_selector
		));
	}

	protected function addentity() {
		checkPerm('view', true, 'enrollrules', 'lms');

		require_once(_adm_.'/class.module/class.directory.php');
		$user_selector = new UserSelector();

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$rule = $this->model->getRule($id_rule);

		$types = $this->model->ruleTypes();
		$rule->rule_type_text = $types[$rule->rule_type];
		
		if(isset($_POST['undo'])) Util::jump_to('index.php?r=alms/enrollrules/modelem&amp;id_rule='.$id_rule);
		if(isset($_POST['save'])) {

			// Save the new course in the list
			$selection = Get::req('userselector_input', DOTY_MIXED, array());
			$newsel = explode(',', $selection['entity_selection']);
			$oldsel = array_keys( $this->model->getEntityRule($id_rule) );

			$to_add = array_diff($newsel, $oldsel);
			$to_del = array_diff($oldsel, $newsel);

			$re = true;
			foreach($to_add as $i => $id_entity) {
				$re &= $this->model->insertEntityRule($id_rule, $id_entity, array());
			}
			foreach($to_del as $i => $id_entity) {
				$re &= $this->model->deleteEntityRule($id_rule, $id_entity);
			}
			Util::jump_to('index.php?r=alms/enrollrules/modelem&amp;id_rule='.$id_rule.'&amp;result='.($re ? 'true' : 'false'));
		}
		//if(isset($_GET['load'])) {

		$entities = $this->model->getEntityRule($id_rule);
		$selection = array_keys($entities);
		
		$this->render('addentity', array(
			'rule' => $rule,
			'user_selector' => $user_selector,
			'group' => ( $rule->rule_type == 'group' ? true : false ),
			'orgchart' => ( $rule->rule_type == 'orgchart' ? true : false ),
			'fncrole' => ( $rule->rule_type == 'fncrole' ? true : false ),
			'init_selection' => $selection
		));
	}

	protected function modbaseelem() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_rule = Get::req('id_rule', DOTY_INT, 0);

		$rule = $this->model->getRule($id_rule);
		$types = $this->model->ruleTypes();
		$rule->rule_type_text = $types[$rule->rule_type];

		$course_selection = $this->json->decode($rule->course_list);

		$courselist = array();
		$coursepath = array();

		require_once(_lms_.'/lib/lib.course.php');
		$man_c = new Man_Course();
		if(isset($course_selection))
			$courselist = $man_c->arrCourseName( $course_selection );

		$columns = array(
			array('key' => 'entity', 'label' => Lang::t('_ENTITY', 'enrollrules')),
		);
		$keys = array('id_entity', 'entity');
		while(list($id_course, $coursename) = each($courselist)) {
			$keys[] = 'course_'.$id_course;
			$columns[] = array('key' => 'course_'.$id_course, 'label' => $coursename, 'formatter' => 'coursecheckbox');
		}
		$this->render('baserule', array(
			'id_rule'	=> $id_rule,
			'keys'		=> $keys,
			'columns'	=> $columns,
			'rule'		=> $rule
		));
	}

	/**
	 * Return the entities and course matrix for standard rule
	 */
	protected function getbaserule() {
		checkPerm('view', true, 'enrollrules', 'lms');

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'title');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$rule = $this->model->getRule($id_rule);
		$course_selection = $this->json->decode($rule->course_list);

		$courselist = array();
		if(isset($course_selection))
		foreach($course_selection as $i => $idc) {
			$courselist['course_'.$idc] = 0;
		}

		$i = 0;
		$rules = array();
		// convert entity from id to name
		$entities = $this->model->getBaseEntityRule($id_rule);
		$entities_name = $this->model->convertEntity($entities, $rule->rule_type);

		while(list(, $entity) = each($entities)) {

			$rules[$i] = array(
				'id_entity' => ( isset($entity->id_entity) ? $entity->id_entity : 0 ),
				'entity' => ( isset($entity->id_entity) ? $entities_name[$entity->id_entity] : '' )
			) + $courselist;

			foreach($entity->course_list as $j => $idc) {
				$rules[$i]['course_'.$idc] = 1;
			}
			$i++;
		}

		$result = array('totalRecords' =>  count($rules),
						'startIndex' => $start_index,
						'sort' => $sort,
						'dir' => $dir,
						'rowsPerPage' => $results,
						'results' => count($rules),
						'records' => $rules);

		echo $this->json->encode($result);
	}

	protected function savebaserule() {
		checkPerm('view', true, 'enrollrules', 'lms');

		if(isset($_POST['undo'])) Util::jump_to('index.php?r=alms/enrollrules/show');

		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		$prev_entities = $this->model->getBaseEntityRule($id_rule, false, true);

		$re = true;
        $all_entities = $this->model->getBaseEntityRule($id_rule, false, false);
        
        foreach ($all_entities as $curr_entity) {
            $curr_entity = $curr_entity;
            $id_entity = $curr_entity->id_entity;
        
			$course_list = array();
            $courses = array();
            if (isset($_POST['entity_course'][$id_entity])){
                $courses = $_POST['entity_course'][$id_entity];
            }
			while(list($id_c, ) = each($courses)) $course_list[] = (int)str_replace('course_', '', $id_c);

			if(isset($prev_entities[$id_entity])) $re &= $this->model->saveEntityRule($id_rule, $id_entity, $course_list);
			else $re &= $this->model->insertEntityRule($id_rule, $id_entity, $course_list);
		}
		Util::jump_to('index.php?r=alms/enrollrules/show&amp;result='.($re ? 'true' : 'false'));
	}

	protected function applyrule() {
		checkPerm('view', true, 'enrollrules', 'lms');
		$id_rule = Get::req('id_rule', DOTY_INT, 0);
		// Get rule info
		$rule = $this->model->getRule($id_rule);
		$types = $this->model->ruleTypes();
		$rule->rule_type_text = $types[$rule->rule_type];

		// Get user for rule
		if ($rule->rule_type == 'base') {
			// ----------- Applicazione politiche di iscrizione disabilitate regole di tipo BASE -----------
			//require_once(_adm_.'/models/UsermanagementAdm.php');
			//$usermanagement = new UsermanagementAdm();
			//$listUsersBase = $usermanagement->getUsersList(0,  false, array('startIndex' => 0,'results' => 9999999,'sort' => 'u.userid','dir' => 'ASC') );
			//$listUsers = array_keys($listUsersBase);
			//$this->model->newRules('_NEW_USER', $listUsers);
			// ----------- Applicazione politiche di iscrizione disabilitate regole di tipo BASE -----------
		} elseif ($rule->rule_type == 'group') {
			require_once(_adm_.'/models/GroupmanagementAdm.php');
			$groupmanagement = new GroupmanagementAdm();
			// Get list group for rule
			$entities = $this->model->getEntityRule($id_rule);
			$id_entities = array_keys($entities);
			$entities_name = $this->model->convertEntity($id_entities, $rule->rule_type);
			// For any group
			while(list(, $entity) = each($entities)) {
				$listUsers = array();
				$listUsersGroup = array();
				// Get users in group
				$listUsersGroup = $groupmanagement->getGroupUsersList($entity->id_entity, 
							array('startIndex' => 0,'results' => 99999999999999999,'sort' => 'userid','dir' => 'ASC'), 
							false);
				while(list(, $userGroup) = each($listUsersGroup)) {
					$listUsers[] = $userGroup->idst;
				}
				// Apply enroll rule
				$this->model->applyRulesMultiLang('_LOG_USERS_TO_GROUP', $listUsers, false, $entity->id_entity, false, $id_rule);
			} 
		} elseif ($rule->rule_type == 'orgchart') {
			require_once(_adm_.'/models/UsermanagementAdm.php');
			$usermanagement = new UsermanagementAdm();
			// Get list orgchart for rule
			$entities = $this->model->getEntityRule($id_rule);
			$id_entities = array_keys($entities);
			// For any orgchart
			while(list(, $entity) = each($entities)) {
				$listUsers = array();
				$listUsersOrgchart = array();
				$descendants = false;
				$folders = $usermanagement->getInfoFolders(array($entity->id_entity));
				$idOrg = key($folders['id_org']);
				
				// Controllo se ho selezionato discendenti
				$aIndex=0;
				foreach ($folders['idst'] as $kFolders => $vFolders) {
					if ($aIndex == 1){
						if ($kFolders==$entity->id_entity) { $descendants = true; }
					}
					$aIndex++;
				}
				
				// Get users in orgchart
				$listUsersOrgchart = $usermanagement->getUsersList($idOrg, 
							$descendants,
							array('startIndex' => 0,'results' => 9999999,'sort' => 'u.userid','dir' => 'ASC') );
				$listUsers = array_keys($listUsersOrgchart);
				// Apply enroll rule
				$this->model->applyRulesMultiLang('_LOG_USERS_TO_ORGCHART', $listUsers, false, $entity->id_entity, false, $id_rule);
			}
		} elseif ($rule->rule_type == 'fncrole') {
			require_once(_adm_.'/models/FunctionalrolesAdm.php');
			$functionalroles = new FunctionalrolesAdm();
			// Get list fncrole for rule
			$entities = $this->model->getEntityRule($id_rule);
			$id_entities = array_keys($entities);
			$entities_name = $this->model->convertEntity($id_entities, $rule->rule_type);
			// For any fncrole
			while(list(, $entity) = each($entities)) {
				$listUsers = array();
				$listUsersFncrole = array();
				// Get users in fncrole
				$listUsersFncrole = $functionalroles->getManageUsersList($entity->id_entity, 
							array('startIndex' => 0,'results' => 99999999,'sort' => 'userid','dir' => 'ASC'), 
							false);
				while(list(, $userFncrole) = each($listUsersFncrole)) {
					$listUsers[] = $userFncrole->idst;
				}
				// Apply enroll rule
				$this->model->applyRulesMultiLang('_LOG_USERS_TO_FNCROLE', $listUsers, false, $entity->id_entity, false, $id_rule);
			} 
		}
		$result = array(
				'success' => true
#				, 'message' => Lang::t('_OPERATION_FAILURE', 'enrollrules')
		);
		echo $this->json->encode($result);
	}
}
