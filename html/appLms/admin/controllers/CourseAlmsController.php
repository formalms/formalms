<?php

use FormaLms\lib\Forma;

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

defined('IN_FORMA') or exit('Direct access is forbidden');

class CourseAlmsController extends AlmsController
{
    protected $json;
    protected $acl_man;
    protected $model;

    protected $data;

    protected $permissions;

    protected $base_link_course;
    protected $base_link_classroom;
    protected $base_link_edition;
    protected $base_link_subscription;
    protected $base_link_competence;

    protected $lo_types_cache;

    public function init()
    {
        parent::init();
        require_once _base_ . '/lib/lib.json.php';
        $this->json = new Services_JSON();
        $this->acl_man = \FormaLms\lib\Forma::getAclManager();
        $this->model = new CourseAlms();

        $this->base_link_course = 'alms/course';
        $this->base_link_classroom = 'alms/classroom';
        $this->base_link_edition = 'alms/edition';
        $this->base_link_subscription = 'alms/subscription';
        $this->base_link_competence = 'adm/competences';

        $this->lo_types_cache = false;

        $this->permissions = [
            'view' => checkPerm('view', true, 'course', 'lms'),
            'add' => checkPerm('add', true, 'course', 'lms'),
            'mod' => checkPerm('mod', true, 'course', 'lms'),
            'del' => checkPerm('del', true, 'course', 'lms'),
            'moderate' => checkPerm('moderate', true, 'course', 'lms'),
            'subscribe' => checkPerm('subscribe', true, 'course', 'lms'),
            'add_category' => checkPerm('add', true, 'coursecategory', 'lms'),
            'mod_category' => checkPerm('mod', true, 'coursecategory', 'lms'),
            'del_category' => checkPerm('del', true, 'coursecategory', 'lms'),
            'view_cert' => checkPerm('view', true, 'certificate', 'lms'),
            'mod_cert' => checkPerm('mod', true, 'certificate', 'lms'),
        ];
    }

    protected function _getMessage($code)
    {
        $message = '';
        switch ($code) {
            case 'no permission':
                $message = '';
                break;
            case '':
                $message = '';
                break;
        }

        return $message;
    }

    // funzione (ajax)
    public function getlolist($p = 0, $sk = '')
    {
        if (isset($_GET['idCourse'])) {
            $query_list = "SELECT * FROM %lms_organization WHERE idCourse = '" . (int) $_GET['idCourse'] . "' AND idParent = " . $p . ' ORDER BY path ASC';
            $result_list = sql_query($query_list);
            if (sql_num_rows($result_list) > 0) {
                if ($p == 0) {
                    echo "<div id='treeDiv' class='ygtv-checkbox'>";
                }
                echo '<ul>';
                while ($lo = sql_fetch_array($result_list)) {
                    echo "<li class=\"expanded\"> <input onclick='cascade(\"" . $lo['idOrg'] . "\")' class='" . $sk . "' type='checkbox' id='" . $lo['idOrg'] . "' name='lo_list[]' value='" . $lo['idOrg'] . "' checked='checked' /> <label for='" . $lo['idOrg'] . "'>" . $lo['title'] . '</label>';
                    $this->getlolist($lo['idOrg'], $sk . ' ' . $lo['idOrg']);
                    echo '</li>';
                }
                echo '</ul>';
                if ($p == 0) {
                    echo '</div>';
                }
            }
        } else {
            echo 'Error';
        }
    }

    public function show()
    {
        if (isset($_GET['res']) && $_GET['res'] !== '') {
            UIFeedback::info(Lang::t('_OPERATION_SUCCESSFUL', 'standard'));
        }
        if (isset($_GET['err']) && $_GET['err'] !== '') {
            UIFeedback::error(Lang::t('_OPERATION_FAILURE', 'standard'));
        }
        $params = [];

        if (!$this->session->has('course_filter')) {
            $courseFilter = [];
            $courseFilter['text'] = '';
            $courseFilter['classroom'] = false;
            $courseFilter['descendants'] = false;
            $courseFilter['waiting'] = false;
            $this->session->set('course_filter', $courseFilter);
            $this->session->save();
        }

        if (isset($_POST['c_filter_set'])) {
            $classroom = (bool) FormaLms\lib\Get::req('classroom', DOTY_INT, false);
            $descendants = (bool) FormaLms\lib\Get::req('descendants', DOTY_INT, false);
            $waiting = (bool) FormaLms\lib\Get::req('waiting', DOTY_INT, false);
            $filter_text = FormaLms\lib\Get::req('text', DOTY_STRING, '');
        } else {
            $classroom = $this->session->get('course_filter')['classroom'];
            $descendants = $this->session->get('course_filter')['descendants'];
            $waiting = $this->session->get('course_filter')['waiting'];
            $filter_text = $this->session->get('course_filter')['text'];
        }

        $filter_open = false;

        if ($descendants || $waiting) {
            $filter_open = true;
        }

        $filter = [
            'classroom' => $classroom,
            'descendants' => $descendants,
            'waiting' => $waiting,
            'text' => $filter_text,
            'open' => $filter_open,
            'id_category' => $this->_getSessionTreeData('id_category', 0), ];

        $courseFilter = $this->session->get('course_filter');
        $courseFilter['text'] = $filter_text;
        $courseFilter['classroom'] = $classroom;
        $courseFilter['descendants'] = $descendants;
        $courseFilter['waiting'] = $waiting;
        $this->session->set('course_filter', $courseFilter);
        $this->session->save();

        $params['initial_selected_node'] = $this->_getSessionTreeData('id_category', 0);
        $params['filter'] = $filter;
        $params['root_name'] = Lang::t('_CATEGORY', 'admin_course_managment');
        $params['permissions'] = $this->permissions;

        $params['base_link_course'] = $this->base_link_course;
        $params['base_link_classroom'] = $this->base_link_classroom;
        $params['base_link_edition'] = $this->base_link_edition;
        $params['base_link_subscription'] = $this->base_link_subscription;
        $params['idCourse'] = FormaLms\lib\Get::req('idCourse', DOTY_MIXED, 0);

        $smodel = new SubscriptionAlms();
        $params['unsubscribe_requests'] = $smodel->countPendingUnsubscribeRequests();

        $this->render('show', $params);
    }

    protected function _getSessionTreeData($index, $default = false)
    {
        if (!$index || !is_string($index)) {
            return false;
        }
        $courseCategory = $this->session->get('course_category');
        if (!isset($courseCategory['filter_status'][$index])) {
            $courseCategory = [];
            $courseCategory['filter_status'][$index] = $default;
            $this->session->set('course_category', $courseCategory);
            $this->session->save();
        }

        return $courseCategory['filter_status'][$index];
    }

    protected function _setSessionTreeData($index, $value)
    {
        $courseCategory = $this->session->get('course_category') ?: [];
        $courseCategory['filter_status'][$index] = $value;
        $this->session->set('course_category', $courseCategory);
        $this->session->save();
    }

    public function filterevent()
    {
        $courseFilter = $this->session->get('course_filter');

        $courseFilter['classroom'] = FormaLms\lib\Get::req('classroom', DOTY_MIXED, false);
        $courseFilter['descendants'] = FormaLms\lib\Get::req('descendants', DOTY_MIXED, false);
        $courseFilter['waiting'] = FormaLms\lib\Get::req('waiting', DOTY_MIXED, false);
        $courseFilter['text'] = FormaLms\lib\Get::req('text', DOTY_STRING, '');

        if ($courseFilter['classroom'] === 'false') {
            $courseFilter['classroom'] = false;
        } else {
            $courseFilter['classroom'] = true;
        }

        if ($courseFilter['descendants'] === 'false') {
            $courseFilter['descendants'] = false;
        } else {
            $courseFilter['descendants'] = true;
        }

        if ($courseFilter['waiting'] === 'false') {
            $courseFilter['waiting'] = false;
        } else {
            $courseFilter['waiting'] = true;
        }

        $this->session->set('course_filter', $courseFilter);
        $this->session->save();

        echo $this->json->encode(['success' => true]);
    }

    public function resetevent()
    {
        $this->session->remove('course_filter');
        $this->session->save();
    }

