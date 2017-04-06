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
 * @package admin-library
 * @subpackage user
 * @version $Id:$
 */

require_once(_base_.'/lib/lib.listview.php' );

define( 'DIRECTORY_TAB', 'DIRECTORY_TAB' );
define( 'PEOPLEVIEW_TAB', 'PEOPLEVIEW_TAB' );
define( 'GROUPVIEW_TAB', 'GROUPVIEW_TAB' );
define( 'ORGVIEW_TAB', 'ORGVIEW_TAB' );

define( 'DIRECTORY_ID', 'directory_id');
define( 'DIRECTORY_OP_SELECTITEM', 'directory_op_selectitem');
define( 'DIRECTORY_OP_SELECTRADIO', 'directory_op_selectradio' );
define( 'DIRECTORY_OP_SELECTMONO', 'directory_op_selectmono' );
define( 'DIRECTORY_ID_PRINTEDITEM', 'directory_id_printeditem');
define( 'DIRECTORY_OP_ADDFIELD', 'directory_op_addfield');
define( 'DIRECTORY_OP_SELECTFOLD', 'directory_op_selectfold' );
define( 'DIRECTORY_ID_PRINTEDFOLD', 'directory_op_printedfold' );
define( 'DIRECTORY_OP_SELECTALL', 'directory_op_selectall' );
define( 'DIRECTORY_OP_DESELECTALL', 'directory_op_deselectall' );

define( 'DIRECTORY_CFIELD', 'DIR_CFIELD' );
define( 'DIRECTORY_ORDER', 'DIR_ORDER' );

if (!defined("GROUP_FIELD_NO")) define("GROUP_FIELD_NO","No");
if (!defined("GROUP_FIELD_NORMAL")) define("GROUP_FIELD_NORMAL","Normal");
if (!defined("GROUP_FIELD_DESCEND")) define("GROUP_FIELD_DESCEND","Descend");
if (!defined("GROUP_FIELD_INHERIT")) define("GROUP_FIELD_INHERIT","Inherit");

class PeopleListView extends ListView {
	var $lang = NULL;
	var $selector_mode = FALSE;
	var $tree_mode = FALSE;
	var $itemSelectedMulti = array();
	var $printedItems = array();
	var $link_pagination = '';

	var $show_flat_mode_flag = FALSE;
	var $flat_mode = FALSE;

	var $more_filter = 'less';
	// FieldList object for custom fields management
	var $field_list = NULL;
	// array of custom fields selected in columns
	var $cFields = NULL;
	// number of customizable columns in listview
	var $nFields = 3;

	// list of all filter fields with their names
	var $arr_fields_translation = NULL;

	// list of all column fields woth theyr translation
	var $arr_fields_col_translation = NULL;

	// list of currents filter fields
	var $arr_fields_filter = array();

	// Use multi selection?
	var $use_multi_sel = false;

	// Extend selector object
	var $sel_extend = NULL;

	// Set this member to TRUE to select all items in selection
	var $select_all = FALSE;
	var $deselect_all = FALSE;

	// Order columns. An array of fields => boolean
	// where TRUE = DESC
	var $arr_fields_order = array(array('userid'=>FALSE));

	var $admins_user = array();

	var $mod_perm;
	var $del_perm;

	var $show_simple_filter = FALSE;

	var $lms_editions_filter = FALSE;

	var $hide_anonymous = FALSE;
	var $hide_suspend	= TRUE;

	// array of natural fields
	// key is fieldid as in db (used in db for filter)
	// value is an array with:
	//		filter_field : TRUE if this is a field for filter (find)
	//		filter_base  : TRUE put this field in base filter section
	//		column_field : TRUE if this is a field for custom columns
	//		field_type   : the type of field as for custom fields
	var $add_nat_fields = array(
								'userid' => array(
													'fieldname' => 'userid',
													'filter_field' => TRUE,
													'filter_base' => TRUE,
													'column_field' => FALSE,
													'field_type' => 'textfield',
													),
								'firstname' => array(
													'fieldname' => 'firstname',
													'filter_field' => TRUE,
													'filter_base' => TRUE,
													'column_field' => FALSE,
													'field_type' => 'textfield'
													),
								'lastname' => array(
													'fieldname' => 'lastname',
													'filter_field' => TRUE,
													'filter_base' => TRUE,
													'column_field' => FALSE,
													'field_type' => 'textfield'
													),
								'email' => array(
													'fieldname' => 'email',
													'filter_field' => TRUE,
													'filter_base' => FALSE,
													'column_field' => TRUE,
													'field_type' => 'textfield'
													)/*,
								'avatar' => array(
													'fieldname' => 'avatar',
													'filter_field' => TRUE,
													'filter_base' => FALSE,
													'column_field' => TRUE,
													'field_type' => 'upload'
													)*/

								);
	var $anonymous_idst;

	var $_expand_user;

	var $editions;

	function _getOpEditItemId() 	{ return '_listview_opedititem_';	}
	function _getEditLabel() { return $this->lang->def('_MOD'); }
	function _getEditAlt() { return $this->lang->def('_MOD'); }
	function _getEditImage() { return getPathImage().'standard/edit.png'; }

	function _getOpDeleteItemId() 	{ return '_listview_opdeleteitem_';	}
	function _getDeleteLabel() { return $this->lang->def('_DEL'); }
	function _getDeleteAlt() { return $this->lang->def('_DEL'); }
	function _getDeleteImage() { return getPathImage().'standard/delete.png'; }

	function _getOpSuspendItemId() 	{ return '_listview_opsuspenditem_';	}
	function _getSuspendLabel() { return $this->lang->def('_SUSPEND'); }
	function _getSuspendAlt() { return $this->lang->def('_SUSPEND'); }
	function _getSuspendImage() { return getPathImage().'standard/suspend.gif'; }

	function _getOpSort() { return '_listview_opsort_'; }
	function _getSortLabel() { return $this->lang->def('_ORDER_BY'); }
	function _getSortAlt() { return $this->lang->def('_ORDER_BY'); }
	function _getSortImage( $fieldname ) {
		if( isset($this->arr_fields_order[$fieldname] ) ) {
			if( $this->arr_fields_order[$fieldname] ) {
				// DESC
				return getPathImage('fw').'standard/up_arrow.png';
			} else {
				// ASC
				return getPathImage('fw').'standard/down_arrow.png';
			}
		} else {
			return getPathImage('fw').'standard/sort.png';
		}
	}

	function _getOpRecoverItemId() 	{ return '_listview_oprecoveritem_';	}
	function _getRecoverLabel() { return $this->lang->def('_REACTIVATE'); }
	function _getRecoverAlt() { return $this->lang->def('_REACTIVATE'); }
	function _getRecoverImage() { return getPathImage().'standard/recover.gif'; }

	function _getOpRemoveItemId() 	{ return '_listview_opremoveitem_';	}
	function _getRemoveLabel() { return $this->lang->def('_DIRECTORY_REMOVEPERSON'); }
	function _getRemoveAlt() { return $this->lang->def('_DIRECTORY_REMOVEPERSON'); }
	function _getRemoveImage() { return getPathImage().'directory/de_assoc.gif'; }

	function _getOpSelectedItemId() 	{ return '_listview_opselecteduser_';	}
	function _getSelectedLabel() { return $this->lang->def('_DIRECTORY_SELECTUSER'); }
	function _getSelectedAlt() { return $this->lang->def('_DIRECTORY_SELECTUSER'); }

	function _getRowsPage() { return Get::sett('visuUser'); }

	function PeopleListView($title, &$data, &$rend, $id) {
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		$this->field_list = new FieldList();
		parent::ListView($title, $data, $rend, $id);
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');

		$field_available = 0;
		for( $i = 0; $i < $this->nFields; $i++ ) {
			$this->cFields[] = 0;
		}

		$arr_fields = $this->field_list->getAllFields();
		$this->arr_fields_translation = array();
		$this->arr_fields_col_translation = array();
		foreach( $this->add_nat_fields as $nat_id => $nat_info ) {

			if($nat_info['filter_base'] === FALSE) $field_available++;
			$this->arr_fields_translation[$nat_id] = $this->lang->def('_DIRECTORY_FILTER_'.$nat_id);
			if( $nat_info['column_field'] )
				$this->arr_fields_col_translation[$nat_id] = $this->arr_fields_translation[$nat_id];
		}
		foreach( $arr_fields as $field_info ) {

			$field_available++;
			$this->arr_fields_translation[$field_info[FIELD_INFO_ID]] = $field_info[FIELD_INFO_TRANSLATION];
			$this->arr_fields_col_translation[$field_info[FIELD_INFO_ID]] = $field_info[FIELD_INFO_TRANSLATION];
		}

		if($this->nFields > $field_available) $this->nFields = $field_available;

		$this->mod_perm = checkPerm('edituser_org_chart', true, 'directory', 'framework');
		$this->del_perm = checkPerm('deluser_org_chart', true, 'directory', 'framework');

		$acl_man =& Docebo::user()->getAclManager();
		$this->anonymous_idst = $acl_man->getAnonymousId();

		$this->_loadAdminIdst();
	}

