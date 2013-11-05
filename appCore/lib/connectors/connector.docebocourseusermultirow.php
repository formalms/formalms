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
class DoceboConnector_DoceboCourseUserMultiRow extends DoceboConnector {
	
  	var $last_error = "";
 	
 	var $acl_man = false;
 	
 	var $sub_man = false;
 	
 	// name, type
 	var $all_cols = array( 
		array( 'code', 'text' ),
		array( 'userid', 'text' ),
		array( 'level', 'int' ),
		array( 'first_access', 'text'),
        array( 'last_access', 'text' ),
        array( 'first_finish', 'text' ),
        array( 'last_finish', 'text' ),
        array( 'title', 'text' ),
        array( 'status', 'text' ),
        array( 'total_seconds', 'int' )
	);
	
	var $mandatory_cols = array('code', 'userid', 'level');
	
	var $default_cols = array( 	'code' => '', 
								'userid' => '', 
								'level' => '3',
                                'first_access' => '0000-00-00 00:00:00',
                                'last_access' => '0000-00-00 00:00:00',
                                'first_finish' => '0000-00-00 00:00:00',
                                'last_finish' => '0000-00-00 00:00:00',
                                'title' => '',
                                'status' => 'n',
                                'total_seconds' => '0' );
	
	var $name 					= "";
	var $description 			= "";
	
	var $readwrite 	= 1; // read = 1, write = 2, readwrite = 3
	var $sendnotify = 1; // send notify = 1, don't send notify = 2
	var $on_delete = 1;  // unactivate = 1, delete = 2
		
	var $arr_pair_inserted 	= array();
	
	var $course_cache = false;
	var $userid_cache = false;
	
	/**
	 * constructor
	 * @param array params	 
	 **/
	function DoceboConnector_DoceboCourseUserMultiRow( $params ) {
		
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
		if( isset($params['readwrite']) )			$this->readwrite = $params['readwrite'];
		if( isset($params['sendnotify']) )			$this->sendnotify = $params['sendnotify'];
		if( isset($params['on_delete']) )			$this->on_delete = $params['on_delete'];
	}
	
	/**
	 * get configuration
	 * @return array 	 
	 **/	 	
	function get_config() {
		
		return array(	'name' => $this->name,
						'description' => $this->description,
						'readwrite' => $this->readwrite,
						'sendnotify' => $this->sendnotify, 
						'on_delete' => $this->on_delete );
	}
	
	/**
	 * get configuration UI
	 * @return  DoceboConnectorUI	 
	 **/	 	
	function get_configUI() {
		
		return new DoceboConnectorUI_DoceboCourseUserMultiRowUI($this);
	}
	
	/**
	 * execute the connection to source
	**/
	function connect() {}
	
	/**
	 * execute the close of the connection 
	**/
	function close() {}

	function get_type_name() { return "docebo-courseusermultirow"; }	 
	
	function get_type_description() { return "connector to docebo user course subscription"; }	 	

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
		print_r($col_descriptor);
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
				
