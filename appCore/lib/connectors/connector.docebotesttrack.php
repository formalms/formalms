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
require_once(_base_.'/lib/lib.eventmanager.php');

/** 
 * class for define docebo course subscription connection to data source.
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.0
 * @author		Fabio Pirovano <fabio (@) docebo (.) com>
 * @access public
 **/
class DoceboConnector_DoceboTestTrack extends DoceboConnector {
	
  	var $last_error = "";
 	
 	var $acl_man = false;
 	
 	var $sub_man = false;
 	
 	// name, type
 	var $all_cols = array( 
		array( 'userid', 'text' ),
		array( 'course_code', 'text' ),
		array( 'test_name', 'text' ),
		array( 'user_score', 'int' ),
		array( 'date_attempt', 'date' )
	);
	
	var $mandatory_cols = array('userid', 'course_code', 'test_name');
	
	var $default_cols = array( 	'userid' => '', 
								'course_code' => '', 
								'test_name' => '',
								'user_score' => 0,
								'date_subscription' => false );
	
	var $name 					= "";
	var $description 			= "";

		
	var $course_cache = false;
	var $userid_cache = false;
	
	var $test_cache = false;
	
	/**
	 * constructor
	 * @param array params	 
	 **/
	function DoceboConnector_DoceboTestTrack( $params ) {
		
		require_once($GLOBALS['where_lms'].'/lib/lib.subscribe.php');
		
		$this->acl_man = new DoceboACLManager();
		$this->sub_man = new CourseSubscribe_Management();
		
		if( $params === NULL ) 
	  		return;	
	  	else
			$this->set_config( $params );	// connection
			
	}

	/**
	 * set configuration
	 * @param array $params
	 **/	 		
	function set_config( $params ) {
		
		if( isset($params['name']) )				$this->name = $params['name'];
		if( isset($params['description']) )			$this->description = $params['description'];
	}
	
	/**
	 * get configuration
	 * @return array 	 
	 **/	 	
	function get_config() {
		
		return array(	'name' => $this->name,
						'description' => $this->description );
	}
	
