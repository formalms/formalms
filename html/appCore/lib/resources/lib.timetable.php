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
 * @version  $Id: $
 */
// ----------------------------------------------------------------------------

class Timetable
{
    public $prefix = null;
    public $dbconn = null;

    public function __construct($prefix = false, $dbconn = null)
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

        /* $ok=($rs ? "[ ]" : "[!]");
        file_put_contents("/path/to/log.txt", $ok."TT:".$query."\n", FILE_APPEND); */

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

    public function _getMainTable()
    {
        return $this->prefix . '_resource_timetable';
    }

    public function _getResourceTable()
    {
        return $this->prefix . '_resource';
    }

    public function &getResourceObject($resource_code)
    {
        $res = false;

        $qtxt = 'SELECT * FROM ' . $this->_getResourceTable() . ' ';
        $qtxt .= "WHERE resource_code='" . $resource_code . "'";

        $q = $this->_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            $row = sql_fetch_assoc($q);

            $class_name = 'Resource' . ucfirst(strtolower($resource_code));
            $resource_file = $GLOBALS['where_' . $row['platform']] . '/lib/resources/lib.resource_';
            $resource_file .= strtolower($resource_code) . '.php';

            if (file_exists($resource_file)) {
                require_once $resource_file;
                $res = new $class_name();
            }
        }

