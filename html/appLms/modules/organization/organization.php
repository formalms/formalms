<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orglib.php');

function organization(&$treeView)
{
    // contruct and initialize TreeView to manage organization
    /*$orgDb = new OrgDirDb();
    if( !checkPerm('lesson') ) {
        $treeView->tdb->setFilterVisibility( TRUE );
        $treeView->tdb->setFilterAccess( \FormaLms\lib\FormaUser::getCurrentUser()-> );
    }
$idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $treeView = new Org_TreeView($orgDb, $idCourse);

    $treeView->parsePositionData($_POST, $_POST, $_POST);*/

    // manage items addition
    if (isset($_GET['replay'])) {
        $treeView->op = 'playitem';
    } elseif (isset($_GET['itemdone'])) {
        $treeView->op = 'itemdone';
    } elseif (isset($_POST['_orgrules_save']) || isset($_POST['_repoproperties_save'])) {
        $treeView->tdb->modifyItem($_POST, false, true);
        $treeView->op = '';
    } elseif (isset($_POST['_orgrules_cancel']) || isset($_POST['_repoproperties_cancel'])) {
        $treeView->op = '';
    } elseif (
        FormaLms\lib\Get::req('op', DOTY_STRING, '') == 'org_select_sco' ||
        FormaLms\lib\Get::req('op', DOTY_STRING, '') == 'org_categorize_sco'
    ) {
        $treeView->op = FormaLms\lib\Get::req('op', DOTY_STRING, '');
        require_once dirname(__FILE__) . '/orgcategorize.php';
    }

    //echo $treeView->op;
    switch ($treeView->op) {
        case 'newfolder':
        case 'renamefolder':
        case 'movefolder':
        case 'deletefolder':
            organization_opfolder($treeView, $treeView->op);
            break;
        case 'import':
            organization_import($treeView);
            break;
        case 'org_categorize':
        case 'org_opcategorize':
            // organization_rules( $treeView, $treeView->opContextId );
            require_once dirname(__FILE__) . '/orgcategorize.php';
            organization_categorize($treeView, $treeView->opContextId);
            break;
        case 'org_select_sco':
            organization_select_sco();
            break;
        case 'org_categorize_sco':
            organization_categorize_sco();
            break;
        case 'org_properties':
        case 'org_opproperties':
            // organization_rules( $treeView, $treeView->opContextId );
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orgprop.php');
            organization_property($treeView, $treeView->opContextId);
            break;
        case 'org_opaccess':
        case 'org_access':
            require_once \FormaLms\lib\Forma::inc(_lms_ . '/modules/organization/orgprop.php');
            organization_access($treeView, $treeView->opContextId);
            break;
        case 'save':
            $treeView->tdb->modifyItem($_POST, false, true);
            organization_display($treeView);
            break;
        /*case 'playitem':
            organization_play( $treeView, $treeView->_getOpPlayEnd() );
        break;*/
        case 'treeview_error':
            organization_showerror($treeView);
            break;
        case 'itemdone':
        default:
            organization_display($treeView);
            break;
    }
}

function organization_display($treeView)
{
    $url = 'index.php?r=lms/lo/show';

    if (checkPerm('view', true, 'storage')) {
        $url = 'index.php?r=lms/lomanager/show';
    }

    if (!empty($_GET)) {
        foreach ($_GET as $name => $value) {
            $value = FormaLms\lib\Get::gReq($name, DOTY_STRING);
            if ($value) {
                $url .= '&' . $name . '=' . $value;
            }
        }
    }

    Util::jump_to($url);
}

function organization_opfolder(&$treeView, $op)
{
    global $modname;
    $GLOBALS['page']->add('<div class="std_block">', 'content');
    $GLOBALS['page']->add('<form id="orgnewfolder" method="post"'
        . ' action="index.php?modname=' . $modname . '&amp;op=organization"'
        . ' >' . "\n"
        . '<input type="hidden" id="authentic_request_org" name="authentic_request" value="' . Util::getSignature() . '" />', 'content');

    switch ($op) {
        case 'newfolder':
            $GLOBALS['page']->add($treeView->loadNewFolder(), 'content');
            break;
        case 'renamefolder':
            $GLOBALS['page']->add($treeView->loadRenameFolder(), 'content');
            break;
        case 'movefolder':
            $GLOBALS['page']->add($treeView->loadMoveFolder(), 'content');
            break;
        case 'deletefolder':
            $GLOBALS['page']->add($treeView->loadDeleteFolder(), 'content');
            break;
    }

    $GLOBALS['page']->add('</form>', 'content');
    $GLOBALS['page']->add('</div>', 'content');
}

