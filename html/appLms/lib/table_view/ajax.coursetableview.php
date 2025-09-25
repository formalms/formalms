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
$session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
$command = FormaLms\lib\Get::req('command', DOTY_ALPHANUM, false);

switch ($command) {
    case 'get_rows':
        $lang = &FormaLanguage::CreateInstance('course', 'lms');

        $startIndex = FormaLms\lib\Get::req('startIndex', DOTY_INT, 0);
        $results = FormaLms\lib\Get::req('results', DOTY_INT, 0); //GLOBALS --> visuItem
        $sort = FormaLms\lib\Get::req('sort', DOTY_ALPHANUM, 'name');
        $dir = FormaLms\lib\Get::req('dir', DOTY_ALPHANUM, 'asc');

        $table_status = [];
        $table_status['startIndex'] = $startIndex;
        $table_status['sort'] = $sort;
        $table_status['dir'] = $dir;

        $courseCategory = $session->get('course_category');

        $courseCategory['table_status'] = $table_status;

        $filter = FormaLms\lib\Get::req('filter', DOTY_MIXED, false);

        $filter_status = [];
        if (isset($filter['c_category']['value'])) {
            $filter_status['c_category'] = $filter['c_category']['value'];
        } else {
            $filter_status['c_category'] = $courseCategory['filter_status']['c_category'];
        }
        if (isset($filter['c_filter']['value'])) {
            $filter_status['c_filter'] = $filter['c_filter']['value'];
        } else {
            $filter_status['c_filter'] = $courseCategory['filter_status']['c_filter'];
        }
        if (isset($filter['c_flatview']['value'])) {
            $filter_status['c_flatview'] = $filter['c_flatview']['value'];
        } else {
            $filter_status['c_flatview'] = $courseCategory['filter_status']['c_flatview'];
        }
        if (isset($filter['c_waiting']['value'])) {
            $filter_status['c_waiting'] = $filter['c_waiting']['value'];
        } else {
            $filter_status['c_waiting'] = $courseCategory['filter_status']['c_waiting'];
        }

        $courseCategory['filter_status'] = $filter_status;
        $session->set('course_category', $courseCategory);
        $session->save();

        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');
        $man_courses = new Man_Course();

        require_once _lms_ . '/lib/lib.edition.php';
        $edition_manager = new EditionManager();

        $num_edition = $edition_manager->getEditionNumber();

        $course_status = [
            CST_PREPARATION => $lang->def('_CST_PREPARATION'),
            CST_AVAILABLE => $lang->def('_CST_AVAILABLE'),
            CST_EFFECTIVE => $lang->def('_CST_CONFIRMED'),
            CST_CONCLUDED => $lang->def('_CST_CONCLUDED'),
            CST_CANCELLED => $lang->def('_CST_CANCELLED'),
        ];
        $courses = [];
        $course_list = &$man_courses->getCoursesRequest($startIndex, $results, $sort, $dir, $filter);

        require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.permission.php');

        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_ADMIN) {
            $moderate = checkPerm('moderate', true, 'course', 'lms');
        } else {
            $moderate = true;
        }

        foreach ($course_list as $row) {
            $row['status'] = $course_status[$row['status']];

            $highlight = false;
            if (isset($filter['c_filter']['value']) && $filter['c_filter']['value'] != '') {
                $highlight = true;
            }

            $courses[] = [
                'idCourse' => $row['idCourse'],
                'code' => ($highlight ? highlightText($row['code'], $filter['c_filter']['value']) : $row['code']),
                'name' => ($highlight ? highlightText($row['name'], $filter['c_filter']['value']) : $row['name']),
                'status' => $row['status'],

                'waiting' => ($row['pending'] && $moderate
                    ? '<a href="index.php?modname=subscribe&op=waitinguser&id_course=' . $row['idCourse'] . '">' . $row['pending'] . '</a>'
                    : ''),

                'subscriptions' => ($row['course_edition'] != 1 ? (isset($row['subscriptions']) ? $row['subscriptions'] : 0) : '--'),
                'classroom' => ($row['course_edition'] == 1 ? '<a href="index.php?r=alms/edition/show&amp;id_course=' . $row['idCourse'] . '">' . (isset($num_edition[$row['idCourse']]) ? $num_edition[$row['idCourse']] : '0') . '</a>' : ''),
                'certificate' => true,
                'competence' => true,
                'menu' => true,
                'dup' => '<a id="dup_' . $row['idCourse'] . '" href="index.php?modname=course&amp;op=dup_course&id_course=' . $row['idCourse'] . '">' . FormaLms\lib\Get::img('standard/dup.png', $lang->def('_MAKE_A_COPY')) . '</a>',
                'mod' => true,
                'del' => true,
            ];
        }

        $output = [
            'startIndex' => (int) $startIndex,
            'recordsReturned' => count($courses),
            'sort' => $sort,
            'dir' => $dir,
            'totalRecords' => (int) $man_courses->getCoursesCountFiltered($filter),
            'pageSize' => (int) $results,
            //'totalFilteredRecords' => $man_courses->getCoursesCountFiltered($filter),
            'records' => $courses,
        ];

        $json = new Services_JSON();
        aout($json->encode($output));

     break;

    case 'del_row':
        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');

        $output = ['success' => false];

        $id_course = FormaLms\lib\Get::req('idrow', DOTY_INT, -1);
        if ($id_course > 0) {
            $man_course = new Man_Course();
            $output['success'] = $man_course->deleteCourse($id_course);
        }

        $json = new Services_JSON();
        aout($json->encode($output));
     break;

    case 'set_name':
        $output = ['success' => false];
        $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, false);
        $new_name = FormaLms\lib\Get::req('new_name', DOTY_STRING, '');

        if (is_numeric($id_course)) {
            if (sql_query('UPDATE ' . $GLOBALS['prefix_lms'] . "_course SET name='" . $new_name . "' WHERE idCourse=" . $id_course)) {
                $output['success'] = true;
            }
        }

        aout($json->encode($output));
     break;

    case 'updateField':
        require_once _base_ . '/lib/lib.json.php';

        $json = new Services_JSON();

        $id_course = FormaLms\lib\Get::req('idCourse', DOTY_INT, false);
        $field = FormaLms\lib\Get::req('col', DOTY_MIXED, false);
        $old_value = FormaLms\lib\Get::req('old_value', DOTY_MIXED, false);
        $new_value = FormaLms\lib\Get::req('new_value', DOTY_MIXED, false);

        switch ($field) {
            case 'name':
                $res = false;

                if ($new_value !== '') {
                    $query = 'UPDATE %lms_course'
                                . " SET name = '" . $new_value . "'"
                                . ' WHERE idCourse = ' . (int) $id_course;

                    $res = sql_query($query);
                }

                aout($json->encode(['success' => $res, 'new_value' => $new_value, 'old_value' => $old_value]));
            break;

            case 'code':
                $res = false;

                if ($new_value !== '') {
                    $query = 'UPDATE %lms_course'
                                . " SET code = '" . $new_value . "'"
                                . ' WHERE idCourse = ' . (int) $id_course;

                    $res = sql_query($query);
                }

                aout($json->encode(['success' => $res, 'new_value' => stripslashes($new_value), 'old_value' => stripslashes($old_value)]));
            break;
        }
    break;

    default:
}
