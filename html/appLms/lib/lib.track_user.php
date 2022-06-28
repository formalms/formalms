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

/**
 * Class for tracking purpose.
 */
class TrackUser
{
    public static function createSessionCourseTrack()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->get('is_ghost', false) === true) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        //retriving last access to thecourse
        list($last_course_access) = sql_fetch_row(sql_query('
		SELECT UNIX_TIMESTAMP(MAX(lastTime)) 
		FROM ' . $GLOBALS['prefix_lms'] . "_tracksession 
		WHERE idCourse = '" . $session->get('idCourse') . "' AND idUser = '" . getLogUserId() . "'"));

        $session->set('lastCourseAccess', $last_course_access);
        $session->save();

        sql_query('UPDATE %lms_tracksession SET active = 0 WHERE idUser = ' . (int)getLogUserId() . ' and active = 1');

        sql_query("INSERT INTO %lms_tracksession 
		( idCourse, idUser, session_id, enterTime, lastTime, ip_address, active ) VALUES ( 
			'" . $session->get('idCourse') . "', 
			'" . getLogUserId() . "',
			'',
			'" . $now . "',
			'" . $now . "',
			'" . $_SERVER['REMOTE_ADDR'] . "',
			1 ) ");
        list($id) = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
        if ($id) {
            $session->set('id_enter_course', $id);
            $session->save();
        }
    }

    public static function setActionTrack($id_user, $id_course, $mod_name, $mode)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($session->get('is_ghost', false) === true) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        sql_query('
		UPDATE ' . $GLOBALS['prefix_lms'] . "_tracksession 
		SET numOp = numOp+1, 
			lastFunction = '" . $mod_name . "', 
			lastOp = '" . $mode . "', 
			lastTime = '" . $now . "',
			ip_address = '" . $_SERVER['REMOTE_ADDR'] . "'
		WHERE idEnter = '" . $session->get('id_enter_course') . "' "
            . "AND idCourse = '" . $id_course . "' AND idUser = '" . $id_user . "'");

        if (FormaLms\lib\Get::sett('tracking') == 'on' && $session->get('levelCourse') != '2') {
            $query_track = "
			INSERT INTO %lms_trackingeneral
			( idUser, idEnter, idCourse, function, type, timeof, session_id, ip ) VALUES (
				'" . $id_user . "',
				'" . $session->get('id_enter_course') . "',
				'" . $id_course . "',
				'" . $mod_name . "',
				'" . $mode . "',
				'" . $now . "',
				'',
				'" . $_SERVER['REMOTE_ADDR'] . "' )";
            sql_query($query_track);
        }
    }

    public static function closeSessionCourseTrack()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        TrackUser::setActionTrack(getLogUserId(), $session->get('idCourse'), '_COURSE_LIST', 'view');
    }

    public static function logoutSessionCourseTrack()
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->get('idCourse')) {
            TrackUser::setActionTrack(getLogUserId(), $session->get('idCourse'), '_LOGOUT', 'view');
        }
    }

    public static function getUserTotalCourseTime($idst_user, $id_course)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($session->get('is_ghost', false) === true) {
            return 0;
        }

        $tot_time = 0;
        $query_time = "SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))
		FROM %lms_tracksession WHERE idCourse = '" . $id_course . "' AND idUser = '" . $idst_user . "'";
        $re = sql_query($query_time);
        if ($re && sql_num_rows($re)) {
            list($tot_time) = sql_fetch_row(sql_query($query_time));
        }

        return $tot_time;
    }

    public static function getUserPreviousSessionCourseTime($idst_user, $id_course)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($session->get('is_ghost', false) === true) {
            return 0;
        }

        $tot_time = 0;
        $query_time = "SELECT SUM((UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)))
		FROM %lms_tracksession  WHERE idCourse = '" . $id_course . "' AND idUser = '" . $idst_user . "' "
            . " AND idEnter <> '" . $session->get('id_enter_course') . "'";
        $re = sql_query($query_time);
        if ($re && sql_num_rows($re)) {
            list($tot_time) = sql_fetch_row($re);
        }

        return $tot_time;
    }

    public static function getUserCurrentSessionCourseTime($id_course)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($session->get('is_ghost', false) === true) {
            return 0;
        }

        if ($session->get('id_enter_course')) {
            $query_time = "SELECT UNIX_TIMESTAMP(enterTime) FROM %lms_tracksession 
			WHERE idCourse = '" . $id_course . "' AND idUser = '" . getLogUserId() . "' 
				AND idEnter = '" . $session->get('id_enter_course') . "'";
            list($partial_time) = sql_fetch_row(sql_query($query_time));

            return time() - $partial_time;
        } else {
            return false;
        }
    }

    /**
     * @param int $id_course id of thecourse
     * @param int $gep_time minute of last action
     *
     * @return int the number of user in the course in the gap of time (logged included)
     */
    public function getWhoIsOnline($id_course, $gap_minute = 5)
    {
        $gap_time = date('Y-m-d H:i:s', time() - (60 * $gap_minute));
        $query_time = '
		SELECT COUNT(DISTINCT idUser)
		FROM ' . $GLOBALS['prefix_lms'] . "_tracksession 
		WHERE idCourse = '" . $id_course . "' AND active = 1 AND lastTime > '" . $gap_time . "'";
        list($who_is_online) = sql_fetch_row(sql_query($query_time));

        return $who_is_online;
    }

    /* the same as above, returning also the array of the idSt's of the users online */
    public function getListWhoIsOnline($id_course, $gap_minute = 5)
    {
        $gap_time = date('Y-m-d H:i:s', time() - (60 * $gap_minute));
        $query_time = '
		SELECT DISTINCT idUser
		FROM ' . $GLOBALS['prefix_lms'] . "_tracksession 
		WHERE idCourse = '" . $id_course . "' AND active = 1 AND (lastTime) > '" . $gap_time . "'";

        $result = sql_query($query_time);
        $who_is_online_list = [];
        while ($row = sql_fetch_array($result)) {
            $who_is_online_list[] = $row['idUser'];
        }

        return $who_is_online_list;
    }

    public static function getLastAccessToCourse($id_user)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if ($session->get('is_ghost', false) === true) {
            return 0;
        }

        $last_access = [];
        $query_time = '
		SELECT idCourse, UNIX_TIMESTAMP(MAX(lastTime)) 
		FROM ' . $GLOBALS['prefix_lms'] . "_tracksession 
		WHERE idUser = '" . $id_user . "' 
		GROUP BY idCourse";
        $re_time = sql_query($query_time);
        while (list($id_c, $access) = sql_fetch_row($re_time)) {
            $last_access[$id_c] = $access;
        }

        return $last_access;
    }

    public static function checkSession($id_user)
    {
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();

        if ($session->get('is_ghost', false) === true) {
            return true;
        }

        if ($session->get('id_enter_course')) {
            $query_time = '
			SELECT COUNT(*) 
			FROM ' . $GLOBALS['prefix_lms'] . "_tracksession 
			WHERE idUser = '" . $id_user . "' AND idEnter = '" . $session->get('id_enter_course') . "' "
                . ' AND active = 1';
            list($num_active) = sql_fetch_row(sql_query($query_time));

            return $num_active == 1;
        } else {
            return true;
        }
    }

    public static function resetUserSession($id_user)
    {
        sql_query("UPDATE %lms_tracksession  SET active = 0 WHERE idUser = '" . $id_user . "'");
    }
}
