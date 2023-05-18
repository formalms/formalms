<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

/**
 * @version 	$Id: lib.listview.php 974 2007-02-17 00:25:06Z giovanni $
 */
require_once _base_ . '/lib/lib.dataretriever.php';
require_once _base_ . '/lib/lib.table.php';

class ListView
{
    public $startRow = 0;
    //data retriever object
    public $data = null;
    //renderer object
    public $rend = null;
    // title of listview
    public $title = '';
    // add ins new row
    public $insNew = false;
    // list view id
    public $LVId = 'LV';
    // multi selection  LV
    public $multiSelect = false;
    // selected id
    public $itemSelected = null;
    // id to show
    public $itemToShow = null;
    // id to play
    public $itemToPlay = null;

    public $cancel = false;
    // op
    public $op = '';
    // lang
    public $lang = null;
    // the recordset
    public $rs = null;
    public $id;

    public function _getOpShowItemId()
    {
        return 'op_listview_opshowitem_';
    }

    public function _getOpPlayItemId()
    {
        return 'op_listview_opplayitem_';
    }

    public function _getOpSelectItemId()
    {
        return 'op_listview_opselectitem_';
    }

    public function _getOpDeselectItemId()
    {
        return 'op_listview_opdeselectitem_';
    }

    public function _getOpNewItemId()
    {
        return 'op_listview_opnewitem_';
    }

    public function _getOpCreateItemId()
    {
        return 'op_listview_opcreateitem_';
    }

    public function _getIdCreateItemId()
    {
        return 'op_listview_idcreateitem_';
    }

    public function _getIdShowItemId()
    {
        return 'op_listview_idshowitem_';
    }

    public function _getIdPlayItemId()
    {
        return 'op_listview_idplayitem_';
    }

    public function _getIdSelectItemId()
    {
        return 'op_listview_idselectitem_';
    }

    public function _getCancelId()
    {
        return 'op_listview_cancel_';
    }

    public function _getIdOpStatus()
    {
        return 'op_listview_idopstate_';
    }

    public function _getIdInitRowId()
    {
        return 'op_listview_startRow_';
    }

    public function getIdShowItem()
    {
        return $this->itemToShow;
    }

    public function getIdPlayItem()
    {
        return $this->itemToPlay;
    }

    public function getIdSelectedItem()
    {
        return $this->itemSelected;
    }

    public function _getTitle()
    {
        return $this->title;
    }

    public function _getRowsPage()
    {
        return 20;
    }

    public function _getStartRow()
    {
        return $this->startRow;
    }

    public function _getCancelLabel()
    {
        return $this->lang->def('_CANCEL');
    }

    public function _getCancelAlt()
    {
        return $this->lang->def('_CANCEL');
    }

    public function _getCancelImage()
    {
        return getPathImage() . 'standard/cancel.png';
    }

    public function _getAddLabel()
    {
        return $this->lang->def('_NEW_ITEM');
    }

    public function _getAddUrl()
    {
        return $this->_getOpNewItemId();
    }

    public function _getAddAlt()
    {
        return '+';
    }

    public function _getAddImage()
    {
        return getPathImage() . 'standard/add.png';
    }

    public function _getCreateLabel()
    {
        return $this->lang->def('_DIRECTORY_NEWGROUP');
    }

    public function _getCreateUrl()
    {
        return $this->_getOpCreateItemId();
    }

    public function _getCreateAlt()
    {
        return $this->lang->def('_DIRECTORY_NEWGROUP');
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'standard/add.png';
    }

    /**
     * This method must return an array whit a number of
     * columns equal to that we want display.
     * Each element of array is an array whit:
     * ['hLabel'] => HeaderLable
     * ['hClass'] => HeaderClass
     * ['filedClass'] => Field class
     * ['data'] => Field in data (name or index)
     * ['toDisplay'] => toDisplay
     * ['sortable'] => sortable.
     **/
    public function _getCols()
    {
        $totCol = $this->data->getFieldCount();
        $fieldInfos = $this->data->getFieldsInfo();
        $colInfos = [];
        foreach ($fieldInfos as $fname => $finfo) {
            $colInfos[] = ['hLabel' => $fname,
                        'hClass' => '',
                        'fieldClass' => '',
                        'data' => $fname,
                        'toDisplay' => true,
                        'sortable' => false, ];
        }

        return $colInfos;
    }

