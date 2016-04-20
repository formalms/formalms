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

function organization_property(&$treeView, $idItem) {
	require_once(_base_.'/lib/lib.tab.php' );
	
	$tv = new TabView( 'organization_properties', '#' );
	$lang =& DoceboLanguage::createInstance('organization', 'lms');
	
	$tv->addTab( new TabElemDefault( 'prereqisites', $lang->def('_PREREQUISITES'), getPathImage().'organizations/prerequisites.gif' ) );
	$tv->addTab( new TabElemDefault( 'settings', $lang->def('_PROPERTIES'), getPathImage().'organizations/settings1.gif' ) );
	//$tv->addTab( new TabElemDefault( 'catalogation', $lang->def('_CATEGORIZATION'), getPathImage().'standard/edit.png' ) );
	
	$tv->parseInput( $_POST, $_POST );
	if( $tv->getActiveTab() == '' )
		$tv->setActiveTab('prereqisites');
	
	require_once( _base_.'/lib/lib.form.php' );
	$form = new Form();
	
	$GLOBALS['page']->add($form->openForm('properties' , 'index.php?modname=storage&amp;op=display'));
	
	$GLOBALS['page']->add($form->getHidden(	$tv->_getStateId(),
											$tv->_getStateId(),
											$tv->getActiveTab()));
	
	/* The form contains the tab buttons and all others input
	 * In the method extendedParsing of the Org_TreeView object 
	 * it's controlled the hidden input named 'stay_on_properties'.
	 * When this input was found the treeView set op to properties so
	 * system go back in this function. But after the control of
	 * 'stay_on_properties' it's controlled the org_properties_ok and
	 * org_properties_cancel; if one of this was found the op of the 
	 * treeView object is set to 'display' and visualization go back 
	 * to area
	 */
	$GLOBALS['page']->add( $form->getHidden('stay_on_properties', 'stay_on_properties', 'stay_on_properties') );
	
	/* when 'stay_on_properties' was found the contextId it's set to 
	 * 'idItem' input
	 */
	$GLOBALS['page']->add( $form->getHidden('idItem', 'idItem', $idItem) );
	
	$GLOBALS['page']->add( $tv->printTabView_Begin("",FALSE) );

	switch( $tv->getActiveTab() ) {
		case 'prereqisites':
			organization_property_prereqisites($treeView, $idItem, $form, $lang);
		break;
		case 'settings':
			$GLOBALS['page']->add($form->openElementSpace('padding05') );
			organization_property_settings($treeView, $idItem, $form, $lang);
			$GLOBALS['page']->add($form->closeElementSpace() );
		break;
		case 'catalogation':
			$GLOBALS['page']->add($form->openElementSpace('padding05') );
			organization_property_catalogation($treeView, $idItem, $form, $lang);
			$GLOBALS['page']->add($form->closeElementSpace() );
		break;
	}
	
	$GLOBALS['page']->add( $tv->printTabView_End() );
	$GLOBALS['page']->add($form->openButtonSpace());
	$GLOBALS['page']->add( Form::getButton("org_properties_ok","org_properties_ok",$lang->def( '_CONFIRM' )), 'content' );
	$GLOBALS['page']->add( Form::getButton("org_properties_cancel","org_properties_cancel",$lang->def( '_CANCEL' )), 'content' );
	$GLOBALS['page']->add($form->closeButtonSpace());
	$GLOBALS['page']->add($form->closeForm());
	
}

