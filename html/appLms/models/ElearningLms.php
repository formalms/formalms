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

class ElearningLms extends Model
{
    protected $_t_order = false;

    /**
     * This function return the correct order to use when you wish to diplay the a
     * course list for the user.
     *
     * @param <array> $t_name the table name to use as a prefix for the field, if false is passed no prefix will e used
     *                            we need a prefix for the course user rows and a prefix for the course table
     *                            array('u', 'c')
     *
     * @return <string> the order to use in a ORDER BY clausole
     */
    protected function _resolveOrder($t_name = ['', ''])
    {
        // read order for the course from database
        if ($this->_t_order === false) {
            $t_order = Forma\lib\Get::sett('tablist_mycourses', false);
            if ($t_order !== false) {
                $arr_order_course = explode(',', $t_order);
                $arr_temp = [];
                foreach ($arr_order_course as $value) {
                    switch ($value) {
                        case 'status':
                            $arr_temp[] = ' ?u.status ';
                            break;
                        case 'code':
                            $arr_temp[] = ' ?c.code ';
                            break;
                        case 'name':
                            $arr_temp[] = ' ?c.name ';
                            break;
                    }
                }
                $t_order = implode(', ', $arr_temp);
            } else {
                $t_order = '?u.status, ?c.name';
            }
            // save a class copy of the resolved list
            $this->_t_order = $t_order;
        }
        foreach ($t_name as $key => $value) {
            if (!empty($value)) {
                $t_name[$key] = $value . '.';
            }
        }

        return str_replace(['?u.', '?c.'], $t_name, $this->_t_order);
    }

    public function compileWhere($conditions, $params)
    {
        if (!is_array($conditions)) {
            return '1';
        }

        $where = [];
        $find = array_keys($params);
        foreach ($conditions as $value) {
            $where[] = str_replace($find, $params, $value);
        }

        return implode(' AND ', $where);
    }

    public function findAll($conditions, $params)
    {
        $db = DbConn::getInstance();

        // exclude course belonging to pathcourse in which the user is enrolled as a student
        $learning_path_enroll = $this->getUserCoursePathCourses($params[':id_user']);
        $exclude_pathcourse = '';
        if (count($learning_path_enroll) > 1 && Forma\lib\Get::sett('on_path_in_mycourses') == 'off') {
            $exclude_path_course = 'select idCourse from learning_courseuser where idUser=' . $params[':id_user'] . ' and level <= 3 and idCourse in (' . implode(',', $learning_path_enroll) . ')';
            $rs = $db->query($exclude_path_course);
            $excl = [];
            foreach ($rs as $d) {
                $excl[] = $d['idCourse'];
            }
            if (count($excl) > 0) {
                $exclude_pathcourse = ' and c.idCourse not in (' . implode(',', $excl) . ' )';
            }
        }

        $commonLabel = $this->session->get('id_common_label');

        $query = 'SELECT c.idCourse, c.course_type, c.idCategory, c.code, c.name, c.description, c.box_description, c.difficult,  c.status, c.level_show_user, '
            . '	  c.course_edition, c.sub_start_date, c.sub_end_date, '
            . '    c.max_num_subscribe, c.create_date, '
            . '    c.direct_play, c.img_othermaterial, c.course_demo, c.use_logo_in_courselist, c.img_course, c.lang_code, '
            . '	  c.course_vote, c.hour_end , c.hour_begin, '
            . '    c.date_begin, c.date_end, c.valid_time, c.show_result, c.userStatusOp, c.auto_unsubscribe, c.unsubscribe_date_limit , '
            . '    cu.status AS user_status, cu.level, cu.date_inscr, cu.date_first_access, cu.date_complete, cu.waiting,'
            . '    cd.unsubscribe_date_limit as date_unsubscribe_date_limit'
            . ' FROM %lms_course AS c '
            . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse)  '
            . ' left JOIN %lms_course_date AS cd ON (c.idCourse = cd.id_course)  '
            . ' left JOIN %lms_category AS cat ON (c.idCategory = cat.idCategory)  '
            . ' WHERE ' . $this->compileWhere($conditions, $params)
            . ($commonLabel > 0 ? " AND c.idCourse IN (SELECT id_course FROM %lms_label_course WHERE id_common_label = '" . $commonLabel . "')" : '')
            . $exclude_pathcourse
            . ' ORDER BY ' . $this->_resolveOrder(['cu', 'c']);

        $rs = $db->query($query);

        $result = [];
        $courses = [];
        foreach ($rs as $data) {
            $data['enrolled'] = 0;
            $data['numof_waiting'] = 0;
            $data['first_lo_type'] = false;

            //** name category
            $data['nameCategory'] = $this->getCategory($data['idCategory']); //$this->getCategory($data['idCategory']);

            $courses[] = $data['idCourse'];
            $result[$data['idCourse']] = $data;
        }

