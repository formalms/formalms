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

namespace FormaLms\db;

use FormaLms\db\drivers\Mysqli;

defined('IN_FORMA') or exit('Direct access is forbidden.');


require_once 'DbHelper.php';

/**
 * This class follow the singleton design pattern, his purpose is to abstract
 * the normal function that interact with the database and add to them some
 * other functionality.
 */
class DbConn
{
    /**
     * The static var that contain the class instance.
     */
    protected static $instance = null;

    protected static $factory = null;

    /**
     * This var will contains the query logger.
     */
    public $debug = false;

    /**
     * This var will contains the query logger.
     */
    public static $connected = false;

    public static function setFactory($factory)
    {
        self::$factory = $factory;
    }

    public static function getInstance($link = false, $connection_parameters = [])
    {
        if ($link) {
            return $link;
        }

        // if connection is active returns connection instance
        if (self::$instance !== null && self::$instance->conn !== null && (self::$factory === null  || (self::$factory !== null && self::$instance instanceof self::$factory))) {
            return self::$instance;
        }

        // get connection parameters
        $config = self::getConnectionParameters($connection_parameters);

        // Se esiste una factory personalizzata, la usiamo
        if (self::$factory !== null) {
            self::$instance = self::$factory->create($config);

        } else {
            self::$instance = self::getConnection(
                $config['db_type'],
                $config['db_host'],
                $config['db_user'],
                $config['db_pass'],
                $config['db_name']
            );
        }

        if (self::$instance) {
            self::$connected = true;
        }

        return self::$instance;
    }

    protected static function getConnectionParameters($connection_parameters)
    {
        // Recupera i parametri dalla sessione
        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        $cfg = [];
        if ($session && $session->has('setValues') && count($session->get('setValues'))) {
            $values = $session->get('setValues');
            $cfg['db_type'] = 'mysqli';
            $cfg['db_user'] = $values['dbUser'] ?? false;
            $cfg['db_pass'] = $values['dbPass'] ?? false;
            $cfg['db_name'] = $values['dbName'] ?? false;
            $cfg['db_host'] = $values['dbHost'] ?? false;
        }

        // Priorità: connection_parameters > Get::cfg > session values
        $config = [
            'db_type' => $connection_parameters['db_type'] ?? \FormaLms\lib\Get::cfg('db_type') ?: ($cfg['db_type'] ?? false),
            'db_host' => $connection_parameters['db_host'] ?? \FormaLms\lib\Get::cfg('db_host') ?: ($cfg['db_host'] ?? false),
            'db_user' => $connection_parameters['db_user'] ?? \FormaLms\lib\Get::cfg('db_user') ?: ($cfg['db_user'] ?? false),
            'db_pass' => $connection_parameters['db_pass'] ?? \FormaLms\lib\Get::cfg('db_pass') ?: ($cfg['db_pass'] ?? false),
            'db_name' => $connection_parameters['db_name'] ?? \FormaLms\lib\Get::cfg('db_name') ?: ($cfg['db_name'] ?? false),
        ];

        return $config;
    }

    public static function getConnection($dbType, $dbHost, $dbUser, $dbPassword, $dbName, $debug = null)
    {
        if (empty($dbType)) {
            $dbType = function_exists('mysqli_connect') ? 'mysqli' : null;
        }

        switch ($dbType) {
            case 'mysqli':
                $instance = new Mysqli();
                if (!$debug) {
                    $instance->debug = \FormaLms\lib\Get::cfg('do_debug');
                } else {
                    $instance->debug = $debug;
                }

                self::$connected = $instance->connect($dbHost,
                    $dbUser,
                    $dbPassword,
                    $dbName);

                return $instance;
        }
        return false;
    }

    public static function checkConnection($dbType, $dbHost, $dbUser, $dbPassword, $dbName, $debug)
    {
        $instance = self::getConnection($dbType, $dbHost, $dbUser, $dbPassword, $dbName, $debug);
        if (self::$connected) {
            return true;
        }
        return false;
    }

    /**
     *    Write a log in the logger classe.
     */
    public function log($str)
    {
        if (class_exists('Log')) {
            \Log::add(trim(str_replace(["\t", "\n", "\r"], [' ', '', ''], $str)));
        }
    }

    /**
     * connect to the dbms with the specified data.
     */
    public function connect($host, $user, $pwd, $dbname = false)
    {
    }

    /**
     * Select the database.
     *
     * @param $dbname string the database name
     * @return bool true if the database was selected successfully, false otherwise
     *
     */
    public function select_db($dbname)
    {
    }

    /**
     * Return the dbms specific way used to represent the NULL value.
     *
     * @return string
     */
    public function get_null()
    {
    }

    /**
     * Escape the data in order to safely use it in a query.
     *
     * @param $data mixed the data to escape
     * @return mixed the escaped data
     *
     */
    public function escape($data)
    {
    }