    public function _getLinkPagination()
    {
        return 'index.php?modname=pubrepo&amp;op=pubrepo&amp;ord='
                . $this->_getOrd()
                . '&amp;ini=';
    }

    public function _getOrd()
    {
        // standard implementation get order from _GET super array
        if (isset($_GET['ord'])) {
            return $_GET['ord'];
        } else {
            return '';
        }
    }

    public function __construct($title, &$data, &$rend, $id)
    {
        $this->title = $title;
        $this->data = &$data;
        $this->rend = $rend;
        $this->id = $id;
        $this->itemSelected = [];
        $this->lang = &FormaLanguage::createInstance('standard', 'framework');
        $this->startRow = FormaLms\lib\Get::req('ini', DOTY_INT, 0);
    }

    public function getOp()
    {
        return $this->op;
    }

    public function setInsNew($insNew)
    {
        $this->insNew = $insNew;
    }

    public function &getDataRetrivier()
    {
        return $this->data;
    }

    public function extendedParsing($arrayState)
    {
    }

    public function parsePositionData($arrayState)
    {
        // preserve state
        if (isset($arrayState[$this->_getCancelId()])) {
            $this->cancel = true;
        } else {
            $this->cancel = false;
        }

        $session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if (isset($arrayState[$this->_getIdInitRowId()])) {
            $this->startRow = (key($arrayState[$this->_getIdInitRowId()]) - 1) * $this->_getRowsPage();
            $session->set($this->id . '_cache_page', $this->startRow);
        } elseif (empty($arrayState)) {
            if ($session->has($this->id . '_cache_page') && $session->get($this->id . '_cache_page') != false) {
                $this->startRow = $session->get($this->id . '_cache_page');
            }
        } else {
            if ($session->has($this->id . '_cache_page')) {
                $session->remove($this->id . '_cache_page');
            }
            $this->startRow = 0;
        }

        if (isset($arrayState[$this->_getIdShowItemId()])) {
            $this->itemToShow = $arrayState[$this->_getIdShowItemId()];
        }
        if (isset($arrayState[$this->_getIdPlayItemId()])) {
            $this->itemToPlay = $arrayState[$this->_getIdPlayItemId()];
        }
        if (isset($arrayState[$this->_getIdSelectItemId()])) {
            $this->itemSelected = @Util::unserialize(urldecode($arrayState[$this->_getIdSelectItemId()]));
        }

        // handle actions that change display mode (op)
        if (isset($arrayState[$this->_getOpNewItemId()])) {
            $this->op = 'newitem';
        }

        if ((isset($arrayState[$this->id])) && (is_array($arrayState[$this->id]))) {
            if (isset($arrayState[$this->id][$this->_getOpCreateItemId()])) {
                $this->op = 'newitem';
            }
        }

        // parse for actions
        if (isset($arrayState[$this->_getOpCreateItemId()])) {
            $this->data->InsertItem($arrayState);
            $this->op = 'display'; // diplay
        }

        foreach ($arrayState as $nameField => $valueField) {
            if (strstr($nameField, $this->_getOpShowItemId())) {
                $id = substr($nameField, strlen($this->_getOpShowItemId()));
                $this->itemToShow = $id;
                $this->op = 'showitem';
            } elseif (strstr($nameField, $this->_getOpPlayItemId())) {
                $id = substr($nameField, strlen($this->_getOpPlayItemId()));
                $this->itemToPlay = $id;
                $this->op = 'playitem';
            } elseif (strstr($nameField, $this->_getOpSelectItemId())) {
                $id = substr($nameField, strlen($this->_getOpSelectItemId()));
                if ($this->multiSelect && !in_array((int) $id, $this->itemSelected)) {
                    $this->itemSelected[] = (int) $id;
                } else {
                    $this->itemSelected = [(int) $id];
                }
                $this->op = 'selectitem';
            } elseif (strstr($nameField, $this->_getOpDeselectItemId())) {
                $id = substr($nameField, strlen($this->_getOpDeselectItemId()));
                $key = array_search($id, $this->itemSelected);
                if ($key !== false) {
                    unset($this->itemSelected[$key]);
                }
            }
        }

        $this->extendedParsing($arrayState);

        if (($this->op == '') && isset($arrayState[$this->_getIdOpStatus()])) {
            if ($this->cancel) {
                $this->op = 'display';
            } // diplay
            else {
                $this->op = $arrayState[$this->_getIdOpStatus()];
            }
        }
    }

