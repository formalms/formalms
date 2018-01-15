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

	private function false_if_null($return){
		return isset($return)?$return:false;
	}

	public function connect($host, $user, $pwd, $dbname = false) {

		if(is_resource($this->conn)) return $this->conn;
		if($dbname != false) {

            if(!$this->conn = @mysqli_connect($host, $user, $pwd, $dbname)) {

                Log::add( 'mysql connect error : '.mysqli_connect_error() );
				//Util::fatal('Cannot connect to the database server.');
                return false;
            }
			$GLOBALS['dbConn'] = $this;
            $this->log( 'mysql connected to : '.$host );
            $this->log( 'mysql db selected' );
        } else {
            if(!$this->conn = @mysqli_connect($host, $user, $pwd)) {

                Log::add( 'mysql connect error : '.mysqli_connect_error() );
				//Util::fatal('Cannot connect to the database server.');
                return false;
            }
			$GLOBALS['dbConn'] = $this;
            $this->log( 'mysql connected to : '.$host );
        }

		$this->set_timezone();	// set connection tz
        if($dbname != false){
			return $this->select_db($dbname);
		}
		return $this;
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

	public function escape($escapestr) {

		return mysqli_real_escape_string($this->conn, $escapestr);
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

		$re = mysqli_query($this->conn, $parsed_query);

		if($this->debug) $this->query_log( $parsed_query, ($this->get_time() - $start_at) );

		return $re;
	}

	public function insert_id() {

		return mysqli_insert_id($this->conn);
	}

	public function fetch_row($result) {
		if(!$result) return false;
		//mysql and mysqli returns different value if empty result
		return $this->false_if_null(mysqli_fetch_row($result));
	}

	public function fetch_assoc($result) {

		if(!$result) return false;
		//mysql and mysqli returns different value if empty result
		return $this->false_if_null(mysqli_fetch_assoc($result));
	}

	public function fetch_array($result) {

		if(!$result) return false;
		//mysql and mysqli returns different value if empty result
		return $this->false_if_null(mysqli_fetch_array($result));
	}

	public function fetch_obj($result, $class_name=null, $params=null) {

		if(!$result) return false;
		if ($params){
			//mysql and mysqli returns different value if empty result
			return $this->false_if_null(mysqli_fetch_object($result));
		}
		if ($class_name){
			//mysql and mysqli returns different value if empty result
			return $this->false_if_null(mysqli_fetch_object($result));
		}
		//mysql and mysqli returns different value if empty result
		return $this->false_if_null(mysqli_fetch_object($result));
	}

	public function num_rows($result) {

		if(!$result) return false;
		$return=mysqli_num_rows($result);
		return $return?$return:false;
	}

	public function affected_rows() {
		return mysqli_affected_rows($this->conn);
	}

	public function errno() {

		return mysqli_errno($this->conn);
	}

	public function error() {

		return mysqli_error($this->conn);
	}

	public function free_result($result) {
		return mysqli_free_result($result);
	}

	public function get_client_info() {
		return mysqli_get_client_info($this->conn);
	}

	public function get_server_info(){
		return mysqli_get_server_info($this->conn);
	}

	public function data_seek($result, $offset){
		return mysqli_data_seek($result, $offset);
	}

	public function field_seek($result, $fieldnr){
		return mysqli_field_seek($result, $fieldnr);
	}

	public function num_fields($result){
		return mysqli_num_fields($result);
	}

	public function fetch_field($result){
		return mysqli_fetch_field($result);
	}

	public function escape_string($escapestring) {
		return mysqli_real_escape_string($this->conn, $escapestring);
	}

	public function real_escape_string($escapestring){
		return mysqli_real_escape_string($this->conn, $escapestring);
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
