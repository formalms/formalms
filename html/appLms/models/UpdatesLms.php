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

defined('IN_FORMA') or exit('Direct access is forbidden');

class UpdatesLms extends Model
{
    protected $db;
    protected $id_user;

    public function __construct()
    {
        parent::__construct();
        require_once _lms_ . '/lib/lib.date.php';
        require_once _lms_ . '/lib/lib.course.php';

        $this->id_user = Docebo::user()->getIdst();
        $this->db = DbConn::getInstance();
    }

    public function getPerm()
    {
        return false;
    }

    public function clean()
    {
        $this->session->remove('updates');
        $this->session->save();
    }

    public function getAll()
    {
        $updates = $this->session->get('updates');
        if (!$updates) {
            $courses_updates = $this->courseUpdates();
            $updates = [
                'elearning' => isset($courses_updates['elearning'][0]) ? $courses_updates['elearning'][0] : 0,
                'classroom' => isset($courses_updates['classroom'][0]) ? $courses_updates['classroom'][0] : 0,
                'catalog' => $this->catalogUpdates(),
                'coursepath' => $this->coursepathUpdates(),
                'games' => $this->gamesUpdates(),
                'communication' => $this->communicationUpdates(),
                'videoconference' => $this->videoconferenceUpdates(),
            ];

            $assessment = isset($courses_updates['assessment'][0]) ? $courses_updates['assessment'][0] : 0;
            if (isset($courses_updates['assessment'][1])) {
                $assessment = $courses_updates['assessment'][1];
            }
            $updates['assessment'] = $assessment;

            $this->session->set('updates', $updates);
            $this->session->save();
        }

        return $updates;
    }

    public static function resetCache()
    {
        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->remove('updates');
        \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->save();
    }

    public function courseUpdates()
    {
        $emodel = new ElearningLms();
        $cp_courses = $emodel->getUserCoursePathCourses((int) $this->id_user);

        $re = $this->db->query('SELECT c.course_type, cu.status, COUNT(*) as count '
        . ' FROM %lms_course AS c '
        . ' JOIN %lms_courseuser AS cu ON (c.idCourse = cu.idCourse) '
        . ' WHERE cu.idUser = ' . (int) $this->id_user . ' '
        . (!empty($cp_courses) ? ' AND (cu.idCourse NOT IN (' . implode(',', $cp_courses) . ') OR cu.status = ' . _CUS_END . ') ' : '')
        . ' GROUP BY c.course_type, cu.status');

        $result = [];
        while ($o = $this->db->fetch_obj($re)) {
            $result[$o->course_type][$o->status] = $o->count;
        }

        return $result;
    }

    public function catalogUpdates()
    {
        $cat = new CatalogLms();

        return $cat->getTotalCourseNumber('new');
    }

    public function coursepathUpdates()
    {
        $qtxt = 'SELECT COUNT(*)'
            . ' FROM %lms_coursepath_user '
            . ' WHERE idUser = ' . (int) $this->id_user
            . " AND ( course_completed = 0 OR date_assign >= '" . $this->session->get('last_enter') . "') ";
        $re = $this->db->query($qtxt);
        if (!$re) {
            return 0;
        }

        list($count) = $this->db->fetch_row($re);

        return $count;
    }

    public function gamesUpdates()
    {
        $arrst = Docebo::user()->getArrSt();
        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_games AS c '
            . '	JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game) ) '
            . " WHERE c.start_date <= '" . date('Y-m-d') . "'"
            . "	AND c.end_date >= '" . date('Y-m-d') . "' AND ca.idst IN ( " . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return 0;
        }

        list($count) = $this->db->fetch_row($re);

