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

class Track_Object
{
    public $idTrack;
    public $idReference;
    public $idUser;
    public $dateAttempt;
    public $status;
    public $firstAttempt;
    public $first_complete;
    public $last_complete;

    public $objectType;
    public $environment = 'course_lo';

    public $_table = '';

    /**
     * object constructor
     * Table : learning_commontrack
     * idReference | idUser | idTrack | objectType | date_attempt  | status |.
     **/
    public function __construct($idTrack, $environment = false)
    {
        $this->environment = ($environment ? $environment : 'course_lo');
        $this->_table = $this->getEnvironmentTable($environment);
        if ($idTrack) {
            $this->idTrack = $idTrack;
            $query = 'SELECT `idReference`, `idUser`, `idTrack`, `objectType`, `dateAttempt`, `status`, `firstAttempt`, `first_complete`, `last_complete` '
                . ' FROM `' . $this->_table . '`'
                . " WHERE idTrack='" . (int) $idTrack . "'"
                . "   AND objectType='" . $this->objectType . "'";
            $rs = sql_query($query) or
            errorCommunication('Track_Object.Track_Object');
            if (sql_num_rows($rs) == 1) {
                list($this->idReference, $this->idUser, $this->idTrack,
                    $this->objectType, $this->dateAttempt, $this->status) = sql_fetch_row($rs);
            }
        }
    }

    public function getEnvironmentTable($environment)
    {
        switch ($environment) {
            case 'communication':
                return $GLOBALS['prefix_lms'] . '_communication_track';
                break;
            case 'games':
                return $GLOBALS['prefix_lms'] . '_games_track';
                break;
            case 'course_lo':
            default:
                return $GLOBALS['prefix_lms'] . '_commontrack';
                break;
        }
    }

    public function setEnvGamesData($id_user, $id_reference, $score, $objectType)
    {
        // find prev info
        $query = 'SELECT max_score, current_score, num_attempts '
            . 'FROM ' . self::getEnvironmentTable('games') . ' '
            . "WHERE objectType = '" . $objectType . "' "
            . '	AND idReference = ' . (int) $id_reference . ' '
            . '	AND idUser = ' . (int) $id_user . ' ';
        list($max_score, $current_score, $num_attempts) = sql_fetch_row(sql_query($query));

        $data = Events::trigger('lms.lo_user.updating', [
            'id_reference' => $id_reference,
            'id_user' => $id_user,
            'object_type' => $objectType,
            'environment' => 'games',
            'old_data' => [
                'num_attempts' => $num_attempts,
                'current_score' => $current_score,
                'max_score' => $max_score,
            ],
            'new_data' => [
                'num_attempts' => $num_attempts + 1,
                'current_score' => $score,
                'max_score' => $score > $max_score ? $score : $max_score,
            ],
        ])['new_data'];

        $query = 'UPDATE ' . self::getEnvironmentTable('games') . ' SET '
            . " current_score = '" . $data['current_score'] . "', "
            . ' num_attempts = ' . $data['num_attempts'] . ' '
            . ", max_score = '" . $data['max_score'] . "' "
            . "WHERE objectType = '" . $objectType . "' "
            . '	AND idReference = ' . (int) $id_reference . ' '
            . '	AND idUser = ' . (int) $id_user . '';
        sql_query($query);

        Events::trigger('lms.lo_user.updated', [
            'id_reference' => $id_reference,
            'id_user' => $id_user,
            'object_type' => $objectType,
            'environment' => 'games',
            'old_data' => [
                'num_attempts' => $num_attempts,
                'current_score' => $current_score,
                'max_score' => $max_score,
            ],
            'new_data' => [
                'num_attempts' => $data['num_attempts'],
                'current_score' => $data['current_score'],
                'max_score' => $data['max_score'],
            ],
        ]);
    }

