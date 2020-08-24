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
        addJs($GLOBALS['where_lms_relative'].'/../addons/tree_window/', 'lib.tree_window.js');
        $this->render('show', array());
    }

    public function organization() {
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
}
