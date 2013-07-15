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

require_once(_base_.'/lib/lib.treedb.php');
require_once(_base_.'/lib/lib.treeview.php');

/**
 * @package  DoceboLms
 * @version  $Id: tree.category.php 573 2006-08-23 09:38:54Z fabio $
 * @category Course category
 * @author   Fabio Pirovano <fabio@docebo.com>
 */

if(!isset($GLOBALS['css_treeview_load']) && isset($GLOBALS['page'])) {//if(!isset($GLOBALS['css_treeview_load'])) {
	$GLOBALS['page']->add('<link href="'.getPathTemplate().'style/base-old-treeview.css" rel="stylesheet" type="text/css" />', 'page_head');
	$GLOBALS['css_treeview_load'] = true;
}

class TreeDb_CatDb extends TreeDb {
	
	// Constructor of TreeDb_CatDb class
	function TreeDb_CatDb($table_name) {
		
		$this->table = $table_name;
		$this->fields = array( 
			'id' => 'idCategory', 
			'idParent' => 'idParent', 
			'path' => 'path', 
			'lev' => 'lev'
		);
	}
	
	function _getOtherFields($tname = FALSE) {
		
	}
	
	function _getOtherValues() {
		
	}
	
	function _getOtherUpdates() {
	
	}
	
	function _getFilter($tname = FALSE) {
		
		$result = "";
		return $result;
	}
	
	function addFolderById($idParent, $folderName) {
		
		return parent::addFolderById($idParent, $folderName);
	}
	
	function addItem($idParent, $org_name) {
		
		$idReference = parent::addFolderById($idParent, $org_name);
		return $idReference;
	}
	
	function modifyItem( $arrData ) {
		
		$folder = $this->getFolderById( $arrData['idItem'] );
		$this->changeOtherData( $folder );
	}
}

class TreeView_CatView extends TreeView {
	
	var $can_add = false;
	var $can_mod = false;
	var $can_del = false;
	var $lang = false;
	var $show_action = true;
	
	var $cat_not_empty = false;
	
	var $hide_inline_action = false;
	
	
	function hideInlineAction() {
		
		$this->hide_inline_action = true;
	}
	
	function showInlineAction() {
		
		$this->hide_inline_action = false;
	}
	
	function  TreeView_CatView($tdb, $id, $rootname = 'root') {
		
		$query_course = "SELECT idCategory, COUNT(*) FROM ".$GLOBALS['prefix_lms']."_course GROUP BY idCategory ";
		$re_course = sql_query($query_course);
		while($row = sql_fetch_row($re_course)) {
			$this->cat_not_empty[$row[0]] = $row[1]; 
		}
		
		$user_lvl = Docebo::user()->getUserLevelId();
		
		parent::TreeView($tdb, $id, $rootname);
		$this->can_add = ($user_lvl == ADMIN_GROUP_GODADMIN);
		$this->can_mod = ($user_lvl == ADMIN_GROUP_GODADMIN);
		$this->can_del = ($user_lvl == ADMIN_GROUP_GODADMIN);
		//require_once(_i18n_.'/lib.lang.php');
		
	}
	
	function _getAddImage() 						{ return getPathImage('lms').'standard/add.png'; }
	function _getAddLabel() 						{ return Lang::t('_NEW_CATEGORY', 'course'); }
	function _getAddAlt() 							{ return Lang::t('_ADD', 'standard'); }
	function canAdd() 								{ return $this->can_add && !$this->hide_inline_action; }
	
	function _getRenameImage()						{ return getPathImage('lms').'standard/edit.png'; }
	function _getRenameLabel() 						{ return Lang::t('_MOD', 'course', 'lms'); }
	function canRename() 							{ return $this->isFolderSelected() && $this->can_mod; }
	function canInlineRename() 						{ return $this->can_mod && !$this->hide_inline_action; }
	function canInlineRenameItem( &$stack, $level ) { return (($level != 0) && $this->can_mod); }
	
	function _getMoveLabel() 						{ return Lang::t('_MOVE', 'course'); }
	function canMove()								{ return $this->isFolderSelected() && $this->can_mod; }
	function canInlineMove() 						{ return $this->can_mod && !$this->hide_inline_action; }
	function canInlineMoveItem( &$stack, $level ) 	{ return (($level != 0) && $this->can_mod); }
	
	function _getDeleteLabel() 						{ return Lang::t('_DEL', 'course'); }
	function canDelete() { 
		$info = $this->getSelectedFolderData();
		
		
		
		
		$id = $info['folder']->id;
		
		return ($info['isLeaf'] == 1) && $this->isFolderSelected() && $this->can_del && !isset($this->cat_not_empty[$id]); 
	}
	function canInlineDelete() 						{ return $this->can_del && !$this->hide_inline_action; }
	function canInlineDeleteItem( &$stack, $level ) { 
		$id = $stack[$level]['folder']->id;
		return ( ($stack[$level]['isLeaf'] == 1) && ($level != 0) && $this->can_del && !isset($this->cat_not_empty[$id]) ); 
	}
	
