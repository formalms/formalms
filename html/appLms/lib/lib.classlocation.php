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
 * @version  $Id:  $
 */
// ----------------------------------------------------------------------------

class ClassLocationManager
{
    public $prefix = null;
    public $dbconn = null;

    public $status_info = [];

    public function __construct($prefix = 'learning', $dbconn = null)
    {
        $this->prefix = $prefix;
        $this->dbconn = $dbconn;
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

    public function _getMainTable()
    {
        return $this->prefix . '_class_location';
    }

    public function getClassLocationTable()
    {
        // lol:
        return $this->_getMainTable();
    }

    public function GetLastOrd($table)
    {
        //require_once(_base_.'/lib/lib.utils.php');
        return utilGetLastOrd($table, 'ord');
    }

    public function moveItem($direction, $id_val)
    {
        //require_once(_base_.'/lib/lib.utils.php');

        $table = $this->_getMainTable();

        utilMoveItem($direction, $table, 'location_id', $id_val, 'ord');
    }

    public function getClassLocationList($ini = false, $vis_item = false)
    {
        $data_info = [];
        $data_info['data_arr'] = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getMainTable() . ' ';
        $qtxt .= 'ORDER BY location ';
        $q = $this->_executeQuery($qtxt);

        if ($q) {
            $data_info['data_tot'] = sql_num_rows($q);
        } else {
            $data_info['data_tot'] = 0;
        }

        if (($ini !== false) && ($vis_item !== false)) {
            $qtxt .= 'LIMIT ' . $ini . ',' . $vis_item;
            $q = $this->_executeQuery($qtxt);
        }

        if (($q) && (sql_num_rows($q) > 0)) {
            $i = 0;
            while ($row = sql_fetch_array($q)) {
                $id = $row['location_id'];
                $data_info['data_arr'][$i] = $row;
                $this->status_info[$id] = $row;

                ++$i;
            }
        }

        return $data_info;
    }

    public function getClassLocationArray($include_any = false)
    {
        $res = [];

        $class_locations = $this->getClassLocationList(false, false);
        $locations_list = $class_locations['data_arr'];

        if ($include_any) {
            $res[0] = Lang::t('_ALL', 'classroom', 'lms');
        }

        foreach ($locations_list as $location) {
            $id = $location['location_id'];
            $res[$id] = $location['location'];
        }

        return $res;
    }

    public function loadClassLocationInfo($id)
    {
        $res = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getMainTable() . ' ';
        $qtxt .= "WHERE location_id='" . (int) $id . "'";
        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            $res = sql_fetch_array($q);
        }

        return $res;
    }

    public function getClassLocationInfo($id)
    {
        if (!isset($this->status_info[$id])) {
            $this->status_info[$id] = $this->loadClassLocationInfo($id);
        }

        return $this->status_info[$id];
    }

    public function saveData($data)
    {
        $id = (int) $data['id'];
        $location = $data['location'];

        if ($id == 0) {
            if (empty($location)) {
                $lang = &FormaLanguage::createInstance('classlocation', 'lms');
                $location = $lang->def('_UNAMED');
            }

            $field_list = 'location';
            $field_val = "'" . $location . "'";

            $qtxt = 'INSERT INTO ' . $this->_getMainTable() . ' (' . $field_list . ') VALUES(' . $field_val . ')';
            $id = $this->_executeInsert($qtxt);
        } elseif ($id > 0) {
            $qtxt = 'UPDATE ' . $this->_getMainTable() . " SET location='" . $location . "' WHERE location_id='" . $id . "'";
            $q = $this->_executeQuery($qtxt);
        }

        return $id;
    }

    public function deleteClassLocation($id)
    {
        $qtxt = 'DELETE FROM ' . $this->_getMainTable() . " WHERE location_id='" . $id . "' LIMIT 1";
        $q = $this->_executeQuery($qtxt);

        return $q;
    }
}
