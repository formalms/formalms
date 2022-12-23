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

//require_once( 'core/class/class.listview.php' );
//require_once( 'core/class/class.treedb.php' );
//require_once( 'core/class/class.treeview.php' );

/**
 * Customization of DataRetriever for _homerepo table.
 **/
class HomeRepo_DataRetriever extends DataRetriever
{
    // id user for filter on its bookmarks
    public $idUser = null;
    // id of selected folder in _homerepo_dir (TreeView)
    // used in query composition to filter items
    public $idFolder = 0;
    // id (name) for opMoveId operations
    // uset to compose name attribute of submit
    // button for showing
    public $opMoveId = '';
    // id (name) for opDeleteId operations
    // uset to compose name attribute of submit
    // button for showing
    public $opDeleteId = '';
    // id (name) for opShowId operations
    // uset to compose name attribute of submit
    // button for showing
    public $opShowId = '';
    // id (name) for opPlayId operations
    // uset to compose name attribute of submit
    // button for playing
    public $opPlayId = '';
    // id (name) for opSelectId operations
    // uset to compose name attribute of submit
    // button for select
    public $opSelectId = '';
    // id of selected item
    // used in fetchRecord to change class of
    // html tag that contains the item
    public $selectedIdObject = -1;
    // array of types to show
    // NULL means all
    public $filterTypes = null;

    // set the selected id
    public function setSelectedObject($selectedIdObject)
    {
        $this->selectedIdObject = $selectedIdObject;
    }

    public function setFilterTypes($lotypes)
    {
        $this->filterTypes = $lotypes;
    }

    // set the user id
    public function setUser($idUser)
    {
        $this->idUser = $idUser;
    }

    // set the folder
    public function setFolder($idFolder)
    {
        $this->idFolder = $idFolder;
    }

    // set the id (name) of opShowId operations
    public function setOpShowId($opShowId)
    {
        $this->opShowId = $opShowId;
    }

    // set the id (name) of opPlayId operations
    public function setOpPlayId($opPlayId)
    {
        $this->opPlayId = $opPlayId;
    }

    // set the id (name) of opSelectId operations
    public function setOpSelectId($opSelectId)
    {
        $this->opSelectId = $opSelectId;
    }

    // set the id (name) of opDeselectId opreations
    public function setOpDeselectId($opDeselectId)
    {
        $this->opDeselectId = $opDeselectId;
    }

    // set the id (name) of opDeleteId opreations
    public function setOpDeleteId($opDeleteId)
    {
        $this->opDeleteId = $opDeleteId;
    }

    // set the id (name) of opMoveId opreations
    public function setOpMoveId($opMoveId)
    {
        $this->opMoveId = $opMoveId;
    }

