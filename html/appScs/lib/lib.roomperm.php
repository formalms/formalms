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

class RoomPermissions
{
    public $prefix = null;
    public $dbconn = null;
    public $room_id = '';
    public $module = '';

    public function RoomPermissions($room_id, $module, $prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_scs']);
        $this->dbconn = $dbconn;
        $this->platform = FormaLms\lib\Get::cur_plat();
        $this->room_id = (int) $room_id;
        $this->module = $module;
    }

    public function _executeQuery($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _executeInsert($query)
    {
        if ($this->dbconn === null) {
            if (!sql_query($query)) {
                return false;
            }
        } else {
            if (!sql_query($query, $this->dbconn)) {
                return false;
            }
        }
        if ($this->dbconn === null) {
            return sql_insert_id();
        } else {
            return sql_insert_id($this->dbconn);
        }
    }

    public function _getPermTable()
    {
        return $this->prefix . '_chatperm';
    }

    public function getRoomId()
    {
        return (int) $this->room_id;
    }

    public function setRoomId($room_id)
    {
        $this->room_id = (int) $room_id;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function addPerm($perm, $idst_arr)
    {
        $res = true;

        if (empty($perm)) {
            return false;
        }

        foreach ($idst_arr as $user_idst) {
            $qtxt = 'INSERT INTO ' . $this->_getPermTable() . ' (room_id, module, user_idst, perm) ';
            $qtxt .= "VALUES ('" . $this->getRoomId() . "', '" . $this->getModule() . "', '" . $user_idst . "', '" . $perm . "')";

            $q = $this->_executeQuery($qtxt);
            if (!$q) {
                $res = false;
            }
        }

        return $res;
    }

    public function removePerm($perm, $idst_arr)
    {
        $res = true;

        if (empty($perm)) {
            return false;
        }

        if ((is_array($idst_arr)) && (count($idst_arr) > 0)) {
            $qtxt = 'DELETE FROM ' . $this->_getPermTable() . " WHERE room_id='" . $this->getRoomId() . "' AND ";
            $qtxt .= "module='" . $this->getModule() . "' AND perm='" . $perm . "' AND ";
            $qtxt .= 'user_idst IN (' . implode(',', $idst_arr) . ')';

            $q = $this->_executeQuery($qtxt);
            if (!$q) {
                $res = false;
            }
        }

        return $res;
    }

    public function getAllPerm()
    {
        $res = [];

        $fields = 'user_idst, perm';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getPermTable() . ' WHERE ';
        $qtxt .= "room_id='" . $this->getRoomId() . "' AND module='" . $this->getModule() . "'";

        $q = $this->_executeQuery($qtxt);

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
