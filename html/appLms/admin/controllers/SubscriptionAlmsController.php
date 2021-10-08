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

class SubscriptionAlmsController extends AlmsController
{
    /** @var SubscriptionAlms $model */
	protected $model;
	protected $json;
	protected $acl_man;
	protected $id_course;
	protected $id_edition;
	protected $id_date;
	protected $data;
	protected $permissions;
	protected $db;
	protected $reached_max_user_subscribed = false;

	public function init()
	{
		checkPerm('subscribe', false, 'course', 'lms');
		require_once(_base_ . '/lib/lib.json.php');
		require_once(Forma::inc(_base_ . '/lib/lib.eventmanager.php'));

		//Course info
		$this->id_course = Get::req('id_course', DOTY_INT, 0);
		$this->id_edition = Get::req('id_edition', DOTY_INT, 0);
		$this->id_date = Get::req('id_date', DOTY_INT, 0);

		$this->model = new SubscriptionAlms($this->id_course, $this->id_edition, $this->id_date);
		$this->json = new Services_JSON();
		$this->acl_man = Docebo::user()->getAclManager();
		$this->db = DbConn::getInstance();

		$this->permissions = array(
			'subscribe_course' => checkPerm('subscribe', true, 'course', 'lms'),
			'subscribe_coursepath' => checkPerm('subscribe', true, 'coursepath', 'lms'),
			'moderate' => checkPerm('moderate', true, 'course', 'lms')
		);

		$this->link = 'alms/subscription';
		$this->link_course = 'alms/course';
		$this->link_edition = 'alms/edition';
		$this->link_classroom = 'alms/classroom';

		$this->checkAdminLimit();
	}