	/**
	 * get configuration UI
	 * @return  DoceboConnectorUI	 
	 **/	 	
	function get_configUI() {
		
		return new DoceboConnectorUI_DoceboTestTrackUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {
	
	
		// if none cache course code info
		if($this->course_cache === false) {
			
			$this->course_cache = array();
			$search_query = "
			SELECT idCourse, code, name
			FROM ".$GLOBALS['prefix_lms']."_course";
			$re_course = sql_query($search_query);
			if(!$re_course) return false;
			while(list($id_course, $code, $name) = sql_fetch_row($re_course)) {
				
				$this->course_cache[$name]['id'] = $id_course; 
				$this->course_cache[$name]['course_name'] = $name; 
			}
		}


		if($this->test_cache === false) {
			
			$this->test_cache = array();
			$query_test = "SELECT org.idOrg, org.idCourse, te.idTest, te.title
			FROM ".$GLOBALS['prefix_lms']."_organization AS org JOIN ".$GLOBALS['prefix_lms']."_test AS te 
				ON ( org.objectType='test' AND org.idResource = te.idTest )";
			$re_test = sql_query($query_test);
			
			
			if(!$re_test)  die($query_test." ".mysql_error());//return false;
			while(list($idOrg, $idCourse, $idTest, $name) = sql_fetch_row($re_test)) {
				
				$name = strtolower($name);
				$this->test_cache[$idCourse.'_'.$name]['id_org'] = $idOrg; 
				$this->test_cache[$idCourse.'_'.$name]['id_test'] = $idTest; 
			}
		}
		
		print_r($this->test_cache);
	}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {}

	function get_type_name() { return "docebo-testtrack"; }	 
	
	function get_type_description() { return "connector to docebo testtrack"; }	 	

	function get_name() { return $this->name; }	 	

	function get_description() { return $this->description; }	 	

	function is_readonly() { return (bool)($this->readwrite & 1); }

	function is_writeonly() { return (bool)($this->readwrite & 2); }
	
	function is_raw_producer() { return false; }
	
	function get_tot_cols(){
		return count( $this->all_cols );
	}
	
	function get_cols_descripor() {
		
		$lang = DoceboLanguage::createInstance('subscribe', 'lms');
		
		$col_descriptor = array();
		foreach($this->all_cols as $k => $col) {
				$in = array_search($col[0], $this->default_cols);
			$col_descriptor[] = array(
				DOCEBOIMPORT_COLNAME 		=> $lang->def('_'.strtoupper($col[0])),
				DOCEBOIMPORT_COLID			=> $col[0],
				DOCEBOIMPORT_COLMANDATORY 	=> ( array_search($col[0], $this->mandatory_cols) === FALSE 
													? false 
													: true ),
				DOCEBOIMPORT_DATATYPE 		=> $col[1],
				DOCEBOIMPORT_DEFAULT 		=> ($in  === FALSE 
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

	function get_row_bypk($pk) {
		
		// if none cache course code info
		if($this->course_cache === false) {
			
			$this->course_cache = array();
			$search_query = "
			SELECT idCourse, code, name
			FROM ".$GLOBALS['prefix_lms']."_course";
			$re_course = sql_query($search_query);
			if(!$re_course) return false;
			while(list($id_course, $code, $name) = sql_fetch_row($re_course)) {
				
				$this->course_cache[$name]['id'] = $id_course; 
				$this->course_cache[$name]['course_name'] = $name; 
			}
		}
		// if userid not cached search for it in the database and populate cache
		if(!isset($this->userid_cache[$pk['userid']])) {
			if($this->userid_cache === false) $this->userid_cache = array();
			
			$user = $this->acl_man->getUser(false, addslashes($pk['userid']));
			if($user === false) return false;
			
			$this->userid_cache[$pk['userid']] = $user[ACL_INFO_IDST];
		}
		$arr = array(
			'id_course' => ( isset($this->course_cache[$pk['course_code']]) ? $this->course_cache[$pk['course_code']]['id'] : 0 ),
			'course_name' => ( isset($this->course_cache[$pk['course_code']]) ? $this->course_cache[$pk['course_code']]['course_name'] : '' ),
			'idst_user' => ( isset($this->userid_cache[$pk['userid']]) ? $this->userid_cache[$pk['userid']] : 0 )
		);
		return $arr;
	}

	function add_row( $row, $pk ) {
		
		$pk['userid'] = $row['userid'];
		
		$re_ins = true;
		$arr_id = $this->get_row_bypk($pk);
		if(($arr_id['idst_user'] == '') || ($arr_id['id_course'] == '')) {
			$this->last_error = 'not found the requested user or course : ['.$row['userid'].']<br />';
			return false;
		}
		$row['test_name'] = strtolower($row['test_name']);
		if(!isset($this->test_cache[$arr_id['id_course'].'_'.$row['test_name']])) {
			$this->last_error = 'not found the associated test : '.$arr_id['id_course'].' '.$row['test_name'].'<br />';
			return false;
		}
		$id_org = $this->test_cache[$arr_id['id_course'].'_'.$row['test_name']]['id_org'];
		$id_test = $this->test_cache[$arr_id['id_course'].'_'.$row['test_name']]['id_test'];
		
		$new_track = "INSERT INTO ".$GLOBALS['prefix_lms']."_testtrack (
			`idTrack` ,
			`idUser` ,
			`idReference` ,
			`idTest` ,
			`date_attempt` ,
			`date_attempt_mod` ,
			`date_end_attempt` ,
			`last_page_seen` ,
			`last_page_saved` ,
			`number_of_save` ,
			`number_of_attempt` ,
			`score` ,
			`bonus_score` ,
			`score_status` ,
			`comment`
		) VALUES (
			NULL , '".$arr_id['idst_user']."', '".$id_org."', '".$id_test."', '".$row['date_attempt']."', NULL , '".$row['date_attempt']."', '0', '0', '0', '0', '".$row['user_score']."' , '0', 'valid', ''
		)";
		if(!sql_query($new_track))  {
			$this->last_error = 'failed track insert : '.mysql_error().'<br />';
			return false;
		}
		$id_track = mysql_insert_id();
		$new_commontrack = "INSERT INTO ".$GLOBALS['prefix_lms']."_commontrack (
			`idReference` ,
			`idUser` ,
			`idTrack` ,
			`objectType` ,
			`dateAttempt` ,
			`status`
		) VALUES (
			'".$id_org."', '".$arr_id['idst_user']."', '".$id_track."', 'test', '".$row['date_attempt']."', 'completed'
		)";
		if(!sql_query($new_commontrack))  {
			$this->last_error = 'failed common track insert : '.mysql_error().'<br />';
			return false;
		}
		return true;
	}
	
	function _delete_by_id($id_course, $idst_user, $course_name) {
		
		$re_ins = true;
		return $re_ins;
	}
	
	function delete_bypk( $pk ) {
		
		$re_ins = true;
		return $re_ins;
	}
	
	function delete_all_filtered( $arr_pk ) {
		
		$re  = true;
		foreach($arr_pk as $k => $pk) {
			
			$re &= $this->delete_bypk( $pk );
		}
		return $re;
	}
	

	function delete_all_notinserted() {
		
		$counter = 0;
		return $counter;
	}
		 	
	function get_error() { return $this->last_error; }
		
}

class DoceboConnectorUI_DoceboTestTrackUI extends DoceboConnectorUI {
	
	var $connector 		= NULL;
	var $post_params 	= NULL;
	var $sh_next 		= TRUE;
	var $sh_prev 		= FALSE;
	var $sh_finish 		= FALSE;
	var $step_next 		= '';
	var $step_prev 		= '';
	
	function DoceboConnectorUI_DoceboTestTrackUI( &$connector ) {
		
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'DoceboTestTrackuiconfig'; }
		
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
			$this->post_params = unserialize(urldecode($post[$this->_get_base_name()]['memory']));
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
										urlencode(serialize($this->post_params)) );
		
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
											
		return $out;
	}
}

function DoceboTestTrack_factory() {
	return new DoceboConnector_DoceboTestTrack(array());
}

?>
