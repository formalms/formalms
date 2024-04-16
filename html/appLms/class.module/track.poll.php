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

require_once _lms_ . '/class.module/track.object.php';

class Track_Poll extends Track_Object
{
    /**
     * @var string|null
     */
    public $back_url;
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
     * id_reference | idUser | id_track | objectType | date_attempt  | status |.
     **/
    public function __construct($idTrack, $idResource = false, $idParams = false, $backUrl = null)
    {
        $this->objectType = 'poll';
        parent::__construct($idTrack);

        $this->idResource = $idResource;
        $this->idParams = $idParams;
        if ($backUrl === null) {
            $this->back_url = [];
        } else {
            $this->back_url = $backUrl;
        }
    }

    /**
     * function createTrack( $idUser, $idTest, $idReference ).
     *
     * create a new row in the _testtrack table for tracking purpose
     *
     * @param int $idUser the id of the user that display the object
     * @param int $idTest the id of the test that is displayed
     * @param int $idReference the idReference from the table of the lesson
     *
     * @return int|false idTrack if the row is created correctly otherwise false
     **/
    public function createNewTrack($idUser, $idResource, $idReference)
    {
        if ($this->session->get('levelCourse') < 6) {
            $query = "INSERT INTO %lms_polltrack 
			SET id_user = '" . (int)$idUser . "', 
				id_poll = '" . (int)$idResource . "', 
				id_reference = '" . (int)$idReference . "', 
				date_attempt = '" . date('Y-m-d H:i:s') . "'";
            if (!sql_query($query)) {
                return false;
            }

            [$idTrack] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            if (!$idTrack) {
                return false;
            } else {
                return $idTrack;
            }
        }

        return 0;
    }

    /**
     * @return int|false if exists or false
     **/
    public static function getTrack($idReference, $idResource, $idUser)
    {
        self::fixDuplicatesAndSyncCommonTrack($idReference, $idResource, $idUser);
        $query = "SELECT id_track FROM %lms_polltrack WHERE id_reference='" . (int)$idReference . "' AND id_poll='" . (int)$idResource . "' AND id_user='" . (int)$idUser . "'";
        $rs = sql_query($query)
        or errorCommunication('Learning_Poll.existTrack');

        if (sql_num_rows($rs) > 0) {
            [$idTrack] = sql_fetch_row($rs);

            return $idTrack;
        } else {
            return false;
        }
    }

    public static function fixDuplicatesAndSyncCommonTrack($idReference, $idResource, $idUser)
    {
        $query = "SELECT id_track FROM %lms_polltrack WHERE id_reference='" . (int)$idReference . "' AND id_poll='" . (int)$idResource . "' AND id_user='" . (int)$idUser . "' order by id_track";
        $rs = sql_query($query);
        $idTrack = false;
        foreach ($rs as $row) {
            if ($idTrack) {
                $query = 'DELETE from learning_polltrack WHERE id_track="' . (int)$row['id_track'] . '"';
                sql_query($query);
            } else {
                $idTrack = $row['id_track'];
            }
        }
        if ($idTrack) {
            CoursestatsLms::fixUserTrackInfo($idReference, $idUser, $idResource, $idTrack, 'poll');
        }
    }


    public function getIdTrack($idReference, $idUser, $idResource, $createOnFail = false)
    {
        $rsTrack = static::getTrack($idReference, $idResource, $idUser);
        if ($rsTrack !== false) {
            return [true, $rsTrack];
        } elseif ($createOnFail) {
            $rsTrack = $this->createNewTrack($idUser, $idResource, $idReference);

            return [false, $rsTrack];
        }

        return false;
    }

    /**
     * @return int|false if create row else false
     **/
    public function setTrack($idReference, $idResource, $idUser)
    {
        $rsTrack = static::getTrack($idReference, $idResource, $idUser);
        if ($rsTrack !== false) {
            $query = "INSERT INTO %lms_polltrack SET id_poll = '" . (int)$idResource . "', id_reference = '" . (int)$idReference . "', id_user = '" . (int)$idUser . "', data_attempt = '" . date('Y-m-d H:i:s') . "'";
            if (!sql_query($query)) {
                return false;
            }

            [$idTrack] = sql_fetch_row(sql_query('SELECT LAST_INSERT_ID()'));
            if ($idTrack) {
                return $idTrack;
            } else {
                return false;
            }
        }
        return $rsTrack;
    }

    /**
     * function updateTrack( $idTrack, $new_info ).
     *
     * create a new row in the _testtrack table for tracking purpose
     *
     * @param int $idTrack the track of the object
     * @param array $new_info an array with the new information
     *
     * @return bool true if success false otherwise
     **/
    public function updateTrack($idTrack, $new_info)
    {
        $first = true;
        if (!is_array($new_info)) {
            return true;
        }
        $query = 'UPDATE %lms_polltrack SET ';
        foreach ($new_info as $field_name => $field_value) {
            $query .= ($first ? '' : ', ') . $field_name . " = '" . $field_value . "'";
            if ($first) {
                $first = false;
            }
        }
        $query .= " WHERE id_track = '" . (int)$idTrack . "'";
        if (!sql_query($query)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * print in standard output.
     **/
    public function loadObjectReport($mvc = false)
    {
        require_once _lms_ . '/modules/poll/do.poll.php';
        $output = writePollReport($this->idResource, $this->idParams, $this->back_url, $mvc);
        if ($mvc) {
            return $output;
        }
    }
}
