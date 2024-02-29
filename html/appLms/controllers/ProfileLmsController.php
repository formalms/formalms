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

if (\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous()) {
    exit("You can't access!");
}

require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.user_profile.php');
require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.urlmanager.php');

class ProfileLmsController extends LmsController
{
    protected $db;
    protected $model;
    protected $json;
    protected $aclManager;

    protected $max_dim_avatar;

    public function init()
    {
        require_once _base_ . '/lib/lib.json.php';
        $this->db = \FormaLms\db\DbConn::getInstance();
        $this->model = new ProfileLms();
        $this->json = new Services_JSON();
        $this->aclManager = \FormaLms\lib\Forma::getAcl()->getACLManager();
        $this->max_dim_avatar = 150;

        Events::listen('lms.layout.selecting', function($event) {

            $layout = $event['layout'];
            $layout = 'lms_user';
            $event['layout'] = $layout;
        }); 
    
    }

    protected function _profileBackUrl()
    {
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);
        $type = FormaLms\lib\Get::req('type', DOTY_STRING, 'false');
        $from = FormaLms\lib\Get::req('from', DOTY_INT, 0);
        $back_my_friend = FormaLms\lib\Get::req('back', DOTY_INT, 0);
        if ($type !== 'false') {
            if ($from == 0) {
                return getBackUi('index.php?modname=profile&op=profile&id_user=' . $id_user . '&ap=goprofile', Lang::t('_BACK', 'standard'));
            } else {
                return getBackUi('index.php?modname=myfiles&op=myfiles&working_area=' . $type, Lang::t('_BACK', 'standard'));
            }
        }
        if ($back_my_friend) {
            return getBackUi('index.php?modname=myfriends&op=myfriends', Lang::t('_BACK', 'standard'));
        }

