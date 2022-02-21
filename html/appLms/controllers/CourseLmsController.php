<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
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
            define('_PATH_COURSE', '/appLms/' . Get::sett('pathcourse'));

            require_once $GLOBALS['where_lms'] . '/lib/lib.levels.php';
        } elseif (!isset($_SESSION['idCourse'])) {
            errorCommunication($lang->def('_FIRSTACOURSE'));
        } else {
            echo "You can't access";
        }
    }

    public function infocourse()
    {
        checkPerm('view_info', false, 'course');

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

        if ($_SESSION['levelCourse'] >= 4) {
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

        $obj_course = new DoceboCourse($_SESSION['idCourse']);
        $info_course = $obj_course->getAllInfo();
        $id_date = CourseLms::getMyDateCourse($_SESSION['idCourse']);
        $info_date = ($info_course['course_type'] == 'classroom' ? CourseLms::getInfoDate($id_date) : '');

        foreach ($levels as $key => $level) {
            if ($course['level_show_user'] & (1 << $key)) {
                $course['show_users'] = true;
                if ($info_course['course_type'] == 'classroom') {
                    if ($_SESSION['levelCourse'] == 7) {
                        $users = &$acl_man->getUsersMappedData(Man_Course::getIdUserOfLevel($_SESSION['idCourse'], $key, $_SESSION['idEdition']));
                    } else {
                        $users = &$acl_man->getUsersMappedData(CourseLms::getIdUserOfLevelDate($_SESSION['idCourse'], $key, $id_date));
                    }
                } else {
                    $users = &$acl_man->getUsersMappedData(Man_Course::getIdUserOfLevel($_SESSION['idCourse'], $key, $_SESSION['idEdition']));
                }
                $course[$level] = ['name' => $level, 'users' => $users];
            }
        }

        if (true || $course['show_extra_info'] == '1') {
            $course['show_extra_info'] = true || $course['show_extra_info'] == '1';
            $course['status'] = $status_lang[$course['status']];
            $course['completion_method'] = $course['permCloseLO'] ? $lang->def('_MANUALACTION') : $lang->def('_ENDOBJECT');

            $course['cannot_enter'] = [];
            if ($this->statusNoEnter($course['userStatusOp'], _CUS_SUBSCRIBED)) {
                $course['cannot_enter'][] = $lang->def('_USER_STATUS_SUBS');
            }
            if ($this->statusNoEnter($course['userStatusOp'], _CUS_BEGIN)) {
                $course['cannot_enter'][] = $lang->def('_USER_STATUS_BEGIN');
            }
            if ($this->statusNoEnter($course['userStatusOp'], _CUS_SUSPEND)) {
                $course['cannot_enter'][] = $lang->def('_USER_STATUS_SUSPEND');
            }
            if ($this->statusNoEnter($course['userStatusOp'], _CUS_END)) {
                $course['cannot_enter'][] = $lang->def('_USER_STATUS_END');
            }

            $course['cannot_enter'][] = $lang->def('_USER_STATUS_BEGIN');
            $course['cannot_enter'][] = $lang->def('_USER_STATUS_END');
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

        $this->render('infocourse', $data);
    }

    private function statusNoEnter($perm, $status)
    {
        return $perm & (1 << $status);
    }

    public function viewprofile()
    {
        $idUser = Get::gReq('id_user');

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