    /**
     * object constructor.
     *
     * @return bool
     *              create a row in global track
     **/
    public function createTrack($idReference, $idTrack, $idUser, $dateAttempt, $status, $objectType = false)
    {
        if (!$idReference || !$idTrack || !$idUser) {
            return false;
        }
        if (isset($this)) {
            $table = $this->_table;
        } else {
            $table = self::getEnvironmentTable('course_lo');
        }

        if (!$objectType) {
            $objectType = $this->objectType;
        }

        $environment = isset($this) ? $this->environment : 'course_lo';
        $firstAttempt = date('Y-m-d H:i:s');

        $data = Events::trigger('lms.lo_user.creating', [
            'id_reference' => $idReference,
            'id_user' => $idUser,
            'object_type' => $objectType,
            'id_track' => $idTrack,
            'environment' => $environment,
            'data' => [
                'firstAttempt' => $firstAttempt,
                'dateAttempt' => $dateAttempt,
                'status' => $status,
            ],
        ])['data'];

        $query = 'INSERT INTO ' . $table . ' '
            . '( `idReference`, `idUser`, `idTrack`, `objectType`, `firstAttempt`, `dateAttempt`, `status` )'
            . ' VALUES ('
            . " '" . (int) $idReference . "',"
            . " '" . (int) $idUser . "',"
            . " '" . (int) $idTrack . "',"
            . " '" . $objectType . "',"
            . " '" . $data['firstAttempt'] . "', "
            . " '" . $data['dateAttempt'] . "', "
            . " '" . $data['status'] . "'"
            . ' )';

        $result = sql_query($query)
        or errorCommunication('createTrack' . sql_error());

        //TODO: EVT_OBJECT (§)
        // include_once (_base_.'/appLms/Events/Lms/LoStatusUpdate.php');
        // $event = new \appLms\Events\Lms\LoStatusUpdate();
        // $event->setUser($idUser);
        // $event->setObjectType((($objectType==FALSE)?($this->objectType):($objectType))); // TODO: $objectTYpe vuoto
        // $event->setReference($idReference);
        // $event->setStatus($status);
        // $event->setDate($dateAttempt);
        // $event->setTrackType(\appLms\Events\Lms\LoStatusUpdate::CREATE_TRACK);
        //TODO: EVT_LAUNCH (&)
        // \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\LoStatusUpdate::EVENT_NAME, $event);

        Events::trigger('lms.lo_user.created', [
            'id_reference' => $idReference,
            'id_user' => $idUser,
            'object_type' => $objectType,
            'id_track' => $idTrack,
            'environment' => $environment,
            'data' => [
                'firstAttempt' => $data['firstAttempt'],
                'dateAttempt' => $data['dateAttempt'],
                'status' => $data['status'],
            ],
        ]);

        if (isset($this)) {
            $this->idReference = $idReference;
            $this->idUser = $idUser;
            $this->idTrack = $idTrack;
            $this->objectType = $objectType;
            $this->dateAttempt = $data['dateAttempt'];
            $this->status = $data['status'];

            $this->_setCourseCompleted();
        }
    }

    public function getObjectType()
    {
        return $this->objectType;
    }

    public function getDate()
    {
        return $this->dateAttempt;
    }

