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
require_once _lms_ . '/lib/lib.repo.php';

define('ORGFIELDIDCOURSE', 13);
define('ORGFIELDPREREQUISITES', 14);
define('ORGFIELDISTERMINATOR', 15);
define('ORGFIELDIDPARAM', 16);
define('ORGFIELDVISIBLE', 17);
define('ORGFIELDMILESTONE', 18);

define('ORGFIELD_WIDTH', 19);
define('ORGFIELD_HEIGHT', 20);
define('ORGFIELD_PUBLISHFROM', 21);
define('ORGFIELD_PUBLISHTO', 22);
define('ORGFIELD_ACCESS', 23);
define('ORGFIELD_PUBLISHFOR', 24);
define('ORGFIELDIGNORESCORE', 25);
define('ACLKINDGROUP', 'group');
define('ACLKINDUSER', 'user');

// lo visibility
define('PF_ALL_USER', '-3');
define('PF_TEACHER', '-2');
define('PF_ATTENDANCE', '-1');

// organization customization of TreeDb class
class OrgDirDb extends RepoDirDb
{
    // these 3 variales are set in overloaded addFolderById
    // before call to parent addFolderById.
    // Then these values are used in _getOtherValues overload
    public $org_idCourse;
    public $org_prerequisites;
    public $org_isTerminator;
    public $org_ignoreScore;
    public $org_idParam;
    public $org_visible;
    public $org_milestone;

    public $org_width;
    public $org_height;
    public $org_publish_for;
    public $org_publish_from;
    public $org_publish_to;
    public $org_access;

    public $idCourse;

    // to filter on types
    public $filterTypes = null;
    // to filter on visibility
    public $filterVisibility = false;
    // filder on access
    public $filterAccess = false;

    public $user_presence = true;

    // Constructor of OrgDirDb class
    // set idCourse to current idCourse or to
    // parameter $idCourse
    public function OrgDirDb($idCourse = false)
    {
        if ($idCourse === false) {
            $this->idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
        } else {
            $this->idCourse = $idCourse;
        }
        parent::RepoDirDb($GLOBALS['prefix_lms'] . '_organization');
        $this->fields = ['id' => 'idOrg', 'idParent' => 'idParent', 'path' => 'path', 'lev' => 'lev'];
    }

    // , '.$prefix.'_organization_access';
    public function setFilterTypes($lotypes)
    {
        $this->filterTypes = $lotypes;
    }

    public function setFilterVisibility($fv = true)
    {
        $this->filterVisibility = $fv;
    }

    public function setFilterAccess($idUser = false)
    {
        $this->filterAccess = $idUser;
    }

    public function extractPrerequisites($idItem, &$prerequisistes)
    {
        if ($prerequisistes == '') {
            return null;
        }
        $arrPre = explode(',', $prerequisistes);
        $arrResult = [];
        while (list($key, $val) = each($arrPre)) {
            if (strncmp($val, $idItem, strlen($idItem)) != 0) {
                $arrResult[] = $val;
            }
        }

        return implode(',', $arrResult);
    }

    public function extractSelfPrerequisites($idItem, &$prerequisites)
    {
        if ($prerequisites == '') {
            return '*';
        }
        $result = '*';
        $arrPre = explode(',', $prerequisites);
        while (list($key, $val) = each($arrPre)) {
            if (strncmp($val, $idItem, strlen($idItem)) == 0) {
                $arrSelf = explode('=', $val);
                if (count($arrSelf) > 1) {
                    $result = $arrSelf[1];
                } else {
                    $result = '*';
                }
                unset($arrPre[$key]);
            }
        }
        $prerequisites = implode(',', $arrPre);

        return $result;
    }

    public function makePrerequisites($idItem, $prerequisites, $selfPrerequisites)
    {
        if ($selfPrerequisites == '*') {
            return $prerequisites;
        } elseif ($prerequisites == '') {
            $prerequisites = $idItem . '=' . $selfPrerequisites;
        } else {
            $prerequisites .= ',' . $idItem . '=' . $selfPrerequisites;
        }

        return $prerequisites;
    }

    // Organization are stored in a table with the structure requested by
    // TreeDb to manage tree. In addition the table contains
    // title, idObject, idCourse
    public function _getOtherFields($tname = false)
    {
        $parent = parent::_getOtherFields($tname);
        if ($tname === false) {
            return $parent . ', idCourse, prerequisites,'
                . ' isTerminator, idParam, visible, milestone, width, height, publish_from, publish_to, access, publish_for, ignoreScore ';
        } else {
            return $parent . ', '
                . $tname . '.idCourse,'
                . $tname . '.prerequisites,'
                . $tname . '.isTerminator, '
                . $tname . '.idParam, '
                . $tname . '.visible, '
                . $tname . '.milestone, '

                . $tname . '.width, '
                . $tname . '.height, '
                . $tname . '.publish_from, '
                . $tname . '.publish_to, '
                . $tname . '.access, '
                . $tname . '.publish_for, '
                . $tname . '.ignoreScore ';
        }
    }

    public function _getOtherValues()
    {
        return parent::_getOtherValues() . ", '"
            . (int) $this->org_idCourse . "', '"
            . $this->org_prerequisites . "', '"
            . (int) $this->org_isTerminator . "', '"
            . (int) $this->org_idParam . "', '"
            . (int) $this->org_visible . "', '"
            . $this->org_milestone . "', "

            . (int) $this->org_width . ', '
            . (int) $this->org_height . ', '
            . ($this->org_publish_from == '' ? "''" : "'" . $this->org_publish_from . "'") . ', '
            . ($this->org_publish_to == '' ? "''" : "'" . $this->org_publish_to . "'") . ', '
            . ($this->org_access == '' ? "''" : "'" . $this->org_access . "'") . ', '
            . ($this->org_publish_for == '' ? "''" : "'" . $this->org_publish_for . "'") . ', '
            . (int) $this->org_ignoreScore . ' ';
    }

    public function _getOtherUpdates()
    {
        return parent::_getOtherUpdates() . ', '
            . " idCourse='" . (int) $this->org_idCourse . "',"
            . " prerequisites='" . $this->org_prerequisites . "',"
            . " isTerminator='" . (int) $this->org_isTerminator . "', "
            . " idParam='" . (int) $this->org_idParam . "', "
            . " visible='" . (int) $this->org_visible . "', "
            . " milestone='" . $this->org_milestone . "', "

            . ' width=' . (int) $this->org_width . ', '
            . ' height=' . (int) $this->org_height . ', '
            . ' publish_from=' . ($this->org_publish_from == null ? 'NULL' : "'" . $this->org_publish_from . "'") . ', '
            . ' publish_to=' . ($this->org_publish_to == null ? 'NULL' : "'" . $this->org_publish_to . "'") . ', '
            . ' access=' . ($this->org_access == null ? 'NULL' : "'" . $this->org_access . "'") . ', '
            . ' publish_for=' . ($this->org_publish_for == null ? 'NULL' : "'" . $this->org_publish_for . "'") . ', '
            . ' ignoreScore=' . (int) $this->org_ignoreScore . ' ';
    }

    public function _getOtherTables($tname = false)
    {
        if ($this->filterAccess !== false) {
            if ($tname === false) {
                return ' LEFT JOIN %lms_organization_access'
                    . ' ON ( %lms_organization.idOrg = %lms_organization_access.idOrgAccess )';
            } else {
                return ' LEFT JOIN %lms_organization_access'
                    . ' ON ( ' . $tname . '.idOrg = %lms_organization_access.idOrgAccess )';
            }
        } else {
            return '';
        }
    }

    public function _getJoinFilter($tname = false)
    {
        return false;
        /*if( $this->filterAccess !== FALSE ) {
            return $tname.'.idOrg = '.$GLOBALS['prefix_lms'].'_organization_access.idOrgAccess';
        } else
            return FALSE;*/
    }

    // overload.
    // filter organization on idCourse
    // filterTypes if they are.
    // visibility in set filterVisibility
    public function _getFilter($tname = false)
    {
        $result = '';
        if ($tname === false) {
            $result = " AND (idCourse = '" . $this->idCourse . "')";
            if ($this->filterTypes !== null) {
                $result .= " AND (objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
            }
            if ($this->filterVisibility) {
                $result .= " AND (visible = '1' )";
                $result .= " AND (NOW() > publish_from OR publish_from = '0000-00-00 00:00:00' OR publish_from IS NULL)";
            }
        } else {
            $result = ' AND (' . $tname . ".idCourse = '" . $this->idCourse . "')";
            if ($this->filterTypes !== null) {
                $result .= ' AND (' . $tname . ".objectType IN ( '" . implode("','", $this->filterTypes) . "' ))";
            }
            if ($this->filterVisibility) {
                $result .= ' AND (' . $tname . ".visible = '1' )";
                $result .= ' AND (NOW() > ' . $tname . '.publish_from OR ' . $tname . ".publish_from = '0000-00-00 00:00:00' OR " . $tname . '.publish_from IS NULL)';
            }
        }
        if ($this->filterAccess !== false) {
            $result .= ' AND ( '
                . '(' . $GLOBALS['prefix_lms'] . "_organization_access.kind IN ('user','group') "
                . ' 	AND ' . $GLOBALS['prefix_lms'] . "_organization_access.value IN ('" . implode("','", $this->filterAccess) . "'))"
                . ' OR (%lms_organization_access.idOrgAccess IS NULL )'
                . ')';
        }

        return $result;
    }

