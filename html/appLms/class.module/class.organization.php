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

class Module_Organization extends LmsModule
{
    public $treeView = null;
    public $repoDb = null;
    public $select_destination = false;

    //class constructor
    public function __construct($module_name = '')
    {
        parent::__construct('organization');
    }

    public function loadHeader()
    {
        //EFFECTS: write in standard output extra header information
        global $op;
        $GLOBALS['page']->setWorkingZone('page_head');
        $GLOBALS['page']->add('<link href="' . getPathTemplate() . 'style/base-old-treeview.css" rel="stylesheet" type="text/css" />');
        /*$GLOBALS['page']->add( '<link href="'.getPathTemplate().'style/style_organizations.css" rel="stylesheet" type="text/css" />');*/
        return;
    }

    public function initialize()
    {
        require_once Forma::inc(_lms_ . "/modules/$this->module_name/$this->module_name.php");
        $ready = false;
        $this->lang = &DoceboLanguage::createInstance('organization', 'lms');

        if (isset($_GET['sor']) && false) {
            // reload from previously saved session
            require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
            $saveObj = new Session_Save();
            $saveName = $_GET['sor'];
            if ($saveObj->nameExists($saveName)) {
                $this->treeView = &$saveObj->load($saveName);
                $this->repoDb = &$this->treeView->tdb;
                $ready = true;
                $saveObj->delete($saveName);
                $this->treeView->extendedParsing($_REQUEST, $_REQUEST, $_REQUEST);
                $this->treeView->refreshTree();
            }
        }

        if (!$ready) {
            // contruct and initialize TreeView to manage public repository
            $id_course = \FormaLms\lib\Get::req('courseid', DOTY_INT, $this->session->get('idCourse'));
            $this->repoDb = new OrgDirDb($id_course);

            /* TODO: ACL */
            if (!checkPerm('lesson', true, 'storage')) {
                $this->repoDb->setFilterVisibility(true);
                $this->repoDb->setFilterAccess(Docebo::user()->getArrSt());
            }

            $this->treeView = new Org_TreeView($this->repoDb, 'organization', $this->lang->def('_ORGROOTNAME', 'organization'));
            $this->treeView->mod_name = 'organization';
            $this->treeView->setLanguage($this->lang);

            require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
            $saveObj = new Session_Save();
            $saveName = 'organization' . $id_course;
            if ($saveObj->nameExists($saveName)) {
                $this->treeView->setState($saveObj->load($saveName));
                $ready = true;
                $saveObj->delete($saveName);
                //$this->treeView->extendedParsing( $_POST, $_POST, $_POST);
                $this->treeView->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
                $this->treeView->refreshTree();
            } else {
                //$this->treeView->extendedParsing( $_POST, $_POST, $_POST);
                $this->treeView->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
            }
        }
        if ($this->select_destination) {
            $this->treeView->setOption(REPOOPTSHOWONLYFOLDER, true);
        }
    }

    public function isSuperActive()
    {
        if ($this->treeView === null) {
            $this->initialize();
        }
        if ($this->treeView->op == 'movefolder') {
            return true;
        }

        return false;
    }

    public function isFindingDestination()
    {
        return $this->treeView->op == 'copyLOSel';
    }

    public function getUrlParams()
    {
        if ($this->isFindingDestination()) {
            return '&amp;crepo=' . $_GET['crepo'] . '&amp;'
                . $this->treeView->_getOpCopyLOSel() . '=1"';
        }

        return '';
    }

    public function hideTab()
    {
        switch ($this->treeView->op) {
            case 'createLO':
            case 'createLOSel':
            case 'editLO':
            case 'playitem':
            case 'org_categorize':
            case 'org_opcategorize':
            case 'org_select_sco':
            case 'org_categorize_sco':
            case 'org_opproperties':
            case 'org_properties':
            case 'org_opaccess':
            case 'org_access':
                return true;
        }

        $op = FormaLms\lib\Get::req('op', DOTY_STRING, '');
        switch ($op) {
            case 'org_select_sco':
            case 'org_categorize_sco':
                return true;
        }

        return false;
    }