    /**
     * Parse a quer in search for %type and replace the term founded with the
     * data passed formatting and validating the data
     * accpted tags are (
     *    %% = %
     *    %NULL = NULL value
     *    %autoinc = autoincrement generate index
     *  %i = integer
     *  %f = float
     *  %d = double
     *  %date = date in iso format yyyy-mm-dd hh:mm:ss
     *  %text = string
     *  $s = string.
     *
     * @param $query Object
     * @param $data array[optional]
     * @return
     *
     */
    public function parse_query($query, $data = false)
    {
        if ($data == false) {
            $data = [];
        }

        $parsed_query = false;
        $keys = preg_split('/%adm_|%cms_|%lms_|%scs_/i', $query, '-1', PREG_SPLIT_OFFSET_CAPTURE);

        // %NULL|%autoinc|%i|%double|%date|%text|%s|
        if ($keys) {
            $current = 0;
            $parsed_query = '';
            foreach ($keys as $ind => $match) {
                $parsed_query .= $match[0];

                //rerive the match
                $str_start = $match[1] + strlen($match[0]);
                if (isset($keys[$ind + 1])) {
                    $type = substr($query, $str_start, ($keys[$ind + 1][1] - $str_start));
                } else {
                    $type = '%last';
                }

                if (!isset($data[$current])) {
                    $data[$current] = '';
                }

                switch ($type) {
                    // manage table prefix ==================================
                    case '%adm_':
                        $parsed_query .= \FormaLms\lib\Get::cfg('prefix_fw') . '_';
                        break;
                    case '%lms_':
                        $parsed_query .= \FormaLms\lib\Get::cfg('prefix_lms') . '_';
                        break;
                    case '%cms_':
                        $parsed_query .= \FormaLms\lib\Get::cfg('prefix_cms') . '_';
                        break;
                    case '%scs_':
                        $parsed_query .= \FormaLms\lib\Get::cfg('prefix_scs') . '_';
                        break;
                    // select by type =======================================
                    /*
                    case "%%" : {
                        //not used
                        $parsed_query .= '%';
                    };break;
                    case "%NULL" : {
                        $parsed_query .= $this->get_null();
                    };break;
                    case "%autoinc" : {
                        $parsed_query .= $this->get_null();
                    };break;
                    case "%i" : {
                        $parsed_query .= (int)$data[$current];
                    };break;
                    case "%f" : {
                        $parsed_query .= (float)$data[$current];
                    };break;
                    case "%double" : {
                        $parsed_query .= (double)$data[$current];
                    };break;
                    case "%date" : {
                        //is in iso format ?
                        $check = preg_match("/[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}/", $data[$current]);
                        
                    };
                    case "%text" :
                    case "%s" : {
                        $parsed_query .= "".$this->escape($data[$current])."";
                    };break;
                    */
                }
                ++$current;
            }
        } else {
            return $query;
        }

        return $parsed_query;
    }

    public function query($query)
    {
    }


    public function query_limit($query)
    {
    }

    public function insert_id()
    {
    }

    public function fetch_row($resource)
    {
    }

    public function fetch_assoc($resource)
    {
    }

    public function fetch_array($resource)
    {
    }

    public function fetch_obj($resource, $class_name = null, $params = null)
    {
    }

    /**
     * @param $resource
     */
    public function num_rows($resource)
    {
    }

    /**
     * Retrieves the number of rows from a result set.
     *
     * @return int
     */
    public function affected_rows()
    {
    }

    /**
     * Begin a transaction.
     */
    public function start_transaction()
    {
    }

    /**
     * Commit a transaction.
     */
    public function commit()
    {
    }

    /**
     * Rollback a transaction.
     */
    public function rollback()
    {
    }

    public function getAll($query)
    {
        $data = [];
        $result = $this->query($query);
        while ($array = $this->fetch_assoc($result)) {
            $data[] = $array;
        }

        return $data;
    }

    public function getOAll($query)
    {
        $data = [];
        $result = $this->query($query);
        while ($obj = $this->fetch_obj($result)) {
            $data[] = $obj;
        }

        return $data;
    }

    /**
     * Returns the numerical value of the error message from previous db operation.
     *
     * @return int
     */
    public function errno()
    {
    }

    /**
     * Returns the text of the error message from previous db operation.
     *
     * @return string
     */
    public function error()
    {
    }

    /**
     * Will free all memory associated with the result identifier result.
     *
     * @param $res
     *
     * @return string
     */
    public function free_result($res)
    {
    }

    /**
     * Will free all memory associated with the result identifier result.
     *
     * @return string
     */
    public function get_client_info()
    {
    }

    /**
     * Close the connection with the dbms.
     *
     * @return string
     */
    public function close()
    {
    }

    /**
     * get_info todo:edit.
     *
     * @return string
     */
    public function get_server_info()
    {
    }

    /**
     * data_seek info todo:edit.
     *
     * @param $result
     * @param $row_number
     *
     * @return string
     */
    public function data_seek($result, $row_number)
    {
    }

    /**
     * @param $result
     * @param $fieldnr
     * @return void
     */
    public function field_seek($result, $fieldnr)
    {
    }

    /**
     * @param $res
     * @return void
     */
    public function num_fields($res)
    {
    }

    /**
     * @param $result
     * @return void
     */
    public function fetch_field($result)
    {
    }

    /**
     * @param $res
     * @return void
     */
    public function escape_string($res)
    {
    }

    /**
     * @param $res
     * @return void
     */
    public function real_escape_string($res)
    {
    }

    /**
     * @return float
     */
    protected function get_time()
    {
        [$usec, $sec] = explode(' ', microtime());

        return (float)$usec + (float)$sec;
    }
}