    public function isDISTINCT()
    {
        return true;
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

    public function addFolderById($idParent, $folderName, $idCourse = false)
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

        $this->org_prerequisites = '';
        $this->org_isTerminator = 0;
        $this->org_idParam = 0;
        $this->org_visible = 1;
        $this->org_milestone = '-';

        $this->org_width = '';
        $this->org_height = '';
        $this->org_publish_from = null;
        $this->org_publish_to = null;
        $this->org_publish_for = '';
        $this->org_ignoreScore = (FormaLms\lib\Get::sett('ignore_score', 'on') == 'on' ? 1 : 0);

        if ($idCourse === false) {
            $this->org_idCourse = $this->idCourse;
        } else {
            $this->org_idCourse = $idCourse;
        }
        TreeDb::addFolderById($idParent, $this->getNewPos($idParent));
    }

    public function addItem($idParent, $title, $objectType, $idResource, $idCategory,
                     $idUser, $idAuthor, $version, $difficult, $description,
                     $language, $resource, $objective, $dateInsert,
                     $otherData = null, $idCourse = false)
    {
        require_once _lms_ . '/lib/lib.param.php';
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

        $this->org_prerequisites = '';
        $this->org_isTerminator = 0;
        $this->org_ignoreScore = (FormaLms\lib\Get::sett('ignore_score', 'on') == 'on' ? 1 : 0);
        $this->org_visible = 1;
        if ($idCourse === false) {
            $this->org_idCourse = $this->idCourse;
        } else {
            $this->org_idCourse = $idCourse;
        }

        require_once _lms_ . '/lib/lib.module.php';
        $lo = createLO($objectType);

        if ($lo) { // Add object to the uncategorized resources
            require_once _lms_ . '/lib/lib.kbres.php';
            $kbres = new KbRes();
            $lang = (isset($this->org_idCourse) && defined('LMS') ? Docebo::course()->getValue('lang_code') : false);
            $kbres->saveUncategorizedResource($title, $idResource, $objectType, 'course_lo', $this->org_idCourse, false, $lang);
        }

        $arrParamsInfo = $lo->getParamInfo();
        if ($arrParamsInfo !== false) {
            $param = current($arrParamsInfo);
            $this->org_idParam = setLOParam(null, $param['param_name'], '');
            next($arrParamsInfo);
            while ($param = current($arrParamsInfo)) {
                setLOParam($this->org_idParam, $param['param_name'], '');
                next($arrParamsInfo);
            }
            reset($arrParamsInfo);
        } else {
            $this->org_idParam = setLOParam(null, 'idReference', '');
        }

        $idReference = TreeDb::addFolderById($idParent, $this->getNewPos($idParent));
        setLOParam($this->org_idParam, 'idReference', $idReference);

        return $idReference;
    }

    public function addItemById($idParent, $idObject, $idCourse = false)
    {
        require_once _lms_ . '/lib/lib.param.php';
        $query = 'SELECT `title`, `objectType`, `idResource`'
            . ' FROM %lms_homerepo'
            . " WHERE idObject='" . (int) $idObject . "'";
        list($title, $objectType, $idResource) = sql_fetch_row(sql_query($query));
        $this->org_idObject = $idObject;
        $this->org_title = $title;
        $this->org_objectType = $objectType;
        $this->org_prerequisites = '';
        $this->org_isTerminator = 0;
        $this->org_ignoreScore = (FormaLms\lib\Get::sett('ignore_score', 'on') == 'on' ? 1 : 0);
        $this->org_idResource = $idResource;
        $this->org_visible = 1;
        if ($idCourse === false) {
            $this->org_idCourse = $this->idCourse;
        } else {
            $this->org_idCourse = $idCourse;
        }

        // creation of custom params
        $lo = createLO($this->org_objectType,
            $this->org_idResource,
            null,
            []);
        $arrParamsInfo = $lo->getParamInfo();
        if ($arrParamsInfo !== false) {
            $param = current($arrParamsInfo);
            $this->org_idParam = setLOParam(null, $param['param_name'], '');
            next($arrParamsInfo);
            while ($param = current($arrParamsInfo)) {
                setLOParam($this->org_idParam, $param['param_name'], '');
                next($arrParamsInfo);
            }
            reset($arrParamsInfo);
        } else {
            $this->org_idParam = setLOParam(null, 'idReference', '');
        }

        $idReference = parent::addFolderById($idParent, $this->getNewPos($idParent));
        setLOParam($this->org_idParam, 'idReference', $idReference);

        return $idReference;
    }

    /** change normal behavior.
     *  NOTE: In organizations rename change title field not path.
     **/
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
        $this->org_ignoreScore = $folder->otherValues[ORGFIELDIGNORESCORE];

