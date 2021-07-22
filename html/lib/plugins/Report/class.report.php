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

//report session managemente class

define('_REPORT_SESSION',   'report_tempdata');
define('_RS_ID',            'id_report');
define('_RS_ROWS_FILTER',   'rows_filter');
define('_RS_COLS_FILTER',   'columns_filter');
define('_RS_COLS_CATEGORY', 'columns_filter_category');

//report superclass

class ReportPlugin {

	var $id_report;

	var $report_name = '';

	var $report_descr = '';

	var $back_url = '';
	var $jump_url = '';
	var $next_url = '';

	var $lang;

	var $usestandardtitle_rows = true;
	//var $usestandardtitle_cols = true;

	var $columns_categories = array();

	var $db = NULL;

	function __construct($id_report, $report_name = false) {

		$this->id_report = $id_report;
		if($report_name == false) {
			$this->_load();
		} else {
			$lang =& DoceboLanguage::createInstance('report', 'framework');
			$this->report_name 	= $lang->def($report_name);
			$this->report_descr = $lang->def($report_name);
		}

		$this->db = DbConn::getInstance();
	}

	function get_name() { return $this->report_name; }
	function get_description() { return $this->report_descr; }

	//function to override in subclasses
	function play_filter_rows() { return ''; }

	//function to override in subclasses
	function play_filter_columns() { return ''; }

	function apply_filter() {}



	//******************************************************************************
	function _set_columns_category($key, $name, $filter, $results, $get_data, $stdtitle=true) {
		$this->columns_categories[$key] = array(
			'key'      => $key,
			'name'     => $name,
			'filter'   => $filter,
			'show'     => $results,
			'get_data' => $get_data,
			'stdtitle' => $stdtitle
		);
	}


	function get_columns_categories() {
		$temp = array();
		foreach ($this->columns_categories as $key=>$value) {
			$temp[$key] = $value['name'];
		}
		return $temp;
	}

	function get_columns_filter($cat) {
		$name_func = $this->columns_categories[$cat]['filter'];
		return $this->$name_func();
	}

	function useStandardTitle_Rows() {
		return $this->usestandardtitle_rows;
	}

	function useStandardTitle_Columns() {
		if (isset($_SESSION['report_tempdata']['columns_filter_category']))
			$temp = $_SESSION['report_tempdata']['columns_filter_category'];
		else
			return true;

		if (isset($this->columns_categories[$temp])) {
			return $this->columns_categories[$temp]['stdtitle']; }
		else
			return true;
	}

	function show_results($cat = false, $report_data = NULL) {
		if (!$cat) $cat = $_SESSION['report_tempdata']['columns_filter_category'];
		$name_func = $this->columns_categories[$cat]['show'];//['get_data'];
		return $this->$name_func($report_data);
	}

	function _get_data($type = 'html', $cat = false, $report_data = NULL) {
		if (!$cat) $cat = $_SESSION['report_tempdata']['columns_filter_category'];
		$name_func = $this->columns_categories[$cat]['get_data'];
		return $this->$name_func($type, $report_data);
	}

	function getHTML($cat = false, $report_data = NULL) {
		return $this->_get_data('html', $cat, $report_data);
	}

	function getCSV($cat = false, $report_data = NULL) {
		return $this->_get_data('csv', $cat, $report_data);
	}

	function getXLS($cat = false, $report_data = NULL) {
		return $this->_get_data('xls', $cat, $report_data);
	}

	/**
	 * load the report info into the class variables
	 */
	function _load() {

		$lang =& DoceboLanguage::createInstance('report', 'framework');

		$query_report = "
		SELECT report_name
		FROM ".$GLOBALS['prefix_lms']."_report
		WHERE id_report = '".$this->id_report."'";
		$re_report = sql_query($query_report);
		list($report_name) = sql_fetch_row($re_report);

		$this->report_name 		= $lang->def($report_name);
		$this->report_descr 	= $lang->def($report_name);
	}



	function play($jump_url, $back_url, $alluser, $org_chart_subdivision, $start_rime, $end_time) {

	}

	function &getAllUserIdst() {

		$p_dr 	= new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);

		$userlevelid = Docebo::user()->getUserLevelId();
		if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
			require_once(_base_.'/lib/lib.preference.php');
			$adminManager = new AdminPreference();
			$p_dr->intersectGroupFilter(
				$adminManager->getAdminTree(Docebo::user()->getIdSt())
			);
		}

		$re_people = $p_dr->getAllRowsIdst();

		$user_selected = array();
		if(!$re_people) return $user_selected;

		while(list($idst) = sql_fetch_row($re_people)) {

			$user_selected[$idst] = $idst;
		}

		return $user_selected;
	}



}



//little class for filter box management
addCss('style_filterbox');

class ReportBox {
	var $id = '';

	var $title = '';
	var $description = '';
	var $body = '';
	var $footer = '';
	var $title_css = 'filter_details';

	var $collapsed = false;

	var $show_collapse_cmd = false;

	function ReportBox($id='') {
		$this->id = $id;
	}

	//...

	function get() {
		$boxid = ($this->id!='' ? ' id="'.$this->id.'"' : '');
		$output = '<div'.$boxid.' class="filter_container'.($this->collapsed ? '' : '').'">';

		$output .=
			'<div class="fc_header">'.
			//'<div class="tl_corner"></div>'.
			'<h2 class="'.$this->title_css.'">'.$this->title.'</h2>'.
			//'<div class="cmd_expand"><a href="#">'.'Expand'.'</a></div>'.
			'';
		if($this->show_collapse_cmd) $output .= '<div class="cmd_collapse"><a href="#">Collapse</a></div>';
		$output .= ''.//'<div class="tr_corner"></div>'.
			'</div>';

		if($this->description) $output .= '<p class="fc_sec_header">'.$this->description.'</p>';

		$output .= '<div class="fc_body_filter">'.$this->body.'</div>';

		if ($this->footer!='')
		$output.= '<p class="fc_sec_header align_right">'.$this->footer.'</p>';

		$output .= '</div>';

		return $output;
	}
} 

class ReportSessionManager {

	var $data = NULL;


	function ReportSessionManager() {
		if (!$this->_is_initialized())
		$this->_initialize();
		$data =& $_SESSION[_REPORT_SESSION];
	}

	function _is_initialized() {
		return (isset($_SESSION[_REPORT_SESSION]));
	}

	function _initialize() {
		$_SESSION[_REPORT_SESSION] = array(
			_RS_ID            => false,
			_RS_ROWS_FILTER   => false,
			_RS_COLS_CATEGORY => false,
			_RS_COLS_FILTER   => false
		);
	}

	function setId($id) { $this->data[_RS_ID]=$id; }
	function getId() { return $this->data[_RS_ID]; }

	function setRowsFilter(&$data) { $this->data[_RS_ROWS_FILTER]=$data; }
	function getRowsFilter() { return $this->data[_RS_ROWS_FILTER]; }

	function setColsFilter(&$data) { $this->data[_RS_COLS_FILTER]=$data; }
	function getColsFilter() { return $this->data[_RS_COLS_FILTER]; }

	function setColsCategory($category) { $this->data[_RS_COLS_CATEGORY]=$category; }
	function getColsCategory() { return $this->data[_RS_COLS_CATEGORY]; }

	function flush() { $this->initialize();	}

}

?>