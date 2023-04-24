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

/*
 * @package  admin-library
 * @subpackage interaction
 * @version 	$Id: lib.treeview.php 992 2007-03-08 17:16:37Z fabio $
 */

/*define("ALT_EXPAND_INTER", "+");
define("ALT_COLLAPSE_INTER", "-");
define("ALT_VERT_INTER", "|");
define("ALT_BRANCH_INTER", "T");
define("ALT_BRANCH_END", "L");
define("ALT_EMPTY", ".");
define("ALT_EXPAND_END", "+");
define("ALT_COLLAPSE_END", "-");
define("ALT_TITLE_CLOSE", "c");
define("ALT_TITLE_OPEN_LEAF", "o");
define("ALT_TITLE_OPEN", "o");
define("ALT_ROOT", "/");*/

define('REND_EXPAND_INTER', 'treeview/menu_tee_plus.gif');
define('REND_COLLAPSE_INTER', 'treeview/menu_tee_minus.gif');
define('REND_VERT_INTER', 'treeview/menu_bar.gif');
define('REND_BRANCH_INTER', 'treeview/menu_tee.gif');
define('REND_BRANCH_END', 'treeview/menu_corner.gif');
define('REND_EMPTY', 'treeview/menu_pixel.gif');
define('REND_EXPAND_END', 'treeview/menu_corner_plus.gif');
define('REND_COLLAPSE_END', 'treeview/menu_corner_minus.gif');
define('REND_TITLE_CLOSE', 'treeview/menu_folder_closed.png');
define('REND_TITLE_OPEN_LEAF', 'treeview/menu_folder_open_leaf.png');
define('REND_TITLE_OPEN', 'treeview/menu_folder_open.png');
define('REND_ROOT', 'treeview/root.png');

define('TVERR_OK', '0');
define('TVERR_MOVEONDESCENDANT', '-1');

class TreeView
{
    // tree db
    public $tdb;
    public $id;
    public $posTree;
    public $posFlat;
    public $expandList;
    public $compressList;
    public $selectedFolder;
    public $selectedFolderData;
    public $op;
    //var $itemToShow = FALSE;
    public $itemToPlay = false;

    // support object
    public $listView = null;

    // refresh tree
    public $refresh;

    // error code
    public $error = 0;

    // cancel action
    public $cancel = false;

    // name of the root
    public $rootname = '';

    // lang object
    public $lang = null;

    public $aclManager;

    protected $session;

