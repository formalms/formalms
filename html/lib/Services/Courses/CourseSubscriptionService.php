<?php

namespace FormaLms\lib\Services\Courses;

class CourseSubscriptionService
{

    protected $baseModel;

    protected $permissions;

    protected $response = [];

    protected $reachedMaxUserSubscribed = false;

    protected $doceboUser;

    const LINK_COURSE = 'alms/course';

    public function __construct() {

        $this->baseModel = new \SubscriptionAlms();
        $this->doceboUser = \Docebo::user();
        $this->permissions = [
            'subscribe_course' => checkPerm('subscribe', true, 'course', 'lms'),
            'subscribe_coursepath' => checkPerm('subscribe', true, 'coursepath', 'lms'),
            'moderate' => checkPerm('moderate', true, 'course', 'lms'),
        ];
    }


    protected function add($selection, $courseType, $courseId) {

       
        if (!$this->permissions['subscribe_course']) {
            $this->setResponse('error',
                $this->_getErrorMessage('no permission'),
                'index.php?r=' . self::LINK_COURSE . '/show',
                'invalid'
            );

            return $this->response;
        } elseif (!$this->checkAdminLimit()) {

            $this->setResponse('error',
                Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
                'index.php?r=' . self::LINK_COURSE . '/show',
                'invalid'
            );

            return $this->response;
        }

        $courseManager = new \CourseAlms();

      
        switch($courseType) {

            case "edition" : 
                $this->baseModel->setIdEdition($id_course);
                break;

            default:
                $this->baseModel->setIdCourse($id_course);
                break;
        }
        
        
        $userSelected = $this->baseModel->acl_man->getAllUsersFromSelection($selection);
        $userAlredySubscribed = $model->loadUserSelectorSelection();
        $userSelected = array_diff($userSelected, $userAlredySubscribed);

        if ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            $toSubscribe = count($userSelected);

            $adminPreference = new \AdminPreference();
            $preference = $adminPreference->getAdminRules($this->doceboUser->getIdSt());
            if ($preference['admin_rules.limit_course_subscribe'] == 'on') {
                $userPreference = new \UserPreferences($this->doceboUser->getIdSt());
                $subscribedCount = $userPreference->getPreference('user_subscribed_count');
                if ($subscribedCount + $toSubscribe > $preference['admin_rules.max_course_subscribe']) {
                  
                    $this->setResponse('error',
                        Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
                        'index.php?r=' . self::LINK_COURSE . '/show',
                        'invalid'
                    );
    
                    return $this->response;
                }
            }
        }

