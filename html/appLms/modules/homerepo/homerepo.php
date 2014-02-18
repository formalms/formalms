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

define("REPOFIELDIDOWNER", 13);

class HomerepoDirDb extends RepoDirDb {
	var $idOwner = 0;
	function HomerepoDirDb( $table_name, $idOwner ) {
		$this->idOwner = $idOwner;
		parent::RepoDirDb( $table_name );
	}
	
	function _getOtherFields($tname = FALSE) {
		if( $tname === FALSE )
			return parent::_getOtherFields(FALSE). ", idOwner";
		else
			return parent::_getOtherFields($tname).", ".$tname.".idOwner ";
	}
	
	function _getOtherValues() {
		return parent::_getOtherValues().", '".(int)$this->idOwner."' ";
	}
	
	function _getOtherUpdates() {
		return parent::_getOtherUpdates().", idAuthor='".(int)$this->idOwner."'";
	}
	
	function _getFilter($tname = FALSE) {
		$result = "";
		if( $tname === FALSE ) {
			$result .= " AND (idOwner = '".(int)$this->idOwner."') ";
		} else {
			$result .= " AND (".$tname.".idOwner = '".(int)$this->idOwner."') ";
		}
		return parent::_getFilter($tname).$result;
	}
}

function homerepo(&$treeView) {
	
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
			homerepo_opfolder( $treeView, $treeView->op );
		break;
		case 'import':
			homerepo_import( $treeView );
		break;
		case 'createLO':
			// Save state in session
			global $modname;
			$GLOBALS['page']->add($treeView->LOSelector($modname), 'content' );
		break;
		case 'createLOSel':
			global $modname;
			$lo = createLO( $_POST['radiolo'] );
			$lo->create( 'index.php?modname'.$modname.'&amp;op=created' );
		break;
		case 'properties':
		case 'properties_accessgroups_remove':
		case 'properties_accessgroups_add':
		case 'properties_accessusers_remove':
		case 'properties_accessusers_add':
			homerepo_itemproperties( $treeView, $_POST, $treeView->opContextId );
		break;
		case 'treeview_error':
			homerepo_showerror($treeView);
		break;
		case 'save':
			$treeView->tdb->modifyItem( $_POST );
		default:
			homerepo_display( $treeView );
		break;
	}
	
}

function homerepo_display( &$treeView ) {
	// print conainer div and form
	global $modname, $op;
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( '<form id="homereposhow" method="post"'
		.' action="index.php?modname='.$modname.'&amp;op='.$op.'"'
		.' >'."\n"
		.'<input type="hidden" id="authentic_request_hrs" name="authentic_request" value="'.Util::getSignature().'" />');
	
	if( funAccess('moditem','MOD', TRUE, 'homerepo' ) ) {
		$treeView->withActions = TRUE;
	} else {
		$tdb = $treeView->getTreeDb();
	}
	$GLOBALS['page']->add( $treeView->load() );
	if( funAccess('moditem','MOD', TRUE, 'homerepo' ) ) {
		$GLOBALS['page']->add( $treeView->loadActions() );
	}

	$GLOBALS['page']->add( '</form>' );
	// print form for import action
	$GLOBALS['page']->add( '</div>' );
}

function homerepo_opfolder(&$treeView, $op) {
	global $modname;
	$GLOBALS['page']->add( '<div class="std_block">' );
	$GLOBALS['page']->add( '<form name="homereponewfolder" method="post"'
	.' action="index.php?modname='.$modname.'&amp;op=homerepo"'
	.' >'."\n"
	.'<input type="hidden" id="authentic_request_hrs" name="authentic_request" value="'.Util::getSignature().'" />');
	
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


function homerepo_itemproperties( &$treeView, &$arrayData, $idItem ) {
	//function loadFields( $arrayData, &$lo, $idLO ) {
	$lang =& DoceboLanguage::createInstance('homerepo', 'lms');
	$langClassification =& DoceboLanguage::createInstance( 'classification', 'lms');
	
	$GLOBALS['page']->add( '<form id="manHomerepo" method="post"'
		.' action="index.php?'.$_SERVER['QUERY_STRING'].'"'
		.' >'."\n"
		.'<input type="hidden" id="authentic_request_hrs" name="authentic_request" value="'.Util::getSignature().'" />');
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
			$folder = $treeView->tdb->getFolderById($idItem);
			
			$GLOBALS['page']->add( '<input type="hidden" name="idItem" id="idItem" value="'.$idItem.'" />' );
			$title = $folder->otherValues[REPOFIELDTITLE];
			$arrayData['version'] = $folder->otherValues[REPOFIELDVERSION];
			$arrayData['difficult'] = $folder->otherValues[REPOFIELDDIFFICULT];
			$arrayData['language'] = $folder->otherValues[REPOFIELDLANGUAGE];
			$arrayData['resource'] = $folder->otherValues[REPOFIELDRESOURCE];
			$arrayData['objective'] = $folder->otherValues[REPOFIELDOBJECTIVE];
		}
	} else {
		$GLOBALS['page']->add( '<input type="hidden" name="idItem" id="idItem" value="'.$idItem.'" />' );
		$title = $_POST['title'];
	}
	
	// ==========================================================
	$GLOBALS['page']->add( '<input type="hidden" name="title" id="title" value="'.$title.'" />' );
	$GLOBALS['page']->add( '<div class="ObjectForm">' );

	$GLOBALS['page']->add( '<span class="mainTitle">'.$langClassification->def( '_CATEGORIZATION' ). ' ' . $title .'</span><br /><br />' );

	$GLOBALS['page']->add( '</div>' );
		//-------------------------------------------------