        return $count;
    }

    public function gamesCounterUpdates()
    {
        $count = ['unread' => 0, 'history' => 0];

        $arrst = Docebo::user()->getArrSt();
        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_games AS c '
            . '	JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game) ) '
            //."	LEFT JOIN %lms_games_track AS ct ON (c.id_game = ct.idReference AND ct.idUser = ".(int)$this->id_user."  )"
            . " WHERE c.start_date <= '" . date('Y-m-d') . "'"
            . "	AND c.end_date >= '" . date('Y-m-d') . "' AND ca.idst IN ( " . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return $count;
        }

        list($count['unread']) = $this->db->fetch_row($re);

        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_games AS c '
            . '	JOIN %lms_games_access AS ca ON (c.id_game = ca.id_game) ) '
            //."	LEFT JOIN %lms_games_track AS ct ON (c.id_game = ct.idReference AND ct.idUser = ".(int)$this->id_user."  )"
            . " WHERE c.end_date < '" . date('Y-m-d') . "' AND ca.idst IN ( " . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return $count;
        }

        list($count['history']) = $this->db->fetch_row($re);

        return $count;
    }

    public function communicationUpdates()
    {
        $arrst = Docebo::user()->getArrSt();
        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_communication AS c '
            . '	JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm) ) '
            . '	LEFT JOIN %lms_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ' . (int) $this->id_user . '  )'
            . " WHERE ( ct.status = 'failed' OR  ct.status = 'ab-initio' OR  ct.status = 'attempted' OR ct.idReference IS NULL ) "
            . ' AND ca.idst IN ( ' . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return 0;
        }

        list($count) = $this->db->fetch_row($re);

        return $count;
    }

    public function communicationCounterUpdates()
    {
        $count = ['unread' => 0, 'history' => 0];

        $arrst = Docebo::user()->getArrSt();
        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_communication AS c '
            . '	JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm) ) '
            . '	LEFT JOIN %lms_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ' . (int) $this->id_user . '  )'
            . " WHERE ( ct.status = 'failed' OR  ct.status = 'ab-initio' OR  ct.status = 'attempted' OR ct.idReference IS NULL ) "
            . ' AND ca.idst IN ( ' . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return $count;
        }

        list($count['unread']) = $this->db->fetch_row($re);

        $qtxt = 'SELECT COUNT(*) '
            . ' FROM ( %lms_communication AS c '
            . '	JOIN %lms_communication_access AS ca ON (c.id_comm = ca.id_comm) ) '
            . '	JOIN %lms_communication_track AS ct ON (c.id_comm = ct.idReference AND ct.idUser = ' . (int) $this->id_user . '  )'
            . " WHERE ( ct.status = 'completed' OR  ct.status = 'passed') "
            . ' AND ca.idst IN ( ' . implode(',', $arrst) . ' ) ';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return $count;
        }

        list($count['history']) = $this->db->fetch_row($re);

        return $count;
    }

    public function videoconferenceUpdates()
    {
        $qtxt = 'SELECT COUNT(*) '
            . ' FROM conference_room'
            . " WHERE starttime <= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . " AND endtime >= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . ' AND idCourse IN( SELECT idCourse'
            . '		FROM %lms_courseuser'
            . '		WHERE idUser = ' . (int) $this->id_user . ' AND status IN (0,1,2) )';
        $re = $this->db->query($qtxt);
        if (!$re) {
            return 0;
        }

        list($count) = $this->db->fetch_row($re);

        return $count;
    }

    public function videoconferenceCounterUpdates()
    {
        $count = ['live' => 0, 'planned' => 0, 'history' => 0];

        $qtxt = 'SELECT COUNT(*) '
            . ' FROM conference_room'
            . " WHERE starttime <= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . " AND endtime >= '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . ' AND idCourse IN( SELECT idCourse'
            . '		FROM %lms_courseuser'
            . '		WHERE idUser = ' . (int) $this->id_user . ' AND status IN (0,1,2) )';
        if (!$re = $this->db->query($qtxt)) {
            return $count;
        }
        list($count['live']) = $this->db->fetch_row($re);

        $qtxt = 'SELECT COUNT(*) '
            . ' FROM conference_room'
            . " WHERE starttime > '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . ' AND idCourse IN( SELECT idCourse'
            . '		FROM %lms_courseuser'
            . '		WHERE idUser = ' . (int) $this->id_user . ' AND status IN (0,1,2) )';
        if (!$re = $this->db->query($qtxt)) {
            return $count;
        }
        list($count['planned']) = $this->db->fetch_row($re);

        $qtxt = 'SELECT COUNT(*) '
            . ' FROM conference_room'
            . " WHERE endtime < '" . fromDatetimeToTimestamp(date('Y-m-d H:i:s')) . "'"
            . ' AND idCourse IN( SELECT idCourse'
            . '		FROM %lms_courseuser'
            . '		WHERE idUser = ' . (int) $this->id_user . ' AND status IN (0,1,2) )';
        if (!$re = $this->db->query($qtxt)) {
            return $count;
        }
        list($count['history']) = $this->db->fetch_row($re);

        return $count;
    }
}