function organization_property_prereqisites(&$treeView, $idItem, &$form, &$lang) {
	$folder = $treeView->tdb->getFolderById( $idItem );
	$values = organization_property_common($treeView, $idItem, $form, $lang, $folder);
	
	// print tree with check
	$GLOBALS['page']->add( $form->getOpenFieldset($lang->def('_PREREQUISITES')));
	$treeView->selector_mode = TRUE;
	$treeView->simple_selector = TRUE;
	$treeView->itemDisabled = array($idItem);
	$GLOBALS['page']->add( $treeView->load() );
	$GLOBALS['page']->add( $form->getCloseFieldset() );

	$GLOBALS['page']->add( $form->getHidden('selfPrerequisites', 'selfPrerequisites', $values['selfPrerequisites']) );
	$GLOBALS['page']->add( $form->getHidden('isTerminator', 'isTerminator', $values['isTerminator']) );
	$GLOBALS['page']->add( $form->getHidden('visibility', 'visibility', $values['visibility']) );
	$GLOBALS['page']->add( $form->getHidden('milestone', 'milestone', $values['milestone']) );
	$GLOBALS['page']->add( $form->getHidden('version', 'version', $values['version']) );
	$GLOBALS['page']->add( $form->getHidden('difficult', 'difficult', $values['difficult']) );
	$GLOBALS['page']->add( $form->getHidden('description', 'description', $values['description']) );
	$GLOBALS['page']->add( $form->getHidden('language', 'language', $values['language']) );
	$GLOBALS['page']->add( $form->getHidden('resource', 'resource', $values['resource']) );
	$GLOBALS['page']->add( $form->getHidden('objective', 'objective', $values['objective']) );
	
	$GLOBALS['page']->add( $form->getHidden('obj_width', 'obj_width', $values['obj_width']) );
	$GLOBALS['page']->add( $form->getHidden('obj_height', 'obj_height', $values['obj_height']) );

	$GLOBALS['page']->add( $form->getHidden('publish_for', 'publish_for', $values['publish_for']) );

	$GLOBALS['page']->add( $form->getHidden('publish_from', 'publish_from', $values['publish_from']) );
	$GLOBALS['page']->add( $form->getHidden('publish_to', 'publish_to', $values['publish_to']) );

}

