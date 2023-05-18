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

require_once _lms_ . '/lib/lib.repo.php';

define('REPOFIELDIDOWNER', 13);

class HomerepoDirDb extends RepoDirDb
{
    public $idOwner = 0;
    public $org_publish_for;
    public $org_access;
    public $org_publish_to;
    public $org_publish_from;
    public $org_height;
    public $org_width;
    public $org_milestone;
    public $org_visible;
    public $org_idParam;
    public $org_isTerminator;
    public $org_prerequisites;
    public $org_idCourse;
    public $org_objectType;

    public function __construct($table_name, $idOwner)
    {
        $this->idOwner = $idOwner;
        parent::__construct($table_name);
    }

    public function _getOtherFields($tname = false)
    {
        if ($tname === false) {
            return parent::_getOtherFields(false) . ', idOwner';
        } else {
            return parent::_getOtherFields($tname) . ', ' . $tname . '.idOwner ';
        }
    }

    public function _getOtherValues()
    {
        return parent::_getOtherValues() . ", '" . (int) $this->idOwner . "' ";
    }

    public function _getOtherUpdates()
    {
        return parent::_getOtherUpdates() . ", idAuthor='" . (int) $this->idOwner . "'";
    }

    public function _getFilter($tname = false)
    {
        $result = '';
        if ($tname === false) {
            $result .= " AND (idOwner = '" . (int) $this->idOwner . "') ";
        } else {
            $result .= ' AND (' . $tname . ".idOwner = '" . (int) $this->idOwner . "') ";
        }

        return parent::_getFilter($tname) . $result;
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

function homerepo(&$treeView)
{
    // manage items addition
    if (isset($_POST['_repoproperties_save'])) {
        $treeView->tdb->modifyItem($_POST);
        $treeView->op = '';
    } elseif (isset($_POST['_repoproperties_cancel'])) {
        $treeView->op = '';
    }

    switch ($treeView->op) {
        case 'newfolder':
        case 'renamefolder':
        case 'movefolder':
        case 'deletefolder':
            homerepo_opfolder($treeView, $treeView->op);
        break;
        case 'import':
            homerepo_import($treeView);
        break;
        case 'createLO':
            // Save state in session
            global $modname;
            $GLOBALS['page']->add($treeView->LOSelector($modname), 'content');
        break;
        case 'createLOSel':
            global $modname;
            $lo = createLO($_REQUEST['radiolo']);
            $lo->create('index.php?modname' . $modname . '&amp;op=created');
        break;
        case 'properties':
        case 'properties_accessgroups_remove':
        case 'properties_accessgroups_add':
        case 'properties_accessusers_remove':
        case 'properties_accessusers_add':
            homerepo_itemproperties($treeView, $_POST, $treeView->opContextId);
        break;
        case 'treeview_error':
            homerepo_showerror($treeView);
        break;
        case 'save':
            $treeView->tdb->modifyItem($_POST);
            // no break
        default:
            homerepo_display($treeView);
        break;
    }
}

function homerepo_display(&$treeView)
{
    Util::jump_to('index.php?r=lms/lomanager/show');
}

function homerepo_opfolder(&$treeView, $op)
{
    global $modname;
    $GLOBALS['page']->add('<div class="std_block">');
    $GLOBALS['page']->add('<form name="homereponewfolder" method="post"'
    . ' action="index.php?modname=' . $modname . '&amp;op=homerepo"'
    . ' >' . "\n"
    . '<input type="hidden" id="authentic_request_hrs" name="authentic_request" value="' . Util::getSignature() . '" />');

    switch ($op) {
        case 'newfolder':
            $GLOBALS['page']->add($treeView->loadNewFolder());
        break;
        case 'renamefolder':
            $GLOBALS['page']->add($treeView->loadRenameFolder());
        break;
        case 'movefolder':
            $GLOBALS['page']->add($treeView->loadMoveFolder());
        break;
        case 'deletefolder':
            $GLOBALS['page']->add($treeView->loadDeleteFolder());
        break;
    }

    $GLOBALS['page']->add('</form>');
    $GLOBALS['page']->add('</div>');
}

function homerepo_itemproperties(&$treeView, &$arrayData, $idItem)
{
    //function loadFields( $arrayData, &$lo, $idLO ) {
    $lang = &FormaLanguage::createInstance('homerepo', 'lms');
    $langClassification = &FormaLanguage::createInstance('classification', 'lms');

    $GLOBALS['page']->add('<form id="manHomerepo" method="post"'
        . ' action="index.php?' . $_SERVER['QUERY_STRING'] . '"'
        . ' >' . "\n"
        . '<input type="hidden" id="authentic_request_hrs" name="authentic_request" value="' . Util::getSignature() . '" />');
    $GLOBALS['page']->add('<div class="std_block">');
    $GLOBALS['page']->add($treeView->printState());
    global $defaultLanguage;

    //including language
    //includeLang("classification");

    //finding category
    $reCategory = sql_query('
	SELECT idCategory, title 
	FROM %lms_coursecategory
	ORDER BY title');

    //searching languages

    /*$langl = dir('menu/language/');
    while($ele = $langl->read())
        if(ereg("lang-",$ele)) {
            $langArray[] = ereg_replace("lang-","",ereg_replace(".php","",$ele));
        }
    closedir($langl->handle);
    sort($langArray);*/
    $langArray = Forma::langManager()->getAllLangCode();

    if (!isset($_POST['idItem'])) {
        if ($idItem !== null) {
            $folder = $treeView->tdb->getFolderById($idItem);

            $GLOBALS['page']->add('<input type="hidden" name="idItem" id="idItem" value="' . $idItem . '" />');
            $title = $folder->otherValues[REPOFIELDTITLE];
            $arrayData['version'] = $folder->otherValues[REPOFIELDVERSION];
            $arrayData['difficult'] = $folder->otherValues[REPOFIELDDIFFICULT];
            $arrayData['language'] = $folder->otherValues[REPOFIELDLANGUAGE];
            $arrayData['resource'] = $folder->otherValues[REPOFIELDRESOURCE];
            $arrayData['objective'] = $folder->otherValues[REPOFIELDOBJECTIVE];
        }
    } else {
        $GLOBALS['page']->add('<input type="hidden" name="idItem" id="idItem" value="' . $idItem . '" />');
        $title = $_POST['title'];
    }

    // ==========================================================
    $GLOBALS['page']->add('<input type="hidden" name="title" id="title" value="' . $title . '" />');
    $GLOBALS['page']->add('<div class="ObjectForm">');

    $GLOBALS['page']->add('<span class="mainTitle">' . $langClassification->def('_CATEGORIZATION') . ' ' . $title . '</span><br /><br />');

    $GLOBALS['page']->add('</div>');
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
    $GLOBALS['page']->add('<div class="title">' . $langClassification->def('_VERSION') . '</div>'
                            . '<div class="content">');

    if (isset($arrayData['version'])) {
        $GLOBALS['page']->add('<input type="text" name="version" maxlength="8" size="10" value="' . $arrayData['version'] . '" />');
    } else {
        $GLOBALS['page']->add('<input type="text" name="version" maxlength="8" size="10" value="1.0" />');
    }

    $GLOBALS['page']->add('</div>'
        //-------------------------------------------------
        . '<div class="title">' . $langClassification->def('_DIFFICULTY') . '</div>'
        . '<div class="content">'
        . '<select name="difficult">');

    if (isset($arrayData['difficult'])) {
        $selDiff = $arrayData['difficult'];
        switch ($selDiff) {
            case '_DIFFICULT_VERYEASY': $selDiff = '1'; break;
            case '_DIFFICULT_EASY': $selDiff = '2'; break;
            case '_DIFFICULT_MEDIUM': $selDiff = '3'; break;
            case '_DIFFICULT_DIFFICULT': $selDiff = '4'; break;
            case '_DIFFICULT_VERYDIFFICULT': $selDiff = '5'; break;
        }
    } else {
        $selDiff = '';
    }

    $GLOBALS['page']->add(
                '<option value="1" ' . (($selDiff == '1') ? 'selected' : '') . ' >' . $langClassification->def('_DIFFICULT_VERYEASY') . '</option>'
                . '<option value="2" ' . (($selDiff == '2') ? 'selected' : '') . ' >' . $langClassification->def('_DIFFICULT_EASY') . '</option>'
                . '<option value="3" ' . (($selDiff == '3') ? 'selected' : '') . ' >' . $langClassification->def('_DIFFICULT_MEDIUM') . '</option>'
                . '<option value="4" ' . (($selDiff == '4') ? 'selected' : '') . ' >' . $langClassification->def('_DIFFICULT_DIFFICULT') . '</option>'
                . '<option value="5" ' . (($selDiff == '5') ? 'selected' : '') . ' >' . $langClassification->def('_DIFFICULT_VERYDIFFICULT') . '</option>'
            . '</select>'
            . '</div>'
        );
    //-------------------------------------------------
    /*.'<div class="title">'._DESCRIPTION.'</div>'
    .'<div class="content">'
    .'<div id="breakfloat">'
        .'<textarea id="description" name="description" rows="10" cols="75"></textarea></div>'
    .'</div>'*/
    //-------------------------------------------------
    $GLOBALS['page']->add('<div class="title">' . $langClassification->def('_LANGUAGE') . '</div>'
        . '<div class="content">'
        . '<select name="language">');
    if (isset($arrayData['language'])) {
        $selLang = $arrayData['language'];
    } else {
        $selLang = $defaultLanguage;
    }

    foreach ($langArray as $valueLang) {
        $GLOBALS['page']->add('<option value="' . $valueLang . '"');
        if ($valueLang == $selLang) {
            $GLOBALS['page']->add(' selected="selected"');
        }
        $GLOBALS['page']->add('>' . $valueLang . '</option>');
    }
    $GLOBALS['page']->add('</select>'
        . '</div>'
        //-------------------------------------------------
        . '<div class="title">' . $langClassification->def('_RESOURCE') . '</div>'
        . '<div class="content">');
    if (isset($arrayData['resource'])) {
        $GLOBALS['page']->add('<input type="text" name="resource" maxlength="255" size="60" value="' . $arrayData['resource'] . '" />');
    } else {
        $GLOBALS['page']->add('<input type="text" name="resource" maxlength="255" size="60" value="http://" />');
    }
    $GLOBALS['page']->add('</div>'
        //-------------------------------------------------
        . '<div class="title">' . $langClassification->def('_OBJECTIVE') . '</div>'
        . '<div class="content">');
    if (isset($arrayData['objective'])) {
        $GLOBALS['page']->add('<textarea name="objective" rows="6" cols="75">' . $arrayData['objective'] . '</textarea>');
    } else {
        $GLOBALS['page']->add('<textarea name="objective" rows="6" cols="75"></textarea>');
    }

    $GLOBALS['page']->add('<br />');
    $GLOBALS['page']->add('<img src="' . $treeView->_getSaveImage() . '" alt="' . $lang->def('_SAVE') . '" /> '
        . '<input type="submit" value="' . $lang->def('_SAVE') . '" class="LVAction"'
        . ' name="' . $treeView->_getOpSaveFile() . '" />');
    $GLOBALS['page']->add(' <img src="' . $treeView->_getCancelImage() . '" alt="' . $treeView->_getCancelAlt() . '" />'
        . '<input type="submit" class="LVAction" value="' . $treeView->_getCancelLabel() . '"'
        . ' name="' . $treeView->_getCancelId() . '" id="' . $treeView->_getCancelId() . '" />');
    $GLOBALS['page']->add('</div>');
    $GLOBALS['page']->add('</div>');
    $GLOBALS['page']->add('</form>');
}

function import(&$treeView)
{
    homerepo_import($treeView);
}
