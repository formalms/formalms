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

define("GROUP_FIELD_NO", "No");
define("GROUP_FIELD_NORMAL", "Normal");
define("GROUP_FIELD_DESCEND", "Descend");
define("GROUP_FIELD_INHERIT", "Inherit");

class UsermanagementAdmController extends AdmController {

	protected $model;
	protected $json;
	protected $numVarFields;
	protected $sessionPrefix;
	protected $permissions;
	protected $reached_max_user_created=false;

	public $link = 'adm/usermanagement';

	public function init() {
		parent::init();
		require_once(_base_.'/lib/lib.json.php');
		$this->model = new UsermanagementAdm();
		$this->json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$this->numVarFields = 3;
		$this->sessionPrefix = 'usermanagement';
		$this->permissions = array(
			'view'					=> checkPerm('view', true, 'usermanagement'),					//view the module
			'view_user'				=> checkPerm('view', true, 'usermanagement'),					//view the users list
			'add_user'				=> checkPerm('add', true, 'usermanagement'),					//create users
			'mod_user'				=> checkPerm('mod', true, 'usermanagement'),					//edit users
			'del_user'				=> checkPerm('del', true, 'usermanagement'),					//remove users
			'approve_waiting_user'	=> checkPerm('approve_waiting_user', true, 'usermanagement'),	//approve waiting users
			'view_org'				=> checkPerm('view', true, 'usermanagement'),					//view orgchart tree
			'add_org'				=> checkPerm('add_org', true, 'usermanagement'),					//create orgchart branches
			'mod_org'				=> checkPerm('mod_org', true, 'usermanagement'),					//edit orgchart branches
			'del_org'				=> checkPerm('del_org', true, 'usermanagement'),					//remove orgchart branches
			'associate_user'		=> checkPerm('associate_user', true, 'usermanagement')
		);


		// Check if the user admin has reached the max number of users he can create
		if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			$admin_pref =new AdminPreference();
			$pref =$admin_pref->getAdminRules(Docebo::user()->getIdSt());
			if ($pref['admin_rules.limit_user_insert'] == 'on') {
				$user_pref =new UserPreferences(Docebo::user()->getIdSt());
				if ($user_pref->getPreference('user_created_count') >= $pref['admin_rules.max_user_insert']) {
					$this->permissions['add_user']=false;
					$this->reached_max_user_created =true;
				}
			}
		}

