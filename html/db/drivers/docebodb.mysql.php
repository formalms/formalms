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

class Mysql_DbConn extends DbConn {

	protected $conn = NULL;

	public function __construct() {}

	public function __destruct() {

		if(is_resource($this->conn)) $this->close();
	}

	public function connect($host, $user, $pwd, $dbname = false) {

		if(is_resource($this->conn)) return $this->conn;
		if(!$this->conn = @mysql_connect($host, $user, $pwd, TRUE)) {

			Log::add( 'mysql connect error : '.mysql_error() );
			return false;
		}
		// todo : i need some drawback compatibility :/ for now
		$GLOBALS['dbConn'] = $this->conn;

		$this->log( 'mysql connected to : '.$host );

		$this->set_timezone();	// set connection tz

		if($dbname !== false) return $this->select_db($dbname);
		return $this->conn;
	}

	public function select_db($dbname) {

		if(!@mysql_select_db($dbname, $this->conn)) {

			$this->log( 'mysql select db error : '.mysql_error() );
			return false;
		}
		$this->log( 'mysql db selected ' );

		// change charset for utf8 (or other if user config in another way)
		// connection with the server
		$charset = Get::cfg('db_charset', 'utf8');
		$this->query("SET NAMES '".$charset."'");
		$this->query("SET CHARACTER SET '".$charset."'");

		return true;
	}

	public function set_timezone() {
		// set connection timezone according to php settings

		if ( Get::cfg('set_mysql_tz', false) ) {
			$dt = new DateTime();
			$offset = $dt->format("P");		// get current timezone offeset
			$this->query("SET time_zone='".$offset."'");
			$this->log( 'mysql set connection timezone offset to : '.$offset );
		}

		return true;
	}

	public function get_null() {

		return 'NULL';
	}

	public function escape($data) {

		return mysql_real_escape_string($data, $this->conn);
	}

	public function query($query) {

		$data = func_get_args();
		array_shift($data); //remove the query form the list

		if(isset($data[0]) && is_array($data[0])) $data = $data[0];

		$parsed_query = $this->parse_query($query, $data);

		$start_at = $this->get_time();
		$re = mysql_query($parsed_query, $this->conn);
		$this->query_log( $parsed_query, ($this->get_time() - $start_at) );

		return $re;
	}

	public function query_limit($query) {

		$data = func_get_args();

		$results = array_pop($data);	//number of element
		$from = array_pop($data);		//from the element
		array_shift($data);				//remove the query form the list

		if(isset($data[0]) && is_array($data[0])) $data = $data[0];

		$parsed_query = $this->parse_query($query, $data)
			." LIMIT ".(int)$from.", ".(int)$results."";

		$start_at = $this->get_time();
		$re = mysql_query($parsed_query, $this->conn);
		$this->query_log( $parsed_query, ($this->get_time() - $start_at) );

		return $re;
	}

	public function insert_id() {

		return mysql_insert_id($this->conn);
	}

	public function fetch_row($resource) {

		if(!$resource) return false;
		return mysql_fetch_row($resource);
	}

	public function fetch_assoc($resource) {

		if(!$resource) return false;
		return mysql_fetch_assoc($resource);
	}

	public function fetch_array($resource) {

		if(!$resource) return false;
		return mysql_fetch_array($resource);
	}

	public function fetch_obj($resource) {

		if(!$resource) return false;
		return mysql_fetch_object($resource);
	}

	public function escape_string($resource) {

		if(!$resource) return false;
		return mysql_escape_string($resource);
	}

	public function num_rows($resource) {

		if(!$resource) return false;
		return mysql_num_rows($resource);
	}

	public function affected_rows($resource) {

		if(!$resource) return false;
		return mysql_affected_rows($resource);
	}

	public function errno() {

		return mysql_errno($this->conn);
	}

	public function error() {

		return mysql_error($this->conn);
	}

	public function start_transaction() {

		return $this->query("START TRANSACTION");
	}

	/**
	 * Commit a transaction
	 */
	public function commit() {

		return $this->query("COMMIT");
	}

	/**
	 * Rollback a transaction
	 */
	public function rollback() {

		return $this->query("ROLLBACK");
	}

	public function close() {

		@mysql_close($this->conn);
		$this->log( 'mysql close connection' );
	}

	function query_log($qtxt, $time_used = false) {

		if(Get::sett('do_debug', 'off') == 'off') return;
		$time_used = number_format($time_used, 6);
		if($this->errno()) {
			$this->log( '<b>('.$this->errno().') '.$this->error().'</b>'
				.' :: '.'<span style="color:red">'.$qtxt.'</span>'
				.( $time_used ? ' in :'.$time_used.' s' : '') );
		} else {
			$this->log( $qtxt.( $time_used ? ' in :'.$time_used.' s' : '') );
		}

	}

}

?>