        return false;
    }

    public function showProfile($selected_tab = '_PROFILE', $error_array = false){
        $profile_data = $this->getUserProfileData();
        $profile_data['tabs'] =  [
            '_PROFILE'=> ['translation'=>Lang::t('_PROFILE','profile'), 'enabled'=>true],
            '_PASSWORD'=>['translation'=>Lang::t('PASSWORD','standard'), 'enabled'=>(FormaLms\lib\Get::sett('profile_modify') != 'disallow')],
            '_PRIVACYPOLICIES'=> ['translation'=>Lang::t('_PRIVACYPOLICIES','privacypolicies'), 'enabled'=>(FormaLms\lib\Get::sett('privacy_policy') == 'on')]
        ];
        $profile_data['selected'] = $selected_tab;
        $profile_data['modify_profile'] = (FormaLms\lib\Get::sett('profile_modify') == 'allow');
        $url_man = UrlManager::getInstance();
        $url_man->setStdQuery('r=lms/profile/show');
        $profile_data['modify_url'] = $url_man->getUrl('ap=mod_profile');


        if ($profile_data['tabs']['_PASSWORD']['enabled']) {
            $profile_data['password_ui'] = $this->passwordMask($error_array);
        }

        $this->render('tab_profile', $profile_data);
        return;
    }

    public function passwordMask($error_array = false){
        $user_manager = new UserManager();
        $url_man = UrlManager::getInstance();
        $url = $url_man->getUrl('ap=savepwd');
        return $user_manager->getElapsedPassword($url, $error_array);
    }

    public function show()
    {

        $ap = FormaLms\lib\Get::req('ap', DOTY_STRING, $start_action);
        require_once _lms_ . '/lib/lib.lms_user_profile.php';

        switch ($ap) {
            case 'saveinfo':
                $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdST();
                $profile = new LmsUserProfile($id_user);
                $profile->init('profile', 'framework', 'r=lms/profile/show', 'ap');
                $profile->saveInfoProfile();
                return $this->showProfile();
                break;
            case 'savepwd':
                $discard = FormaLms\lib\Get::req('undo', DOTY_STRING) == Lang::t('_UNDO');
                if ($discard) {
                    $this->showProfile();
                } else {
                    $user_manager = new UserManager();
                    $error = $user_manager->saveElapsedPassword();
                    if ($error['error'] == true) {
                        $this->showProfile('_PASSWORD', $error['msg']);
                    } else {
                        $this->showProfile();
                    }
                }
                break;
            default:
                return $this->showProfile();
                break;

        };
        return;
/*
                 require_once _lms_ . '/lib/lib.lms_user_profile.php';

                $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdST();
                $profile = new LmsUserProfile($id_user);
                $profile->init('profile', 'framework', 'r=lms/profile/show', 'ap');

                $_check = false;
                if (!defined('LMS')) {
                    $_check = checkRole('/lms/course/public/profile/mod', true);
                } else {
                    $_check = checkPerm('mod', true, 'profile', 'lms');
                }
                if ($_check) {
                    $profile->enableEditMode();
                }


                echo $profile->getTitleArea();
                echo $profile->getHead();
                echo $profile->getHead();
                if (FormaLms\lib\Get::sett('profile_modify') == 'limit') {
                    echo $profile->performAction(false, 'mod_password');
                } elseif (FormaLms\lib\Get::sett('profile_modify') == 'allow') {
                    echo $profile->performAction();
               }
                echo $this->_profileBackUrl();
                echo $profile->getFooter();

                echo $profile->getPrivacy();*/
    }

    private function getUserProfileData(){
        $user = \FormaLms\lib\FormaUser::getCurrentUser();
        $idUser = \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt();
        $uploaded_file_size = \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getUploadedFileSize();
        $uploaded_file_size =  number_format(($uploaded_file_size / (1024 * 1024)), '2');
        $max_quota = FormaLms\lib\Get::sett('user_quota');

        $userProfileDataManager = new UserProfileData();
        $last_view = $userProfileDataManager->getUserProfileViewList($idUser, 15);
        $user_stat = $userProfileDataManager->getUserStats($idUser);
        $user_custom_fields = $user->loadUserCustomFields();
        $user_manager = $user->getManagerUser();
        $course_as_teacher = $userProfileDataManager->getCourseAsTeacher($idUser);
        $course_as_tutor = $userProfileDataManager->getCourseAsTutor($idUser);
        $course_as_mentor =   $userProfileDataManager->getCourseAsMentor($idUser);


        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.middlearea.php');
        $ma = new Man_MiddleArea();
        $can_access_messages = $ma->currentCanAccessObj('mo_message');
        $user = $this->aclManager->getUser($idUser, false);
        $data = [
            'user' => $this->aclManager->getUserMappedData($user),
            'user_custom_fields' => $user_custom_fields,
            'lastViews' => $last_view,
            'userStats' => $user_stat,
            'uploaded_file_size' => $uploaded_file_size,
            'max_quota' => $max_quota,
            'course_as_teacher' => $course_as_teacher,
            'course_as_tutor' => $course_as_tutor,
            'course_as_mentor' => $course_as_mentor,
            'templatePath' => getPathTemplate(),
            'route' => [
                'message' => ['url' => 'index.php?r=lms/message/directWrite'],
                'profile' => ['url' => 'index.php?r=lms/course/viewprofile'],
            ],
            'can_access_messages' => $can_access_messages,
            'user_manager' => $user_manager,
        ];
        return $data;
    }

    public function modProfile(){
        require_once _lms_ . '/lib/lib.lms_user_profile.php';

        $id_user = \FormaLms\lib\FormaUser::getCurrentUser()->getIdST();
        $profile = new LmsUserProfile($id_user);
        $profile->init('profile', 'framework', 'r=lms/profile/show', 'ap');

        echo $profile->getModUser();
    }

    public function renewalpwd()
    {
        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.usermanager.php');
        $user_manager = new UserManager();

        $_title = '';
        $_content = '';

        $url = 'index.php?r=lms/profile/renewalpwd'; //'index.php?modname=profile&amp;op=renewalpwd'

        if ($user_manager->clickSaveElapsed()) {
            $error = $user_manager->saveElapsedPassword();
            if ($error['error'] == true) {
                $res = \FormaLms\lib\FormaUser::getCurrentUser()->isPasswordElapsed();

                if ($res == 2) {
                    $_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
                } else {
                    $_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));
                }

                $_content = $user_manager->getElapsedPassword($url, $error['msg']);
            } else {
                $this->session->remove('must_renew_pwd');
                $this->session->save();
                //Util::jump_to('index.php?r=lms/profile/show');
                $user = new \FormaLms\lib\FormaUser(\FormaLms\lib\FormaUser::getCurrentUser()->getUserId(), 'public_area');
                $homepageAdm = new HomepageAdm();
                switch ($homepageAdm->saveUser($user)) {
                    case MANDATORY_FIELDS:
                        $this->session->set('request_mandatory_fields_compilation', 1);
                        $this->session->save();
                        break;
                    case USER_SAVED:
                    default:
                        break;
                }
                Util::jump_to('index.php');
            }
        } else {
            $this->session->set('must_renew_pwd', 1);
            $this->session->save();
            $res = \FormaLms\lib\FormaUser::getCurrentUser()->isPasswordElapsed();
            if ($res == 2) {
                $_title = getTitleArea(Lang::t('_CHANGEPASSWORD', 'profile'));
            } else {
                $_title = getTitleArea(Lang::t('_TITLE_CHANGE', 'profile'));
            }
            $_content = $user_manager->getElapsedPassword($url);
        }

        //view part
        echo $_title . '<div class="std_block">' . $_content . '</div>';
    }

    public function credits()
    {
        require_once _lms_ . '/lib/lib.course.php';
        //		$str = '<h2 class="heading">' . Lang::t ( '_CREDITS', 'catalogue' ) . '</h2>' . '<div class="content">';
        $str = '';
        $period_start = '';
        $period_end = '';

        // extract checking period
        $year = date('Y');
        $p_list = [];
        $p_selected = FormaLms\lib\Get::pReq('credits_period', DOTY_INT, 0);
        $p_res = sql_query('SELECT * FROM %lms_time_period ORDER BY end_date DESC, start_date DESC');
        if (sql_num_rows($p_res) > 0) {
            while ($obj = sql_fetch_object($p_res)) {
                if ($p_selected == 0) {
                    $p_selected = $obj->id_period;
                }
                $p_list[$obj->id_period] = Format::date($obj->start_date, 'date') . ' - ' . Format::date($obj->end_date, 'date');
                if ($p_selected == $obj->id_period) {
                    $period_start = $obj->start_date;
                    $period_end = $obj->end_date;
                }
            }
        }

        if (count($p_list) <= 0) {
            $p_list['0'] = Lang::t('_NO_PERIODS', 'catalogue');
        }
        if (!array_key_exists($p_selected, $p_list)) {
            $p_selected = 0;
        }
        if ($p_selected == 0) {
            $p_selected = false;
        }

        // extract courses which have been completed in the considered period and the credits associated
        $course_type_trans = getCourseTypes();
        $query = 'SELECT c.idCourse, c.name, c.course_type, c.credits, cu.status ' . ' FROM %lms_course as c ' . ' JOIN %lms_courseuser as cu ' . ' ON (cu.idCourse = c.idCourse) WHERE cu.idUser=' . (int) \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt() . " AND c.course_type IN ('" . implode("', '", array_keys($course_type_trans)) . "') " . " AND cu.status = '" . _CUS_END . "' " . ($period_start != '' ? " AND cu.date_complete > '" . $period_start . "' " : '') . ($period_end != '' ? " AND cu.date_complete < '" . $period_end . "' " : '') . ' ORDER BY c.name';
        $res = sql_query($query);

        $course_data = [];
        while ($obj = sql_fetch_object($res)) {
            switch ($obj->course_type) {
                case 'elearning':
                    $course_data['elearning'][$obj->idCourse] = $obj;
                    break;
                case 'classroom':
                case 'blended':
                    $course_data['classroom'][$obj->idCourse] = $obj;
                    break;
            }
        }

        // draw tables
        $no_cdata = true;

        $table = '<div class="table-credit-wrapper">';
        if (count($course_data) > 0) {
            $table .= '
                    <table class="table-credit">
                        <thead>
                            <tr class="table-credit__row table-credit__row--head">
                                <td>' . Lang::t('_COURSE', 'catalogue') . '</td>
                                <td>' . Lang::t('_CREDITS', 'catalogue') . '</td>
                            </tr>
                        </thead>
                        <tbody>';

            // BUG: LRZ no count
            // #19820
            $total = 0;
            foreach ($course_data as $ctype => $cdata) {
                if (count($cdata) > 0) {
                    $no_cdata = false;

                    //   $total = 0;
                    foreach ($cdata as $id_course => $data) {
                        $table .= '<tr class="table-credit__row">
                            <td>
                                ' . $data->name . '
                            </td>
                            <td>
                                ' . $data->credits . '
                            </td>
                        </tr>';

                        $total += $data->credits;
                    }
                }
            }

            $table .= '
                        </tbody>
                        <tfoot>
                            <tr class="table-credit__row table-credit__row--footer">
                                <td>' . Lang::t('_TOTAL', 'catalogue') . '</td>
                                <td>' . $total . '</td>
                            </tr>
                        </tfoot>    
                    </table>';
        }

        if ($no_cdata) {
            $table .= '<p>' . Lang::t('_NO_CONTENT', 'catalogue') . '</p>';
        }

        $table .= '</div>';

        echo $table;
    }
}
