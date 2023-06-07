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

class Module_Course extends LmsModule
{
    public function beforeLoad()
    {
        switch ($GLOBALS['op']) {
            case 'mycourses':
            case 'unregistercourse':
                if ($this->session->has('idCourse')) {
                    TrackUser::closeSessionCourseTrack();
                    $this->session->remove('idCourse');
                    $this->session->remove('idEdition');
                }
                if ($this->session->has('cp_assessment_effect')) {
                    $this->session->remove('cp_assessment_effect');
                }
                $this->session->save();
                break;
            default:
                break;
        }
    }

    public function loadBody()
    {
        switch ($GLOBALS['op']) {
            case 'showresults':
                $id_course = FormaLms\lib\Get::req('id_course', DOTY_INT, false);
                $this->session->set('idCourse', $id_course);
                $this->session->save();
                Util::jump_to('index.php?modname=organization&op=showresults&idcourse=' . $id_course);
             break;
            case 'mycourses' :
            case 'unregistercourse' :
                require_once _lms_ . '/modules/' . $this->module_name . '/course.php';

                require_once _base_ . '/lib/lib.urlmanager.php';
                $url = &UrlManager::getInstance('course');
                $url->setStdQuery(FormaLms\lib\Get::home_page_query());

                mycourses($url);
             break;
            case 'donwloadmaterials':
                downloadMaterials();
            break;
            default:
                require_once _lms_ . '/modules/' . $this->module_name . '/infocourse.php';
                infocourseDispatch($GLOBALS['op']);
             break;
        }
    }

    public static function getAllToken($op = '')
    {
        switch ($op) {
            case 'infocourse':
                return [
                    'view' => ['code' => 'view_info',
                                        'name' => '_VIEW',
                                        'image' => 'standard/view.png', ],
                    'mod' => ['code' => 'mod',
                                        'name' => '_MOD',
                                        'image' => 'standard/edit.png', ],
                ];
             break;
            default:
                return [
                    'view' => ['code' => 'view',
                                        'name' => '_VIEW',
                                        'image' => 'standard/view.png', ],
                ];
        }
    }

    public function getPermissionsForMenu($op)
    {
        return [
            1 => $this->selectPerm($op, 'view'),
            2 => $this->selectPerm($op, 'view'),
            3 => $this->selectPerm($op, 'view'),
            4 => $this->selectPerm($op, 'view'),
            5 => $this->selectPerm($op, 'view,mod'),
            6 => $this->selectPerm($op, 'view,mod'),
            7 => $this->selectPerm($op, 'view,mod'),
        ];
    }
}
