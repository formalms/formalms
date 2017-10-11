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

if(!Docebo::user()->isLoggedIn() || !isset($_SESSION['idCourse'])) die( "You can't access to oragnization");

require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
require_once( _lms_.'/lib/lib.stats.php' );

Util::get_css('lms-scormplayer.css', false, true);

class StatOrg_TreeDb extends OrgDirDb {
	var $stat_filter_on_items = FALSE;
	var $filterGroup = FALSE;

	function _getOtherTables($tname = FALSE) {
			$prefix = $GLOBALS['prefix_lms'];
		if( $this->filterGroup !== FALSE ) {
			echo "\n\n<!-- filterGroup: ".$this->filterGroup."-->";
			if( $tname === FALSE )
				return   ' LEFT JOIN '.$prefix.'_organization_access'
						.' ON ( '.$prefix.'_organization.idOrg = '.$prefix.'_organization_access.idOrgAccess '
						.'   AND '.$prefix.'_organization_access.kind = \'group\' )';
						/*.' LEFT JOIN '.$prefix.'_coursegroupuser'
						.' ON ('.$prefix."_organization_access.kind = 'group'"
						.'     AND '.$prefix.'_organization_access.value = '.$prefix.'_coursegroupuser.idGroup )';*/
			else
				return   ' LEFT JOIN '.$prefix.'_organization_access'
						.' ON ( '.$tname.'.idOrg = '.$prefix.'_organization_access.idOrgAccess '
						.'   AND '.$prefix.'_organization_access.kind = \'group\' )';
						/*.' LEFT JOIN '.$prefix.'_coursegroupuser'
						.' ON ('.$prefix."_organization_access.kind = 'group'"
						.'     AND '.$prefix.'_organization_access.value = '.$prefix.'_coursegroupuser.idGroup )';*/
		} else
			return "";
	}

    function _getOtherFields($tname = FALSE) {
        if( $tname === FALSE )
            return ", title, objectType, idResource, idCategory, idUser, "
                ."idAuthor, version, difficult, description, language, "
                ."resource, objective, dateInsert, isTerminator, idParam, visible, milestone, width, height, publish_from, publish_to, access, publish_for ";
        else
            return   ", ".$tname.".title,".$tname.".objectType,"
                .$tname.".idResource, ".$tname.".idCategory, "
                .$tname.".idUser, ".$tname.".idAuthor, ".$tname.".version, "
                .$tname.".difficult, ".$tname.".description, "
                .$tname.".language, ".$tname.".resource, "
                .$tname.".objective, ".$tname.".dateInsert , "
                .$tname.".idCourse,"
                .$tname.".prerequisites, "
                .$tname.".isTerminator, "
                .$tname.".idParam, "
                .$tname.".visible, "
                .$tname.".milestone, "
                .$tname.".width,"
                .$tname.".height, "
                .$tname.".publish_from, "
                .$tname.".publish_to, "
                .$tname.".access, "
                .$tname.".publish_for ";
    }

    function _getFilter($tname = FALSE) {
		$prefix = $GLOBALS['prefix_lms'];
		$result = "";
		if( $tname === FALSE ) {
			if( $this->stat_filter_on_items ) {
				$result .= " AND (idCourse = '".$this->idCourse."')"
						." AND (idObject <> 0)";
			} else {
				$result .= " AND (idCourse = '".$this->idCourse."')";
			}
		} else {
			if( $this->stat_filter_on_items ) {
				$result .= " AND (".$tname.".idCourse = '".$this->idCourse."')"
					 	." AND (".$tname.".idObject <> 0)";
			} else {
				$result .= " AND (".$tname.".idCourse = '".$this->idCourse."')";
			}
		}
		if( $this->filterGroup !== FALSE ) {
			$result .= " AND ( ".$prefix."_organization_access.value = '".(int)$this->filterGroup."'"
						."  OR ".$prefix."_organization_access.value IS NULL "
					  	.")";
			/*if( $tname === FALSE )
				$result .= ' AND ( '.$prefix.'_organization.idOrg = '.$prefix.'_organization_access.idOrgAccess '
						  .'   AND '.$prefix.'_organization_access.kind = \'group\' )';
			else
				$result .= ' AND ( '.$tname.'.idOrg = '.$prefix.'_organization_access.idOrgAccess '
						  .'   AND '.$prefix.'_organization_access.kind = \'group\' )';*/
		}
		return $result;
	}

	function _getJoinFilter($tname = FALSE) {
		return FALSE;
		if( $this->filterGroup !== FALSE ) {
			$prefix = $GLOBALS['prefix_lms'];
			return $tname.'.idOrg = '.$prefix.'_organization_access.idOrgAccess';
		} else
			return FALSE;
	}

}

define("ONEUSERVIEW", "1");
define("ITEMSVIEW", "2");

class StatOrg_TreeView extends Org_TreeView {
	var $kindOfView = ONEUSERVIEW;
	var $stat_idUser;

	function extendedParsing( $arrayState, $arrayExpand, $arrayCompress ) {
		$arrayState; $arrayExpand; $arrayCompress;
	}


	function printElement(&$stack, $level) {
		$out = TreeView::printElement($stack, $level);
		if( $this->kindOfView == ONEUSERVIEW )
		    $out .= $this->printElementOneUser($stack, $level);
		else
      		$out .= $this->printElementItem($stack, $level);
		return $out;
	}
	function printElementItem(&$stack, $level) {
		if( $level > 0 ) {
			$arrData = $stack[$level]['folder']->otherValues;
			if( is_array($arrData) && $arrData[3] != '' ) {
				return '<input type="submit" class="OrgPlay" value="" name="'
					.$this->_getOpPlayItemId().$stack[$level]['folder']->id .'"'
					.'title="'.$this->_getOpPlayTitle().'" />';
			}
		}
	}
	function printElementOneUser(&$stack, $level) {
		if( $level > 0 ) {
			$arrData = $stack[$level]['folder']->otherValues;
			if( is_array($arrData) && $arrData[3] != '' ) {
				require_once(_lms_.'/class.module/track.object.php' );
				$status = Track_Object::getStatusFromId(
							$stack[$level]['folder']->id,
							$this->stat_idUser );
				return printReport( $status, TRUE, ($arrData[1] !== '' ? true : false) );
			} else {
				$this->tdb->stat_filter_on_items = TRUE;
				$totC = getSubStatStatusCount(	$this->stat_idUser,
												$this->tdb->idCourse,
												array( 'completed', 'passed'),
												$stack[$level]['folder'],
												$this->tdb);
				$totF = getSubStatStatusCount(	$this->stat_idUser,
												$this->tdb->idCourse,
												array( 'failed'),
												$stack[$level]['folder'],
												$this->tdb);
				$tot = count( $this->tdb->getDescendantsId($stack[$level]['folder']) );
				$this->tdb->stat_filter_on_items = TRUE;
				$out = '<div class="fright" >';
				$out .= renderProgress($totC,$totF,$tot,130);
				$out .= '</div>';
				return $out;
			}
		}
	}
}

