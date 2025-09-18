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

class OrganizationManagement
{
    protected $session;

    public function __construct(public $id_course)
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function getAllLoAbsoluteIdWhereType($objectType, $id_course = false)
    {
        if ($id_course === false) {
            $id_course = $this->session->get('idCourse');
        }

        $objectList = [];
        $query_lo = '
		SELECT idResource 
		FROM learning_organization 
		WHERE objectType IN (\'' . (is_array($objectType) ? implode('\',\'', $objectType) : $objectType) . "') AND idCourse = '" . $id_course . "'
		ORDER BY path";
        $re_lo = sql_query($query_lo);
        foreach ($re_lo as $row) {
            $objectList[] = $row['idResource'];
        }

        return $objectList;
    }

    public function getInfoWhereType($objectType = false, $id_course = false)
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
        foreach ($re_lo as $row) {
            $l_obj[$row['idOrg']] = ['id_org' => $row['idOrg'], 'title' => $row['title'], 'id_resource' => $row['idResource']];
        }

        return $l_obj;
    }

    public static function getCountUnreaded($id_user, $courses, &$last_access)
    {
        $unreaded = [];
        if (empty($courses)) {
            return $unreaded;
        }

        foreach ($courses as $id_c) {
            $query_unreaded = '
				SELECT count(idOrg) 
				FROM learning_organization LEFT JOIN learning_organization_access '
                . ' ON ( learning_organization.idOrg = ' . "learning_organization_access.idOrgAccess ) 
				WHERE idCourse = '" . $id_c . "' AND (idResource <> 0)
					AND (visible = '1')"
                . ' AND ( (' . "learning_organization_access.kind = 'user'"
                . ' 	AND ' . "learning_organization_access.value = '" . (int)$id_user . "')"
                . '	    OR learning_organization_access.idOrgAccess IS NULL'
                . ") AND UNIX_TIMESTAMP(dateInsert) >= '" . (isset($last_access[$id_c]) ? $last_access[$id_c] : 0) . "'";

            [$obj_unreaded] = sql_fetch_row(sql_query($query_unreaded));

            if (isset($unreaded[$id_c])) {
                $unreaded[$id_c] += $obj_unreaded;
            } else {
                $unreaded[$id_c] = $obj_unreaded;
            }
        }

        return $unreaded;
    }

    public static function objectFilter($arr_course, $filter_type = false)
    {
        $l_obj = [];
        $query_lo = '
		SELECT idCourse, width, height
		FROM ' . $GLOBALS['prefix_lms'] . "_organization 
		WHERE objectType = '" . $filter_type . "' 
			AND idCourse IN ( " . implode(',', $arr_course) . ' )
		GROUP BY idCourse';
        $re_lo = sql_query($query_lo);

        foreach ($re_lo as $row) {

            $l_obj[$row['idCourse']] = [$row['width'], $row['height']];
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

        foreach ($re_lo as $row) {
            $l_obj[$row['idCourse']] = ['id_org' => $row['idOrg'], 'id_resource' => $row['idResource'], 'obj_type' => $row['objectType'], 'id_course' => $row['idCourse']];
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

        foreach ($re_lo as $row) {
            $l_obj[$row['objectType']][$row['idResource']] = $row['idResource'];
            $course_obj_assoc[$row['objectType']][$row['idResource']] = $row['idCourse'];
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
                    break;
                // ------------------------------------------------------------
                case 'test':
                    require_once _lms_ . '/lib/lib.test.php';
                    $group_test = new GroupTestManagement();
                    $test_score = &$group_test->getSimpleTestsScores($l_obj['test'], $arr_user);
                    foreach ($test_score as $id_test => $test_info) {
                        $idc = $course_obj_assoc['test'][$id_test];
                        $score[$idc] = $test_info;
                    }

                    break;
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

        foreach ($re_lo as $row) {
            $l_obj[$row['idCourse']] = ['id_org' => $row['idOrg'], 'id_resource' => $row['idResource'], 'obj_type' => $row['objectType'], 'id_course' => $row['idCourse']];
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

        foreach ($re_lo as $row) {
            $l_obj[$row['objectType']][$row['idResource']] = $row['idResource'];
            $r_obj[$row['objectType']][$row['idOrg']] = $row['idOrg'];
            $course_obj_assoc[$row['objectType']][$row['idResource']] = $row['idCourse'];
            $course_org_assoc[$row['objectType']][$row['idOrg']] = $row['idCourse'];
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
                    break;
                // ------------------------------------------------------------
                case 'test':
                    require_once _lms_ . '/lib/lib.test.php';
                    $group_test = new GroupTestManagement();
                    $test_score = &$group_test->getSimpleTestsScores($l_obj['test'], $arr_user);

                    foreach ($test_score as $id_test => $test_info) {
                        $idc = $course_obj_assoc['test'][$id_test];
                        $score[$idc] = $test_info;
                    }

                    break;
            }
        }

        return $score;
    }
}
