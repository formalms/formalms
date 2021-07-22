<?php defined("IN_FORMA") or die("Direct access is forbidden");

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

Class TimeperiodsAlms extends Model {

	protected $db;

	public function __construct($id_course = 0, $id_date = 0) {
		$this->db = DbConn::getInstance();
	}

	public function getPerm() {
		return array(
			'view' => 'standard/view.png',
			'add' => 'standrd/add.png',
			'mod' => 'standard/edit.png',
			'del' => 'standard/rem.png'
		);
	}

	/*
	 * internal method: returns the name of time periods table
	 */
	protected function _getPeriodsTable() { return '%lms_time_period'; }

	/*
	 * get DB table records
	 */
	public function getTimePeriodsList($startIndex, $results, $sort, $dir, $filter = false) {
		//validate parameters
		$_dir = strtoupper($dir);
		if ($_dir != "ASC" && $_dir != "DESC") $_dir = "ASC";

		//prevents invalid sort attributes
		$_sort = 'title';
		switch ($sort) {
			case "label": $_sort = 'label'; break;
			case "start_date": $_sort = 'start_date'; break;
			case "end_date": $_sort = 'end_date'; break;
		}

		//build query
		$query = "SELECT * FROM ".$this->_getPeriodsTable();
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$query .= " WHERE title LIKE '%".$filter['text']."%' ";
		}
		$query .= " ORDER BY ".$_sort." ".$_dir." ";
		$query .= " LIMIT ".(int)$startIndex.", ".(int)$results;

		//execute query
		$res = $this->db->query($query);
		if (!$res) return false;

		//prepare records for output
		$output = array();
		while ($obj = $this->db->fetch_obj($res))
			$output[] = $obj;
		
		return $output;
	}

	/*
	 * get records count in DB
	 */
	public function getTimePeriodsTotal($filter = false) {
		$output = false;
		$query = "SELECT COUNT(*) FROM ".$this->_getPeriodsTable();
		if (is_array($filter)) {
			if (isset($filter['text']) && $filter['text'] != '')
				$query .= " WHERE title LIKE '%".$filter['text']."%' ";
		}
		$res = $this->db->query($query);
		list($output) = $this->db->fetch_row($res);
		return $output;
	}


	/*
	 * read a single record by primary key (id_period)
	 */
	function getTimePeriod($id) {
		$query = "SELECT * FROM ".$this->_getPeriodsTable()." WHERE id_period=".(int)$id;
		$res = $this->db->query($query);
		if ($res && $this->db->num_rows($res) > 0)
			return $this->db->fetch_obj($res);
		else
			return false;
	}


	/*
	 * insert a new time period
	 */
	function createTimePeriod($data) {
		$query = "INSERT INTO ".$this->_getPeriodsTable()." (title, start_date, end_date) VALUES "
			." ('".$data->title."', '".$data->start_date."', '".$data->end_date."')";
		$res = $this->db->query($query);

		return ($res ? true : false);
	}


	/*
	 * update an existend time period specified by primary key (id_period)
	 */
	function updateTimePeriod($data) {
		if ((int)$data->id <= 0) return false;

		$query = "UPDATE ".$this->_getPeriodsTable()." SET "
			." title = '".$data->title."', "
			." start_date = '".$data->start_date."',"
			." end_date = '".$data->end_date."' "
			." WHERE id_period=".(int)$data->id;
		$res = $this->db->query($query);

		return ($res ? true : false);
	}


	/*
	 * delete from DB an existend time period specified by primary key (id_period)
	 */
	function deleteTimePeriod($id) {
		if ((int)$id <= 0) return false;

		$query = "DELETE FROM ".$this->_getPeriodsTable()." WHERE id_period = ".(int)$id;
		$res = $this->db->query($query);

		return ($res ? true : false);
	}


	/*
	 * get time periods array for dropdowns
	 */
	function getTimePeriods($labels = false, $objs = false) {
		
		if (is_string($labels)) $arr = array($labels);
		elseif (is_array($labels) && count($labels)>0) $arr =& $labels;
		else $labels = false;

		$output = array();
		$query = "SELECT * FROM ".$GLOBALS['prefix_lms']."_time_period "
			.($labels != false ? " WHERE label IN (".implode(",", $labels).") " : "")
			." ORDER BY end_date DESC, start_date DESC";
		$res = $this->db->query($query);
		if ($res) {
			$method = $objs ? 'fetch_obj' : 'fetch_assoc';
			while ($record = $this->db->$method($res))
				$output[] = $record;
		}
		return $output;
	}

}

?>