    public function numRows($idFolder)
    {
        $query = 'SELECT count(idObject)'
            . ' FROM ' . $this->prefix . '_homerepo';
        if ($idFolder === null) {
            $query .= " WHERE idFolder='" . (int) $this->idFolder . "'";
        } else {
            $query .= " WHERE idFolder='" . (int) $idFolder . "'";
        }
        if ($this->filterTypes !== null) {
            $query .= " AND (objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
        }

        $query .= "   AND idUser='" . (int) $this->idUser . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('numRows');

            return -1;
        } else {
            list($nrows) = sql_fetch_row($rs);

            return $nrows;
        }
    }

    public function getItemPrintName($idItem)
    {
        $query = 'SELECT `title`'
            . ' FROM ' . $this->prefix . '_homerepo'
            . " WHERE idObject='" . (int) $idItem . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('getItemPrintName');

            return 'errore!!!';
        }
        list($title) = sql_fetch_row($rs);

        return $title;
    }

    public function moveItem($idFolder, $idObject)
    {
        // a big insert query .... wow wooo ... yep
        $query = 'UPDATE ' . $this->prefix . '_homerepo'
                . " SET `idFolder` = '" . (int) $idFolder . "'"
                . " WHERE idObject = '" . (int) $idObject . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('moveItem');

            return 'errore!!!';
        }
    }

    public function deleteItem($idItem)
    {
        $query = 'SELECT `idResource`, `objectType`'
            . ' FROM ' . $this->prefix . '_homerepo'
            . " WHERE idObject='" . (int) $idItem . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('deleteItem');

            return 'errore!!!';
        }
        list($idResource, $objectType) = sql_fetch_row($rs);
        $lo = createLO($objectType, $idResource, null, '');
        if ($lo->delete()) {
            $query = 'DELETE FROM ' . $this->prefix . '_homerepo'
                . " WHERE idObject = '" . (int) $idItem . "'";
            $rs = sql_query($query);
            if ($rs === false) {
                errorCommunication('deleteItem');

                return 'errore!!!';
            }
        }
    }

    // getRows: overload of method of the DataRetriever class
    // execute query for data retrieving
    // tipically called from listView
    public function getRows($startRow, $numRows)
    {
        $query = 'SELECT `idObject`, `idResource`, `idCategory`,'
            . '`idUser`, `idAuthor`, `objectType`, `title`,'
            . '`version`, `difficult`, `language`,'
            . '`resource`, `objective`, `dateInsert`'
            . ' FROM ' . $this->prefix . '_homerepo'
            . " WHERE idFolder='" . (int) $this->idFolder . "'"
            . "   AND idUser='" . (int) $this->idUser . "'";
        if ($this->filterTypes !== null) {
            $query .= " AND (objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
        }

        return $this->_getData($query, $startRow, $numRows);
    }

    // fetchRecord: overload of method of the DataRetriever class
    public function fetchRecord()
    {
        // fetch a record from record set
        $arrData = parent::fetchRecord();
        if ($arrData === false) {
            return false;
        }

        // ------ compute title
        // use a special class for selected items
        $title = '<input type="submit" class="';

        if (is_array($this->selectedIdObject) && in_array($arrData['idObject'], $this->selectedIdObject)) {
            $title .= 'TreeItemSelected';
            $op = $this->opDeselectId;
        } else {
            $title .= 'TreeItem';
            $op = $this->opSelectId;
        }
        // attach select operation to title
        $title .= '" value="' . $arrData['title']
                 . '" name="' . $op . $arrData['idObject'] . '" id="' . $arrData['idObject'] . '_select" />';

        $arrData['title'] = $title;

        // ------ compute icon
        $arrData['icon'] = '<img src="' . getPathImage() . 'lobject/'
                            . $arrData['objectType'] . '.gif" alt="'
                            . $arrData['objectType'] . '" />';

        // ------ move operation
        $arrData['move'] = '<div class="LVShowItem">'
                            . '<input type="submit" class="LVMoveItem" value="'
                            . '" name="' . $this->opMoveId . $arrData['idObject'] . '" id="' . $arrData['idObject'] . 'img_show" />'
                            . '</div>';

        // ------ delete operation
        $arrData['delete'] = '<div class="LVShowItem">'
                            . '<input type="submit" class="LVDeleteItem" value="'
                            . '" name="' . $this->opDeleteId . $arrData['idObject'] . '" id="' . $arrData['idObject'] . 'img_show" />'
                            . '</div>';

        // ------ show operation
        $arrData['show'] = '<div class="LVShowItem">'
                            . '<input type="submit" class="LVShowItem" value="'
                            . '" name="' . $this->opShowId . $arrData['idObject'] . '" id="' . $arrData['idObject'] . 'img_show" />'
                            . '</div>';

        // ------ play operation
        $arrData['play'] = '<div class="LVShowItem">'
                            . '<input type="submit" class="LVShowItem" value="'
                            . '" name="' . $this->opPlayId . $arrData['idObject'] . '" id="' . $arrData['idObject'] . 'img_play" />'
                            . '</div>';

        return $arrData;
    }
}

/**
 * Customizaton of ListView class for homerepo.
 **/
class HomeRepo_ListView extends ListView
{
    // id to delete
    public $itemToDelete = null;
    // id to move
    public $itemToMove = null;

    public function _getOpMoveItemId()
    {
        return '_listview_opmoveitem_' . $this->id;
    }

    public function getIdMoveItem()
    {
        return $this->itemToMove;
    }

    public function _getIdMoveItemId()
    {
        return '_listview_idmoveitem_' . $this->id;
    }

    public function _getIdMoveItemMove()
    {
        return '_listview_idmoveitemmove_' . $this->id;
    }

    public function _getOpDeleteItemId()
    {
        return '_listview_opdeleteitem_' . $this->id;
    }

    public function _getIdDeleteItemId()
    {
        return '_listview_iddeleteitem_' . $this->id;
    }

    public function getIdDeleteItem()
    {
        return $this->itemToDelete;
    }

    public function _getIdDeleteItemConfirm()
    {
        return '_listview_iddeleteitemconfirm_' . $this->id;
    }

    // overload for _getAddLabel operation
    public function _getAddLabel()
    {
        return _NEWHOMEITEM;
    }

    public function _getCreateAlt()
    {
        return _NEWHOMEITEM;
    }

    public function _getAddImage()
    {
        return getPathImage() . 'homerepo/filenew.png';
    }

    public function _getCreateLabel()
    {
        return _CREATEHOMEITEM;
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'homerepo/filenew.png';
    }

    public function _getDeleteLabel()
    {
        return _DELETEITEM;
    }

    public function _getDeleteConfirmLabel()
    {
        return _AREYOUSURE;
    }

    public function _getDeleteUrl()
    {
        return $this->_getOpDeleteItemId();
    }

    public function _getDeleteAlt()
    {
        return _DELETEITEM;
    }

    public function _getDeleteImage()
    {
        return getPathImage() . 'standard/delete.png';
    }

    public function _getMoveLabel()
    {
        return _MOVEITEM;
    }

    public function _getMoveUrl()
    {
        return $this->_getOpMoveItemId();
    }

    public function _getMoveAlt()
    {
        return _DELETEITEM;
    }

    public function _getMoveImage()
    {
        return getPathImage() . 'standard/move.png';
    }

    public function getItemPrintName($idItem)
    {
        return $this->data->getItemPrintName($idItem);
    }

