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

require_once _base_ . '/lib/lib.treedb.php';
require_once _base_ . '/lib/lib.treeview.php';

define('REPOFIELDTITLE', 0);
define('REPOFIELDOBJECTTYPE', 1);
define('REPOFIELDIDRESOURCE', 2);
define('REPOFIELDIDCATEGORY', 3);
define('REPOFIELDIDUSER', 4);
define('REPOFIELDIDAUTHOR', 5);
define('REPOFIELDVERSION', 6);
define('REPOFIELDDIFFICULT', 7);
define('REPOFIELDDESCRIPTION', 8);
define('REPOFIELDLANGUAGE', 9);
define('REPOFIELDRESOURCE', 10);
define('REPOFIELDOBJECTIVE', 11);
define('REPOFIELDDATEINSERT', 12);

define('REPOOPTSHOWONLYFOLDER', 'REPOOPTSHOWONLYFOLDER');

define('REPO_ID_PRINTEDITEM', 'REPO_ID_PRINTEDITEM');
define('REPO_OP_SELECTITEM', 'REPO_OP_SELECTITEM');
define('REPO_OP_SELECTMONO', 'REPO_OP_SELECTMONO');
define('REPO_OP_SELECTRADIO', 'REPO_OP_SELECTRADIO');
define('REPO_ID_SELECTIONSTATE', 'REPO_ID_SELECTIONSTATE');

// repository customization of TreeDb class
class RepoDirDb extends TreeDb
{
    // these 3 variales are set in overloaded addFolderById
    // before call to parent addFolderById.
    // Then these values are used in _getOtherValues overload
    public $org_title;
    public $org_obejctType;
    public $org_idResource;
    public $org_idCategory;
    public $org_idUser;
    public $org_idAuthor;
    public $org_version;
    public $org_difficult;
    public $org_description;
    public $org_language;
    public $org_resource;
    public $org_objective;
    public $org_dateInsert;

    // to filter on types
    public $filterTypes = null;
    // filder on access
    public $filterAccess = false;

    // Constructor of RepoDirDb class
    public function __construct($table_name)
    {
        $this->table = $table_name;
        $this->fields = ['id' => 'idRepo', 'idParent' => 'idParent', 'path' => 'path', 'lev' => 'lev'];
    }

    // , '.$prefix.'_repo_access';
    public function setFilterTypes($lotypes)
    {
        $this->filterTypes = $lotypes;
    }
    /*
        TODO: ACL
    function setFilterVisibility( $fv = TRUE ) {
        $this->filterVisibility = $fv;
    }

    function setFilterAccess( $idUser = FALSE ) {
        $this->filterAccess = $idUser;
    }
    */

    // Repository are stored in a table with the structure requested by
    // TreeDb to manage tree. In addition the table contains
    // title, objectType, idResource
    public function _getOtherFields($tname = false)
    {
        if ($tname === false) {
            return ', title, objectType, idResource, idCategory, idUser, '
                . 'idAuthor, version, difficult, description, language, '
                . 'resource, objective, dateInsert';
        } else {
            return ', ' . $tname . '.title,' . $tname . '.objectType,'
                . $tname . '.idResource, ' . $tname . '.idCategory, '
                . $tname . '.idUser, ' . $tname . '.idAuthor, ' . $tname . '.version, '
                . $tname . '.difficult, ' . $tname . '.description, '
                . $tname . '.language, ' . $tname . '.resource, '
                . $tname . '.objective, ' . $tname . '.dateInsert ';
        }
    }

    public function _getOtherValues()
    {
        return ", '" . addslashes($this->org_title) . "', '"
            . addslashes($this->org_objectType) . "', '"
            . (int) $this->org_idResource . "', '"
            . (int) $this->org_idCategory . "', '"
            . (int) $this->org_idUser . "', '"
            . (int) $this->org_idAuthor . "', '"
            . addslashes($this->org_version) . "', '"
            . addslashes($this->org_difficult) . "', '"
            . addslashes($this->org_description) . "', '"
            . addslashes($this->org_language) . "', '"
            . addslashes($this->org_resource) . "', '"
            . addslashes($this->org_objective) . "', '"
            . addslashes($this->org_dateInsert) . "' ";
    }

