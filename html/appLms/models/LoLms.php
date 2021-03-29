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

    static const ORGDIRDB = 'ORGDIRDB';
    static const REPODIRDB = 'REPODIRDB';
    static const HOMEREPODIRDB = 'HOMEREPODIRDB';

    function __construct($type = LoLms::ORGDIRDB)
    {
        switch($type) {
            case LoLms::ORGDIRDB: 
                $this->DirDb = "($this->DirDb)";
                break;
            case LoLms::REPODIRDB: 
                $this->DirDb = "RepoDirDb";
                break;
            case LoLms::HOMEREPODIRDB: 
                $this->DirDb = "HomerepoDirDb";
                break;
            default:
                throw new Error('Missing directory type in LoLms constructor');
        }
    }
    
    public function getLearningObjects($idCourse = FALSE, $rootId = 0, $offset = null, $limit = null, $filters = array(), $groupBy = null, $selectFunction = null, $orderBy = null) {
        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new ($this->DirDb)($idCourse, $filters, $offset, $limit, $groupBy, $selectFunction, $orderBy);

        $tree_view = new Org_TreeView($tdb, 'organization' );
        return $tree_view->getChildrensDataById($rootId);
    }

    public function getFolders($collection_id, $id = 0) {
        $learning_objects = $this->getLearningObjects($collection_id, $id);
        if(!isUserCourseSubcribed(getLogUserId(), $collection_id)) {
            foreach($learning_objects as $index => $lo){
                if(!$lo['isPublic']){
                    $learning_objects[$index]['isPrerequisitesSatisfied'] = false;
                }
            }
        }
        return $learning_objects;
    }

    public function getCurrentState($idCourse,$idFolder = 0){

        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new ($this->DirDb)($idCourse);

        $tree_view = new Org_TreeView($tdb, 'organization' );
        return $tree_view->getCurrentState($idFolder);
    }

    public function deleteFolder($idCourse, $id, $type) {
        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        
        
        $tdb = new ($this->DirDb)($idCourse, $filters, $offset, $limit, $groupBy, $selectFunction, $orderBy);
        
        //repo db
        //$tdb = new RepoDirDb( $GLOBALS['prefix_lms'].'_repo', getLogUserId());

        //home db
        //$tdb = new HomerepoDirDb( $GLOBALS['prefix_lms'] .'_homerepo', getLogUserId());

        $folder = $tdb->getFolderById( (string)$id);
        
        return $tdb->_deleteTree( $folder );
        
    }

    public function renameFolder($idCourse, $id, $newName) {
        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new ($this->DirDb)($idCourse, $filters, $offset, $limit, $groupBy, $selectFunction, $orderBy);
        
        //repo db
        //$tdb = new RepoDirDb( $GLOBALS['prefix_lms'].'_repo', getLogUserId());

        //home db
        //$tdb = new HomerepoDirDb( $GLOBALS['prefix_lms'] .'_homerepo', getLogUserId());

        $folder = $tdb->getFolderById( (string)$id);
        
        return $tdb->renameFolder( $folder, $newName );
        
    }

    public function moveFolder($idCourse, $id, $newParentId) {
        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new ($this->DirDb)($idCourse, $filters, $offset, $limit, $groupBy, $selectFunction, $orderBy);
        
        //repo db
        //$tdb = new RepoDirDb( $GLOBALS['prefix_lms'].'_repo', getLogUserId());

        //home db
        //$tdb = new HomerepoDirDb( $GLOBALS['prefix_lms'] .'_homerepo', getLogUserId());

        $folder = $tdb->getFolderById( (string)$id);

        $newParent = $tdb->getFolderById( (string)$newParentId);
        
        return $folder->move( $newParent );
        
    }

    public function reorder($idCourse, $idToMove, $newParent, $newOrder) {
        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new ($this->DirDb)($idCourse);
        
        //repo db
        //$tdb = new RepoDirDb( $GLOBALS['prefix_lms'].'_repo', getLogUserId());

        //home db
        //$tdb = new HomerepoDirDb( $GLOBALS['prefix_lms'] .'_homerepo', getLogUserId());
        $folder = $tdb->getFolderById( (string)$idToMove);
        
        return $folder->reorder($newParent, $newOrder );
        
    }

}