    public function setDate($new_date)
    {
        $this->dateAttempt = $new_date;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($new_status)
    {
        $this->status = $new_status;
    }

    public function update()
    {
        $class = get_class($this);
        $old_track = new $class($this->idTrack, $this->environment);

        $data = Events::trigger('lms.lo_user.updating', [
            'id_reference' => $this->idReference,
            'id_user' => $this->idUser,
            'object_type' => $this->objectType,
            'id_track' => $this->idTrack,
            'environment' => $this->environment,
            'old_data' => [
                'dateAttempt' => $old_track->getDate(),
                'status' => $old_track->getStatus(),
            ],
            'new_data' => [
                'dateAttempt' => $this->dateAttempt,
                'status' => $this->status,
            ],
        ])['new_data'];

        $query = 'UPDATE ' . $this->_table . ' SET '
            . " dateAttempt ='" . $data['dateAttempt'] . "',"
            . " status ='" . $data['status'] . "'"
            . " WHERE idTrack = '" . (int) $this->idTrack . "' AND objectType = '" . $this->objectType . "'";
        if (!sql_query($query) || sql_affected_rows() === 0) {
            $query = 'INSERT INTO ' . $this->_table . ' '
                . '( `idReference`, `idUser`, `idTrack`, `objectType`, `firstAttempt`, `dateAttempt`, `status` )'
                . ' VALUES ('
                . " '" . (int) $this->idReference . "',"
                . " '" . (int) $this->idUser . "',"
                . " '" . (int) $this->idTrack . "',"
                . " '" . $this->objectType . "',"
                . " '" . $data['dateAttempt'] . "', "
                . " '" . $data['dateAttempt'] . "', "
                . " '" . $data['status'] . "'"
                . ' )';

            if (!sql_query($query)) {
                return false;
            }
        }
        //TODO: EVT_OBJECT (§)
        // include_once (_base_.'/appLms/Events/Lms/LoStatusUpdate.php');
        // $event = new \appLms\Events\Lms\LoStatusUpdate();
        // $event->setUser($this->idUser);
        // $event->setObjectType($this->objectType?$this->objectType:$this->_table); // TODO: $objectTYpe vuoto
        // $event->setReference($this->idReference);
        // $event->setStatus($this->status);
        // $event->setDate($this->dateAttempt);
        // $event->setTrackType(\appLms\Events\Lms\LoStatusUpdate::UPDATE_TRACK);
        //TODO: EVT_LAUNCH (&)
        // \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\LoStatusUpdate::EVENT_NAME, $event);

        Events::trigger('lms.lo_user.updated', [
            'id_reference' => $this->idReference,
            'id_user' => $this->idUser,
            'object_type' => $this->objectType,
            'id_track' => $this->idTrack,
            'environment' => $this->environment,
            'old_data' => [
                'dateAttempt' => $old_track->getDate(),
                'status' => $old_track->getStatus(),
            ],
            'new_data' => [
                'dateAttempt' => $data['dateAttempt'],
                'status' => $data['status'],
            ],
        ]);

        $this->_setCourseCompleted();

        return true;
    }

    public function _setCourseCompleted()
    {
        if ($this->environment != 'course_lo') {
            return;
        }
        if ($this->status == 'completed' || $this->status == 'passed') {
            //update complete dates in DB
            $query = 'SELECT first_complete, last_complete FROM %lms_commontrack WHERE idTrack=' . (int) $this->idTrack;
            $res = sql_query($query);
            if ($res && sql_num_rows($res) > 0) {
                $now = date('Y-m-d H:i:s');
                list($first_complete, $last_complete) = sql_fetch_row($res);

                $old_data = ['last_complete' => $last_complete];
                $new_data = ['last_complete' => $now];

                if (!$first_complete || $first_complete > $now) {
                    $old_data['first_complete'] = $first_complete;
                    $new_data['first_complete'] = $now;
                }

                $data = Events::trigger('lms.lo_user.updating', [
                    'id_reference' => $this->idReference,
                    'id_user' => $this->idUser,
                    'object_type' => $this->objectType,
                    'id_track' => $this->idTrack,
                    'environment' => $this->environment,
                    'old_data' => $old_data,
                    'new_data' => $new_data,
                ])['new_data'];

                $query = "UPDATE %lms_commontrack SET last_complete='" . $data['last_complete'] . "'";
                if (array_key_exists('first_complete', $data)) {
                    $query .= ", first_complete='" . $data['first_complete'] . "'";
                }
                $query .= ' WHERE idTrack=' . (int) $this->idTrack;
                $res = sql_query($query);

                Events::trigger('lms.lo_user.updated', [
                    'id_reference' => $this->idReference,
                    'id_user' => $this->idUser,
                    'object_type' => $this->objectType,
                    'id_track' => $this->idTrack,
                    'environment' => $this->environment,
                    'old_data' => $old_data,
                    'new_data' => [
                        'last_complete' => $data['last_complete'],
                        'first_complete' => $data['first_complete'],
                    ],
                ]);
            }
            //---
            /*
                        if(isset($_SESSION['idCourse'])) {

                            $idCourse = $_SESSION['idCourse'];
                        } else {
                            */
            // the only way is a direct query :(, or else if more than one course is open only the last one will complete
            $query = 'SELECT idCourse '
                . 'FROM %lms_organization '
                . "WHERE idOrg = '" . (int) $this->idReference . "' ";
            list($idCourse) = sql_fetch_row(sql_query($query));
            //}
            $useridst = $this->idUser;
            require_once Forma::inc(_lms_ . '/modules/organization/orglib.php');
            $repoDb = new OrgDirDb($idCourse);
            $item = $repoDb->getFolderById($this->idReference);
            $values = $item->otherValues;
            $isTerminator = (isset($values[ORGFIELDISTERMINATOR]) && $values[ORGFIELDISTERMINATOR]);

            if ($isTerminator) {
                require_once $GLOBALS['where_lms'] . '/lib/lib.course.php';
                require_once $GLOBALS['where_lms'] . '/lib/lib.stats.php';
                saveTrackStatusChange((int) $useridst, (int) $idCourse, _CUS_END);
            }
        }
    }

    /**
     * print in standard output ($mvc parameter: to be set if we are in a mvc module).
     **/
    public function loadReport($idUser = false, $mvc = false)
    {
    }

    /**
     * print in standard output the details of a track.
     **/
    public function loadReportDetail($idUser, $idItemDetail)
    {
    }

    /**
     * print in standard output.
     *
     * @return nothing
     **/
    public function loadObjectReport()
    {
        return;
    }

    /**
     * static function to fast compute prerequisites.
     **/
    public function isPrerequisitesSatisfied($arrId, $idUser, $environment = false)
    {
        if (is_string($arrId)) {
            if (strlen($arrId) > 0) {
                if ($arrId[0] == ',') {
                    $arrId = substr($arrId, 1);
                }
            }
        }
        if ($arrId == '') {
            return true;
        } else {
            // in this brach we extract two array
            // 1) $idList array of id for use in query
            // 2) $arrPre array composed by $id => $status
            $idList = [];
            $arrTokens = explode(',', $arrId);
            while (($val = current($arrTokens)) !== false) {
                $arrPeer = explode('=', $val);
                if ($arrPeer[0] !== 'rray') {    // patch to skip wrong prerequisites
                    // saved in db in first version of 3.0.1
                    if (count($arrPeer) > 1) {
                        $arrPre[$arrPeer[0]] = $arrPeer[1];
                    } else {
                        $arrPre[$arrPeer[0]] = 'completed';
                    }
                    $idList[] = $arrPeer[0];
                }
                next($arrTokens);
            }
        }
        if (empty($idList)) {
            return true;
        } else {
            $query = 'SELECT idReference, status '
                . ' FROM ' . self::getEnvironmentTable($environment) . ''
                . ' WHERE ((idReference IN ( ' . rtrim(implode(',', $idList), ',') . ' ))'
                . "   AND (idUser = '" . (int) $idUser . "'))";
        }
        // ."   AND ((status = 'completed') OR (status = 'passed')))";
        $rs = sql_query($query)
        or exit("Error in query=[ $query ] " . sql_error());

        //echo "\n".'<!-- sto controllando i prerequisiti con questa query : '.$query.' -->';
        while (list($id, $status) = sql_fetch_row($rs)) {
            $arrStatus[$id] = $status;
        }

        //if(isset($arrStatus)) echo "\n".'<!-- gli stati letti per i prerequisiti chiesti sono : '.print_r($arrStatus, true).' -->';
        //else echo "\n".'<!-- nessuno dei prerequisiti � stato tracciato -->';
        foreach ($arrPre as $id => $status) {
            switch ($status) {
                case 'NULL':
                    if (isset($arrStatus[$id])) {
                        return false;
                    }
                    break;
                case 'completed':
                case 'passed':
                    if (!isset($arrStatus[$id])
                        || ($arrStatus[$id] != 'completed' && $arrStatus[$id] != 'passed')) {
                        return false;
                    }
                    break;
                case 'failed':
                case 'incomplete':
                case 'not attempted':
                case 'attempted':
                case 'ab-initio':
                    if (isset($arrStatus[$id])
                        && ($arrStatus[$id] != 'failed'
                            && $arrStatus[$id] != 'incomplete'
                            && $arrStatus[$id] != 'not attempted'
                            && $arrStatus[$id] != 'attempted'
                            && $arrStatus[$id] != 'ab-initio')) {
                        return false;
                    }
                    break;
            }
        }

        return true;
    }

    /**
     * static function to get status.
     **/
    public function getStatusFromId($idReference, $idUser, $environment = false)
    {
        $query = 'SELECT status '
            . ' FROM ' . self::getEnvironmentTable($environment) . ''
            . ' WHERE (idReference = ' . (int) $idReference . ')'
            . "   AND (idUser = '" . (int) $idUser . "')"
            . ' ORDER BY `dateAttempt` DESC';
        $rs = sql_query($query)
        or exit("Error in query=[ $query ] " . sql_error());

        if (sql_num_rows($rs) == 0) {
            return 'not attempted';
        } else {
            for ($i = 0; $i < sql_num_rows($rs); ++$i) {
                list($status) = sql_fetch_row($rs);
                if ($status == 'passed' || $status == 'completed') {
                    break;
                }
            }

            return $status;
        }
    }

    /**
     * @return idTrack if found else false
     **/
    public function getIdTrackFromCommon($idReference, $idUser, $environment = false)
    {
        $query = 'SELECT idTrack '
            . ' FROM ' . self::getEnvironmentTable($environment) . ''
            . ' WHERE (idReference = ' . (int) $idReference . ')'
            . "   AND (idUser = '" . (int) $idUser . "')";
        $rs = sql_query($query)
        or exit("Error in query=[ $query ] " . sql_error());

        if (sql_num_rows($rs) == 0) {
            return false;
        } else {
            list($idTrack) = sql_fetch_row($rs);

            return $idTrack;
        }
    }

    public function delIdTrackFromCommon($idReference)
    {
        Events::trigger('lms.lo_user.deleting', [
            'ids_reference' => (array) $idReference,
            'environment' => $this->environment,
        ]);

        if (is_numeric($idReference)) {
            $query = 'DELETE FROM ' . $this->_table . ''
                . ' WHERE (idReference = ' . (int) $idReference . ')';
        } elseif (is_array($idReference)) {
            $query = 'DELETE FROM ' . $this->_table . ''
                . ' WHERE (idReference IN (' . implode(',', $idReference) . '))';
        }
        $rs = sql_query($query)
        or exit("Error in query=[ $query ] " . sql_error());

        Events::trigger('lms.lo_user.deleted', [
            'ids_reference' => (array) $idReference,
            'environment' => $this->environment,
        ]);

        return $rs;
    }

    /**
     * @return bool true if this object use extra colum in user report
     */
    public function otherUserField()
    {
        return false;
    }

    /**
     * @return array an array with the header of extra colum
     */
    public function getHeaderUserField()
    {
        return [];
    }

    /**
     * @return array an array with the extra colum
     */
    public function getUserField()
    {
        return [];
    }

    public function updateObjectTitle($idResource, $objectType, $new_title)
    {
        $new_title = str_replace('/', '', $new_title);

        $re = true;

        $query_search = '
		SELECT path
		FROM ' . $GLOBALS['prefix_lms'] . "_homerepo 
		WHERE idResource = '" . (int) $idResource . "'  
			AND objectType = '" . $objectType . "'
		LIMIT 1";
        $re_search = sql_query($query_search);
        while (list($path) = sql_fetch_row($re_search)) {
            $path_piece = explode('/', $path);
            unset($path_piece[count($path_piece) - 1]);
            $new_path = implode('/', $path_piece) . '/' . $new_title;

            $query_lo = '
			UPDATE ' . $GLOBALS['prefix_lms'] . "_homerepo
			SET path = '" . $new_path . "', title = '" . $new_title . "' 
			WHERE idResource = '" . (int) $idResource . "'  
				AND objectType = '" . $objectType . "'";
            $re &= sql_query($query_lo);
        }

        $query_lo = '
		UPDATE ' . $GLOBALS['prefix_lms'] . "_organization
		SET title = '" . $new_title . "' 
		WHERE idResource = '" . (int) $idResource . "'  
			AND objectType = '" . $objectType . "'";
        $re &= sql_query($query_lo);

        $query_search = '
		SELECT path
		FROM ' . $GLOBALS['prefix_lms'] . "_repo 
		WHERE idResource = '" . (int) $idResource . "'  
			AND objectType = '" . $objectType . "'
		LIMIT 1";
        $re_search = sql_query($query_search);
        while (list($path) = sql_fetch_row($re_search)) {
            $path_piece = explode('/', $path);
            unset($path_piece[count($path_piece) - 1]);
            $new_path = implode('/', $path_piece) . '/' . $new_title;

            $query_lo = '
			UPDATE ' . $GLOBALS['prefix_lms'] . "_repo
			SET path = '" . $new_path . "', title = '" . $new_title . "' 
			WHERE idResource = '" . (int) $idResource . "'  
				AND objectType = '" . $objectType . "'";
            $re &= sql_query($query_lo);
        }

        return $re;
    }

    /**
     * @return idTrack if exists or false
     **/
    public function deleteTrack($idTrack)
    {
        return true;
    }

    public function deleteTrackInfo($id_lo, $id_user)
    {
        Events::trigger('lms.lo_user.deleting', [
            'id_reference' => $id_lo,
            'id_user' => $id_user,
            'environment' => $this->environment,
        ]);

        $query = 'DELETE FROM ' . $this->_table . ' WHERE idUser=' . (int) $id_user . ' AND idReference=' . (int) $id_lo;
        $res = sql_query($query);

        Events::trigger('lms.lo_user.deleted', [
            'id_reference' => $id_lo,
            'id_user' => $id_user,
            'environment' => $this->environment,
        ]);

        $query = 'DELETE FROM %lms_materials_track WHERE idUser=' . (int) $id_user . ' AND idReference=' . (int) $id_lo;
        $res = sql_query($query);

        return $res;
    }

    public function getHistory()
    {
        return [];
    }

    public function getTotalTime()
    {
        return null;
    }
}
