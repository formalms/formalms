<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */

/**
 * This class follow the singleton design pattern, his purpose is to abstract
 * the normal function that interact with the database and add to them some
 * other functionality
 */
class DbConn {

	/**
	 * The static var that contain the class instance
	 */
	static private $instance = NULL;

	/**
	 * This var will contains the query logger
	 */
	public $debug = false;

	/**
	 * This var will contains the query logger
	 */
	static public $connected = false;

	/**
	 * Not really used
	 * @return null
	 */
	public function __construct() {}

    /**
     * This function return the current instance for the class, if it's the first
     * time that is called it will instance the class
     * @param bool $link
     * @param array $connection_parameters
     * @return bool|DbConn
     */
	public static function &getInstance($link=false, $connection_parameters=array()) {
        $db_type=Get::cfg('db_type');
	    $host=Get::cfg('db_host');
        $user=Get::cfg('db_user');
        $pass=Get::cfg('db_pass');
        $name=Get::cfg('db_name');
	    if (isset($connection_parameters['db_type']) && isset($connection_parameters['db_host']) && isset($connection_parameters['db_user']) && isset($connection_parameters['db_pass'])){
            $db_type=$connection_parameters['db_type'];
	        $host=$connection_parameters['db_host'];
            $user=$connection_parameters['db_user'];
            $pass=$connection_parameters['db_pass'];
            $name=$connection_parameters['db_name'];
        }
        if ($link){
            return $link;
        }
		if(self::$instance == NULL) {
            if(empty($db_type)){
                $db_type = function_exists('mysqli_connect') ? 'mysqli' : 'mysql' ;
            }
			switch($db_type) {
				case "mysql" : {

					require_once _base_.'/db/drivers/docebodb.mysql.php';

					self::$instance = new Mysql_DbConn();
					self::$instance->debug = Get::cfg('do_debug');

					$conn = self::$instance->connect(	$host,
														$user,
														$pass,
														$name);
					if($conn) self::$connected = true;
				};break;
				case "mysqli" : {

					require_once _base_.'/db/drivers/docebodb.mysqli.php';
					self::$instance = new Mysqli_DbConn();
					self::$instance->debug = Get::cfg('do_debug');

					$conn = self::$instance->connect(	$host,
														$user,
														$pass,
														$name);
					if($conn) self::$connected = true;
				};break;
			}
		}
		return self::$instance;
	}

	/**
	 *	Write a log in the logger classe
	 */
	public function log($str) { if (class_exists('Log')){Log::add( trim(str_replace(array("\t", "\n", "\r"), array(" ", "", ""), $str)) );}  }

	/**
	 * connect to the dbms with the specified data
	 */
	public function connect($host, $user, $pwd, $dbname = false) {}

	/**
	 * Select the database
	 * @return boolean true if the database was selected successfully, false otherwise
	 * @param $dbname string the database name
	 */
	public function select_db($dbname) {}

	/**
	 * Return the dbms specific way used to represent the NULL value
	 * @return string
	 */
	public function get_null() {}

	/**
	 * Escape the data in order to safely use it in a query
	 * @return mixed the escaped data
	 * @param $data mixed the data to escape
	 */
	public function escape($data) {}

