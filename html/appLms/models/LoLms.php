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

    const ORGDIRDB = 'ORGDIRDB';
    const REPODIRDB = 'REPODIRDB';
    const HOMEREPODIRDB = 'HOMEREPODIRDB';

    private $tdb = null;

    public function LoLMS() {
        $this->setTdb();
    }

    public function setTdb($type = LoLms::ORGDIRDB, $idCourse = false) {
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
    }

    public function getLearningObjects($rootId) {
        $tree_view = new Org_TreeView($this->tdb, 'organization' );
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
        $tree_view = new Org_TreeView($this->tdb, 'organization' );
        return $tree_view->getCurrentState($idFolder);
    }

    public function deleteFolder($id) {
        $folder = $this->tdb->getFolderById( (string)$id);
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

}