	function _getMoveTargetLabel()					{ return Lang::t('_MOVE', 'course').' : '; }
	function _getCancelLabel()						{ return Lang::t('_UNDO', 'course'); }
	
	function _getOtherActions() {
		
		if( $this->isFolderSelected() ) {
			
			return array();
		}
		return array();
	}
	
	function getFolderPrintName( &$folder ) {
		
		return parent::getFolderPrintName( $folder );
	}
	
	function extendedParsing( $arrayState, $arrayExpand, $arrayCompress ) {
		
		if(!isset($arrayState[$this->id])) return;
		
	}
	
	function printElement(&$stack, $level) {
		
		return parent::printElement($stack, $level);
	}
	
	function printActions( &$stack, $level ) {
		$tree = '';
		if( $this->canInlineDelete() ) {
			if( $this->canInlineDeleteItem($stack, $level) ) 
				$tree .= '<input type="submit" class="TVActionDelete" value="" name="'
					.$this->_getOpDeleteFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getDeleteLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}	
		if( $this->canInlineRename() ) {
			if( $this->canInlineRenameItem($stack, $level) ) 
				$tree .= '<input type="submit" class="TVActionRename" value="" name="'
					.$this->_getOpRenameFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getRenameLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}
		
		if( $this->canInlineMove() ) {
			if( $this->canInlineMoveItem($stack, $level) ) 
				$tree .= '<input type="submit" class="TVActionMove" value="" name="'
					.$this->_getOpMoveFolderId().$stack[$level]['folder']->id .'"'
					.' title="'.$this->_getMoveLabel().'" />';
			else
				$tree .= '<div class="TVActionEmpty"></div>';
		}
		if($this->show_action === false) return '';
		return $tree;
	}
	
	function loadNewFolder() {
		
		require_once(_base_.'/lib/lib.form.php');
		
		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		
		return Form::openElementSpace()
			.$this->printState()
			.Form::getTextfield( $lang->def('_NAME'), $this->_getFolderNameId(), $this->_getFolderNameId(), 255 )
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.' <img src="'.$this->_getCreateImage().'" alt="'.$this->_getCreateAlt().'" /> '
				.'<input type="submit" class="TreeViewAction" value="'.$lang->def('_CREATE', 'standard').'"'
				.' name="'.$this->_getCreateFolderId().'" id="'.$this->_getCreateFolderId().'" />'
			.' <img src="'.$this->_getCancelImage().'" alt="'.$this->_getCancelAlt().'" /> '
				.'<input type="submit" class="TreeViewAction" value="'.$lang->def('_UNDO', 'standard').'"'
				.' name="'.$this->_getCancelId().'" id="'.$this->_getCancelId().'" />'
			.Form::closeButtonSpace();
	}
	
	function loadRenameFolder() {
		
		
		$lang =& DoceboLanguage::CreateInstance('course', 'lms');
		
		$tdb = $this->tdb;
		$folder = $tdb->getFolderById( $this->getSelectedFolderId() );
		return Form::openElementSpace()
			.$this->printState()
			.Form::getTextfield( $lang->def('_NAME'), $this->_getFolderNameId(), 
				$this->_getFolderNameId(), 255, $this->getFolderPrintName($folder) )
			.Form::closeElementSpace()
			.Form::openButtonSpace()
			.' <img src="'.$this->_getRenameImage().'" alt="'.$this->_getRenameAlt().'" /> '
			.'<input type="submit" class="TreeViewAction" value="'.$lang->def('_MOD').'"'
			.' name="'.$this->_getRenameFolderId().'" id="'.$this->_getRenameFolderId().'" />'
			.' <img src="'.$this->_getCancelImage().'" alt="'.$this->_getCancelAlt().'" /> '
				.'<input type="submit" class="TreeViewAction" value="'.$lang->def('_UNDO', 'standard').'"'
				.' name="'.$this->_getCancelId().'" id="'.$this->_getCancelId().'" />'
			.Form::closeButtonSpace();
	}	
	
	function loadDeleteFolder() {
		$tdb = $this->tdb;
		$folder = $tdb->getFolderById( $this->getSelectedFolderId() );
		$lang =& DoceboLanguage::createInstance('course', 'lms');
		return $this->printState()
			.getDeleteUi(	$lang->def('_AREYOUSURE'), 
							'<span class="text_bold">'.$lang->def('_CATEGORY').' : </span>'
							.$this->getFolderPrintName($folder), 
							false, 
							$this->_getDeleteFolderId(),
							$this->_getCancelId() );
	}
	
	function __sleep() {
		
		$this->lang = null;
		
		return array(	'tdb',
						'id',
						'posTree',
						'posFlat',
						'expandList',
						'compressList',
						'selectedFolder',
						'op',
						'rootname' );
	}
	
	function __wakeup() {
	
		$this->lang =& DoceboLanguage::createInstance('treeview', 'framework');
	}
}

?>