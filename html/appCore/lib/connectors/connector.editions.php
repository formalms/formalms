<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

require_once( dirname(__FILE__) . '/lib.connector.php' );
require_once( _lms_ . '/lib/lib.course.php' );
require_once( _lms_ . '/lib/lib.edition.php' );

/** 
 * class for define editions connection to data source.
 * 
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.0
 * @access public
 **/
class ConnectorEditions extends DoceboConnector {
	
  	var $last_error = "";
 	
 	// name, type
 	var $all_cols = array( 
        array( 'id_edition', 'int' ),
		array( 'id_course', 'int' ),
		array( 'code', 'text' ), 
		array( 'name', 'text' ), 
		array( 'description', 'text' ), 
		array( 'status', 'text' ), 
		array( 'date_begin', 'date' ), 
		array( 'date_end', 'date' ), 
		array( 'max_num_subscribe', 'int' ), 
		array( 'min_num_subscribe', 'int' ), 
		array( 'price', 'int' ), 
		array( 'overbooking', 'int' ), 
		array( 'can_subscribe', 'int' ), 
		array( 'sub_date_begin', 'date' ), 
		array( 'sub_date_end', 'date' )
	);
	
	var $mandatory_cols = array('id_edition', 'id_course');
	
	var $default_cols = array(
		'code' => '' , 
		'name' => '' , 
		'description' => '' , 
		'status' => '0' , 
		'date_begin' => '' , 
		'date_end' => '' , 
		'max_num_subscribe' => '' , 
		'min_num_subscribe' => '' , 
		'price' => '' , 
		'overbooking' => '0' , 
		'can_subscribe' => '0' , 
		'sub_date_begin' => '' , 
		'sub_date_end' => ''
    );
	
	var $valid_filed_type 		= array( 'text','date','dropdown','yesno');
	
	var $dbconn 				= NULL;
	
	var $readwrite 				= 1; // read = 1, write = 2, readwrite = 3
	//var $sendnotify = 1; // send notify = 1, don't send notify = 2
	
	var $name 					= "";
	var $description 			= "";
	
	//var $on_delete = 1;  // unactivate = 1, delete = 2
	
		
	var $arr_id_inserted 		= array();
	
	var $first_row_header = '1';
	
	var $_readed_end;
	
	var $lang;
	
	var $first_row = false;
	
	var $today;
	
	var $all_data;
	
	var $position;
	
	var $tot_row;
	
	function ConnectorEditions($params) {
		
		if( $params === NULL ) 
	  		return;	
	  	else
			$this->set_config( $params );	// connection
			
		
	}
	
	function get_config() {
		
		return array(	'name' => $this->name,
						'description' => $this->description,
						'readwrite' => $this->readwrite,
                        'first_row_header' => $this->first_row_header/*,
						'sendnotify' => $this->sendnotify, 
						'on_delete' => $this->on_delete*/);
	}
	