    public function printState()
    {
        $out = '<input type="hidden"'
            . ' id="' . $this->_getIdSelectItemId() . '"'
            . ' name="' . $this->_getIdSelectItemId() . '"'
            . ' value="' . urlencode(Util::serialize($this->itemSelected)) . '" />' . "\n";
        $out .= '<input type="hidden"'
            . ' id="' . $this->_getIdPlayItemId() . '"'
            . ' name="' . $this->_getIdPlayItemId() . '"'
            . ' value="' . $this->getIdPlayItem() . '" />' . "\n";
        $out .= '<input type="hidden"'
            . ' id="' . $this->_getIdShowItemId() . '"'
            . ' name="' . $this->_getIdShowItemId() . '"'
            . ' value="' . $this->getIdShowItem() . '" />' . "\n";
        $out .= '<input type="hidden"'
            . ' id="' . $this->_getIdOpStatus() . '"'
            . ' name="' . $this->_getIdOpStatus() . '"'
            . ' value="' . $this->op . '" />' . "\n";

        return $out;
    }

    public function getRows($start, $len)
    {
        $this->rs = $this->data->getRows($start, $len);
    }

    public function getTotRowsWhithFilter()
    {
        return sql_num_rows($this->data->rs_f);
    }

    public function getLoadedRows()
    {
        return sql_num_rows($this->rs);
    }

    public function getTotalRows()
    {
        return $this->data->getTotalRows();
    }

    public function fetchRecord()
    {
        return $this->data->fetchRecord();
    }

    public function printOut()
    {
        $out = $this->rend->OpenTable($this->_getTitle());

        $this->getRows($this->_getStartRow(), $this->_getRowsPage());

        $totRow = $this->getTotalRows();

        if ($totRow == -1) {
            $totRow = $this->getLoadedRows();
        }
        $colInfo = $this->_getCols();
        $colData = $colInfo;
        $out .= $this->rend->WriteHeaderCss($colInfo);

        while ($values = $this->fetchRecord()) {
            foreach ($colInfo as $key => $fieldInfo) {
                $colData[$key]['data'] = $values[$fieldInfo['data']];
            }
            $out .= $this->rend->WriteRowCss($colData);
        }

        if ($this->insNew) {
            /*$out .= $this->rend->WriteAddRow('<input type="image" class="tree_view_image" '
                .' src="'.$this->_getCreateImage().'"'
                .' id="'.$this->id.'_'.$this->_getOpCreateItemId().'" '
                .' name="'.$this->id.'['.$this->_getOpCreateItemId().'][0]" '
                .' title="'.$this->_getCreateLabel().'" '
                .' alt="'.$this->_getCreateAlt().'" />'
                .$this->lang->def('_ADD') );
                */

            $out .= $this->rend->WriteAddRow('<input type="submit" class="transparent_add_button"'
                . ' id="' . $this->id . '_' . $this->_getOpCreateItemId() . '" '
                . ' name="' . $this->id . '[' . $this->_getOpCreateItemId() . '][0]" '
                . ' value="' . $this->lang->def('_ADD') . '"'
                . ' title="' . $this->_getCreateLabel() . '" '
                . ' alt="' . $this->_getCreateAlt() . '" />');
        }
        $out .= $this->rend->CloseTable();

        $this->rend->initNavBar($this->_getIdInitRowId(), 'button');

        $out .= $this->rend->getNavBar($this->_getStartRow(), $totRow);

        $out .= $this->printState();

        return $out;
    }

    public function printInsert()
    {
        $this->printState();

        return '<input type="image" class="tree_view_image" '
            . ' src="' . $this->_getCreateImage() . '"'
            . ' id="' . $this->id . '_' . $this->_getOpCreateItemId() . '" '
            . ' name="' . $this->id . '[' . $this->_getOpCreateItemId() . '][0]" '
            . ' title="' . $this->_getCreateLabel() . '" '
            . ' alt="' . $this->_getCreateAlt() . '" />';
    }
}