	function printState() {
		$out = parent::printState();
		$out .= '<input type="hidden"'
			.' id="'.$this->id.'_cfield_state"'
			.' name="'.$this->id.'[cfield_state]"'
			.' value="'.urlencode(Util::serialize($this->cFields)).'" />'."\n";
		$out .= '<input type="hidden"'
			.' id="'.$this->id.'_order_state"'
			.' name="'.$this->id.'[order_state]"'
			.' value="'.urlencode(Util::serialize($this->arr_fields_order)).'" />'."\n";
		$out .= '<input type="hidden"'
			.' id="'.DIRECTORY_ID.DIRECTORY_ID_PRINTEDITEM.'"'
			.' name="'.DIRECTORY_ID.'['.DIRECTORY_ID_PRINTEDITEM.']"'
			.' value="'.urlencode(Util::serialize($this->printedItems)).'" />'."\n";
		// save state of custom columns
		Docebo::user()->preference->setPreference(
						'ui.directory.custom_columns',
						urlencode(Util::serialize($this->cFields)));
		Docebo::user()->preference->setPreference(
						'ui.directory.order_columns',
						urlencode(Util::serialize($this->arr_fields_order)));
		// save state of filter
		Docebo::user()->preference->setPreference(
						'ui.directory.filters.current',
						urlencode(Util::serialize($this->arr_fields_filter)));

		return $out;
	}

	function setLinkPagination( $link_pagination ) {
		$this->link_pagination = $link_pagination;
	}

	function _getLinkPagination() {
		return $this->link_pagination.'&amp;ord='
				.$this->_getOrd()
				.'&amp;ini=';
	}

