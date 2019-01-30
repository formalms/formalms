<?php defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
\ ======================================================================== */

class MycoursesLms extends Model {

    public function getDefaultTab() {

        $query =  " SELECT ma.obj_index"
                . " FROM %lms_middlearea ma"
                . " WHERE ma.is_home = 1";
        
        list($tab) = sql_fetch_row(sql_query($query));

        return $tab;
    }

    public function getTabReq($tab) {

        switch($tab) {
            case 'tb_classroom':        $req = 'lms/classroom/show';        break;
            case 'tb_communication':    $req = 'lms/communication/show';    break;
            case 'tb_coursepath':       $req = 'lms/coursepath/show';       break;
            case 'tb_elearning':        $req = 'lms/elearning/show';        break;
            case 'tb_games':            $req = 'lms/games/show';            break;
            case 'tb_home':             $req = 'lms/home/show';             break;
            case 'tb_kb':               $req = 'lms/kb/show';               break;
            case 'tb_label':            $req = 'lms/label/show';            break;
            case 'tb_videoconference':  $req = 'lms/videoconference/show';  break;
            default:                    $req = null;                    break;
        }

        return $req;
    }

    public function shouldRedirectToCatalogue() {

        if(Get::sett('on_usercourse_empty') == 'on') {
            require_once _lms_ . '/lib/lib.course.php';
            $cu = new Man_CourseUser();
            if(!$cu->countUserCourses(Docebo::user()->getIdSt())) {
                return true;
            }
        }

        return false;
    }

    public function getCatalogueURL() {

        return Get::rel_path('lms') . '/index.php?r=lms/catalog/show';
    }

    public function getMyCoursesURL() {

        return Get::rel_path('lms') . '/index.php?r=lms/mycourses/show';
    }
}