	function set_config( $params ) {
		
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];	
		if( isset($params['readwrite']) )			$this->readwrite = $params['readwrite'];
		if( isset($params['first_row_header']) )	$this->first_row_header = $params['first_row_header'];
		//if( isset($params['sendnotify']) )			$this->sendnotify = $params['sendnotify'];
		//if( isset($params['on_delete']) )			$this->on_delete = $params['on_delete'];
	}
	
	function get_configUI() {
		return new ConnectorEditionsUI($this);
	}
	
	function connect() {
        
		$this->lang = DoceboLanguage::createInstance('rg_report');
		
		$this->_readed_end = false;
		$this->today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$this->position = 1;
        
        $query = "SELECT COUNT(*) FROM %lms_course_editions";
		
		list($tot_row) = sql_fetch_row(sql_query($query));
		
		$this->tot_row = $tot_row;
		
		$query =  " SELECT *"
                . " FROM %lms_course_editions ce";
		
		$result = sql_query($query);
		
		$data = array();
        $fields = array();
        
        while ($col = sql_fetch_field($result)) {
            $fields[] = $col->name;
        }
        
        if ($this->first_row_header) {
            $data[0] = $fields;
        }

        while($row = sql_fetch_array($result)) {
            $_data = array();
            foreach($fields as $field) {
                switch($field) {
                    case 'date_end':
                        $row[$field] = Format::date($row[$field], 'datetime');
                        break;
                    case 'date_begin':
                    case 'sub_date_begin':
                    case 'sub_date_end':
                        $row[$field] = Format::date($row[$field], 'date');
                        break;
                    default: break;
                }
                $_data[] = $row[$field];
            }
            $data[] = $_data;
		}
        
		$this->all_data = $data;
		
		return true;
    }
	
	function close() {}
	
	function get_type_name() { return "course-editions"; }	 
	
	function get_type_description() { return "Connector to course editions"; }	 	

	function get_name() { return $this->name; }	 	

	function get_description() { return $this->description; }	 	

	function is_readonly() { return (bool)($this->readwrite & 1); }

	function is_writeonly() { return (bool)($this->readwrite & 2); }
	
	function get_tot_cols(){
		return count( $this->all_cols );
	}
	
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
		
		$lang = DoceboLanguage::createInstance('course', 'lms');
		
		$col_descriptor = array();
		foreach($this->all_cols as $k => $col) {
				
			$col_descriptor[] = array(
				DOCEBOIMPORT_COLNAME 		=> $lang->def('_'.strtoupper($col[0])),
				DOCEBOIMPORT_COLID			=> $col[0],
				DOCEBOIMPORT_COLMANDATORY 	=> ( array_search($col[0], $this->mandatory_cols) === FALSE 
													? false 
													: true ),
				DOCEBOIMPORT_DATATYPE 		=> $col[1],
				DOCEBOIMPORT_DEFAULT 		=> ( $in = array_search($col[0], $this->default_cols) === FALSE 
													? '' 
													: $this->default_cols[$in] )
			);
		}
		return $col_descriptor;
	}

	function get_first_row() {
		if($this->first_row) return $this->first_row;
		$this->first_row = $this->all_data[0];
		return $this->first_row;
    }
	

	function get_next_row() {
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
	

	function is_eof() {
		return $this->_readed_end;
	}
	

	function get_row_index() {
		return $this->position;

    }
	
	function get_tot_mandatory_cols() {
		
		return count($this->mandatory_cols);
	}

	function add_row( $row, $pk ) {
		
		$id_edition = false;
        
        foreach($this->default_cols as $key => $default) {
            if($row[$key] == '') $row[$key] = $default;
        }
        
        $id_edition = $row['id_edition'];
        
        $id_course = self::getIdCourseFromCode($row['id_course']);
        $id_edition = self::getIdEditionFromCode($row['code']);
        $date_end_datetime = $row['date_end'] . " 00:00"; //for database type 
        if ($id_course != null) {
        $em = new EditionManager();
		
		$is_add = false;
		if(!$id_edition) {
			
			$is_add = true;
            
				$em->insertEdition($id_course, $row['code'], Util::add_slashes($row['name']), Util::add_slashes($row['description']), $row['status'], $row['max_num_subscribe'], $row['min_num_subscribe'], $row['price'], $row['date_begin'], $date_end_datetime, $row['overbooking'], $row['can_subscribe'], $row['sub_date_begin'], $row['sub_date_end'], $id_edition);
				$id_edition = self::getIdEditionFromCode($row['code']);

		} else {
			// edition is to update
			
                $em->modEdition($id_edition, $row['code'], Util::add_slashes($row['name']), Util::add_slashes($row['description']), $row['status'], $row['max_num_subscribe'], $row['min_num_subscribe'], $row['price'], $row['date_begin'], $date_end_datetime, $row['overbooking'], $row['can_subscribe'], $row['sub_date_begin'], $row['sub_date_end']);
		}
		if($id_edition != false) {
			
			if($this->cache_inserted)
				$this->arr_id_inserted[] = $id_edition;
			return true;
		}
        }
		$this->last_error = 'Unknow error';
		return false;
	}
    
	function get_error() { return $this->last_error; }

          function getIdCourseFromCode($code) {
            $query = "";
            $ret = array();
            $query = "select idCourse from %lms_course where code = '" . $code . "'";
            $rs = sql_query($query);
            $ret = sql_fetch_array($rs)['idCourse'];
            //course_edition must be 1
            if ($ret != null) {
                $query = "";
                $query = "update %lms_course set course_edition=1 where idCourse=" . $ret . " and course_edition<>1";
                sql_query($query);
            }
            return $ret;
        }
  
  function getIdEditionFromCode($edition_code) {
    $query = "";
    $ret = array();
    $query = "select id_edition from %lms_course_editions where code = '" . $edition_code . "'" ;
    $rs = sql_query($query);
    $ret = sql_fetch_array($rs)['id_edition'];
    return $ret;
  }
	
}

/** 
 * class for define editions UI connection
 * @package		forma.lms
 * @subpackage	ImportExport
 * @version 	1.0
 * @access public
 **/
class ConnectorEditionsUI extends DoceboConnectorUI {
	
	var $connector 		= NULL;
	var $post_params 	= NULL;
	var $sh_next 		= TRUE;
	var $sh_prev 		= FALSE;
	var $sh_finish 		= FALSE;
	var $step_next 		= '';
	var $step_prev 		= '';
	
	function ConnectorEditionsUI( &$connector ) {
				
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'editionsuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
		
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
	  	// ---- access type read/write -----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_ACCESSTYPE'), 
		  									$this->_get_base_name().'_readwrite', 
											$this->_get_base_name().'[readwrite]',
											array( 	$this->lang->def('_READ')  => '1', 
													$this->lang->def('_WRITE') => '2'), 
											$this->post_params['readwrite']);
		
		$out .= $this->form->getRadioSet( 	$this->lang->def('_FIRST_ROW_HEADER'),
											$this->_get_base_name().'_first_row_header',
											$this->_get_base_name().'[first_row_header]',
											array( 	$this->lang->def('_YES') => '1',
													$this->lang->def('_NO') => '0'),
											$this->post_params['first_row_header']);
		return $out;
	}
}

function editions_factory() {
	return new ConnectorEditions(array());
}
