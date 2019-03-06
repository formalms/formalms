<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */
require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );

class LoLms extends Model {
    
    public function getLearningObjects($idCourse = FALSE, $rootId = 0, $offset = null, $limit = null, $filters = array(), $groupBy = null, $selectFunction = null, $orderBy = null) {
        require_once( Docebo::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new OrgDirDb($idCourse, $filters, $offset, $limit, $groupBy, $selectFunction, $orderBy);
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

}