    public function _getOtherUpdates()
    {
        return " title='" . addslashes($this->org_title) . "',"
            . " objectType='" . addslashes($this->org_objectType) . "',"
            . " idResource='" . (int) $this->org_idResource . "',"
            . " idCategory='" . (int) $this->org_idCategory . "',"
            . " idUser='" . (int) $this->org_idUser . "',"
            . " idAuthor='" . (int) $this->org_idAuthor . "',"
            . " version='" . addslashes($this->org_version) . "',"
            . " difficult='" . addslashes($this->org_difficult) . "',"
            . " description='" . addslashes($this->org_description) . "',"
            . " language='" . addslashes($this->org_language) . "',"
            . " resource='" . addslashes($this->org_resource) . "',"
            . " objective='" . addslashes($this->org_objective) . "',"
            . " dateInsert='" . addslashes($this->org_dateInsert) . "' ";
    }
    /* TODO: ACL
    function _getOtherTables($tname = FALSE) {
        if( $this->filterAccess !== FALSE ) {
            global $prefix;
            if( $tname === FALSE )
                return   ' LEFT JOIN '.$prefix.'_organization_access'
                        .' ON ( '.$prefix.'_organization.idOrg = '.$prefix.'_organization_access.idOrgAccess )'
                        .' LEFT JOIN '.$prefix.'_coursegroupuser'
                        .' ON ('.$prefix."_organization_access.kind = 'group'"
                        .'     AND '.$prefix.'_organization_access.value = '.$prefix.'_coursegroupuser.idGroup )';
            else
                return   ' LEFT JOIN '.$prefix.'_organization_access'
                        .' ON ( '.$tname.'.idOrg = '.$prefix.'_organization_access.idOrgAccess )'
                        .' LEFT JOIN '.$prefix.'_coursegroupuser'
                        .' ON ('.$prefix."_organization_access.kind = 'group'"
                        .'     AND '.$prefix.'_organization_access.value = '.$prefix.'_coursegroupuser.idGroup )';
        } else
            return "";
    }


    function _getJoinFilter($tname = FALSE) {
        return FALSE;
        if( $this->filterAccess !== FALSE ) {
            global $prefix;
            return $tname.'.idOrg = '.$prefix.'_organization_access.idOrgAccess';
        } else
            return FALSE;
    }
    */

    // overload.
    // filter organization on idCourse
    // filterTypes if they are.
    // visibility in set filterVisibility
    public function _getFilter($tname = false)
    {
        $result = '';
        if ($tname === false) {
            if ($this->filterTypes !== null) {
                $result .= " AND (objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
            }
        } else {
            if ($this->filterTypes !== null) {
                $result .= ' AND (' . $tname . ".objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
            }
        }
        /* TODO: ACL
        if( $this->filterAccess !== FALSE )
            $result .= " AND ( (".$prefix."_organization_access.kind = 'group'"
                         ." 	AND ".$prefix."_coursegroupuser.idUser = '".(int)$this->filterAccess."')"
                         ."  OR (".$prefix."_organization_access.kind = 'user'"
                         ." 	AND ".$prefix."_organization_access.value = '".(int)$this->filterAccess."')"
                      ."     OR ".$prefix."_organization_access.idOrgAccess IS NULL"
                      .")";
        */
        return $result;
    }

    public function getMaxChildPos($idFolder)
    {
        $query = "SELECT MAX(SUBSTRING_INDEX(path, '/', -1))"
            . ' FROM ' . $this->table
            . ' WHERE (' . $this->fields['idParent'] . " = '" . (int) $idFolder . "')"
            . $this->_getFilter();
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
        echo parent::renameFolder($folder, $folderName2 . 'tmp');
        echo parent::renameFolder($folder2, $folderName);
        echo parent::renameFolder($folder, $folderName2);
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
        echo parent::renameFolder($folder, $folderName2 . 'tmp');
        echo parent::renameFolder($folder2, $folderName);
        echo parent::renameFolder($folder, $folderName2);
    }

    public function addFolderById($idParent, $folderName)
    {
        $this->org_title = $folderName;
        $this->org_objectType = '';
        $this->org_idResource = 0;

        $this->org_idCategory = 0;
        $this->org_idUser = 0;
        $this->org_idAuthor = 0;
        $this->org_version = '';
        $this->org_difficult = null;
        $this->org_description = '';
        $this->org_language = '';
        $this->org_resource = '';
        $this->org_objective = '';
        $this->org_dateInsert = '';
        echo parent::addFolderById($idParent, $folderName);
    }

    /** Add a new item to tree db.
     * @param int    $idParent    the id of the container folder
     * @param string $title       title of the item
     * @param string $objectType  type of the lo item
     * @param int    $idResource  id of the resource
     * @param int    $idCategory  id of the category
     * @param int    $idUser      owner's id of the item
     * @param int    $idAuthor    author's id of the content
     * @param string $version     version of the item
     * @param string $difficult   the level of difficult of the item
     * @param string $description description of the item
     * @param string $language    language of the item
     * @param string $resource    web address from ....
     * @param string $objective   the item's objective
     * @param string $dateInsert  the insert date
     * @param array  $otherData   other parameters for repo extensions
     **/
    public function addItem($idParent, $title, $objectType, $idResource, $idCategory,
                            $idUser, $idAuthor, $version, $difficult, $description,
                            $language, $resource, $objective, $dateInsert,
                            $otherData = null)
    {
        $this->org_title = $title;
        $this->org_objectType = $objectType;
        $this->org_idResource = $idResource;
        $this->org_idCategory = $idCategory;
        $this->org_idUser = $idUser;
        $this->org_idAuthor = $idAuthor;
        $this->org_version = $version;
        $this->org_difficult = $difficult;
        $this->org_description = $description;
        $this->org_language = $language;
        $this->org_resource = $resource;
        $this->org_objective = $objective;
        $this->org_dateInsert = $dateInsert;
        $idReference = parent::addFolderById($idParent, addslashes($title));

        return $idReference;
    }