/**
 * This function print a colored box based on given $status
 * If $returnToCaller id TRUE the function return the output string and
 *  don't put out it.
 * @param String $status the status of the box to be printed
 * @param BOOL $returnToCaller optional parameter; put it to TRUE to get
 *  avoid output and give it as return of function
 **/
function printReport( $status, $returnToCaller = FALSE, $show_progress = true ) {

	switch( $status ) {
		case "completed":
		case "passed":
			$div_class = "reportcomplete";
		break;
		case "failed":
			$div_class = "reportfailed";
		break;
		default:
			$div_class = "reportincomplete";
		break;
	}
	if($show_progress)
	{
		$strOut = '<div class="report_on_tree '.$div_class.'" >';
		if( isset($GLOBALS['statusLabels'][$status]) )
			$strOut .= $GLOBALS['statusLabels'][$status];
		else
			$strOut .= Lang::t($status, 'standard');
		$strOut .= '</div>';
	}
	else
		$strOut = '';
	if($returnToCaller)
		return $strOut;
	else
		echo $strOut;
}

function getSubStatStatusCount($stat_idUser, $stat_idCourse, $arrStauts, $folder, &$tdb) {
	$prefix = $GLOBALS['prefix_lms'];
	$arrItems = $tdb->getDescendantsId($folder);
	if( $arrItems === FALSE )
		return 0;
	$query = "SELECT count(ct.idreference)"
		." FROM ".$prefix."_commontrack ct, ".$prefix."_organization org"
		." WHERE (ct.idReference = org.idOrg)"
		."   AND (idUser = '".(int)$stat_idUser."')"
		."   AND (idCourse = '".(int)$stat_idCourse."')"
		."   AND (idOrg IN (".implode(",", $arrItems)."))"
		."   AND (status IN ('".implode("','",$arrStauts)."'))";
	if( ($rsItems = sql_query( $query )) === FALSE ) {
		echo $query;
		UiFeedback::error( "Error on query to get user count based on status" );
		return;
	}

	list($tot) = sql_fetch_row( $rsItems );
	//sql_free_result( $rsItems );
	return $tot;
}

define("STATFILTER_ALL_GROUP", -1);
define("STATFILTER_ALL_STATUS", -1000);
define("STATFILTER_ALL_EDITION", -1);