				$this->course_cache[$code]['id'] = $id_course; 
				$this->course_cache[$code]['course_name'] = $name; 
			}
		}
		// if userid not cached search for it in the database and populate cache
		if(!isset($this->userid_cache[$pk['userid']])) {
			if($this->userid_cache === false) $this->userid_cache = array();
			
			$user = $this->acl_man->getUser(false, $pk['userid']);
			if($user === false) return false;
			
			$this->userid_cache[$pk['userid']] = $user[ACL_INFO_IDST];
		}
		return array(
			'id_course' => ( isset($this->course_cache[$pk['code']]) ? $this->course_cache[$pk['code']]['id'] : 0 ),
			'course_name' => ( isset($this->course_cache[$pk['code']]) ? $this->course_cache[$pk['code']]['course_name'] : '' ),
			'idst_user' => ( isset($this->userid_cache[$pk['userid']]) ? $this->userid_cache[$pk['userid']] : 0 )
		);
	}

	function add_row( $row, $pk ) {
		
		$arr_id = $this->get_row_bypk($pk);
		
		if(($arr_id['idst_user'] == '') || ($arr_id['id_course'] == '')) {
			$this->last_error = 'not found the requested user or course <br/> ';
			return false;
		}
		if(!$row['level']) $row['level'] = 3;
		
		$re_ins = $this->sub_man->subscribeUserWithConnection($arr_id['idst_user'], $arr_id['id_course'], $row['level'], $this->get_name());
        
        if (	($row['title'] == 'Questionario di valutazione di fine corso' 
        		|| $row['title'] == 'Test finale' 
        		|| $row['title'] == 'Autovalutazione per PowerPoint 2003 Livello 1'  
        		|| $row['title'] == 'Autovalutazione per PowerPoint 2003 Livello 2' 
        		|| $row['title'] == 'Autovalutazione per Excel 2003 Livello 1' 
        		|| $row['title'] == 'Autovalutazione per Excel 2003 Livello 2' ) 
        		&& ($row['status'] == 'c' || $row['status'] == 'p'))
		{
            $query = "UPDATE learning_courseuser"
                    ." SET status = '"._CUS_END."'"
                    ." WHERE idUser = '".$arr_id['idst_user']."'"
                    ." AND idCourse = '".$arr_id['id_course']."'";
            
            $result = sql_query($query);
        
        } elseif($row['status'] != 'n') {
        	
            $query = "SELECT status, date_inscr, date_first_access, date_complete "
                    ." FROM learning_courseuser"
                    ." WHERE idUser = '".$arr_id['idst_user']."'"
                    ." AND idCourse = '".$arr_id['id_course']."'";
            
            list($status, $date_inscr, $date_first_access, $date_complete) = sql_fetch_row(sql_query($query));
            
            if($date_inscr > $row['first_access']) {
            
                $query = "UPDATE learning_courseuser"
                        ." SET date_inscr = '".$row['first_access']."'"
                        ." WHERE idUser = '".$arr_id['idst_user']."'"
                        ." AND idCourse = '".$arr_id['id_course']."'";
                $result = sql_query($query);
			}
            if($date_first_access < $row['last_access']) {
            
                $query = "UPDATE learning_courseuser"
                        ." SET date_first_access = '".$row['last_access']."'"
                        ." WHERE idUser = '".$arr_id['idst_user']."'"
                        ." AND idCourse = '".$arr_id['id_course']."'";
                $result = sql_query($query);
			}
            if($date_complete < $row['last_finish']) {
            
                $query = "UPDATE learning_courseuser"
                        ." SET date_complete = '".$row['last_finish']."'"
                        ." WHERE idUser = '".$arr_id['idst_user']."'"
                        ." AND idCourse = '".$arr_id['id_course']."'";
                $result = sql_query($query);
			}
			
			
            if($status != '2' && $status != '1')
            {
                $query = "UPDATE learning_courseuser"
                        ." SET status = '"._CUS_BEGIN."'"
                        ." WHERE idUser = '".$arr_id['idst_user']."'"
                        ." AND idCourse = '".$arr_id['id_course']."'";
                
                $result = sql_query($query);
            }
        }
		
		
		
           /* if($row['total_seconds'])
            {
                $query =    "INSERT INTO learning_tracksession"
                            ." (idEnter, idCourse, idUser, session_id, enterTime, numOp, lastFunction, lastOp, lastTime, ip_address, active)"
                            ." VALUES ('',
                            '".$arr_id['id_course']."',
                            '".$arr_id['idst_user']."',
                            '',
                            '".$row['first_access']."',
                            '".$row['total_seconds']."',
                            'course',
                            'view',
                            '".date('Y-m-d H:i:s', fromDatetimeToTimestamp($row['first_access']) + $row['total_seconds'])."',
                            '',
                            '')";
                
                $result = sql_query($query);
                if(!$result) die($query.' '.mysql_error());
            }*/
        
		if($re_ins) {
			
			if($this->cache_inserted) {
				$this->arr_pair_inserted[] = $arr_id['id_course'].' '.$arr_id['idst_user'];
				
			}
		} else {
			
			$this->last_error = 'error on user course subscription : '.mysql_error();
		}
		return $re_ins;
	}
	
	function _delete_by_id($id_course, $idst_user, $course_name) {
		
		if($this->on_delete == 1)
			$res &= $this->sub_man->suspendUserWithConnection($idst_user, $id_course, $this->get_name());
		else
			$re_ins = $this->sub_man->unsubscribeUserWithConnection($idst_user, $id_course, $this->get_name());
		if($re_ins === 'jump') return true;
		if($re_ins) {
			if($this->sendnotify == 1) {
				
				$array_subst = array(	'[url]' => Get::sett('url'),
								'[course]' => $course_name );
				
				// message to user that is waiting 
				$msg_composer = new EventMessageComposer();
				
				$msg_composer->setSubjectLangText('email', '_DEL_USER_SUBSCRIPTION_SUBJECT', false);
				$msg_composer->setBodyLangText('email', '_DEL_USER_SUBSCRIPTION_TEXT', $array_subst);
				
				$msg_composer->setBodyLangText('sms', '_DEL_USER_SUBSCRIPTION_TEXT_SMS', $array_subst);
				
				// send message to the user subscribed
				createNewAlert(	'UserCourseRemoved', 'subscribe', 'remove', '1', 'User removed form a course',
							array($idst_user), $msg_composer  );
			}
		}
		return $re_ins;
	}
	
	function delete_bypk( $pk ) {
		$arr_id = $this->get_row_bypk($pk);
		
		if($this->on_delete == 1)
			$res &= $this->sub_man->suspendUserWithConnection($arr_id['idst_user'], $arr_id['id_course'], $this->get_name());
		else
			$re_ins = $this->sub_man->unsubscribeUserWithConnection($arr_id['idst_user'], $arr_id['id_course'], $this->get_name());
		
		if($re_ins === 'jump') return true;
		if($re_ins) {
			if($this->sendnotify == 1) {
				
				$array_subst = array(	'[url]' => Get::sett('url'),
								'[course]' => $arr_id['course_name'] );
				
				// message to user that is waiting 
				$msg_composer = new EventMessageComposer();
				
				$msg_composer->setSubjectLangText('email', '_DEL_USER_SUBSCRIPTION_SUBJECT', false);
				$msg_composer->setBodyLangText('email', '_DEL_USER_SUBSCRIPTION_TEXT', $array_subst);
				
				$msg_composer->setBodyLangText('sms', '_DEL_USER_SUBSCRIPTION_TEXT_SMS', $array_subst);
				
				// send message to the user subscribed
				createNewAlert(	'UserCourseRemoved', 'subscribe', 'remove', '1', 'User removed form a course',
							array($arr_id['idst_user']), $msg_composer  );
			}
		}
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
		
		//cache course name
		$search_course = "
		SELECT idCourse, name
		FROM ".$GLOBALS['prefix_lms']."_course
		WHERE 1";
		$re_course = sql_query($search_course);
		while(list($id_course, $name) = sql_fetch_row($re_course)) {
			$course_name[$id_course] = $name;
		}
		
		$search_query = "
		SELECT idCourse, idUser
		FROM ".$GLOBALS['prefix_lms']."_courseuser 
		WHERE 1";
		if(!empty($this->arr_pair_inserted)) {
		
			$search_query .= " AND CONCAT(idCourse, '_', idUser) NOT IN (".implode($this->arr_pair_inserted , ',').") ";
		}
		$re_courseuser = sql_query($search_query);
		if(!$re_courseuser) return 0;
		$counter = 0;
		while(list($id_course, $id_user) = sql_fetch_row($re_courseuser)) {
			
			if($this->_delete_by_id($id_course, $id_user, $course_name[$id_course])) $counter++;
		}
		return $counter;
	}
		 	
	function get_error() { return $this->last_error; }
		
}

