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
 * @package  admin-library
 * @subpackage interaction
 * @version 	$Id: lib.listview.php 974 2007-02-17 00:25:06Z giovanni $
 */

require_once(_base_.'/lib/lib.dataretriever.php');
require_once(_base_.'/lib/lib.table.php');

class ListView {
	var $startRow = 0;
	//data retriever object
	var $data = NULL;
	//renderer object
	var $rend = NULL;
	// title of listview
	var $title = "";
	// add ins new row
	var $insNew = FALSE;
	// list view id
	var $LVId = "LV";
	// multi selection  LV
	var $multiSelect = FALSE;
	// selected id
	var $itemSelected = NULL;
	// id to show
	var $itemToShow = NULL;
	// id to play
	var $itemToPlay = NULL;

	var $cancel = FALSE;
	// op
	var $op = "";
	// lang
	var $lang = NULL;
	// the recordset
	var $rs = NULL;

	function _getOpShowItemId() 	{ return 'op_listview_opshowitem_'	;	}
	function _getOpPlayItemId() 	{ return 'op_listview_opplayitem_'	;	}
	function _getOpSelectItemId() 	{ return 'op_listview_opselectitem_'	;	}
	function _getOpDeselectItemId() { return 'op_listview_opdeselectitem_';	}
	function _getOpNewItemId() 		{ return 'op_listview_opnewitem_'		;	}
	function _getOpCreateItemId()	{ return 'op_listview_opcreateitem_'	;	}
	function _getIdCreateItemId()	{ return 'op_listview_idcreateitem_'	;	}
	function _getIdShowItemId() 	{ return 'op_listview_idshowitem_'	;	}
	function _getIdPlayItemId() 	{ return 'op_listview_idplayitem_'	;	}
	function _getIdSelectItemId() 	{ return 'op_listview_idselectitem_'	;	}
	function _getCancelId() 		{ return 'op_listview_cancel_'		; }
	function _getIdOpStatus() 		{ return 'op_listview_idopstate_'		; }
	function _getIdInitRowId()		{ return 'op_listview_startRow_';}

	function getIdShowItem() { return $this->itemToShow; }
	function getIdPlayItem() { return $this->itemToPlay; }
	function getIdSelectedItem() { return $this->itemSelected; }


	function _getTitle() { return $this->title; }
	function _getRowsPage() { return 20; }
	function _getStartRow() { return $this->startRow; }

	function _getCancelLabel() { return $this->lang->def('_CANCEL'); }
	function _getCancelAlt() { return $this->lang->def('_CANCEL'); }
	function _getCancelImage() { return getPathImage().'standard/cancel.png'; }

	function _getAddLabel() { return $this->lang->def('_NEW_ITEM'); }
	function _getAddUrl() { return $this->_getOpNewItemId(); }
	function _getAddAlt() { return "+"; }
	function _getAddImage() { return getPathImage().'standard/add.png'; }

	function _getCreateLabel() { return $this->lang->def('_DIRECTORY_NEWGROUP'); }
	function _getCreateUrl() { return $this->_getOpCreateItemId(); }
	function _getCreateAlt() { return $this->lang->def('_DIRECTORY_NEWGROUP'); }
	function _getCreateImage() { return getPathImage().'standard/add.png'; }

	/**
	 * This method must return an array whit a number of
	 * columns equal to that we want display.
	 * Each element of array is an array whit:
	 * ['hLabel'] => HeaderLable
	 * ['hClass'] => HeaderClass
	 * ['filedClass'] => Field class
	 * ['data'] => Field in data (name or index)
	 * ['toDisplay'] => toDisplay
	 * ['sortable'] => sortable
	 **/
	function _getCols() {
		$totCol = $this->data->getFieldCount();
		$fieldInfos = $this->data->getFieldsInfo();
		$colInfos = array();
		foreach( $fieldInfos as $fname => $finfo ) {
			$colInfos[] = array( 	'hLabel' => $fname,
						'hClass' => "",
						'fieldClass' => "",
						'data' => $fname,
						'toDisplay' => true,
						'sortable' => false );
		}
		return $colInfos;
	}

	function _getLinkPagination() {
		return 'index.php?modname=pubrepo&amp;op=pubrepo&amp;ord='
				.$this->_getOrd()
				.'&amp;ini=';
	}

	function _getOrd() {
		// standard implementation get order from _GET super array
		if( isset($_GET['ord']) ) {
			return $_GET['ord'];
		} else {
			return '';
		}
	}

	function ListView( $title, &$data, &$rend, $id ) {
		$this->title = $title;
		$this->data = &$data;
		$this->rend = $rend;
		$this->id = $id;
		$this->itemSelected = array();
		$this->lang =& DoceboLanguage::createInstance('standard','framework');
		$this->startRow = Get::req('ini', DOTY_INT, 0);
	}

	function getOp() {
		return $this->op;
	}

	function setInsNew( $insNew ) {
		$this->insNew = $insNew;
	}

	function &getDataRetrivier() {
		return $this->data;
	}

	function extendedParsing( $arrayState ) {

	}

