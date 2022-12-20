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

require_once _lms_ . '/class.module/track.object.php';
define('_track_scorm_basepath', $GLOBALS['where_lms'] . '/modules/scorm/');

class Track_ScormOrg extends Track_Object
{
    public $idTrack;
    public $idReference;
    public $idUser;
    public $dateAttempt;
    public $status;
    public $objectType;
    /**
     * @var string|null
     */
    public string $back_url;
    /**
     * @var int|null
     */
    public int $idParams;
    /**
     * @var int|null
     */
    public int $idResource;

    /**
     * object constructor
     * Table : learning_commontrack
     * idReference | idUser | idTrack | objectType | date_attempt  | status |.
     **/
    public function __construct($idTrack, $idResource = false, $idParams = false, $back_url = null, $environment = false)
    {
        $this->objectType = 'scormorg';
        parent::__construct($idTrack, $environment);

        $this->idResource = $idResource;
        $this->idParams = $idParams;
        if ($back_url === null) {
            $this->back_url = [];
        } else {
            $this->back_url = $back_url;
        }
    }

    /**
     * print in standard output.
     **/
    public function loadReport($idUser = false, $mvc = false)
    {
        require_once _track_scorm_basepath . 'scorm_stats.php';
        require_once _lms_ . '/lib/lib.param.php';
        if ($idUser !== false) {
            $this->idReference = getLOParam($this->idParams, 'idReference');

            return scorm_userstat($this->idResource, $idUser, $this->idReference, $mvc);
        }
    }

    /**
     * print in standard output the details of a track.
     **/
    public function loadReportDetail($idUser, $idItemDetail, $idItem = 0)
    {
        require_once _track_scorm_basepath . 'scorm_stats.php';
        if ($idUser !== false) {
            return scorm_userstat_detail($this->idResource, $idUser, $idItemDetail, $idItem);
        }
    }

    /**
     * print in standard output the details of a track.
     **/
    public function loadReportDetailHistory($idUser, $idItemDetail, $idItem)
    {
        require_once _track_scorm_basepath . 'scorm_stats.php';
        if ($idUser !== false) {
            return scorm_userstat_detailhist($this->idResource, $idUser, $idItemDetail, $idItem);
        }
    }

    /**
     * @return idTrack if exists or false
     **/
    public function deleteTrack($idTrack)
    {
        $query = 'DELETE FROM ' . $this->_table . ' '
                . " WHERE idTrack='" . (int) $idTrack . "'"
                . "   AND objectType='scormorg'";
        if (!sql_query($query)) {
            return false;
        }

        return true;
    }

    /*
     * delete all tracking info
     */
    public function deleteTrackInfo($id_lo, $id_user)
    {
        //first of all: make sure the object is of the correct type 'scormorg'
        $query = 'SELECT idUser, idReference, idTrack FROM ' . $this->_table . ' WHERE idUser=' . (int) $id_user . ' AND idReference=' . (int) $id_lo . " AND objectType='scormorg'";
        $res = sql_query($query);
        if ($res && sql_num_rows($res) > 0) {
            list($id_user, $id_lo, $idTrack) = sql_fetch_row($res);

            //collect data for tracking history table
            $arr_ids = [];
            $query = 'SELECT idscorm_tracking FROM %lms_scorm_tracking WHERE idUser=' . $id_user . ' AND idReference=' . $id_lo;
            $res = sql_query($query);
            if ($res) {
                while (list($id_scorm_tracking) = sql_fetch_row($res)) {
                    $arr_ids[] = $id_scorm_tracking;
                }
            }

            //delete tracking scorm data
            $query = 'DELETE FROM %lms_scorm_tracking WHERE idUser=' . $id_user . ' AND idReference=' . $id_lo;
            $res1 = sql_query($query);
            $query = 'DELETE FROM %lms_scorm_items_track WHERE idUser=' . $id_user . ' AND idReference=' . $id_lo;
            $res2 = sql_query($query);

            $res3 = true;
            if (count($arr_ids) > 0) {
                $query = 'DELETE FROM %lms_scorm_tracking_history WHERE idscorm_tracking IN (' . implode(',', $arr_ids) . ')';
                $res3 = sql_query($query);
            }

            if ($res1 && $res2 && $res3) {
                return $this->deleteTrack($idTrack);
            }
        }

        return false;
    }

    public function getHistory()
    {
        $query = <<<SQL
SELECT
	DATE_SUB(sth.date_action, INTERVAL COALESCE(TIME_TO_SEC(sth.session_time), 0) SECOND) AS start_datetime,
    sth.date_action AS end_datetime,
    sth.score_raw,
    sth.score_max,
    TIME_FORMAT(sth.session_time, "%H:%i:%s") AS duration,
    sth.lesson_status AS status
FROM %lms_scorm_tracking_history sth
	INNER JOIN %lms_scorm_tracking st ON sth.idscorm_tracking = st.idscorm_tracking
WHERE st.idReference = {$this->idReference} AND st.idUser = {$this->idUser}
ORDER BY sth.date_action ASC
SQL;

        $history = [];
        if ($res = sql_query($query)) {
            while ($session = sql_fetch_object($res)) {
                $history[] = $session;
            }
        }

        return $history;
    }

    public function getTotalTime()
    {
        $query = <<<SQL
SELECT TIME_FORMAT(SUM(COALESCE(TIME_TO_SEC(sth.session_time), 0)), "%H:%i:%s") AS total_time
FROM %lms_scorm_tracking_history sth
	INNER JOIN %lms_scorm_tracking st ON sth.idscorm_tracking = st.idscorm_tracking
WHERE st.idReference = {$this->idReference} AND st.idUser = {$this->idUser}
LIMIT 1
SQL;

        $total_time = null;
        if ($res = sql_query($query)) {
            list($total_time) = sql_fetch_row($res);
        }

        return $total_time;
    }
}
