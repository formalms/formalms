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
 * class for define docebo organization chart connection to data source.
 * @package admin-core
 * @subpackage io-operation
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboOrgChart extends DoceboConnector {
  
  	var $last_error = "";
 	var $all_cols = array(	'idOrg','idParent','path','level');
	var $mandatory_cols = array('path');
	var $default_cols = array();
	var $ignore_cols = array( 'idOrg','idParent','level' );
 	var $valid_filed_type = array( 'textfield','date','dropdown','yesno');
	var $cols_descriptor = NULL;
	var $dbconn = NULL;
	var $tree = 0;			// idst where to insert the imported tree
	var $tree_desc = 0;		// the descendant idst
	
	var $default_lang = '';

	var $readwrite = 0; // read = 1, write = 2, readwrite = 3
	var $canceled = 1;  // don't remove = 1, remove = 2

	var $name = "";
	var $description = "";
	
	var $directory = NULL;
	var $tree_view = NULL;
		
	var $arr_folders = array();
	/**
	 * This constructor require the source file name
	 * @param array $params the array of params
	 *		- 'filename' => name of the file (required)
	 *		- 'first_row_header' => bool TRUE if first row is header (Optional, default = TRUE )
	 *		- 'separator' => string a char with the fields separator (Optional, default = ,)
	**/
	function DoceboConnectorDoceboOrgChart( $params ) {
	  	if( $params === NULL ) 
	  		return;	// connector
	  	else
			$this->set_config( $params );	// connection
	}
	
	function get_config() {
		return array( 	'tree' => $this->tree,
						'canceled' => $this->canceled,
						'readwrite' => $this->readwrite,
						'name' => $this->name,
						'description' => $this->description,
						'default_lang' => $this->default_lang );
	}
	
	function set_config( $params ) {
		if( isset($params['tree']) )		$this->tree = $params['tree'];
		if( isset($params['canceled']) )	$this->canceled = $params['canceled'];		
		if( isset($params['readwrite']) )	$this->readwrite = $params['readwrite'];
		if( isset($params['name']) )		$this->name = $params['name'];
		if( isset($params['description']) )	$this->description = $params['description'];
		if( isset($params['default_lang']) )$this->default_lang = $params['default_lang'];
	}

	function get_configUI() {
		return new DoceboConnectorDoceboOrgChartUI($this);
	}
	
	function connect() {
		require_once(_base_.'/lib/lib.userselector.php');

		require_once(_adm_.'/modules/org_chart/tree.org_chart.php');
		$this->directory = new UserSelector();
		//$this->tree_view = $this->directory->getTreeView_OrgView();
		$orgDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
		$this->tree_view = new TreeView_OrgView($orgDb, 'organization_chart', Get::sett('title_organigram_chart'));
		$this->tree_view->aclManager =& Docebo::aclm();

		list($this->tree_desc) = $this->tree_view->tdb->getDescendantsSTFromST(array($this->tree));

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		// load language for fields names
		$lang_dir = DoceboLanguage::createInstance('admin_directory', 'framework');
		$fl = new FieldList();
		$fl->setGroupFieldsTable($GLOBALS['prefix_fw'].ORGCHAR_FIELDTABLE);
		$arr_fields = $fl->getAllFields();
		
		$this->cols_descriptor = NULL;
		if( $this->dbconn === NULL ) {
			$this->dbconn = $GLOBALS['dbConn'];
		}
	
		$table_fields = array( 	array('Field' => 'idOrg','Type' => 'text' ),
								array('Field' => 'idParent','Type' => 'text' ),
								array('Field' => 'path','Type' => 'text' ),
								array('Field' => 'level','Type' => 'text' )
							);
	
		$this->cols_descriptor = array();
		foreach( $table_fields as $field_info ) {
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

		foreach($arr_fields as $field_id => $field_info) {
			if( in_array($field_info[FIELD_INFO_TYPE],$this->valid_filed_type) ) {
				$this->cols_descriptor[] =
							array(  DOCEBOIMPORT_COLNAME => $field_info[FIELD_INFO_TRANSLATION],
									DOCEBOIMPORT_COLID => $field_id,
									DOCEBOIMPORT_COLMANDATORY => FALSE,
									DOCEBOIMPORT_DATATYPE => 'text',
									);
			}
		}
		
		$this->tree_view->tdb->setFolderLang($this->default_lang);
		$arr_foldersid = $this->tree_view->tdb->getFoldersIdFromIdst(array($this->tree));
		$folderid = $arr_foldersid[$this->tree];
		
		$root_folder = $this->tree_view->tdb->getFolderById($folderid);
		$arr_id = $this->tree_view->tdb->getDescendantsId($root_folder);
		$this->arr_folders = array();
		if( $arr_id !== FALSE ) {
			$coll_folders = $this->tree_view->tdb->getFoldersCollection($arr_id);
			// make the new structure
			$curr_path = array();
			while( ($folder = $coll_folders->getNext()) !== FALSE ) {
				$curr_path = array_slice($curr_path,0,$folder->level-$root_folder->level-1);
				$curr_path[] = $folder->otherValues[ORGDB_POS_TRANSLATION];
				$this->arr_folders[implode('/',$curr_path)] = array('id'=>$folder->id,'inserted'=>FALSE);
			}
		}

		return TRUE;
	}
	
	function close() {
		$this->directory = NULL;
		$this->tree_view = NULL;
		$this->cols_descriptor = NULL;
		$this->arr_folders = array();
	}

	function get_type_name() { return "docebo-orgchart"; }	 
	
	function get_type_description() { return "connector to docebo organization chart"; }	 	

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
		if( isset( $this->arr_folders[$pk['path']] ) )
			return $this->arr_folders[$pk['path']];
		else
			return FALSE;
	}

	function add_row( $row, $pk ) {
		$path = $row['path'];
		$path_tokens = explode('/',$path);
		$parent_path = implode('/',array_slice($path_tokens,0,-1));
		$name = $path_tokens[count($path_tokens)-1];
		
		$arr_folder = $this->get_row_bypk(array('path'=> $path));
		if( $parent_path == '' ) {
			$this->tree_view->tdb->setFolderLang($this->default_lang);
			$arr_foldersid = $this->tree_view->tdb->getFoldersIdFromIdst(array($this->tree));
			$parent_id = $arr_foldersid[$this->tree];
		} else {
			$arr_parent_folder = $this->get_row_bypk(array('path'=> $parent_path));
			$parent_id = $arr_parent_folder['id'];
		}
		
		
		// ---- Extract extra languages title
		$array_lang = Docebo::langManager()->getAllLangCode();
		if( isset($row['lang_titles']))
			$folderName = addslashes($row['lang_titles']);
		else
			$folderName = array();
		foreach( $array_lang as $lang) 
			if( !isset($folderName[$lang]) )
				$folderName[$lang] = addslashes($name);
		
		if( $arr_folder === FALSE ) {
                        //VECCHIO SISTEMA vedi Update  $id = $this->tree_view->tdb->addFolderByIdTranslation( $parent_id, $folderName );
                        require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));
                        $umodel = new UsermanagementAdm();
                        $id = $umodel->addFolder($parent_id, $folderName, $row['code']);

			$this->arr_folders[$path] = array( 'id' => $id, 'inserted' => TRUE );
		} else {
			$this->tree_view->tdb->updateFolderByIdTranslation( $arr_folder['id'], $folderName );
			$this->arr_folders[$path]['inserted'] = TRUE;
		}
		
		// ---- Add custom fields
		if( isset($row['custom_fields']) )
			$this->_add_custom_fields( $this->arr_folders[$path]['id'], $row['custom_fields'] );
		else
			$this->_add_custom_fields( $this->arr_folders[$path]['id'], array() );
		
		return TRUE;
	}
	
	function delete_bypk( $pk ) {
		$arr_folder = $this->get_row_bypk($pk);
		if( $arr_folder === FALSE )
			return FALSE;
		else {
			if( $this->canceled == '2' ) {
				$folder = $this->tree_view->tdb->getFolderById($arr_folder['id']);
				if( $folder !== NULL )	// already deleted
					$this->tree_view->tdb->deleteTreeById($arr_folder['id']);
			} 
			return TRUE;
		}
	}

	function delete_all_filtered( $arr_pk ) {
		// retrieve all users idst
		if( $this->canceled == '1' )
			return TRUE;
		foreach( $this->arr_folders as $path => $arr_folder ) {
			if( !in_array($path, $arr_pk) )
				$this->delete_bypk(array('path'=>$path));			
		}
	}

	function delete_all_notinserted() {
		if( $this->canceled == '1' )
			return 0;
		$counter = 0;
		foreach( $this->arr_folders as $path => $arr_folder ) {
			if( $arr_folder['inserted'] === FALSE ) {
				$this->delete_bypk(array('path'=>$path));
				$counter++;
			}
		}
		return $counter;
	}
	
	function get_error() { return $this->last_error; }

	/**
	 * @param string $id_folder folder destination of fields
	 * @param array $arr_fields an array with fields to attach to folder
	 * 							any element of folder is an array:
	 * 							key => array( 'fvalue', 'mandatory' )
	 * 							- key is the field name in current language
	 * 							- fvalue is the value of the field
	 * 							- mandatory is TRUE if this field is mandatory
	 */
	function _add_custom_fields($id_folder, $arr_fields) {
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		
		$fl = new FieldList();
		$fl->setGroupFieldsTable($GLOBALS['prefix_fw'].ORGCHAR_FIELDTABLE);
		$fl->setFieldEntryTable($GLOBALS['prefix_fw'].ORGCHAR_FIELDENTRYTABLE);
		
		$arr_all_fields = $fl->getFlatAllFields(false, false, $this->default_lang);

		// remove all fields 
		foreach( $arr_all_fields as $id_field => $ftranslation ) {
			$fl->removeFieldFromGroup($id_field, $id_folder);
		}
		
		$arr_all_fields_translation = array_flip($arr_all_fields);

		$arr_fields_value = array();		
		// add selected fields
		foreach( $arr_fields as $field_translation => $field_data ) {
			if( isset($arr_all_fields_translation[$field_translation]) ) {
				$field_id = $arr_all_fields_translation[$field_translation];
				$fl->addFieldToGroup( 	$field_id, 
										$id_folder,
										$field_data['mandatory']
									);
				$arr_fields_value[$field_id] = $field_data['fvalue'];
			} else {
				die("Field non trovato: $field_translation");
			}
		}
		$fl->storeDirectFieldsForUser($id_folder, $arr_fields_value, FALSE );
				
	}

}


