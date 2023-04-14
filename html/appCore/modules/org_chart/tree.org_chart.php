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
 * @version  $Id: tree.org_chart.php 1002 2007-03-24 11:55:51Z fabio $
 *
 * @category Organization chart
 *
 * @author   Emanuele Sandri <emanuele@docebo.com>
 */
require_once _base_ . '/lib/lib.treedb.php';
require_once _base_ . '/lib/lib.treeview.php';

define('ORGDB_POS_TRANSLATION', 0);

define('ORG_CHART_FIELD_NO', 'No');
define('ORG_CHART_FIELD_NORMAL', 'Normal');
define('ORG_CHART_FIELD_DESCEND', 'Descend');
define('ORG_CHART_FIELD_INHERIT', 'Inherit');

define('ORG_CHART_FOLDER_FIELD_GROUP', '/framework/orgchart/fields');
define('ORGCHAR_FIELDTABLE', '_org_chart_field');
define('ORGCHAR_FIELDENTRYTABLE', '_org_chart_fieldentry');

class TreeDb_OrgDb extends TreeDb
{
    // with other fields, only for view
    public $withOtherFields = true;

    // retrieve only folders for get path in following array
    // if array is NULL retrieve all
    public $filter_path = false;

    // the lang to use for folders names
    // if FALSE the current lang was used
    public $folder_lang = false;

    // Constructor of TreeDb_OrgDb class
    public function __construct($table_name)
    {
        $this->table = $table_name;
        $this->fields = [
            'id' => 'idOrg',
            'idParent' => 'idParent',
            'path' => 'path',
            'lev' => 'lev',
        ];
    }

    public function setFolderLang($lang)
    {
        $this->folder_lang = $lang;
    }

    public function getFolderLang()
    {
        if ($this->folder_lang === false) {
            return Lang::get();
        } else {
            return $this->folder_lang;
        }
    }

    public function _getOtherTables()
    {
        return ' INNER JOIN ' . $GLOBALS['prefix_fw'] . '_org_chart';
    }

    public function _getJoinFilter($table = false)
    {
        if ($table === false) {
            return 'idOrg = id_dir';
        } else {
            return $table . '.idOrg = ' . $GLOBALS['prefix_fw'] . '_org_chart' . '.id_dir';
        }
    }

    public function _getOtherFields($tname = false)
    {
        if ($this->withOtherFields) {
            if ($tname === false) {
                return ', translation';
            } else {
                return ', ' . $GLOBALS['prefix_fw'] . '_org_chart' . '.translation';
            }
        } else {
            return '';
        }
    }

    public function _getParentJoinFilter($t1name, $t2name)
    {
        if ($this->filter_path !== false) {
            $in_condition = "IN ('" . implode("','", $this->filter_path) . "')";

            return ' AND (' . $t2name . '.' . $this->fields['path'] . ' ' . $in_condition . ')';
        } else {
            return '';
        }
        $t1name = $t1name;
    }

    public function _getFilter($tname = false)
    {
        $out = '';
        if ($this->withOtherFields) {
            if ($tname === false) {
                $out .= "AND (lang_code = '" . $this->getFolderLang() . "')";
            } else {
                $out .= 'AND (' . $GLOBALS['prefix_fw'] . '_org_chart' . ".lang_code = '" . $this->getFolderLang() . "')";
            }
        }
        if ($this->filter_path !== false) {
            $in_condition = "IN ('" . implode("','", $this->filter_path) . "')";
            if ($tname === false) {
                $out .= ' AND (' . $this->fields['path'] . ' ' . $in_condition;
            } else {
                $out .= ' AND (' . $tname . '.' . $this->fields['path'] . ' ' . $in_condition . ')';
            }
        }

        return $out;
    }

    public function getMaxChildPos($idFolder)
    {
        $query = "SELECT MAX(SUBSTRING_INDEX(path, '/', -1))"
            . ' FROM ' . $this->table
            . ' WHERE (' . $this->fields['idParent'] . " = '" . (int) $idFolder . "')";

        $rs = sql_query($query)
        or exit("Error [$query] " . sql_error());
        if (sql_num_rows($rs) == 1) {
            list($result) = sql_fetch_row($rs);

            return $result;
        } else {
            return '00000001';
        }
    }

    public function getNewPos($idFolder)
    {
        return substr('00000000' . ($this->getMaxChildPos($idFolder) + 1), -8);
    }

    public function moveUp($idFolder)
    {
        $folder = $this->getFolderById($idFolder);
        // $parent = $this->tdb->getFolderById( $folder->idParent );
        $arrIdSiblings = $this->getChildrensIdById($folder->idParent);
        if (!is_array($arrIdSiblings)) {
            return;
        }
        $pos = array_search($idFolder, $arrIdSiblings);
        if ($pos === null || $pos === false) { // prior to php 4.2.0 and after
            return;
        }
        if ($pos == 0) { // I know it's possible the merge with previous if but this is clear ...
            return;
        }
        $folder2 = $this->getFolderById($arrIdSiblings[$pos - 1]);
        $tmpArr = explode('/', $folder->path);
        $folderName = $tmpArr[count($tmpArr) - 1];
        $tmpArr = explode('/', $folder2->path);
        $folderName2 = $tmpArr[count($tmpArr) - 1];
        parent::renameFolder($folder, $folderName2 . 'tmp');
        parent::renameFolder($folder2, $folderName);
        parent::renameFolder($folder, $folderName2);
    }

    public function moveDown($idFolder)
    {
        $folder = $this->getFolderById($idFolder);
        // $parent = $this->tdb->getFolderById( $folder->idParent );
        $arrIdSiblings = $this->getChildrensIdById($folder->idParent);
        if (!is_array($arrIdSiblings)) {
            return;
        }
        $pos = array_search($idFolder, $arrIdSiblings);
        if ($pos === null || $pos === false) { // prior to php 4.2.0 and after
            return;
        }
        if ($pos == (count($arrIdSiblings) - 1)) {
            return;
        }
        $folder2 = $this->getFolderById($arrIdSiblings[$pos + 1]);
        $tmpArr = explode('/', $folder->path);
        $folderName = $tmpArr[count($tmpArr) - 1];
        $tmpArr = explode('/', $folder2->path);
        $folderName2 = $tmpArr[count($tmpArr) - 1];
        parent::renameFolder($folder, $folderName2 . 'tmp');
        parent::renameFolder($folder2, $folderName);
        parent::renameFolder($folder, $folderName2);
    }

    public function addFolderById($idParent, $folderName)
    {
        $this->withOtherFields = false;
        $id = parent::addFolderById($idParent, addslashes($folderName));
        $aclManager = &\FormaLms\lib\Forma::getAclManager();;
        $idST = $aclManager->registerGroup($this->getGroupId($id), '', true);
        $idST = $aclManager->registerGroup($this->getGroupDescendantsId($id), '', true);
        $aclManager->addToGroup($this->getGroupDescendantsST($idParent), $idST);
        $this->withOtherFields = true;

        return $id;
    }

    public function addFolderByIdTranslation($idParent, $folderName)
    {
        $id = $this->addFolderById($idParent, $this->getNewPos($idParent));
        foreach ($folderName as $lang_code => $translation) {
            $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_org_chart'
                . '( id_dir, lang_code, translation ) '
                . "VALUES ('" . $id . "','" . $lang_code . "','" . $translation . "')";
            $this->_executeQuery($query);
        }

        return $id;
    }

    public function updateFolderByIdTranslation($idFolder, $folderName)
    {
        $array_translations = $this->getFolderTranslations($idFolder);
        foreach ($folderName as $lang_code => $translation) {
            if (isset($array_translations[$lang_code])) {
                $query = 'UPDATE ' . $GLOBALS['prefix_fw'] . '_org_chart'
                    . "   SET translation = '" . $translation . "'"
                    . " WHERE id_dir = '" . $idFolder . "'"
                    . "   AND lang_code = '" . $lang_code . "'";
            } else {
                $query = 'INSERT INTO ' . $GLOBALS['prefix_fw'] . '_org_chart'
                    . '( id_dir, lang_code, translation ) '
                    . "VALUES ('" . $idFolder . "','" . $lang_code . "','" . $translation . "')";
            }
            $this->_executeQuery($query);
        }
    }

    public function getFolderTranslations($idFolder)
    {
        $query = 'SELECT lang_code, translation '
            . '  FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
            . " WHERE id_dir = '" . $idFolder . "'";
        $rs = sql_query($query);
        $arrTranslations = [];
        while (list($lang_code, $translation) = sql_fetch_row($rs)) {
            $arrTranslations[$lang_code] = $translation;
        }

        return $arrTranslations;
    }

    public function getFolderIdByTranslations($translation)
    {
        if ($translation === false) {
            return false;
        }
        $query = 'SELECT id_dir '
            . '  FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
            . " WHERE translation = '" . $translation . "'";

        if (!$rs = sql_query($query)) {
            return false;
        }
        list($id_dir) = sql_fetch_row($rs);

        return $id_dir;
    }

    public function getFoldersCurrTranslation($arr_groupsid)
    {
        $query = 'SELECT id_dir, translation '
            . '  FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
            . " WHERE CONCAT( '/ocd_', id_dir) IN ('" . implode("','", $arr_groupsid) . "')"
            . "   AND lang_code = '" . $this->getFolderLang() . "'";
        $rs = sql_query($query);
        $arrTranslations = [];
        while (list($id_dir, $translation) = sql_fetch_row($rs)) {
            $arrTranslations['/ocd_' . $id_dir] = $translation;
        }

        return $arrTranslations;
    }

    public function getFoldersCurrTranslationDoubleCheck($arr_groupsid)
    {
        $query = 'SELECT id_dir, translation '
            . '  FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
            . ' WHERE ( '
            . " 	CONCAT( '/ocd_', id_dir) IN ('" . implode("','", $arr_groupsid) . "') OR"
            . " 	CONCAT( '/oc_', id_dir) IN ('" . implode("','", $arr_groupsid) . "') ) "
            . "   AND lang_code = '" . $this->getFolderLang() . "'";
        $rs = sql_query($query);
        $arrTranslations = [];
        while (list($id_dir, $translation) = sql_fetch_row($rs)) {
            $arrTranslations[$id_dir] = $translation;
        }

        return $arrTranslations;
    }