function organization_import(&$treeView)
{
    $lang = &FormaLanguage::createInstance('organization', 'lms');
    global $modname, $op;
    require_once _lms_ . '/lib/lib.homerepo.php';

    // ----------------------------------
    $GLOBALS['page']->add('<div class="std_block">');
    $GLOBALS['page']->add('<form id="orgimport" method="post"'
        . ' action="index.php?modname=' . $modname . '&amp;op=import" >' . "\n"
        . '<input type="hidden" id="authentic_request_org" name="authentic_request" value="' . Util::getSignature() . '" />');
    // call pubrepo visualization to select items to import
    $GLOBALS['page']->add($treeView->printState());
    $treeViewPR = manHomerepo(false, true, null, true);

    $GLOBALS['page']->add('</form>');

    // ----------------------------------
    // then use an other form to submit back to organization op whit id of
    // selected items
    $GLOBALS['page']->add('<form id="orgimport" method="post"'
        . ' action="index.php?modname=' . $modname . '&amp;op=organization&amp;import=1" >' . "\n"
        . '<input type="hidden" id="authentic_request_org2" name="authentic_request" value="' . Util::getSignature() . '" />');

    $GLOBALS['page']->add($treeView->printState());
    $listView = $treeViewPR->getListView();
    $arrSelected = $listView->getIdSelectedItem();
    $GLOBALS['page']->add('<input type="hidden" value="'
        . addslashes(serialize($arrSelected))
        . '" name="idSelectedObjects">');
    $GLOBALS['page']->add('<input type="submit" value="' . $lang->def('_IMPORT') . '" name="import">');

    $GLOBALS['page']->add('</form>');
    $GLOBALS['page']->add('</div>');
}

function organization_play(&$treeView, $idItem)
{
    global $modname, $op;
    require_once _lms_ . '/lib/lib.param.php';
    $tdb = $treeView->getTreeDb();
    $item = $tdb->getFolderById($idItem);
    $values = $item->otherValues;
    $objectType = $values[REPOFIELDOBJECTTYPE];
    $idResource = $values[REPOFIELDIDRESOURCE];
    $idParams = $values[ORGFIELDIDPARAM];

    $param = $treeView->printState(false);
    $back_url = 'index.php?modname=' . $modname . '&op=organization&itemdone=' . $idItem;

    $lo = createLO($objectType,
        $idResource);

    $lo->play($idResource, $idParams, $back_url);
}

function import()
{
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $orgDb = new OrgDirDb();
    $treeView = new Org_TreeView($orgDb, $idCourse);
    $treeView->parsePositionData($_POST, $_POST, $_POST);

    organization_import($treeView);
}

function edit()
{
    $idCourse = \FormaLms\lib\Session\SessionManager::getInstance()->getSession()->get('idCourse');
    $orgDb = new OrgDirDb();
    $treeView = new Org_TreeView($orgDb, $idCourse);
    $treeView->parsePositionData($_POST, $_POST, $_POST);

    organization_properties($treeView);
}

function organization_showerror(&$treeView)
{
    $lang = &FormaLanguage::createInstance('organization', 'lms');
    global $modname, $op;
    $GLOBALS['page']->add('<form id="orgshow" method="post"'
        . ' action="index.php?modname=' . $modname . '&amp;op=organization"'
        . ' >' . "\n"
        . '<input type="hidden" id="authentic_request_org" name="authentic_request" value="' . Util::getSignature() . '" />');
    $GLOBALS['page']->add('<div class="std_block">');
    if ($treeView->error == TVERR_MOVEONDESCENDANT) {
        $GLOBALS['page']->add($lang->def('_ERROR_MOVEONDESCENDANT'));
    }
    $GLOBALS['page']->add(' <img src="' . $treeView->_getCancelImage() . '" alt="' . $treeView->_getCancelAlt() . '" />'
        . '<input type="submit" class="LVAction" value="' . $treeView->_getCancelLabel() . '"'
        . ' name="' . $treeView->_getCancelId() . '" id="' . $treeView->_getCancelId() . '" />');
    $GLOBALS['page']->add('</div>');
    $GLOBALS['page']->add('</form>');
}

/*switch( $op ) {
    case "organization":
    case "display":
        organization();
    break;
    case "import":
        import();
    break;
}*/