	/**
	 * Parse a quer in search for %type and replace the term founded with the
	 * data passed formatting and validating the data
	 * accpted tags are (
	 * 	%% = %
	 * 	%NULL = NULL value
	 * 	%autoinc = autoincrement generate index
	 *  %i = integer
	 *  %f = float
	 *  %d = double
	 *  %date = date in iso format yyyy-mm-dd hh:mm:ss
	 *  %text = string
	 *  $s = string
	 * @return
	 * @param $query Object
	 * @param $data Array[optional]
	 */
	public function parse_query($query, $data = false) {

		if($data == false) $data = array();

		$parsed_query = false;
		$keys = preg_split( "/%adm_|%cms_|%lms_|%scs_/i", $query, '-1', PREG_SPLIT_OFFSET_CAPTURE);

		// %NULL|%autoinc|%i|%double|%date|%text|%s|
		if($keys) {
			$current = 0;
			$parsed_query = '';
			while(list($ind, $match) = each($keys)) {

				$parsed_query .= $match[0];

				//rerive the match
				$str_start = $match[1] + strlen($match[0]);
				if(isset($keys[$ind+1])) $type = substr($query, $str_start, ($keys[$ind+1][1] - $str_start));
				else $type = '%last';

				if(!isset($data[$current])) $data[$current] = '';

				switch($type) {
					// manage table prefix ==================================
                    case "%adm_" :	$parsed_query .= Get::cfg('prefix_fw').'_';break;
					case "%lms_" :	$parsed_query .= Get::cfg('prefix_lms').'_';break;
					case "%cms_" :	$parsed_query .= Get::cfg('prefix_cms').'_';break;
					case "%scs_" :	$parsed_query .= Get::cfg('prefix_scs').'_';break;
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
						if($check == false) $data[$current] = '0000-00-00 00:00:00';
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

	/**
	 * Perform a query on the database (variable number of argument)
	 * @return reouce_id
	 * @param $query string
	 */
	public function query($query) {}

	/**
	 * Perform a query and limit the result with the last two args passed, the must be the start record to consider from and
	 * the numbers of record to retrive
	 * @return resource_id
	 * @param $query string the query to perform
	 * @param mixed number of extra args
	 * @param int the start record
	 * @param int the number of records to retrive
	 */
	public function query_limit($query) {}

	/**
	 * Return the last auto increment value inserted
	 * @return int
	 */
	public function insert_id() {}

	/**
	 * Get a result row as an enumerated array
	 * @return array
	 * @param $resource resource_id
	 */
	public function fetch_row($resource) {}

	/**
	 * Get a result row as an associative array
	 * @return array
	 * @param $resource resource_id
	 */
	public function fetch_assoc($resource) {}

	/**
	 * Get a result row as an array
	 * @return array
	 * @param $resource resource_id
	 */
	public function fetch_array($resource) {}

    /**
     * Get a result row as an object
     *
     * @param $resource resource_id
     * @param null $class_name
     * @param null $params
     * @param bool $conn
     * @return Object
     */
	public function fetch_obj($resource, $class_name=null, $params=null) {}

	/**
	 * Retrieves the number of rows from a result set
	 * @return int
	 * @param $resource resource_id
	 */
	public function num_rows($resource) {}
	
	/**
	 * Retrieves the number of rows from a result set
	 * @return int
	 */
	public function affected_rows() {}

	/**
	 * Begin a transaction
	 */
	public function start_transaction() {}

	/**
	 * Commit a transaction
	 */
	public function commit() {}

	/**
	 * Rollback a transaction
	 */
	public function rollback() {}

	function getAll($query) {

		$data = array();
		$result = $this->query($query);
		while($array = $this->fetch_assoc($result)) {
			$data[] = $array;
		}
		return $data;
	}

	function getOAll($query) {

		$data = array();
		$result = $this->query($query);
		while($obj = $this->fetch_obj($result)) {
			$data[] = $obj;
		}
		return $data;
	}

	/**
	 * Returns the numerical value of the error message from previous db operation
	 * @return int
	 */
	public function errno() {}

	/**
	 * Returns the text of the error message from previous db operation
	 * @return string
	 */
	public function error() {}

    /**
     * Will free all memory associated with the result identifier result.
     * @param $res
     * @return string
     */
    public function free_result($res) {}

    /**
     * Will free all memory associated with the result identifier result.
     * @return string
     */
    public function get_client_info() {}

	/**
	 * Close the connection with the dbms
	 * @return string
	 */
	public function close() {}

    /**
     * get_info todo:edit
     * @return string
     */
    public function get_server_info(){}

    /**
     * data_seek info todo:edit
     * @param $result
     * @param $row_number
     * @return string
     */
    public function data_seek($result, $row_number){}

    /**
     * field_seek info todo:edit
     * @param $result
     * @param $fieldnr
     * @return string
     */
    public function field_seek($result, $fieldnr){}

    /**
     * num_field info todo:edit
     * @param $res
     * @return string
     */
    public function num_fields($res){}

    /**
     * fetch_field info todo:edit
     * @param $result
     * @return string
     */
    public function fetch_field($result){}

    /**
     * escape_string info todo:edit
     * @param $res
     * @return string
     */
    public function escape_string($res){}

    /**
	 * real_escape_string info todo:edit
	 * @return string
	 */
    public function real_escape_string(){}

    /**
	 * Return the current time
	 * @return float
	 */
	protected function get_time() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

}

function sql_query($query, $conn = false) {

	$db = DbConn::getInstance($conn);
	$re = $db->query($query);

	return $re;
}
function sql_limit_query($query, $from, $results, $conn = false) {

	$db = DbConn::getInstance($conn);
	$re = $db->query_limit($query, $from, $results);

	return $re;
}

function sql_insert_id($conn = false) {

	$db = DbConn::getInstance($conn);
	$re = $db->insert_id();

	return $re;
}

function sql_num_rows($res) {

	$db = DbConn::getInstance();
	$re = $db->num_rows($res);

	return $re;
}

function sql_fetch_row($res) {

	$db = DbConn::getInstance();
	$re = $db->fetch_row($res);

	return $re;
}

function sql_fetch_assoc($res) {

	$db = DbConn::getInstance();
	$re = $db->fetch_assoc($res);

	return $re;
}

function sql_fetch_array($res) {

	$db = DbConn::getInstance();
	$re = $db->fetch_array($res);

	return $re;
}

function sql_fetch_object($res, $class_name=null, $params=null) {

	$db = DbConn::getInstance();
	$re = $db->fetch_obj($res, $class_name, $params);

	return $re;
}

function  sql_escape_string($res)
{
	$db = DbConn::getInstance();
	$re = $db->escape_string($res);

	return $re;
}

function  sql_error($link=null){
    $db = DbConn::getInstance($link);
    $re = $db->error();
    return $re;
}

function sql_free_result($res){
    $db = DbConn::getInstance();
    $re = $db->free_result($res);
    return $re;
}

function  sql_get_client_info($link=null){
    $db = DbConn::getInstance($link);
    $re = $db->get_client_info();
    return $re;
}

function sql_get_server_info($link=null){
    $db = DbConn::getInstance($link);
    $re = $db->get_server_info();
    return $re;
}

function sql_data_seek($result, $row_number){
    $db = DbConn::getInstance();
    $re = $db->data_seek($result, $row_number);
    return $re;
}

function sql_errno($link=null){
    $db = DbConn::getInstance($link);
    $re = $db->errno();
    return $re;
}

function sql_affected_rows($link=null){
    $db = DbConn::getInstance($link);
    $re = $db->affected_rows();
    return $re;
}
function sql_field_seek($result, $fieldnr){
    $db = DbConn::getInstance();
    $re = $db->field_seek($result, $fieldnr);
    return $re;
}
function sql_num_field($res){
    $db = DbConn::getInstance();
    $re = $db->num_field($res);
    return $re;
}
function sql_fetch_field($result){
    $db = DbConn::getInstance();
    $re = $db->fetch_field($result);
    return $re;
}
function sql_real_escape_string(){
    $db = DbConn::getInstance();
    $re = $db->real_escape_string();
    return $re;
}

function sql_connect($db_host, $db_user, $db_pass, $db_name=false){
    $db = DbConn::getInstance();
    $re = $db->connect($db_host, $db_user, $db_pass, $db_name);
    return $re;
}

function sql_select_db($db_name){
    $db = DbConn::getInstance();
    $re = $db->select_db($db_name);
    return $re;
}

function sql_close(){
    $db = DbConn::getInstance();
    $re = $db->close();
    return $re;
}

?>