    public function addItem($idParent, $org_name)
    {
        $idReference = parent::addFolderById($idParent, addslashes($org_name));

        return $idReference;
    }

    public function modifyItem($arrData)
    {
        $folder = $this->getFolderById($arrData['idItem']);
        $this->changeOtherData($folder);
    }

    public function _deleteTree($folder)
    {
        // first delete all childs (recursive)
        $rs = $this->getChildrensById($folder->id);
        if ($rs !== false) {
            $fc = new FoldersCollection($this, $rs);
            while ($child = $fc->getNext()) {
                $this->_deleteTree($child);
            }
        }
        $this->withOtherFields = false;
        $result = parent::_deleteTree($folder);
        $this->withOtherFields = true;
        // delete translations
        if ($result) {
            $query = 'DELETE FROM ' . $GLOBALS['prefix_fw'] . '_org_chart'
                . " WHERE id_dir = '" . (int) $folder->id . "'";
            $this->_executeQuery($query);
        }

        $aclManager = &\FormaLms\lib\Forma::getAclManager();;
        $idST = $this->getGroupDescendantsST($folder->id);
        // detach this descendant group from parent descendant group
        $aclManager->removeFromGroup($this->getGroupDescendantsST($folder->idParent), $idST);
        // delete descendant group
        $aclManager->deleteGroup($idST);
        // delete OU group
        $aclManager->deleteGroup($this->getGroupST($folder->id));

        return $result;
    }

    public function moveFolder(&$folder, &$parentFolder, $newfoldername = false)
    {
        $aclManager = &\FormaLms\lib\Forma::getAclManager();;
        $idST = $this->getGroupDescendantsST($folder->id);
        $aclManager->removeFromGroup($this->getGroupDescendantsST($folder->idParent), $idST);
        $this->withOtherFields = false;
        parent::moveFolder($folder, $parentFolder, $this->getNewPos($parentFolder->id));
        $this->withOtherFields = true;
        $aclManager->addToGroup($this->getGroupDescendantsST($parentFolder->id), $idST);
        $newfoldername = $newfoldername;
    }

    public function getDescendantsSTFromST($arr_idst)
    {
        $aclManager = &\FormaLms\lib\Forma::getAclManager();;
        $arr_groupid = $aclManager->getGroupsId($arr_idst);
        foreach ($arr_groupid as $key => $val) {
            $arr_groupid[$key] = substr_replace($val, '/ocd', 0, 3);
        }
        $arr_result = $aclManager->getArrGroupST($arr_groupid);

        return array_values($arr_result);
    }

    public function getGroupId($idFolder)
    {
        return 'oc_' . $idFolder;
    }

    public function getFoldersIdFromIdst($arr_idst)
    {
        $aclManager = &\FormaLms\lib\Forma::getAclManager();;
        $arr_groupid = $aclManager->getGroupsId($arr_idst);
        foreach ($arr_groupid as $key => $val) {
            $arr_groupid[$key] = substr($val, 4);
        }

        return $arr_groupid;
    }

    public function setFilterPathFromFolderId($arr_folders)
    {
        // the array of path
        $arr_path = $this->getPathFromFolderId($arr_folders);
        // the array of admitted path
        $this->filter_path = [];
        foreach ($arr_path as $path) {
            $arr_tok = explode('/', substr($path, 1));
            $tok_path = '';
            foreach ($arr_tok as $tok) {
                $tok_path .= '/' . $tok;
                $this->filter_path[] = $tok_path;
            }
        }
        $this->filter_path = array_unique($this->filter_path);
    }

    public function getGroupDescendantsId($idFolder)
    {
        return 'ocd_' . $idFolder;
    }

    public function getGroupST($idFolder)
    {
        $acl = \FormaLms\lib\Forma::getAcl();

        return $acl->getGroupST($this->getGroupId($idFolder));
    }

    public function getAllGroupST()
    {
        $rootFolder = &$this->getRootFolder();
        $arrId = $this->getDescendantsId($rootFolder);
        $arrResult = [];
        $acl = \FormaLms\lib\Forma::getAcl();
        foreach ($arrId as $groupId) {
            $arrResult[] = $acl->getGroupST($this->getGroupId($groupId));
        }

        return $arrResult;
    }

    public function getGroupDescendantsST($idFolder)
    {
        $acl = \FormaLms\lib\Forma::getAcl();

        return $acl->getGroupST($this->getGroupDescendantsId($idFolder));
    }

    /**
     * given an array of idst search for GroupDescendants and reduce
     * to ....
     * for any idst wich groupid is ocd_xxx remove all contained idst groups
     * form given record.
     **/
    public function removeDescentants($arr_idst)
    {
        $aclManager = &\FormaLms\lib\Forma::getAclManager();;

        // get array of groupid
        $arr_id = $aclManager->getGroupsId($arr_idst);

        // array of processed idst
        $arr_processed = [];

        $count = 0;
        while (count($arr_idst) > $count) {
            if (!isset($arr_processed[$arr_idst[$count]])) { // already processed?
                if (strncmp($arr_id[$arr_idst[$count]], '/ocd_', 5) == 0) {
                    $arr_processed[$arr_idst[$count]] = $arr_idst[$count];
                    $arr_desc = $aclManager->getGroupGDescendants($arr_idst[$count]);

                    if (count($arr_desc) > 0) {
                        // compute all regular groups of descendants
                        $arr_descid = $aclManager->getGroupsId($arr_desc);
                        for ($index = 0, $indexMax = count($arr_desc); $index < $indexMax; ++$index) {
                            $arr_descid[$arr_desc[$index]] = substr_replace($arr_descid[$arr_desc[$index]], '/oc_', 0, 5);
                        }
                        $arr_std_id = $aclManager->getArrGroupST($arr_descid);
                        $arr_desc = array_merge($arr_desc, $arr_std_id);
                        $arr_idst = array_values(array_diff($arr_idst, $arr_desc));
                        $count = 0;
                        // skip count increment
                        continue;
                    }
                }
            }
            ++$count;
        }

        return $arr_idst;
    }
}

class TreeView_OrgView extends TreeView
{
    public $selector_mode = false;
    public $simple_selector = false;
    public $itemSelectedMulti = [];
    public $itemSelectedMulti_alt = [];
    public $filter_nodes = false;
    public $printed_items = [];
    public $printed_items_alt = [];

    public $multi_choice = true;

    // set this member variable to TRUE to select all
    public $select_all = false;
    public PeopleListView $lv_view;
    public PeopleDataRetriever $lv_data;
    /**
     * @var bool|mixed
     */
    public $deselect_all;

    public function __construct($tdb, $id, $rootname = 'root')
    {
        parent::__construct($tdb, $id, $rootname = 'root');
        $this->multi_choice = FormaLms\lib\Get::sett('use_org_chart_multiple_choice') == '1';
    }

    public function _getRenameImage()
    {
        return getPathImage('fw') . 'standard/edit.png';
    }

    public function _getOpAssignField()
    {
        return 'assignfield';
    }

    public function _getImgAssignField()
    {
        return getPathImage('fw') . 'org_chart/assign_field.gif';
    }

    public function _getLabelAssignField()
    {
        return $this->lang->def('_ASSIGN_USERS');
    }

    public function _getOpFolderField()
    {
        return 'folderfield';
    }

    public function _getImgFolderField()
    {
        return getPathImage('fw') . 'org_chart/folder_field.gif';
    }

    public function _getLabelFolderField()
    {
        return $this->lang->def('_ORGCHART_FOLDER_FIELD_ALT');
    }

    public function _getOpAssignUser()
    {
        return 'assignUser';
    }

    public function _getImgAssignUser()
    {
        return getPathImage('fw') . 'org_chart/assign_identity.png';
    }

    public function _getLabelAssignUser()
    {
        return $this->lang->def('_ASSIGN_USERS');
    }

    public function _getOpCreateUser()
    {
        return 'createUser';
    }

    public function _getImgCreateUser()
    {
        return getPathImage('fw') . 'standard/identity.png';
    }

    public function _getLabelCreateUser()
    {
        return $this->lang->def('_NEW_USER_ALT');
    }

    public function _getOpWaitingUser()
    {
        return 'waitingUser';
    }

    public function _getImgWaitingUser()
    {
        return getPathImage('fw') . 'org_chart/waiting_identity.png';
    }

    public function _getLabelWaitingUser()
    {
        return $this->lang->def('_WAITING_USER');
    }

    public function _getAddImage()
    {
        return getPathImage('fw') . 'org_chart/add_node.gif';
    }

    public function _getAddLabel()
    {
        return $this->lang->def('_ORGCHART_ADDNODE');
    }

    public function _getAddAlt()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getOpImportUsers()
    {
        return 'importusers';
    }

    public function _getImgImportUsers()
    {
        return getPathImage('fw') . 'org_chart/import.gif';
    }

    public function _getLabelImportUsers()
    {
        return $this->lang->def('_ORGCHART_IMPORT_USERS_ALT');
    }