/*		.'<div class="title">'._CATEGORY.'</div>'
		.'<div class="content">'
		.'<select name="idCategory">';
		
	if( isset($arrayData['idCategory']) )
		$selectedIdCat = $arrayData['idCategory'];
	else
		$selectedIdCat = "";
	
	while(list($idCat, $catTitle) = sql_fetch_row($reCategory)) {
		if( $selectedIdCat == $idCat )
			echo '<option value="'.$idCat.'" selected >'.$catTitle.'</option>';
		else
			echo '<option value="'.$idCat.'">'.$catTitle.'</option>';
	}
	echo '</select> ( '.sql_num_rows($reCategory).' '._DISP.')'
		.'</div>'*/
		//-------------------------------------------------
	$GLOBALS['page']->add( 	'<div class="title">'.$langClassification->def( '_VERSION' ).'</div>'
							.'<div class="content">' );
	
	if( isset($arrayData['version']) )
		$GLOBALS['page']->add( '<input type="text" name="version" maxlength="8" size="10" value="'.$arrayData['version'].'" />' );
	else
		$GLOBALS['page']->add( '<input type="text" name="version" maxlength="8" size="10" value="1.0" />' );
		
	$GLOBALS['page']->add( '</div>'
		//-------------------------------------------------
		.'<div class="title">'.$langClassification->def( '_DIFFICULTY' ).'</div>'
		.'<div class="content">'
		.'<select name="difficult">');

	if( isset($arrayData['difficult']) ) {
		$selDiff = $arrayData['difficult'];
		switch($selDiff) {
			case '_DIFFICULT_VERYEASY': $selDiff = "1"; break;
			case '_DIFFICULT_EASY': $selDiff = "2"; break;
			case '_DIFFICULT_MEDIUM': $selDiff = "3"; break;
			case '_DIFFICULT_DIFFICULT': $selDiff = "4"; break;
			case '_DIFFICULT_VERYDIFFICULT': $selDiff = "5"; break;
		}	
	} else
		$selDiff = "";
	
		$GLOBALS['page']->add( 
				'<option value="1" '.(($selDiff=="1")?'selected':'').' >'.$langClassification->def( '_DIFFICULT_VERYEASY' ).'</option>'
				.'<option value="2" '.(($selDiff=="2")?'selected':'').' >'.$langClassification->def( '_DIFFICULT_EASY' ).'</option>'
				.'<option value="3" '.(($selDiff=="3")?'selected':'').' >'.$langClassification->def( '_DIFFICULT_MEDIUM' ).'</option>'
				.'<option value="4" '.(($selDiff=="4")?'selected':'').' >'.$langClassification->def( '_DIFFICULT_DIFFICULT' ).'</option>'
				.'<option value="5" '.(($selDiff=="5")?'selected':'').' >'.$langClassification->def( '_DIFFICULT_VERYDIFFICULT' ).'</option>'
			.'</select>'
			.'</div>'
		);
		//-------------------------------------------------
		/*.'<div class="title">'._DESCRIPTION.'</div>'
		.'<div class="content">'
		.'<div id="breakfloat">'
			.'<textarea id="description" name="description" rows="10" cols="75"></textarea></div>'
		.'</div>'*/
		//-------------------------------------------------	
	$GLOBALS['page']->add(  '<div class="title">'.$langClassification->def( '_LANGUAGE' ).'</div>'
		.'<div class="content">'
		.'<select name="language">' );
	if( isset($arrayData['language']) )
		$selLang = $arrayData['language'];
	else
		$selLang = $defaultLanguage;

		while(list( ,$valueLang)= each($langArray)) {
			$GLOBALS['page']->add( '<option value="'.$valueLang.'"' );
			if($valueLang == $selLang) 
				$GLOBALS['page']->add( ' selected="selected"' );
			$GLOBALS['page']->add( '>'.$valueLang.'</option>' );
		}
	$GLOBALS['page']->add( '</select>'
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'.$langClassification->def( '_RESOURCE' ).'</div>'
		.'<div class="content">' );
	if( isset($arrayData['resource']) )
		$GLOBALS['page']->add( '<input type="text" name="resource" maxlength="255" size="60" value="'.$arrayData['resource'].'" />' );
	else
		$GLOBALS['page']->add( '<input type="text" name="resource" maxlength="255" size="60" value="http://" />' );
	$GLOBALS['page']->add( '</div>'
		//-------------------------------------------------
		.'<div class="title">'.$langClassification->def( '_OBJECTIVE' ).'</div>'
		.'<div class="content">');
	if( isset($arrayData['objective']) )
		$GLOBALS['page']->add( '<textarea name="objective" rows="6" cols="75">'.$arrayData['objective'].'</textarea>' );
	else
		$GLOBALS['page']->add( '<textarea name="objective" rows="6" cols="75"></textarea>' );
		
	$GLOBALS['page']->add( '<br />' );
	$GLOBALS['page']->add(  '<img src="'.$treeView->_getSaveImage().'" alt="'.$lang->def( '_SAVE' ).'" /> '
		.'<input type="submit" value="'.$lang->def( '_SAVE' ).'" class="LVAction"'
		.' name="'.$treeView->_getOpSaveFile().'" />');
	$GLOBALS['page']->add(  ' <img src="'.$treeView->_getCancelImage().'" alt="'.$treeView->_getCancelAlt().'" />'
		.'<input type="submit" class="LVAction" value="'.$treeView->_getCancelLabel().'"'
		.' name="'.$treeView->_getCancelId().'" id="'.$treeView->_getCancelId().'" />');			
	$GLOBALS['page']->add( '</div>' );
	$GLOBALS['page']->add( '</div>' );
	$GLOBALS['page']->add( '</form>' );
}

function import( &$treeView ) {	
	homerepo_import($treeView);
}


}

?>