function statuserfilter() {

	require_once(_base_.'/lib/lib.table.php');
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_lms_.'/lib/lib.subscribe.php');
	
	$view_all_perm = checkPerm('view_all_statuser', true);
	
	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form = new Form();
	$aclManager =& Docebo::user()->getACLManager();

	$out->setWorkingZone('content');

	$GLOBALS['module_assigned_name'][$GLOBALS['modname']] = $lang->def('_STATFORUSER');

	$out->add(getTitleArea($lang->def('_STATFORUSER'), 'stats'));
	$out->add('<div class="std_block">');

	$group_filter = Get::req('group_filter', DOTY_INT, STATFILTER_ALL_GROUP);
	$status_filter = Get::req('status_filter', DOTY_INT, STATFILTER_ALL_STATUS);
	$editions_filter = Get::req('editions_filter', DOTY_INT, STATFILTER_ALL_EDITION);
        $date_filter = Get::req('date_filter', DOTY_INT, STATFILTER_ALL_EDITION);
	$user_filter = Get::req('user_filter', DOTY_MIXED, '');

	$cs = new CourseSubscribe_Manager();
	/*
	 * Print form for group and status selection
	 */
	$out->add( $form->openForm( "statuserfilter" , "index.php?modname=stats&amp;op=statuser" ) );

	// ------- Filter on group
	$arr_idst = $aclManager->getBasePathGroupST('/lms/course/'.(int)$_SESSION['idCourse'].'/group');
	$arr_result_groups = $aclManager->getGroups($arr_idst);

	$std_content = $aclManager->getContext();
	$aclManager->setContext('/lms/course/'.(int)$_SESSION['idCourse'].'/group');


	$arr_groups = array(STATFILTER_ALL_GROUP => $lang->def('_ALL'));
	foreach( $arr_result_groups as $idst_group => $info_group ) {
		if( !$info_group[ACL_INFO_GROUPHIDDEN] )
			$arr_groups[$idst_group] = $aclManager->relativeId($info_group[ACL_INFO_GROUPID]);
	}
	$aclManager->setContext($std_content);

	$out->add(Form::getTextField(Lang::t('_FULLNAME', 'standard'), 'user_filter', 'user_filter', 255, $user_filter));

	$out->add( $form->getDropdown(
		$lang->def('_GROUPS'),
		'group_filter',
		'group_filter',
		$arr_groups ,
		$group_filter
	) );

	// ------ Filter on status
	$arr_status = array( STATFILTER_ALL_STATUS => $lang->def('_FILTERSTATUSSELECTONEOPTION') );
	$arr_status = $arr_status + $cs->getUserStatus();
	$out->add( $form->getDropdown(
		$lang->def('_ORDER_BY'),
		'status_filter',
		'status_filter',
		$arr_status ,
		$status_filter
	) );

	//--- filter on edition ------------------------------------------------------

	//retrieve edition
	$query = "SELECT * FROM %lms_course_editions WHERE id_course = ".(int)$_SESSION['idCourse'];
	$res = sql_query($query);

	//is there more any edition ?
	if (sql_num_rows($res) > 0) {
		$arr_editions = array(STATFILTER_ALL_EDITION => $lang->def('_FILTEREDITIONSELECTONEOPTION'));

		//list of editions for the dropdown, in the format: "[code] name (date_begin - date_end)"
		while ($einfo = sql_fetch_object($res)) {
			$_label = '';
			if ($einfo->code != '') {
				$_label .= '['.$einfo->code.'] ';
			}
			if ($einfo->name != '') {
				$_label .= $einfo->neme;
			}
			if (($einfo->date_begin != '' || $einfo->date_begin != '0000-00-00') && ($einfo->date_end != '' || $einfo->date_end != '0000-00-00')) {
				$_label .= ' ('.Format::date($einfo->date_begin, 'date')
					.' - '.Format::date($einfo->date_end, 'date').')';
			}
			if ($_label == '') {
				//...
			}
			$arr_editions[$einfo->id_edition] = $_label;
		}

		//draw editions dropdown
		$out->add( $form->getDropdown( 	$lang->def('_FILTEREDITIONSELECTTITLE'),
										'editions_filter',
										'editions_filter',
										$arr_editions ,
										$editions_filter ) );
	}
	//--- filter on class ------------------------------------------------------

	//retrieve class (date)
	//$query = "SELECT * FROM %lms_course_date WHERE id_course = ".(int)$_SESSION['idCourse'];
	$query = "SELECT dt.id_date, dt.code, dt.name, MIN( dy.date_begin ) AS sub_start_date, MAX( dy.date_end ) AS sub_end_date
		FROM %lms_course_date AS dt
		JOIN %lms_course_date_day AS dy ON dy.id_date = dt.id_date
		WHERE dt.id_course = ".(int)$_SESSION['idCourse']."
		GROUP BY dt.id_date
		ORDER BY dy.date_begin";
	$res = sql_query($query);

	//is there more any edition ?
	if (sql_num_rows($res) > 0) {
		$arr_date = array(STATFILTER_ALL_EDITION => $lang->def('_FILTEREDITIONSELECTONEOPTION'));

		//list of editions for the dropdown, in the format: "[code] name (date_begin - date_end)"
		while ($einfo = sql_fetch_object($res)) {
			$_label = '';
			if ($einfo->code != '') {
				$_label .= '['.$einfo->code.'] ';
			}
			if ($einfo->name != '') {
				$_label .= $einfo->neme;
			}
			if (($einfo->sub_start_date != '' || $einfo->sub_start_date != '0000-00-00') && ($einfo->sub_end_date != '' || $einfo->sub_end_date != '0000-00-00')) {
				$_label .= ' ('.Format::date($einfo->sub_start_date, 'date')
					.' - '.Format::date($einfo->sub_end_date, 'date').')';
			}
			if ($_label == '') {
				//...
			}
			$arr_date[$einfo->id_date] = $_label;
		}

		//draw editions dropdown
		$out->add( $form->getDropdown( 	$lang->def('_FILTEREDITIONSELECTTITLE'),
										'date_filter',
										'date_filter',
										$arr_date ,
										$date_filter ) );
	}
        