        return $this->changeOtherData($folder);
    }

    public function _strip($data, $cond)
    {
        return $cond ? stripslashes($data) : $data;
    }

    public function modifyItem($arrData, $idCourse = false, $strips = false)
    {
        $folder = $this->getFolderById($arrData['idItem']);

        require_once _base_ . '/lib/lib.tab.php';

        $tv = new TabView('organization_properties', '#');

        $tv->addTab(new TabElemDefault('prereqisites', '', getPathImage() . 'standard/property.png'));
        $tv->addTab(new TabElemDefault('settings', '', getPathImage() . 'standard/property.png'));
        //$tv->addTab( new TabElemDefault( 'catalogation', '', getPathImage().'standard/edit.png' ) );

        $tv->parseInput($_POST, $_POST);

        $prerequisite = '';

        if ($tv->getActiveTab() === 'prereqisites') {
            if (isset($arrData['organization']['REPO_OP_SELECTITEM'])) {
                $prerequisite = implode(',', $arrData['organization']['REPO_OP_SELECTITEM']);
            }
        } else {
            $prerequisite = $arrData['prerequisites'];
        }

        // unmodifiable values
        $this->org_objectType = $folder->otherValues[REPOFIELDOBJECTTYPE];
        $this->org_idResource = $folder->otherValues[REPOFIELDIDRESOURCE];
        $this->org_idUser = $folder->otherValues[REPOFIELDIDUSER];
        $this->org_idAuthor = $folder->otherValues[REPOFIELDIDAUTHOR];
        $this->org_dateInsert = $folder->otherValues[REPOFIELDDATEINSERT];

        $this->org_title = isset($arrData['title'])
            ? $this->_strip($arrData['title'], $strips)
            : stripslashes($folder->otherValues[REPOFIELDTITLE]);
        $this->org_idCategory = isset($arrData['idCategory'])
            ? $arrData['idCategory']
            : $folder->otherValues[REPOFIELDIDCATEGORY];
        $this->org_version = isset($arrData['version'])
            ? $arrData['version']
            : $folder->otherValues[REPOFIELDVERSION];
        $this->org_difficult = isset($arrData['difficult'])
            ? $arrData['difficult']
            : $folder->otherValues[REPOFIELDDIFFICULT];
        $this->org_description = isset($arrData['description'])
            ? $this->_strip($arrData['description'], $strips)
            : $folder->otherValues[REPOFIELDDESCRIPTION];
        $this->org_language = isset($arrData['language'])
            ? $this->_strip($arrData['language'], $strips)
            : $folder->otherValues[REPOFIELDLANGUAGE];
        $this->org_resource = isset($arrData['resource'])
            ? $this->_strip($arrData['resource'], $strips)
            : $folder->otherValues[REPOFIELDRESOURCE];
        $this->org_objective = isset($arrData['objective'])
            ? $this->_strip($arrData['objective'], $strips)
            : $folder->otherValues[REPOFIELDOBJECTIVE];

        if (isset($arrData['prerequisites'])) {
            $this->org_prerequisites = $this->makePrerequisites($arrData['idItem'], $prerequisite, $arrData['selfPrerequisites']);
        } else {
            $this->org_prerequisites = $folder->otherValues[ORGFIELDPREREQUISITES];
        }

        $this->org_isTerminator = isset($arrData['isTerminator'])
            ? $arrData['isTerminator']
            : $folder->otherValues[ORGFIELDISTERMINATOR];

        $this->org_ignoreScore = isset($arrData['ignoreScore'])
            ? $arrData['ignoreScore']
            : $folder->otherValues[ORGFIELDIGNORESCORE];

        $this->org_idParam = $folder->otherValues[ORGFIELDIDPARAM];
        $this->org_visible = isset($arrData['visibility'])
            ? $arrData['visibility']
            : $folder->otherValues[ORGFIELDVISIBLE];

        if ($idCourse === false) {
            $this->org_idCourse = $this->idCourse;
        } else {
            $this->org_idCourse = $idCourse;
        }

        if (isset($arrData['milestone'])) {
            $this->org_milestone = $arrData['milestone'];
            /* reset milestone */
            if ($this->org_milestone != '-'
                && $this->org_milestone != $folder->otherValues[ORGFIELDMILESTONE]) {
                $this->_resetMilestone($this->org_milestone, $this->org_idCourse);
            }
        } else {
            $this->org_milestone = $folder->otherValues[ORGFIELDMILESTONE];
        }

        $this->org_width = isset($arrData['obj_width'])
            ? $arrData['obj_width']
            : $folder->otherValues[ORGFIELD_WIDTH];

        $this->org_height = isset($arrData['obj_height'])
            ? $arrData['obj_height']
            : $folder->otherValues[ORGFIELD_HEIGHT];

        $arrData['publish_from'] = Format::dateDb($arrData['publish_from'], 'date');
        $arrData['publish_to'] = Format::dateDb($arrData['publish_to'], 'date');

        if ($arrData['publish_from'] > $arrData['publish_to'] && $arrData['publish_to'] != '') {
            $temp = $arrData['publish_from'];
            $arrData['publish_from'] = $arrData['publish_to'];
            $arrData['publish_to'] = $temp;
        }

        $this->org_publish_from = isset($arrData['publish_from'])
            ? $arrData['publish_from']
            : $folder->otherValues[ORGFIELD_PUBLISHFROM];

        $this->org_publish_to = isset($arrData['publish_to'])
            ? $arrData['publish_to']
            : $folder->otherValues[ORGFIELD_PUBLISHTO];

        $this->org_access = $folder->otherValues[ORGFIELD_ACCESS];

        $this->org_publish_for = isset($arrData['publish_for'])
            ? $arrData['publish_for']
            : $folder->otherValues[ORGFIELD_PUBLISHFOR];

        $this->changeOtherData($folder);

        if (isset($arrData['accessGroups'])) {
            if ($arrData['accessGroups'] == '') {
                $arrGroups = [];
            } else {
                $arrGroups = Util::unserialize(urldecode($arrData['accessGroups']));
            }

            if ($arrData['accessUsers'] == '') {
                $arrUsers = [];
            } else {
                $arrUsers = Util::unserialize(urldecode($arrData['accessUsers']));
            }

            $this->setAccess($arrData['idItem'], $arrGroups, $arrUsers);
        }

        if ($this->org_objectType != '' && isset($arrData['customParam'])) {
            // ---- custom LO parameters

            $lo = createLO($this->org_objectType,
                $this->org_idResource,
                $this->org_idParam,
                []);
            $arrParamsInfo = $lo->getParamInfo();

            if ($arrParamsInfo !== false) {
                require_once _lms_ . '/lib/lib.param.php';
                while ($param = current($arrParamsInfo)) {
                    if (isset($arrData[$param['param_name']])) {
                        setLOParam($this->org_idParam, $param['param_name'], $arrData[$param['param_name']]);
                    }
                    next($arrParamsInfo);
                }
            }
        }
    }

    public function _resetMilestone($milestone, $idCourse)
    {
        $query = 'UPDATE ' . $this->table
            . "   SET milestone = '-'"
            . " WHERE milestone = '" . $milestone . "'"
            . "   AND  idCourse = '" . (int) $idCourse . "'";

        return sql_query($query);
    }

    public function getMilestone($milestone, $idCourse)
    {
        $query = 'SELECT idOrg FROM ' . $this->table
            . " WHERE milestone = '" . $milestone . "'"
            . "   AND  idCourse = '" . (int) $idCourse . "'";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 1) {
            list($idFolder) = sql_fetch_row($rs);
            $folder = $this->getFolderById($idFolder);

            return $folder;
        } else {
            return false;
        }
    }

    public function _deleteTree($folder)
    {
        if (parent::_deleteTree($folder)) {
            $query = 'SELECT idOrg, prerequisites FROM ' . $this->table
                . " WHERE FIND_IN_SET( '" . $folder->id . "', prerequisites ) > 0";
            $rs = sql_query($query);
            if ($rs) {
                $num_rows = sql_num_rows($rs);
            } else {
                $num_rows = 0;
            }
            if ($num_rows) {
                while (list($idOrg, $prerequisites) = sql_fetch_row($rs)) {
                    $arrPrequisites = explode(',', $prerequisites);
                    $key = array_search($folder->id, $arrPrequisites);
                    unset($arrPrequisites[$key]);
                    $prerequisites = implode(',', $arrPrequisites);
                    sql_query('UPDATE ' . $this->table
                        . "   SET prerequisites='" . $prerequisites . "' "
                        . " WHERE idOrg='" . $idOrg . "'");
                    $this->deleteAllAccessUG($idOrg);
                }
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * function deleteAllTree()
     *    Delete all items in tree, all folders, all records!
     *
     * @return bool TRUE if success, FALSE otherwise
     **/
    public function deleteAllTree()
    {
        // loop on all items
        require_once _lms_ . '/lib/lib.param.php';
        require_once _lms_ . '/class.module/track.object.php';
        $nullVal = null;
        $coll = $this->getFoldersCollection($nullVal);
        while ($folder = $coll->getNext()) {
            if ($folder->otherValues[REPOFIELDIDRESOURCE] != 0) {
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                $this->deleteAllAccessUG($folder->id);
                delAllLOParam($folder->otherValues[ORGFIELDIDPARAM]);
                Track_Object::delIdTrackFromCommon($folder->id);
                if ($lo->del($folder->otherValues[REPOFIELDIDRESOURCE]) === false) {
                    return false;
                }
            }
        }
        // remove all records from repo
        TreeDb::deleteAllTree();

        return true;
    }

    /**
     * @param int    $idOrgAccess id of the organization item
     * @param string $kind        ACLKINDGROUP or ACLKINDUSER
     *
     * @return array of int, one element for any user/group that as access
     *               to object
     *
     * @internal
     * Get users or groups access for an object in organization
     */
    public function _getAccessUG($idOrgAccess, $kind)
    {
        return true;

        $query = 'SELECT value FROM %lms_organization_access'
            . " WHERE idOrgAccess = '" . (int) $idOrgAccess . "'"
            . "   AND kind = '" . $kind . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('ERROR in query ' . $query);
            exit(0);
        } else {
            $result = [];
            while (list($id) = sql_fetch_row($rs)) {
                $result[] = $id;
            }

            return $result;
        }
    }

    /**
     *    Get groups access for an object in organization.
     *
     * @param int $idOrgAccess id of the organization item
     *
     * @return array of int, one element for any group that as access
     *               to object
     */
    public function getAccessGroups($idOrgAccess)
    {
        return true;

        return $this->_getAccessUG($idOrgAccess, ACLKINDGROUP);
    }

    /**
     *    Get users access for an object in organization.
     *
     * @param int $idOrgAccess id of the organization item
     *
     * @return array of int, one element for any user that as access
     *               to object
     */
    public function getAccessUsers($idOrgAccess)
    {
        return true;

        return $this->_getAccessUG($idOrgAccess, ACLKINDUSER);
    }

    /**
     * @param int    $idOrgAccess id of the item to set access to
     * @param string $kind        ACLKINDGROUP or ACLKINDUSER
     * @param int    $id          id of user or group to add to ACL of object
     *
     **@internal
     *    Insert an user or group to access list of object
     */
    public function _insertAccessUG($idOrgAccess, $kind, $id)
    {
        return true;

        $query = 'INSERT INTO %lms_organization_access'
            . ' (idOrgAccess, kind, value) VALUES ('
            . " '" . (int) $idOrgAccess . "','" . $kind . "','" . (int) $id . "')";
        $rs = sql_query($query);
        if ($rs === false) {
            if (sql_errno() == 1062) {
                // duplicate entry. This is not a error that should block
                return;
            } else {
                errorCommunication('Error on query ' . $query);
                exit(0);
            }
        }
    }

    /**
     * Remove all user and groups from access list of object.
     *
     * @param int $idOrgAccess id of the item to set access to
     **/
    public function deleteAllAccessUG($idOrgAccess)
    {
        $query = 'DELETE FROM %lms_organization_access'
            . " WHERE idOrgAccess = '" . (int) $idOrgAccess . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('Error on query ' . $query);
            exit(0);
        }
    }

    /**
     * @param int    $idOrgAccess id of the item to set access to
     * @param string $kind        ACLKINDGROUP or ACLKINDUSER
     * @param int    $id          id of user or group to add to ACL of object
     *
     **@internal
     *    Remove an user or grouo from access list of object
     */
    public function _deleteAccessUG($idOrgAccess, $kind, $id)
    {
        $query = 'DELETE FROM %lms_organization_access'
            . " WHERE idOrgAccess = '" . (int) $idOrgAccess . "'"
            . "   AND kind = '" . $kind . "'"
            . "   AND value = '" . (int) $id . "'";
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('Error on query ' . $query);
            exit(0);
        }
    }

    /**
     * @param int    $idOrgAccess the id of the organization item
     * @param int    $arrId       is the array with ids
     * @param string $kind        ACLKINDGROUP or ACLKINDUSER
     *
     **@internal
     *    Update user or group access to object.
     *    This function made all operations needed, deletions and inserts
     */
    public function _setAccessUG($idOrgAccess, $kind, $arrId)
    {
        return true;
        $arrCurrId = $this->_getAccessUG($idOrgAccess, $kind);

        while (list($currKey, $currId) = each($arrCurrId)) {
            $pos = array_search($currId, $arrId);
            if ($pos === false) {
                $this->_deleteAccessUG($idOrgAccess, $kind, $currId);
            } else {
                unset($arrId[$pos]);
            }
        }
        // now in $arrId they are only $id to insert
        while (list($newKey, $newId) = each($arrId)) {
            $this->_insertAccessUG($idOrgAccess, $kind, $newId);
        }
    }

    /**
     *    Set access for an object in organization
     *    To reset all access simply assign empry arrays
     *    to $idOrgAccess and $idUsers.
     *    If $idGroups or $idUsers is NULL that kind of operations
     *    are ignored; so call setAccess with $idGroups and $idUsers
     *    each set to NULL make no sense.
     *
     * @param int $idOrgAccess id of the item
     * @param int $idGroups    array of idGroup that can access
     * @param int $idUsers     array of idUser that can access
     **/
    public function setAccess($idOrgAccess, $idGroups, $idUsers)
    {
        return true;
        if ($idGroups !== null) {
            $this->_setAccessUG($idOrgAccess, ACLKINDGROUP, $idGroups);
        }
        if ($idUsers !== null) {
            $this->_setAccessUG($idOrgAccess, ACLKINDUSER, $idUsers);
        }
    }

    /**
     *    Return an array with all groups of the course that have a prof as owner.
     *
     * @return array all groups of course with a prof as user
     */
    public function getAllGroups()
    {
        $query = 'SELECT idGroup, groupName FROM %lms_coursegroup'
            . " WHERE idCourse = '" . (int) $this->idCourse . "'"
            . '   AND level >= 6 ';
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('Error in query ' . $query);
            exit(0);
        }
        $result = [];
        while (list($idGroup, $groupName) = sql_fetch_row($rs)) {
            $result[$idGroup] = $groupName;
        }

        return $result;
    }

    /**
     *    Return an array with all the users of the course.
     *
     * @return array all users of course
     */
    public function getAllUsers()
    {
        $query = 'SELECT u.idUser, u.userid '
            . ' FROM %lms_courseuser cu, %adm_user u '
            . " WHERE idCourse = '" . (int) $this->idCourse . "'"
            . '   AND u.idUser = cu.idUser ';
        $rs = sql_query($query);
        if ($rs === false) {
            errorCommunication('Error in query ' . $query);
            exit(0);
        }
        $result = [];
        while (list($idUser, $userid) = sql_fetch_row($rs)) {
            $result[$idUser] = $userid;
        }

        return $result;
    }

    //****************************************************************************

    public function __getAccess($idOrgAccess, $userlist = false)
    {
        $query = 'SELECT value FROM %lms_organization_access'
            . " WHERE idOrgAccess = '" . (int) $idOrgAccess . "'";
        $rs = sql_query($query);
        $result = [];
        while (list($id) = sql_fetch_row($rs)) {
            $result[] = $id;
        }

        return $result;
    }

    public function __setAccess($idOrgAccess, $selection, $relation = '')
    {
        $acl_man = &Docebo::user()->getAclManager();

        $id_groups = $acl_man->getAllGroupsFromSelection($selection);

        if ($relation != '') {
            $idst_element = current($selection);
            if (array_search($idst_element, $id_groups) !== false) {
                $type = 'group';
            } else {
                $type = 'user';
            }

            $query = 'DELETE FROM %lms_organization_access'
                . ' WHERE idOrgAccess = ' . (int) $idOrgAccess . " AND kind = '" . $type . "' AND value = '" . (int) $idst_element . "'";
            sql_query($query);

            if ($relation != 'NULL') {
                $query = 'INSERT INTO %lms_organization_access'
                    . ' (idOrgAccess, kind, value, params) VALUES ('
                    . " '" . (int) $idOrgAccess . "','" . $type . "','" . (int) $idst_element . "','" . $relation . "')";

                sql_query($query);
            }
        } else {
            $query_old_values = 'SELECT value, params FROM %lms_organization_access'
                . ' WHERE idOrgAccess = ' . (int) $idOrgAccess;
            $re_old_values = sql_query($query_old_values);

            $old_relations = [];
            while (list($old_value, $old_relation) = sql_fetch_row($re_old_values)) {
                $old_relations[$old_value] = $old_relation;
            }

            $query = 'DELETE FROM %lms_organization_access'
                . ' WHERE idOrgAccess = ' . (int) $idOrgAccess;
            sql_query($query);

            foreach ($selection as $idst_element) {
                if (array_search($idst_element, $id_groups) !== false) {
                    $type = 'group';
                } else {
                    $type = 'user';
                }

                $query = 'INSERT INTO %lms_organization_access'
                    . ' (idOrgAccess, kind, value, params) VALUES ('
                    . " '" . (int) $idOrgAccess . "','" . $type . "','" . (int) $idst_element . "','" . $old_relations[$idst_element] . "')";

                sql_query($query);
            }
        }
    }

    public function __deleteAccess($idOrgAccess)
    {
        $db = DbConn::getInstance();
        $query = 'UPDATE ' . $GLOBALS['prefix_lms'] . "_organization SET access='' WHERE idOrg='" . $idOrgAccess . "'";

        return $res = $db->query($query);
    }
}