    /** change normal behavior.
     *  NOTE: In repo rename change title field not path.
     **/
    /* NOTE: Change folder name like FS
    function renameFolder( &$folder, $newName ) {
       $this->org_title = $newName;
       $this->org_idObject = $folder->otherValues[ORGFIELDIDOBJECT];
       $this->org_idCourse = $folder->otherValues[ORGFIELDIDCOURSE];
       $this->org_objectType = $folder->otherValues[ORGFIELDOBJECTTYPE];
       $this->org_prerequisites = $folder->otherValues[ORGFIELDPREREQUISITES];
       $this->org_isTerminator = $folder->otherValues[ORGFIELDISTERMINATOR];
       $this->org_idResource = $folder->otherValues[ORGFIELDIDRESOURCE];
       $this->org_idParam = $folder->otherValues[ORGFIELDIDPARAM];
       $this->org_visible = $folder->otherValues[ORGFIELDVISIBLE];
       $this->changeOtherData( $folder );
    }*/

    /* NOTE: in fs like not needed
    // overload to modify folder internal name to avoid conflicts
    // and send it to the end of parent
    function moveFolder( &$folder, &$parentFolder ) {
        // change folder name
        $folder->path = $this->getNewPos( $parentFolder->id );
        parent::moveFolder( $folder, $parentFolder );
    }*/

    public function modifyItem($arrData)
    {
        $folder = $this->getFolderById($arrData['idItem']);
        $this->org_title = $arrData['title'];
        $this->org_objectType = $folder->otherValues[REPOFIELDOBJECTTYPE];
        $this->org_idResource = $folder->otherValues[REPOFIELDIDRESOURCE];
        //$this->org_idCategory = $arrData['idCategory'];
        $this->org_idUser = $folder->otherValues[REPOFIELDIDUSER];
        $this->org_idAuthor = $folder->otherValues[REPOFIELDIDAUTHOR];
        $this->org_version = $arrData['version'];
        $this->org_difficult = $arrData['difficult'];
        //$this->org_description = $arrData['description'];
        $this->org_language = $arrData['language'];
        $this->org_resource = $arrData['resource'];
        $this->org_objective = $arrData['objective'];
        $this->org_dateInsert = $folder->otherValues[REPOFIELDDATEINSERT];

        echo 'lang: ' . $this->org_language . '<br />';

        $this->changeOtherData($folder);

        /* TODO: ACL
        if( $arrData['accessGroups'] == '' )
            $arrGroups = array();
        else
            $arrGroups = Util::unserialize(urldecode($arrData['accessGroups']));
        if( $arrData['accessUsers'] == '' )
            $arrUsers = array();
        else
            $arrUsers = Util::unserialize(urldecode($arrData['accessUsers']));
        $this->setAccess( $arrData['idItem'], $arrGroups, $arrUsers );
            */
        /* TODO: LO management in repo
        if( $this->org_objectType != '' ) {
            // ---- custom LO parameters
            $lo = createLO(	$this->org_objectType,
                            $this->org_idResource,
                            $this->org_idParam,
                            array() );
            $arrParamsInfo = $lo->getParamInfo();
            if( $arrParamsInfo !== FALSE ) {
                require_once( 'core/manParam.php' );
                while( $param = current($arrParamsInfo) ) {
                    if( isset( $arrData[$param['param_name']] ) )
                        setLOParam( $this->org_idParam, $param['param_name'], $arrData[$param['param_name']] );
                    next( $arrParamsInfo );
                }
            }
        }
        */
    }

    /**
     * function deleteAllTree()
     *    Delete all items in tree, all folders, all records!
     **/
    public function deleteAllTree()
    {
        // loop on all items
        $nullVal = null;
        $coll = $this->getFoldersCollection($nullVal);
        while ($folder = $coll->getNext()) {
            if ($folder->otherValues[REPOFIELDIDRESOURCE] != 0) {
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                // delete categorized resource
                require_once _lms_ . '/lib/lib.kbres.php';
                $kbres = new KbRes();
                $kbres->deleteResourceFromItem(
                    $folder->otherValues[REPOFIELDIDRESOURCE],
                    $folder->otherValues[REPOFIELDOBJECTTYPE],
                    'course_lo'
                );
                // ---------------------------
                $ok = $lo->del($folder->otherValues[REPOFIELDIDRESOURCE]);
            }
        }
        // remove all records from repo
        parent::deleteAllTree();
    }

    public function reorder($idToMove, $newParent, $newOrder = [])
    {
        $folderToMove = $this->getFolderById($idToMove);
        $parent = $this->getFolderById($newParent);
        $folderToMove->move($parent);

        if (
            count($newOrder) > 0
        ) {
            foreach ($newOrder as $index => $id) {
                $folder = $this->getFolderById($id);
                $folderName = substr('00000000' . ($index + 1), -8);
                parent::renameFolder($folder, $folderName);
            }
        }

        return true;
    }