/**
 * The configurator for docebousers connectors
 * @package		Docebo
 * @subpackage	ImportExport
 * @version 	1.1
 * @author		Emanuele Sandri <emanuele (@) docebo (.) com>
 **/
class DoceboConnectorDoceboOrgChartUI extends DoceboConnectorUI {
	var $connector = NULL;
	var $post_params = NULL;
	var $sh_next = TRUE;
	var $sh_prev = FALSE;
	var $sh_finish = FALSE;
	var $step_next = '';
	var $step_prev = '';
	
	var $directory = NULL;

			
	function DoceboConnectorDoceboOrgChartUI( &$connector ) {
		require_once(_base_.'/lib/lib.userselector.php');
		$this->connector = $connector;
		$this->directory = new UserSelector();
	}
	
	function _get_base_name() { return 'doceboorgchartuiconfig'; }
		
	function get_old_name() { return $this->post_params['old_name']; }
	/** 
	 * All post fields are in array 'doceboorgchartuiconfig'
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
			if( isset($arr_new_params['reset']) ) {
				$this->post_params['tree'] = '';
			} elseif( $this->directory->isParseDataAvailable($post) ) {
				$arr_selection = $this->directory->getSelection($post);
				list( $this->post_params['tree'] ) = $this->directory->getSelection($post);				
			}
			$this->directory->resetSelection(array($this->post_params['tree']));
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
		// ---- remove or not folders ----
	  	$out .= $this->form->getRadioSet( 	$this->lang->def('_CANCELED_FOLDER'), 
		  									$this->_get_base_name().'_canceled', 
											$this->_get_base_name().'[canceled]',
											array( 	$this->lang->def('_DONTDELETE') => '1', 
													$this->lang->def('_DEL') => '2'), 
											$this->post_params['canceled']);	
		// ---- default lang ----
		$languages = Docebo::langManager()->getAllLangCode();
		$lang_key = array(); 
		for( $index = 0; $index < count($languages); $index++ ) 
			$lang_key[$languages[$index]] = $languages[$index]; 
		$out .= $this->form->getDropdown( 	$this->lang->def('_LANGUAGE'),
											$this->_get_base_name().'default_lang',
											$this->_get_base_name().'[default_lang]',
											$lang_key,
											$this->post_params['default_lang']);
		
		return $out;
	}
	
	function _step1() {
		/*$GLOBALS['page']->add($this->form->getLineBox( 	$this->lang->def('_NAME'),
											$this->post_params['name'] ));

*/
		// ---- the tree selector -----
		//$GLOBALS['page']->add($this->lang->def('_TREE_INSERT_FOLDER'));
		$this->directory->show_user_selector = false;
		$this->directory->show_group_selector = false;
		$this->directory->show_orgchart_selector = true;
		$this->directory->show_orgchart_simple_selector = true;
		$this->directory->show_fncrole_selector = false;
		
		$this->directory->multi_choice = FALSE;
		$this->directory->selector_mode = TRUE;
		$this->directory->loadSelector(
			'index.php?modname=iotask&op=display&addconnection&gotab=connections',
			$this->lang->def('_TREE_INSERT_FOLDER'),
			'',
			false
		);//loadOrgChartView();
		// ---- add a button to reset selection -----
		$out = $this->form->getButton(	$this->_get_base_name().'_reset', 
										$this->_get_base_name().'[reset]', 
										$this->lang->def('_RESET'));
				
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

function doceboorgchart_factory() {
	return new DoceboConnectorDoceboOrgChart(array());
}

?>