    public function load()
    {
        if ($this->select_all) {
            $this->itemSelectedMulti = $this->tdb->getAllGroupST();
        }

        $ot = parent::load();

        if ($this->selector_mode) {
            if (!$this->multi_choice) {
                // only a choice - set all previously selecteds items as printed
                // and print the first as radio with checked = "cheched" in a
                // hidden div
                $arr_selected_not_printed = array_diff($this->itemSelectedMulti, $this->printed_items);
                $ot .= "<div style=\"display: none\">\n";
                if (is_array($arr_selected_not_printed) && count($arr_selected_not_printed) > 0) {
                    $idst = $arr_selected_not_printed[0];
                    $ot .= '<input type="radio"'
                        . ' id="' . DIRECTORY_ID . DIRECTORY_OP_SELECTMONO . '_' . $idst . '" '
                        . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTMONO . ']" '
                        . ' value="' . $idst . '"'
                        . ' checked="checked" />' . "\n";
                }
                $ot .= "</div>\n";
                $this->printed_items = array_merge($this->printed_items, $this->itemSelectedMulti);
            } elseif ($this->select_all) {
                $arr_selected_not_printed = array_diff($this->itemSelectedMulti, $this->printed_items_alt);
                $arr_selected_not_printed = array_diff($arr_selected_not_printed, $this->printed_items);
                $ot .= "<div style=\"display: none\">\n";
                if (is_array($arr_selected_not_printed) && count($arr_selected_not_printed) > 0) {
                    foreach ($arr_selected_not_printed as $idst) {
                        $ot .= '<input type="checkbox"'
                            . ' id="' . DIRECTORY_ID . DIRECTORY_OP_SELECTMONO . '_' . $idst . '" '
                            . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTITEM . '][' . $idst . ']" '
                            . ' value="' . $idst . '"'
                            . ' checked="checked" />' . "\n";
                    }
                }
                $ot .= "</div>\n";
            }

            $ot .= '<input type="hidden"'
                . ' id="' . DIRECTORY_ID . '_' . DIRECTORY_ID_PRINTEDITEM . '"'
                . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_ID_PRINTEDITEM . ']"'
                . ' value="' . urlencode(Util::serialize($this->printed_items)) . '" />' . "\n";
            $ot .= '<input type="hidden"'
                . ' id="' . DIRECTORY_ID . '_' . DIRECTORY_ID_PRINTEDFOLD . '"'
                . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_ID_PRINTEDFOLD . ']"'
                . ' value="' . urlencode(Util::serialize($this->printed_items_alt)) . '" />' . "\n";
        }

        return $ot;
    }

    public function setFilterNodes($arr_idst)
    {
        $this->filter_nodes = $this->tdb->getFoldersIdFromIdst($arr_idst);
        $this->tdb->setFilterPathFromFolderId($this->filter_nodes);
    }

    public function isFolderAccessible($folder = false)
    {
        if (is_array($this->filter_nodes)) {
            if ($folder === false) {
                return array_search($this->selectedFolder, $this->filter_nodes) !== false;
            } else {
                return array_search($folder->id, $this->filter_nodes) !== false;
            }
        } else {
            return true;
        }
    }

    public function loadState()
    {
        if ($this->session->has('org_chart_state')) {
            $this->setState($this->session->get('org_chart_state'));
        }
    }

    public function saveState()
    {
        $this->session->set('org_chart_state', $this->getState());
        $this->session->save();
    }

    public function _getOtherActions()
    {
        $waiting_user = $this->aclManager->getTempUserNumber();
        $to_check = ($GLOBALS['modname'] === 'directory' ? 'directory' : 'public_user_admin');
        if ($this->isFolderSelected()) {
            if ($this->isFolderAccessible() && checkPerm('createuser_org_chart', true, $to_check, 'framework')) {
                return [
                    $this->id . $this->_getOpCreateUser() => [$this->id . '[' . $this->_getOpCreateUser() . ']',
                        $this->lang->def('_NEW_USER'),
                        getPathImage('fw') . 'standard/user_create.gif', ],
                    $this->id . $this->_getOpImportUsers() => [$this->id . '[' . $this->_getOpImportUsers() . ']',
                        $this->lang->def('_ORG_CHART_IMPORT_USERS'),
                        getPathImage('fw') . 'org_chart/import.gif', ],
                ];
            } else {
                return [];
            }
        }
        $actions = [
            $this->id . $this->_getOpAssignField() => [$this->id . '[' . $this->_getOpAssignField() . ']',
                $this->lang->def('_ORGCHART_USER_FIELD'),
                $this->_getImgAssignField(), ],
        ];
        if (checkPerm('createuser_org_chart', true, $to_check, 'framework')) {
            $actions[$this->id . $this->_getOpCreateUser()] = [$this->id . '[' . $this->_getOpCreateUser() . ']',
                $this->lang->def('_NEW_USER'),
                getPathImage('fw') . 'standard/user_create.gif', ];
            $actions[$this->id . $this->_getOpImportUsers()] = [$this->id . '[' . $this->_getOpImportUsers() . ']',
                $this->lang->def('_ORG_CHART_IMPORT_USERS'),
                getPathImage('fw') . 'org_chart/import.gif', ];
        }
        if ($waiting_user && checkPerm('approve_waiting_user', true, 'directory', 'framework')) {
            $actions[$this->id . $this->_getOpWaitingUser()] = [$this->id . '[' . $this->_getOpWaitingUser() . ']',
                $this->_getLabelWaitingUser() . ' (' . $waiting_user . ')',
                $this->_getImgWaitingUser(), ];
        }

        return $actions;
    }

    public function getFolderPrintName(&$folder)
    {
        $print_name = '';
        if ($folder->level == 0) {
            $print_name = FormaLms\lib\Get::sett('title_organigram_chart');
        } else {
            if ($this->filter_nodes === false) {
                $print_name = str_replace('"', '&quot;', strip_tags($folder->otherValues[ORGDB_POS_TRANSLATION]));
            } elseif ($this->isFolderAccessible($folder)) {
                $print_name = str_replace('"', '&quot;', strip_tags($folder->otherValues[ORGDB_POS_TRANSLATION]));
            } else {
                $print_name = $this->lang->def('_HIDDEN');
            }
        }

        return $print_name;
    }

    public function getFolderPrintOther(&$folder)
    {
        if ($this->selector_mode && $this->simple_selector && $folder->id != 0 && $this->isFolderAccessible($folder)) {
            return 'onclick="var c = document.getElementById( \'' . $folder->idtag . '\' );'
                . 'c.checked = !c.checked; return false;"';
        }
    }

    public function getPreFolderName(&$folder)
    {
        if ($this->selector_mode && $this->simple_selector && $folder->id != 0 && $this->isFolderAccessible($folder)) {
            $idst = $this->tdb->getGroupST($folder->id);
            $this->printed_items[] = $idst;
            $folder->idtag = DIRECTORY_ID . DIRECTORY_OP_SELECTITEM . '_' . $idst;
            if ($this->multi_choice) {
                $out = '<input type="checkbox"'
                    . ' id="' . $folder->idtag . '" '
                    . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTITEM . '][' . $idst . ']" '
                    . ' value="' . $idst . '"';
            } else {
                $out = '<input type="radio"'
                    . ' id="' . $folder->idtag . '" '
                    . ' name="' . DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTMONO . ']" '
                    . ' value="' . $idst . '"';
            }
            if (array_search($idst, $this->itemSelectedMulti) !== false) {
                $out .= ' checked="checked" ';
            }
            $out .= ' />';
            $out .= '<label class="access-only" for="' . $folder->idtag . '">' . $this->getFolderPrintName($folder) . '</label>';

            return $out;
        } else {
            return '';
        }
    }