class DoceboConnectorUI_DoceboCourseUserMultiRowUI extends DoceboConnectorUI {
	
	var $connector 		= NULL;
	var $post_params 	= NULL;
	var $sh_next 		= TRUE;
	var $sh_prev 		= FALSE;
	var $sh_finish 		= FALSE;
	var $step_next 		= '';
	var $step_prev 		= '';
	
	function DoceboConnectorUI_DoceboCourseUserMultiRowUI( &$connector ) {
		
		$this->connector = $connector;
	}
	
	function _get_base_name() { return 'docebocourseusermultirowuiconfig'; }
		
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
	  	// ---- access type read/write -----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_ACCESSTYPE'), 
		  									$this->_get_base_name().'_readwrite', 
											$this->_get_base_name().'[readwrite]',
											array( 	$this->lang->def('_READ')  => '1', 
													$this->lang->def('_WRITE') => '2',
													$this->lang->def('_READWRITE') => '3'), 
											$this->post_params['readwrite']);
		// ---- on delete -> delete or unactivate -----
		$out .= $this->form->getRadioSet( 	$this->lang->def('_CANCELED_COURSEUSER'), 
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
											
		return $out;
	}
}

function docebocourseusermultirow_factory() {
	return new DoceboConnector_DoceboCourseUserMultiRow(array());
}

?>
