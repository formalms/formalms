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

class OrganizationManagement
{
    public $id_course;
    protected $session;

    public function __construct($id_course)
    {
        $this->id_course = $id_course;
        $this->session = \Forma\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function &getAllLoAbsoluteIdWhereType($objectType, $id_course = false)
    {
        if ($id_course === false) {
            $id_course = $this->session->get('idCourse');
        }

        $l_obj = [];
        $query_lo = '
		SELECT idResource 
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE objectType IN ('" . (is_array($objectType) ? implode('\',\'', $objectType) : $objectType) . "') AND idCourse = '" . $id_course . "'
		ORDER BY path";
        $re_lo = sql_query($query_lo);
        while (list($id_lo) = sql_fetch_row($re_lo)) {
            $l_obj[$id_lo] = $id_lo;
        }

        return $l_obj;
    }

    public function &getInfoWhereType($objectType = false, $id_course = false)
    {
        if ($id_course === false) {
            $id_course = $this->session->get('idCourse');
        }

        $l_obj = [];
        $query_lo = '
		SELECT idOrg, title, idResource  
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE idCourse = '" . $id_course . "'"
        . ($objectType !== false ? " AND objectType = '" . $objectType . "' " : '')
        . 'ORDER BY path';
        $re_lo = sql_query($query_lo);
        while (list($id_org, $title, $id_resource) = sql_fetch_row($re_lo)) {
            $l_obj[$id_org] = ['id_org' => $id_org, 'title' => $title, 'id_resource' => $id_resource];
        }

        return $l_obj;
    }

    public function getCountUnreaded($id_user, $courses, &$last_access)
    {
        $unreaded = [];
        if (empty($courses)) {
            return $unreaded;
        }

        foreach ($courses as $id_c) {
            $query_unreaded = '
				SELECT count(idOrg) 
				FROM ' . $GLOBALS['prefix_lms'] . '_organization LEFT JOIN ' . $GLOBALS['prefix_lms'] . '_organization_access '
                    . ' ON ( ' . $GLOBALS['prefix_lms'] . '_organization.idOrg = ' . $GLOBALS['prefix_lms'] . "_organization_access.idOrgAccess ) 
				WHERE idCourse = '" . $id_c . "' AND (idResource <> 0)
					AND (visible = '1')"
                    . ' AND ( (' . $GLOBALS['prefix_lms'] . "_organization_access.kind = 'user'"
                    . ' 	AND ' . $GLOBALS['prefix_lms'] . "_organization_access.value = '" . (int) $id_user . "')"
                    . '	    OR ' . $GLOBALS['prefix_lms'] . '_organization_access.idOrgAccess IS NULL'
                    . ") AND UNIX_TIMESTAMP(dateInsert) >= '" . (isset($last_access[$id_c]) ? $last_access[$id_c] : 0) . "'";

            list($obj_unreaded) = sql_fetch_row(sql_query($query_unreaded));

            if (isset($unreaded[$id_c])) {
                $unreaded[$id_c] += $obj_unreaded;
            } else {
                $unreaded[$id_c] = $obj_unreaded;
            }
        }

        return $unreaded;
    }