	function parsePositionData( $arrayState ) {
		// preserve state
		if( isSet( $arrayState[$this->_getCancelId()] ) )
			$this->cancel = TRUE;
		else
			$this->cancel = FALSE;

		if( isSet( $arrayState[$this->_getIdInitRowId()] ) ) {
			$this->startRow = (key($arrayState[$this->_getIdInitRowId()])-1)*$this->_getRowsPage();
			$_SESSION[$this->id.'_cache_page'] = $this->startRow;
		} elseif(empty($arrayState)) {
			if(isset($_SESSION[$this->id.'_cache_page']) && $_SESSION[$this->id.'_cache_page'] != false) $this->startRow = $_SESSION[$this->id.'_cache_page'];
		} else {
			if(isset($_SESSION[$this->id.'_cache_page'])) unset($_SESSION[$this->id.'_cache_page']);
			$this->startRow = 0;
		}

		if( isSet( $arrayState[$this->_getIdShowItemId()] ) ) {
			$this->itemToShow = $arrayState[$this->_getIdShowItemId()];
		}
		if( isSet( $arrayState[$this->_getIdPlayItemId()] ) ) {
			$this->itemToPlay = $arrayState[$this->_getIdPlayItemId()];
		}
		if( isSet( $arrayState[$this->_getIdSelectItemId()] ) ) {
			$this->itemSelected = @Util::unserialize(urldecode($arrayState[$this->_getIdSelectItemId()]));
		}

		// handle actions that change display mode (op)
		if( isSet( $arrayState[$this->_getOpNewItemId()] ) ) {
			$this->op = 'newitem';
		}

		if( (isset( $arrayState[$this->id] )) && (is_array( $arrayState[$this->id] )) ) {
			if( isset( $arrayState[$this->id][$this->_getOpCreateItemId()] ) )
				$this->op = 'newitem';
		}

		// parse for actions
		if( isSet( $arrayState[$this->_getOpCreateItemId()] ) ) {
			$this->data->InsertItem( $arrayState );
			$this->op = 'display'; // diplay
		}

		foreach( $arrayState as $nameField => $valueField ) {
			if( strstr( $nameField, $this->_getOpShowItemId() ) ) {
				$id = substr( $nameField, strlen($this->_getOpShowItemId()) );
				$this->itemToShow = $id;
				$this->op = 'showitem';
			} else if( strstr( $nameField, $this->_getOpPlayItemId() ) ) {
				$id = substr( $nameField, strlen($this->_getOpPlayItemId()) );
				$this->itemToPlay = $id;
				$this->op = 'playitem';
			} else if( strstr( $nameField, $this->_getOpSelectItemId() ) ) {
				$id = substr( $nameField, strlen($this->_getOpSelectItemId()) );
				if( $this->multiSelect && !in_array((int)$id, $this->itemSelected) ) {
					$this->itemSelected[] = (int)$id;
				} else
					$this->itemSelected = array( (int)$id );
				$this->op = 'selectitem';
			} else if( strstr( $nameField, $this->_getOpDeselectItemId() ) ) {
				$id = substr( $nameField, strlen($this->_getOpDeselectItemId()) );
				$key = array_search ( $id, $this->itemSelected );
				if( $key !== FALSE )
					unset( $this->itemSelected[$key] );
			}
		}

		$this->extendedParsing( $arrayState );

		if( ($this->op == '') && isSet( $arrayState[$this->_getIdOpStatus()] ) ) {
			if( $this->cancel )
				$this->op = 'display'; // diplay
			else
				$this->op = $arrayState[$this->_getIdOpStatus()];;
		}

	}

	function printState() {
		$out = '<input type="hidden"'
			.' id="'.$this->_getIdSelectItemId().'"'
			.' name="'.$this->_getIdSelectItemId().'"'
			.' value="'.urlencode(Util::serialize($this->itemSelected)).'" />'."\n";
		$out .= '<input type="hidden"'
			.' id="'.$this->_getIdPlayItemId().'"'
			.' name="'.$this->_getIdPlayItemId().'"'
			.' value="'.$this->getIdPlayItem().'" />'."\n";
		$out .= '<input type="hidden"'
			.' id="'.$this->_getIdShowItemId().'"'
			.' name="'.$this->_getIdShowItemId().'"'
			.' value="'.$this->getIdShowItem().'" />'."\n";
		$out .= '<input type="hidden"'
			.' id="'.$this->_getIdOpStatus().'"'
			.' name="'.$this->_getIdOpStatus().'"'
			.' value="'.$this->op.'" />'."\n";
		return $out;
	}

	function getRows($start, $len) {
		$this->rs = $this->data->getRows($start, $len);
	}

	function getTotRowsWhithFilter()
	{
		return sql_num_rows($this->data->rs_f);
	}

	function getLoadedRows() {
		return sql_num_rows($this->rs);
	}

	function getTotalRows() {
		return $this->data->getTotalRows();
	}

	function fetchRecord() {
		return $this->data->fetchRecord();
	}

	function printOut() {
		$out = $this->rend->OpenTable($this->_getTitle());

		$this->getRows( $this->_getStartRow(), $this->_getRowsPage());

		$totRow = $this->getTotalRows();

		if( $totRow == -1 ) {
			$totRow = $this->getLoadedRows();
		}
		$colInfo = $this->_getCols();
		$colData = $colInfo;
		$out .= $this->rend->WriteHeaderCss($colInfo);

		while( $values = $this->fetchRecord() ) {
			foreach( $colInfo as $key => $fieldInfo ) {
				$colData[$key]['data'] = $values[$colInfo[$key]['data']];
			}
			$out .= $this->rend->WriteRowCss($colData);
		}

		if( $this->insNew ) {
			/*$out .= $this->rend->WriteAddRow('<input type="image" class="tree_view_image" '
				.' src="'.$this->_getCreateImage().'"'
				.' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
				.' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
				.' title="'.$this->_getCreateLabel().'" '
				.' alt="'.$this->_getCreateAlt().'" />'
				.$this->lang->def('_ADD') );
				*/

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

	function printInsert() {
		$this->printState();
		return '<input type="image" class="tree_view_image" '
			.' src="'.$this->_getCreateImage().'"'
			.' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
			.' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
			.' title="'.$this->_getCreateLabel().'" '
			.' alt="'.$this->_getCreateAlt().'" />';
	}

}

?>
