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
		$GLOBALS['dbConn'] = $this;

		$this->log( 'mysql connected to : '.$host );

		$this->set_timezone();	// set connection tz

		if($dbname !== false) return $this->select_db($dbname);
		return $this;
	}

	public function select_db($dbname) {

		if(!@mysql_select_db($dbname,$this->conn)) {

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

	public function fetch_row($result) {

		if(!$result) return false;
		return mysql_fetch_row($result);
	}

	public function fetch_assoc($result) {

		if(!$result) return false;
		return mysql_fetch_assoc($result);
	}

	public function fetch_array($result) {

		if(!$result) return false;
		return mysql_fetch_array($result);
	}

	public function fetch_obj($result, $class_name=null, $params=null) {

		if(!$result) return false;
		if ($params){
			return mysql_fetch_object($result, $class_name, $params);
		}
		if ($class_name){
			return mysql_fetch_object($result, $class_name);
		}
		return mysql_fetch_object($result);
	}

	public function escape_string($unescaped_string) {

		if(!$unescaped_string) return false;
		return mysql_escape_string($unescaped_string);
	}

	public function num_rows($result) {

		if(!$result) return false;
		return mysql_num_rows($result);
	}
    
    public function affected_rows() {
        return mysql_affected_rows($this->conn);
    }    

	public function errno() {
		return mysql_errno($this->conn);
	}

	public function error() {
		return mysql_error($this->conn);
	}

	public function free_result($result) {
		return mysql_free_result($result);
	}

	public function get_client_info() {
		return mysql_get_client_info($this->conn);
	}

	public function get_server_info(){
		return mysql_get_server_info($this->conn);
	}

	public function data_seek($result, $row_number){
		return mysql_data_seek($result, $row_number);
	}

	public function field_seek($result, $row_number){
		return mysql_field_seek($result, $row_number);
	}

	public function num_fields($result){
		return mysql_num_fields($result);
	}

	public function fetch_field($result){
		return mysql_fetch_field($result);
	}

	public function real_escape_string($unescaped_string){
		return mysql_real_escape_string($unescaped_string, $this->conn);
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
