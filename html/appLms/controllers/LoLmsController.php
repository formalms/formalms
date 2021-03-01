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

    /**
     * @var LoLms $model
     */
    protected $model;

    protected $user_status;

    function init()
    {

        $this->model = new LoLms();
    }

    private function getFolders($idCourse, $idFolder = false) {
        return array_values($this->model->getFolders($idCourse, $idFolder));
    }

    public function show() {
        $id_course = $_SESSION['idCourse'];
        $this->render('show', ['data' => $this->getFolders($id_course)]);
    }

    public function organization() {
        $this->render('organization', array([
            'teacher' => true
        ]));
    }

    public function get(){
        $id_course = $_SESSION['idCourse'];
        $id = Get::req('id', DOTY_INT, false);
        header('Content-type:application/json');
        echo json_encode($this->getFolders($id_course, $id));
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

    public function reorder(){
        header('Content-type:application/json');
        $id = Get::req('id', DOTY_INT, false);
        $newParent = Get::req('newParent', DOTY_INT, false);
        $newOrderString = Get::req('newOrder', DOTY_STRING, false);
        $newOrder = explode(",", $newOrderString);
        if ($id && $newParent !== false) {
            $id_course = $_SESSION['idCourse'];
            if ($this->model->reorder($id_course, $id, $newParent, $newOrder ? $newOrder : null)) {
                echo json_encode([
                    "success" => true
                ]);
                die();   
            }
        }
        echo json_encode([
            "success" => false
        ]);
        die();
    }

    public function edit() {


        require_once( Forma::inc( _lms_.'/modules/organization/orglib.php' ) );
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
