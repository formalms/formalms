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
 * @subpackage user
 */

require_once(dirname(__FILE__).'/class.definition.php');
require_once(dirname(__FILE__).'/../lib/lib.directory.php');


define("FILTER_FOLD","FILTER_FOLD");

class Module_Directory extends Module {

	var $lang 							= NULL;
	var $tab 							= NULL;
	var $aclManager 					= NULL;
	var $selection 						= array();
	var $selection_alt 					= array();
	var $selector_mode 					= FALSE;
	var $use_multi_sel 					= FALSE;
	var $sel_extend 					= NULL;
	var $show_user_selector 			= TRUE;
	var $show_group_selector 			= TRUE;
	var $show_orgchart_selector 		= TRUE;
	var $show_orgchart_simple_selector 	= FALSE;
	var $multi_choice 					= TRUE;
	var	$group_filter 					= array();
	var $user_filter 					= array();
	var $not_idst_filter				= array();
	var $page_title 					= FALSE;
	var $select_all						= FALSE;
	var $deselect_all					= FALSE;
	var $nFields						= FALSE;
	var $requested_tab					= FALSE;
	var $_extra_form					= array();
	var $show_only_group_name 			= FALSE;

	var $show_simple_filter 			= FALSE;

	var $hide_anonymous 				= FALSE;
	var $hide_suspend					= TRUE;
	
	var $lms_editions_filter 			= FALSE;


	function Module_Directory() {
		parent::Module();
		$this->aclManager = new DoceboACLManager();
		$this->lang =& DoceboLanguage::createInstance('admin_directory', 'framework');

		require_once($GLOBALS["where_framework"]."/lib/lib.selextend.php");
		$this->sel_extend= new ExtendSelector();
		$this->multi_choice = Get::sett('use_org_chart_multiple_choice');
	}

	function directory_save_state( &$data, &$selection, &$selection_alt ) {
		$_SESSION['directory'] = serialize($data);
		$_SESSION['directory_selection'] = serialize($selection);
		$_SESSION['directory_selection_alt'] = serialize($selection_alt);
	}

	function &directory_load_state() {

		$result = array( array(), array(), array() );
		if( isset($_SESSION['directory']) && isset($_SESSION['directory_selection']) ) {
			$result = array( unserialize( $_SESSION['directory'] ),
							unserialize( $_SESSION['directory_selection'] ),
							unserialize( $_SESSION['directory_selection_alt'] ));
		}
		return $result;
	}

	function isParseDataAvailable( $arrayState ) {
		return isset( $arrayState[DIRECTORY_ID] );
	}