		$sql = "SELECT * FROM `audittrail_logs_events` WHERE track = 1 ORDER BY id ASC";
		if ($query = sql_query($sql)) {
			while($eventItem = sql_fetch_object($query)) {
				\appCore\Events\DispatcherManager::addListener($eventItem->identifier, function($event) use ($eventItem) {
					// Not register if exists
					$sql = "SELECT COUNT(id) AS count FROM `audittrail_logs` WHERE created_at = NOW() LIMIT 1";
					$query = sql_query($sql);
					$test = sql_fetch_object($query);
					if (!$test->count) {
						$data = json_encode($event->getData());
						if ($user_id = (int)$_SESSION['public_area_idst']) {
						    $sql = "
						    	INSERT INTO `audittrail_logs` (`event_id`, `user_id`, `data`) 
						    	VALUES ({$eventItem->id}, {$user_id}, '{$data}')
							";
						    $query = sql_query($sql);
					    }
				    }
				});
			}
		}
	}

	protected function _setSessionValue($index, $value) {
		$_SESSION[$this->sessionPrefix][$index] = $value;
	}

	protected function _getSessionValue($index, $default = false) {
		if (!isset($_SESSION[$this->sessionPrefix][$index])) $_SESSION[$this->sessionPrefix][$index] = $default;
		return $_SESSION[$this->sessionPrefix][$index];
	}

	protected function _issetSessionValue($index) {
		return (isset($_SESSION[$this->sessionPrefix]) && isset($_SESSION[$this->sessionPrefix][$index]));
	}


	protected function _getErrorMessage($code) {
		$message = "";

		switch ($code) {
			case "no permission":		$message = "You don't have permission to do this."; break;
			case "cannot self delete":	$message = ""; break;
			case "password mismatch":	$message = Lang::t('PASSWRONG', 'register');
			//...
			case "": $message = ""; break;
			default: $message = Lang::t('_OPERATION_FAILURE', 'standard'); break;
		}

		return $message;
	}

	public function show() {
		require_once(_adm_.'/lib/lib.field.php');

		$fman = new FieldList();
		$fields = $fman->getFlatAllFields(array('framework', 'lms'));

		$f_list = array(
			'email'			=> Lang::t('_EMAIL', 'standard'),
			'lastenter'		=> Lang::t('_DATE_LAST_ACCESS', 'profile'),
			'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
			'language' => Lang::t('_LANGUAGE', 'standard'),
			'level' => Lang::t('_LEVEL', 'standard')
		);
		$f_list = $f_list + $fields;
		$f_selected = $this->json->decode(Docebo::user()->getPreference('ui.directory.custom_columns'));
		if ($f_selected == false) {
			$f_selected = array('email', 'lastenter', 'register_date');
			/*$k_list = array_keys($f_list);
			$counter = 0;
			$lastkey = !empty($k_list) ? $k_list[0] : false;
			while (list($key, $value) = each($f_list) && $counter < $this->numVarFields) {
				$f_selected[] = $key;
				$lastkey = $key;
				$counter++;
			}
			if (count($f_selected) < $this->numVarFields) {
				for ($i=0; $i<($this->numVarFields - $counter); $i++) {
					$f_selected[] = $lastkey;
				}
			}*/
		}

		$js_arr = array();
		foreach ($f_list as $key=>$value) $js_arr[] = $key.': '.$this->json->encode($value);
		$f_list_js = '{'.implode(',', $js_arr).'}';

		if ($this->permissions['add_user'] == false && $this->reached_max_user_created) {
			$message =getInfoUi(Lang::t('_USER_CREATION_LIMIT_REACHED', 'admin_directory'));
		}

		$res = Get::req('res', DOTY_STRING, '');
		switch ($res) {
			case 'ok_assignuser': $message = getResultUi(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
			case 'err_assignuser': $message = getErrorUi(Lang::t('_GROUP_USERASSIGN_ERROR', 'admin_directory')); break;
			case 'no_file': $message = getErrorUi(Lang::t('_NO_FILE', 'user_managment')); break;
			case 'need_to_alert': $message = getErrorUi(Lang::t('_NEED_TO_ALERT', 'user_managment')); break;
			case 'userid_needed': $message = getErrorUi(Lang::t('_USERID_NEEDED', 'user_managment')); break;
			case 'field_repeated': $message = getErrorUi(Lang::t('_FIELD_REPEATED', 'user_managment')); break;

			case 'err_alreadyassigned': {
				$countassigned = Get::req('count', DOTY_STRING, '');
				$id_first = Get::req('id_first', DOTY_STRING, '');
				$profile_user = $this->model->getProfileData($id_first);

				if($countassigned == 1) {
					$message = getErrorUi(Lang::t('_USER').' '.$profile_user->firstname.' '.$profile_user->lastname.' '.Lang::t('_ALREADY_ASSIGNED', 'admin_directory'));
				} else {
					$message = getErrorUi($countassigned.' '.Lang::t('_USERS_ALREADY_ASSIGNED', 'admin_directory').' ('.$profile_user->firstname.' '.$profile_user->lastname.'...)');
				}
				break;
			}
			default: $message = "";
		}

		$root_node_actions = $this->_getNodeActions(0);

		require_once(_adm_.'/lib/user_selector/lib.dynamicuserfilter.php');
		$dyn_filter = new DynamicUserFilter("user_dyn_filter");
		$dyn_filter->init();

		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('adm').'/views/usermanagement/usermanagement.js', true, true);

		if (!$this->_issetSessionValue('selected_node') && Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			//select the first folder of the sub admin
			$this->_setSessionValue('selected_node', $this->model->getAdminFolder(Docebo::user()->getIdst(), true));
		}
		$selected_orgchart = $this->_getSessionValue('selected_node', 0);

		$this->render('show', array(
			'permissions' => $this->permissions,
			'num_var_fields' => $this->numVarFields,
			'fieldlist' => $f_list,
			'fieldlist_js' => $f_list_js,
			'selected' => $f_selected,
			'selected_orgchart' => $selected_orgchart,//$this->_getSelectedNode(),
			'root_node_actions' => $root_node_actions,
			'show_descendants' => $this->_getSessionValue('show_descendants', false),//$this->_getDescendantsFilter(),
			'show_suspended' => $this->_getSessionValue('show_suspended', true),//$this-> _getSuspendedFilter(),
			'filter_text' => $this->_getSessionValue('text_filter', ""),//$this->_getTextFilter(),
			'result_message' => $message,
			'dynamic_filter' => $dyn_filter,
			'num_waiting_users' => $this->model->getWaitingUsersTotal(),
			'num_deleted_users' => $this->model->getDeletedUsersTotal()
		));
	}

	protected function _getDynamicFilter($input) {
		$output = false;
		if (is_string($input) && $input != "") {
			$dyn_data = $this->json->decode(urldecode(stripslashes($input))); //decode the filter json string
			if (isset($dyn_data['exclusive']) && isset($dyn_data['filters'])) //required fields
				if (count($dyn_data['filters']) > 0) //there must be any filter selected
					$output = $dyn_data;
		}
		return $output;
	}

	public function gettabledata() {
		//check permissions
		if (!$this->permissions['view_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall": {
				$this->selectall();
				return;
			} break;
		}

		$idOrg = Get::req('id_org', DOTY_INT, 0);
		$descendants = (Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem'));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");

		$var_fields = Get::req('_dyn_field', DOTY_MIXED, array());
		if (stristr($sort, '_dyn_field_') !== false) {
			$index = str_replace('_dyn_field_', '', $sort);
			$sort = $var_fields[(int)$index];
		}

		$filter_text = Get::req('filter_text', DOTY_STRING, '');

		$searchFilter = array(
			'text' => $filter_text,
			'suspended' => (Get::req('suspended', DOTY_INT, 1)>0 ? true : false)
		);

		$dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}

		$total = $this->model->getTotalUsers($idOrg, $descendants, $searchFilter, true);
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

		$list = $this->model->getUsersList($idOrg, $descendants, $pagination, $searchFilter, true);

		//prepare the data for sending
		$acl_man = Docebo::user()->getAclManager();

		require_once(_adm_.'/lib/lib.field.php');
		$fman = new FieldList();
		$date_fields = $fman->getFieldsByType("date");

		$users = $this->model->getAllUsers($idOrg, $descendants, $searchFilter, true);

		$user_entry_data = $fman->getUsersFieldEntryData($users);

		$output_results = array();
		if (is_array($list) && count($list)>0) {
			$current_user = Docebo::user()->getIdSt();
			foreach ($list as $idst=>$record) {
				$record_row = array(
					'id'		=> (int)$record['idst'],
					'userid'	=> Layout::highlight($acl_man->relativeId( $record['userid']), $filter_text),
					'firstname' => Layout::highlight($record['firstname'], $filter_text),
					'lastname'	=> Layout::highlight($record['lastname'], $filter_text),
					'email'		=> Layout::highlight($record['email'], $filter_text),
					'register_date' => Format::date($record['register_date'], "datetime"),
					'lastenter' => Format::date($record['lastenter'], "datetime"),
					'unassoc'	=> $idOrg > 0 ? (!empty($record['is_descendant']) ? 0 : 1) : -1,
					'valid'		=> (int)$record['valid'] > 0 ? 1 : 0,
					'mod'		=> 'ajax.adm_server.php?r='.$this->link.'/moduser&id='.(int)$idst,
					'del'		=> ($idst != $current_user) ? 'ajax.adm_server.php?r='.$this->link.'/deluser&id='.(int)$idst : false,
				);

				foreach ($var_fields as $i=>$value) {
					if (is_numeric($value)) {
						$name = '_custom_'.$value;
					} else {
						$name = $value;
					}

					//check if we must perform some post-format on retrieved field values
					$content = (isset($record[$name]) ? $record[$name] : '');
					if ($name == 'register_date') $content = Format::date($content, 'datetime');
					if ($name == 'lastenter') $content = Format::date($content, 'datetime');
					if ($name == 'level' && $content != '') $content = Lang::t('_DIRECTORY_'.$content, 'admin_directory');
					if (!empty($date_fields) && in_array($value, $date_fields)) $content = Format::date(substr($content, 0, 10), 'date');
					if ($name == '_custom_'.$value) $content = $user_entry_data[(int)$record['idst']][$value];
					$record_row['_dyn_field_'.$i] = $content;
				}

				$output_results[] = $record_row;
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

		$event = new appCore\Events\Core\User\UsersManagementShowEvent;
		$event->setUsers($output['records']);
		\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementShowEvent::EVENT_NAME, $event);
		$output['records'] = $event->getUsers();

		echo $this->json->encode($output);
	}

	protected function _getUserEditMask($idst = false) {
		require_once(_adm_.'/lib/lib.field.php');
		require_once(_base_.'/lib/lib.platform.php');

		$mask = "";
		$model = new UsermanagementAdm();
		$acl_man = Docebo::user()->getAclManager();
		$level = ADMIN_GROUP_USER;

		$is_editing = false;
		if (is_numeric($idst) && $idst>0) {
			//set form for editing and not for creating
			$is_editing = true;
			$form_id = 'edit_user_form';
			$form_url = 'ajax.adm_server.php?r='.$this->link.'/moduser_action';
			$user_info = $acl_man->getUser($idst, false);

			$info_userid = $acl_man->relativeId($user_info[ACL_INFO_USERID]);
			$info_firstname = $user_info[ACL_INFO_FIRSTNAME];
			$info_lastname = $user_info[ACL_INFO_LASTNAME];
			$info_email = $user_info[ACL_INFO_EMAIL];

			$info_facebook_id = $user_info[ACL_INFO_FACEBOOK_ID];
			$info_twitter_id = $user_info[ACL_INFO_TWITTER_ID];
			$info_linkedin_id = $user_info[ACL_INFO_LINKEDIN_ID];
			$info_google_id = $user_info[ACL_INFO_GOOGLE_ID];

			$force_change = $user_info[ACL_INFO_FORCE_CHANGE];

			$level = $acl_man->getUserLevelId($idst);

		} else {
			$form_id = 'create_user_form';
			$form_url = 'ajax.adm_server.php?r='.$this->link.'/createuser_action';
			$info_userid = $info_firstname = $info_lastname = $info_email = "";
			$info_facebook_id = $info_twitter_id = $info_linkedin_id = $info_google_id = "";
		}

		$arr_levels = $acl_man->getAdminLevels();//index = idst; value = groupid;
		$levels = array();
		foreach ($arr_levels as $groupid_level=>$idst_level) {
			if ($this->_canUseLevel($groupid_level))
				$levels[ $groupid_level ] = Lang::t('_DIRECTORY_'.$groupid_level, 'admin_directory');
		}

		$language = getDefaultLanguage();
		$languages = Docebo::langManager()->getAllLanguages();

		$pman =& PlatformManager::createInstance();// = new PlatformManager();
		$platforms = $pman->getPlatformList();
		$fman = new FieldList();

		$preference = new UserPreferences($is_editing ? $idst : 0);
		$modify_mask = $preference->getModifyMask('ui.');

		$arr_idst = false;
		if(isset($_SESSION['usermanagement']['selected_node']) && $_SESSION['usermanagement']['selected_node'] != 0 && !$is_editing)
		{
			$arr_idst = array();
			$tmp = $acl_man->getGroup(false, '/oc_'.$_SESSION['usermanagement']['selected_node']);
			$arr_idst[] = $tmp[0];
			$tmp = $acl_man->getGroup(false, '/ocd_'.$_SESSION['usermanagement']['selected_node']);
			$arr_idst[] = $tmp[0];
			$acl =& Docebo::user()->getACL();
			$arr_idst = $acl->getArrSTGroupsST($arr_idst);
		}

		$fields_mask = $fman->playFieldsForUser($is_editing ? $idst : -1, $arr_idst, false, true, false, false, !$is_editing ? Docebo::user()->getIdst() : false);

		$info = new stdClass();
		$info->userid = trim($info_userid);
		$info->firstname = trim($info_firstname);
		$info->lastname = trim($info_lastname);
		$info->email = trim($info_email);
		$info->facebook_id = trim($info_facebook_id);
		$info->twitter_id = trim($info_twitter_id);
		$info->linkedin_id = trim($info_linkedin_id);
		$info->google_id = trim($info_google_id);
		$info->force_change = isset($force_change) ? $force_change : false;
		$info->level = $level;

		$this->render('editmask', array(
			'idst' => $idst,
			'is_editing' => $is_editing,
			'form_id' => $form_id,
			'form_url' => $form_url,
			'info' => $info,
			'levels' => $levels,
			'modify_mask' => $modify_mask,
			'fields_mask' => $fields_mask
		));

		$mask = ob_get_clean();
		@ob_start();
		return $mask;
	}

	function create() {
		//check permissions
		if (!$this->permissions['add_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array(
			'success' => true,
			'header' => Lang::t('_NEW_USER', 'admin_directory'),
			'body' => $this->_getUserEditMask()
		);
		if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) $output['__date_inputs'] = $GLOBALS['date_inputs'];
		echo $this->json->encode($output);
	}


	protected function echoResult($output) {

		if (Util::getIsAjaxRequest()) {
			if ($output['success']) {
				$output['message'] = UIFeedback::pinfo($output['message']);
			}
			else {
				$output['message'] = UIFeedback::perror($output['message']);
			}
		}
		else { // We're in a POST call due to YUI upload via iframe..
			if ($output['success']) {
				$output['feedback_type']='info';
			}
			else {
				$output['feedback_type']='notice';
			}
		}

		echo $this->json->encode($output);
	}


	function createuser_action() {
		//check permissions
		if (!$this->permissions['add_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->echoResult($output);
			return;
		}

		$userid = Get::req('username', DOTY_STRING, '');
		$password = Get::Req('password', DOTY_STRING, '');
		$password_confirm = Get::req('password_confirm', DOTY_STRING, '');

		$output = array();

		if ($userid == '') {
			$output['success'] = false;
			$output['message'] = Lang::t('_ERR_INVALID_USER', 'register');
			echo $this->echoResult($output);
			return;
		}

		if ($password == "") {
			$output['success'] = false;
			$output['message'] = Lang::t('_ERR_PASSWORD_MIN_LENGTH', 'register');
			echo $this->echoResult($output);
			return;
		}

		if ($password != $password_confirm) {
			$output['success'] = false;
			$output['message'] = Lang::t('_ERR_PASSWORD_NO_MATCH', 'register');
			echo $this->echoResult($output);
			return;
		}

		$userdata = new stdClass();
		$userdata->userid = trim($userid);
		$userdata->firstname = trim(Get::req('firstname', DOTY_STRING, ''));
		$userdata->lastname = trim(Get::req('lastname', DOTY_STRING, ''));
		$userdata->email = trim(Get::req('email', DOTY_STRING, ''));
		$userdata->password = $password;
		$userdata->force_change = trim(Get::Req('force_changepwd', DOTY_INT, 0));
		/* $userdata->facebook_id = Get::pReq('facebook_id', DOTY_STRING, '');
		$userdata->twitter_id = Get::pReq('twitter_id', DOTY_STRING, '');
		$userdata->linkedin_id = Get::pReq('linkedin_id', DOTY_STRING, '');
		$userdata->google_id = Get::pReq('google_id', DOTY_STRING, ''); */
		if (Docebo::user()->user_level == ADMIN_GROUP_GODADMIN) {
			$userdata->level = Get::req('level', DOTY_STRING, ADMIN_GROUP_USER);
		}
		else {
			$userdata->level = ADMIN_GROUP_USER;
		}

		if (!$this->model->checkUserid($userdata->userid)) {
			$output['success'] = false;
			$output['message'] = Lang::t('_USERID_DUPLICATE', 'organization_chart');
			echo $this->echoResult($output);
			return;
		}

		if (!$this->_canUseLevel($userdata->level)) {
			$output['success'] = false;
			$output['message'] = Lang::t('_OPERATION_FAILURE', 'standard');
			echo $this->echoResult($output);
			return;
		}

		if (Get::sett('pass_change_first_login', 'off') == 'on') {
			$userdata->force_change = 1;
		}

		$userdata->preferences =& $_POST;


		$folders = Get::req('sel', DOTY_MIXED, false);

		$idst = $this->model->createUser($userdata, $folders);
		if (is_numeric($idst) && $idst>0) {
			$output['success'] = true;
			$output['idst'] = $idst;
			$output['total_users'] = $this->model->getUsersCount();
			$output['message'] = Lang::t('_OPERATION_SUCCESSFUL', 'standard').': '.$userid;


			// Send alert:
			require_once(_base_.'/lib/lib.eventmanager.php');
			$acl_man = Docebo::user()->getAclManager();

			$array_subst = array(
				'[url]' => Get::sett('url'),
				'[userid]' => $userid,
				'[password]' => $password
			);

			$e_msg = new EventMessageComposer();

			$e_msg->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
			$e_msg->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst );

			$e_msg->setBodyLangText('sms', '_REGISTERED_USER_TEXT_SMS', $array_subst );

			$recipients = array($idst);

			if(!empty($recipients)) {
				createNewAlert(	'UserNew', 'directory', 'edit', '1', 'New user created',
					$recipients, $e_msg  );
				ob_clean();
			}


			// Increment the counter for users created by this admin:
			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				$admin_pref =new AdminPreference();
				$pref =$admin_pref->getAdminRules(Docebo::user()->getIdSt());
				if ($pref['admin_rules.limit_user_insert'] == 'on') {
					$user_pref =new UserPreferences(Docebo::user()->getIdSt());
					$user_created_count =(int)$user_pref->getPreference('user_created_count');
					$user_created_count++;
					$user_pref->setPreference('user_created_count', $user_created_count);
					if ($user_created_count >= $pref['admin_rules.max_user_insert']) {
						$output['force_page_refresh'] =true;
						$output['message'] =Lang::t('_USER_CREATED_MAX_REACHED', 'admin_directory');
					}
				}
			}

		} else {
			$output['success'] = false;
			$output['message'] = $idst;
		}

		$this->echoResult($output);
	}

	function moduser() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$idst = Get::req('id', DOTY_INT, -1);
		if ($idst > 0) {
			$output = array(
				'success' => true,
				'header' => Lang::t('_MOD', 'admin_directory'),
				'body' => $this->_getUserEditMask($idst)
			);
			if (isset($GLOBALS['date_inputs']) && !empty($GLOBALS['date_inputs'])) $output['__date_inputs'] = $GLOBALS['date_inputs'];
		} else {
			$output = array('success' => false, 'message' => 'invalid user id');
		}
		echo $this->json->encode($output);
	}

	function moduser_action() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$idst = Get::req('idst', DOTY_INT, -1);
		if ($idst <= 0) {
			echo $this->json->encode(array('success'=>false, 'message'=>'invalid user id'));
			return;
		}

		$userid = Get::req('username', DOTY_STRING, '');
		$new_password = Get::Req('new_password', DOTY_STRING, '');
		$new_password_confirm = Get::req('new_password_confirm', DOTY_STRING, '');

		$output = array();
		$check_pwd = true;
		if ($new_password != "") {
			if ($new_password != $new_password_confirm)
				$check_pwd = false;
		} else {
			$new_password = false;
		}

		if (!$check_pwd) {
			echo $this->json->encode(array('success'=>false, 'message'=>'invalid password'));
			return;
		}

		$userdata = new stdClass();
		$userdata->userid = $userid;
		$userdata->firstname = Get::req('firstname', DOTY_STRING, '');
		$userdata->lastname = Get::req('lastname', DOTY_STRING, '');
		$userdata->email = Get::req('email', DOTY_STRING, '');
		if ($check_pwd && !Get::cfg('demo_mode')) $userdata->password = $new_password;
		if (Docebo::user()->user_level == ADMIN_GROUP_GODADMIN) {
			$userdata->level = Get::req('level', DOTY_STRING, ADMIN_GROUP_USER);
		}
		else {
			$userdata->level = false;
		}
		$userdata->force_change = Get::req('force_changepwd', DOTY_INT, 0);

		$userdata->preferences =& $_POST; //Get::req('user_preferences', DOTY_MIXED, array());

		$res = $this->model->editUser($idst, $userdata);
		if ($res === true) {
			$output['success'] = true;
		} else {
			$output['success'] = false;
			$output['message'] = $res;
		}

		$model = new UsermanagementAdm();
		$oldUserdata = $model->getProfileData($idst);

		// SET EDIT USER SINGLE EVENT
		$event = new \appCore\Events\Core\User\UsersManagementEditEvent();
		$event->setType('single');
		$event->setUser($userdata);
		$event->setOldUser($oldUserdata);
		\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementEditEvent::EVENT_NAME, $event);

		echo $this->json->encode($output);
	}

	function deluser() {
		//check permissions
		if (!$this->permissions['del_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		if(Get::cfg('demo_mode'))
			die('Cannot del user during demo mode.');

		$acl_man = Docebo::user()->getAclManager();
		$id_user = Get::req('id', DOTY_INT, -1);
		if ($id_user>0) {

			if ($id_user == Docebo::user()->getIdSt()) {
				$output = array('success' => false, 'message' => $this->_getErrorMessage('cannot self delete'));
				echo $this->json->encode($output);
				return;
			}

			$output = array();

			$model = new UsermanagementAdm();
			$user = $model->getProfileData($id_user);

			if ($acl_man->deleteUser($id_user)) {
				$output = array('success'=>true);
				if (Get::sett('register_deleted_user', "off") == "on")
					$output['total_deleted_users'] = $this->model->getDeletedUsersTotal();

				// Increment the counter for users created by this admin:
				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$admin_pref =new AdminPreference();
					$pref =$admin_pref->getAdminRules(Docebo::user()->getIdSt());
					if ($pref['admin_rules.limit_user_insert'] == 'on') {
						$user_pref =new UserPreferences(Docebo::user()->getIdSt());
						$user_created_count =(int)$user_pref->getPreference('user_created_count');
						$user_created_count = $user_created_count-1;
						$user_pref->setPreference('user_created_count', $user_created_count);
					}
				}

				// SET DELETE USER EVENT
				$event = new \appCore\Events\Core\User\UsersManagementDeleteEvent();
				$event->setUser($user);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementDeleteEvent::EVENT_NAME, $event);
			} else {
				$output = array('success'=>false, 'message'=>'Error: unable to delete user #'.$id_user.'.');
			}
		} else {
			$output = array('success'=>false, 'message'=>'invalid input');
		}
		echo $this->json->encode($output);
	}

	function delmultiuser() {
		//check permissions
		if (!$this->permissions['del_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$acl_man = Docebo::user()->getAclManager();
		$output = array();
		$users = Get::req('users', DOTY_STRING, '');
		if ($users != '') {
			//eliminates current user idst from list
			$users = str_replace(Docebo::user()->getIdSt(), '', $users);
			$users = str_replace(',,', ',', $users); //adjust commas
			$users_arr = explode(',', $users);
			$count_users = count($users_arr);

			$model = new UsermanagementAdm();
			$users = [];
			foreach ($users_arr as $idst) {
				if ($model->getProfileData($idst)) {
					$users[] = $model->getProfileData($idst);
				}
			}

			$res = $this->model->deleteUsers($users_arr);

			// SET DELETE USER MULTIPLE EVENT
			$event = new \appCore\Events\Core\User\UsersManagementDeleteEvent();
			$event->setUsers($users);
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementDeleteEvent::EVENT_NAME, $event);

			if (is_array($res)) {
				$output['success'] = true;
				$output['deleted'] = count($res);
				$output['list'] = $res;
				if (Get::sett('register_deleted_user', "off") == "on")
					$output['total_deleted_users'] = $this->model->getDeletedUsersTotal();

				// Increment the counter for users created by this admin:
				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$admin_pref =new AdminPreference();
					$pref =$admin_pref->getAdminRules(Docebo::user()->getIdSt());
					if ($pref['admin_rules.limit_user_insert'] == 'on') {
						$user_pref =new UserPreferences(Docebo::user()->getIdSt());
						$user_created_count =(int)$user_pref->getPreference('user_created_count');
						$user_created_count = $user_created_count-$count_users;
						$user_pref->setPreference('user_created_count', $user_created_count);
					}
				}

			} else {
				$output['success'] = false;
				$output['message'] = 'error while deleting users';
			}
		} else {
			$output['success'] = false;
			$output['message'] = 'invalid users specification';
		}
		echo $this->json->encode($output);
	}

	function suspend() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$idst = Get::req('id', DOTY_INT, -1);
		$output = array();
		$action = Get::req('action', DOTY_INT, -1);

		if ($idst>0 && ($action==0 || $action==1)) {
			$model = new UsermanagementAdm();
			$user = $model->getProfileData($idst);

			if ($action==0) {
				$output['success'] = $this->model->suspendUsers($idst);
				$output['message'] = UIFeedback::pinfo(Lang::t('_OPERATION_SUCCESSFUL', 'standard'));

				// SET SUSPAND USER EVENT
				$event = new \appCore\Events\Core\User\UsersManagementSuspendEvent();
				$event->setUser($user);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementSuspendEvent::EVENT_NAME, $event);
			}
			else {
				$output['success'] = $this->model->unsuspendUsers($idst);
				$output['message'] = UIFeedback::pinfo(Lang::t('_OPERATION_SUCCESSFUL', 'standard'));

				// SET UNSUSPAND USER EVENT
				$event = new \appCore\Events\Core\User\UsersManagementUnsuspendEvent();
				$event->setUser($user);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementUnsuspendEvent::EVENT_NAME, $event);
			}
		} else {
			$output['success'] = false;
			$output['message'] = Lang::t('_INVALID_USER', 'admin_directory');
		}
		echo $this->json->encode($output);
	}

	function multisuspend() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$users = Get::req('users', DOTY_STRING, "");
		$output = array();
		$action = Get::req('action', DOTY_INT, -1);
		if ($users!="" && ($action==0 || $action==1)) {
			$arr_users = explode(',', $users);

			$model = new UsermanagementAdm();
			$users = [];
			foreach ($arr_users as $idst) {
				$users[] = $model->getProfileData($idst);
			}

			if ($action==0) {
				$output['success'] = $this->model->suspendUsers($arr_users);

				// SET SUSPAND USERS MULTIPLE EVENT
				$event = new \appCore\Events\Core\User\UsersManagementSuspendEvent();
				$event->setUsers($users);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementSuspendEvent::EVENT_NAME, $event);
			}
			else {
				$output['success'] = $this->model->unsuspendUsers($arr_users);

				// SET UNSUSPAND USERS MULTIPLE EVENT
				$event = new \appCore\Events\Core\User\UsersManagementUnsuspendEvent();
				$event->setUsers($users);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementUnsuspendEvent::EVENT_NAME, $event);
			}
		} else {
			$output['success'] = false;
			$output['message'] = Lang::t('_EMPTY_SELECTION', 'admin_directory');
		}
		echo $this->json->encode($output);
	}

	function multigenpwd() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$users = Get::req('users', DOTY_STRING, "");
		$output = array();
		if ($users!="") {
			$arr_users = explode(',', $users);
			$output['success'] = true;
			foreach ($arr_users AS $user){
				if(!$this->model->randomPassword($user)){
					$output['success'] = false;
				}
			}
			if (!$output['success']){
				$output['message'] = Lang::t('_OPERATION_FAILURE', 'standard');
			}
		} else {
			$output['success'] = false;
			$output['message'] = Lang::t('_EMPTY_SELECTION', 'admin_directory');
		}
		echo $this->json->encode($output);
	}

	function selectall() {
		$idOrg = Get::req('id_org', DOTY_INT, 0);
		$descendants = (Get::req('descendants', DOTY_INT, 0) > 0 ? true : false);
		$filter_text = Get::req('filter_text', DOTY_STRING, '');
		$searchFilter = array(
			'text' => $filter_text,
			'suspended' => (Get::req('suspended', DOTY_INT, 1)>0 ? true : false)
		);
		$dyn_filter = $this->_getDynamicFilter(Get::req('dyn_filter', DOTY_STRING, ''));
		if ($dyn_filter !== false) {
			$searchFilter['dyn_filter'] = $dyn_filter;
		}
		$output = $this->model->getAllUsers($idOrg, $descendants, $searchFilter, true);
		echo $this->json->encode($output);
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

	public function gettreedata() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {

			case "expand": {
				//check permissions
				if (!$this->permissions['view_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$idOrg = Get::req('node_id', DOTY_INT, -1);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

				if ($initial) {
					//get selected node from session and set the expanded tree
					$idOrg = $this->_getSessionValue('selected_node', 0);//$this->_getSelectedNode();
					$nodes = $this->model->getOrgChartInitialNodes($idOrg, true);
					//create actions for every node
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
					$nodes = $this->model->getOrgChartNodes($idOrg, false, false, true);
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
				$idOrg = Get::req('node_id', DOTY_INT, -1);
				$this->_setSessionValue('selected_node', $idOrg);//_setSelectedNode($idOrg);
			} break;

			case "delete": {
				//check permissions
				if (!$this->permissions['del_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$this->delfolder();
			} break;

			case "getmodform": {
				//check permissions
				if (!$this->permissions['mod_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$output = array();
				$id = Get::req('node_id', DOTY_INT, -1);
				if ($id < 0) {
					$output = array(
						'success' => false,
						'message' => Lang::t('_INVALID_INPUT')
					);
				} else {
					if ($id == 0) {
						$root_name = Get::sett('title_organigram_chart', Lang::t('_ORG_CHART', 'organization_chart'));
						$body = Form::openForm('modfolder_form', "ajax.adm_server.php?r=".$this->link."/modrootfolder")
							.'<p id="addfolder_error_message"></p>'
							.Form::getTextfield(Lang::t('_ROOT_RENAME', 'organization_chart'), 'modfolder_root', 'modfolder_root', 50, $root_name)
							.Form::closeForm();
					} else {
						$folder_info = $this->model->getFolderById($id);
						$languages = Docebo::langManager()->getAllLanguages(true);//getAllLangCode();
						$std_lang = getLanguage();

						$template =(!empty($folder_info->associated_template) ? $folder_info->associated_template : getDefaultTemplate());
						$template_arr =getTemplateList();
						$template_tmp_arr =array_flip($template_arr);
						$template_id =$template_tmp_arr[$template];
						unset($template_tmp_arr);

						$form_content = Form::getHidden('modfolder_id', 'node_id', $id);
						$form_content .= Form::getTextfield(Lang::t('_CODE', 'organization_chart'), 'org_code', 'org_code', 50, $folder_info->code);
						$form_content .= Form::getDropdown(Lang::t('_DEFAULTTEMPLATE', 'configuration'), 'associated_template', 'associated_template', $template_arr, $template_id);
						$form_content .= Form::getBreakRow();

						$translations = $this->model->getFolderTranslations($id, true);
						foreach ($languages as $language) {
							$lang_code = $language['code'];
							$lang_name = $language['description'];
							$translation = (isset($translations[$lang_code]) ? $translations[$lang_code] : "");
							$form_content .= Form::getTextfield($lang_name, 'modfolder_'.$lang_code, 'modfolder['.$lang_code.']', 255, $translation);
						}
						$body = Form::openForm('modfolder_form', "ajax.adm_server.php?r=".$this->link."/modfolder")
							.'<p id="addfolder_error_message"></p>'
							.$form_content
							.Form::closeForm();
					}

					$output = array(
						'success' => true,
						'body' => $body
					);
				}

				echo $this->json->encode($output);
			} break;

			case "assignfields": {
				//check permissions
				if (!$this->permissions['mod_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$this->assignfields();
			} break;

			case "options": {
				//check permissions
				if (!$this->permissions['view_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$id = Get::req('node_id', DOTY_INT, -1);
				$output = array();
				if ($id <= 0) {
					$output['success'] = false;
				} else {
					$output['success'] = true;
					$output['options'] = $this->_getNodeActions($id);
				}
				echo $this->json->encode($output);
			} break;

			case "movefolder": {
				//check permissions
				if (!$this->permissions['mod_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$src_folder = Get::req('src', DOTY_INT, -1);
				$dest_folder = Get::req('dest', DOTY_INT, -1);

				$output = array();
				if ($this->model->moveFolder($src_folder, $dest_folder)) {
					$output['success'] = true;
				} else {
					$output['success'] = false;
				}
				echo $this->json->encode($output);
			} break;

			default: {
				$output = array();
				$output['success'] = false;
				echo $this->json->encode($output);
			}
		} // end switch

	}

	protected function _getNodeActions($node) {
		if (is_numeric($node)) { //if we have the id of the node, extract data
			require_once(_base_.'/lib/lib.json.php');
			$model = new UsermanagementAdm();
			$nodedata = $model->getFolderById($node);
			$node = array(
				'id' => $nodedata->idOrg,
				'label' => $model->getFolderTranslation($nodedata->idOrg, getLanguage()),
				'is_leaf' => (($nodedata->iRight-$nodedata->iLeft) == 1),
				'count_content' => (int)(($nodedata->iRight-$nodedata->iLeft-1)/2)
			);
		}
		if (!is_array($node)) return false; //unrecognized type for node data
		$actions = array();
		$id_action = $node['id'];
		if (!$this->model->isFolderEnabled($id_action)) return false;

		$is_root = ($id_action == 0);

		//assign users to folder action
		if ($this->permissions['associate_user']) {
			if (!$is_root) {
				$actions[] = array(
					'id' => 'moduser_'.$id_action,
					'command' => 'moduser',
					'icon' => 'standard/moduser.png',
					'href' => 'index.php?r='.$this->link.'/assignuser&id='.$id_action,
					'alt' => Lang::t('_ASSIGN_USERS', 'organization_chart')
				);
			} else {
				$actions[] = array(
					'id' => 'moduser_'.$id_action,
					'command' => false,
					'icon' => 'blank.png'
				);
			}
		}

		//assign custom fields action
		if ($this->permissions['mod_org']) {
			$actions[] = array(
				'id' => 'assignfields_'.$id_action,
				'command' => 'assignfields',
				'icon' => 'standard/database.png',
				'alt' => Lang::t('_ASSIGNED_EXTRAFIELD', 'organization_chart')
			);
		}

		//rename action
		if ($this->permissions['mod_org']) {
			$actions[] = array(
				'id' => 'mod_'.$id_action,
				'command' => 'modify',
				'icon' => 'standard/edit.png',
				'alt' => Lang::t('_MOD', 'standard')
			);
		}

		//delete action
		if ($this->permissions['del_org']) {
			if ($node['is_leaf'] && !$is_root) {
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

		//add action
		if ($this->permissions['add_org']) {
			$actions[] = array(
				'id' => 'add_'.$id_action,
				'command' => 'add',
				'icon' => 'blank.png',
				'alt' => ''
			);
		}

		return $actions;
	}

	public function addfolder_dialog() {
		//check permissions
		if (!$this->permissions['add_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_parent = Get::req('id', DOTY_INT, 0);
		if ($id_parent < 0) $id_parent = 0;

		$template =getDefaultTemplate();
		$template_arr =getTemplateList();
		$template_tmp_arr =array_flip($template_arr);
		$template_id =$template_tmp_arr[$template];
		unset($template_tmp_arr);

		$this->render('add_folder', array(
			'id_parent' => $id_parent,
			'title' => Lang::t('_ORGCHART_ADDNODE', 'organization_chart'),
			'json' => $this->json,
			'default_template'=>$template_id,
		));
	}

	function createfolder() {
		//check permissions
		if (!$this->permissions['add_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$code = Get::req('org_code', DOTY_STRING, '');
		$langs = Get::req('langs', DOTY_MIXED, false);
		if ($langs == false) {
			$output['success'] = false;
			$output['message'] = Lang::t('_INVALID_INPUT');
		} else {
			$id_parent = Get::req('id_parent', DOTY_INT, -1);
			if ($id_parent < 0) $id_parent = 0;
			$id = $this->model->addFolder($id_parent, $langs, $code);
			if ($id > 0) {
				$output['success'] = true;
				$nodedata = array(
					'id' => $id,
					'label' => ($code != "" ? '['.$code.'] ' : '').$this->model->getFolderTranslation($id, getLanguage()),
					'is_leaf' => true,
					'count_content' => 0
				);
				$nodedata['options'] = $this->_getNodeActions($nodedata);
				$output['node'] = $nodedata;
				$output['id_parent'] = $id_parent;

				$event = new \appCore\Events\Core\User\UsersManagementOrgChartCreateNodeEvent();			
				$event->setNode($nodedata);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartCreateNodeEvent::EVENT_NAME, $event);

			} else {
				$output['success'] = false;
				$output['message'] = Lang::t('_CONNECTION_ERROR');
			}
		}

		echo $this->json->encode($output);
	}

	function delfolder() {
		//check permissions
		if (!$this->permissions['del_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array('success' => false);
		$id = Get::req('node_id', DOTY_INT, -1);

		if ($id > 0) {
			$event = new \appCore\Events\Core\User\UsersManagementOrgChartDeleteNodeEvent();			
			$event->setNode($this->model->getFolderById($id));
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartDeleteNodeEvent::EVENT_NAME, $event);

			$output['success'] = $this->model->deleteFolder($id, true);
		}
		echo $this->json->encode($output);
	}

	protected function _formatFolderCode($id, $code) {
		if (!$code || $id <= 0) return "";
		return '<span id="orgchart_code_'.(int)$id.'">['.$code.']&nbsp;</span>';
	}

	public function modfolder() {
		//check permissions
		if (!$this->permissions['mod_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$id = Get::req('node_id', DOTY_INT, -1);
		$code = Get::req('org_code', DOTY_STRING, '');
		$template_id = Get::req('associated_template', DOTY_INT, '');
		$template_arr =getTemplateList();
		$langs = Get::req('modfolder', DOTY_MIXED, false);
		$old_node = $this->model->getFolderById($id);
		$res = $this->model->modFolderCodeAndTemplate($id, $code, $template_arr[$template_id]);
		$res = $this->model->renameFolder($id, $langs);
		if ($res) {
			$output['success'] = true;
			//$output['new_name'] = ($code != "" ? '['.$code.'] ' : '').$langs[getLanguage()];
			$output['new_name'] = $this->_formatFolderCode($id, $code).$langs[getLanguage()];

			$event = new \appCore\Events\Core\User\UsersManagementOrgChartEditNodeEvent();			
			$event->setOldNode($old_node);
			$event->setNode($this->model->getFolderById($id));
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartEditNodeEvent::EVENT_NAME, $event);
		} else {
			$output['success'] = false;
			$output['message'] = Lang::t('_CONNECTION_ERROR');
		}
		echo $this->json->encode($output);
	}

	public function modrootfolder() {
		//check permissions
		if (!$this->permissions['mod_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = array();
		$root_name = Get::req('modfolder_root', DOTY_STRING, "");
		$res = $this->model->renameRootFolder($root_name);
		if ($res) {
			$output['success'] = true;
			$output['new_name'] = $root_name;
		} else {
			$output['success'] = false;
			$output['message'] = $this->_getErrorMessage("mod folder");
		}
		echo $this->json->encode($output);
	}

	function assignuser() {
		$id = Get::req('id', DOTY_INT, -1);

		$base_url = 'index.php?r='.$this->link.'/';
		$back_url = $base_url.'show';
		$jump_url = $base_url.'assignuser';
		$next_url = $base_url.'show';

		//check permissions
		if (!$this->permissions['associate_user']) Util::jump_to($back_url);

		if ($id > 0) {
			require_once(_adm_.'/lib/lib.directory.php');
			require_once(_adm_.'/class.module/class.directory.php');

			$aclm = Docebo::user()->getAclManager();
			$selector = new UserSelector();
			$selector->use_suspended = true;

			$cancel = Get::req('cancelselector', DOTY_MIXED, false);
			$save = Get::req('okselector', DOTY_MIXED, false);

			if ($cancel) {
				Util::jump_to($back_url);
			} elseif ($save) {
				$selection = $selector->getSelection($_POST);

				$singlenode = Get::sett('orgchart_singlenode', '');
				if ($singlenode){ // se in configuazione è impostata l'univocita della posizione nell'organigramma per l'utente
					// eseguo il controllo ed eventualmente do l'errore
					require_once(_lib_.'/lib.user_profile.php');
					require_once(_adm_.'/modules/org_chart/tree.org_chart.php');

					$treedborgdb = new TreeDb_OrgDb();
					$alreadyassigned = array();
					foreach ($selection as $sel_user){
						$user_org = $this->model->getUserFolders($sel_user);
						$folder_id = $treedborgdb->getFoldersIdFromIdst(array_keys($user_org));
						if(count($folder_id) && (count($folder_id) > 1 || $id != reset($folder_id))){
							$alreadyassigned[] = $sel_user;
						}
					}
					if(count($alreadyassigned)) {
						Util::jump_to($next_url.'&res=err_alreadyassigned&count='.count($alreadyassigned).'&id_first='.$alreadyassigned[0]);
					}
				}

				$res = $this->model->assignUsers($id, $selection);

				$model = new UsermanagementAdm();
				$users = [];
				foreach ($selection as $idst) {
					$users[] = $model->getProfileData($idst);
				}
				$event = new \appCore\Events\Core\User\UsersManagementOrgChartAssignEditEvent();
				$nodedata = $this->model->getFolderById($id);
				$event->setUsers($users);
				$event->setNode($nodedata);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartAssignEditEvent::EVENT_NAME, $event);

				if($res) {
					$enrollrules = new EnrollrulesAlms();
					$enrollrules->applyRulesMultiLang('_LOG_USERS_TO_ORGCHART', $selection, $id);
				}

				Util::jump_to($next_url.($res ? '&res=ok_assignuser' : '&res=err_assignuser'));
			} else {
				$selector->show_user_selector = true;
				$selector->show_group_selector = false;
				$selector->show_orgchart_selector = false;
				$selector->show_fncrole_selector = false;

				if (Get::req('is_updating', DOTY_INT, false)) {

				} else {
					$members = $this->model->getFolderUsers($id);
					$selector->requested_tab = PEOPLEVIEW_TAB;
					$selector->resetSelection($members);
				}
				$selector->addFormInfo(
					Form::getHidden('is_updating', 'is_updating', 1).
					Form::getHidden('id', 'id', $id)
				);
				$selector->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
					array( 'index.php?r='.$this->link.'/show' => Lang::t('_ORG_CHART', 'organization_chart'),
						Lang::t('_ASSIGN_USERS', 'organization_chart') ),
					false,
					true);

			}

		} else {
			Util::jump_to($back_url);
		}
	}

	function assignfields() {
		//check permissions
		if (!$this->permissions['mod_org']) Util::jump_to('index.php?r='.$this->link.'/show');

		require_once(_base_.'/lib/lib.table.php');
		require_once(_adm_.'/lib/lib.field.php');

		$id_org = Get::req('id_node', DOTY_INT, 0);
		$table = new Table();

		$type_h = array('', 'image', 'image', 'image', 'image', 'image');
		$cont_h = array(
			Lang::t('_FIELD_NAME', 'organization_chart'),
			Lang::t('_DIRECTORY_ASSIGNFIELDGROUP', 'admin_directory'),
			Lang::t('_ORG_CHART_INHERIT', 'organization_chart'),
			Lang::t('_MANDATORY', 'organization_chart'),
			Lang::t('_ORG_CHART_FIELD_WRITE', 'organization_chart'),
			Lang::t('_USER_INHERIT', 'organization_chart')
		);

		$table->addHead($cont_h, $type_h);

		$fl = new FieldList();
		$acl = Docebo::user()->getACL();
		$acl_man = Docebo::user()->getAclManager();

		$body = "";
		$body .= Form::openForm('assignfieldgroup', 'ajax.adm_server.php?r='.$this->link.'/assignfields_action');
		$body .= Form::getLineBox(Lang::t('_ORG_CHART_LIST_FIELDS', 'organization_chart').':&nbsp;', $this->model->getOrgPath($id_org));
		$body .= Form::getHidden('idst_group', 'idst_group', $id_org);

		$arr_all_fields = $fl->getAllFields();
		$arr_fields_normal = $fl->getFieldsFromIdst(array($acl_man->getGroupST('oc_'.$id_org)));
		$arr_fields_inherit = $fl->getFieldsFromIdst(array($acl_man->getGroupST('ocd_'.$id_org)));

		//$body .= '<pre>'.print_r($arr_fields_normal, true).print_r($arr_fields_inherit, true).'</pre>';

		foreach ($arr_all_fields as $field ) {
			$id_field = $field[FIELD_INFO_ID];

			$def_value = GROUP_FIELD_NO;
			if (isset($arr_fields_normal[$id_field]))
				$def_value = GROUP_FIELD_NORMAL;
			if (isset($arr_fields_inherit[$id_field]))
				$def_value = GROUP_FIELD_INHERIT;

			switch ($def_value) {
				case GROUP_FIELD_NORMAL: {
					$is_mandatory = isset($arr_fields_normal[$id_field]) && $arr_fields_normal[$id_field][FIELD_INFO_MANDATORY] == 'true';
					$is_invisible = isset($arr_fields_normal[$id_field]) && $arr_fields_normal[$id_field][FIELD_INFO_USERACCESS] == 'readwrite';
					$is_userinherit = isset($arr_fields_normal[$id_field]) && $arr_fields_normal[$id_field][FIELD_INFO_USERINHERIT] == '1';
				} break;

				case GROUP_FIELD_INHERIT: {
					$is_mandatory = isset($arr_fields_inherit[$id_field]) && $arr_fields_inherit[$id_field][FIELD_INFO_MANDATORY] == 'true';
					$is_invisible = isset($arr_fields_inherit[$id_field]) && $arr_fields_inherit[$id_field][FIELD_INFO_USERACCESS] == 'readwrite';
					$is_userinherit = isset($arr_fields_inherit[$id_field]) && $arr_fields_inherit[$id_field][FIELD_INFO_USERINHERIT] == '1';
				} break;

				default: {
					$is_mandatory = false;
					$is_invisible = false;
					$is_userinherit = false;
				}
			}

			$selected = $def_value != GROUP_FIELD_NO;
			$disabled = 'disabled="disabled"';

			$line = array();
			$line[] = $field[FIELD_INFO_TRANSLATION];
			$line[] = Form::getInputCheckbox('fields_use_'.$id_field, 'fields_use['.$id_field.']', 1, $selected, '');
			$line[] = Form::getInputCheckbox('fields_inherit_'.$id_field, 'fields_inherit['.$id_field.']', 1, $def_value == GROUP_FIELD_INHERIT, $selected ? '' : $disabled);
			$line[] = Form::getInputCheckbox('fields_mandatory_'.$id_field, 'fields_mandatory['.$id_field.']', 1, $is_mandatory, $selected ? '' : $disabled);
			$line[] = Form::getInputCheckbox('fields_invisible_'.$id_field, 'fields_invisible['.$id_field.']', 1, $is_invisible, $selected ? '' : $disabled);
			$line[] = Form::getInputCheckbox('fields_userinherit_'.$id_field, 'fields_userinherit['.$id_field.']', 1, $is_userinherit, $selected ? '' : $disabled);

			$table->addBody($line);
		}

		$body .= $table->getTable();
		$body .= Form::closeForm();

		$output = array(
			'success' => true,
			'header' => Lang::t('_ORGCHART_FOLDER_FIELD_ALT', 'organization_chart'),
			'body' => $body
		);
		//$this->render('assign_fields', $params);

		echo $this->json->encode($output);
	}

	public function assignfields_action() {
		//check permissions
		if (!$this->permissions['mod_org']) Util::jump_to('index.php?r='.$this->link.'/show');

		require_once(_adm_.'/lib/lib.field.php');

		$id_org = Get::req('idst_group', DOTY_INT, 0);
		$fields_use = Get::req('fields_use', DOTY_MIXED, array());
		$fields_inherit = Get::req('fields_inherit', DOTY_MIXED, array());
		$fields_mandatory = Get::req('fields_mandatory', DOTY_MIXED, array());
		$fields_invisible = Get::req('fields_invisible', DOTY_MIXED, array());
		$fields_userinherit = Get::req('fields_userinherit', DOTY_MIXED, array());

		$nodedata = $this->model->getFolderById($id_org);

		$fl = new FieldList();
		$acl_man = Docebo::user()->getAclManager();

		$count = 0;
		$all_fields = $fl->getAllFields();
		$new_fields = [];

		foreach ($all_fields as $field) {
			$id_field = $field[FIELD_INFO_ID];

			if (isset($fields_use[$id_field])) {

				$arr_idgroups = array();
				if (isset($fields_inherit[$id_field])) {
					//$arr_idorgs = $acl_man->getGroupGDescendants($id_org);
					//$arr_idorgs[] = $id_org;
					//$arr_idorgs = array_unique($arr_idorgs);
					$arr_idgroups[] = $acl_man->getGroupST('oc_'.$id_org);
					$arr_idgroups[] = $acl_man->getGroupST('ocd_'.$id_org);
				} else {
					$arr_idgroups[] = $acl_man->getGroupST('oc_'.$id_org);
					$fl->removeFieldFromGroup($id_field, $acl_man->getGroupST('ocd_'.$id_org));
				}

				foreach ($arr_idgroups as $idst_group) {
					$res = $fl->addFieldToGroup(
						$id_field,
						$idst_group,
						isset($fields_mandatory[$id_field]) ? 'true' : 'false',
						isset($fields_invisible[$id_field]) ? 'readwrite' : 'readonly',
						isset($fields_userinherit[$id_field]) ? 1 : 0
					);
					if ($res) $count++;
				}
			} else {
				$res = $fl->removeFieldFromGroup($id_field, $acl_man->getGroupST('oc_'.$id_org));
				$res = $fl->removeFieldFromGroup($id_field, $acl_man->getGroupST('ocd_'.$id_org));
				if ($res) $count++;
			}
		}
		foreach ($arr_idgroups as $idst) {
			$new_fields[] = $this->getFieldGroupById($idst);
		}

		$event = new \appCore\Events\Core\User\UsersManagementOrgChartEditNodeFieldsEvent();
		$event->setNode($nodedata);
		$event->setFields($new_fields);
		\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartEditNodeFieldsEvent::EVENT_NAME, $event);

		$output = array('success' => true, 'total' => count($fields_use), 'done' => $count);
		echo $this->json->encode($output);
	}

	public function assoc() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		$id_org = Get::req('id_org', DOTY_INT, -1);
		$success = false;
		if ($id_user > 0 && $id_org > 0) { //idst of the user must be valid and the orgbranch must not be the root
			$acl_man = Docebo::user()->getAclManager();
			$idst_org = $acl_man->getGroupST('oc_'.$id_org);
			$idst_orgd = $acl_man->getGroupST('ocd_'.$id_org);
			//add to group
			$acl_man->addToGroup($idst_org, $id_user);
			$acl_man->addToGroup($idst_orgd, $id_user);
			$success = true;

			// apply enroll rules
			$lang_code = $acl_man->getSettingValueOfUsers('ui.language', array($id_user));
			$lang_code = ( $lang_code ? $lang_code : getDefaultLanguage() );

			$enrollrules = new EnrollrulesAlms();
			$enrollrules->applyRules(array($id_user), $lang_code, $id_org);
			$enrollrules->applyRulesMultiLang('_USER_ASSIGNED_TO_TREE', array($id_user), $id_org);
		}
		$output = array('success' => $success);
		echo $this->json->encode($output);
	}

	private function getFieldGroupById($idst)
	{
		$sql = "SELECT * FROM core_group_fields WHERE idst = {$idst}";
		$query = sql_query($sql);

		return sql_fetch_object($query);
	}

	public function unassoc() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, -1);
		$id_org = Get::req('id_org', DOTY_INT, -1);
		$success = false;
		if ($id_org == 0) $success = true;
		if ($id_user > 0 && $id_org > 0) { //idst of the user must be valid and the orgbranch must not be the root
			$acl_man = Docebo::user()->getAclManager();
			$idst_org = $acl_man->getGroupST('oc_'.$id_org);
			$idst_orgd = $acl_man->getGroupST('ocd_'.$id_org);
			//cancel from group
			$acl_man->removeFromGroup($idst_org, $id_user);
			$acl_man->removeFromGroup($idst_orgd, $id_user);
			$success = true;

			$event = new \appCore\Events\Core\User\UsersManagementOrgChartRemoveEvent();
			$model = new UsermanagementAdm();
			$user = $model->getProfileData($id_user);
			$event->setUser($user);
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartRemoveEvent::EVENT_NAME, $event);
		}
		$output = array('success' => $success);
		echo $this->json->encode($output);
	}

	public function multiunassoc() {
		//check permissions
		if (!$this->permissions['associate_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$users = Get::req('users', DOTY_STRING, "");
		$id_org = Get::req('id_org', DOTY_INT, -1);
		$output = array('success' => false);
		if ($id_org == 0) {
			$output['success'] = true;
			$output['count'] = 0;
			$output['list'] = array();
		}
		if ($users != "" && $id_org >= 0) {
			$acl_man = Docebo::user()->getAclManager();
			$idst_org = $acl_man->getGroupST('oc_'.$id_org);
			$idst_orgd = $acl_man->getGroupST('ocd_'.$id_org);
			$arr_users = explode(",", $users);

			$arr_members = $acl_man->getGroupUMembers(array($idst_org, $idst_orgd));
			$arr_removed = array_intersect($arr_members, $arr_users);

			//cancel from group
			$acl_man->removeFromGroup($idst_org, $arr_users);
			$acl_man->removeFromGroup($idst_orgd, $arr_users);
			$output['success'] = true;
			$output['count'] = count($arr_removed);
			$output['list'] = $arr_removed;

			$model = new UsermanagementAdm();
			$users = [];
			foreach ($arr_users as $idst) {
				$users[] = $model->getProfileData($idst);
			}
			$event = new \appCore\Events\Core\User\UsersManagementOrgChartRemoveEvent();			
			$event->setUsers($users);
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementOrgChartRemoveEvent::EVENT_NAME, $event);
		}
		echo $this->json->encode($output);
	}

	function movefolder() {
		//check permissions
		if (!$this->permissions['mod_org']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$src = Get::req('src', DOTY_INT, -1);
		$dest = Get::req('dest', DOTY_INT, -1);
		//&$folder, &$parentFolder, $newfoldername = FALSE

		$success = false;
		if ($src>0 && $dest>=0) {
			$idst_src = $acl_man->getGroupST('/oc_'.$src);
			$idst_src_d = $acl_man->getGroupST('/ocd_'.$src);

			$idst_dest = $acl_man->getGroupST('/oc_'.$dest);
			$idst_dest_d = $acl_man->getGroupST('/ocd_'.$dest);

			//...
		}

		$output = array('success' => $success);
		echo $this->json->encode($output);
	}

	public function changepwdTask() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		if(Get::cfg('demo_mode'))
		{
			$output['success'] = false;
			$output['message'] = UIFeedback::perror('Cannot mod password during demo mode.');
			echo $this->json->encode($output);
			return;
		}

		$this->render('changepwd', array(
			'title' => Lang::t('_CHANGEPASSWORD', 'profile'),
			'json' => $this->json
		));
	}

	public function changepwd_actionTask() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		if(Get::cfg('demo_mode'))
		{
			$output['success'] = false;
			$output['message'] = UIFeedback::perror('Cannot mod password during demo mode.');
			echo $this->json->encode($output);
			return;
		}

		$userid = Get::req('userid', DOTY_STRING, '');
		$idst = Get::req('idst', DOTY_INT, 0);
		$new_password = Get::req('new_password', DOTY_STRING, '');
		$confirm_password = Get::req('confirm_password', DOTY_STRING, '');
		$force_changepwd = Get::req('force_changepwd', DOTY_INT, 0);
		$output = array();
		$acl_man = Docebo::user()->getAclManager();

		if ($new_password == "" || $confirm_password == "") {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror(Lang::t('_REG_PASS_MIN_CHAR', 'register', array('[min_char]' => 1)));
			echo $this->json->encode($output);
			return;
		}

		if ($new_password != $confirm_password) {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror(Lang::t('_ERR_PASSWORD_NO_MATCH', 'register'));
			echo $this->json->encode($output);
			return;
		}

		if ($idst <= 0) {
			if ($userid == "") {
				$output['success'] = false;
				$output['message'] = UIFeedback::perror(Lang::t('_ERR_INVALID_USER', 'register'));
				echo $this->json->encode($output);
				return;
			}
			$idst = $acl_man->getUserST($userid);
			if ($idst === false) {
				$output['success'] = false;
				$output['message'] = UIFeedback::perror(Lang::t('_ERR_INVALID_USER', 'register'));
				echo $this->json->encode($output);
				return;
			}
		} else {
			$_userid = $acl_man->getUserid($idst);
			if ($_userid != $userid) {
				$idst = $acl_man->getUserST($userid);
				if (!$idst) {
					$output['success'] = false;
					$output['message'] = UIFeedback::perror(Lang::t('_OPERATION_FAILURE', 'standard'));
					echo $this->json->encode($output);
					return;
				}
			}
		}

		$res = $this->model->changePassword($idst, $new_password, $force_changepwd);
		if ($res) {
			$output['success'] = true;
			$output['message'] = UIFeedback::pinfo(Lang::t('_OPERATION_SUCCESSFUL', 'standard'));
		} else {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror(Lang::t('_OPERATION_FAILURE', 'standard').': <b>'.$userid.'</b>');
		}
		echo $this->json->encode($output);
	}

	public function users_autocompleteTask() {
		$query = Get::req('query', DOTY_STRING, '');
		$results = Get::Req('results', DOTY_INT, Get::sett('visuItem', 25));
		$output = array('users' => array());
		if ($query != "") {
			$users = $this->model->searchUsersByUserid($query, $results, true);
			$acl_man = Docebo::user()->getAclManager();
			foreach ($users as $user) {
				$_userid = $acl_man->relativeId($user->userid);
				$output['users'][] = array(
					'idst' => $user->idst,
					'userid' => $_userid,
					'userid_highlight' => Layout::highlight($_userid, $query),
					'name' => $user->lastname.' '.$user->firstname
				);
			}
		}
		echo $this->json->encode($output);
	}

	public function importusers() {
		$base_url = 'index.php?r='.$this->link.'/show';

		//check permissions
		if (!$this->permissions['add_user']) Util::jump_to($base_url);

		$idOrg = Get::req('id', DOTY_INT, -1);
		if ($idOrg<0) return false;
		$step = Get::req('step', DOTY_INT, 1);
		$params = array('id_org' => $idOrg, 'step' => $step);

		$undo = Get::req('import_groupcancel', DOTY_MIXED, false);
		if ($undo) Util::jump_to($base_url);

		switch ($step) {

			case 1: {
			} break;

			case 2: {
				$params['orgchart_list'] = $this->model->getOrgChartDropdownList(Docebo::user()->getIdSt());

				require_once(_base_.'/lib/lib.upload.php');

				// ----------- file upload -----------------------------------------
				if($_FILES['file_import']['name'] == '') {
					//$_SESSION['last_error'] = Lang::t('_FILEUNSPECIFIED');
					Util::jump_to($base_url.'&res=no_file' );
				} else {
					$path = '/appCore/';
					$savefile = mt_rand(0,100).'_'.time().'_'.$_FILES['file_import']['name'];
					if (!file_exists(Get::rel_path('base').'/files'.$path.$savefile )) {
						sl_open_fileoperations();
						if (!sl_upload($_FILES['file_import']['tmp_name'], $path.$savefile)) {
							sl_close_fileoperations();
							//$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
							Util::jump_to($base_url.'&err=no_upload');
						}
						sl_close_fileoperations();
					} else {
						$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
						Util::jump_to($base_url.'&err=no_upload');
					}
				}

				require_once(_adm_.'/modules/org_chart/import.org_chart.php');
				$separator_info = Get::req('import_separator', DOTY_STRING, ',');
				$separator = false;
				switch ($separator_info) {
					case "comma": $separator = ","; break;
					case "dotcomma": $separator = ";"; break;
					case "manual": $separator = Get::req('import_separator_manual', DOTY_STRING, ""); break;
				}
				$first_row_header = (Get::req('import_first_row_header', DOTY_STRING, 'false') == 'true');
				$import_charset = Get::req('import_charset', DOTY_STRING, 'UTF-8');
				if (trim($import_charset) === '') $import_charset = 'UTF-8';

				$pwd_force_change_policy = Get::req('pwd_force_change_policy', DOTY_STRING, 'do_nothing');
				$set_password = Get::req('set_password', DOTY_STRING, 'no_action');
				$use_manual_password = Get::req('use_manual_password', DOTY_BOOL, false);
				$manual_password = Get::req('manual_password', DOTY_STRING, '');

				$src = new DeceboImport_SourceCSV(array(
					'filename' => $GLOBALS['where_files_relative'].$path.$savefile,
					'separator' => $separator,
					'first_row_header' => $first_row_header,
					'import_charset' => $import_charset
				));
				$dst = new ImportUser(array(
					'dbconn'=>$GLOBALS['dbConn'],
					'tree' => $idOrg,
					'pwd_force_change_policy' => $pwd_force_change_policy,
					'set_password' => $set_password,
					'use_manual_password' => false,
					'manual_password' => NULL,
					'send_alert' => 0,
					'action_on_users' => 'create_and_update'
				));

				$src->connect();
				$dst->connect();

				$importer = new DoceboImport();
				$importer->setSource( $src );
				$importer->setDestination( $dst );

				$params['UIMap'] = $importer->getUIMap();
				$params['tot_row'] = $importer->getTotRow();
				$params['filename'] = $GLOBALS['where_files_relative'].$path.$savefile;
				$params['first_row_header'] = $first_row_header;
				$params['separator'] = $separator;
				$params['import_charset'] = $import_charset;
			} break;

			case 3: {
				//if (!Get::pReq('send_alert', DOTY_INT, 0) && Get::req('set_password', DOTY_STRING, 'from_file') != 'from_file') {
				//    Util::jump_to($base_url.'&res=need_to_alert' );
				//}

				$filename = Get::req('filename', DOTY_STRING, "");
				if ($filename == "") return false;
				$separator = Get::req('import_separator', DOTY_STRING, ',');
				$first_row_header = Get::req('import_first_row_header', DOTY_STRING, 'false') == 'true';
				$import_charset = Get::req('import_charset', DOTY_STRING, 'UTF-8');
				if (trim($import_charset) === '') $import_charset = 'UTF-8';

				require_once(_adm_.'/modules/org_chart/import.org_chart.php');
				$src = new DeceboImport_SourceCSV(array(
					'filename'=>$filename,
					'separator'=>$separator,
					'first_row_header'=>$first_row_header,
					'import_charset' => $import_charset
				));
				$dst = new ImportUser(array(
					'dbconn'=>$GLOBALS['dbConn'],
					'tree' => $idOrg,
					'pwd_force_change_policy' => Get::req('pwd_force_change_policy', DOTY_STRING, 'do_nothing'),
					'set_password' => Get::req('set_password', DOTY_STRING, 'from_file'),
					'manual_password' => Get::req('password_to_insert', DOTY_STRING, 'automatic_password') == 'use_manual_password'? Get::req('manual_password', DOTY_STRING, NULL) : NULL,
					'send_alert' => Get::pReq('send_alert', DOTY_INT, 0),
					'action_on_users' => Get::pReq('action_on_users', DOTY_STRING, 'create_and_update')
				));
				$src->connect();
				$dst->connect();

				$importer = new DoceboImport();
				$importer->setSource( $src );
				$importer->setDestination( $dst );

				$importer->parseMap();
				if (!in_array('userid', $importer->import_map)
					|| !in_array(array_search('userid', $importer->import_map), array_keys($importer->import_tocompare))) {
					Util::jump_to($base_url.'&res=userid_needed' );
				}

				foreach($importer->import_map AS $im){
					if($im != DOCEBOIMPORT_IGNORE && count(array_keys($importer->import_map, $im)) > 1){
						Util::jump_to($base_url.'&res=field_repeated' );
					}
				}

				$results = $importer->doImport();

				$users = $dst->getNewImportedIdst();
				//apply enroll rules
				if(!empty($users)) {
					$model = new UsermanagementAdm();
					$arr_users = [];
					foreach ($users as $idst) {
						$arr_users[] = $model->getProfileData($idst);
					}
					$event = new \appCore\Events\Core\User\UsersManagementCSVimportEvent();
					$event->setUsers($arr_users);
					\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementCSVimportEvent::EVENT_NAME, $event);

					$enrollrules = new EnrollrulesAlms();
					$enrollrules->newRules('_NEW_IMPORTED_USER', $users, 'all', $idOrg);
				}

				$src->close();
				$dst->close();

				$buffer = "";
				if (count($results) > 1) {
					require_once(_base_.'/lib/lib.table.php');
					$buffer .= Lang::t('_ERRORS', 'admin_directory').': <b>'.(count($results)-1).'</b><br/>';
					$table = new Table(
						Get::sett('visuItem', 25),
						Lang::t('_ERRORS', 'admin_directory'),
						Lang::t('_ERRORS', 'admin_directory')
					);
					$table->setColsStyle(array('',''));
					$table->addHead(array(
						Lang::t('_ROW', 'admin_directory'),
						Lang::t('_DESCRIPTION', 'admin_directory')
					));

					foreach ($results as $key=>$err_val) {
						if ($key != 0) {
							$table->addBody(array($key, $err_val));
						}
					}
					$buffer .= $table->getTable();
				}

				if($buffer === '')
					$buffer = '<br/><br/>';

				$params['backUi'] = getBackUi($base_url, Lang::t('_BACK', 'standard'));
				$params['resultUi'] = Lang::t('_IMPORT', 'standard').': <b>'.( $first_row_header ? $results[0] - 1 : $results[0] ).'</b><br />';
				$params['results'] = $results;
				$params['table'] = $buffer;

				// remove uploaded file:
				require_once(_base_.'/lib/lib.upload.php');
				sl_open_fileoperations();
				unlink($filename);
				sl_close_fileoperations();
			} break;

		}

		$this->render('importusers', $params);
	}

	protected function _formatCsvValue($value, $delimiter) {
		$formatted_value = str_replace($delimiter, '\\'.$delimiter, $value);
		return $delimiter.$formatted_value.$delimiter;
	}

	public function csvexport() {
		//check permissions
		if (!$this->permissions['view_user']) Util::jump_to('index.php?r='.$this->link.'/show');

		require_once(_base_.'/lib/lib.download.php');
		require_once(_adm_.'/lib/lib.field.php');

		$users = Get::req('users', DOTY_STRING, "");
		$separator = ',';
		$delimiter = '"';
		$line_end = "\r\n";

		$output = "";
		$fman = new FieldList();
		$field_list = $fman->getFlatAllFields();

		$head = array();
		$head[] = $this->_formatCsvValue(Lang::t('_USERNAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_FIRSTNAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_LASTNAME', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_EMAIL', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_SIGNATURE', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_REGISTER_DATE', 'standard'), $delimiter);
		$head[] = $this->_formatCsvValue(Lang::t('_LAST_ENTER', 'standard'), $delimiter);
		foreach ($field_list as $id_field=>$field_translation) {
			$head[] = $this->_formatCsvValue($field_translation, $delimiter);
		}
		$output .= implode($separator, $head).$line_end;

		if ($users != "") {
			$acl_man = Docebo::user()->getAclManager();
			$arr_users = explode(',', $users);
			$arr_users = array_unique($arr_users);
			$details = $this->model->getUsersDetails($arr_users, true, true);
			if (is_array($details)) {
				foreach ($details as $id_user => $detail) {
					$row = array();
					$row[] = $acl_man->relativeId($detail->userid);
					$row[] = $detail->firstname;
					$row[] = $detail->lastname;
					$row[] = $detail->email;
					$row[] = $detail->signature;
					$row[] = $detail->register_date;
					$row[] = $detail->lastenter;

					foreach ($field_list as $id_field=>$field_translation) {
						$row[] = isset($detail->_custom_fields[$id_field]) ? $detail->_custom_fields[$id_field] : "";
					}

					//format row and produce a string text to add to CSV file
					$csv_row = array();
					foreach ($row as $row_data) {
						$csv_row[] = $this->_formatCsvValue($row_data, $delimiter);
					}

					$output .= implode($separator, $csv_row).$line_end;
				}
			}
		}

		sendStrAsFile($output, 'users_export_'.date("Ymd").'.csv');
	}

	public function profile_dialog() {
		//check permissions
		if (!$this->permissions['view_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		require_once(_base_.'/lib/lib.user_profile.php');

		$id_user = Get::req('id', DOTY_INT, -1);
		if ($id_user <= 0) {
			//no luck with idst? then try with userid
			$userid = Get::req('userid', DOTY_STRING, "");
			if ($userid != "") {
				$acl_man = Docebo::user()->getACLManager();
				$id_user = $acl_man->getUserST($userid);
			}

			//neither userid is valid: return error
			if (!$id_user) {
				echo $this->json->encode(array(
					'success' => false,
					'message' => Lang::t('_INVALID_USER', 'admin_directory')
				));
				return;
			}
		}

		$profile = new UserProfile($id_user);
		$profile->init('profile', 'framework', 'r='.$this->link.'/editprofile&id_user='.(int)$id_user, 'ap');
		if (Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) $profile->enableGodMode();
		//$profile->setEndUrl('index.php?modname=directory&op=org_chart#user_row_'.$id_user);

		//evento mostra dettaglio profilo

		$event = new \appCore\Events\Core\User\UsersManagementShowDetailsEvent();
		$event->setProfile($profile);
		\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementShowDetailsEvent::EVENT_NAME, $event);



		$this->render('user_profile', array(
			'id_user' => $id_user,
			'title' => Lang::t('_DETAILS', 'standard').': '.$this->model->getUserId($id_user),
			'profile' => $profile,
			'model' => $this->model,
			'json' => $this->json
		));
	}

	public function editprofile() {
		//check permissions
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		require_once(_base_.'/lib/lib.user_profile.php');

		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user > 0) {
			$profile = new UserProfile($id_user);
			$profile->init('profile', 'framework', 'r='.$this->link.'/editprofile&id_user='.(int)$id_user, 'ap');
			if (Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) $profile->enableGodMode();

			echo '<br />'
				.'<div class="std_block">'
				.getBackUi('index.php?r='.$this->link.'/show', Lang::t('_BACK', 'standard') )

				.$profile->performAction()

				.'</div>';
		}
	}

	public function show_waitingTask() {
		//check permissions
		if (!$this->permissions['approve_waiting_user']) Util::jump_to('index.php?r='.$this->link.'/show');

		Util::get_js(Get::rel_path('base').'/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('adm').'/views/usermanagement/waiting_users.js', true, true);
		$this->render('show_waiting', array(
			'filter_text' => ""
		));
	}

	public function show_deletedTask() {
		//check permissions
		if (!$this->permissions['view_user']) Util::jump_to('index.php?r='.$this->link.'/show');

		$this->render('show_deleted', array(
			'filter_text' => ""
		));
	}

	public function waiting_user_detailsTask() {
		//check permissions
		if (!$this->permissions['view_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		require_once(_adm_.'/lib/lib.field.php');
		$fman = new FieldList();

		$acl_man = Docebo::user()->getACLManager();
		$id_user = Get::req('id_user', DOTY_INT, 0);
		$userid = $acl_man->relativeId($acl_man->getUserid($id_user));

		$this->render('waiting_user_details', array(
			'id_user' => $id_user,
			'title' => Lang::t('_DETAILS', 'standard').': '.$userid,
			'fields' => $fman,
			'json' => $this->json
		));
	}


	public function getdeleteduserstabledataTask() {
		//check permissions
		if (!$this->permissions['view_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		$total = $this->model->getDeletedUsersTotal($filter);
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

		$list = $this->model->getDeletedUsersList($pagination, $filter);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$_userid = $acl_man->relativeId($record->userid);
				$records[] = array(
					'id' => (int)$record->idst,
					'userid' => Layout::highlight($_userid, $filter),
					'firstname' => Layout::highlight($record->firstname, $filter),
					'lastname' => Layout::highlight($record->lastname, $filter),
					'email' => Layout::highlight($record->email, $filter),
					'deletion_date' => Format::date($record->deletion_date, 'datetime'),
					'deleted_by' => $acl_man->relativeId($record->deleted_by)
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}

	public function getwaitinguserstabledataTask() {
		//check permissions
		if (!$this->permissions['approve_waiting_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$op = Get::req('op', DOTY_STRING, "");
		if ($op == 'selectall') {
			$filter = Get::req('filter', DOTY_STRING, "");
			$output = $this->model->getWaitingUsersIds($filter);
			echo $this->json->encode($output);
			return;
		}

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_INT, Get::sett('visuItem', 25));
		$rowsPerPage = Get::req('rowsPerPage', DOTY_INT, $results);
		$sort = Get::req('sort', DOTY_STRING, "");
		$dir = Get::req('dir', DOTY_STRING, "asc");
		$filter = Get::req('filter', DOTY_STRING, "");

		$total = $this->model->getWaitingUsersTotal($filter);
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

		$list = $this->model->getWaitingUsersList($pagination, $filter);

		//format models' data
		$records = array();
		$acl_man = Docebo::user()->getAclManager();
		if (is_array($list)) {
			foreach ($list as $record) {
				$_userid = $acl_man->relativeId($record->userid);
				$_inserted_by = $record->inserted_by != "" ? $acl_man->relativeId($record->inserted_by) : "";
				$records[] = array(
					'id' => (int)$record->idst,
					'userid' => Layout::highlight($_userid, $filter),
					'firstname' => Layout::highlight($record->firstname, $filter),
					'lastname' => Layout::highlight($record->lastname, $filter),
					'email' => Layout::highlight($record->email, $filter),
					'confirmed' => Layout::highlight($record->confirmed, $filter),
					'insert_date' => Format::date($record->insert_date, 'datetime'),
					'inserted_by' => $_inserted_by,
					'del' => 'ajax.adm_server.php?r='.$this->link.'/delete_waiting&id_user='.(int)$record->idst
				);
			}
		}

		$output = array(
			'startIndex' => $startIndex,
			'recordsReturned' => count($records),
			'sort' => $sort,
			'dir' => $dir,
			'totalRecords' => $total,
			'pageSize' => $rowsPerPage,
			'records' => $records
		);

		echo $this->json->encode($output);
	}


	public function confirm_waitingTask() {
		//check permissions
		if (!$this->permissions['approve_waiting_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$arr_users = array();
		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user > 0) {
			$arr_users = array($id_user);
		} else {
			$str_users = trim(Get::Req('users', DOTY_STRING, ""));
			$arr_users = $str_users != "" ? explode(",", $str_users) : array();
		}

		$output = array();
		if (!is_array($arr_users) || empty($arr_users)) {
			$output['success'] = false;
			$output['message'] = UIFeedback::pnotice(Lang::t('_NO_USERS_SPECIFIED', 'admin_directory'));
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->confirmWaitingUsers($arr_users);
		if ($res) {
			$output['success'] = true;
		} else {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror(Lang::t('_OPERATION_FAILURE', 'standard'));
		}
		echo $this->json->encode($output);
	}

	public function delete_waitingTask() {
		//check permissions
		if (!$this->permissions['approve_waiting_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$arr_users = array();
		$id_user = Get::req('id_user', DOTY_INT, -1);
		if ($id_user > 0) {
			$arr_users = array($id_user);
		} else {
			$str_users = trim(Get::Req('users', DOTY_STRING, ""));
			$arr_users = $str_users != "" ? explode(",", $str_users) : array();
		}

		$output = array();
		if (!is_array($arr_users) || empty($arr_users)) {
			$output['success'] = false;
			$output['message'] = UIFeedback::pnotice(Lang::t('_NO_USERS_SPECIFIED', 'admin_directory'));
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->deleteWaitingUsers($arr_users);
		if ($res) {
			$output['success'] = true;
		} else {
			$output['success'] = false;
			$output['message'] = UIFeedback::perror(Lang::t('_OPERATION_FAILURE', 'standard'));
		}
		echo $this->json->encode($output);
	}


	public function multimodTask() {
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$title = Lang::t('_MOD', 'admin_directory');
		$users_count = Get::req('users_count', DOTY_INT, 0);
		if ($users_count <= 0) {
			$output = array(
				'success' => true,
				'header' => $title,
				'body' => Lang::t('_EMPTY_SELECTION', 'admin_directory')
			);
			echo $this->json->encode($output);
			return;
		}

		$acl_man = Docebo::user()->getAclManager();
		$level = ADMIN_GROUP_USER;


		$arr_levels = $acl_man->getAdminLevels();//index = idst; value = groupid;
		$levels = array();
		foreach ($arr_levels as $groupid_level=>$idst_level) {
			$levels[ $groupid_level ] = Lang::t('_DIRECTORY_'.$groupid_level, 'admin_directory');
		}

		$info = array(
			'firstname' => '',
			'lastname' => '',
			'email' => '',
			'level' => '',
			'facebook_id' => '',
			'twitter_id' => '',
			'linkedin_id' => '',
			'google_id' => ''
		);

		$language = getDefaultLanguage();
		$languages = Docebo::langManager()->getAllLanguages();

		require_once(_base_.'/lib/lib.platform.php');
		$pman =& PlatformManager::createInstance();// = new PlatformManager();
		$platforms = $pman->getPlatformList();

		require_once(_adm_.'/lib/lib.field.php');
		$fman = new FieldList();
		$field_list = $fman->getFlatAllFields(array_keys($platforms));
		$fields_to_exclude = $fman->getFieldsByType('upload');

		$preference = new UserPreferences(0);
		$modify_mask = $preference->getModifyMask('ui.', true, true);

		$fields_mask = $fman->playFieldsForUser(-1, false, false, true, false, true);

		//build edit mask
		$this->render('multimod', array(
			'users_count' => $users_count,
			'title' => $title,
			'levels' => $levels,
			'modify_mask' => $modify_mask,
			'fields_mask' => $fields_mask,
			'fields_to_exclude' => is_array($fields_to_exclude) ? $fields_to_exclude : array(),
			'info' => $info,
			'json' => $this->json
		));
	}

	public function multimod_actionTask() {
		if (!$this->permissions['mod_user']) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
			echo $this->json->encode($output);
			return;
		}

		$output = false;

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

		$info = new stdClass();
		$to_update = Get::req('to_change', DOTY_MIXED, array());
		$count_updated = 0;

		if (!empty($to_update)) {
			foreach ($to_update as $property) {

			}
		}

		//read input data
		$sel_properties = Get::req('multimod_sel', DOTY_MIXED, array());
		$pref_properties = Get::req('multimod_selpref', DOTY_MIXED, array());
		$field_properties = Get::req('multimod_selfield', DOTY_MIXED, array());

		//validate input data
		$info = new stdClass();
		if (isset($sel_properties['firstname'])) $info->firstname = Get::req('firstname', DOTY_STRING, "");
		if (isset($sel_properties['lastname'])) $info->lastname = Get::req('lastname', DOTY_STRING, "");
		if (isset($sel_properties['email'])) $info->email = Get::req('email', DOTY_STRING, "");
		if (isset($sel_properties['password'])) {
			$pwd_1 = Get::req('new_password', DOTY_STRING, "");
			$pwd_2 = Get::req('new_password_confirm', DOTY_STRING, "");
			if ($pwd_1 == $pwd_2) {
				$info->password = $pwd_1;
			} else {
				$output = array('success' => false, 'message' => $this->_getErrorMessage('password mismatch'));
				echo $this->json->encode($output);
				return;
			}
		}
		if (isset($sel_properties['force_change'])) $info->force_change = Get::req('force_change', DOTY_INT, 0) > 0;

		if (isset($sel_properties['level'])) $info->level = Get::req('level', DOTY_STRING, "");

		/*
		if (isset($sel_properties['facebook_id'])) $info->facebook_id = Get::req('facebook_id', DOTY_STRING, "");
		if (isset($sel_properties['twitter_id'])) $info->twitter_id = Get::req('twitter_id', DOTY_STRING, "");
		if (isset($sel_properties['linkedin_id'])) $info->linkedin_id = Get::req('linkedin_id', DOTY_STRING, "");
		if (isset($sel_properties['google_id'])) $info->google_id = Get::req('google_id', DOTY_STRING, "");
		*/

		if (!empty($field_properties)) {
			require_once(_adm_.'/lib/lib.field.php');
			$fields = new FieldList();

			$selected_fields = array_keys($field_properties);
			$finfo = $fields->getFieldsFromArray($selected_fields);

			$field_info = array();
			foreach ($finfo as $id_field => $data) {
				$input_data = Get::req('field_'.$data[FIELD_INFO_TYPE], DOTY_MIXED, array());
				if (isset($input_data[$id_field])) {
					$value_to_set = "";
					switch ($data[FIELD_INFO_TYPE]) {
						case "": $value_to_set = Format::dateDb($input_data[$id_field], 'date'); break;
						default: $value_to_set = $input_data[$id_field];
					}
					$field_info[$id_field] = $value_to_set;
				}
			}

			$info->__fields = $field_info;
		}

		if (!empty($pref_properties)) {
			$info->__preferences = $pref_properties;
		}

		$event = new \appCore\Events\Core\User\UsersManagementEditEvent();
		$event->setType('multiple');
		$event->setUsers($users);
		\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersManagementEditEvent::EVENT_NAME, $event);
		$users = $event->getUsers();

		$res = $this->model->updateMultipleUsers($users, $info);

		if (!$res) {
			$output = array('success' => false, 'message' => $this->_getErrorMessage('server error'));
		} else {
			$output = array('success' => true, 'update' => $count_updated);
		}
		echo $this->json->encode($output);
	}



	public function gettreedata_create() {
		$command = Get::req('command', DOTY_ALPHANUM, "");

		switch ($command) {

			case "expand": {
				//check permissions
				if (!$this->permissions['view_org']) {
					$output = array('success' => false, 'message' => $this->_getErrorMessage('no permission'));
					echo $this->json->encode($output);
					return;
				}

				$idOrg = Get::req('node_id', DOTY_INT, -1);
				$initial = (Get::req('initial', DOTY_INT, 0) > 0 ? true : false);

				if ($initial) {
					//get selected node from session and set the expanded tree
					$idOrg = $this->_getSessionValue('selected_node', 0);//$this->_getSelectedNode();
					$nodes = $this->model->getOrgChartInitialNodes($idOrg, true);

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
					$nodes = $this->model->getOrgChartNodes($idOrg, false, false, true);

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
			} break;

			default: {
				$output = array();
				$output['success'] = false;
				echo $this->json->encode($output);
			}
		} // end switch

	}


	/**
	 * Check which levels an admin can manage in users creation
	 * @param string $level_to_check
	 * @return boolean
	 */
	protected function _canUseLevel($level_to_check) {
		$my_level = Docebo::user()->getUserLevelId();
		if ($my_level == ADMIN_GROUP_GODADMIN) return TRUE;
		if ($my_level == ADMIN_GROUP_USER) return FALSE;
		if ($my_level == ADMIN_GROUP_ADMIN ) {
			if ($level_to_check == ADMIN_GROUP_USER) return TRUE;
		}
		return FALSE;
	}


}

?>