class Org_TreeView extends RepoTreeView
{
    /** bool $playOnly if true show only play action */
    public $playOnly = false;

    public function Org_TreeView($tdb, $id, $rootname = 'root')
    {
        parent::__construct($tdb, $id, $rootname);
    }

    public function _getPropertiesId()
    {
        return 'org_opproperties';
    }

    public function _getCategorizeId()
    {
        return 'org_opcategorize';
    }

    public function _getAccessId()
    {
        return 'org_opaccess';
    }

    public function _getOpUpId()
    {
        return '_orgopup_';
    }

    public function _getOpDownId()
    {
        return '_orgopdown_';
    }

    public function _getShowResultsId()
    {
        return '_showresults_';
    }

    public function _getAddImage()
    {
        return getPathImage() . 'standard/folder_new.png';
    }

    public function _getAddLabel()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getAddAlt()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getFolderNameLabel()
    {
        return $this->lang->def('_NAME');
    }

    public function _getCreateLabel()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getCreateAlt()
    {
        return $this->lang->def('_NEW_FOLDER');
    }

    public function _getCreateImage()
    {
        return getPathImage() . 'standard/folder_new.png';
    }

    public function _getOpUpTitle()
    {
        return $this->lang->def('_MOVE_UP');
    }

    public function _getOpDownTitle()
    {
        return $this->lang->def('_MOVE_DOWN');
    }

    public function _getOpPlayTitle()
    {
        return $this->lang->def('_DETAILS');
    }

    public function _getPropertiesTitle()
    {
        return $this->lang->def('_PROPERTIES');
    }

    public function _getCategorizeTitle()
    {
        return $this->lang->def('_CATEGORIZE');
    }

    public function _getAccessTitle()
    {
        return $this->lang->def('_ORG_ACCESS');
    }

    public function _getOpLockedTitle()
    {
        return $this->lang->def('_ORGLOCKEDTITLE');
    }

    public function _getShowResultsTitle()
    {
        return $this->lang->def('_SHOW_RESULTS');
    }

    public function _getPropertiesImg()
    {
        return getPathImage() . 'standard/property.png';
    }

    public function _getCategorizeImg()
    {
        return getPathImage() . 'standard/categorize.png';
    }

    public function _getOpUpImg()
    {
        return getPathImage() . 'standard/up.png';
    }

    public function _getOpDownImg()
    {
        return getPathImage() . 'standard/down.png';
    }

    public function _getAccessImg()
    {
        return getPathImage() . 'standard/moduser.png';
    }

    public function _getOpLockedImg()
    {
        return getPathImage() . 'standard/locked.png';
    }

    public function _getShowResultsImg()
    {
        return getPathImage() . 'standard/report.png';
    }

    public function _getOpLockedId()
    {
        return 'locked_item_';
    }

