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
namespace FormaLms\db\drivers;

use FormaLms\db\DbConn;

defined('IN_FORMA') or exit('Direct access is forbidden.');

class Mysqli extends DbConn
{
    protected $conn = null;

    public function __destruct()
    {
        if (is_resource($this->conn)) {
            $this->close();
        }
    }

    private function false_if_null($return)
    {
        return $return ?? false;
    }

    public function connect($host, $user, $pwd, $dbname = false)
    {
        if (is_resource($this->conn)) {
            return $this->conn;
        }
        try {

            if ($dbname != false) {
                if (!$this->conn = @mysqli_connect($host, $user, $pwd, $dbname)) {
                    $this->log('mysql connect error : ' . mysqli_connect_error());
                    //Util::fatal('Cannot connect to the database server.');
                    return false;
                }
                $GLOBALS['dbConn'] = $this;
                $this->log('mysql connected to : ' . $host);
                $this->log('mysql db selected');
            } else {
            
                    if (!$this->conn = @mysqli_connect($host, $user, $pwd)) {
                        $this->log('mysql connect error : ' . mysqli_connect_error());
                        //Util::fatal('Cannot connect to the database server.');
                        return false;
                    }
            
                $GLOBALS['dbConn'] = $this;
                $this->log('mysql connected to : ' . $host);
            }

        } catch (\Exception $exception) {
            return false;
        }

        $this->set_timezone();    // set connection tz
        if ($dbname != false) {
            return $this->select_db($dbname);
        }

        return $this;
    }

    public function select_db($dbname)
    {
        if (!$this->conn || !@mysqli_select_db($this->conn, $dbname)) {
            $this->log('mysql select db error');
            //Util::fatal('Cannot connect to the database server.');
            return false;
        }
        $this->log('mysql db selected ');

        // change charset for utf8 (or other if user config in another way)
        // connection with the server
        $charset = \FormaLms\lib\Get::cfg('db_charset', 'utf8');
        $this->query("SET NAMES '" . $charset . "'", $this->conn);
        $this->query("SET CHARACTER SET '" . $charset . "'", $this->conn);

        // required by userselector: almost 10000 users selected
		//TODO to be removed when userselector reworked
        $this->query('SET SESSION group_concat_max_len = 70000', $this->conn);

        //TODO NO_Strict_MODE: to be confirmed
        $this->query("SET SQL_MODE = 'NO_AUTO_CREATE_USER'", $this->conn);

        return true;
    }

    public function set_timezone()
    {
        // set connection timezone according to php settings

        if (\FormaLms\lib\Get::cfg('set_mysql_tz', false)) {
            $dt = new \DateTime();
            $offset = $dt->format('P');        // get current timezone offset
            $this->query("SET time_zone='" . $offset . "'", $this->conn);
            $this->log('mysql set connection timezone offset to : ' . $offset);
        }

        return true;
    }

    public function get_null()
    {
        return 'NULL';
    }

    public function escape($escapestr)
    {
        if ($this->conn) {
            return mysqli_real_escape_string($this->conn, $escapestr);
        }
        return false;
    }