    public function numRows($idFolder = null)
    {
        return $this->data->numRows($idFolder);
    }

    public function extendedParsing($arrayState)
    {
        if (isset($arrayState['idLO']) && !$this->cancel) {
            $this->itemToShow = $arrayState['idLO'];
            $this->op = 'showitem';
        }
        if (isset($arrayState[$this->_getIdDeleteItemId()])) {
            $this->itemToDelete = $arrayState[$this->_getIdDeleteItemId()];
        }
        if (isset($arrayState[$this->_getIdMoveItemId()])) {
            $this->itemToMove = $arrayState[$this->_getIdMoveItemId()];
        }

        foreach ($arrayState as $nameField => $valueField) {
            if (strstr($nameField, $this->_getOpDeleteItemId())) {
                $id = substr($nameField, strlen($this->_getOpDeleteItemId()));
                $this->itemToDelete = $id;
                $this->op = 'deleteitem';
            } elseif (strstr($nameField, $this->_getOpMoveItemId())) {
                $id = substr($nameField, strlen($this->_getOpMoveItemId()));
                $this->itemToMove = $id;
                $this->op = 'moveitem';
            }
        }

        if (isset($arrayState[$this->_getIdDeleteItemConfirm()]) && !$this->cancel) {
            $this->data->deleteItem($this->itemToDelete);
            $this->op = 'diplay';
        }
        if (isset($arrayState[$this->_getIdMoveItemMove()]) && !$this->cancel) {
            $this->data->moveItem($this->data->idFolder, $this->itemToMove);
            $this->op = 'diplay';
        }
        // handle actions that change display mode (op)
        if (isset($arrayState[$this->_getOpDeleteItemId()]) && !$this->cancel) {
            $this->op = 'deleteitem';
        }
        if (isset($arrayState[$this->_getOpMoveItemId()]) && !$this->cancel) {
            $this->op = 'moveitem';
        }
    }

    public function printState()
    {
        parent::printState();
        echo '<input type="hidden"'
            . ' id="' . $this->_getIdDeleteItemId() . '"'
            . ' name="' . $this->_getIdDeleteItemId() . '"'
            . ' value="' . $this->getIdDeleteItem() . '" />' . "\n";
        echo '<input type="hidden"'
            . ' id="' . $this->_getIdMoveItemId() . '"'
            . ' name="' . $this->_getIdMoveItemId() . '"'
            . ' value="' . $this->getIdMoveItem() . '" />' . "\n";
    }

    // utility function
    public function _createColInfo($label, $hClass, $fieldClass, $data, $toDisplay, $sortable)
    {
        return ['hLabel' => $label,
                        'hClass' => $hClass,
                        'fieldClass' => $fieldClass,
                        'data' => $data,
                        'toDisplay' => $toDisplay,
                        'sortable' => $sortable, ];
    }

    // overload
    public function _getCols()
    {
        $colInfos = [];
        $colInfos[] = $this->_createColInfo('idObject', '', '', 'idObject', false, false);
        $colInfos[] = $this->_createColInfo('idResource', '', '', 'idResource', false, false);
        $colInfos[] = $this->_createColInfo('idCategory', '', '', 'idCategory', false, false);
        $colInfos[] = $this->_createColInfo('idAuthor', '', '', 'idAuthor', false, false);
        $colInfos[] = $this->_createColInfo('objectType', '', '', 'objectType', false, false);
        $colInfos[] = $this->_createColInfo('icon', 'image', 'image', 'icon', true, false);
        $colInfos[] = $this->_createColInfo(_HOMEREPO_TITLE, '', '', 'title', true, false);
        $colInfos[] = $this->_createColInfo('<input type="text" disabled class="LVShowItem" value="" name="showheader" id="showheader" />',
                        'image', 'image', 'show', true, false);
        $colInfos[] = $this->_createColInfo('<input type="text" disabled class="LVDeleteItem" value="" name="deleteheader" id="deleteheader" />',
                        'image', 'image', 'delete', true, false);
        $colInfos[] = $this->_createColInfo('<input type="text" disabled class="LVMoveItem" value="" name="moveheader" id="moveheader" />',
                        'image', 'image', 'move', true, false);

        return $colInfos;
    }
}

// customization of TreeDb for homerepo_dir
class HomeDirDb extends TreeDb
{
    // id of current user
    public $hd_idUser;
    public $hd_filterTypes = null;

    // it's all ok! only to set table name and fields name
    public function __construct($idUser = null)
    {
        $this->table = $GLOBALS['prefix_lms'] . '_homerepo_dir';
        $this->fields = ['id' => 'id', 'idParent' => 'idParent', 'path' => 'path', 'lev' => 'lev'];
        if ($idUser === null) {
            $this->hd_idUser = getLogUserId();
        } else {
            $this->hd_idUser = $idUser;
        }
    }

    public function setFilterTypes($lotypes)
    {
        $this->hd_filterTypes = $lotypes;
    }

