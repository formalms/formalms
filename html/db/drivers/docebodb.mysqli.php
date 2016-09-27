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

class mysqli_DbConn extends DbConn {

	protected $conn = NULL;

	public function __construct() {}

	public function __destruct() {

		if(is_resource($this->conn)) $this->close();
	}

	public function connect($host, $user, $pwd, $dbname = false) {

		if(is_resource($this->conn)) return $this->conn;

		if($dbname !== false) {

            if(!$this->conn = @mysqli_connect($host, $user, $pwd, $dbname)) {

                Log::add( 'mysql connect error : '.mysqli_connect_error() );
				//Util::fatal('Cannot connect to the database server.');
                return false;
            }
            $this->log( 'mysql connected to : '.$host );
            $this->log( 'mysql db selected' );
        } else {
            if(!$this->conn = @mysqli_connect($host, $user, $pwd)) {

                Log::add( 'mysql connect error : '.mysqli_connect_error() );
				//Util::fatal('Cannot connect to the database server.');
                return false;
            }
            $this->log( 'mysql connected to : '.$host );
        }

		$this->set_timezone();	// set connection tz

		return $this->conn;
	}

	public function select_db($dbname) {

		if(!@mysqli_select_db($this->conn, $dbname)) {

			Log::add( 'mysql select db error' );
			//Util::fatal('Cannot connect to the database server.');
			return false;
		}
		$this->log( 'mysql db selected ' );

		// change charset for utf8 (or other if user config in another way)
		// connection with the server
		$charset = Get::cfg('db_charset', 'utf8');
		$this->query("SET NAMES '".$charset."'", $this->conn);
		$this->query("SET CHARACTER SET '".$charset."'", $this->conn);

		return true;
	}

	public function set_timezone() {
		// set connection timezone according to php settings

		if ( Get::cfg('set_mysql_tz', false) ) {
			$dt = new DateTime();
			$offset = $dt->format("P");		// get current timezone offeset
			$this->query("SET time_zone='".$offset."'", $this->conn);
			$this->log( 'mysql set connection timezone offset to : '.$offset );
		}
		return true;
	}

	public function get_null() {

		return 'NULL';
	}

	public function escape($data) {

		return mysqli_real_escape_string($data, $this->conn);
	}

	public function query($query) {

		$data = func_get_args();
		array_shift($data); //remove the query form the list

		if(isset($data[0]) && is_array($data[0])) $data = $data[0];

		$parsed_query = $this->parse_query($query, $data);

		if($this->debug) $start_at = $this->get_time();

		$re = mysqli_query($this->conn, $parsed_query);

		if($this->debug) $this->query_log( $parsed_query, ($this->get_time() - $start_at) );

		return $re;
	}

	public function query_limit($query) {

		$data = func_get_args();

		$results = array_pop($data);	//number of element
		$from = array_pop($data);	//from the element
		array_shift($data); 		//remove the query form the list

		if(isset($data[0]) && is_array($data[0])) $data = $data[0];

		$parsed_query = $this->parse_query($query, $data)
			." LIMIT ".(int)$from.", ".(int)$results."";

		if($this->debug) $start_at = $this->get_time();

		$re = mysqli_query($parsed_query, $this->conn);

		if($this->debug) $this->query_log( $parsed_query, ($this->get_time() - $start_at) );

		return $re;
	}

	public function insert_id() {

		return mysqli_insert_id($this->conn);
	}

	public function fetch_row($resource) {

		if(!$resource) return false;
		return mysqli_fetch_row($resource);
	}

	public function fetch_assoc($resource) {

		if(!$resource) return false;
		return mysqli_fetch_assoc($resource);
	}

	public function fetch_array($resource) {

		if(!$resource) return false;
		return mysqli_fetch_array($resource);
	}

	public function num_rows($resource) {

		if(!$resource) return false;
		return mysqli_num_rows($resource);
	}

	public function errno() {

		return mysqli_errno($this->conn);
	}

	public function error() {

		return mysqli_error($this->conn);
	}

	public function free_result($resource) {
		return mysqli_free_result($resource);
	}

	public function get_client_info() {
		return mysqli_get_client_info();
	}

	public function get_server_info($resource){
		return mysqli_get_server_info($resource);
	}

	public function data_seek($resource, $row_number){
		return mysqli_data_seek($resource, $row_number);
	}

	public function field_seek($resource, $row_number){
		return mysqli_field_seek($resource, $row_number);
	}

	public function num_fields($resource){
		return mysqli_num_fields($resource);
	}

	public function fetch_field($resource){
		return mysqli_fetch_field($resource);
	}

	public function real_escape_string($resource, $escapestring){
		return mysqli_real_escape_string($resource, $escapestring);
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

		@mysqli_close($this->conn);
		$this->log( 'mysql close connection' );
	}

	function query_log($qtxt, $time_used = false) {

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
