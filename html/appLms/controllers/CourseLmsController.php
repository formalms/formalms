<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once Forma::inc(_base_ . '/lib/lib.json.php');
require_once Forma::inc(_base_ . '/lib/lib.user_profile.php');
require_once Forma::inc(_adm_ . '/lib/lib.myfiles.php');

class CourseLmsController extends LmsController
{
    /**
     * @var UserProfileData the instance of the profile data manager
     */
    public $userProfileDataManager;

    public const STUDENTNOTADMITTED = [_CUS_SUBSCRIBED => _USER_STATUS_SUBS,
                                    _CUS_BEGIN => _USER_STATUS_BEGIN,
                                    _CUS_SUSPEND => _USER_STATUS_SUSPEND,
                                    _CUS_END => _USER_STATUS_END, ];

    public function init()
    {
        require_once _adm_ . '/lib/lib.field.php';

        /* @var Services_JSON json */
        $this->json = new Services_JSON();
        $this->_mvc_name = 'course';
        $this->permissions = [
            'view' => true,
            'mod' => true,
        ];

        $this->userProfileDataManager = new UserProfileData();

        if (!Docebo::user()->isAnonymous()) {
            define('_PATH_COURSE', '/appLms/' . FormaLms\lib\Get::sett('pathcourse'));

            require_once _lms_ . '/lib/lib.levels.php';
        } elseif (!$this->session->has('idCourse') || empty($this->session->get('idCourse'))) {
            errorCommunication($lang->def('_FIRSTACOURSE'));
        } else {
            echo "You can't access";
        }
    }

    public function infocourse()
    {
        checkPerm('view_info', false, 'course');
        $mod_perm = checkPerm('mod', true);
        try {
            $acl_man = Docebo::user()->getAclManager();
            $lang = &DoceboLanguage::createInstance('course');
            $course = $GLOBALS['course_descriptor']->getAllInfo();
            $levels = CourseLevel::getLevels();
        } catch (\Exception $exception) {
        }

        $status_lang = [
            0 => $lang->def('_NOACTIVE'),
            1 => $lang->def('_ACTIVE'),
            2 => $lang->def('_CST_CONFIRMED'),
            3 => $lang->def('_CST_CONCLUDED'),
            4 => $lang->def('_CST_CANCELLED'),
        ];

        $difficult_lang = [
            'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
            'easy' => $lang->def('_DIFFICULT_EASY'),
            'medium' => $lang->def('_DIFFICULT_MEDIUM'),
            'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
            'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'),
        ];

        $subs_lang = [
            0 => $lang->def('_COURSE_S_GODADMIN'),
            1 => $lang->def('_COURSE_S_MODERATE'),
            2 => $lang->def('_COURSE_S_FREE'),
            3 => $lang->def('_COURSE_S_SECURITY_CODE'),
        ];

        $course['difficulty_translate'] = $difficult_lang[$course['difficult']];
        $course['subscribe_method_translate'] = $subs_lang[$course['subscribe_method']];

        if ($this->session->get('levelCourse') >= 4) {
            $course['show_quota'] = true;
            $quota = [];
            $max_quota = $GLOBALS['course_descriptor']->getQuotaLimit();
            $actual_space = $GLOBALS['course_descriptor']->getUsedSpace();

            $actual_space = number_format(($actual_space / (1024 * 1024)), '2');

            $percent = 0;
            if ($max_quota > 0) {
                $percent = ($actual_space != 0 ? number_format((($actual_space / $max_quota) * 100), '2') : '0');
            }

            $quota['percent'] = $percent;
            $quota['actual_space'] = $actual_space;
            $quota['max_quota'] = $max_quota;
            $quota['unlimited'] = $max_quota == USER_QUOTA_UNLIMIT;

            $course['quota'] = $quota;
        }

        $obj_course = new DoceboCourse($this->session->get('idCourse'));
        $info_course = $obj_course->getAllInfo();
        $id_date = CourseLms::getMyDateCourse($this->session->get('idCourse'));
        $info_date = ($info_course['course_type'] == 'classroom' ? CourseLms::getInfoDate($id_date) : '');