    public function objectFilter($arr_course, $filter_type = false)
    {
        $l_obj = [];
        $query_lo = '
		SELECT idCourse, width, height
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE objectType = '" . $filter_type . "' 
			AND idCourse IN ( " . implode(',', $arr_course) . ' )
		GROUP BY idCourse';
        $re_lo = sql_query($query_lo);

        while (list($id_course, $width, $height) = sql_fetch_row($re_lo)) {
            $l_obj[$id_course] = [$width, $height];
        }

        return $l_obj;
    }

    public function getStartObjectId($arr_course = false)
    {
        $l_obj = [];
        $query_lo = '
		SELECT idOrg, idResource, objectType, idCourse  
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE milestone = 'start' "
        . ($arr_course !== false ? ' AND idCourse IN ( ' . implode(',', $arr_course) . ' )' : '') . ' ';
        $re_lo = sql_query($query_lo);

        while (list($id_org, $id_resource, $obj_type, $id_course) = sql_fetch_row($re_lo)) {
            $l_obj[$id_course] = ['id_org' => $id_org, 'id_resource' => $id_resource, 'obj_type' => $obj_type, 'id_course' => $id_course];
        }

        return $l_obj;
    }

    public function getStartObjectScore($arr_user, $arr_course = false)
    {
        $l_obj = [];
        $score = [];
        $query_lo = '
		SELECT idOrg, idResource, objectType, idCourse  
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE milestone = 'start' "
        . (!empty($arr_course)/* !== false */ ? ' AND idCourse IN ( ' . implode(',', $arr_course) . ' )' : '') . ' ';
        $re_lo = sql_query($query_lo);

        while (list($id_org, $id_resource, $obj_type, $id_course) = sql_fetch_row($re_lo)) {
            $l_obj[$obj_type][$id_resource] = $id_resource;
            $course_obj_assoc[$obj_type][$id_resource] = $id_course;
        }

        $obj_types = array_keys($l_obj);
        foreach ($obj_types as $type) {
            switch ($type) {
                case 'scormorg':
                    require_once _lms_ . '/lib/lib.scorm.php';
                    $group_test = new GroupScormObjMan();
                    $scorm_score = &$group_test->getSimpleScormScores($l_obj['scormorg'], $arr_user);
          foreach ($scorm_score as $id_test => $scorm_info) {
              $idc = $course_obj_assoc['scormorg'][$id_test];
              $score[$idc] = $scorm_info;
          }
                ; break;
                // ------------------------------------------------------------
                case 'test':
                    require_once _lms_ . '/lib/lib.test.php';
                    $group_test = new GroupTestManagement();
                    $test_score = &$group_test->getSimpleTestsScores($l_obj['test'], $arr_user);
          foreach ($test_score as $id_test => $test_info) {
              $idc = $course_obj_assoc['test'][$id_test];
              $score[$idc] = $test_info;
          }

                ; break;
            }
        }

        return $score;
    }

    public function getFinalObjectId($arr_course = false)
    {
        $l_obj = [];
        $query_lo = '
		SELECT idOrg, idResource, objectType, idCourse  
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE milestone = 'end' "
        . ($arr_course !== false ? ' AND idCourse IN ( ' . implode(',', $arr_course) . ' )' : '') . ' ';
        $re_lo = sql_query($query_lo);

        while (list($id_org, $id_resource, $obj_type, $id_course) = sql_fetch_row($re_lo)) {
            $l_obj[$id_course] = ['id_org' => $id_org, 'id_resource' => $id_resource, 'obj_type' => $obj_type, 'id_course' => $id_course];
        }

        return $l_obj;
    }

    public function getFinalObjectScore($arr_user, $arr_course = false)
    {
        $l_obj = [];
        $r_obj = [];
        $score = [];
        $query_lo = '
		SELECT idOrg, idResource, objectType, idCourse  
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE milestone = 'end' "
        . (!empty($arr_course)/* !== false */ ? ' AND idCourse IN ( ' . implode(',', $arr_course) . ' )' : '') . ' ';
        $re_lo = sql_query($query_lo);

        while (list($id_org, $id_resource, $obj_type, $id_course) = sql_fetch_row($re_lo)) {
            $l_obj[$obj_type][$id_resource] = $id_resource;
            $r_obj[$obj_type][$id_org] = $id_org;
            $course_obj_assoc[$obj_type][$id_resource] = $id_course;
            $course_org_assoc[$obj_type][$id_org] = $id_course;
        }

        $obj_types = array_keys($l_obj);
        foreach ($obj_types as $type) {
            switch ($type) {
                case 'scormorg':
                    require_once _lms_ . '/lib/lib.scorm.php';
                    $group_test = new GroupScormObjMan();
                    $scorm_score = &$group_test->getSimpleScormScores($r_obj['scormorg'], $arr_user);

                    foreach ($scorm_score as $id_test => $scorm_info) {
                        $idc = $course_org_assoc['scormorg'][$id_test];
                        $score[$idc] = $scorm_info;
                    }
                ; break;
                // ------------------------------------------------------------
                case 'test':
                    require_once _lms_ . '/lib/lib.test.php';
                    $group_test = new GroupTestManagement();
                    $test_score = &$group_test->getSimpleTestsScores($l_obj['test'], $arr_user);

          foreach ($test_score as $id_test => $test_info) {
              $idc = $course_obj_assoc['test'][$id_test];
              $score[$idc] = $test_info;
          }

                ; break;
            }
        }

        return $score;
    }
}