        if (!empty($courses)) {
            // find subscriptions
            $re_enrolled = $db->query(
                'SELECT c.idCourse, COUNT(*) as numof_associated, SUM(waiting) as numof_waiting'
                . ' FROM %lms_course AS c '
                . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
                . ' WHERE c.idCourse IN (' . implode(',', $courses) . ') '
                . ' GROUP BY c.idCourse'
            );
            foreach ($re_enrolled as $data) {
                $result[$data['idCourse']]['enrolled'] = $data['numof_associated'] - $data['numof_waiting'];
                $result[$data['idCourse']]['numof_waiting'] = $data['numof_waiting'];
            }

            //3562 Grifo multimedia - LR
            $query_lo = "select org.idOrg, org.idCourse, org.objectType from (SELECT o.idOrg, o.idCourse, o.objectType 
                          FROM %lms_organization AS o WHERE o.objectType != '' AND o.idCourse IN (" . implode(',', $courses) . ') ORDER BY o.path) as org 
                          GROUP BY org.idCourse ';
            // find first LO type
            $re_firstlo = $db->query($query_lo);
            foreach ($re_firstlo as $data) {
                $result[$data['idCourse']]['first_lo_type'] = $data['objectType'];
            }
        }

        return $result;
    }

    public function getFilterYears($id_user)
    {
        $output = [0 => Lang::t('_ALL_YEARS', 'course')];
        $db = DbConn::getInstance();

        $query = 'SELECT DISTINCT YEAR(cu.date_inscr) AS inscr_year '
            . ' FROM %lms_courseuser AS cu '
            . ' WHERE cu.idUser = ' . (int) $id_user
            . ' ORDER BY inscr_year ASC';

        $res = $db->query($query);
        if ($res && $db->num_rows($res) > 0) {
            foreach ($res as $row) {
                $inscr_year = $row['inscr_year'];

                if ($inscr_year == 0) {
                    $output['no-data'] = Lang::t('_NO_COURSE_DATA', 'course');
                } else {
                    $output[$inscr_year] = $inscr_year;
                }
            }
        }

        return $output;
    }

    //** Calculates the course states **
    public function getFilterStatusCourse($id_user)
    {
        $output['all'] = Lang::t('_ALL_OPEN', 'course');

        $db = DbConn::getInstance();

        $query = 'SELECT DISTINCT status AS status_course  FROM learning_courseuser WHERE learning_courseuser.idUser = ' . (int) $id_user;

        $res = $db->query($query);
        if ($res && $db->num_rows($res) > 0) {
            foreach ($res as $row) {
                $status_course = $row['status_course'];
                switch ($status_course) {
                    case 0:
                        $output[$status_course] = Lang::t('_NEW', 'standard');
                        break;
                    case 1:
                        $output[$status_course] = Lang::t('_USER_STATUS_BEGIN', 'standard');
                        break;
                    case 2:
                        $output[$status_course] = Lang::t('_COMPLETED', 'standard');
                        break;
                }
            }
        }

        return $output;
    }

    // LR: list category of subscription
    public function getListCategory($idUser, $completePath = true)
    {
        $db = DbConn::getInstance();

        $query = 'select idCategory,path from %lms_category where idcategory in (
       						select distinct idCategory from %lms_course as c,%lms_courseuser as cu where cu.idUser=' . $idUser . ' and cu.idCourse=c.idCourse)
       						ORDER BY path ASC';

        $res = $db->query($query);
        if ($res && $db->num_rows($res) > 0) {
            foreach ($res as $row) {
                $idCategory = $row['idCategory'];
                $path = $row['path'];
                if ($completePath) {
                    $category = str_replace('/root/', '', $path);
                } else {
                    $category = explode('/', $path);
                }
                $output[$idCategory] = $category[count($category) - 1];
            }
            natcasesort($output);
            $output = [0 => Lang::t('_ALL_CATEGORIES', 'standard')] + $output;
        } else {
            $output[0] = Lang::t('_NO_CATEGORY', 'standard');
        }

        return $output;
    }

    public function getUserCoursePathCourses($id_user)
    {
        require_once _lms_ . '/lib/lib.coursepath.php';
        $cp_man = new Coursepath_Manager();
        $output = [];
        $cp_list = $cp_man->getUserSubscriptionsInfo($id_user);
        if (!empty($cp_list)) {
            $cp_list = array_keys($cp_list);
            $output = $cp_man->getAllCourses($cp_list);
        }

        return $output;
    }

    private function getCategory($idCat)
    {
        $db = DbConn::getInstance();
        $query = 'select path from %lms_category where idCategory=' . $idCat;
        $res = $db->query($query);
        $path = '';
        if ($res && $db->num_rows($res) > 0) {
            list($path) = $db->fetch_row($res);
        }

        return $path;
    }
}
