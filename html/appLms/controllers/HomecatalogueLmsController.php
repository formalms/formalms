<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

class HomecatalogueLmsController extends CatalogLmsController
{

    public function init()
    {
        if (!HomepageAdm::staticIsCatalogToShow()) {
            Util::jump_to('');
        }

        YuiLib::load('base,tabview');
        Lang::init('course');
        $this->path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';
        $this->model = new HomecatalogueLms();
        $this->_mvc_name = 'catalog';
        $this->acl_man = &Docebo::user()->getAclManager();
    }

    public function isTabActive($tab_name)
    {
        return true;
    }

    protected function getBaseData()
    {
        $data = parent::getBaseData();
        $data['catalogueType'] = 'homecatalogue';
        $data['endpoint'] = 'lms/homecatalogue';

        return $data;
    }

    public function show()
    {
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);

        $catalogue = $this->model->GetGlobalJsonTree($id_catalogue);
        $total_category = count($catalogue);

        $data = $this->getBaseData();

        $data = array_merge($data, [
            'id_catalogue' => $id_catalogue,
            'user_catalogue' => [],
            'show_general_catalogue_tab' => true,
            'show_empty_catalogue_tab' => false,
            'show_user_catalogue_tab' => false,
            'tab_actived' => false,
            'total_category' => $total_category,
            'starting_catalogue' => $id_catalogue,
            'catalogue' => $catalogue
        ]);

        $this->render('catalog', [
            'data' => $data
        ]);
    }

    public function allCourseForma()
    {
        $id_category = FormaLms\lib\Get::req('id_category', DOTY_INT, 0);
        $typeCourse = FormaLms\lib\Get::req('type_course', DOTY_STRING, '');
        $id_catalogue = FormaLms\lib\Get::req('id_catalogue', DOTY_INT, 0);

        $courses = $this->model->getCatalogCourseList($typeCourse, 1, $id_catalogue, $id_category);

        foreach ($courses as $index => $course) {
            if ((int)$course['show_rules'] !== 0) {
                unset($courses[$index]);
            }
        }

        $data = $this->getBaseData();

        $data = array_merge($data, compact('courses', 'id_catalogue'));

        $this->render('courselist', ['data' => $data]);
    }
}
