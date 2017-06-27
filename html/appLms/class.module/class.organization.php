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

class Module_Organization extends LmsModule {
	var $treeView = NULL;
	var $repoDb = NULL;
	var $select_destination = FALSE;

	//class constructor
	function Module_Organization($module_name = '') {
		parent::LmsModule('organization');
	}

	function loadHeader() {
		//EFFECTS: write in standard output extra header information
		global $op;
		$GLOBALS['page']->setWorkingZone( 'page_head' );
		$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />');
		/*$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_organizations.css" rel="stylesheet" type="text/css" />');*/
		return;
	}

	/**
	 *
	**/
	function initialize() {
		require_once($GLOBALS['where_lms'].'/modules/'.$this->module_name.'/'.$this->module_name.'.php');
		$ready = FALSE;
		$this->lang =& DoceboLanguage::createInstance('organization', 'lms');

		if( isset($_GET['sor']) && FALSE ) {
			// reload from previously saved session
			require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
			$saveObj = new Session_Save();
			$saveName = $_GET['sor'];
			if( $saveObj->nameExists($saveName) ) {
				$this->treeView =& $saveObj->load($saveName);
				$this->repoDb =& $this->treeView->tdb;
				$ready = TRUE;
				$saveObj->delete( $saveName );
				$this->treeView->extendedParsing( $_REQUEST, $_REQUEST, $_REQUEST);
				$this->treeView->refreshTree();
			}
		}

		if( !$ready ) {
			// contruct and initialize TreeView to manage public repository
			$id_course = ( isset($_GET['courseid']) ? $_GET['courseid'] : $_SESSION['idCourse'] );
			$this->repoDb = new OrgDirDb($id_course);

			/* TODO: ACL */
			if(!checkPerm('lesson', TRUE, 'storage') ) {
				$this->repoDb->setFilterVisibility( TRUE );
				$this->repoDb->setFilterAccess( Docebo::user()->getArrSt() );
			}
			//
			$this->treeView = new Org_TreeView($this->repoDb, 'organization', $this->lang->def('_ORGROOTNAME', 'organization'));
			$this->treeView->mod_name = 'organization';
			$this->treeView->setLanguage( $this->lang );

			require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
			$saveObj = new Session_Save();
			$saveName = 'organization'.$id_course;
			if( $saveObj->nameExists($saveName) ) {

				$this->treeView->setState($saveObj->load($saveName));
				$ready = TRUE;
				$saveObj->delete( $saveName );
				//$this->treeView->extendedParsing( $_POST, $_POST, $_POST);
				$this->treeView->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
				$this->treeView->refreshTree();
			} else {

				//$this->treeView->extendedParsing( $_POST, $_POST, $_POST);
				$this->treeView->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
			}
		}
		if( $this->select_destination ) {
			$this->treeView->setOption(REPOOPTSHOWONLYFOLDER, TRUE);
		}

	}

	function isSuperActive() {
		if( $this->treeView === NULL )
			$this->initialize();
		if( $this->treeView->op == 'movefolder' )
			return TRUE;
		return FALSE;
	}

	function isFindingDestination() {
		return ($this->treeView->op == 'copyLOSel');
	}

	function getUrlParams() {
		if( $this->isFindingDestination() )
			return '&amp;crepo='.$_GET['crepo'].'&amp;'
					.$this->treeView->_getOpCopyLOSel().'=1"';
		return '';
	}

	function hideTab() {
		switch($this->treeView->op) {
			case 'createLO':
			case 'createLOSel':
			case 'editLO':
			case 'playitem':
			case 'org_categorize':
			case 'org_opcategorize':
			case 'org_select_sco':
			case 'org_categorize_sco':
			case 'org_opproperties':
			case 'org_properties':
			case 'org_opaccess':
			case 'org_access':
				return TRUE;
		}

		$op =Get::req('op', DOTY_STRING, '');
		switch($op) {
			case 'org_select_sco':
			case 'org_categorize_sco':
				return TRUE;
		}

		return FALSE;
	}