    public function getExtraTop()
    {
        global $modname;
        if ($this->isFindingDestination()) {
            require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
            $saveObj = new Session_Save();
            $saveName = $_GET['crepo'];
            if ($saveObj->nameExists($saveName)) {
                $saveData = &$saveObj->load($saveName);

                return '<div class="std_block">'
                    . '<form id="homereposhow" method="post"'
                    . ' action="index.php?modname=' . $modname
                    . '&amp;op=display&amp;crepo=' . $_GET['crepo'] . '&amp;'
                    . $this->treeView->_getOpCopyLOSel() . '=1"'
                    . ' >' . "\n"
                    . '<input type="hidden" id="authentic_request_org" name="authentic_request" value="' . Util::getSignature() . '" />'
                    . $this->lang->def('_REPOSELECTDESTINATION')
                    . ' <img src="' . getPathImage() . 'lobject/' . $saveData['objectType']
                    . '.gif" alt="' . $saveData['objectType']
                    . '" title="' . $saveData['objectType'] . '"/>'
                    . $saveData['name'];
            }
        }

        return '';
    }

    public function getExtraBottom()
    {
        global $modname;
        if ($this->isFindingDestination()) {
            return '<img src="' . $this->treeView->_getCopyImage() . '" alt="' . $this->lang->def('_REPOPASTELO') . '" /> '
                . '<input type="submit" value="' . $this->lang->def('_REPOPASTELO') . '" class="LVAction"'
                . ' name="' . $this->treeView->_getOpCopyLOEndOk() . '" />'
                . ' <img src="' . $this->treeView->_getCancelImage() . '" alt="' . $this->treeView->_getCancelAlt() . '" />'
                . '<input type="submit" class="LVAction" value="' . $this->treeView->_getCancelLabel() . '"'
                . ' name="' . $this->treeView->_getOpCopyLOEndCancel() . '" id="' . $this->treeView->_getOpCopyLOEndCancel() . '" />'
                . '</form>'
                . '</div>';
        }

        return '';
    }

    public function setOptions($select_destination)
    {
        $this->select_destionation = $select_destination;
        if ($this->treeView !== null) {
            $this->treeView->setOption(REPOOPTSHOWONLYFOLDER, true);
        }
    }

    public function hideLateralMenu()
    {
        if ($this->session->has('test_assessment')) {
            return true;
        }
        if ($this->session->has('direct_play')) {
            return true;
        }
        return false;
    }

    public function loadBody()
    {
        global $op, $modname;

        if ($this->treeView === null) {
            $this->initialize();
        }

        // tree indipendent play lo -----------------------------------------------

        if ($GLOBALS['op'] == 'scorm_track') {
            require_once Forma::inc(_lms_ . '/modules/organization/orgresults.php');
            $user = FormaLms\lib\Get::req('id_user', DOTY_INT, false);
            $org = FormaLms\lib\Get::req('id_org', DOTY_INT, false);
            getTrackingTable($user, $org);

            return;
        }

        if ($GLOBALS['op'] == 'scorm_history') {
            require_once Forma::inc(_lms_ . '/modules/organization/orgresults.php');
            $user = FormaLms\lib\Get::req('id_user', DOTY_INT, false);
            $obj = FormaLms\lib\Get::req('id_obj', DOTY_INT, false);
            getHistoryTable($user, $obj);

            return;
        }

        if ($GLOBALS['op'] == 'scorm_interactions') {
            require_once Forma::inc(_lms_ . '/modules/organization/orgresults.php'); //__FILE__.'/appLms/modules/organization/orgresults.php');
            $user = FormaLms\lib\Get::req('id_user', DOTY_INT, false);
            $track = FormaLms\lib\Get::req('id_track', DOTY_INT, false);
            getInteractionsTable($user, $track);

            return;
        }

        if ($GLOBALS['op'] === 'test_track') {
            require_once Forma::inc(_lms_ . '/modules/organization/orgresults.php');
            $user = FormaLms\lib\Get::req('id_user', DOTY_INT, false);
            $org = FormaLms\lib\Get::req('id_org', DOTY_INT, false);
            getCompilationTable($user, $org);

            return;
        }

        if ($GLOBALS['op'] === 'track_details') {
            $type = FormaLms\lib\Get::req('type', DOTY_STRING);
            $user = FormaLms\lib\Get::req('id_user', DOTY_INT, false);
            $org = FormaLms\lib\Get::req('id_org', DOTY_INT, false);

            if ($lo_class = createLO($type)) {
                $lo_class->trackDetails($user, $org);
            }

            return;
        }

        if ($GLOBALS['op'] == 'custom_playitem') {
            require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
            $saveObj = new Session_Save();
            $saveName = $saveObj->getName('organization' . $this->session->get('idCourse'), true);
            $saveObj->save($saveName, $this->treeView->getState());

            $id_item = FormaLms\lib\Get::req('id_item', DOTY_INT, 0);
            $folder = $this->repoDb->getFolderById($id_item);
            $idItem = $folder->otherValues[REPOFIELDIDRESOURCE];
            $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE], $idItem);
            if (isset($_GET['edit']) && $_GET['edit']) {
                $back_url = 'index.php?r=lms/lomanager/show';
            } else {
                $back_url = 'index.php?r=lms/lo/show';
            }
            require_once Forma::inc(_lms_ . '/class.module/track.object.php');