function organization_property_settings(&$treeView, $idItem, &$form, &$lang) {
	$folder = $treeView->tdb->getFolderById( $idItem );
	
	$values = organization_property_common($treeView, $idItem, $form, $lang, $folder);
	
	$GLOBALS['page']->add( $treeView->printState() );
	$GLOBALS['page']->add( $form->getHidden('version', 'version', $values['version']) );
	$GLOBALS['page']->add( $form->getHidden('difficult', 'difficult', $values['difficult']) );
	$GLOBALS['page']->add( $form->getHidden('description', 'description', $values['description']) );
	$GLOBALS['page']->add( $form->getHidden('language', 'language', $values['language']) );
	$GLOBALS['page']->add( $form->getHidden('resource', 'resource', $values['resource']) );
	$GLOBALS['page']->add( $form->getHidden('objective', 'objective', $values['objective']) );

	$GLOBALS['page']->add( 
		$form->getRadioSet( $lang->def( '_HIDDEN' ), 
							'visibility', 
							'visibility', 
							array( 	$lang->def( '_NO' ) => "1",
									$lang->def( '_YES' ) => "0"),
							$values['visibility']) );
	
	
	$isFolder = ($values['objectType'] === '');
	if( !$isFolder ) {
		if(Docebo::course()->getValue('course_type') == 'classroom') {
			$GLOBALS['page']->add( $form->getDropdown( $lang->def( '_PUBLISH' ),
												"publish_for",
												"publish_for",
												array(
													PF_ALL_USER => $lang->def('_ALL'),
													PF_TEACHER => Lang::t('_LEVEL_6', 'levels'),
													PF_ATTENDANCE => $lang->def('_ATTENDANCE')
												),
												isset($values['publish_for'])?$values['publish_for']:"")
							);
		} else {

			$GLOBALS['page']->add( $form->getHidden('publish_for', 'publish_for', PF_ALL_USER) );
		}
		$GLOBALS['page']->add( $form->getDatefield( $lang->def( '_PUBLISH_FROM' ), 
											"publish_from", 
											"publish_from", 
											isset($values['publish_from'])?$values['publish_from']:"") 
						);
	
		$GLOBALS['page']->add( $form->getDatefield( $lang->def( '_PUBLISH_TO' ), 
											"publish_to", 
											"publish_to", 
											isset($values['publish_to'])?$values['publish_to']:"") 
						);
		
		
		$orgselfprerequisites = array( $lang->def( '_UNTIL_COMPLETED' ) => "incomplete",
											$lang->def( '_UNLIMITED' ) => "*",
											$lang->def( '_ONLY_ONCE' ) => "NULL" );
		
		$GLOBALS['page']->add($form->getOpenCombo( $lang->def( '_PLAY_CHANCE' ) ) );
		foreach($orgselfprerequisites as $name => $id ) {
			
			$GLOBALS['page']->add(
				$form->getRadio( $name, 
									'selfPrerequisites_'.$id, 
									'selfPrerequisites', 
									$id,
									($values['selfPrerequisites'] == $id)) 
			);
		}
		$GLOBALS['page']->add($form->getCloseCombo() );
		/*$GLOBALS['page']->add( 
				$form->getRadioSet( $lang->def( '_PLAY_CHANCE' ),
									'selfPrerequisites', 
									'selfPrerequisites', 
									array( $lang->def( '_UNTIL_COMPLETED' ) => "incomplete",
											$lang->def( '_UNLIMITED' ) => "*",
											$lang->def( '_ONLY_ONCE' ) => "NULL"	 ),
									$values['selfPrerequisites']) );
									*/
		$GLOBALS['page']->add( $form->getBreakRow() );
		
		// ------------------- terminator
		$GLOBALS['page']->add( 
				$form->getRadioSet( $lang->def( '_ORGISTERMINATOR' ), 
									'isTerminator', 
									'isTerminator', 
									array( 	$lang->def( '_NO' ) => "0",
											$lang->def( '_YES' ) => "1"),
									$values['isTerminator']) );

		$GLOBALS['page']->add( $form->getBreakRow() );
	
		// ---- custom LO parameters
		$lo = createLO(	$values['objectType'], 
						$values['idResource'], 
						$values['idParam'], 
						array() );
		if( $lo->canBeMilestone() ) {
			if( $values['milestone'] == '' ) 
				$values['milestone'] = '-';
			$arr_milestones = array( 	$lang->def( '_NO') => '-',
										$lang->def( '_ORGMILESTONE_START') => 'start',
										$lang->def( '_ORGMILESTONE_END') => 'end' );
			$GLOBALS['page']->add( 
					$form->getRadioSet( $lang->def( '_ORGMILESTONE' ), 
										'milestone', 
										'milestone', 
										$arr_milestones , 
										$values['milestone']) );
			$GLOBALS['page']->add( '<br />' );

			$startFolder = $treeView->tdb->getMilestone( 'start', (int)$_SESSION['idCourse'] );
			$endFolder = $treeView->tdb->getMilestone( 'end', (int)$_SESSION['idCourse'] );
			$jsOut = '';
			if(  $startFolder !== FALSE && $startFolder->id != $folder->id ) {
				$GLOBALS['page']->add( '<div class="form_line_l">'."\n"
										.'<div class="label_effect">'.$lang->def( '_ORGMILESTONE_PREVSTART').'</div>'
										.'<div class="grouping">' 
										.$startFolder->otherValues[REPOFIELDTITLE]
										.'</div></div>'
										);
				$GLOBALS['page']->add( '<br />' );
				$jsOut .= 'if( milestone_value == "start" )'
							.'if( !window.confirm("'
							.Util::unhtmlentities(addslashes(str_replace( '%LOTitle%',
										$startFolder->otherValues[REPOFIELDTITLE], 
										$lang->def( '_ORGMILESTONE_PREVSTART_REMOVED'))))
							.'") )'."\n" 
							.'		return false;'."\n";
			}
			if(  $endFolder !== FALSE && $endFolder->id != $folder->id) {
				$GLOBALS['page']->add( '<div class="form_line_l">'."\n"
										.'<div class="label_effect">'.$lang->def( '_ORGMILESTONE_PREVEND').'</div>'
										.'<div class="grouping">' 
										.$endFolder->otherValues[REPOFIELDTITLE]
										.'</div></div>'
										);
				$GLOBALS['page']->add( '<br />' );
				$jsOut .= 'if( milestone_value == "end" )'
							.'if( !window.confirm("'
							.Util::unhtmlentities(addslashes(str_replace( '%LOTitle%',
										$endFolder->otherValues[REPOFIELDTITLE], 
										$lang->def( '_ORGMILESTONE_PREVEND_REMOVED'))))
							.'") )'."\n" 
							.'		return false;'."\n";
			}	
			/*$GLOBALS['page']->add( '<script type="text/javascript">'."\n"
									.'//<![CDATA['."\n"
									.'var form_prop = null;'."\n"
									.'var milestone_field = null;'."\n"
									.'var milestone_prev_index = 0;'."\n"
									.'window.onload = function() {'."\n"
									.'	form_prop = document.getElementById( "properties" );'."\n"
									.'	milestone_field = form_prop.milestone;'."\n"
									.'	for( var i = 0; i < milestone_field.length; i++ ) {'."\n"
									.'		if( milestone_field[i].checked ) '."\n"
									.'			milestone_prev_index = i;'."\n"
									.'  	milestone_field[i].onchange = function() {'
									.'	}'."\n"
									.'	form_prop.onsubmit = function() {'."\n"
									.'		var milestone_field = form_prop.milestone;'."\n"
									.'		var milestone_value = "-";'."\n"
									.'		for( var i = 0; i < milestone_field.length; i++ ) {'."\n"
									.'			if( milestone_field[i].checked ) '."\n"
									.'				milestone_value = milestone_field[i].value;'."\n"
									.'		}'."\n"
									.'  //alert( milestone_value ); ');
			$GLOBALS['page']->add( $jsOut );
			$GLOBALS['page']->add( '		return true;'."\n"
									.'	}'."\n"
									.'}'."\n"
									.'//]]>'."\n"
									.'</script>'."\n" );*/
		} else {
			$GLOBALS['page']->add( ' <input type="hidden" value="-" id="milestone" name="milestone" />' );
		}
		
		$arrParamsInfo = $lo->getParamInfo();
		if( $arrParamsInfo !== FALSE ) {
			require_once($GLOBALS['where_lms'].'/lib/lib.param.php');
			$param_values = getLOParamArray( $values['idParam'] );
			if( is_callable( array($lo,'renderCustomSettings') ) ) {
				$GLOBALS['page']->add( $lo->renderCustomSettings($param_values,
																 $form,
																 $lang
																)
										);
			} else {
				while( $param = current( $arrParamsInfo ) ) {
					//$GLOBALS['page']->add( '<label for="'.$param['param_name'].'">'.$param['label'].'</label>' );
					if( isset($_POST[$param['param_name']])) {
						$pval = $_POST[$param['param_name']];
					} else if ( isset($param_values[$param['param_name']]) ) {
						$pval = $param_values[$param['param_name']];
					} else {
						$pval = '';
					}
					$GLOBALS['page']->add( $form->getTextfield( $param['label'], 
																$param['param_name'], 
																$param['param_name'], 
																'255', 
																$pval ) );				
					//$GLOBALS['page']->add( ' <input type="text" value="'.$pval.'" name="'.$param['param_name'].'" /><br />' );
					next( $arrParamsInfo );
				}
			}
			$GLOBALS['page']->add( ' <input type="hidden" value="1" name="customParam" /><br />' );			
		}
	} else {
		$GLOBALS['page']->add( ' <input type="hidden" value="'.$values['isTerminator'].'" id="isTerminator" name="isTerminator" />' );
		$GLOBALS['page']->add( ' <input type="hidden" value="" id="selfPrerequisites" name="selfPrerequisites" />' );
		$GLOBALS['page']->add( ' <input type="hidden" value="-" id="milestone" name="milestone" />' );
	}
	
	if(isset($lo) && get_class($lo) == "Learning_ScormOrg") {
		$GLOBALS['page']->add( $form->getTextfield( $lang->def( '_WIDTH' ), 
												"obj_width", 
												"obj_width", 
												4, 
												isset($values['obj_width'])?$values['obj_width']:"") 
							);
		
		$GLOBALS['page']->add( $form->getTextfield( $lang->def( '_HEIGHT' ), 
												"obj_height", 
												"obj_height", 
												4, 
												isset($values['obj_height'])?$values['obj_height']:"") 
							);
	}
}

