<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');
require_once Forma::inc(_lib_ . '/Helpers/Filters/Course/FilterCourseManager.php');

class MycoursesLmsController extends LmsController
{
    public $name = 'mycourses';

    private $model;

    private $filterManager;

    public function init()
    {
        $this->model = new MycoursesLms();
        $this->filterManager = new FilterCourseManager();
    }

    public function show()
    {
        if (!$tab = FormaLms\lib\Get::req('mycourses_tab', DOTY_STRING, null)) {
            $tab = $this->model->getDefaultTab();
        }

        if (count($filters = FormaLms\lib\Get::getRegexUrlMatches('filterCourse'))) {
            foreach ($filters as $filter) {
                $this->filterManager->setFilterByCookie($this->filterManager->getCookieIndex($filter, Docebo::user()->idst), FormaLms\lib\Get::req($filter));
            }
        }

        if ($req = $this->model->getTabReq($tab)) {
            $requesthandler = new RequestHandler($req, 'lms');
            $requesthandler->run();
        }
    }

    public function home()
    {
        if ($this->model->shouldRedirectToCatalogue()) {
            $url = $this->model->getCatalogueURL();
        } else {
            $url = $this->model->getMyCoursesURL();
        }

        Util::jump_to($url);
    }
}
