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
 * @package		Forma
 * @subpackage	ImportExport
 * @version 	$Id: import.org_chart.php 977 2007-02-23 10:40:19Z fabio $
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
**/
require_once(_adm_.'/lib/lib.import.php');
require_once(_lms_.'/admin/models/EnrollrulesAlms.php');

class ImportUser extends DoceboImport_Destination {

	public $last_error = NULL;
	public $mandatory_cols = array('userid');
	public $default_cols = array(	'firstname'=>'','lastname'=>'','pass'=>'',
								'email'=>'','avatar'=>'',
								'signature'=>'');
	public $ignore_cols = array( 'idst', 'avatar', 'lastenter', 'valid', 'pwd_expire_at', 'level', 'register_date', 'force_change',
		'facebook_id', 'twitter_id', 'linkedin_id', 'google_id', 'signature', 'privacy_policy' );
	public $valid_filed_type = array( 'textfield', 'date', 'dropdown', 'yesno', 'freetext', 'country',	'gmail', 'icq', 'msn', 'skype', 'yahoo', 'codicefiscale', 'country');
	public $cols_descriptor = NULL;
	public $dbconn = NULL;
	public $tree = 0;
	public $charset = '';

	public $idst_imported = array();
	public $orgchart = array();

	public $pwd_force_change_policy = "do_nothing";
	public $set_password = "from_file";
	public $manual_password = NULL;
        public $action_on_users = "create_and_update";

	/**
	 * constructor for forma users destination connection
	 * @param array $params
	 *			- 'dbconn' => connection to database (required)
	 *			- 'tree' => The id of the destination folder on tree (required)
	**/
	function ImportUser( $params ) {
		$this->dbconn = $params['dbconn'];
		$this->tree = (int)$params['tree'];
		if (isset($params['pwd_force_change_policy'])) {
			switch ( strtolower( $params['pwd_force_change_policy'] ) ) {
				case 'true':
				case 'false':
				case 'by_setting':
				case 'do_nothing': $this->pwd_force_change_policy = strtolower( $params['pwd_force_change_policy'] ); break;
			}
		}
		$this->send_alert = ($params['send_alert'] == '1');
		$this->set_password = $params['set_password'];
		$this->manual_password = $params['manual_password'];
		$this->action_on_users = $params['action_on_users'];
	}