//------------------------------------------------------------------------------

	if(isset($_POST['start_filter']) && $_POST['start_filter'] = 1)
		$out->add($form->getCheckBox($lang->def('_FILTEROBJECTFINISHED'), 'start_filter', 'start_filter', '1', true));
	else
		$out->add($form->getCheckBox($lang->def('_FILTEROBJECTFINISHED'), 'start_filter', 'start_filter', '1'));

	$out->add('<br/>');

	$out->add( $form->getButton('gofilter', 'gofilter', $lang->def('_SEARCH')) );

	/*
	 * Get all students of course that is contained in selected group
	 * For any student compute progress
	 */

	$tabStat = new Table(Get::sett('visuItem'), $lang->def('_STATS_USERS'), $lang->def('_STATS_USERS'));
	$tabStat->initNavBar('ini', 'button');
	$limit = $tabStat->getSelectedElement();

	// step 2) load all students of course in selected group
	$lev = false;
	$group_all_members = false;
	if($group_filter != STATFILTER_ALL_GROUP) $group_all_members = $aclManager->getGroupAllUser($group_filter);
	//$students = getSubscribedInfo((int)$_SESSION['idCourse'], FALSE, $lev, TRUE, ( $status_filter != -1 ? $status_filter : false ), false, true);
	$students = getSubscribedInfo(
		(int)$_SESSION['idCourse'],
		false,
		$lev,
		true,
		( $status_filter != STATFILTER_ALL_STATUS ? $status_filter : false ),
		( $editions_filter != STATFILTER_ALL_EDITION ? $editions_filter : false),//false,
		true,
		$user_filter,
		$group_all_members,
		$limit,
                ( $date_filter != STATFILTER_ALL_EDITION ? $date_filter : false)
                );

                $query =	"SELECT COUNT(*)"
			." FROM %lms_courseuser AS cu"
			.($user_filter !== '' ? " JOIN ".$GLOBALS['prefix_fw']."_user AS u ON u.idst = cu.idUser" : '')
			." WHERE cu.idCourse = ".(int)$_SESSION['idCourse']
			.($status_filter != STATFILTER_ALL_STATUS ? " AND cu.status = '".$status_filter."'" : '')
			.($user_filter !== '' ? " AND (u.firstname LIKE '%".$user_filter."%' OR u.lastname LIKE '%".$user_filter."%' OR u.userid LIKE '%".$user_filter."%')" : '')
			.($group_all_members !== false ? " AND c.idUser IN (".implode(',', $group_all_members).")" : '');

	list($total_user) = sql_fetch_row(sql_query($query));

	//apply sub admin filters, if needed
	if( !$view_all_perm ) {
		//filter users
		require_once(_base_.'/lib/lib.preference.php');
		$ctrlManager = new ControllerPreference();
		$ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
                foreach( $students as $idst => $user_course_info ) {
                    if ( !in_array ($idst, $ctrl_users) ) {
                        // Elimino gli studenti non amministrati
                        unset ($students[$idst]);
                    }
                    
                }
                $total_user = count($students);
	}
        
	$content_h 	= array(
		$lang->def('_USERNAME'),
		$lang->def('_STATS_FULLNAME'),
		$lang->def('_STATUS'),
		$lang->def('_LEARNING_OBJECTS'),
		$lang->def('_PROGRESS')
		);
	$type_h 	= array('', '', 'image', 'image', 'image');

	$tabStat->setColsStyle($type_h);
	$tabStat->addHead($content_h);

	$aclManager =& Docebo::user()->getACLManager();
	$acl =& Docebo::user()->getACL();

	//apply sub admin filters, if needed
	if( !$view_all_perm ) {
		//filter users
		require_once(_base_.'/lib/lib.preference.php');
		$ctrlManager = new ControllerPreference();
		$ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
                foreach( $students as $idst => $user_course_info ) {
                    if ( !in_array ($idst, $ctrl_users) ) {
                        // Elimino gli studenti non amministrati
                        unset ($students[$idst]);
                    }
                    
                }
	}
                
	// search memebers of the selected group

	foreach( $students as $idst => $user_course_info ) {

		if($group_filter == STATFILTER_ALL_GROUP || in_array($idst, $group_all_members) ) {
			$user_info = $aclManager->getUser( $idst, FALSE );

			if($user_info != false) {
				$totItems = getNumCourseItems( 		(int)$_SESSION['idCourse'],
													FALSE,
													$idst,
													FALSE );
				$totComplete = getStatStatusCount(	$idst,
													(int)$_SESSION['idCourse'],
													array( 'completed', 'passed' )
													);
				$totFailed = getStatStatusCount(	$idst,
													(int)$_SESSION['idCourse'],
													array( 'failed' )
													);
				$stat_status = $cs->getUserStatusTr( $user_course_info['status'] );

				if(isset($_POST['start_filter']) && $_POST['start_filter'] = 1)
				{
				     if($totComplete)
				     {
	                    // now print entry
	        			$content = array('<a href="index.php?modname=stats&amp;op=statoneuser&amp;idUser='.$idst.'" >'
	        							.$aclManager->relativeId($user_info[ACL_INFO_USERID]).'</a>',
	        						$user_info[ACL_INFO_LASTNAME].'&nbsp;'.$user_info[ACL_INFO_FIRSTNAME],
	        						'<a href="index.php?modname=stats&amp;op=modstatus&amp;idUser='.$idst.'">'
	        						.$stat_status.'</a>');

	        			$content[] = $totComplete.'/'.$totFailed.'/'.$totItems;
	        			$content[] = renderProgress($totComplete,$totFailed,$totItems);
	        			$tabStat->addBody($content);
	                 }
	            }
	            else
	            {
	                // now print entry
	    			$content = array('<a href="index.php?modname=stats&amp;op=statoneuser&amp;idUser='.$idst.'" >'
	    							.$aclManager->relativeId($user_info[ACL_INFO_USERID]).'</a>',
	    						$user_info[ACL_INFO_LASTNAME].'&nbsp;'.$user_info[ACL_INFO_FIRSTNAME],
	    						'<a href="index.php?modname=stats&amp;op=modstatus&amp;idUser='.$idst.'">'
	    						.$stat_status.'</a>');

	    			$content[] = $totComplete.'/'.$totFailed.'/'.$totItems;
	    			$content[] = renderProgress($totComplete,$totFailed,$totItems);
	    			$tabStat->addBody($content);
	            }
			}
		}
	}
	$out->add($tabStat->getTable());
	$out->add($tabStat->getNavBar($limit, $total_user));
	$out->add( $form->closeForm() );
	$out->add('</div>');
}

function statoneuser() {
	

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$aclManager =& Docebo::user()->getACLManager();

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATFORUSER'), 'stats', false, true));
	$out->add('<div class="std_block">');

	$idst 		= (int)$_GET['idUser'];
	$user_info 	= $aclManager->getUser( $idst, FALSE );

	$orgDb = new StatOrg_TreeDb();
	$treeView = new StatOrg_TreeView($orgDb, $_SESSION['idCourse']);
	$treeView->stat_idUser = $idst;
	$treeView->parsePositionData($_POST, $_POST, $_POST);

	// print container div and form
	$out->add(getBackUi('index.php?modname=stats&amp;op=statuser', $lang->def('_BACK')));
	$out->add('<div class="title">'
		.$lang->def('_STATFORUSER').' '.$user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME]
		.'</div>');
	$out->add('<form name="orgshow" method="post"'
	.' action="index.php?modname=stats&amp;op=statoneuser&amp;idUser='.$idst.'"'
	.' >'."\n"
	.'<input type="hidden" id="authentic_request_org" name="authentic_request" value="'.Util::getSignature().'" />');

	$out->add($treeView->load());
	//if( funAccess('orgedit','MOD', TRUE, 'organization' ) ) $treeView->loadActions();

	$out->add('</form>');
	// print form for import action

	// display track if exists
	$item = $orgDb->getFolderById( $treeView->getSelectedFolderId() );
	$values = $item->otherValues;

	$param = $treeView->printState(FALSE);
	$arrBack_Url = array( 	'address' => 'index.php?modname=stats&op=statoneuser&idUser='.$treeView->stat_idUser,
							'end_address' => 'index.php?modname=stats&op=statoneuser&idUser='.$treeView->stat_idUser,
							'param' => $param
						);

	require_once(_lms_.'/class.module/track.object.php');
	//find idTrack
	$idTrack = Track_Object::getIdTrackFromCommon( $treeView->getSelectedFolderId(), $treeView->stat_idUser );


	if($idTrack) {
		$lo = createLOTrack( $idTrack,
						$values[REPOFIELDOBJECTTYPE],
						$values[REPOFIELDIDRESOURCE],
						$values[ORGFIELDIDPARAM],
						$arrBack_Url);

		if($lo !== false) {
			$GLOBALS['wrong_way_to_pass_parameter'] = $lo->idReference;//$values[REPOFIELDIDRESOURCE];
			$out->add($lo->loadReport( $treeView->stat_idUser ));
		}
	}
	$out->add('</div>');
}

