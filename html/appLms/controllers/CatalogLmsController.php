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

class CatalogLmsController extends LmsController
{
    public $name = 'catalog';

    private $path_course = '';

    protected $_default_action = 'show';

    /** @var CatalogLms */
    public $model;
    public $json;
    /** @var DoceboACLManager */
    public $acl_man;

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {
        YuiLib::load('base,tabview');
        Lang::init('course');
        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';
        $this->model = new CatalogLms();

        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();

        $this->acl_man = &Docebo::user()->getAclManager();
    }

    protected function getBaseData()
    {
        return [
            'catalogueType' => 'catalog',
            'endpoint' => 'lms/catalog',
            'logged_in' => $this->session->get('logged_in'),
        ];
    }

    // displays header and catalogue tree
    public function show()
    {
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $onCatalogueEmptySetting = FormaLms\lib\Get::sett('on_catalogue_empty') == 'on';

        $show_general_catalogue_tab = ($onCatalogueEmptySetting && count($user_catalogue) === 0);
        $show_empty_catalogue_tab = (!$onCatalogueEmptySetting && count($user_catalogue) === 0);
        $show_user_catalogue_tab = count($user_catalogue) > 0;

        $catalogue = '';
        $total_category = 0;

        if (!$show_empty_catalogue_tab) {
            if ($show_general_catalogue_tab) {
                $starting_catalogue = 0;
            }

            if (count($user_catalogue) > 0) {
                if ($id_catalogue === 0) {
                    reset($user_catalogue);
                    $key = key($user_catalogue);
                    $starting_catalogue = $user_catalogue[$key]['idCatalogue'];
                } else {
                    $starting_catalogue = $id_catalogue;
                }
            }
            $catalogue = $this->model->GetGlobalJsonTree($starting_catalogue);
            $total_category = count($catalogue);
        }

        $data = $this->getBaseData();

        $data = array_merge($data, [
            'id_catalogue' => $id_catalogue,
            'user_catalogue' => $user_catalogue,
            'show_general_catalogue_tab' => $show_general_catalogue_tab,
            'show_empty_catalogue_tab' => $show_empty_catalogue_tab,
            'show_user_catalogue_tab' => $show_user_catalogue_tab,
            'tab_actived' => false,
            'total_category' => $total_category,
            'starting_catalogue' => $starting_catalogue,
            'catalogue' => $catalogue
        ]);

        $this->render('catalog', [
            'data' => $data
        ]);
    }

    // AJAX: display courses from selected catalogue, category, courses
    public function allCourseForma()
    {
        $id_category = FormaLms\lib\Get::req('id_category', DOTY_INT, 0);
        $typeCourse = FormaLms\lib\Get::req('type_course', DOTY_STRING, '');
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);

        $courses = $this->model->getCatalogCourseList($typeCourse, 1, $id_catalogue, $id_category);

        $data = $this->getBaseData();

        $data = array_merge($data, compact('courses', 'id_catalogue'));