	/**
	 * Set the permissions and / or returns the check results.
	 * @return boolean returns false if no other users can be subscribed
	 */
	protected function checkAdminLimit()
	{
		$res = true;

		if ($this->reached_max_user_subscribed) {
			$res = false;
		} else if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			$admin_pref = new AdminPreference();
			$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
			/*
			 * Array ( [admin_rules.direct_course_subscribe] => on
			 * [admin_rules.direct_user_insert] => on
			 * [admin_rules.limit_course_subscribe] => on
			 * [admin_rules.limit_user_insert] => on
			 * [admin_rules.max_course_subscribe] => 1
			 * [admin_rules.max_user_insert] => 1 )
			 */

			if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
				$user_pref = new UserPreferences(Docebo::user()->getIdSt());
				$subscribed_count = $user_pref->getPreference('user_subscribed_count');
				if ($subscribed_count >= $pref['admin_rules.max_course_subscribe']) {
					// $this->permissions['subscribe_course']=false;
					// $this->permissions['subscribe_coursepath']=false;
					$this->reached_max_user_subscribed = true;
					$res = false;
				}
			}
		}

		return $res;
	}


	protected function _getErrorMessage($code)
	{
		return $this->_getMessage($code);
	}


	protected function _getMessage($code)
	{
		$message = "";
		switch ($code) {
			case "no permission":
				$message = 'You don\'t have the required permission';
				break;
			case "no options selected":
				$message = "";
				break;
		}
		return $message;
	}


	protected function _addToCourseGroup($id_group, $id_user)
	{
		Docebo::aclm()->addToGroup($id_group, $id_user);
	}


	public function show()
	{

		if (!$this->permissions['subscribe_course']) {
			// the user cannot use this function
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		Util::get_js(Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);
		Util::get_js(Get::rel_path('base') . '/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('lms') . '/admin/views/subscription/subscription.js', true, true);

		if (isset($_GET['res']) && $_GET['res'] !== '')
			UIFeedback::info(Lang::t(strtoupper($_GET['res']), 'subscription'));

		if (isset($_GET['err']) && $_GET['err'] !== '')
			UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));

		Form::loadDatefieldScript(); //some dialogs use date inputs
		$umodel = new UsermanagementAdm();

		$course_info = $this->model->getCourseInfoForSubscription();
		$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

		//generate field list for dynamic user column
		require_once(_adm_ . '/lib/lib.field.php');
		$fman = new FieldList();
		$fields = $fman->getFlatAllFields(array('framework', 'lms'));
		$f_list = array(
			'email' => Lang::t('_EMAIL', 'standard'),
			'lastenter' => Lang::t('_DATE_LAST_ACCESS', 'profile'),
			'register_date' => Lang::t('_DIRECTORY_FILTER_register_date', 'admin_directory'),
			'date_complete' => Lang::t('_DATE_COMPLETE', 'certificate'),
			'level' => Lang::t('_LEVEL', 'standard'),
			'language' => Lang::t('_LANGUAGE', 'standard')
		);
		$f_list = $f_list + $fields;
		$f_selected = $this->json->decode(Docebo::user()->getPreference('ui.directory.custom_columns'));
		if ($f_selected == false) {
			$f_selected = array('email');
		}
		$js_arr = array();
		foreach ($f_list as $key => $value) $js_arr[] = $key . ': ' . $this->json->encode($value);
		$f_list_js = '{' . implode(',', $js_arr) . '}';

		$params = array(
			'back_link' => $this->_getBackLink(),
			'id_course' => $this->id_course,
			'id_edition' => $this->id_edition,
			'id_date' => $this->id_date,
			'level_list_js' => $this->_getJsArrayLevel(),
			'status_list_js' => $this->_getJsArrayStatus(),
			'orgchart_list' => $umodel->getOrgChartDropdownList(),
			'is_active_advanced_filter' => false,
			'filter_text' => "",
			'filter_orgchart' => 0,
			'filter_descendants' => false,
			'filter_date_valid' => "",
			'filter_show' => 0,
			'course_name' => $course_name,
			//'sub_start' => $sub_start,
			//'sub_end' => $sub_end,
			//'del_sub' => $del_sub,
			'use_overbooking' => (isset($course_info['overbooking']) ? (bool)$course_info['overbooking'] : false),
			//'info_subscriptions' => $this->model->getSubscriptionsInfo(),
			'num_var_fields' => 1,
			'fieldlist' => $f_list,
			'fieldlist_js' => $f_list_js,
			'selected' => $f_selected,
			'hidden_validity' => false
		);

		$this->render('show', $params);
	}


	public function add()
	{
		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		} else if (!$this->checkAdminLimit()) {
			$this->render('invalid', array(
				'message' => Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_base_ . '/lib/lib.form.php');
		require_once(_base_ . '/lib/lib.userselector.php');
		//require_once($GLOBALS['where_framework'].'/class.module/class.directory.php');

		$user_selector = new UserSelector();
		$cman = new CourseAlms();

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		if (isset($_POST['cancelselector']))
			Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date);

		$model = new SubscriptionAlms($id_course, $id_edition, $id_date);

		if (isset($_POST['okselector'])) {

			$_selection = $user_selector->getSelection($_POST);
			$acl_man = Docebo::user()->getAclManager();
			$user_selected = $acl_man->getAllUsersFromSelection($_selection);//$acl_man->getAllUsersFromIdst($_selection);

			$user_alredy_subscribed = $model->loadUserSelectorSelection();
			$user_selected = array_diff($user_selected, $user_alredy_subscribed);

			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				$to_subscribe = count($user_selected);

				$admin_pref = new AdminPreference();
				$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
				if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
					$user_pref = new UserPreferences(Docebo::user()->getIdSt());
					$subscribed_count = $user_pref->getPreference('user_subscribed_count');
					if ($subscribed_count + $to_subscribe > $pref['admin_rules.max_course_subscribe']) {
						$this->render('invalid', array(
							'message' => Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
							'back_url' => 'index.php?r=' . $this->link_course . '/show'
						));
						return;
					}
				}
			}


			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				require_once(_base_ . '/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
				$user_selected = array_intersect($user_selected, $admin_users);
			}

			$user_selected = $acl_man->getUsersFromMixedIdst($user_selected);
			if (count($user_selected) == 0)
				Util::jump_to('index.php?r=' . $this->link . '/add&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date . '&err=_empty_selection');


			$sel_date_begin_validity = Get::req('sel_date_begin_validity', DOTY_INT, 0) > 0;
			$sel_date_expire_validity = Get::req('sel_date_expire_validity', DOTY_INT, 0) > 0;
			$date_begin_validity = $sel_date_begin_validity ? Get::req('set_date_begin_validity', DOTY_STRING, "") : false;
			$date_expire_validity = $sel_date_expire_validity ? Get::req('set_date_expire_validity', DOTY_STRING, "") : false;
			if ($date_begin_validity) $date_begin_validity = Format::dateDb($date_begin_validity, 'date');
			if ($date_expire_validity) $date_expire_validity = Format::dateDb($date_expire_validity, 'date');

			$select_level_mode = Get::req('select_level_mode', DOTY_STRING, "");
			switch ($select_level_mode) {
				case "students":
					{

						// subscribe the selection with the students level
						require_once(_lms_ . '/lib/lib.course.php');
						$course_info = $model->getCourseInfoForSubscription();

						//check if the subscriber is a sub admin and, if true check it's limitation
						$can_subscribe = true;
						$subscribe_method = $course_info['subscribe_method'];
						if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
							$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
							$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
							$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

							if ($limited_subscribe == 'on') $limited_subscribe = true;
							else $limited_subscribe = false;
							if ($direct_subscribe == 'on') $direct_subscribe = true;
							else $direct_subscribe = false;
						} else {

							$limited_subscribe = false;
							$max_subscribe = 0;
							$direct_subscribe = true;
						}

						if ($can_subscribe) {
							require_once(_lms_ . '/lib/lib.course.php');
							$docebo_course = new DoceboCourse($id_course);

							$level_idst = &$docebo_course->getCourseLevel($id_course);
							if (count($level_idst) == 0 || $level_idst[1] == '')
								$level_idst =& $docebo_course->createCourseLevel($id_course);

							$waiting = 0;
							$user_subscribed = array();
							$user_waiting = array();

							if (!$direct_subscribe) $waiting = 1;

							// do the subscriptions
							$result = true;
							$this->db->start_transaction();
							while (list(, $id_user) = each($user_selected)) {
								if (!$limited_subscribe || $max_subscribe) {

									//$this->acl_man->addToGroup($level_idst[3], $id_user);
									$this->_addToCourseGroup($level_idst[3], $id_user);

									if ($model->subscribeUser($id_user, 3, $waiting, $date_begin_validity, $date_expire_validity)) {
										$max_subscribe--;
									} else {
										$this->acl_man->removeFromGroup($level_idst[3], $id_user);
										$result = false;
									}

								}
							}//End While
							$this->db->commit();


							// Save limit preference for admin
							if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
								$to_subscribe = count($user_selected);

								if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
									$user_pref->setPreference('user_subscribed_count', $subscribed_count + $to_subscribe);
								}
							}


							reset($user_selected);
							$send_alert = Get::req('send_alert', DOTY_INT, 0);
							//basically we will consider the alert as a checkbox, the initial state of the checkbox will be setted according to the alert status
							if (!empty($user_selected) && $send_alert) {
								require_once(_base_ . '/lib/lib.eventmanager.php');

								$array_subst = array(
									'[url]' => Get::site_url(),
									'[course]' => $course_info['name'],
									'[medium_time]' => $course_info['mediumTime'], //Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'))
									'[course_name]' => $course_info['name'],
									'[course_code]' => $course_info['code']
								);

								// message to user that is waiting
								$msg_composer = new EventMessageComposer();
								$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
								$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);
								$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

								// send message to the user subscribed
								createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', $user_selected, $msg_composer, $send_alert, true);
							}

						}

						$result = $result > 0 ? '_operation_successful' : '_operation_failed';
						Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date . '&res=' . $result);
					}
					break;
			}

			$model->loadSelectedUser($user_selected);

			$course_info = $this->model->getCourseInfoForSubscription();
			$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

			$this->render('level', array(
				'id_course' => $id_course,
				'id_edition' => $id_edition,
				'id_date' => $id_date,
				'model' => $model,
				'course_info' => $cman->getInfo($id_course, $id_edition, $id_date),
				'num_subscribed' => count($user_selected),
				'send_alert' => Get::req('send_alert', DOTY_INT, 0),
				'date_begin_validity' => $date_begin_validity,
				'date_expire_validity' => $date_expire_validity,
				'course_name' => $course_name
			));
		} else {
			if (isset($_GET['err']) && $_GET['err'] !== '')
				UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));

			$user_selector->show_user_selector = TRUE;
			$user_selector->show_group_selector = TRUE;
			$user_selector->show_orgchart_selector = TRUE;
			$user_selector->show_orgchart_simple_selector = TRUE;

			$user_alredy_subscribed = array();
			if (isset($_GET['load'])) {
				$user_selector->requested_tab = PEOPLEVIEW_TAB;
				$user_alredy_subscribed = $model->loadUserSelectorSelection();
				$user_selector->resetSelection($user_alredy_subscribed);
			}

			//find if the event manager is configured to send an alert or not in case of new subscription
			list($send_alert) = sql_fetch_row(sql_query("SELECT permission "
				. " FROM %adm_event_class as ec"
				. " JOIN %adm_event_manager as em"
				. " WHERE ec.idClass = em.idClass AND ec.class = 'UserCourseInserted' "));

			$course_info = $this->model->getCourseInfoForSubscription();
			$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

			$this->render('add', array(
				'id_course' => $id_course,
				'id_edition' => $id_edition,
				'id_date' => $id_date,
				'model' => $model,
				'course_info' => $cman->getInfo($id_course, $id_edition, $id_date),
				'user_selector' => $user_selector,
				'user_alredy_subscribed' => $user_alredy_subscribed,
				'send_alert' => ($send_alert == 'mandatory'),
				'course_name' => $course_name
			));
		}
	}

	public function ins()
	{
		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_lms_ . '/lib/lib.course.php');

		//Course info
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$id_date = Get::req('id_date', DOTY_INT, 0);

		if (isset($_POST['undo']))
			Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date);

		$model = new SubscriptionAlms($id_course, $id_edition, $id_date);

		$course_info = $model->getCourseInfoForSubscription();

		$date_begin_validity = Get::req('set_date_begin_validity', DOTY_STRING, "");
		$date_expire_validity = Get::req('set_date_expire_validity', DOTY_STRING, "");

		$can_subscribe = true;
		$subscribe_method = $course_info['subscribe_method'];
		if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
			$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
			$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

			if ($limited_subscribe == 'on')
				$limited_subscribe = true;
			else
				$limited_subscribe = false;

			if ($direct_subscribe == 'on')
				$direct_subscribe = true;
			else
				$direct_subscribe = false;
		} else {
			$limited_subscribe = false;
			$max_subscribe = 0;
			$direct_subscribe = true;
		}

		if ($can_subscribe) {
			require_once(_lms_ . '/lib/lib.course.php');
			$docebo_course = new DoceboCourse($id_course);

			$level_idst = &$docebo_course->getCourseLevel($id_course);

			if (count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst = &$docebo_course->createCourseLevel($id_course);

			$waiting = 0;
			$user_subscribed = array();
			$user_waiting = array();

			if (!$direct_subscribe)
				$waiting = 1;

			$user_selected = array();
			if ($_POST['subs']) {
				$subs = $_POST['subs'];
				$subs = explode(",", $_POST['subs']);
				foreach ($subs AS $sub) {
					list($user, $level) = explode(":", $sub);
					$user_selected[$user] = $level;
				}
			}

			$this->db->start_transaction();

			// To track event data
			$userModel = new UsermanagementAdm();
			$users = [];

			while (list($id_user, $lv_sel) = each($user_selected)) {
				if (!$limited_subscribe || $max_subscribe) {
					if ($lv_sel != 0) {
						//$this->acl_man->addToGroup($level_idst[$lv_sel], $id_user);
						$this->_addToCourseGroup($level_idst[$lv_sel], $id_user);

						if ($model->subscribeUser($id_user, $lv_sel, $waiting, $date_begin_validity, $date_expire_validity)) {
							$max_subscribe--;

							$data[] = [
								'user' => $userModel->getProfileData($id_user),
								'level' => $lv_sel,
								'waiting' => $waiting,
								'course_info' => $course_info,
								'date_begin_validity' => $date_begin_validity,
								'date_expire_validity' => $date_expire_validity,
							];
						} else
							$this->acl_man->removeFromGroup($level_idst[$lv_sel], $id_user);
					}
				}
			} //End While
			$this->db->commit();

			// SET ADD STANDARD (multiple) SUBSCRIPTION EVENT
			if ($data) {
				$event = new \appCore\Events\Core\Courses\CourseSubscriptionAddEvent();
				$event->setData($data);
				$event->setType('standard');
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionAddEvent::EVENT_NAME, $event);
			}

			// Save limit preference for admin
			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				$to_subscribe = count($user_selected);

				$admin_pref = new AdminPreference();
				$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
				if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
					$user_pref = new UserPreferences(Docebo::user()->getIdSt());
					$subscribed_count = $user_pref->getPreference('user_subscribed_count');
					$user_pref->setPreference('user_subscribed_count', $subscribed_count + $to_subscribe);
				}
			}


			reset($user_selected);
			$send_alert = Get::req('send_alert', DOTY_INT, 0);
			//basically we will consider the alert as a checkbox, the initial state of the checkbox will be setted according to the alert status
			if (!empty($user_selected) && $send_alert) {

				$array_subst = array(
					'[url]' => Get::site_url(),
					'[course]' => $course_info['name'],
					'[medium_time]' => $course_info['mediumTime'], //Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'))
					'[course_name]' => $course_info['name'],
					'[course_code]' => $course_info['code']
				);

				// message to user that is waiting
				require_once(_base_ . '/lib/lib.eventmanager.php');
				$msg_composer = new EventMessageComposer();
				$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
				$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);
				$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

				// send message to the user subscribed
				createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', array_keys($user_selected), $msg_composer, $send_alert, true);
			}

			$user_selected = array();
			if ($_POST['subs']) {
				$subs = $_POST['subs'];
				$subs = explode(",", $_POST['subs']);
				foreach ($subs AS $sub) {
					list($user_id, $level) = explode(":", $sub);
					$user_selected[$user] = $level;

					// Moderator notification
					$userData = $userModel->getProfileData($user_id);
					$username = str_replace('/', '', $userData->userid);

					$array_subst = array(
						'[url]' => Get::site_url(),
						'[firstname]' => $userData->firstname,
						'[lastname]' => $userData->lastname,
						'[course]' => $course_info['name'],
						'[username]' => $username,
					);

					// message to user that is odified
					$msg_composer = new EventMessageComposer();

					$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT_MODERATORS', false);
					$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT_MODERATORS', $array_subst);
					$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS_MODERATORS', $array_subst);

					$recipients = [];
					$sql = "SELECT idUser FROM learning_courseuser WHERE idCourse = $id_course AND (level = 6 OR level = 7)";
					$query = sql_query($sql);
					while ($row = sql_fetch_object($query)) {
						$recipients[] = $row->idUser;
					}

					createNewAlert(	'UserCourseInsertedModerators', 'directory', 'edit', '1', 'User '.$username.' was modified', $recipients, $msg_composer );
				}
			}
		}

		Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date . '&res=_operation_successful');
	}

	protected function _selectall()
	{
		$filter = array(
			'text' => Get::req('filter_text', DOTY_STRING, ""),
			'orgchart' => Get::req('filter_orgchart', DOTY_INT, 0),
			'descendants' => Get::req('filter_descendants', DOTY_INT, 0),
			'date_valid' => Get::req('filter_date_valid', DOTY_STRING, "")
		);
		$output = array_values($this->model->getSubscriptionsList($filter));
		echo $this->json->encode($output);
	}

	public function getlist()
	{
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall":
				{
					$this->_selectall();
					return;
				}
				break;
		}

		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'userid');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$dyn_fields = Get::req('_dyn_field', DOTY_MIXED, array());

		$filter = array(
			'text' => Get::req('filter_text', DOTY_STRING, ""),
			'orgchart' => Get::req('filter_orgchart', DOTY_INT, 0),
			'descendants' => Get::req('filter_descendants', DOTY_INT, 0),
			'date_valid' => Get::req('filter_date_valid', DOTY_STRING, ""),
			'show' => Get::req('filter_show', DOTY_INT, 0)
		);

		$total_user = $this->model->totalUser($filter);
		$array_user = $this->model->loadUser($start_index, $results, $sort, $dir, $filter);

		$list = array();
		$date_complete = array();
		foreach ($array_user as $value) {
			$is_valid_begin = $value['date_begin_validity'] && $value['date_begin_validity'] != '0000-00-00 00:00:00';
			$is_valid_expire = $value['date_expire_validity'] && $value['date_expire_validity'] != '0000-00-00 00:00:00';

			$del_url = 'ajax.adm_server.php?r=' . $this->link . '/del&id_user=' . $value['id_user']
				. '&id_course=' . $this->id_course . '&id_edition=' . $this->id_edition . '&id_date=' . $this->id_date;

			$record = array(
				'id' => $value['id_user'],
				'userid' => highlightText($value['userid'], $filter['text']),
				'fullname' => highlightText($value['fullname'], $filter['text']),
				'level' => $value['level_id'],
				'status' => $value['status_id'],
				'date_begin' => $is_valid_begin ? Format::date($value['date_begin_validity'], 'date') : false,
				'date_expire' => $is_valid_expire ? Format::date($value['date_expire_validity'], 'date') : false,
				'date_begin_timestamp' => $is_valid_begin ? Format::toTimestamp($value['date_begin_validity']) : 0,
				'date_expire_timestamp' => $is_valid_expire ? Format::toTimestamp($value['date_expire_validity']) : 0,
				'del' => $del_url
			);
			if (isset($value['overbooking'])) {
				$record['overbooking'] = $value['overbooking'];
				if ($value['overbooking']) $record['status'] = '' . _CUS_OVERBOOKING;
			}
			$list[(int)$value['id_user']] = $record;
			$date_complete[(int)$value['id_user']] = $value['date_complete'];
		}

		//custom fields
		$arr_idst = array_keys($list);
		$umodel = new UsermanagementAdm();
		$field_data = $umodel->getCustomFieldValues($arr_idst);
		foreach ($arr_idst as $idst) {
			$field_data[$idst]["date_complete"] = $date_complete[$idst];
			foreach ($dyn_fields as $dindex => $dvalue) {
				$content = "" . (isset($field_data[$idst][$dvalue]) ? $field_data[$idst][$dvalue] : "");
				if ($dvalue == 'level' && $content != "") $content = Lang::t('_DIRECTORY_' . $content, 'admin_directory');
				$list[$idst]['_dyn_field_' . $dindex] = $content;
			}
		}

        $event = new \appCore\Events\Core\User\UsersListEvent($list, $this->link,$this->id_course,$this->id_edition,$this->id_date );

        \appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\User\UsersListEvent::EVENT_NAME_LIST, $event);

        $list = $event->getUsersList();

        $result = array(
			'totalRecords' => $total_user,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($list),
			'records' => array_values($list)
		);

		$this->data = $this->json->encode($result);
		echo $this->data;
	}

	public function del()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		require_once(_lms_ . '/lib/lib.course.php');

		$id_user = Get::req('id_user', DOTY_INT, 0);
		$docebo_course = new DoceboCourse($this->id_course);

		$level_idst = &$docebo_course->getCourseLevel($this->id_course);
		$level = $this->model->getUserLevel($id_user);

		if ($this->model->delUser($id_user)) {
			if ($this->id_edition == 0 && $this->id_date == 0)
				$this->acl_man->removeFromGroup($level_idst[$level], $id_user);
			$res = array('success' => true);

			// SET REMOVE SUBSCRIPTION EVENT
			$event = new \appCore\Events\Core\Courses\CourseSubscriptionRemoveEvent();
			$userModel = new UsermanagementAdm();
			$user = $userModel->getProfileData($id_user);
			$event->setUser($user);
			$event->setLevel($level);
			$event->setCourse($docebo_course->course_info);
			\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionRemoveEvent::EVENT_NAME, $event);
		} else {
			$res = array('success' => false);
		}

		$this->data = $this->json->encode($res);
		echo $this->data;
	}

	public function multidel()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		require_once(_lms_ . '/lib/lib.course.php');

		$users = Get::req('users', DOTY_STRING, "");
		$docebo_course = new DoceboCourse($this->id_course);
		$output = array();

		if ($users == '') {
			$output = array('success' => true, 'count' => 0, 'total' => 0);
		} else {
			$level_idst = &$docebo_course->getCourseLevel($this->id_course);
			$list_users = explode(',', $users);
			$count = 0;
			$total = count($list_users);
			$deleted = array();
			foreach ($list_users as $id_user) {
				$level = $this->model->getUserLevel($id_user);
				if ($this->model->delUser($id_user)) {
					if ($this->id_edition == 0 && $this->id_date == 0)
						$this->acl_man->removeFromGroup($level_idst[$level], $id_user);
					$count++;
					$deleted[] = $id_user;
				} else {
					//...
				}
			}
			$output = array('success' => true, 'count' => $count, 'total' => $total, 'deleted' => $deleted);
		}

		$this->data = $this->json->encode($output);
		echo $this->data;
	}

	public function update()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$id_user = Get::req('id_user', DOTY_INT, 0);
		if ($id_user <= 0) {
			echo $this->json->encode(array('succes' => true));
			return;
		}

		//Update info
		$new_value = Get::req('new_value', DOTY_STRING, '');//DOTY_MIXED  DOTY_INT
		$old_value = Get::req('old_value', DOTY_STRING, '');//DOTY_MIXED  DOTY_INT
		$col = Get::req('col', DOTY_STRING, '');

		if ($new_value === $old_value) {
			echo $this->json->encode(array('succes' => true));
		} else {
			$userModel = new UsermanagementAdm();
			$user = $userModel->getProfileData($id_user);

			require_once(_lms_ . '/lib/lib.course.php');
			$docebo_course = new DoceboCourse($this->id_course);

			switch ($col) {
				case 'level':
					{
						require_once(_lms_ . '/lib/lib.course.php');

						$level_idst = &$docebo_course->getCourseLevel($this->id_course);
						if (count($level_idst) == 0 || $level_idst[1] == '')
							$level_idst = &$docebo_course->createCourseLevel($this->id_course);

						$level = $this->model->getUserLevel($id_user);
						if (!isset($level_idst[$level])) {
							echo $this->json->encode(array('succes' => false));
							break;
						}
						$this->acl_man->removeFromGroup($level_idst[$level], $id_user);

						if (!isset($level_idst[$new_value])) {
							echo $this->json->encode(array('succes' => false));
							break;
						}
						$this->acl_man->addToGroup($level_idst[$new_value], $id_user);

						if ($this->model->updateUserLevel($id_user, $new_value)) {
							echo $this->json->encode(array('succes' => true));

							// SET EDIT LEVEL SUBSCRIPTION EVENT
							$event = new \appCore\Events\Core\Courses\CourseSubscriptionEditLevelEvent();
							$event->setUser($user);
							$event->setLevel($level);
							$event->setCourse($docebo_course->course_info);
							\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionEditLevelEvent::EVENT_NAME, $event);
						} else
							echo $this->json->encode(array('succes' => false));
					}
					break;

				case 'status':
					{
						$status = $this->model->getUserStatusList();

						if (!isset($status[$new_value])) {
							echo $this->json->encode(array('succes' => false));
							break;
						}

						if ($this->model->updateUserStatus($id_user, $new_value)) {
							echo $this->json->encode(array('succes' => true));


							switch ((int)$new_value) {
								case _CUS_SUSPEND:

									require_once(Forma::inc(_base_ . '/lib/lib.eventmanager.php'));


									$uinfo = Docebo::aclm()->getUser($id_user, false);

									$userid = Docebo::aclm()->relativeId($uinfo[ACL_INFO_USERID]);

									$array_subst = array(
										'[url]' => Get::site_url(),
										'[firstname]' => $uinfo[ACL_INFO_FIRSTNAME],
										'[lastname]' => $uinfo[ACL_INFO_LASTNAME],
										'[username]' => $userid,
										'[course]' => $docebo_course->course_info['name']
									);

									// message to user that is odified
									$msg_composer = new EventMessageComposer();

									$msg_composer->setSubjectLangText('email', '_EVENT_COURSE_EVENT_SUSPENDED_USER_SBJ', false);
									$msg_composer->setBodyLangText('email', '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT', $array_subst);

									$msg_composer->setBodyLangText('sms', '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT_SMS', $array_subst);

									$acl_manager = \Docebo::user()->getAclManager();

									$permission_godadmin = $acl_manager->getGroupST(ADMIN_GROUP_GODADMIN);
									$permission_admin = $acl_manager->getGroupST(ADMIN_GROUP_ADMIN);

									$course_man = new Man_Course();
									$recipients = $course_man->getIdUserOfLevel($this->id_course, '6');

									$recipients = array_merge($recipients, $acl_manager->getGroupAllUser($permission_godadmin));
									$recipients = array_merge($recipients, $acl_manager->getGroupAllUser($permission_admin));

									createNewAlert('UserCourseSuspendedSuperAdmin', 'directory', 'edit', '1', 'User ' . $userid . ' was suspended',
										$recipients, $msg_composer);

									break;
								default:
							}
							// SET EDIT STATUS SUBSCRIPTION EVENT
							$event = new \appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent();
							$event->setUser($user);
							$event->setStatus(['id' => $new_value, 'name' => $status[$new_value]]);
							$event->setCourse($docebo_course->course_info);
							\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionEditStatusEvent::EVENT_NAME, $event);
						} else
							echo $this->json->encode(array('succes' => false));
					}
					break;
				case 'date_begin':
					{
						if ($this->model->updateUserDateBeginValidity($id_user, Format::dateDb($new_value, 'date')))
							echo $this->json->encode(array('succes' => true));
						else
							echo $this->json->encode(array('succes' => false));
					}
					break;
				case 'date_expire':
					{
						if ($this->model->updateUserDateExpireValidity($id_user, Format::dateDb($new_value, 'date')))
							echo $this->json->encode(array('succes' => true));
						else
							echo $this->json->encode(array('succes' => false));
					}
					break;
				default:
					{
						echo $this->json->encode(array('succes' => false));
					}
			}
		}
	}

	public function fastadd()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		//Filter on user
		$filter = str_replace('?query=', '', Get::req('filter', DOTY_MIXED, ''));

		//$this->model->setCourseData($id_course, $id_edition, $id_date);
		$list = $this->model->getFastSubscribeList($filter);

		$output_data = array();
		foreach ($list as $value) {
			$userid = $this->acl_man->relativeId($value['userid']);
			$name = $userid . ' (' . $value['firstname'] . ' ' . $value['lastname'] . ')';
			$row = array();
			$row['id'] = $value['idst'];
			$row['userid'] = $userid;
			$row['name'] = highlightText($name, $filter);
			$output_data[] = $row;
		}

		$output = array();
		$output['succes'] = true;
		$output['count'] = count($output_data); //this may be useless ...
		$output['users'] = $output_data;

		echo $this->json->encode($output);
	}

    public function fastsubscribe()
    {
        if (!$this->permissions['subscribe_course']) {
            $output = array('success' => false, 'message' => $this->_getMessage("no permission"));
            echo $this->json->encode($output);
            return;
        }
        if (!$this->checkAdminLimit()) {
            $output = array('success' => false, 'message' => Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'));
            echo $this->json->encode($output);
            return;
        }

        $id_user = Get::req('idst', DOTY_INT, 0); //user idst
        $userid = Get::req('userid', DOTY_STRING, ''); //user username
        $result = false;

        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            require_once(_base_ . '/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            $is_admin = true;
        }

        //adjust idst to subscribe
        if ($id_user <= 0) {
            $id_user = false;
            if ($userid != '') {
                $id_user = Docebo::aclm()->getUserST($userid);
            }
        }

        if ($id_user <= 0) {
            $output = array('success' => false, 'message' => $this->_getMessage("invalid user"));
            echo $this->json->encode($output);
            return;
        }

        if (isset($admin_users) && array_search($id_user, $admin_users, false) === false && Docebo::user()->getUserLevelId() !== ADMIN_GROUP_GODADMIN) {
            $output = array('success' => false, 'message' => $this->_getMessage("invalid user"));
            echo $this->json->encode($output);
            return;
        } elseif ($id_user) {
			$level = 3; //student level
			$waiting = false;
			$result = $this->model->subscribeUser($id_user, $level, $waiting);

			if ($result) {
				require_once(_lms_ . '/lib/lib.course.php');

				$docebo_course = new DoceboCourse($this->id_course);

				$level_idst = &$docebo_course->getCourseLevel($this->model->getIdCourse());

				if (count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst = &$docebo_course->createCourseLevel($this->id_course);

				//$this->acl_man->addToGroup($level_idst[$level], $id_user);
				$this->_addToCourseGroup($level_idst[$level], $id_user);

				// Save limit preference for admin
				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$admin_pref = new AdminPreference();
					$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
					if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
						$user_pref = new UserPreferences(Docebo::user()->getIdSt());
						$subscribed_count = $user_pref->getPreference('user_subscribed_count');
						$user_pref->setPreference('user_subscribed_count', $subscribed_count + 1);
					}
				}

				//check if we have selected send alert checkbox
				$send_alert = Get::req('send_alert', DOTY_INT, 0) > 0;

				// SET ADD FAST SUBSCRIPTION EVENT
				$event = new \appCore\Events\Core\Courses\CourseSubscriptionAddEvent();
				$userModel = new UsermanagementAdm();
				$user = $userModel->getProfileData($id_user);
				$event->setUser($user);
				$event->setType('fast');
				$event->setLevel($level);
				\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionAddEvent::EVENT_NAME, $event);

				require_once(_base_ . '/lib/lib.eventmanager.php');

				$course_info = $docebo_course->getAllInfo();
				if ($send_alert) {
					$uinfo = Docebo::aclm()->getUser($id_user, false);
					$array_subst = array(
						'[url]' => Get::site_url(),
						'[course]' => $course_info['name'],
						'[medium_time]' => $course_info['mediumTime'],//Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'),
						'[firstname]' => $uinfo[ACL_INFO_FIRSTNAME],
						'[lastname]' => $uinfo[ACL_INFO_LASTNAME],
						'[username]' => Docebo::aclm()->relativeId($uinfo[ACL_INFO_USERID])
					);

					// message to user that is waiting
					$msg_composer = new EventMessageComposer();
					$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
					$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);
					$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

					// send message to the user subscribed
					$users = array($id_user);
					createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', $users, $msg_composer, $send_alert, true);
				}

				// Moderator notification
				$username = str_replace('/', '', $user->userid);

				$array_subst = array(
					'[url]' => Get::site_url(),
					'[firstname]' => $user->firstname,
					'[lastname]' => $user->lastname,
					'[course]' => $course_info['name'],
					'[username]' => $username,
				);

				// message to user that is odified
				$msg_composer = new EventMessageComposer();

				$msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT_MODERATORS', false);
				$msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT_MODERATORS', $array_subst);
				$msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS_MODERATORS', $array_subst);

				$recipients = [];
				$sql = "SELECT idUser FROM learning_courseuser WHERE idCourse = ".$this->id_course." AND (level = 6 OR level = 7)";
				$query = sql_query($sql);
				while ($row = sql_fetch_object($query)) {
					$recipients[] = $row->idUser;
				}

				createNewAlert(	'UserCourseInsertedModerators', 'directory', 'edit', '1', 'User '.$username.' was modified', $recipients, $msg_composer );
			}

			$output = array('success' => $result);
			echo $this->json->encode($output);
		}
	}

	protected function _getBackLink()
	{
		if ($this->id_edition != 0) {
			return getBackUi('index.php?r=' . $this->link_edition . '/show&id_course=' . $this->id_course, Lang::t('_BACK', 'standard'));
		} elseif ($this->id_date != 0) {
			return getBackUi('index.php?r=' . $this->link_classroom . '/classroom&id_course=' . $this->id_course, Lang::t('_BACK', 'standard'));
		} else {
			return getBackUi('index.php?r=' . $this->link_course . '/show', Lang::t('_BACK', 'standard'));
		}
	}

	protected function _getJsArrayLevel()
	{
		$first = true;
		$output = '[';
		$list = $this->model->getUserLevelList();
		foreach ($list as $id_level => $level_translation) {
			if ($first)
				$first = false; else
				$output .= ',';
			$output .= '{"value":' . $this->json->encode($id_level) . ',"label":' . $this->json->encode($level_translation) . '}';
		}
		$output .= ']';
		return $output;
	}

	protected function _getJsArrayStatus()
	{
		$first = true;
		$output = '[';
		$list = $this->model->getUserStatusList();
		foreach ($list as $id_status => $status_translation) {
			if ($first)
				$first = false; else
				$output .= ', ';
			$output .= '{"value":' . $this->json->encode($id_status) . ',"label":' . $this->json->encode($status_translation) . '}';
		}
		$output .= ']';
		return $output;
	}

	public function multimod_dialog()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();

		if (Get::req('count_sel', DOTY_INT, 0) <= 0) {
			$output['success'] = true;
			$output['header'] = Lang::t('_MOD', 'subscribe') . '&nbsp;';
			$output['body'] = '<p>' . Lang::t('_EMPTY_SELECTION', 'admin_directory') . '</p>';
			echo $this->json->encode($output);
			return;
		}

		$sel_level = Form::getInputCheckbox('multimod_level_set', 'multimod_level_set', 1, false, '') . ' ';
		$sel_status = Form::getInputCheckbox('multimod_status_set', 'multimod_status_set', 1, false, '') . ' ';
		$sel_date_begin = Form::getInputCheckbox('multimod_date_begin_set', 'multimod_date_begin_set', 1, false, '') . ' ';
		$sel_date_expire = Form::getInputCheckbox('multimod_date_expire_set', 'multimod_date_expire_set', 1, false, '') . ' ';

		$sel_date_complete = Form::getInputCheckbox('multimod_date_complete_set', 'multimod_date_complete_set', 1, false, 'disabled') . ' ';

		$sel_date_begin_reset = Form::getInputCheckbox('multimod_date_begin_reset', 'multimod_date_begin_reset', 1, false, '') . ' ';
		$sel_date_expire_reset = Form::getInputCheckbox('multimod_date_expire_reset', 'multimod_date_expire_reset', 1, false, '') . ' ';

		$body = Form::openForm('multimod_dialog', 'ajax.adm_server.php?r=' . $this->link . '/multimod')
			. Form::getDropdown(Lang::t('_LEVEL', 'subscribe'), 'multimod_level', 'multimod_level', $this->model->getUserLevelList(), '', '', $sel_level)
			. Form::getDropdown(Lang::t('_STATUS', 'subscribe'), 'multimod_status', 'multimod_status', $this->model->getUserStatusList(), '', '', $sel_status)
			. Form::getDateField(Lang::t('_DATE_COMPLETE', 'subscribe'), 'multimod_date_complete', 'multimod_date_complete', '', false, false, '', '', $sel_date_complete)
			. Form::getDatefield(Lang::t('_DATE_BEGIN_VALIDITY', 'subscribe'), 'multimod_date_begin', 'multimod_date_begin', '', false, false, '', '', $sel_date_begin)
			. Form::getDateField(Lang::t('_DATE_EXPIRE_VALIDITY', 'subscribe'), 'multimod_date_expire', 'multimod_date_expire', '', false, false, '', '', $sel_date_expire)

			. Form::openFormLine() . $sel_date_begin_reset . '<p>' . Form::getLabel('multimod_date_begin_reset', Lang::t('_RESET', 'subscribe') . ': ' . Lang::t('_DATE_BEGIN_VALIDITY', 'subscribe')) . '</p>' . Form::closeFormLine()
			. Form::openFormLine() . $sel_date_expire_reset . '<p>' . Form::getLabel('multimod_date_expire_reset', Lang::t('_RESET', 'subscribe') . ': ' . Lang::t('_DATE_EXPIRE_VALIDITY', 'subscribe')) . '</p>' . Form::closeFormLine()

			. Form::getHidden('mod_dialog_users', 'users', '')
			. Form::getHidden('id_course', 'id_course', $this->id_course)
			. Form::getHidden('id_edition', 'id_edition', $this->id_edition)
			. Form::getHidden('id_date', 'id_date', $this->id_date)
			. Form::closeForm();

		$output['success'] = true;
		$output['header'] = Lang::t('_MOD', 'subscribe') . '&nbsp;';
		$output['body'] = $body;
		//$output['script'] = 'YAHOO.util.Dom.get("mod_dialog_users").value = DataTableSelector_subscribed_table.toString();';

		$output['__date_inputs'] = $GLOBALS['date_inputs'];

		echo $this->json->encode($output);
	}

	public function multimod()
	{
		if (!$this->permissions['subscribe_course']) {
			$output = array('success' => false, 'message' => $this->_getMessage("no permission"));
			echo $this->json->encode($output);
			return;
		}

		$output = array();

		$users = Get::req('users', DOTY_STRING, '');
		if ($users == '') {
			$output['success'] = false;
			$output['message'] = Lang::t('_NO_USER_SELECTED', 'subscribe');
		} else {
			$set_level = Get::req('multimod_level_set', DOTY_INT, 0);
			$set_status = Get::req('multimod_status_set', DOTY_INT, 0);
			$set_date_begin = Get::req('multimod_date_begin_set', DOTY_INT, 0);
			$set_date_expire = Get::req('multimod_date_expire_set', DOTY_INT, 0);
			$reset_date_begin = Get::req('multimod_date_begin_reset', DOTY_INT, 0);
			$reset_date_expire = Get::req('multimod_date_expire_reset', DOTY_INT, 0);

			if ($set_level <= 0 && $set_status <= 0 && $set_date_begin <= 0 && $set_date_expire <= 0 && $reset_date_begin <= 0 && $reset_date_expire <= 0) {
				$output['success'] = false;
				$output['message'] = UIFeedback::info($this->_getMessage('no options selected'), true);
			} else {

				$users_list = explode(',', $users);

				require_once(_lms_ . '/lib/lib.subscribe.php');
				$sman = new CourseSubscribe_Manager();

				$res1 = true;
				if ($set_level > 0) {
					$new_level = Get::req('multimod_level', DOTY_INT, -1);
					if ($new_level > 0)
						$res1 = $sman->updateUserLeveInCourse($users_list, $this->id_course, $new_level);
				}

				$res2 = true;
				if ($set_status > 0) {
					$new_date_complete = Get::req('multimod_date_complete', DOTY_STRING, "");
					$new_date_complete = Format::dateDb($new_date_complete, 'date');
					$new_status = Get::req('multimod_status', DOTY_INT, -999);
					if (in_array($new_status, array_keys($this->model->getUserStatusList()))) {
						$res2 = $sman->updateUserStatusInCourse($users_list, $this->id_course, $new_status, $new_date_complete);
					}

					foreach ($users_list as $user) {
						switch ((int)$new_status) {
							case _CUS_SUSPEND:

								require_once(_lms_ . '/lib/lib.course.php');
								$docebo_course = new DoceboCourse($this->id_course);

								require_once(Forma::inc(_base_ . '/lib/lib.eventmanager.php'));


								$uinfo = Docebo::aclm()->getUser($user, false);

								$userid = Docebo::aclm()->relativeId($uinfo[ACL_INFO_USERID]);

								$array_subst = array(
									'[url]' => Get::site_url(),
									'[firstname]' => $uinfo[ACL_INFO_FIRSTNAME],
									'[lastname]' => $uinfo[ACL_INFO_LASTNAME],
									'[username]' => $userid,
									'[course]' => $docebo_course->course_info['name']
								);

								// message to user that is odified
								$msg_composer = new EventMessageComposer();

								$msg_composer->setSubjectLangText('email', '_EVENT_COURSE_EVENT_SUSPENDED_USER_SBJ', false);
								$msg_composer->setBodyLangText('email', '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT', $array_subst);

								$msg_composer->setBodyLangText('sms', '_EVENT_COURSE_EVENT_SUSPENDED_USER_TEXT_SMS', $array_subst);

								$acl_manager = \Docebo::user()->getAclManager();

								$permission_godadmin = $acl_manager->getGroupST(ADMIN_GROUP_GODADMIN);
								$permission_admin = $acl_manager->getGroupST(ADMIN_GROUP_ADMIN);

								$course_man = new Man_Course();
								$recipients = $course_man->getIdUserOfLevel($this->id_course, '6');

								$recipients = array_merge($recipients, $acl_manager->getGroupAllUser($permission_godadmin));
								$recipients = array_merge($recipients, $acl_manager->getGroupAllUser($permission_admin));

								createNewAlert('UserCourseSuspendedSuperAdmin', 'directory', 'edit', '1', 'User ' . $userid . ' was suspended',
									$recipients, $msg_composer);

								break;
							default:
						}
					}
				}

				$res3 = true;
				if ($set_date_begin > 0) {
					$new_date_begin = Get::req('multimod_date_begin', DOTY_STRING, "");
					$res3 = $sman->updateUserDateBeginValidityInCourse($users_list, $this->id_course, Format::dateDb($new_date_begin, 'date'));
				}

				$res4 = true;
				if ($set_date_expire > 0) {
					$new_date_expire = Get::req('multimod_date_expire', DOTY_STRING, "");
					$res4 = $sman->updateUserDateExpireValidityInCourse($users_list, $this->id_course, Format::dateDb($new_date_expire, 'date'));
				}

				$res5 = true;
				if ($reset_date_begin > 0) {
					$res5 = $sman->resetValidityDateBegin($this->id_course, 0, $users_list);//$this->id_edition
				}

				$res6 = true;
				if ($reset_date_expire > 0) {
					$res6 = $sman->resetValidityDateExpire($this->id_course, 0, $users_list);//$this->id_edition
				}

				$success = $res1 && $res2 && $res3 && $res4 && $res5 && $res6;
				$output['success'] = $success;
				if (!$success) {
					$message = "";
					if (!$res1)
						$message .= 'Unable to change level;'; //TO DO: make translation
					if (!$res2)
						$message .= 'Unable to change status;'; //TO DO: make translation
					if (!$res3)
						$message .= 'Unable to change date begin;'; //TO DO: make translation
					if (!$res4)
						$message .= 'Unable to change date expire;'; //TO DO: make translation
					$output['message'] = $message;
				} else {
					// SET EDIT MULTI SUBSCRIPTION EVENT
					$event = new \appCore\Events\Core\Courses\CourseSubscriptionEditEvent();

					$users = [];
					foreach ($users_list as $idst) {
						$query = "SELECT * FROM core_user as u WHERE u.idst=" . (int)$idst;
						$res = $this->db->query($query);
						$users[] = $this->db->fetch_obj($res);
					}
					$event->setUsers($users);
					if ($set_level > 0) {
						$event->setLevel($new_level);
					}
					if ($set_status > 0 && $new_status) {
						$status_list = $this->model->getUserStatusList();
						$event->setStatus(['id' => $new_status, 'name' => $status_list[$new_status]]);
					}
					\appCore\Events\DispatcherManager::dispatch(\appCore\Events\Core\Courses\CourseSubscriptionEditEvent::EVENT_NAME, $event);
				}
			}
		}

		echo $this->json->encode($output);
	}

	public function multiplesubscription()
	{
		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		} else if (!$this->checkAdminLimit()) {
			$this->render('invalid', array(
				'message' => Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_base_ . '/lib/lib.form.php');
		require_once(_base_ . '/lib/lib.userselector.php');
		require_once(_lms_ . '/lib/lib.course.php');

		$course_selector = new Selector_Course();
		//$user_selector = new Module__Directory();
		$user_selector = new UserSelector();

		//Step info
		$step = Get::req('step', DOTY_INT, 1);

		$model = new SubscriptionAlms();

		if (isset($_POST['okselector']) || isset($_POST['next']))
			$step++;

		if (isset($_POST['back']))
			$step--;

		if (isset($_POST['undo']) || isset($_POST['cancelselector']))
			$step = 0;

		switch ($step) {
			case '0':
				Util::jump_to('index.php?r=' . $this->link_course . '/show');
				break;

			case '1':
				if (isset($_POST['back'])) {
					$course_selector->parseForState($_POST);

					$course_selection = urlencode(Util::serialize($course_selector->getSelection()));

					$user_selector->addFormInfo(Form::getHidden('course_selection', 'course_selection', $course_selection));
				}

				$user_selector->show_user_selector = TRUE;
				$user_selector->show_group_selector = TRUE;
				$user_selector->show_orgchart_selector = TRUE;
				$user_selector->show_orgchart_simple_selector = TRUE;

				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					require_once(_base_ . '/lib/lib.preference.php');
					$adminManager = new AdminPreference();
					$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
					$admin_users = $this->acl_man->getAllUsersFromIdst($admin_tree);

					$user_selector->setUserFilter('user', $admin_users);
					$user_selector->setUserFilter('group', $admin_tree);
				}

				if (isset($_GET['load']))
					$user_selector->resetSelection(array());

				if (isset($_POST['user_selection']))
					$user_selector->resetSelection(Util::unserialize(urldecode($_POST['user_selection'])));

				$user_selector->setUserFilter('exclude', array($this->acl_man->getAnonymousId()));

				$this->render('multiple_subscription_1', array('model' => $model, 'user_selector' => $user_selector));
				break;

			case '2':
				$id_cat = Get::req('id_cat', DOTY_INT, 0);

				if (isset($_POST['okselector'])) {
					$_selection = $user_selector->getSelection($_POST);
					$acl_man = Docebo::user()->getAclManager();
					$user_selected = $acl_man->getAllUsersFromSelection($_selection);//$acl_man->getAllUsersFromIdst($_selection);
					//$user_selected = $user_selector->getSelection($_POST);

					if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
						require_once(_base_ . '/lib/lib.preference.php');
						$adminManager = new AdminPreference();
						$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
						$admin_users = $this->acl_man->getAllUsersFromIdst($admin_tree);

						$user_selected = array_intersect($user_selected, $admin_users);


						if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
							$to_subscribe = count($user_selected);

							$admin_pref = new AdminPreference();
							$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
							if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
								$user_pref = new UserPreferences(Docebo::user()->getIdSt());
								$subscribed_count = $user_pref->getPreference('user_subscribed_count');
								if ($subscribed_count + $to_subscribe > $pref['admin_rules.max_course_subscribe']) {
									$this->render('invalid', array(
										'message' => Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
										'back_url' => 'index.php?r=' . $this->link_course . '/show'
									));
									return;
								}
							}
						}
					}

					if (count($user_selected) == 0)
						Util::jump_to('index.php?r=' . $this->link . '/multiplesubscription');

					$model->setUserData(urlencode(Util::serialize($user_selected)));
				}
				$course_selector->parseForState($_POST);
				if (isset($_POST['course_selection']))
					$course_selector->resetSelection(Util::unserialize(urldecode($_POST['course_selection'])));
				elseif (isset($_POST['okselector']))
					$course_selector->resetSelection(array());

				$user_selection = (isset($_POST['user_selection']) ? $_POST['user_selection'] : $model->getUserData());

				$this->render('multiple_subscription_2', array('model' => $model, 'id_cat' => $id_cat, 'course_selector' => $course_selector, 'user_selection' => $user_selection));
				break;

			case '3':
				$user_selection = $_POST['user_selection'];

				if (isset($_POST['course_selection']))
					$course_selection = $_POST['course_selection'];
				else {
					$course_selector->parseForState($_POST);
					$course_selection = urlencode(Util::serialize($course_selector->getSelection()));
				}

				$control = $model->controlCoursesWithEdition($course_selector->getSelection());

				if ($control && !isset($_POST['edition_selected'])) {
					$this->render('multiple_subscription_2_2', array('model' => $model, 'course_selection' => $course_selection, 'user_selection' => $user_selection, 'courses' => $course_selector->getSelection()));
				} else {
					$courses = Util::unserialize(urldecode($course_selection));
					$edition_selected = array();

					foreach ($courses as $id_course)
						if (isset($_POST['edition_' . $id_course]))
							$edition_selected[$id_course] = (int)$_POST['edition_' . $id_course];

					$model->loadSelectedUser(Util::unserialize(urldecode($user_selection)));

					$this->render('multiple_subscription_3', array('model' => $model, 'course_selection' => $course_selection, 'user_selection' => $user_selection, 'edition_selected' => urlencode(Util::serialize($edition_selected))));
				}
				break;

			case '4':
				//Start case 4
				require_once(_lms_ . '/lib/lib.course.php');

				if (isset($_POST['undo']))
					Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date);

				$user_selection = $_POST['user_selection'];
				$course_selection = $_POST['course_selection'];
				$edition_selected = $_POST['edition_selected'];

				$user_selected = Util::unserialize(urldecode($user_selection));
				$course_selected = Util::unserialize(urldecode($course_selection));
				$edition_selected = Util::unserialize(urldecode($edition_selected));

				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
					$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
					$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

					if ($limited_subscribe == 'on')
						$limited_subscribe = true;
					else
						$limited_subscribe = false;

					if ($direct_subscribe == 'on')
						$direct_subscribe = true;
					else
						$direct_subscribe = false;
				} else {
					$limited_subscribe = false;
					$max_subscribe = 0;
					$direct_subscribe = true;
				}

				$just_subscribed_count = 0;
				foreach ($course_selected as $id_course) {
					$course_man = new Man_Course();

					$course_info = $course_man->getCourseInfo($id_course);

					if ($course_info['course_type'] !== 'classroom' && $course_info['course_edition'] == 0) {
						$model = new SubscriptionAlms($id_course);

						$course_info = $model->getCourseInfoForSubscription();

						$can_subscribe = true;
						$max_num_subscribe = $course_info['max_num_subscribe'];
						$subscribe_method = $course_info['subscribe_method'];

						if ($can_subscribe) {
							require_once(_lms_ . '/lib/lib.course.php');

							$docebo_course = new DoceboCourse($id_course);

							$level_idst = &$docebo_course->getCourseLevel($id_course);
							if (count($level_idst) == 0 || $level_idst[1] == '')
								$level_idst = &$docebo_course->createCourseLevel($id_course);

							$waiting = 0;
							$user_subscribed = array();
							$user_waiting = array();

							if (!$direct_subscribe)
								$waiting = 1;

							$user_selected = array();
							if ($_POST['subs']) {
								$subs = $_POST['subs'];
								$subs = explode(",", $_POST['subs']);
								foreach ($subs AS $sub) {
									list($user, $level) = explode(":", $sub);
									$user_selected[$user] = $level;
								}
							}

							while (list($id_user, $lv_sel) = each($user_selected)) {
								if (!$limited_subscribe || $max_subscribe) {
									if ($lv_sel != 0) {
										//$this->acl_man->addToGroup($level_idst[$lv_sel], $id_user);
										$this->_addToCourseGroup($level_idst[$lv_sel], $id_user);

										if ($model->subscribeUser($id_user, $lv_sel, $waiting)) {
											$max_subscribe--;
											$just_subscribed_count++;
										} else
											$this->acl_man->removeFromGroup($level_idst[$lv_sel], $id_user);
									}
								}
							}//End While

							reset($_POST['user_level_sel']);
						}
					} elseif (isset($edition_selected[$id_course])) {
						if ($course_info['course_type'] === 'classroom') {
							$model_t = new SubscriptionAlms($id_course, false, $edition_selected[$id_course]);

							$course_info = $model_t->getCourseInfoForSubscription();

							$can_subscribe = true;
							$max_num_subscribe = $course_info['max_num_subscribe'];
							$subscribe_method = $course_info['subscribe_method'];

							if ($can_subscribe) {
								require_once(_lms_ . '/lib/lib.course.php');

								$docebo_course = new DoceboCourse($id_course);

								$level_idst = &$docebo_course->getCourseLevel($id_course);

								if (count($level_idst) == 0 || $level_idst[1] == '')
									$level_idst = &$docebo_course->createCourseLevel($id_course);

								$waiting = 0;
								$user_subscribed = array();
								$user_waiting = array();

								if (!$direct_subscribe)
									$waiting = 1;

								while (list($id_user, $lv_sel) = each($_POST['user_level_sel'])) {
									if (!$limited_subscribe || $max_subscribe) {
										if ($lv_sel != 0) {
											//$this->acl_man->addToGroup($level_idst[$lv_sel], $id_user);
											$this->_addToCourseGroup($level_idst[$lv_sel], $id_user);

											if ($model_t->subscribeUser($id_user, $lv_sel, $waiting)) {
												$max_subscribe--;
												$just_subscribed_count++;
											} else
												$this->acl_man->removeFromGroup($level_idst[$lv_sel], $id_user);
										}
									}
								}//End While

								reset($_POST['user_level_sel']);
							}
						} else {
							$model_t = new SubscriptionAlms($id_course, $edition_selected[$id_course], false);

							$course_info = $model_t->getCourseInfoForSubscription();

							$can_subscribe = true;
							$max_num_subscribe = $course_info['max_num_subscribe'];
							$subscribe_method = $course_info['subscribe_method'];

							if ($can_subscribe) {
								require_once(_lms_ . '/lib/lib.course.php');

								$docebo_course = new DoceboCourse($id_course);

								$level_idst = &$docebo_course->getCourseLevel($id_course);

								if (count($level_idst) == 0 || $level_idst[1] == '')
									$level_idst = &$docebo_course->createCourseLevel($id_course);

								$waiting = 0;
								$user_subscribed = array();
								$user_waiting = array();

								if (!$direct_subscribe)
									$waiting = 1;

								while (list($id_user, $lv_sel) = each($_POST['user_level_sel'])) {
									if (!$limited_subscribe || $max_subscribe) {
										if ($lv_sel != 0) {
											//$this->acl_man->addToGroup($level_idst[$lv_sel], $id_user);
											$this->_addToCourseGroup($level_idst[$lv_sel], $id_user);

											if ($model_t->subscribeUser($id_user, $lv_sel, $waiting)) {
												$max_subscribe--;
												$just_subscribed_count++;
											} else
												$this->acl_man->removeFromGroup($level_idst[$lv_sel], $id_user);
										}
									}
								}//End While

								reset($_POST['user_level_sel']);
							}
						}
					}
				}

				// Save limit preference for admin
				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$admin_pref = new AdminPreference();
					$pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
					if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
						$user_pref = new UserPreferences(Docebo::user()->getIdSt());
						$subscribed_count = $user_pref->getPreference('user_subscribed_count');
						$user_pref->setPreference('user_subscribed_count', $subscribed_count + $just_subscribed_count);
					}
				}

				Util::jump_to('index.php?r=' . $this->link_course . '/show&res=_operation_successful');
				//End case 4
				break;
		}
	}

	public function import_csv()
	{
		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_base_ . '/lib/lib.form.php');

		//Step info
		$step = Get::req('step', DOTY_INT, 1);

		if (isset($_POST['next']))
			$step++;

		if (isset($_POST['back']))
			$step--;

		if (isset($_POST['undo']))
			$step = 0;

		switch ($step) {
			case '0':
				Util::jump_to('index.php?r=' . $this->link_course . '/show');
				break;

			case '1':

				$course_info = $this->model->getCourseInfoForSubscription();
				$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

				$params = array('table' => $tb,
					'id_course' => $this->id_course,
					'id_date' => $this->id_date,
					'id_edition' => $this->id_edition,
					'course_name' => $course_name,
					'model' => $this->model);

				$this->render('import_csv_step_1', $params);
				break;

			case '2':
				require_once(_lms_ . '/lib/lib.course.php');
				require_once(_base_ . '/lib/lib.upload.php');
				require_once(_adm_ . '/lib/lib.import.php');

				$separator = Get::req('import_separator', DOTY_MIXED, ',');
				$first_row_header = Get::req('import_first_row_header', DOTY_BOOL, false);
				$import_charset = Get::req('import_charset', DOTY_MIXED, 'UTF-8');

				$docebo_course = new DoceboCourse($this->id_course);

				$level_idst = &$docebo_course->getCourseLevel($this->id_course);

				if (count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst = &$docebo_course->createCourseLevel($this->id_course);

				$back_url = 'index.php?r=' . $this->link . '/show&id_course=' . $this->id_course . '&id_edition=' . $this->id_edition . '&id_date=' . $this->id_date;

				// ----------- file upload -----------------------------------------
				if ($_FILES['file_import']['name'] == '') {
					$_SESSION['last_error'] = Lang::t('_FILEUNSPECIFIED');
					jumpTo($back_url . '&err=_file_unspecified');
				} else {
					$path = '/appCore/';
					$savefile = mt_rand(0, 100) . '_' . time() . '_' . $_FILES['file_import']['name'];

					if (!file_exists($GLOBALS['where_files_relative'] . $path . $savefile)) {
						sl_open_fileoperations();

						if (!sl_upload($_FILES['file_import']['tmp_name'], $path . $savefile)) {
							sl_close_fileoperations();
							$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD', 'subscribe');
							jumpTo($back_url . '&err=_err_upload');
						}

						sl_close_fileoperations();
					} else {
						$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD', 'subscribe');
						jumpTo($back_url . '&err=_err_upload');
					}
				}

				$src = new DeceboImport_SourceCSV(array('filename' => $GLOBALS['where_files_relative'] . $path . $savefile,
					'separator' => $separator,
					'first_row_header' => $first_row_header,
					'import_charset' => $import_charset));

				$src->connect();

				$user_added = 0;
				$user_error = 0;
				$user_not_needed = 0;

				$id_user_added = array();

				$counter = 0;

				$course_info = $this->model->getCourseInfoForSubscription();

				$can_subscribe = true;
				$max_num_subscribe = $course_info['max_num_subscribe'];
				$subscribe_method = $course_info['subscribe_method'];

				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
					$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
					$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

					if ($limited_subscribe == 'on')
						$limited_subscribe = true;
					else
						$limited_subscribe = false;

					if ($direct_subscribe == 'on')
						$direct_subscribe = true;
					else
						$direct_subscribe = false;
				} else {
					$limited_subscribe = false;
					$max_subscribe = 0;
					$direct_subscribe = true;
				}

				if (is_array($row = $src->get_first_row()) && !empty($row)) {
					$user_info = $this->acl_man->getUser(false, $row[0]);

					if ($user_info) {
						$id_user = $user_info[ACL_INFO_IDST];

						if ($can_subscribe) {
							require_once(_lms_ . '/lib/lib.course.php');
							$docebo_course = new DoceboCourse($this->id_course);

							$level_idst = &$docebo_course->getCourseLevel($this->id_course);

							if (count($level_idst) == 0 || $level_idst[1] == '')
								$level_idst = &$docebo_course->createCourseLevel($this->id_course);

							if (!$direct_subscribe)
								$waiting = 1;
							else
								$waiting = 0;

							if (!$limited_subscribe || $max_subscribe) {
								//$this->acl_man->addToGroup($level_idst['3'], $id_user);
								$this->_addToCourseGroup($level_idst['3'], $id_user);

								if ($this->model->subscribeUser($id_user, '3', $waiting)) {
									$user_added++;
									$id_user_added[$counter]['id_user'] = $row[0];
									$id_user_added[$counter]['status'] = '_CORRECT';
									$user_subscribed[] = $id_user;

									$max_subscribe--;
								} else {
									$query = "SELECT COUNT(*)"
										. " FROM " . $GLOBALS['prefix_lms'] . "_courseuser"
										. " WHERE idUser = '" . $id_user . "'"
										. " AND idCourse = '" . $this->id_course . "'";

									list($control) = sql_fetch_row(sql_query($query));

									if ($control) {
										$user_not_needed++;
										$id_user_added[$counter]['id_user'] = $row[0];
										$id_user_added[$counter]['status'] = '_NOT_NEEDED';
									} else {
										$user_error++;
										$id_user_added[$counter]['id_user'] = $row[0];
										$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';

										$this->acl_man->removeFromGroup($level_idst['3'], $id_user);
									}
								}
							}
						}
					} else {
						$user_error++;
						$id_user_added[$counter]['id_user'] = $row[0];
						$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
					}

					$counter++;
				}

				while (is_array($row = $src->get_next_row()) && !empty($row)) {
					$user_info = $this->acl_man->getUser(false, $row[0]);

					if ($user_info) {
						$id_user = $user_info[ACL_INFO_IDST];

						if ($can_subscribe) {
							require_once(_lms_ . '/lib/lib.course.php');
							$docebo_course = new DoceboCourse($this->id_course);

							$level_idst = &$docebo_course->getCourseLevel($this->id_course);

							if (count($level_idst) == 0 || $level_idst[1] == '')
								$level_idst = &$docebo_course->createCourseLevel($this->id_course);

							if (!$direct_subscribe)
								$waiting = 1;
							else
								$waiting = 0;

							if (!$limited_subscribe || $max_subscribe) {
								//$this->acl_man->addToGroup($level_idst['3'], $id_user);
								$this->_addToCourseGroup($level_idst['3'], $id_user);

								if ($this->model->subscribeUser($id_user, '3', $waiting)) {
									$user_added++;
									$id_user_added[$counter]['id_user'] = $row[0];
									$id_user_added[$counter]['status'] = '_CORRECT';
									$user_subscribed[] = $id_user;

									$max_subscribe--;
								} else {
									$query = "SELECT COUNT(*)"
										. " FROM " . $GLOBALS['prefix_lms'] . "_courseuser"
										. " WHERE idUser = '" . $id_user . "'"
										. " AND idCourse = '" . $this->id_course . "'";

									list($control) = sql_fetch_row(sql_query($query));

									if ($control) {
										$user_not_needed++;
										$id_user_added[$counter]['id_user'] = $row[0];
										$id_user_added[$counter]['status'] = '_NOT_NEEDED';
									} else {
										$user_error++;
										$id_user_added[$counter]['id_user'] = $row[0];
										$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';

										$this->acl_man->removeFromGroup($level_idst['3'], $id_user);
									}
								}
							}
						}
					} else {
						$user_error++;
						$id_user_added[$counter]['id_user'] = $row[0];
						$id_user_added[$counter]['status'] = '_OPERATION_FAILURE';
					}

					$counter++;
				}

				$src->close();
				unset($row);

				require_once(_base_ . '/lib/lib.table.php');

				$type_h = array('align_center', 'align_center', 'align_center', 'align_center');
				$cont_h = array(Lang::t('_USERNAME', 'subscribe'), Lang::t('_LASTNAME', 'subscribe'), Lang::t('_FIRSTNAME', 'subscribe'), Lang::t('_INSER_STATUS', 'subscribe'));

				$tb = new Table(false, Lang::t('_USER_SUBSCRIBED', 'subscribe'), Lang::t('_USER_SUBSCRIBED', 'subscribe'));
				$tb->addHead($cont_h, $type_h);

				while (list(, $id_user_added_detail) = each($id_user_added)) {
					$cont = array();

					$user_info = $this->acl_man->getUser(false, $id_user_added_detail['id_user']);

					$cont[] = $this->acl_man->relativeId($user_info[ACL_INFO_USERID]);
					$cont[] = $user_info[ACL_INFO_FIRSTNAME];
					$cont[] = $user_info[ACL_INFO_LASTNAME];
					$cont[] = Lang::t($id_user_added_detail['status'], 'subscribe');

					$tb->addBody($cont);
				}

				sl_open_fileoperations();

				sl_unlink($GLOBALS['where_files_relative'] . $path . $savefile);

				sl_close_fileoperations();

				$course_info = $this->model->getCourseInfoForSubscription();
				$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

				$params = array('table' => $tb,
					'id_course' => $this->id_course,
					'id_date' => $this->id_date,
					'id_edition' => $this->id_edition,
					'course_name' => $course_name,
					'back_link' => getBackUi('index.php?r=' . $this->link . '/show&id_course=' . $this->id_course . '&id_edition=' . $this->id_edition . '&id_date=' . $this->id_date, Lang::t('_BACK', 'subscribe'))
				);

				$this->render('import_csv_step_2', $params);
				break;
		}
	}

	public function import_course()
	{
		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_lms_ . '/lib/lib.course.php');

		$course_selector = new Selector_Course();
		$course_selector->parseForState($_POST);

		if (isset($_POST['undo']))
			Util::jump_to('index.php?r=' . $this->link . '/show&amp;id_course=' . $this->model->getIdCourse());

		if (isset($_POST['import'])) {
			$course_selected = $course_selector->getSelection();

			if (count($course_selected) == 0)
				Util::jump_to('index.php?r=' . $this->link . '/import_course&amp;load=1&amp;id_course=' . $this->model->getIdCourse() . '&err=_no_course_sel');

			require_once(_lms_ . '/lib/lib.course.php');

			$docebo_course = new DoceboCourse($this->id_course);

			$level_idst = &$docebo_course->getCourseLevel($this->id_course);
			if (count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst = &$docebo_course->createCourseLevel($this->id_course);

			$query = "SELECT idUser, MIN(level) AS level"
				. " FROM %lms_courseuser"
				. " WHERE idCourse IN (" . implode(',', $course_selected) . ")"
				. " GROUP BY idUser";

			$result = sql_query($query);

			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
				$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
				$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

				if ($limited_subscribe == 'on')
					$limited_subscribe = true;
				else
					$limited_subscribe = false;

				if ($direct_subscribe == 'on')
					$direct_subscribe = true;
				else
					$direct_subscribe = false;
			} else {
				$limited_subscribe = false;
				$max_subscribe = 0;
				$direct_subscribe = true;
			}

			require_once(_lms_ . '/lib/lib.course.php');

			$docebo_course = new DoceboCourse($this->id_course);

			$level_idst = &$docebo_course->getCourseLevel($this->id_course);

			if (count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst = &$docebo_course->createCourseLevel($this->id_course);

			$waiting = 0;

			if (!$direct_subscribe)
				$waiting = 1;

			while (list($id_user, $level) = sql_fetch_row($result)) {
				if (!$limited_subscribe || $max_subscribe)
					if ($this->model->subscribeUser($id_user, $level, $waiting)) {
						//$this->acl_man->addToGroup($level_idst[$level], $id_user);
						$this->_addToCourseGroup($level_idst[$level], $id_user);
						$max_subscribe--;
					}
			}

			Util::jump_to('index.php?r=' . $this->link . '/show&amp;id_course=' . $this->model->getIdCourse() . '&res=_import_ok');
		} else {
			$id_cat = Get::req('id_cat', DOTY_INT, 0);

			if (isset($_GET['load']) && $_GET['load'] == 1)
				$course_selector->resetSelection(array());

			if (isset($_GET['err']) && $_GET['err'] !== '')
				UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));

			$course_info = $this->model->getCourseInfoForSubscription();
			$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

			$this->render('import_course', array('model' => $this->model,
				'id_cat' => $id_cat,
				'course_selector' => $course_selector,
				'course_name' => $course_name,
				'id_course' => $this->id_course,
				'id_edition' => $this->id_edition,
				'id_date' => $this->id_date));
		}
	}


	public function copy_course()
	{
		$users = Get::req('users', DOTY_STRING, "");
		$move = Get::req('move', DOTY_STRING, "");

		if (!$this->permissions['subscribe_course']) {
			$this->render('invalid', array(
				'message' => $this->_getErrorMessage('no permission'),
				'back_url' => 'index.php?r=' . $this->link_course . '/show'
			));
			return;
		}

		require_once(_lms_ . '/lib/lib.course.php');

		$course_selector = new Selector_Course();
		$course_selector->parseForState($_POST);

		if (isset($_POST['undo']))
			Util::jump_to('index.php?r=' . $this->link . '/show&amp;id_course=' . $this->model->getIdCourse());

		if (isset($_POST['copy'])) {
			$course_selected = $course_selector->getSelection();

			if (count($course_selected) == 0)
				Util::jump_to('index.php?r=' . $this->link . '/copy_course&amp;load=1&amp;id_course=' . $this->model->getIdCourse() . '&err=_no_course_sel');

			require_once(_lms_ . '/lib/lib.course.php');

			foreach ($course_selected as $id_course) {

				$docebo_course = new DoceboCourse($id_course);

				$level_idst = &$docebo_course->getCourseLevel($id_course);
				if (count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst = &$docebo_course->createCourseLevel($id_course);

				$query = "SELECT idUser, MIN(level) AS level"
					. " FROM %lms_courseuser"
					. " WHERE idUser IN (" . $users . ")"
					. " GROUP BY idUser";

				$result = sql_query($query);

				if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
					$limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
					$max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
					$direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

					if ($limited_subscribe == 'on')
						$limited_subscribe = true;
					else
						$limited_subscribe = false;

					if ($direct_subscribe == 'on')
						$direct_subscribe = true;
					else
						$direct_subscribe = false;
				} else {
					$limited_subscribe = false;
					$max_subscribe = 0;
					$direct_subscribe = true;
				}

				require_once(_lms_ . '/lib/lib.course.php');

				$docebo_course = new DoceboCourse($id_course);

				$level_idst = &$docebo_course->getCourseLevel($id_course);

				if (count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst = &$docebo_course->createCourseLevel($id_course);

				$waiting = 0;

				if (!$direct_subscribe)
					$waiting = 1;

				$old_id_course = $_GET['id_course'];
				$_GET['id_course'] = $id_course;
				$course = new self();

				while (list($id_user, $level) = sql_fetch_row($result)) {
					if (!$limited_subscribe || $max_subscribe)
						if ($course->model->subscribeUser($id_user, $level, $waiting)) {
							//$this->acl_man->addToGroup($level_idst[$level], $id_user);
							$course->_addToCourseGroup($level_idst[$level], $id_user);
							$max_subscribe--;
						}
				}

				if ($move) {
					$db = DbConn::getInstance();
					$arr_users = explode(",", $users);
					$re = $db->query("DELETE FROM learning_courseuser
						WHERE idUser IN ( " . implode(',', $arr_users) . " ) AND idCourse = '" . $old_id_course . "'");
				}
			}

			Util::jump_to('index.php?r=' . $this->link . '/show&amp;id_course=' . $this->model->getIdCourse() . '&res=_copy_ok');

			////////////////////////////////
		} else {

			$id_cat = Get::req('id_cat', DOTY_INT, 0);

			if (isset($_GET['load']) && $_GET['load'] == 1)
				$course_selector->resetSelection(array());

			if (isset($_GET['err']) && $_GET['err'] !== '')
				UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));

			$course_info = $this->model->getCourseInfoForSubscription();
			$course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

			$this->render('copy_course', array('model' => $this->model,
				'id_cat' => $id_cat,
				'users' => $users,
				'move' => $move,
				'course_selector' => $course_selector,
				'course_name' => $course_name,
				'id_course' => $this->id_course,
				'id_edition' => $this->id_edition,
				'id_date' => $this->id_date));
		}
	}


	//--- catalogue subscription -------------------------------------------------
	//----------------------------------------------------------------------------


	protected function _getCatalogueCourses($id_catalogue, $filter = false)
	{
		$output = array();

		$db = DbConn::getInstance();
		$entries = array();
		$query = "SELECT idEntry FROM %lms_catalogue_entry "
			. " WHERE idCatalogue = '" . $id_catalogue . "' AND  type_of_entry = 'course'";
		$res = $db->query($query);
		while (list($idEntry) = sql_fetch_row($res)) {
			$entries[] = $idEntry;
		}

		if (is_array($filter)) {
			$entries = array_intersect($entries, $filter);
		}

		if (count($entries) > 0) {
			$query = "SELECT * FROM %lms_course "
				. " WHERE idCourse IN (" . implode(",", $entries) . ")";
			$res = $db->query($query);
			while ($cinfo = $db->fetch_obj($res)) {
				$output[$cinfo->idCourse] = $cinfo;
			}
		}

		return $output;
	}

	protected function _getCatalogueName($id_catalogue)
	{
		if ((int)$id_catalogue <= 0)
			return false;
		$db = DbConn::getInstance();
		$query = "SELECT name, description FROM %lms_catalogue WHERE idCatalogue='" . (int)$id_catalogue . "'";
		$res = $db->query($query);
		list($name, $description) = $db->fetch_row($res);
		return $name;
	}

	public function cataloguesubscribeusersTask()
	{
		require_once(_adm_ . '/lib/lib.directory.php');
		require_once(_adm_ . '/class.module/class.directory.php');
		require_once(_lms_ . '/lib/lib.course.php');
		require_once(_lms_ . '/lib/lib.edition.php');
		require_once(_lms_ . '/lib/lib.date.php');

		$acl_man = &Docebo::user()->getAclManager();
		$edition_man = new EditionManager();
		$date_man = new DateManager();

		$id_catalogue = Get::req('id_catalogue', DOTY_INT, 0);

		$back_url = 'index.php?modname=catalogue&op=catlist&of_platform=lms';
		$jump_url = 'index.php?r=' . $this->link . '/cataloguesubscribeusers';

		//if we are a subadmin, check which courses/catalogues we can see
		$can_see_catalogue = true;
		if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
			require_once(_base_ . '/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
			$all_courses = false;
			if (isset($admin_courses['course'][0]))
				$all_courses = true;
			if (isset($admin_courses['course'][-1])) {
				require_once(_lms_ . '/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$admin_courses['catalogue'] = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());

				if (count($admin_courses['catalogue']) == 0 && Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			}
			if (!isset($admin_courses['catalogue'][$id_catalogue]))
				$can_see_catalogue = false;
		}

		//invalid specified catalogue
		if ($id_catalogue <= 0 || !$can_see_catalogue) {
			$this->render('invalid', array(
				'message' => Lang::t('_INVALID_CATALOGUE', 'subscribe'),
				'back_url' => $back_url
			));
			return;
		}

		//check if the selected catalogue has any courses
		$courses_list = $this->_getCatalogueCourses($id_catalogue, (isset($admin_courses['course']) ? $admin_courses['course'] : false));
		if (count($courses_list) <= 0) {
			$this->render('invalid', array(
				'message' => Lang::t('_NO_COURSES_IN_THE_CATALOGUE', 'subscribe'),
				'back_url' => $back_url
			));
			return;
		}

		$name = $this->_getCatalogueName($id_catalogue);

		if (isset($_POST['cancelselector'])) {

			//--- UNDO: return to catalogue list -------------------------------------
			Util::jump_to($back_url);
		} elseif (isset($_POST['okselector'])) {

			//--- USERS SELECTION IS CONFIRMED: now select editions (if any) ---------
			//check user selection
			$_selector = new UserSelector();
			$json = new Services_JSON();
			$_entity_selected = $_selector->getSelection($_POST);
			$user_selected = &$acl_man->getAllUsersFromSelection($_entity_selected);


			//free some memory from garbage variables
			unset($_selector);
			unset($_entity_selected);

			//if no user selected, than give invalid screen
			if (!is_array($user_selected) || count($user_selected) <= 0) {
				$this->render('invalid', array(
					'message' => Lang::t('_EMPTY_SELECTION', 'subscribe'),
					'back_url' => $back_url
				));
				return;
			}

			//extract editions info data by courses
			$editions_list = $edition_man->getEditionsInfoByCourses(array_keys($courses_list), true);

			//extract editions info data by courses
			$classrooms_list = $date_man->getDatesInfoByCourses(array_keys($courses_list), true);


			$tables = array(
				'editions' => false,
				'classrooms' => false
			);

			//check if the catalogue has editions; if any, than set the editions selector
			if (count($editions_list) > 0 || count($classrooms_list) > 0) {
				//set title
				$page_title_arr = array(
					$back_url => Lang::t('_SUBSCRIBE', 'subscribe'),
					$name,
					Lang::t('_SUBSCRIBE', 'subscribe')
				);

				if (count($editions_list) > 0) {
					//instantiate a new table for editions selection
					require_once(_base_ . '/lib/lib.table.php');
					$tb = new Table(
						0,
						Lang::t('_CATALOGUE_SUBSCRIBE_CHOOSE_EDITIONS', 'subscribe'),
						Lang::t('_CATALOGUE_SUBSCRIBE_CHOOSE_EDITIONS', 'subscribe')
					);
					$head_style = array('align_center', '', 'align_center');
					$head_label = array(
						Lang::t('_COURSE_CODE', 'course'),
						Lang::t('_COURSE_NAME', 'course'),
						Lang::t('_CLASSROOM_EDITION', 'course')
					);
					$tb->addHead($head_label, $head_style);


					//set table rows
					foreach ($editions_list as $id_course => $editions) {
						$line = array();

						$cinfo = $courses_list[$id_course];

						$line[] = $cinfo->code;
						$line[] = $cinfo->name;

						//create the dropdown with the editions for every course
						$_dropdown = array();
						foreach ($editions as $id_edition => $ed_info) {
							$_line_content = '';

							//print begin and end date
							if ($ed_info->date_begin != '') {
								$_line_content .= '[' . $ed_info->code . '] ' . $ed_info->name . ' '
									. '(' . Format::date($ed_info->date_begin, 'date')
									. ' - ' . Format::date($ed_info->date_end, 'date') . ')';
							}

							//check if the string is valid
							if ($_line_content == '') {
								//...
							}

							//add to dropdown list and sort the list alphabetically
							$_dropdown[$id_edition] = $_line_content;
							asort($_dropdown);
						}

						$line[] = Form::getInputDropdown('dropdown', 'sel_editions_' . $id_course, 'sel_editions[' . $id_course . ']', $_dropdown, false, '');

						$tb->addBody($line);
					}

					$tables['editions'] = $tb;
				}


				if (count($classrooms_list) > 0) {
					//instantiate a new table for editions selection
					require_once(_base_ . '/lib/lib.table.php');
					$tb = new Table(
						0,
						Lang::t('_COURSE_TYPE_EDITION', 'course'),
						Lang::t('_CLASSROOM', 'standard')
					);
					$head_style = array('align_center', '', 'align_center');
					$head_label = array(
						Lang::t('_COURSE_CODE', 'course'),
						Lang::t('_COURSE_NAME', 'course'),
						Lang::t('_CLASSROOM_EDITION', 'course')
					);
					$tb->addHead($head_label, $head_style);


					//set table rows
					foreach ($classrooms_list as $id_course => $classrooms) {
						$line = array();

						$cinfo = $courses_list[$id_course];

						$line[] = $cinfo->code;
						$line[] = $cinfo->name;

						//create the dropdown with the editions for every course
						$_dropdown = array();
						foreach ($classrooms as $id_date => $cl_info) {
							$_line_content = '';

							//print begin and end date
							if ($cl_info->date_begin != '') {
								$_line_content .= '[' . $cl_info->code . '] ' . $cl_info->name . ' '
									. '(' . Format::date($cl_info->date_begin, 'date')
									. ' - ' . Format::date($cl_info->date_end, 'date') . ')';
							}

							//check if the string is valid
							if ($_line_content == '') {
								//...
							}

							//add to dropdown list and sort the list alphabetically
							$_dropdown[$id_date] = $_line_content;
							asort($_dropdown);
						}

						$line[] = Form::getInputDropdown('dropdown', 'sel_classrooms_' . $id_course, 'sel_classrooms[' . $id_course . ']', $_dropdown, false, '');

						$tb->addBody($line);
					}

					$tables['classrooms'] = $tb;
				}

				$this->render('catalogue_editions', array(
					'id_catalogue' => $id_catalogue,
					'page_title_arr' => $page_title_arr,
					'num_users_selected' => count($user_selected),
					'_sel_users' => $json->encode($user_selected),
					'tables' => $tables
				));
			} else {

				//no editions in the catalogue's courses, call the save operation directly
				$data = array();
				foreach ($user_selected as $user) {
					foreach ($courses_list as $idCourse => $course) {
						$data[] = array($user, $idCourse, false, false);
					}
				}

				$num_subscribed = $this->_subscribeUsersToCatalogue($data);

				Util::jump_to($back_url . '&res=' . $num_subscribed); //_operation_successful
			}
		} else {

			//--- USER SELECTION IS IN PROGRESS: show selector -----------------------
			$user_select = new UserSelector();

			$user_select->show_user_selector = TRUE;
			$user_select->show_group_selector = TRUE;
			$user_select->show_orgchart_selector = TRUE;
			//$user_select->show_orgchart_simple_selector = TRUE;
			//filter selectable user by sub-admin permission
			$user_select->setUserFilter('exclude', array($this->acl_man->getAnonymousId()));
			if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				require_once(_base_ . '/lib/lib.preference.php');
				$adminManager = new AdminPreference();
				$admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
				$admin_users = $this->acl_man->getAllUsersFromIdst($admin_tree);
				$user_select->setUserFilter('user', $admin_users);
				$user_select->setUserFilter('group', $admin_tree);
			}

			if (Get::req('is_updating', DOTY_INT, false)) {
				//...
			} else {
				$user_select->requested_tab = PEOPLEVIEW_TAB;
				$user_select->resetSelection(/* ... */);
			}

			$page_title_arr = array(
				$back_url => Lang::t('_SUBSCRIBE', 'subscribe'),
				$name,
				Lang::t('_SUBSCRIBE', 'subscribe')
			);
			$user_select->addFormInfo(
				Form::getHidden('is_updating', 'is_updating', 1) .
				Form::getHidden('id_catalogue', 'id_catalogue', $id_catalogue)
			);
			//$user_select->setPageTitle($page_title_arr);
			//$user_select->resetSelection($_SESSION['report_tempdata']['rows_filter']['users']);
			$user_select->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
				$page_title_arr,
				false,
				true);
		}
	}

	/*
	 * insert subscriptions in the DB
	 */

	public function cataloguesubscribesaveTask()
	{
		require_once(_base_ . '/lib/lib.json.php');

		$back_url = 'index.php?modname=catalogue&op=catlist&of_platform=lms';

		//invalid specified catalog
		$id_catalogue = Get::req('id_catalogue', DOTY_INT, 0);
		if ($id_catalogue <= 0) {
			$this->render('invalid', array(
				'message' => Lang::t('_INVALID_CATALOGUE', 'subscribe'),
				'back_url' => $back_url
			));
			return;
		}

		$courses_list = $this->_getCatalogueCourses($id_catalogue);

		$editions = Get::req('sel_editions', DOTY_MIXED, array());
		if (count($editions) <= 0) {
			//...
		}

		$classrooms = Get::req('sel_classrooms', DOTY_MIXED, array());
		if (count($classrooms) <= 0) {
			//...
		}

		//"unzip" user selection from user selector
		$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);
		$user_selection = $json->decode(Get::req('user_selection', DOTY_STRING, '[]'));

		if (!is_array($user_selection) || count($user_selection) <= 0) {
			$this->render('invalid', array(
				'message' => Lang::t('_EMPTY_SELECTION', 'subscribe'),
				'back_url' => $back_url
			));
			return;
		}

		$data = array();
		foreach ($user_selection as $user) {
			foreach ($courses_list as $idCourse => $course) {
				$data[] = array(
					$user,
					$idCourse,
					array_key_exists($idCourse, $editions) ? (int)$editions[$idCourse] : false,
					array_key_exists($idCourse, $classrooms) ? (int)$classrooms[$idCourse] : false,
				);
			}
		}

		$num_subscribed = $this->_subscribeUsersToCatalogue($data);

		Util::jump_to($back_url . '&res=_operation_successful');
	}

	protected function _subscribeUsersToCatalogue($data)
	{
		require_once(_lms_ . '/lib/lib.course.php');
		require_once(_lms_ . '/lib/lib.subscribe.php');
		require_once(_lms_ . '/lib/lib.edition.php');
		require_once(_lms_ . '/lib/lib.date.php');

		$subscribe_man = new CourseSubscribe_Manager();
		$edition_man = new EditionManager();
		$date_man = new DateManager();

		$user_subscribed = array();
		$user_waiting = array();

		//check users who are already subscribed in any selected course
		$arr_courses = array();
		foreach ($data as $value) {
			list($id_user, $id_course, $id_edition, $id_date) = $value;
			$arr_courses[] = $id_course;
		}

		$arr_editions = $edition_man->getCourseEditions($arr_courses);
		$arr_classrooms = $date_man->getCourseDate($arr_courses);

		//get subscriptions to courses and editions
		$already_courses = $subscribe_man->getCourseSubscribedUserIdst($arr_courses, true);
		$already_editions = $edition_man->getEditionSubscribed($arr_editions, true);
		$already_classrooms = $date_man->getDatesSubscribed(array_keys($arr_classrooms), true);

		//subscribe users
		$count_u = 0;
		$count_e = 0;
		$count_d = 0;
		$lv_user = 3; //only students can be managed for multiple subscriptions
		$waiting = false; //no waiting condition

		reset($data);
		foreach ($data as $value) {
			list($id_user, $id_course, $id_edition, $id_date) = $value;

			$_u_subscribed = false;
			if ($id_date) {
				if (!isset($already_classrooms[$id_date][$id_user])) {
					//subscribe to classroom
					$res = $date_man->subscribeUserToDate($id_user, $id_course, $id_date, $lv_user, $waiting);
					if ($res) $count_d++;
					if ($res) $_u_subscribed = true;    // user subscribed
				}
			} elseif ($id_edition) {
				if (!isset($already_editions[$id_edition][$id_user])) {
					//subscribe to edition
					$res = $edition_man->subscribeUserToEdition($id_user, $id_course, $id_edition, $lv_user, $waiting);
					if ($res) $count_e++;
					if ($res) $_u_subscribed = true;    // user subscribed
				}
			} else {
				if (!isset($already_courses[$id_course][$id_user])) {
					//subscribe to course
					$res = $subscribe_man->subscribeUserToCourse($id_user, $id_course, $lv_user, $waiting);
					if ($res) $count_u++;
					if ($res) $_u_subscribed = true;    // user subscribed
				}
			}
			if ($_u_subscribed) {
				// user subscribed
				$docebo_course = new DoceboCourse($id_course);
				$level_idst = &$docebo_course->getCourseLevel($id_course);
				if (count($level_idst) == 0 || $level_idst[1] == '') {
					$level_idst =& $docebo_course->createCourseLevel($id_course);
				}
				$this->_addToCourseGroup($level_idst[$lv_user], $id_user);
			}
		}

		/*
		  //send alerts
		  if(!empty($user_subscribed)) {
		  _sendSubscribedAlert($user_subscribed, $course_info);
		  }

		  if(!empty($user_waiting)) {
		  _sendWaitingAlert($user_waiting, $course_info);
		  }
		 */
		return $count_u + $count_e + $count_d;
	}


	//dialog for fast subscribing of an user to courses
	public function fast_subscribe_dialog()
	{
		require_once(_lms_ . '/lib/lib.subscribe.php');
		$subscribe_man = new CourseSubscribe_Manager();

		$this->render('fast_subscribe_dialog', array(
			'title' => Lang::t('_SUBSCRIBE', 'subscribe'),
			'levels' => $subscribe_man->getUserLevel(),
			'selected_level' => 3, //student level
			'json' => $this->json
		));
	}

	public function fast_subscribe_dialog_action()
	{
		require_once(_lms_ . '/lib/lib.course.php');
		$man_course = new Man_Course();
		$acl_man = Docebo::user()->getAclManager();

		$id_user = Get::req('id_user', DOTY_INT, 0);
		$id_course = Get::Req('id_course', DOTY_INT, 0);
		$level = Get::Req('level', DOTY_INT, 3); //default: student level

		$userid = Get::req('userid', DOTY_STRING, "");
		$course = Get::req('course', DOTY_STRING, "");
		if ($course != "")
			$course = trim(preg_replace('|^\[([^\]]*)\][\s]*|i', '', $course)); //eliminates che code from the course name

		$id_user = $acl_man->getUserST($userid);
		$id_course = $man_course->getCourseIdByName($course);

		//check if input is correct
		if ($id_user <= 0 || $id_course <= 0) {
			$output['success'] = false;
			$output['message'] = UiFeedback::perror(Lang::t('_INVALID_DATA', 'standard')); //'Invalid input. COURSE: '.$id_course.'; USER: '.$id_user;
			echo $this->json->encode($output);
			return;
		}

		//check if there are any edition/classroom selected
		$edition = Get::req('edition', DOTY_INT, 0);
		$classroom = Get::Req('classroom', DOTY_INT, 0);
		$cinfo = $man_course->getCourseInfo($id_course);
		if ($cinfo['course_edition'] > 0)
			$classroom = 0;
		else
			$edition = 0;
		if ($cinfo['course_type'] == 'classroom')
			$edition = 0;

		if ($cinfo['course_type'] == 'classroom' && $classroom <= 0) {
			$output['success'] = false;
			$output['message'] = UIFeedback::pnotice(Lang::t('_CLASSROOM', 'standard') . ': ' . Lang::t('_EMPTY_SELECTION', 'standard'));
			echo $this->json->encode($output);
			return;
		}

		$_model = new SubscriptionAlms($id_course, ($edition > 0 ? $edition : false), ($classroom > 0 ? $classroom : false));

		//check if user is already subscribed
		if ($_model->isUserSubscribed($id_user)) {
			$output['success'] = false;
			$output['message'] = UIFeedback::pnotice(Lang::t('_USER_ALREADY_SUBSCRIBED', 'course') . ': ' . $userid);
			echo $this->json->encode($output);
			return;
		}

		//subscribe user
		$res = $_model->subscribeUser($id_user, $level, false);
		if ($res) {
			$docebo_course = new DoceboCourse($id_course);
			$level_idst = &$docebo_course->getCourseLevel($id_course);
			if (count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst = &$docebo_course->createCourseLevel($id_course);
			//$this->acl_man->addToGroup($level_idst[$level], $id_user);
			$this->_addToCourseGroup($level_idst[$level], $id_user);
		}

		$output['success'] = $res ? true : false;
		if (!$res)
			$output['message'] = UIFeedback::perror(Lang::t('_ERROR_WHILE_SAVING', 'standard'));
		else
			$output['message'] = UIFeedback::pinfo(Lang::t('_GOTO_COURSE_T', 'course'));
		echo $this->json->encode($output);
	}

	public function show_inline_editorTask()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		$acl_man = new DoceboACLManager();

		require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
		require_once($GLOBALS['where_lms'] . '/lib/lib.subscribe.php');

		if ($id_user <= 0) {
			echo $this->json->encode(array('success' => true));
			return;
		}

		//Update info
		$new_value = Get::req('new_value', DOTY_MIXED, '');
		$old_value = Get::req('old_value', DOTY_MIXED, '');
		$col = Get::req('col', DOTY_STRING, '');

		if ($new_value === $old_value) {
			echo $this->json->encode(array('success' => true));
		} else {

			switch ($col) {

				case 'date_begin':
					{
						$_new_date = date("Y-m-d H:i:s", $new_value); //convert the input in ISO format
						//extract date_expire and check if less than date_begin
						$res = false;
						$query = "SELECT date_expire_validity FROM %lms_courseuser "
							. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
						list($date_expire) = sql_fetch_row(sql_query($query));
						if ($date_expire == NULL || $date_expire == "" || $date_expire == "0000-00-00 00:00:00" || $date_expire > $_new_date) {
							$query = "UPDATE %lms_courseuser SET date_begin_validity = '" . $_new_date . "' "
								. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
							$res = sql_query($query);
						}

						$output = array('success' => $res ? true : false);
						if ($res)
							$output['new_value'] = Format::date($_new_date, 'date');

						echo $this->json->encode($output);
					}
					break;

				case 'date_expire':
					{
						$_new_date = date("Y-m-d H:i:s", $new_value); //convert the input in ISO format
						//extract date_begin and check if ggreater than date_expire
						$res = false;
						$query = "SELECT date_begin_validity FROM %lms_courseuser "
							. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
						list($date_begin) = sql_fetch_row(sql_query($query));
						if ($date_begin == NULL || $date_begin == "" || $date_begin == "0000-00-00 00:00:00" || $date_begin < $_new_date) {
							$query = "UPDATE %lms_courseuser SET date_expire_validity = '" . $_new_date . "' "
								. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
							$res = sql_query($query);
						}

						$output = array('success' => $res ? true : false);
						if ($res)
							$output['new_value'] = Format::date($_new_date, 'date');

						echo $this->json->encode($output);
					}
					break;

				case 'date_complete':
					{
						$_new_date = date("Y-m-d H:i:s", $new_value); //convert the input in ISO format
						//extract date_begin and check if ggreater than date_expire
						$res = false;
						$query = "SELECT date_complete FROM %lms_courseuser "
							. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
						list($date_begin) = sql_fetch_row(sql_query($query));
						$query = "UPDATE %lms_courseuser SET date_complete = '" . $_new_date . "' "
							. " WHERE idCourse=" . (int)$id_course . " AND idUser=" . (int)$id_user . " AND edition_id=" . (int)$id_edition;
						$res = sql_query($query);

						$output = array('success' => $res ? true : false);
						if ($res)
							$output['new_value'] = Format::date($_new_date, 'date');

						echo $this->json->encode($output);
					}
					break;

				default:
					{
						echo $this->json->encode(array('success' => false));
					}
					break;
			}
		}
	}


	public function reset_validity_datesTask()
	{
		$id_course = Get::req('id_course', DOTY_INT, 0);
		$id_edition = Get::req('id_edition', DOTY_INT, 0);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		if ($id_course <= 0 || $id_user <= 0) {
			//...
			return;
		}
		$res = $this->model->resetValidityDates($id_course, $id_edition, $id_user);
		echo $this->json->encode(array('success' => $res ? true : false));
	}


	/****************************************************************************
	 * Coursepaths subscriptions management
	 ****************************************************************************/

	public function show_coursepathTask()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		Util::get_js(Get::rel_path('base') . '/lib/lib.elem_selector.js', true, true);

		$res = Get::req('res', DOTY_STRING, "");
		$message = false;
		switch ($res) {
			case 'ok':
				$message = UIFeedback::info(Lang::t(strtoupper($_GET['res']), 'subscription'));
				break;
			case 'err':
				$message = UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));
				break;
		}

		Form::loadDatefieldScript(); //some dialogs use date inputs
		$umodel = new UsermanagementAdm();

		$params = array(
			'back_link' => 'index.php?modname=coursepath&op=pathlist&of_platform=lms',
			'id_path' => $id_path,
			'message' => $message,
			'orgchart_list' => $umodel->getOrgChartDropdownList(),
			'is_active_advanced_filter' => false,
			'filter_text' => "",
			'filter_orgchart' => 0,
			'filter_descendants' => false,
			'filter_date_valid' => "",
			'filter_show' => 0,
			'path_name' => $this->model->getCoursepathNameForSubscription($id_path)
		);

		$this->render('show_coursepath', $params);
	}

	protected function _selectall_coursepath()
	{
		$filter = array(
			'text' => Get::req('filter_text', DOTY_STRING, ""),
			'orgchart' => Get::req('filter_orgchart', DOTY_INT, 0),
			'descendants' => Get::req('filter_descendants', DOTY_INT, 0),
			'date_valid' => Get::req('filter_date_valid', DOTY_STRING, "")
		);
		$output = array_values($this->model->getCoursePathSubscriptionsList($filter));
		echo $this->json->encode($output);
	}

	public function getlist_coursepathTask()
	{
		$op = Get::req('op', DOTY_MIXED, false);
		switch ($op) {
			case "selectall":
				{
					$this->_selectall_coursepath();
					return;
				}
				break;
		}

		$id_path = Get::req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		$start_index = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'userid');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$filter = array(
			'text' => Get::req('filter_text', DOTY_STRING, ""),
			'orgchart' => Get::req('filter_orgchart', DOTY_INT, 0),
			'descendants' => Get::req('filter_descendants', DOTY_INT, 0),
			'date_valid' => Get::req('filter_date_valid', DOTY_STRING, ""),
			'show' => Get::req('filter_show', DOTY_INT, 0)
		);

		$total_user = $this->model->getCoursePathUsersTotal($id_path, $filter);
		$array_user = $this->model->getCoursePathUsersList($id_path, $start_index, $results, $sort, $dir, $filter);

		$list = array();
		foreach ($array_user as $record) {
			$list[] = array(
				'id' => $record->idst,
				'userid' => Layout::highlight($this->acl_man->relativeId($record->userid), $filter['text']),
				'fullname' => Layout::highlight($record->lastname, $filter['text']) . ' ' . Layout::highlight($record->firstname, $filter['text']),
				'date_begin' => Format::date($record->date_begin_validity, 'date'),
				'date_expire' => Format::date($record->date_expire_validity, 'date'),
				'date_begin_timestamp' => Format::toTimestamp($record->date_begin_validity == null ? date("U") : $record->date_begin_validity),
				'date_expire_timestamp' => Format::toTimestamp($record->date_expire_validity == null ? date("U") : $record->date_expire_validity),
				'del' => 'ajax.adm_server.php?r=' . $this->link . '/del_coursepath&id_user=' . $record->idst . '&id_path=' . $id_path
			);
		}

		$result = array(
			'totalRecords' => $total_user,
			'startIndex' => $start_index,
			'sort' => $sort,
			'dir' => $dir,
			'rowsPerPage' => $results,
			'results' => count($list),
			'records' => $list
		);

		echo $this->json->encode($result);
	}

	public function multimod_dialog_coursepathTask()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		$output = array();

		if (Get::req('count_sel', DOTY_INT, 0) <= 0) {
			$output['success'] = true;
			$output['header'] = Lang::t('_MOD', 'subscribe') . '&nbsp;';
			$output['body'] = '<p>' . Lang::t('_EMPTY_SELECTION', 'admin_directory') . '</p>';
			echo $this->json->encode($output);
			return;
		}

		$sel_date_begin = Form::getInputCheckbox('multimod_date_begin_set', 'multimod_date_begin_set', 1, false, '');
		$sel_date_expire = Form::getInputCheckbox('multimod_date_expire_set', 'multimod_date_expire_set', 1, false, '');

		$body = Form::openForm('multimod_dialog', 'ajax.adm_server.php?r=' . $this->link . '/multimod_coursepath')
			. Form::getDatefield(Lang::t('_DATE_BEGIN_VALIDITY', 'subscribe'), 'multimod_date_begin', 'multimod_date_begin', '', false, false, '', '', $sel_date_begin)
			. Form::getDateField(Lang::t('_DATE_EXPIRE_VALIDITY', 'subscribe'), 'multimod_date_expire', 'multimod_date_expire', '', false, false, '', '', $sel_date_expire)
			. Form::getHidden('mod_dialog_users', 'users', '')
			. Form::getHidden('id_path', 'id_path', $id_path)
			. Form::closeForm();

		$output['success'] = true;
		$output['header'] = Lang::t('_MOD', 'subscribe') . '&nbsp;';
		$output['body'] = $body;

		$output['__date_inputs'] = $GLOBALS['date_inputs'];

		echo $this->json->encode($output);
	}


	public function reset_validity_dates_coursepathTask()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		$id_user = Get::req('id_user', DOTY_INT, 0);
		if ($id_path <= 0 || $id_user <= 0) {
			//...
			return;
		}
		$res = $this->model->resetCoursepathValidityDates($id_path, $id_user);
		echo $this->json->encode(array('success' => $res ? true : false));
	}

	public function multimod_coursepath()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		$output = array();

		$users = Get::req('users', DOTY_STRING, '');
		if ($users == '') {
			$output['success'] = false;
			$output['message'] = Lang::t('_NO_USER_SELECTED', 'subscribe');
		} else {
			$set_date_begin = Get::req('multimod_date_begin_set', DOTY_INT, 0);
			$set_date_expire = Get::req('multimod_date_expire_set', DOTY_INT, 0);

			if ($set_date_begin <= 0 && $set_date_expire <= 0) {
				$output['success'] = false;
				$output['message'] = UIFeedback::info($this->_getMessage('no options selected'), true);
			} else {

				$users_list = explode(',', $users);

				require_once(_lms_ . '/lib/lib.coursepath.php');
				$sman = new CoursePath_Manager();

				$res1 = true;
				if ($set_date_begin > 0) {
					$new_date_begin = Get::req('multimod_date_begin', DOTY_STRING, "");
					$res3 = $sman->updateUserDateBeginValidityInCourse($users_list, $id_path, Format::dateDb($new_date_begin, 'date'));
				}

				$res2 = true;
				if ($set_date_expire > 0) {
					$new_date_expire = Get::req('multimod_date_expire', DOTY_STRING, "");
					$res4 = $sman->updateUserDateExpireValidityInCourse($users_list, $id_path, Format::dateDb($new_date_expire, 'date'));
				}

				$success = $res1 && $res2;
				$output['success'] = $success;
				if (!$success) {
					$message = "";
					if (!$res1)
						$message .= 'Unable to change date begin;'; //TO DO: make translation
					if (!$res2)
						$message .= 'Unable to change date expire;'; //TO DO: make translation
					$output['message'] = $message;
				}
			}
		}

		echo $this->json->encode($output);
	}

	public function multidel_coursepath()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			$output = array('success' => false);
			echo $this->json->encode($output);
			return;
		}

		$users = trim(Get::req('users', DOTY_STRING, ""));
		$output = array();

		if ($users == '') {
			$output = array('success' => true);
		} else {
			$arr_users = explode(",", $users);
			$res = $this->model->unsubscribeFromCoursepath($id_path, $arr_users);
			$output = array('success' => $res);
		}

		echo $this->json->encode($output);
	}

	public function del_coursepathTask()
	{
		$id_path = Get::req('id_path', DOTY_INT, 0);
		$id_user = Get::req('id_user', DOTY_INT, 0);

		$output = array();
		if ($id_path <= 0 || $id_user <= 0) {
			$output['success'] = false;
			echo $this->json->encode($output);
			return;
		}

		$res = $this->model->unsubscribeFromCoursepath($id_path, $id_user);
		$output['success'] = $res ? true : false;
		echo $this->json->encode($output);
	}

	public function sel_users_coursepathTask()
	{
		$id_path = Get::Req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		$params = array(
			'id_path' => $id_path,
			'user_selection' => $this->model->getCoursePathSubscriptionsList($id_path),
			'path_name' => $this->model->getCoursepathNameForSubscription($id_path)
		);
		$this->render('sel_users_coursepath', $params);
	}

	public function sel_users_coursepath_actionTask()
	{
		$id_path = Get::Req('id_path', DOTY_INT, 0);
		if ($id_path <= 0) {
			//...
			return;
		}

		$selection = Get::req('userselector_input', DOTY_MIXED, true);
		$new_selection = $selection['coursepath_subscriptions'];
		$old_selection = $this->model->getCoursePathSubscriptionsList($id_path);

		$new_selection = explode(",", $new_selection);
		$new_selection = $this->acl_man->getAllUsersFromSelection($new_selection);
		$_common = array_intersect($new_selection, $old_selection);
		$_to_add = array_diff($new_selection, $_common);
		$_to_del = array_diff($old_selection, $_common);

		$res = true;
		if (!$res) {
			$result = 'err_subscribe';
		} else {
			$result = 'ok_subcribe';
		}

		require_once(_lms_ . '/lib/lib.coursepath.php');
		$path_man = new CoursePath_Manager();

		//1 - get list of the courses of the coursepath
		$base_url = 'index.php?r=' . $this->link . '/show_coursepath&id_path=' . (int)$id_path;
		$courses = $path_man->getAllCourses(array($id_path));
		if (empty($courses)) {
			Util::jump_to($base_url);
		}

		//2 - check if there are any editions or classrooms
		require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
		$course_man = new Man_Course();

		$classroom = $course_man->getAllCourses(false, 'classroom', $courses);
		$edition = $course_man->getAllCourses(false, 'edition', $courses);

		//3 - if yes, then make a second step in order to choose editions and classrooms
		if (!empty($classroom) || !empty($edition)) {

			$classroom_list = array();
			if (!empty($classroom)) {
				require_once(_lms_ . '/lib/lib.date.php');
				$date_man = new DateManager();

				foreach ($classroom as $id_course => $info) {
					$classrooms = $date_man->getCourseDate($id_course, true);

					$classrooms_for_dropdown = array();
					$classrooms_for_dropdown[0] = Lang::t('_NO_CLASSROOM_SUBSCRIPTION', 'coursepath');

					foreach ($classrooms as $classroom_info)
						$classrooms_for_dropdown[$classroom_info['id_date']] = $classroom_info['code'] . ' - ' . $classroom_info['name']
							. ' - ' . Format::date($classroom_info['date_begin'], 'date') . ' - ' . Format::date($classroom_info['date_end'], 'date');

					$classroom_list[] = array(
						'id_course' => $id_course,
						'label' => $info['name'],
						'list' => $classrooms_for_dropdown
					);
					//cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'classroom_'.$id_course, 'classroom_'.$id_course, $edition_for_dropdown));
				}
			}

			$edition_list = array();
			if (!empty($edition)) {
				require_once(_lms_ . '/lib/lib.edition.php');
				$edition_man = new EditionManager();

				foreach ($edition as $id_course => $info) {
					$editions = $edition_man->getEditionsInfoByCourses($id_course);

					$editions_for_dropdown = array();
					$editions_for_dropdown[0] = Lang::t('_NONE', 'coursepath');

					foreach ($editions[$id_course] as $edition_info)
						$editions_for_dropdown[$edition_info['id_edition']] = $edition_info['code'] . ' - ' . $edition_info['name']
							. ' - ' . Format::date($edition_info['date_begin'], 'date') . ' - ' . Format::date($edition_info['date_end'], 'date');

					$edition_list[] = array(
						'id_course' => $id_course,
						'label' => $info['name'],
						'list' => $edition_for_dropdown
					);
					//cout(Form::getDropdown(Lang::t('_EDITION_SELECTION', 'coursepath').' : '.$info['code'].' - '.$info['name'], 'edition_'.$id_course, 'edition_'.$id_course, $edition_for_dropdown));
				}
			}


			$this->render('choose_editions_coursepath', array(
				'id_path' => $id_path,
				'courses_list' => $courses,
				'editions_list' => $edition_list,
				'classrooms_list' => $classroom_list,
				'users_to_add' => $_to_add,
				'users_to_del' => $_to_del,
				'path_name' => $this->model->getCoursepathNameForSubscription($id_path)
			));
		} else {
			$path_man->subscribeUserToCoursePath($id_path, $_to_add);
			require_once(_lms_ . '/lib/lib.course.php');

			foreach ($courses as $id_course) {
				$docebo_course = new DoceboCourse($id_course);
				$level_idst = &$docebo_course->getCourseLevel($id_course);
				if (count($level_idst) == 0 || $level_idst[1] == '')
					$level_idst =& $docebo_course->createCourseLevel($id_course);
				foreach ($_to_add as $id_user) {
					$level = 3; //student
					$waiting = false;
					//$this->acl_man->addToGroup($level_idst[$level], $id_user);
					$this->_addToCourseGroup($level_idst[$level], $id_user);
					$this->model->id_course = $id_course;
					$this->model->subscribeUser($id_user, $level, $waiting);
				}
			}
			Util::jump_to('index.php?r=' . $this->link . '/show_coursepath&id_path=' . (int)$id_path . '&res=' . $result);
		}
	}

	public function choose_editions_coursepath_action()
	{
		if (isset($_POST['undo']))
			Util::jump_to('index.php?r=' . $this->link . '/show_coursepath&id_path=' . (int)$_POST['id_path']);

		$courses = explode(',', Get::req('courses_list', DOTY_MIXED, ''));
		$_to_add = explode(',', Get::req('users_to_add', DOTY_MIXED, ''));
		$_to_del = explode(',', Get::req('users_to_del', DOTY_MIXED, ''));
		$id_path = Get::req('id_path', DOTY_INT, 0);
		$classrooms = Get::req('classrooms', DOTY_MIXED, array());
		$editions = Get::req('editions', DOTY_MIXED, array());

		require_once(_lms_ . '/lib/lib.coursepath.php');
		require_once(_lms_ . '/lib/lib.course.php');
		$path_man = new CoursePath_Manager();

		foreach ($courses as $id_course) {
			$res = true;

			$docebo_course = new DoceboCourse($id_course);
			$level_idst = &$docebo_course->getCourseLevel($id_course);
			if (count($level_idst) == 0 || $level_idst[1] == '')
				$level_idst =& $docebo_course->createCourseLevel($id_course);

			foreach ($_to_add as $id_user) {
				$level = 3; //student
				$waiting = false;
				//$this->acl_man->addToGroup($level_idst[$level], $id_user);
				$this->_addToCourseGroup($level_idst[$level], $id_user);
				$this->model->id_course = $id_course;
				if (isset($classrooms[$id_course]))
					$this->model->id_date = $classrooms[$id_course];
				if (isset($editions[$id_course]))
					$this->model->id_edition = $editions[$id_course];
				if (!$this->model->subscribeUser($id_user, $level, $waiting))
					$res = false;
			}
		}

		if ($res)
			$res = $path_man->subscribeUserToCoursePath($id_path, $_to_add);

		Util::jump_to('index.php?r=' . $this->link . '/show_coursepath&id_path=' . (int)$_POST['id_path'] . '&res=' . $res);
	}

	/****** End coursepaths ****************************************************/

	function waitinguser()
	{
		if (!$this->permissions['moderate']) die("You can't access");

		require_once(_lms_ . '/lib/lib.course.php');
		require_once(_adm_ . '/lib/lib.field.php');
		require_once(_base_ . '/lib/lib.form.php');
		require_once(_base_ . '/lib/lib.table.php');
		require_once(_base_ . '/lib/lib.user_profile.php');

		$id_course = Get::req('id_course', DOTY_INT, 0);
		$man_course = new Man_Course();
		$course_info = $man_course->getCourseInfo($id_course);

		$is_classroom = $course_info['course_type'] == 'classroom';

		$edition_id = Get::req('id_edition', DOTY_INT, 0);
		$ed_url_param = '&id_edition=' . $edition_id;

		$out =& $GLOBALS['page'];
		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		$lang =& DoceboLanguage::CreateInstance('subscribe', 'lms');
		$acl_man =& Docebo::user()->getAclManager();
		$levels = CourseLevel::getLevels();

		$waiting_users =& $man_course->getWaitingSubscribed($id_course, $edition_id);
		$users_name =& $acl_man->getUsers($waiting_users['all_users_id']);

		$arr_status = array(//_CUS_RESERVED		=> $lang->def('_USER_STATUS_RESERVED'),
			_CUS_WAITING_LIST => $lang->def('_WAITING_USERS'),
			_CUS_CONFIRMED => $lang->def('_USER_STATUS_CONFIRMED'),

			_CUS_SUBSCRIBED => $lang->def('_USER_STATUS_SUBS'),
			_CUS_BEGIN => $lang->def('_USER_STATUS_BEGIN'),
			_CUS_END => $lang->def('_USER_STATUS_END'),
			_CUS_SUSPEND => $lang->def('_SUSPENDED'));

		$page_title = array(
			'index.php?r=' . $this->link_course . '/show' => Lang::t('_COURSES', 'course'),
			Lang::t('_USERWAITING', 'course') . ': ' . $course_info['name']
		);
		$GLOBALS['page']->add(
			getTitleArea($page_title, 'subscribe')
			. '<div class="std_block">'
			. Form::openForm('approve users', 'index.php?r=' . $this->link . '/approveusers')
			. Form::getHidden('id_course', 'id_course', $id_course)
			. Form::getHidden('edition_id', 'edition_id', $edition_id)
			, 'content');

		$tb = new Table(0, $lang->def('_SELECT_WHO_CONFIRM'), $lang->def('_SUMMARY_SELECT_WHO_CONFIRM'));

		$type_h = array();
		$type_h[] = '';
		$type_h[] = '';
		$type_h[] = '';
		if ($is_classroom) $type_h[] = '';
		$type_h[] = '';
		$type_h[] = '';
		$type_h[] = 'image';
		$type_h[] = 'image';
		$type_h[] = 'image';

		$content_h = array();
		$content_h[] = $lang->def('_USERNAME');
		$content_h[] = $lang->def('_FULLNAME');
		$content_h[] = $lang->def('_LEVEL');
		if ($is_classroom) $content_h[] = $lang->def('_CLASSROOM');
		$content_h[] = $lang->def('_SUBSCRIBED_BY');
		$content_h[] = $lang->def('_STATUS');
		$content_h[] = $lang->def('_APPROVE');
		$content_h[] = $lang->def('_DENY');
		$content_h[] = $lang->def('_WAIT');
		$tb->addHead($content_h, $type_h);

		if (is_array($waiting_users['users_info'])) {

			reset($waiting_users['users_info']);
			while ((list($id_user, $info) = each($waiting_users['users_info']))) {

				$id_sub_by = $info['subscribed_by'];
				$subscribed = ($users_name[$id_sub_by][ACL_INFO_LASTNAME] . '' . $users_name[$id_sub_by][ACL_INFO_FIRSTNAME] != ''
					? $users_name[$id_sub_by][ACL_INFO_LASTNAME] . ' ' . $users_name[$id_sub_by][ACL_INFO_FIRSTNAME]
					: $acl_man->relativeId($users_name[$id_sub_by][ACL_INFO_USERID]));
				$more = (isset($_GET['id_user']) && $_GET['id_user'] == $id_user
					? '<a href="index.php?r=' . $this->link . '/waitinguser&amp;id_course=' . $id_course . $ed_url_param . '"><img src="' . getPathImage() . 'standard/menu_open.png"></a> '
					: '<a href="index.php?r=' . $this->link . '/waitinguser&amp;id_course=' . $id_course . $ed_url_param . '&amp;id_user=' . $id_user . '"><img src="' . getPathImage() . 'standard/menu_closed.png"></a> ');


				$is_overbooking = FALSE;
				if ($is_classroom) {
					$is_overbooking = $info['overbooking'];
				} else {
					$is_overbooking = FALSE; //$info['status'] == _CUS_OVERBOOKING
				}

				$content = array();

				$content[] = $more . $acl_man->relativeId($users_name[$id_user][ACL_INFO_USERID]);
				$content[] = $users_name[$id_user][ACL_INFO_LASTNAME] . ' ' . $users_name[$id_user][ACL_INFO_FIRSTNAME];
				$content[] = $levels[$info['level']];
				if ($is_classroom) $content[] = ($info['code'] != '' ? '[' . $info['code'] . '] ' : '') . $info['name'];
				$content[] = $subscribed . ' [' . $users_name[$id_sub_by][ACL_INFO_EMAIL] . ']';
				$content[] = $is_overbooking ? $lang->def('_OVERBOOKING') : $arr_status[$info['status']];

				if ($is_overbooking) {

					$content[] = '';
					$content[] = '';
					$content[] = '';

				} else {

					$content[] = Form::getInputRadio(
							'waiting_user_0_' . $id_user,
							'waiting_user[' . $id_user . ']',
							'0',
							false,
							'') . '<label class="access-only" for="waiting_user_0_' . $id_user . '">' . $users_name[$id_user][ACL_INFO_USERID] . '</label>';

					$content[] = Form::getInputRadio(
							'waiting_user_1_' . $id_user,
							'waiting_user[' . $id_user . ']',
							'1',
							false,
							'') . '<label class="access-only" for="waiting_user_1_' . $id_user . '">' . $users_name[$id_user][ACL_INFO_USERID] . '</label>';

					$content[] = Form::getInputRadio(
							'waiting_user_2_' . $id_user,
							'waiting_user[' . $id_user . ']',
							'2',
							true,
							'') . '<label class="access-only" for="waiting_user_1_' . $id_user . '">' . $users_name[$id_user][ACL_INFO_USERID] . '</label>';
				}

				$tb->addBody($content);
				if (isset($_GET['id_user']) && $id_user == $_GET['id_user']) {
					$field = new FieldList();
					$info = $field->playFieldsForUser($id_user, false, true);
					$tb->addBodyExpanded(($info != '' ? $info : $lang->def('_NO_EXTRAINFO_AVAILABLE')), 'user_specific_info');
				}
			}
		}

		$GLOBALS['page']->add(
			$tb->getTable()
			. '<br />'
			. Form::openElementSpace()
			. Form::getSimpleTextarea($lang->def('_SUBSCRIBE_ACCEPT'), 'subscribe_accept', 'subscribe_accept')
			. Form::getSimpleTextarea($lang->def('_SUBSCRIBE_REFUSE'), 'subscribe_refuse', 'subscribe_refuse')
			. Form::closeElementSpace()
			. Form::openButtonSpace()
			. '<br />'
			. Form::getButton('subscribe', 'subscribe', $lang->def('_SAVE'))
			. Form::getButton('cancelselector', 'cancelselector', $lang->def('_UNDO'))
			. Form::closeButtonSpace()
			. Form::closeForm()
			, 'content');
		$GLOBALS['page']->add('</div>', 'content');
	}


	function removeSubscription($id_course, $id_user, $lv_group, $edition_id = 0, $start_date = FALSE, $end_date = FALSE)
	{

		require_once($GLOBALS["where_framework"] . "/lib/resources/lib.timetable.php");
		$tt = new TimeTable();
		// ----------------------------------------
		$resource = "user";
		$resource_id = $id_user;
		if ($edition_id > 0) {
			$consumer = "course_edition";
			$consumer_id = $edition_id;
		} else {
			$consumer = "course";
			$consumer_id = $id_course;
		}
		// ----------------------------------------
		$tt->deleteEvent(FALSE, $resource, $resource_id, $consumer, $consumer_id, $start_date, $end_date);

		$acl_man =& Docebo::user()->getAclManager();
		$acl_man->removeFromGroup($lv_group, $id_user);

		if ($edition_id > 0) {
			$group = '/lms/course_edition/' . $edition_id . '/subscribed';
			$group_idst = $acl_man->getGroupST($group);
			$acl_man->removeFromGroup($group_idst, $id_user);
		}

		return sql_query("
		DELETE FROM " . $GLOBALS['prefix_lms'] . "_courseuser
		WHERE idUser = '" . $id_user . "' AND idCourse = '" . $id_course . "'
		AND edition_id='" . (int)$edition_id . "'");
	}

	function approveusers()
	{
		if (!$this->permissions['moderate']) die("You can't access");

		require_once(_lms_ . '/lib/lib.course.php');
		require_once(_base_ . '/lib/lib.preference.php');

		$id_course = Get::req('id_course', DOTY_INT, 0);
		$course_info = Man_Course::getCourseInfo($id_course);

		$edition_id = Get::req('id_edition', DOTY_INT, 0);;

		$re = true;
		$approve_user = array();
		$deny_user = array();
		if (isset($_POST['waiting_user'])) {

			$man_course = new Man_Course();
			$waiting_users =& $man_course->getWaitingSubscribed($id_course);
			$tot_deny = array();

			require_once(_lms_ . '/lib/lib.course.php');
			//require_once (_lms_.'/admin/modules/subscribe/subscribe.php');

			$docebo_course = new DoceboCourse($id_course);

			$group_levels = $docebo_course->getCourseLevel($id_course);
			if (count($group_levels) == 0 || $group_levels[1] == '')
				$group_levels =& $docebo_course->createCourseLevel($id_course);

			while (list($id_user, $action) = each($_POST['waiting_user'])) {

				if ($action == 0) {
					// approved -----------------------------------------------

					$text_query = "
					UPDATE " . $GLOBALS['prefix_lms'] . "_courseuser
					SET waiting = 0,
						status = '" . _CUS_SUBSCRIBED . "'
					WHERE idCourse = '" . $id_course . "' AND idUser = '" . $id_user . "' ";
					$text_query .= "AND edition_id='" . $edition_id . "'";
					$result = sql_query($text_query);
					if ($result) $approve_user[] = $id_user;
					$re &= $result;

				} elseif ($action == 1) {
					// refused --------------------------------------------------

					$level = $waiting_users['users_info'][$id_user]['level'];
					$sub_by = $waiting_users['users_info'][$id_user]['subscribed_by'];
					$result = $this->removeSubscription($id_course, $id_user, $group_levels[$level], $edition_id);
					if ($sub_by != 0 && ($id_user != $sub_by)) {

						if (isset($tot_deny[$sub_by])) $tot_deny[$sub_by]++;
						else $tot_deny[$sub_by] = 1;
					}
					if ($result) $deny_user[] = $id_user;
					$re &= $result;
				}
			}
		}
		if (!empty($tot_deny)) {

			while (list($id_user, $inc) = each($tot_deny)) {

				$pref = new UserPreferences($id_user);
				$max_subscribe = $pref->getAdminPreference('admin_rules.max_course_subscribe');
				$pref->setPreference('admin_rules.max_course_subscribe', ($max_subscribe + $inc));
			}
		}
		require_once(_base_ . '/lib/lib.eventmanager.php');
		$array_subst = array('[url]' => Get::site_url(),
			'[course]' => $course_info['name']);
		if (!empty($approve_user)) {

			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_APPROVED_SUBSCRIBED_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_APPROVED_SUBSCRIBED_TEXT', $array_subst);
			$msg_composer->setBodyLangText('email', "\n\n" . $_POST['subscribe_accept'], array(), true);

			$msg_composer->setBodyLangText('sms', '_APPROVED_SUBSCRIBED_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert('UserCourseInserted', 'subscribe', 'approve', '1', 'User course approve',
				$approve_user, $msg_composer, true);

		}
		if (!empty($deny_user)) {

			$msg_composer = new EventMessageComposer();

			$msg_composer->setSubjectLangText('email', '_DENY_SUBSCRIBED_SUBJECT', false);
			$msg_composer->setBodyLangText('email', '_DENY_SUBSCRIBED_TEXT', $array_subst);
			$msg_composer->setBodyLangText('email', "\n\n" . $_POST['subscribe_refuse'], array(), true);

			$msg_composer->setSubjectLangText('sms', '_DENY_SUBSCRIBED_SUBJECT_SMS', false);
			$msg_composer->setBodyLangText('sms', '_DENY_SUBSCRIBED_TEXT_SMS', $array_subst);

			// send message to the user subscribed
			createNewAlert('UserCourseInserted', 'subscribe', 'deny', '1', 'User course deny',
				$deny_user, $msg_composer, true);
		}
		Util::jump_to('index.php?r=' . $this->link_course . '/show&res=' . ($re ? 'ok' : 'err'));

	}

	public function unsubscriberequestsTask()
	{
		Util::get_js(Get::rel_path('base') . '/lib/js_utils.js', true, true);
		Util::get_js(Get::rel_path('lms') . '/admin/views/subscription/unsubscriberequests.js', true, true);

		$this->render('unsubscriberequests', array(
			'filter_text' => "",
			'num_subs_selected' => 0
		));

	}


	public function getunsubscribetabledataTask()
	{
		$op = Get::req('op', DOTY_STRING, "");
		if ($op == 'selectall') {
			$this->_getUnsubscribeSelectAll();
			return;
		}

		$startIndex = Get::req('startIndex', DOTY_INT, 0);
		$results = Get::req('results', DOTY_MIXED, Get::sett('visuItem', 25));
		$sort = Get::req('sort', DOTY_MIXED, 'userid');
		$dir = Get::req('dir', DOTY_MIXED, 'asc');

		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_course = Get::req('filter_course', DOTY_INT, 0);

		$filter = array();
		if ($filter_text != "") $filter['text'] = $filter_text;

		$courses_filter = FALSE;

		$ulevel = Docebo::user()->getUserLevelId();
		if ($ulevel != ADMIN_GROUP_GODADMIN) {
			require_once(_base_ . '/lib/lib.preference.php');
			$preference = new AdminPreference();
			$view = $preference->getAdminCourse(Docebo::user()->idst);
			$all_courses = false;
			if (isset($view['course'][0]))
				$all_courses = true;
			elseif (isset($view['course'][-1])) {
				require_once(_lms_ . '/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if (count($user_catalogue) > 0) {
					$courses = array(0);

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach ($courses as $id_course)
						if ($id_course != 0)
							$view['course'][$id_course] = $id_course;
				} elseif (Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			} else {
				$array_courses = array();
				$array_courses = array_merge($array_courses, $view['course']);

				if (!empty($view['coursepath'])) {
					require_once(_lms_ . '/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($view['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if (!empty($view['catalogue'])) {
					require_once(_lms_ . '/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach ($view['catalogue'] as $id_cat) {
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$view['course'] = array_merge($view['course'], $array_courses);
			}

			if (!$all_courses) $courses_filter = $view['course'];
			$filter['user_q'] = $preference->getAdminUsersQuery(Docebo::user()->getIdst(), 'user_id');
		}

		if ($filter_course > 0) {
			if ($courses_filter === FALSE) {
				$courses_filter = (int)$filter_course;
			} else {
				if (!in_array($filter_course, $courses_filter)) {
					$courses_filter = array();
				} else {
					$courses_filter = (int)$filter_course;
				}
			}
		}


		if (is_array($courses_filter)) $filter['course'] = $courses_filter;

		$total = $this->model->getUnsubscribeRequestsTotal($filter);
		if ($startIndex >= $total) {
			if ($total < $results) {
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

		$list = $this->model->getUnsubscribeRequestsList($pagination, $filter);

		//format models' data
		$records = array();
		if (is_array($list)) {
			foreach ($list as $record) {
				$id_unsub = (int)$record->user_id . '_' . $record->idCourse . '_' . $record->res_id . '_' . $record->r_type;
				$record->id = $id_unsub;
				$record->userid = Layout::highlight($this->acl_man->relativeId($record->userid), $filter_text);
				$record->firstname = Layout::highlight($record->firstname, $filter_text);
				$record->lastname = Layout::highlight($record->lastname, $filter_text);
				$record->request_date = Format::date($record->request_date, 'datetime');
				$record->del = 'ajax.adm_server.php?r=alms/subscription/deny_unsubscribe_request&id=' . $id_unsub;
				$records[] = $record;
			}
		}

		if (is_array($records)) {
			$output = array(
				'startIndex' => $startIndex,
				'recordsReturned' => count($records),
				'sort' => $sort,
				'dir' => $dir,
				'totalRecords' => $total,//$this->model->getTotalGroups($filter),
				'pageSize' => $results,//$rowsPerPage,
				'records' => $records
			);
		} else {
			$output['success'] = false;
		}

		echo $this->json->encode($output);
	}


	protected function _getUnsubscribeSelectAll()
	{
		$filter_text = Get::req('filter_text', DOTY_STRING, "");
		$filter_course = Get::req('filter_course', DOTY_INT, 0);

		$courses_filter = FALSE;

		$ulevel = Docebo::user()->user_level;
		if ($ulevel != ADMIN_GROUP_GODADMIN) {
			require_once(_base_ . '/lib/lib.preference.php');
			$preference = new AdminPreference();
			$view = $preference->getAdminCourse(Docebo::user()->idst);
			$all_courses = false;
			if (isset($view['course'][0]))
				$all_courses = true;
			elseif (isset($view['course'][-1])) {
				require_once(_lms_ . '/lib/lib.catalogue.php');
				$cat_man = new Catalogue_Manager();

				$user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
				if (count($user_catalogue) > 0) {
					$courses = array(0);

					foreach ($user_catalogue as $id_cat) {
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat);

						$courses = array_merge($courses, $catalogue_course);
					}

					foreach ($courses as $id_course)
						if ($id_course != 0)
							$view['course'][$id_course] = $id_course;
				} elseif (Get::sett('on_catalogue_empty', 'off') == 'on')
					$all_courses = true;
			} else {
				$array_courses = array();
				$array_courses = array_merge($array_courses, $view['course']);

				if (!empty($view['coursepath'])) {
					require_once(_lms_ . '/lib/lib.coursepath.php');
					$path_man = new Catalogue_Manager();
					$coursepath_course =& $path_man->getAllCourses($view['coursepath']);
					$array_courses = array_merge($array_courses, $coursepath_course);
				}
				if (!empty($view['catalogue'])) {
					require_once(_lms_ . '/lib/lib.catalogue.php');
					$cat_man = new Catalogue_Manager();
					foreach ($view['catalogue'] as $id_cat) {
						$catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
						$array_courses = array_merge($array_courses, $catalogue_course);
					}
				}
				$view['course'] = array_merge($view['course'], $array_courses);
			}

			if (!$all_courses) $courses_filter = $view['course'];
		}

		if ($filter_course > 0) {
			if ($courses_filter === FALSE) {
				$courses_filter = (int)$filter_course;
			} else {
				if (!in_array($filter_course, $courses_filter)) {
					$courses_filter = array();
				} else {
					$courses_filter = (int)$filter_course;
				}
			}
		}

		$filter = array();
		if ($filter_text != "") $filter['text'] = $filter_text;
		if (is_array($courses_filter)) $filter['course'] = $courses_filter;

		$output = $this->model->getUnsubscribeRequestsAll($filter);
		echo $this->json->encode($output);
	}


	public function accept_unsubscribe_requestTask()
	{
		$_id = Get::req('id', DOTY_ALPHANUM, "");
		if (!$_id) {
			//...
		}

		list($user_id, $idCourse, $res_id, $r_type) = explode("_", $_id);
		$smodel = new SubscriptionAlms();
		switch ($r_type) {
			case "course":
				{
					$res = $smodel->unsubscribeUser($user_id, $idCourse);
				}
				break;
			case "edition":
				{
					$res = $smodel->unsubscribeUser($user_id, $idCourse, $res_id);
				}
				break;
			case "classroom":
				{
					$res = $smodel->unsubscribeUser($user_id, $idCourse, false, $res_id);
				}
				break;
		}
		$output = array('success' => $res ? TRUE : FALSE);
		echo $this->json->encode($output);
	}


	public function deny_unsubscribe_requestTask()
	{
		$_id = Get::req('id', DOTY_ALPHANUM, "");
		if (!$_id) {
			//...
		}
		list($user_id, $idCourse, $res_id, $r_type) = explode("_", $_id);
		$smodel = new SubscriptionAlms();
		switch ($r_type) {
			case "course":
				{
					$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse);
				}
				break;
			case "edition":
				{
					$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse, $res_id);
				}
				break;
			case "classroom":
				{
					$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse, false, $res_id);
				}
				break;
		}

		$output = array('success' => $res ? TRUE : FALSE);
		echo $this->json->encode($output);
	}


	public function accept_unsubscribe_request_multiTask()
	{
		$_requests = Get::req('requests', DOTY_MIXED, FALSE);
		if (!$_requests) {
			//...
		}

		$res = TRUE;
		$smodel = new SubscriptionAlms();
		$list = explode(",", $_requests);
		foreach ($list as $request) {
			list($user_id, $idCourse, $res_id, $r_type) = explode("_", $request);
			switch ($r_type) {
				case "course":
					{
						$res = $smodel->unsubscribeUser($user_id, $idCourse);
					}
					break;
				case "edition":
					{
						$res = $smodel->unsubscribeUser($user_id, $idCourse, $res_id);
					}
					break;
				case "classroom":
					{
						$res = $smodel->unsubscribeUser($user_id, $idCourse, false, $res_id);
					}
					break;
			}
		}

		$output = array('success' => $res ? TRUE : FALSE);
		echo $this->json->encode($output);
	}


	public function deny_unsubscribe_request_multiTask()
	{
		$_requests = Get::req('requests', DOTY_MIXED, FALSE);
		if (!$_requests) {
			//...
		}

		$res = TRUE;
		$smodel = new SubscriptionAlms();
		$list = explode(",", $_requests);
		foreach ($list as $request) {
			list($user_id, $idCourse, $res_id, $r_type) = explode("_", $request);
			switch ($r_type) {
				case "course":
					{
						$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse);
					}
					break;
				case "edition":
					{
						$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse, $res_id);
					}
					break;
				case "classroom":
					{
						$res = $smodel->unsetUnsubscribeRequest($user_id, $idCourse, false, $res_id);
					}
					break;
			}
		}

		$output = array('success' => $res ? TRUE : FALSE);
		echo $this->json->encode($output);
	}


}

?>