function statcourse() {
	
	require_once(_base_.'/lib/lib.form.php');

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$aclManager =& Docebo::user()->getACLManager();
	$form = new Form();

	if( isset( $_POST['group_filter'] ) ) {
		$group_filter = $_POST['group_filter'];
	} else {
		$group_filter = "";
	}

	$orgDb = new StatOrg_TreeDb();
	if( $group_filter != "" ) {
		$orgDb->filterGroup = $group_filter;
	}
	$treeView = new StatOrg_TreeView($orgDb, $_SESSION['idCourse']);
	$treeView->kindOfView = ITEMSVIEW;

	$treeView->parsePositionData($_POST, $_POST, $_POST);
	if( $treeView->op == 'playitem' )
    	Util::jump_to(" index.php?modname=stats&op=statitem&idItem=".$treeView->getItemToPlay());

	$out->setWorkingZone('content');
	$out->add(getTitleArea(lang::t('_STATCOURSE', 'menu_course')));
	$out->add('<div class="std_block">');
	$out->add( $form->openForm( 'orgshow', "index.php?modname=stats&amp;op=statcourse" ) );

	/*
	 * Print form for group selection
	 */
	// ------- Filter on group
	$arr_idst = $aclManager->getBasePathGroupST('/lms/course/'.(int)$_SESSION['idCourse'].'/');
	$arr_result_groups = $aclManager->getGroups($arr_idst);
	$arr_groups = array('' => $lang->def('_ALL'));

	$std_content = $aclManager->getContext();
	$aclManager->setContext('/lms/course/'.(int)$_SESSION['idCourse'].'/group');


	$arr_groups = array('' => $lang->def('_ALL'));
	foreach( $arr_result_groups as $idst_group => $info_group ) {
		if( !$info_group[ACL_INFO_GROUPHIDDEN] )
			$arr_groups[$idst_group] = $aclManager->relativeId($info_group[ACL_INFO_GROUPID]);
	}
	$aclManager->setContext($std_content);

	$out->add( $form->getDropdown( 	$lang->def('_GROUPS'),
									'group_filter',
									'group_filter',
									$arr_groups ,
									$group_filter ) );

	$out->add( $form->getButton('gofilter', 'gofilter', $lang->def('_SEARCH')) );

	$out->add($treeView->load());
	//if( funAccess('orgedit','MOD', TRUE, 'organization' ) ) $treeView->loadActions();

	$out->add($form->closeForm());
	// print form for import action

	$out->add('</div>');

	$idFolder = $treeView->getSelectedFolderId();
	if( $idFolder != 0 ) {
		$item = $orgDb->getFolderById( $idFolder );
		$values = $item->otherValues;

		$param = $treeView->printState(FALSE);
		$arrBack_Url = array('address' => 'index.php?modname=stats&op=statcourse',
							 'end_address' => 'index.php?modname=stats&op=statcourse',
							 'param' => $param
						 );
		$lo = createLOTrack( NULL,
							$values[REPOFIELDOBJECTTYPE],
							$values[REPOFIELDIDRESOURCE],
							$values[ORGFIELDIDPARAM],
							$arrBack_Url);

		if($lo !== false) {
			$out->add($lo->loadObjectReport( ));
		} else {
			if( Get::sett('do_debug') == 'on' )
				$out->add(	"<!-- createLOTrack fallita".
							"oggetto type: ".$values[REPOFIELDOBJECTTYPE]."<br/>".
							" resource id: ".$values[REPOFIELDIDRESOURCE]."<br/>".
							    "param id: ".$values[ORGFIELDIDPARAM]." -->" );
		}
	}
}

/**
 * Print statistic on one item
 *
 **/