        foreach ($levels as $key => $level) {
            if ($course['level_show_user'] & (1 << $key)) {
                $course['show_users'] = true;
                if ($info_course['course_type'] == 'classroom') {
                    if ($this->session->get('levelCourse') == 7) {
                        $users = &$acl_man->getUsersMappedData(Man_Course::getIdUserOfLevel($this->session->get('idCourse'), $key, $this->session->get('idEdition')));
                    } else {
                        $users = &$acl_man->getUsersMappedData(CourseLms::getIdUserOfLevelDate($this->session->get('idCourse'), $key, $id_date));
                    }
                } else {
                    $users = &$acl_man->getUsersMappedData(Man_Course::getIdUserOfLevel($this->session->get('idCourse'), $key, $this->session->get('idEdition')));
                }
                $course[$level] = ['name' => $level, 'users' => $users];
            }
        }

        if (true || $course['show_extra_info'] == '1') {
            $course['show_extra_info'] = true || $course['show_extra_info'] == '1';
            $course['status'] = $status_lang[$course['status']];
            $course['completion_method'] = $course['permCloseLO'] ? $lang->def('_MANUALACTION') : $lang->def('_ENDOBJECT');

            $course['cannot_enter'] = [];
            foreach (self::STUDENTNOTADMITTED as $studentStatus => $langStatus) {
                if ($this->statusNoEnter($course['userStatusOp'], $studentStatus)) {
                    $course['cannot_enter'][] = $lang->def($langStatus);
                }
            }
        }

        //checking if  message for enabled current user
        $ma = new Man_MiddleArea();
        $course['can_access_messages'] = $ma->currentCanAccessObj('mo_message');

        $data = [
            'templatePath' => getPathTemplate(),
            'route' => [
                'message' => ['url' => 'index.php?r=lms/message/directWrite'],
                'profile' => ['url' => 'index.php?r=lms/course/viewprofile'],
            ],
            'course' => $course,
            'info_date' => $info_date,
        ];