    protected function _getNodeActions($id_category, $is_leaf, $associated_courses = 0)
    {
        $node_options = [];

        //modify category action
        if ($this->permissions['mod_category']) {
            $node_options[] = [
                'id' => 'mod_' . $id_category,
                'command' => 'modify',
                'icon' => 'standard/edit.png',
                'alt' => Lang::t('_MOD'),
            ];
        }

        //delete category action
        if ($this->permissions['del_category']) {
            if ($is_leaf && $associated_courses == 0) {
                $node_options[] = [
                    'id' => 'del_' . $id_category,
                    'command' => 'delete',
                    'icon' => 'standard/delete.png',
                    'alt' => Lang::t('_DEL'), ];
            } else {
                $node_options[] = [
                    'id' => 'del_' . $id_category,
                    'command' => false,
                    'icon' => 'blank.png', ];
            }
        }

        return $node_options;
    }

    public function gettreedata()
    {
        require_once _lms_ . '/lib/category/class.categorytree.php';
        $treecat = new Categorytree();

        $command = FormaLms\lib\Get::req('command', DOTY_ALPHANUM, '');
        switch ($command) {
            case 'expand':
                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, 0);
                $initial = FormaLms\lib\Get::req('initial', DOTY_INT, 0);

                $db = \FormaLms\db\DbConn::getInstance();
                $result = [];
                if ($initial == 1) {
                    $treestatus = $this->_getSessionTreeData('id_category', 0);
                    $folders = $treecat->getOpenedFolders($treestatus);
                    $result = [];

                    $ref = &$result;
                    foreach ($folders as $folder) {
                        if ($folder > 0) {
                            $count = count($ref);
                            for ($i = 0; $i < $count; ++$i) {
                                if ($ref[$i]['node']['id'] == $folder) {
                                    $ref[$i]['children'] = [];
                                    $ref = &$ref[$i]['children'];
                                    break;
                                }
                            }
                        }

                        $childrens = $treecat->getJoinedChildrensById($folder);
                        while (list($id_category, $idParent, $path, $lev, $left, $right, $associated_courses) = $db->fetch_row($childrens)) {
                            $is_leaf = ($right - $left) == 1;
                            $node_options = $this->_getNodeActions($id_category, $is_leaf, $associated_courses);
                            $labelArray = explode('/', $path);
                            $ref[] = [
                                'node' => [
                                    'id' => $id_category,
                                    'label' => end($labelArray),
                                    'is_leaf' => $is_leaf,
                                    'count_content' => (int) (($right - $left - 1) / 2),
                                    'options' => $node_options, ], ];
                        }
                    }
                } else { //not initial selection, just an opened folder
                    $re = $treecat->getJoinedChildrensById($node_id);
                    while (list($id_category, $idParent, $path, $lev, $left, $right, $associated_courses) = $db->fetch_row($re)) {
                        $is_leaf = ($right - $left) == 1;

                        $node_options = $this->_getNodeActions($id_category, $is_leaf, $associated_courses);
                        $pathArray = explode('/', $path);
                        $result[] = [
                            'id' => $id_category,
                            'label' => end($pathArray),
                            'is_leaf' => $is_leaf,
                            'count_content' => (int) (($right - $left - 1) / 2),
                            'options' => $node_options, ]; //change this
                    }
                }

                $output = ['success' => true, 'nodes' => $result, 'initial' => ($initial == 1)];
                echo $this->json->encode($output);
                break;

            case 'set_selected_node':
                $id_node = FormaLms\lib\Get::req('node_id', DOTY_INT, -1);
                if ($id_node >= 0) {
                    $this->_setSessionTreeData('id_category', $id_node);
                }
                break;

            case 'modify':
                if (!$this->permissions['mod_category']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, 0);
                $new_name = FormaLms\lib\Get::req('name', DOTY_STRING, false);

                $result = ['success' => false];
                if ($new_name !== false) {
                    $result['success'] = $treecat->renameFolderById($node_id, $new_name);
                }
                if ($result['success']) {
                    $result['new_name'] = stripslashes($new_name);
                }

                echo $this->json->encode($result);
                break;

            case 'create':
                if (!$this->permissions['add_category']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, false);
                $node_name = FormaLms\lib\Get::req('name', DOTY_STRING, false); //no multilang required for categories

                $result = [];
                if ($node_id === false) {
                    $result['success'] = false;
                } else {
                    $success = false;
                    $new_node_id = $treecat->addFolderById($node_id, $node_name);
                    if ($new_node_id != false && $new_node_id > 0) {
                        $success = true;
                    }

                    $result['success'] = $success;
                    if ($success) {
                        $result['node'] = [
                            'id' => $new_node_id,
                            'label' => stripslashes($node_name),
                            'is_leaf' => true,
                            'count_content' => 0,
                            'options' => $this->_getNodeActions($new_node_id, true), ];
                    }
                }
                echo $this->json->encode($result);
                break;

            case 'delete':
                if (!$this->permissions['del_category']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, 0);
                $result = ['success' => $treecat->deleteTreeById($node_id)];
                echo $this->json->encode($result);
                break;

            case 'move':
                if (!$this->permissions['mod_category']) {
                    $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
                    echo $this->json->encode($output);

                    return;
                }

                $node_id = FormaLms\lib\Get::req('src', DOTY_INT, 0);
                $node_dest = FormaLms\lib\Get::req('dest', DOTY_INT, 0);
                $model = new CoursecategoryAlms();
                $result = ['success' => $model->moveFolder($node_id, $node_dest)];

                echo $this->json->encode($result);
                break;

            case 'options':
                $node_id = FormaLms\lib\Get::req('node_id', DOTY_INT, 0);

                //get properties from DB
                $count = $treecat->getChildrenCount($node_id);
                $is_leaf = true;
                if ($count > 0) {
                    $is_leaf = false;
                }
                $node_options = $this->_getNodeActions($node_id, $is_leaf);

                $result = ['success' => true, 'options' => $node_options, '_debug' => $count];
                echo $this->json->encode($result);
                break;
            //invalid command
            default:
        }
    }

    public function getcourselist()
    {
        //Datatable info
        $start_index = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_MIXED, FormaLms\lib\Get::sett('visuItem', 25));
        $sort = FormaLms\lib\Get::req('sort', DOTY_MIXED, 'userid');
        $dir = FormaLms\lib\Get::req('dir', DOTY_MIXED, 'asc');
        $idCourse = FormaLms\lib\Get::req('idCourse', DOTY_MIXED, null);

        $id_category = FormaLms\lib\Get::req('node_id', DOTY_INT, (int) $this->_getSessionTreeData('id_category', 0));
        $filter_text = $this->session->get('course_filter')['text'];
        $classroom = $this->session->get('course_filter')['classroom'];
        $descendants = $this->session->get('course_filter')['descendants'];
        $waiting = $this->session->get('course_filter')['waiting'];

        $filter_open = false;

        if ($descendants || $waiting) {
            $filter_open = true;
        }

        $filter = [
            'id_category' => !(is_null($idCourse)) ? null : $id_category,
            'classroom' => $classroom,
            'descendants' => $descendants,
            'waiting' => $waiting,
            'text' => $filter_text,
            'open' => $filter_open,
            'idCourse' => $idCourse,
        ];

        $total_course = $this->model->getCourseNumber($filter);
        if ($start_index >= $total_course) {
            if ($total_course < $results) {
                $start_index = 0;
            } else {
                $start_index = $total_course - $results;
            }
        }

        $course_res = $this->model->loadCourse($start_index, $results, $sort, $dir, $filter);
        $course_with_cert = $this->model->getCourseWithCertificate();
        $course_with_competence = $this->model->getCourseWithCompetence();

        $list = [];

        while ($row = sql_fetch_assoc($course_res)) {
            $course_type = 'elearning';
            switch ($row['course_type']) {
                case 'classroom':
                    $course_type = 'classroom';
                    break;
                case 'elearning':
                    if ($row['course_edition'] > 0) {
                        $course_type = 'edition';
                    }
            }

            $num_overbooking = (int) $this->model->getUserInOverbooking($row['idCourse']);

            $num_subscribed = (int) $row['subscriptions'] - ((int) $row['pending'] + $num_overbooking);

            $list[$row['idCourse']] = [
                'id' => $row['idCourse'],
                'code' => $row['code'],
                'name' => $row['name'],
                'type' => Lang::t('_' . strtoupper($row['course_type'])),
                'type_id' => $course_type,
                'wait' => (/*$row['course_type'] !== 'classroom' && */
                ($row['course_edition'] != 1 && $row['pending'] != 0) || $num_overbooking > 0
                    ? '<a href="index.php?r=' . $this->base_link_subscription . '/waitinguser&id_course=' . $row['idCourse'] . '" title="' . Lang::t('_WAITING', 'course') . '">' . ($row['pending'] + $num_overbooking) . '</a>'
                    : ''),
                'user' => ($row['course_type'] !== 'classroom' && $row['course_edition'] != 1
                    ? '<a class="nounder" href="index.php?r=' . $this->base_link_subscription . '/show&amp;id_course=' . $row['idCourse'] . '" title="' . Lang::t('_SUBSCRIPTION', 'course') . '">' . $num_subscribed . ' ' . FormaLms\lib\Get::img('standard/moduser.png', Lang::t('_SUBSCRIPTION', 'course')) . '</a>'
                    : ''),
                'edition' => ($row['course_type'] === 'classroom'
                    ? '<a href="index.php?r=' . $this->base_link_classroom . '/classroom&amp;id_course=' . $row['idCourse'] . '" title="' . Lang::t('_CLASSROOM_EDITION', 'course') . '">' . $this->model->classroom_man->getDateNumber($row['idCourse'], true) . '</a>' : ($row['course_edition'] == 1 ? '<a href="index.php?r=' . $this->base_link_edition . '/show&amp;id_course=' . $row['idCourse'] . '" title="' . Lang::t('_EDITIONS', 'course') . '">' . $this->model->edition_man->getEditionNumber($row['idCourse']) . '</a>'
                        : '')),
            ];

            $perm_assign = checkPerm('assign', true, 'certificate', 'lms');
            $perm_release = checkPerm('release', true, 'certificate', 'lms');

            if ($perm_assign) {
                $list[$row['idCourse']]['certificate'] = '<a href="index.php?r=' . $this->base_link_course . '/certificate&amp;id_course=' . $row['idCourse'] . '">' . FormaLms\lib\Get::sprite('subs_pdf' . (!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course')) . '</a>';
            }

            if ($perm_release) {
                $list[$row['idCourse']]['certreleased'] = '<a href="index.php?modname=certificate&op=view_report_certificate&amp;id_course=' . $row['idCourse'] . '&from=courselist&of_platform=lms">' . FormaLms\lib\Get::sprite('subs_print' . (!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_RELEASE', 'course')) . '</a>';
                $list[$row['idCourse']]['certreleased'] = '<a href="index.php?r=alms/course/list_certificate&amp;id_course=' . $row['idCourse'] . '&amp;from=courselist">' . FormaLms\lib\Get::sprite('subs_print' . (!isset($course_with_cert[$row['idCourse']]) ? '_grey' : ''), Lang::t('_CERTIFICATE_RELEASE', 'course')) . '</a>';
            }

            $list[$row['idCourse']] = array_merge($list[$row['idCourse']], [
                'competences' => '<a href="index.php?r=' . $this->base_link_competence . '/man_course&amp;id_course=' . $row['idCourse'] . '">' . FormaLms\lib\Get::sprite('subs_competence' . (!isset($course_with_competence[$row['idCourse']]) ? '_grey' : ''), Lang::t('_COMPETENCES', 'course')) . '</a>',
                'menu' => '<a href="index.php?r=' . $this->base_link_course . '/menu&amp;id_course=' . $row['idCourse'] . '">' . FormaLms\lib\Get::sprite('subs_menu', Lang::t('_ASSIGN_MENU', 'course')) . '</a>',
                'dup' => 'ajax.adm_server.php?r=' . $this->base_link_course . '/dupcourse&id_course=' . $row['idCourse'],
                'mod' => '<a href="index.php?r=' . $this->base_link_course . '/modcourse&amp;id_course=' . $row['idCourse'] . '">' . FormaLms\lib\Get::sprite('subs_mod', Lang::t('_MOD', 'standard')) . '</a>',
                'del' => 'ajax.adm_server.php?r=' . $this->base_link_course . '/delcourse&id_course=' . $row['idCourse'] . '&confirm=1',
            ]);
        }

        if (!empty($list)) {
            $id_list = array_keys($list);
            $count_students = $this->model->getCoursesStudentsNumber($id_list);
            foreach ($list as $id_course => $cinfo) {
                $list[$id_course]['students'] = isset($count_students[$id_course]) ? $count_students[$id_course] : '0';
            }
        }

        $eventData = Events::trigger('core.course.data.listing',
            [
                'coursesList' => $list,
                'coursesResult' => $course_res,
                'coursesWithCertificates' => $course_with_cert,
                'coursesWithCompetences' => $course_with_competence,
            ]);

        $list = $eventData['coursesList'];

        $result = [
            'totalRecords' => $total_course,
            'startIndex' => $start_index,
            'sort' => $sort,
            'dir' => $dir,
            'rowsPerPage' => $results,
            'results' => count($list),
            'records' => array_values($list),
        ];

        echo $this->json->encode($result);
    }

    protected function _createLO($objectType, $idResource = null)
    {
        if ($this->lo_types_cache === false) {
            $this->lo_types_cache = [];
            $query = 'SELECT objectType, className, fileName FROM %lms_lo_types';
            $rs = sql_query($query);
            while (list($type, $className, $fileName) = sql_fetch_row($rs)) {
                $this->lo_types_cache[$type] = [$className, $fileName];
            }
        }
        /*
        $query = "SELECT className, fileName FROM %lms_lo_types WHERE objectType='".$objectType."'";
        $rs = sql_query( $query );
        list( $className, $fileName ) = sql_fetch_row( $rs );
        */
        if (!isset($this->lo_types_cache[$objectType])) {
            return null;
        }
        list($className, $fileName) = $this->lo_types_cache[$objectType];
        require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/' . $fileName);
        $lo = new $className($idResource);

        return $lo;
    }

    public function dupcourse()
    {
        if (!$this->permissions['add']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        //TO DO: make it a sqltransaction if possible

        if (isset($_POST['confirm'])) {
            $idCourseToDulicate = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
            $learningObjectsIdOrgs = [];
            $array_new_testobject = [];

            // read the old course info
            $query = "SELECT * FROM %lms_course WHERE idCourse = '" . $idCourseToDulicate . "' ";
            $result = sql_query($query);
            $courseData = sql_fetch_assoc($result);

            foreach ($courseData as $k => $v) {
                $courseData[$k] = sql_escape_string($v);
            }

            $newCourseFileData = [];

            if (!empty($courseData['imgSponsor'])) {
                $newFileNameArray = explode('_', str_replace('course_sponsor_logo_', '', $courseData['imgSponsor']));
                if (is_array($newFileNameArray) && count($newFileNameArray) >= 2) {
                    $filename = 'course_sponsor_logo_' . random_int(0, 100) . '_' . time() . '_' . str_replace('course_sponsor_logo_' . $newFileNameArray[0] . '_' . $newFileNameArray[1] . '_', '', $courseData['imgSponsor']);

                    $newCourseFileData[0]['old'] = $courseData['imgSponsor'];
                    $newCourseFileData[0]['new'] = $filename;
                    $courseData['imgSponsor'] = $filename;
                }
            }

            if (!empty($courseData['img_course'])) {
                $newFileNameArray = explode('_', str_replace('course_logo_', '', $courseData['img_course']));

                if (is_array($newFileNameArray) && count($newFileNameArray) >= 2) {
                    $filename = 'course_logo_' . random_int(0, 100) . '_' . time() . '_' . str_replace('course_logo_' . $newFileNameArray[0] . '_' . $newFileNameArray[1] . '_', '', $courseData['img_course']);

                    $newCourseFileData[1]['old'] = $courseData['img_course'];
                    $newCourseFileData[1]['new'] = $filename;
                    $courseData['img_course'] = $filename;
                }
            }

            if (!empty($courseData['img_material'])) {
                $newFileNameArray = explode('_', str_replace('course_user_material_', '', $courseData['img_material']));
                if (is_array($newFileNameArray) && count($newFileNameArray) >= 2) {
                    $filename = 'course_user_material_' . random_int(0, 100) . '_' . time() . '_' . str_replace('course_user_material_' . $newFileNameArray[0] . '_' . $newFileNameArray[1] . '_', '', $courseData['img_material']);

                    $newCourseFileData[2]['old'] = $courseData['img_material'];
                    $newCourseFileData[2]['new'] = $filename;
                    $courseData['img_material'] = $filename;
                }
            }

            if (!empty($courseData['img_othermaterial'])) {
                $newFileNameArray = explode('_', str_replace('course_otheruser_material_', '', $courseData['img_othermaterial']));
                if (is_array($newFileNameArray) && count($newFileNameArray) >= 2) {
                    $filename = 'course_otheruser_material_' . random_int(0, 100) . '_' . time() . '_' . str_replace('course_otheruser_material_' . $newFileNameArray[0] . '_' . $newFileNameArray[1] . '_', '', $courseData['img_othermaterial']);

                    $newCourseFileData[3]['old'] = $courseData['img_othermaterial'];
                    $newCourseFileData[3]['new'] = $filename;
                    $courseData['img_othermaterial'] = $filename;
                }
            }

            if (!empty($courseData['course_demo'])) {
                $newFileNameArray = explode('_', str_replace('course_demo_', '', $courseData['course_demo']));
                if (is_array($newFileNameArray) && count($newFileNameArray) >= 2) {
                    $filename = 'course_demo_' . random_int(0, 100) . '_' . time() . '_' . str_replace('course_demo_' . $newFileNameArray[0] . '_' . $newFileNameArray[1] . '_', '', $courseData['course_demo']);

                    $newCourseFileData[4]['old'] = $courseData['course_demo'];
                    $newCourseFileData[4]['new'] = $filename;
                    $courseData['course_demo'] = $filename;
                }
            }

            // duplicate the entry of learning_course
            $insertQuery = "INSERT INTO %lms_course
				( idCategory, code, name, description, lang_code, status, level_show_user,
				subscribe_method, linkSponsor, imgSponsor, img_course, img_material, img_othermaterial,
				course_demo, mediumTime, permCloseLO, userStatusOp, difficult, show_progress, show_time, show_extra_info,
				show_rules, valid_time, max_num_subscribe, min_num_subscribe,
				max_sms_budget, selling, prize, course_type, policy_point, point_to_all, course_edition, classrooms, certificates,
				create_date, security_code, imported_from_connection, course_quota, used_space, course_vote, allow_overbooking, can_subscribe,
				sub_start_date, sub_end_date, advance, show_who_online, direct_play, autoregistration_code, use_logo_in_courselist )
				VALUES
				( '" . $courseData['idCategory'] . "', '" . $courseData['code'] . "', '" . 'Copia di ' . $courseData['name'] . "', '" . $courseData['description'] . "', '" . $courseData['lang_code'] . "', '" . $courseData['status'] . "', '" . $courseData['level_show_user'] . "',
				'" . $courseData['subscribe_method'] . "', '" . $courseData['linkSponsor'] . "', '" . $courseData['imgSponsor'] . "', '" . $courseData['img_course'] . "', '" . $courseData['img_material'] . "', '" . $courseData['img_othermaterial'] . "',
				'" . $courseData['course_demo'] . "', '" . $courseData['mediumTime'] . "', '" . $courseData['permCloseLO'] . "', '" . $courseData['userStatusOp'] . "', '" . $courseData['difficult'] . "', '" . $courseData['show_progress'] . "', '" . $courseData['show_time'] . "', '" . $courseData['show_extra_info'] . "',
				'" . $courseData['show_rules'] . "', '" . $courseData['valid_time'] . "', '" . $courseData['max_num_subscribe'] . "', '" . $courseData['min_num_subscribe'] . "',
				'" . $courseData['max_sms_budget'] . "', '" . $courseData['selling'] . "', '" . $courseData['prize'] . "', '" . $courseData['course_type'] . "', '" . $courseData['policy_point'] . "', '" . $courseData['point_to_all'] . "', '" . $courseData['course_edition'] . "', '" . $courseData['classrooms'] . "', '" . $courseData['certificates'] . "',
				'" . date('Y-m-d H:i:s') . "', '" . $courseData['security_code'] . "', '" . $courseData['imported_from_connection'] . "', '" . $courseData['course_quota'] . "', '" . $courseData['used_space'] . "', '" . $courseData['course_vote'] . "', '" . $courseData['allow_overbooking'] . "', '" . $courseData['can_subscribe'] . "',
				'" . $courseData['sub_start_date'] . "', '" . $courseData['sub_end_date'] . "', '" . $courseData['advance'] . "', '" . $courseData['show_who_online'] . "', '" . $courseData['direct_play'] . "', '" . $courseData['autoregistration_code'] . "', '" . $courseData['use_logo_in_courselist'] . "' )";
            $insertResult = sql_query($insertQuery);

            if (!$insertResult) {
                ob_clean();
                ob_start();
                echo $this->json->encode(['success' => false]);
                exit();
            }

            // the id of the new course created
            $newCourseId = sql_insert_id();

            $duplicateImages = $this->request->get('image', null);
            //Create the new course file
            if (!empty($duplicateImages)) {
                $path = FormaLms\lib\Get::sett('pathcourse');
                $path = '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . (substr($path, -1) != '/' && substr($path, -1) != '\\' ? '/' : '');

                require_once _base_ . '/lib/lib.upload.php';

                sl_open_fileoperations();

                foreach ($newCourseFileData as $fileInfo) {
                    sl_copy($path . $fileInfo['old'], $path . $fileInfo['new']);
                }

                sl_close_fileoperations();
            }

            //--- copy menu data -----------------------------------------------------

            // copy the old course menu into the new one
            $menuSequence = [];
            $query = "SELECT * FROM %lms_menucourse_main WHERE idCourse = '" . $idCourseToDulicate . "' ";
            $menuResult = sql_query($query);
            foreach ($menuResult as $courseDatamen) {
                $query = 'INSERT INTO %lms_menucourse_main ' .
                    ' (idCourse, sequence, name, image) ' .
                    ' VALUES ' .
                    " ( '" . $newCourseId . "', '" . $courseDatamen['sequence'] . "', '" . $courseDatamen['name'] . "', '" . $courseDatamen['image'] . "' )";
                $result = sql_query($query);
                $menuSequence[$courseDatamen['idMain']] = sql_insert_id();
            }

            $insertQueryList = [];
            $query = "SELECT * FROM %lms_menucourse_under WHERE idCourse = '" . $idCourseToDulicate . "' ";
            $menuUnderResult = sql_query($query);
            foreach ($menuUnderResult as $menuUnderRow) {
                $idMain = $menuSequence[$menuUnderRow['idMain']];
                $insertQueryList[] = "('" . $idMain . "', '" . $newCourseId . "', '" . $menuUnderRow['sequence'] . "', '" . $menuUnderRow['idModule'] . "', '" . $menuUnderRow['my_name'] . "')";
            }
            $menuDuplicated = true;
            if (!empty($insertQueryList)) {
                $query = 'INSERT INTO %lms_menucourse_under
					(idMain, idCourse, sequence, idModule, my_name)
					VALUES ' . implode(',', $insertQueryList);
                $menuDuplicated = sql_query($query);
            }

            //--- end menu -----------------------------------------------------------

            require_once _lms_ . '/lib/lib.course.php';
            require_once _lms_ . '/lib/lib.manmenu.php';
            require_once Forma::include(_lms_ . '/lib/', 'lib.subscribe.php');

            $formaCourse = new FormaCourse($idCourseToDulicate);
            $subscribeManager = new CourseSubscribe_Manager();

            $newCourseGroupLevels = FormaCourse::createCourseLevel($newCourseId);
            $oldCourseGroupLevels = $formaCourse->getCourseLevel($idCourseToDulicate);
            $newCoursePermissions = createPermForDuplicatedCourse($oldCourseGroupLevels, $newCourseId, $idCourseToDulicate);
            $levels = $subscribeManager->getUserLevel();

            foreach ($levels as $level => $levelName) {
                foreach ($newCoursePermissions[$level] as $idrole => $value) {
                    if ($newCourseGroupLevels[$level] !== 0 && $idrole !== 0) {
                        \FormaLms\lib\Forma::getAclManager()->addToRole($idrole, $newCourseGroupLevels[$level]);
                    }
                }
            }

            $duplicateCertificates = $this->request->get('certificate', null);
            //Create the new course file
            if (!empty($duplicateCertificates)) {
                // duplicate the certificate assigned
                $insertQueryList = [];
                $query = "SELECT * FROM %lms_certificate_course WHERE id_course = '" . $idCourseToDulicate . "' ";
                $certificatesResult = sql_query($query);

                foreach ($certificatesResult as $certificateData) {
                    $insertQueryList[] = "('" . $certificateData['id_certificate'] . "', '" . $newCourseId . "', 
						'" . $certificateData['available_for_status'] . "', '" . $certificateData['point_required'] . "' )";
                }
                $menuDuplicated = true;

                if (!empty($insertQueryList)) {
                    $query = 'INSERT INTO %lms_certificate_course
						(id_certificate, id_course, available_for_status, point_required)
						VALUES ' . implode(',', $insertQueryList);
                    $menuDuplicated = sql_query($query);
                }
            }

            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orglib.php');
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.param.php');
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/track.object.php');
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/class.module/learning.object.php');

            $duplicateLearningObjects = $this->request->get('lo', null);
            if (!empty($duplicateLearningObjects)) {
                $learningObjectList = $this->request->get('lo_list', []);

                $learningObjects = [];
                $learningObjectsIdOrgs = [];
                $learningObjectPrerequisites = [];

                // retrive all the folders and object, the order by grant that folder are created before the elements contained in them
                $query = 'SELECT * FROM %lms_organization WHERE idCourse = ' . (int) $idCourseToDulicate . ' ORDER BY path ASC';
                $learningObjectResult = sql_query($query);

                // Go trough all the entry of learning_organization
                foreach ($learningObjectResult as $source) {
                    $source = (object) $source;
                    // check if LO id is checked
                    if (in_array($source->idOrg, $learningObjectList, true)) {
                        // if it's an object we must make a copy, if it's a folder we can create a row
                        // inside learning_orgation and save the id for later use
                        if (empty($source->objectType)) {
                            // is a folder
                            // create a new row in learning_organization
                            $query = "INSERT INTO %lms_organization (
								idParent,
								path, lev, title,
								objectType, idResource, idCategory, idUser,
								idAuthor, version, difficult, description,
								language, resource, objective, dateInsert,
								idCourse, prerequisites, isTerminator, idParam,
								visible, milestone)
								VALUES
								('" . ($learningObjectsIdOrgs[$source->idParent] ?? 0) . "',
								'" . $source->path . "', '" . $source->lev . "', '" . sql_escape_string($source->title) . "',
								'" . $source->objectType . "', '" . $source->idResource . "', '" . $source->idCategory . "', '" . $source->idUser . "',
								'" . $source->idAuthor . "', '" . $source->version . "', '" . $source->difficult . "', '" . sql_escape_string($source->description) . "',
								'" . $source->language . "', '" . $source->resource . "', '" . $source->objective . "', '" . $source->dateInsert . "',
								'" . $newCourseId . "', '" . $source->prerequisites . "', '" . $source->isTerminator . "', '" . $source->idParam . "',
								'" . $source->visible . "', '" . $source->milestone . "')";
                            $result = sql_query($query);
                            if ($result) {
                                $newIdReference = sql_insert_id();

                                // map for later use
                                $learningObjects['folder'][$source->idOrg] = $newIdReference;
                            }
                        } else {
                            // is an object
                            // make a copy
                            $newlearningObject = $this->_createLO($source->objectType);
                            $newIdResource = $newlearningObject->copy($source->idResource);

                            // create a new row in learning_organization
                            $query = "INSERT INTO %lms_organization (
								idParent, path, lev, title,
								objectType, idResource, idCategory, idUser,
								idAuthor, version, difficult, description,
								language, resource, objective, dateInsert,
								idCourse, prerequisites, isTerminator, idParam,
								visible, milestone)
								VALUES
								('" . ($learningObjectsIdOrgs[$source->idParent] ?? 0) . "',
								'" . $source->path . "', '" . $source->lev . "', '" . sql_escape_string($source->title) . "',
								'" . $source->objectType . "', '" . $newIdResource . "', '" . $source->idCategory . "', '" . $source->idUser . "',
								'" . $source->idAuthor . "', '" . $source->version . "', '" . $source->difficult . "', '" . sql_escape_string($source->description) . "',
								'" . $source->language . "', '" . $source->resource . "', '" . $source->objective . "', '" . $source->dateInsert . "',
								'" . $newCourseId . "', '" . $source->prerequisites . "', '" . $source->isTerminator . "', '0',
								'" . $source->visible . "', '" . $source->milestone . "')";
                            $result = sql_query($query);
                            if ($result) {
                                $newIdReference = sql_insert_id();

                                // for a learning_object we have to create a row in lo_param as well
                                // with 4.1 or 4.2 we plan to remove this table, but until then we need this
                                $query = "INSERT INTO %lms_lo_param (param_name, param_value) VALUES ('idReference', '" . $newIdReference . "') ";
                                $result = sql_query($query);
                                if ($result) {
                                    $idLoParam = sql_insert_id();

                                    $query = "UPDATE %lms_lo_param SET idParam = '" . $idLoParam . "' WHERE id = '" . $idLoParam . "' ";
                                    $result = sql_query($query);

                                    $query = "UPDATE %lms_organization SET idParam = '" . $idLoParam . "' WHERE idOrg = '" . $newIdReference . "' ";
                                    $result = sql_query($query);

                                    // map for later use
                                    $learningObjects[$source->objectType][$source->idResource] = $newIdResource;
                                }
                            }
                        }
                        // create a map for the olds and new idReferences
                        $learningObjectsIdOrgs[$source->idOrg] = $newIdReference;
                        if ($source->prerequisites != '') {
                            $learningObjectPrerequisites[$newIdReference] = $source->prerequisites;
                        }
                    }
                }

                // updates prerequisites
                foreach ($learningObjectPrerequisites as $newIdReference => $oldPrerequisites) {
                    $newPrerequisites = [];
                    $oldPrerequisites = explode(',', $oldPrerequisites);
                    foreach ($oldPrerequisites as $oldPrerequisite) {
                        //a prerequisite can be a pure number or something like 7=NULL, or 7=incomplete

                        if (array_key_exists((int) $oldPrerequisite, $learningObjectsIdOrgs)) {
                            $newLearningObjectId = $learningObjectsIdOrgs[(int) $oldPrerequisite];

                            if ($newLearningObjectId !== $newIdReference) {
                                $newPrerequisites[] = $newLearningObjectId;
                            }
                        }
                    }
                    if (!empty($newPrerequisites)) {
                        $query = 'UPDATE %lms_organization '
                            . "SET prerequisites = '" . implode(',', $newPrerequisites) . "' "
                            . 'WHERE idOrg = ' . $newIdReference . ' ';
                        $result = sql_query($query);
                    }
                }

                //--- copy forum data --------------------------------------------------

                $insertQueryList = [];
                $query = "SELECT * FROM %lms_forum WHERE idCourse = '" . $idCourseToDulicate . "' ";
                $result = sql_query($query);

                foreach ($result as $forum) {
                    $insertQueryList[] = "('" . $newCourseId . "', '" . sql_escape_string($forum['title']) . "', '" . sql_escape_string($forum['description']) . "',
						'" . $forum['locked'] . "', '" . $forum['sequence'] . "', '" . $forum['emoticons'] . "')";
                }
                $menuDuplicated = true;
                if (!empty($insertQueryList)) {
                    $query = 'INSERT INTO %lms_forum
						(idCourse, title, description, locked, sequence, emoticons)
						VALUES ' . implode(',', $insertQueryList);
                    $menuDuplicated = sql_query($query);
                }

                //--- end forum --------------------------------------------------------

                //--- copy coursereports data ------------------------------------------

                //create a conversion table for tests and scoitems coursereports
                $organizationArray = [
                    'test' => [],
                    'scoitem' => [],
                ];
                $itemsArray = [
                    'test' => [],
                    'scoitem' => [],
                ];

                $query = "SELECT source_of, id_source
					FROM %lms_coursereport WHERE id_course = '" . $idCourseToDulicate . "'
					AND source_of IN ('test', 'scoitem')";
                $result = sql_query($query);

                foreach ($result as $row) {
                    switch ($row['source_of']) {
                        case 'scoitem':
                            $itemsArray['scoitem'][] = $row['id_source'];
                            break;
                        default:
                    }
                }

                if (!empty($itemsArray['scoitem'])) {
                    //retrieve idOrgs of scoitems' scormorgs
                    $oldIdOrganizationsArray = [];
                    $oldIdentifiersArray = [];
                    $query = 'SELECT o.idOrg, o.idResource, s.idscorm_item, s.item_identifier
						FROM %lms_organization AS o
						JOIN %lms_scorm_items AS s
						ON (o.idResource = s.idscorm_organization)
						WHERE s.idscorm_item IN (' . implode(',', $itemsArray['scoitem']) . ")
						AND o.objectType = 'scormorg'";
                    $res = sql_query($query);

                    foreach ($res as $row) {
                        list($idOrg, $idResource, $idscorm_item, $item_identifier) = $row;

                        $oldIdOrganizationsArray[] = $idOrg;
                        $oldIdentifiersArray[$idOrg . '/' . $item_identifier] = $idscorm_item;
                    }
                    if (!empty($oldIdOrganizationsArray)) {
                        $newIdOrganizationsArray = [];
                        foreach ($oldIdOrganizationsArray as $idOrg) {
                            $newIdOrganizationsArray[] = $learningObjectsIdOrgs[$idOrg];
                        }
                        $query = 'SELECT o.idOrg, o.idResource, s.idscorm_item, s.item_identifier
							FROM %lms_organization AS o
							JOIN %lms_scorm_items AS s
							ON (o.idResource = s.idscorm_organization)
							WHERE o.idOrg IN (' . implode(',', $newIdOrganizationsArray) . ")
							AND o.objectType = 'scormorg'";
                        $res = sql_query($query);
                        $newToOld = array_flip($learningObjectsIdOrgs);
                        foreach ($res as $row) {
                            list($idOrg, $idResource, $idscorm_item, $item_identifier) = $row;

                            $_key = $newToOld[$idOrg] . '/' . $item_identifier;
                            if (array_key_exists($_key, $oldIdentifiersArray)) {
                                $_index = $oldIdentifiersArray[$_key];
                                $organizationArray['scoitem'][$_index] = $idscorm_item;
                            }
                        }
                    }
                }

                $insertQueryList = [];
                $query = "SELECT * FROM %lms_coursereport WHERE id_course = '" . $idCourseToDulicate . "' ";
                $result = sql_query($query);
                foreach ($result as $newOrg) {
                    $idSourceValue = 0;
                    switch ($newOrg['source_of']) {
                        case 'test':
                            $idSourceValue = !isset($learningObjects['test'][$newOrg['id_source']])
                                ? 0
                                : $learningObjects['test'][$newOrg['id_source']];

                            break;
                        case 'scoitem':
                            $idSourceValue = !isset($organizationArray['scoitem'][$newOrg['id_source']]) || $organizationArray['scoitem'][$newOrg['id_source']] == ''
                                ? 0
                                : $organizationArray['scoitem'][$newOrg['id_source']];

                            break;
                        default:
                    }

                    $insertQueryList[] = "('" . $newCourseId . "', '" . sql_escape_string($newOrg['title']) . "', '" . $newOrg['max_score'] . "',
						'" . $newOrg['required_score'] . "', '" . $newOrg['weight'] . "', '" . $newOrg['show_to_user'] . "',
						'" . $newOrg['use_for_final'] . "', '" . $newOrg['sequence'] . "', '" . $newOrg['source_of'] . "',
						'" . $idSourceValue . "')";
                }

                $result_dupman = true;
                if (!empty($insertQueryList)) {
                    $query = 'INSERT IGNORE INTO %lms_coursereport
						(id_course,title,max_score,required_score,weight,show_to_user,use_for_final,sequence,source_of,id_source)
						VALUES ' . implode(',', $insertQueryList);
                    $menuDuplicated = sql_query($query);
                }
                //--- end coursereports ------------------------------------------------

                //--- copy htmlfront data ----------------------------------------------

                $insertQueryList = [];
                $query = "SELECT * FROM %lms_htmlfront WHERE id_course = '" . $idCourseToDulicate . "' ";
                $result = sql_query($query);
                foreach ($result as $newOrg) {
                    $insertQueryList[] = "('" . $newCourseId . "', '" . sql_escape_string($newOrg['textof']) . "')";
                }

                $menuDuplicated = true;
                if (!empty($insertQueryList)) {
                    $query = 'INSERT INTO %lms_htmlfront
						(id_course, textof)
						VALUES ' . implode(',', $insertQueryList);
                    $menuDuplicated = sql_query($query);
                }

                //--- end htmlfront ----------------------------------------------------
            }

            $duplicateAdvice = $this->request->get('advice', null);
            //Create the new course file
            if (!empty($duplicateAdvice)) {
                $query = 'SELECT * FROM %lms_advice WHERE idCourse = ' . (int) $idCourseToDulicate;
                $result = sql_query($query);

                if (sql_num_rows($result) > 0) {
                    $insertQueryList = [];

                    $arraySub = [];
                    $arrayReplace = [];

                    foreach ($learningObjectsIdOrgs as $oldObjId => $newObjId) {
                        $arraySub[] = 'id_org=' . $oldObjId;
                        $arrayReplace[] = 'id_org=' . $newObjId;
                        //convert direct links to LOs. TO DO: make sure you are changing only the correct link urls
                        $arraySub[] = 'id_item=' . $oldObjId;
                        $arrayReplace[] = 'id_item=' . $newObjId;
                    }

                    foreach ($result as $row) {
                        $newDescription = (!empty($learningObjectsIdOrgs)) ? str_replace($arraySub, $arrayReplace, $row['description']) : $row['description'];
                        $insertQueryList[] = '(NULL, ' . (int) $newCourseId . ", '" . $row['posted'] . "', " . (int) $row['author'] . ", '" . $row['title'] . "', '" . $newDescription . "', " . (int) $row['important'] . ')';
                    }

                    if (!empty($insertQueryList)) {
                        $query = 'INSERT INTO %lms_advice
							(idAdvice, idCourse, posted, author, title, description, important)
							VALUES ' . implode(',', $insertQueryList);
                        sql_query($query);
                    }
                }
            }

            ob_clean();
            echo $this->json->encode(['success' => true]);
        }
    }

    public function certificate()
    {
        $perm_assign = checkPerm('assign', true, 'certificate', 'lms');

        if (!$perm_assign && !$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getErrorMessage('no permission'),
                'back_url' => 'index.php?r=' . $this->base_link_course . '/show',
            ]);

            return;
        }

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=' . $this->base_link_course . '/show');
        }

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.certificate.php');
        $cert = new Certificate();

        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        if (isset($_POST['assign'])) {
            $point_required = FormaLms\lib\Get::req('point_required', DOTY_INT, 0);

            // , $list_of_assign_obj, $list_of_who

            if (!$cert->updateCertificateCourseAssign($id_course, $_POST['certificate_assign'], $_POST['certificate_ex_assign'], $point_required, $_POST['certificate_assign_minutes'])) {
                Util::jump_to('index.php?r=' . $this->base_link_course . '/show&err=_up_cert_err');
            } else {
                Util::jump_to('index.php?r=' . $this->base_link_course . '/show&res=_up_cert_ok');
            }
        } else {
            require_once _base_ . '/lib/lib.table.php';

            $all_languages = \FormaLms\lib\Forma::langManager()->getAllLanguages(true);
            $languages = [];
            foreach ($all_languages as $k => $v) {
                $languages[$v['code']] = $v['description'];
            }

            $query = 'SELECT code, name, course_type'
                . " FROM %lms_course WHERE idCourse = '" . $id_course . "'";
            $course = sql_fetch_array(sql_query($query));

            $tb = new Table(false, Lang::t('_TITLE_CERTIFICATE_TO_COURSE', 'course'), Lang::t('_TITLE_CERTIFICATE_TO_COURSE', 'course'));

            $certificate_list = $cert->getCertificateList();
            $course_cert = $cert->getCourseCertificate($id_course);
            $course_ex_cert = $cert->getCourseExCertificate($id_course);
            $released = $cert->numOfcertificateReleasedForCourse($id_course);
            $point_required = $cert->getPointRequiredForCourse($id_course);

            $possible_status = [
                AVS_NOT_ASSIGNED => Lang::t('_NOT_ASSIGNED', 'course'),
                AVS_ASSIGN_FOR_ALL_STATUS => Lang::t('_ASSIGN_FOR_ALL_STATUS', 'course'),
                AVS_ASSIGN_FOR_STATUS_INCOURSE => Lang::t('_ASSIGN_FOR_STATUS_INCOURSE', 'course'),
                AVS_ASSIGN_FOR_STATUS_COMPLETED => Lang::t('_ASSIGN_FOR_STATUS_COMPLETED', 'course'),
            ];

            $type_h = ['nowrap', 'nowrap', '', '', 'image'];
            $cont_h = [
                Lang::t('_TITLE', 'course'),
                Lang::t('_CERTIFICATE_LANGUAGE', 'course'),
                Lang::t('_CERTIFICATE_ASSIGN_STATUS', 'course'),
                //Lang::t('_CERTIFICATE_EX_ASSIGN_STATUS', 'course'),
                Lang::t('_CERTIFICATE_RELEASED', 'course'),
            ];
            $tb->setColsStyle($type_h);
            $tb->addHead($cont_h);

            $view_cert = false;
            if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                if (checkPerm('view', true, 'certificate', 'lms')) {
                    $view_cert = true;
                }
            } else {
                $view_cert = true;
            }

            foreach ($certificate_list as $id_cert => $cert) {
                $cont = [];
                $cont[] = '<label for="certificate_assign_' . $id_cert . '">' . $cert[CERT_NAME] . '</label>';
                $cont[] = (isset($languages[$cert[CERT_LANG]]) ? $languages[$cert[CERT_LANG]] : $cert[CERT_LANG]); //lang description?
                $cont[] = Form::getInputDropdown('dropdown_nowh',
                        'certificate_assign_' . $id_cert,
                        'certificate_assign[' . $id_cert . ']',
                        $possible_status,
                        (isset($course_cert[$id_cert]['available_for_status']) ? $course_cert[$id_cert]['available_for_status'] : 0),
                        '')
                    . '<br/>'
                    . Lang::t('_ASSIGN_FOR_AT_LEAST_MINUTES', 'course') . ' '
                    . Form::getInputTextfield('dropdown_nowh',
                        'certificate_assign_minutes_' . $id_cert,
                        'certificate_assign_minutes[' . $id_cert . ']',
                        array_key_exists($id_cert, $course_cert) ? $course_cert[$id_cert]['minutes_required'] : 0,
                        '',
                        6,
                        'style="width: 40px; text-align: right;"');
                /*$cont[] = Form::getInputDropdown('dropdown_nowh',
                    'certificate_ex_assign_' . $id_cert,
                    'certificate_ex_assign[' . $id_cert . ']',
                    $possible_status,
                    (isset($course_ex_cert[$id_cert]) ? $course_ex_cert[$id_cert] : 0),
                    '');*/
                $cont[] = (isset($course_cert[$id_cert]) && $course_cert[$id_cert] != 0 && $view_cert ? '<a href="index.php?r=alms/course/list_certificate&amp;id_certificate=' . $id_cert . '&amp;id_course=' . $id_course . '&amp;from=course&amp;of_platform=lms"><b><u>' : '') . (isset($released[$id_cert]) ? $released[$id_cert] : '0') . (isset($course_cert[$id_cert]) && $course_cert[$id_cert] != 0 ? '</b></u></a>' : '');

                $tb->addBody($cont);
            }

            $course_info = $this->model->getInfo($id_course);
            $course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

            $this->render(
                'certificate', [
                'id_course' => $id_course,
                'tb' => $tb,
                'point_required' => $point_required,
                'base_link_course' => $this->base_link_course,
                'course_name' => $course_name,
            ]);
        }
    }

    public function menu()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getErrorMessage('no permission'),
                'back_url' => 'index.php?r=' . $this->base_link_course . '/show',
            ]);

            return;
        }

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=' . $this->base_link_course . '/show');
        }
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        if (isset($_POST['assign'])) {
            $id_custom = FormaLms\lib\Get::req('selected_menu', DOTY_INT, 0);

            require_once _lms_ . '/lib/lib.manmenu.php';
            require_once _lms_ . '/lib/lib.course.php';

            $acl_man = \FormaLms\lib\Forma::getAclManager();
            $course_man = new Man_Course();

            $levels = &$course_man->getCourseIdstGroupLevel($id_course);
            if (empty($levels) || implode('', $levels) == '') {
                $levels = FormaCourse::createCourseLevel($id_course);
            }

            $course_man->removeCourseRole($id_course);
            $course_man->removeCourseMenu($id_course);
            $course_idst = &$course_man->getCourseIdstGroupLevel($id_course);

            $result = createCourseMenuFromCustom($id_custom, $id_course, $course_idst);

            if ($this->session->get('idCourse') == $id_course) {
                $query = 'SELECT module.idModule, main.idMain
							FROM ( %lms_menucourse_main AS main JOIN
							%lms_menucourse_under AS un ) JOIN
							' . $GLOBALS['prefix_lms'] . "_module AS module
							WHERE main.idMain = un.idMain AND un.idModule = module.idModule
							AND main.idCourse = '" . (int) $id_course . "'
							AND un.idCourse = '" . (int) $id_course . "'
							ORDER BY main.sequence, un.sequence
							LIMIT 0,1";

                list($id_module, $id_main) = sql_fetch_row(sql_query($query));

                $this->session->set('current_main_menu', $id_main);
                $this->session->set('sel_module_id', $id_module);
                $this->session->save();

                //loading related ST
                \FormaLms\lib\FormaUser::getCurrentUser()->loadUserSectionST('/lms/course/public/');
                \FormaLms\lib\FormaUser::getCurrentUser()->saveInSession();
            }

            if ($result) {
                Util::jump_to('index.php?r=' . $this->base_link_course . '/show&res=_up_menu_ok');
            }

            Util::jump_to('index.php?r=' . $this->base_link_course . '/show&res=_up_menu_err');
        } else {
            require_once _lms_ . '/lib/lib.manmenu.php';
            $menu_custom = getAllCustom();
            $sel_custom = getAssociatedCustom($id_course);

            $course_info = $this->model->getInfo($id_course);
            $course_name = ($course_info['code'] !== '' ? '[' . $course_info['code'] . '] ' : '') . $course_info['name'];

            $this->render('menu', [
                'menu_custom' => $menu_custom,
                'sel_custom' => $sel_custom,
                'id_course' => $id_course,
                'base_link_course' => $this->base_link_course,
                'course_name' => $course_name,
            ]);
        }
    }

    public function newcourse()
    {
        if (!$this->permissions['add']) {
            $this->render('invalid', [
                'message' => $this->_getErrorMessage('no permission'),
                'back_url' => 'index.php?r=' . $this->base_link_course . '/show',
            ]);

            return;
        }

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=' . $this->base_link_course . '/show');
        }

        if (isset($_POST['save'])) {
            //resolve course type
            if ($_POST['course_type'] == 'edition') {
                $_POST['course_type'] = 'elearning';
                $_POST['course_edition'] = 1;
            } else {
                $_POST['course_edition'] = 0;
            }

            $result = $this->model->insCourse($_POST);
            $url = 'index.php?r=' . $this->base_link_course . '/show';
            foreach ($result as $key => $value) {
                $url .= '&' . $key . '=' . $value;
            }
            Util::jump_to($url);
        } else {
            $this->coursemask();
        }
    }

    public function modcourse()
    {
        if (!$this->permissions['mod']) {
            $this->render('invalid', [
                'message' => $this->_getErrorMessage('no permission'),
                'back_url' => 'index.php?r=' . $this->base_link_course . '/show',
            ]);

            return;
        }

        if (isset($_POST['undo'])) {
            Util::jump_to('index.php?r=' . $this->base_link_course . '/show');
        }

        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

        if (isset($_POST['save'])) {
            //resolve course type
            if ($_POST['course_type'] == 'edition') {
                $_POST['course_type'] = 'elearning';
                $_POST['course_edition'] = 1;
            } else {
                $_POST['course_edition'] = 0;
            }

            $result = $this->model->upCourse($id_course, $_POST);
            $url = 'index.php?r=' . $this->base_link_course . '/show';
            foreach ($result as $key => $value) {
                $url .= '&' . $key . '=' . $value;
            }
            Util::jump_to($url);
        } else {
            $this->coursemask($id_course);
        }
    }

    public function delcourse()
    {
        if (!$this->permissions['del']) {
            $output = ['success' => false, 'message' => $this->_getMessage('no permission')];
            echo $this->json->encode($output);

            return;
        }

        if (FormaLms\lib\Get::cfg('demo_mode')) {
            exit('Cannot del course during demo mode.');
        }

        if (isset($_GET['confirm'])) {
            $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);

            $op_res = $this->model->delCourse($id_course);
            if ($op_res && $this->session->has('idCourse') && $this->session->get('idCourse') == $id_course) {
                $this->session->remove('idCourse');
                $this->session->save();
            }
            $res = ['success' => $op_res];

            echo $this->json->encode($res);
        }
    }

    public function coursemask($id_course = false)
    {
        $perm_requested = $id_course ? 'mod' : 'add';
        if (!$this->permissions[$perm_requested]) {
            $this->render('invalid', [
                'message' => $this->_getErrorMessage('no permission'),
                'back_url' => 'index.php?r=' . $this->base_link_course . '/show',
            ]);

            return;
        }

        YuiLib::load();

        require_once _lms_ . '/lib/lib.levels.php';
        require_once _lms_ . '/admin/models/LabelAlms.php';
        $levels = CourseLevel::getTranslatedLevels();
        $label_model = new LabelAlms();

        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $array_lang[] = 'none';

        //status of course -----------------------------------------------------
        $status = [
            CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
            CST_AVAILABLE => Lang::t('_CST_AVAILABLE', 'course'),
            CST_EFFECTIVE => Lang::t('_CST_CONFIRMED', 'course'),
            CST_CONCLUDED => Lang::t('_CST_CONCLUDED', 'course'),
            CST_CANCELLED => Lang::t('_CST_CANCELLED', 'course'), ];
        //difficult ------------------------------------------------------------
        $difficult_lang = [
            'veryeasy' => Lang::t('_DIFFICULT_VERYEASY', 'course'),
            'easy' => Lang::t('_DIFFICULT_EASY', 'course'),
            'medium' => Lang::t('_DIFFICULT_MEDIUM', 'course'),
            'difficult' => Lang::t('_DIFFICULT_DIFFICULT', 'course'),
            'verydifficult' => Lang::t('_DIFFICULT_VERYDIFFICULT', 'course'), ];
        //type of course -------------------------------------------------------
        $course_type = [
            'classroom' => Lang::t('_CLASSROOM', 'course'),
            'elearning' => Lang::t('_COURSE_TYPE_ELEARNING', 'course'),
            'edition' => Lang::t('_COURSE_TYPE_EDITION', 'course'),
        ];

        $show_who_online = [
            0 => Lang::t('_DONT_SHOW', 'course'),
            _SHOW_COUNT => Lang::t('_SHOW_COUNT', 'course'),
            _SHOW_INSTMSG => Lang::t('_SHOW_INSTMSG', 'course'), ];

        $hours = ['-1' => '- -', '0' => '00', '01', '02', '03', '04', '05', '06', '07', '08', '09',
            '10', '11', '12', '13', '14', '15', '16', '17', '18', '19',
            '20', '21', '22', '23', ];
        $quarter = ['-1' => '- -', '00' => '00', '15' => '15', '30' => '30', '45' => '45'];

        $params = [
            'id_course' => $id_course,
            'levels' => $levels,
            'array_lang' => $array_lang,
            'label_model' => $label_model,
            'status' => $status,
            'difficult_lang' => $difficult_lang,
            'course_type' => $course_type,
            'show_who_online' => $show_who_online,
            'hours' => $hours,
            'quarter' => $quarter,
            'model' => $this->model,
        ];

        if ($id_course === false) {
            require_once _lms_ . '/lib/lib.manmenu.php';
            $menu_custom = getAllCustom();
            list($sel_custom) = current($menu_custom);
            reset($menu_custom);

            $params['menu_custom'] = $menu_custom;
            $params['sel_custom'] = $sel_custom;

            $params['name_category'] = $this->model->getCategoryName($this->_getSessionTreeData('id_category', 0));
        }

        $params['course'] = $this->model->getCourseModDetails($id_course);

        //resolve edition flag into type
        if ($params['course']['course_edition'] == 1) {
            $params['course']['course_type'] = 'edition';
        }

        if ($id_course == false) {
            $params['has_editions_or_classrooms'] = false;
        } else {
            $params['has_editions_or_classrooms'] = $this->model->hasEditionsOrClassrooms($id_course);
        }

        if ($params['course']['hour_begin'] != '-1') {
            $hb_sel = (int) substr($params['course']['hour_begin'], 0, 2);
            $qb_sel = substr($params['course']['hour_begin'], 3, 2);
        } else {
            $hb_sel = $qb_sel = '-1';
        }
        if ($params['course']['hour_end'] != '-1') {
            $he_sel = (int) substr($params['course']['hour_end'], 0, 2);
            $qe_sel = substr($params['course']['hour_end'], 3, 2);
        } else {
            $he_sel = $qe_sel = '-1';
        }
        $params['hb_sel'] = $hb_sel;
        $params['qb_sel'] = $qb_sel;
        $params['he_sel'] = $he_sel;
        $params['qe_sel'] = $qe_sel;
        $params['base_link_course'] = $this->base_link_course;

        $params['use_unsubscribe_date_limit'] = (bool) ($params['course']['unsubscribe_date_limit'] != '');
        $params['unsubscribe_date_limit'] = $params['course']['unsubscribe_date_limit'] != '' && !$params['course']['unsubscribe_date_limit']
            ? Format::date($params['course']['unsubscribe_date_limit'], 'date')
            : '';

        $subsModel = new SubscriptionAlms($id_course, ($params['course']['course_edition']  > 0 ?? false), $params['has_editions_or_classrooms'] ?? false);

        $params['subscribed'] = false;
        //check if user is already subscribed
        if ($subsModel->isUserSubscribed(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt())) {
            $params['subscribed']  = true;
        }

        $this->render('maskcourse', $params);
    }

    public function list_certificate()
    {
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, 0);
        $id_certificate = FormaLms\lib\Get::req('id_certificate', DOTY_INT, 0);
        $from = FormaLms\lib\Get::req('from');
        $op = FormaLms\lib\Get::req('op');

        require_once \FormaLms\lib\Forma::inc(_adm_ . '/lib/lib.field.php');
        $fman = new FieldList();
        $custom_field_array = $fman->getFlatAllFields();

        $data_certificate = $this->model->getListTototalUserCertificate($id_course, $id_certificate, $custom_field_array);
        // pushing empty element at the top of array
        foreach ($data_certificate as $key => $value) {
            array_unshift($data_certificate[$key], '');
        }

        $course_info = $this->model->getCourseModDetails($id_course);
        $this->render(
            'list_certificate', [
            'id_course' => $id_course,
            'id_certificate' => $id_certificate,
            'course_type' => $course_info['course_type'],
            'course_name' => $course_info['name'],
            'from' => $from,
            'data_certificate' => $data_certificate,
            'custom_fields' => $custom_field_array,
            'op' => $op,
        ]);
    }
}