function statitem() {
	require_once( _lms_.'/class.module/track.object.php' );
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');
	require_once(_lms_.'/lib/lib.subscribe.php');

	$view_all_perm = checkPerm('view_all_statcourse', true);
        
	$cs = new CourseSubscribe_Manager();

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form =  new Form();
	$aclManager =& Docebo::user()->getACLManager();
	$acl =& Docebo::user()->getACL();

	$idItem = (int)$_GET['idItem'];

	$group_filter = Get::req('group_filter', DOTY_INT, -1);
	$status_filter = Get::req('status_filter', DOTY_INT, -1);
	$user_filter = Get::req('user_filter', DOTY_MIXED, '');

	$tabStat = new Table(Get::sett('visuItem'), $lang->def('_STATSITEM').$titleLO, $lang->def('_STATSITEM').$titleLO);
	$tabStat->initNavBar('ini', 'button');
	$limit = $tabStat->getSelectedElement();

	list($titleLO, $objectType) = sql_fetch_row(sql_query("SELECT title, objectType FROM "
																.$GLOBALS['prefix_lms']."_organization"
																." WHERE idOrg='".$idItem."'"));

	$lev = false;
	$group_all_members = false;
	if($group_filter != '-1') $group_all_members = $aclManager->getGroupAllUser($group_filter);
	$students = getSubscribedInfo(
		(int)$_SESSION['idCourse'],
		false,
		$lev,
		true,
		( $status_filter != -1 ? $status_filter : false ),
		( $editions_filter != -1 ? $editions_filter : false),//false,
		true,
		$user_filter,
		$group_all_members,
		$limit);

	$query =	"SELECT COUNT(*)"
			." FROM %lms_courseuser AS cu"
			.($user_filter !== '' ? " JOIN ".$GLOBALS['prefix_fw']."_user AS u ON u.idst = cu.idUser" : '')
			." WHERE cu.idCourse = ".(int)$_SESSION['idCourse']
			.($status_filter != -1 ? " AND cu.status = '".$status_filter."'" : '')
			.($user_filter !== '' ? " AND (u.firstname LIKE '%".$user_filter."%' OR u.lastname LIKE '%".$user_filter."%' OR u.userid LIKE '%".$user_filter."%')" : '')
			.($group_all_members !== false ? " AND c.idUser IN (".implode(',', $group_all_members).")" : '');

	list($total_user) = sql_fetch_row(sql_query($query));

	//apply sub admin filters, if needed
	if( !$view_all_perm ) {
		//filter users
		require_once(_base_.'/lib/lib.preference.php');
		$ctrlManager = new ControllerPreference();
		$ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
                foreach( $students as $idst => $user_course_info ) {
                    if ( !in_array ($idst, $ctrl_users) ) {
                        // Elimino gli studenti non amministrati
                        unset ($students[$idst]);
                    }
                    
                }
                $total_user = count($students);
	}
        
	// get idst of the access in item
	$query = "SELECT value FROM ".$GLOBALS['prefix_lms']."_organization_access"
			." WHERE idOrgAccess = '".$idItem."'";
	if( ($rs = sql_query( $query )) === FALSE ) {
		UiFeedback::error( "Error on query to load item access" );
		return;
	}

	$arr_access = array();
	while( list($value) = sql_fetch_row( $rs ) )
		$arr_access[] = $value;

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATSITEM').$titleLO, 'stats'));
	$out->add('<div class="std_block">'
			.getBackUi('index.php?modname=stats&amp;op=statcourse', $lang->def('_BACK')));
	$out->add( $form->openForm( 'orgshow', 'index.php?modname=stats&amp;op=statitem&amp;idItem='.$idItem ) );
	if (isset($_POST['view_open_quest']))
	{
		$query_resource = "SELECT idResource" .
							" FROM ".$GLOBALS['prefix_lms']."_organization" .
							" WHERE idOrg = '".$idItem."'";

		list($id_poll) = sql_fetch_row(sql_query($query_resource));

		$query_quest = "SELECT id_quest, title_quest" .
						" FROM ".$GLOBALS['prefix_lms']."_pollquest" .
						" WHERE id_poll = '".$id_poll."'" .
						" AND type_quest = 'extended_text'";

		$result_quest = sql_query($query_quest);

		$type_h = array('');
		$cont_h = array($lang->def('_ANSWER'));

		while (list($id_quest, $title_quest) = sql_fetch_row($result_quest))
		{
			$tb = new Table(400, $title_quest);
			$tb->setColsStyle($type_h);
			$tb->addHead($cont_h);

			$query_answer = "SELECT more_info" .
							" FROM ".$GLOBALS['prefix_lms']."_polltrack_answer" .
							" WHERE id_quest = '".$id_quest."'";

			$result_answer = sql_query($query_answer);

			while (list($answer) = sql_fetch_row($result_answer))
			{
				$cont = array();
				$cont[] = $answer;

				$tb->addBody($cont);
			}

			$out->add($tb->getTable().'<br/>');
		}

		$out->add(
			$form->openButtonSpace()
			.$form->getButton('back', 'back', $lang->def('_BACK'))
			.$form->closeButtonSpace());
	}
	else
	{
		$arr_idst = $aclManager->getBasePathGroupST('/lms/course/'.(int)$_SESSION['idCourse'].'/group');
		$arr_result_groups = $aclManager->getGroups($arr_idst);

		$std_content = $aclManager->getContext();
		$aclManager->setContext('/lms/course/'.(int)$_SESSION['idCourse'].'/group');


		$arr_groups = array(-1 => $lang->def('_ALL'));
		foreach( $arr_result_groups as $idst_group => $info_group ) {
			if( !$info_group[ACL_INFO_GROUPHIDDEN] )
				$arr_groups[$idst_group] = $aclManager->relativeId($info_group[ACL_INFO_GROUPID]);
		}

		$aclManager->setContext($std_content);

		$out->add(Form::getTextField(Lang::t('_FULLNAME', 'standard'), 'user_filter', 'user_filter', 255, $user_filter));

		$out->add( $form->getDropdown( 	$lang->def('_GROUPS'),
									'group_filter',
									'group_filter',
									$arr_groups ,
									$group_filter) );

		// ------ Filter on status
		$arr_status = array( 	-1 => $lang->def('_FILTERSTATUSSELECTONEOPTION'),
								_CUS_SUBSCRIBED => $lang->def('_USER_STATUS_SUBS'),
								_CUS_BEGIN 		=> $lang->def('_USER_STATUS_BEGIN'),
								_CUS_END 		=> $lang->def('_END'),
								_CUS_SUSPEND 	=> $lang->def('_SUSPENDED') );
		$out->add( $form->getDropdown( 	$lang->def('_STATUS'),
										'status_filter',
										'status_filter',
										$arr_status ,
										$status_filter ) );

		$out->add( $form->getButton('gofilter', 'gofilter', $lang->def('_SEARCH')) );


		//-----------------------------------------
		$content_h 	= array(
			$lang->def('_USERNAME'),
			$lang->def('_STATS_FULLNAME'),
			$lang->def('_STATUS'),
			$lang->def('_PROGRESS')
			);
		$type_h 	= array('', '', 'image', 'image', '');

		$tabStat->setColsStyle($type_h);
		$tabStat->addHead($content_h);

		//-----------------------------------------
		foreach( $students as $idst => $user_course_info ) {
			$user_info = $aclManager->getUser( $idst, FALSE );
			if($user_info != false) {

				$arr_allst = $acl->getUserAllST( $user_info[ACL_INFO_USERID] );

				if( count($arr_access) === 0 || count(array_intersect($arr_access,$arr_allst)) > 0 ) {
					$status = Track_Object::getStatusFromId(
									$idItem,
									$idst );
					// NOTE: How to get stat_status for users?
					$stat_status = $cs->getUserStatusTr($user_course_info['status']);
					$tabStat->addBody(
						array( '<a href="index.php?modname=stats&amp;op=statoneuseroneitem&amp;idUser='.$idst.'&amp;idItem='.$idItem.'" >'
									.$aclManager->relativeId($user_info[ACL_INFO_USERID]).'</a>',
								$user_info[ACL_INFO_LASTNAME].'&nbsp;'.$user_info[ACL_INFO_FIRSTNAME],
								$stat_status,
								printReport( $status, TRUE )
								)
						);
				}
			}
		}
		$out->add($tabStat->getTable());
		$out->add($tabStat->getNavBar($limit, $total_user));

		$query = "SELECT idResource" .
				" FROM ".$GLOBALS['prefix_lms']."_organization" .
				" WHERE idOrg = '".$idItem."'";

		list($id_poll) = sql_fetch_row(sql_query($query));

		$query = "SELECT id_quest" .
				" FROM ".$GLOBALS['prefix_lms']."_pollquest" .
				" WHERE id_poll = '".$id_poll."'" .
				" AND type_quest = 'extended_text'";

		$result = sql_query($query);

		if (sql_num_rows($result) && $objectType == 'poll')
		{
			$out->add(
				$form->openButtonSpace()
				.'<br/>'
				.$form->getButton('view_open_quest', 'view_open_quest', $lang->def('_VIEW_OPEN_QUEST'))
				.$form->closeButtonSpace());
		}
	}

	$out->add($form->closeForm());
	$out->add('</div>'."\n");

}