    public function extendedParsing($arrayState, $arrayExpand, $arrayCompress)
    {
        if (isset($arrayState['editpersonsave'])) {
            $idst = $_POST['idst'];
            $userid = $_POST['userid'];
            $firstname = $_POST['firstname'];
            $lastname = $_POST['lastname'];
            $pass = $_POST['pass'];
            $userlevel = $_POST['userlevel'];
            $olduserlevel = $_POST['olduserlevel'];
            if ($pass === '') {
                $pass = false;
            }
            $email = $_POST['email'];

            if ($idst !== '') {
                //-extra field-----------------------------------------------
                require_once _adm_ . '/lib/lib.field.php';
                $fields = new FieldList();
                //$re_filled = $fields->isFilledFieldsForUser($idst);

                if ($userid != '') {
                    $info = $this->aclManager->getUser($idst, false);
                    $this->aclManager->updateUser($idst, $userid, $firstname, $lastname,
                        $pass, $email, false,
                        false);
                    //-extra field-----------------------------------------------
                    $fields->storeFieldsForUser($idst);
                    //-----------------------------------------------------------

                    // remove from old group level
                    $this->aclManager->removeFromGroup($olduserlevel, $idst);

                    // add to group level
                    $this->aclManager->addToGroup($userlevel, $idst);

                    if (($this->aclManager->absoluteId($userid) != $info[ACL_INFO_USERID]) || ($this->aclManager->password_verify_update($pass, $info[ACL_INFO_PASS]))) {
                        require_once _base_ . '/lib/lib.eventmanager.php';

                        $reg_code = null;
                        $uma = new UsermanagementAdm();
                        if ($nodes = $uma->getUserFolders($userid)) {
                            $idst_oc = array_keys($nodes)[0];

                            if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                                $reg_code = sql_fetch_object($query)->idOrg;
                            }
                        }

                        $array_subst = [
                            '[url]' => FormaLms\lib\Get::site_url(),
                            '[userid]' => $this->aclManager->getUserid($userid),
                            '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
                            '[password]' => $pass,
                        ];

                        // message to user that is odified
                        $msg_composer = new EventMessageComposer();

                        $msg_composer->setSubjectLangText('email', '_MODIFIED_USER_SBJ', false);
                        $msg_composer->setBodyLangText('email', '_MODIFIED_USER_TEXT', $array_subst);
                        if ($pass != '') {
                            $msg_composer->setBodyLangText('email', '_PASSWORD_CHANGED', ['[password]' => $pass]);
                        }

                        $msg_composer->setBodyLangText('sms', '_MODIFIED_USER_TEXT_SMS', $array_subst);
                        if ($pass != '') {
                            $msg_composer->setBodyLangText('sms', '_PASSWORD_CHANGED_SMS', ['[password]' => $pass]);
                        }

                        createNewAlert('UserMod', 'directory', 'edit', '1', 'User ' . $userid . ' was modified',
                            [$userid], $msg_composer);

                        $uinfo = \FormaLms\lib\Forma::getAclManager()->getUser($idst, false);

                        $array_subst = [
                            '[url]' => FormaLms\lib\Get::site_url(),
                            '[dynamic_link]' => getCurrentDomain($reg_code) ?: FormaLms\lib\Get::site_url(),
                            '[firstname]' => $uinfo[ACL_INFO_FIRSTNAME],
                            '[lastname]' => $uinfo[ACL_INFO_LASTNAME],
                            '[username]' => $userid,
                        ];

                        // message to user that is odified
                        $msg_composer = new EventMessageComposer();

                        $msg_composer->setSubjectLangText('email', '_EVENT_MOD_USER_SBJ', false);
                        $msg_composer->setBodyLangText('email', '_EVENT_MOD_USER_TEXT', $array_subst);

                        $msg_composer->setBodyLangText('sms', '_EVENT_MOD_USER_TEXT_SMS', $array_subst);

                        $permission_godadmin = $this->aclManager->getGroupST(ADMIN_GROUP_GODADMIN);
                        $permission_admin = $this->aclManager->getGroupST(ADMIN_GROUP_ADMIN);

                        $recipients = $this->aclManager->getGroupAllUser($permission_godadmin);
                        $recipients = array_merge($recipients, $this->aclManager->getGroupAllUser($permission_admin));

                        createNewAlert('UserModSuperAdmin', 'directory', 'edit', '1', 'User ' . $userid . ' was modified',
                            $recipients, $msg_composer);
                    }

                    if ($userlevel !== $olduserlevel) {
                        require_once \FormaLms\lib\Forma::inc(_base_ . '/lib/lib.eventmanager.php');

                        $uinfo = \FormaLms\lib\Forma::getAclManager()->getUser($idst, false);

                        $array_subst = [
                            '[url]' => FormaLms\lib\Get::site_url(),
                            '[firstname]' => $uinfo[ACL_INFO_FIRSTNAME],
                            '[lastname]' => $uinfo[ACL_INFO_LASTNAME],
                            '[username]' => $userid,
                        ];

                        // message to user that is odified
                        $msg_composer = new EventMessageComposer();

                        $msg_composer->setSubjectLangText('email', '_EVENT_CHANGE_NODE_USER_SBJ', false);
                        $msg_composer->setBodyLangText('email', '_EVENT_CHANGE_NODE_USER_SBJ', $array_subst);

                        $msg_composer->setBodyLangText('sms', '_EVENT_CHANGE_NODE_USER_SBJ_SMS', $array_subst);

                        $permission_godadmin = $this->aclManager->getGroupST(ADMIN_GROUP_GODADMIN);
                        $permission_admin = $this->aclManager->getGroupST(ADMIN_GROUP_ADMIN);

                        $recipients = $this->aclManager->getGroupAllUser($permission_godadmin);
                        $recipients = array_merge($recipients, $this->aclManager->getGroupAllUser($permission_admin));

                        createNewAlert('UserModNodeSuperAdmin', 'directory', 'edit', '1', 'User ' . $userid . ' change node',
                            $recipients, $msg_composer);
                    }

                    $GLOBALS['page']->add(getResultUi($this->lang->def('_OPERATION_SUCCESSFUL')));
                } else {
                    $this->op = 'reedit_person';
                    //$GLOBALS['page']->add( getErrorUi( implode(',', $re_filled) ), 'content');
                }
            } else {
                if (isset($_POST['arr_idst_groups'])) {
                    $arr_idst_groups = Util::unserialize(urldecode($_POST['arr_idst_groups']));
                    $acl = \FormaLms\lib\Forma::getAcl();
                    $arr_idst_all = $acl->getArrSTGroupsST($arr_idst_groups);
                } else {
                    $arr_idst_groups = false;
                    $arr_idst_all = false;
                }
                //-verify that userid is not already used
                if ($this->aclManager->getUserST($userid) !== false) {
                    $GLOBALS['page']->add(getErrorUi($this->lang->def('_USERID_DUPLICATE')));
                    $_POST['userid'] = '';
                    $this->op = 'reedit_person';
                } else {
                    //-verify mandatory extra field--------------------------------
                    require_once _adm_ . '/lib/lib.field.php';
                    $fields = new FieldList();
                    //$re_filled = $fields->isFilledFieldsForUser(0, $arr_idst_all);
                    if ($arr_idst_groups != false && $userid != '') {
                        $idst = false;
                        if (\FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {
                            $limit_insert = \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getAdminPreference('admin_rules.limit_user_insert');
                            $max_insert = \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getAdminPreference('admin_rules.max_user_insert');
                            $direct_insert = \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->getAdminPreference('admin_rules.direct_user_insert');

                            if (($limit_insert == 'off') || ($limit_insert == 'on' && $max_insert > 0)) {
                                if ($direct_insert == 'on') {
                                    \FormaLms\lib\FormaUser::getCurrentUser()->getUserPreference()->setPreference('admin_rules.max_user_insert', $max_insert - 1);
                                    $idst = $this->aclManager->registerUser($userid, $firstname, $lastname,
                                        $pass, $email, '',
                                        '');

                                    require_once _base_ . '/lib/lib.preference.php';
                                    $preference = new UserPreferences($idst);
                                    $preference->savePreferences($_POST, 'ui.');

                                    $uma = new UsermanagementAdm();
                                    $reg_code = null;
                                    if ($nodes = $uma->getUserFolders($idst)) {
                                        $idst_oc = array_keys($nodes)[0];

                                        if ($query = sql_query("SELECT idOrg FROM %adm_org_chart_tree WHERE idst_oc = $idst_oc LIMIT 1")) {
                                            $reg_code = sql_fetch_object($query)->idOrg;
                                        }
                                    }

                                    require_once _base_ . '/lib/lib.eventmanager.php';
                                    $pl_man = &PlatformManager::createInstance();

                                    $array_subst = ['[url]' => FormaLms\lib\Get::site_url(),
                                        '[userid]' => $userid,
                                        '[password]' => $pass,
                                        '[dynamic_link]' => getCurrentDomain($this->tree) ?: FormaLms\lib\Get::site_url(),
                                    ];
                                    // message to user that is inserted
                                    $msg_composer = new EventMessageComposer();

                                    $msg_composer->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
                                    $msg_composer->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst);

                                    $msg_composer->setBodyLangText('sms', '_REGISTERED_USER_TEXT_SMS', $array_subst);

                                    createNewAlert('UserNew', 'directory', 'edit', '1', 'User ' . $userid . ' created',
                                        [$userid], $msg_composer);
                                    $GLOBALS['page']->add(getResultUi($this->lang->def('_INSERTED_NEW_USER')));
                                } else {
                                    $acl = \FormaLms\lib\Forma::getAcl();

                                    $idst = $this->aclManager->registerTempUser($userid, $firstname, $lastname,
                                        $pass, $email, 0, \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt());

                                    require_once _base_ . '/lib/lib.eventmanager.php';
                                    $pl_man = &PlatformManager::createInstance();

                                    $array_subst = ['[url]' => FormaLms\lib\Get::site_url(),
                                        '[userid]' => $userid,
                                        '[password]' => $pass, ];

                                    // message to user that is waiting
                                    $msg_composer = new EventMessageComposer();

                                    $msg_composer->setSubjectLangText('email', '_WAITING_USER_SBJ', false);
                                    $msg_composer->setBodyLangText('email', '_WAITING_USER_TEXT', $array_subst);

                                    $msg_composer->setBodyLangText('sms', '_WAITING_USER_TEXT_SMS', $array_subst);

                                    // send message to the user subscribed
                                    createNewAlert('UserNew', 'directory', 'edit', '1', 'User ' . $userid . ' was modified',
                                        [$userid], $msg_composer);

                                    // set as recipients all who can approve a waiting user
                                    $msg_c_approve = new EventMessageComposer();

                                    $msg_c_approve->setSubjectLangText('email', '_TO_APPROVE_USER_SBJ', false);
                                    $msg_c_approve->setBodyLangText('email', '_TO_APPROVE_USER_TEXT', ['[url]' => FormaLms\lib\Get::site_url()]);

                                    $msg_c_approve->setBodyLangText('sms', '_TO_APPROVE_USER_TEXT_SMS', ['[url]' => FormaLms\lib\Get::site_url()]);
                                    $idst_approve = $acl->getRoleST('/framework/admin/directory/approve_waiting_user');
                                    $recipients = $this->aclManager->getAllRoleMembers($idst_approve);

                                    createNewAlert('UserNewModerated', 'directory', 'edit', '1', 'User ' . $userid . ' to moderate',
                                        $recipients, $msg_c_approve);

                                    $GLOBALS['page']->add(getResultUi($this->lang->def('_INSERTED_WAIT_FOR_ADMIN')));
                                }
                            }
                        } else {
                            $idst = $this->aclManager->registerUser($userid, $firstname, $lastname,
                                $pass, $email, '',
                                '');

                            require_once _base_ . '/lib/lib.preference.php';
                            $preference = new UserPreferences($idst);
                            $preference->savePreferences($_POST, 'ui.');

                            require_once _base_ . '/lib/lib.eventmanager.php';
                            $pl_man = &PlatformManager::createInstance();

                            $array_subst = ['[url]' => FormaLms\lib\Get::site_url(),
                                '[userid]' => $userid,
                                '[password]' => $pass, ];
                            // message to user that is inserted
                            $msg_composer = new EventMessageComposer();

                            $msg_composer->setSubjectLangText('email', '_REGISTERED_USER_SBJ', false);
                            $msg_composer->setBodyLangText('email', '_REGISTERED_USER_TEXT', $array_subst);

                            $msg_composer->setBodyLangText('sms', '_REGISTERED_USER_TEXT_SMS', $array_subst);

                            createNewAlert('UserNew', 'directory', 'edit', '1', 'User ' . $userid . ' created',
                                [$idst], $msg_composer);
                        }
                        if ($idst !== false) {
                            foreach ($arr_idst_groups as $idst_group) {
                                $this->aclManager->addToGroup($idst_group, $idst);
                            }
                            // add to group level
                            $this->aclManager->addToGroup($userlevel, $idst);

                            //-save extra field------------------------------------------
                            $fields->storeFieldsForUser($idst, $arr_idst_all);
                            //-----------------------------------------------------------
                        }
                    } else {
                        $this->op = 'reedit_person';
                        //$GLOBALS['page']->add(getErrorUi(implode('<br/>', $re_filled)), 'content');
                    }
                }
            }
        } elseif (isset($arrayState['deleteperson'])) {
            $idst = $_POST['idst'];
            if ($idst !== '') {
                require_once _base_ . '/lib/lib.eventmanager.php';

                $u_info = $this->aclManager->getUser($idst, false);
                $userid = $u_info[ACL_INFO_USERID];

                $pl_man = &PlatformManager::createInstance();

                $array_subst = ['[url]' => FormaLms\lib\Get::site_url(),
                    '[userid]' => $this->aclManager->relativeId($userid), ];
                // message to user that is inserted
                $msg_composer = new EventMessageComposer();

                $msg_composer->setSubjectLangText('email', '_DELETED_USER_SBJ', false);
                $msg_composer->setBodyLangText('email', '_DELETED_USER_TEXT', $array_subst);

                $msg_composer->setBodyLangText('sms', '_DELETED_USER_TEXT_SMS', $array_subst);
                /*
                createNewAlert(	'UserDel', 'directory', 'edit', '1', 'User '.$userid.' deleted',
                            array($idst), $msg_composer );*/

                $event = &FormaEventManager::newEvent('UserDel', 'directory', 'edit', '1', 'User ' . addslashes($userid) . ' deleted');
                $event->setProperty('recipientid', implode(',', [$idst]));
                $event->setProperty('subject', $msg_composer->getSubject('email', Lang::get()));
                $event->setProperty('body', $msg_composer->getBody('email', Lang::get()));
                $msg_composer->prepare_serialize();
                $event->setProperty('MessageComposer', addslashes(rawurlencode(serialize($msg_composer))));
                $event->setProperty('userdeleted', $idst);
                FormaEventManager::dispatch($event);

                $this->aclManager->deleteUser($idst);

                $GLOBALS['page']->add(getResultUi($this->lang->def('_OPERATION_SUCCESSFUL')));
            }
        }
        if (!isset($arrayState[$this->id])) {
            return;
        }
        foreach ($arrayState[$this->id] as $key => $action) {
            if ($key === 'save_newfolder') {
                $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
                $mand_lang = Lang::get();
                if (!isset($action[$mand_lang])) {
                    $this->op = 'newfolder';
                } else {
                    $folderName = [];
                    foreach ($array_lang as $langItem) {
                        $folderName[$langItem] = $arrayState[$this->id]['new_folder'][$langItem];
                    }
                    $this->tdb->addFolderByIdTranslation($this->selectedFolder, $folderName);
                    $this->refresh = true;
                }
            } elseif ($key === 'save_renamefolder') {
                $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();

                if ($this->getSelectedFolderId() == '0') {
                    $mand_lang = 'root';
                } else {
                    $mand_lang = Lang::get();
                }

                if (!isset($action[$mand_lang]) || $action[$mand_lang] == '') {
                    $this->op = 'renamefolder';
                } else {
                    $folder_id = $this->getSelectedFolderId();

                    $acl = \FormaLms\lib\Forma::getAcl();

                    //-extra field check mandatory -----------------------------
                    require_once _adm_ . '/lib/lib.field.php';
                    $fields = new FieldList();
                    $fields->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
                    $fields->setFieldEntryTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDENTRYTABLE);

                    $folder_id = $this->getSelectedFolderId();
                    $folder = &$this->tdb->getFolderById($folder_id);
                    $ancestor = $this->tdb->getAllParentId($folder, $this->tdb);
                    array_push($ancestor, $folder_id);

                    //$filled = $fields->isFilledFieldsForUser($folder_id, $ancestor, FALSE );

                    //----------------------------------------------------------

                    //if( $filled === true ) {
                    $folderName = [];
                    if ($this->getSelectedFolderId() == '0') {
                        // is root
                        $folderName = $arrayState[$this->id]['rename_folder']['root'];

                        $query_root_name = '
							UPDATE ' . $GLOBALS['prefix_fw'] . "_setting 
							SET param_value = '" . $folderName . "'
							WHERE param_name = 'title_organigram_chart'";
                        sql_query($query_root_name);
                    } else {
                        foreach ($array_lang as $langItem) {
                            $folderName[$langItem] = $arrayState[$this->id]['rename_folder'][$langItem];
                        }
                        $this->tdb->updateFolderByIdTranslation($this->selectedFolder, $folderName);
                    }

                    //-extra field store --------------------------------------
                    $folder_idst = $this->tdb->getGroupST($folder_id);

                    $fl = new FieldList();
                    $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
                    $fl->setFieldEntryTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDENTRYTABLE);
                    $arr_groups_filterd = $acl->getSTGroupsST($folder_idst, FILTER_FOLD);

                    $fl->storeFieldsForUser($folder_id, $ancestor, false);
                    //----------------------------------------------------------

                    $this->refresh = true;
                    if ($this->getSelectedFolderId() == '0') {
                        Util::jump_to('index.php?modname=directory&op=org_chart');
                    }
                    /*} else {

                        $this->op = 'renamefolder';
                        $GLOBALS['page']->add( getErrorUi(implode('<br/>', $filled)), 'content' );
                    }*/
                }
            } elseif ($key === 'next_formfield1') {
                $this->op = 'folder_field2';
            } elseif ($key === 'save_formfield') {
                require_once _adm_ . '/lib/lib.field.php';

                if (isset($arrayState[$this->id]['field_set'])) {
                    $arr_fields = $arrayState[$this->id]['field_set'];
                } else {
                    $arr_fields = [];
                }

                if (isset($arrayState[$this->id]['field_mandatory'])) {
                    $arr_fields_mandatory = $arrayState[$this->id]['field_mandatory'];
                } else {
                    $arr_fields_mandatory = [];
                }

                $fl = new FieldList();
                $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);

                $arr_all_fields = $fl->getAllFields();
                $id_folder = $this->getSelectedFolderId();

                // remove all fields
                foreach ($arr_all_fields as $id_field => $field) {
                    $fl->removeFieldFromGroup($id_field, $id_folder);
                }

                // add selected fields
                foreach ($arr_fields as $id_field => $dummy_val) {
                    $fl->addFieldToGroup($id_field,
                        $id_folder,
                        isset($arr_fields_mandatory[$id_field]) ? $arr_fields_mandatory[$id_field] : 'false'
                    );
                }
            } elseif ($key === 'next1_assignfield') {
                $this->op = 'assign2_field';
            } elseif ($key === 'next2_assignfield') {
                $this->op = 'assign3_field';
            } elseif ($key === 'save_assignfield') {
                $arr_fields = $arrayState[$this->id]['field_set'];
                $arr_fields_mandatory = (isset($arrayState[$this->id]['field_mandatory']) ? $arrayState[$this->id]['field_mandatory'] : []);
                $arr_fields_useraccess = (isset($arrayState[$this->id]['field_useraccess']) ? $arrayState[$this->id]['field_useraccess'] : []);
                require_once _adm_ . '/lib/lib.field.php';
                $fl = new FieldList();

                foreach ($arr_fields as $id_filed => $status) {
                    switch ($status) {
                        case ORG_CHART_FIELD_NO:
                            $fl->removeFieldFromGroup(
                                $id_filed,
                                $this->tdb->getGroupST($this->getSelectedFolderId())
                            );
                            $fl->removeFieldFromGroup(
                                $id_filed,
                                $this->tdb->getGroupDescendantsST($this->getSelectedFolderId())
                            );
                            break;
                        case ORG_CHART_FIELD_NORMAL:
                            $fl->removeFieldFromGroup(
                                $id_filed,
                                $this->tdb->getGroupDescendantsST($this->getSelectedFolderId())
                            );
                            $fl->addFieldToGroup(
                                $id_filed,
                                $this->tdb->getGroupST($this->getSelectedFolderId()),
                                isset($arr_fields_mandatory[$id_filed]) ? $arr_fields_mandatory[$id_filed] : 'false',
                                isset($arr_fields_useraccess[$id_filed]) ? $arr_fields_useraccess[$id_filed] : 'readonly'
                            );

                            break;
                        case ORG_CHART_FIELD_DESCEND:
                            $fl->removeFieldFromGroup(
                                $id_filed,
                                $this->tdb->getGroupST($this->getSelectedFolderId())
                            );
                            $fl->addFieldToGroup(
                                $id_filed,
                                $this->tdb->getGroupDescendantsST($this->getSelectedFolderId()),
                                isset($arr_fields_mandatory[$id_filed]) ? $arr_fields_mandatory[$id_filed] : 'false',
                                isset($arr_fields_useraccess[$id_filed]) ? $arr_fields_useraccess[$id_filed] : 'readonly'
                            );
                            break;
                    }
                }
            } elseif ($key == $this->_getOpFolderField()) {
                if (is_array($action)) {
                    $this->selectedFolder = key($action);
                }
                $this->op = 'folder_field';
            } elseif ($key == $this->_getOpImportUsers()) {
                if (is_array($action)) {
                    $this->selectedFolder = key($action);
                }
                $this->op = 'import_users';
            } elseif ($key === 'next1_importusers') {
                $this->op = 'import_users2';
            } elseif ($key === 'next2_importusers') {
                $this->op = 'import_users3';
            } elseif ($key === $this->_getOpAssignField()) {
                if (is_array($action)) {
                    $this->selectedFolder = key($action);
                }
                $this->op = 'assign_field';
            } elseif ($key === $this->_getOpAssignUser()) {
                if (is_array($action)) {
                    $this->selectedFolder = key($action);
                }
                $this->op = 'addtotree';
            } elseif ($key === $this->_getOpCreateUser()) {
                if (is_array($action)) {
                    $this->selectedFolder = key($action);
                }
                $this->op = 'create_user';
            } elseif ($key === $this->_getOpWaitingUser()) {
                $this->op = 'waiting_user';
            }
        }

        return;
        $arrayExpand = $arrayExpand;
        $arrayCompress = $arrayCompress;
    }

    public function canMove()
    {
        return false; /*($this->isFolderSelected() && $this->isFolderAccessible());*/
    }

    public function canRename()
    {
        return false; /*return ($this->isFolderSelected() && $this->isFolderAccessible());*/
    }

    public function canDelete()
    {
        return false; /*return ($this->isFolderSelected() && $this->isFolderAccessible());*/
    }

    public function canAdd()
    {
        return $this->isFolderAccessible() && (FormaLms\lib\Get::sett('use_org_chart') == '1');
    }

    public function canInlineMove()
    {
        return !$this->selector_mode;
    }

    public function canInlineRename()
    {
        return !$this->selector_mode;
    }

    public function canInlineDelete()
    {
        return !$this->selector_mode && checkPerm('deluser_org_chart', true, 'directory', 'framework');
    }

    /**
     * functions canInlineXXXXItem()
     * return TRUE if the XXXX action is available for specific item.
     **/
    public function canInlineMoveItem(&$stack, $level)
    {
        return $level > 0 && $this->isFolderAccessible($stack[$level]['folder']);
    }

    public function canInlineRenameItem(&$stack, $level)
    {
        return $this->isFolderAccessible($stack[$level]['folder']);
    }

    public function canInlineDeleteItem(&$stack, $level)
    {
        return $level > 0 && $this->isFolderAccessible($stack[$level]['folder']);
    }

    public function getImage(&$stack, $currLev, $maxLev)
    {
        $arr_result = parent::getImage($stack, $currLev, $maxLev);
        $enabled = true;
        if ($this->filter_nodes === false || $this->isFolderAccessible($stack[$currLev]['folder'])) {
            $enabled = true;
        } else {
            $enabled = false;
        }
        if ($maxLev > 0 && $currLev == $maxLev && !$enabled) {
            $arr_toks = explode('.', $arr_result[1]);
            $arr_result[1] = implode('.', array_slice($arr_toks, 0, count($arr_toks) - 1))
                . '_disabled.'
                . $arr_toks[count($arr_toks) - 1];
        }

        return $arr_result;
    }

    public function printElement(&$stack, $level)
    {
        $tree = parent::printElement($stack, $level);
        if (!$this->selector_mode) {
            // assign field to folder
            // assign field to user
            if ($this->isFolderAccessible($stack[$level]['folder']) && FormaLms\lib\Get::sett('use_user_fields') == '1') {
                $tree .= '<input type="image" class="tree_view_image" '
                    . ' src="' . $this->_getImgAssignField() . '"'
                    . ' id="' . $this->id . '_' . $this->_getOpAssignField() . '_' . $stack[$level]['folder']->id . '" '
                    . ' name="' . $this->id . '[' . $this->_getOpAssignField() . '][' . $stack[$level]['folder']->id . ']" '
                    . ' title="' . $this->_getLabelAssignField() . '" '
                    . ' alt="' . $this->_getLabelAssignField() . '" />';
            }
            /*$tree .= '<input type="image" class="tree_view_image" '
                .' src="'.$this->_getImgImportUsers().'"'
                .' id="'.$this->id.'_'.$this->_getOpImportUsers().'_'.$stack[$level]['folder']->id.'" '
                .' name="'.$this->id.'['.$this->_getOpImportUsers().']['.$stack[$level]['folder']->id.']" '
                .' title="'.$this->_getLabelImportUsers().'" '
                .' alt="'.$this->_getLabelImportUsers().'" />';*/
            if ($level != 0) {
                if ($this->isFolderAccessible($stack[$level]['folder'])) {
                    $tree .= '<input type="image" class="tree_view_image" '
                        . ' src="' . $this->_getImgAssignUser() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpAssignUser() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpAssignUser() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getLabelAssignUser() . '" '
                        . ' alt="' . $this->_getLabelAssignUser() . '" />';
                    if (FormaLms\lib\Get::sett('use_org_chart_field') == '1') {
                        $tree .= '<input type="image" class="tree_view_image" '
                            . ' src="' . $this->_getImgFolderField() . '"'
                            . ' id="' . $this->id . '_' . $this->_getOpFolderField() . '_' . $stack[$level]['folder']->id . '" '
                            . ' name="' . $this->id . '[' . $this->_getOpFolderField() . '][' . $stack[$level]['folder']->id . ']" '
                            . ' title="' . $this->_getLabelFolderField() . '" '
                            . ' alt="' . $this->_getLabelFolderField() . '" />';
                    }
                }
            } else {
                $tree .= '<div class="TVActionEmpty"></div>';
            }
        } elseif (!$this->simple_selector) {
            $stack[$level]['desc'] = false;
            $idst = $this->tdb->getGroupST($stack[$level]['folder']->id);
            $idst_desc = $this->tdb->getGroupDescendantsST($stack[$level]['folder']->id);
            if ($level > 0 && $stack[$level - 1]['desc']) {
                $stack[$level]['desc'] = true;
                $disabled = ' disabled="disabled" ';
            } else {
                $disabled = '';
            }
            $radio_name = DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTRADIO . '][' . $idst . '_' . $idst_desc . ']';
            $check_name = DIRECTORY_ID . '[' . DIRECTORY_OP_SELECTFOLD . '][' . $idst . ']';
            $this->printed_items_alt[] = $idst;
            $found = false;
            $tree .= '<div class="special_input">';
            $tree .= Form::getLabel(DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_INHERIT_' . $idst,
                $this->lang->def('_ORG_CHART_INHERIT'),
                'label_bold tree_view_image');
            $tree .= '<input type="radio" class="tree_view_image"'
                . ' id="' . DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_INHERIT_' . $idst . '" '
                . ' name="' . $radio_name . '" '
                . ' value="' . $idst_desc . '"'
                . $disabled;
            if (array_search($idst_desc, $this->itemSelectedMulti) !== false) {
                $tree .= ' checked="checked" ';
                $stack[$level]['desc'] = true;
                $found = true;
            }
            $tree .= ' />';
            $tree .= Form::getLabel(DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_YES_' . $idst,
                $this->lang->def('_YES'),
                'label_bold tree_view_image');
            $tree .= '<input type="radio"  class="tree_view_image"'
                . ' id="' . DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_YES_' . $idst . '" '
                . ' name="' . $radio_name . '" '
                . ' value="' . $idst . '"'
                . $disabled;
            if (array_search($idst, $this->itemSelectedMulti) !== false) {
                $tree .= ' checked="checked" ';
                $found = true;
            }
            $tree .= ' />';
            $tree .= Form::getLabel(DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_NO_' . $idst,
                $this->lang->def('_NO'),
                'label_bold tree_view_image');
            $tree .= '<input type="radio" class="tree_view_image"'
                . ' id="' . DIRECTORY_ID . DIRECTORY_OP_SELECTRADIO . '_NO_' . $idst . '" '
                . ' name="' . $radio_name . '" '
                . ' value=""'
                . $disabled;
            if (!$found) {
                $tree .= ' checked="checked" ';
            }
            $tree .= ' />';

            $tree .= '</div>';
        }

        return $tree;
    }

    public function loadNewFolder()
    {
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();

        $tree = $form->getFormHeader($this->lang->def('_NEW_FOLDER'));
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $mand_lang = Lang::get();
        foreach ($array_lang as $k => $lang_code) {
            $tree .= $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                'new_folder_' . $lang_code,
                $this->id . '[new_folder][' . $lang_code . ']',
                255,
                '',
                $lang_code . ' ' . $this->lang->def('_NEW_FOLDER'));
        }

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_newfolder' . $this->id, $this->id . '[save_newfolder]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadRenameFolder()
    {
        $tdb = &$this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());
        $folder_idst = $tdb->getGroupST($this->getSelectedFolderId());
        $acl = \FormaLms\lib\Forma::getAcl();
        //$idst_field_group = $aclManager->getGroupST(ORG_CHART_FOLDER_FIELD_GROUP);

        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();

        $tree = $form->openElementSpace();
        $tree .= $this->printState();

        $array_lang = \FormaLms\lib\Forma::langManager()->getAllLangCode();
        $mand_lang = Lang::get();
        $array_translations = $tdb->getFolderTranslations($this->getSelectedFolderId());

        if ($this->getSelectedFolderId() == '0') {
            // is root
            $tree .= $form->getTextfield($this->lang->def('_ROOT_RENAME'),
                'rename_folder_root',
                $this->id . '[rename_folder][root]',
                255,
                FormaLms\lib\Get::sett('title_organigram_chart'),
                FormaLms\lib\Get::sett('title_organigram_chart')
                . ' ' . $this->lang->def('MOD'));
        } else {
            foreach ($array_lang as $k => $lang_code) {
                $tree .= $form->getTextfield((($mand_lang == $lang_code) ? '<span class="mandatory">*</span>' : '') . $lang_code,
                    'rename_folder_' . $lang_code,
                    $this->id . '[rename_folder][' . $lang_code . ']',
                    255,
                    $array_translations[$lang_code],
                    $lang_code . ' ' . $this->lang->def('_MOD'));
            }
        }
        $tree .= $form->closeElementSpace();

        // -- begin -- custom fields for folder
        $tree .= $form->getOpenFieldset($this->lang->def('_ASSIGNED_EXTRAFIELD'));

        require_once _adm_ . '/lib/lib.field.php';
        $fields = new FieldList();
        $fields->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
        $fields->setFieldEntryTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDENTRYTABLE);

        $folder_id = $this->getSelectedFolderId();
        $folder = &$this->tdb->getFolderById($folder_id);
        $ancestor = $this->tdb->getAllParentId($folder, $this->tdb);
        $ancestor[] = $folder_id;

        $tree .= $fields->playFieldsForUser($folder_id, $ancestor, false, false);

        $tree .= $form->getCloseFieldset();
        // -- end -- custom fields for folder

        $tree .= $form->openButtonSpace()
            . $form->getButton('save_renamefolder' . $this->id, $this->id . '[save_renamefolder]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadFolderField()
    {
        require_once _adm_ . '/lib/lib.field.php';
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $fl = new FieldList();
        $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);
        //$acl =& \FormaLms\lib\FormaUser::getCurrentUser()->getACL();
        //$aclManager =& $acl->getACLManager();
        $arr_all_fields = $fl->getAllFields();
        $id_folder = $this->getSelectedFolderId();
        $id_folder_desc = $this->tdb->getFolderTranslations($id_folder);
        if (isset($id_folder_desc [Lang::get()])) {
            $id_folder_desc = $id_folder_desc [Lang::get()];
        } else {
            $id_folder_desc = '';
        }
        $tree = $form->getFormHeader($this->lang->def('_ORG_CHART_LIST_FIELDS') . ' : ' . $id_folder_desc);
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        $tree .= $form->getHidden($this->id . '_id_folder',
            $this->id . '[id_folder]',
            $id_folder);
        $tree .= $form->getHidden($this->id . '_id_folder_desc',
            $this->id . '[id_folder_desc]',
            $id_folder_desc);

        //$idst_group = $aclManager->getGroupST(ORG_CHART_FOLDER_FIELD_GROUP);
        $arr_fields = $fl->getFieldsFromIdst([$id_folder], false);
        foreach ($arr_all_fields as $field) {
            $tree .= $form->getCheckbox(
                $field[FIELD_INFO_TRANSLATION],
                $this->id . '_' . $field[FIELD_INFO_ID],
                $this->id . '[field_set][' . $field[FIELD_INFO_ID] . ']',
                $field[FIELD_INFO_ID],
                isset($arr_fields[$field[FIELD_INFO_ID]]));
        }

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('next_formfield1' . $this->id, $this->id . '[next_formfield1]', $this->lang->def('_NEXT'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadFolderField2()
    {
        if (isset($_POST[$this->id]['field_set'])) {
            $arr_fields = $_POST[$this->id]['field_set'];
        } else {
            $arr_fields = [];
        }

        $id_folder = $_POST[$this->id]['id_folder'];
        $id_folder_desc = $_POST[$this->id]['id_folder_desc'];
        require_once _adm_ . '/lib/lib.field.php';
        $fl = new FieldList();
        $fl->setGroupFieldsTable($GLOBALS['prefix_fw'] . ORGCHAR_FIELDTABLE);

        $arr_all_fields = $fl->getAllFields();
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $tree = $form->openElementSpace();
        $tree .= $this->printState();

        $tree .= $form->getHidden($this->id . '_id_folder',
            $this->id . '[id_folder]',
            $id_folder);
        $tree .= $form->getHidden($this->id . '_id_folder_desc',
            $this->id . '[id_folder_desc]',
            $id_folder_desc);

        foreach ($arr_fields as $id_field => $status) {
            $tree .= $form->getHidden($this->id . '_' . $id_field,
                $this->id . '[field_set][' . $id_field . ']',
                '');
        }

        // data from previous selected fields
        $arr_fields_prev = $fl->getFieldsFromIdst([$id_folder], false);

        foreach ($arr_fields as $id_field) {
            if (isset($arr_fields_prev[$id_field]) && $arr_fields_prev[$id_field][FIELD_INFO_MANDATORY] == 'true') {
                $checked = true;
            } else {
                $checked = false;
            }

            $field = $arr_all_fields[$id_field];

            $tree .= $form->getCheckbox(
                $field[FIELD_INFO_TRANSLATION],
                $this->id . '_' . $field[FIELD_INFO_ID],
                $this->id . '[field_mandatory][' . $field[FIELD_INFO_ID] . ']',
                'true',
                $checked);
        }

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_formfield' . $this->id, $this->id . '[save_formfield]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadAssignField()
    {
        $tdb = &$this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());

        require_once _adm_ . '/lib/lib.field.php';
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $fl = new FieldList();

        $tree = $form->getFormHeader($this->lang->def('_ORG_CHART_LIST_FIELDS'));
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        $acl = \FormaLms\lib\Forma::getAcl();

        $arr_all_fields = $fl->getAllFields();
        $idst_group = $tdb->getGroupST($this->getSelectedFolderId());
        $idst_desc = $tdb->getGroupDescendantsST($this->getSelectedFolderId());
        $arr_fields_normal = $fl->getFieldsFromIdst([$idst_group]);
        $arr_fields_descend = $fl->getFieldsFromIdst([$idst_desc]);
        $arr_idst_inherit = array_merge($acl->getGroupGroupsST($idst_desc), $acl->getGroupGroupsST($idst_group));
        $arr_fields_inherit = $fl->getFieldsFromIdst($arr_idst_inherit);
        if (count($arr_idst_inherit)) {
            $arr_groupid = $this->aclManager->getGroupsId($arr_idst_inherit);
        } else {
            $arr_groupid = [];
        }
        $arr_tree_translations = $tdb->getFoldersCurrTranslation($arr_groupid);
        $arr_values = [
            $this->lang->def('_NO') => ORG_CHART_FIELD_NO,
            $this->lang->def('_YES') => ORG_CHART_FIELD_NORMAL,
            $this->lang->def('_ORG_CHART_FIELD_DESCEND') => ORG_CHART_FIELD_DESCEND,
        ];
        $tree .= $form->getHidden($this->id . '_idst_group',
            $this->id . '[idst_group]',
            $idst_group);
        $tree .= $form->getHidden($this->id . '_idst_desc',
            $this->id . '[idst_desc]',
            $idst_desc);
        foreach ($arr_all_fields as $field) {
            $def_value = ORG_CHART_FIELD_NO;
            if (isset($arr_fields_inherit[$field[FIELD_INFO_ID]])) {
                $def_value = ORG_CHART_FIELD_INHERIT;
            } elseif (isset($arr_fields_normal[$field[FIELD_INFO_ID]])) {
                $def_value = ORG_CHART_FIELD_NORMAL;
            } elseif (isset($arr_fields_descend[$field[FIELD_INFO_ID]])) {
                $def_value = ORG_CHART_FIELD_DESCEND;
            }

            $tree .= $form->openFormLine();
            $tree .= '<div class="label_effect">' . $field[FIELD_INFO_TRANSLATION] . '</div>';
            foreach ($arr_values as $label => $value) {
                $tree .= '<input class="radio" type="radio"'
                    . ' id="' . $this->id . '_' . $field[FIELD_INFO_ID] . '_' . $value . '"'
                    . ' name="' . $this->id . '[field_set][' . $field[FIELD_INFO_ID] . ']"'
                    . ' value="' . $value . '"'
                    . (($value == $def_value) ? ' checked="checked"' : '')
                    . (($value == ORG_CHART_FIELD_NO and $def_value == ORG_CHART_FIELD_INHERIT) ? ' disabled="true"' : '')
                    . ' />';
                $tree .= $form->getLabel($this->id . '_' . $field[FIELD_INFO_ID] . '_' . $value, $label, 'label_bold');
            }
            if ($def_value == ORG_CHART_FIELD_INHERIT) {
                $gid = $arr_fields_inherit[$field[FIELD_INFO_ID]][FIELD_INFO_GROUPID];
                if ($gid == '/ocd_0') {
                    $text = FormaLms\lib\Get::sett('title_organigram_chart');
                } else {
                    if (isset($arr_tree_translations[$gid])) {
                        $text = $arr_tree_translations[$gid];
                    } else {
                        //$text = $arr_groupid[$gid];
                        $text = $gid;
                    }
                }
                $tree .= '<span class="label_bold">'
                    . $this->lang->def('_ORG_CHART_FIELD_INHERIT')
                    . ' [' . $text . ']'
                    . '</span>';
            }
            //$tree .= $form->getLabel( $id, $field[FIELD_INFO_TRANSLATION] );
            $tree .= $form->closeFormLine();
        }

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('next1_assignfield' . $this->id, $this->id . '[next1_assignfield]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadAssignField2()
    {
        require_once _base_ . '/lib/lib.table.php';
        $arr_fields = $_POST[$this->id]['field_set'];
        $idst_group = $_POST[$this->id]['idst_group'];
        $idst_desc = $_POST[$this->id]['idst_desc'];
        require_once _adm_ . '/lib/lib.field.php';
        $fl = new FieldList();
        $arr_all_fields = $fl->getAllFields();
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $tree = $form->openElementSpace();
        $tree .= $this->printState();
        // print custom fields status
        $arr_fields_normal = $fl->getFieldsFromIdst([$idst_group]);
        $arr_fields_descend = $fl->getFieldsFromIdst([$idst_desc]);
        foreach ($arr_fields_descend as $id_field => $field) {
            $arr_fields_normal[$id_field] = $field;
        }

        $tree .= $form->getHidden($this->id . '_idst_group',
            $this->id . '[idst_group]',
            $idst_group);
        $tree .= $form->getHidden($this->id . '_idst_desc',
            $this->id . '[idst_desc]',
            $idst_desc);

        foreach ($arr_fields as $id_filed => $status) {
            $tree .= $form->getHidden($this->id . '_' . $id_filed,
                $this->id . '[field_set][' . $id_filed . ']',
                $status);
        }
        /*
        $tree .= $form->openFormLine();
        $tree .= '<div class="label_effect">&nbsp;</div>';
        $tree .= '<div class="label_head">'.$this->lang->def('_MANDATORY').'</div>';
        $tree .= '<div class="label_head">'.$this->lang->def('_ORG_CHART_FIELD_WRITE').'</div>';
        $tree .= $form->closeFormLine();
        */
        $tb = new Table(0,
            $this->lang->def('_TITLE'),
            $this->lang->def('_TITLE'));
        $tb->setTableStyle('tree_org_table_field');
        $tb->addHeadCustom('<tr class="first_intest">'
            . '<th scope="col" abbr="' . $this->lang->def('_NAME') . '">'
            . $this->lang->def('_FIELD_NAME') . '</th>'
            . '<th scope="col" abbr="' . $this->lang->def('_MANDATORY') . '">'
            . $this->lang->def('_MANDATORY') . '</th>'
            . '<th scope="col" abbr="' . $this->lang->def('_ORG_CHART_FIELD_WRITE_ABBR') . '">'
            . $this->lang->def('_ORG_CHART_FIELD_WRITE') . '</th>'
            . '</tr>');

        // checkbox for mandatory and useraccess
        foreach ($arr_fields as $id_filed => $status) {
            if ($status == ORG_CHART_FIELD_NORMAL || $status == ORG_CHART_FIELD_DESCEND) {
                /*$tree .= $form->openFormLine();
                // field title
                $tree .= '<div class="label_effect">'.$arr_all_fields[$id_filed][FIELD_INFO_TRANSLATION].'</div>';
                // checkbox for mandatory
                $tree .= '<input class="checkbox" type="checkbox"'
                            .' id="'.$this->id.'_'.$id_filed.'_mandatory"'
                            .' name="'.$this->id.'[field_mandatory]['.$id_filed.']"'
                            .' value="true"';
                if( isset( $arr_fields_normal[$id_filed] ) && $arr_fields_normal[$id_filed][FIELD_INFO_MANDATORY] == 'true' )
                    $tree .= ' checked="checked"';
                $tree .= ' />';
                $tree .= $form->getLabel( $this->id.'_'.$id_filed.'_mandatory', $this->lang->def('_MANDATORY'), 'label_bold access-only' );
                // checkbox for useraccess
                $tree .= '<input class="checkbox" type="checkbox"'
                            .' id="'.$this->id.'_'.$id_filed.'_useraccess"'
                            .' name="'.$this->id.'[field_useraccess]['.$id_filed.']"'
                            .' value="readwrite"';
                if( isset( $arr_fields_normal[$id_filed] ) && $arr_fields_normal[$id_filed][FIELD_INFO_USERACCESS] == 'readwrite' )
                    $tree .= ' checked="checked"';
                $tree .= ' />';
                $tree .= $form->getLabel( $this->id.'_'.$id_filed.'_useraccess', $this->lang->def('_ORG_CHART_FIELD_WRITE'), 'label_bold access-only' );
                $tree .= $form->closeFormLine();*/

                $input_manadatory = '<input class="checkbox" type="checkbox"'
                    . ' id="' . $this->id . '_' . $id_filed . '_mandatory"'
                    . ' name="' . $this->id . '[field_mandatory][' . $id_filed . ']"'
                    . ' value="true"';
                if (isset($arr_fields_normal[$id_filed]) && $arr_fields_normal[$id_filed][FIELD_INFO_MANDATORY] == 'true') {
                    $input_manadatory .= ' checked="checked"';
                }
                $input_manadatory .= ' />'
                    . $form->getLabel($this->id . '_' . $id_filed . '_mandatory', $this->lang->def('_MANDATORY'), 'label_bold access-only');

                $input_useraccess = '<input class="checkbox" type="checkbox"'
                    . ' id="' . $this->id . '_' . $id_filed . '_useraccess"'
                    . ' name="' . $this->id . '[field_useraccess][' . $id_filed . ']"'
                    . ' value="readwrite"';
                if (isset($arr_fields_normal[$id_filed]) && $arr_fields_normal[$id_filed][FIELD_INFO_USERACCESS] == 'readwrite') {
                    $input_useraccess .= ' checked="checked"';
                }
                $input_useraccess .= ' />'
                    . $form->getLabel($this->id . '_' . $id_filed . '_useraccess', $this->lang->def('_ORG_CHART_FIELD_WRITE'), 'label_bold access-only');

                $tb->addHeadCustom('<tr>'
                    . '<th scope="row">' . $arr_all_fields[$id_filed][FIELD_INFO_TRANSLATION] . '</th>'
                    . '<td>' . $input_manadatory . '</td>'
                    . '<td>' . $input_useraccess . '</td>'
                    . '</tr>');
            }
        }
        $tree .= $tb->getTable();

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('save_assignfield' . $this->id, $this->id . '[save_assignfield]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadAssignField3()
    {
        $arr_fields = $_POST[$this->id]['field_set'];
        $idst_group = $_POST[$this->id]['idst_group'];
        require_once _adm_ . '/lib/lib.field.php';
        $fl = new FieldList();
        $arr_all_fields = $fl->getAllFields();
        $arr_fields_normal = $fl->getFieldsFromIdst([$idst_group]);
        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();
        $tree = $form->getFormHeader($this->lang->def('_ORG_CHART_LIST_FIELDS'));
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        $tree .= $form->getHidden($this->id . '_idst_group',
            $this->id . '[idst_group]',
            $idst_group);
        // print custom fields status
        foreach ($arr_fields as $id_filed => $status) {
            $tree .= $form->getHidden($this->id . '_' . $id_filed,
                $this->id . '[field_set][' . $id_filed . ']',
                $status);
        }

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('next1_assignfield' . $this->id, $this->id . '[save_assignfield]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadImportUsers()
    {
        $tdb = &$this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());

        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();

        $tree = $form->getFormHeader($this->lang->def('_ORG_CHART_IMPORT_USERS'));
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        $tree .= $form->getFilefield($this->lang->def('_ORG_CHART_IMPORT_FILE'), 'file_import', 'file_import');
        $tree .= $form->getTextfield($this->lang->def('_ORG_CHART_IMPORT_SEPARATOR'), 'import_separator', 'import_separator', 1, ',');
        $tree .= $form->getCheckbox($this->lang->def('_ORG_CHART_IMPORT_HEADER'), 'import_first_row_header', 'import_first_row_header', 'true', true);
        $tree .= $form->getTextfield($this->lang->def('_ORG_CHART_IMPORT_CHARSET'), 'import_charset', 'import_charset', 20, 'ISO-8859-1');

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('next1_importusers' . $this->id, $this->id . '[next1_importusers]', $this->lang->def('_NEXT'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadImportUsers2()
    {
        require_once _base_ . '/lib/lib.upload.php';
        $tdb = &$this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());
        $back_url = 'index.php?modname=directory&op=org_chart';

        // ----------- file upload -----------------------------------------
        if ($_FILES['file_import']['name'] == '') {
            $this->session->getFlashBag()->add('error', Lang::t('_FILEUNSPECIFIED'));
            Util::jump_to($back_url . '&import_result=-1');
        } else {
            $path = '/appCore/';
            $savefile = mt_rand(0, 100) . '_' . time() . '_' . $_FILES['file_import']['name'];
            if (!file_exists(_files_ . $path . $savefile)) {
                sl_open_fileoperations();
                if (!sl_upload($_FILES['file_import']['tmp_name'], $path . $savefile)) {
                    sl_close_fileoperations();

                    $this->session->getFlashBag()->add('error', Lang::t('_ERROR_UPLOAD'));
                    Util::jump_to($back_url . '&import_result=-1');
                }
                sl_close_fileoperations();
            } else {
                $this->session->getFlashBag()->add('error', Lang::t('_ERROR_UPLOAD'));

                Util::jump_to($back_url . '&create_result=-1');
            }
        }

        require_once _base_ . '/lib/lib.form.php';
        $form = new Form();

        $tree = $form->getFormHeader($this->lang->def('_ORG_CHART_IMPORT_USERS'));
        $tree .= $form->openElementSpace();
        $tree .= $this->printState();

        require_once __DIR__ . '/import.org_chart.php';
        $separator = isset($_POST['import_separator']) ? $_POST['import_separator'] : ',';
        $first_row_header = isset($_POST['import_first_row_header']) ? ($_POST['import_first_row_header'] == 'true') : false;
        $import_charset = isset($_POST['import_charset']) ? $_POST['import_charset'] : 'UTF-8';
        if (trim($import_charset) === '') {
            $import_charset = 'UTF-8';
        }

        $src = new DeceboImport_SourceCSV(['filename' => _files_ . $path . $savefile,
                'separator' => $separator,
                'first_row_header' => $first_row_header,
                'import_charset' => $import_charset,
            ]
        );
        $dst = new ImportUser(['dbconn' => $GLOBALS['dbConn'],
            'tree' => &$this, ]);
        $src->connect();
        $dst->connect();
        $importer = new FormaImport();
        $importer->setSource($src);
        $importer->setDestination($dst);

        $tree .= $importer->getUIMap();
        $tree .= $form->getHidden($this->id . '_filename', $this->id . '[filename]', _files_ . $path . $savefile);
        $tree .= $form->getHidden('import_first_row_header', 'import_first_row_header', ($first_row_header ? 'true' : 'false'));
        $tree .= $form->getHidden('import_separator', 'import_separator', $separator);
        $tree .= $form->getHidden('import_charset', 'import_charset', $import_charset);

        $tree .= $form->closeElementSpace()
            . $form->openButtonSpace()
            . $form->getButton('next2_importusers' . $this->id, $this->id . '[next2_importusers]', $this->lang->def('_SAVE'))
            . $form->getButton($this->_getCancelId(), $this->_getCancelId(), $this->lang->def('_UNDO'))
            . $form->closeButtonSpace();

        return $tree;
    }

    public function loadImportUsers3()
    {
        $back_url = 'index.php?modname=directory&op=org_chart';
        $tdb = &$this->tdb;
        $folder = $tdb->getFolderById($this->getSelectedFolderId());
        $back_url = 'index.php?modname=directory&op=org_chart';
        $filename = $_POST[$this->id]['filename'];
        $separator = isset($_POST['import_separator']) ? $_POST['import_separator'] : ',';
        $first_row_header = isset($_POST['import_first_row_header']) ? ($_POST['import_first_row_header'] == 'true') : false;
        $import_charset = isset($_POST['import_charset']) ? $_POST['import_charset'] : 'UTF-8';
        if (trim($import_charset) === '') {
            $import_charset = 'UTF-8';
        }

        require_once __DIR__ . '/import.org_chart.php';
        $src = new DeceboImport_SourceCSV(['filename' => $filename,
            'separator' => $separator,
            'first_row_header' => $first_row_header,
            'import_charset' => $import_charset,
        ]);
        $dst = new ImportUser(['dbconn' => $GLOBALS['dbConn'],
            'tree' => &$this, ]);
        $src->connect();
        $dst->connect();
        $importer = new FormaImport();
        $importer->setSource($src);
        $importer->setDestination($dst);

        $importer->parseMap();
        $result = $importer->doImport();

        $src->close();
        $dst->close();

        // print total processed rows
        $tree = '';
        $tree .= getBackUi($back_url, $this->lang->def('_BACK'));
        $tree .= getResultUi(str_replace('%count%', $result[0], $this->lang->def('_OPERATION_SUCCESSFUL')));

        if (count($result) > 1) {
            require_once _base_ . '/lib/lib.table.php';
            $tree .= str_replace('%count%', count($result) - 1, $this->lang->def('_OPERATION_FAILURE'));
            $table = new Table(FormaLms\lib\Get::sett('visuItem'), $this->lang->def('_OPERATION_FAILURE'), $this->lang->def('_OPERATION_FAILURE'));
            $table->setColsStyle(['', '']);
            $table->addHead([$this->lang->def('_OPERATION_FAILURE'),
                $this->lang->def('_OPERATION_FAILURE'),
            ]);

            foreach ($result as $key => $err_val) {
                if ($key != 0) {
                    $table->addBody([$key, $err_val]);
                }
            }
            $tree .= $table->getTable();
        }
        $tree .= getBackUi($back_url, $this->lang->def('_BACK'));

        return $tree;
    }
}
