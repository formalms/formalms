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
 * @package admin-core
 * @subpackage io-operation
 */
 
/**
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	$id$
 * @author		Pirovano Fabio <fabio (@) docebo (.) com>
**/

require_once( dirname(__FILE__) . '/lib.connector.php' );

/** 
 * class for define user report connection.
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.0
 * @author		Pirovano Fabio <fabio (@) docebo (.) com>
 **/
class DoceboConnectorCourseExport extends DoceboConnector {
  	
  	var $name = '';
  	
  	var $description = '';
  	
	var $export_field_list = '';
	
	var $_query_result;
	
	var $first_row_header = '1';
	
	var $_readed_end;
	
	var $lang;
	
	var $first_row = false;
	
	var $today;
	
	var $all_data;
	
	var $position;
	
	var $tot_row;
	
	// name, type 
        // COURSE_TYPE, COURSE_EDITION
 	var $all_cols = array( 
		array( 'code', 'text' ), 
		array( 'name', 'text' ), 
		array( 'description', 'text' ), 
		array( 'lang_code', 'text' ), 
		array( 'status', 'text' ), 
		array( 'subscribe_method', 'int' ), 
		array( 'permCloseLO', 'int' ), 
		array( 'difficult', 'dropdown' ), 
		array( 'show_progress', 'int' ), 
		array( 'show_time', 'int' ), 
		array( 'medium_time', 'int'),
		array( 'show_extra_info', 'int' ), 
		array( 'show_rules', 'int' ), 
		array( 'date_begin', 'date' ), 
		array( 'date_end', 'date' ), 
		array( 'valid_time', 'int' ), 
		array( 'min_num_subscribe', 'int' ),
		array( 'max_num_subscribe', 'int' ), 
		array( 'selling', 'int' ), 
		array( 'prize', 'int' ),
		array( 'create_date', 'date' ),
		array( 'id_course', 'int' ), 
		array( 'course_type', 'dropdown' ), 
		array( 'course_edition', 'int' )             
	);
		
