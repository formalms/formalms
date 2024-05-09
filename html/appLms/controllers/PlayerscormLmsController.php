<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   forma.lms - The E-Learning Suite                                        |
|                                                                           |
|   Copyright (c) 2013-2023 (forma.lms)                                     |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class PlayerscormLmsController extends LmsController
{
    const mod_name = 'playerscorm';

    protected $model;

    public function init()
    {
        http_response_code(500);
        $this->model = new PlayerscormLms();
    }

    public function show()
    {
        $id = FormaLms\lib\Get::req('id', DOTY_INT);
        http_response_code(200);
        $this->render('show', array('scorm' => $id));
    }

    public function keep()
    {
        http_response_code(200);
        // I'm still alive :)
    }

    public function scorm()
    {
        $id = FormaLms\lib\Get::req('id', DOTY_INT);
        header('Content-type: application/json');
        echo json_encode($this->model->getSCORM($id));
        http_response_code(200);
        exit;
    }

    public function initialize()
    {   
        $id = FormaLms\lib\Get::req('id', DOTY_INT);
        header('Content-type: application/json');
        echo json_encode($this->model->initialize($id));
        http_response_code(200);
        exit;
    }

    public function commit()
    {   
        $id = FormaLms\lib\Get::req('id', DOTY_INT);
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
        } else {
            $data = json_decode(file_get_contents('php://input'));
        }
        $this->model->commit($id, $data);
        header('Content-type: application/json');
        http_response_code(200);
        exit;
    }

    public function finish()
    {
        $id = FormaLms\lib\Get::req('id', DOTY_INT);
        if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = json_decode($GLOBALS['HTTP_RAW_POST_DATA']);
        } else {
            $data = json_decode(file_get_contents('php://input'));
        }
        $this->model->commit($id, $data);
        $this->model->finish($id);
        header('Content-type: application/json');
        http_response_code(200);
        exit;
    }
}