	function parseInput($arrayState) {
		$itemSelectedMulti = array();
		$printedItems = array();
		$itemSelectedMulti_alt = array();
		$printedItems_alt = array();
		if( isset( $arrayState[DIRECTORY_ID] ) ) {
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTITEM] )) {
				$itemSelectedMulti = array_keys ( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTITEM] );
				//print_r( $arrayState[DIRECTORY_ID] );
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTFOLD] )) {
				//print_r( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTFOLD] );
				$itemSelectedMulti_alt = array_keys ( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTFOLD] );
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_ID_PRINTEDITEM] )) {
				$printedItems = Util::unserialize(urldecode($arrayState[DIRECTORY_ID][DIRECTORY_ID_PRINTEDITEM]));
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_ID_PRINTEDFOLD] )) {
				$printedItems_alt = Util::unserialize(urldecode($arrayState[DIRECTORY_ID][DIRECTORY_ID_PRINTEDFOLD]));
				//print_r( $printedItems_alt );
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTMONO] )) {
				$itemSelectedMulti = array($arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTMONO]);
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTRADIO] )) {
				foreach( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTRADIO] as $key => $val ) {
					// $key contains tree normal group and descendants group idst
					// concat with an _
					list( $idst, $idst_desc ) = preg_split( '/_/', $key );
					$printedItems[] = $idst;
					$printedItems[] = $idst_desc;
					if( $val != '' ) $itemSelectedMulti[] = $val;
				}
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_SELECTALL] )) {
				$this->select_all = TRUE;
			}
			if( isset( $arrayState[DIRECTORY_ID][DIRECTORY_OP_DESELECTALL] )) {
				$this->deselect_all = TRUE;
				$array_selection = array();
				$array_selection_alt = array();
				$this->selection = $array_selection;
				$this->selection_alt = $array_selection_alt;
				$_SESSION['directory_selection'] 			= serialize($array_selection);
				$_SESSION['directory_selection_alt'] 		= serialize($array_selection_alt);
			}
		}
		
		if(!is_array($this->selection))
			$this->selection = array();
		
		$unselectedItems = array_diff( $printedItems, $itemSelectedMulti);
		$this->selection = array_diff($this->selection, $unselectedItems );
		$this->selection = array_values(array_unique(array_merge( $this->selection, $itemSelectedMulti )));

		$unselectedItems_alt = array_diff( $printedItems_alt, $itemSelectedMulti_alt);
		$this->selection_alt = array_diff($this->selection_alt, $unselectedItems_alt );
		$this->selection_alt = array_values(array_unique(array_merge( $this->selection_alt, $itemSelectedMulti_alt )));
		//print_r($this->selection_alt );
		//print_r($this->selection);
	}

	function directory_create_TabView( $op ) {
		global $_tab_op_map;
		$arr_tabs = array();
		require_once(_base_.'/lib/lib.tab.php');
		$this->tab = new TabView( DIRECTORY_TAB, 'index.php?modname=directory&amp;op=directory' );

		if( $this->show_user_selector ) {
			$tabPeople = new TabElemDefault(PEOPLEVIEW_TAB, $this->lang->def( '_USERS' ), getPathImage('fw').'area_title/directory_people.gif');
			$this->tab->addTab($tabPeople);
			$arr_tabs[] = PEOPLEVIEW_TAB;
		}

		if( $this->show_group_selector ) {
			$tabGroup = new TabElemDefault(GROUPVIEW_TAB, $this->lang->def( '_GROUPS' ), getPathImage('fw').'area_title/directory_group.gif');
			$this->tab->addTab($tabGroup);
			$arr_tabs[] = GROUPVIEW_TAB;
		}
		if( $this->show_orgchart_selector ) {
			$tabOrg = new TabElemDefault(ORGVIEW_TAB, $this->lang->def( '_DIRECTORY_ORGVIEWTITLE' ), getPathImage('fw').'area_title/directory_org.gif');
			$this->tab->addTab($tabOrg);
			$arr_tabs[] = ORGVIEW_TAB;
		}

		if( count($this->selection) == 0 )
			list($extra_data, $this->selection, $this->selection_alt) = $this->directory_load_state();
		$this->parseInput( $_POST );
		$this->tab->parseInput( $_POST, $extra_data );

		if( $this->tab->getActiveTab() === NULL )
			if( in_array ( $op, $arr_tabs ) )
				$this->tab->setActiveTab($op);
			else
				$this->tab->setActiveTab(ORGVIEW_TAB);
		if( $this->requested_tab !== FALSE )
			$this->tab->setActiveTab($this->requested_tab);
	}

	function directory_destroy_TabView( ) {
		$this->directory_save_state( $this->tab->getState(), $this->selection, $this->selection_alt );
	}


	function resetSelection($array_selection = NULL, $array_selection_alt = NULL) {
		if( $array_selection === NULL )
			$array_selection = array();
		if( $array_selection_alt === NULL )
			$array_selection_alt = array();
		$this->selection = $array_selection;
		$this->selection_alt = $array_selection_alt;
		$_SESSION['directory_selection'] 			= serialize($array_selection);
		$_SESSION['directory_start_selection'] 		= serialize($array_selection);
		$_SESSION['directory_selection_alt'] 		= serialize($array_selection_alt);
		$_SESSION['directory_start_selection_alt'] 	= serialize($array_selection_alt);
	}

	function getSelection( $arrayData ) {
		list($extra_data,$this->selection,$this->selection_alt) = $this->directory_load_state();
		$this->parseInput( $arrayData );
		return $this->selection;
	}

	function getSelectionAlt( $arrayData ) {
		list($extra_data,$this->selection,$this->selection_alt) = $this->directory_load_state();
		$this->parseInput( $arrayData );
		return $this->selection_alt;
	}

	function getAllSelection( $arrayData ) {
		list($extra_data,$this->selection,$this->selection_alt) = $this->directory_load_state();
		$this->parseInput( $arrayData );
		return array( $this->selection, $this->selection_alt);
	}

	function getPrintedItems($arrayState) {
		return Util::unserialize(urldecode($arrayState[DIRECTORY_ID][DIRECTORY_ID_PRINTEDITEM]));
	}

	function getStartSelection() {
		return unserialize( $_SESSION['directory_start_selection'] );
	}

	function getStartSelectionAlt() {
		return unserialize( $_SESSION['directory_start_selection_alt'] );
	}

	function getUnselected() {
		return array_diff( $this->getStartSelection(), $this->selection );
	}

	function getUnselectedAlt() {
		return array_diff( $this->getStartSelectionAlt(), $this->selection_alt );
	}

	function useExtraMenu() {
		return true;
	}

	function loadExtraMenu() {
		loadAdminModuleLanguage($this->module_name);
	}

	function loadBody() {
		global $op, $modname;
		
		switch( $op ) {

			// group related actions ==========================================
			case 'listgroup': {
				checkPerm('view_group', false, 'directory', 'framework');
				$this->loadGroupView();
			};break;
			case 'editgroup': {
				checkPerm('editgroup', false, 'directory', 'framework');
				$this->editGroup( importVar( 'groupid', FALSE, '' ) );
			};break;
			case 'deletegroup': {
				checkPerm('delgroup', false, 'directory', 'framework');
				$this->deleteGroup( importVar( 'groupid', FALSE, '' ) );
			};break;

			// group members related actions ===================================
			case 'import_groupuser' : {
				checkPerm('associate_group', false, 'directory', 'framework');
				$this->importToGroup();
			};break;
			case 'import_groupuser_2' : {
				checkPerm('associate_group', false, 'directory', 'framework');
				$this->importToGroup_step2();
			};break;
			case 'import_groupuser_3' : {
				checkPerm('associate_group', false, 'directory', 'framework');
				$this->importToGroup_step3();
			};break;

			case 'addtogroup': {
				checkPerm('associate_group', false, 'directory', 'framework');
				$this->addToGroup( importVar( 'groupid', FALSE, '' ) );
			};break;
			case 'membersGroupDelFilter':
			case 'membersgroup': {
				checkPerm('view_group', false, 'directory', 'framework');
				$this->membersGroup( importVar( 'groupid', FALSE, '' ) );
			};break;
			case "waitinggroup" : {
				checkPerm('view_group', false, 'directory', 'framework');
				$this->waitingUserGroup( importVar( 'groupid', FALSE, '' ) );
			};break;

			// org chart related actions ======================================
			case 'org_chart': {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->loadOrgChartView();
			};break;
			case 'addtotree': {
				checkPerm('edituser_org_chart', false, 'directory', 'framework');
				$this->addToTree( importVar( 'treeid', FALSE, '' ) );
			};break;
			case 'assignfield': {
				checkPerm('edituser_org_chart', false, 'directory', 'framework');
				$this->loadAssignField( importVar( 'groupid', FALSE, '' ) );
			};break;
			case 'assignfieldmandatory': {
				checkPerm('edituser_org_chart', false, 'directory', 'framework');
				$this->loadAssignField2( importVar( 'groupid', FALSE, '' ) );
			};break;

			// users related actions =========================================
			case 'listuser': {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->loadPeopleView();
			};break;
			case 'org_createuser': {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->org_createUser();
			};break;
			case 'org_waitinguser': {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->org_waitingUser();
			};break;


			case 'org_manageuser': {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->org_manageuser();
			};break;
			
			case 'view_deleted_user':
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->viewDeletedUser();
			break;

			case 'quick_change_password' : {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->quickChangePassword();
			};break;


			default: {
				checkPerm('view_org_chart', false, 'directory', 'framework');
				$this->loadSelector( '', '', '', FALSE );
			}
		}
	}

	// TODO: move this function in a proper position
	function quickChangePassword() {
		checkPerm('edituser_org_chart', false, 'directory', 'framework');

		// check If the user exist
		$user_info = $this->aclManager->getUser(false, $_POST['cp_userid']);
		if($user_info != false) {
			//change the user password
			$re = $this->aclManager->updateUser( $user_info[ACL_INFO_IDST], FALSE, FALSE, FALSE,
								$_POST['cp_pwd'], FALSE, FALSE,
								FALSE, FALSE, FALSE, ( isset($_POST['cp_force']) ? 1 : '' ) );
			if($re) {

				$GLOBALS['page']->add(getResultUi($this->lang->def('_OPERATION_SUCCESSFUL') ));
			} else {
				//update user failed
				$GLOBALS['page']->add(getErroUi($this->lang->def('_OPERATION_FAILURE') ));
			}
		} else {
			// user not found
			$GLOBALS['page']->add(getResultUi($this->lang->def('_USER_NOT_FOUND') ));
		}
		$this->loadOrgChartView();
	}

	function _getTableDeletedUser()
	{
		return 'core_deleted_user';
	}
	
	function _getTableUser()
	{
		return 'core_user';
	}
	
	function viewDeletedUser()
	{
		require_once(_base_.'/lib/lib.table.php');
		
		$lang =& DoceboLanguage::createInstance('profile', 'framework');
		$out =& $GLOBALS['page'];
		$out->setWorkingZone('content');
		$acl_man =& Docebo::user()->getAclManager();
		
		$max_row = 10;
		$tb = new Table($max_row);
		$tb->initNavBar('ini', 'link');
		$ini = $tb->getSelectedElement();
		
		$query = "SELECT * FROM ".$this->_getTableDeletedUser()."";
		$result = sql_query($query);
		$num_rows = sql_num_rows($result);
		//print_r($ini);
		if($ini)
		{
			$limit = $ini;
		}
		else
		{
			$limit = 0;
		}
		
		$query = "SELECT d.idst, d.userid, d.firstname, d.lastname, d.email, d.lastenter, d.deletion_date, d.deleted_by, u.userid, u.firstname, u.lastname" .
				" FROM ".$this->_getTableDeletedUser()." AS d JOIN" .
				" ".$this->_getTableUser()." AS u ON d.deleted_by = u.idst" .
				" LIMIT ".$limit.", ".$max_row."";
		
		$result = sql_query($query);
		
		$out->add(getTitleArea($lang->def('_DELETED_USER_LIST')).'<div class="std_block">');
		$out->add(getBackUi('index.php?modname=directory&amp;op=org_chart', "&lt;&lt;".$lang->def('_BACK')));
		
		if ($num_rows)
		{
			$cont_h = array
			(
				$lang->def('_IDST_DELETED_USER'),
				$lang->def('_USERNAME'),
				$lang->def('_FIRSTNAME'),
				$lang->def('_LASTNAME'),
				$lang->def('_EMAIL'),
				$lang->def('_DELETION_DATE'),
				$lang->def('_USERID_DELETER'),
				$lang->def('_FIRSTNAME_DELETER'),
				$lang->def('_LASTNAME_DELETER')
			);
			$type_h = array('', '', '', '', '', '', '', '', '', '', '');
			
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);
			
			while(list($idst_deleted, $userid_deleted, $firstname_deleted, $lastname_deleted, $email_deleted, $last_enter_deleted, $deletion_date, $idst_deleter, $userid_deleter, $firstname_deleter, $lastname_deleter) = sql_fetch_row($result))
			{
				$count = array();
				
				$count[] = $idst_deleted;
				$count[] = $acl_man->relativeId($userid_deleted);
				$count[] = $firstname_deleted;
				$count[] = $lastname_deleted;
				$count[] = $email_deleted;
				
				$count[] = Format::date($deletion_date);
				
				$count[] = $acl_man->relativeId($userid_deleter);
				$count[] = $firstname_deleter;
				$count[] = $lastname_deleter;
				
				$tb->addBody($count);
			}
			
			$out->add(
				$tb->getTable()
				.$tb->getNavBar($ini, $num_rows)
				.'</div>'
			);
		}
		else
		{
			$out->add($lang->def('_EMPTY_SELECTION'));
		}
		
		$out->add(getBackUi('index.php?modname=directory&amp;op=org_chart', "&lt;&lt;".$lang->def('_BACK')));
		$out->add('</div>');
	}
	
	function org_manageuser() {
		checkPerm('view_org_chart', false, 'directory', 'framework');
		require_once(_base_.'/lib/lib.user_profile.php');

		$lang =& DoceboLanguage::createInstance('profile', 'framework');

		$profile = new UserProfile( importVar('id_user', true, 0) );
		$profile->init('profile', 'framework', 'modname=directory&op=org_manageuser&id_user='.importVar('id_user', true, 0), 'ap');
		$profile->enableGodMode();

		$profile->setEndUrl('index.php?modname=directory&op=org_chart#user_row_'.importVar('id_user', true, 0));
		
		$GLOBALS['page']->add(
			$profile->getHead()
			.getBackUi('index.php?modname=directory&amp;op=org_chart', $lang->def('_BACK'))

			.$profile->performAction()

			.$profile->getFooter()
		, 'content');
		
	}

	/**
	 * Set filters for user data retriever
	 * @param string $filter_type one of the following:
	 * 								- "platform": retrieve only user of the platforms
	 * 												given in $filter_arg array
	 * 								- "group": retrieve only user members of the
	 * 												groups given in $filter_arg array
	 * 								- "exclude": exclude users with idst passed in
	 * 												$filter_arg array
	 * @param array $filter_arg an array of platforms or an array of groups or
	 * 								an array of idst (see $filter_type)
	 * @return NULL
	 **/
	function setUserFilter($filter_type, $filter_arg) {
		switch($filter_type) {
			case "platform" : $this->user_filter['platform'] = $filter_arg;break;
			case "user" 	: $this->user_filter['user'] = $filter_arg;break;
			case "group" 	: $this->user_filter['group'] = $filter_arg;break;
			case "exclude"	: $this->user_filter['exclude'] = $filter_arg;break;
		}
		return;
	}

	function setGroupFilter($filter_type, $filter_arg) {

		switch($filter_type) {
			case "platform" : $this->group_filter['platform'] = $filter_arg;break;
			case "group" 	: $this->group_filter['group'] = $filter_arg;break;
			case "path" 	: $this->group_filter['path'] = $filter_arg;break;
		}
		return;
	}

	/**
	 * @param string 	$page_title	the value returned by getTitleArea or an equivalent intestation for the page
	 */
	function setPageTitle($page_title) {

		$this->page_title = $page_title;
	}

	function addFormInfo($string) {

		$this->_extra_form[] = $string;
	}

	function resetFormInfo() {

		$this->_extra_form = array();
	}

	function loadSelector($url, $title, $text, $selector_mode = TRUE ) {

		require_once(_base_.'/lib/lib.form.php');
		global $op, $modname;
		$this->directory_create_TabView($op);
		$this->selector_mode = $selector_mode;

		//print_r($this->selection);

		if( $selector_mode ) {
			if($this->page_title === false) {
				$GLOBALS['page']->add( getTitleArea($title, 'directory' ), 'content' );
			} else {
				$GLOBALS['page']->add( $this->page_title, 'content' );
			}
			$GLOBALS['page']->add( '<div class="std_block">', 'content');
			$GLOBALS['page']->add( $text.'<br /><br />', 'content' );
			$GLOBALS['page']->addEnd( '</div>', 'content');
		}
		$GLOBALS['page']->add(	'<form action="'.$url.'" method="post" id="directoryselector">'
								.'<input type="hidden" id="authentic_request_directoryselector" name="authentic_request" value="'.Util::getSignature().'" />', 'content' );

		if(is_array($this->_extra_form) && !empty($this->_extra_form)) {

			$GLOBALS['page']->add( implode('', $this->_extra_form), 'content' );
		}

		if (($this->use_multi_sel)) {
			$GLOBALS['page']->add("\n\n");
			$GLOBALS['page']->add($this->sel_extend->writeSelectedInfo());
			$GLOBALS['page']->add("\n\n");
		}

		switch( $this->tab->getActiveTab() ) {
			case PEOPLEVIEW_TAB: {
				$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
				$this->loadPeopleView($url);
			};break;
			case GROUPVIEW_TAB: {
				$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
				$this->loadGroupView();
			};break;
			case ORGVIEW_TAB: {
				$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
				$this->loadOrgChartView();
			};break;
			default : {
				if($this->show_user_selector) {

					$this->tab->setActiveTab(PEOPLEVIEW_TAB);
					$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
					$this->loadPeopleView($url);
				} elseif($this->show_group_selector && $GLOBALS['use_groups'] == '1') {

					$this->tab->setActiveTab(GROUPVIEW_TAB);
					$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
					$this->loadGroupView();
				} elseif($this->show_orgchart_selector && Get::sett('use_org_chart') == '1') {

					$this->tab->setActiveTab(ORGVIEW_TAB);
					$GLOBALS['page']->add( $this->tab->printTabView_Begin('', FALSE), 'content' );
					$this->loadOrgChartView();
				}
			};break;
		}
		$GLOBALS['page']->add( $this->tab->printTabView_End(), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton( DIRECTORY_ID.'_'.DIRECTORY_OP_SELECTALL, DIRECTORY_ID.'['.DIRECTORY_OP_SELECTALL.']', $this->lang->def('_SELECTALL') ), 'content' );
		$GLOBALS['page']->add( Form::getButton( DIRECTORY_ID.'_'.DIRECTORY_OP_DESELECTALL, DIRECTORY_ID.'['.DIRECTORY_OP_DESELECTALL.']', $this->lang->def('_UNSELECT_ALL') ), 'content' );
		$GLOBALS['page']->add( Form::getButton( "okselector", "okselector", $this->lang->def('_CONFIRM') ), 'content' );
		$GLOBALS['page']->add( Form::getButton( "cancelselector", "cancelselector", $this->lang->def('_CANCEL') ), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( '</form>', 'content' );
		$this->directory_destroy_TabView( );
	}


	function setNFields($nFields) {
		$this->nFields=$nFields;
	}


	function loadPeopleView($url = '') {
		//checkPerm('view_user', false, 'directory', 'framework');
		$data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
		$rend = new Table(Get::sett('visuUser'));
		$lv = new PeopleListView('', $data, $rend, 'pepledirectory');
		
		if ($this->nFields !== FALSE)
			$lv->setNFields($this->nFields);

		if ($this->show_simple_filter !== FALSE)
			$lv->show_simple_filter = TRUE;

		if ($this->lms_editions_filter !== FALSE)
			$lv->lms_editions_filter = TRUE;

		if ($this->hide_anonymous !== FALSE)
			$lv->hide_anonymous = TRUE;
		
		if ($this->hide_suspend !== TRUE)
			$lv->hide_suspend = FALSE;

		if( $url == '' )
			$url = "index.php?modname=directory&amp;op=listuser";
		$lv->setLinkPagination( $url );
		$lv->aclManager =& $this->aclManager;
		$lv->selector_mode = $this->selector_mode;
		$lv->select_all = $this->select_all;
		$lv->deselect_all = $this->deselect_all;
		$lv->use_multi_sel=$this->use_multi_sel;
		$lv->sel_extend=$this->sel_extend;
		$lv->idModule = 'directory_selector';
		if( $this->selector_mode === FALSE )
			$lv->setInsNew(TRUE);
		$lv->parsePositionData( $_POST );
		$lv->itemSelectedMulti = $this->selection;
		
		if( $lv->getOp() == 'newitem' ) {
			$this->editPerson();
		} elseif( $lv->getOp() == 'editperson' ) {
			$this->editPerson($lv->getIdSelectedItem());
		} elseif( $lv->getOp() == 'deleteperson' ) {
			$this->deletePerson($lv->getIdSelectedItem());
		} elseif( $lv->getOp() == 'suspendperson' ) {
			$this->suspendPerson($lv->getIdSelectedItem());
			$GLOBALS['page']->add(getResultUi($this->lang->def('_SUSPENDED') ));
		} elseif( $lv->getOp() == 'recoverperson' ) {
			$this->recoverPerson($lv->getIdSelectedItem());
			$GLOBALS['page']->add(getResultUi($this->lang->def('_REACTIVATED_USER') ));
		} else {
			if( !$this->selector_mode ) {
				$GLOBALS['page']->add( getTitleArea($this->lang->def( '_USERS' ), 'directory_people' ), 'content' );
				$GLOBALS['page']->add( '<div class="std_block">', 'content' );
				$GLOBALS['page']->addEnd( '</div>', 'content' );
				$GLOBALS['page']->add( '<form id="dirctory_listpeople" action="index.php?modname=directory&amp;op=listuser" method="post">'
					.'<input type="hidden" id="authentic_request_listpeople" name="authentic_request" value="'.Util::getSignature().'" />', 'content' );
				$GLOBALS['page']->addEnd( '</form>', 'content' );
			}
			if( isset( $this->user_filter['exclude'] ) )
				$data->addNotFilter($this->user_filter['exclude']);

			if( isset( $this->user_filter['user'] ) ) {
					$data->setUserFilter($this->user_filter['user']);
			}

			if( isset( $this->user_filter['group'] ) ) {
				foreach( $this->user_filter['group'] as $idstGroup )
					$data->setGroupFilter($idstGroup);
			} else {
				$userlevelid = Docebo::user()->getUserLevelId();
				if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
					require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
					$adminManager = new AdminManager();
					$data->intersectGroupFilter($adminManager->getAdminTree(Docebo::user()->getIdSt()));
				}
			}
			// print out the listview
			$GLOBALS['page']->add($lv->printOut(), 'content');
		}
	}

	function deletePerson($userid) {
		require_once(_base_.'/lib/lib.form.php');
		//if( $userid === FALSE ) //this has been commented after dialogbox introduction
			return;
		$arrUser = $this->aclManager->getUser( FALSE, $userid );
		if( $arrUser !== FALSE ) {
			$idst = $arrUser[0];
			$firstname = $arrUser[2];
			$lastname = $arrUser[3];
		}
		$are_title = array(

		);
		$GLOBALS['page']->add(

			'<h2 id="directory_deluser">'.$this->lang->def('_DEL').'</h2>'

			.Form::openForm( 	'directorydeleteperson',
								'index.php?modname=directory&amp;op=org_chart')
			.'<input type="hidden" id="idst" name="idst" value="'.$idst.'" 	\>'
			.getDeleteUi(	$this->lang->def('_AREYOUSURE'),
							$this->lang->def('_USERNAME').' : '.$userid.'<br />'
								.$this->lang->def('_LASTNAME').' : '.$lastname.'<br />'
								.$this->lang->def('_FIRSTNAME').' : '.$firstname,
							false,
							'deleteperson',
							"deletepersoncancel"
						)
			.Form::closeForm(), 'content');
	}

	function editPerson($userid = FALSE, $arr_idst_groups = FALSE, $form_url=FALSE) {
		require_once(_base_.'/lib/lib.form.php');
		require_once(_adm_.'/lib/lib.adminmanager.php');
		if( $userid === FALSE ) {
			$userid	= importVar('userid',	FALSE, 	'' );
			$userLabel = importVar('userid',	FALSE, 	$this->lang->def( '_NEW_USER' ) );
		} else {
			$userLabel = $userid;
		}
		$firstname = importVar('firstname',	FALSE, 	'' );;
		$lastname = importVar('lastname',	FALSE, 	'' );;
		$email = importVar('email',	FALSE, 	'' );;
		$pass = importVar('pass',	FALSE, 	'' );;
		$idst = '';
		// get all levels
		$arr_levels_id = $this->aclManager->getAdminLevels();
		$arr_levels_idst = array_values($arr_levels_id);
		$arr_levels_id = array_flip( $arr_levels_id );
		$arr_levels_translation = array();
		foreach( $arr_levels_id as $lev_idst => $lev_id ) {

			if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

				if($lev_id == ADMIN_GROUP_USER)
					$arr_levels_translation[$lev_idst] = $this->lang->def('_DIRECTORY_'.$lev_id);
			} else {
				$arr_levels_translation[$lev_idst] = $this->lang->def('_DIRECTORY_'.$lev_id);
			}
		}
		// set default level
		$userlevel = array_search(ADMIN_GROUP_USER, $arr_levels_id);
		if( $userid != '' ) {
			$arrUser = $this->aclManager->getUser( FALSE, $userid );
			if( $arrUser !== FALSE ) {
				$idst = $arrUser[0];
				$firstname = $arrUser[2];
				$lastname = $arrUser[3];
				$email = $arrUser[5];
				// compute user level
				$arr_groups = $this->aclManager->getGroupsContainer($idst);

				$arr_user_level = array_intersect( $arr_levels_idst, $arr_groups );
				$arr_user_level = array_values($arr_user_level);
				if( count( $arr_user_level ) > 0 )
					$userlevel = $arr_user_level[0];
				else
					$userlevel = $arr_levels_idst[0];
			} else {
				// the user don't exist
				$firstname = $_POST['firstname'];
				$lastname = $_POST['lastname'];
				$email = $_POST['email'];
				// get arr_folders to know collect custom fields
				if( $arr_idst_groups === FALSE && isset( $_POST['arr_idst_groups'] ) ) {
					$arr_idst_groups = Util::unserialize(urldecode( $_POST['arr_idst_groups'] ) );
				}
			}
		} else {
			if( $arr_idst_groups === FALSE && isset( $_POST['arr_idst_groups'] ) ) {
				$arr_idst_groups = Util::unserialize(urldecode( $_POST['arr_idst_groups'] ) );
			}
			if(!$arr_idst_groups) {
				
				$arr_idst_groups = array();
				$oc = $this->aclManager->getGroup(false, '/oc_0');
				$ocd = $this->aclManager->getGroup(false, '/ocd_0');
				
				$arr_idst_groups['/oc_0'] = $oc[ACL_INFO_IDST];
				$arr_idst_groups['/ocd_0'] = $ocd[ACL_INFO_IDST];
			}
		}
		
		/*
		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_USERS' )
								.': '.$userLabel, 'directory_people' ), 'content' );
*/
		//$GLOBALS['page']->add( '<div class="std_block">', 'content');

		if ($form_url === FALSE)
			$form_url='index.php?modname=directory&amp;op=org_chart';

		$GLOBALS['page']->add( Form::getFormHeader( $this->lang->def( '_MOD' ) ), 'content' );
		$GLOBALS['page']->add( Form::openForm( 	'directoryeditperson',
												$form_url,
												FALSE,
												'post',
												'multipart/form-data'),
								'content');
		$GLOBALS['page']->add( Form::openElementSpace(), 'content' );

		$GLOBALS['page']->add( Form::getOpenFieldset( $this->lang->def( '_MOD' ).' - '.$userLabel ), 'content' );
		$GLOBALS['page']->add( Form::getTextfield( $this->lang->def( '_USERNAME' ), "userid", "userid", 50, $userid), 'content' );
		$GLOBALS['page']->add( Form::getTextfield( $this->lang->def( '_FIRSTNAME' ), "firstname", "firstname", 50, $firstname), 'content' );
		$GLOBALS['page']->add( Form::getTextfield( $this->lang->def( '_LASTNAME' ), "lastname", "lastname", 50, $lastname), 'content' );
		$GLOBALS['page']->add( Form::getTextfield( $this->lang->def( '_EMAIL' ), "email", "email", 50, $email), 'content' );
		$GLOBALS['page']->add( Form::getPassword( $this->lang->def( '_PASSWORD' ), "pass", "pass", 50), 'content' );

		$GLOBALS['page']->add( Form::getDropdown(
										$this->lang->def( '_LEVEL' ),
										"userlevel",
										"userlevel",
										$arr_levels_translation,
										$userlevel),
								'content');
		$GLOBALS['page']->add( Form::getHidden('olduserlevel', 'olduserlevel', $userlevel), 'content' );
		$GLOBALS['page']->add( Form::getHidden('idst', 'idst', $idst), 'content' );
		$GLOBALS['page']->add( Form::getHidden('arr_idst_groups',
												'arr_idst_groups',
												urlencode(Util::serialize($arr_idst_groups))),
								'content' );
								
		$preference = new UserPreferences(0);
														
		$GLOBALS['page']->add( $preference->getModifyMask('ui.') );												
														
														
		
		$GLOBALS['page']->add( Form::getCloseFieldset(), 'content' );
		/*
		$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton("editpersonsave","editpersonsave",$this->lang->def( '_SAVE' )), 'content' );
		$GLOBALS['page']->add( Form::getButton("editpersoncancel","editpersoncancel",$this->lang->def( '_CANCEL' )), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::openElementSpace(), 'content' );
		*/
		//-extra field-----------------------------------------------
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		$fields = new FieldList();
		if( $arr_idst_groups != FALSE ) {
			$acl =& Docebo::user()->getACL();
			$arr_idst_all = $acl->getArrSTGroupsST(array_values($arr_idst_groups));
		} else
			$arr_idst_all = FALSE;
		if ($fields->playFieldsForUser(( $userid !== false ? $idst : -1 ), $arr_idst_all))
		{
		$GLOBALS['page']->add( Form::getOpenFieldset( $this->lang->def( '_ASSIGNED_EXTRAFIELD' ) ), 'content' );
		$GLOBALS['page']->add( $fields->playFieldsForUser(
															( $userid !== false ? $idst : -1 ),
															$arr_idst_all
														), 'content' );
		
														
		$GLOBALS['page']->add( Form::getCloseFieldset(), 'content' );
		}
		//-----------------------------------------------------------

		$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton("editpersonsave_2","editpersonsave",$this->lang->def( '_SAVE' )), 'content' );
		$GLOBALS['page']->add( Form::getButton("editpersoncancel_2","editpersoncancel",$this->lang->def( '_CANCEL' )), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::closeForm(), 'content' );
		//$GLOBALS['page']->add( '</div>', 'content');
	}

	function loadGroupView() {
		$data = new GroupDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
		$rend = new Table(Get::sett('visuItem'));
		$lv = new GroupListView('', $data, $rend, 'groupdirectory');
		$lv->aclManager =& $this->aclManager;
		$lv->selector_mode = $this->selector_mode;
		$lv->select_all = $this->select_all;
		$lv->deselect_all = $this->deselect_all;
		$lv->use_multi_sel=$this->use_multi_sel;
		$lv->sel_extend=$this->sel_extend;
		$lv->idModule = 'directory_selector';
		if($this->show_only_group_name === true) {
			$lv->showOnlyGroupName(true);
		}
		if(isset($this->group_filter['platform'])) {
			$data->addPlatformFilter($this->group_filter['platform']);
		} else {
			$data->addPlatformFilter(array(Get::cur_plat()));
		}
		if(isset($this->group_filter['group'])) {
			$data->addGroupFilter($this->group_filter['group']);
		}
		if(isset($this->group_filter['path'])) {
			$data->addPathFilter($this->group_filter['path']);
		}
		if( $this->selector_mode === FALSE )
			$lv->setInsNew(TRUE);
		$lv->parsePositionData( $_POST );
		$lv->itemSelectedMulti = $this->selection;

		if( $lv->getOp() == 'newitem' ) {
			Util::jump_to( 'index.php?modname=directory&op=editgroup' );
		} elseif( $lv->getOp() == 'addtogroup' ) {
			Util::jump_to( 'index.php?modname=directory&op=addtogroup&groupid='.$lv->getIdSelectedItem() );
		} elseif( $lv->getOp() == 'assignfield' ) {
			Util::jump_to( 'index.php?modname=directory&op=assignfield&groupid='.$lv->getIdSelectedItem() );
		} elseif( $lv->getOp() == 'membersgroup' ) {
			Util::jump_to( 'index.php?modname=directory&op=membersgroup&groupid='.$lv->getIdSelectedItem() );


		} elseif( $lv->getOp() == 'import_groupuser' ) {
			Util::jump_to( 'index.php?modname=directory&op=import_groupuser' );
		} elseif( $lv->getOp() == 'import_groupuser_2' ) {
			Util::jump_to( 'index.php?modname=directory&op=import_groupuser_2' );
		} elseif( $lv->getOp() == 'import_groupuser_3' ) {
			Util::jump_to( 'index.php?modname=directory&op=import_groupuser_3' );


		} elseif( $lv->getOp() == 'editgroup' ) {
			Util::jump_to( 'index.php?modname=directory&op=editgroup&groupid='.$lv->getIdSelectedItem() );
		} elseif( $lv->getOp() == 'deletegroup' ) {
			Util::jump_to( 'index.php?modname=directory&op=deletegroup&groupid='.$lv->getIdSelectedItem() );
		} elseif( $lv->getOp() == 'waitinggroup' ) {
			Util::jump_to( 'index.php?modname=directory&op=waitinggroup&groupid='.$lv->getIdSelectedItem() );
		} else {
			if( !$this->selector_mode ) {
				$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' ), 'directory_group' ), 'content' );
				$GLOBALS['page']->add( '<div class="std_block">', 'content' );
				$GLOBALS['page']->addEnd( '</div>', 'content' );
				$GLOBALS['page']->add( '<form id="dirctory_listgroup" action="index.php?modname=directory&amp;op=listgroup" method="post">'
					.'<input type="hidden" id="authentic_request_listgroup" name="authentic_request" value="'.Util::getSignature().'" />', 'content' );
				$GLOBALS['page']->addEnd( '</form>', 'content' );
			}
			$GLOBALS['page']->add($lv->printOut(), 'content');
		}
	}

	function deleteGroup($groupid) {
		require_once(_base_.'/lib/lib.form.php');
		if( $groupid === FALSE )
			return;
		$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
		if( $arrGroup !== FALSE ) {
			$idst = $arrGroup[0];
			$description = $arrGroup[2];
		}
		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
								.': '.$this->lang->def('_DEL'), 'directory_group' ), 'content' );

		$GLOBALS['page']->add( '<div class="std_block">'
			.'<b>'.$this->lang->def( '_AREYOUSURE' ).'</b><br />'
			.'<div class="evidenceBlock">'
			.'<img src="'.getPathImage('fw').'standard/delete.png" '
			.'alt="'.$this->lang->def( '_DIRECTORY_GROUPID' ).'" '
			.'title="'.$this->lang->def('_DEL').' '.$groupid.'"/><b>'
			.$groupid.'</b> [ '
			.$description.']', 'content');
		$GLOBALS['page']->add( '</div>', 'content');
		$GLOBALS['page']->add( Form::openForm( 	'directorydeletegroup',
								'index.php?modname=directory&amp;op=listgroup'),
								'content' );
		$GLOBALS['page']->add( Form::getHidden('idst', 'idst', $idst), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton("deletegroup","deletegroup",$this->lang->def( '_DEL' )), 'content' );
		$GLOBALS['page']->add( Form::getButton("deletegroupcancel","deletegroupcancel",$this->lang->def( '_CANCEL' )), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::closeForm(), 'content' );
		$GLOBALS['page']->add( '</div>', 'content');
	}

	function editGroup($groupid = FALSE) {
		require_once(_base_.'/lib/lib.form.php');
		if( $groupid === FALSE || $groupid === '' ) {
			$groupid	= importVar('groupid',	FALSE, 	'' );
			$groupLabel = importVar('groupid',	FALSE, 	$this->lang->def( '_DIRECTORY_NEWGROUP' ) );
		} else {
			$groupLabel = $groupid;
		}
		$description 		= '';
		$idst 				= '';
		$type 				= 'free';
		$show_on_platform 	= array();
		if( $groupid != '' ) {
			$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
			if( $arrGroup !== FALSE ) {
				$idst 				= $arrGroup[0];
				$description 		= $arrGroup[2];
				$type 				= $arrGroup[4];
				$show_on_platform 	= array_flip(explode(',', $arrGroup[5]));
			}
		}
		$all_group_type = array(
			'free' => $this->lang->def('_DIRECTORY_GROUPTYPE_FREE'),
			'moderate' => $this->lang->def('_DIRECTORY_GROUPTYPE_MODERATE'),
			'private' => $this->lang->def('_DIRECTORY_GROUPTYPE_PRIVATE'),
			'invisible' => $this->lang->def('_DIRECTORY_GROUPTYPE_INVISIBLE')
		);

		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
								.': '.$groupLabel, 'directory_group' ), 'content' );

		$GLOBALS['page']->add( '<div class="std_block">', 'content');
		$GLOBALS['page']->add( Form::getFormHeader( $this->lang->def( '_DEL' ) ), 'content' );
		$GLOBALS['page']->add( Form::openForm( 	'directoryeditgroup',
								'index.php?modname=directory&amp;op=listgroup'),
								'content' );
		$GLOBALS['page']->add( Form::openElementSpace(), 'content' );

		$GLOBALS['page']->add( Form::getOpenFieldset( $this->lang->def( '_DEL' ).' - '.$groupLabel ), 'content' );

		$GLOBALS['page']->add( Form::getTextfield( $this->lang->def( '_DIRECTORY_GROUPID' ), "groupid", "groupid", 50, $groupid), 'content' );
		$GLOBALS['page']->add( Form::getSimpleTextarea( $this->lang->def( '_DESCRIPTION' ), "description", "description", $description), 'content' );
		$GLOBALS['page']->add( Form::getHidden('idst', 'idst', $idst), 'content' );

		$GLOBALS['page']->add( Form::getDropdown($this->lang->def('_DIRECTORY_GROUPTYPE'), 'group_type', 'group_type', $all_group_type, $type), 'content' );
		$GLOBALS['page']->add( Form::getCloseFieldset(), 'content' );

		$GLOBALS['page']->add( Form::getOpenFieldset($this->lang->def('_DIRECTORY_GROUP_VISIBLE')) , 'content');

		$plt_man = PlatformManager::createInstance();
		$plt_list = $plt_man->getPlatformList(true);

		$GLOBALS['page']->add( Form::getHidden('show_on_platform_framework', 'show_on_platform[framework]', 1), 'content');
		while(list($code, $name) = each($plt_list)) {

			$GLOBALS['page']->add( Form::getCheckbox($name, 'show_on_platform_'.$code, 'show_on_platform['.$code.']', 1, isset($show_on_platform[$code])) , 'content');
		}
		$GLOBALS['page']->add( Form::getCloseFieldset() , 'content');

		$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton("editgroupsave","editgroupsave",$this->lang->def( '_SAVE' )), 'content' );
		$GLOBALS['page']->add( Form::getButton("editgroupcancel","editgroupcancel",$this->lang->def( '_CANCEL' )), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::closeForm(), 'content' );
		$GLOBALS['page']->add( '</div>', 'content');
	}


	function waitingUserGroup($groupid) {
		require_once(_base_.'/lib/lib.form.php');
		require_once(_base_.'/lib/lib.table.php');
		require_once($GLOBALS['where_framework'] . "/lib/lib.field.php");
		$acl =& Docebo::user()->getAcl();
		$groupLabel = $groupid;
		if( $groupid != '' ) {
			$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
			if( $arrGroup !== FALSE ) {
				$idst = $arrGroup[0];
			}
		}
		$tb = new Table(0, $this->lang->def( '_DIRECTORY_GROUPWAIT_ACCORDECLINE' ),
								$this->lang->def( '_DIRECTORY_GROUPWAIT_ACCORDECLINE_SUMMARY' ));
		$tb->setColsStyle(array('', '', 'image', 'image'));
		$tb->addHead(array(
			$this->lang->def('_USERNAME'),
			$this->lang->def('_LASTNAME_FIRSTNAME'),
			$this->lang->def('_EMAIL'),
			$this->lang->def('_ACCEPT'),
			$this->lang->def('_DECLINE'),
			$this->lang->def('_POSTPONE')
		));
		$waiting_users =& $this->aclManager->getWaitingUserForGroup($idst);
		if($waiting_users) {

			while(list($idst_u, $user_info) = each($waiting_users)) {

				$more = '';
				$more = ( isset($_GET['id_user']) && $_GET['id_user'] == $idst_u
					? '<a href="index.php?modname=directory&amp;op=waitinggroup&groupid='.$groupid.'"><img src="'.getPathImage().'standard/less.gif"></a> '
					: '<a href="index.php?modname=directory&amp;op=waitinggroup&groupid='.$groupid.'&amp;id_user='.$idst_u.'"><img src="'.getPathImage().'standard/more.gif"></a> ');

				$tb->addBody(array(
					$more.$this->aclManager->relativeId($user_info[ACL_INFO_USERID]),
					$user_info[ACL_INFO_LASTNAME].' '.$user_info[ACL_INFO_FIRSTNAME],
					'<a href="mailto:'.$user_info[ACL_INFO_EMAIL].'">'.$user_info[ACL_INFO_EMAIL].'</a>',
					Form::getInputRadio('waiting_user_accept_'.$idst_u, 'waiting_user['.$idst_u.']', 'accept', true, '')
						.''.Form::getLabel('waiting_user_accept_'.$idst_u, $this->lang->def('_ACCEPT'), 'access-only'),
					Form::getInputRadio('waiting_user_decline_'.$idst_u, 'waiting_user['.$idst_u.']', 'decline', false, '')
						.''.Form::getLabel('waiting_user_decline_'.$idst_u, $this->lang->def('_DECLINE'), 'access-only'),
					Form::getInputRadio('waiting_user_postpone_'.$idst_u, 'waiting_user['.$idst_u.']', 'postpone', false, '')
						.''.Form::getLabel('waiting_user_postpone_'.$idst_u, $this->lang->def('_POSTPONE'), 'access-only')
				));

				if ( isset($_GET['id_user']) && $idst_u == $_GET['id_user']) {
					$field = new FieldList();
					$temp = array($idst);
					$idst_list = array_merge($temp, $acl->getUserGroupsST($idst_u));
					$tb->addBodyExpanded($field->playFieldsForUser( $idst_u, $idst_list, true ), 'user_specific_info');
				}
			}
		}

		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
								.': '.$groupLabel, 'directory_group' ), 'content' );

		$GLOBALS['page']->add( '<div class="std_block">', 'content');
		$GLOBALS['page']->add( Form::openForm( 	'directoryeditgroup',
								'index.php?modname=directory&amp;op=listgroup'),
								'content' );
		$GLOBALS['page']->add( Form::openElementSpace(), 'content' );
		$GLOBALS['page']->add( Form::getHidden('idst', 'idst', $idst), 'content' );
		$GLOBALS['page']->add( $tb->getTable(), 'content' );
		$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
		$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::getButton("editwaitsave", "editwaitsave", $this->lang->def( '_SAVE' )), 'content' );
		$GLOBALS['page']->add( Form::getButton("editgroupcancel", "editgroupcancel",$this->lang->def( '_CANCEL' )), 'content' );
		$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
		$GLOBALS['page']->add( Form::closeForm(), 'content' );
		$GLOBALS['page']->add( '</div>', 'content');
	}

	function membersGroup($groupid, $simple = FALSE) {
		if(isset($_POST['reset_filter']))
			unset($_POST['user_id']);
		require_once(_base_.'/lib/lib.form.php');
		if( $groupid === FALSE )
			return;

		if( $groupid != '' ) {
			$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
			if( $arrGroup !== FALSE ) {
				$idst = $arrGroup[0];
				$description = $arrGroup[2];
			}
		}
		if( $simple === FALSE ) {
			if( isset( $_POST['membersgroupadd'] ) )
				Util::jump_to( 'index.php?modname=directory&op=addtogroup&groupid='.$groupid );
			if( isset( $_POST['membersgroupcancel'] ) )
				Util::jump_to( 'index.php?modname=directory&op=listgroup' );
			$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
									.': '.$groupid, 'directory_group' ), 'content' );


			$GLOBALS['page']->add( '<div class="std_block">', 'content');
			$GLOBALS['page']->add( Form::getFormHeader( $this->lang->def( '_DIRECTORY_MEMBERSGROUP' ) ), 'content' );
			$GLOBALS['page']->add( Form::openForm( 	'directoryeditgroup',
									'index.php?modname=directory&amp;op=membersgroup&amp;groupid='.$groupid),
									'content' );
			
			$GLOBALS['page']->add( Form::openElementSpace(), 'content' );
			$GLOBALS['page']->add( Form::getTextField($this->lang->def( '_DIRECTORY_ITEMID' ), 'user_id', 'user_id', '255', (isset($_POST['user_id']) ? $_POST['user_id'] : '')), 'content' );
			$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
			
			$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
			$GLOBALS['page']->add( Form::getButton("membersGroup","membersGroup",$this->lang->def( '_FILTER' )), 'content' );
			$GLOBALS['page']->add( Form::getButton("reset_filter", "reset_filter",$this->lang->def( '_DEL' )), 'content' );
			$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
			
			$GLOBALS['page']->add( Form::openElementSpace(), 'content' );
		}
		$data = new GroupMembersDataRetriever($idst, $GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
		$rend = new Table(Get::sett('visuItem'));
		$lv = new GroupMembersListView($idst,'', $data, $rend, 'groupmembersdirectory');
		$lv->aclManager =& $this->aclManager;
		$lv->parsePositionData( $_POST );
		$GLOBALS['page']->add($lv->printOut(), 'content');
		$this->selected = $lv->printedItems;
		if( $simple === FALSE ) {

			$GLOBALS['page']->add( Form::closeElementSpace(), 'content' );
			$GLOBALS['page']->add( Form::getCloseFieldset(), 'content' );
			$GLOBALS['page']->add( Form::openButtonSpace(), 'content' );
			$GLOBALS['page']->add( Form::getButton("membersgroupadd","membersgroupadd",$this->lang->def( '_ADD' )), 'content' );
			$GLOBALS['page']->add( Form::getButton("membersgroupcancel","membersgroupcancel",$this->lang->def( '_CLOSE' )), 'content' );
			$GLOBALS['page']->add( Form::closeButtonSpace(), 'content' );
			$GLOBALS['page']->add( Form::closeForm(), 'content' );
			$GLOBALS['page']->add( '</div>', 'content');
		}
	}

	function addToGroup($groupid) {
		require_once(_base_.'/lib/lib.form.php');
		if( $groupid === FALSE ) {
			echo "groupid is FALSE";
			return;
		}
		if( isset($_POST['okselector']) ) {
			// aggiungere i selezionati al gruppo
			require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
			$orgDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
			$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
			if( $arrGroup !== FALSE ) {
				$idst = $arrGroup[0];
				$description = $arrGroup[2];
			}
			list( $arr_delta_selection, $arr_selection_alt) = $this->getAllSelection($_POST);
			$arr_selection = $orgDb->removeDescentants($arr_delta_selection);
			$arr_unselected = array_merge(	$this->getUnselected(),
											array_diff( $arr_delta_selection, $arr_selection) );

			foreach( $arr_unselected as $idstMember ) {
				$this->aclManager->removeFromGroup($idst, $idstMember );
			}
			foreach( $arr_selection as $idstMember )
				$this->aclManager->addToGroup($idst, $idstMember );

			$arr_unselected_alt = $this->getUnselectedAlt();

			foreach( $arr_unselected_alt as $idstMember ) {
				$this->aclManager->removeFromGroup($idst, $idstMember, FILTER_FOLD );
			}
			foreach( $arr_selection_alt as $idstMember )
				$this->aclManager->addToGroup($idst, $idstMember, FILTER_FOLD );
			Util::jump_to( 'index.php?modname=directory&op=listgroup' );
		} elseif( isset($_POST['cancelselector']) ) {
			Util::jump_to( 'index.php?modname=directory&op=listgroup' );
		} else {
			if( !isset($_GET['stayon']) ) {
				$idst = $this->aclManager->getGroupST($groupid);
				$this->resetSelection(	$this->aclManager->getGroupMembers($idst),
										$this->aclManager->getGroupMembers($idst, FILTER_FOLD));
			}
			$this->loadSelector('index.php?modname=directory&amp;op=addtogroup&amp;groupid='.$groupid.'&amp;stayon=1',
							$this->lang->def( '_DIRECTORY_ADDTOGROUP' ).' '.$groupid,
							$this->lang->def( '_DIRECTORY_ADDTOGROUPDESCR' ),
							TRUE );
		}
	}

	function importToGroup() {
		require_once(_base_.'/lib/lib.form.php');
		$form = new Form();


		$tree = getTitleArea( $this->lang->def( '_ORG_CHART_IMPORT_USERS', 'organization_chart' ), 'directory_group' )
			.'<div class="std_block">'

			.$form->getFormHeader( $this->lang->def( '_ASSIGN_USERS' ) )
			.$form->openForm( 	'directory_importgroupuser',
								'index.php?modname=directory&amp;op=import_groupuser_2',
								false,
								false,
								'multipart/form-data' );

		$tree .= $form->openElementSpace();

		$tree .= $form->getFilefield( $this->lang->def('_GROUP_USER_IMPORT_FILE'), 'file_import', 'file_import')
				.$form->getTextfield( $this->lang->def('_GROUP_USER_IMPORT_SEPARATOR'), 'import_separator', 'import_separator', 1, ',')
				.$form->getCheckbox( $this->lang->def('_GROUP_USER_IMPORT_HEADER'), 'import_first_row_header', 'import_first_row_header', 'true', TRUE )
				.$form->getTextfield( $this->lang->def('_GROUP_USER_IMPORT_CHARSET'), 'import_charset', 'import_charset', 20, 'ISO-8859-1');

		$tree .= $form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('import_groupuser_2', 'import_groupuser_2', $this->lang->def('_NEXT'))
				.$form->getButton('import_groupcancel', 'import_groupcancel', $this->lang->def('_UNDO'))
				.$form->closeButtonSpace();

		$tree .= Form::closeForm()
				.'</div>';
		$GLOBALS['page']->add($tree, 'content');
	}

	function importToGroup_step2() {

		require_once(_base_.'/lib/lib.upload.php');

		// ----------- file upload -----------------------------------------
		if($_FILES['file_import']['name'] == '') {
			$_SESSION['last_error'] = Lang::t('_FILEUNSPECIFIED');
			Util::jump_to( 'index.php?modname=directory&amp;op=listgroup&import_result=-1' );
		} else {
			$path = '/appCore/';
			$savefile = mt_rand(0,100).'_'.time().'_'.$_FILES['file_import']['name'];
			if(!file_exists( $GLOBALS['where_files_relative'].$path.$savefile )) {
				sl_open_fileoperations();
				if(!sl_upload($_FILES['file_import']['tmp_name'], $path.$savefile)) {

					sl_close_fileoperations();
					$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
					Util::jump_to( 'index.php?modname=directory&amp;op=listgroup&import_result=-1' );
				}
				sl_close_fileoperations();
			} else {
				$_SESSION['last_error'] = Lang::t('_ERROR_UPLOAD');
				Util::jump_to( 'index.php?modname=directory&amp;op=listgroup&import_result=-1' );
			}
		}

		require_once(_base_.'/lib/lib.form.php');
		$form = new Form();

		$tree = getTitleArea( $this->lang->def( '_ORG_CHART_IMPORT_USERS', 'organization_chart' ), 'directory_group' )
			.'<div class="std_block">'

			.$form->openForm( 	'directory_importgroupuser',
								'index.php?modname=directory&amp;op=import_groupuser_3',
								false,
								false,
								'multipart/form-data' );


		$tree .= $form->openElementSpace();


		require_once($GLOBALS['where_framework'].'/modules/org_chart/import.org_chart.php');
		$separator 			= importVar('import_separator', false, ',');
		$first_row_header 	= isset($_POST['import_first_row_header']) ? ($_POST['import_first_row_header']=='true') : FALSE;
		$import_charset 	=  importVar('import_charset', false, 'UTF-8');
		if(trim($import_charset) === '') $import_charset = 'UTF-8';

		$src = new DeceboImport_SourceCSV(array('filename' => $GLOBALS['where_files_relative'].$path.$savefile,
												'separator' => $separator,
												'first_row_header' => $first_row_header,
												'import_charset' => $import_charset )
										);
		$dst = new ImportGroupUser(array('dbconn'=>$GLOBALS['dbConn']));
		$src->connect();
		$dst->connect();
		$importer = new DoceboImport();
		$importer->setSource( $src );
		$importer->setDestination( $dst );

		$tree .= $importer->getUIMap();
		$tree .= $form->getHidden( 'filename',  'filename', $GLOBALS['where_files_relative'].$path.$savefile);
		$tree .= $form->getHidden( 'import_first_row_header', 'import_first_row_header', ($first_row_header?'true':'false'));
		$tree .= $form->getHidden( 'import_separator', 'import_separator', $separator);
		$tree .= $form->getHidden( 'import_charset', 'import_charset', $import_charset);

		$tree .= $form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('next_importusers_3', 'next_importusers_3', $this->lang->def('_NEXT'))
				.$form->getButton('import_groupcancel', 'import_groupcancel', $this->lang->def('_UNDO'))
				.$form->closeButtonSpace();

		$tree .= Form::closeForm()
				.'</div>';
		$GLOBALS['page']->add($tree, 'content');
	}

	function importToGroup_step3() {
		$back_url = 'index.php?modname=directory&op=listgroup';

		$filename = $_POST['filename'];
		$separator 			= importVar('import_separator', false, ',');
		$first_row_header 	= isset($_POST['import_first_row_header']) ? ($_POST['import_first_row_header']=='true') : FALSE;
		$import_charset 	= importVar('import_charset', false, 'UTF-8');
		if(trim($import_charset) === '') $import_charset = 'UTF-8';

		require_once($GLOBALS['where_framework'].'/modules/org_chart/import.org_chart.php');
		$src = new DeceboImport_SourceCSV(	array(	'filename'=>$filename,
													'separator'=>$separator,
													'first_row_header'=>$first_row_header,
													'import_charset' => $import_charset
											));
		$dst = new ImportGroupUser(array('dbconn'=>$GLOBALS['dbConn']));
		$src->connect();
		$dst->connect();
		$importer = new DoceboImport();
		$importer->setSource( $src );
		$importer->setDestination( $dst );

		$importer->parseMap();
		$result = $importer->doImport();

		$src->close();
		$dst->close();

		// print total processed rows
		$tree = getTitleArea( $this->lang->def( '_ORG_CHART_IMPORT_USERS', 'organization_chart' ), 'directory_group' )
			.'<div class="std_block">';
		$tree .= getBackUi($back_url, $this->lang->def('_BACK'));
		$tree .= getResultUi(str_replace("[count]", $result[0], $this->lang->def('_GROUP_ASSIGN_COUNT')) );

		if( count($result) > 1 ) {
			require_once(_base_.'/lib/lib.table.php');
			$tree .= $this->lang->def('_ERRORS').': <b>'.(count($result)-1).'</b><br/>';
			$table = new Table(Get::sett('visuItem'), $this->lang->def('_ERRORS'), $this->lang->def('_ERRORS'));
			$table->setColsStyle(array('',''));
			$table->addHead(array(	$this->lang->def('_ROW'),
									$this->lang->def('_DESCRIPTION')
							));

			foreach( $result as $key => $err_val ) {
				if( $key != 0 ) {
					$table->addBody(array($key,$err_val));
				}
			}
			$tree .= $table->getTable();
		}
		$tree .= getBackUi($back_url, $this->lang->def('_BACK'));
		$tree .= '</div>';
		$GLOBALS['page']->add($tree, 'content');
	}

	function &getOrgDb() {
		$org_db = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
		return $org_db;
	}

	function &getTreeView_OrgView() {
		require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
		$orgDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
		$treeView = new TreeView_OrgView($orgDb, 'organization_chart', Get::sett('title_organigram_chart'));
		$treeView->aclManager =& $this->aclManager;
		return $treeView;
	}

	function &getPeopleView() {
		$lv_data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
		$rend = new Table(Get::sett('visuUser'));
		$lv_view = new PeopleListView('', $lv_data, $rend, 'usersmembersdirectory');
		$lv_view->aclManager =& $this->aclManager;
		return $lv_view;
	}

	function loadOrgChartView() {

		require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
		$lang =& DoceboLanguage::createInstance('organization_chart', 'framework');
		$userlevelid = Docebo::user()->getUserLevelId();

		$repoDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');

		$treeView = new TreeView_OrgView($repoDb, 'organization_chart', Get::sett('title_organigram_chart'));
		$treeView->setLanguage( $lang );
		$treeView->aclManager =& $this->aclManager;

		if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
			require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
			$adminManager = new AdminManager();
			$treeView->setFilterNodes($adminManager->getAdminTree(Docebo::user()->getIdSt()));
		}

		$treeView->loadState();
		$treeView->parsePositionData($_POST, $_POST, $_POST);
		$treeView->selector_mode = $this->selector_mode;
		$treeView->simple_selector = $this->show_orgchart_simple_selector;

		$treeView->itemSelectedMulti = $this->selection;
		$treeView->itemSelectedMulti_alt = $this->selection_alt;
		$treeView->multi_choice = $this->multi_choice;
		$treeView->select_all = $this->select_all;
		$treeView->deselect_all = $this->deselect_all;

		$treeView->saveState();

		require_once(_base_.'/lib/lib.form.php');

		$GLOBALS['page']->add('<link href="'.getPathTemplate('framework').'/style/base-old-treeview.css" rel="stylesheet" type="text/css" />', 'page_head');
		$GLOBALS['page']->setWorkingZone('content');
		if( !$this->selector_mode ) {
			$GLOBALS['page']->add( getTitleArea($lang->def('_ORG_CHART'), 'org_chart' ) );
			$GLOBALS['page']->add( '<div class="std_block">' );
			$GLOBALS['page']->addEnd( '</div>' );
		}
		
		if( $treeView->op != '' ) {
			$processed = FALSE;
			switch( $treeView->op ) {
				case 'reedit_person':
					$processed = TRUE;
					$this->editPerson();
				break;
				case 'create_user':
					//$this->org_createUser($treeView->getSelectedFolderId());
					$processed = TRUE;
					Util::jump_to( 'index.php?modname=directory&op=org_createuser&treeid='.$treeView->getSelectedFolderId() );
				break;
				case 'addtotree':
					$processed = TRUE;
					Util::jump_to( 'index.php?modname=directory&op=addtotree&treeid='.$treeView->getSelectedFolderId() );
				break;
				case 'waiting_user':
					$processed = TRUE;
					Util::jump_to( 'index.php?modname=directory&op=org_waitinguser&treeid='.$treeView->getSelectedFolderId() );
				break;
			}
			if( !$this->selector_mode && !$processed ) {
				$GLOBALS['page']->add( Form::openForm('directory_org_chart', 'index.php?modname=directory&amp;op=org_chart', 'std_form', 'post', 'multipart/form-data') );
				$GLOBALS['page']->addEnd( Form::closeForm() );
			}
			switch( $treeView->op ) {
				case 'newfolder':
					$GLOBALS['page']->add($treeView->loadNewFolder());
				break;
				case 'deletefolder':
					$GLOBALS['page']->add($treeView->loadDeleteFolder());
				break;
				case 'renamefolder':
					$GLOBALS['page']->add($treeView->loadRenameFolder());
				break;
				case 'movefolder':
					$GLOBALS['page']->add($treeView->loadMoveFolder());
				break;
				case 'import_users':
					$GLOBALS['page']->add($treeView->loadImportUsers());
				break;
				case 'import_users2':
					$GLOBALS['page']->add($treeView->loadImportUsers2());
				break;
				case 'import_users3':
					$GLOBALS['page']->add($treeView->loadImportUsers3());
				break;
				case 'assign2_field':
					$GLOBALS['page']->add($treeView->loadAssignField2());
				break;
				case 'assign_field':
					$GLOBALS['page']->add($treeView->loadAssignField());
				break;
				case 'folder_field2':
					$GLOBALS['page']->add($treeView->loadFolderField2());
				break;
				case 'folder_field':
					$GLOBALS['page']->add($treeView->loadFolderField());
				break;
			}
		} else {
			if(!$this->selector_mode){

				//quick change password
				if(	checkPerm('edituser_org_chart', true, 'directory', 'framework')) {
					$GLOBALS['page']->add('<div class="align_right">'
						.'<a href="javascript:;" onclick="YAHOO.Animation.BlindToggle(\'quick_change\');">'.$lang->def('_CHANGEPASSWORD').'</a>'
						.'<div id="quick_change" style="display:none;">'
						.'<form method="post" action="index.php?modname=directory&amp;op=quick_change_password">'
						.'<div class="instruction_container">'
						.'<input type="hidden" id="authentic_request_chpwd" name="authentic_request" value="'.Util::getSignature().'" />'
						.'<label for="cp_userid">'.$lang->def('_USERNAME').' </label>'
						.'<input type="text" id="cp_userid" name="cp_userid" maxlenght="255" />&nbsp;&nbsp;'
						.'<label for="cp_pwd">'.$lang->def('_PASSWORD').' </label>'
						.'<input type="password" id="cp_pwd" name="cp_pwd" maxlenght="255" autocomplete="off" />&nbsp;&nbsp;'
						.'<input type="submit" id="cp_click" name="cp_click" value="'.$lang->def("_SAVE").'" /><br/>'

						.'<input type="checkbox" id="cp_force" name="cp_force" value="1" checked="checked" />'
						.' <label for="cp_force">'.$lang->def('_FORCE_CHANGE').'</label>'

						.'</div>'
						.'</form>'
						.'</div>'
						.'</div><br/>');
				}

				$treeView->lv_data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
				$rend = new Table(Get::sett('visuUser'));
				$treeView->lv_view = new PeopleListView('', $treeView->lv_data, $rend, 'usersmembersdirectory');
				$treeView->lv_view->hide_suspend = FALSE;
				$treeView->lv_view->setLinkPagination( "index.php?modname=directory&amp;op=org_chart" );
				$treeView->lv_view->aclManager =& $this->aclManager;
				$treeView->lv_view->parsePositionData( $_POST );

				if( $treeView->lv_view->getOp() == 'newitem' ) {
					$this->editPerson();
				} elseif( $treeView->lv_view->getOp() == 'editperson' ) {
					$this->editPerson($treeView->lv_view->getIdSelectedItem());
				} elseif( $treeView->lv_view->getOp() == 'deleteperson' ) {
					$this->deletePerson($treeView->lv_view->getIdSelectedItem());
				} else {
					if( $treeView->lv_view->getOp() == 'removeperson') {
						$idmember = $treeView->lv_view->getIdSelectedItem();
						$idmember_idst = $this->aclManager->getUserST($idmember);
						$id_org = $treeView->getSelectedFolderId();
						$id_org_idst = $treeView->tdb->getGroupST($id_org);
						$id_org_desc_idst = $treeView->tdb->getGroupDescendantsST($id_org);

						// echo "\nmember idst: $member_idst, org_idst: $id_org_idst, org_desc_idst: $id_org_desc_idst\n";
						$this->aclManager->removeFromGroup($id_org_idst, $idmember_idst);
						$this->aclManager->removeFromGroup($id_org_desc_idst, $idmember_idst);
						$treeView->lv_view->op='';
					}
					$GLOBALS['page']->add( Form::openForm('directory_org_chart', 'index.php?modname=directory&amp;op=org_chart') );
					$GLOBALS['page']->addEnd( Form::closeForm() );
					if( Get::sett('use_org_chart') == '1' ) {
						$GLOBALS['page']->add($treeView->load());
						$GLOBALS['page']->add( $treeView->loadActions() );
					}
					if( Get::sett('use_org_chart') == '1') {
						$id_org = $treeView->getSelectedFolderId();
						if( $id_org > 0 && $treeView->isFolderAccessible()) {
							if( $treeView->lv_view->flat_mode ) {
								$groupid = $treeView->tdb->getGroupDescendantsId($id_org);
							} else {
								$groupid = $treeView->tdb->getGroupId($id_org);
							}
						}
					} else {
						$id_org = 0;
					}
					if( $id_org > 0 && $treeView->isFolderAccessible() )
						$this->membersTree($groupid, $treeView);
					elseif($id_org == 0)
						$this->membersTree('', $treeView);
					if( Get::sett('use_org_chart') != '1' ) {
						$GLOBALS['page']->add( $treeView->loadActions() );
					}
				}
			} else {
				$GLOBALS['page']->add($treeView->load());
			}
		}
	}

	/**
	 * Print list of user in org_chart pages
	 **/
	function membersTree( $groupid, &$treeView ) {
		require_once(_base_.'/lib/lib.form.php');
		if (Get::sett('register_deleted_user') == 'on')
		{
			$lang =& DoceboLanguage::createInstance('profile', 'framework');
			$GLOBALS['page']->add('<br />'.'<a href="index.php?modname=directory&amp;op=view_deleted_user">'.$lang->def('_DELETED_USER_LIST').'</a>');

		}
		$data =& $treeView->lv_data;
		$lv =& $treeView->lv_view;
		$lv->show_flat_mode_flag = TRUE;
		if( $groupid === FALSE )
			return;
		if( $groupid != '' ) {
			$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
			if( $arrGroup !== FALSE ) {
				$idst = $arrGroup[0];
				$description = $arrGroup[2];
			}
		} else {
			$lv->show_flat_mode_flag = FALSE;
		}
		if( $lv->op == 'deleteperson' ) {
			$userid = $lv->getIdSelectedItem();
			$idst_user = $this->aclManager->getUserST( $userid );
			$id_org = $treeView->getSelectedFolderId();
			$idst_group = $treeView->tdb->getGroupST($id_org);
			$this->aclManager->removeFromGroup( $idst_group, $idst_user );
			$idst_group_desc = $treeView->tdb->getGroupDescendantsST($id_org);
			$this->aclManager->removeFromGroup( $idst_group_desc, $idst_user );
		} elseif( $lv->op == 'suspendperson' ) {
			$userid = $lv->getIdSelectedItem();
			$idst_user = $this->aclManager->getUserST( $userid );
			$this->aclManager->suspendUser( $idst_user );
			$GLOBALS['page']->add(getResultUi($this->lang->def('_SUSPENDED') ));
		} elseif($lv->op == 'recoverperson' ) {
			$userid = $lv->getIdSelectedItem();
			$idst_user = $this->aclManager->getUserST( $userid );
			$this->aclManager->recoverUser( $idst_user );
			$GLOBALS['page']->add(getResultUi($this->lang->def('_REACTIVATED_USER') ));
		}
		if( $groupid != '' )
			$data->setGroupFilter($idst, $lv->flat_mode );
		$userlevelid = Docebo::user()->getUserLevelId();
		if( $userlevelid != ADMIN_GROUP_GODADMIN ) {
			require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
			$adminManager = new AdminManager();
			$data->intersectGroupFilter($adminManager->getAdminTree(Docebo::user()->getIdSt()));
		}
		$GLOBALS['page']->add($lv->printOut(), 'content');
		//$this->selected = $lv->printedItems;
	}

	/**
	 *
	 **/
	function addToTree($treeid) {
		require_once(_base_.'/lib/lib.form.php');
		if( $treeid === FALSE )
			return;

		require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
		$repoDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');

		if( isset($_POST['okselector']) ) {
			// aggiungere i selezionati al gruppo
			$idst = $repoDb->getGroupST($treeid);
			$idst_desc = $repoDb->getGroupDescendantsST($treeid);
			$arr_selection = $this->getSelection($_POST);
			$arr_unselected = $this->getUnselected();
			foreach( $arr_unselected as $idstMember ) {
				$this->aclManager->removeFromGroup($idst, $idstMember );
				$this->aclManager->removeFromGroup($idst_desc, $idstMember );
			}
			foreach( $arr_selection as $idstMember ) {
				$this->aclManager->addToGroup($idst, $idstMember );
				$this->aclManager->addToGroup($idst_desc, $idstMember );
			}
			Util::jump_to( 'index.php?modname=directory&op=org_chart' );
		} elseif( isset($_POST['cancelselector']) ) {
			Util::jump_to( 'index.php?modname=directory&op=org_chart' );
		} else {
			if( !isset($_GET['stayon']) ) {
				$idst = $repoDb->getGroupST($treeid);
				$this->resetSelection($this->aclManager->getGroupUMembers($idst));
			}
			$arr_translations = $repoDb->getFolderTranslations($treeid);
			$this->show_group_selector = FALSE;
			$this->show_orgchart_selector = FALSE;
			$this->hide_suspend = FALSE;
			$this->loadSelector('index.php?modname=directory&amp;op=addtotree&amp;treeid='.$treeid.'&amp;stayon=1',
							$this->lang->def( '_ADD' ).' '.$arr_translations[getLanguage()],
							$this->lang->def( '_ADD' ),
							TRUE);
		}
	}

	function org_createUser( $treeid = FALSE ) {
		checkPerm('createuser_org_chart', false, 'directory', 'framework');
		require_once(_base_.'/lib/lib.form.php');

		$title_page = array(
			$this->lang->def('_USERS'),
			$this->lang->def('_NEW_USER')
		);
		
		$control_view = 1;
		
		$data = new GroupDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
		$rend = new Table(Get::sett('visuItem'));
		$lv = new GroupListView('', $data, $rend, 'groupdirectory');
		
		$group_count = $lv->getTotalRows();
		
		$query_org_chart = "SELECT COUNT(*)"
							." FROM ".$GLOBALS['prefix_fw']."_org_chart_tree";
		
		list($number_of_folder) = sql_fetch_row(sql_query($query_org_chart));
		
		if ($number_of_folder == 0 && $group_count == 0)
			$control_view = 0;
		
		$GLOBALS['page']->add(getTitleArea($title_page, 'directory_people')
			.'<div class="std_block">');

		if($control_view && (Get::sett('use_org_chart') == '1' || $GLOBALS['use_groups'] == '1') ) {
			if( isset($_POST['okselector']) ) {
				// go to user creation with folders selected
				require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
				$repoDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');

				$arr_selection = $this->getSelection($_POST);
				if( count($arr_selection) > 0 )
					$arr_selection = array_merge( $arr_selection, $repoDb->getDescendantsSTFromST($arr_selection));
				$arr_selection = array_merge( 	$arr_selection,
												$this->aclManager->getArrGroupST( array('/oc_0', '/ocd_0') ) );
				$this->editPerson( FALSE, $arr_selection );
			} elseif( isset($_POST['cancelselector']) ) {
				Util::jump_to( 'index.php?modname=directory&op=org_chart' );
			} else {
				if( !isset($_GET['stayon']) ) {
					if( $treeid === FALSE && isset($_GET['treeid']) )
						$treeid = (int)$_GET['treeid'];
					if( $treeid != 0 ) {
						require_once(dirname(__FILE__).'/../modules/org_chart/tree.org_chart.php');
						$repoDb = new TreeDb_OrgDb($GLOBALS['prefix_fw'].'_org_chart_tree');
						$idst = $repoDb->getGroupST($treeid);
						$this->resetSelection(array($idst));
					} else {
						$this->resetSelection(array());
					}

				}
				$this->show_user_selector = FALSE;
				
				if ($group_count == 0)
					$this->show_group_selector = FALSE;
				else
					$this->show_group_selector = TRUE;
				
				if( Get::sett('use_org_chart') == '1' && $number_of_folder != 0) {
					$this->show_orgchart_selector = TRUE;
					$this->show_orgchart_simple_selector = TRUE;
				} else {
					$this->show_orgchart_selector = FALSE;
				}
				
				if(Docebo::user()->getUserLevelId() === '/framework/level/admin')
				{
					require_once($GLOBALS['where_framework'].'/lib/lib.adminmanager.php');
					
					$adminManager = new AdminManager();
					
					$this->setGroupFilter('group', $adminManager->getAdminTree(getLogUserId()));
				}
				
				$this->loadSelector('index.php?modname=directory&amp;op=org_createuser&amp;stayon=1',
								$this->lang->def( '_NEW_USER' ),
								$this->lang->def( '_NEW_USERDESCR' ),
								TRUE);
			}
		} else {
			$this->editPerson( FALSE, array() );
		}

		$GLOBALS['page']->add('</div>');
	}

	function org_waitingUser() {
		checkPerm('approve_waiting_user', false, 'directory', 'framework');

		require_once(_base_.'/lib/lib.form.php');
		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		require_once(_base_.'/lib/lib.table.php');
		require_once(Forma::inc(_base_ . '/lib/lib.usermanager.php'));

		if( isset($_POST['ok_waiting']) ) {

			$user_man = new UserManager();
			// Remove refused users
			$refused = array();
			$aopproved = array();
			if(isset($_POST['waiting_user_refuse'])) {

				while(list($idst) = each($_POST['waiting_user_refuse'])) {

					$this->aclManager->deleteTempUser( $idst , false, false, true );
				}
				$refused[] = $idst;
			}
			// Subscribed accepted users
			if(isset($_POST['waiting_user_accept'])) {

				$idst_usergroup 	= $this->aclManager->getGroup(false, ADMIN_GROUP_USER);
				$idst_usergroup 	= $idst_usergroup[ACL_INFO_IDST];

				$idst_oc 			= $this->aclManager->getGroup(false, '/oc_0');
				$idst_oc 			= $idst_oc[ACL_INFO_IDST];

				$idst_ocd 			= $this->aclManager->getGroup(false, '/ocd_0');
				$idst_ocd 			= $idst_ocd[ACL_INFO_IDST];

				$request = $this->aclManager->getTempUsers(false, true);

				while(list($idst) = each($_POST['waiting_user_accept'])) {

					if($this->aclManager->registerUser(addslashes($request[$idst]['userid']),
						addslashes($request[$idst]['firstname']),
						addslashes($request[$idst]['lastname']),
						$request[$idst]['pass'],
						addslashes($request[$idst]['email']),
						'',
						'',
						true,
						$idst )) {

						$approved[] = $idst;

						$this->aclManager->addToGroup($idst_usergroup, $idst);
						$this->aclManager->addToGroup($idst_oc, $idst);
						$this->aclManager->addToGroup($idst_ocd, $idst);

						if($request[$idst]['create_by_admin'] != 0) {

							$pref = new UserPreferences($request[$idst]['create_by_admin']);
							if($pref->getAdminPreference('admin_rules.limit_user_insert') == 'on') {

								$max_insert = $pref->getAdminPreference('admin_rules.max_user_insert');
								$pref->setPreference('admin_rules.max_user_insert', $max_insert -1 );
							}
						}
						$this->aclManager->deleteTempUser( $idst , false, false, false );
					}
				}
			}

			require_once(_base_.'/lib/lib.platform.php');
			require_once(_base_.'/lib/lib.eventmanager.php');
			// send the alert
			/*
			if(!empty($refused)) {

				$array_subst = array('[url]' => Get::sett('url'));

				$msg_composer = new EventMessageComposer('admin_directory', 'framework');

				$msg_composer->setSubjectLangText('email', '_REFUSED_USER_SBJ', false);
				$msg_composer->setBodyLangText('email', '_REFUSED_USER_TEXT', $array_subst);

				$msg_composer->setBodyLangText('sms', '_REFUSED_USER_TEXT_SMS', $array_subst);

				createNewAlert(	'UserApproved', 'directory', 'edit', '1', 'Users refused',
							$refused, $msg_composer );
			}*/
			if(!empty($approved)) {
				$pl_man =& PlatformManager::createInstance();
				$array_subst = array('[url]' => Get::sett('url', ''));

				$msg_composer2 = new EventMessageComposer('admin_directory', 'framework');

				$msg_composer2->setSubjectLangText('email', '_APPROVED_USER_SBJ', false);
				$msg_composer2->setBodyLangText('email', '_APPROVED_USER_TEXT', $array_subst);

				$msg_composer2->setBodyLangText('sms', '_APPROVED_USER_TEXT_SMS', $array_subst);

				createNewAlert(	'UserApproved', 'directory', 'edit', '1', 'Users approved',
							$approved, $msg_composer2, true );
			}

			Util::jump_to( 'index.php?modname=directory&op=org_chart' );
		} elseif( isset($_POST['cancel_waiting']) ) {

			Util::jump_to( 'index.php?modname=directory&op=org_chart' );
		} else {

			$tb = new Table(0,
				$this->lang->def('_WAITING_USERS'),
				$this->lang->def('_WAITING_USER_SUMMARY'));

			$type_h = array('', '', '', 'image', 'image');
			$cont_h = array(
				$this->lang->def('_USERNAME'),
				$this->lang->def('_DIRECTORY_FULLNAME'),
				$this->lang->def('_BY'),
				'<img src="'.getPathImage('framework').'directory/wuser_accept.gif" alt="'.$this->lang->def('_ACCEPT').'" '
					.'title="'.$this->lang->def('_ACCEPT_USER').'" />',
				'<img src="'.getPathImage('framework').'directory/wuser_refuse.gif" alt="'.$this->lang->def('_REFUSE_USER').'" '
					.'title="'.$this->lang->def('_REFUSE_USER_TITLE').'" />'
			);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

			$temp_users = $this->aclManager->getTempUsers(false, true);

			if($temp_users !== false) {
				$idst_admins = array();
				while(list($idst, $info) = each($temp_users)) {

					if($info['create_by_admin'] != 0) {
						$idst_admins[] = $info['create_by_admin'];
					}
				}
				$admins = $this->aclManager->getUsers($idst_admins);

				reset($temp_users);
				while(list($idst, $info) = each($temp_users)) {

					if($info['create_by_admin'] != 0) {
						$creator = $admins[$info['create_by_admin']][ACL_INFO_LASTNAME].' '
									.$admins[$info['create_by_admin']][ACL_INFO_FIRSTNAME];
						if($creator == '') {
							$creator = $this->aclManager->relativeId($admins[$info['create_by_admin']][ACL_INFO_USERID]);
						}
					} else {
						$creator = $this->lang->def('_DIRECOTRY_SELFREGISTERED');
					}
					$more = ( isset($_GET['id_user']) && $_GET['id_user'] == $idst
						? '<a href="index.php?modname=directory&amp;op=org_waitinguser"><img src="'.getPathImage().'standard/less.gif"></a> '
						: '<a href="index.php?modname=directory&amp;op=org_waitinguser&amp;id_user='.$idst.'"><img src="'.getPathImage().'standard/more.gif"></a> ');

					$cont = array(
						$more.$this->aclManager->relativeId($info['userid']),
						$info['lastname'].' '.$info['firstname'],
						$creator,
						Form::getInputCheckbox('waiting_user_accept_'.$idst,
												'waiting_user_accept['.$idst.']',
												$idst, false, '' )
						.Form::getLabel('waiting_user_accept_'.$idst, $this->lang->def('_ACCEPT'), 'access-only'),
						Form::getInputCheckbox('waiting_user_refuse_'.$idst,
												'waiting_user_refuse['.$idst.']',
												$idst, false, '' )
							.Form::getLabel('waiting_user_refuse_'.$idst, $this->lang->def('_REFUSE_USER'), 'access-only')
					);
					$tb->addBody($cont);

					if ( isset($_GET['id_user']) && $idst == $_GET['id_user']) {
						$field = new FieldList();
						$tb->addBodyExpanded($field->playFieldsForUser( $idst, false, true ), 'user_specific_info');
					}
				}
			}
			$GLOBALS['page']->add(
				getTitleArea($this->lang->def('_WAITING_USERS'),'directory')
				.'<div class="std_block">'
				.Form::openForm('waiting_user', 'index.php?modname=directory&amp;op=org_waitinguser')
				.$tb->getTable()
				.Form::openButtonSpace()
				.Form::getButton('ok_waiting', 'ok_waiting', $this->lang->def('_SAVE'))
				.Form::getButton('cancel_waiting', 'cancel_waiting', $this->lang->def('_UNDO'))
				.Form::closeButtonSpace()
				.Form::closeForm()
				.'</div>', 'content' );
		}
	}


	// Function for permission managment
	function getAllToken($op) {

		switch($op) {
			case "org_chart" : {
				return array(
					'view' => array( 'code' => 'view_org_chart',
												'name' => '_VIEW_ORG_CHART',
												'image' => 'standard/view.png'),
					'add' => array( 	'code' => 'createuser_org_chart',
												'name' => '_NEW_USER',
												'image' => 'standard/add.png'),
					'mod' => array( 	'code' => 'edituser_org_chart',
												'name' => '_MOD',
												'image' => 'standard/edit.png'),
					'del' => array( 	'code' => 'deluser_org_chart',
												'name' => '_DELUSER_ORG_CHART',
												'image' => 'standard/delete.png'),
					'moderate' => array( 	'code' => 'approve_waiting_user',
												'name' => '_MODERATE',
												'image' => 'org_chart/waiting_identity.png')
				);
			};break;
			case "listgroup" : {
				return array(
					'view' => array( 	'code' => 'view_group',
										'name' => '_VIEW',
										'image' => 'standard/view.png'),
					'add' => array( 	'code' => 'creategroup',
										'name' => '_ADD',
										'image' => 'standard/add.png'),
					'mod' => array( 	'code' => 'editgroup',
										'name' => '_MOD',
										'image' => 'standard/edit.png'),
					'del' => array( 	'code' => 'delgroup',
										'name' => '_DEL',
										'image' => 'standard/delete.png'),
					'associate' => array( 	'code' => 'associate_group',
										'name' => '_ASSOCIATEUSERTOGROUP',
										'image' => 'directory/addto.gif')
				);
			};break;
		}
	}

	/**
	 * Assign fields for group
	 **/
	function loadAssignField( $groupid ) {
		if( isset( $_POST[DIRECTORY_ID] ) ) {
			if( isset( $_POST[DIRECTORY_ID]['save_assignfield'] ) ) {
				return $this->loadAssignField2( $groupid );
			} elseif( isset( $_POST[DIRECTORY_ID]['cancel_assignfield'] ) ) {
				Util::jump_to( 'index.php?modname=directory&op=listgroup' );
			}
		}

		$arrGroup = $this->aclManager->getGroup( FALSE, $groupid );
		if( $arrGroup !== FALSE ) {
			$idst = $arrGroup[0];
			$description = $arrGroup[2];
		}

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');
		require_once(_base_.'/lib/lib.form.php');

		$form = new Form();
		$fl = new FieldList();
		$acl =& Docebo::user()->getACL();

		$GLOBALS['page']->setWorkingZone('content');
		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
								.': '.$groupid, 'directory_group' ) );

		$GLOBALS['page']->add( '<div class="std_block">');
		$GLOBALS['page']->add( $form->getFormHeader( $this->lang->def( '_DIRECTORY_ASSIGNFIELDGROUP' ) ));
		$GLOBALS['page']->add( $form->openForm( 	'directoryassignfieldgroup',
								'index.php?modname=directory&amp;op=assignfield&amp;groupid='.$groupid)
							);
		$GLOBALS['page']->add( $form->openElementSpace());
			//$GLOBALS['page']->add( $this->printState());

		$GLOBALS['page']->add( $form->getHidden(	DIRECTORY_ID.'_idst_group',
									DIRECTORY_ID.'[idst_group]',
									$idst) );

		$arr_all_fields = $fl->getAllFields();
		$arr_fields_normal = $fl->getFieldsFromIdst(array($idst));
		$arr_fields_inherit = $fl->getFieldsFromIdst($acl->getGroupGroupsST($idst));
		$arr_values = array(
						$this->lang->def('_NO') => GROUP_FIELD_NO,
						$this->lang->def('_GROUP_FIELD_NORMAL') => GROUP_FIELD_NORMAL,
					);
		foreach($arr_all_fields as $field ) {
			$def_value = GROUP_FIELD_NO;
			if( isset($arr_fields_normal[$field[FIELD_INFO_ID]]) )
				$def_value = GROUP_FIELD_NORMAL;
			elseif( isset($arr_fields_inherit[$field[FIELD_INFO_ID]]) )
				$def_value = GROUP_FIELD_INHERIT;

			$GLOBALS['page']->add( $form->openFormLine() );
			$GLOBALS['page']->add( '<div class="label_effect">'.$field[FIELD_INFO_TRANSLATION].'</div>' );
			if( $def_value == GROUP_FIELD_INHERIT ) {
				$GLOBALS['page']->add( '<div class="floating">'.$this->lang->def('GROUP_FIELD_INHERIT').'</div>' );
			} else {
				foreach( $arr_values as $label => $value ) {
					$GLOBALS['page']->add(
							'<input class="radio" type="radio"'
							.' id="'.DIRECTORY_ID.'_'.$field[FIELD_INFO_ID].'_'.$value.'"'
							.' name="'.DIRECTORY_ID.'['.DIRECTORY_OP_ADDFIELD.']['.$field[FIELD_INFO_ID].']"'
							.' value="'.$value.'"'
							.( ($value == $def_value)? ' checked="checked"' : '' )
							.' />' );
					$GLOBALS['page']->add( $form->getLabel( DIRECTORY_ID.'_'.$field[FIELD_INFO_ID].'_'.$value, $label, 'label_bold' ) );
				}
			}
				//$tree .= $form->getLabel( $id, $field[FIELD_INFO_TRANSLATION] );

			$GLOBALS['page']->add( $form->closeFormLine() );
		}

		$GLOBALS['page']->add( $form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('save_assignfield'.DIRECTORY_ID, DIRECTORY_ID.'[save_assignfield]', $this->lang->def('_NEXT'))
				.$form->getButton('cancel_assignfield'.DIRECTORY_ID, DIRECTORY_ID.'[cancel_assignfield]', $this->lang->def('_UNDO'))
				.$form->closeButtonSpace() );
	}

	/**
	 * Assign fields mandatory and user for group
	 **/
	function loadAssignField2( $groupid ) {
		$arr_fields = $_POST[DIRECTORY_ID][DIRECTORY_OP_ADDFIELD];
		$idst_group = $_POST[DIRECTORY_ID]['idst_group'];

		require_once($GLOBALS['where_framework'].'/lib/lib.field.php');

		if( isset( $_POST[DIRECTORY_ID]['save_assignfield2'] ) ) {
			$fl = new FieldList();
			$arr_fields_mandatory = $_POST[DIRECTORY_ID]['field_mandatory'];
			$arr_fields_useraccess = $_POST[DIRECTORY_ID]['field_useraccess'];

			foreach( $arr_fields as $id_filed => $status ) {
				switch( $status ) {
					case GROUP_FIELD_NO:
						$fl->removeFieldFromGroup(
								$id_filed,
								$idst_group
								);
					break;
					case GROUP_FIELD_NORMAL:
						$fl->addFieldToGroup(
								$id_filed,
								$idst_group,
								isset($arr_fields_mandatory[$id_filed])?$arr_fields_mandatory[$id_filed]:'false',
								isset($arr_fields_useraccess[$id_filed])?$arr_fields_useraccess[$id_filed]:'readonly'
								);
					break;
				}
			}
			Util::jump_to( 'index.php?modname=directory&op=listgroup' );
		} elseif( isset( $_POST[DIRECTORY_ID]['cancel_assignfield'] ) ) {
			Util::jump_to( 'index.php?modname=directory&op=listgroup' );
		}

		$fl = new FieldList();
		$arr_all_fields = $fl->getAllFields();
		require_once(_base_.'/lib/lib.form.php');
		$form = new Form();

		$GLOBALS['page']->setWorkingZone('content');
		$GLOBALS['page']->add( getTitleArea($this->lang->def( '_GROUPS' )
								.': '.$groupid, 'directory_group' ) );

		$GLOBALS['page']->add( '<div class="std_block">');
		$GLOBALS['page']->add( $form->openForm( 	'directoryassignfieldgroupmandatory',
								'index.php?modname=directory&amp;op=assignfieldmandatory')
							);

		$GLOBALS['page']->add( $form->openElementSpace() );

		// print custom fields status
		$arr_fields_normal = $fl->getFieldsFromIdst(array($idst_group));

		$GLOBALS['page']->add( $form->getHidden(	DIRECTORY_ID.'_idst_group',
										DIRECTORY_ID.'[idst_group]',
										$idst_group)
							);

		foreach( $arr_fields as $id_filed => $status ) {
			$GLOBALS['page']->add( $form->getHidden(	DIRECTORY_ID.'_'.$id_filed,
										DIRECTORY_ID.'['.DIRECTORY_OP_ADDFIELD.']['.$id_filed.']',
										$status)
								);
		}

		$GLOBALS['page']->add(
				$form->openFormLine()
				.'<div class="label_effect">&nbsp;</div>'
				.'<div class="label_head">'.$this->lang->def('_MANDATORY').'</div>'
				.'<div class="label_head">'.$this->lang->def('_DIRECTORY_GROUP_FIELD_WRITE').'</div>'
				.$form->closeFormLine()
			);
		// checkbox for mandatory and useraccess
		foreach( $arr_fields as $id_filed => $status ) {
			if( $status == GROUP_FIELD_NORMAL ) {
				$GLOBALS['page']->add(
						$form->openFormLine()
						// field title
						.'<div class="label_effect">'.$arr_all_fields[$id_filed][FIELD_INFO_TRANSLATION].'</div>'
						// checkbox for mandatory
						.'<input class="label_head" type="checkbox"'
							.' id="'.DIRECTORY_ID.'_'.$id_filed.'_mandatory"'
							.' name="'.DIRECTORY_ID.'[field_mandatory]['.$id_filed.']"'
							.' value="true"'
					);

				if( isset( $arr_fields_normal[$id_filed] ) && $arr_fields_normal[$id_filed][FIELD_INFO_MANDATORY] == 'true' )
					$GLOBALS['page']->add( ' checked="checked"' );
				$GLOBALS['page']->add( ' />' );
				$GLOBALS['page']->add( $form->getLabel( DIRECTORY_ID.'_'.$id_filed.'_mandatory', $this->lang->def('_MANDATORY'), 'label_bold access-only' ) );
				// checkbox for useraccess
				$GLOBALS['page']->add(
						'<input class="label_head" type="checkbox"'
							.' id="'.DIRECTORY_ID.'_'.$id_filed.'_useraccess"'
							.' name="'.DIRECTORY_ID.'[field_useraccess]['.$id_filed.']"'
							.' value="readwrite"'
					);
				if( isset( $arr_fields_normal[$id_filed] ) && $arr_fields_normal[$id_filed][FIELD_INFO_USERACCESS] == 'readwrite' )
					$GLOBALS['page']->add( ' checked="checked"' );
				$GLOBALS['page']->add( ' />' );
				$GLOBALS['page']->add( $form->getLabel( DIRECTORY_ID.'_'.$id_filed.'_useraccess', $this->lang->def('_DIRECTORY_GROUP_FIELD_WRITE'), 'label_bold access-only' ) );
				$GLOBALS['page']->add( $form->closeFormLine() );

			}
		}


		$GLOBALS['page']->add(
				$form->closeElementSpace()
				.$form->openButtonSpace()
				.$form->getButton('save_assignfield2'.DIRECTORY_ID, DIRECTORY_ID.'[save_assignfield2]', $this->lang->def('_SAVE'))
				.$form->getButton('cancel_assignfield'.DIRECTORY_ID, DIRECTORY_ID.'[cancel_assignfield]', $this->lang->def('_UNDO'))
				.$form->closeButtonSpace()
			);


	}

	function getUsersStats($stats_required = false, $arr_users = false) {

		$users = array();
		if($stats_required == false || empty($stats_required) || !is_array($stats_required)) {
			$stats_required = array('all', 'suspended', 'register_today', 'register_yesterday', 'register_7d',
				'now_online', 'inactive_30d', 'waiting', 'superadmin', 'admin', 'public_admin');
		}
		$stats_required = array_flip($stats_required);

		if(isset($stats_required['all'])) {
			$data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);
			$users['all'] 	= $data->getTotalRows();
		}
		if(isset($stats_required['suspended'])) {
			$data->addFieldFilter('valid', 0);
			$users['suspended'] = $data->getTotalRows();
			$users['suspended']--; // one is anonymous
		}
		if(isset($stats_required['register_today'])) {
			$data->resetFieldFilter();
			$data->addFieldFilter('register_date', date("Y-m-d").' 00:00:00', '>');
			$users['register_today'] = $data->getTotalRows();
		}
		if(isset($stats_required['register_yesterday'])) {
			$data->resetFieldFilter();
			$yesterday = date("Y-m-d", time() - 86400);
			$data->addFieldFilter('register_date', $yesterday.' 00:00:00', '>');
			$data->addFieldFilter('register_date', $yesterday.' 23:59:59', '<');
			$users['register_yesterday'] = $data->getTotalRows();
		}
		if(isset($stats_required['register_7d'])) {
			$data->resetFieldFilter();
			$sevendaysago = date("Y-m-d", time() - (7 * 86400));
			$data->addFieldFilter('register_date', $sevendaysago.' 00:00:00', '>');
			$users['register_7d'] = $data->getTotalRows();
		}
		if(isset($stats_required['now_online'])) {
			$data->resetFieldFilter();
			$data->addFieldFilter('lastenter', date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER), '>');
			$users['now_online'] = $data->getTotalRows();
			if (($arr_users !== false) && (is_array($arr_users)) && (count($arr_users) > 0)) {
				$data->setUserFilter($arr_users);
				$users['now_online_filtered'] = $data->getTotalRows();
			}
			else {
				$users['now_online_filtered'] =0;
			}
		}
		if(isset($stats_required['inactive_30d'])) {
			$data->resetFieldFilter();
			$data->addFieldFilter('lastenter', date("Y-m-d", time() - 30 * 86400).' 00:00:00', '<');
			$users['inactive_30d'] = $data->getTotalRows();
		}
		if(isset($stats_required['waiting'])) {
			$users['waiting'] 	= $this->aclManager->getTempUserNumber();
		}
		if(isset($stats_required['superadmin'])) {
			$idst_sadmin = $this->aclManager->getGroupST(ADMIN_GROUP_GODADMIN);
			$users['superadmin'] 	= $this->aclManager->getGroupUMembersNumber($idst_sadmin);
		}
		if(isset($stats_required['admin'])) {
			$idst_admin = $this->aclManager->getGroupST(ADMIN_GROUP_ADMIN);
			$users['admin'] 		= $this->aclManager->getGroupUMembersNumber($idst_admin);
		}
		return $users;
	}

}

?>
