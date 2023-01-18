<?php

namespace FormaLms\lib\Services\Courses;



require_once _lms_.'/lib/lib.course.php';
use FormaLms\lib\Get;

class CourseSubscriptionService
{

    protected $baseModel;

    protected $permissions;

    protected $response = [];

    protected $reachedMaxUserSubscribed = false;

    protected $doceboUser;

    protected $session;

    const LINK_COURSE = 'alms/course';

    public function __construct() {

        $this->baseModel = new \SubscriptionAlms();
        $this->doceboUser = \Docebo::user();
        $this->permissions = [
            'subscribe_course' => checkPerm('subscribe', true, 'course', 'lms'),
            'subscribe_coursepath' => checkPerm('subscribe', true, 'coursepath', 'lms'),
            'moderate' => checkPerm('moderate', true, 'course', 'lms'),
        ];

        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }


    public function add($selection, $courseType, $courseId, $params = []) : array {

       
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
                $this->baseModel->setIdEdition($courseId);
                break;

            case "date" : 
                $this->baseModel->setIdDate($courseId);
                break;

            default:
                $this->baseModel->setIdCourse($courseId);
                break;
        }
        
       
        $userSelected = $this->baseModel->getAclManager()->getAllUsersFromSelection($selection);
        $userAlredySubscribed = $this->baseModel->loadUserSelectorSelection();
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

        $userSelected = $this->baseModel->getAclManager()->getUsersFromMixedIdst($userSelected);
        if (count($userSelected) == 0) {

            $this->setResponse('error',
                '_empty_selection',
                'index.php?r=' . self::LINK_COURSE . '/show'
            );

            return $this->response;
         }

        $selDateBeginValidity = (bool) $params['sel_date_begin_validity'];
        $selDateExpireValidity = (bool) $params['sel_date_expire_validity'];
        $dateBeginValidity = $selDateBeginValidity ? $params['set_date_begin_validity'] : false;
        $dateExpireValidity = $selDateExpireValidity ? $params['set_date_expire_validity'] : false;
        if ($dateBeginValidity) {
            $dateBeginValidity = Format::dateDb($dateBeginValidity, 'date');
        }
        if ($dateExpireValidity) {
            $dateExpireValidity = Format::dateDb($dateExpireValidity, 'date');
        }

        $selectLevelMode = $params['select_level_mode'] ?? '';
        switch ($selectLevelMode) {
            case 'students':
                    // subscribe the selection with the students level
                    $courseInfo = $this->baseModel->getCourseInfoForSubscription();

                    //check if the subscriber is a sub admin and, if true check it's limitation
                    $canSubscribe = true;
                    $subscribeMethod = $courseInfo['subscribe_method'];
                    if ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                        $limitedSubscribe = $this->doceboUser->preference->getAdminPreference('admin_rules.limit_course_subscribe');
                        $maxSubscribe = $this->doceboUser->preference->getAdminPreference('admin_rules.max_course_subscribe');
                        $directSubscribe = $this->doceboUser->preference->getAdminPreference('admin_rules.direct_course_subscribe');

                        if ($limitedSubscribe == 'on') {
                            $limitedSubscribe = true;
                        } else {
                            $limitedSubscribe = false;
                        }
                        if ($directSubscribe == 'on') {
                            $directSubscribe = true;
                        } else {
                            $directSubscribe = false;
                        }
                    } else {
                        $limitedSubscribe = false;
                        $maxSubscribe = 0;
                        $directSubscribe = true;
                    }