function organization_property_catalogation(&$treeView, $idItem, &$form, &$lang) {
	$folder = $treeView->tdb->getFolderById( $idItem );
	$values = organization_property_common($treeView, $idItem, $form, $lang, $folder);

	$GLOBALS['page']->add( $treeView->printState() );
	$GLOBALS['page']->add( $form->getHidden('selfPrerequisites', 'selfPrerequisites', $values['selfPrerequisites']) );
	$GLOBALS['page']->add( $form->getHidden('isTerminator', 'isTerminator', $values['isTerminator']) );
	$GLOBALS['page']->add( $form->getHidden('visibility', 'visibility', $values['visibility']) );
	$GLOBALS['page']->add( $form->getHidden('description', 'description', $values['description']) );
	$GLOBALS['page']->add( $form->getHidden('milestone', 'milestone', $values['milestone']) );
	
	$GLOBALS['page']->add( $form->getHidden('obj_width', 'obj_width', $values['obj_width']) );
	$GLOBALS['page']->add( $form->getHidden('obj_height', 'obj_height', $values['obj_height']) );
	$GLOBALS['page']->add( $form->getHidden('publish_for', 'publish_for', $values['publish_for']) );
	$GLOBALS['page']->add( $form->getHidden('publish_from', 'publish_from', $values['publish_from']) );
	$GLOBALS['page']->add( $form->getHidden('publish_to', 'publish_to', $values['publish_to']) );

	$GLOBALS['page']->add( $form->getTextfield( $lang->def( '_VERSION' ), 
												"version", 
												"version", 
												8, 
												isset($values['version'])?$values['version']:"1.0"
												) 
							);
	
	$arr_diff = array( 	'1'	=>	$lang->def( '_DIFFICULT_VERYEASY' ),
						'2'	=>	$lang->def( '_DIFFICULT_EASY' ),
						'3'	=>	$lang->def( '_DIFFICULT_MEDIUM' ),
						'4'	=>	$lang->def( '_DIFFICULT_DIFFICULT'),
						'5'	=>	$lang->def( '_DIFFICULT_VERYDIFFICULT')
					);
	$selDiff = isset($values['difficult'])?$values['difficult']:"";
	
	$GLOBALS['page']->add( $form->getDropdown(	$lang->def( '_DIFFICULTY' ),
												"difficult", 
												"difficult", 
												$arr_diff , 
												$selDiff 
											)				
						);

						
	$langArray = Docebo::langManager()->getAllLangCode();
	if( isset($values['language']) )
		$selLang = $values['language'];
	else
		$selLang = getDefaultLang();

	$GLOBALS['page']->add( $form->getDropdown(	$lang->def( '_LANGUAGE' ), 
												"language", 
												"language", 
												$langArray , 
												$selLang 
											)				
						);
						
	
	$GLOBALS['page']->add( $form->getTextfield( $lang->def( '_RESOURCE' ), 
												"resource", 
												"resource", 
												255, 
												isset($values['resource'])?$values['resource']:"http://"
												) 
							);

	$GLOBALS['page']->add( $form->getSimpleTextarea(	$lang->def( '_OBJECTIVE' ), 
														"objective",
														"objective", 
														isset($values['objective'])?$values['objective']:""
													) 
						);

}