	var $default_cols = array(	'description' 		=> '', 
								'lang_code' 		=> '', 
								'status' 		=> '0', 
								'subscribe_method' 	=> '', 
								'permCloseLO' 		=> '', 
								'difficult' 		=> 'medium', 
								'show_progress' 	=> '1', 
								'show_time' 		=> '1', 
								'medium_time'		=> '0',
								'show_extra_info' 	=> '0', 
								'show_rules' 		=> '0', 
								'date_begin' 		=> '0000-00-00', 
								'date_end' 		=> '0000-00-00', 
								'valid_time' 		=> '0', 
								'min_num_subscribe'     => '0', 
								'max_num_subscribe'     => '0', 
								'selling' 		=> '0', 
								'prize' 		=> '',
								'create_date'		=> '0000-00-00',
								'id_course'		=> '0',
                                                                'course_type'           => 'elearning',
                                                                'course_edition'        => '0');         

	
	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnectorCourseExport( $params ) {
		
		require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
		require_once(_base_.'/lib/lib.userselector.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		
		$this->set_config( $params );
	}
	
	function get_config() {
		return array('name' =>$this->name,
			'description' => $this->description,
			'first_row_header' => $this->first_row_header
		);
	}
	
	function set_config( $params ) {
		
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
		if( isset($params['first_row_header']) )	$this->first_row_header = (int)$params['first_row_header'];
	}

	function get_configUI() {
		
		return new DoceboConnectorCourseExportUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect()
	{
		$this->lang = DoceboLanguage::createInstance('rg_report');
		
		$this->_readed_end = false;
		$this->today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$this->position = 1;
		
		$course_man = new Man_Course();
		
		// perform the query for data retriving
		
		$query =	"SELECT COUNT(*)"
					." FROM ".$GLOBALS['prefix_lms']."_course";
		
		list($number_of_course) = sql_fetch_row(sql_query($query));
		
		$this->tot_row = $number_of_course;
		
		$query = 	"SELECT `code`, `name`, `description`, `lang_code`, `status`, `subscribe_method`, `mediumTime`, `permCloseLO`, `difficult`, `show_progress`, `show_time`, `show_extra_info`, `show_rules`, `date_begin`, `date_end`, `valid_time`, `max_num_subscribe`, `min_num_subscribe`, `selling`, `prize`, `create_date`, `idCourse`, `course_type`, `course_edition`"
					." FROM ".$GLOBALS['prefix_lms']."_course"
					." ORDER BY name";
		
		$result = sql_query($query);
		
		$data = array();
		
		$counter = 0;
		
		if($this->first_row_header)
		{
			$data[$counter][] = 'code';
			$data[$counter][] = 'name';
			$data[$counter][] = 'description';
			$data[$counter][] = 'lang_code';
			$data[$counter][] = 'status';
			$data[$counter][] = 'subscribe_method';
			$data[$counter][] = 'mediumTime';
			$data[$counter][] = 'permCloseLO';
			$data[$counter][] = 'difficult';
			$data[$counter][] = 'show_progress';
			$data[$counter][] = 'show_time';
			$data[$counter][] = 'show_extra_info';
			$data[$counter][] = 'show_rules';
			$data[$counter][] = 'date_begin';
			$data[$counter][] = 'date_end';
			$data[$counter][] = 'valid_time';
			$data[$counter][] = 'max_num_subscribe';
			$data[$counter][] = 'min_num_subscribe';
			$data[$counter][] = 'selling';
			$data[$counter][] = 'prize';
			$data[$counter][] = 'create_date';
                        $data[$counter][] = 'idCourse';
			$data[$counter][] = 'course_type';
			$data[$counter][] = 'course_edition';
                        
			$counter++;
		}
		
		while($row = sql_fetch_array($result))
		{
			$data[$counter][] = $row[0];
			$data[$counter][] = $row[1];
			$data[$counter][] = $row[2];
			$data[$counter][] = $row[3];
			$data[$counter][] = $row[4];
			$data[$counter][] = $row[5];
			$data[$counter][] = $row[6];
			$data[$counter][] = $row[7];
			$data[$counter][] = $row[8];
			$data[$counter][] = $row[9];
			$data[$counter][] = $row[10];
			$data[$counter][] = $row[11];
			$data[$counter][] = $row[12];
			$data[$counter][] = $row[13];
			$data[$counter][] = $row[14];
			$data[$counter][] = $row[15];
			$data[$counter][] = $row[16];
			$data[$counter][] = $row[17];
			$data[$counter][] = $row[18];
			$data[$counter][] = $row[19];
			$data[$counter][] = $row[20];
			$data[$counter][] = $row[21];
			$data[$counter][] = $row[22];
			$data[$counter][] = $row[23];                        
			$counter++;
		}
		$counter--;
		$this->all_data = $data;
		
		return TRUE;		
	}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {
		
		return TRUE;	
	}

	/**
	 * Return the type of the connector 
	 **/
	function get_type_name()
	{
		return "course-export-connector";
	}	 
	
	/**
	 * Return the description of the connector 
	 **/
	function get_type_description()
	{
		return "connector for course export";
	}	 	

	/**
	 * Return the name of the connection
	 **/
	function get_name()
	{
		return $this->name;
	}	 	

	function get_description()
	{
		return $this->description;
	}	 	

	function is_readonly() { return true; }

	function is_writeonly() { return false; }
	
	function get_tot_cols() { return count($this->cols_descriptor); }
	
	/**
	 * @return array the array of columns descriptor
	 *				- DOCEBOIMPORT_COLNAME => string the name of the column
	 *				- DOCEBOIMPORT_COLID => string the id of the column (optional,
	 *										 same as COLNAME if not given)
	 *				- DOCEBOIMPORT_COLMANDATORY => bool TRUE if col is mandatory
	 *				- DOCEBOIMPORT_DATATYPE => the data type of the column
	 *				- DOCEBOIMPORT_DEFAULT => the default value for the column (Optional)
	 * For readonly connectos only 	DOCEBOIMPORT_COLNAME and DOCEBOIMPORT_DATATYPE
	 * are required	 				 
	 **/
	function get_cols_descripor()
	{
		$lang = DoceboLanguage::createInstance('userreport', 'lms');
		
		$col_descriptor = array();
		
		foreach($this->all_cols as $k => $col) {
				
			
			
			$col_descriptor[] = array(
				DOCEBOIMPORT_COLNAME 		=> $lang->def('_'.strtoupper($col[0])),
				DOCEBOIMPORT_COLID			=> $col[0],
				DOCEBOIMPORT_COLMANDATORY 	=> false,
				DOCEBOIMPORT_DATATYPE 		=> $col[1],
				DOCEBOIMPORT_DEFAULT 		=> ( $in = array_search($col[0], $this->default_cols) === FALSE 
													? '' 
													: $this->default_cols[$in] )
			);
		}
		return $col_descriptor;
	}
	
	function get_first_row()
	{
		if($this->first_row) return $this->first_row;
		$this->first_row = $this->all_data[0];
		return $this->first_row;
	}
	
	function get_next_row()
	{
		$row = array();
		if($this->first_row_header)
		{
			if($this->tot_row >= $this->position)
			{
				$row = $this->all_data[$this->position];
				
				$this->position++;
				
				return $row;
			}
			else
			{
				$this->_readed_end = true;
				return false;
			}
		}
		else
		{
			if($this->tot_row > $this->position)
			{
				$row = $this->all_data[$this->position];
				
				$this->position++;
				
				return $row;
			}
			else
			{
				$this->_readed_end = true;
				return false;
			}
		}
	}
	
	function is_eof()
	{
		return $this->_readed_end;
	}
	
	function get_row_index()
	{
		return $this->position;
	}
	
	function get_tot_mandatory_cols() { return 0; }
	
	function get_error() { return $this->last_error; }

}


/**
 * The configurator for csv connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorCourseExportUI extends DoceboConnectorUI {
	
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
			
	function DoceboConnectorCourseExportUI( &$connector ) {
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'coursereportuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
	/** 
	 * All post fields are in array 'csvuiconfig'
	 **/	 	
	function parse_input( $get, $post ) {

		if( !isset($post[$this->_get_base_name()]) ) {
			
			// first call - first step, initialize variables
			$this->post_params = $this->connector->get_config();
			
			$this->post_params['step'] = '0';
			$this->post_params['old_name'] = $this->post_params['name'];
			if( $this->post_params['name'] == '' ) 
				$this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');

		} else {
			// get previous values
			$this->post_params = Util::unserialize(urldecode($post[$this->_get_base_name()]['memory']));
			$arr_new_params = $post[$this->_get_base_name()];
			// overwrite with the new posted values
			foreach($arr_new_params as $key => $val) {
				if( $key != 'memory' && $key != 'reset' ) {
					$this->post_params[$key] = stripslashes($val);
				}
			}			
		}		
		
		$this->_load_step_info();
	}

	function _set_step_info( $next, $prev, $sh_next, $sh_prev, $sh_finish ) {
		$this->step_next = $next;
		$this->step_prev = $prev;
		$this->sh_next = $sh_next;
		$this->sh_prev = $sh_prev;
		$this->sh_finish = $sh_finish;
	}

	function _load_step_info() {
	
		$this->_set_step_info( '1', '0', FALSE, FALSE, TRUE );
	}
	
	function go_next() {
		$this->post_params['step'] = $this->step_next;
		$this->_load_step_info();
	}

	function go_prev() {
		$this->post_params['step'] = $this->step_prev;
		$this->_load_step_info();		
	}
	
	function go_finish() {
		$this->filterParams($this->post_params);
		$this->connector->set_config( $this->post_params );
	}
	
	function show_next() { return $this->sh_next; }
	function show_prev() { return $this->sh_prev; }
	function show_finish() { return $this->sh_finish; }

	function get_htmlheader() {
		return '';
	}
	
	function get_html() {
	  	$out = '';
		switch( $this->post_params['step'] ) {
			case '0':
				$out .= $this->_step0();
			break;
		}
		// save parameters
		$out .=  $this->form->getHidden($this->_get_base_name().'_memory',
										$this->_get_base_name().'[memory]',
										urlencode(Util::serialize($this->post_params)) );
		
		return $out;
	}
	
	function _step0() {
	  	
	  	$out = '';
	  	
	  	// ---- name -----
	  	$out = $this->form->getTextfield(	$this->lang->def('_NAME'), 
											$this->_get_base_name().'_name', 
											$this->_get_base_name().'[name]', 
											255, 
											$this->post_params['name']);
		// ---- description -----
		$out .= $this->form->getSimpleTextarea( $this->lang->def('_DESCRIPTION'), 
											$this->_get_base_name().'_description', 
											$this->_get_base_name().'[description]', 
											$this->post_params['description'] );
		
		$out .= $this->form->getRadioSet( 	$this->lang->def('_FIRST_ROW_HEADER'),
											$this->_get_base_name().'_first_row_header',
											$this->_get_base_name().'[first_row_header]',
											array( 	$this->lang->def('_YES') => '1',
													$this->lang->def('_NO') => '0'),
											$this->post_params['first_row_header']);
		
		return $out;
	}
	
	
}

function courseexport_factory() {
	
	return new DoceboConnectorCourseExport(array());
}


?>