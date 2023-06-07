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

/*
 * This is the base library for import/export operations in Forma.
 * You should import this library if you want to develop your own
 * source or destination connector. This file is also imported in
 * modules/ioTask.php
 *
 * @package admin-library
 * @subpackage io-operation
 * @version 	$Id: lib.iotask.php 1003 2007-03-31 13:59:46Z fabio $
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/

/*	index for name in array returned by get_first/next_connection **/
define('CONNMGR_CONN_NAME', 0);
/*	index for description in array returned by get_first/next_connection **/
define('CONNMGR_CONN_DESCRIPTION', 1);
/*	index for type in array returned by get_first/next_connection **/
define('CONNMGR_CONN_TYPE', 2);
/*	index for params in array returned by get_first/next_connection **/
define('CONNMGR_CONN_PARAMS', 3);

/*	index for type in array returned by get_first/next_connection_type **/
define('CONNMGR_CONNTYPE_TYPE', 0);
/*	index for file in array returned by get_first/next_connection_type **/
define('CONNMGR_CONNTYPE_FILE', 1);
/*	index for class in array returned by get_first/next_connection_type **/
define('CONNMGR_CONNTYPE_CLASS', 2);

/* index for name in array returned by get_first/next_task **/
define('CONNMGR_TASK_NAME', 0);
/* index for description in array returned by get_first/next_task **/
define('CONNMGR_TASK_DESCRIPTION', 1);
/* index for source (name) in array returned by get_first/next_task **/
define('CONNMGR_TASK_SOURCE', 2);
/* index for destination (name) in array returned by get_first/next_task **/
define('CONNMGR_TASK_DESTINATION', 3);
/* index for schedule type in array returned by get_first/next_task **/
define('CONNMGR_TASK_SCHEDTYPE', 4);
/* index for schedule in array returned by get_first/next_task **/
define('CONNMGR_TASK_SCHEDULE', 5);
/* index for import type in array returned by get_task_byname **/
define('CONNMGR_TASK_IMPORT_TYPE', 6);
/* index for map in array returned by get_task_byname **/
define('CONNMGR_TASK_MAP', 7);
/* index for last execution in array returned by get_first/next_task/get_task_byname **/
define('CONNMGR_TASK_LAST_EXECUTION', 8);

define('CONNMGR_TASK_SEQUENCE', 9);

/* constant for import type = import only **/
define('TASK_IMPORT_TYPE_INSERTONLY', '1');
/* constant for import type = import remove **/
define('TASK_IMPORT_TYPE_INSERTREMOVE', '2');