/**
 * Callback for make link in scorm renderer
 * @param $text string the text
 * @param $idItemDetail string the unique id of item
 * @return string the link to be renderd
 **/
function cbMakeReportLink($text, $idItemDetail ) {
	if(isset($_GET['idItem'])) {
		$idItem = (int)$_GET['idItem'];
		$backto = 'statoneuseroneitem';
	}
	if(isset($GLOBALS['wrong_way_to_pass_parameter'])) {
		$idItem = (int)$GLOBALS['wrong_way_to_pass_parameter'];
		$backto = 'statoneuser';
	}
	$idst_user = (int)$_GET['idUser'];

	return '<a href="index.php?modname=stats&amp;op=statoneuseroneitemdetail&amp;idUser='.$idst_user.'&amp;idItem='.$idItem.'&amp;idItemDetail='.$idItemDetail.'&amp;backto='.$backto.'" >'
			.$text.'</a>';
}

/**
 * Print statistics for one user and one item
 *  $_GET['idUser']
 *  $_GET['idItem']
 **/
function statoneuseroneitem() {
	require_once(_lms_.'/class.module/track.object.php' );
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form =  new Form();
	$aclManager =& Docebo::user()->getACLManager();
	$acl =& Docebo::user()->getACL();

	$idItem = (int)$_GET['idItem'];
	$idst_user = (int)$_GET['idUser'];

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATSUSERITEM'), 'stats'));
	$out->add('<div class="std_block">'
			.getBackUi('index.php?modname=stats&amp;op=statitem&amp;idItem='.$idItem, $lang->def('_BACK')));
	//$out->add( $form->openForm( 'orgshow', 'index.php?modname=stats&amp;op=statitem&amp;idItem='.$idItem ) );

	list($titleLO, $objectType) = sql_fetch_row(sql_query("SELECT title, objectType FROM "
												 				.$GLOBALS['prefix_lms']."_organization"
												 				." WHERE idOrg='".(int)$_GET['idItem']."'"));

	$user_info = $aclManager->getUser( $idst_user, FALSE );

	$out->add( '<div class="title">'
		.$lang->def('_STATFORUSER').' '.$user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' '
		.$lang->def('_STATSFORITEM').' <img src="'.getPathImage().'lobject/'.$objectType.'.gif"'
		.' alt="'.$objectType.'" />'.$titleLO
		. '</div>');

	$loTrack = createLOTrackShort( 	$idItem,
									$idst_user,
									'index.php?modname=stats&op=statitem&idItem='.$idItem);
	if( $loTrack === FALSE )
		$out->add( $lang->def('_STATNOTRACKFORUSER') );
	else
		$out->add( $loTrack->loadReport($idst_user) );
	$out->add( '</div>' );
}

/**
 * Print statistics for one user and one item
 *  $_GET['idUser']
 *  $_GET['idItem']
 **/
function statoneuseroneitemdetails() {
	require_once(_lms_.'/class.module/track.object.php' );
	
	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form =  new Form();
	$aclManager =& Docebo::user()->getACLManager();
	$acl =& Docebo::user()->getACL();

	$backto = $_GET['backto'];
	$idItem = (int)$_GET['idItem'];
	$idst_user = (int)$_GET['idUser'];
	$idItemDetail = (int)$_GET['idItemDetail'];

		$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATSUSERITEM'), 'stats'));
	$out->add('<div class="std_block">'
			.getBackUi('index.php?modname=stats&amp;op='.$backto.'&amp;idUser='.$idst_user.'&amp;idItem='.$idItem, $lang->def('_BACK')));
	//$out->add( $form->openForm( 'orgshow', 'index.php?modname=stats&amp;op=statitem&amp;idItem='.$idItem ) );

	list($titleLO, $objectType) = sql_fetch_row(sql_query("SELECT title, objectType FROM "
												 				.$GLOBALS['prefix_lms']."_organization"
												 				." WHERE idOrg='".(int)$_GET['idItem']."'"));

	$user_info = $aclManager->getUser( $idst_user, FALSE );

	$out->add( '<div class="title">'
		.$lang->def('_STATFORUSER').' '.$user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' '
		.$lang->def('_STATSFORITEM').' <img src="'.getPathImage().'lobject/'.$objectType.'.gif"'
		.' alt="'.$objectType.'" />'.$titleLO
		. '</div>');
	$loTrack = createLOTrackShort( 	$idItem,
									$idst_user,
									'index.php?modname=stats&op=statitem&idItem='.$idItem);
	if( $loTrack === FALSE )
		$out->add( $lang->def('_STATNOTRACKFORUSER') );
	else
		$out->add( $loTrack->loadReportDetail($idst_user,$idItemDetail, $idItem) );
	$out->add( '</div>' );
}

/**
 * Print statistics history for one user and one item
 *  $_GET['idUser']
 *  $_GET['idItem']
 **/