function organization_property_common(&$treeView, $idItem, &$form, &$lang, &$folder) {

	// extract info from POST data
	$values = array();
	$data = $folder->otherValues;
	$values['title'] = ( isset($_POST['title']) ? stripslashes($_POST['title']) : $data[REPOFIELDTITLE] );
	
	if( isset($_POST['stay_on_properties']) ) {
		$values['selfPrerequisites'] = $_POST['selfPrerequisites'];
		$values['isTerminator'] = $_POST['isTerminator'];
		$values['objectType'] = $_POST['objectType'];
		$values['idResource'] = $_POST['idResource'];
		$values['idParam'] = $_POST['idParam'];
		$values['visibility'] = $_POST['visibility'];
		$values['milestone'] = $_POST['milestone'];
		$values['version'] = $_POST['version'];
		$values['difficult'] = $_POST['difficult'];
		$values['description'] = $_POST['description'];
		$values['language'] = $_POST['language'];
		$values['resource'] = $_POST['resource'];
		$values['objective'] = $_POST['objective'];	
			
		$values['obj_width'] = $_POST['obj_width'];	
		$values['obj_height'] = $_POST['obj_height'];
		$values['publish_for'] = $_POST['publish_for'];
		$values['publish_from'] = $_POST['publish_from'];	
		$values['publish_to'] = $_POST['publish_to'];	
	} else {
		$arrPre = $data[ORGFIELDPREREQUISITES];
		$values['selfPrerequisites'] = $treeView->tdb->extractSelfPrerequisites( $idItem, $arrPre );
	
		$prerequisites = $treeView->tdb->extractPrerequisites( $idItem, $data[ORGFIELDPREREQUISITES] );
		$treeView->itemSelectedMulti = explode(',',$prerequisites);
		
		$values['title'] = $data[REPOFIELDTITLE];
		$values['isTerminator'] = $data[ORGFIELDISTERMINATOR];	
		$values['objectType'] = $data[REPOFIELDOBJECTTYPE];
		$values['idResource'] = $data[REPOFIELDIDRESOURCE];
		$values['idParam'] = $data[ORGFIELDIDPARAM];
		$values['visibility'] = $data[ORGFIELDVISIBLE];
		$values['milestone'] = $data[ORGFIELDMILESTONE];
		$values['version'] = $data[REPOFIELDVERSION];
		$values['difficult'] = $data[REPOFIELDDIFFICULT];
		$values['description'] = $data[REPOFIELDDESCRIPTION];
		$values['language'] = $data[REPOFIELDLANGUAGE];
		$values['resource'] = $data[REPOFIELDRESOURCE];
		$values['objective'] = $data[REPOFIELDOBJECTIVE];
		
		$values['obj_width'] = $data[ORGFIELD_WIDTH];
		$values['obj_height'] = $data[ORGFIELD_HEIGHT];
		$values['publish_for'] = $data[ORGFIELD_PUBLISHFOR];
		$values['publish_from'] = Format::date($data[ORGFIELD_PUBLISHFROM], 'date');	
		$values['publish_to'] = Format::date($data[ORGFIELD_PUBLISHTO], 'date');
	}
	
	$GLOBALS['page']->add( $form->getHidden('objectType', 'objectType', $values['objectType']) );
	$GLOBALS['page']->add( $form->getHidden('idResource', 'idResource', $values['idResource']) );
	$GLOBALS['page']->add( $form->getHidden('idParam', 'idParam', $values['idParam']) );
	
	if($data[REPOFIELDOBJECTTYPE] == 'scormorg' || $data[REPOFIELDOBJECTTYPE] == '') {
		
		$GLOBALS['page']->add( $form->getTextfield( $lang->def('_TITLE'), 
													'title', 
													'title', 
													'255', 
													$values['title'] ) );	
	} else {
		
		$GLOBALS['page']->add( $form->getLineBox( $lang->def('_TITLE'),	$values['title'] ) );
	}
	return $values;
}