    public function _getOtherActions()
    {
        if ($this->playOnly) {
            return [];
        }
        $langRepo = &DoceboLanguage::createInstance('storage', 'lms');
        if ($this->isFolderSelected()) {
            $stackData = $this->getSelectedFolderData();
            $arrData = $stackData['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
            if (!$isFolder) {
                return [];
            }
            /*array(	array($this->_getOpEditLO(), $langRepo->def('_MOD'), getPathImage().'standard/edit.png' ),
                                array($this->_getOpCopyLO(), $langRepo->def('_REPOCOPYLO'), getPathImage().'dup.png' )
                            );*/
        }

        return [[$this->_getOpCreateLO(), $langRepo->def('_REPOCREATELO'), getPathImage() . 'standard/add.png']];
    }

    public function canMove()
    {
        return false;
        /*if( $this->playOnly ) return FALSE;
        return $this->isFolderSelected();*/
    }

    public function canRename()
    {
        return false;
        /*if( $this->playOnly ) return FALSE;
        return $this->isFolderSelected();*/
    }

    public function canAdd()
    {
        if ($this->playOnly) {
            return false;
        }
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
        /*if( $this->playOnly ) return FALSE;
        if( !$this->isFolderSelected() )
            return FALSE;
        $data = $this->getSelectedFolderData();
        if( $data['isLeaf'] === FALSE ) {
            return FALSE;
        }
        return TRUE;*/
    }

    public function canInlineMove()
    {
        return $this->withActions && !$this->playOnly;
    }

    public function canInlineRename()
    {
        return false; /*$this->withActions && !$this->playOnly;*/
    }

    public function canInlineDelete()
    {
        return $this->withActions && !$this->playOnly;
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
        /*if( $level == 0 )
            return FALSE;
        return TRUE;*/
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

    public function getFolderPrintName(&$folder)
    {
        if (isset($folder->otherValues[REPOFIELDTITLE])) {
            return str_replace('"', '&quot;', strip_tags($folder->otherValues[REPOFIELDTITLE]));
        } else {
            return parent::getFolderPrintName($folder);
        }
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

    public function extendedParsing($arrayState, $arrayExpand, $arrayCompress)
    {
        parent::extendedParsing($arrayState, $arrayExpand, $arrayCompress);

        if (isset($_GET['org_access'])) {
            $this->op = 'org_access';
            $this->opContextId = $_GET['idItem'];
        }
        if (isset($arrayState['stay_on_categorize'])) {
            $this->op = 'org_categorize';
            $this->opContextId = $arrayState['idItem'];
        }
        if (isset($arrayState['org_categorize_cancel'])) {
            $this->op = 'display';
        }
        if (isset($arrayState['org_categorize_save'])) {
            require_once dirname(__FILE__) . '/orgcategorize.php';
            organization_categorize_save($this, $arrayState['idItem']);
            $this->op = 'display';
        }
        if (isset($arrayState['org_categorize_switch_subcat'])) {
            require_once dirname(__FILE__) . '/orgcategorize.php';
            organization_categorize_switch_subcat($this, $arrayState['idItem']);
            $this->op = 'org_categorize';
            $this->opContextId = $arrayState['idItem'];
        }
        if (isset($arrayState['stay_on_properties'])) {
            $this->op = 'org_properties';
            $this->opContextId = $arrayState['idItem'];
        }
        if (isset($arrayState['org_properties_cancel'])) {
            $this->op = 'display';
        }
        if (isset($arrayState['org_properties_ok'])) {
            $arrayState['prerequisites'] = implode(',', $this->itemSelectedMulti);
            if ($arrayState['prerequisites'] != '' && $arrayState['prerequisites'][0] == ',') {
                $arrayState['prerequisites'] = substr($arrayState['prerequisites'], 1);
            }
            //LRZ: mem info for custom field of LO
            require_once _adm_ . '/lib/lib.customfield.php';
            $extra_field = new CustomFieldList();
            $extra_field->setFieldArea('LO_OBJECT');
            $extra_field->storeFieldsForObj($arrayState['idItem']);
            // end manage custom field for lo_object

            $this->tdb->modifyItem($arrayState, false, true);
            $this->op = 'display';
        }

        if (isset($arrayState[$this->id])) {
            foreach ($arrayState[$this->id] as $key => $action) {
                if ($key == $this->_getAccessId()) {
                    if (is_array($action)) {
                        $this->opContextId = key($action);
                    }
                    $this->op = $this->_getAccessId();
                } elseif ($key == $this->_getOpUpId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        $this->tdb->moveUp($id);
                        $this->refresh = true;
                    }
                } elseif ($key == $this->_getOpDownId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        $this->tdb->moveDown($id);
                        $this->refresh = true;
                    }
                } elseif ($key == $this->_getPropertiesId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        $this->op = $this->_getPropertiesId();
                        $this->opContextId = $id;
                    }
                } elseif ($key == $this->_getCategorizeId()) {
                    if (is_array($action)) {
                        $id = key($action);
                        $this->op = $this->_getCategorizeId();
                        $this->opContextId = $id;
                    }
                } elseif ($key == $this->_getSelectedId() && !checkPerm('lesson', true, 'storage')) {
                    if (is_array($action)) {
                        $id = key($action);
                        if (strlen($id) > 0) {
                            $folder = $this->tdb->getFolderById((int) $id);
                            if ($folder->otherValues[REPOFIELDOBJECTTYPE] != '') {
                                require_once _lms_ . '/class.module/track.object.php';
                                if (Track_Object::isPrerequisitesSatisfied(
                                    $folder->otherValues[ORGFIELDPREREQUISITES],
                                    getLogUserId())) {
                                    $this->op = 'playitem';
                                    $this->itemToPlay = $id;
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach ($arrayState as $nameField => $valueField) {
            if (strstr($nameField, $this->_getSelectedId()) && !checkPerm('lesson', true, 'storage')) {
                $id = substr($nameField, strlen($this->_getSelectedId()));
                if (strlen($id) > 0) {
                    $folder = $this->tdb->getFolderById((int) $id);
                    if (isset($folder->otherValues[REPOFIELDOBJECTTYPE]) && $folder->otherValues[REPOFIELDOBJECTTYPE] != '') {
                        require_once _lms_ . '/class.module/track.object.php';
                        if (Track_Object::isPrerequisitesSatisfied(
                            $folder->otherValues[ORGFIELDPREREQUISITES],
                            getLogUserId())) {
                            $this->op = 'playitem';
                            $this->itemToPlay = $id;
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

    /** @deprecated */
    public function printElement(&$stack, $level)
    {
        // include_once(_base_ . '/appLms/Events/Lms/OrgPropertiesPrintEvent.php');
        // $event = new \appLms\Events\Lms\OrgPropertiesPrintEvent();

        // $event->setElement($stack[$level]['folder']);

        // $event->setDisplayable(true);
        // $event->setAccessible(true);

        // $event->setId($this->id);

        // \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\OrgPropertiesPrintEvent::EVENT_NAME, $event);

        // if (!$event->getDisplayable()) {
        // 	return '';
        // }

        require_once _lms_ . '/class.module/track.object.php';

        // $out = '<div class="TreeViewRowBase">';
        $out = '<td>';
        $id = ($stack[$level]['isExpanded']) ? ($this->_getCompressActionId()) : ($this->_getExpandActionId());
        $id .= $stack[$level]['folder']->id;
        for ($i = 0; $i <= $level; ++$i) {
            list($classImg, $imgFileName, $imgAlt) = $this->getImage($stack, $i, $level);
            if ($i != ($level - 1) || $stack[$level]['isLeaf']) {
                $out .= '<img src="' . getPathImage() . $imgFileName . '" '
                    . 'class="' . $classImg . '" alt="' . $imgAlt . '" '
                    . 'title="' . $imgAlt . '" />';
            } else {
                $out .= '<input type="submit" class="' . $classImg . '" value="'
                    . '" name="' . $id . '" id="seq_' . $stack[$level]['idSeq'] . 'img" />';
            }
        }
        if ($stack[$level]['folder']->id == $this->selectedFolder) {
            $this->selectedFolderData = $stack[$level];
            $classStyle = 'TreeItemSelected';
        } else {
            $classStyle = 'TreeItem';
        }
        $out .= $this->getPreFolderName($stack[$level]['folder']);

        // find extra data and check if the node is a folder or a LO
        $arrData = $stack[$level]['folder']->otherValues;
        if (is_array($arrData) && !empty($arrData)) {
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');
        } else {
            $isFolder = true;
        }

        $lo_type = $arrData[REPOFIELDOBJECTTYPE];
        $lo_class = createLO($lo_type);

        if (!is_object($lo_class) && !$isFolder) {
            return '';
        }

        //check for void selection
        if (is_array($arrData) && isset($arrData[ORGFIELD_ACCESS]) && $this->playOnly) {
            //if (!$this->userSelector->isUserInSelection(getLogUserId(), $arrData[ORGFIELD_ACCESS])) return false;
            if (!empty($arrData[ORGFIELD_ACCESS]) && !in_array(Docebo::user()->getIdst(), $arrData[ORGFIELD_ACCESS])) {
                return false;
            } //?!?
        }

        // read width and hieght param
        $lb_param = '';
        if (!$isFolder) {
            if ($arrData[ORGFIELD_WIDTH] != '' && $arrData[ORGFIELD_WIDTH] != '0') {
                $lb_param .= ';width=' . $arrData[ORGFIELD_WIDTH] . '';
            }
            if ($arrData[ORGFIELD_HEIGHT] != '' && $arrData[ORGFIELD_HEIGHT] != '0') {
                $lb_param .= ';height=' . $arrData[ORGFIELD_HEIGHT] . '';
            }
        }

        // folder are input and LO are link only in the play area
        if ($isFolder || (checkPerm('lesson', true, 'storage') && !$this->playOnly)) {
            $out .= '<input type="submit" class="' . $classStyle . '" value="'
                . $this->getFolderPrintName($stack[$level]['folder'])
                . '" name="'
                . $this->_getSelectedId() . $stack[$level]['folder']->id
                . '" id="seq_' . $stack[$level]['idSeq'] . '" '
                . $this->getFolderPrintOther($stack[$level]['folder'])
                . ' />';
        } else {
            $isPrerequisitesSatisfied = Track_Object::isPrerequisitesSatisfied(
                $stack[$level]['folder']->otherValues[ORGFIELDPREREQUISITES],
                getLogUserId());
            $levelCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('levelCourse');
            if ($arrData[ORGFIELD_PUBLISHFOR] == PF_TEACHER && $levelCourse <= 3) {
                return false;
            } elseif ($arrData[ORGFIELD_PUBLISHFOR] == PF_ATTENDANCE && !$this->presence()) {
                $out .= ' <span class="' . $classStyle . '" ' .
                    'id="' . $this->id . '_' . $this->_getOpPlayItemId() . '_' . $stack[$level]['folder']->id . '" ' .
                    'name="' . $this->id . '[' . $this->_getOpPlayItemId() . '][' . $stack[$level]['folder']->id . ']">'
                    . $this->getFolderPrintName($stack[$level]['folder']) .
                    '</span>';
            } elseif ($isPrerequisitesSatisfied) { // && $event->getAccessible()) {
                $out .= ' <a ' . ($lo_class->showInLightbox() ? ' rel="lightbox' . $lb_param . '"' : '') . ' class="' . $classStyle . '" ' .
                    'id="' . $this->id . '_' . $this->_getOpPlayItemId() . '_' . $stack[$level]['folder']->id . '" ' .
                    'name="' . $this->id . '[' . $this->_getOpPlayItemId() . '][' . $stack[$level]['folder']->id . ']" ' .
                    'href="index.php?modname=organization&amp;op=custom_playitem&amp;id_item=' . $stack[$level]['folder']->id . '" ' .
                    'title="' . $this->getFolderPrintName($stack[$level]['folder']) . '">'
                    . $this->getFolderPrintName($stack[$level]['folder']) .
                    '</a>';
            } else {
                $out .= ' <span class="' . $classStyle . '" ' .
                    'id="' . $this->id . '_' . $this->_getOpPlayItemId() . '_' . $stack[$level]['folder']->id . '" ' .
                    'name="' . $this->id . '[' . $this->_getOpPlayItemId() . '][' . $stack[$level]['folder']->id . ']">'
                    . $this->getFolderPrintName($stack[$level]['folder']) .
                    '</span>';
            }
        }

        // $out .= '</div>';

        $out .= $this->printActions($stack, $level);

        if ($level > 0) {
            if (checkPerm('lesson', true, 'storage') && !$this->playOnly) {
                if ($this->withActions == false) {
                    return $out;
                }
                if ($stack[$level]['isFirst']) {
                    $out .= '<div class="TVActionEmpty">&nbsp;</div>';
                } else {
                    $out .= '<input type="image" class="tree_view_image" '
                        . ' src="' . $this->_getOpUpImg() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpUpId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpUpId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getOpUpTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getOpUpTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                }
                if ($stack[$level]['isLast']) {
                    $out .= '<div class="TVActionEmpty">&nbsp;</div>';
                } else {
                    $out .= '<input type="image" class="tree_view_image" '
                        . ' src="' . $this->_getOpDownImg() . '"'
                        . ' id="' . $this->id . '_' . $this->_getOpDownId() . '_' . $stack[$level]['folder']->id . '" '
                        . ' name="' . $this->id . '[' . $this->_getOpDownId() . '][' . $stack[$level]['folder']->id . ']" '
                        . ' title="' . $this->_getOpDownTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                        . ' alt="' . $this->_getOpDownTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                }
                $out .= '<input type="image" class="tree_view_image" '
                    . ' src="' . $this->_getAccessImg() . '"'
                    . ' id="' . $this->id . '_' . $this->_getAccessId() . '_' . $stack[$level]['folder']->id . '" '
                    . ' name="' . $this->id . '[' . $this->_getAccessId() . '][' . $stack[$level]['folder']->id . ']" '
                    . ' title="' . $this->_getAccessTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                    . ' alt="' . $this->_getAccessTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
            }
            $arrData = $stack[$level]['folder']->otherValues;
            $isFolder = ($arrData[REPOFIELDOBJECTTYPE] === '');

            if (is_array($arrData)) {
                switch ($this->kind) {
                    case 'prerequisites':
                        $out .= '<input type="text" value="" name="'
                            . $this->_getPrerequisitesId() . $stack[$level]['folder']->id . '" />';
                        break;
                    default:
                        if (checkPerm('lesson', true, 'storage') && !$this->playOnly) {
                            if ($this->withActions == false) {
                                return $out;
                            }
                            $canBeCategorized = false;
                            if (is_object($lo_class)) {
                                $canBeCategorized = $lo_class->canBeCategorized();
                            }

                            if ($canBeCategorized) {
                                $out .= '<input type="image" class="tree_view_image" '
                                    . ' src="' . $this->_getCategorizeImg() . '"'
                                    . ' id="' . $this->id . '_' . $this->_getCategorizeId() . '_' . $stack[$level]['folder']->id . '" '
                                    . ' name="' . $this->id . '[' . $this->_getCategorizeId() . '][' . $stack[$level]['folder']->id . ']" '
                                    . ' title="' . $this->_getCategorizeTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                    . ' alt="' . $this->_getCategorizeTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                            } else {
                                $out .= '<div class="TVActionEmpty">&nbsp;</div>';
                            }
                            $out .= '<input type="image" class="tree_view_image" '
                                . ' src="' . $this->_getPropertiesImg() . '"'
                                . ' id="' . $this->id . '_' . $this->_getPropertiesId() . '_' . $stack[$level]['folder']->id . '" '
                                . ' name="' . $this->id . '[' . $this->_getPropertiesId() . '][' . $stack[$level]['folder']->id . ']" '
                                . ' title="' . $this->_getPropertiesTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                . ' alt="' . $this->_getPropertiesTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                            if (!$isFolder) {
                                $out .= '<input type="image" class="tree_view_image" '
                                    . ' src="' . $this->_getCopyImage() . '"'
                                    . ' id="' . $this->id . '_' . $this->_getOpCopyLOId() . '_' . $stack[$level]['folder']->id . '" '
                                    . ' name="' . $this->id . '[' . $this->_getOpCopyLOId() . '][' . $stack[$level]['folder']->id . ']" '
                                    . ' title="' . $this->_getOpCopyTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                    . ' alt="' . $this->_getOpCopyTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';

                                //if ($arrData[REPOFIELDOBJECTTYPE] != 'scormorg') {
                                $out .= '<input type="image" class="tree_view_image" '
                                    . ' src="' . $this->_getEditImage() . '"'
                                    . ' id="' . $this->id . '_' . $this->_getOpEditLOId() . '_' . $stack[$level]['folder']->id . '" '
                                    . ' name="' . $this->id . '[' . $this->_getOpEditLOId() . '][' . $stack[$level]['folder']->id . ']" '
                                    . ' title="' . $this->_getOpEditTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                    . ' alt="' . $this->_getOpEditTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                                /*}
                                else {
                                    $out .='<div class="TVActionEmpty">&nbsp;</div>';
                                }*/

                                $out .= '<a ' . ($lo_class->showInLightbox() ? ' rel="lightbox' . $lb_param . '"' : '') . ' class="tree_view_image" ' .
                                    'id="' . $this->id . '_' . $this->_getOpPlayItemId() . '_' . $stack[$level]['folder']->id . '" ' .
                                    'name="' . $this->id . '[' . $this->_getOpPlayItemId() . '][' . $stack[$level]['folder']->id . ']" ' .
                                    'href="index.php?modname=organization&amp;op=custom_playitem&amp;edit=1&amp;id_item=' . $stack[$level]['folder']->id . '" ' .
                                    'title="' . $this->getFolderPrintName($stack[$level]['folder']) . '">'
                                    . '<img src="' . $this->_getOpPlayItemImg() . '"'
                                    . ' alt="' . $this->_getOpPlayTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />' .
                                    '</a>';
                            } else {
                                $out .= '<div class="TVActionEmpty"></div>';
                            }
                        } else {
                            if (!$isFolder) {
                                if ($arrData[ORGFIELD_PUBLISHFROM] != '' && $arrData[ORGFIELD_PUBLISHFROM] != '0000-00-00 00:00:00') {
                                    if ($arrData[ORGFIELD_PUBLISHFROM] > date('Y-m-d H:i:s')) {
                                        return false;
                                    }
                                }
                                if ($arrData[ORGFIELD_PUBLISHTO] != '' && $arrData[ORGFIELD_PUBLISHTO] != '0000-00-00 00:00:00') {
                                    if ($arrData[ORGFIELD_PUBLISHTO] < date('Y-m-d H:i:s')) {
                                        return false;
                                    }
                                }

                                $status = Track_Object::getStatusFromId(
                                    $stack[$level]['folder']->id,
                                    getLogUserId());

                                if ($arrData[ORGFIELD_PUBLISHFOR] == PF_TEACHER && $levelCourse <= 3) {
                                    return false;
                                } elseif ($arrData[ORGFIELD_PUBLISHFOR] == PF_ATTENDANCE && !$this->presence()) {
                                    $out .= '<input type="image" class="tree_view_image" '
                                        . ' src="' . $this->_getOpLockedImg() . '"'
                                        . ' id="' . $this->id . '_' . $this->_getOpLockedId() . '_' . $stack[$level]['folder']->id . '" '
                                        . ' name="' . $this->id . '[' . $this->_getOpLockedId() . '][' . $stack[$level]['folder']->id . ']" '
                                        . ' title="' . $this->_getOpLockedTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                        . ' alt="' . $this->_getOpLockedTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                                } elseif ($isPrerequisitesSatisfied) { // && $event->getAccessible()) {
                                    if (method_exists($lo_class, 'trackDetails')) {
                                        $out .= '<a class="tree_view_image" '
                                            . 'id="' . $this->id . '_' . $this->_getShowResultsId() . '_' . $stack[$level]['folder']->id . '" '
                                            . 'name="' . $this->id . '[' . $this->_getShowResultsId() . '][' . $stack[$level]['folder']->id . ']" '
                                            . 'href="index.php?modname=organization&amp;op=track_details&amp;type=' . $arrData[REPOFIELDOBJECTTYPE] . '&amp;id_user=' . getLogUserId() . '&amp;id_org=' . $arrData[REPOFIELDIDRESOURCE] . '" '
                                            . 'title="' . $this->_getShowResultsTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '">'
                                            . '<img src="' . $this->_getShowResultsImg() . '"'
                                            . ' alt="' . $this->_getShowResultsTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />'
                                            . '</a>';
                                    } else {
                                        $out .= '<img src="' . getPathImage() . 'blank.png" class="OrgStatus"'
                                            . ' alt="' . Lang::t($status, 'standard', 'framework') . '" title="' . Lang::t($status, 'standard', 'framework') . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                                    }
                                } else {
                                    $out .= '<input type="image" class="tree_view_image" '
                                        . ' src="' . $this->_getOpLockedImg() . '"'
                                        . ' id="' . $this->id . '_' . $this->_getOpLockedId() . '_' . $stack[$level]['folder']->id . '" '
                                        . ' name="' . $this->id . '[' . $this->_getOpLockedId() . '][' . $stack[$level]['folder']->id . ']" '
                                        . ' title="' . $this->_getOpLockedTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" '
                                        . ' alt="' . $this->_getOpLockedTitle() . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';
                                }

                                switch ($status) {
                                    case 'not attempted':
                                        $img = 'blank.png';
                                        break;
                                    case 'ab-initio':
                                        $img = 'ab-initio.png';
                                        break;
                                    case 'attempted':
                                        $img = 'attempted.png';
                                        break;
                                    case 'passed':
                                    case 'completed':
                                        $img = 'completed.png';
                                        break;
                                    case 'failed':
                                        $img = 'fail.png';
                                        break;
                                }
                                $out .= '<img src="' . getPathImage() . 'lobject/' . $img
                                    . '" class="OrgStatus" alt="' . Lang::t($status, 'standard', 'framework') . '" title="' . Lang::t($status, 'standard', 'framework') . ': ' . $this->getFolderPrintName($stack[$level]['folder']) . '" />';

                                // foreach ($event->getAction() as $action){
                                // 	$out .= $action;
                                // }
                            }
                        }
                        break;
                }
            }

            $out .= '</td>';
        }

        return $out;
    }

    public function countChildren($parentId)
    {
        $tdb = $this->tdb->table;
        $query = "SELECT count(*) as countChildren FROM $tdb"
            . ' WHERE idParent = ' . (int) $parentId . " AND objectType = '' ";
        $rs = sql_query($query);
        if (sql_num_rows($rs) == 1) {
            list($count) = sql_fetch_row($rs);

            return $count;
        } else {
            return false;
        }
    }

    public function getChildrensDataById($id)
    {
        $root_folder = $this->tdb->getFolderById($id);
        $childrens = $this->tdb->getChildrensIdById($root_folder->id);

        return $this->getLoData($childrens);
    }

    public function reoderTree()
    {
        $learningObjectIdsToMove = [];
        $this->getLearningObjectToMoveInRoot(0, $learningObjectIdsToMove);

        foreach ($learningObjectIdsToMove as $learningObjectIdToMove) {
            $folder = $this->tdb->getFolderById((string) $learningObjectIdToMove);
            $newParent = $this->tdb->getFolderById((string) 0);
            $folder->move($newParent);
        }
    }

    public function getLearningObjectToMoveInRoot($folderId = 0, &$learningObjectIdsToMove = [])
    {
        $rootFolder = $this->tdb->getFolderById($folderId);
        $children = $this->tdb->getChildrensIdById($rootFolder->id);

        foreach ($children as $child) {
            $learningObject = $this->tdb->getFolderById($child);
            $type = $learningObject->otherValues[REPOFIELDOBJECTTYPE];
            if (!empty($type)) {
                $childChildren = $this->tdb->getChildrensIdById($child);
                if (count($childChildren) > 0) {
                    foreach ($childChildren as $item) {
                        $learningObjectIdsToMove[] = $item;
                    }
                    $this->getLearningObjectToMoveInRoot($child, $learningObjectIdsToMove);
                }
            } else {
                $this->getLearningObjectToMoveInRoot($child, $learningObjectIdsToMove);
            }
        }
    }

    public function getFolderTree($tree)
    {
        $ids = array_keys($tree);
        $info = $this->getLoData($ids);
        $loToShow = array_keys($info);
        foreach ($ids as $id) {
            if (in_array($id, $loToShow)) {
                $folder = $this->tdb->getFolderById($id);
                $tree[$id] = [
                    'id' => $id,
                    'name' => $folder->otherValues[REPOFIELDTITLE],
                    'children' => $this->tdb->getChildrensIdById($id, true),
                    'isPrerequisitesSatisfied' => $info[$id]['isPrerequisitesSatisfied'],
                    'active' => $info[$id]['active'],
                    'locked' => $info[$id]['locked'],
                ];
                if (is_array($tree[$id]['children']) && count($tree[$id]['children']) > 0) {
                    $tree[$id]['children'] = $this->getFolderTree($tree[$id]['children']);
                }
                $tree[$id]['children'] = array_values($tree[$id]['children']);
            }
        }

        return $tree;
    }

    public function getCurrentState($id)
    {
        $possTree = [];

        $childrensRoot = $this->_getChildrens($id);
        if (is_array($childrensRoot) && count($childrensRoot) > 0) {
            foreach (array_flip($childrensRoot) as $children) {
                $possTree[$children] = $this->getCurrentState($children);
            }
        } else {
            return $id;
        }

        return $possTree;
    }

    /** @deprecated */
    public function getLoData($idLoList)
    {
        $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
        if ($GLOBALS['course_descriptor']->getValue('course_type') == 'classroom') {
            require_once _lms_ . '/lib/lib.date.php';
            $man_date = new DateManager();
            $this->user_presence = $man_date->checkUserPresence(getLogUserId(), $idCourse);
        }

        $idLoList = (array) $idLoList;
        include_once _base_ . '/customscripts/appLms/Events/Lms/OrgPropertiesPrintEvent.php';
        require_once _lms_ . '/lib/lib.kbres.php';
        require_once _lms_ . '/class.module/track.object.php';
        require_once _lms_ . '/lib/lib.course.php';

        $res = [];
        $idx = 0;

        foreach ($idLoList as $index => $idLo) {
            $node = [];

            $folder = $this->tdb->getFolderById($idLo);
            //dump($idLo);
            // $event = new \appLms\Events\Lms\OrgPropertiesPrintEvent();

            // $event->setElement($folder);

            // $event->setDisplayable(true);

            // $event->setAccessible(true);

            // $event->setId($this->id);

            // \appCore\Events\DispatcherManager::dispatch(\appLms\Events\Lms\OrgPropertiesPrintEvent::EVENT_NAME, $event);

            // if (!$event->getDisplayable()) {
            // 	continue;
            // }

            $kbres = new KbRes();
            $type = $folder->otherValues[REPOFIELDOBJECTTYPE];
            if ($type === 'scormorg') {
                $type = 'scorm';
            }
            $kbres_information = $kbres->getResourceFromItem($folder->otherValues[REPOFIELDIDRESOURCE], $type, 'course_lo');
            if (isset($kbres_information)) {
                $node['isPublic'] = $kbres_information['force_visible'];
            }

            $html = '';

            // foreach ($event->getAction() as $action){
            // $html .= $action;
            // }

            $arrData = $folder->otherValues;
            $lo_type = $arrData[REPOFIELDOBJECTTYPE];
            $lo_class = createLO($lo_type);

            $node['html'] = $html;

            $node['typeId'] = $this->id;

            $node['title'] = $this->getFolderPrintName($folder);

            $idCourse = $folder->otherValues[ORGFIELDIDCOURSE];
            $course = new DoceboCourse($idCourse);

            $node['actions'] = [];
            $node['visible_actions'] = [];

            $node['idCourse'] = $idCourse;

            $node['courseTitle'] = $course->getValue('name');
            $node['courseType'] = $course->getValue('course_type');
            $node['courseTypeTranslation'] = Lang::t($course->getValue('course_type'), 's4b');

            $isPrerequisitesSatisfied = Track_Object::isPrerequisitesSatisfied($folder->otherValues[ORGFIELDPREREQUISITES], getLogUserId());

            $node['isPrerequisitesSatisfied'] = $isPrerequisitesSatisfied; // && $event->getAccessible();

            $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
            $levelCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('levelCourse');
            if ($folder->otherValues[ORGFIELD_PUBLISHFOR] == PF_TEACHER && $levelCourse <= 3) {
                break;
            }

            $node['active'] = false;

            $node['locked'] = false;

            $node['id'] = $folder->id;

            $node['resource'] = $folder->otherValues[REPOFIELDIDRESOURCE];

            if ($folder->otherValues[ORGFIELD_PUBLISHFOR] == PF_ATTENDANCE && !$this->presence()) {
                $node['active'] = false;
            } elseif ($isPrerequisitesSatisfied) { // && $event->getAccessible()){
                $node['active'] = true;
            }

            if (is_array($arrData) && !empty($arrData)) {
                $node['is_folder'] = ($arrData[REPOFIELDOBJECTTYPE] === '');
            } else {
                $node['is_folder'] = true;
            }
            //$node['is_folder']=count($this->tdb->getidLosIdById($folder->id)) != 0;

            if (($folder->otherValues[ORGFIELD_PUBLISHFROM] != '' && $folder->otherValues[ORGFIELD_PUBLISHFROM] != '0000-00-00 00:00:00') && ($levelCourse <= 3)) {
                if ($folder->otherValues[ORGFIELD_PUBLISHFROM] > date('Y-m-d H:i:s')) {
                    continue;
                }
            }
            if (($folder->otherValues[ORGFIELD_PUBLISHTO] != '' && $folder->otherValues[ORGFIELD_PUBLISHTO] != '0000-00-00 00:00:00') && ($levelCourse <= 3)) {
                if ($folder->otherValues[ORGFIELD_PUBLISHTO] < date('Y-m-d H:i:s')) {
                    continue;
                }
            }

            $status = Track_Object::getStatusFromId($folder->id, getLogUserId());

            if ($folder->otherValues[ORGFIELD_PUBLISHFOR] == PF_TEACHER && $levelCourse <= 3) {
                continue;
            } elseif ($folder->otherValues[ORGFIELD_PUBLISHFOR] == PF_ATTENDANCE && !$this->presence()) {
                $node['locked'] = true;
            } elseif ($isPrerequisitesSatisfied) { // && $event->getAccessible() ) {
                if (!$node['is_folder']) {
                    $node['play'] = true;
                }

                $node['locked'] = false;
            } else {
                $node['locked'] = true;
            }

            if (checkPerm('lesson', true, 'storage') && !$this->playOnly) {
                $node['canEdit'] = true;

                $canBeCategorized = false;
                if (is_object($lo_class)) {
                    $canBeCategorized = $lo_class->canBeCategorized();
                }

                if ($canBeCategorized) {
                    $node['canBeCategorized'] = true;
                }
            }

            $node['status'] = $status;

            switch ($status) {
                case 'not attempted':
                    $img = 'blank.png';
                    break;
                case 'ab-initio':
                    $img = 'ab-initio.png';
                    break;
                case 'attempted':
                    $img = 'attempted.png';
                    break;
                case 'passed':
                case 'completed':
                    $img = 'completed.png';
                    break;
                case 'failed':
                    $img = 'fail.png';
                    break;
            }

            $node['status_logo'] = getPathImage() . 'lobject/' . $img;
            $node['status_label'] = $status;

            $node['type'] = $folder->otherValues[1];

            if ($folder->otherValues[1] != '') {
                $node['image_type'] = $folder->otherValues[1];
            }

            if (!array_key_exists('image_type', $node)) {
                $node['image_type'] = 'folder';
            }

            $node['properties'] = $folder->properties;

            $node['img_path'] = FormaLms\lib\Get::rel_path('files_lms') . '/lo/';

            if (!$node['is_folder']) {
                if ($arrData[ORGFIELD_PUBLISHFOR] == PF_ATTENDANCE && !$this->presence()) {
                    $node['locked'] = true;
                } elseif ($isPrerequisitesSatisfied) { // && $event->getAccessible()) {
                    if (method_exists($lo_class, 'trackDetails')) {
                        $node['track_detail'] = [
                            'type' => $arrData[REPOFIELDOBJECTTYPE],
                            'is_user' => getLogUserId(),
                            'id_org' => $arrData[REPOFIELDIDRESOURCE],
                        ];
                    }
                } else {
                    $node['locked'] = true;
                }
            } else {
                $node['childCount'] = (int) $this->countChildren($folder->id);
            }

            $res[$idLo] = $node;
            ++$idx;
        }

        return $res;
    }

    public function load()
    {
        $isFirst = true;
        $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
        // check if the user attende the course
        if ($GLOBALS['course_descriptor']->getValue('course_type') == 'classroom') {
            require_once _lms_ . '/lib/lib.date.php';
            $man_date = new DateManager();
            $this->user_presence = $man_date->checkUserPresence(getLogUserId(), $idCourse);
        }

        $tree = $this->printState();
        $coll = $this->_retrieveData();
        $stack = [];
        $level = 0;
        $count = 0;

        // $tree .= '<div class="TreeViewContainer">'."\n";
        $tree .= '<div class="panel panel-default panel-treeview">' . "\n";

        $tree .= '<div class="panel-heading">';
        $tree .= $this->printElement($stack, $level);
        $tree .= '</div>';

        $tree .= '<table class="table table-striped table-hover">' . "\n";
        $folder = $this->tdb->getRootFolder();
        $stack[$level] = [];
        $stack[$level]['folder'] = $folder;
        $stack[$level]['childs'] = $this->posTree[0];
        $stack[$level]['isLast'] = true;
        $stack[$level]['isLeaf'] = false;
        $stack[$level]['isExpanded'] = false;
        $stack[$level]['idSeq'] = $folder->id;
        $stack[$level]['isFirst'] = $isFirst;

        // $tree .= '<div class="TreeViewRowOdd" id="row_'.$stack[$level]['idSeq'].'">';
        // $tree .= $this->printElement($stack, $level);
        // $tree .= '</div>';

        ++$level;

        if ($coll !== false) {
            while ($folder = $coll->getNext()) {
                list($key, $val) = each($stack[$level - 1]['childs']);
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

                $row_content = $this->printElement($stack, $level);

                if ($row_content !== false) {
                    ++$count;
                    // if( $count % 2 == 0 )
                    // 	$tree .= '<div class="TreeViewRowOdd" id="row_'.$stack[$level]['idSeq'].'">';
                    // else
                    // 	$tree .= '<div class="TreeViewRowEven" id="row_'.$stack[$level]['idSeq'].'">';
                    $tree .= '<tr id="row_' . $stack[$level]['idSeq'] . '">';
                    $tree .= $row_content;
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

    public function presence()
    {
        return $this->user_presence;
    }
}
