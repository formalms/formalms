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

/**
 * @version  $Id:  $
 */
// ----------------------------------------------------------------------------

class CourseSubscribe
{
    public $prefix = null;
    public $dbconn = null;

    // Subscribe info
    public $subscribe_info;

    public function __construct($prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_lms']);
        $this->dbconn = $dbconn;

        $this->subscribe_info = ['course' => [], 'edition' => []];
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

    public function _getCourseTable()
    {
        return $this->prefix . '_course';
    }

    public function _getEditionTable()
    {
        return $this->prefix . '_course_edition';
    }

    public function &getInstance($prefix = false, $dbconn = null)
    {
        if (!isset($GLOBALS['course_subscribe_manager'])) {
            $GLOBALS['course_subscribe_manager'] = new CourseSubscribe($prefix, $dbconn);
        }

        return $GLOBALS['course_subscribe_manager'];
    }

    /**
     * Use this one if you already have loaded the needed information
     * If something is missing the _getInfo() method will reload
     * information from the database.
     */
    public function setSubscribeInfo($data, $course_id, $edition_id = false)
    {
        if (!$this->_isEdition($edition_id)) {
            $key = 'course';
            $item_id = (int) $course_id;
        } else {
            $key = 'edition';
            $item_id = (int) $edition_id;
        }

        $res = $this->getSubscribeInfo($course_id, $edition_id);

        $look_for = ['allow_overbooking', 'can_subscribe', 'sub_start_date', 'sub_end_date',
                        'user_count', 'waiting', 'max_num_subscribe', ];

        foreach ($look_for as $name) {
            if (isset($data[$name])) {
                $this->subscribe_info[$key][$item_id][$name] = $data[$name];
            }
        }
    }

    /**
     * This is not private but you should use the getSubscribeInfo.
     */
    public function loadSubscribeInfo($course_id, $edition_id = false)
    {
        $res = [];
        $main_res = [];
        $user_res = [];

        if (!$this->_isEdition($edition_id)) {
            $id_name = 'idCourse';
            $id_name_user = 'idCourse';
            $id_val = (int) $course_id;
            $table = $this->_getCourseTable();
        } else {
            $id_name = 'idCourseEdition';
            $id_name_user = 'edition_id';
            $id_val = (int) $edition_id;
            $table = $this->_getEditionTable();
        }

        $fields = 'max_num_subscribe, allow_overbooking, can_subscribe, sub_start_date, sub_end_date';
        $qtxt = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE ' . $id_name . "='" . $id_val . "'";

        $q = sql_query($qtxt);
        if (($q) && (sql_num_rows($q) > 0)) {
            $main_res = sql_fetch_assoc($q);
        }

        $fields = "sum(waiting = '1') as waiting, COUNT(*) as user_count";
        $qtxt = 'SELECT ' . $fields . ' FROM %lms_courseuser ';
        $qtxt .= 'WHERE ' . $id_name_user . "='" . $id_val . "' ";
        if (!$this->_isEdition($edition_id)) {
            $qtxt .= "AND edition_id='0' ";
        }
        $qtxt .= 'GROUP BY ' . $id_name_user;

        $q = sql_query($qtxt);
        if (($q) && (sql_num_rows($q) > 0)) {
            $user_res = sql_fetch_assoc($q);
        } else {
            $user_res['waiting'] = 0;
            $user_res['user_count'] = 0;
        }

        $res = $main_res + $user_res;

        return $res;
    }

    public function getSubscribeInfo($course_id, $edition_id = false, $use_cache = true)
    {
        if (!$this->_isEdition($edition_id)) {
            $key = 'course';
            $item_id = (int) $course_id;
        } else {
            $key = 'edition';
            $item_id = (int) $edition_id;
        }

        if ((!isset($this->subscribe_info[$key][$item_id])) || (!$use_cache)) { //echo "Load! ";
            $this->subscribe_info[$key][$item_id] = $this->loadSubscribeInfo($course_id, $edition_id);
        }

        return $this->subscribe_info[$key][$item_id];
    }

    public function _isEdition($edition_id)
    {
        if (($edition_id !== false) && ($edition_id > 0)) {
            return true;
        } else {
            return false;
        }
    }

    public function _getInfo($name, $course_id, $edition_id = false)
    {
        if (!$this->_isEdition($edition_id)) {
            $key = 'course';
            $item_id = (int) $course_id;
        } else {
            $key = 'edition';
            $item_id = (int) $edition_id;
        }

        if (!isset($this->subscribe_info[$key][$item_id][$name])) {
            $this->getSubscribeInfo($course_id, $edition_id);
        }

        return $this->subscribe_info[$key][$item_id][$name];
    }

    public function canSubscribe($course_id, $edition_id = false)
    {
        $res = false;

        $today = date('Y-m-d') . ' 00:00:00';

        $can_subscribe = $this->_getInfo('can_subscribe', $course_id, $edition_id);

        if ($can_subscribe == 1) {
            $allow_overbooking = $this->allowOverbooking($course_id, $edition_id);
            $full = $this->isFull($course_id, $edition_id);
            if ((!$full) || ($allow_overbooking)) {
                $res = true;
            }
        } elseif ($can_subscribe == 2) {
            $sub_start_date = $this->_getInfo('sub_start_date', $course_id, $edition_id);
            $sub_end_date = $this->_getInfo('sub_end_date', $course_id, $edition_id);

            if ((strcmp($today, $sub_start_date) >= 0) && (strcmp($today, $sub_end_date) <= 0)) {
                $res = true;
            }
        }

        return $res;
    }

    public function allowOverbooking($course_id, $edition_id = false)
    {
        $res = false;

        $allow_overbooking = $this->_getInfo('allow_overbooking', $course_id, $edition_id);
        $res = ($allow_overbooking == 1 ? true : false);

        return $res;
    }

    public function isFull($course_id, $edition_id = false)
    {
        $res = false;

        $max = $this->_getInfo('max_num_subscribe', $course_id, $edition_id);
        $user_sub = $this->_getInfo('user_count', $course_id, $edition_id);
        $user_waiting = $this->_getInfo('waiting', $course_id, $edition_id);
        $sub = $user_sub + $user_waiting;
        $res = (($max == 0) || ($sub < $max) ? false : true);

        return $res;
    }
}