                    if ($canSubscribe) {
                   
                        $doceboCourse = new \DoceboCourse($idCourse);

                        $levelIdst = $doceboCourse->getCourseLevel($idCourse);
                        if (count($levelIdst) == 0 || $levelIdst[1] == '') {
                            $levelIdst = $doceboCourse->createCourseLevel($idCourse);
                        }

                        $waiting = 0;
                        $userSubscribed = [];
                        $userWaiting = [];

                        if (!$directSubscribe) {
                            $waiting = 1;
                        }

                        // do the subscriptions
                        $result = true;
                        $this->baseModel->db->start_transaction();
                        foreach ($userSelected as $idUser) {
                            if (!$limitedSubscribe || $maxSubscribe) {
                           
                                $this->_addToCourseGroup($levelIdst[3], $idUser);

                                if ($this->baseModel->subscribeUser($idUser, 3, $waiting, $dateBeginValidity, $dateExpireValidity)) {
                                    --$maxSubscribe;
                                } else {
                                    $this->baseModel->getAclManager()->removeFromGroup($levelIdst[3], $idUser);
                                    $result = false;
                                }
                            }
                        } //End While
                        $this->baseModel->db->commit();

                        // Save limit preference for admin
                        if ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                            $toSubscribe = count($userSelected);

                            if ($preference['admin_rules.limit_course_subscribe'] == 'on') {
                                $userPreference->setPreference('user_subscribed_count', $subscribedCount + $toSubscribe);
                            }
                        }