    public function renameFolder(&$folder, $newName)
    {
        $this->org_title = $newName;
        $this->org_objectType = $folder->otherValues[REPOFIELDOBJECTTYPE];
        $this->org_idResource = $folder->otherValues[REPOFIELDIDRESOURCE];
        $this->org_idCategory = $folder->otherValues[REPOFIELDIDCATEGORY];
        $this->org_idUser = $folder->otherValues[REPOFIELDIDUSER];
        $this->org_idAuthor = $folder->otherValues[REPOFIELDIDAUTHOR];
        $this->org_version = $folder->otherValues[REPOFIELDVERSION];
        $this->org_difficult = $folder->otherValues[REPOFIELDDIFFICULT];
        $this->org_description = $folder->otherValues[REPOFIELDDESCRIPTION];
        $this->org_language = $folder->otherValues[REPOFIELDLANGUAGE];
        $this->org_resource = $folder->otherValues[REPOFIELDRESOURCE];
        $this->org_objective = $folder->otherValues[REPOFIELDOBJECTIVE];
        $this->org_dateInsert = $folder->otherValues[REPOFIELDDATEINSERT];

        $this->org_idCourse = $folder->otherValues[ORGFIELDIDCOURSE];
        $this->org_prerequisites = $folder->otherValues[ORGFIELDPREREQUISITES];
        $this->org_isTerminator = $folder->otherValues[ORGFIELDISTERMINATOR];
        $this->org_idParam = $folder->otherValues[ORGFIELDIDPARAM];
        $this->org_visible = $folder->otherValues[ORGFIELDVISIBLE];
        $this->org_milestone = $folder->otherValues[ORGFIELDMILESTONE];

        $this->org_width = $folder->otherValues[ORGFIELD_WIDTH];
        $this->org_height = $folder->otherValues[ORGFIELD_HEIGHT];
        $this->org_publish_from = $folder->otherValues[ORGFIELD_PUBLISHFROM];
        $this->org_publish_to = $folder->otherValues[ORGFIELD_PUBLISHTO];
        $this->org_access = $folder->otherValues[ORGFIELD_ACCESS];
        $this->org_publish_for = $folder->otherValues[ORGFIELD_PUBLISHFOR];

        return $this->changeOtherData($folder);
    }
}

class RepoTreeView extends TreeView
{
    public $options = [];
    public $creatingObjectType = '';
    public $withActions = false;
    public $pathToExpand = null;

    public $kind = '';
    public $opContextId = 0;
    public $mod_name = '';

    public $selector_mode = false;
    public $simple_selector = false;
    public $itemSelectedMulti = [];
    public $filter_nodes = false;
    public $printed_items = [];

    public $multi_choice = true;

    public function _getOpPropertiesId()
    {
        return $this->id . '_properties_';
    }

    public function _getOpCreateLO()
    {
        return $this->id . '_createLOBegin';
    }

    public function _getOpCreateLOSel()
    {
        return $this->id . '_createLOSel';
    }

    public function _getOpCreateLOEnd()
    {
        return $this->id . '_createLOEnd';
    }

    public function _getOpEditLO()
    {
        return $this->id . '_editLOBegin';
    }

    public function _getOpEditLOEnd()
    {
        return $this->id . '_editLOEnd';
    }

    public function _getOpEditLOId()
    {
        return $this->id . '_editLOBegin';
    }

    public function _getOpCopyLO()
    {
        return $this->id . '_copyLOBegin';
    }

    public function _getOpCopyLOSel()
    {
        return 'copyLOSel';
    }

    public function _getOpCopyLOEndOk()
    {
        return $this->id . '_copyLOEndOk';
    }

    public function _getOpCopyLOEndCancel()
    {
        return $this->id . '_copyLOEndCancel';
    }

    public function _getOpCopyLOId()
    {
        return $this->id . '_copyLOBegin';
    }

    public function _getOpPlayEnd()
    {
        return $this->id . '_playEnd';
    }

    public function _getSaveImage()
    {
        return getPathImage() . 'treeview/save.gif';
    }

    public function _getOpPropertiesImg()
    {
        return getPathImage() . 'standard/configure.gif';
    }

    public function _getAddImage()
    {
        return getPathImage() . 'standard/folder_new.png';
    }

    public function _getAddLabel()
    {
        return Lang::t('_NEW_FOLDER', 'storage');
    }

    public function _getAddAlt()
    {
        return Lang::t('_NEW_FOLDER', 'storage');
    }

    public function _getFolderNameLabel()
    {
        return Lang::t('_NAME', 'storage');
    }

    public function _getCreateLabel()
    {
        return Lang::t('_NEW_FOLDER', 'storage');
    }

    public function _getCreateAlt()
    {
        return Lang::t('_NEW_FOLDER', 'storage');
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'standard/folder_new.png';
    }

    public function _getCreateLOLabel()
    {
        return Lang::t('_REPOCREATELO', 'storage');
    }

    public function _getCreateLOAlt()
    {
        return Lang::t('_REPOCREATELO', 'storage');
    }

    public function _getCreateLOImage()
    {
        return getPathImage() . 'standard/add.png';
    }

    public function _getOpPlayTitle()
    {
        return Lang::t('_PLAY', 'storage');
    }

    public function _getOpEditTitle()
    {
        return Lang::t('_MOD', 'storage');
    }

    public function _getOpCopyTitle()
    {
        return Lang::t('_REPOCOPYLO', 'storage');
    }

    public function _getCopyImage()
    {
        return getPathImage() . 'standard/dup.png';
    }

    public function _getEditImage()
    {
        return getPathImage() . 'standard/edit.png';
    }

