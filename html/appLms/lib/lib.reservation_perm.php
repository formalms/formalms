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

class ReservationRoomPermissions
{
    public function ReservationRoomPermissions()
    {
    }

    public function _getReservationPermTable()
    {
        return 'learning_reservation_perm';
    }

    public function addReservationPerm($perm, $event_id, $idst_arr)
    {
        $res = true;

        if (empty($perm)) {
            return false;
        }

        foreach ($idst_arr as $user_idst) {
            $qtxt = 'INSERT INTO ' . $this->_getReservationPermTable() . ' (event_id, user_idst, perm) ';
            $qtxt .= "VALUES ('" . $event_id . "', '" . $user_idst . "', '" . $perm . "')";

            $q = sql_query($qtxt);
            if (!$q) {
                $res = false;
            }
        }

        return $res;
    }

    public function removeReservationPerm($perm, $event_id, $idst_arr)
    {
        $res = true;

        if (empty($perm)) {
            return false;
        }

        if ((is_array($idst_arr)) && (count($idst_arr) > 0)) {
            $qtxt = 'DELETE FROM ' . $this->_getReservationPermTable() . " WHERE event_id='" . $event_id . "' AND ";
            $qtxt .= "perm='" . $perm . "' AND ";
            $qtxt .= 'user_idst IN (' . implode(',', $idst_arr) . ')';

            $q = sql_query($qtxt);
            if (!$q) {
                $res = false;
            }
        }

        return $res;
    }

    public function getAllReservationPerm($event_id)
    {
        $res = [];

        $fields = 'user_idst, perm';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getReservationPermTable() . ' WHERE ';
        $qtxt .= "event_id='" . $event_id . "'";

        $q = sql_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $user_idst = $row['user_idst'];
                $perm = $row['perm'];
                $res[$perm][$user_idst] = $user_idst;
            }
        }

        return $res;
    }
}
