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

class Track_Faq extends Track_Object
{
    public function __construct($idTrack)
    {
        $this->objectType = 'faq';
        parent::__construct($idTrack);
    }

    public function getIdTrack($idReference, $idUser, $idResource, $createOnFail = false)
    {
        $query = 'SELECT idTrack FROM %lms_materials_track'
                . " WHERE idReference='" . (int) $idReference . "'"
                . "   AND idUser='" . (int) $idUser . "'";
        $rs = sql_query($query)
            or errorCommunication('getIdTrack');
        if (sql_num_rows($rs) > 0) {
            list($idTrack) = sql_fetch_row($rs);

            return [true, $idTrack];
        } elseif ($createOnFail) {
            $query = 'INSERT INTO %lms_materials_track'
                    . '( idResource, idReference, idUser ) VALUES ('
                    . "'" . (int) $idResource . "','" . (int) $idReference . "','" . (int) $idUser . "')";
            sql_query($query)
                or errorCommunication('getIdTrack');
            $idTrack = sql_insert_id();

            return [false, $idTrack];
        }

        return false;
    }
}
