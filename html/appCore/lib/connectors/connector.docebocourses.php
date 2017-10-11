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

require_once( dirname(__FILE__) . '/lib.connector.php' );
require_once( $GLOBALS['where_lms'] . '/lib/lib.course.php' );

/** 
 * class for define docebo courses connection to data source.
 * 
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.0
 * @author		Fabio Pirovano <fabio (@) docebo (.) com>
 * @access public
 **/
class DoceboConnectorDoceboCourses extends DoceboConnector {
	
  	var $last_error = "";
 	
 	// name, type
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
		array( 'show_extra_info', 'int' ), 
		array( 'show_rules', 'int' ), 
		array( 'date_begin', 'date' ), 
		array( 'date_end', 'date' ), 
		array( 'valid_time', 'int' ), 
		array( 'max_num_subscribe', 'int' ), 
		array( 'selling', 'int' ), 
		array( 'prize', 'int' ),
            //lp
		array( 'course_type', 'dropdown' ), 
		array( 'course_edition', 'int' ), 
            
	);
	
	var $mandatory_cols = array('code', 'name');
	
	var $default_cols = array(	'description' 		=> '', 
								'lang_code' 		=> '', 
								'status' 		=> '0', 
								'subscribe_method' 	=> '', 
								'permCloseLO' 		=> '', 
								'difficult' 		=> 'medium', 
								'show_progress' 	=> '1', 
								'show_time' 		=> '1', 
								'show_extra_info' 	=> '0', 
								'show_rules' 		=> '0', 
								'date_begin' 		=> '0000-00-00', 
								'date_end' 		=> '0000-00-00', 
								'valid_time' 		=> '0', 
								'max_num_subscribe'     => '0', 
								'selling' 		=> '0', 
								'prize' 		=> '',
                                                                'course_type'           => 'elearning',
                                                                'course_edition'        => '0');
	
	var $valid_filed_type 		= array( 'text','date','dropdown','yesno');
	
	var $dbconn 				= NULL;
	
	var $readwrite 				= 1; // read = 1, write = 2, readwrite = 3
	var $sendnotify = 1; // send notify = 1, don't send notify = 2
	
	var $name 					= "";
	var $description 			= "";
	
	var $on_delete = 1;  // unactivate = 1, delete = 2
	
	var $std_menu_to_assign 	= false;
	
		
	var $arr_id_inserted 		= array();
	
	function DoceboConnectorDoceboCourses($params) {
		
		$this->default_cols['lang_code'] = getDefaultLanguage();
		if( $params === NULL ) 
	  		return;	
	  	else
			$this->set_config( $params );	// connection
			
		
	}
	
	function get_config() {
		
		return array(	'name' => $this->name,
						'description' => $this->description,
						'readwrite' => $this->readwrite,
						'sendnotify' => $this->sendnotify, 
						'on_delete' => $this->on_delete, 
						'std_menu_to_assign' => $this->std_menu_to_assign );
	}
	
	function set_config( $params ) {
		
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];	
		if( isset($params['readwrite']) )			$this->readwrite = $params['readwrite'];
		if( isset($params['sendnotify']) )			$this->sendnotify = $params['sendnotify'];
		if( isset($params['on_delete']) )			$this->on_delete = $params['on_delete'];
		if( isset($params['std_menu_to_assign']) )	$this->std_menu_to_assign = $params['std_menu_to_assign'];	
	}
	
	function get_configUI() {
		return new DoceboConnectorDoceboCoursesUI($this);
	}
	
	function connect() {}
	
	function close() {}
	
	function get_type_name() { return "docebo-courses"; }	 
	
	function get_type_description() { return "connector to docebo courses"; }	 	

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
		
		return false;
	}
	
	function get_next_row() {
		
		return false;
	}
	
	function is_eof() {
		
		return false;
	}
	
	function get_row_index() {
		return false;
	}
	
	function get_tot_mandatory_cols() {
		
		return count($this->mandatory_cols);
	}
	
	function get_row_by_pk($pk) {
		
		$search_query = "
		SELECT idCourse, imported_from_connection 
		FROM ".$GLOBALS['prefix_lms']."_course 
		WHERE 1";
		foreach($pk as $fieldname => $fieldvalue) {
			
			$search_query .= " AND $fieldname = '".addslashes($fieldvalue)."'";
		}
		
		$re_course = sql_query($search_query);
		if(sql_num_rows($re_course) == 0) return 0;
		if(!$re_course) return false;
		list($id_course, $imported_from) = sql_fetch_row($re_course);
		
		if($this->get_name() != $imported_from) return 'jump';
		
		return $id_course;
	}
	
	function add_row( $row, $pk ) {
		
		$id_course = false;
		
		if($row['code'] == '') 			$row['code'] = $this->default_cols['code'];
		if($row['name'] == '') 			$row['name'] = $this->default_cols['name'];
		if($row['description'] == '') 		$row['description'] = $this->default_cols['description'];
		if($row['lang_code'] == '') 		$row['lang_code'] = $this->default_cols['lang_code'];
		if($row['status'] == '') 		$row['status'] = $this->default_cols['status'];
		if($row['subscribe_method'] == '') 	$row['subscribe_method'] = $this->default_cols['subscribe_method'];
		if($row['permCloseLO'] == '') 		$row['permCloseLO'] = $this->default_cols['permCloseLO'];
		if($row['difficult'] == '') 		$row['difficult'] = $this->default_cols['difficult'];
		if($row['show_progress'] == '') 	$row['show_progress'] = $this->default_cols['show_progress'];
		if($row['show_time'] == '') 		$row['show_time'] = $this->default_cols['show_time'];
		if($row['show_extra_info'] == '') 	$row['show_extra_info'] = $this->default_cols['show_extra_info'];
		if($row['show_rules'] == '') 		$row['show_rules'] = $this->default_cols['show_rules'];
		if($row['date_begin'] == '') 		$row['date_begin'] = $this->default_cols['date_begin'];
		if($row['date_end'] == '') 		$row['date_end'] = $this->default_cols['date_end'];
		if($row['valid_time'] == '') 		$row['valid_time'] = $this->default_cols['valid_time'];
		if($row['max_num_subscribe'] == '')     $row['max_num_subscribe'] = $this->default_cols['max_num_subscribe'];
		if($row['prize'] == '') 		$row['prize'] = $this->default_cols['prize'];
		if($row['selling'] == '') 		$row['selling'] = $this->default_cols['selling'];
		if($row['course_type'] == '') 		$row['course_type'] = $this->default_cols['course_type'];
                if($row['course_edition'] == '') 	$row['course_edition'] = $this->default_cols['course_edition'];
		// check if the course identified by the pk alredy exits
		$id_course = $this->get_row_by_pk($pk);
		if($id_course === false) {
			$this->last_error = 'Error in search query : ( '.sql_error().' )';
			return false;
		}
		if($id_course === 'jump') return true;
		
		$is_add = false;
		if($id_course === 0) {
			
			$is_add = true;
			// course is to add
			$query_course = "
			INSERT INTO ".$GLOBALS['prefix_lms']."_course 
			SET idCategory = '0', 
				code = '".addslashes($row['code'])."', 
				name = '".addslashes($row['name'])."', 
				description = '".addslashes($row['description'])."', 
				lang_code = '".$row['lang_code']."', 
				status = '".$row['status']."', 
				subscribe_method = '".$row['subscribe_method']."',
				permCloseLO = '".$row['permCloseLO']."', 
				difficult = '".$row['difficult']."', 
				show_progress = '".$row['show_progress']."', 
				show_time = '".$row['show_time']."', 
				show_extra_info = '".$row['show_extra_info']."', 
				show_rules = '".$row['show_rules']."', 
				date_begin = '".$row['date_begin']."', 
				date_end = '".$row['date_end']."', 
				valid_time = '".$row['valid_time']."',
				max_num_subscribe = '".$row['max_num_subscribe']."', 
				prize = '".$row['prize']."',
				selling = '".$row['selling']."',                                    
				course_type = '".$row['course_type']."', 
				course_edition = '".$row['course_edition']."',                                     
				imported_from_connection = '".$this->get_name()."'"; 
			
			if(!sql_query($query_course)) {
				$this->last_error = 'Error in insert query : ( '.sql_error().' )'
					.'<!-- '.$query_course.' -->';
				return false;
			}
			$id_course = sql_insert_id();
			
			// import the menu
			
			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
				
				$re &= sql_query("
				INSERT INTO ".$GLOBALS['prefix_fw']."_admin_course 
				( id_entry, type_of_entry, idst_user ) VALUES 
				( '".$id_course."', 'course', '".getLogUserId()."') ");
			}
			
			//if the scs exist create a room
			if($GLOBALS['where_scs'] !== false) {
			
				require_once($GLOBALS['where_scs'].'/lib/lib.room.php');
				
				$rules = array(
							'room_name' => $row['name'], 
							'room_type' => 'course', 
							'id_source' => $id_course );
				$admin_rules = getAdminRules();
				$rules = array_merge($rules, $admin_rules);
				$re = insertRoom($rules);
			}
			$course_idst = DoceboCourse::createCourseLevel($id_course);
			
			require_once($GLOBALS['where_lms'].'/lib/lib.manmenu.php');
			
			if(!createCourseMenuFromCustom($this->std_menu_to_assign, $id_course, $course_idst)) {
				$this->last_error = 'Error in menu assignament';
				return false;
			}
			
		} else {
			// course is to update
			
			$query_course = "
			UPDATE ".$GLOBALS['prefix_lms']."_course 
			SET code = '".addslashes($row['code'])."', 
				name = '".addslashes($row['name'])."', 
				description = '".addslashes($row['description'])."', 
				lang_code = '".$row['lang_code']."', 
				status = '".$row['status']."', 
				subscribe_method = '".$row['subscribe_method']."',
				permCloseLO = '".$row['permCloseLO']."', 
				difficult = '".$row['difficult']."', 
				show_progress = '".$row['show_progress']."', 
				show_time = '".$row['show_time']."', 
				show_extra_info = '".$row['show_extra_info']."', 
				show_rules = '".$row['show_rules']."', 
				date_begin = '".$row['date_begin']."', 
				date_end = '".$row['date_end']."', 
				valid_time = '".$row['valid_time']."',
				max_num_subscribe = '".$row['max_num_subscribe']."', 
				prize = '".$row['prize']."',
				selling = '".$row['selling']."',
                                course_type = '".$row['course_type']."', 
				course_edition = '".$row['course_edition']."'
			WHERE idCourse = '".$id_course."'";
			
			if(!sql_query($query_course)){
				$this->last_error = 'Error in update query : ( '.sql_error().' )'
					.'<!-- '.$query_course.' -->';
				return false;
			}
		}
		if($id_course != false) {
			
			if($this->cache_inserted)
				$this->arr_id_inserted[] = $id_course;
			
			if($this->sendnotify == 1) {
				// send notify
				if($is_add) {
					
					require_once(_base_ . '/lib/lib.eventmanager.php');
		
					$msg_composer = new EventMessageComposer();
					
					$msg_composer->setSubjectLangText('email', '_ALERT_SUBJECT', false);
					$msg_composer->setBodyLangText('email', '_ALERT_TEXT', array(	'[url]' => Get::sett('url'), 
																					'[course_code]' => $row['code'], 
																					'[course]' => $row['name'] ) );
																						
					$msg_composer->setBodyLangText('sms', '_ALERT_TEXT_SMS', array(	'[url]' => Get::sett('url'), 
																					'[course_code]' => $row['code'], 
																					'[course]' => $row['name'] ) );
					
					require_once($GLOBALS['where_lms'] . '/lib/lib.course.php'); 
					$course_man = new Man_Course();
					$recipients = $course_man->getIdUserOfLevel($id_course);
					createNewAlert(	'CoursePropModified', 
									'course', 
									'add', 
									'1', 
									'Inserted course '.$_POST['course_name'], 
									$recipients, 
									$msg_composer );
				}
			}
			return true;
		}
		$this->last_error = 'Unknow error';
		return false;
	}
	
	function _delete_by_id($id_course) {
		
		if( $this->on_delete == '1' ) {
			
			// unactivate course
			$query_course = "
			UPDATE ".$GLOBALS['prefix_lms']."_course 
			SET status = '0'
			WHERE idCourse = '".$id_course."'";
			if(sql_query($query_course)) return true;
			else return false;
		} else {
			
			require_once($GLOBALS['where_lms'].'/admin/modules/course/course.php');
			// delete the course
			if(removeCourse($id_course)) return true;
			else return false;
		}
	}
	
	function delete_bypk( $pk ) {
		
		// check if the course identified by the pk alredy exits
		$id_course = $this->get_row_by_pk($pk);
		if($id_course === 'jump') return true;
		if($id_course === false) return false;
		if($id_course === 0) return true;
		
		return $this->_delete_by_id($id_course);
	}
	
	function delete_all_filtered( $arr_pk ) {
		
		$re  = true;
		foreach($arr_pk as $k => $pk) {
			
			$re &= $this->delete_bypk( $pk );
		}
		return $re;
	}

	function delete_all_notinserted() {
		
		$search_query = "
		SELECT idCourse
		FROM ".$GLOBALS['prefix_lms']."_course 
		WHERE imported_from_connection = '".$this->get_name()."'";
		if(!empty($this->arr_id_inserted)) {
		
			$search_query .= " AND idCourse NOT IN (".implode($this->arr_id_inserted , ',').") ";
		}
		$re_course = sql_query($search_query);
		if(!$re_course) return 0;
		$counter = 0;
		while(list($id_course) = sql_fetch_row($re_course)) {
			
			if($this->_delete_by_id($id_course)) $counter++;
		}
		return $counter;
	}
	
	function get_error() { return $this->last_error; }
	
}

