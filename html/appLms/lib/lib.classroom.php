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

class ClassroomManager
{
    public $prefix = null;
    public $dbconn = null;

    public function ClassroomManager($prefix = 'learning', $dbconn = null)
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
        return $this->prefix . '_classroom';
    }

    public function getClassroomList($ini = false, $vis_item = false, $where = false)
    {
        require_once _lms_ . '/lib/lib.classlocation.php';
        $clm = new ClassLocationManager();

        $data_info = [];
        $data_info['data_arr'] = [];

        $fields = '*';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $this->_getMainTable() . ' as t1, ';
        $qtxt .= $clm->getClassLocationTable() . ' as t2 ';
        $qtxt .= 'WHERE t1.location_id=t2.location_id ';
        if ($where !== false) {
            $qtxt .= 'AND ' . $where . ' ';
        }
        $qtxt .= 'ORDER BY name ';
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
                $id = $row['idClassroom'];
                $data_info['data_arr'][$i] = $row;
                $this->classroom_info[$id] = $row;

                ++$i;
            }
        }

        return $data_info;
    }

    public function getClassroomNameList($where = false)
    {
        require_once _lms_ . '/lib/lib.classlocation.php';
        $clm = new ClassLocationManager();

        $data_info = [];

        $qtxt = '
		SELECT t1.idClassroom, t1.name, t2.location 
		FROM ' . $this->_getMainTable() . ' as t1 
			JOIN ' . $clm->getClassLocationTable() . ' as t2 
		WHERE t1.location_id = t2.location_id ';
        if ($where !== false) {
            $qtxt .= ' AND ' . $where . ' ';
        }
        $qtxt .= 'ORDER BY t1.name ';
        $q = $this->_executeQuery($qtxt);

        while (list($id, $name, $location) = sql_fetch_row($q)) {
            $data_info[$id] = ['classroom' => $name, 'location' => $location];
        }

        return $data_info;
    }

    public function getClassroomArray($include_any = false)
    {
        $res = [];

        $classrooms = $this->getClassroomList(false, false);
        $rooms_list = $classrooms['data_arr'];

        if ($include_any) {
            $res[0] = Lang::t('_ALL', 'classroom', 'lms');
        }

        foreach ($rooms_list as $room) {
            $id = $room['idClassroom'];
            $res[$id] = $room['name'];
        }

        return $res;
    }

    public function loadClassroomInfo($id)
    {
        $res = [];
        require_once _lms_ . '/lib/lib.classlocation.php';
        $clm = new ClassLocationManager();

        $qtxt = ' SELECT * '
            . ' FROM ' . $this->_getMainTable() . ' as t1, '
            . $clm->getClassLocationTable() . ' as t2 '
            . ' WHERE t1.location_id = t2.location_id '
            . "	AND t1.idClassroom='" . (int) $id . "' ";
        $qtxt .= 'ORDER BY t1.name ';

        $q = $this->_executeQuery($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            $res = sql_fetch_array($q);
        }

        return $res;
    }

    public function getClassroomInfo($id)
    {
        if (!isset($this->classroom_info[$id])) {
            $this->classroom_info[$id] = $this->loadClassroomInfo($id);
        }

        return $this->classroom_info[$id];
    }
}