        if ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            #require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new \AdminPreference();
            $adminUsers = $adminManager->getAdminUsers($this->doceboUser->getIdST());
            $userSelected = array_intersect($userSelected, $adminUsers);
        }

        $userSelected = $this->baseModel->acl_man->getUsersFromMixedIdst($user_selected);
        if (count($userSelected) == 0) {

            $this->setResponse('error',
                '_empty_selection',
                'index.php?r=' . self::LINK_COURSE . '/show'
            );

            return $this->response;
         }

            $sel_date_begin_validity = FormaLms\lib\Get::req('sel_date_begin_validity', DOTY_INT, 0) > 0;
            $sel_date_expire_validity = FormaLms\lib\Get::req('sel_date_expire_validity', DOTY_INT, 0) > 0;
            $date_begin_validity = $sel_date_begin_validity ? FormaLms\lib\Get::req('set_date_begin_validity', DOTY_STRING, '') : false;
            $date_expire_validity = $sel_date_expire_validity ? FormaLms\lib\Get::req('set_date_expire_validity', DOTY_STRING, '') : false;
            if ($date_begin_validity) {
                $date_begin_validity = Format::dateDb($date_begin_validity, 'date');
            }
            if ($date_expire_validity) {
                $date_expire_validity = Format::dateDb($date_expire_validity, 'date');
            }

            $select_level_mode = FormaLms\lib\Get::req('select_level_mode', DOTY_STRING, '');
            switch ($select_level_mode) {
                case 'students':
                        // subscribe the selection with the students level
                        require_once Forma::inc(_lms_ . '/lib/lib.course.php');
                        $course_info = $model->getCourseInfoForSubscription();

                        //check if the subscriber is a sub admin and, if true check it's limitation
                        $can_subscribe = true;
                        $subscribe_method = $course_info['subscribe_method'];
                        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                            $limited_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.limit_course_subscribe');
                            $max_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.max_course_subscribe');
                            $direct_subscribe = Docebo::user()->preference->getAdminPreference('admin_rules.direct_course_subscribe');

                            if ($limited_subscribe == 'on') {
                                $limited_subscribe = true;
                            } else {
                                $limited_subscribe = false;
                            }
                            if ($direct_subscribe == 'on') {
                                $direct_subscribe = true;
                            } else {
                                $direct_subscribe = false;
                            }
                        } else {
                            $limited_subscribe = false;
                            $max_subscribe = 0;
                            $direct_subscribe = true;
                        }

                        if ($can_subscribe) {
                            require_once Forma::inc(_lms_ . '/lib/lib.course.php');
                            $docebo_course = new DoceboCourse($id_course);

                            $level_idst = &$docebo_course->getCourseLevel($id_course);
                            if (count($level_idst) == 0 || $level_idst[1] == '') {
                                $level_idst = &$docebo_course->createCourseLevel($id_course);
                            }

                            $waiting = 0;
                            $user_subscribed = [];
                            $user_waiting = [];

                            if (!$direct_subscribe) {
                                $waiting = 1;
                            }

                            // do the subscriptions
                            $result = true;
                            $this->db->start_transaction();
                            foreach ($user_selected as $id_user) {
                                if (!$limited_subscribe || $max_subscribe) {
                                    //$this->acl_man->addToGroup($level_idst[3], $id_user);
                                    $this->_addToCourseGroup($level_idst[3], $id_user);

                                    if ($model->subscribeUser($id_user, 3, $waiting, $date_begin_validity, $date_expire_validity)) {
                                        --$max_subscribe;
                                    } else {
                                        $this->acl_man->removeFromGroup($level_idst[3], $id_user);
                                        $result = false;
                                    }
                                }
                            } //End While
                            $this->db->commit();

                            // Save limit preference for admin
                            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                                $to_subscribe = count($user_selected);

                                if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
                                    $user_pref->setPreference('user_subscribed_count', $subscribed_count + $to_subscribe);
                                }
                            }

                            reset($user_selected);
                            $send_alert = FormaLms\lib\Get::req('send_alert', DOTY_INT, 0);
                            //basically we will consider the alert as a checkbox, the initial state of the checkbox will be setted according to the alert status
                            if (!empty($user_selected) && $send_alert) {
                                require_once _base_ . '/lib/lib.eventmanager.php';

                                $uma = new UsermanagementAdm();

                                foreach (array_keys($user_selected) as $user_id) {
                                    $reg_code = null;
                                    if ($nodes = $uma->getUserFolders($user_id)) {
                                        $idst_oc = array_keys($nodes)[0];

                                        if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                                            $reg_code = sql_fetch_object($query)->idOrg;
                                        }
                                    }

                                    $array_subst = [
                                        '[url]' => FormaLms\lib\Get::site_url(),
                                        '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
                                        '[course]' => $course_info['name'],
                                        '[medium_time]' => $course_info['mediumTime'], //Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'))
                                        '[course_name]' => $course_info['name'],
                                        '[course_code]' => $course_info['code'],
                                    ];

                                    // message to user that is waiting
                                    $msg_composer = new EventMessageComposer();
                                    $msg_composer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
                                    $msg_composer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $array_subst);
                                    $msg_composer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $array_subst);

                                    // send message to the user subscribed
                                    createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', [$user_id], $msg_composer, $send_alert);

                                    if ($course_info['sendCalendar'] && $course_info['course_type'] == 'classroom') {
                                        $uinfo = Docebo::aclm()->getUser($user_id, false);
                                        $calendar = CalendarManager::getCalendarDataContainerForDateDays((int) $this->id_course, (int) $this->id_date, (int) $uinfo[ACL_INFO_IDST]);
                                        $msg_composer->setAttachments([$calendar->getFile()]);
                                    }
                                }
                            }
                        }

                        $result = $result > 0 ? '_operation_successful' : '_operation_failed';
                        Util::jump_to('index.php?r=' . $this->link . '/show&id_course=' . $id_course . '&id_edition=' . $id_edition . '&id_date=' . $id_date . '&res=' . $result);

                    break;
                default:
                    break;
            }

            $model->loadSelectedUser($user_selected);

            $course_info = $this->model->getCourseInfoForSubscription();
            $course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

            $this->render('level', [
                'id_course' => $id_course,
                'id_edition' => $id_edition,
                'id_date' => $id_date,
                'model' => $model,
                'course_info' => $cman->getInfo($id_course, $id_edition, $id_date),
                'num_subscribed' => count($user_selected),
                'send_alert' => FormaLms\lib\Get::req('send_alert', DOTY_INT, 0),
                'date_begin_validity' => $date_begin_validity,
                'date_expire_validity' => $date_expire_validity,
                'course_name' => $course_name,
            ]);
       
        
        /*
        else {
            if (isset($_GET['err']) && $_GET['err'] !== '') {
                UIFeedback::error(Lang::t(strtoupper($_GET['err']), 'subscription'));
            }

            $user_selector->show_user_selector = true;
            $user_selector->show_group_selector = true;
            $user_selector->show_orgchart_selector = true;
            $user_selector->show_orgchart_simple_selector = true;

            $user_alredy_subscribed = [];
            if (isset($_GET['load'])) {
                $user_selector->requested_tab = PEOPLEVIEW_TAB;
                $user_alredy_subscribed = $model->loadUserSelectorSelection();
                $user_selector->resetSelection($user_alredy_subscribed);
            }

            //find if the event manager is configured to send an alert or not in case of new subscription
            list($send_alert) = sql_fetch_row(sql_query('SELECT permission '
                . ' FROM %adm_event_class as ec'
                . ' JOIN %adm_event_manager as em'
                . " WHERE ec.idClass = em.idClass AND ec.class = 'UserCourseInserted' "));

            $course_info = $this->model->getCourseInfoForSubscription();
            $course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

            $this->render('add', [
                'id_course' => $id_course,
                'id_edition' => $id_edition,
                'id_date' => $id_date,
                'model' => $model,
                'course_info' => $cman->getInfo($id_course, $id_edition, $id_date),
                'user_selector' => $user_selector,
                'user_alredy_subscribed' => $user_alredy_subscribed,
                'send_alert' => ($send_alert == 'mandatory'),
                'course_name' => $course_name,
            ]);
        }

        */
        
    }

       /**
     * Set the permissions and / or returns the check results.
     *
     * @return bool returns false if no other users can be subscribed
     */
    protected function checkAdminLimit()
    {
        $res = true;

        if ($this->reachedMaxUserSubscribed) {
            $res = false;
        } elseif (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            $admin_pref = new AdminPreference();
            $pref = $admin_pref->getAdminRules(Docebo::user()->getIdSt());
     
            if ($pref['admin_rules.limit_course_subscribe'] == 'on') {
                $user_pref = new UserPreferences(Docebo::user()->getIdSt());
                $subscribed_count = $user_pref->getPreference('user_subscribed_count');
                if ($subscribed_count >= $pref['admin_rules.max_course_subscribe']) {
                    // $this->permissions['subscribe_course']=false;
                    // $this->permissions['subscribe_coursepath']=false;
                    $this->reachedMaxUserSubscribed = true;
                    $res = false;
                }
            }
        }

        return $res;
    }


    private function setResponse(string $result, $messages = [], string $backUrl = null, string $view = null) : array {

        $this->response['result'] = $result;
        $this->response['messages'] = $messages;

        if($backUrl) {
            $this->response['backUrl'] = $backUrl;
        }

        if($view) {
            $this->response['view'] = $view;
        }

        return $this->resèponse;

    }

    protected function _getErrorMessage($code)
    {
        return $this->_getMessage($code);
    }

    protected function _getMessage($code)
    {
        $message = '';
        switch ($code) {
            case 'no permission':
                $message = 'You don\'t have the required permission';
                break;
            case 'no options selected':
                $message = 'You have not selected any options';
                break;
        }

        return $message;
    }

}