	function _getCols() {
		require_once(_base_."/lib/lib.form.php");
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');

		$totCol = $this->data->getFieldCount();
		$colInfos = array();
		if( ($this->selector_mode) && (!$this->use_multi_sel) ) {
			$colInfos[] = array( 	'hLabel' => '',
									'hClass' => "image",
									'fieldClass' => "image",
									'data' => 'selection',
									'toDisplay' => true,
									'sortable' => false );
		}
		$colInfos[] = array( 	'hLabel' => '<span class="directory_header_label">'
											.$this->lang->def( '_USERNAME' )
											.'</span>'
											.'<input type="image" '
											.'value="'.$this->_getSortLabel().'"'
											.'name="'.$this->id.'['.DIRECTORY_ORDER.'][userid]'.'" '
											.'src="'.$this->_getSortImage('userid').'" '
											.'class="directory_order_image" '
											.' />',
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'userid',
								'toDisplay' => true,
								'sortable' => true );
		$colInfos[] = array( 	'hLabel' => '<span class="directory_header_label">'
											.$this->lang->def( '_DIRECTORY_FULLNAME' )
											.'</span>'
											.'<input type="image" '
											.'value="'.$this->_getSortLabel().'"'
											.'name="'.$this->id.'['.DIRECTORY_ORDER.'][lastname]'.'" '
											.'src="'.$this->_getSortImage('lastname').'" '
											.'class="directory_order_image" '
											.' />',
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'fullname',
								'toDisplay' => true,
								'sortable' => true );
		/*$colInfos[] = array( 	'hLabel' => $this->lang->def( '_LASTNAME' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'lastname',
								'toDisplay' => true,
								'sortable' => true );*/

		for( $i = 0; $i < $this->nFields; $i++ ) {
			$colInfos[] = array( 	'hLabel' => Form::getInputDropdown(
												'dropdown',
												DIRECTORY_CFIELD.'_'.$i,
												$this->id.'['.DIRECTORY_CFIELD.']['.$i.']',
												$this->arr_fields_col_translation,
												$this->cFields[$i],
												'onchange="submit()"'
												)
											.(is_numeric($this->cFields[$i])?'':('<input type="image" '
											.'value="'.$this->_getSortLabel().'"'
											.'name="'.$this->id.'['.DIRECTORY_ORDER.']['.$this->cFields[$i].']'.'" '
											.'src="'.$this->_getSortImage($this->cFields[$i]).'"'
											.'class="directory_order_image" '
											.' />')),
									'hClass' => "directory_custom_columns",
									'fieldClass' => "directory_custom_columns",
									'data' => DIRECTORY_CFIELD.'_'.$i,
									'toDisplay' => true,
									'sortable' => true );
		}
		//echo var_dump($colInfos);
		if( !$this->selector_mode ) {
			if( !$this->tree_mode ) {
				if($this->mod_perm) {
					$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getEditImage().'" '
												.'alt="'.$this->lang->def( '_MOD' ).'" '
												.'title="'.$this->lang->def( '_MOD' ).'" />',
											'hClass' => "image",
											'fieldClass' => "image",
											'data' => 'edit',
											'toDisplay' => true,
											'sortable' => false );

					if( !$this->flat_mode && $this->show_flat_mode_flag)
						$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getRemoveImage().'" '
												.'alt="'.$this->lang->def( '_DIRECTORY_REMOVEPERSON' ).'" '
												.'title="'.$this->lang->def( '_DIRECTORY_REMOVEPERSON' ).'" />',
											'hClass' => "image",
											'fieldClass' => "image",
											'data' => 'remove',
											'toDisplay' => true,
											'sortable' => false );

					$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getSuspendImage().'" '
											.'alt="'.$this->lang->def( '_SUSPEND' ).'" '
											.'title="'.$this->lang->def( '_SUSPEND' ).'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'valid',
										'toDisplay' => true,
										'sortable' => false );
				}
			}
			if($this->del_perm) {
				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getDeleteImage().'" '
										.'alt="'.$this->lang->def( '_DEL' ).'" '
										.'title="'.$this->lang->def( '_DEL' ).'" />',
									'hClass' => "image",
									'fieldClass' => "image",
									'data' => 'delete',
									'toDisplay' => true,
									'sortable' => false );
			}
		}
		if ($this->use_multi_sel) {
			$arr=$this->sel_extend->extendListHeader();
			foreach($arr as $key=>$val) {
				$colInfos[] = array( 	'hLabel' => $val,
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => $key,
										'toDisplay' => true,
										'sortable' => false );
			}
		}
		return $colInfos;
	}

	function extendedParsing( $arrayState ) {

		// set filter for base fields
		require_once($GLOBALS['where_framework'].'/modules/field/class.field.php');
		$field = new Field(0);
		$arr_fields = Field::getArrFieldValue_Filter( $_POST, $this->add_nat_fields, $this->id, '_' );
		foreach( $arr_fields as $fname => $fvalue )
			if( isset( $fvalue['value'] ) /*&& $fvalue['value'] != ''*/ )
				$this->arr_fields_filter[$fname] = $fvalue;


		if(isset($_POST['pw_more_usersel'])) {

			$this->_expand_user = key($_POST['pw_more_usersel']);
		}

		// set filter for custom fields
		$arr_all_fields = $this->field_list->getAllFields();
		$arr_fields = Field::getArrFieldValue_Filter( $_POST, $arr_all_fields, $this->id, '_' );
		foreach( $arr_fields as $fname => $fvalue )
			if( isset( $fvalue['value'] ) )
				$this->arr_fields_filter[$fname] = $fvalue;

		if( isset( $arrayState[$this->id] ) ) {
			// this test first - not correct in foreach
			if( isset( $arrayState[$this->id]['directory_lessmore'] ) )
				$this->more_filter = $arrayState[$this->id]['directory_lessmore'];

			if( isset($arrayState[$this->id]['order_state']) )
				$this->arr_fields_order = Util::unserialize(urldecode($arrayState[$this->id]['order_state']));


			$isFieldsSet = FALSE;
			foreach( $arrayState[$this->id] as $key => $val ) {

				switch( $key ) {
					case $this->_getOpEditItemId():
						$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpEditItemId()] );
						$this->op = 'editperson';
					break;
					case $this->_getOpDeleteItemId():
						$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpDeleteItemId()] );
						$this->op = 'deleteperson';
					break;
					case $this->_getOpSuspendItemId():
						$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpSuspendItemId()] );
						$this->op = 'suspendperson';
					break;
					case $this->_getOpRecoverItemId():
						$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpRecoverItemId()] );
						$this->op = 'recoverperson';
					break;
					case $this->_getOpRemoveItemId():
						$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpRemoveItemId()] );
						$this->op = 'removeperson';
					break;
					case 'cfield_state':
						if( !$isFieldsSet )
							$this->cFields = Util::unserialize(urldecode($val));
					break;
					case DIRECTORY_CFIELD:
						$isFieldsSet = TRUE;
						foreach( $val as $nField => $idField ) {
							$this->cFields[$nField] = $idField;
						}
					break;
					case DIRECTORY_ORDER:
						$arr_order = array();
						foreach( $val as $fieldName => $dummyvar ) {
							if( isset($this->arr_fields_order[$fieldName] ) ) {
								if( $this->arr_fields_order[$fieldName] ) {
									// is DESC
									// do nothing, therefore remove order
								} else {
									// is ASC, go to DESC
									$arr_order[$fieldName] = TRUE;
								}
							} else {
								// is unordered
								$arr_order[$fieldName] = FALSE;
							}
						}
						$this->arr_fields_order = $arr_order;
					break;
					case 'directory_more':
						$this->more_filter = 'more';
					break;
					case 'directory_less':
						$this->more_filter = 'less';
					break;
					case 'flat_mode':
						$this->flat_mode = ($val == 'flat_mode');
					break;
					case 'add_filter':
						$id_field = $arrayState[$this->id]['add_field_filter'];
						if( is_numeric( $id_field ) ) {
							$this->arr_fields_filter['ff'.count($this->arr_fields_filter).'_'.$id_field] = $arr_all_fields[$id_field];
						} else {
							$this->arr_fields_filter['ff'.count($this->arr_fields_filter).'_'.$id_field] = $this->add_nat_fields[$id_field];
						}
					break;
					case 'del_filter':
						if( is_array( $val ) ) {
							unset($this->arr_fields_filter[key($val)]);
						} else {
							$this->arr_fields_filter = array();
						}
					break;
				}
			}
		} else {
			// default initializations
			$this->cFields = Util::unserialize(urldecode(Docebo::user()->preference->getPreference( 'ui.directory.custom_columns' )));
			$this->arr_fields_order = Util::unserialize(urldecode(Docebo::user()->preference->getPreference( 'ui.directory.order_columns' )));
			$this->arr_fields_filter = Util::unserialize(urldecode(Docebo::user()->preference->getPreference( 'ui.directory.filters.current' )));
		}

		// remove anonymous ================================================
		if($this->hide_anonymous === true) {

			$this->data->addCustomFilter('', " idst <> '".$this->anonymous_idst."' ");
		}
		
		if($this->hide_suspend === true) {

			$this->data->addCustomFilter('', " valid <> '0' ");
		}
		
		// filter by editions ==============================================
		if($this->lms_editions_filter === true) {

			if(isset($GLOBALS['course_descriptor']) && $GLOBALS['course_descriptor']->hasEdition()) {

				$fvalue = (isset($_POST[$this->id]['edition_filter'])
					? strip_tags(html_entity_decode($_POST[$this->id]['edition_filter']))
					: '' );

				if($fvalue != false) {

					$acl_man =& Docebo::user()->getAclManager();
					$members = $acl_man->getGroupAllUser($fvalue);
					if($members && !empty($members)) {
						$this->data->addCustomFilter('', "idst IN (".implode(',', $members).") ");
					}

				} else {

					$ed_list = array();
					if($this->editions == false) $this->editions = $GLOBALS['course_descriptor']->getEditionsSimpleList(getLogUserId(), true);

					$this->data->intersectGroupFilter(array_keys($this->editions));
				}
			}
		}
		// show filter ============================================================
		if($this->show_simple_filter === true) {

			$fvalue = (isset($_POST[$this->id]['simple_fulltext_search'])
				? strip_tags(html_entity_decode($_POST[$this->id]['simple_fulltext_search']))
				: '' );

			if(trim($fvalue !== '')) {

				$this->data->addCustomFilter('', " ( userid LIKE '%".$fvalue."%' OR firstname LIKE '%".$fvalue."%' OR lastname LIKE '%".$fvalue."%' ) ");
			}

		} else {

			if(is_array($this->arr_fields_filter))
			foreach( $this->arr_fields_filter as $fname => $fvalue ) {
				if( isset( $fvalue['value'] ) ) {
					if( isset( $fvalue['fieldname'] ) ) {
						if( $fvalue['field_type'] == 'upload' ) {
							$this->data->addFieldFilter( $fvalue['fieldname'], '', '<>');
						} else {
							if( $fvalue['value'] == '' ) {
								$search_op = " = ";
								$search_val = "";
							} else {
								$search_op = " LIKE ";
								$search_val = "%".$fvalue['value']."%";
							}
							$this->data->addFieldFilter( $fvalue['fieldname'], $search_val, $search_op);
						}
					} else {
						if( $fvalue[FIELD_INFO_TYPE] == 'upload' ) {
							$this->data->addCustomFilter( 	" LEFT JOIN ".$field->_getUserEntryTable()." AS ".$fname
															." ON ( $fname.id_common = '".(int)$fvalue[FIELD_INFO_ID]."'"
															." AND $fname.id_user = idst ) ",
															" ($fname.user_entry IS ".(($fvalue['value']=='true')?'NOT':'')." NULL ) " );
						} else {
							if( $fvalue['value'] == '' )
								$where = " ($fname.user_entry = '' OR $fname.user_entry IS NULL )";
							elseif($fvalue[FIELD_INFO_TYPE] == 'date')
								$where = " ($fname.user_entry LIKE '%".Format::dateDb($fvalue['value'], 'date')."%' ) ";
							else
								$where = " ($fname.user_entry LIKE '%".$fvalue['value']."%' ) ";
							$this->data->addCustomFilter( 	" LEFT JOIN ".$field->_getUserEntryTable()." AS ".$fname
															." ON ( $fname.id_common = '".(int)$fvalue[FIELD_INFO_ID]."'"
															." AND $fname.id_user = idst ) ",
															$where );
						}
					}
				}
			}
		} // end else simple filter
	}

	/**
	 * Add a filter on fields. All fields can be used native of custom.
	 * You must pass an array of array. Any element of the ancestor array
	 * is an array:
	 * 		for native fields this is
	 * 			- 'value' => the value of the field
	 * 			- 'fieldname' => the name of the field
	 * 			- 'field_type' = the type of the field
	 * 		for custom fields
	 * 			- 'value' => the value of the field
	 * 			- FIELD_INFO_ID => the id of the custom field
	 * 			- FIELD_INFO_TYPE => the type of the custom field
	 * @param array $arr_filter the array of fields to filter (see above)
	 */
	function addFieldFilters( $arr_filter, $strict = FALSE ) {

		if($this->lms_editions_filter === true) {

			// filter for the editions selected =====================================
			$fvalue = (isset($_POST[$this->id]['edition_filter'])
				? (int)$_POST[$this->id]['edition_filter']
				: '' );

			if($fvalue != false) {
				$acl_man =& Docebo::user()->getAclManager();
				$members = $acl_man->getGroupAllUser($fvalue);
				if($members && !empty($members)) {
					$this->data->addCustomFilter('', "idst IN (".implode(',', $members).") ");
				}
			}
		}

		if($this->show_simple_filter === true) {

			// filter for userid firstname e lastname , fulltext search ====================
			$fvalue = (isset($_POST[$this->id]['simple_fulltext_search'])
				? strip_tags(html_entity_decode($_POST[$this->id]['simple_fulltext_search']))
				: '' );

			if(trim($fvalue !== '')) {

				$this->data->addCustomFilter('', " ( userid LIKE '%".$fvalue."%' OR firstname LIKE '%".$fvalue."%' OR lastname LIKE '%".$fvalue."%' ) ");
			}
			return;
		}

		require_once($GLOBALS['where_framework'].'/modules/field/class.field.php');
		$field = new Field(0);
		foreach( $arr_filter as $fname => $fvalue ) {
			if(is_numeric($fname)) {
				$fname = "cfield_".$fname;
			}
			if( isset( $fvalue['value'] ) ) {
				if( isset( $fvalue['fieldname'] ) ) {
					if( $fvalue['field_type'] == 'upload' ) {
						$this->data->addFieldFilter( $fvalue['fieldname'], '', '<>');
					} else {
						if( $fvalue['value'] == '' ) {
							$search_op = " = ";
							$search_val = "";
						} else {
							if( $strict ) {
								$search_op = " LIKE ";
								$search_val = "%".$fvalue['value']."%";
							} else {
								$search_op = " = ";
								$search_val = $fvalue['value'];
							}
						}
						$this->data->addFieldFilter( $fvalue['fieldname'], $search_val, $search_op);
					}
				} else {
					if( $fvalue[FIELD_INFO_TYPE] == 'upload' ) {
						$this->data->addCustomFilter( 	" LEFT JOIN ".$field->_getUserEntryTable()." AS ".$fname
														." ON ( $fname.id_common = '".(int)$fvalue[FIELD_INFO_ID]."'"
														." AND $fname.id_user = idst ) ",
														" ($fname.user_entry IS ".(($fvalue['value']=='true')?'NOT':'')." NULL ) " );
					} else {
						if( $fvalue['value'] == '' )
							$where = " ($fname.user_entry = '' OR $fname.user_entry IS NULL )";
						else {
							if( $strict )
								$where = " ($fname.user_entry = '".$fvalue['value']."' ) ";
							else
								$where = " ($fname.user_entry LIKE '%".$fvalue['value']."%' ) ";
						}
						$this->data->addCustomFilter( 	" LEFT JOIN ".$field->_getUserEntryTable()." AS ".$fname
														." ON ( $fname.id_common = '".(int)$fvalue[FIELD_INFO_ID]."'"
														." AND $fname.id_user = idst ) ",
														$where );
					}
				}
			}
		}
	}

	function _loadAdminIdst() {

		$this->admins_user = array();

		$aclManager =& Docebo::user()->getAclManager();
		$arr_levels_id = $aclManager->getAdminLevels();

		$arr_groups_godadmin = $aclManager->getGroupUMembers($arr_levels_id[ADMIN_GROUP_GODADMIN]);
		$arr_groups_admin = $aclManager->getGroupUMembers($arr_levels_id[ADMIN_GROUP_ADMIN]);

		$this->admins_user = array_merge($this->admins_user , $arr_groups_godadmin);
		$this->admins_user = array_merge($this->admins_user , $arr_groups_admin);
	}

	function fetchRecord() {
		$arrResult = $this->data->fetchRecord();
		if( $arrResult === FALSE )
			return FALSE;

		// print userid
		$userid = $this->aclManager->relativeId($arrResult['userid']);
		$idstUser = $arrResult['idst'];
				
		if(!$this->selector_mode && $userid != 'Anonymous') {

			$arrResult['userid'] =

				'<input type="image" class="button_image'.( $arrResult['idst'] == $this->_expand_user ? '' : ' container-hide' ).'" '
					.'src="'.getPathImage('fw').'standard/less.gif" '
					.'alt="'.$this->lang->def('_CLOSE').'" '
					.'id="pw_less_usersel_'.$arrResult['idst'].'" '
					.'name="pw_less_usersel['.$arrResult['idst'].']" '
					.'onclick="closeUserProfile('.$arrResult['idst'].'); return false;"'
				.' />'

				.'<input type="image" class="button_image'.( $arrResult['idst'] == $this->_expand_user ? ' container-hide' : '' ).'" '
					.'src="'.getPathImage('fw').'standard/more.gif" '
					.'alt="'.$this->lang->def('_MORE_INFO').'" '
					.'id="pw_more_usersel_'.$arrResult['idst'].'" '
					.'name="pw_more_usersel['.$arrResult['idst'].']"'
					.'onclick="getUserProfile('.$arrResult['idst'].'); return false;"'
				.' />'
				.$userid;
		} else {

			$arrResult['userid'] = $userid;
		}

		$arrResult['fullname'] = ( $arrResult['valid']!='1' && $arrResult['idst'] != $this->anonymous_idst
					? '<em class="user_suspended">'.$this->lang->def('_SUSPENDED').'</em> '
					: '' ).join( array( $arrResult['firstname'], $arrResult['lastname'] ), ' ');

		for( $i = 0; $i < $this->nFields; $i++ ) {
			if( is_numeric($this->cFields[$i]) )
				$arrResult[DIRECTORY_CFIELD.'_'.$i] = $this->field_list->showFieldForUser($arrResult['idst'],$this->cFields[$i],TRUE);
			elseif(isset($arrResult[$this->cFields[$i]]))
				$arrResult[DIRECTORY_CFIELD.'_'.$i] = $arrResult[$this->cFields[$i]];
			else
				$arrResult[DIRECTORY_CFIELD.'_'.$i] = '';
		}
		if( !$this->selector_mode ) { // Normal mode

			$lev_current_user = Docebo::user()->getUserLevelId();

			if(($lev_current_user == ADMIN_GROUP_GODADMIN) || !isset($this->admins_user[$arrResult['idst']])) {

				$arrResult['edit'] = '<a href="index.php?modname=directory&op=org_manageuser&id_user='.$arrResult['idst'].'&ap=mod_profile" '
					.'id="'.$this->id.'_'.$this->_getOpEditItemId().'_'.$userid.'" '
					.'title="'.$this->_getEditLabel().'">'
					.'<img src="'.$this->_getEditImage().'" alt="'.$this->_getEditAlt().'" />'
					.'</a>';
					/*
					'<input type="image" class="tree_view_image" '
								.' src="'.$this->_getEditImage().'"'
								.' id="'.$this->id.'_'.$this->_getOpEditItemId().'_'.$userid.'" '
								.' name="'.$this->id.'['.$this->_getOpEditItemId().']['.$userid.']" '
								.' title="'.$this->_getEditLabel().'" '
								.' alt="'.$this->_getEditAlt().'" />';
				*/
				if( $this->flat_mode === FALSE && $this->show_flat_mode_flag)
					$arrResult['remove'] = '<input type="image" class="tree_view_image" '
									.' src="'.$this->_getRemoveImage().'"'
									.' id="'.$this->id.'_'.$this->_getOpRemoveItemId().'_'.$userid.'" '
									.' name="'.$this->id.'['.$this->_getOpRemoveItemId().']['.$userid.']" '
									.' title="'.$this->_getRemoveLabel().' : '.$userid.'" '
									.' alt="'.$this->_getRemoveAlt().'" />';
			} else {
				$arrResult['edit'] = '';
			}
			if($arrResult['idst'] != $this->anonymous_idst) {

				$arrResult['delete'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getDeleteImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpDeleteItemId().'_'.$idstUser.'" '//$userid.'" '
							.' name="'.$this->id.'['.$this->_getOpDeleteItemId().']['.$idstUser.']" '//$userid.']" '
							.' title="'.$this->_getDeleteLabel().'" '
							.' alt="'.$this->_getDeleteAlt().'" />';


				$suspendImage = ($arrResult['valid']!='1')?$this->_getRecoverImage():$this->_getSuspendImage();
				$suspendId = ($arrResult['valid']!='1')?$this->_getOpRecoverItemId():$this->_getOpSuspendItemId();
				$suspendLabel = ($arrResult['valid']!='1')?$this->_getRecoverLabel():$this->_getSuspendLabel();
				$suspendAlt = ($arrResult['valid']!='1')?$this->_getRecoverAlt():$this->_getSuspendAlt();
				$arrResult['valid'] = '<input type="image" class="tree_view_image" '
								.' src="'.$suspendImage.'"'
								.' id="'.$this->id.'_'.$suspendId.'_'.$userid.'" '
								.' name="'.$this->id.'['.$suspendId.']['.$userid.']" '
								.' title="'.$suspendLabel.'" '
								.' alt="'.$suspendAlt.'" />';
			} else {
				$arrResult['edit'] = '';
				$arrResult['delete'] = '';
				$arrResult['valid'] = '';
			}
		} else if (($this->selector_mode) && (!$this->use_multi_sel)) { // Selector mode
			$arrResult['selection'] = '<input type="checkbox"'
							.' id="'.DIRECTORY_ID.DIRECTORY_OP_SELECTITEM.'_'.$arrResult['idst'].'" '
							.' name="'.DIRECTORY_ID.'['.DIRECTORY_OP_SELECTITEM.']['.$arrResult['idst'].']" '
							.' value="'.$arrResult['userid'].'"'
							.' title="'.$this->_getSelectedLabel().'" '
							.' alt="'.$this->_getSelectedAlt().'" ';
			if( array_search( $arrResult['idst'], $this->itemSelectedMulti ) !== FALSE )
				$arrResult['selection'] .= ' checked="checked" ';
			$arrResult['selection'] .= '/>';
			$arrResult['userid'] = 	'<label for="'.DIRECTORY_ID.DIRECTORY_OP_SELECTITEM.'_'.$arrResult['idst'].'">'
									.$arrResult['userid'].'</label>';
		} elseif ($this->use_multi_sel) { // Multi selector mode

			$extra_cols=$this->sel_extend->extendListRow($arrResult['idst']);
			$arrResult=array_merge($arrResult, $extra_cols);

		}

		$this->printedItems[] = $arrResult['idst'];
		return $arrResult;
	}

	function userExtraData($id_user) {

		require_once(_base_.'/lib/lib.user_profile.php');

		$lang =& DoceboLanguage::createInstance('profile', 'framework');

		$profile = new UserProfile( $id_user );
		$profile->init('profile', 'framework', 'modname=directory&op=org_manageuser&id_user='.$id_user, 'ap');
		$profile->enableGodMode();
		$profile->disableModViewerPolicy();
		return $profile->_up_viewer->getUserInfo()

			 	// teacher profile, if the user is a teacher
			 	.$profile->getUserTeacherProfile()

				.$profile->getUserLmsStat();
	}

	function printOut() {

		YuiLib::load();
		//addJs($GLOBALS['where_framework_relative'].'/modules/directory/', 'ajax.directory.js');
		Util::get_js(Get::rel_path('adm').'/modules/directory/ajax.directory.js', true, true);


		require_once(_base_.'/lib/lib.user_profile.php');

		$profile = new UserProfile( getLogUserId() );
		$profile->init('profile', 'framework', 'modname=directory&op=org_chart', 'ap');
		$profile->addStyleSheet('lms');

		$GLOBALS['page']->add('<script type="text/javascript">'
			.' setup_directory(); '
			.'</script>', 'page_head');


		$out = '';
		if( $this->select_all ) {
			// This is not a beautiful position for this operation but at this point
			// I'm sure that all filter was applied
			$rs_all = $this->data->getAllRowsIdst();
			if( $rs_all !== FALSE ) {
				$this->itemSelectedMulti = array();
				while( list($all_idst) = sql_fetch_row($rs_all) )
					$this->itemSelectedMulti[] = $all_idst;
			}
			$this->itemSelected = $this->itemSelectedMulti;
		}
		if( $this->deselect_all ) {
			// This is not a beautiful position for this operation but at this point
			// I'm sure that all filter was applied
			$this->itemSelectedMulti = array();
			$this->itemSelected = array();
		}
		require_once(_base_.'/lib/lib.form.php');

		$ord = importVar('ord', false, 'trans');
		$flip = importVar('flip', true, 0);
		$filter = new Form();

		$out .= $filter->getOpenFieldset($this->lang->def('_SEARCH'));

		$out .= $filter->getHidden('ord', 'ord', $ord);
		$out .= $filter->getHidden('flip', 'flip', $flip);


		if($this->lms_editions_filter === true) {

			if(isset($GLOBALS['course_descriptor']) && $GLOBALS['course_descriptor']->hasEdition()) {

				// add editions filter ============================================================
				$ed_list = array();
				if($this->editions == false) $this->editions = $GLOBALS['course_descriptor']->getEditionsSimpleList(getLogUserId(), true);

				$sel = ( isset($_POST[$this->id]['edition_filter'])
					? (int)$_POST[$this->id]['edition_filter']
					: $GLOBALS['course_descriptor']->getActualEditionsForUser( getLogUserId() )
				);
				if(!empty($this->editions)) {

					$out .= $filter->getDropdown(	$this->lang->def('_FILTER_BY_EDITION'),
													$this->id.'_edition_filter',
													$this->id.'[edition_filter]',
													$this->editions,
													$sel );
				}

			}

		} // end lms editions filter

		if($this->show_simple_filter === TRUE) {

			// show simple filter ============================================================

			$out .= $filter->getTextfield( $this->lang->def('_SIMPLE_FILTER'),
											$this->id.'_simple_fulltext_search',
											$this->id.'[simple_fulltext_search]',
											255,
											( isset($_POST[$this->id]['simple_fulltext_search'])
												? strip_tags(html_entity_decode($_POST[$this->id]['simple_fulltext_search']))
												: '' ),
											strip_tags($this->lang->def('_SIMPLE_FILTER'))
										);
			$out .= '<div class="align_right">'
				.$filter->getButton( $this->id.'_search', $this->id.'[search]', $this->lang->def('_SEARCH'), 'button_nowh')
				.'</div>';
		} else {

			// show complex filter ===========================================================

			$out .= '<h2 id="customize_filter">'.$this->lang->def('_CUSTOMIZE_FILTERS').'</h2>';

			// --- print check box for flat mode
			if( $this->show_flat_mode_flag )
				$out .= $filter->getCheckbox( $this->lang->def('_DIRECTORY_FILTER_FLATMODE'),
								$this->id.'_flat_mode',
								$this->id.'[flat_mode]',
								'flat_mode',
								$this->flat_mode,
								"onclick=\"window.document.forms['directory_org_chart'].submit();\"" );
			else
				$out .= $filter->getHidden(	$this->id.'_flat_mode',
											$this->id.'[flat_mode]',
											($this->flat_mode ? 'flat_mode':''));

			// line for add a field filter
			$out .= $filter->openFormLine();
			foreach( $this->add_nat_fields as $nat_id => $nat_info ) {
				$local_arr_fields_translation[$nat_id] = $this->lang->def('_DIRECTORY_FILTER_'.$nat_id);
			}

			$filter_to_show = $this->arr_fields_translation;
			if(is_array($this->arr_fields_filter)) {
				foreach($this->arr_fields_filter as $filter_info) {

					if(isset($filter_info['fieldname']))
						unset($filter_to_show[$filter_info['fieldname']]);
					else
						unset($filter_to_show[$filter_info[0]]);
				}
			}
			if(is_array($filter_to_show) && !empty($filter_to_show)) {

				$out .= $filter->getInputDropdown( 'new_filter',
													$this->id.'_add_field_filter',
													$this->id.'[add_field_filter]',
													$filter_to_show,
													'',
													'');
				$out .= $filter->getButton( $this->id.'_add_filter',
											$this->id.'[add_filter]',
											$this->lang->def('_NEW_FILTER'),
											'button_nowh');
			}
			$out .= $filter->getButton($this->id.'_del_filter',
										$this->id.'[del_filter]',
										$this->lang->def('_RESET'),
										'button_nowh');

			$out .= $filter->closeFormLine();

			if(is_array($this->arr_fields_filter))
			foreach( $this->arr_fields_filter as $field_id => $field_prop ) {

				if( !isset( $field_prop['fieldname'] ) ) {
					// custom field
					$arr_field_info = $this->field_list->getBaseFieldInfo( $field_prop[FIELD_INFO_TYPE] );
					require_once($GLOBALS['where_framework'].'/modules/field/'.$arr_field_info[FIELD_BASEINFO_FILE]);
					$field_obj =  new $arr_field_info[FIELD_BASEINFO_CLASS]( $field_id );

					$del_spot = '<input type="image" class="cancel_filter" '
								.' src="'.getPathImage('framework').'standard/cancel.png"'
								.' id="'.$this->id.'_del_filter_'.$field_id.'"'
								.' name="'.$this->id.'[del_filter]['.$field_id.']"'
								.' title="'.$this->lang->def('_DEL').'"'
								.' alt="'.$this->lang->def('_DEL').'" />';


					$out .= $field_obj->play_filter($field_id,
													( isset($field_prop['value']) ? $field_prop['value'] : false ),
													$field_prop[FIELD_INFO_TRANSLATION],
													$this->id,
													$del_spot,
													'',
													$field_prop[FIELD_INFO_ID]);
					//play_filter( $id_field, $value = FALSE, $label = FALSE, $field_prefix = FALSE, $other_after = '', $other_before = '', $field_special = FALSE )
				} else {
					// base field
					$arr_field_info = $this->field_list->getBaseFieldInfo( $field_prop['field_type'] );
					require_once($GLOBALS['where_framework'].'/modules/field/'.$arr_field_info[FIELD_BASEINFO_FILE]);

					$field_obj =  new $arr_field_info[FIELD_BASEINFO_CLASS]( 0 );

					$del_spot = '<input type="image" class="cancel_filter" '
								.' src="'.getPathImage('framework').'standard/cancel.png"'
								.' id="'.$this->id.'_del_filter_'.$field_id.'"'
								.' name="'.$this->id.'[del_filter]['.$field_id.']"'
								.' title="'.$this->lang->def('_DEL').'"'
								.' alt="'.$this->lang->def('_DEL').'" />';

					$out .= $field_obj->play_filter($field_id,
													( isset($field_prop['value']) ? $field_prop['value'] : false ),
													$this->lang->def('_DIRECTORY_FILTER_'.$field_prop['fieldname']),
													$this->id,
													$del_spot,
													'',
													'');

				}

			}

			$out .= $filter->openButtonSpace();

			$out .= $filter->getButton('search', 'search', $this->lang->def('_SEARCH'));
			$out .= $filter->closeButtonSpace();

		} // end else for filter

		$out .= $filter->getCloseFieldset();

		// ---------------------------------------------------------------------------------------

		// set order rows
		if(is_array($this->arr_fields_order)) {

			foreach( $this->arr_fields_order as $ordFieldName => $isDesc )
				$this->data->setOrderCol($ordFieldName,$isDesc);
		}
		$this->getRows( $this->_getStartRow(), $this->_getRowsPage());
		$totRow = $this->getTotalRows();

		if( $totRow == -1 ) $totRow = $this->getLoadedRows();
		$colInfo = $this->_getCols();
		$colData = $colInfo;

		$this->rend->setCaption($this->_getTitle());

		$type_h = array();
		$cont_h = array();
		while(list($key, $contentCell) = each($colInfo)) {

			if( $contentCell['toDisplay'] ) {

				$type_h[] = $contentCell['hClass'];
				$cont_h[] = $contentCell['hLabel'];
			}
		}
		reset($colInfo);
		$this->rend->addHead($cont_h, $type_h);

		while( $values = $this->fetchRecord() ) {

			$colData = array();
			foreach( $colInfo as $key => $fieldInfo ) {
				$colData[] = $values[$colInfo[$key]['data']];
			}
			$this->rend->addBody($colData, false, false, 'user_row_'.$values['idst']);

			if($this->_expand_user == $values['idst']) {

				// extra user info if requested
				$this->rend->addBodyExpanded($this->userExtraData($this->_expand_user), 'user_more_info');
			}
		}

		if( $this->insNew ) {

			$this->rend->addActionAdd('<input type="submit" class="transparent_add_button"'
				.' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
				.' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
				.' value="'.$this->lang->def('_ADD').'"'
				.' title="'.$this->_getCreateLabel().'" '
				.' alt="'.$this->_getCreateAlt().'" />');
		}

		$this->rend->initNavBar($this->_getIdInitRowId(),'button');

		$out .= $this->rend->getTable()
				.$this->rend->getNavBar($this->_getStartRow(), $totRow)
				.$this->printState();
				
		//prepare delete confirm popups [id format: "usersmembersdirectory__listview_opdeleteitem__" ]
		$lang =& DoceboLanguage::CreateInstance('standard');
		require_once(_base_.'/lib/lib.dialog.php');
		setupFormDialogBox(
			'directory_org_chart',
			'index.php?modname=directory&op=org_chart',
			'input[name*=_listview_opdeleteitem_]',
			$lang->def('_AREYOUSURE'),
			$lang->def('_CONFIRM'),
			$lang->def('_UNDO'),
			'function(o) { return o.title; }',
			'usersmembersdirectory__listview_opdeleteitem__',
			'idst',
			'deleteperson' );
		
		//tree remove user from branch confirm popup
		setupSimpleFormDialogBox('directory_org_chart', 'input[name*=_listview_opremoveitem_]');
		// ---------------------------------------------------------------------------------------

		if( $this->select_all ) {
			$arr_notPrint = array_diff( $this->itemSelectedMulti, $this->printedItems );
			foreach( $arr_notPrint as $id_notPrint ) {
				$out .= '<input type="checkbox" '
						.' id="'.DIRECTORY_ID.DIRECTORY_OP_SELECTITEM.'_'.$id_notPrint.'" '
						.'name="'.DIRECTORY_ID.'['.DIRECTORY_OP_SELECTITEM.']['.$id_notPrint.']" '
						.'value="" checked="checked" style="display:none;" />';
			}
		}
		return $out;
	}


	function setNFields($nFields) {
		$this->nFields=$nFields;
	}
}

