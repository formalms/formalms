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
 * esportazione dati relativi ai corsi   cid,codicecorso,anno
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
class DoceboConnector_CourseSap extends DoceboConnector {
  	
  	var $name = '';
  	
  	var $description = '';
  	
  	var $cid_field = 1;
  	
	var $export_field_list = '';
	
	var $_query_result;
	
	var $_readed_end;
	
	var $row_index;
	
	var $lang;
	
	var $first_row = false;
	
	var $acl_man;
	var $users_info;
	var $category_list;
	var $time_list;
	var $session_list;
	var $lastaccess_list;
	
	// name, type
 	var $all_cols = array( 
		array( 'userid', 'text' ), 
		array( 'cid', 'text' ), 
		array( 'cod_course', 'text' ), 
		array( 'year', 'text' ), 
	);
		
	var $default_cols = array(	'userid' 			=> '', 
								'cid' 				=> '', 
								'cod_course' 		=> '', 
								'year'              => '' );
	
	
	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnector_CourseSap( $params ) {
		
		require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
		require_once(_base_.'/lib/lib.userselector.php');
		require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
		
		$this->set_config( $params ); 
	}
	
	function get_config() {
		return array( 	'name' => $this->name,
						'description' => $this->description,
						'cid_field' => $this->cid_field );
	}
	
	function set_config( $params ) {
		
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
		if( isset($params['cid_field']) )			$this->cid_field = $params['cid_field'];
	}

	function get_configUI() {
		
		return new DoceboConnector_CourseSapUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {
		
		$this->lang = DoceboLanguage::createInstance('sap_report');
		
		// perform the query for data retriving
		
		$course_man = new Man_Course();
		
		$this->_query_result = false;
		$this->_readed_end = false;
		$this->row_index = 0;
		
		// find some information
		
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		
		$field_man 			= new FieldList();
		$field_man->setFieldEntryTable($GLOBALS['prefix_fw'].'_field_userentry');
		$this->_cid_list 	= $field_man->getAllFieldEntryData($this->cid_field);
		
		$query_course_user = "
		SELECT cu.idUser, c.idCourse, c.code, cu.date_complete
		FROM  ".$GLOBALS['prefix_lms']."_courseuser AS cu 
			JOIN ".$GLOBALS['prefix_lms']."_course AS c
		WHERE cu.idCourse = c.idCourse 
			AND cu.status = '"._CUS_END."' ";
		$this->_query_result = sql_query($query_course_user);
		
		if(!$this->_query_result) {
			
			$this->last_error = sql_error();
			return FALSE;
		}
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
	function get_type_name() {
		return "sap-user-report-connector";
	}	 
	
	/**
	 * Return the description of the connector 
	 **/
	function get_type_description() {
		return "connector for user statistics";
	}	 	

	/**
	 * Return the name of the connection
	 **/
	function get_name() {
		return $this->name;
	}	 	

	function get_description() {
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
	function get_cols_descripor() {
		
		$lang = DoceboLanguage::createInstance('userreport', 'lms');
		
		$col_descriptor = array();
		foreach($this->all_cols as $k => $col) {
				
			$col_descriptor[] = array(
				DOCEBOIMPORT_COLNAME 		=> $lang->def('_'.strtoupper($col[0])),
				DOCEBOIMPORT_COLID			=> $col[0],
				DOCEBOIMPORT_COLMANDATORY 	=> false,
				DOCEBOIMPORT_DATATYPE 		=> $col[1],
				DOCEBOIMPORT_DEFAULT 		=> ( isset($this->default_cols[$col[0]]) ? $this->default_cols[$col[0]] : '' ) 
			);
		}
		return $col_descriptor;
	}
	
	function get_first_row() {
		
		$default = array('','','','');
		if($this->first_row) return $this->first_row;
		if(!$this->_query_result) return $default;
		
		$result = sql_fetch_row($this->_query_result);
		
		if(!$result) {
			$this->_readed_end = true;
			return $default;
		}
		$this->row_index++;
		
		list($id_user, $id_course, $code, $date_complete) = $result;
		
		if(!isset($this->_cid_list[$id_user])) return $default;
		
		$row = array(
			$this->_cid_list[$id_user],
			$code, 
			substr($date_complete, 0, 4)
		);
		return $row;
	}
	
	function get_next_row() {
		
		//$this->export_field_list
		
		$default = array('','','','');
		if(!$this->_query_result) return false;
		if(!$result = sql_fetch_row($this->_query_result)) {
			$this->_readed_end = true;
			return FALSE;
		}
		$this->row_index++;
		
		list($id_user, $id_course, $code, $date_complete) = $result;
		
		if(!isset($this->_cid_list[$id_user])) return $default;
		if(!$date_complete) return $default;
		
		$row = array(
			$id_user,
			$this->_cid_list[$id_user], 
			$code, 
			substr($date_complete, 0, 4)
		);
		return $row;
	}
	
	function is_eof() {
		
		return $this->_readed_end;
	}
	
	function get_row_index() {
		
		return $this->row_index;
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
class DoceboConnector_CourseSapUI extends DoceboConnectorUI {
	
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
			
	function DoceboConnector_CourseSapUI( &$connector ) {
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'userreportconfig'; }
		
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
			if( $this->post_params['cid_field'] == '' ) 
				$this->post_params['cid_field'] = 0;

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
	  	
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		$field_man 			= new FieldList();
		$field_list = $field_man->getFlatAllFields();
		
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
	  	// --- cid field --------
	  	$out .= $this->form->getDropdown(	$this->lang->def('_CID'), 
											$this->_get_base_name().'_cid_field', 
											$this->_get_base_name().'[cid_field]', 
											$field_list, 
											$this->post_params['cid_field']);
		return $out;
	}
	
	
}

function coursesap_factory() {
	
	return new DoceboConnector_CourseSap(array());
}


?>
