<?php

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

//require_once( 'core/class/class.listview.php' );
//require_once( 'core/class/class.treedb.php' );
//require_once( 'core/class/class.treeview.php' );

/**
 * Customization of DataRetriever for _pubrepo table
 **/
class PubRepo_DataRetriever extends DataRetriever {
	// id of selected folder in _pubrepo_dir (TreeView)
	// used in query composition to filter items
	var $idFolder = 0;
	// id (name) for opShowId operations
	// uset to compose name attribute of submit
	// button for showing
	var $opShowId = "";
	// id (name) for opPlayId operations
	// uset to compose name attribute of submit
	// button for playing
	var $opPlayId = "";
	// id (name) for opSelectId operations
	// uset to compose name attribute of submit
	// button for select
	var $opSelectId = "";
	// id of selected item
	// used in fetchRecord to change class of
	// html tag that contains the item
	var $selectedIdObject = -1;
	
	// set the selected id 
	function setSelectedObject( $selectedIdObject ) {
		$this->selectedIdObject = $selectedIdObject;
	}
	
	// set the folder
	function setFolder( $idFolder ) { $this->idFolder = $idFolder; }
	
	// set the id (name) of opShowId operations
	function setOpShowId( $opShowId ) {	$this->opShowId = $opShowId; }
	
	// set the id (name) of opPlayId operations
	function setOpPlayId( $opPlayId ) {	$this->opPlayId = $opPlayId; }

	// set the id (name) of opSelectId operations
	function setOpSelectId( $opSelectId ) {	$this->opSelectId = $opSelectId; }

	// set the id (name) of opDeselectId opreations
	function setOpDeselectId( $opDeselectId ) { $this->opDeselectId = $opDeselectId; }
	
	// getRows: overload of method of the DataRetriever class
	// execute query for data retrieving 
	// tipically called from listView
	function getRows( $startRow, $numRows ) {
		$query = "SELECT `idObject`, `idResource`, `idCategory`,"
			."`idAuthor`, `objectType`, `title`, `public`,"
			."`version`, `difficult`, `language`,"
			."`resource`, `objective`, `dateInsert`"
			." FROM ".$this->prefix."_pubrepo"
			." WHERE idFolder='". (int)$this->idFolder . "'";
		return $this->_getData( $query, $startRow, $numRows );
	}
	
	// fetchRecord: overload of method of the DataRetriever class
	function fetchRecord() {
		// fetch a record from record set
		$arrData = parent::fetchRecord();
		if( $arrData === FALSE ) 
			return FALSE;
		
		// ------ compute title
		// use a special class for selected items
		$title = '<input type="submit" class="';
		
		if( is_array( $this->selectedIdObject ) && in_array ( $arrData['idObject'], $this->selectedIdObject ) ) {
			$title .= 'TreeItemSelected';
			$op = $this->opDeselectId;
		} else {
			$title .= 'TreeItem';
			$op = $this->opSelectId;
		}
		// attach select operation to title
		$title .= '" value="'.$arrData['title']
				 .'" name="'. $op.$arrData['idObject'] .'" id="'. $arrData['idObject'] .'_select" />';
				 
		$arrData['title'] = $title;
		
		// ------ compute icon						
		$arrData['icon'] = '<img src="'.getPathImage().'lobject/'
							.$arrData['objectType'] .'.gif" alt="'
							.$arrData['objectType'] .'" />';
							
		// ------ show operation
		$arrData['show'] = '<div class="LVShowItem">'
							.'<input type="submit" class="LVShowItem" value="'
							.'" name="'. $this->opShowId .$arrData['idObject'] .'" id="'. $arrData['idObject'] .'img_show" />'
							.'</div>';
							
		// ------ play operation
		$arrData['play'] = '<div class="LVShowItem">'
							.'<input type="submit" class="LVShowItem" value="'
							.'" name="'. $this->opPlayId .$arrData['idObject'] .'" id="'. $arrData['idObject'] .'img_play" />'
							.'</div>';
							
		return $arrData;
	}
}

/**
 * Customizaton of ListView class for pubrepo 
 **/