	function connect() {
		
		require_once(_adm_.'/lib/lib.field.php');
		require_once(_base_.'/lib/lib.eventmanager.php');
		
		// Load language for fields names
		$lang_dir =& DoceboLanguage::createInstance('admin_directory', 'framework');
		$acl =& Docebo::user()->getACL();
		$acl_manager = Docebo::user()->getAclManager();
		
		$this->fl = new FieldList();
		$this->idst_group = $acl_manager->getGroupST('oc_'.(int)$this->tree);
		$this->idst_desc = $acl_manager->getGroupST('ocd_'.(int)$this->tree);
		
		$this->arr_fields = $this->fl->getAllFields();

		$this->cols_descriptor = NULL;
		if( $this->dbconn === NULL ) {
			$this->last_error = Lang::t('_ORG_IMPORT_ERR_DBCONNISNULL');
			return FALSE;
		}
		$query = "SHOW FIELDS FROM ".$GLOBALS['prefix_fw']."_user";
		$rs = sql_query( $query, $this->dbconn );
		if( $rs === FALSE ) {
			$this->last_error = Lang::t('_ORG_IMPORT_ERR_ERRORONQUERY').$query.' ['.sql_error($this->dbconn).']';
			return FALSE;
		}
		$this->cols_descriptor = array();
		while( $field_info = sql_fetch_array($rs) ) {
			if( !in_array($field_info['Field'],$this->ignore_cols) ) {
				$mandatory = in_array($field_info['Field'],$this->mandatory_cols);
				if( isset($this->default_cols[$field_info['Field']])) {
					$this->cols_descriptor[] =
								array(  DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_'.$field_info['Field']),
										DOCEBOIMPORT_COLID => $field_info['Field'],
										DOCEBOIMPORT_COLMANDATORY => $mandatory,
										DOCEBOIMPORT_DATATYPE => $field_info['Type'],
										DOCEBOIMPORT_DEFAULT => $this->default_cols[$field_info['Field']]
										);
				} else {
					$this->cols_descriptor[] =
								array(  DOCEBOIMPORT_COLNAME => $lang_dir->def('_DIRECTORY_FILTER_'.$field_info['Field']),
										DOCEBOIMPORT_COLID => $field_info['Field'],
										DOCEBOIMPORT_COLMANDATORY => $mandatory,
										DOCEBOIMPORT_DATATYPE => $field_info['Type']
										);
				}
			}
		}
		$this->cols_descriptor[] = array(
			DOCEBOIMPORT_COLNAME => Lang::t('_FOLDER_NAME', 'standard'),
			DOCEBOIMPORT_COLID => 'tree_name',
			DOCEBOIMPORT_COLMANDATORY => false,
			DOCEBOIMPORT_DATATYPE => 'text'
		);

		sql_free_result( $rs );

		foreach($this->arr_fields as $field_id => $field_info) {
			if( in_array($field_info[FIELD_INFO_TYPE],$this->valid_filed_type) ) {
				$this->cols_descriptor[] =
							array(  DOCEBOIMPORT_COLNAME => $field_info[FIELD_INFO_TRANSLATION],
									DOCEBOIMPORT_COLID => $field_id,
									DOCEBOIMPORT_COLMANDATORY => FALSE,
									DOCEBOIMPORT_DATATYPE => 'text',
									DOCEBOIMPORT_DEFAULT => false
									);
			}
		}
		$this->userlevel = $acl_manager->getGroupST(ADMIN_GROUP_USER);
		
		$idst_oc		= $acl_manager->getGroup(false, '/oc_0');
		$this->idst_oc	= $idst_oc[ACL_INFO_IDST];

		$idst_ocd		= $acl_manager->getGroup(false, '/ocd_0');
		$this->idst_ocd	= $idst_ocd[ACL_INFO_IDST];

		// cache orgchart
		$um = new UsermanagementAdm();
		$this->orgchart = $um->getAllFolders('both', ( $this->tree != 0 ? $this->tree : false ) );
		
		return TRUE;
	}

	function close() {}

	function get_tot_cols(){
		return count( $this->cols_descriptor );
	}

	function get_cols_descripor() {
		return $this->cols_descriptor;
	}

	/**
	 * @return integer the number of mandatory columns to import
	**/
	function get_tot_mandatory_cols() {
		$result = array();
		foreach( $this->cols_descriptor as $col ) {
			if( $col[DOCEBOIMPORT_COLMANDATORY] )
				$result[] = $col;
		}
		return count($result);
	}

	function _convert_char( $text ) {
		if( function_exists('mb_convert_encoding') ) {
			return mb_convert_encoding($text, 'UTF-8', $this->charset);
		} else {
			return utf8_encode($text);
		}
	}