    // Home directories are stored in a table with the structure requested by
    // TreeDb to manage tree. In addition the table contains
    // idUser field
    public function _getOtherFields($tname = false)
    {
        if ($tname === false) {
            return ', idUser ';
        } else {
            return ', ' . $tname . '.idUser';
        }
    }

    public function _getOtherValues()
    {
        return ", '" . $this->hd_idUser . "'";
    }

    // overload.
    // filter organization on idCourse
    public function _getFilter($tname = false)
    {
        if ($tname === false) {
            $result = " AND (idUser = '" . $this->hd_idUser . "')";
        } else {
            $result = ' AND (' . $tname . ".idUser = '" . $this->hd_idUser . "')";
        }

        return $result;
    }
}

class Homerepo_TreeView extends TreeView
{
    public $withActions = false;

    public function _getAddImage()
    {
        return getPathImage() . 'homerepo/folder_new.png';
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'homerepo/folder_new.png';
    }

    public function _getSaveImage()
    {
        return getPathImage() . 'homerepo/save.gif';
    }

    public function _getAddLabel()
    {
        return _NEW_FOLDER;
    }

    public function _getAddAlt()
    {
        return _NEW_FOLDER;
    }

    public function _getCreateLabel()
    {
        return _NEW_FOLDER;
    }

    public function _getCreateAlt()
    {
        return _NEW_FOLDER;
    }

