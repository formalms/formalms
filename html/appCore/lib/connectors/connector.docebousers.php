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

/** 
 * class for define docebo users connection to data source.
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboUsers extends DoceboConnector {
  
  var $last_error = "";
 	var $all_cols = array(	'idst','userid','firstname','lastname','pass',
							'email','avatar',
							'signature','templatename',
							'language', 'valid','tree_code');
	var $mandatory_cols = array('userid');
	var $default_cols = array(	'firstname'=>'','lastname'=>'','pass'=>'',
								'email'=>'','avatar'=>'',
								'signature'=>'','templatename'=>'',
								'language'=>'', 'tree_code'=>'');
	var $ignore_cols = array( 'idst', 'avatar', 'lastenter', 'valid', 'pwd_expire_at', 'level','register_date' );
	var $valid_filed_type = array( 'textfield','date','dropdown','yesno', 'upload', 'freetext', 'country');
	var $cols_descriptor = NULL;
	var $dbconn = NULL;
	var $tree = 0;
	var $tree_desc = 0;
	var $groupFilter = 0;

	var $readwrite = 0; // read = 1, write = 2, readwrite = 3
	var $canceled = 1;  // suspend = 1, delete = 2
	var $sendnotify = 1; // send notify = 1, don't send notify = 2

	var $name = "";
	var $description = "";
	var $preg_match_folder = '\[(.*)\]';
	
	
	var $directory = NULL;
	var $tree_view = NULL;
	var $people_view = NULL;
	
	var $data = false;
	
	var $tree_oc = NULL;
	var $tree_ocd = NULL;
	
	var $org_chart_name = array();
	var $org_chart_group = array();
	var $user_org_chart = array();
	
	var $arr_idst_inserted = array();

	var $org_chart_destination = 0;

	var $pwd_force_change_policy = 'do_nothing';


	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnectorDoceboUsers( $params ) {
	  	if( $params === NULL ) 
	  		return;	// connector
	  	else
			$this->set_config( $params );	// connection
	}
	
	function get_config() {
		return array( 	'tree' => $this->tree,
						//'group' => $this->groupFilter,
						'canceled' => $this->canceled,
						'readwrite' => $this->readwrite,
						'sendnotify' => $this->sendnotify,
						'name' => $this->name,
						'description' => $this->description,
						'preg_match_folder' => $this->preg_match_folder,
						'org_chart_destination' => $this->org_chart_destination,
						'pwd_force_change_policy' => $this->pwd_force_change_policy
			);
	}
	
	function set_config( $params ) {
		if( isset($params['tree']) )		$this->tree = $params['tree'];
		//if( isset($params['group']) )		$this->groupFilter = $params['group'];
		if( isset($params['canceled']) )	$this->canceled = $params['canceled'];		
		if( isset($params['readwrite']) )	$this->readwrite = $params['readwrite'];
		if( isset($params['sendnotify']) )	$this->sendnotify = $params['sendnotify'];
		if( isset($params['name']) )		$this->name = $params['name'];
		if( isset($params['description']) )	$this->description = $params['description'];
		if( isset($params['preg_match_folder']) )	$this->preg_match_folder = $params['preg_match_folder'];
		if( isset($params['org_chart_destination']) )	$this->org_chart_destination = $params['org_chart_destination'];
		if( isset($params['pwd_force_change_policy']) ) $this->pwd_force_change_policy = $params['pwd_force_change_policy'];
	}

	function get_configUI() {
		return new DoceboConnectorDoceboUsersUI($this);
	}
	
	function connect() {
		
		require_once(_base_.'/lib/lib.userselector.php');
		require_once(_adm_.'/lib/lib.field.php');

		$aclManager =Docebo::user()->getACLManager();

		$this->directory = new UserSelector();
		$this->groupFilter_idst = $aclManager->getGroupST($this->groupFilter);

		// load language for fields names
		$lang_dir = DoceboLanguage::createInstance('admin_directory', 'framework');
		$fl = new FieldList();
		$this->fl = $fl;
		
		// root and root descendant
		$tmp = $aclManager->getGroup( false, '/oc_0' );
		$arr_idst[] = $tmp[0];
		$this->tree_oc = $tmp[0];
		$tmp = $aclManager->getGroup( false, '/ocd_0' );
		$arr_idst[] = $tmp[0];
		$this->tree_ocd = $tmp[0];
		
		// tree folder selected
		if( $this->tree != 0 ) {

			$arr_groupid = $aclManager->getGroupsId( $arr_idst );
			foreach( $arr_groupid as $key => $val ) {
				$arr_groupid[$key] = substr_replace($val, '/ocd', 0, 3);
			}
			$arr_result = $aclManager->getArrGroupST( $arr_groupid );

			list($this->tree_desc) = array_values($arr_result);
			$arr_idst[] = $this->tree;
			$arr_idst[] = $this->tree_desc;		
		}
		$arr_fields = $fl->getFieldsFromIdst($arr_idst);

		// generating cols descriptor
		$this->cols_descriptor = NULL;
		if( $this->dbconn === NULL ) {
			$this->dbconn = $GLOBALS['dbConn'];
		}
		$query = "SHOW FIELDS FROM ".$GLOBALS['prefix_fw']."_user";
		$rs = sql_query( $query, $this->dbconn );
		if( $rs === FALSE ) {
			$this->last_error = Lang::t('_OPERATION_FAILURE', 'standard').$query.' ['.sql_error().']';
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

		sql_free_result( $rs );

		foreach($arr_fields as $field_id => $field_info) {
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
		
		$this->cols_descriptor[] =
			array(  DOCEBOIMPORT_COLNAME => 'tree_code',
				DOCEBOIMPORT_COLID => 'tree_code',
				DOCEBOIMPORT_COLMANDATORY => FALSE,
				DOCEBOIMPORT_DATATYPE => 'text');
		
		$this->arr_fields = $arr_fields;
		
		$this->index = 0;
		$this->eof = true;
		
		if ($this->org_chart_destination == 0) {
            $q = "SELECT idst from core_user";
            $r =  sql_query($q);
             while(list($idstMember) = sql_fetch_row($r)) {
                    $this->user_org_chart[$idstMember][0] = 0;
             }
        } else {
            $match = array();
		    $this->org_chart_name = array();
		    $this->org_chart_group = array();
		    $this->user_org_chart = array();

		    // cache org_chart group
		    $this->org_chart_group = $aclManager->getBasePathGroupST('/oc');

		    $query = " SELECT id_dir, translation "
				    ." FROM ".$GLOBALS['prefix_fw']."_org_chart"
				    ." WHERE lang_code = '".getLanguage()."'";
		    $result = sql_query($query);
		    while(list($id_dir, $dir_name) = sql_fetch_row($result)) {
                if ($id_dir == $this->org_chart_destination) {		
			        $valid = preg_match('/'.$this->preg_match_folder.'/i', $dir_name, $match);
			        if($valid) $dir_name = $match[1];
		        
			        $this->org_chart_name[$dir_name] = $id_dir;
			        $idst_group = $this->org_chart_group['/oc_'.$id_dir];
						        
			        $query_idstMember = "SELECT idstMember"
								        ." FROM ".$GLOBALS['prefix_fw']."_group_members "
								        ." WHERE idst = '".$idst_group."'";
			        $re = sql_query($query_idstMember);
			        while(list($idstMember) = sql_fetch_row($re)) {
				        
				        if(!isset($this->user_org_chart[$idstMember])) $this->user_org_chart[$idstMember] = array();
				        $this->user_org_chart[$idstMember][$id_dir] = $id_dir;
			        }
                }
		    }
        }    
		return TRUE;
	}
	
	function close() {
		$this->directory = NULL;
		$this->tree_view = NULL;
		$this->people_view = NULL; 
		$this->cols_descriptor = NULL;
		$this->arr_idst_inserted = array();
	}

	function get_type_name() { return "docebo-users"; }	 
	
	function get_type_description() { return "connector to docebo users"; }	 	

	function get_name() { return $this->name; }	 	

	function get_description() { return $this->description; }	 	

	function is_readonly() { return (bool)($this->readwrite & 1); }

	function is_writeonly() { return (bool)($this->readwrite & 2); }
	
	function get_tot_cols(){
		return count( $this->cols_descriptor );
	}

	function get_cols_descripor() {
		return $this->cols_descriptor;
	}
	
	function get_first_row() {
		
		$this->simplecols = array();
		$export = array();
		foreach($this->cols_descriptor as $field_id => $field_info) {
			
			$this->simplecols[$field_info[DOCEBOIMPORT_COLID]] = $field_info[DOCEBOIMPORT_COLNAME];
			$export[] = $field_info[DOCEBOIMPORT_COLNAME];
		}
		
		return $export;
	}
	
	function get_next_row() {

		$pdr = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
        $pdr->idFilters = array(key($this->user_org_chart));
        next($this->user_org_chart);            
        $this->data = $pdr->getRows();
		$row = sql_fetch_row($this->data); //print_r($row);
		if($row == false) {
			$this->eof = true;
			return false;
		}
		
		$export = array();
		
		//find user field value
		foreach($this->simplecols as $field_id => $name) {
			
			if(is_numeric($field_id)) {
				
				$pluto = $this->fl->fieldValue((int)$field_id, array($row[0]));
				
				list(,$export[]) = each($pluto);
			} else {
				
				switch($field_id) {
					case "userid" : 	{ $export[] = substr($row[1], 1);  };break;
					case "firstname" : 	{ $export[] = $row[2]; };break;
					case "lastname" : 	{ $export[] = $row[3]; };break;
					case "pass" : 		{ $export[] = '*****'; };break;
					case "email" : 		{ $export[] = $row[4]; };break;
					case "signature" : 	{ $export[] = $row[6]; };break;
				}
			}
		}
		
		
		
		$this->index++;
		return $export;
	}
	
	function is_eof() {
		
		return $this->eof;
	}
	
	function get_row_index() {
		return $this->index;
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

	function get_row_bypk( $pk ) {
		// create field filter
		$arr_filter = array();
		foreach( $pk as $fieldname => $fieldvalue ) {
			if( in_array($fieldname, $this->all_cols) )
				$arr_filter[] = array('fieldname' => $fieldname, 'field_type' => 'text', 'value' => $fieldvalue );
			else
				$arr_filter[] = array(FIELD_INFO_ID => $fieldname, FIELD_INFO_TYPE => 'text', 'value' => $fieldvalue );
		}
		require_once(_adm_.'/lib/lib.directory.php');
		$this->people_view = new PeopleListView();
		$this->people_view->data = new PeopleDataRetriever();
		$this->people_view->data->idFilters = NULL;
		$this->people_view->data->resetFieldFilter();
		$this->people_view->data->resetCustomFilter();
		$this->people_view->addFieldFilters($arr_filter);
		$this->people_view->data->getRows(0,1);
		$arr_result = $this->people_view->data->fetchRecord();
		
		if( $arr_result === FALSE )
			return FALSE;
		return $arr_result;
	}

	function add_row( $row, $pk ) {
		
		foreach($pk as $key => $val) {
			if($val !== false)
				$pk[$key] = addslashes(trim($val));
		}
		foreach($row as $key => $val) {
			if($val !== false)
				$row[$key] = addslashes(trim($val));
		}
		
		$userid 	= $row['userid'];
		$firstname 	= $row['firstname'];
		$lastname 	= $row['lastname'];
		$pass 		= $row['pass'];
		$email 		= $row['email'];
		$tree_code 	= $row['tree_code'];
		
		$force_change = '';
		switch ($this->pwd_force_change_policy) {
			case "by_setting": $force_change = Get::sett('pass_change_first_login', 'off') == 'on' ? 1 : 0; break;
			case "true": $force_change = 1; break;
			case 'false': $force_change = 0; break;
		}
		
		$arr_user = $this->get_row_bypk($pk);

		if( $arr_user === FALSE ) {
			if( $firstname === NULL || $firstname === '') 	$firstname 	= $this->default_cols['firstname'];
			if( $lastname === NULL || $lastname === '') 	$lastname 	= $this->default_cols['lastname'];
			if( $pass === NULL || $pass === '')				$pass 		= $this->default_cols['pass'];
			if( $email === NULL || $email === '')			$email 		= $this->default_cols['email'];
			
			$idst = Docebo::aclm()->registerUser(
				$userid,
				$firstname,
				$lastname,
				$pass,
				$email,
				'', //avatar
				'', //signature
				FALSE, //already_encripted
				FALSE, //idst
				'', //pwd_expire_at
				$force_change,
				FALSE, //facebook_id
				FALSE, //twitter_id
				FALSE, //linkedin_id
				FALSE //google_id
			);

		} else {
			$idst = $arr_user['idst'];
			if( $firstname === NULL || $firstname === '') 	$firstname = FALSE;
			if( $lastname === NULL || $lastname === '') 	$lastname = FALSE;
			if( $pass === NULL || $pass === '')				$pass = FALSE;
			if( $email === NULL || $email === '')			$email = FALSE;
			$result = Docebo::aclm()->updateUser(
				$idst,
				$userid,
				$firstname,
				$lastname,
				$pass, 
				$email,
				FALSE,
				FALSE,
				FALSE,
				TRUE,
				$force_change,
				FALSE, //facebook_id
				FALSE, //twitter_id
				FALSE, //linkedin_id
				FALSE //google_id
			);
			if( !$result ) {
				$this->last_error = 'error on update user<br />';
				return FALSE;				
			}
		}
		if($idst !== false) {

			//destination folder
			if ($this->org_chart_destination > 0) {
				$res = DbConn::getInstance()->query("SELECT idst_oc, idst_ocd FROM %adm_org_chart_tree WHERE idOrg = " . (int) $this->org_chart_destination);
				if ($res && DbConn::getInstance()->num_rows($res) > 0) {
					list($oc, $ocd) = DbConn::getInstance()->fetch_row($res);
					if ($oc && $ocd) {
						Docebo::aclm()->addToGroup($oc, $idst);
						Docebo::aclm()->addToGroup($ocd, $idst);
					}
				}
			}

			if( $this->cache_inserted)
				$this->arr_idst_inserted[] = $idst;
			if( $this->sendnotify == 1 ) {
				// - Send alert ----------------------------------------------------
			}	
			
			//Assign the user to correct folder
			
			if($tree_code) {
				
				$tree_codes = explode(';', $tree_code);
				
				$readed_folders = array();
				while(list(, $tree_code)= each($tree_codes)) {
					
					$dir_name = stripslashes($tree_code);
					if(isset($this->org_chart_name[$dir_name])) {
						
						$readed_folders[] = $this->org_chart_name[$dir_name];
					}
				}
				if(!isset($this->user_org_chart[$idst])) $this->user_org_chart[$idst] = array();
				$to_add = array_diff($readed_folders, $this->user_org_chart[$idst]);
				$to_del = array_diff($this->user_org_chart[$idst], $readed_folders);
		
				foreach($to_add as $id_dir) {
					
					$idst_oc_folder = $this->org_chart_group['/oc_'.$id_dir];
					$idst_ocd_folder = $this->org_chart_group['/ocd_'.$id_dir];
						
					Docebo::aclm()->addToGroup($idst_oc_folder, $idst);
					Docebo::aclm()->addToGroup($idst_ocd_folder, $idst);
				}
				foreach($to_del as $id_dir) {
					
					$idst_oc_folder = $this->org_chart_group['/oc_'.$id_dir];
					$idst_ocd_folder = $this->org_chart_group['/ocd_'.$id_dir];
						
					Docebo::aclm()->removeFromGroup($idst_oc_folder, $idst);
					Docebo::aclm()->removeFromGroup($idst_ocd_folder, $idst);
				}
			}
			//  -------------------------------------------------------------------
			$result = TRUE;
			Docebo::aclm()->addToGroup($this->tree_oc, $idst);
			Docebo::aclm()->addToGroup($this->tree_ocd, $idst);
			
			if ($this->tree != $this->tree_oc) {
				Docebo::aclm()->addToGroup($this->tree,$idst );
				Docebo::aclm()->addToGroup($this->tree_desc,$idst );
			}
			
			// add to group level
			$userlevel = Docebo::aclm()->getGroupST(ADMIN_GROUP_USER);
			Docebo::aclm()->addToGroup($userlevel,$idst );

			//-save extra field------------------------------------------
			
			$arr_fields_toset = array();
			foreach( $this->arr_fields as $field_id => $field_info)
				if(isset($row[$field_id]) && $row[$field_id] !== false)
					$arr_fields_toset[$field_id] = $row[$field_id];

			if( count($arr_fields_toset) > 0 )
				$result = $this->fl->storeDirectFieldsForUser($idst, $arr_fields_toset);
			//-----------------------------------------------------------
			if( !$result ) {
				$this->last_error = 'error in store custom fields<br />';
			}
			return $result;
		} else {
			$this->last_error = 'error on register user<br />';
			return FALSE;
		}		
	}
	
	function delete_bypk( $pk ) {
		$arr_people = $this->get_row_bypk($pk);
		if( $arr_people === FALSE )
			return FALSE;
		else {
			if( $this->canceled == '1' )
				Docebo::aclm()->suspendUser($arr_people['idst']);
			else
				Docebo::aclm()->deleteUser($arr_people['idst']);
		}
	}

	function delete_all_filtered( $arr_pk ) {
		// retrieve all users idst
		$arr_idst = array();
		foreach( $arr_pk as $pk ) {
			$arr_user = $this->get_row_bypk( $pk );
			$arr_idst[] = $arr_user['idst'];
		}
		$this->people_view->data->resetFieldFilter();
		$this->people_view->data->resetCustomFilter();
		$this->people_view->data->addFilter($arr_idst);
		$arr_idst_todelete = $this->people_view->data->getAllRowsIdst();
		foreach( $arr_idst_todelete as $id_st ) {
			if( $this->canceled == '1' )
				Docebo::aclm()->suspendUser($id_st);
			else
				Docebo::aclm()->deleteUser($id_st);
		}
	}

	function delete_all_notinserted() {
		$this->people_view->data->resetFieldFilter();
		$this->people_view->data->resetCustomFilter();
		$this->people_view->addFieldFilters(array(array('fieldname'=>'valid','value'=>'1','field_type'=>'text')));
		$this->people_view->data->addNotFilter($this->arr_idst_inserted);
		$idst_rs = $this->people_view->data->getAllRowsIdst();
		$counter = 0;
		if( $idst_rs !== FALSE )
			while(list($id_st) = sql_fetch_row($idst_rs)) {
				if( $this->canceled == '1' )
					Docebo::aclm()->suspendUser($id_st);
				else
					Docebo::aclm()->deleteUser($id_st);
				$counter++;
			}
		return $counter;			
	}
	
	function get_error() { return $this->last_error; }

}


/**
 * The configurator for docebousers connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboUsersUI extends DoceboConnectorUI {
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
	
	var $directory = NULL;

			
	function DoceboConnectorDoceboUsersUI( &$connector ) {
		require_once(_base_.'/lib/lib.userselector.php');
		$this->connector = $connector;
		$this->directory = new UserSelector();
	}
	
	function _get_base_name() { return 'docebousersuiconfig'; }
		
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
			// $this->post_params['org_chart_destination'] = $this->org_chart_destination;
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
			if( isset($arr_new_params['reset']) ) {
				$this->post_params['tree'] = '';
			} elseif( $this->directory->isParseDataAvailable($post) ) {
				$arr_selection = $this->directory->getSelection($post);
				list( $this->post_params['tree'] ) = $this->directory->getSelection($post);				
			}
			$this->directory->resetSelection(array($this->post_params['tree']));

			$this->post_params['org_chart_destination'] =
							isset($arr_new_params['org_chart_destination'])
							? (int)$arr_new_params['org_chart_destination']
							: $this->post_params['org_chart_destination'];
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
		switch( $this->post_params['step'] ) {
			case '0':
				$this->_set_step_info( '1', '0', TRUE, FALSE, FALSE );
			break;
		  	case '1':
			  	$this->_set_step_info( '1', '0', FALSE, TRUE, TRUE );
		  	break;
		  	case '2':
			  	$this->_set_step_info( '2', '1', FALSE, TRUE, TRUE );
		  	break;
		}
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
			case '1':
				$out .= $this->_step1();
			break;
			case '2':
				$out .= $this->_step2();
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
	  	// ---- access type read/write -----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_SENDNOTIFY'), 
		  									$this->_get_base_name().'_sendnotify', 
											$this->_get_base_name().'[sendnotify]',
											array( 	$this->lang->def('_SEND')  => '1', 
													$this->lang->def('_DONTSEND') => '2'), 
											$this->post_params['sendnotify']);											
		// ---- suspend users ----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_CANCELED_USERS'), 
		  									$this->_get_base_name().'_canceled', 
											$this->_get_base_name().'[canceled]',
											array( 	$this->lang->def('_SUSPENDED')  => '1', 
													$this->lang->def('_DEL') => '2'), 
											$this->post_params['canceled']);
											
	  	$out .= $this->form->getTextfield(	$this->lang->def('_PREG_MATCH_FOLDER'), 
											$this->_get_base_name().'_preg_match_folder', 
											$this->_get_base_name().'[preg_match_folder]', 
											255, 
											$this->post_params['preg_match_folder']);

			$out .= $this->form->getRadioSet(Lang::t('_FORCE_PASSWORD_CHANGE', 'admin_directory'),
											$this->_get_base_name().'_pwd_force_change_policy',
											$this->_get_base_name().'[pwd_force_change_policy]',
											array(											
												Lang::t('_NO', 'standard') => 'false',
												Lang::t('_YES', 'standard') => 'true',
												Lang::t('_SERVERINFO', 'configuration') => 'by_setting',
												Lang::t('_DO_NOTHING', 'preassessment') => 'do_nothing'
											),
											$this->post_params['pwd_force_change_policy']);
											
		return $out;
	}
	
	function _step1() {
		$GLOBALS['page']->add($this->form->getLineBox( 	$this->lang->def('_NAME'),
											$this->post_params['name'] ));


		// ---- the tree selector -----
		/* $GLOBALS['page']->add($this->lang->def('_TREE_INSERT'));
		$this->directory->show_orgchart_selector = FALSE;
		$this->directory->show_orgchart_simple_selector = TRUE;
		$this->directory->multi_choice = FALSE;
		$this->directory->selector_mode = TRUE;
		$this->directory->loadOrgChartView(); */
		$umodel = new UsermanagementAdm();
		$out = $this->form->getDropdown(
				Lang::t('_DIRECTORY_MEMBERTYPETREE', 'admin_directory'),
				$this->_get_base_name().'_org_chart_destination',
				$this->_get_base_name().'[org_chart_destination]',
				$umodel->getOrgChartDropdownList(),
				$this->post_params['org_chart_destination']
			);
		// ---- add a button to reset selection -----
		/* $out = $this->form->getButton(	$this->_get_base_name().'_reset',
										$this->_get_base_name().'[reset]', 
										$this->lang->def('_RESET')); */

		return $out;
	}
	
	function _step2() {
		$out = $this->form->getLineBox( 	$this->lang->def('_NAME'),
											$this->post_params['name'] );

	  	$out .= $this->form->getTextfield(	$this->lang->def('_GROUP_FILTER'), 
											$this->_get_base_name().'_group', 
											$this->_get_base_name().'[group]', 
											255, 
											$this->post_params['group']);
		return $out;
	}
	
}

function docebousers_factory() {
	return new DoceboConnectorDoceboUsers(array());
}

?>
