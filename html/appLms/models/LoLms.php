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

    public function copy($id) {
        require_once( $GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('crepo',true);
        $folder = $this->tdb->getFolderById( (string)$id );
        $saveData = array(	'repo' => 'pubrepo',
                            'id' => $id,
                            'objectType' => $folder->otherValues[REPOFIELDOBJECTTYPE],
                            'name' => $folder->getFolderName(),
                            'idResource' => $folder->otherValues[REPOFIELDIDRESOURCE]
                        ); 
        $saveObj->save( $saveName, $saveData );
        return true;
    }

    public function paste($folderId) {
        require_once($GLOBALS['where_framework'].'/lib/lib.sessionsave.php' );
        $saveObj = new Session_Save();
        $saveName = $_GET['crepo'];
        if( $saveObj->nameExists($saveName) ) {
            $saveData =& $saveObj->load($saveName);

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

}
