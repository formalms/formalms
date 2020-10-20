<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class LoLmsController extends LmsController
{

    public $name = 'lo';
    
    protected $model;

    protected $user_status;

    function init()
    {

        $this->model = new LoLms();
    }

    public function show() {
        addJs($GLOBALS['where_lms_relative'].'/../addons/fancyTree/', 'jquery.fancytree-all-deps.min.js');
        addCss('../../../addons/fancyTree/skin-custom-1/ui.fancytree');
        addJs($GLOBALS['where_lms_relative'].'/../addons/tree_window/', 'lib.tree_window.js');
        $this->render('show', array());
    }

    public function organization() {
        addJs($GLOBALS['where_lms_relative'].'/../addons/fancyTree/', 'jquery.fancytree-all-deps.min.js');
        addCss('../../../addons/fancyTree/skin-custom-1/ui.fancytree');
        addJs($GLOBALS['where_lms_relative'].'/../addons/tree_window/', 'lib.tree_window.js');
        $this->render('organization', array([
            'teacher' => true
        ]));
    }
    
    public function get(){
        $id_course = $_SESSION['idCourse'];
        $id = Get::req('id', DOTY_INT, false);
        header('Content-type:application/json');
        echo json_encode(array_values($this->model->getFolders($id_course, $id)));
        die();
    }

    public function delete(){
        header('Content-type:application/json');
        $id = Get::req('id', DOTY_INT, false);
        $id_course = $_SESSION['idCourse'];
        echo json_encode($this->model->deleteFolder($id_course, $id));
        die();
    }

    public function rename(){
        header('Content-type:application/json');
        $id = Get::req('id', DOTY_INT, false);
        $newName = Get::req('newName', DOTY_STRING, false);
        $id_course = $_SESSION['idCourse'];
        echo json_encode($this->model->renameFolder($id_course, $id, $newName));
        die();
    }

    public function move(){
        header('Content-type:application/json');
        $id = Get::req('id', DOTY_INT, false);
        $newParentId = Get::req('newParentId', DOTY_INT, false);
        $id_course = $_SESSION['idCourse'];
        echo json_encode($this->model->moveFolder($id_course, $id, $newParentId));
        die();
    }

    public function edit() {


        require_once( Docebo::inc( _lms_.'/modules/organization/orglib.php' ) );
        $tdb = new OrgDirDb($_SESSION['idCourse'], array());
        
        $tree_view = new Org_TreeView($tdb, 'organization' );

        require_once Forma::inc(_adm_ . '/lib/lib.sessionsave.php' );
        $saveObj = new Session_Save();
        $saveName = $saveObj->getName('organization'.$_SESSION['idCourse'], true);
        $saveObj->save( $saveName, $tree_view->getState() );

        $id = Get::req('id', DOTY_INT, false);

        $folder = $tdb->getFolderById( (string)$id );
        $lo = createLO( $folder->otherValues[REPOFIELDOBJECTTYPE]);
        $lo->edit($folder->otherValues[REPOFIELDIDRESOURCE], 'index.php?r=lms/lo/organization&id_course=1' );
    }
}