        $this->render('courselist', ['data' => $data]);
    }

    public function newCourse()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        $active_tab = 'new';

        $page = FormaLms\lib\Get::req('page', DOTY_INT, 1);
        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $nav_bar = new NavBar('page', FormaLms\lib\Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

        $nav_bar->setLink('index.php?r=catalog/newCourse' . ($id_cat > 1 ? '&amp;id_cat=' . $id_cat : ''));

        $html = $this->model->getCourseList($active_tab, $page);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="middlearea_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab,
            'user_coursepath' => $user_coursepath,
            'std_link' => 'index.php?r=catalog/newCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $this->render('courselist', [
            'html' => $html,
            'nav_bar' => $nav_bar,
        ]);
        $this->render('tab_end', [
            'std_link' => 'index.php?r=catalog/newCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function elearningCourse()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        $active_tab = 'elearning';

        $page = FormaLms\lib\Get::req('page', DOTY_INT, 1);
        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $nav_bar = new NavBar('page', FormaLms\lib\Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

        $nav_bar->setLink('index.php?r=catalog/elearningCourse' . ($id_cat > 1 ? '&amp;id_cat=' . $id_cat : ''));

        $html = $this->model->getCourseList($active_tab, $page);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="middlearea_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab,
            'user_coursepath' => $user_coursepath,
            'std_link' => 'index.php?r=catalog/elearningCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $this->render('courselist', [
            'html' => $html,
            'nav_bar' => $nav_bar,
        ]);
        $this->render('tab_end', [
            'std_link' => 'index.php?r=catalog/elearningCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function classroomCourse()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        $active_tab = 'classroom';

        $page = FormaLms\lib\Get::req('page', DOTY_INT, 1);
        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $nav_bar = new NavBar('page', FormaLms\lib\Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

        $nav_bar->setLink('index.php?r=catalog/classroomCourse' . ($id_cat > 1 ? '&amp;id_cat=' . $id_cat : ''));

        $html = $this->model->getCourseList($active_tab, $page);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="middlearea_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab,
            'user_coursepath' => $user_coursepath,
            'std_link' => 'index.php?r=catalog/classroomCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $this->render('courselist', [
            'html' => $html,
            'nav_bar' => $nav_bar,
        ]);
        $this->render('tab_end', [
            'std_link' => 'index.php?r=catalog/classroomCourse' . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function catalogueCourse()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);
        $active_tab = 'catalogue';

        $page = FormaLms\lib\Get::req('page', DOTY_INT, 1);
        $id_cat = FormaLms\lib\Get::req('id_cat', DOTY_INT, 0);

        $nav_bar = new NavBar('page', FormaLms\lib\Get::sett('visuItem'), $this->model->getTotalCourseNumber($active_tab), 'link');

        $nav_bar->setLink('index.php?r=catalog/catalogueCourse&amp;id_catalogue=' . $id_catalogue . ($id_cat > 1 ? '&amp;id_cat=' . $id_cat : ''));

        $html = $this->model->getCourseList($active_tab, $page);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="middlearea_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab . '_' . $id_cat,
            'user_coursepath' => $user_coursepath,
            'std_link' => 'index.php?r=catalog/catalogueCourse&amp;id_catalogue=' . $id_catalogue . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $this->render('courselist', [
            'html' => $html,
            'nav_bar' => $nav_bar,
        ]);
        $this->render('tab_end', [
            'std_link' => 'index.php?r=catalog/catalogueCourse&amp;id_catalogue=' . $id_catalogue . ($page > 1 ? '&amp;page=' . $page : ''),
            'model' => $this->model,
        ]);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function coursepathCourse()
    {
        require_once _base_ . '/lib/lib.navbar.php';
        $active_tab = 'coursepath';

        $nav_bar = new NavBar('page', FormaLms\lib\Get::sett('visuItem'), count($this->model->getUserCoursepath(Docebo::user()->getIdSt())), 'link');

        $nav_bar->setLink('index.php?r=catalog/coursepathCourse');

        $page = FormaLms\lib\Get::req('page', DOTY_INT, 1);

        $html = $this->model->getCoursepathList(Docebo::user()->getIdSt(), $page);
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="layout_colum_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab,
            'user_coursepath' => $user_coursepath,
        ]);
        $this->render('courselist', [
            'html' => $html,
            'nav_bar' => $nav_bar,
        ]);
        $this->render('tab_end', []);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function calendarCourse()
    {
        $active_tab = 'calendar';
        $user_catalogue = $this->model->getUserCatalogue(Docebo::user()->getIdSt());
        $user_coursepath = $this->model->getUserCoursepath(Docebo::user()->getIdSt());

        echo '<div class="layout_colum_container">';

        $lmstab = $this->widget('lms_tab', [
            'active' => 'catalog',
            'close' => false,
        ]);

        $this->render('tab_start', [
            'user_catalogue' => $user_catalogue,
            'active_tab' => $active_tab,
            'user_coursepath' => $user_coursepath,
        ]);
        $this->render('calendar', []);
        $this->render('tab_end', []);
        $lmstab->endWidget();

        echo '</div>';
    }

    public function subscribeCoursePathInfo()
    {
        $id_path = FormaLms\lib\Get::req('id_path', DOTY_INT, 0);

        $res = $this->model->subscribeCoursePathInfo($id_path);

        echo $this->json->encode($res);
    }

    public function chooseEdition()
    {
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $type_course = FormaLms\lib\Get::req('type_course', DOTY_STRING, 'elearning');
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);
        $id_category = FormaLms\lib\Get::req('id_category', DOTY_INT, 0);
        $res = $this->model->courseSelectionInfo($id_course);
        $this->render('edition-modal', ['id_course' => $id_course, 'available_classrooms' => $res['available_classrooms'], 'teachers' => $res['teachers'],
            'course_name' => $res['course_name'], 'type_course' => $type_course, 'id_catalogue' => $id_catalogue, 'id_category' => $id_category,]);
    }

    //UG  select a user subscription level
    public function get_userlevel_subscription($idu)
    {
        $level = 3;        // default subscription level = Student
        $reg_code = '';
        $reg_code = FormaLms\lib\Get::cfg('registration_code_gu', '');
        if (FormaLms\lib\Get::cfg('register_type_guestuser') && $reg_code != '') {
            $uma = new UsermanagementAdm();
            $array_folder = $uma->getFoldersFromCode($reg_code);
            $userfolders = $uma->getUserFoldersCode($idu);
            if (in_array($reg_code, $userfolders)) {
                // it's a guest user , register to guest level
                $level = 1;            // Guest user level subscription = Guest
            }
        }

        return $level;
    }

    public function subscribeToCourse()
    {
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $id_date = FormaLms\lib\Get::req('id_date', DOTY_INT, 0);
        $id_edition = FormaLms\lib\Get::req('id_edition', DOTY_INT, 0);
        $overbooking = (FormaLms\lib\Get::req('overbooking', DOTY_INT, 0) == 1);

        $id_user = Docebo::user()->getIdSt();

        $docebo_course = new DoceboCourse($id_course);

        require_once _lms_ . '/admin/models/SubscriptionAlms.php';
        $model = new SubscriptionAlms($id_course, $id_edition, $id_date);

        $course_info = $model->getCourseInfoForSubscription();
        $userinfo = $this->acl_man->getUser($id_user, false);

        $level_idst = &$docebo_course->getCourseLevel($id_course);

        if (count($level_idst) == 0 || $level_idst[1] == '') {
            $level_idst = &$docebo_course->createCourseLevel($id_course);
        }

        $waiting = $course_info['subscribe_method'] == 1; // need approval

        $userlevel_subscrip = $this->get_userlevel_subscription($id_user);    //UG

        $this->acl_man->addToGroup($level_idst[$userlevel_subscrip], $id_user);    //UG

        if ($model->subscribeUser($id_user, $userlevel_subscrip, $waiting, false, false, $overbooking)) {        //UG
            $res['success'] = true;
            $res['new_status_code'] = '';

            if ($id_edition != 0 || $id_date != 0) {
                $must_change_status = $this->model->controlSubscriptionRemaining($id_course);
                $res['new_status'] = '';

                if (!$must_change_status) {
                    $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_NO_EDITIONS', 'catalogue') . '</p>';
                }
            } else {
                if ($waiting == 1) {
                    $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_WAITING', 'catalogue') . '</p>';
                    $res['new_status_code'] = 'waiting';
                } else {
                    $res['new_status'] = '<p class="subscribed">' . Lang::t('_USER_STATUS_ENTER', 'catalogue') . '</p>';
                    $res['new_status_code'] = 'subscribed';
                }
            }

            // message to user that is waiting
            require_once _base_ . '/lib/lib.eventmanager.php';

            $acl = &Docebo::user()->getAcl();
            $acl_man = &$this->acl_man;

            $recipients = [];

            // get all superadmins
            // no mail to superadmin
            /*
                $idst_group_god_admin = $acl->getGroupST(ADMIN_GROUP_GODADMIN);
               $recipients = $acl_man->getGroupMembers($idst_group_god_admin);
            */

            // get all admins
            $idst_group_admin = $acl->getGroupST(ADMIN_GROUP_ADMIN);
            $idst_admin = $acl_man->getGroupMembers($idst_group_admin);

            require_once _adm_ . '/lib/lib.adminmanager.php';

            foreach ($idst_admin as $id_user) {
                $adminManager = new AdminManager();
                $acl_manager = &$acl_man;

                // st = organization, get all orgs related to the user
                $idst_associated = $adminManager->getAdminTree($id_user);

                $array_user = &$acl_manager->getAllUsersFromIdst($idst_associated);

                $array_user = array_unique($array_user);

                $control_user = array_search(getLogUserId(), $array_user);
                if ($control_user === 0) {
                    $control_user = true;
                }

                $query = 'SELECT COUNT(*)'
                    . ' FROM ' . FormaLms\lib\Get::cfg('prefix_fw') . '_admin_course'
                    . " WHERE idst_user = '" . $id_user . "'"
                    . " AND type_of_entry = 'course'"
                    . ' AND id_entry in (-1,0,' . $id_course . ')';

                list($control_course) = sql_fetch_row(sql_query($query));

                $query = 'SELECT COUNT(*)'
                    . ' FROM ' . FormaLms\lib\Get::cfg('prefix_fw') . '_admin_course'
                    . " WHERE idst_user = '" . $id_user . "'"
                    . " AND type_of_entry = 'coursepath'"
                    . ' AND id_entry IN'
                    . ' ('
                    . ' SELECT id_path'
                    . ' FROM ' . FormaLms\lib\Get::cfg('prefix_lms') . '_coursepath_courses'
                    . " WHERE id_item = '" . $id_course . "'"
                    . ' )';

                list($control_coursepath) = sql_fetch_row(sql_query($query));

                $query = 'SELECT COUNT(*)'
                    . ' FROM ' . FormaLms\lib\Get::cfg('prefix_fw') . '_admin_course'
                    . " WHERE idst_user = '" . $id_user . "'"
                    . " AND type_of_entry = 'catalogue'"
                    . ' AND id_entry IN'
                    . ' ('
                    . ' SELECT idCatalogue'
                    . ' FROM ' . FormaLms\lib\Get::cfg('prefix_lms') . '_catalogue_entry'
                    . " WHERE idEntry = '" . $id_course . "'"
                    . ' )';

                list($control_catalogue) = sql_fetch_row(sql_query($query));

                if ($control_user && ($control_course || $control_coursepath || $control_catalogue)) {
                    $recipients[] = $id_user;
                }
            }

            $recipients = array_unique($recipients);

            $array_subst = [
                '[url]' => FormaLms\lib\Get::site_url(),
                '[course]' => $course_info['name'],
                '[firstname]' => $userinfo[ACL_INFO_FIRSTNAME],
                '[lastname]' => $userinfo[ACL_INFO_LASTNAME],
            ];

            $msg_composer = new EventMessageComposer('subscribe', 'lms');
            if ($overbooking) {
                $subject_key = '_NEW_USER_OVERBOOKING_SUBSCRIBED_SUBJECT';
                $body_key = '_NEW_USER_OVERBOOKING_SUBSCRIBED_TEXT';

                $msg_composer->setSubjectLangText('email', $subject_key, false);
                $msg_composer->setBodyLangText('email', $body_key, $array_subst);

                $msg_composer->setSubjectLangText('sms', $subject_key . '_SMS', false);
                $msg_composer->setBodyLangText('sms', $body_key . '_SMS', $array_subst);
                createNewAlert('UserCourseInsertOverbooking', 'subscribe', 'insert', '1', 'User overbooked subscribed with moderation', $recipients, $msg_composer);
            } else {
                $description = 'User subscribed';
                if ($waiting) {
                    $description .= ' with moderation';
                    $subject_key = '_NEW_USER_SUBS_WAITING_SUBJECT';
                    $body_key = '_NEW_USER_SUBS_WAITING_TEXT';
                    $myevent = 'UserCourseInsertModerate';
                } else {
                    $subject_key = '_NEW_USER_SUBSCRIBED_SUBJECT';
                    $body_key = '_NEW_USER_SUBSCRIBED_TEXT_MODERATORS';
                    $myevent = 'UserCourseInserted';
                }

                $msg_composer->setSubjectLangText('email', $subject_key, false);
                $msg_composer->setBodyLangText('email', $body_key, $array_subst);

                $msg_composer->setSubjectLangText('sms', '_TO_NEW_USER_TEXT_SMS', false);
                $msg_composer->setBodyLangText('sms', '_TO_NEW_USER_TEXT_SMS', $array_subst);

                createNewAlert($myevent, 'subscribe', 'insert', '1', $description, $recipients, $msg_composer);
            }

            $res['message'] = UIFeedback::info(Lang::t('_SUBSCRIPTION_CORRECT', 'catalogue'), true);
        } else {
            $this->acl_man->removeFromGroup($level_idst[3], $id_user);
            $res['success'] = false;

            $res['message'] = UIFeedback::error(Lang::t('_SUBSCRIPTION_ERROR', 'catalogue'), true);
        }

        $this->allCourseForma();
    }

    public function subscribeToCoursePath()
    {
        $id_path = FormaLms\lib\Get::req('id_path', DOTY_INT, 0);

        $id_user = Docebo::user()->getIdSt();

        $query_pathlist = '
        SELECT path_name, subscribe_method
        FROM ' . $GLOBALS['prefix_lms'] . "_coursepath
        WHERE id_path = '" . $id_path . "'
        ORDER BY path_name ";
        list($path_name, $subscribe_method) = sql_fetch_row(sql_query($query_pathlist));

        if ($subscribe_method == 1) {
            $waiting = 1;
        } else {
            $waiting = 0;
        }
        $text_query = '
            INSERT INTO ' . $GLOBALS['prefix_lms'] . "_coursepath_user
            ( id_path, idUser, waiting, subscribed_by ) VALUES
            ( '" . $id_path . "', '" . $id_user . "', '" . $waiting . "', '" . getLogUserId() . "' )";
        $re_s = sql_query($text_query);

        /////////////////////////

        if ($waiting == 0) {
            require_once _lms_ . '/lib/lib.subscribe.php';
            require_once _lms_ . '/lib/lib.coursepath.php';

            $cpath_man = new CoursePath_Manager();
            $subs_man = new CourseSubscribe_Management();

            $id_path = FormaLms\lib\Get::req('id_path', DOTY_INT, 0);
            $user_selected = Util::unserialize(urldecode(FormaLms\lib\Get::req('users', DOTY_MIXED, [])));

            $courses = $cpath_man->getAllCourses([$id_path]);

            $users_subsc = [$id_user];

            $re = $subs_man->multipleSubscribe($users_subsc, $courses, 3);
        }

        $res['success'] = true;
        if ($waiting == 1) {
            $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_WAITING', 'catalogue') . '</p>';
        } else {
            $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_USER_STATUS_SUBS', 'catalogue') . '</p>';
        }

        $res['message'] = $res['message'] = UIFeedback::info(Lang::t('_SUBSCRIPTION_CORRECT', 'catalogue'), true);

        echo $this->json->encode($res);
    }

    public function addToCart()
    {
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $id_date = FormaLms\lib\Get::req('id_date', DOTY_INT, 0);
        $id_edition = FormaLms\lib\Get::req('id_edition', DOTY_INT, 0);

        $currentCart = $this->session->get('lms_cart');
        if ($id_edition != 0) {
            $currentCart[$id_course]['edition'][$id_edition] = $id_edition;
        } elseif ($id_date != 0) {
            $currentCart[$id_course]['classroom'][$id_date] = $id_date;
        } else {
            $currentCart[$id_course] = $id_course;
        }
        $this->session->set('lms_cart', $currentCart);
        $this->session->save();

        $res['success'] = true;
        $res['message'] = UIFeedback::info(Lang::t('_COURSE_ADDED_IN_CART', 'catalogue'), true);

        if ($id_edition !== 0 || $id_date !== 0) {
            $must_change_status = $this->model->controlSubscriptionRemaining($id_course);
            $res['new_status'] = '';

            if (!$must_change_status) {
                $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_ALL_EDITION_BUYED', 'catalogue') . '</p>';
            }
        } else {
            $res['new_status'] = '<p class="cannot_subscribe">' . Lang::t('_COURSE_IN_CART', 'catalogue') . '</p>';
        }

        require_once _lms_ . '/lib/lib.cart.php';

        $res['cart_element'] = '' . Learning_Cart::cartItemCount() . '';
        $res['num_element'] = Learning_Cart::cartItemCount();
        $res['cart_message'] = Lang::t('_COURSE_ADDED_IN_CART', 'catalogue');
        $this->allCourseForma();
    }

    public function downloadDemoMaterialTask()
    {
        require_once _base_ . '/lib/lib.download.php';

        $id = FormaLms\lib\Get::gReq('course_id', DOTY_INT);
        $db = DbConn::getInstance();

        $qtxt = 'SELECT course_demo FROM %lms_course WHERE idCourse=' . $id;

        $q = $db->query($qtxt);
        list($fname) = $db->fetch_row($q);

        if (!empty($fname)) {
            sendFile('/appLms/course/', $fname);
        } else {
            echo 'nothing found';
        }
        exit();
    }

    public function self_unsubscribe()
    {
        $id_user = Docebo::user()->idst;
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        $cmodel = new CourseAlms();
        $cinfo = $cmodel->getCourseModDetails($id_course);

        $smodel = new SubscriptionAlms();
        $param = '';
        if ($cinfo['course_type'] == 'classroom') {
            $csmodel = new ClassroomLms();
            $enroll_array = $csmodel->getUserEditionsInfo($id_user, $id_course);
            foreach ($enroll_array[$id_course] as $k => $obj) {
                //moderated self unsubscribe
                if ($cinfo['auto_unsubscribe'] == 1) {
                    $res = $smodel->setUnsubscribeRequest($id_user, $id_course, $id_edition, $obj->id_date);
                }
                //directly unsubscribe user
                if ($cinfo['auto_unsubscribe'] == 2) {
                    $res = $smodel->unsubscribeUser($id_user, $id_course, $id_edition, $obj->id_date);
                }
            }
        }

        if ($cinfo['course_type'] == 'elearning') {
            if ($cinfo['auto_unsubscribe'] == 1) {
                //moderated self unsubscribe
                $res = $smodel->setUnsubscribeRequest($id_user, $id_course, $id_edition, $id_date);
            }

            if ($cinfo['auto_unsubscribe'] == 2) {
                //directly unsubscribe user
                $res = $smodel->unsubscribeUser($id_user, $id_course, $id_edition, $id_date);
            }
        }

        if ($res) {
            $this->allCourseForma();
        } else {
            return 'error';
        }
    }
}
