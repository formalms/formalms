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

    private $tdb = null;
    private $treeView = null;

    public function __construct() {
        $this->setTdb();
    }

    public function setTdb($idCourse = false) {
        $this->tdb = new OrgDirDb($idCourse);
        $this->treeView = new Org_TreeView($this->tdb, "organization");
        return $this->tdb;
    }

    public function getLearningObjects($rootId) {
        return $this->treeView->getChildrensDataById($rootId);
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
        return $this->treeView->getCurrentState($idFolder);
    }

    public function getFolderTree() {
        $root_folder = $this->treeView->tdb->getFolderById(0);
        $tree = [$root_folder->id => []];
        $tree = $this->treeView->getFolderTree($tree);
        return $tree;
    }
}