	/**
	 * @param array data to insert; is an array with keys the names of cols and
	 *				values the data
	 * @return TRUE if the row was succesfully inserted, FALSE otherwise
	**/
	function add_row( $row, $tocompare ) {
		$acl =& Docebo::user()->getACL();
		$acl_manager = Docebo::aclm();

		foreach($row as $k => $v) {
			if($row[$k] !== false)
				$row[$k] = trim($v);
		}
		
		$userid		= strtolower( addslashes($this->_convert_char($row['userid'])) );
		$firstname	= (Get::sett('import_ucfirst', 'on') == "on" ? ucfirst( strtolower( addslashes($this->_convert_char($row['firstname'])) ) ) : addslashes($this->_convert_char($row['firstname'])) )  ;
		$lastname	= (Get::sett('import_ucfirst', 'on') == "on" ? ucfirst( strtolower( addslashes($this->_convert_char($row['lastname']))  ) ) : addslashes($this->_convert_char($row['lastname'] )) )  ; 
		$pass		= addslashes($this->_convert_char($row['pass']));
		$email		= addslashes($this->_convert_char($row['email']));

                if(isset($tocompare['userid']))     $tocompare['userid']    = '/'.strtolower( addslashes($this->_convert_char($tocompare['userid'])) );
		if(isset($tocompare['firstname']))  $tocompare['firstname'] = ucfirst( strtolower( addslashes($this->_convert_char($tocompare['firstname'])) ) );
		if(isset($tocompare['lastname']))   $tocompare['lastname']  = ucfirst( strtolower( addslashes($this->_convert_char($tocompare['lastname'])) ) ); 
		if(isset($tocompare['pass']))       $tocompare['pass']      = addslashes($this->_convert_char($tocompare['pass']));
		if(isset($tocompare['email']))      $tocompare['email']     = addslashes($this->_convert_char($tocompare['email']));
                
				if($pass == '') $pass = FALSE;
				$force_send_alert = FALSE;
                
                switch ($this->set_password){
                    case 'insert_empty':
                        if (!$pass) {
                            if ($this->manual_password != NULL) {
                                $newpass = $this->manual_password;
                            } else {
                                $newpass = $acl_manager->random_password();
                            }
                            $pass = $newpass;
                        }
                        break;
                    case 'insert_all':
                        if ($this->manual_password != NULL) {
                            $newpass = $this->manual_password;
                        } else {
                            $newpass = $acl_manager->random_password();
                        }
                        $pass = $newpass;
						break;
					case 'from_file':
                        if (!$pass) {
                            if ($this->manual_password != NULL) {
                                $newpass = $this->manual_password;
                            } else {
                                $newpass = $acl_manager->random_password();
							}
							
							if ( $this->action_on_users == 'only_update' && $row['pass'] == '' )  {
								$pass = FALSE;
							} else {
								$pass = $newpass;
								$force_send_alert = TRUE;
							}

                        }
                        break;
                }

		$force_change = '';
		switch ($this->pwd_force_change_policy) {
			case "by_setting": $force_change = Get::sett('pass_change_first_login', 'off') == 'on' ? 1 : 0; break;
			case "true": $force_change = 1; break;
			case 'false': $force_change = 0; break;
		}
		
		$is_an_update = false;
                $err = false;
                $idst = $acl_manager->getUserST( $tocompare['userid'] );
                $sameuserid = FALSE;
                
                if($idst !== FALSE){
                    $user_mng = new UsermanagementAdm();
                    $infouser = $user_mng->getProfileData($idst);
                    $fielduser = $this->fl->getUserFieldEntryData($idst);
			
                    foreach($tocompare as $field_id => $field_value) {				
                        if(isset($this->arr_fields[$field_id])) {
                            if($field_value != $fielduser[$field_id]){
                                $idst = FALSE;
                                $sameuserid = TRUE;
                            }
                        } else {
                            if($field_value != $infouser->$field_id && $field_id != "pass"){
                                $idst = FALSE;
                                $sameuserid = TRUE;
                            }
                        }
                    }
                }
				
                
                switch($this->action_on_users) {
                    case 'create_and_update':
                        if($idst === FALSE && !$sameuserid) {
                            // create a new user
                            $idst = $acl_manager->registerUser(
					$userid,
					$firstname,
					$lastname,
                                    $pass ? $pass : '',
					$email,
                                    '',
                                    '',
                                    FALSE,
                                    FALSE,
                                    '',
                                    $force_change,
                                    FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE
                            );
                            
                            if ($idst === FALSE) {
                                $this->last_error = 'Error on insert user';
                                $err = true;
                            }
                        } else if ($idst !== FALSE) {	//   if ($sameuserid == TRUE) {
                            $result = $acl_manager->updateUser(
                                    $acl_manager->getUserST( $tocompare['userid']),
                                    $userid,
                                    $firstname != '' ? $firstname : FALSE,
                                    $lastname != '' ? $lastname : FALSE,
                                    $pass,
                                    $email != '' ? $email : FALSE,
					FALSE,
					FALSE,
					FALSE,
					TRUE,
                                    $force_change != '' ? $force_change : FALSE,
					FALSE,
					FALSE,
					FALSE,
					FALSE
				);
				$is_an_update = true;
				// the user exist but the update query fails
				if( !$result ) {
					$this->last_error = 'Error on update user';
                                    $err = true;				
				}
			} else {
                            $a = 1;
                            $newuserid = $userid;
                            while ($acl_manager->getUserST( $newuserid )) {
                                $newuserid = $userid . $a;
                                $a++;
                            }
                            $userid = $newuserid;

                                // create a new user
                            $idst = $acl_manager->registerUser(
                                    $userid,
                                    $firstname,
                                    $lastname,
                                    $pass ? $pass : '',
                                    $email,
                                    '',
                                    '',
                                    FALSE,
                                    FALSE,
                                    '',
                                    $force_change,
                                    FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE
                            );

                            if ($idst === FALSE) {
                                $this->last_error = 'Error on insert user';
                                $err = true;
			}
		}
                        break;
                    case 'create_all':
                        $a = 1;
                        $newuserid = $userid;
                        while ($acl_manager->getUserST( $newuserid )) {
                            $newuserid = $userid . $a;
                            $a++;
                        }
                        $userid = $newuserid;
                        
                            // create a new user
                        $idst = $acl_manager->registerUser(
                                $userid,
                                $firstname,
                                $lastname,
                                $pass ? $pass : '',
                                $email,
                                '',
                                '',
                                FALSE,
                                FALSE,
                                '',
                                $force_change,
                                FALSE,
                                FALSE,
                                FALSE,
                                FALSE
                        );
                        
		if($idst === FALSE) {
                            $this->last_error = 'Error on insert user';
                            $err = true;
                        }
                        break;
                    case 'only_create':
                        if($idst === FALSE && !$sameuserid) {
			// create a new user
			$idst = $acl_manager->registerUser(
				$userid,
				$firstname,
				$lastname,
                                    $pass ? $pass : '',
				$email,
				'',
				'',
				FALSE,
				FALSE,
				'',
				$force_change,
				FALSE,
				FALSE,
				FALSE,
				FALSE
			);
                            
                            if ($idst === FALSE) {
                                $this->last_error = 'Error on insert user';
                                $err = true;
                            }
                        } elseif ($idst !== FALSE || $sameuserid) {
                            $idst = FALSE;
                            $this->last_error = Lang::t('_USER_ALREADY_EXISTS', 'standard').' --> '.$userid.' | '.$firstname.' | '.$lastname.' | '.$pass.' | '.$email.' |';
                            return FALSE;
                        } else {
                            $a = 1;
                            $newuserid = $userid;
                            while ($acl_manager->getUserST( $newuserid )) {
                                $newuserid = $userid . $a;
                                $a++;
                            }
                            $userid = $newuserid;

                                // create a new user
                            $idst = $acl_manager->registerUser(
                                    $userid,
                                    $firstname,
                                    $lastname,
                                    $pass ? $pass : '',
                                    $email,
                                    '',
                                    '',
                                    FALSE,
                                    FALSE,
                                    '',
                                    $force_change,
                                    FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE
                            );

                            if ($idst === FALSE) {
                                $this->last_error = 'Error on insert user';
                                $err = true;
                            }
		}
                        break;
                    case 'only_update':
			if($idst !== FALSE ){   //if($sameuserid !== false) {
				$result = $acl_manager->updateUser(
                                    $acl_manager->getUserST( $tocompare['userid']),
                                    $userid,
                                    $firstname != '' ? $firstname : FALSE,
                                    $lastname != '' ? $lastname : FALSE,
                                    $pass,
                                    $email != '' ? $email : FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE,
                                    TRUE,
                                    $force_change != '' ? $force_change : FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE,
                                    FALSE
				);
				$is_an_update = true;
				// the user exist but the update query fails
				if( !$result ) {
					$this->last_error = 'Error on update user';
					$err = true;				
				}
			}
                        break;
                }
                
		if($idst !== false || $sameuserid == TRUE) {
                    
                        if ($idst == FALSE && $sameuserid == TRUE) {
                            $idst = $acl_manager->getUserST( $tocompare['userid'] );
                        }
			
			$result = TRUE;
			$this->idst_imported[$idst] = $idst;
			if(!$is_an_update) {
				// -- Add user to registered users group if not importing into root ---
				$acl_manager->addToGroup($this->idst_oc, $idst);
				$acl_manager->addToGroup($this->idst_ocd, $idst);

				// -- add to group level ----------------------------------------------
				$acl_manager->addToGroup($this->userlevel, $idst);
			}
			// --------------------------------------------------------------------
			if(isset($row['tree_name']) && $row['tree_name'] != '') {
				
				$row['tree_name'] = strtolower($row['tree_name']);
				if( isset($this->orgchart[$row['tree_name']]) ) {
					
					$f = $this->orgchart[$row['tree_name']];
					$acl_manager->addToGroup($f->idst_oc, $idst);
					$acl_manager->addToGroup($f->idst_ocd, $idst);
					
					// apply enroll rules
					$enrollrules = new EnrollrulesAlms();
					$enrollrules->newRules('_NEW_IMPORTED_USER', array($idst), 'all', $f->idOrg);
				}
			} elseif ($this->idst_group != $this->idst_oc) {
				
				$acl_manager->addToGroup($this->idst_group, $idst);
				$acl_manager->addToGroup($this->idst_desc, $idst);
			}
			
			$array_subst = array(
				'[url]' => Get::sett('url'),
				'[userid]' => $userid,
				'[password]' => $pass
			);
			//send email alert
			if(($this->send_alert && (!$is_an_update || $pass)) || $force_send_alert) {
				$e_msg = new EventMessageComposer();

				$e_msg->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
				$e_msg->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst );

				$e_msg->setBodyLangText('sms', '_REGISTERED_USER_TEXT_SMS', $array_subst );

				$recipients = array($idst);
				createNewAlert(	'UserNew', 'directory', 'edit', '1', 'New user created', $recipients, $e_msg, true );
			}
			//-save extra field------------------------------------------
			/*
			$arr_idst_all = $acl->getArrSTGroupsST(array($this->idst_group,$this->idst_desc));
			$arr_fields = $this->fl->getFieldsFromIdst($arr_idst_all);
			$arr_fields_toset = array();
			foreach( $arr_fields as $field_id => $field_info)
				if(isset($row[$field_id]) && $row[$field_id] !== false)
					$arr_fields_toset[$field_id] = addslashes($this->_convert_char($row[$field_id]));
			*/
			foreach($row as $field_id => $field_value) {
				
				if(isset($this->arr_fields[$field_id])) {
					
					$arr_fields_toset[$field_id] = addslashes($this->_convert_char($field_value));
				}
			}
				
				
			if( count($arr_fields_toset) > 0 )
				$result = $this->fl->storeDirectFieldsForUser($idst, $arr_fields_toset, false);
			//-----------------------------------------------------------
			if( !$result ) {
				$this->last_error = Lang::t('_ORG_IMPORT_ERR_STORECUSTOMFIELDS').' : <b>'.$userid.'</b>';
			}
			return $result;
		} else if ($err) {
			$this->last_error = Lang::t('_OPERATION_FAILURE').' : <b>'.$userid.'</b>';
			return FALSE;
		} else return TRUE;
	}

	function getNewImportedIdst() {

		return $this->idst_imported;
	}

	function set_charset( $charset ) { $this->charset = $charset; }


	function get_error() {
		return $this->last_error;
	}
}

