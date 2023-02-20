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

class MycoursesLms extends Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getDefaultTab()
    {
        $query = ' SELECT ma.obj_index'
                . ' FROM %lms_middlearea ma'
                . ' WHERE ma.is_home = 1';

        list($tab) = sql_fetch_row(sql_query($query));

        return $tab;
    }

    public function getTabReq($tab)
    {
        switch ($tab) {
            case 'tb_classroom':        $req = 'lms/classroom/show'; break;
            case 'tb_communication':    $req = 'lms/communication/show'; break;
            case 'tb_coursepath':       $req = 'lms/coursepath/show'; break;
            case 'tb_elearning':        $req = 'lms/elearning/show'; break;
            case 'tb_games':            $req = 'lms/games/show'; break;
            case 'tb_home':             $req = 'lms/home/show'; break;
            case 'tb_dashboard':        $req = 'lms/dashboard/show'; break;
            case 'tb_kb':               $req = 'lms/kb/show'; break;
            case 'tb_label':            $req = 'lms/label/show'; break;
            case 'tb_videoconference':  $req = 'lms/videoconference/show'; break;
            default:                    $req = null; break;
        }

        // checking plugin tab
        $pl = new PluginManager('');
        $list_pl = $pl->get_all_plugins();
        foreach ($list_pl as $key) {
            $plugin_name = strtolower($key['name']);
            if ($tab == 'tb_' . $plugin_name) {
                $req = 'lms/' . $plugin_name . '/show';
            }
        }

        return $req;
    }

    public function shouldRedirectToCatalogue()
    {
        if (FormaLms\lib\Get::sett('on_usercourse_empty') == 'on') {
            require_once _lms_ . '/lib/lib.course.php';
            $cu = new Man_CourseUser();
            if (!$cu->countUserCourses(Docebo::user()->getIdSt())) {
                return true;
            }
        }

        return false;
    }

    public function getCatalogueURL()
    {
        return FormaLms\lib\Get::rel_path('lms') . '/index.php?r=lms/catalog/show';
    }

    public function getMyCoursesURL()
    {
        return FormaLms\lib\Get::rel_path('lms') . '/index.php?r=lms/mycourses/show';
    }
}