	function getExtraTop() {
		global $modname;
		if( $this->isFindingDestination() ) {
			require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
			$saveObj = new Session_Save();
			$saveName = $_GET['crepo'];
			if( $saveObj->nameExists($saveName) ) {
				$saveData =& $saveObj->load($saveName);
				return 	'<div class="std_block">'
						.'<form id="homereposhow" method="post"'
						.' action="index.php?modname='.$modname
						.'&amp;op=display&amp;crepo='.$_GET['crepo'].'&amp;'
						.$this->treeView->_getOpCopyLOSel().'=1"'
						.' >'."\n"
						.'<input type="hidden" id="authentic_request_org" name="authentic_request" value="'.Util::getSignature().'" />'
						.$this->lang->def('_REPOSELECTDESTINATION')
						.' <img src="'.getPathImage().'lobject/'.$saveData['objectType']
						.'.gif" alt="'.$saveData['objectType']
						.'" title="'.$saveData['objectType'].'"/>'
						.$saveData['name'];
			}
		}
		return "";
	}

	function getExtraBottom() {
		global $modname;
		if( $this->isFindingDestination() ) {
			return 	'<img src="'.$this->treeView->_getCopyImage().'" alt="'.$this->lang->def('_REPOPASTELO').'" /> '
					.'<input type="submit" value="'.$this->lang->def('_REPOPASTELO').'" class="LVAction"'
					.' name="'.$this->treeView->_getOpCopyLOEndOk().'" />'
					.' <img src="'.$this->treeView->_getCancelImage().'" alt="'.$this->treeView->_getCancelAlt().'" />'
					.'<input type="submit" class="LVAction" value="'.$this->treeView->_getCancelLabel().'"'
					.' name="'.$this->treeView->_getOpCopyLOEndCancel().'" id="'.$this->treeView->_getOpCopyLOEndCancel().'" />'
					.'</form>'
					.'</div>';
		}
		return "";
	}

	function setOptions( $select_destination ) {
		$this->select_destionation = $select_destination;
		if( $this->treeView !== NULL )
			$this->treeView->setOption(REPOOPTSHOWONLYFOLDER, TRUE);
	}

	function hideLateralMenu() {

		if(isset($_SESSION['test_assessment'])) return true;
		if(isset($_SESSION['direct_play'])) return true;
		return false;
	}

	function loadBody() {
		global $op, $modname;

		if( $this->treeView === NULL )
			$this->initialize();

		// tree indipendent play lo -----------------------------------------------

		if($GLOBALS['op'] == 'scorm_track') {
			require_once($GLOBALS['where_lms'].'/modules/organization/orgresults.php');
			$user = Get::req('id_user', DOTY_INT, false);
			$org  = Get::req('id_org', DOTY_INT, false);
			getTrackingTable($user, $org);
			return;
		}


		if($GLOBALS['op'] == 'scorm_history') {
			require_once(_lms_.'/modules/organization/orgresults.php');
			$user = Get::req('id_user', DOTY_INT, false);
			$obj  = Get::req('id_obj', DOTY_INT, false);
			getHistoryTable($user, $obj);
			return;
		}

		if($GLOBALS['op'] == 'scorm_interactions') {
			require_once(_lms_.'/modules/organization/orgresults.php');//__FILE__.'/appLms/modules/organization/orgresults.php');
			$user  = Get::req('id_user', DOTY_INT, false);
			$track = Get::req('id_track', DOTY_INT, false);
			getInteractionsTable($user, $track);
			return;
		}

		if($GLOBALS['op'] === 'test_track')
		{
			require_once(_lms_.'/modules/organization/orgresults.php');
			$user = Get::req('id_user', DOTY_INT, false);
			$org  = Get::req('id_org', DOTY_INT, false);
			getCompilationTable($user, $org);
			return;
		}

        
   
		if($GLOBALS['op'] == 'custom_playitem') {

			require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
			$saveObj = new Session_Save();
			$saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
			$saveObj->save( $saveName, $this->treeView->getState() );

			$id_item = importVar('id_item');
			$folder = $this->repoDb->getFolderById( $id_item );
			$idItem = $folder->otherValues[REPOFIELDIDRESOURCE];
			$lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE], $idItem);
			if(isset($_GET['edit']) && $_GET['edit']) {

				$back_url = 'index.php?modname='.$modname
						.'&op=custom_enditem&edit=1&id_item='
						.$id_item;
			} else {

				$back_url = 'index.php?modname='.$modname
						.'&op=custom_enditem&id_item='
						.$id_item;
			}
			require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
	