class ImportGroupUser extends DoceboImport_Destination {

	var $last_error = NULL;
	var $cols_id			= array('userid', 'groupid');
	var $cols_default		= array();
	var $cols_mandatory		= array('userid', 'groupid');
	var $cols_type			= array('userid' => 'text', 'groupid' => 'text');
	var $cols_descriptor 	= array();
	var $dbconn = NULL;
	var $charset = '';
	
	var $acl_man;
	
	var $group_cache = array();
	var $user_cache = array();

	/**
	 * constructor for forma users destination connection
	 * @param array $params
	 *			- 'dbconn' => connection to database (required)
	 *			- 'tree' => The id of the destination folder on tree (required)
	**/
	function ImportGroupUser( $params ) {
		$this->dbconn = $params['dbconn'];
		$this->acl_man 	=& Docebo::user()->getAclManager();
	}

	function connect() {
		
		$this->cols_descriptor = array();
		foreach($this->cols_id as $k => $field_id) {

			$mandatory = in_array($field_id, $this->cols_mandatory);
				
			if( in_array($field_id, $this->cols_default)) {
				
				$this->cols_descriptor[] = array(  
					DOCEBOIMPORT_COLNAME 		=> Lang::t('_GROUPUSER_'.$field_id, 'organization_chart', 'framework'),
					DOCEBOIMPORT_COLID 			=> $field_id,
					DOCEBOIMPORT_COLMANDATORY 	=> in_array($field_id, $this->cols_mandatory),
					DOCEBOIMPORT_DATATYPE 		=> $this->cols_type[$field_id],
					DOCEBOIMPORT_DEFAULT => $this->default_cols[$field_id]
				);
			} else {
				
				$this->cols_descriptor[] = array(  
					DOCEBOIMPORT_COLNAME 		=> Lang::t('_GROUPUSER_'.$field_id, 'organization_chart', 'framework'),
					DOCEBOIMPORT_COLID 			=> $field_id,
					DOCEBOIMPORT_COLMANDATORY 	=> in_array($field_id, $this->cols_mandatory),
					DOCEBOIMPORT_DATATYPE 		=> $this->cols_type[$field_id]
				);
			}
		}
		return TRUE;
	}