    /* NOTE: Not for fs like
    function _getOpUpTitle() { return _REPOUPTITLE; }
    function _getOpDownTitle() { return _REPODOWNTITLE; }
    */
    public function _getOpPropertiesTitle()
    {
        return Lang::t('_PROPERTIES');
    }

    public function canRename()
    {
        return false;
    }

    public function canAdd()
    {
        if ($this->isFolderSelected()) {
            $stackData = $this->getSelectedFolderData();
            $arrData = $stackData['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
            if (!$isFolder) {
                return [];
            }
        }

        return true;
    }

    public function canDelete()
    {
        return false;
        if (!$this->isFolderSelected()) {
            return false;
        }
        $data = $this->getSelectedFolderData();
        if ($data['isLeaf'] === false) {
            return false;
        }

        return true;
    }

    public function canMove()
    {
        return false;
    }

    public function canInlineMove()
    {
        return $this->withActions;
    }

    public function canInlineRename()
    {
        return false;
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
        return false;
    }

    public function canInlineDeleteItem(&$stack, $level)
    {
        if ($level == 0) {
            return false;
        }
        if ($stack[$level]['isLeaf'] === false) {
            return false;
        } else {
            return true;
        }
    }

    public function _getOtherActions()
    {
        if ($this->isFolderSelected()) {
            $stackData = $this->getSelectedFolderData();
            $arrData = $stackData['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
            if (!$isFolder) {
                return [];
            }
            /* array(	array($this->_getOpEditLO(), Lang::t('_MOD', 'storage', 'lms'), getPathImage().'standard/edit.png' ),
                            array($this->_getOpCopyLO(), Lang::t('_REPOCOPYLO', 'storage', 'lms'), getPathImage().'standard/dup.png' )
                        );*/
        }

        return [[$this->_getOpCreateLO(), Lang::t('_REPOCREATELO', 'storage'), getPathImage() . 'standard/add.png']];
    }

    public function getFolderPrintName(&$folder)
    {
        return parent::getFolderPrintName($folder);
    }

    public function expandPath($path)
    {
        $arrId = [];
        $splitPath = explode('/', $path);
        unset($splitPath[0]);
        $path = '';
        foreach ($splitPath as $tok) {
            $path .= '/' . $tok;

            $folder = $this->tdb->getFolderByPath($path);
            $arrId[] = $folder->id;
        }
        $this->pathToExpand = array_flip($arrId);
    }

    public function setOption($option_name, $option_value)
    {
        $this->options[$option_name] = $option_value;
    }

    public function getOption($option_name)
    {
        if (isset($this->options[$option_name])) {
            return $this->options[$option_name];
        } else {
            return null;
        }
    }

    public function beforeDeleteItem(&$folder)
    {
        if ($folder->otherValues[REPOFIELDOBJECTTYPE] != '') {
            if ($folder->otherValues[REPOFIELDIDRESOURCE] != 0) {
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                // delete categorized resource
                require_once _lms_ . '/lib/lib.kbres.php';
                $kbres = new KbRes();
                $kbres->deleteResourceFromItem(
                    $folder->otherValues[REPOFIELDIDRESOURCE],
                    $folder->otherValues[REPOFIELDOBJECTTYPE],
                    'course_lo'
                );
                // ---------------------------
                $ret = $lo->del($folder->otherValues[REPOFIELDIDRESOURCE]);

                return $ret;
            } else {
                return true;
            }
        }

        return parent::beforeDeleteItem($folder);
    }

    public function refreshTree()
    {
        $this->refresh = true;
        $this->posFlat = [];
        $this->_visitArrayDeep($this->posTree, $this->posFlat, $this->expandList, $this->compressList);
    }

    public function selection_parseInput($arrayState)
    {
        $itemSelectedMulti = [];
        $printedItems = [];
        if (isset($arrayState[$this->id])) {
            if (isset($arrayState[$this->id][REPO_ID_SELECTIONSTATE])) {
                $this->itemSelectedMulti = Util::unserialize(urldecode($arrayState[$this->id][REPO_ID_SELECTIONSTATE]));
            }
            if (isset($arrayState[$this->id][REPO_OP_SELECTITEM])) {
                $itemSelectedMulti = array_keys($arrayState[$this->id][REPO_OP_SELECTITEM]);
            }
            if (isset($arrayState[$this->id][REPO_ID_PRINTEDITEM])) {
                $printedItems = Util::unserialize(urldecode($arrayState[$this->id][REPO_ID_PRINTEDITEM]));
            }
            if (isset($arrayState[$this->id][REPO_OP_SELECTMONO])) {
                $itemSelectedMulti = [$arrayState[$this->id][REPO_OP_SELECTMONO]];
            }
            if (isset($arrayState[$this->id][REPO_OP_SELECTRADIO])) {
                foreach ($arrayState[$this->id][REPO_OP_SELECTRADIO] as $key => $val) {
                    // $key contains tree normal group and descendants group idst
                    // concat with an _
                    list($idst, $idst_desc) = explode('_', $key);
                    $printedItems[] = $idst;
                    $printedItems[] = $idst_desc;
                    if ($val != '') {
                        $itemSelectedMulti[] = $val;
                    }
                }
            }
        }
        $unselectedItems = array_diff($printedItems, $itemSelectedMulti);
        $this->itemSelectedMulti = array_diff($this->itemSelectedMulti, $unselectedItems);
        $this->itemSelectedMulti = array_values(array_unique(array_merge($this->itemSelectedMulti, $itemSelectedMulti)));
    }

    public function extendedParsing($arrayState, $arrayExpand, $arrayCompress)
    {
        $this->selection_parseInput($arrayState);
        if (isset($arrayState[$this->_getOpCreateLO()])) {
            $this->op = 'createLO';
        } elseif (isset($arrayState[$this->_getOpCreateLOSel()])) {
            $this->op = 'createLOSel';
            $this->creatingObjectType = $_REQUEST['radiolo'];
        } elseif (isset($_GET[$this->_getOpCreateLOEnd()])) {
            // get result and id resource
            $this->op = 'createLOEnd';
            if (isset($_GET['create_result'])) {
                switch ($_GET['create_result']) {
                    case '1':
                        $idResource = (int) $_GET['id_lo'];
                        $lo = createLO($this->creatingObjectType, $idResource);
                        $this->tdb->addItem($this->getSelectedFolderId(),
                            $lo->getTitle(), $lo->getObjectType(),
                            $idResource,
                            0, /* idCategory */
                            0, /* idUser */
                            getLogUserId(), /* idAuthor */
                            '1.0' /* version */,
                            '_DIFFICULT_MEDIUM', /* difficult */
                            '', /* description */
                            '', /* language */
                            '', /* resource */
                            '', /* objective */
                            date('Y-m-d H:i:s'));
                        $this->refresh = true;
                        break;
                    case '2':
                        $idMultiResource = (int) $_GET['id_lo'];
                        $multiLo = createLO($this->creatingObjectType);
                        $arrIdResources = $multiLo->getMultipleResource($idMultiResource);
                        foreach ($arrIdResources as $idResource) {
                            $lo = createLO($this->creatingObjectType, $idResource);
                            $this->tdb->addItem($this->getSelectedFolderId(),
                                $lo->getTitle(), $lo->getObjectType(),
                                $idResource,
                                0, /* idCategory */
                                0, /* idUser */
                                getLogUserId(), /* idAuthor */
                                '1.0' /* version */,
                                '_DIFFICULT_MEDIUM', /* difficult */
                                '', /* description */
                                '', /* language */
                                '', /* resource */
                                '', /* objective */
                                date('Y-m-d H:i:s'));
                        }
                        $this->refresh = true;

                        break;
                    case '3':
                        $idResources = explode(',', $_GET['id_los']);

                        foreach ($idResources as $idResource) {
                            $lo = createLO($this->creatingObjectType, $idResource);
                            $this->tdb->addItem($this->getSelectedFolderId(),
                                $lo->getTitle(), $lo->getObjectType(),
                                $idResource,
                                0, /* idCategory */
                                0, /* idUser */
                                getLogUserId(), /* idAuthor */
                                '1.0' /* version */,
                                '_DIFFICULT_MEDIUM', /* difficult */
                                '', /* description */
                                '', /* language */
                                '', /* resource */
                                '', /* objective */
                                date('Y-m-d H:i:s'));
                        }
                        $this->refresh = true;
                    // no break
                    default:
                }
            }
        } elseif (isset($arrayState[$this->_getOpEditLO()])) {
            $this->op = 'editLO';
        } elseif (isset($_GET[$this->_getOpEditLOEnd()])) {
            $this->op = 'editLOEnd';
        } elseif (isset($arrayState[$this->_getOpCopyLO()])) {
            $this->op = 'copyLO';
        } elseif (isset($_GET[$this->_getOpCopyLOSel()])) {
            $this->op = 'copyLOSel';
        } elseif (isset($_GET[$this->_getOpPlayEnd()])) {
            $this->op = 'itemdone';
        }

        if (isset($arrayState[$this->_getOpCopyLOEndOk()])) {
            // op now can be copyLOSel, but we override it
            require_once _adm_ . '/lib/lib.sessionsave.php';
            $saveObj = new Session_Save();
            $saveName = $_GET['crepo'];
            if ($saveObj->nameExists($saveName)) {
                $saveData = &$saveObj->load($saveName);

                $lo = createLO($saveData['objectType']);
                $idResource = $lo->copy((int) $saveData['idResource']);
                if ($idResource != 0) {
                    $lo = createLO($saveData['objectType']);
                    $this->tdb->addItem($this->getSelectedFolderId(),
                        $saveData['name'], $saveData['objectType'],
                        $idResource,
                        0, /* idCategory */
                        0, /* idUser */
                        getLogUserId(), /* idAuthor */
                        '1.0' /* version */,
                        '_DIFFICULT_MEDIUM', /* difficult */
                        '', /* description */
                        '', /* language */
                        '', /* resource */
                        '', /* objective */
                        date('Y-m-d H:i:s'));
                    $this->refresh = true;
                }
            }
            $this->op = 'copyLOEndOk';
        } elseif (isset($arrayState[$this->_getOpCopyLOEndCancel()])) {
            // op now can be copyLOSel, but we override it
            $this->op = 'copyLOEndCancel';
        }

        if (isset($arrayState[$this->id])) {
            foreach ($arrayState[$this->id] as $key => $action) {
                if ($key == $this->_getOpPropertiesId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        $this->op = 'properties';
                        $this->opContextId = $id;
                    }
                } elseif ($key == $this->_getOpEditLOId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        if ($id > 0) {
                            $this->op = 'editLO';
                            $this->selectedFolder = $id;
                        }
                    }
                } elseif ($key == $this->_getOpCopyLOId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        if ($id > 0) {
                            $this->op = 'copyLO';
                            $this->selectedFolder = $id;
                        }
                    }
                }
            }
        }

