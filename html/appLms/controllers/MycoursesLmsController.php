<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class MycoursesLmsController extends LmsController {

    public $name = 'mycourses';

    private $model;

    public function init() {

        $this->model = new MycoursesLms();
    }

    public function show() {

        if(!$tab = Get::req('mycourses_tab', DOTY_STRING, null)) {
            $tab = $this->model->getDefaultTab();
        }

        if($req = $this->model->getTabReq($tab)) {
            $requesthandler = new RequestHandler($req, 'lms');
            $requesthandler->run();
        }
    }

    public function home() {

        if($this->model->shouldRedirectToCatalogue()) {
            $url = $this->model->getCatalogueURL();
        } else {
            $url = $this->model->getMyCoursesURL();
        }

        Util::jump_to($url);
    }
}