        if ($mod_perm) {
            $data['route']['edit'] = ['url' => 'index.php?r=lms/course/modcourse'];
        }
        $this->render('infocourse', $data);
    }

    public function modcourse()
    {
        checkPerm('mod');

        $data['lang_c'] = &DoceboLanguage::createInstance('course');
        $lang = &DoceboLanguage::createInstance('course');
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        $data['id_course'] = $session->get('idCourse');

        $data['levels'] = CourseLevel::getTranslatedLevels();
        $data['array_lang'] = Docebo::langManager()->getAllLangCode();
        $data['difficult_lang'] = [
            'veryeasy' => $lang->def('_DIFFICULT_VERYEASY'),
            'easy' => $lang->def('_DIFFICULT_EASY'),
            'medium' => $lang->def('_DIFFICULT_MEDIUM'),
            'difficult' => $lang->def('_DIFFICULT_DIFFICULT'),
            'verydifficult' => $lang->def('_DIFFICULT_VERYDIFFICULT'),
        ];

        $query_course = '
            SELECT code, name, description, lang_code, status, level_show_user, subscribe_method, 
                linkSponsor, mediumTime, permCloseLO, userStatusOp, difficult, 
                show_progress, show_time, show_extra_info, show_rules, date_begin, date_end, valid_time 
            FROM %lms_course
            WHERE idCourse = "' . $data['id_course'] . '"';
        $data['course'] = sql_fetch_array(sql_query($query_course));

        foreach (self::STUDENTNOTADMITTED as $studentStatus => $langStatus) {
            $data['cannot_enter_status'][] = ['value' => $this->statusNoEnter($data['course']['userStatusOp'], $studentStatus), 'lang' => $langStatus];
        }
        $this->render('modinfocourse', $data);
    }

    public function upcourse()
    {
        checkPerm('mod');

        $array_lang = Docebo::langManager()->getAllLangCode();
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $user_status = 0;
        if ($this->request->request->has('user_status')) {
            foreach ($this->request->get('user_status') as $status => $v) {
                $user_status |= (1 << $status);
            }
        }
        $file_sponsor = '';
        $file_logo = '';
        $re = true;
        $show_level = 0;
        if ($this->request->request->has('course_show_level')) {
            foreach ($this->request->get('course_show_level') as $lv => $v) {
                $show_level |= (1 << $lv);
            }
        }
        $query_course = '
	                    UPDATE %lms_course 
	                    SET code = "' . $this->request->get('course_code') . '", 
                        name = "' . $this->request->get('course_name') . '", 
                        description = "' . $this->request->get('course_descr') . '", 
                        lang_code = "' . $array_lang[$this->request->get('course_lang')] . '", 
                        status = "' . (int) $this->request->get('course_status') . '", 
                        level_show_user = "' . $show_level . '", 
                        mediumTime = "' . $this->request->get('course_medium_time') . '",
                        permCloseLO = "' . $this->request->get('course_em') . '", 
                        userStatusOp = "' . $user_status . '", 
                        difficult = "' . $this->request->get('course_difficult') . '", 
                        show_progress = "' . ($this->request->request->has('course_progress') ? 1 : 0) . '", 
                        show_time = "' . ($this->request->request->has('course_time') ? 1 : 0) . '", 
                        show_extra_info = "' . ($this->request->request->has('course_advanced') ? 1 : 0) . '", 
                        show_rules = "' . (int) $this->request->get('course_show_rules') . '" 
                    WHERE idCourse = "' . $session->get('idCourse') . '"';
        if (!sql_query($query_course)) {
            $re = false;
        }

        $acl_man = &Docebo::user()->getAclManager();
        // send alert
        require_once _base_ . '/lib/lib.eventmanager.php';

        $msg_composer = new EventMessageComposer();

        $msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT_MODCOURSE_INFO', false);
        $msg_composer->setBodyLangText('email', '_ALERT_TEXT_MODCOURSE_INFO', ['[url]' => FormaLms\lib\Get::site_url(),
            '[course_code]' => $this->request->get('course_code'),
            '[course]' => $this->request->get('course_name'), ]);

        $msg_composer->setBodyLangText('sms', '_ALERT_TEXT_MODCOURSE_INFO_SMS', ['[url]' => FormaLms\lib\Get::site_url(),
            '[course_code]' => $this->request->get('course_code'),
            '[course]' => $this->request->get('course_name'), ]);

        require_once _lms_ . '/lib/lib.course.php';
        $course_man = new Man_Course();
        $recipients = $course_man->getIdUserOfLevel($session->get('idCourse'));

        createNewAlert('CoursePorpModified',
            'course',
            'add',
            '1',
            'Inserted course ' . $this->request->get('course_name') ,
            $recipients,
            $msg_composer);

        Util::jump_to('index.php?r=lms/course/infocourse&result=' . ($re ? 'ok' : 'err'));
    }

    private function statusNoEnter($perm, $status)
    {
        return $perm & (1 << $status);
    }

    public function viewprofile()
    {
        $idUser = FormaLms\lib\Get::gReq('id_user');

        $acl_man = Docebo::user()->getAclManager();

        $user = $acl_man->getUser($idUser, false);

        $last_view = $this->userProfileDataManager->getUserProfileViewList($idUser, 15);
        $friend_list = &$this->userProfileDataManager->getUserFriend($idUser);
        $user_stat = $this->userProfileDataManager->getUserStats($idUser);
        $ma = new Man_MiddleArea();
        $can_access_messages = $ma->currentCanAccessObj('mo_message');

        $data = [
            'user' => $acl_man->getUserMappedData($user),
            'lastViews' => $last_view,
            'friendsList' => $friend_list,
            'userStats' => $user_stat,
            'templatePath' => getPathTemplate(),
            'route' => [
                'message' => ['url' => 'index.php?r=lms/message/directWrite'],
                'profile' => ['url' => 'index.php?r=lms/course/viewprofile'],
            ],
            'can_access_messages' => $can_access_messages,
        ];

        $this->render('viewprofile', $data);
    }
}
