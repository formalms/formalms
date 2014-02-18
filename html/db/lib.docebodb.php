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
	 * @return DbConn
	 */
	public static function &getInstance() {

		if(self::$instance == NULL) {

			$db_type = Get::cfg('db_type', ( function_exists('mysqli_connect') ? 'mysqli' : 'mysql' ) );
			switch($db_type) {
				case "mysql" : {

					require_once _base_.'/db/drivers/docebodb.mysql.php';

					self::$instance = new Mysql_DbConn();
					self::$instance->debug = Get::cfg('do_debug');

					$conn = self::$instance->connect(	Get::cfg('db_host'),
														Get::cfg('db_user'),
														Get::cfg('db_pass'),
														Get::cfg('db_name'));
					if($conn) self::$connected = true;
				};break;
				case "mysqli" : {

					require_once _base_.'/db/drivers/docebodb.mysqli.php';
					self::$instance = new Mysqli_DbConn();
					self::$instance->debug = Get::cfg('do_debug');

					$conn = self::$instance->connect(	Get::cfg('db_host'),
														Get::cfg('db_user'),
														Get::cfg('db_pass'),
														Get::cfg('db_name'));
					if($conn) self::$connected = true;
				};break;
			}
		}
		return self::$instance;
	}

	/**
	 *	Write a log in the logger classe
	 */
	public function log($str) { Log::add( trim(str_replace(array("\t", "\n", "\r"), array(" ", "", ""), $str)) ); }

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
	 * @return Object
	 * @param $resource resource_id
	 */
	public function fetch_obj($resource) {}

	/**
	 * Retrieves the number of rows from a result set
	 * @return int
	 * @param $resource resource_id
	 */
	public function num_rows($resource) {}
	
	/**
	 * Retrieves the number of rows from a result set
	 * @return int
	 * @param $resource resource_id
	 */
	public function affected_rows($resource) {}

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
	 * Close the connection with the dbms
	 * @return string
	 */
	public function close() {}

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

	$db = DbConn::getInstance();
	$re = $db->query($query);

	return $re;
}
function sql_limit_query($query, $from, $results, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->query_limit($query, $from, $results);

	return $re;
}

function sql_insert_id($conn = false) {

	$db = DbConn::getInstance();
	$re = $db->insert_id();

	return $re;
}

function sql_num_rows($res, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->num_rows($res);

	return $re;
}

function sql_fetch_row($res, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->fetch_row($res);

	return $re;
}

function sql_fetch_assoc($res, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->fetch_assoc($res);

	return $re;
}

function sql_fetch_array($res, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->fetch_array($res);

	return $re;
}

function sql_fetch_object($res, $conn = false) {

	$db = DbConn::getInstance();
	$re = $db->fetch_obj($res);

	return $re;
}

function  sql_escape_string($res, $conn = false)
{
	$db = DbConn::getInstance();
	$re = $db->escape_string($res);

	return $re;
}

?>