function statoneuseroneitemhistory() {
	require_once(_lms_.'/class.module/track.object.php' );

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.table.php');

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form =  new Form();
	$aclManager =& Docebo::user()->getACLManager();
	$acl =& Docebo::user()->getACL();

	$backto = $_GET['backto'];
	$idItem = (int)$_GET['idItem'];
	$idst_user = (int)$_GET['idUser'];
	$idItemDetail = (int)$_GET['idItemDetail'];

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATSUSERITEM'), 'stats'));
	$out->add('<div class="std_block">'
			.getBackUi('index.php?modname=stats&amp;op='.$backto.'&amp;idUser='.$idst_user.'&amp;idItem='.$idItem, $lang->def('_BACK')));
	//$out->add( $form->openForm( 'orgshow', 'index.php?modname=stats&amp;op=statitem&amp;idItem='.$idItem ) );

	list($titleLO, $objectType) = sql_fetch_row(sql_query("SELECT title, objectType FROM "
												 				.$GLOBALS['prefix_lms']."_organization"
												 				." WHERE idOrg='".(int)$_GET['idItem']."'"));

	$user_info = $aclManager->getUser( $idst_user, FALSE );

	$out->add( '<div class="title">'
		.$lang->def('_STATFORUSER').' '.$user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME].' '
		.$lang->def('_STATSFORITEM').' <img src="'.getPathImage().'lobject/'.$objectType.'.gif"'
		.' alt="'.$objectType.'" />'.$titleLO
		. '</div>');
	$loTrack = createLOTrackShort( 	$idItem,
									$idst_user,
									'index.php?modname=stats&op=statitem&idItem='.$idItem);
	if( $loTrack === FALSE )
		$out->add( $lang->def('_STATNOTRACKFORUSER') );
	else
		$out->add( $loTrack->loadReportDetailHistory($idst_user,$idItemDetail, $idItem) );
	$out->add( '</div>' );
}

function modstatus() {
	funAccess('statuser', 'OP');
	require_once(_base_.'/lib/lib.form.php');
	require_once(_lms_.'/lib/lib.subscribe.php');

	$lang =& DoceboLanguage::createInstance('stats', 'lms');
	$out  =& $GLOBALS['page'];
	$form =  new Form();
	$aclManager =& Docebo::user()->getACLManager();

	$idUser = (int)$_GET['idUser'];
	//$idItem = (int)$_GET['idItem'];

	$user_info = $aclManager->getUser( $idUser, FALSE );

	$out->setWorkingZone('content');
	$out->add(getTitleArea($lang->def('_STATUS').' '.$user_info[ACL_INFO_FIRSTNAME].' '.$user_info[ACL_INFO_LASTNAME], 'stats'));
	$out->add('<div class="std_block">'
			.getBackUi('index.php?modname=stats&amp;op=statuser&amp;idUser='.$idUser, $lang->def('_BACK')));

	$query = "
	SELECT status
	FROM ".$GLOBALS['prefix_lms']."_courseuser
	WHERE idUser = '".$idUser."'
		AND idCourse = '".(int)$_SESSION['idCourse']."'";
	list($status) = sql_fetch_row(sql_query($query));

	$out->add( $form->openForm('modstatus', 'index.php?modname=stats&amp;op=upstatus') );

	$out->add( $form->getHidden( 'idUser', 'idUser', $idUser ) );

	$cs = new CourseSubscribe_Manager();
	$arr_status = $cs->getUserStatus();
	$out->add( $form->getDropdown( 	$lang->def('_STATUS'),
									'status',
									'status',
									$arr_status ,
									$status ) );

	$out->add( $form->getButton('gofilter', 'gofilter', $lang->def('_SAVE')) );
	$out->add( $form->closeForm() );
	$out->add( '</div>' );
}

function upstatus() {
	funAccess('statuser', 'OP');

	if( !saveTrackStatusChange($_POST['idUser'], $_SESSION['idCourse'] , $_POST['status']) ) {
		UiFeedback::error(_OPERATION_FAILURE);
		return;
	}
	Util::jump_to('index.php?modname=stats&op=statuser');
}

function exportTxt() {
	require_once(_base_.'/lib/lib.download.php' );

	$id_quest = importVar('id_quest', true, 0);

	$query_quest = "SELECT id_quest, title_quest" .
					" FROM ".$GLOBALS['prefix_lms']."_pollquest" .
					" WHERE id_quest = '".$id_quest."'";

	$result_quest = sql_query($query_quest);

	list($id_quest, $title_quest) = sql_fetch_row($result_quest);

	$filename = str_replace('?', '', $title_quest).'.txt';

	$txt = $title_quest."\r\n"."\r\n";

	$query_answer = "SELECT more_info" .
					" FROM ".$GLOBALS['prefix_lms']."_polltrack_answer" .
					" WHERE id_quest = '".$id_quest."'";

	$result_answer = sql_query($query_answer);

	$separator = "--------------------\r\n";
	while (list($answer) = sql_fetch_row($result_answer))
		$txt .= $separator.$answer."\r\n";

	sendStrAsFile($txt, $filename);
}

switch( $GLOBALS['op'] ) {  // ---------------------------------------------------------------------
	case "statuser":
		statuserfilter();
	break;
	case "statoneuser":
		statoneuser();
	break;
	case "statcourse":
		statcourse();
	break;
	case "statitem":
		statitem();
	break;
	case "statoneuseroneitem":
		statoneuseroneitem();
	break;
	case "statoneuseroneitemdetail":
		statoneuseroneitemdetails();
	break;
	case "statoneuseroneitemhistory":
		statoneuseroneitemhistory();
	break;
	case "modstatus":
		modstatus();
	break;
	case "upstatus":
		upstatus();
	break;

	case "modpagel":
		modpagel();
	break;

	case "showsema":
		showsema();
	break;

	case "add_atvt": {
		add_edit_atvt();
	};break;

	case "edit_atvt": {
		add_edit_atvt("edit");
	};break;

	case "del_atvt": {
		confdel();
	};break;

	case "export_txt":
		exportTxt();
	break;
}

?>