class PubRepo_ListView extends ListView {
	
	var $addurl = "";
	
	// overload for _getAddLabel operation
	function _getAddLabel() { return _NEWITEM; }
	
	// utility function
	function _createColInfo( $label, $hClass, $fieldClass, $data, $toDisplay, $sortable ) {
		return array( 	'hLabel' => $label,
						'hClass' => $hClass,	
						'fieldClass' => $fieldClass,	
						'data' => $data,
						'toDisplay' => $toDisplay,
						'sortable' => $sortable );
	}
	
	// overload
	function _getCols() {
		$colInfos = array();
		$colInfos[] = $this->_createColInfo( 'idObject','','','idObject',false, false );
		$colInfos[] = $this->_createColInfo( 'idResource','','','idResource',false, false );
		$colInfos[] = $this->_createColInfo( 'idCategory','','','idCategory',false, false );
		$colInfos[] = $this->_createColInfo( 'idAuthor','','','idAuthor',false, false );
		$colInfos[] = $this->_createColInfo( 'objectType','','','objectType',false, false );
		$colInfos[] = $this->_createColInfo( 'icon','image','image','icon',true, false );
		$colInfos[] = $this->_createColInfo( 'title','','','title',true, false );
		$colInfos[] = $this->_createColInfo( 'show','image','image','show',true, false );
		return $colInfos;
	}
}
	
// customization of TreeDb for pubrepo_dir
class PubDirDb extends TreeDb {
	// it's all ok! only to set table name and fields name
	function PubDirDb() {
		
		$this->table = $GLOBALS['prefix_lms'] . '_pubrepo_dir';
		$this->fields = array( 'id' => 'id', 'idParent' => 'idParent', 'path' => 'path', 'lev' => 'lev' );
	}
}

function manPurepo_save( $idFolder, &$lo, $arrParam ) {
	
	// a big insert query .... wow wooo ... yep
	$query = "INSERT INTO ".$GLOBALS['prefix_lms']."_pubrepo"
			." ( `idFolder` , `idResource` , `idCategory` , `idAuthor` ,"
			." `objectType` , `title` , `public` , `version` , `difficult` ,"
			." `description` , `language` , `resource` , `objective` , `dateInsert` )"
			." VALUES ( '"
			. (int)$idFolder ."','". (int)$lo->getId() ."','"
			. (int)(isset($arrParam['idCategory'])?($arrParam['idCategory']):'') ."','"
			. (int)(isset($arrParam['idAuthor'])?($arrParam['idAuthor']):'') ."','"
			. $lo->getObjectType() ."','"
			. $lo->getTitle() ."','"
			. (int)(isset($arrParam['public'])?($arrParam['public']):'') ."','"
			. (isset($arrParam['version'])?($arrParam['version']):'') ."','"
			. (isset($arrParam['difficult'])?($arrParam['difficult']):'') ."','"
			. (isset($arrParam['description'])?($arrParam['description']):'') ."','"
			. (isset($arrParam['language'])?($arrParam['language']):'') ."','"
			. (isset($arrParam['resource'])?($arrParam['resource']):'') ."','"
			. (isset($arrParam['objective'])?($arrParam['objective']):'') ."','"
			. getdate() ."' )";
	sql_query( $query ) 
		or die( sql_error() );
}

function manPubrepo_display( &$treeView, $withContents, $withActions = FALSE ) {
	
	echo '<div class="std_block">';
	if( $withContents ) {
		$listView = $treeView->getListView();
		$treeView->load();
		if( $withActions ) {
			$treeView->loadActions();
		}
		$listView->printOut();
	} else {
		$treeView->load();	
		if( $withActions ) {
			$treeView->loadActions();
		}
	}
	echo '</div>';
}

function manPubrepo_getOp( &$treeView ) {
	$op = $treeView->op;
	
	if( $op == "" ) {
		$listView = $treeView->getListView();
		if( $listView !== NULL )
		 	$op = $listView->op;
	}
		
	return $op;
}

function manPubrepo_addfolder( &$treeView ) {
	
	
	echo '<div class="std_block">';
	$treeView->loadNewFolder();
	echo '</div>';
}