	function close() {}

	function get_tot_cols(){
		return count( $this->cols_descriptor );
	}

	function get_cols_descripor() {
		return $this->cols_descriptor;
	}

	/**
	 * @return integer the number of mandatory columns to import
	**/
	function get_tot_mandatory_cols() {
		
		return count( $this->cols_mandatory );
	}

	function _convert_char( $text ) {
		if( function_exists('mb_convert_encoding') ) {
			return mb_convert_encoding($text, 'UTF-8', $this->charset);
		} else {
			return utf8_encode($text);
		}
	}

	/**
	 * @param array data to insert; is an array with keys the names of cols and
	 *				values the data
	 * @return TRUE if the row was succesfully inserted, FALSE otherwise
	**/
	function add_row( $row ) {

		while(list($k, $v) = each($row)) {
			
			$row[$k] = sql_escape_string($v);
		}
		reset($row);
		// find the group idst
		$group_idst = array_search($row['groupid'], $this->group_cache);
		if($group_idst === NULL || $group_idst === false) {
			
			$group = $this->acl_man->getGroup(false, $row['groupid']);
			$this->group_cache[$group[ACL_INFO_IDST]] = $row['groupid'];
			$group_idst = $group[ACL_INFO_IDST];
		}
		if($group_idst == false) {
			// the group doesn't exist
			$this->last_error = Lang::t('_GROUP_IMPORT_ERR_GROUP_DOESNT_EXIST', 'org_chart', 'framework');
			return false;
		}
		// find the user idst
		$user_idst = array_search($row['userid'], $this->user_cache);
		if($user_idst === NULL || $user_idst === false) {
			
			$user = $this->acl_man->getUser(false, $row['userid']);
			$this->user_cache[$user[ACL_INFO_IDST]] = $row['userid'];
			$user_idst = $user[ACL_INFO_IDST];
		}
		if($user_idst == false) {
			// the user doesn't exist
			$this->last_error = Lang::t('_GROUP_IMPORT_ERR_USER_DOESNT_EXIST', 'org_chart', 'framework');
			return false;
		}
		
		$result = $this->acl_man->addToGroup( $group_idst, $user_idst );
		if( !$result ) {
			$this->last_error = Lang::t('_GROUP_IMPORT_ERR_SUBSCRIPTION', 'org_chart', 'framework');
		}
		return true;
	}

	function set_charset( $charset ) { $this->charset = $charset; }


	function get_error() {
		return $this->last_error;
	}
}

?>