            //#11944 ,  can view LO by teacher
            if (Track_Object::isPrerequisitesSatisfied(
                    $folder->otherValues[ORGFIELDPREREQUISITES],
                    getLogUserId()) || (isset($_GET['edit']) && $_GET['edit'])) {
                $lo->play($idItem,
                    $folder->otherValues[ORGFIELDIDPARAM],
                    $back_url);
            } else {
                exit("You don't have permissions");
            }

            return;
        }

        // tree indipendent play end --------------------------------------------
        if ($GLOBALS['op'] == 'custom_enditem') {
            $lang = &DoceboLanguage::createInstance('organization', 'lms');

            require_once Forma::inc(_lms_ . '/class.module/track.object.php');
            require_once Forma::inc(_lms_ . '/lib/lib.stats.php');

            $id_item = importVar('id_item');

            $folder = $this->repoDb->getFolderById($id_item);

            $objectType = $folder->otherValues[REPOFIELDOBJECTTYPE];
            $idResource = $folder->otherValues[REPOFIELDIDRESOURCE];
            $idParams = $folder->otherValues[ORGFIELDIDPARAM];
            $isTerminator = $folder->otherValues[ORGFIELDISTERMINATOR];
            //With this direct_play courses was set as finished if is passed the object automatically without needing to set it as finish course object
            $idCourse = $this->session->get('idCourse');

            if ($isTerminator) {
                require_once Forma::inc(_lms_ . '/lib/lib.course.php');
                $idTrack = Track_Object::getIdTrackFromCommon($id_item, getLogUserId());
                $track = createLOTrack($idTrack, $objectType, $idResource, $idParams, '');
                if ($track->getStatus() == 'completed' || $track->getStatus() == 'passed') {
                    if (!saveTrackStatusChange((int)getLogUserId(), (int)$idCourse, _CUS_END)) {
                        errorCommunication($lang->def('_OPERATION_FAILURE'));

                        return;
                    }
                }
            }

            if (FormaLms\lib\Get::req('edit', DOTY_INT, 0) > 0) {
                Util::jump_to('index.php?modname=storage&op=display');
            }

            if ($this->session->has('direct_play')) {
                $from = FormaLms\lib\Get::req('from', DOTY_ALPHANUM, '');
                //reset cache for the notication
                UpdatesLms::resetCache();

                // autoplay with more than an object and the first one is completed

                require_once Forma::inc(_lms_ . '/lib/lib.orgchart.php');
                $orgman = new OrganizationManagement($this->session->get('idCourse'));
                $first_lo = &$orgman->getInfoWhereType(false, $this->session->get('idCourse'));

                if (count($first_lo) >= 2) {
                    // if we have more than an object we need to play the first one until it's completed
                    $obj = array_shift($first_lo);
                    $query = 'SELECT status FROM %lms_commontrack WHERE idReference = ' . (int)$obj['id_org'] . ' AND idUser = ' . (int)Docebo::user()->getId();
                    list($status) = sql_fetch_row(sql_query($query));
                    if ($status == 'completed' || $status == 'passed') {
                        // we have more then one object and the first one is complete, we can go to the course first page
                        $this->session->remove('direct_play');
                        $this->session->save();
                        $first_page = firstPage();
                        $this->session->set('current_main_menu', $first_page['idMain']);
                        $this->session->set('sel_module_id', $first_page['idModule']);
                        $this->session->save();
                        Util::jump_to('index.php?modname=' . $first_page['modulename'] . '&op=' . $first_page['op'] . '&id_module_sel=' . $first_page['idModule']);
                    }
                }
                // back and out of the course
                switch ($from) {
                    case 'catalogue':
                        Util::jump_to('index.php?r=lms/catalog/show&sop=unregistercourse');
                        break;
                    case 'lo_plan':
                        Util::jump_to('index.php?r=lms/mycourses/show&mycourses_tab=tb_elearning&sop=unregistercourse');
                        break;
                    case 'lo_history':
                        Util::jump_to('index.php?r=lms/mycourses/show&mycourses_tab=tb_elearning&current_tab=lo_history&sop=unregistercourse');
                        break;
                    default:
                        Util::jump_to('index.php?r=lms/mycourses/show&mycourses_tab=tb_elearning&sop=unregistercourse');
                        break;
                }
            }
        }

        //--- direct edit item -----------------------------------------------------
        if ($GLOBALS['op'] == 'direct_edit_item') {
            $id_item = FormaLms\lib\Get::req('id_item', DOTY_INT, 0);
            $this->treeView->op = 'editLO';
        }

        // normal tree function --------------------------------------------

        $this->treeView->playOnly = ($modname == 'organization');

        switch ($this->treeView->op) {
            case 'import':
                import($this->treeView);
                break;
            case 'createLO':
                global $modname;
                // save state

                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('organization' . $this->session->get('idCourse'), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $GLOBALS['page']->add($this->treeView->LOSelector($modname, 'index.php?r=lms/lomanagerorganization/completeAction&op=display&sor=' . $saveName . '&'
                    . $this->treeView->_getOpCreateLOEnd() . '=1'), 'content');
                break;
            case 'createLOSel':
                global $modname;
                // save state
                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('organization' . $this->session->get('idCourse'), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $parentId = (int)$_REQUEST['treeview_selected_organization'];
                // start learning object creation
                $lo = createLO($_REQUEST['radiolo']);

                if ($lo !== false) {
                    $lo->create('index.php?r=lms/lomanagerorganization/completeAction&op=display&sor=' . $saveName . '&'
                        . $this->treeView->_getOpCreateLOEnd() . '=1');
                } else {
                    $GLOBALS['page']->addStart(
                        getTitleArea($this->lang->def('_ORGANIZATION', 'organization', 'lms'), 'organization')
                        . '<div class="std_block">', 'content');
                    $GLOBALS['page']->addEnd('</div>', 'content');
                    if (Forma::errorsExists()) {
  
                        UIFeedback::error(Forma::getFormattedErrors(true));
                    }
                    organization($this->treeView);
                }
                break;
            case 'editLO':
                global $modname;
                // save state
                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('organization' . $this->session->get('idCourse'), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $folder = $this->repoDb->getFolderById($this->treeView->getSelectedFolderId());
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lo/organization&id_course=1');
                break;
            case 'playitem':
                global $modname;
                // save state
                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('organization' . $this->session->get('idCourse'), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $folder = $this->repoDb->getFolderById($this->treeView->getItemToPlay());

                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);

                $idItem = $folder->otherValues[REPOFIELDIDRESOURCE];
                $back_url = 'index.php?r=lms/lomanagerorganization/completeAction&op=organization&sor=' . $saveName . '&'
                    . $this->treeView->_getOpPlayEnd()
                    . '=' . $folder->id;

                $lo->play($idItem,
                    $folder->otherValues[ORGFIELDIDPARAM],
                    $back_url);
                break;
            case 'copyLOSel':
                $GLOBALS['page']->add($this->treeView->load());
                break;
            case 'createLOEnd':
            case 'copyLOEndOk':
            case 'copyLOEndCancel':
                global $modname;
                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $_GET['crepo'];
                if ($saveObj->nameExists($saveName)) {
                    $saveData = &$saveObj->load($saveName);
                    $saveObj->delete($saveName);
                    Util::jump_to(' index.php?r=lms/lomanagerorganization/completeAction&op=' . $saveData['repo']);
                }
                Util::jump_to('index.php?r=lms/lomanagerorganization/completeAction&op=display');
                break;
            case 'copyLO':
                global $modname;
                // save state
                require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php');
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('crepo', true);
                $folder = $this->treeView->tdb->getFolderById($this->treeView->selectedFolder);
                $saveData = ['repo' => 'organization',
                    'id' => $this->treeView->getSelectedFolderId(),
                    'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
                    'name' => $folder->otherValues[REPOFIELDTITLE],
                    'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE],
                ];
                $saveObj->save($saveName, $saveData);
                Util::jump_to('index.php?r=lms/lomanagerorganization/completeAction&op=display&crepo=' . $saveName . '&'
                    . $this->treeView->_getOpCopyLOSel() . '=1');
            // no break
            case 'display' :
            case 'organization' :
            default:
                if (Forma::errorsExists()) {
               
                    UIFeedback::error(Forma::getFormattedErrors(true));
                }
                organization($this->treeView);

                break;
        }
    }

    public function useExtraMenu()
    {
        return false;
    }

    public function loadExtraMenu()
    {
    }
}

//create class istance