function &manPubrepo_CreateTreeView( $withContents = TRUE, $multiSelect = FALSE, $withActions = FALSE ) {
		
		$dirDb = new PubDirDb();
		$treeView = new TreeView( $dirDb, 'pubrepo' );
		$treeView->parsePositionData( $_POST, $_POST, $_POST );
		if( $withContents ) {
			$dataRetriever = new PubRepo_DataRetriever( NULL, $GLOBALS['prefix_lms'] );
			$TableRenderer = new Table(20);
			$listView = new PubRepo_ListView( _LVTITLEPUBREPO, $dataRetriever, $TableRenderer, 'pubrepo');
			
			$listView->multiSelect = $multiSelect;
	
			$listView->parsePositionData( $_POST );
	
			$dataRetriever->setFolder( $treeView->selectedFolder );
			$dataRetriever->setOpShowId( $listView->_getOpShowItemId() );
			$dataRetriever->setOpPlayId( $listView->_getOpPlayItemId() );
			$dataRetriever->setOpSelectId( $listView->_getOpSelectItemId() );
			$dataRetriever->setOpDeselectId( $listView->_getOpDeselectItemId() );
			$dataRetriever->setSelectedObject( $listView->getIdSelectedItem() );
			$listView->addurl = $treeView->_getOpNewFolderId();
			
			if( $withActions ) { 
				//if(funAccess("insitem","NEW", TRUE, "pubrepo")) {
					$listView->setInsNew( TRUE );
				//}
			}
			
			$treeView->setlistView( $listView );

		}
		return $treeView;
}

function manPubrepo( $withForm = FALSE, $withContents = TRUE, $treeView = NULL, $multiSelect = FALSE, $withActions = FALSE ) {
	//if(funAccess("pubrepo","OP")) {

		if( $treeView === NULL ) {
			$treeView = manPubrepo_CreateTreeView($withContents, $multiSelect, $withActions);
		}
		
		
		if( $withForm ) {
			echo '<form name="manPubrepo" method="post"'
				.' action="index.php?modname='.$_GET['modname'].'&op='.$_GET['op'].'"'
				.' >'."\n"
				.'<input type="hidden" id="authentic_request_pr" name="authentic_request" value="'.Util::getSignature().'" />';
		}
		
		
		switch ( manPubrepo_getOp( $treeView ) ) {
			case 'newfolder':
				manPubrepo_addfolder( $treeView);
			break;
			case 'showitem':
				$treeView->printState();
				$listView = &$treeView->getListView();
				$listView->printState();
				manPubRepo_ShowItem($listView->getIdShowItem());
				echo '<img src="'.getPathImage().'standard/close.gif" alt="'._CLOSE.'" /> '
											.'<input type="submit" value="'._CLOSE.'"'
											.' name="close" />';
				break;
			/*case 'playitem':
				$treeView->printState();
				manPubRepo_ShowItem($treeView->getItemToShow());
				echo '<img src="'.getPathImage().'standard/close.gif" alt="'._CLOSE.'" /> '
											.'<input type="submit" value="'._CLOSE.'"'
											.' name="close" />';
				break;*/

			case 'display':
			default:
				manPubrepo_display( $treeView, $withContents, $withActions );
			break;
		}
		if( $withForm ) {
			echo '</form>'."\n";
		}
		
	//} else 
	//	echo "You can't access";
	return $treeView;
}


/**
 * This function can be called from LO insert/edit operation
 * to set position in pubrepo and other metadata.
 * @param $lo instance of the learning object to edit
 * @param $withForm print form tag
 * @param $withContents display the Items in folders
 **/
