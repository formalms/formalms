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

class AdminmanagerAdmController extends AdmController
{
    protected $model;
    protected $json;
    protected $acl_man;
    protected $permissions;

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';

        $this->model = new AdminmanagerAdm();
        $this->json = new Services_JSON();
        $this->acl_man = &Docebo::user()->getAclManager();
        $this->permissions = [
            'view' => checkPerm('view', true, 'adminmanager'),
            'assign_profile' => checkPerm('mod', true, 'adminmanager'),
            'assign_users' => checkPerm('mod', true, 'adminmanager'),
            'assign_courses' => checkPerm('mod', true, 'adminmanager'),
        ];
    }

    protected function _getSessionValue($index, $default = false)
    {
        if (!$this->session->has('adminmanager_' . $index)) {
            $this->session->set('adminmanager_' . $index, $default);
            $this->session->save();
        }

        return $this->session->get('adminmanager_' . $index);
    }

    protected function _setSessionValue($index, $value)
    {
        $this->session->set('adminmanager_' . $index, $value);
        $this->session->save();
    }

    public function show()
    {
        Util::get_js(FormaLms\lib\Get::rel_path('base') . '/lib/js_utils.js', true, true);

        $rmodel = new AdminrulesAdm();

        switch (FormaLms\lib\Get::req('res', DOTY_ALPHANUM, '')) {
            case 'ok_ins': UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard')); break;
            case 'err_ins': UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard')); break;
            default: if ($rmodel->totalGroup() <= 0) {
                UIFeedback::notice(Lang::t('_NO_PROFILE_SET', 'adminrules'));
            } break;
        }

        $rules_list_js = '';
        if ($this->permissions['assign_profile']) {
            $rules = $rmodel->getGroupForDropdown();
            $rules_list_js .= '[';
            $first = true;
            foreach ($rules as $idst => $label) {
                $rules_list_js .= ($first ? '' : ',') . '{"label":"' . $label . '","value":' . $idst . '}';
                if ($first) {
                    $first = false;
                }
            }
            $rules_list_js .= ']';
        }

        $this->render('show', [
            'filter_text' => $this->_getSessionValue('filter', ''),
            'permissions' => $this->permissions,
            'rules_list_js' => $rules_list_js,
        ]);
    }

    public function getAdmin()
    {
        //read input data
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'userid');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');

        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, $this->_getSessionValue('filter', ''));
        $this->_setSessionValue('filter', $filter_text);

        //retrieve records from model
        $total_group = $this->model->totalAdmin($filter_text);
        $array_group = $this->model->loadAdmin($start_index, $results, $sort, $dir, $filter_text);

        //extract admin info for data formatting
        $idst_list = $this->model->acl_man->getGroupMembers($this->model->idst_admin_group);
        $users_info = $this->model->preference->getMultipleAdminTree($idst_list);
        $courses_info = $this->model->preference->getMultipleAdminCourse($idst_list);
        $classlocations_info = $this->model->preference->getMultipleAdminClasslocation($idst_list);

        //format data retrieved from model
        $output_list = [];
        foreach ($array_group as $value) {
            $id_user = $value['id_user'];

            $has_users = (isset($users_info[$id_user]) && count($users_info[$id_user]) > 0);
            $has_courses = (isset($courses_info[$id_user]) && (
                count($courses_info[$id_user]['course']) > 0 ||
                count($courses_info[$id_user]['coursepath']) > 0 ||
                count($courses_info[$id_user]['catalogue']) > 0 ||
                count($courses_info[$id_user]['category']) > 0));
            $has_classlocations = (isset($classlocations_info[$id_user]['classlocation']) && count($classlocations_info[$id_user]['classlocation']) > 0);

            $userid = $this->acl_man->relativeId($value['userid']);

            $output_list[] = [
                'id_user' => $id_user,
                'userid' => Layout::highlight($userid, $filter_text),
                'firstname' => Layout::highlight($value['firstname'], $filter_text),
                'lastname' => Layout::highlight($value['lastname'], $filter_text),
                'idst_profile' => $value['idst_profile'],
                'user_profile' => $value['user_profile'] ? str_replace('/framework/adminrules/', '', $value['user_profile']) : false,
                'has_users' => $has_users ? 1 : 0,
                'has_courses' => $has_courses ? 1 : 0,
                'has_classlocations' => $has_classlocations ? 1 : 0,
            ];
        }

        //produce output for datatable
        $output = [
            'totalRecords' => $total_group,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($output_list),
            'records' => $output_list,
        ];

        echo $this->json->encode($output);
    }

    public function updateFilter()
    {
        $filter = FormaLms\lib\Get::req('filter', DOTY_MIXED, '');

        $this->session->set('adminmanager_filter', $filter);
        $this->session->save();

        $res = ['success' => true, 'filter' => $filter];

        echo $this->json->encode($res);
    }

    public function update_profileTask()
    {
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);
        $idst_profile = FormaLms\lib\Get::req('idst_profile', DOTY_INT, 0);

        $output = [];
        if ($idst_profile <= 0) {
            $result = $this->model->removeAdminAssociation($id_user);
            $output['success'] = $result;
            if ($result) {
                $output['new_string'] = false;
            }
        } else {
            $result = $this->model->saveSingleAdminAssociation($idst_profile, $id_user);
            $output['success'] = $result;
            if ($result) {
                $rmodel = new AdminrulesAdm();
                $output['new_string'] = $rmodel->getGroupName($idst_profile);
            }
        }

        echo $this->json->encode($output);
    }

    public function users()
    {
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);

        require_once _base_ . '/lib/lib.form.php';
        require_once _base_ . '/lib/lib.userselector.php';

        $user_selector = new UserSelector();

        if (isset($_POST['cancelselector'])) {
            Util::jump_to('index.php?r=adm/adminmanager/show');
        }

        if (isset($_POST['okselector'])) {
            $user_selected = $user_selector->getSelection($_POST);

            if ($this->model->saveUsersAssociation($id_user, $user_selected)) {
                Util::jump_to('index.php?r=adm/adminmanager/show&res=ok_ins');
            }
            Util::jump_to('index.php?r=adm/adminmanager/show&res=err_ins');
        } else {
            $user_selector->show_user_selector = true;
            $user_selector->show_group_selector = true;
            $user_selector->show_orgchart_selector = true;
            $user_selector->show_orgchart_simple_selector = false;

            if (isset($_GET['load'])) {
                $user_selector->requested_tab = PEOPLEVIEW_TAB;
                $old_association = $this->model->loadUserSelectorSelection($id_user);
                $user_selector->resetSelection($old_association);
            }

            $user_selector->setUserFilter('exclude', [$this->acl_man->getAnonymousId()]);

            $this->render('users', ['id_user' => $id_user,
                                            'user_selector' => $user_selector,
                                            'model' => $this->model,
                                            'user_alredy_subscribed' => $old_association, ]);
        }
    }

    public function courses()
    {
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);

        require_once _base_ . '/lib/lib.form.php';
        require_once _lms_ . '/lib/lib.course.php';
        require_once _lms_ . '/lib/lib.course_managment.php';

        $course_selector = new Course_Manager();
        $course_selector->setLink('index.php?r=adm/adminmanager/courses&id_user=' . $id_user);

        $course_selector->show_catalogue_selector = true;
        $course_selector->show_coursepath_selector = true;

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=adm/adminmanager/show');
        }

        if (isset($_POST['save'])) {
            $course_selected = $course_selector->getCourseSelection($_POST);
            $coursepath_selected = $course_selector->getCoursePathSelection($_POST);
            $catalogue_selected = $course_selector->getCatalogueSelection($_POST);

            if (isset($_POST['all_courses']) && $_POST['all_courses'] == 1) {
                $course_selected = [0];
            }

            if (isset($_POST['all_courses']) && $_POST['all_courses'] == -1) {
                $course_selected = [-1];
            }

            if ($this->model->saveCoursesAssociation($id_user, $course_selected, $coursepath_selected, $catalogue_selected)) {
                Util::jump_to('index.php?r=adm/adminmanager/show&res=ok_ins');
            }
            Util::jump_to('index.php?r=adm/adminmanager/show&res=err_ins');
        } else {
            $all_courses = 0;

            if (isset($_GET['load'])) {
                $old_association = $this->model->loadCourseSelectorSelection($id_user);

                if (isset($old_association['course'][0])) {
                    $all_courses = 1;
                } elseif (isset($old_association['course'][-1])) {
                    $all_courses = -1;
                } else {
                    $course_selector->resetCourseSelection($old_association['course']);
                    $course_selector->resetCoursePathSelection($old_association['coursepath']);
                    $course_selector->resetCatalogueSelection($old_association['catalogue']);
                }
            }

            $this->render('courses', [
                'id_user' => $id_user,
                'all_courses' => $all_courses,
                'course_selector' => $course_selector,
                'model' => $this->model,
            ]);
        }
    }

    public function classlocationsTask()
    {
        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);
        $selection = $this->model->loadClasslocationsSelection($id_user);
        $this->render('classlocations', [
            'id_user' => $id_user,
            'selection' => $selection,
            'num_selected' => count($selection),
            'filter_text' => '',
            'model' => $this->model,
        ]);
    }

    public function classlocations_setTask()
    {
        if (FormaLms\lib\Get::req('undo', DOTY_MIXED, false) !== false) {
            Util::jump_to('index.php?r=adm/adminmanager/show');
        }

        $id_user = FormaLms\lib\Get::req('id_user', DOTY_INT, 0);
        $selection_str = FormaLms\lib\Get::req('selection', DOTY_STRING, '');

        if (!$selection_str) {
            $selection = [];
        } else {
            $selection = explode(',', $selection_str);
        }

        $res = $this->model->saveClasslocationsAssociation($id_user, $selection);
        Util::jump_to('index.php?r=adm/adminmanager/show&res=' . ($res ? 'ok' : 'err'));
    }

    public function selectallclasslocationsTask()
    {
        $output = [];

        //instantiate locations model
        $lmodel = new LocationAlms();

        //get list of all locations in DB
        $output = $lmodel->getLocationAll();

        echo $this->json->encode($output);
    }

    public function getclasslocationstabledataTask()
    {
        $op = FormaLms\lib\Get::req('op', DOTY_MIXED, false);
        switch ($op) {
            case 'selectall':
                $this->selectallclasslocationsTask();

                return;
             break;
        }

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, FormaLms\lib\Get::sett('visuItem', 25));
        $rowsPerPage = FormaLms\lib\Get::req('rowsPerPage', DOTY_INT, $results);
        $sort = FormaLms\lib\Get::req('sort', DOTY_STRING, 'location');
        $dir = FormaLms\lib\Get::req('dir', DOTY_STRING, 'asc');
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        //instantiate model
        $lmodel = new LocationAlms();

        //get total from database and validate the results count
        $total = $lmodel->getLocationTotal($filter_text);
        if ($startIndex >= $total) {
            if ($total < $results) {
                $startIndex = 0;
            } else {
                $startIndex = $total - $results;
            }
        }

        //read records from database
        $list = $lmodel->getLocationList($startIndex, $results, $sort, $dir, $filter_text);

        //prepare the data for sending
        $acl_man = Docebo::user()->getAclManager();
        $output_results = [];
        if (is_array($list) && count($list) > 0) {
            foreach ($list as $idst => $record) {
                //prepare output record
                $output_results[] = [
                    'id' => $record->id_location,
                    'location' => $record->location,
                ];
            }
        }

        $output = [
            'totalRecords' => $total,
            'startIndex' => $startIndex,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $rowsPerPage,
            'results' => count($list),
            'records' => $output_results,
        ];

        echo $this->json->encode($output);
    }
}
