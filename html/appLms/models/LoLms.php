<?php defined("IN_FORMA") or die('Direct access is forbidden.');


/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */
require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
require_once( Forma::inc( _lms_.'/modules/homerepo/homerepo.php') );

class LoLms extends Model {

    const ORGDIRDB = 'organization';
    const REPODIRDB = 'pubrepo';
    const HOMEREPODIRDB = 'homerepo';

    const STORAGE_HOMEREPODIRDB = 'storage_home';
    const STORAGE_ORGDIRDB = 'storage_course';
    const STORAGE_REPODIRDB = 'storage_pubrepo';

    const STORAGE_TABS = array(
        self::HOMEREPODIRDB => self::STORAGE_HOMEREPODIRDB,
        self::ORGDIRDB => self::STORAGE_ORGDIRDB,
        self::REPODIRDB => self::STORAGE_REPODIRDB,
    );

    private $tdb = null;
    private $treeView = null;

    public function __construct() {
        $this->setTdb();
    }

    public function setTdb($type = LoLms::ORGDIRDB, $idCourse = false) {
        $this->treeView = $type;
        switch($type) {
            case LoLms::ORGDIRDB: 
                $this->tdb = new OrgDirDb($idCourse);
                break;
            case LoLms::REPODIRDB: 
                $this->tdb = new RepoDirDb( $GLOBALS['prefix_lms'].'_repo', getLogUserId());
                break;
            case LoLms::HOMEREPODIRDB: 
                $this->tdb = new HomerepoDirDb( $GLOBALS['prefix_lms'] .'_homerepo', getLogUserId());
                break;
            default:
                throw new Error('Missing directory type in LoLms constructor');
        }
        return $this->tdb;
    }

    public function getLearningObjects($rootId) {
        $tree_view = new Org_TreeView($this->tdb, $this->treeView );
        $tree_view->creatingObjectType = $_REQUEST['lo_type'];
        $tree_view->selectedFolder = $_REQUEST['parentId'];
        $tree_view->parsePositionData($_REQUEST, $_REQUEST, $_REQUEST);
        return $tree_view->getChildrensDataById($rootId);
    }

    public function getFolders($collection_id, $id = 0) {
        $learning_objects = $this->getLearningObjects($id);
        if(!isUserCourseSubcribed(getLogUserId(), $collection_id)) {
            foreach($learning_objects as $index => $lo){
                if(!$lo['isPublic']){
                    $learning_objects[$index]['isPrerequisitesSatisfied'] = false;
                }
            }
        }
        return $learning_objects;
    }

    public function getCurrentState($idFolder = 0) {
        $tree_view = new Org_TreeView($this->tdb, $this->treeView);
        return $tree_view->getCurrentState($idFolder);
    }

    public function setCurrentTab($type) {
        if (array_key_exists($type, self::STORAGE_TABS)) {
            $_SESSION['storage'] = serialize(['tabview_storage_status' => self::STORAGE_TABS[$type]]);

            return $_SESSION['storage'];
        }
        return false;
    }

    public function deleteFolder($id) {
        $folder = $this->tdb->getFolderById( (string)$id);
        $lo = createLO( $folder->otherValues[REPOFIELDOBJECTTYPE] );
        if ($lo) {
            // delete categorized resource
            require_once(_lms_.'/lib/lib.kbres.php');
            $kbres =new KbRes();
            $kbres->deleteResourceFromItem(
                $folder->otherValues[REPOFIELDIDRESOURCE],
                $folder->otherValues[REPOFIELDOBJECTTYPE],
                'course_lo'
            );
            // ---------------------------
            $lo->del( $folder->otherValues[REPOFIELDIDRESOURCE] );
        }
        return $this->tdb->_deleteTree( $folder );
    }

    public function renameFolder($id, $newName) {
        $folder = $this->tdb->getFolderById( (string)$id);
        return $this->tdb->renameFolder( $folder, $newName );
    }

    public function moveFolder($id, $newParentId) {
        $folder = $this->tdb->getFolderById( (string)$id);
        $newParent = $this->tdb->getFolderById( (string)$newParentId);
        return $folder->move( $newParent );
    }

    public function reorder($idToMove, $newParent, $newOrder) {
        $folder = $this->tdb->getFolderById( (string)$idToMove);
        return $folder->reorder($newParent, $newOrder );
    }

    public function copy($id, $fromType) {
        require_once( $GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('crepo',true);
        $folder = $this->tdb->getFolderById( (string)$id );
        $saveData = array(
            'repo' => $fromType,
            'id' => $id,
            'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
            'name' => $folder->otherValues[REPOFIELDTITLE],
            'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE]
        );
        $saveObj->save( $saveName, $saveData );
        return true;
    }

    public function paste($folderId) {
        require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
        $saveObj = new Session_Save();
        if( $saveObj->nameExists("crepo") ) {
            $saveData =& $saveObj->load("crepo");

            $lo = createLO( $saveData['objectType'] );
            $idResource = $lo->copy((int)$saveData['idResource']);
            if( $idResource != 0 ) { 
                $this->tdb->addItem( $folderId, 
                    $saveData['name'],
                    $saveData['objectType'], 
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
                    date("Y-m-d H:i:s") 
                );
                return true;
            }
        }
        return false;
    }

    public function addFolderById($selectedNode, $folderName, $idCourse) {
        return $this->tdb->addFolderById($selectedNode, $folderName, $idCourse);
    }

}