function manPubRepoSave( &$lo, $withForm = FALSE, $withContents = TRUE ) {

		$treeView = manPubrepo_CreateTreeView($withContents, FALSE);
		
		// print a form that submit to the same url
		if( $withForm ) {
			echo '<form name="manPubrepo" method="post"'
				.' action="index.php?'.$_SERVER['QUERY_STRING'].'"'
				.' >'."\n"
				.'<input type="hidden" id="authentic_request_pr" name="authentic_request" value="'.Util::getSignature().'" />';
		}
		
		// handle operations
		switch ( $treeView->op ) {
			case 'newfolder':
				manPubrepo_addfolder($treeView);
			break;
			case 'save':
				manPurepo_save( $treeView->getSelectedFolderId(), $lo, $_POST );
				Util::jump_to( ''.$lo->getBackUrl());
			case 'display':
			default:
				manPubrepo_display( $treeView, $withContents);
				loadFields( $_POST, $lo );
			break;
		}
		
		// add save button
		echo '<img src="'.getPathImage().'standard/save.gif" alt="'._SAVE.'" /> '
									.'<input type="submit" value="'._SAVE.'"'
									.' name="'.$treeView->_getOpSaveFile().'" />';
		
		if( $withForm ) {
			echo '</form>'."\n";
		}
	
}