    		 //#11944 ,  can view LO by teacher
            if( Track_Object::isPrerequisitesSatisfied(
					$folder->otherValues[ORGFIELDPREREQUISITES],
					getLogUserId() )  || (isset($_GET['edit']) && $_GET['edit'])  ) {
				$lo->play(  $idItem, 
							$folder->otherValues[ORGFIELDIDPARAM],
							$back_url );
			} else {
				die("You don't have permissions");
			}

			return;
		}
        
        
		// tree indipendent play end --------------------------------------------
		if($GLOBALS['op'] == 'custom_enditem') {

			$lang =& DoceboLanguage::createInstance('organization', 'lms');

			require_once($GLOBALS['where_lms'].'/class.module/track.object.php');
			require_once($GLOBALS['where_lms'].'/lib/lib.stats.php');

			$id_item = importVar('id_item');

			$folder = $this->repoDb->getFolderById( $id_item );

			$objectType = $folder->otherValues[REPOFIELDOBJECTTYPE];
			$idResource = $folder->otherValues[REPOFIELDIDRESOURCE];
			$idParams = $folder->otherValues[ORGFIELDIDPARAM];
			$isTerminator = $folder->otherValues[ORGFIELDISTERMINATOR];
			/*With this direct_play courses was set as finished if is passed the object automatically without needing to set it as finish course object
			$isTerminator = ( isset($_SESSION['direct_play']) ? true : $folder->otherValues[ORGFIELDISTERMINATOR] );*/
			$idCourse = $_SESSION['idCourse'];

			if ( $isTerminator ) {

				require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
				$idTrack = Track_Object::getIdTrackFromCommon( $id_item, getLogUserId() );
				$track = createLOTrack( $idTrack, $objectType, $idResource, $idParams, "" );
				if( $track->getStatus() == 'completed' || $track->getStatus() == 'passed' ) {
					if( !saveTrackStatusChange((int)getLogUserId(), (int)$idCourse , _CUS_END) ) {
						errorCommunication($lang->def( '_OPERATION_FAILURE' ));
						return;
					}
				}
			}

			if (Get::req('edit', DOTY_INT, 0) > 0) {
				Util::jump_to('index.php?modname=storage&op=display');
			}

			if(isset($_SESSION['direct_play'])) {

				$from = Get::req('from', DOTY_ALPHANUM, '');
				//reset cache for the notication
				UpdatesLms::resetCache();

				// autoplay with more than an object and the first one is completed


				require_once(_lms_.'/lib/lib.orgchart.php');
				$orgman = new OrganizationManagement($_SESSION['idCourse']);
				$first_lo =& $orgman->getInfoWhereType(false, $_SESSION['idCourse']);

				if(count($first_lo) >= 2) {

					// if we have more than an object we need to play the first one until it's completed
					$obj = array_shift($first_lo);
					$query = "SELECT status FROM %lms_commontrack WHERE idReference = ".(int)$obj['id_org']." AND idUser = ".(int)Docebo::user()->getId();
					list($status) = sql_fetch_row(sql_query($query));
					if($status == 'completed' || $status == 'passed') {

						// we have more then one object and the first one is complete, we can go to the course first page
						unset($_SESSION['direct_play']);
						$first_page = firstPage();
						$_SESSION['current_main_menu']	= $first_page['idMain'];
						$_SESSION['sel_module_id']		= $first_page['idModule'];
						Util::jump_to('index.php?modname='.$first_page['modulename'].'&op='.$first_page['op'].'&id_module_sel='.$first_page['idModule']);
					}
				}
				// back and out of the course
				switch($from) {
					case "catalogue" 	: { Util::jump_to('index.php?r=lms/catalog/show&sop=unregistercourse'); };break;
					case "lo_plan" 		: { Util::jump_to('index.php?r='._lms_home_.'&sop=unregistercourse'); };break;
					case "lo_history" 	: { Util::jump_to('index.php?r='._lms_home_.'&current_tab=lo_history&sop=unregistercourse'); };break;
					default				: { Util::jump_to('index.php?r='._lms_home_.'&sop=unregistercourse'); };break;
				}
			}
		}

		//--- direct edit item -----------------------------------------------------
		if ($GLOBALS['op']=='direct_edit_item') {
			$id_item = Get::req('id_item', DOTY_INT, 0);
			$this->treeView->op = 'editLO';
		}

		// normal tree function --------------------------------------------

		$this->treeView->playOnly = ($modname == 'organization');

		switch($this->treeView->op) {
			case 'import':
				import($this->treeView);
			break;
			case 'createLO':
				global $modname;
				// save state

				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
				$saveObj->save( $saveName, $this->treeView->getState() );

				$GLOBALS['page']->add( $this->treeView->LOSelector($modname, 'index.php?modname='.$modname
							.'&op=display&sor='.$saveName.'&'
							.$this->treeView->_getOpCreateLOEnd().'=1'), 'content');
			break;
			case 'createLOSel':
				global $modname;
				// save state
				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
				$saveObj->save( $saveName, $this->treeView->getState() );

				// start learning object creation
				$lo = createLO( $_POST['radiolo'] );

				if($lo !== false) {
					$lo->create( 'index.php?modname='.$modname
								.'&op=display&sor='.$saveName.'&'
								.$this->treeView->_getOpCreateLOEnd().'=1' );
				} else {
                   
					$GLOBALS['page']->addStart(
					getTitleArea($this->lang->def('_ORGANIZATION', 'organization', 'lms'), 'organization')
						.'<div class="std_block">', 'content');
					$GLOBALS['page']->addEnd('</div>', 'content');
					if( isset($_SESSION['last_error']) )
						if( $_SESSION['last_error'] != "" ) {
							//$GLOBALS['page']->add( $_SESSION['last_error'], 'content' );
							UIFeedback::error($_SESSION['last_error']);
							unset( $_SESSION['last_error'] );
						}
					organization($this->treeView);
				}
			break;
			case 'editLO':
				global $modname;
				// save state
				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
				$saveObj->save( $saveName, $this->treeView->getState() );

				$folder = $this->repoDb->getFolderById( $this->treeView->getSelectedFolderId() );
				$lo = createLO( $folder->otherValues[REPOFIELDOBJECTTYPE]);
				$lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?modname='.$modname
							.'&op=display&sor='.$saveName.'&'
							.$this->treeView->_getOpEditLOEnd().'=1' );
			break;
			case 'playitem':
				global $modname;
				// save state
				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
				$saveObj->save( $saveName, $this->treeView->getState() );

				$folder = $this->repoDb->getFolderById( $this->treeView->getItemToPlay() );

				$lo = createLO( $folder->otherValues[REPOFIELDOBJECTTYPE]);

				$idItem = $folder->otherValues[REPOFIELDIDRESOURCE];
				$back_url = 'index.php?modname='.$modname
							.'&op=organization&sor='.$saveName.'&'
							.$this->treeView->_getOpPlayEnd()
							.'='.$folder->id;

				$lo->play(  $idItem,
							$folder->otherValues[ORGFIELDIDPARAM],
							$back_url );
			break;
			case 'copyLOSel':
				$GLOBALS['page']->add( $this->treeView->load() );
			break;
 			case 'createLOEnd':			
			case 'copyLOEndOk':
			case 'copyLOEndCancel':
				global $modname;
				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $_GET['crepo'];
				if( $saveObj->nameExists($saveName) ) {
					$saveData =& $saveObj->load($saveName);
					$saveObj->delete($saveName);
					Util::jump_to( ' index.php?modname='.$modname
							.'&op='.$saveData['repo'] );
				}
				Util::jump_to( ' index.php?modname='.$modname
							.'&op=display' );
			break;
			case 'copyLO':
				global $modname;
				// save state
				require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
				$saveObj = new Session_Save();
				$saveName = $saveObj->getName('crepo', true);
				$folder = $this->treeView->tdb->getFolderById( $this->treeView->selectedFolder );
				$saveData = array(	'repo' => 'organization',
									'id' => $this->treeView->getSelectedFolderId(),
									'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
									'name' => $folder->otherValues[REPOFIELDTITLE],
									'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE]
								);
				$saveObj->save( $saveName, $saveData );
				Util::jump_to( ' index.php?modname='.$modname
							.'&op=display&crepo='.$saveName.'&'
							.$this->treeView->_getOpCopyLOSel().'=1' );
			case "display" :
			case "organization" :
			default:

				if( isset($_SESSION['last_error']) )
					if( $_SESSION['last_error'] != "" ) {
						//$GLOBALS['page']->add( $_SESSION['last_error'], 'content' );
						UIFeedback::error($_SESSION['last_error']);
						unset( $_SESSION['last_error'] );
					}
				organization($this->treeView);

			break;
		}
	}

	function useExtraMenu() {
		return false;
	}

	function loadExtraMenu() {

	}

}


//create class istance


?>
