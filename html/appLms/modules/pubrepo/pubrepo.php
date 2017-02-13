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

if(!Docebo::user()->isAnonymous()) {

require_once( $GLOBALS['where_lms'].'/lib/lib.repo.php' );

function pubrepo(&$treeView) {
	
	// manage items addition
	if( isset($_POST['_repoproperties_save']) ) {
		$treeView->tdb->modifyItem( $_POST );
		$treeView->op = '';
	} else if( isSet( $_POST['_repoproperties_cancel'] ) ) {
		$treeView->op = '';
	}

	switch( $treeView->op ) {
		case 'newfolder':
		case 'renamefolder':
		case 'movefolder':
		case 'deletefolder':
			pubrepo_opfolder( $treeView, $treeView->op );
		break;
		case 'import':
			pubrepo_import( $treeView );
		break;
		case 'properties':
		case 'properties_accessgroups_remove':
		case 'properties_accessgroups_add':
		case 'properties_accessusers_remove':
		case 'properties_accessusers_add':
			pubrepo_itemproperties( $treeView, $_POST, $treeView->opContextId );
		break;
		case 'treeview_error':
			pubrepo_showerror($treeView);
		break;
		case 'save':
			$treeView->tdb->modifyItem( $_POST );
		default:
			pubrepo_display( $treeView );
		break;
	}
	
}

function pubrepo_display( &$treeView ) {
	// print conainer div and form
	global $modname, $op;
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( '<form id="pubreposhow" method="post"'
	.' action="index.php?modname='.$modname.'&op='.$op.'"'
	.' >'."\n"
	.'<input type="hidden" id="authentic_request_pubr" name="authentic_request" value="'.Util::getSignature().'" />' );
	
	if( funAccess('moditem','MOD', TRUE, 'pubrepo' ) ) {
		$treeView->withActions = TRUE;
	} else {
		$tdb = $treeView->getTreeDb();
	}
	$GLOBALS['page']->add( $treeView->load() );
	if( funAccess('moditem','MOD', TRUE, 'pubrepo' ) ) {
		$GLOBALS['page']->add( $treeView->loadActions() );
	}

	$GLOBALS['page']->add( '</form>' );
	// print form for import action
	$GLOBALS['page']->add( '</div>' );
}

function pubrepo_opfolder(&$treeView, $op) {
	global $modname;
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( '<form name="pubreponewfolder" method="post"'
	.' action="index.php?modname='.$modname.'&op=pubrepo"'
	.' >'."\n"
	.'<input type="hidden" id="authentic_request_pubr" name="authentic_request" value="'.Util::getSignature().'" />' );
	
	switch( $op ) {
		case 'newfolder':
			$GLOBALS['page']->add( $treeView->loadNewFolder() );
		break;
		case 'renamefolder':
			$GLOBALS['page']->add( $treeView->loadRenameFolder() );
		break;
		case 'movefolder':
			$GLOBALS['page']->add( $treeView->loadMoveFolder() );
		break;
		case 'deletefolder':
			$GLOBALS['page']->add( $treeView->loadDeleteFolder() );
		break;
	}
	
	$GLOBALS['page']->add( '</form>' );
	$GLOBALS['page']->add( '</div>' );
}

function pubrepo_import(&$treeView) {
	global $modname, $op;
	require_once( $GLOBALS['where_lms'].'/lib/lib.homerepo.php' );
	
	// ----------------------------------
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( '<form name="pubrepoimport" method="post"'
	.' action="index.php?modname='.$modname.'&op=import" >'."\n"
	.'<input type="hidden" id="authentic_request_pubr" name="authentic_request" value="'.Util::getSignature().'" />' );
		// call pubrepo visualization to select items to import
		$GLOBALS['page']->add( $treeView->printState() );
		$treeViewPR = manHomerepo(FALSE, TRUE, NULL, TRUE );
	
	$GLOBALS['page']->add( '</form>' );
	
	// ----------------------------------
	// then use an other form to submit back to organization op whit id of
	// selected items
	$GLOBALS['page']->add( '<form name="pubrepoimport" method="post"'
	.' action="index.php?modname='.$modname.'&op=pubrepo&import=1" >'."\n"
	.'<input type="hidden" id="authentic_request_pubr2" name="authentic_request" value="'.Util::getSignature().'" />' );
		$GLOBALS['page']->add( $treeView->printState() );
		$listView = $treeViewPR->getListView();
		$arrSelected = $listView->getIdSelectedItem(); 
		
		$GLOBALS['page']->add( '<input type="hidden" value="'
		.addslashes(serialize($arrSelected))
		.'" name="idSelectedObjects">' );
		$GLOBALS['page']->add( '<input type="submit" value="'._IMPORT.'" name="import">' );
	
	$GLOBALS['page']->add( '</form>' );
	$GLOBALS['page']->add( '</div>' );
}

function pubrepo_itemproperties( &$treeView, &$arrayData, $idItem ) {
	//function loadFields( $arrayData, &$lo, $idLO ) {
	$lang =& DoceboLanguage::createInstance('pubrepo', 'lms');
	$langClassification =& DoceboLanguage::createInstance( 'classification', 'lms');

	$GLOBALS['page']->add( '<form id="manHomerepo" method="post"'
		.' action="index.php?'.$_SERVER['QUERY_STRING'].'"'
		.' >'."\n"
	.'<input type="hidden" id="authentic_request_pubr" name="authentic_request" value="'.Util::getSignature().'" />');
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( $treeView->printState() );
	global $defaultLanguage;
	
	//including language
	//includeLang("classification");
	
	//finding category
	$reCategory = sql_query("
	SELECT idCategory, title 
	FROM ".$GLOBALS['prefix_lms']."_coursecategory
	ORDER BY title");

	//searching languages
	
	/*$langl = dir('menu/language/');
	while($ele = $langl->read())
		if(ereg("lang-",$ele)) {
			$langArray[] = ereg_replace("lang-","",ereg_replace(".php","",$ele));
		}
	closedir($langl->handle);
	sort($langArray);*/
	$langArray = Docebo::langManager()->getAllLangCode();

	if( !isset($_POST['idItem']) ) {
		if( $idItem !== NULL ) {
			$query = "SELECT idCategory, idAuthor,"
					." objectType, title, version, difficult,"
					." description, language, resource, objective"
					." FROM ".$GLOBALS['prefix_lms']."_repo"
					." WHERE idRepo='".(int)$idItem."'";
					
			$rs = sql_query( $query ) 
				or die( sql_error() );
			
			$arrayData = sql_fetch_assoc($rs);
			$GLOBALS['page']->add( '<input type="hidden" name="idItem" id="idItem" value="'.$idItem.'" />');
			$title = $arrayData['title'];
		} else {
			$title = $lo->getTitle();
		}
	} else {
		$GLOBALS['page']->add( '<input type="hidden" name="idItem" id="idItem" value="'.$idItem.'" />');
		$title = $_POST['title'];
	}
	
	// ==========================================================
	$GLOBALS['page']->add( '<input type="hidden" name="title" id="title" value="'.$title.'" />' );
	$GLOBALS['page']->add( '<div class="ObjectForm">' );

	$GLOBALS['page']->add( '<span class="mainTitle">'._CATEGORIZATION. ' ' . $title .'</span><br /><br />' );

	$GLOBALS['page']->add( '</div>' );

	$GLOBALS['page']->add( '<div class="title">'._VERSION.'</div>'
		.'<div class="content">');
	
	if( isset($arrayData['version']) )
		$GLOBALS['page']->add( '<input type="text" name="version" maxlength="8" size="10" value="'.$arrayData['version'].'" />' );
	else
		$GLOBALS['page']->add( '<input type="text" name="version" maxlength="8" size="10" value="1.0" />' );
		
	$GLOBALS['page']->add( '</div>'
		//-------------------------------------------------
		.'<div class="title">'._DIFFICULT.'</div>'
		.'<div class="content">'
		.'<select name="difficult">');

	if( isset($arrayData['difficult']) ) {
		$selDiff = $arrayData['difficult'];
		switch($selDiff) {
			case '_DIFFICULT_VERYEASY': $selDiff = "1"; break;
			case '_DIFFICULT_EASY': $selDiff = "2"; break;
			case '_DIFFICULT_MEDIUM': $selDiff = "3"; break;
			case '_DIFFICULT': $selDiff = "4"; break;
			case '_VERYDIFFICULT': $selDiff = "5"; break;
		}	
	} else
		$selDiff = "";
	
		$GLOBALS['page']->add( 
			 '<option value="1" '.(($selDiff=="1")?'selected':'').' >'._VERYEASY.'</option>'
			.'<option value="2" '.(($selDiff=="2")?'selected':'').' >'._EASY.'</option>'
			.'<option value="3" '.(($selDiff=="3")?'selected':'').' >'._MEDIUM.'</option>'
			.'<option value="4" '.(($selDiff=="4")?'selected':'').' >'._DIFFICULT.'</option>'
			.'<option value="5" '.(($selDiff=="5")?'selected':'').' >'._VERYDIFFICULT.'</option>'
		.'</select>'
		.'</div>');
		//-------------------------------------------------
		/*.'<div class="title">'._DESCRIPTION.'</div>'
		.'<div class="content">'
		.'<div id="breakfloat">'
			.'<textarea id="description" name="description" rows="10" cols="75"></textarea></div>'
		.'</div>'*/
		//-------------------------------------------------	
	$GLOBALS['page']->add(
		'<div class="title">'._LANGUAGE.'</div>'
		.'<div class="content">'
		.'<select name="language">' );
	if( isset($arrayData['language']) )
		$selLang = $arrayData['language'];
	else
		$selLang = $defaultLanguage;

		while(list( ,$valueLang)= each($langArray)) {
			$GLOBALS['page']->add( '<option value="'.$valueLang.'"' );
			if($valueLang == $selLang) $GLOBALS['page']->add( ' selected="selected"' );
			$GLOBALS['page']->add( '>'.$valueLang.'</option>' );
		}
	$GLOBALS['page']->add( 
		'</select>'
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._RESOURCE.'</div>'
		.'<div class="content">');
	if( isset($arrayData['resource']) )
		$GLOBALS['page']->add( '<input type="text" name="resource" maxlength="255" size="60" value="'.$arrayData['resource'].'" />' );
	else
		$GLOBALS['page']->add( '<input type="text" name="resource" maxlength="255" size="60" value="http://" />' );
	$GLOBALS['page']->add( 
		'</div>'
		//-------------------------------------------------
		.'<div class="title">'._OBJECTIVE.'</div>'
		.'<div class="content">');
	if( isset($arrayData['objective']) )
		$GLOBALS['page']->add( '<textarea name="objective" rows="6" cols="75">'.$arrayData['objective'].'</textarea>' );
	else
		$GLOBALS['page']->add( '<textarea name="objective" rows="6" cols="75"></textarea>' );
		
	$GLOBALS['page']->add( '<br />' );
	$GLOBALS['page']->add( 
		'<img src="'.$treeView->_getSaveImage().'" alt="'._SAVE.'" /> '
		.'<input type="submit" value="'._SAVE.'" class="LVAction"'
		.' name="'.$treeView->_getOpSaveFile().'" />');
	$GLOBALS['page']->add(  
		' <img src="'.$treeView->_getCancelImage().'" alt="'.$treeView->_getCancelAlt().'" />'
		.'<input type="submit" class="LVAction" value="'.$treeView->_getCancelLabel().'"'
		.' name="'.$treeView->_getCancelId().'" id="'.$treeView->_getCancelId().'" />');			
	$GLOBALS['page']->add( '</div>' );
	$GLOBALS['page']->add( '</div>' );
	$GLOBALS['page']->add( '</form>' );
}

function import( &$treeView ) {	
	pubrepo_import($treeView);
}


}

?>