function organization_access( &$treeView, $idItem ) {

	require_once(_base_.'/lib/lib.form.php');
	require_once(_base_.'/lib/lib.userselector.php');
	require_once( $GLOBALS['where_lms'].'/lib/lib.repo.php' );
	$lang =& DoceboLanguage::createInstance('organization', 'lms');
	$folder = $treeView->tdb->getFolderById( $idItem );

	$user_select 	= new UserSelector();
	$user_select->learning_filter = 'course';
	$user_select->org_type = $folder->otherValues[REPOFIELDOBJECTTYPE];

	$aclManager = new DoceboACLManager();
	if( isset($_POST['okselector']) )
	{
		$treeView->tdb->__setAccess($idItem, $user_select->getSelection($_POST), Get::req('relation'));
		Util::jump_to( 'index.php?modname=storage' );
	}
	elseif( isset($_POST['cancelselector']) )
		Util::jump_to( 'index.php?modname=storage' );
	else
	{
		$user_select->show_user_selector = TRUE;
		$user_select->show_group_selector = TRUE;
		$user_select->show_orgchart_selector = FALSE;
		$user_select->show_fncrole_selector = FALSE;
		$user_select->id_org = $idItem;
		$user_select->nFields = 2;

		$temp = $treeView->tdb->__getAccess($idItem);
		$user_select->resetSelection($temp);

		cout(getTitleArea(array('index.php?modname=storage&amp;org_access=1&amp;idItem='.$idItem.'&amp;stayon=1'=>$lang->def( '_ORG_ACCESS' ))) );
		cout('<div class="std_block">'.$lang->def( '_ASSIGN_USERS' ).':&nbsp;<span class="text_bold">'.$folder->otherValues[REPOFIELDTITLE].'</span>');

		cout($user_select->loadSelector('index.php?modname=storage&org_access=1&idItem='.$idItem.'&stayon=1'));
		cout('</div>');
	}
  
}

?>