class GroupListView extends ListView {

	var $lang = NULL;
	var $selector_mode = TRUE;
	var $itemSelectedMulti = array();
	var $printedItems = array();

	// Use multi selection?
	var $use_multi_sel = false;

	// Extend selector object
	var $sel_extend = NULL;

	// set this member variable to TRUE to select all elements of listview
	var $select_all = FALSE;

	var $name_as_last = FALSE;

	var $add_perm;
	var $mod_perm;
	var $del_perm;
	var $associate_perm;

	function _getOpEditItemId() 	{ return '_listview_opedititem_';	}
	function _getEditLabel() { return $this->lang->def('_DEL'); }
	function _getEditAlt() { return $this->lang->def('_DEL'); }
	function _getEditImage() { return getPathImage().'standard/edit.png'; }

	function _getOpDeleteItemId() 	{ return '_listview_opdeleteitem_';	}
	function _getDeleteLabel() 	{ return $this->lang->def('_DEL'); }
	function _getDeleteAlt() 		{ return $this->lang->def('_DEL'); }
	function _getDeleteImage() 	{ return getPathImage().'standard/delete.png'; }


	function _getOpAddToItemId() 	{ return '_listview_opaddtogroup_';	}
	function _getAddToLabel() 		{ return $this->lang->def('_DIRECTORY_ADDTOGROUP'); }
	function _getAddToAlt() 		{ return $this->lang->def('_DIRECTORY_ADDTOGROUP'); }
	function _getAddToImage() 		{ return getPathImage('fw').'directory/addto.gif'; }