    public function canDelete()
    {
        if (!$this->isFolderSelected()) {
            return false;
        }
        $data = $this->getSelectedFolderData();
        echo "\n\n<!-- $data -->";
        if ($data['isLeaf'] === false) {
            return false;
        } else {
            $listview = $this->getListView();
            if ($listview == null || $listview->numRows($this->getSelectedFolderId()) == 0) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function canInlineMove()
    {
        return $this->withActions;
    }

    public function canInlineRename()
    {
        return $this->withActions;
    }

    public function canInlineDelete()
    {
        return $this->withActions;
    }

    public function canInlineMoveItem(&$stack, $level)
    {
        if ($level == 0) {
            return false;
        }

        return true;
    }

    public function canInlineRenameItem(&$stack, $level)
    {
        if ($level == 0) {
            return false;
        }

        return true;
    }

    public function canInlineDeleteItem(&$stack, $level)
    {
        if ($level == 0) {
            return false;
        }
        if ($stack[$level]['isLeaf'] === false) {
            return false;
        } else {
            $listview = $this->getListView();
            if ($listview == null || $listview->numRows($stack[$level]['folder']->id) == 0) {
                return true;
            } else {
                return false;
            }
        }
    }
}

function manHomerepo_save($idFolder, &$lo, &$arrParam)
{
    return manHomerepo_saveIdResource($idFolder,
                                        $lo->getId(),
                                        $lo->getObjectType(),
                                        $lo->getTitle(),
                                        $arrParam,
                                        $idLO);
}

function manHomerepo_saveIdResource($idFolder, $idResource, $objectType, $title, &$arrParam)
{
    // a big insert query .... wow wooo ... yep
    $query = 'INSERT INTO %lms_homerepo'
            . ' ( `idFolder` , `idResource` , `idCategory` , `idUser` ,'
            . ' `idAuthor` , `objectType` , `title`, `version` , `difficult` ,'
            . ' `description` , `language` , `resource` , `objective` , `dateInsert` )'
            . " VALUES ( '"
            . (int) $idFolder . "','" . (int) $idResource . "','"
            . (int) (isset($arrParam['idCategory']) ? ($arrParam['idCategory']) : '') . "','"
            . (int) (isset($arrParam['idUser']) ? ($arrParam['idUser']) : (getLogUserId())) . "','"
            . (int) (isset($arrParam['idAuthor']) ? ($arrParam['idAuthor']) : (getLogUserId())) . "','"
            . $objectType . "','"
            . addslashes($title) . "','"
            . (isset($arrParam['version']) ? (addslashes($arrParam['version'])) : '') . "','"
            . (isset($arrParam['difficult']) ? ($arrParam['difficult']) : '') . "','"
            . (isset($arrParam['description']) ? (addslashes($arrParam['description'])) : '') . "','"
            . (isset($arrParam['language']) ? ($arrParam['language']) : '') . "','"
            . (isset($arrParam['resource']) ? (addslashes($arrParam['resource'])) : '') . "','"
            . (isset($arrParam['objective']) ? (addslashes($arrParam['objective'])) : '') . "','"
            . getdate() . "' )";
    sql_query($query)
        or exit(sql_error());
}

function manHomerepo_update($idFolder, $idObject, &$arrParam)
{
    // a big insert query .... wow wooo ... yep
    $query = 'UPDATE %lms_homerepo'
            . " SET `idFolder` = '" . (int) $idFolder . "',"
            . " `idCategory` = '" . (int) (isset($arrParam['idCategory']) ? ($arrParam['idCategory']) : '') . "',"
            . " `idAuthor` = '" . (int) (isset($arrParam['idAuthor']) ? ($arrParam['idAuthor']) : (getLogUserId())) . "',"
            . " `version` = '" . (isset($arrParam['version']) ? (addslashes($arrParam['version'])) : '') . "',"
            . " `difficult` = '" . (isset($arrParam['difficult']) ? ($arrParam['difficult']) : '') . "',"
            . " `description` = '" . (isset($arrParam['description']) ? (addslashes($arrParam['description'])) : '') . "',"
            . " `language` = '" . (isset($arrParam['language']) ? ($arrParam['language']) : '') . "',"
            . " `resource` = '" . (isset($arrParam['resource']) ? (addslashes($arrParam['resource'])) : '') . "',"
            . " `objective` = '" . (isset($arrParam['objective']) ? (addslashes($arrParam['objective'])) : '') . "',"
            . " `dateInsert` = '" . getdate() . "'"
            . " WHERE idObject = '" . (int) $idObject . "'";
    sql_query($query)
        or exit(sql_error());
}

function manHomerepo_display(&$treeView, $withContents, $withActions = false)
{
    if ($withContents) {
        $listView = $treeView->getListView();
        if ($withActions === false) {
            $treeView->withActions = false;
        }
        $treeView->load();
        if ($withActions) {
            $treeView->loadActions();
        }
        $listView->printOut();
    } else {
        if ($withActions === false) {
            $treeView->withActions = false;
        }
        $treeView->load();
        if ($withActions) {
            $treeView->loadActions();
        }
    }
}

function manHomerepo_getOp(&$treeView)
{
    $op = $treeView->op;

    if ($op == '') {
        $listView = $treeView->getListView();
        if ($listView !== null) {
            $op = $listView->op;
        }
    }

    return $op;
}

function manHomerepo_addfolder(&$treeView)
{
    echo '<div class="std_block">';
    $treeView->loadNewFolder();
    echo '</div>';
}

function manHomerepo_renamefolder(&$treeView)
{
    echo '<div class="std_block">';
    $treeView->loadRenameFolder();
    echo '</div>';
}

function manHomerepo_movefolder(&$treeView)
{
    echo '<div class="std_block">';
    $treeView->withActions = false;
    $treeView->loadMoveFolder();
    echo '</div>';
}

function manHomerepo_deletefolder(&$treeView)
{
    echo '<div class="std_block">';
    $treeView->loadDeleteFolder();
    echo '</div>';
}

function manHomerepo_deleteitem(&$treeView)
{
    $listView = $treeView->getListView();
    $treeView->printState();
    $listView->printState();
    echo '<div class="std_block">';
    echo $listView->_getDeleteConfirmLabel() . ' ' . $listView->getItemPrintName($listView->getIdDeleteItem()) . '?  ';
    echo '<br /><br /><img src="' . $listView->_getDeleteImage() . '" alt="' . $listView->_getDeleteAlt() . '" />'
        . '<input type="submit" class="LVAction" value="' . $listView->_getDeleteLabel() . '"'
        . ' name="' . $listView->_getIdDeleteItemConfirm() . '" id="' . $listView->_getIdDeleteItemConfirm() . '" />';
    echo ' <img src="' . $listView->_getCancelImage() . '" alt="' . $listView->_getCancelAlt() . '" />'
        . '<input type="submit" class="LVAction" value="' . $listView->_getCancelLabel() . '"'
        . ' name="' . $listView->_getCancelId() . '" id="' . $listView->_getCancelId() . '" />';
    echo '</div>';
}

function manHomerepo_moveitem(&$treeView)
{
    $listView = $treeView->getListView();
    $treeView->printState();
    $listView->printState();
    if (isset($_POST[$listView->_getIdMoveItemId()])) {
        $itemid = $_POST[$listView->_getIdMoveItemId()];
    } else {
        $itemid = $listView->getIdMoveItem();
    }

    echo '<div class="std_block">';
    //echo '<input type="hidden" value="" name="'.$listView->_getOpMoveFolderId().'" />';
    //echo '<input type="hidden" value="'.$itemid.'" name="'.$listView->_getIdMoveItemId().'" />';
    echo '<div>' . $listView->_getMoveLabel() . $listView->getItemPrintName($itemid) . '</div>';
    $treeView->withActions = false;
    $treeView->load();
    echo ' <img src="' . $listView->_getMoveImage() . '" alt="' . $listView->_getMoveAlt() . '" /> '
        . '<input type="submit" class="LVAction" value="' . $listView->_getMoveLabel() . '"'
        . ' name="' . $listView->_getIdMoveItemMove() . '" id="' . $listView->_getIdMoveItemMove() . '" />';
    echo ' <img src="' . $listView->_getCancelImage() . '" alt="' . $listView->_getCancelAlt() . '" /> '
        . '<input type="submit" class="LVAction" value="' . $listView->_getCancelLabel() . '"'
        . ' name="' . $listView->_getCancelId() . '" id="' . $listView->_getCancelId() . '" />';
    echo '</div>';
}

function manHomerepo_showerror(&$treeView)
{
    echo '<div class="std_block">';
    if ($treeView->error == TVERR_MOVEONDESCENDANT) {
        echo _ERROR_MOVEONDESCENDANT;
    }
    echo ' <img src="' . $treeView->_getCancelImage() . '" alt="' . $treeView->_getCancelAlt() . '" />'
        . '<input type="submit" class="LVAction" value="' . $treeView->_getCancelLabel() . '"'
        . ' name="' . $treeView->_getCancelId() . '" id="' . $treeView->_getCancelId() . '" />';
    echo '</div>';
}

function &manHomerepo_CreateTreeView($withContents = true, $multiSelect = false, $withActions = false, $lotypes = null)
{
    $dirDb = new HomeDirDb();
    //$dirDb->setFilterTypes( $lotypes );
    $treeView = new Homerepo_TreeView($dirDb, 'homerepo');
    $treeView->parsePositionData($_POST, $_POST, $_POST);
    if ($withContents) {
        $dataRetriever = new HomeRepo_DataRetriever(null, $GLOBALS['prefix_lms']);
        $TableRenderer = new Table(20);
        $listView = new HomeRepo_ListView('', $dataRetriever, $TableRenderer, 'homerepo');

        $listView->multiSelect = $multiSelect;
        //$listView->cancel = $treeView->cancel;

        $dataRetriever->setFilterTypes($lotypes);
        $dataRetriever->setUser(getLogUserId());
        $dataRetriever->setFolder($treeView->selectedFolder);
        $dataRetriever->setOpShowId($listView->_getOpShowItemId());
        $dataRetriever->setOpPlayId($listView->_getOpPlayItemId());
        $dataRetriever->setOpSelectId($listView->_getOpSelectItemId());
        $dataRetriever->setOpDeselectId($listView->_getOpDeselectItemId());

        $listView->parsePositionData($_POST);

        $dataRetriever->setSelectedObject($listView->getIdSelectedItem());
        $dataRetriever->setOpDeleteId($listView->_getOpDeleteItemId());
        $dataRetriever->setOpMoveId($listView->_getOpMoveItemId());
        $listView->addurl = $treeView->_getOpNewFolderId();

        if ($withActions) {
            //if(funAccess("insitem","NEW", TRUE, "homerepo")) {
            $listView->setInsNew(true);
            $treeView->withActions = true;
            //}
        }

        $treeView->setlistView($listView);
    }

    return $treeView;
}

function manHomerepo($withForm = false, $withContents = true, $treeView = null, $multiSelect = false, $withActions = false, $lotypes = null)
{
    //if(funAccess("homerepo","OP")) {

    if ($treeView === null) {
        $treeView = manHomerepo_CreateTreeView($withContents, $multiSelect, $withActions, $lotypes);
    }

    if ($withForm) {
        echo '<form name="manHomerepo" method="post"'
                . ' action="index.php?modname=' . $_GET['modname'] . '&op=' . $_GET['op'] . '"'
                . ' >' . "\n"
                . '<input type="hidden" id="authentic_request_hr" name="authentic_request" value="' . Util::getSignature() . '" />';
    }

    switch (manHomerepo_getOp($treeView)) {
            case 'newfolder':
                manHomerepo_addfolder($treeView);
            break;
            case 'save':
                $listView = &$treeView->getListView();
                $lo = null;
                manHomeRepoSave($lo, false, false, $treeView, $listView->getIdShowItem());
                break;
            case 'showitem':
                $listView = &$treeView->getListView();
                $lo = null;
                manHomeRepoSave($lo, false, false, $treeView, $listView->getIdShowItem());
                /*
                $treeView->printState();
                $listView = &$treeView->getListView();
                $listView->printState();
                manHomeRepo_ShowItem($listView->getIdShowItem());
                echo '<img src="'.getPathImage().'standard/close.gif" alt="'._CLOSE.'" /> '
                                            .'<input type="submit" value="'._CLOSE.'"'
                                            .' name="close" />';*/

            break;
            /*case 'playitem':
                $treeView->printState();
                manHomeRepo_ShowItem($treeView->getItemToShow());
                echo '<img src="'.getPathImage().'standard/close.gif" alt="'._CLOSE.'" /> '
                                            .'<input type="submit" value="'._CLOSE.'"'
                                            .' name="close" />';
                break;*/
            case 'renamefolder':
                manHomerepo_renamefolder($treeView);
            break;
            case 'movefolder':
                manHomerepo_movefolder($treeView);
            break;
            case 'deletefolder':
                manHomerepo_deletefolder($treeView);
            break;
            case 'deleteitem':
                manHomerepo_deleteitem($treeView);
            break;
            case 'moveitem':
                manHomerepo_moveitem($treeView);
            break;
            case 'treeview_error':
                manHomerepo_showerror($treeView);
            break;
            case 'display':
            default:
                echo '<div class="std_block">';
                manHomerepo_display($treeView, $withContents, $withActions);
                echo '</div>';
            break;
        }
    if ($withForm) {
        echo '</form>' . "\n";
    }

    //} else
    //	echo "You can't access";
    return $treeView;
}

/**
 * This function can be called from LO insert/edit operation
 * to set position in homerepo and other metadata.
 *
 * @param $lo instance of the learning object to edit
 * @param $withForm print form tag
 * @param $withContents display the Items in folders
 * @param $idLO id of learning object to change. If this parameter is not NULL
 *			$lo is ignored
 **/
function manHomeRepoSave(&$lo, $withForm = false, $withContents = true, $treeView = null, $idLO = null)
{
    if ($treeView === null) {
        $treeView = manHomerepo_CreateTreeView($withContents, false);
    }

    // print a form that submit to the same url
    if ($withForm) {
        echo '<form name="manHomerepo" method="post"'
                . ' action="index.php?' . $_SERVER['QUERY_STRING'] . '"'
                . ' >' . "\n"
                . '<input type="hidden" id="authentic_request_hr" name="authentic_request" value="' . Util::getSignature() . '" />';
    }

    if ($treeView->cancel) {
        if (isset($_POST['idLO'])) {
            Util::jump_to('index.php?modname=homerepo&op=homerepo');
        } else {
            Util::jump_to('' . $lo->getBackUrl());
        }
    }

    // handle operations
    switch ($treeView->op) {
            case 'newfolder':
                manHomerepo_addfolder($treeView);
            break;
            case 'save':
                if (isset($_POST['idLO'])) {
                    manHomerepo_update($treeView->getSelectedFolderId(), $_POST['idLO'], $_POST);
                    Util::jump_to('index.php?modname=homerepo&op=homerepo');
                } else {
                    manHomerepo_save($treeView->getSelectedFolderId(), $lo, $_POST);
                    Util::jump_to('' . $lo->getBackUrl());
                }
            break;
            case 'display':
            default:
                echo '<div class="std_block">';
                manHomerepo_display($treeView, $withContents);
                loadFields($_POST, $lo, $idLO);
                // add save button
                echo '<img src="' . $treeView->_getSaveImage() . '" alt="' . _SAVE . '" /> '
                    . '<input type="submit" value="' . _SAVE . '" class="LVAction"'
                    . ' name="' . $treeView->_getOpSaveFile() . '" />';
                echo ' <img src="' . $treeView->_getCancelImage() . '" alt="' . $treeView->_getCancelAlt() . '" />'
                    . '<input type="submit" class="LVAction" value="' . $treeView->_getCancelLabel() . '"'
                    . ' name="' . $treeView->_getCancelId() . '" id="' . $treeView->_getCancelId() . '" />';
                echo '</div>';
            break;
        }

    if ($withForm) {
        echo '</form>' . "\n";
    }
}

function loadFields($arrayData, &$lo, $idLO)
{
    global $defaultLanguage;

    //including language
    includeLang('classification');

    //finding category
    $reCategory = sql_query('
	SELECT idCategory, title 
	FROM %lms_coursecategory
	ORDER BY title');

    //searching languages

    $langl = dir('menu/language/');
    while ($ele = $langl->read()) {
        if (preg_match('lang-', $ele)) {
            $langArray[] = str_replace('lang-', '', str_replace('.php', '', $ele));
        }
    }
    closedir($langl->handle);
    sort($langArray);

    if (!isset($_POST['idLO'])) {
        if ($idLO !== null) {
            $query = 'SELECT idFolder, idCategory, idAuthor,'
                    . ' objectType, title, version, difficult,'
                    . ' description, language, resource, objective'
                    . ' FROM %lms_homerepo'
                    . " WHERE idObject='" . (int) $idLO . "'";

            $rs = sql_query($query)
                or exit(sql_error());

            $arrayData = sql_fetch_assoc($rs);
            echo '<input type="hidden" name="idLO" id="idLO" value="' . $idLO . '" />';
            $title = $arrayData['title'];
        } else {
            $title = $lo->getTitle();
        }
    } else {
        echo '<input type="hidden" name="idLO" id="idLO" value="' . $idLO . '" />';
        $title = $_POST['title'];
    }

    // ==========================================================
    echo '<input type="hidden" name="title" id="title" value="' . $title . '" />';
    echo '<div class="ObjectForm">';

    echo '<span class="mainTitle">' . _CATEGORIZATION . ' ' . $title . '</span><br /><br />';

    echo '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _CATEGORY . '</div>'
        . '<div class="content">'
        . '<select name="idCategory">';

    if (isset($arrayData['idCategory'])) {
        $selectedIdCat = $arrayData['idCategory'];
    } else {
        $selectedIdCat = '';
    }

    while (list($idCat, $catTitle) = sql_fetch_row($reCategory)) {
        if ($selectedIdCat == $idCat) {
            echo '<option value="' . $idCat . '" selected >' . $catTitle . '</option>';
        } else {
            echo '<option value="' . $idCat . '">' . $catTitle . '</option>';
        }
    }
    echo '</select> ( ' . sql_num_rows($reCategory) . ' ' . _DISP . ')'
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _VERSION . '</div>'
        . '<div class="content">';

    if (isset($arrayData['version'])) {
        echo '<input type="text" name="version" maxlength="8" size="10" value="' . $arrayData['version'] . '" />';
    } else {
        echo '<input type="text" name="version" maxlength="8" size="10" value="1.0" />';
    }

    echo '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _DIFFICULT . '</div>'
        . '<div class="content">'
        . '<select name="difficult">';

    if (isset($arrayData['difficult'])) {
        $selDiff = $arrayData['difficult'];
        switch ($selDiff) {
            case '_DIFFICULT_VERYEASY': $selDiff = '1'; break;
            case '_DIFFICULT_EASY': $selDiff = '2'; break;
            case '_DIFFICULT_MEDIUM': $selDiff = '3'; break;
            case '_DIFFICULT': $selDiff = '4'; break;
            case '_VERYDIFFICULT': $selDiff = '5'; break;
        }
    } else {
        $selDiff = '';
    }

    echo '<option value="1" ' . (($selDiff == '1') ? 'selected' : '') . ' >' . _VERYEASY . '</option>'
            . '<option value="2" ' . (($selDiff == '2') ? 'selected' : '') . ' >' . _EASY . '</option>'
            . '<option value="3" ' . (($selDiff == '3') ? 'selected' : '') . ' >' . _MEDIUM . '</option>'
            . '<option value="4" ' . (($selDiff == '4') ? 'selected' : '') . ' >' . _DIFFICULT . '</option>'
            . '<option value="5" ' . (($selDiff == '5') ? 'selected' : '') . ' >' . _VERYDIFFICULT . '</option>'
        . '</select>'
        . '</div>';
    //-------------------------------------------------
    /*.'<div class="title">'._DESCRIPTION.'</div>'
    .'<div class="content">'
    .'<div id="breakfloat">'
        .'<textarea id="description" name="description" rows="10" cols="75"></textarea></div>'
    .'</div>'*/
    //-------------------------------------------------
    echo '<div class="title">' . _LANGUAGE . '</div>'
        . '<div class="content">'
        . '<select name="language">';
    if (isset($arrayData['language'])) {
        $selLang = $arrayData['language'];
    } else {
        $selLang = $defaultLanguage;
    }

    foreach ($langArray as $valueLang) {
        echo '<option value="' . $valueLang . '"';
        if ($valueLang == $selLang) {
            echo ' selected="selected"';
        }
        echo '>' . $valueLang . '</option>';
    }
    echo '</select>'
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _RESOURCE . '</div>'
        . '<div class="content">';
    if (isset($arrayData['resource'])) {
        echo '<input type="text" name="resource" maxlength="255" size="60" value="' . $arrayData['resource'] . '" />';
    } else {
        echo '<input type="text" name="resource" maxlength="255" size="60" value="http://" />';
    }
    echo '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _OBJECTIVE . '</div>'
        . '<div class="content">';
    if (isset($arrayData['objective'])) {
        echo '<textarea name="objective" rows="6" cols="75">' . $arrayData['objective'] . '</textarea>';
    } else {
        echo '<textarea name="objective" rows="6" cols="75"></textarea>';
    }

    echo '</div>';
}

function manHomeRepo_ShowItem($itemId)
{
    includeLang('classification');

    $query = 'SELECT `idFolder` , `idResource` ,'
            . $GLOBALS['prefix_lms'] . '_coursecategory.title catTitle , `idAuthor` ,'
            . '`objectType` ,'
            . $GLOBALS['prefix_lms'] . '_homerepo.title title, `version` , `difficult` ,'
            . '`description` , `language` , `resource` , `objective` , `dateInsert`'
            . ' FROM %lms_homerepo, %lms_coursecategory'
            . ' WHERE %lms_homerepo.idCategory = %lms_coursecategory.idCategory'
            . " AND idObject='" . (int) $itemId . "'";

    $rs = sql_query($query)
        or exit(sql_error());

    $arrayData = sql_fetch_assoc($rs);

    echo '<div class="ObjectForm">';
    echo '<span class="mainTitle">' . _CATEGORIZATION . ' ' . $arrayData['title'] . '</span><br /><br />';

    echo '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _CATEGORY . '</div>'
        . '<div class="content">'
        . $arrayData['catTitle']
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _VERSION . '</div>'
        . '<div class="content">'
        . $arrayData['version']
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _DIFFICULT . '</div>'
        . '<div class="content">'
        . $arrayData['difficult']
        . '</div>'
        . '<div class="title">' . _LANGUAGE . '</div>'
        . '<div class="content">'
        . $arrayData['language']
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _RESOURCE . '</div>'
        . '<div class="content">'
        . $arrayData['resource']
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . _OBJECTIVE . '</div>'
        . '<div class="content">'
        . $arrayData['objective']
        . '</div>';
    //-------------------------------------------------

    echo '</div>';
}

function manHomeRepo_getData($idObject)
{
    $query = 'SELECT `idObject`, `idResource`, `idCategory`, `idUser`,'
        . '`idAuthor`, `objectType`, `title`,'
        . '`version`, `difficult`, `language`,'
        . '`resource`, `objective`, `dateInsert`'
        . ' FROM %lms_homerepo'
        . " WHERE idObject='" . (int) $idObject . "'";
    $rs = sql_query($query) or exit(sql_error());

    return sql_fetch_assoc($rs);
}
