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

class Module_Pubrepo extends LmsModule
{
    public $treeView = null;
    public $repoDb = null;
    public $select_destination = false;

    //class constructor
    public function __construct($module_name = '')
    {
        parent::__construct('pubrepo');
    }

    public function loadHeader()
    {
        //EFFECTS: write in standard output extra header information
        global $op;

        $GLOBALS['page']->setWorkingZone('page_head');
        $GLOBALS['page']->add('<link href="' . getPathTemplate() . 'style/base-old-treeview.css" rel="stylesheet" type="text/css" />');

        return;
    }

    public function initialize()
    {
        require_once $GLOBALS['where_lms'] . '/modules/' . $this->module_name . '/' . $this->module_name . '.php';
        $ready = false;
        $this->lang = &DoceboLanguage::createInstance('homerepo', 'lms');
        if (isset($_GET['spr']) && false) {
            // reload from previously saved session
            require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
            $saveObj = new Session_Save();
            $saveName = $_GET['spr'];
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
            $this->repoDb = new RepoDirDb($GLOBALS['prefix_lms'] . '_repo', getLogUserId());

            /* TODO: ACL
            if( !funAccess('pubrepoedit','MOD', TRUE, 'pubrepo' ) ) {
                $repoDb->setFilterVisibility( TRUE );
                $repoDb->setFilterAccess( getLogUserId() );
            }*/

            $this->treeView = new RepoTreeView($this->repoDb, 'pubrepo', Lang::t('_PUBREPOROOTNAME', 'storage', 'lms'));
            $this->treeView->mod_name = 'pubrepo';
            require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
            $saveObj = new Session_Save();
            $saveName = 'pubrepo' . getLogUserId();
            if ($saveObj->nameExists($saveName)) {
                $this->treeView->setState($saveObj->load($saveName));
                $ready = true;
                $saveObj->delete($saveName);
                //$this->treeView->extendedParsing( $_POST, $_POST, $_POST);
                $this->treeView->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
                $this->treeView->refreshTree();
            } else {
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
                return true;
        }

        return false;
    }

    public function getExtraTop()
    {
        global $modname;
        if ($this->isFindingDestination()) {
            require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
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
                        . '<input type="hidden" id="authentic_request_prm" name="authentic_request" value="' . Util::getSignature() . '" />'
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

    public function loadBody()
    {
        global $op, $modname
        ;
        if ($this->treeView === null) {
            $this->initialize();
        }

        switch ($this->treeView->op) {
            case 'import':
                import($this->treeView);
            break;
            case 'createLO':
                global $modname;
                // save state
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();

                $saveName = $saveObj->getName('pubrepo' . getLogUserId(), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $GLOBALS['page']->add($this->treeView->LOSelector($modname, 'index.php?r=lms/lomanagerrepo/completeAction&op=display&spr=' . $saveName . '&'
                            . $this->treeView->_getOpCreateLOEnd() . '=1'),
                            'content');
            break;
            case 'createLOSel':
                global $modname;
                // save state
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('pubrepo' . getLogUserId(), true);
                $saveObj->save($saveName, $this->treeView->getState());

                // start learning object creation
                $lo = createLO($_REQUEST['radiolo']);
                $lo->create('index.php?r=lms/lomanagerrepo/completeAction&op=display&spr=' . $saveName . '&'
                            . $this->treeView->_getOpCreateLOEnd() . '=1');
            break;
            case 'editLO':
                global $modname;
                // save state
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('pubrepo' . getLogUserId(), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $folder = $this->repoDb->getFolderById($this->treeView->getSelectedFolderId());
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lomanagerrepo/completeAction&op=display&spr=' . $saveName . '&'
                            . $this->treeView->_getOpEditLOEnd() . '=1');
            break;
            case 'playitem':
                global $modname;
                // save state
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('pubrepo' . getLogUserId(), true);
                $saveObj->save($saveName, $this->treeView->getState());

                $folder = $this->repoDb->getFolderById($this->treeView->getItemToPlay());
                $lo = createLO($folder->otherValues[REPOFIELDOBJECTTYPE]);
                $idItem = $folder->otherValues[REPOFIELDIDRESOURCE];

                $back_url = 'index.php?r=lms/lomanagerrepo/completeAction&op=pubrepo&spr=' . $saveName . '&'
                            . $this->treeView->_getOpPlayEnd()
                            . '=' . $folder->id;

                $lo->play($idItem,
                            null,
                            $back_url);
            break;
            case 'copyLOSel':
                $GLOBALS['page']->add($this->treeView->load());
            break;
            case 'copyLOEndOk':
            case 'copyLOEndCancel':
                global $modname;
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();
                $saveName = $_GET['crepo'];
                if ($saveObj->nameExists($saveName)) {
                    $saveData = &$saveObj->load($saveName);
                    $saveObj->delete($saveName);
                    Util::jump_to('index.php?r=lms/lomanagerrepo/completeAction&op=' . $saveData['repo']);
                }
                Util::jump_to('index.php?r=lms/lomanagerrepo/completeAction&op=display');
            break;
            case 'copyLO':
                global $modname;
                // save state
                require_once $GLOBALS['where_framework'] . '/lib/lib.sessionsave.php';
                $saveObj = new Session_Save();
                $saveName = $saveObj->getName('crepo', true);
                $folder = $this->treeView->tdb->getFolderById($this->treeView->selectedFolder);
                $saveData = ['repo' => 'pubrepo',
                                    'id' => $this->treeView->getSelectedFolderId(),
                                    'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
                                    'name' => $folder->getFolderName(),
                                    'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE],
                ];
                $saveObj->save($saveName, $saveData);
                Util::jump_to('index.php?r=lms/lomanagerrepo/completeAction&op=display&crepo=' . $saveName . '&'
                            . $this->treeView->_getOpCopyLOSel() . '=1');
                            // no break
            case 'createLOEnd':
                // insertion managed by extendParsing
            case 'display' :
            case 'pubrepo' :
            default:
                /*$GLOBALS['page']->addStart(
                    getTitleArea(Lang::t('_PUBREPO', 'pubrepo', 'lms'), 'pubrepo')
                    .'<div class="std_block">', 'content');
                $GLOBALS['page']->addEnd('</div>', 'content');
                if( isset($_SESSION['last_error']) )
                    if( $_SESSION['last_error'] != "" ) {
                        $GLOBALS['page']->add( $_SESSION['last_error'], 'content' );
                        unset( $_SESSION['last_error'] );
                    }*/
                pubrepo($this->treeView);
            break;
        }
    }
}