	function _getOpAssignFieldId() 	{ return '_listview_opassignfieldgroup_';	}
	function _getAssignFieldLabel() 	{ return $this->lang->def('_DIRECTORY_ASSIGNFIELDGROUP'); }
	function _getAssignFieldAlt() 		{ return $this->lang->def('_DIRECTORY_ASSIGNFIELDGROUP'); }
	function _getAssignFieldImage() 	{ return getPathImage().'directory/assign_field.gif'; }


	function _getOpMembersId() 		{ return '_listview_opmembersgroup_';	}
	function _getMembersLabel() 		{ return $this->lang->def('_DIRECTORY_MEMBERSGROUP'); }
	function _getMembersAlt() 			{ return $this->lang->def('_DIRECTORY_MEMBERSGROUP'); }
	function _getMembersImage() 		{ return getPathImage().'directory/group.gif'; }

	function _getOpSelectedItemId() 	{ return '_listview_opselectedgroup_';	}
	function _getSelectedLabel() 		{ return $this->lang->def('_DIRECTORY_SELECTGROUP'); }
	function _getSelectedAlt() 		{ return $this->lang->def('_DIRECTORY_SELECTGROUP'); }

	function _getOpWaitingId() 		{ return '_listview_opwaitinggroup_'; }
	function _getWaitingLabel() 		{ return $this->lang->def('_WAITING_USERS'); }