    public function query($query)
    {
        try {

            $data = func_get_args();
            array_shift($data); //remove the query form the list

            if (isset($data[0]) && is_array($data[0])) {
                $data = $data[0];
            }

            $parsed_query = $this->parse_query($query, $data);

            if ($this->debug) {
                $start_at = $this->get_time();
            }

            if ($this->conn) {
                $re = mysqli_query($this->conn, $parsed_query);

                if ($this->debug) {
                    $this->query_log($parsed_query, ($this->get_time() - $start_at));
                }

                return $re;
            }
            return false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function query_limit($query)
    {
        $data = func_get_args();

        $results = array_pop($data);    //number of element
        $from = array_pop($data);    //from the element
        array_shift($data);        //remove the query form the list

        if (isset($data[0]) && is_array($data[0])) {
            $data = $data[0];
        }

        $parsed_query = $this->parse_query($query, $data)
            . ' LIMIT ' . (int)$from . ', ' . (int)$results . '';

        if ($this->debug) {
            $start_at = $this->get_time();
        }

        $re = mysqli_query($this->conn, $parsed_query);

        if ($this->debug) {
            $this->query_log($parsed_query, ($this->get_time() - $start_at));
        }

        return $re;
    }

    public function insert_id()
    {
        return mysqli_insert_id($this->conn);
    }

    public function fetch_row($result)
    {
        if (!$result) {
            return false;
        }
        //mysql and mysqli returns different value if empty result
        return $this->false_if_null(mysqli_fetch_row($result));
    }

    public function fetch_assoc($result)
    {
        if (!$result) {
            return false;
        }
        //mysql and mysqli returns different value if empty result
        return $this->false_if_null(mysqli_fetch_assoc($result));
    }

    public function fetch_array($result)
    {
        if (!$result) {
            return false;
        }
        //mysql and mysqli returns different value if empty result
        return $this->false_if_null(mysqli_fetch_array($result));
    }

    public function fetch_obj($result, $class_name = null, $params = null)
    {
        if (!$result) {
            return false;
        }
        if ($params) {
            //mysql and mysqli returns different value if empty result
            return $this->false_if_null(mysqli_fetch_object($result, $class_name, $params));
        }
        if ($class_name) {
            //mysql and mysqli returns different value if empty result
            return $this->false_if_null(mysqli_fetch_object($result, $class_name));
        }
        //mysql and mysqli returns different value if empty result
        return $this->false_if_null(mysqli_fetch_object($result));
    }

    public function num_rows($result)
    {
        if (!$result) {
            return false;
        }
        $return = mysqli_num_rows($result);

        return $return ?: false;
    }

    public function affected_rows()
    {
        if ($this->conn) {
            return mysqli_affected_rows($this->conn);
        }
        return false;
    }

    public function errno()
    {
        return mysqli_errno($this->conn);
    }

    public function error()
    {
        if ($this->conn) {
            return mysqli_error($this->conn);
        }
        return false;
    }

    public function free_result($result)
    {
        return mysqli_free_result($result);
    }

    public function get_client_info()
    {
        return mysqli_get_client_info();
    }

    public function get_server_info()
    {
        if ($this->conn) {
            return mysqli_get_server_info($this->conn);
        }
        return false;

    }

    public function data_seek($result, $offset)
    {
        return mysqli_data_seek($result, $offset);
    }

    public function field_seek($result, $fieldnr)
    {
        return mysqli_field_seek($result, $fieldnr);
    }

    public function num_fields($result)
    {
        return mysqli_num_fields($result);
    }

    public function fetch_field($result)
    {
        return mysqli_fetch_field($result);
    }

    public function escape_string($escapestring)
    {
        return $this->escape($escapestring);
    }

    public function real_escape_string($escapestring)
    {
        return $this->escape($escapestring);
    }

    public function start_transaction()
    {
        return $this->query('START TRANSACTION');
    }

    /**
     * Commit a transaction.
     */
    public function commit()
    {
        return $this->query('COMMIT');
    }

    /**
     * Rollback a transaction.
     */
    public function rollback()
    {
        return $this->query('ROLLBACK');
    }

    public function close()
    {
        if ($this->conn) {
            @mysqli_close($this->conn);
        }
        $this->log('mysql close connection');
    }

    public function query_log($qtxt, $time_used = false)
    {
        if ($this->errno()) {
            $this->log('<b>(' . $this->errno() . ') ' . $this->error() . '</b>'
                . ' :: ' . '<span style="color:red">' . $qtxt . '</span>'
                . ($time_used ? ' in :' . $time_used . ' s' : ''));
        } else {
            $this->log($qtxt . ($time_used ? ' in :' . $time_used . ' s' : ''));
        }
    }
}
