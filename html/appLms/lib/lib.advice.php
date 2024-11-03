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

class Man_Advice
{
    public static function getCountUnreaded($id_user, $courses, &$last_access)
    {
        if (empty($courses)) {
            return [];
        }

        $unreaded = [];
        $query_unreaded = '
		SELECT idCourse, UNIX_TIMESTAMP(posted) 
		FROM ' . $GLOBALS['prefix_lms'] . "_advice 
		WHERE author <> '" . $id_user . "' AND idCourse IN ( " . implode(',', $courses) . ' ) ';
        $re_advice = sql_query($query_unreaded);
        if (!sql_num_rows($re_advice)) {
            return [];
        }

        while (list($id_c, $posted) = sql_fetch_row($re_advice)) {
            if (!isset($last_access[$id_c])) {
                if (isset($unreaded[$id_c])) {
                    ++$unreaded[$id_c];
                } else {
                    $unreaded[$id_c] = 1;
                }
            } elseif ($posted > $last_access[$id_c]) {
                if (isset($unreaded[$id_c])) {
                    ++$unreaded[$id_c];
                } else {
                    $unreaded[$id_c] = 1;
                }
            }
        }

        return $unreaded;
    }

    /**
     * @param int $id_course the id of the course to be deleted
     *
     * @return bool true if success false otherwise
     */
    public function deleteAllCourseAdvices($id_course)
    {
        //validate input
        if ((int) $id_course <= 0) {
            return false;
        }

        $db = \FormaLms\db\DbConn::getInstance();

        $db->start_transaction();

        //get all existing advices for the course
        $arr_id_advice = [];
        $query = 'SELECT idAdvice FROM %lms_advice WHERE idCourse = ' . (int) $id_course;
        $res = $db->query($query);
        while (list($id_advice) = $db->fetch_row($res)) {
            $arr_id_advice[] = $id_advice;
        }

        //delete all adviceusers
        if (!empty($arr_id_advice)) {
            $query = 'DELETE FROM %lms_adviceuser WHERE idAdvice IN (' . implode(',', $arr_id_advice) . ')';
            $res = $db->query($query);
            if (!$res) {
                $db->rollback();

                return false;
            }
        }

        //delete course advices
        $query = "DELETE FROM %lms_advice WHERE idCourse = '" . (int) $id_course . "'";
        $res = $db->query($query);
        if (!$res) {
            $db->rollback();

            return false;
        }

        $db->commit();

        return true;
    }
}