	function _getRowsPage() { return Get::sett('visuItem'); }

	function showOnlyGroupName() { $this->name_as_last = true; }

	function printState() {

		$out = parent::printState();
		$out = '<input type="hidden"'
			.' id="'.DIRECTORY_ID.DIRECTORY_ID_PRINTEDITEM.'"'
			.' name="'.DIRECTORY_ID.'['.DIRECTORY_ID_PRINTEDITEM.']"'
			.' value="'.urlencode(Util::serialize($this->printedItems)).'" />'."\n";
		return $out;
	}

	function GroupListView($title, &$data, &$rend, $id) {
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');
		parent::ListView($title, $data, $rend, $id);

		$this->mod_perm = checkPerm('editgroup', true, 'directory', 'framework');
		$this->del_perm = checkPerm('delgroup', true, 'directory', 'framework');
		$this->associate_perm = checkPerm('associate_group', true, 'directory', 'framework');
	}

	function _getCols() {
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');
		$totCol = $this->data->getFieldCount();
		$colInfos = array();
		if( ($this->selector_mode) && (!$this->use_multi_sel) ) {
			$colInfos[] = array( 	'hLabel' => '',
									'hClass' => "image",
									'fieldClass' => "image",
									'data' => 'selection',
									'toDisplay' => true,
									'sortable' => false );
		}
		$colInfos[] = array( 	'hLabel' => $this->lang->def( '_DIRECTORY_GROUPID' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'groupid',
								'toDisplay' => true,
								'sortable' => true );
		$colInfos[] = array( 	'hLabel' => $this->lang->def( '_DESCRIPTION' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'description',
								'toDisplay' => true,
								'sortable' => true );
		if( !$this->selector_mode ) {

			if($this->mod_perm) {
				$colInfos[] = array( 	'hLabel' => '<img src="'.getPathImage('fw').'/directory/group_waiting.gif" '
											.'alt="'.$this->lang->def( '_WAITING_USERS' ).'" '
											.'title="'.$this->lang->def( '_WAITING_USERS' ).'" />',
									'hClass' => "image",
									'fieldClass' => "image",
									'data' => 'waiting_user',
									'toDisplay' => true,
									'sortable' => true );
				$colInfos[] = array( 	'hLabel' => '<img src="'.getPathImage('fw').'/directory/group_type.gif" '
											.'alt="'.$this->lang->def( '_DIRECTORY_GROUPTYPE' ).'" '
											.'title="'.$this->lang->def( '_DIRECTORY_GROUPTYPE' ).'" />',
									'hClass' => "image",
									'fieldClass' => "image",
									'data' => 'type',
									'toDisplay' => true,
									'sortable' => true );
			}
			if($this->associate_perm) {
				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getMembersImage().'" '
											.'alt="'.$this->lang->def( '_DIRECTORY_MEMBERSGROUP' ).'" '
											.'title="'.$this->lang->def( '_DIRECTORY_MEMBERSGROUP' ).'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'members',
										'toDisplay' => true,
										'sortable' => false );

				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getAddToImage().'" '
											.'alt="'.$this->lang->def( '_DIRECTORY_ADDTOGROUP' ).'" '
											.'title="'.$this->lang->def( '_DIRECTORY_ADDTOGROUP' ).'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'addto',
										'toDisplay' => true,
										'sortable' => false );
				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getAssignFieldImage().'" '
											.'alt="'.$this->_getAssignFieldAlt().'" '
											.'title="'.$this->_getAssignFieldLabel().'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'assignfield',
										'toDisplay' => true,
										'sortable' => false );
			}
			if($this->mod_perm) {
				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getEditImage().'" '
											.'alt="'.$this->lang->def( '_DEL' ).'" '
											.'title="'.$this->lang->def( '_DEL' ).'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'edit',
										'toDisplay' => true,
										'sortable' => false );
			}
			if($this->del_perm) {
				$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getDeleteImage().'" '
											.'alt="'.$this->lang->def( '_DEL' ).'" '
											.'title="'.$this->lang->def( '_DEL' ).'" />',
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => 'delete',
										'toDisplay' => true,
										'sortable' => false );
			}
		}
		if ($this->use_multi_sel) {
			$arr=$this->sel_extend->extendListHeader();
			foreach($arr as $key=>$val) {
				$colInfos[] = array( 	'hLabel' => $val,
										'hClass' => "image",
										'fieldClass' => "image",
										'data' => $key,
										'toDisplay' => true,
										'sortable' => false );
			}
		}
		return $colInfos;
	}

	function extendedParsing( $arrayState ) {

		if( isset($arrayState['editgroupsave']) ) {
			$idst = $_POST['idst'];
			$groupid = $_POST['groupid'];
			$description = $_POST['description'];
			$type = $_POST['group_type'];
			$show_on_platform = '';
			if(isset($_POST['show_on_platform'])) {
				while(list($code, ) = each($_POST['show_on_platform']))
					$show_on_platform .= $code.',';
			}
			if( $idst !== '' ) {
				$this->aclManager->updateGroup( $idst, $groupid, $description, NULL, $type, $show_on_platform );
			} else {
				$this->aclManager->registerGroup( $groupid, $description, NULL, $type, $show_on_platform );
			}
		} elseif( isset($arrayState['deletegroup']) ) {
			$idst = $_POST['idst'];
			if( $idst !== '' ) $this->aclManager->deleteGroup( $idst );
		}

		// Subscribe the user approved
		if( isset($arrayState['editwaitsave']) ) {

			require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

			$field_manager = new FieldList();

			$idst_group = $_POST['idst'];
			if(isset($arrayState['waiting_user']) && is_array($arrayState['waiting_user'])) {

				while(list($idst_user, $action) = each($arrayState['waiting_user'])) {

					if($action == 'accept') {

						$this->aclManager->addToGroup($idst_group, $idst_user);
						$this->aclManager->removeFromUserWaitingOfGroup($idst_group, $idst_user);
					} elseif($action == 'decline') {

						if($field_manager->removeUserEntry($idst_user, $idst_group)) {
							$this->aclManager->removeFromUserWaitingOfGroup($idst_group, $idst_user);
						}
					} // end if

				} //end while

			} // end if

		} // end action managment

		if( isset( $arrayState[$this->id] ) ) {
			if( isset( $arrayState[$this->id][$this->_getOpEditItemId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpEditItemId()] );
				$this->op = 'editgroup';
			}
			if( isset( $arrayState[$this->id][$this->_getOpDeleteItemId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpDeleteItemId()] );
				$this->op = 'deletegroup';
			}
			if( isset( $arrayState[$this->id][$this->_getOpAddToItemId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpAddToItemId()] );
				$this->op = 'addtogroup';
			}
			if( isset( $arrayState[$this->id][$this->_getOpAssignFieldId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpAssignFieldId()] );
				$this->op = 'assignfield';
			}
			if( isset( $arrayState[$this->id][$this->_getOpMembersId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpMembersId()] );
				$this->op = 'membersgroup';
			}
			if( isset( $arrayState[$this->id][$this->_getOpWaitingId()] )) {
				$this->itemSelected = key ( $arrayState[$this->id][$this->_getOpWaitingId()] );
				$this->op = 'waitinggroup';
			}
			if( isset( $arrayState[$this->id]['import_groupuser'] )) {
				$this->op = 'import_groupuser';
			}

		}
		if($this->insNew) {
			$this->setInsNew(checkPerm('creategroup', true, 'directory', 'framework'));
		}
	}

	function fetchRecord() {
		$arrResult = $this->data->fetchRecord();
		if( $arrResult === FALSE )
			return FALSE;
		$arrResult['groupid'] = $this->aclManager->relativeId($arrResult['groupid']);

		if($this->name_as_last === false) {
			$arrResult['groupid'] = substr($arrResult['groupid'], strlen($this->data->getPathFilter()) );
		} else {
			$arrResult['groupid'] = substr($arrResult['groupid'], strrpos($arrResult['groupid'], '/')+1 );
		}

		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');

		if(($arrResult['type'] == 'moderate') || ($arrResult['waiting_user'] > 0)) {

			$arrResult['waiting_user'] = '<input type="submit" class="transparent_aslink_button" '
										.' id="'.$this->id.'_'.$this->_getOpWaitingId().'_'.$arrResult['groupid'].'" '
										.' name="'.$this->id.'['.$this->_getOpWaitingId().']['.$arrResult['groupid'].']" '
										.' title="'.$this->_getWaitingLabel().'" '
										.' value="'.$arrResult['waiting_user'].'" />';
		} else {

			$arrResult['waiting_user'] = '';
		}

		switch($arrResult['type']) {
			case "free" : {
				$arrResult['type'] = '<img src="'.getPathImage('fw').'directory/group_free.gif" '
										.'title="'.$this->lang->def('_DIRECTORY_GROUPTYPE_FREE').'" '
										.'alt="'.$this->lang->def('_DIRECTORY_GROUPTYPE_FREE_ALT').'" />';
			};break;
			case "moderate" : {
				$arrResult['type'] = '<img src="'.getPathImage('fw').'directory/group_moderate.gif" '
										.'title="'.$this->lang->def('_DIRECTORY_GROUPTYPE_MODERATE').'" '
										.'alt="'.$this->lang->def('_DIRECTORY_GROUPTYPE_MODERATE_ALT').'" />';
			};break;
			case "private"  : {
				$arrResult['type'] = '<img src="'.getPathImage('fw').'directory/group_private.gif" '
										.'title="'.$this->lang->def('_DIRECTORY_GROUPTYPE_PRIVATE').'" '
										.'alt="'.$this->lang->def('_DIRECTORY_GROUPTYPE_PRIVATE_ALT').'" />';
			};break;
			case "invisible" : {
				$arrResult['type'] = '<img src="'.getPathImage('fw').'directory/group_invisible.gif" '
										.'title="'.$this->lang->def('_DIRECTORY_GROUPTYPE_INVISIBLE').'" '
										.'alt="'.$this->lang->def('_DIRECTORY_GROUPTYPE_INVISIBLE_ALT').'" />';
			};break;
		}

		if( !$this->selector_mode ) { // Normal mode
			$arrResult['members'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getMembersImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpMembersId().'_'.$arrResult['groupid'].'" '
							.' name="'.$this->id.'['.$this->_getOpMembersId().']['.$arrResult['groupid'].']" '
							.' title="'.$this->_getMembersLabel().'" '
							.' alt="'.$this->_getMembersAlt().'" />';
			$arrResult['addto'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getAddToImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpAddToItemId().'_'.$arrResult['groupid'].'" '
							.' name="'.$this->id.'['.$this->_getOpAddToItemId().']['.$arrResult['groupid'].']" '
							.' title="'.$this->_getAddToLabel().'" '
							.' alt="'.$this->_getAddToAlt().'" />';
			$arrResult['assignfield'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getAssignFieldImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpAssignFieldId().'_'.$arrResult['groupid'].'" '
							.' name="'.$this->id.'['.$this->_getOpAssignFieldId().']['.$arrResult['groupid'].']" '
							.' title="'.$this->_getAssignFieldLabel().'" '
							.' alt="'.$this->_getAssignFieldAlt().'" />';
			$arrResult['edit'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getEditImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpEditItemId().'_'.$arrResult['groupid'].'" '
							.' name="'.$this->id.'['.$this->_getOpEditItemId().']['.$arrResult['groupid'].']" '
							.' title="'.$this->_getEditLabel().'" '
							.' alt="'.$this->_getEditAlt().'" />';
			$arrResult['delete'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getDeleteImage().'"'
							//.' id="'.$this->id.'_'.$this->_getOpDeleteItemId().'_'.$arrResult['groupid'].'" '
							//.' name="'.$this->id.'['.$this->_getOpDeleteItemId().']['.$arrResult['groupid'].']" '
							.' id="'.$this->id.'_'.$this->_getOpDeleteItemId().'_'.$arrResult['idst'].'" '
							.' name="'.$this->id.'['.$this->_getOpDeleteItemId().']['.$arrResult['idst'].']" '
							.' title="'.$this->_getDeleteLabel().' : '.$arrResult['groupid'].'" '
							.' alt="'.$this->_getDeleteAlt().'" />';
		} else if (($this->selector_mode) && (!$this->use_multi_sel)) { // Selector mode
			$arrResult['selection'] = '<input type="checkbox"'
							.' id="'.DIRECTORY_ID.DIRECTORY_OP_SELECTITEM.'_'.$arrResult['idst'].'" '
							.' name="'.DIRECTORY_ID.'['.DIRECTORY_OP_SELECTITEM.']['.$arrResult['idst'].']" '
							.' value="'.$arrResult['groupid'].'"'
							.' title="'.$this->_getSelectedLabel().'" '
							.' alt="'.$this->_getSelectedAlt().'" ';
			if( array_search( $arrResult['idst'], $this->itemSelectedMulti ) !== FALSE )
				$arrResult['selection'] .= ' checked="checked" ';
			$arrResult['selection'] .= '/>';
			$arrResult['groupid'] = 	'<label for="'.DIRECTORY_ID.DIRECTORY_OP_SELECTITEM.'_'.$arrResult['idst'].'">'
									.$arrResult['groupid'].'</label>';
		} elseif ($this->use_multi_sel) { // Multi selector mode

			$extra_cols=$this->sel_extend->extendListRow($arrResult['idst']);
			$arrResult=array_merge($arrResult, $extra_cols);

		}
		else {

		}
		$this->printedItems[] = $arrResult['idst'];
		return $arrResult;
	}

	function printOut() {
		if( $this->select_all ) {
			// This is not a beautiful position for this operation but at this point
			// I'm sure that all filter was applied
			$rs_all = $this->data->getAllRowsIdst();
			if( $rs_all !== FALSE ) {
				$this->itemSelectedMulti = array();
				while( list($all_idst) = sql_fetch_row($rs_all) )
					$this->itemSelectedMulti[] = $all_idst;
			}
		}
		//$out = $this->rend->OpenTable($this->_getTitle());
		$out = "";

		$this->getRows( $this->_getStartRow(), $this->_getRowsPage());

		$totRow = $this->getTotalRows();

		if( $totRow == -1 ) {
			$totRow = $this->getLoadedRows();
		}
		$colInfo = $this->_getCols();
		$colData = $colInfo;
		//$out .= $this->rend->WriteHeaderCss($colInfo);
		$arr_label = array();
		$arr_style = array();
		foreach ($colInfo as $col) {
			$arr_label[] = $col['hLabel'];
			$arr_style[] = $col['hClass'];
		}
		$this->rend->addHead($arr_label, $arr_style);

		while( $values = $this->fetchRecord() ) {
			$arr_line = array();
			foreach( $colInfo as $key => $fieldInfo ) {
				$colData[$key]['data'] = $values[$colInfo[$key]['data']];
				$arr_line[] = $values[$colInfo[$key]['data']];
			}
			//$out .= $this->rend->WriteRowCss($colData);
			$this->rend->addBody($arr_line);
		}

		if( $this->insNew ) {

			//$out .= $this->rend->WriteAddRow(
			$this->rend->addActionAdd(
				'<input type="submit" class="transparent_add_button"'
					.' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
					.' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
					.' value="'.$this->lang->def('_ADD').'"'
					.' title="'.$this->_getCreateLabel().'" '
					.' alt="'.$this->_getCreateAlt().'" />'

				.'<input type="submit" class="transparent_add_button"'
					.' id="'.$this->id.'_import_groupuser" '
					.' name="'.$this->id.'[import_groupuser][0]" '
					.' value="'.$this->lang->def('_IMPORT').'"'
					.' title="'.$this->lang->def('_IMPORT').'" '
					.' alt="'.$this->lang->def('_IMPORT').'" />'
			);
		}
		//$out .= $this->rend->CloseTable();
		$out .= $this->rend->getTable();

		$this->rend->initNavBar($this->_getIdInitRowId(),'button');
		$out .= $this->rend->getNavBar($this->_getStartRow(), $totRow);

		$out .= $this->printState();

		//add confirm popups
		if( !$this->selector_mode ) {
			require_once(_base_.'/lib/lib.dialog.php');
			$lang =& DoceboLanguage::createInstance('standard');
			setupFormDialogBox(
				'dirctory_listgroup',
				'index.php?modname=directory&op=listgroup',
				'input[id*='.$this->_getOpDeleteItemId().']',
				$lang->def('_AREYOUSURE'),
				$lang->def('_CONFIRM'),
				$lang->def('_UNDO'),
				'function(o) { return o.title; }',
				$this->id.'_'.$this->_getOpDeleteItemId().'_',
				'idst',
				'deletegroup');
		}

		return $out;
	}
}

class GroupMembersListView extends ListView {

	var $printedItems = array();
	function _getMemberTypeImage() { return getPathImage().'directory/addto.gif'; }
	function _getMemberTypeUser() { return getPathImage().'directory/identity.png'; }
	function _getMemberTypeGroup() { return getPathImage().'directory/group.gif'; }
	function _getMemberTypeTree() { return getPathImage().'directory/tree.gif'; }

	function _getOpDeleteItemId() 	{ return '_listview_opdeleteitem_';	}
	function _getDeleteLabel() { return $this->lang->def('_DEL'); }
	function _getDeleteAlt() { return $this->lang->def('_DEL'); }
	function _getDeleteImage() { return getPathImage().'standard/delete.png'; }

	function _getRowsPage() { return Get::sett('visuItem'); }

	function GroupMembersListView( $idst, $title, &$data, &$rend, $id ) {
		$this->idst = $idst;
		parent::ListView($title, $data, $rend, $id );
	}

	function getTreeTranslation( $groupid ) {
		require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
		$repoDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
		$pos = strpos($groupid, '_' );
		$arr_translations = $repoDb->getFolderTranslations(substr($groupid, $pos+1));
		return $arr_translations[getLanguage()];
	}

	function extendedParsing( $arrayState ) {
		if( isset( $arrayState[$this->id] ) ) {
			if( isset( $arrayState[$this->id][$this->_getOpDeleteItemId()] )) {
				$idstMember = key ( $arrayState[$this->id][$this->_getOpDeleteItemId()] );
				$this->aclManager->removeFromGroup($this->idst, $idstMember);
			}
		}

	}

	function _getCols() {
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');
		$totCol = $this->data->getFieldCount();
		$colInfos = array();
		$colInfos[] = array( 	'hLabel' => '',
								'hClass' => "image",
								'fieldClass' => "image",
								'data' => 'itemtype',
								'toDisplay' => true,
								'sortable' => false );
		$colInfos[] = array( 	'hLabel' => $this->lang->def( '_DIRECTORY_ITEMID' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'iditem',
								'toDisplay' => true,
								'sortable' => false );

		$colInfos[] = array( 	'hLabel' => $this->lang->def( '_DIRECTORY_FULLNAME' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'fullname',
								'toDisplay' => true,
								'sortable' => false );
		$colInfos[] = array( 	'hLabel' => $this->lang->def( '_EMAIL' ),
								'hClass' => "",
								'fieldClass' => "",
								'data' => 'email',
								'toDisplay' => true,
								'sortable' => false );

		$colInfos[] = array( 	'hLabel' => '<img src="'.$this->_getDeleteImage().'" '
										.'alt="'.$this->lang->def( '_DIRECTORY_REMOVEFROMGROUP' ).'" '
										.'title="'.$this->lang->def( '_DIRECTORY_REMOVEFROMGROUP' ).'" />',
								'hClass' => "image",
								'fieldClass' => "image",
								'data' => 'delete',
								'toDisplay' => true,
								'sortable' => false );
		return $colInfos;
	}

	function fetchRecord() {
		$arrResult = $this->data->fetchRecord();

		if( $arrResult === FALSE )
			return FALSE;
		$arrData['idst'] = $arrResult['idstMember'];
		if(isset($arrResult['groupid']) && $arrResult['groupid'] != NULL ) {
			if( $arrResult['hidden'] == 'true' ) {
				$arrData['itemtype'] = '<img src="'.$this->_getMemberTypeTree().'" '
											.'alt="'.$this->lang->def( '_DIRECTORY_MEMBERTYPETREE' ).'" '
											.'title="'.$this->lang->def( '_DIRECTORY_MEMBERTYPETREE' ).'" />';

				$arrData['iditem'] = $this->getTreeTranslation($this->aclManager->relativeId($arrResult['groupid']));
			} else {
				$arrData['itemtype'] = '<img src="'.$this->_getMemberTypeGroup().'" '
											.'alt="'.$this->lang->def( '_DIRECTORY_MEMBERTYPEGROUP' ).'" '
											.'title="'.$this->lang->def( '_DIRECTORY_MEMBERTYPEGROUP' ).'" />';
				$arrData['iditem'] = $this->aclManager->relativeId($arrResult['groupid']);
			}
		} else {
			$arrData['itemtype'] = '<img src="'.$this->_getMemberTypeUser().'" '
										.'alt="'.$this->lang->def( '_DIRECTORY_MEMBERTYPEUSER' ).'" '
										.'title="'.$this->lang->def( '_DIRECTORY_MEMBERTYPEUSER' ).'" />';
			$arrData['iditem'] = $this->aclManager->relativeId($arrResult['userid']);
		}
		$arrData['fullname'] 	= $arrResult['lastname'].' '.$arrResult['firstname'];
		$arrData['email'] 		= '<a href="mailto:'.$arrResult['email'].'">'.$arrResult['email'].'</a>';

		$arrData['delete'] = '<input type="image" class="tree_view_image" '
							.' src="'.$this->_getDeleteImage().'"'
							.' id="'.$this->id.'_'.$this->_getOpDeleteItemId().'_'.$arrResult['idstMember'].'" '
							.' name="'.$this->id.'['.$this->_getOpDeleteItemId().']['.$arrResult['idstMember'].']" '
							.' title="'.$this->_getDeleteLabel().'" '
							.' alt="'.$this->_getDeleteAlt().'" />';
		$this->printedItems[] = $arrResult['idstMember'];
		return $arrData;
	}
	
	function printOut() {
		$acl_man =& Docebo::user()->getAclManager();
		
		$out = $this->rend->OpenTable($this->_getTitle());

		$this->getRows( $this->_getStartRow(), $this->_getRowsPage());

		$totRow = $this->getTotalRows();
		
		if( $totRow == -1 ) {
			$totRow = $this->getLoadedRows();
		}
		$colInfo = $this->_getCols();
		$colData = $colInfo;
		$out .= $this->rend->WriteHeaderCss($colInfo);

		while( $values = $this->fetchRecord() )
		{
			if($values['iditem'] == '')
			{
				$values['itemtype'] = '<img src="'.getPathImage().'/directory/group.gif" alt="'.$this->lang->def('_DIRECTORY_MEMBERTYPEGROUP').'" title="'.$this->lang->def('_DIRECTORY_MEMBERTYPEGROUP').'">';
				$values['fullname'] = '-';
				$values['email'] = '-';
				
				$group_info = $acl_man->getGroup($values['idst'], FALSE);
				
				$values['iditem'] = $acl_man->relativeId($group_info[ACL_INFO_GROUPID]);
			}
			
			foreach( $colInfo as $key => $fieldInfo ) {
				$colData[$key]['data'] = $values[$colInfo[$key]['data']];
			}
			$out .= $this->rend->WriteRowCss($colData);
		}
		
		if( $this->insNew ) {
			$out .= $this->rend->WriteAddRow('<input type="submit" class="transparent_add_button"'
				.' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
				.' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
				.' value="'.$this->lang->def('_ADD').'"'
				.' title="'.$this->_getCreateLabel().'" '
				.' alt="'.$this->_getCreateAlt().'" />');
		}
		$out .= $this->rend->CloseTable();

		$this->rend->initNavBar($this->_getIdInitRowId(),'button');

		$out .= $this->rend->getNavBar($this->_getStartRow(), $totRow);
		
		$out .= $this->printState();

		return $out;
	}
}
?>