function loadFields( &$arrayData, &$lo ) {

	global $defaultLanguage;
	
	//including language
	includeLang("classification");
	
	//finding category
	$reCategory = sql_query("
	SELECT idCategory, title 
	FROM ".$GLOBALS['prefix_lms']."_coursecategory
	ORDER BY title");

	//searching languages
	$langl = dir('menu/language/');
	while($ele = $langl->read())
		if(preg_match("lang-",$ele)) {
			$langArray[] = preg_replace("/lang-/","",preg_replace(".php","",$ele));
		}
	closedir($langl->handle);
	sort($langArray);

	// ==========================================================
	
	echo '<div class="ObjectForm">';

	echo '<span class="mainTitle">'._CATEGORIZATION. ' ' . $lo->getTitle() .'</span><br /><br />';

	
	//-------------------------------------------------
	echo '<div class="title">'._PUBLIC.'</div>'
		.'<div class="content">';
	if( isset($arrayData['public']) && $arrayData['public'] == "1" ) {
		echo '<input type="radio" name="public" value="1" checked="checked" /> '._YES
			.'<input type="radio" name="public" value="0" /> '._NO;
	} else {
		echo '<input type="radio" name="public" value="1" /> '._YES
			.'<input type="radio" name="public" value="0" checked="checked" /> '._NO;
	}
	
	echo '</div>'
		//-------------------------------------------------
		.'<div class="title">'._CATEGORY.'</div>'
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
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._VERSION.'</div>'
		.'<div class="content">';
	
	if( isset($arrayData['version']) )
		echo '<input type="text" name="version" maxlength="8" size="10" value="'.$arrayData['version'].'" />';
	else
		echo '<input type="text" name="version" maxlength="8" size="10" value="1.0" />';
		
	echo '</div>'
		//-------------------------------------------------
		.'<div class="title">'._DIFFICULT.'</div>'
		.'<div class="content">'
		.'<select name="difficult">';

	if( isset($arrayData['difficult']) )
		$selDiff = $arrayData['difficult'];
	else
		$selDiff = "";
	
		echo '<option value="1" '.(($selDiff=="1")?'selected':'').' >'._VERYEASY.'</option>'
			.'<option value="2" '.(($selDiff=="2")?'selected':'').' >'._EASY.'</option>'
			.'<option value="3" '.(($selDiff=="3")?'selected':'').' >'._MEDIUM.'</option>'
			.'<option value="4" '.(($selDiff=="4")?'selected':'').' >'._DIFFICULT.'</option>'
			.'<option value="5" '.(($selDiff=="5")?'selected':'').' >'._VERYDIFFICULT.'</option>'
		.'</select>'
		.'</div>';
		//-------------------------------------------------
		/*.'<div class="title">'._DESCRIPTION.'</div>'
		.'<div class="content">'
		.'<div id="breakfloat">'
			.'<textarea id="description" name="description" rows="10" cols="75"></textarea></div>'
		.'</div>'*/
		//-------------------------------------------------	
	echo '<div class="title">'._LANGUAGE.'</div>'
		.'<div class="content">'
		.'<select name="language">';
	if( isset($arrayData['language']) )
		$selLang = $arrayData['language'];
	else
		$selLang = $defaultLanguage;

		while(list( ,$valueLang)= each($langArray)) {
			echo '<option value="'.$valueLang.'"';
			if($valueLang == $selLang) echo ' selected="selected"';
			echo '>'.$valueLang.'</option>';
		}
	echo '</select>'
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._RESOURCE.'</div>'
		.'<div class="content">';
	if( isset($arrayData['resource']) )
		echo '<input type="text" name="resource" maxlength="255" size="60" value="'.$arrayData['resource'].'" />';
	else
		echo '<input type="text" name="resource" maxlength="255" size="60" value="http://" />';
	echo '</div>'
		//-------------------------------------------------
		.'<div class="title">'._OBJECTIVE.'</div>'
		.'<div class="content">';
	if( isset($arrayData['objective']) )
		echo '<textarea name="objective" rows="6" cols="75">'.$arrayData['objective'].'</textarea>';
	else
		echo '<textarea name="objective" rows="6" cols="75"></textarea>';
		
		echo '</div>';
		//-------------------------------------------------	
		/*.'<div class="title">'
		.'<input class="button" type="submit" value="'._INSOBJECT.'" />'
		.'</div>'
		.'</form>';*/
	echo '</div>';
	
}

function manPubRepo_ShowItem( $itemId ) {
	

	includeLang("classification");


	$query = 'SELECT `idFolder` , `idResource` ,'
			.$GLOBALS['prefix_lms'].'_coursecategory.title catTitle , `idAuthor` ,'
			.'`objectType` ,'
			.$GLOBALS['prefix_lms'].'_pubrepo.title title, `public` , `version` , `difficult` ,'
			.'`description` , `language` , `resource` , `objective` , `dateInsert`'
			.' FROM '.$GLOBALS['prefix_lms'].'_pubrepo, '.$GLOBALS['prefix_lms'].'_coursecategory'
			.' WHERE '.$GLOBALS['prefix_lms'].'_pubrepo.idCategory = '.$GLOBALS['prefix_lms'].'_coursecategory.idCategory'
			." AND idObject='".(int)$itemId."'";
	$rs = sql_query( $query ) 
		or die( sql_error() );
	
	$arrayData = sql_fetch_assoc($rs);
	
	echo '<div class="ObjectForm">';
	echo '<span class="mainTitle">'._CATEGORIZATION. ' ' . $arrayData['title'] .'</span><br /><br />';

	
	//-------------------------------------------------
	echo '<div class="title">'._PUBLIC.'</div>'
		.'<div class="content">';
	if( isset($arrayData['public']) && $arrayData['public'] == "1" ) {
		echo _YES;
	} else {
		echo _NO;
	}
	
	echo '</div>'
		//-------------------------------------------------
		.'<div class="title">'._CATEGORY.'</div>'
		.'<div class="content">'
		.$arrayData['catTitle']
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._VERSION.'</div>'
		.'<div class="content">'
		.$arrayData['version']
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._DIFFICULT.'</div>'
		.'<div class="content">'
		.$arrayData['difficult']
		.'</div>'
		.'<div class="title">'._LANGUAGE.'</div>'
		.'<div class="content">'
		.$arrayData['language']
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._RESOURCE.'</div>'
		.'<div class="content">'
		.$arrayData['resource']
		.'</div>'
		//-------------------------------------------------
		.'<div class="title">'._OBJECTIVE.'</div>'
		.'<div class="content">'
		.$arrayData['objective']
		.'</div>';
		//-------------------------------------------------	

	echo '</div>';
	
}


function manPubRepo_getData( $idObject ) {
	
	$query = "SELECT `idObject`, `idResource`, `idCategory`,"
		."`idAuthor`, `objectType`, `title`, `public`,"
		."`version`, `difficult`, `language`,"
		."`resource`, `objective`, `dateInsert`"
		." FROM ".$GLOBALS['prefix_lms']."_pubrepo"
		." WHERE idObject='". (int)$idObject . "'";
	$rs = sql_query( $query ) or die( sql_error() );
	return sql_fetch_assoc( $rs );
}
?>