        foreach ($arrayState as $nameField => $valueField) {
            if (strstr($nameField, $this->_getOpPropertiesId())) {
                $id = substr($nameField, strlen($this->_getOpPropertiesId()));
                $this->op = 'properties';
                $this->opContextId = $id;
            } else {
                if (strstr($nameField, $this->_getOpEditLOId())) {
                    $id = substr($nameField, strlen($this->_getOpEditLOId()));
                    if (strlen($id) > 0) {
                        $this->op = 'editLO';
                        $this->selectedFolder = $id;
                    }
                } else {
                    if (strstr($nameField, $this->_getOpCopyLOId())) {
                        $id = substr($nameField, strlen($this->_getOpCopyLOId()));
                        if (strlen($id) > 0) {
                            $this->op = 'copyLO';
                            $this->selectedFolder = $id;
                        }
                    }
                }
            }
        }
        if ($this->pathToExpand != null) {
            if (is_array($this->expandList)) {
                $this->expandList = $this->expandList + $this->pathToExpand;
            } else {
                $this->expandList = $this->pathToExpand;
            }
        }
    }

    public function printElement(&$stack, $level)
    {
        $elem = parent::printElement($stack, $level);
        if ($this->withActions == false) {
            return $elem;
        }
        if ($level > 0) {
            $arrData = $stack[$level]['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
            if (is_array($arrData)) {
                /*$elem .= '<input type="image" class="tree_view_image" '
                    .' src="'.$this->_getOpPropertiesImg().'"'
                    .' id="'.$this->id.'_'.$this->_getOpPropertiesId().'_'.$stack[$level]['folder']->id.'" '
                    .' name="'.$this->id.'['.$this->_getOpPropertiesId().']['.$stack[$level]['folder']->id.']" '
                    .' title="'.$this->_getOpPropertiesTitle().': '.$this->getFolderPrintName( $stack[$level]['folder']).'" '
                    .' alt="'.$this->_getOpPropertiesTitle().': '.$this->getFolderPrintName( $stack[$level]['folder']).'" />';*/
                if (!$isFolder) {
                    $elem .= '<input type="image" class="tree_view_image" '
                        . ' src="' . $this->_getCopyImage() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpCopyLOId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpCopyLOId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getOpCopyTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getOpCopyTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                    if ($stack[1]['folder']->otherValues[5] == $this->session->get('public_area_idst') ||
                        Docebo::user()->getUserLevelId() == ADMIN_GROUP_GODADMIN) {
                        $elem .= '<input type="image" class="tree_view_image" '
                            . ' src="' . $this->_getEditImage() . '"'
                            . ' id="' . $this->id . '_' . $this->_getOpEditLOId() . '_' . $stack[$level]['folder']->id . '" '
                            . ' name="' . $this->id . '[' . $this->_getOpEditLOId() . '][' . $stack[$level]['folder']->id . ']" '
                            . ' title="' . $this->_getOpEditTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                            . ' alt="' . $this->_getOpEditTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                    } else {
                        $elem .= '<div class="TVActionEmpty"></div>';
                    }
                    $elem .= '<input type="image" class="tree_view_image" '
                        . ' src="' . $this->_getOpPlayItemImg() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpPlayItemId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpPlayItemId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getOpPlayTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getOpPlayTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                } else {
                    $elem .= '<div class="TVActionEmpty"></div>';
                    $elem .= '<div class="TVActionEmpty"></div>';
                    $elem .= '<div class="TVActionEmpty"></div>';
                }
            }
        }

        return $elem;
    }

    public function getImage(&$stack, $currLev, $maxLev)
    {
        if ($currLev > 0 && $currLev == $maxLev) {
            $arrData = $stack[$currLev]['folder']->otherValues;
            if (is_array($arrData) && $arrData[REPOFIELDOBJECTTYPE] != '') {
                return ['TreeViewImage', 'lobject/' . $arrData[REPOFIELDOBJECTTYPE] . '.png', $arrData[REPOFIELDOBJECTTYPE]];
            }
        }

        return parent::getImage($stack, $currLev, $maxLev);
    }

    public function LOSelector($module, $back_url)
    {
        $query = 'SELECT objectType FROM %lms_lo_types';
        $rs = sql_query($query)
        or exit('Table _lo_types not present');

        $out = '<div class="std_block">';
        $out .= '<div class="title">'
            . Lang::t('_SELECTLO', 'storage', 'lms')
            . '</div><br />';
        $out .= getBackUi(Util::str_replace_once('&', '&amp;', $back_url), Lang::t('_BACK'));
        $out .= '<form id="LOSelector" method="post" action="index.php?modname=' . $module . '&amp;op=display&amp;' . $this->_getOpCreateLOSel() . '=1" >'
            . '<input type="hidden" id="authentic_request_lo" name="authentic_request" value="' . Util::getSignature() . '" />';
        $first = true;
        while (list($objectType) = sql_fetch_row($rs)) {
            $out .= '<label for="' . $objectType . '"><img src="' . getPathImage() . 'lobject/' . $objectType . '.png" alt="' . $objectType . '" '
                . 'title="' . $objectType . '" /></label> ';
            if ($first) {
                $out .= '<input type="radio" name="radiolo" value="' . $objectType . '" id="' . $objectType . '" checked="true"/>';
            } else {
                $out .= '<input type="radio" name="radiolo" value="' . $objectType . '" id="' . $objectType . '"/>';
            }
            $out .= ' <label for="' . $objectType . '">' . Lang::t('_LONAME_' . $objectType, 'storage') . '</label>'
                . '<br />';
            $first = false;
        }
        $out .= $this->printState();
        $out .= '<br /><input type="submit" class="button" value="' . Lang::t('_NEW')
            . '" name="' . $this->_getOpCreateLOSel() . '"/>';
        $out .= '</form>';
        $out .= '</div>';

        return $out;
    }

    public function _getDeleteLabel()
    {
        if ($this->isFolderSelected()) {
            $stackData = $this->getSelectedFolderData();

            if (!isset($stackData['folder']->otherValues)) {
                return Lang::t('_DEL');
            }

            $arrData = $stackData['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
            if (!$isFolder) {
                return Lang::t('_DEL');
            }
        }

        return Lang::t('_DEL');
    }

    public function _getDeleteAlt()
    {
        return $this->_getDeleteLabel();
    }

    public function printState($echoit = true)
    {
        $ot = parent::printState();
        $ot .= '<input type="hidden"'
            . ' id="' . $this->id . REPO_ID_SELECTIONSTATE . '"'
            . ' name="' . $this->id . '[' . REPO_ID_SELECTIONSTATE . ']"'
            . ' value="' . urlencode(Util::serialize($this->itemSelectedMulti)) . '" />' . "\n";

        return $ot;
    }

    public function load()
    {
        if ($this->getOption(REPOOPTSHOWONLYFOLDER) === true) {
            $this->tdb->setFilterTypes(['']);
            $this->refresh = true;
            // we must refresh
            $this->_visitArrayDeep($this->posTree, $this->posFlat, $this->expandList, $this->compressList);
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
                        . ' id="' . $this->id . REPO_OP_SELECTMONO . '_' . $idst . '" '
                        . ' name="' . $this->id . '[' . REPO_OP_SELECTMONO . ']" '
                        . ' value="' . $idst . '"'
                        . ' checked="checked" />' . "\n";
                }
                $ot .= "</div>\n";
                $this->printed_items = array_merge($this->printed_items, $this->itemSelectedMulti);
            }

            $ot .= '<input type="hidden"'
                . ' id="' . $this->id . '_' . REPO_ID_PRINTEDITEM . '"'
                . ' name="' . $this->id . '[' . REPO_ID_PRINTEDITEM . ']"'
                . ' value="' . urlencode(Util::serialize($this->printed_items)) . '" />' . "\n";
        }

        return $ot;
    }

    public function getState()
    {
        $parent_data = parent::getState();
        $parent_data['creatingObjectType'] = $this->creatingObjectType;

        return $parent_data;
    }

    public function setState($arr_state)
    {
        parent::setState($arr_state);
        if (isset($arr_state['creatingObjectType'])) {
            $this->creatingObjectType = $arr_state['creatingObjectType'];
        }
    }

    public function getFolderPrintOther(&$folder)
    {
        if ($this->selector_mode && $this->simple_selector && $folder->id != 0) {
            if (array_search($folder->id, $this->itemDisabled) !== false) {
                return 'onclick="return false;"';
            } else {
                return 'onclick="var c = document.getElementById( \'' . $folder->idtag . '\' );'
                    . 'c.checked = !c.checked; return false;"';
            }
        }
    }

    public function getPreFolderName(&$folder)
    {
        if ($this->selector_mode && $this->simple_selector && $folder->id != 0) {
            $this->printed_items[] = $folder->id;
            $folder->idtag = $this->id . REPO_OP_SELECTITEM . '_' . $folder->id;
            if ($this->multi_choice) {
                $out = '<input type="checkbox"'
                    . ' class="Treeview_checkbox"'
                    . ' id="' . $folder->idtag . '" '
                    . ' name="' . $this->id . '[' . REPO_OP_SELECTITEM . '][' . $folder->id . ']" '
                    . ' value="' . $folder->id . '"';
            } else {
                $out = '<input type="radio"'
                    . ' id="' . $folder->idtag . '" '
                    . ' name="' . $this->id . '[' . REPO_OP_SELECTMONO . ']" '
                    . ' value="' . $folder->id . '"';
            }
            if (array_search($folder->id, $this->itemDisabled) !== false) {
                $out .= ' disabled="disabled" ';
            }
            if (array_search($folder->id, $this->itemSelectedMulti) !== false) {
                $out .= ' checked="checked" ';
            }
            $out .= ' />';

            return $out;
        } else {
            return '';
        }
    }
}