        return $res;
    }

    /**
     * @param string   $resource_code
     * @param int      $resource_id
     * @param string   $consumer_code
     * @param int      $consumer_id
     * @param datetime $start_date    if FALSE will be considered infinite
     * @param datetime $end_date      if FALSE will be considered infinite
     *
     * @return mixed FALSE if fails, item id if succeed
     */
    public function addEvent($resource_code, $resource_id, $consumer_code, $consumer_id, $start_date = false, $end_date = false)
    {
        $res = false;

        $resource_code = substr($resource_code, 0, 60);
        $resource_id = (int) $resource_id;
        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $r_obj = &$this->getResourceObject($resource_code);
        $available = $r_obj->checkAvailability($resource_id, $start_date, $end_date); //var_dump($available);

        if ($available) {
            $field_list = 'resource, resource_id, consumer, consumer_id';
            $field_list .= ($start_date !== false ? ', start_date' : '');
            $field_list .= ($end_date !== false ? ', end_date' : '');
            $field_val = "'" . $resource_code . "', '" . $resource_id . "', '" . $consumer_code . "', '" . $consumer_id . "'";
            $field_val .= ($start_date !== false ? ", '" . $start_date . "'" : '');
            $field_val .= ($end_date !== false ? ", '" . $end_date . "'" : '');

            $qtxt = 'INSERT INTO ' . $this->_getMainTable() . ' (' . $field_list . ') VALUES(' . $field_val . ')';

            $res = $this->_insQuery($qtxt);
        }

        return $res;
    }

    /**
     * @param mixed id false if you don't know the id, else id number is used
     *                  to perform the "id > 0" / "not false" check only here
     *                  instead of each time
     *
     * @return mixed id of found item or FALSE if it fails
     */
    public function getEventId($id, $resource_code, $resource_id, $consumer_code, $consumer_id, $start_date = false, $end_date = false)
    {
        $res = false;

        if (($id !== false) && ($id > 0)) {
            $res = $id;
        } else { // Find id by parameters
            $where = "WHERE resource='" . $resource_code . "' AND resource_id='" . $resource_id . "' ";
            $where .= "AND consumer='" . $consumer_code . "' AND consumer_id='" . $consumer_id . "' ";
            $where .= 'AND start_date' . ($start_date !== false ? "='" . $start_date . "' " : ' IS NULL ');
            $where .= 'AND end_date' . ($end_date !== false ? "='" . $end_date . "' " : ' IS NULL ');

            $sel_qtxt = 'SELECT id FROM ' . $this->_getMainTable() . ' ' . $where; //echo $sel_qtxt;

            $q = $this->_query($sel_qtxt);

            if (($q) && (sql_num_rows($q) > 0)) {
                $row = sql_fetch_assoc($q);
                $id = (int) $row['id'];
            } else {
                return $res;
            }

            $res = $id;
        }

        return $res;
    }

    /**
     * Used to update the start and/or end date of the event.
     *
     * @return mixed id of updated item or FALSE if it fails
     */
    public function updateEvent($id, $start_date, $end_date, $old_start_date = false, $old_end_date = false, $resource_code = false, $resource_id = false, $consumer_code = false, $consumer_id = false)
    {
        $res = false;

        $resource_code = substr($resource_code, 0, 60);
        $resource_id = (int) $resource_id;
        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;
        // We call the checkNullDate only for the old dates
        $old_start_date = $this->checkNullDate($old_start_date);
        $old_end_date = $this->checkNullDate($old_end_date);

        if (($start_date === false) && ($end_date === false)) {
            return $res;
        } // Nothing to update

        $qtxt = 'UPDATE ' . $this->_getMainTable() . ' SET ';
        $first = true;
        if ($start_date !== false) {
            $qtxt .= (!$first ? ', ' : '');
            $qtxt .= "start_date='" . $start_date . "' ";
            $first = false;
        }
        if ($end_date !== false) {
            $qtxt .= (!$first ? ', ' : '');
            $qtxt .= "end_date='" . $end_date . "' ";
            $first = false;
        }

        $id = $this->getEventId($id, $resource_code, $resource_id, $consumer_code, $consumer_id, $old_start_date, $old_end_date);

        $qtxt .= "WHERE id='" . $id . "'";

        if (($id !== false) && ($id > 0)) {
            $q = $this->_query($qtxt);

            if ($q) {
                $res = $id;
            }
        }

        return $res;
    }

    public function deleteEvent($id, $resource_code = false, $resource_id = false, $consumer_code = false, $consumer_id = false, $start_date = false, $end_date = false)
    {
        $resource_code = substr($resource_code, 0, 60);
        $resource_id = (int) $resource_id;
        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $id = $this->getEventId($id, $resource_code, $resource_id, $consumer_code, $consumer_id, $start_date, $end_date);

        $qtxt = 'DELETE FROM ' . $this->_getMainTable() . " WHERE id='" . (int) $id . "' LIMIT 1";
        $q = $this->_query($qtxt);

        return $q;
    }

    /**
     * @param string $resource_code
     * @param int    $consumer_code
     * @param string $consumer_id
     * @param mixed  $exclude_resource_id if not FALSE will use an array to specify wich for wich resource id
     *                                    the consumer entry have not to be deleted
     *
     * @return query result
     */
    public function deleteAllConsumerEventsForResource($resource_code, $consumer_code, $consumer_id, $exclude_resource_id = false)
    {
        $resource_code = substr($resource_code, 0, 60);
        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;

        $qtxt = 'DELETE FROM ' . $this->_getMainTable() . " WHERE resource='" . $resource_code . "' AND ";
        $qtxt .= "consumer='" . $consumer_code . "' AND consumer_id='" . $consumer_id . "'";

        if (($exclude_resource_id !== false) && (is_array($exclude_resource_id)) && (count($exclude_resource_id) > 0)) {
            $qtxt .= ' AND resource_id NOT IN (' . implode(',', $exclude_resource_id) . ')';
        }

        $q = $this->_query($qtxt);

        return $q;
    }

    /**
     * Try to update an event; if not exsists will create it.
     */
    public function saveEvent($id, $start_date, $end_date, $old_start_date = false, $old_end_date = false, $resource_code = false, $resource_id = false, $consumer_code = false, $consumer_id = false)
    {
        $res = $this->updateEvent($id, $start_date, $end_date, $old_start_date, $old_end_date, $resource_code, $resource_id, $consumer_code, $consumer_id);

        if ($res === false) {
            $res = $this->addEvent($resource_code, $resource_id, $consumer_code, $consumer_id, $start_date, $end_date);
        }

        return $res;
    }

    public function updateEventDateByResource($resource_code, $resource_id, $start_date, $end_date, $consumer_code = false)
    {
        $resource_code = substr($resource_code, 0, 60);
        $resource_id = (int) $resource_id;
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $new_start_date = ($start_date !== false ? "'" . $start_date . "'" : 'NULL');
        $new_end_date = ($end_date !== false ? "'" . $end_date . "'" : 'NULL');

        $qtxt = 'UPDATE ' . $this->_getMainTable() . ' SET start_date=' . $new_start_date . ', end_date=' . $new_end_date . ' ';
        $qtxt .= "WHERE resource='" . $resource_code . "' AND resource_id='" . $resource_id . "'";
        $qtxt .= ($consumer_code !== false ? " AND consumer='" . $consumer_code . "'" : '');

        $q = $this->_query($qtxt);

        return $q;
    }

    public function updateEventDateByConsumer($consumer_code, $consumer_id, $start_date, $end_date, $resource_code = false)
    {
        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $new_start_date = ($start_date !== false ? "'" . $start_date . "'" : 'NULL');
        $new_end_date = ($end_date !== false ? "'" . $end_date . "'" : 'NULL');

        $qtxt = 'UPDATE ' . $this->_getMainTable() . ' SET start_date=' . $new_start_date . ', end_date=' . $new_end_date . ' ';
        $qtxt .= "WHERE consumer='" . $consumer_code . "' AND consumer_id='" . $consumer_id . "'";
        $qtxt .= ($resource_code !== false ? " AND resource='" . $resource_code . "'" : '');

        $q = $this->_query($qtxt);

        return $q;
    }

    /**
     * This funciont will return wich resources are used by a given consumer and in wich period.
     *
     * @return array like: array[]=>array[id, consumer, consumer_id, resource, resource_id, start_date, end_date]
     */
    public function getConsumerResources($consumer_code, $consumer_id, $start_date = false, $end_date = false, $resouce_code = false)
    {
        $res = [];

        $consumer_code = substr($consumer_code, 0, 60);
        $consumer_id = (int) $consumer_id;
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $qtxt = 'SELECT * FROM ' . $this->_getMainTable() . ' WHERE ';
        $qtxt .= ($resouce_code !== false ? "resource='" . $resouce_code . "' AND " : '');
        $qtxt .= "consumer='" . $consumer_code . "' AND consumer_id='" . $consumer_id . "'";

        $where_start_date = " AND start_date >= '" . $start_date . "' OR start_date IS NULL";
        $qtxt .= ($start_date !== false ? $where_start_date : '');

        $where_end_date = " AND end_date <= '" . $end_date . "' OR end_date IS NULL";
        $qtxt .= ($end_date !== false ? $where_end_date : '');

        $q = $this->_query($qtxt);

        if (($q) && (sql_num_rows($q) > 0)) {
            while ($row = sql_fetch_assoc($q)) {
                $res[] = $row;
                // TODO: cache result in global variable
            }
        }

        return $res;
    }

    public function getResourceEntries($resource_code, $resource_id = false, $start_date = false, $end_date = false, $consumer_filter = false)
    {
        $resource_code = substr($resource_code, 0, 60);
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $r_obj = &$this->getResourceObject($resource_code);
        $res = $r_obj->getResourceEntries($resource_id, $start_date, $end_date, $consumer_filter);

        return $res;
    }

    /**
     * @param bool allow_partial if true will check if a resource is busy only for some time during the
     *                                   specified period, instead of if the resource is busy for the whole period
     * @param mixed $exclude_consumer_id if not FALSE will use an array to specify wich consumer id has to be
     *                                   excluded from the check
     */
    public function getResourcesInUse($resource_code, $start_date = false, $end_date = false, $allow_partial = false, $exclude_consumer_id = false)
    {
        $start_date = $this->checkNullDate($start_date);
        $end_date = $this->checkNullDate($end_date);

        $r_obj = &$this->getResourceObject($resource_code);
        $res = $r_obj->getResourcesInUse($start_date, $end_date, $allow_partial, $exclude_consumer_id);

        return $res;
    }

    public function checkNullDate($date)
    {
        if ($date !== false) {
            if ((empty($date)) || is_null($date)) {
                $date = false;
            }
        }

        return $date;
    }
}