    public function __construct($tdb, $id, $rootname = 'root')
    {
        $this->tdb = $tdb;
        $this->id = $id;
        $this->posTree = [0];
        $this->posFlat = [0];
        $this->expandList = [];
        $this->compressList = [];
        $this->selectedFolder = 0;
        $this->op = '';
        $this->rootname = $rootname;
        $this->lang = FormaLanguage::createInstance('treeview', 'framework');
        $this->aclManager = new FormaACLManager();
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function setLanguage(&$lang)
    {
        $this->lang = &$lang;
    }

    public function deRegisterLang()
    {
        $this->lang = null;
    }

    public function reRegisterLang()
    {
        $this->lang = &FormaLanguage::createInstance('treeview', 'framework');
    }

    public function _getStateId()
    {
        return 'treeview_state_' . $this->id;
    }

    public function _getExpandId()
    {
        return 'treeview_expand_' . $this->id;
    }

    public function _getCompressId()
    {
        return 'treeview_compress_' . $this->id;
    }

    public function _getSelectedId()
    {
        return 'treeview_selected_' . $this->id;
    }

    public function _getCancelId()
    {
        return 'treeview_cancel_' . $this->id;
    }

    public function _getCreateFolderId()
    {
        return 'treeview_create_folder_' . $this->id;
    }

    public function _getRenameFolderId()
    {
        return 'treeview_rename_folder_' . $this->id;
    }

    public function _getMoveFolderId()
    {
        return 'treeview_move_folder_' . $this->id;
    }

    public function _getDeleteFolderId()
    {
        return 'treeview_delete_folder_' . $this->id;
    }

    public function _getFolderNameId()
    {
        return 'treeview_folder_name_' . $this->id;
    }

    public function _getExpandActionId()
    {
        return 'treeview_doexpand_' . $this->id;
    }

    public function _getCompressActionId()
    {
        return 'treeview_docompress_' . $this->id;
    }

    public function _getOpNewFolderId()
    {
        return 'treeview_opnewfolder_' . $this->id;
    }

    public function _getOpRenameFolderId()
    {
        return 'treeview_oprenamefolder_' . $this->id;
    }

    public function _getOpMoveFolderId()
    {
        return 'treeview_opmovefolder_' . $this->id;
    }

    public function _getOpDeleteFolderId()
    {
        return 'treeview_opdeletefolder_' . $this->id;
    }

    public function _getOpSaveFile()
    {
        return 'treeview_opsavefile_' . $this->id;
    }

    //function _getOpShowItemId() 	{ return 'treeview_opshowitem_'	.$this->id;	}
    public function _getOpPlayItemId()
    {
        return 'treeview_opplayitem_' . $this->id;
    }

    public function _getOpPlayItemImg()
    {
        return getPathImage() . 'standard/view.png';
    }

    //function _getIdShowItemId() 	{ return 'treeview_idshowitem_'	.$this->id;	}
    public function _getIdPlayItemId()
    {
        return 'treeview_idplayitem_' . $this->id;
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

    public function _getFolderNameLabel()
    {
        return $this->lang->def('_NAME');
    }

    public function _getAddLabel()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getAddUrl()
    {
        return $this->_getOpNewFolderId();
    }

    public function _getAddAlt()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getAddImage()
    {
        return getPathImage() . 'standard/add.png';
    }

    public function _getCreateLabel()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getCreateUrl()
    {
        return $this->_getCreateFolderId();
    }

    public function _getCreateAlt()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'standard/add.png';
    }

    public function _getRenameLabel()
    {
        return $this->lang->def('_MODe');
    }

    public function _getRenameUrl()
    {
        return $this->_getOpRenameFolderId();
    }

    public function _getRenameAlt()
    {
        return $this->lang->def('_MOD');
    }

    public function _getRenameImage()
    {
        return getPathImage() . 'treeview/rename.png';
    }

    public function _getDeleteLabel()
    {
        return $this->lang->def('_DEL');
    }

    public function _getDeleteUrl()
    {
        return $this->_getOpDeleteFolderId();
    }

    public function _getDeleteAlt()
    {
        return $this->lang->def('_DEL');
    }

    public function _getDeleteImage()
    {
        return getPathImage() . 'standard/delete.png';
    }

    public function _getMoveLabel()
    {
        return $this->lang->def('_MOVE');
    }

    public function _getMoveUrl()
    {
        return $this->_getOpMoveFolderId();
    }

    public function _getMoveAlt()
    {
        return $this->lang->def('_MOVE');
    }

    public function _getMoveImage()
    {
        return getPathImage() . 'treeview/move.png';
    }

    public function _getMoveTargetLabel()
    {
        return Lang::t('_TARGETMOVEFOLDER', 'storage');
    }

    public function _getOtherActions()
    {
        return [];
    }

    public function _getChildrens($idFolder)
    {
        return $this->tdb->getChildrensIdById($idFolder);
    }

    public function getSelectedFolderId()
    {
        return $this->selectedFolder;
    }

    public function isFolderSelected()
    {
        return $this->selectedFolder != 0;
    }

    public function getSelectedFolderData()
    {
        return $this->selectedFolderData;
    }

    public function getRootName()
    {
        return $this->rootname;
    }

    //function getItemToShow() {	return $this->itemToShow; }
    public function getItemToPlay()
    {
        return (int) $this->itemToPlay;
    }

    /**
     * functions canXXXX()
     * return TRUE if the XXXX action can be done in tree.
     **/
    public function canMove()
    {
        return $this->isFolderSelected();
    }

    public function canRename()
    {
        return $this->isFolderSelected();
    }

    public function canDelete()
    {
        return $this->isFolderSelected();
    }

    public function canAdd()
    {
        return true;
    }

    /**
     * functions canInlineXXXX()
     * return TRUE if the XXXX action exist in tree.
     **/
    public function canInlineMove()
    {
        return false;
    }

    public function canInlineRename()
    {
        return false;
    }

    public function canInlineDelete()
    {
        return false;
    }

    /**
     * functions canInlineXXXXItem()
     * return TRUE if the XXXX action is available for specific item.
     **/
    public function canInlineMoveItem(&$stack, $level)
    {
        return false;
    }

    public function canInlineRenameItem(&$stack, $level)
    {
        return false;
    }

    public function canInlineDeleteItem(&$stack, $level)
    {
        return false;
    }

    public function setListView(&$listView)
    {
        $this->listView = &$listView;
    }

    public function getListView()
    {
        return $this->listView;
    }

    public function getTreeDb()
    {
        return $this->tdb;
    }

    //return the folder print name
    public function getFolderPrintName(&$folder)
    {
        if ($folder->id == 0) {
            return $this->rootname;
        } else {
            return str_replace('"', '&quot;', strip_tags($folder->getFolderName()));
        }
    }

    public function getFolderPrintOther(&$folder)
    {
        return '';
    }

    public function extendedParsing($arrayState, $arrayExpand, $arrayCompress)
    {
    }

    public function beforeDeleteItem(&$folder)
    {
        return true;
    }

    // make flat array from tree array. Also execute expand and compress opertations
    public function _visitArrayDeep(&$tree, &$result, &$expand, &$compress)
    {
        reset($tree);
        $counter = 0;
        foreach ($tree as $key => $val) {
            if ($this->refresh) {
                if ($childrens = $this->_getChildrens($key)) {
                    $childrens = array_flip($childrens);
                    foreach ($childrens as $keynew => $valnew) {
                        if (!is_array($tree[$key])) {
                            $tree[$key] = [];
                        }
                        if (isset($tree[$key][$keynew])) {
                            // ----- put this value in $childrens
                            if (is_array($tree[$key][$keynew])) {
                                $childrens[$keynew] = $tree[$key][$keynew];
                            }
                        }
                    }
                    reset($childrens);
                    $tree[$key] = $childrens;
                } else {
                    $tree[$key] = $counter;
                }
            }
            ++$counter;
            $result[] = $key;
            if (array_key_exists($key, $expand)) {
                // to expand
                if ($childrens = $this->_getChildrens($key)) {
                    $tree[$key] = array_flip($childrens);
                }
            } elseif (array_key_exists($key, $compress)) {
                // to compress
                $tree[$key] = $key;
                $val = $key;
            }

            if (is_array($tree[$key])) {
                $this->_visitArrayDeep($tree[$key], $result, $expand, $compress);
            }
        }
        reset($tree);
    }

    /**
     * Set treeview obj datas.
     *
     * retrieve data from array of elements
     * Normally $arrayState is $_POST
     * $arrayExpand is $_GET
     * $arrayCompress id $_GET
     **/
    public function parsePositionData($arrayState, $arrayExpand, $arrayCompress)
    {
        // optimistic prevision :-P

        $this->refresh = false;
        $this->cancel = isset($arrayState[$this->_getCancelId()]);

        if (isset($arrayState[$this->_getSelectedId()])) {
            $this->selectedFolder = $arrayState[$this->_getSelectedId()];
        }

        if (isset($arrayState[$this->_getIdPlayItemId()])) {
            $this->itemToPlay = (int) $arrayState[$this->_getIdPlayItemId()];
        }

        if (array_key_exists('treeview_opplayitem_' . $this->id, $arrayState)) {
            if (is_array($arrayState[$this->id]['treeview_opplayitem_' . $this->id])) {
                $this->itemToPlay = (int) array_key_first($arrayState[$this->id]['treeview_opplayitem_' . $this->id]);

                if (!isset($arrayState[$this->_getIdPlayItemId()])) {
                    $arrayState[$this->_getIdPlayItemId()] = $this->itemToPlay;
                }
                $_POST[$this->_getIdPlayItemId()] = $this->itemToPlay;
            }
        }
        if (isset($arrayExpand[$this->_getExpandId()])) {
            $this->expandList = explode(',', $arrayExpand[$this->_getExpandId()]);
        }
        if (isset($arrayCompress[$this->_getCompressId()])) {
            $this->compressList = explode(',', $arrayCompress[$this->_getCompressId()]);
        }

        // remove elements present in both lists
        $arrayIntersect = array_intersect($this->expandList, $this->compressList);
        $this->expandList = array_diff($this->expandList, $arrayIntersect);
        $this->compressList = array_diff($this->compressList, $arrayIntersect);

        // for faster search ...
        $this->expandList = array_flip($this->expandList);
        $this->compressList = array_flip($this->compressList);

        // handle some immediate action
        if (isset($arrayState[$this->id])) {
            foreach ($arrayState[$this->id] as $key => $action) {
                if ($key == $this->_getOpRenameFolderId()) {
                    if (is_array($action)) {
                        $this->selectedFolder = key($action);
                    }
                    $this->op = 'renamefolder';
                } elseif ($key == $this->_getOpDeleteFolderId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        if ($id > 0) {
                            $this->op = 'deletefolder';
                            $this->selectedFolder = $id;
                        }
                    }
                } elseif ($key == $this->_getOpMoveFolderId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        if ($id > 0) {
                            $this->op = 'movefolder';
                            $this->selectedFolder = $id;
                        }
                    }
                } elseif ($key == $this->_getOpPlayItemId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        if ($id > 0) {
                            $this->op = 'playitem';
                            $this->itemToPlay = (int) $id;
                        }
                    }
                }
            }
        }

        // expand
        foreach ($_POST as $nameField => $valueField) {
            if (strstr($nameField, $this->_getExpandActionId()) && !$this->cancel) {
                $id = substr($nameField, strlen($this->_getExpandActionId()));
                $this->expandList[$id] = $id;
            } elseif (strstr($nameField, $this->_getCompressActionId()) && !$this->cancel) {
                $id = substr($nameField, strlen($this->_getCompressActionId()));
                $this->compressList[$id] = $id;
            } elseif (strstr($nameField, $this->_getSelectedId()) && !$this->cancel) {
                $id = substr($nameField, strlen($this->_getSelectedId()));
                if (strlen($id) > 0) {
                    $this->selectedFolder = $id;
                    $this->expandList[$id] = $id;
                }
            } elseif (strstr($nameField, $this->_getOpPlayItemId()) && !$this->cancel) {
                $id = substr($nameField, strlen($this->_getOpPlayItemId()));
                $this->op = 'playitem';
                $this->itemToPlay = (int) $id;
            } elseif (strstr($nameField, $this->_getOpDeleteFolderId()) && !$this->cancel) {
                // TODO: Remove this elseif branch (moved on previous foreach
                $id = substr($nameField, strlen($this->_getOpDeleteFolderId()));
                if ($id > 0) {
                    $this->op = 'deletefolder';
                    $this->selectedFolder = $id;
                }
            } elseif (strstr($nameField, $this->_getOpMoveFolderId()) && !$this->cancel) {
                // TODO: Remove this elseif branch (moved on previous foreach
                $id = substr($nameField, strlen($this->_getOpMoveFolderId()));
                if ($id > 0) {
                    $this->op = 'movefolder';
                    $this->selectedFolder = $id;
                }
            } elseif (strstr($nameField, $this->_getOpRenameFolderId()) && !$this->cancel) {
                // TODO: Remove this elseif branch (moved on previous foreach
                $id = substr($nameField, strlen($this->_getOpRenameFolderId()));
                if ($id > 0) {
                    $this->op = 'renamefolder';
                    $this->selectedFolder = $id;
                }
            }
        }

        // create folder
        if (isset($arrayState[$this->_getCreateFolderId()]) && !$this->cancel && isset($arrayState[$this->_getFolderNameId()])) {
            $folderName = $arrayState[$this->_getFolderNameId()];
            if (trim($folderName) != '') {
                $this->tdb->addFolderById($this->selectedFolder, $folderName);
                $this->refresh = true;
            }
        }
        // handle actions that change display mode (op)
        if (isset($arrayState[$this->_getOpNewFolderId()]) && !$this->cancel) {
            $this->op = 'newfolder';
        }

        // delete folder
        if (isset($arrayState[$this->_getDeleteFolderId()])
            && !$this->cancel && $this->selectedFolder) {
            $folder = $this->tdb->getFolderById($this->selectedFolder);
            if ($folder !== null) {
                if ($this->beforeDeleteItem($folder) !== false) {
                    $this->tdb->_deleteTree($folder);
                    $this->refresh = true;
                }
            }
        }
        // handle actions that change display mode (op)
        if (isset($arrayState[$this->_getOpDeleteFolderId()]) && !$this->cancel) {
            $this->op = 'deletefolder';
        }

        // rename folder
        if (isset($arrayState[$this->_getRenameFolderId()]) && !$this->cancel) {
            $folderName = $arrayState[$this->_getFolderNameId()];
            if (trim($folderName) != '') {
                $folder = $this->tdb->getFolderById($this->selectedFolder);
                $this->tdb->renameFolder($folder, $folderName);
                $this->refresh = true;
            }
        }
        // handle actions that change display mode (op)
        if (isset($arrayState[$this->_getOpRenameFolderId()]) && !$this->cancel) {
            $this->op = 'renamefolder';
        }
        // move folder
        if (isset($arrayState[$this->_getOpMoveFolderId()]) && !$this->cancel) {
            $this->op = 'movefolder';
        }
        if (isset($arrayState[$this->_getMoveFolderId()]) && !$this->cancel) {
            $folderid = $arrayState[$this->_getFolderNameId()];
            $dstfolder = &$this->tdb->getFolderById($this->selectedFolder);
            $folder = &$this->tdb->getFolderById($folderid);
            if ($this->tdb->checkAncestor($folder, $dstfolder)) {
                $this->op = 'treeview_error';
                $this->error = TVERR_MOVEONDESCENDANT;
            } else {
                $this->tdb->moveFolder($folder, $dstfolder);
                $this->op = '';
                $this->refresh = true;
            }
        }

        if (isset($arrayState[$this->_getOpSaveFile()]) && !$this->cancel) {
            $this->op = 'save';
        }

        $this->extendedParsing($arrayState, $arrayExpand, $arrayCompress);

        if (isset($arrayState[$this->_getStateId()])) {
            $this->posTree = unserialize(stripslashes($arrayState[$this->_getStateId()]));
            // root is always expanded
            if (!is_array($this->posTree[0])) {
                $childrensRoot = $this->_getChildrens(0);
                if (is_array($childrensRoot)) {
                    $this->posTree[0] = array_flip($childrensRoot);
                }
            }
            $this->posFlat = [];
            $this->_visitArrayDeep($this->posTree, $this->posFlat, $this->expandList, $this->compressList);
        } else {
            // root is always expanded
            if (!is_array($this->posTree[0])) {
                $childrensRoot = $this->_getChildrens(0);
                if (is_array($childrensRoot)) {
                    $this->posTree[0] = array_flip($childrensRoot);
                }
            }
            $this->posFlat = [];
            $this->_visitArrayDeep($this->posTree, $this->posFlat, $this->expandList, $this->compressList);
        }
        if (!in_array($this->selectedFolder, $this->posFlat)) {
            if (count($this->compressList) > 0) {
                $this->selectedFolder = current($this->compressList);
            } else {
                $this->selectedFolder = $arrayState[$this->_getSelectedId()] ?? '0' ;
            }
        }
    }

    public function expand($id)
    {
        $this->expandList[$id] = $id;
    }

    public function _retrieveData()
    {
        if (is_array($this->posFlat) && count($this->posFlat) > 0) {
            $coll = $this->tdb->getFoldersCollection($this->posFlat);
        } else {
            $coll = false;
        }

        return $coll;
    }

    public function autoLoad()
    {
        switch ($this->op) {
            case 'display':
                $this->load();
                // no break
            case 'newfolder':
                $this->loadNewFolder();
        }
    }

    public function load()
    {
        $isFirst = true;
        $tree = $this->printState();
        $coll = $this->_retrieveData();
        $stack = [];
        $level = 0;
        $count = 0;

        $folder = $this->tdb->getRootFolder();
        $stack[$level] = [];
        $stack[$level]['folder'] = $folder;
        $stack[$level]['childs'] = $this->posTree[0];
        $stack[$level]['isLast'] = true;
        $stack[$level]['isLeaf'] = false;
        $stack[$level]['isExpanded'] = false;
        $stack[$level]['idSeq'] = $folder->id;
        $stack[$level]['isFirst'] = $isFirst;

        $tree .= '<div class="panel panel-default panel-treeview">' . "\n";
        $tree .= '<div class="panel-heading">';
        $tree .= $this->printElement($stack, $level);
        $tree .= '</div>';
        $tree .= '<table class="table table-striped table-hover">' . "\n";

        ++$level;

        if ($coll !== false) {
            while ($folder = $coll->getNext()) {
                ++$count;
                $key = key($stack[$level - 1]['childs']);
                $val = current($stack[$level - 1]['childs']);
                next($stack[$level - 1]['childs']);
                $stack[$level] = [];
                $stack[$level]['folder'] = $folder;
                $stack[$level]['childs'] = $val;
                $stack[$level]['isFirst'] = $isFirst;
                $isFirst = false;

                if (current($stack[$level - 1]['childs'])) {
                    $stack[$level]['isLast'] = false;
                } else {
                    $stack[$level]['isLast'] = true;
                }

                if (is_array($val)) {
                    $stack[$level]['isExpanded'] = true;
                } else {
                    $stack[$level]['isExpanded'] = false;
                }

                if ($folder->countChildrens() > 0) {
                    $stack[$level]['isLeaf'] = false;
                } else {
                    $stack[$level]['isLeaf'] = true;
                }

                $stack[$level]['idSeq'] = $stack[$level - 1]['idSeq'] . '.' . $folder->id;

                // if( $count % 2 == 0 )
                // 	$tree .= '<div class="TreeViewRowOdd" id="row_'.$stack[$level]['idSeq'].'">';
                // else
                // 	$tree .= '<div class="TreeViewRowEven" id="row_'.$stack[$level]['idSeq'].'">';
                $tree .= '<tr id="row_' . $stack[$level]['idSeq'] . '">';
                $tree .= $this->printElement($stack, $level);
                // $tree .= '</div>';
                $tree .= '</tr>';

                if (is_array($val)) {
                    ++$level;
                    $isFirst = true;
                } elseif ($stack[$level]['isLast']) {
                    while ($stack[$level]['isLast'] && $level > 1) {
                        --$level;
                    }
                }
            }
        } else {
            $tree .= "\n<!-- coll is null -->";
        }

        /* echo "\n<!-- diag ";
        print_r( $this->posTree );
        print_r( $this->posFlat );
        print_r( $this->expandList );
        print_r( $this->compressList );
        echo "-->\n"; */

        $tree .= '</table>' . "\n";
        $tree .= '</div>' . "\n";

        return $tree;
    }

    public function getState()
    {
        return ['selectedFolder' => $this->selectedFolder,
                        'itemToPlay' => $this->getItemToPlay(),
                        'posTree' => $this->posTree, ];
    }

    public function setState($arr_state)
    {
        if (isset($arr_state['selectedFolder'])) {
            $this->selectedFolder = $arr_state['selectedFolder'];
        }
        if (isset($arr_state['itemToPlay'])) {
            $this->itemToPlay = (int) $arr_state['itemToPlay'];
        }
        if (isset($arr_state['posTree'])) {
            $this->posTree = $arr_state['posTree'];
        }
    }

    public function printState($echoit = true)
    {
        $ot = '<input type="hidden"'
            . ' id="' . $this->_getSelectedId() . '"'
            . ' name="' . $this->_getSelectedId() . '"'
            . ' value="' . $this->selectedFolder . '" />' . "\n";
        $ot .= '<input type="hidden"'
            . ' id="' . $this->_getIdPlayItemId() . '"'
            . ' name="' . $this->_getIdPlayItemId() . '"'
            . ' value="' . $this->getItemToPlay() . '" />' . "\n";
        $ot .= '<input type="hidden"'
            . ' id="' . $this->_getStateId() . '"'
            . ' name="' . $this->_getStateId() . '"'
            . ' value="' . addslashes(serialize($this->posTree)) . '" />' . "\n";

        return $ot;
    }

    public function getPreFolderName(&$folder)
    {
        return '';
    }

    public function printElement(&$stack, $level)
    {
        // $tree = '<div class="TreeViewRowBase">';
        $tree = '<td>';
        $id = ($stack[$level]['isExpanded']) ? ($this->_getCompressActionId()) : ($this->_getExpandActionId());
        $id .= $stack[$level]['folder']->id;
        for ($i = 0; $i <= $level; ++$i) {
            list($classImg, $imgFileName, $imgAlt) = $this->getImage($stack, $i, $level);
            if ($i != ($level - 1) || $stack[$level]['isLeaf']) {
                $tree .= '<img src="' . getPathImage() . $imgFileName . '" '
                        . 'class="' . $classImg . '" alt="' . $imgAlt . '" '
                        . 'title="' . $imgAlt . '" />';
            } else {
                $tree .= '<input type="submit" class="' . $classImg . '" value="'
                    . '" name="' . $id . '" id="seq_' . $stack[$level]['idSeq'] . 'img" />';
            }
        }
        if ($stack[$level]['folder']->id == $this->selectedFolder) {
            $this->selectedFolderData = $stack[$level];
            $classStyle = 'TreeItemSelected';
        } else {
            $classStyle = 'TreeItem';
        }
        $tree .= $this->getPreFolderName($stack[$level]['folder']);
        $tree .= '<input type="submit" class="' . $classStyle . '" value="'
            . $this->getFolderPrintName($stack[$level]['folder'])
            . '" name="'
            . $this->_getSelectedId() . $stack[$level]['folder']->id
            . '" id="seq_' . $stack[$level]['idSeq'] . '" '
            . $this->getFolderPrintOther($stack[$level]['folder'])
            . ' />';
        // $tree .= '</div>';

        $tree .= $this->printActions($stack, $level);

        //$tree .= '</td>';
        return $tree . "\n";
    }

    public function printActions(&$stack, $level)
    {
        $tree = '';
        if ($this->canInlineDelete()) {
            if ((($stack[1]['folder']->tdb->table != 'learning_repo') && ($this->canInlineDeleteItem($stack, $level) && !FormaLms\lib\Get::cfg('demo_mode')))
            || (($stack[1]['folder']->tdb->table == 'learning_repo') && ($stack[1]['folder']->otherValues[5] == $this->session->get('public_area_idst') || \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_GODADMIN))) {
                $tree .= '<input type="image"'
                        . ' class="tree_view_image" '
                        . ' src="' . $this->_getDeleteImage() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpDeleteFolderId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpDeleteFolderId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getDeleteLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getDeleteLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
            /*$tree .= '<input type="submit" class="TVActionDelete" value="" name="'
                .$this->_getOpDeleteFolderId().$stack[$level]['folder']->id .'"'
                .' title="'.$this->_getDeleteLabel().': '.$this->getFolderPrintName( $stack[$level]['folder']).'" />';*/
            } else {
                $tree .= '<div class="TVActionEmpty">&nbsp;</div>';
            }
        }
        if ($this->canInlineMove()) {
            if ((($stack[1]['folder']->tdb->table != 'learning_repo') && ($this->canInlineMoveItem($stack, $level) && !FormaLms\lib\Get::cfg('demo_mode')))
            || (($stack[1]['folder']->tdb->table == 'learning_repo') && ($stack[1]['folder']->otherValues[5] == $this->session->get('public_area_idst') || \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_GODADMIN))) {
                $tree .= '<input type="image"'
                        . ' class="tree_view_image" '
                        . ' src="' . $this->_getMoveImage() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpMoveFolderId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpMoveFolderId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getMoveLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getMoveLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
            /*$tree .= '<input type="submit" class="TVActionMove" value="" name="'
                .$this->_getOpMoveFolderId().$stack[$level]['folder']->id .'"'
                .' title="'.$this->_getMoveLabel().': '.$this->getFolderPrintName( $stack[$level]['folder']).'" />';*/
            } else {
                $tree .= '<div class="TVActionEmpty">&nbsp;</div>';
            }
        }
        if ($this->canInlineRename()) {
            if ((($stack[1]['folder']->tdb->table != 'learning_repo') && ($this->canInlineRenameItem($stack, $level) && !FormaLms\lib\Get::cfg('demo_mode')))
            || (($stack[1]['folder']->tdb->table == 'learning_repo') && ($stack[1]['folder']->otherValues[5] == $this->session->get('public_area_idst') || \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() == ADMIN_GROUP_GODADMIN))) {
                $tree .= '<input type="image"'
                        . ' class="tree_view_image" '
                        . ' src="' . $this->_getRenameImage() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpRenameFolderId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpRenameFolderId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getRenameLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getRenameLabel() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
            } else {
                $tree .= '<div class="TVActionEmpty">&nbsp;</div>';
            }
        }

        return $tree;
    }

    public function getImage(&$stack, $currLev, $maxLev)
    {
        $imgFileName = '';
        $imgAlt = '';
        $classImg = 'TreeViewImage';
        if ($maxLev == 0) {
            $imgFileName = REND_ROOT;
            $imgAlt = $this->lang->def('_ALT_ROOT');
        } elseif ($currLev == $maxLev) {
            if ($stack[$maxLev]['isExpanded']) {
                if ($stack[$maxLev]['isLeaf']) {
                    $imgFileName = REND_TITLE_OPEN_LEAF;
                    $imgAlt = $this->lang->def('_OPEN');
                } else {
                    $imgFileName = REND_TITLE_OPEN;
                    $imgAlt = $this->lang->def('_OPEN');
                }
            } else {
                $imgFileName = REND_TITLE_CLOSE;
                $imgAlt = $this->lang->def('_CLOSE');
            }
        } elseif ($currLev == $maxLev - 1) {
            if ($stack[$maxLev]['isLeaf']) {
                if ($stack[$maxLev]['isLast']) {
                    $imgFileName = REND_BRANCH_END;
                    $imgAlt = $this->lang->def('_END');
                } else {
                    $imgFileName = REND_BRANCH_INTER;
                    $imgAlt = $this->lang->def('_CONTINUE');
                }
            } else {
                if ($stack[$maxLev]['isExpanded']) {
                    if ($stack[$maxLev]['isLast']) {
                        $imgFileName = REND_COLLAPSE_END;
                        $imgAlt = $this->lang->def('_CLOSE');
                        $classImg = 'TreeViewOpCollapseCorner';
                    } else {
                        $imgFileName = REND_COLLAPSE_INTER;
                        $imgAlt = $this->lang->def('_COLLAPSE');
                        $classImg = 'TreeViewOpCollapseInter';
                    }
                } else {
                    if ($stack[$maxLev]['isLast']) {
                        $imgFileName = REND_EXPAND_END;
                        $imgAlt = $this->lang->def('_OPEN');
                        $classImg = 'TreeViewOpExpandCorner';
                    } else {
                        $imgFileName = REND_EXPAND_INTER;
                        $imgAlt = $this->lang->def('_COLLAPSE');
                        $classImg = 'TreeViewOpExpandInter';
                    }
                }
            }
        } else {
            if ($stack[$currLev + 1]['isLast']) {
                $imgFileName = REND_EMPTY;
                $imgAlt = $this->lang->def('_ALT_EMPTY');
            } else {
                $imgFileName = REND_VERT_INTER;
                $imgAlt = $this->lang->def('_CONTINUE');
            }
        }

        return [$classImg, $imgFileName, $imgAlt];
    }

    public function loadActions()
    {
        // $tree = '<div class="TreeViewActionContainer">';
        $tree = '<td>';
        if ($this->canAdd()) {
            $tree .= '<img src="' . $this->_getAddImage() . '" alt="' . $this->_getAddAlt() . '" /> '
                . '<input type="submit" class="TreeViewAction" value="' . $this->_getAddLabel() . '"'
                . ' name="' . $this->_getAddUrl() . '" />';
        }
        if ($this->canRename()) {
            $tree .= '<img src="' . $this->_getRenameImage() . '" alt="' . $this->_getRenameAlt() . '" /> '
                . '<input type="submit" class="TreeViewAction" value="' . $this->_getRenameLabel() . '"'
                . ' name="' . $this->_getRenameUrl() . '" />';
        }
        if ($this->canMove()) {
            $tree .= '<img src="' . $this->_getMoveImage() . '" alt="' . $this->_getMoveAlt() . '" /> '
                . '<input type="submit" class="TreeViewAction" value="' . $this->_getMoveLabel() . '"'
                . ' name="' . $this->_getMoveUrl() . '" />';
        }
        if ($this->canDelete() && !FormaLms\lib\Get::cfg('demo_mode')) {
            $tree .= '<img src="' . $this->_getDeleteImage() . '" alt="' . $this->_getDeleteAlt() . '" /> '
                . '<input type="submit" class="TreeViewAction" value="' . $this->_getDeleteLabel() . '"'
                . ' name="' . $this->_getDeleteUrl() . '" />';
        }

        $otherActions = $this->_getOtherActions();
        while (list($actId, $actLabel, $actImg) = current($otherActions)) {
            $tree .= '<img src="' . $actImg . '" alt="' . $actLabel . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $actLabel . '"'
            . ' name="' . $actId . '"';
            if (!is_numeric(key($otherActions))) {
                $tree .= ' id="' . key($otherActions) . '"';
            }
            $tree .= '/>';
            next($otherActions);
        }
        reset($otherActions);
        // return $tree .= '</div>';
        return $tree .= '</td>';
    }

    public function loadNewFolder()
    {
        $tree = $this->printState();
        $tree .= '<label for="' . $this->_getFolderNameId() . '">' . $this->_getFolderNameLabel() . '</label>';
        $tree .= ' <input type="text" value="" name="' . $this->_getFolderNameId()
            . '" id="' . $this->_getFolderNameId() . '" />';
        $tree .= ' <img src="' . $this->_getCreateImage() . '" alt="' . $this->_getCreateAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getCreateLabel() . '"'
            . ' name="' . $this->_getCreateFolderId() . '" id="' . $this->_getCreateFolderId() . '" />';
        $tree .= ' <img src="' . $this->_getCancelImage() . '" alt="' . $this->_getCancelAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getCancelLabel() . '"'
            . ' name="' . $this->_getCancelId() . '" id="' . $this->_getCancelId() . '" />';

        return $tree;
    }

    public function loadRenameFolder()
    {
        $tree = $this->printState();
        $tdb = $this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());
        $tree .= '<label for="' . $this->_getFolderNameId() . '">' . $this->_getFolderNameLabel() . '</label>';
        $tree .= ' <input type="text" value="' . $this->getFolderPrintName($folder) . '" name="' . $this->_getFolderNameId()
            . '" id="' . $this->_getFolderNameId() . '" />';
        $tree .= ' <img src="' . $this->_getRenameImage() . '" alt="' . $this->_getRenameAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getRenameLabel() . '"'
            . ' name="' . $this->_getRenameFolderId() . '" id="' . $this->_getRenameFolderId() . '" />';
        $tree .= ' <img src="' . $this->_getCancelImage() . '" alt="' . $this->_getCancelAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getCancelLabel() . '"'
            . ' name="' . $this->_getCancelId() . '" id="' . $this->_getCancelId() . '" />';

        return $tree;
    }

    public function loadMoveFolder()
    {
        if (isset($_POST[$this->_getFolderNameId()])) {
            $folderid = $_POST[$this->_getFolderNameId()];
        } else {
            $folderid = $this->getSelectedFolderId();
        }

        $folder = $this->tdb->getFolderById($this->getSelectedFolderId());
        $tree = '<input type="hidden" value="1" name="' . $this->_getOpMoveFolderId() . '" />';
        $tree .= '<input type="hidden" value="' . $folderid . '" name="' . $this->_getFolderNameId() . '" />';
        $tree .= '<div>' . $this->_getMoveTargetLabel() . ' "' . $this->getFolderPrintName($folder) . '"</div>';
        $tree .= $this->load();
        $tree .= ' <img src="' . $this->_getMoveImage() . '" alt="' . $this->_getMoveAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getMoveLabel() . '"'
            . ' name="' . $this->_getMoveFolderId() . '" id="' . $this->_getMoveFolderId() . '" />';
        $tree .= ' <img src="' . $this->_getCancelImage() . '" alt="' . $this->_getCancelAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getCancelLabel() . '"'
            . ' name="' . $this->_getCancelId() . '" id="' . $this->_getCancelId() . '" />';

        return $tree;
    }

    public function loadDeleteFolder()
    {
        $tree = $this->printState();
        $tdb = $this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());
        $tree .= $this->_getDeleteLabel() . ' ' . $this->getFolderPrintName($folder) . '?  ';
        $tree .= ' <img src="' . $this->_getDeleteImage() . '" alt="' . $this->_getDeleteAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getDeleteLabel() . '"'
            . ' name="' . $this->_getDeleteFolderId() . '" id="' . $this->_getDeleteFolderId() . '" />';
        $tree .= ' <img src="' . $this->_getCancelImage() . '" alt="' . $this->_getCancelAlt() . '" /> '
            . '<input type="submit" class="TreeViewAction" value="' . $this->_getCancelLabel() . '"'
            . ' name="' . $this->_getCancelId() . '" id="' . $this->_getCancelId() . '" />';

        return $tree;
    }

    /**
     * Note that posFlat is empty until parsePositionData() is called.
     **/
    public function getPosFlat()
    {
        return (array) $this->posFlat;
    }

    public function __sleep()
    {
        $this->lang = null;

        return ['tdb',
                        'id',
                        'posTree',
                        'posFlat',
                        'expandList',
                        'compressList',
                        'selectedFolder',
                        'op',
                        'rootname', ];
    }

    public function __wakeup()
    {
        $this->lang = &FormaLanguage::createInstance('treeview', 'framework');
    }
}