/** 
 * class for define docebo courses UI connection
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.0
 * @author		Fabio Pirovano <fabio (@) docebo (.) com>
 * @access public
 **/
class DoceboConnectorDoceboCoursesUI extends DoceboConnectorUI {
	
	var $connector 		= NULL;
	var $post_params 	= NULL;
	var $sh_next 		= TRUE;
	var $sh_prev 		= FALSE;
	var $sh_finish 		= FALSE;
	var $step_next 		= '';
	var $step_prev 		= '';
	var $available_menu = array();
	
	function DoceboConnectorDoceboCoursesUI( &$connector ) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.manmenu_course.php');
		
		$this->available_menu = getAllCustom();
		
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'docebocoursesuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
		
	function parse_input( $get, $post ) {
		
		if( !isset($post[$this->_get_base_name()]) ) {
			
			// first call - first step, initialize variables
			$this->post_params = $this->connector->get_config();
			$this->post_params['step'] = '0';
			$this->post_params['old_name'] = $this->post_params['name'];
			if( $this->post_params['name'] == '' ) 
				$this->post_params['name'] = $this->lang->def('_CONN_NAME_EXAMPLE');
			if( $this->post_params['std_menu_to_assign'] == '' ) 
				$this->post_params['std_menu_to_assign'] = key($this->available_menu);

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
													$this->lang->def('_WRITE') => '2',
													$this->lang->def('_READWRITE') => '3'), 
											$this->post_params['readwrite']);
		// ---- on delete -> delete or unactivate -----
		$out .= $this->form->getRadioSet( 	$this->lang->def('_CANCELED_COURSES'), 
		  									$this->_get_base_name().'_on_delete', 
											$this->_get_base_name().'[on_delete]',
											array( 	$this->lang->def('_DEACTIVATE')  => '1', 
													$this->lang->def('_DEL') => '2'), 
											$this->post_params['on_delete']);
	  	// ---- access type read/write -----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_SENDNOTIFY'), 
		  									$this->_get_base_name().'_sendnotify', 
											$this->_get_base_name().'[sendnotify]',
											array( 	$this->lang->def('_SEND')  => '1', 
													$this->lang->def('_DONTSEND') => '2'), 
											$this->post_params['sendnotify']);
													
		// ---- standard menu to use ----
	  	$out .= $this->form->getDropdown( 	$this->lang->def('_STANDARD_MENU'), 
		  									$this->_get_base_name().'_std_menu_to_assign', 
											$this->_get_base_name().'[std_menu_to_assign]',
											$this->available_menu, 
											$this->post_params['std_menu_to_assign']);	
		return $out;
	}
}

function docebocourses_factory() {
	return new DoceboConnectorDoceboCourses(array());
}


?>
