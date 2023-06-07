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

class ChatBooking
{
    public $prefix = null;
    public $dbconn = null;
    public $platform = '';
    public $module = '';

    public $room_subscriptions = null;
    public $user_subscriptions = null;

    public function __construct($module, $prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_scs']);
        $this->dbconn = $dbconn;
        $this->platform = FormaLms\lib\Get::cur_plat();
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

    public function _getBookingTable()
    {
        return $this->prefix . '_booking';
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function bookRoom($user_idst, $room_id)
    {
        $res = false;

        $qtxt = 'SELECT booking_id FROM ' . $this->_getBookingTable() . ' ';
        $qtxt .= "WHERE room_id='" . (int) $room_id . "' AND user_idst='" . (int) $user_idst . "' LIMIT 0,1";
        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            $row = sql_fetch_assoc($q);
            $res = $row['booking_id'];
        } elseif (($q) && (sql_num_rows($q) == 0)) {
            $qtxt = 'INSERT INTO ' . $this->_getBookingTable() . ' (room_id, platform, module, user_idst) ';
            $qtxt .= "VALUES ('" . (int) $room_id . "', '" . $this->getPlatform() . "', ";
            $qtxt .= "'" . $this->getModule() . "', '" . (int) $user_idst . "')";

            $booking_id = $this->_executeInsert($qtxt);
            $res = $booking_id;
        }

        return $res;
    }

    public function loadRoomSubscriptions($room_id, $where = false)
    {
        $res = [];

        $fields = 'booking_id, platform, module, user_idst, approved';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getBookingTable() . ' ';
        $qtxt .= "WHERE room_id='" . (int) $room_id . "'";

        if (($where !== false) && (!empty($where))) {
            $qtxt .= ' AND ' . $where;
        }

        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $user_idst = $row['user_idst'];
                $res[$user_idst] = $row;
            }
        }

        return $res;
    }

    public function getRoomSubscriptions($room_id, $where = false)
    {
        $rs = $this->room_subscriptions;

        if ((isset($rs[$room_id])) && (is_array($rs[$room_id]))) {
            return $rs[$room_id];
        } else {
            $this->room_subscriptions[$room_id] = $this->loadRoomSubscriptions($room_id, $where);

            return $this->room_subscriptions[$room_id];
        }
    }

    public function setApproved($user_idst, $room_id, $val = true)
    {
        $qtxt = 'UPDATE ' . $this->_getBookingTable() . " SET approved='" . (int) $val . "' ";
        $qtxt .= "WHERE room_id='" . (int) $room_id . "' AND user_idst='" . (int) $user_idst . "' LIMIT 1";
        $q = $this->_executeQuery($qtxt);

        return $q;
    }

    public function deleteByRoom($room_to_del)
    {
        if ((!is_array($room_to_del)) || (count($room_to_del) < 1)) {
            return false;
        }

        $qtxt = 'DELETE FROM ' . $this->_getBookingTable() . ' ';
        $qtxt .= "WHERE room_id IN '" . implode(',', $room_to_del) . "' ";
        $qtxt .= "AND module='" . $this->getModule() . "'";
        $q = $this->_executeQuery($qtxt);

        return $q;
    }

    public function deleteBooking($user_idst, $room_id)
    {
        $qtxt = 'DELETE FROM ' . $this->_getBookingTable() . ' ';
        $qtxt .= "WHERE room_id='" . (int) $room_id . "' AND user_idst='" . (int) $user_idst . "' LIMIT 1";
        $q = $this->_executeQuery($qtxt);

        return $q;
    }
}

class RoomBooking
{
    protected $dbconn;

    public function __construct()
    {
        $this->dbconn = \FormaLms\db\DbConn::getInstance();
    }

    public function __destruct()
    {
    }

    protected function _getBookingTable()
    {
        return $GLOBALS['prefix_scs'] . '_booking';
    }

    protected function _getRoomTable()
    {
        return $GLOBALS['prefix_scs'] . '_room';
    }

    public function roomIsFull($room_id)
    {
        $query = 'SELECT maxparticipants'
                    . ' FROM ' . $this->_getRoomTable() . ''
                    . " WHERE id = '" . $room_id . "'";

        list($max_participants) = $this->dbconn->fetch_row($this->dbconn->query($query));

        $query = 'SELECT COUNT(*)'
                    . ' FROM ' . $this->_getBookingTable() . ''
                    . " WHERE idRoom = '" . $room_id . "'"
                    . " AND valid = '1'";

        list($participants) = $this->dbconn->fetch_row($this->dbconn->query($query));

        if ($max_participants > $participants) {
            return false;
        }

        return true;
    }

    public function userIsBooked($user_id, $room_id)
    {
        $query = 'SELECT COUNT(*)'
                    . 'FROM ' . $this->_getBookingTable() . ''
                    . " WHERE idUser = '" . $user_id . "'"
                    . " AND idRoom = '" . $room_id . "'";

        list($is_booked) = $this->dbconn->fetch_row($this->dbconn->query($query));

        if ($is_booked) {
            return true;
        }

        return false;
    }

    public function userIsValid($user_id, $room_id)
    {
        $query = 'SELECT valid'
                    . ' FROM ' . $this->_getBookingTable() . ''
                    . " WHERE idUser = '" . $user_id . "'"
                    . " AND idRoom = '" . $room_id . "'";

        list($is_valid) = $this->dbconn->fetch_row($this->dbconn->query($query));

        if ($is_valid) {
            return true;
        }

        return false;
    }

    public function bookRoom($user_id, $room_id)
    {
        $query = 'INSERT INTO ' . $this->_getBookingTable() . ''
                    . ' (idUser, idRoom, date)'
                    . " VALUES ('" . $user_id . "', '" . $room_id . "', '" . date('Y-m-d H:i:s') . "')";

        return $this->dbconn->query($query);
    }

    public function getRoomSubscriptions($room_id)
    {
        $query = 'SELECT idUser, date, valid'
                    . ' FROM ' . $this->_getBookingTable() . ''
                    . " WHERE idRoom = '" . $room_id . "'";

        $result = $this->dbconn->query($query);

        $res = [];

        while (list($id_user, $date, $valid) = $this->dbconn->fetch_row($result)) {
            $res[$id_user]['idUser'] = $id_user;
            $res[$id_user]['date'] = $date;
            $res[$id_user]['valid'] = ($valid ? true : false);
        }

        return $res;
    }

    public function setApproved($user_id, $room_id, $valid)
    {
        $query = 'UPDATE ' . $this->_getBookingTable() . ''
                    . " SET valid = '" . $valid . "'"
                    . " WHERE idUser = '" . $user_id . "'"
                    . " AND idRoom = '" . $room_id . "'";

        return $this->dbconn->query($query);
    }

    public function deleteBookingByRoom($room_id)
    {
        $query = 'DELETE FROM ' . $this->_getBookingTable() . ''
                    . " WHERE idRoom = '" . $room_id . "'";

        return $this->dbconn->query($query);
    }
}
