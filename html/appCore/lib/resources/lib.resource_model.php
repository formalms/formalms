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
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

class ResourceModel
{
    public $prefix = null;
    public $dbconn = null;
    public $timetable_table = false;

    public $resource_code = false;
    public $allowed_simultaneously = 1;

    public function ResourceModel($prefix = false, $dbconn = null)
    {
        $this->prefix = ($prefix !== false ? $prefix : $GLOBALS['prefix_fw']);
        $this->dbconn = $dbconn;
    }

    public function _query($query)
    {
        if ($this->dbconn === null) {
            $rs = sql_query($query);
        } else {
            $rs = sql_query($query, $this->dbconn);
        }

        return $rs;
    }

    public function _insQuery($query)
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

    public function setTimeTableTable($table)
    {
        $this->timetable_table = $table;
    }

    public function getTimeTableTable()
    {
        if ($this->timetable_table === false) {
            return $this->prefix . '_resource_timetable';
        } else {
            return $this->timetable_table;
        }
    }

    public function setResourceCode($code)
    {
        $this->resource_code = $code;
    }

    public function getResourceCode()
    {
        if ($this->resource_code !== false) {
            return $this->resource_code;
        } else {
            return '0';
        }
    }

    public function setAllowedSimultaneously($max)
    {
        $this->allowed_simultaneously = $max;
    }

    public function getAllowedSimultaneously()
    {
        return $this->allowed_simultaneously;
    }

    public function getResourceEntries($resource_id = false, $start_date = false, $end_date = false, $consumer_filter = false)
    {
        $res = [];

        $qtxt = 'SELECT * FROM ' . $this->getTimeTableTable() . ' WHERE ';
        $qtxt .= "resource='" . $this->getResourceCode() . "'";
        $qtxt .= ($resource_id !== false ? " AND resource_id='" . $resource_id . "'" : '');

        if (($consumer_filter !== false) && (is_array($consumer_filter)) && (count($consumer_filter) > 0)) {
            $consumer_filter = addSurroundingQuotes($consumer_filter);
            $qtxt .= ' AND consumer IN (' . implode(',', $consumer_filter) . ')';
        }

        $where_start_date = " AND (start_date >= '" . $start_date . "' OR start_date IS NULL)";
        $qtxt .= ($start_date !== false ? $where_start_date : '');

        $where_end_date = " AND (end_date <= '" . $end_date . "' OR end_date IS NULL)";
        $qtxt .= ($end_date !== false ? $where_end_date : '');

        $q = $this->_query($qtxt); //echo $qtxt;

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $res[] = $row;
                // TODO: cache result in global variable
            }
        }

        return $res;
    }

    public function getResourcesInUse($start_date = false, $end_date = false, $allow_partial = false, $exclude_consumer_id = false)
    {
        $res = [];
        $first = true;

        $qtxt = 'SELECT * FROM ' . $this->getTimeTableTable() . ' WHERE ';
        $qtxt .= "resource='" . $this->getResourceCode() . "' ";

        if (($start_date !== false) && ($end_date !== false)) {
            $qtxt .= ' AND (';
        }

        if ($start_date !== false) {
            if ((!$first) && (!$allow_partial)) {
                $qtxt .= ' AND ';
            } elseif ((!$first) && ($allow_partial)) {
                $qtxt .= ' OR ';
            }
            $qtxt .= "((start_date >= '" . $start_date . "'";
            if ($allow_partial) {
                $qtxt .= " AND (start_date <= '" . $end_date . "')";
            }
            $qtxt .= ') OR start_date IS NULL)';
            $first = false;
        }

        if ($end_date !== false) {
            if ((!$first) && (!$allow_partial)) {
                $qtxt .= ' AND ';
            } elseif ((!$first) && ($allow_partial)) {
                $qtxt .= ' OR ';
            }
            $qtxt .= "((end_date <= '" . $end_date . "'";
            if ($allow_partial) {
                $qtxt .= " AND (end_date >= '" . $start_date . "')";
            }
            $qtxt .= ') OR end_date IS NULL)';
            $first = false;
        }

        if (($start_date !== false) && ($end_date !== false)) {
            $qtxt .= ')';
        }

        if (($exclude_consumer_id !== false) && (is_array($exclude_consumer_id)) && (count($exclude_consumer_id) > 0)) {
            $qtxt .= ' AND consumer_id NOT IN (' . implode(',', $exclude_consumer_id) . ')';
        }

        $q = $this->_query($qtxt); //echo $qtxt;

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $resource_id = $row['resource_id'];
                $res[$resource_id] = $resource_id;
            }
        }

        return $res;
    }

    public function checkAvailability($resource_id, $start_date = false, $end_date = false)
    {
        return false;
    }
}
