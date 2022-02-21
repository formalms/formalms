<?php defined("IN_FORMA") or die('Direct access is forbidden.');
require_once Forma::inc(_lib_ .'/Helpers/Filters/Course/FilterCourseManager.php');

class MycoursesLmsController extends LmsController {

    public $name = 'mycourses';

    private $model;

    private $filterManager;

    public function init() {

        $this->model = new MycoursesLms();
        $this->filterManager = new FilterCourseManager();
    }

    public function show() {

        
        if(!$tab = Get::req('mycourses_tab', DOTY_STRING, null)) {
            $tab = $this->model->getDefaultTab();
        }

        
        if(count($filters = Get::getRegexUrlMatches('filterCourse'))) {
           
            foreach($filters as $filter) {
                $this->filterManager->setFilterByCookie($this->filterManager->getCookieIndex($filter, Docebo::user()->idst), Get::req($filter)); 
            }
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