                        reset($userSelected);
                        $sendAlert = (int) $params['send_alert'];
                        //basically we will consider the alert as a checkbox, the initial state of the checkbox will be setted according to the alert status
                        if (!empty($userSelected) && $sendAlert) {

                            $userManagement = new \UsermanagementAdm();

                            foreach (array_keys($userSelected) as $userId) {
                                $regCode = null;
                                if ($nodes = $userManagement->getUserFolders($userId)) {
                                    $idstOc = array_keys($nodes)[0];

                                    if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idstOc LIMIT 1")) {
                                        $regCode = sql_fetch_object($query)->idOrg;
                                    }
                                }

                                $arraySubstitions = [
                                    '[url]' => Get::getBaseUrl(),
                                    '[dynamic_link]' => getCurrentDomain($regCode) ?: Get::getBaseUrl(),
                                    '[course]' => $courseInfo['name'],
                                    '[medium_time]' => $courseInfo['mediumTime'], //Format::date(date("Y-m-d", time() + ($course_info['mediumTime']*24*60*60) ), 'date'))
                                    '[course_name]' => $courseInfo['name'],
                                    '[course_code]' => $courseInfo['code'],
                                ];

                                // message to user that is waiting
                                $msgComposer = new \EventMessageComposer();
                                $msgComposer->setSubjectLangText('email', '_NEW_USER_SUBSCRIBED_SUBJECT', false);
                                $msgComposer->setBodyLangText('email', '_NEW_USER_SUBSCRIBED_TEXT', $arraySubstitions);
                                $msgComposer->setBodyLangText('sms', '_NEW_USER_SUBSCRIBED_TEXT_SMS', $arraySubstitions);

                                // send message to the user subscribed
                                createNewAlert('UserCourseInserted', 'subscribe', 'insert', '1', 'User subscribed', [$userId], $msgComposer, $sendAlert);

                                if ($courseInfo['sendCalendar'] && $courseInfo['course_type'] == 'classroom') {
                                    $uinfo = \Docebo::aclm()->getUser($userId, false);
                                    $idDate = 0;
                                    
                                    switch($courseType) {

                                        case "date" : 
                                            $idDate = $courseId;
                                            $courseId = 0;
                                            break;
                            
                                        default:
                                            
                                            break;
                                    }
                                    $calendar = \CalendarManager::getCalendarDataContainerForDateDays((int) $courseId, (int) $idDate, (int) $uinfo[ACL_INFO_IDST]);
                                    $msgComposer->setAttachments([$calendar->getFile()]);
                                }
                            }
                        }
                    }

                    $result = $result > 0 ? '_operation_successful' : '_operation_failed';
                    
                    return $this->setResponse('ok',
                        $result,
                        'index.php?r=' . self::LINK_COURSE . '/show'
                    );
                break;
            default:
                break;
        }


        if($params['viewParams']) {
            //find if the event manager is configured to send an alert or not in case of new subscription

            $courseInfo = $this->baseModel->getCourseInfoForSubscription();
            $courseName = ($courseInfo['code'] !== '' ? '[' . $courseInfo['code'] . '] ' : '') . $courseInfo['name'];

            switch($courseType) {

                case "date" : 
                    $idDate = $courseId;
                    $courseId = 0;
                    $idEdition = 0;
                    break;

                case "edition" : 
                    $idEdition = $courseId;
                    $courseId = 0;
                    $idDate = 0;
                    break;
    
                default:
                $idDate = 0;
              
                $idEdition = 0;
                    break;
            }
            $moreCourseInfo = $courseManager->getInfo($courseId, $idEdition, $idDate);

            $this->baseModel->loadSelectedUser($userSelected);
            return [
                'id_course' => $courseId,
                'id_edition' => $idEdition,
                'id_date' => $idDate,
                'model' => $this->baseModel,
                'course_info' => $moreCourseInfo ,
                'num_subscribed' => count($userSelected),
                'post_url' => 'alms/subscription',
                'user_alredy_subscribed' => $userAlredySubscribed,
                'course_name' => $courseName,
            ];
        }

        return [];
        
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
        } elseif ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            $adminPreference = new \AdminPreference();
            $preference = $adminPreference->getAdminRules($this->doceboUser->getIdSt());
     
            if ($preference['admin_rules.limit_course_subscribe'] == 'on') {
                $userPreference = new \UserPreferences($this->doceboUser->getIdSt());
                $subscribedCount = $userPreference->getPreference('user_subscribed_count');
                if ($subscribedCount >= $preference['admin_rules.max_course_subscribe']) {
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

        return $this->response;

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

    protected function _addToCourseGroup($idGroup, $idUser)
    {
        \Docebo::aclm()->addToGroup($idGroup, $idUser);
    }

    public function getSubscribed($courseId, $courseType) {

        switch($courseType) {

            case "edition" : 
                $this->baseModel->setIdEdition($courseId);
                break;

            case "date" : 
                $this->baseModel->setIdDate($courseId);
                break;

            default:
                $this->baseModel->setIdCourse($courseId);
                break;
        }
        return  $this->baseModel->loadUserSelectorSelection();
    }


    public function checkSelection($selection, $params=[]) {
        

        $userSelected = $this->baseModel->getAclManager()->getAllUsersFromSelection($selection); //$acl_man->getAllUsersFromIdst($_selection);
    

        if ($this->doceboUser->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new \AdminPreference();
            $adminTree = $adminManager->getAdminTree($this->doceboUser->getIdST());
            $adminUsers = $this->acl_man->getAllUsersFromIdst($adminTree);

            $userSelected = array_intersect($userSelected, $adminUsers);

            $toSubscribe = count($userSelected);

            if ($adminTree['admin_rules.limit_course_subscribe'] == 'on') {
                $userPref = new \UserPreferences(Docebo::user()->getIdSt());
                $subscribedCount = $userPref->getPreference('user_subscribed_count');
                if ($subscribedCount + $toSubscribe > $adminTree['admin_rules.max_course_subscribe']) {
                    $this->setResponse('error',
                        Lang::t('_SUBSCRIBE_LIMIT_REACHED', 'subscribe'),
                        'index.php?r=' . self::LINK_COURSE . '/show',
                        'invalid'
                    );

                    return $this->response;
                }
            }

        }

        if (count($userSelected) == 0) {
            Util::jump_to('index.php?r=adm/userselector/show&instance=multiplecoursesubscription');
        }

        return $userSelected;
      
    }


    public function multipleAdd($selection, $params=[]) {
        $idCat = $params['id_cat'];


        $this->baseModel->setUserData(urlencode(\Util::serialize($selection)));
   
        if($params['viewParams']) {
                return [
                    'model' => $this->baseModel,
                    'id_cat' => $idCat,
                    'course_selector' => new \Selector_Course(),
                    'user_selection' => $this->baseModel->getUserData()
                ];

            }
       
        return [];
    }

}