/**
 * An object of this class manage connections in docebo.
 * List existing connection, add new connections, modify it,
 * remove and also add new connection drivers.
 *
 * @version 	$Id: lib.iotask.php 1003 2007-03-31 13:59:46Z fabio $
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaConnectionManager
{
    public $rs_connection = false;
    public $rs_connector = false;
    public $rs_task = false;
    public $last_error = '';

    /**
     * return the first avaliable connection.
     *
     * @return array an array with connection property
     **/
    public function get_first_connection()
    {
        $query = 'SELECT name, description, type FROM ' . $GLOBALS['prefix_fw'] . '_connection ';
        $this->rs_connection = sql_query($query);
        if ($this->rs_connection === false) {
            return false;
        }
        if (sql_num_rows($this->rs_connection) == 0) {
            return false;
        }

        return sql_fetch_row($this->rs_connection);
    }

    /**
     * return the next avaliable connection.
     *
     * @return array an array with connection property
     **/
    public function get_next_connection()
    {
        if ($this->rs_connection === false) {
            return false;
        }

        return sql_fetch_row($this->rs_connection);
    }

    /**
     * Return all connections name.
     *
     * @return array array of all connection's name
     **/
    public function get_all_connections_name()
    {
        $query = 'SELECT name FROM ' . $GLOBALS['prefix_fw'] . '_connection';
        $rs_connection = sql_query($query);
        if ($rs_connection === false) {
            return false;
        }
        if (sql_num_rows($rs_connection) == 0) {
            return [];
        }
        $arr_name = [];
        while (list($name) = sql_fetch_row($rs_connection)) {
            $arr_name[] = $name;
        }

        return $arr_name;
    }

    /**
     * Return a connection searched by name.
     *
     * @return array array with connections properties
     */
    public function get_connection_byname($name)
    {
        $query = 'SELECT name, description, type, params FROM ' . $GLOBALS['prefix_fw'] . '_connection'
            . " WHERE name = '" . $name . "'"
            . ' ORDER BY name ';
        $rs_connection = sql_query($query);
        if ($rs_connection === false) {
            return false;
        }
        if (sql_num_rows($rs_connection) == 0) {
            return false;
        }

        return sql_fetch_row($rs_connection);
    }

    public function &create_connection_byname($name)
    {
        $arr = $this->get_connection_byname($name);
        $connection = $this->create_connector_bytype($arr[CONNMGR_CONN_TYPE]);
        $params_arr = Util::unserialize(urldecode($arr[CONNMGR_CONN_PARAMS]));
        $params_arr = $this->stripslashes_deep($params_arr);
        $connection->set_config($params_arr);

        return $connection;
    }

    public function delete_connection_byname($name)
    {
        $lang = &$this->get_lang();
        $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . '_connection'
            . " WHERE name = '" . $name . "'";
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    /**
     * save a new connection or update an old connection.
     *
     * @param string          $old_name
     * @param FormaConnector $connection
     **/
    public function save_connection($old_name, $connection)
    {
        //		$name = \FormaLms\lib\Forma::db()->escape($connection->get_name());
        //		$description = \FormaLms\lib\Forma::db()->escape($connection->get_description());
        $name = $connection->get_name();
        $description = $connection->get_description();
        $type = FormaLms\lib\Get::filter($connection->get_type_name(), DOTY_ALPHANUM);
        $params = $connection->get_config();
        $str_params = urlencode(Util::serialize($params));
        $lang = &$this->get_lang();

        if (strlen(trim($name)) == 0) {
            $this->last_error = $lang->def('_OPERATION_FAILURE');

            return false;
        }

        if ($old_name === '') {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_connection'
                . '(name,description,type,params)'
                . ' VALUES '
                . "('$name','$description','$type','$str_params')";
        } else {
            $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_connection SET'
                . " name = '" . $name . "',"
                . " description = '" . $description . "',"
                . " type = '$type',"
                . " params = '$str_params' "
                . " WHERE name = '" . $old_name . "'";
        }
        //echo $query; die();
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    /**
     * return the first avaliable connection type.
     *
     * @return array an array with connection type property
     **/
    public function get_first_connector()
    {
        $query = 'SELECT type, file, class FROM ' . $GLOBALS['prefix_fw'] . '_connector';
        $this->rs_connector = sql_query($query);
        if ($this->rs_connector === false) {
            return false;
        }
        if (sql_num_rows($this->rs_connector) == 0) {
            return false;
        }

        return sql_fetch_row($this->rs_connector);
    }

    /**
     * return the next avaliable connection type.
     *
     * @return array an array with connection type property
     **/
    public function get_next_connector()
    {
        if ($this->rs_connector === false) {
            return false;
        }

        return sql_fetch_row($this->rs_connector);
    }

    /**
     * return the connector with this file.
     *
     * @return array an array with connection type property
     **/
    public function get_connector_byfile($file)
    {
        $query = 'SELECT type, file, class FROM ' . $GLOBALS['prefix_fw'] . '_connector'
            . " WHERE file = '" . $file . "'";
        $rs_connector = sql_query($query);
        if ($rs_connector === false) {
            return false;
        }
        if (sql_num_rows($rs_connector) == 0) {
            return false;
        }

        return sql_fetch_row($rs_connector);
    }

    /**
     * return the connector with this type.
     *
     * @return array an array with connection type property
     **/
    public function get_connector_bytype($type)
    {
        $query = 'SELECT type, file, class FROM ' . $GLOBALS['prefix_fw'] . '_connector'
            . " WHERE type = '" . $type . "'";
        $rs_connector = sql_query($query);
        if ($rs_connector === false) {
            return false;
        }
        if (sql_num_rows($rs_connector) == 0) {
            return false;
        }

        return sql_fetch_row($rs_connector);
    }

    /**
     * add the connector to available connectors.
     *
     * @param string $file the php file that contains the connector code
     **/
    public function add_connector($file)
    {
        require_once _adm_ . '/lib/connectors/lib.connector.php';
        $directory = $GLOBALS['where_framework'] . '/lib/connectors';
        $scanned_directory = array_diff(scandir($directory), ['..', '.', 'index.htm', 'lib.connector.php']);
        if (!in_array($file, $scanned_directory)) {
            echo "Specified connector doesn't exist";
            exit();
        } else {
            require_once _adm_ . '/lib/connectors/' . $file;
        }
        // create function pointer. I file is connector.xxx.php the
        // factory function should be xxx_factory
        list(, $func_factory) = explode('.', $file, 3);
        $func_factory .= '_factory';
        $connector = $func_factory();

        $old_connector = $this->get_connector_bytype($connector->get_type_name());
        if ($old_connector !== false) {
            $this->last_error = 'connector type already registered';

            return false;
        }

        $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_connector '
            . '( type, file, class ) VALUES ('
            . "'" . $connector->get_type_name() . "', "
            . "'" . $file . "', "
            . "'" . get_class($connector) . "') ";
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    /**
     * Create a new connector of requested type.
     *
     * @param string $type the type of the required connector
     *
     * @return FormaConnector the requeste connector
     **/
    public function create_connector_bytype($type)
    {
        $arr_conn = $this->get_connector_bytype($type);
        if ($arr_conn === false) {
            return false;
        }
        require_once _adm_ . '/lib/connectors/lib.connector.php';
        require_once \FormaLms\lib\Forma::inc($GLOBALS['where_framework'] . '/lib/connectors/' . $arr_conn[CONNMGR_CONNTYPE_FILE]);

        return eval('return new ' . $arr_conn[CONNMGR_CONNTYPE_CLASS] . '(NULL);');
    }

    /**
     * return the first avaliable task.
     *
     * @return array an array with task properties
     **/
    public function get_first_task()
    {
        $query = 'SELECT name, description, conn_source, conn_destination, schedule_type, schedule, import_type, map, last_execution, sequence
		FROM ' . $GLOBALS['prefix_fw'] . '_task
		ORDER BY sequence ';
        $this->rs_task = sql_query($query);
        if ($this->rs_task === false) {
            return false;
        }
        if (sql_num_rows($this->rs_task) == 0) {
            return false;
        }

        $arr_result = sql_fetch_row($this->rs_task);
        if ($arr_result === false) {
            return false;
        }
        $schedule = explode(' ', $arr_result[CONNMGR_TASK_SCHEDULE]);
        $arr_result[CONNMGR_TASK_SCHEDULE] = ['qt' => $schedule[0], 'um' => $schedule[1]];

        return $arr_result;
    }

    /**
     * return the next avaliable task.
     *
     * @return array an array with task properties
     **/
    public function get_next_task()
    {
        if ($this->rs_task === false) {
            return false;
        }
        $arr_result = sql_fetch_row($this->rs_task);
        if ($arr_result === false) {
            return false;
        }
        $schedule = explode(' ', $arr_result[CONNMGR_TASK_SCHEDULE]);
        $arr_result[CONNMGR_TASK_SCHEDULE] = ['qt' => $schedule[0], 'um' => $schedule[1]];

        return $arr_result;
    }

    /**
     * return the task params by name.
     *
     * @param string $task_name the name of the task
     *
     * @return array the task properties of task named $task_name
     */
    public function get_task_byID($task_id)
    {
        $query = 'SELECT name, description, conn_source, conn_destination, schedule_type, schedule, import_type, map, last_execution FROM ' . $GLOBALS['prefix_fw'] . '_task'
            . " WHERE sequence = $task_id";

        $rs_task = sql_query($query);
        if ($rs_task === false) {
            return false;
        }
        if (sql_num_rows($rs_task) == 0) {
            return false;
        }

        $arr_result = sql_fetch_row($rs_task);
        $arr_result[CONNMGR_TASK_MAP] = Util::unserialize(urldecode($arr_result[CONNMGR_TASK_MAP]));
        $schedule = explode(' ', $arr_result[CONNMGR_TASK_SCHEDULE]);
        $arr_result[CONNMGR_TASK_SCHEDULE] = ['qt' => $schedule[0], 'um' => $schedule[1]];

        return $arr_result;
    }

    /**
     * check if task is to do.
     *
     * @param array $params array of task parameters as returned by
     *                      get_first_task/get_next_task/get_task_byname
     *
     * @return bool TRUE if task is to do
     *              FALSE otherwise
     */
    /**
     * check if task is to do.
     *
     * @param array $params array of task parameters as returned by
     *                      get_first_task/get_next_task/get_task_byname
     *
     * @return array|bool TRUE if task is to do
     *                    FALSE otherwise
     */
    public function is_task_todo($params, $returnDate = false)
    {
        // compute next execution time
        if ($params[CONNMGR_TASK_LAST_EXECUTION] !== null) {
            $last_execution = strtotime($params[CONNMGR_TASK_LAST_EXECUTION]);
        } else {
            $last_execution = mktime(0, 0, 0, 1, 1, 1);
        }

        if ($params[CONNMGR_TASK_SCHEDTYPE] === 'at') {
            list($hour, $min) = explode(':', $params[CONNMGR_TASK_SCHEDULE]['qt']);
            $next_run_time = mktime($hour,
                $min,
                0,
                date('n', $last_execution),
                date('j', $last_execution) + 1,
                date('Y', $last_execution));
        } else {
            if ($params[CONNMGR_TASK_LAST_EXECUTION] !== null) {
                if ($params[CONNMGR_TASK_SCHEDULE]['um'] === 'day') {
                    $next_run_time = mktime(0,
                        0,
                        0,
                        date('n', $last_execution),
                        date('j', $last_execution) + $params[CONNMGR_TASK_SCHEDULE]['qt'],
                        date('Y', $last_execution));
                } else { // hour
                    $next_run_time = mktime(date('G', $last_execution) + $params[CONNMGR_TASK_SCHEDULE]['qt'],
                        date('i', $last_execution),
                        0,
                        date('n', $last_execution),
                        date('j', $last_execution),
                        date('Y', $last_execution));
                }
            } else {
                $next_run_time = mktime(0, 0, 0, 0, 0, 0);
            }
        }

        if ($returnDate) {
            return [
                'is_task_todo' => $next_run_time < time(),
                'scheduledDate' => date('Y-m-d G:m:s', $next_run_time),
                'executionDate' => date('Y-m-d G:m:s'),
            ];
        }

        return $next_run_time < time();
    }

    /**
     * delete a task.
     *
     * @return bool TRUE is successfully deleted, FALSE otherwise
     */
    public function delete_task_byname($name)
    {
        $lang = &$this->get_lang();
        $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . '_task'
            . " WHERE name = '" . $name . "'";
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    public function delete_task_byid($id_task)
    {
        $lang = &$this->get_lang();
        $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . '_task'
            . ' WHERE sequence = ' . $id_task;
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    /**
     * save a new task or update an old task.
     *
     * @param string $old_name
     * @param array  $params
     **/
    public function save_task($old_name, &$params)
    {
        $map = $params[CONNMGR_TASK_MAP];
        $schedule = $params[CONNMGR_TASK_SCHEDULE];
        $str_map = urlencode(Util::serialize($map));
        $str_schedule = $schedule['qt'] . ' ' . $schedule['um'];
        $lang = &$this->get_lang();

        if (strlen(trim($params[CONNMGR_TASK_NAME])) == 0) {
            $this->last_error = $lang->def('_OPERATION_FAILURE');

            return false;
        }
        if ($params[CONNMGR_TASK_SCHEDTYPE] == 'at' && !preg_match('/^\d\d:\d\d$/', $schedule['qt'])) {
            $this->last_error = $lang->def('_ERROR_TIME_FORMAT');

            return false;
        }

        if ($old_name === '') {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_task'
                . '(name,description,conn_source,conn_destination,schedule_type,schedule,import_type,map)'
                . ' VALUES '
                . "('" . $params[CONNMGR_TASK_NAME] . "'," .
                "'" . $params[CONNMGR_TASK_DESCRIPTION] . "'," .
                "'" . $params[CONNMGR_TASK_SOURCE] . "'," .
                "'" . $params[CONNMGR_TASK_DESTINATION] . "'," .
                "'" . $params[CONNMGR_TASK_SCHEDTYPE] . "'," .
                "'" . $str_schedule . "'," .
                "'" . $params[CONNMGR_TASK_IMPORT_TYPE] . "'," .
                "'" . $str_map . "'" .
                ')';
            echo $query;
        } else {
            $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_task SET'
                . " name = '" . $params[CONNMGR_TASK_NAME] . "',"
                . " description = '" . $params[CONNMGR_TASK_DESCRIPTION] . "',"
                . " conn_source = '" . $params[CONNMGR_TASK_SOURCE] . "',"
                . " conn_destination = '" . $params[CONNMGR_TASK_DESTINATION] . "', "
                . " schedule_type = '" . $params[CONNMGR_TASK_SCHEDTYPE] . "', "
                . " schedule = '" . $str_schedule . "', "
                . " import_type = '" . $params[CONNMGR_TASK_IMPORT_TYPE] . "', "
                . " map = '" . $str_map . "' "
                . " WHERE name = '" . $old_name . "'";
        }
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    public function set_execution_time($idSeq)
    {
        $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_task SET'
            . ' last_execution = NOW()'
            . ' WHERE sequence = ' . $idSeq;
        if (sql_query($query)) {
            return true;
        } else {
            $this->last_error = sql_error();

            return false;
        }
    }

    /**
     * return the last generated error.
     *
     * @return string the last error
     **/
    public function get_last_error()
    {
        return $this->last_error;
    }

    public function &get_lang()
    {
        if (!isset($this->lang)) {
            //require_once(_i18n_.'/lib.lang.php');
            $this->lang = &FormaLanguage::createInstance('iotask', 'framework');
        }

        return $this->lang;
    }

    /**
     * return a string or an array with slash striped from stirngs.
     * Recurse into nested array.
     *
     * @return the array stripped
     **/
    private function stripslashes_deep($value)
    {
        if (is_string($value)) {
            $value = stripslashes($value);
        } elseif (is_array($value)) {
            foreach ($value as $k => $v) {
                $value[$k] = $this->stripslashes_deep($v);
            }
        } else {
            // NOOP
        }

        return $value;
    }
}

/**
 * class to manage import.
 *
 * @version 	1.1
 *
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class FormaImport
{
    public $source = null;
    public $destination = null;
    public $import_map = null;

    public function execute_task($taskID)
    {
        $connMgr = new FormaConnectionManager();
        $params = $connMgr->get_task_byID($taskID);
        $source = &$connMgr->create_connection_byname($params[CONNMGR_TASK_SOURCE]);
        $destination = &$connMgr->create_connection_byname($params[CONNMGR_TASK_DESTINATION]);
        $lang = &FormaLanguage::createInstance('iotask', 'framework');

        $result = $source->connect();
        if ($result === false) {
            return $source->get_error();
        } elseif ($result === DOCEBO_IMPORT_NOTHINGTOPROCESS) {
            $connMgr->set_execution_time($name);

            return $lang->def('_IMPORT_NOTHINGTOPROCESS');
        }
        if ($destination->connect() === false) {
            return false;
        }
        $this->set_source($source);
        $this->set_destination($destination);
        $this->set_map($params[CONNMGR_TASK_MAP]);
        $result = $this->doImport($params[CONNMGR_TASK_IMPORT_TYPE]);
        $connMgr->set_execution_time($taskID);

        return $result;
    }

    public function set_source(&$source)
    {
        $this->source = &$source;
    }

    public function set_destination(&$destination)
    {
        $this->destination = &$destination;
    }

    /**
     * This method create an HTML UI for create the map of fields from
     * source to destination.
     **/
    public function getUIMap()
    {
        require_once _base_ . '/lib/lib.table.php';
        require_once _base_ . '/lib/lib.form.php';
        $lang = &FormaLanguage::createInstance('organization_chart', 'framework');
        $form = new Form();
        $table = new Table(FormaLms\lib\Get::sett('visuItem'), $lang->def('_IMPORT_MAP'), $lang->def('_IMPORT_MAP'));

        $src_cols = $this->source->get_cols_descripor();
        $dst_cols = $this->destination->get_cols_descripor();

        $combo_elements = [];
        foreach ($dst_cols as $col) {
            if (isset($col[DOCEBOIMPORT_COLID])) {
                $combo_elements[$col[DOCEBOIMPORT_COLID]] = $col[DOCEBOIMPORT_COLNAME];
            } else {
                $combo_elements[$col[DOCEBOIMPORT_COLNAME]] = $col[DOCEBOIMPORT_COLNAME];
            }
        }

        $combo_elements[DOCEBOIMPORT_IGNORE] = $lang->def('_IMPORT_IGNORE');

        $table_dst_labels = [];
        $table_src_labels = [];
        $table_src_labels_type = [];
        $count = 0;
        foreach ($src_cols as $col) {
            $pk = '0';
            $map = '';
            if (isset($this->import_map[$count])) {
                $pk = isset($this->import_map[$count]['pk']) ? $this->import_map[$count]['pk'] : '0';
                $map = isset($this->import_map[$count]['map']) ? $this->import_map[$count]['map'] : '';
            }
            $table_src_labels[] = $col[DOCEBOIMPORT_COLNAME] .
                $form->getInputCheckbox('import_map_' . $count . '_pk',
                    'import_map[' . $count . '][pk]',
                    '1',
                    ($pk == '1'),
                    '');
            $table_src_labels_type[] = '';
            $table_dst_labels[] = $form->getInputDropdown('dropdown_nowh',
                'import_map_' . $count . '_map',
                'import_map[' . $count . '][map]',
                $combo_elements,
                $map,
                '');
            ++$count;
        }

        $table->setColsStyle($table_src_labels_type);
        $table->addHead($table_dst_labels);
        $table->addHead($table_src_labels);
        $count = 0;
        $row = $this->source->get_first_row();

        while ($row !== false && $count < 10) {
            $table->addBody($row);
            $row = $this->source->get_next_row();
            ++$count;
        }

        return $table->getTable();
    }

    public function set_map($map)
    {
        $this->import_map = $map;
    }

    public function parse_map()
    {
        if (isset($_POST['import_map'])) {
            $this->import_map = $_POST['import_map'];
        }

        return $this->import_map;
    }

    /**
     * Do the import operation. This function reads all row from source and puts
     * its on destination.
     *
     * @return array with input_row_index => error only for rows with error
     *               in index 0 there are the total processed rows
     **/
    public function doImport($import_type)
    {
        $out = []; 	// error list
        $arr_pk = [];  // list of imported primary keys

        $israw_import = $this->source->is_raw_producer();

        if ($import_type == TASK_IMPORT_TYPE_INSERTREMOVE) {
            $this->destination->enable_cache_inserted(true);
        }

        if (!$israw_import) {
            $dst_cols = $this->destination->get_cols_descripor();
        }
        echo '<pre>';
        $row = $this->source->get_first_row();
        while ($row !== false) {
            if ($israw_import) {
                if (!$this->destination->add_row($row, null)) {
                    $out[$this->source->get_row_index()] = [['raw'], $this->destination->get_error()];
                }
            } else {
                $insrow = [];
                $pk = []; 	// a primary keys is an array of arrays
                // any element array is
                // array( dst_colnameX => pk1, dst_colnameY => pk2 )

                for ($index = 0; $index < count($this->import_map); ++$index) {
                    if ($this->import_map[$index]['map'] != DOCEBOIMPORT_IGNORE) {
                        $insrow[$this->import_map[$index]['map']] = $row[$index];
                        if (isset($this->import_map[$index]['pk'])) {
                            if ($this->import_map[$index]['pk'] == '1') {
                                $pk[$this->import_map[$index]['map']] = $row[$index];
                            }
                        }
                    }
                }
                if ($import_type == TASK_IMPORT_TYPE_INSERTREMOVE) {
                    $arr_pk[] = $pk;
                }
                foreach ($dst_cols as $col) {
                    $col_name = isset($col[DOCEBOIMPORT_COLID]) ? $col[DOCEBOIMPORT_COLID] : $col[DOCEBOIMPORT_COLNAME];
                    if (!isset($insrow[$col_name])) {
                        $insrow[$col_name] = null;
                    }
                }
                if (!$this->destination->add_row($insrow, $pk)) {
                    $out[$this->source->get_row_index()] = [$pk, $this->destination->get_error()];
                }
            }
            $row = $this->source->get_next_row();
        }
        // now remove all not imported records
        $count_deleted = 0;
        if ($import_type == TASK_IMPORT_TYPE_INSERTREMOVE) {
            $count_deleted = $this->destination->delete_all_notinserted();
        }
        $out[0] = ['inserted' => $this->source->get_row_index(), 'removed' => $count_deleted];
        $this->source->close();
        $this->destination->close();

        return $out;
    }
}
