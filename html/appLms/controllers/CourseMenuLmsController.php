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

class CourseMenuLmsController extends LmsController
{
    protected $model;

    protected $idCourse;

    public function isTabActive($tab_name)
    {
        return true;
    }

    public function init()
    {
        $this->_mvc_name = 'coursemenu';
        $this->idCourse = $this->session->get('idCourse');

        YuiLib::load('base,tabview');

        require_once \FormaLms\lib\Forma::include(_lms_ . '/lib/', 'lib.course.php');

        //$this->model = new CoursepathLms();
    }

    public function show()
    {
        
        // url accesso al corso http://forma/appLms/index.php?r=course/show&course_id=1

        if (!\FormaLms\lib\FormaUser::getCurrentUser()->isAnonymous() && $this->idCourse) {
            $db = \FormaLms\db\DbConn::getInstance();

            $query_course = 'SELECT name, img_course FROM %lms_course WHERE idCourse = ' . $this->idCourse . ' ';
            $course_data = $db->query($query_course);
            $path_course = $GLOBALS['where_files_relative'] . '/appLms/' . FormaLms\lib\Get::sett('pathcourse') . '/';
            while ($course = $db->fetch_obj($course_data)) {
                $course_name = $course->name;
                $course_img = (empty($course->img_course) || is_null($course->img_course)) ? FormaLms\lib\Get::tmpl_path() . 'images/course/course_nologo.png' : $path_course . $course->img_course;
            }

            // get select menu
            $id_list = [];
            $menu_module = [];
            $query = 'SELECT idMain AS id, name FROM %lms_menucourse_main WHERE idCourse = ' . $this->idCourse . ' ORDER BY sequence';
            $re_main = $db->query($query);
            while ($main = $db->fetch_obj($re_main)) {
                $menu_module[] = [
                    // 'submenu'=> array(),
                    'id_menu' => $main->id,
                    'name' => Lang::t($main->name, 'menu_course', false, false, $main->name),
                    'link' => 'id_main_sel=' . $main->id,
                ];
                $id_list[] = '"menu_lat_' . $main->id . '"';
            }
            $main_menu_id = FormaLms\lib\Get::req('main_menu_id', DOTY_INT, '') ? FormaLms\lib\Get::req('main_menu_id', DOTY_INT, '') : $menu_module[0]['id_menu'];
            // horizontal menu

            $menu_horizontal = [];
            $query_menu = 'SELECT mo.idModule AS id, mo.module_name, mo.default_op, mo.default_name, mo.token_associated AS token, mo.mvc_path, under.idMain AS id_main, under.my_name
                            FROM %lms_module AS mo JOIN %lms_menucourse_under AS under ON (mo.idModule = under.idModule) WHERE under.idCourse = ' . $this->idCourse . '
                            AND under.idMain = ' . $main_menu_id . ' ORDER BY under.idMain, under.sequence';
            $re_menu_voice = $db->query($query_menu);

            while ($obj = $db->fetch_obj($re_menu_voice)) {
                // checkmodule module
                if (checkPerm($obj->token, true, $obj->module_name)) {
                    $GLOBALS['module_assigned_name'][$obj->module_name] = ($obj->my_name != '' ? $obj->my_name : Lang::t($obj->default_name, 'menu_course'));

                    $menu_horizontal[] = [
                        'id_submenu' => $obj->id,
                        'name' => $GLOBALS['module_assigned_name'][$obj->module_name],
                        'link' => ($obj->mvc_path != ''
                            ? 'index.php?r=' . $obj->mvc_path . '&id_module_sel=' . $obj->id . '&id_main_sel=' . $obj->id_main
                            : 'index.php?modname=' . $obj->module_name . '&op=' . $obj->default_op . '&id_module_sel=' . $obj->id . '&id_main_sel=' . $obj->id_main
                        ),
                    ];
                } // end if checkPerm
            } // end while

            $user_stats = [];
            if (!$this->session->has('is_ghost') || $this->session->get('is_ghost') !== true) {
                if (\FormaLms\lib\Forma::course()->getValue('show_time') == 1) {
                    $tot_time_sec = TrackUser::getUserPreviousSessionCourseTime(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), $this->session->get('idCourse'));
                    $partial_time_sec = TrackUser::getUserCurrentSessionCourseTime($this->session->get('idCourse'));
                    $tot_time_sec += $partial_time_sec;

                    $hours = (int) ($partial_time_sec / 3600);
                    $minutes = (int) (($partial_time_sec % 3600) / 60);
                    $seconds = (int) ($partial_time_sec % 60);
                    if ($minutes < 10) {
                        $minutes = '0' . $minutes;
                    }
                    if ($seconds < 10) {
                        $seconds = '0' . $seconds;
                    }
                    $partial_time = ($hours != 0 ? $hours . 'h ' : '') . $minutes . 'm '; //.$seconds.'s ';

                    $hours = (int) ($tot_time_sec / 3600);
                    $minutes = (int) (($tot_time_sec % 3600) / 60);
                    $seconds = (int) ($tot_time_sec % 60);
                    if ($minutes < 10) {
                        $minutes = '0' . $minutes;
                    }
                    if ($seconds < 10) {
                        $seconds = '0' . $seconds;
                    }
                    $tot_time = ($hours != 0 ? $hours . 'h ' : '') . $minutes . 'm '; //.$seconds.'s ';

                    $user_stats['show_time']['partial_time'] = $partial_time;

                    $user_stats['show_time']['total_time'] = $tot_time;
                }
            }

            // who is online ---------------------------------------------------------
            $user_stats['who_is_online']['type'] = \FormaLms\lib\Forma::course()->getValue('show_who_online');
            $user_stats['who_is_online']['user_online_n'] = TrackUser::getWhoIsOnline($this->session->get('idCourse'));

            // print first pannel
            if (!empty($user_stats['head'])) {
                $tempo_parziale = Lang::t('_PARTIAL_TIME', 'course');
                $tempo_totale = Lang::t('_TOTAL_TIME', 'standard');
                $user_online = Lang::t('_WHOIS_ONLINE', 'course');
                //** LR responsive tabella statistiche **
                $info_panel .= '<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
                                        #user_stats td:nth-of-type(1):before { content: "' . $tempo_parziale . '"; }
                                        #user_stats td:nth-of-type(2):before { content: "' . $tempo_totale . '"; }
                                        #user_stats td:nth-of-type(3):before { content: "' . $user_online . '"; }    
                                        }        
                                        </style>
                                    ';

                $info_panel .= '<table id="user_stats" class="quick_table">'
                    . '<thead><tr>'
                    . (isset($user_stats['head'][0]) ? '<th scope="col">' . $user_stats['head'][0] . '</th>' : '')
                    . (isset($user_stats['head'][1]) ? '<th scope="col">' . $user_stats['head'][1] . '</th>' : '')
                    . (isset($user_stats['head'][2]) ? '<th scope="col">' . $user_stats['head'][2] . '</th>' : '')
                    . '</tr></thead><tbody><tr>'
                    . (isset($user_stats['body'][0]) ? '<td>' . $user_stats['body'][0] . '</td>' : '')
                    . (isset($user_stats['body'][1]) ? '<td>' . $user_stats['body'][1] . '</td>' : '')
                    . (isset($user_stats['body'][2]) ? '<td>' . $user_stats['body'][2] . '</td>' : '')
                    . '</tr></tbody>'
                    . '</table>';
            }

            // print progress bar -------------------------------------------------
            if (\FormaLms\lib\Forma::course()->getValue('show_progress') == 1) {
                require_once _lms_ . '/lib/lib.stats.php';
                $total = getNumCourseItems($this->session->get('idCourse'),
                    false,
                    \FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                    false);
                $tot_complete = getStatStatusCount(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                    $this->session->get('idCourse'),
                    ['completed', 'passed']);
                $tot_failed = getStatStatusCount(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(),
                    $this->session->get('idCourse'),
                    ['failed']);

                $materiali = Lang::t('_PROGRESS_ALL', 'course');
                $completato = Lang::t('_COMPLETED', 'standard');
                $sbagliati = Lang::t('_PROGRESS_FAILED', 'course');
                //** LR responsive stats tab **
                $info_panel .= '<style>
                            @media
                            only screen and (max-width: 870px),
                            (min-device-width: 870px) and (max-device-width: 1024px)  {            
                                        #course_stats td:nth-of-type(1):before { content: "' . $materiali . '"; }
                                        #course_stats td:nth-of-type(2):before { content: "' . $completato . '"; }
                                        #course_stats td:nth-of-type(3):before { content: "' . $sbagliati . '"; }    
                                        }        
                                        </style>
                                    ';

                $info_panel .= '<table id="course_stats" class="quick_table">'
                    . '<thead><tr>'
                    . '<th scope="col">' . Lang::t('_PROGRESS_ALL', 'course') . '</th>'
                    . '<th scope="col">' . Lang::t('_COMPLETED', 'course') . '</th>'
                    . '<th scope="col">' . Lang::t('_PROGRESS_FAILED', 'course') . '</th>'
                    . '</tr></thead><tbody><tr>'
                    . '<td>' . $total . '</td>'
                    . '<td>' . $tot_complete . '</td>'
                    . '<td>' . $tot_failed . '</td>'
                    . '</tr></tbody>'
                    . '</table>';

                $info_panel_progress = '<p class="course_progress">'
                    . '<span>' . Lang::t('_PROGRESS', 'course') . ' </span>'
                    . '</p>'
                    . '<div class="nofloat"></div>'
                    . renderProgress($tot_complete, $tot_failed, $total, false) . "\n";

                // MENU OVER
                cout('<div class="row" style="padding-top:80px;">', 'menu_over');
                cout('<div class="col-sm-3">' . $logo_panel . '</div>', 'menu_over');

                cout('<div class="col-sm-9" >', 'menu_over');
                cout('<div class="col-md-7"><div><h1>' . \FormaLms\lib\Forma::course()->getValue('name') . '</h1></div></div>
                        <div class="col-md-4"><div>' . $info_panel_progress . '</div></div>
                        <div class="col-md-1"><div><br> <button type="button" class="btn btn-sm" data-toggle="modal" data-target="#formaModal"><span class="glyphicon glyphicon-stats"></span></button></div></div>
                        ', 'menu_over');
                cout('</div></div>&nbsp;', 'menu_over');
            } else {
                // MENU OVER
                cout('<div class="row" style="padding-top:80px;">', 'menu_over');
                cout('<div class="col-sm-3">' . $logo_panel . '</div>', 'menu_over');

                cout('<div class="col-sm-9" >', 'menu_over');
                cout('<div class="col-md-7"><div><h1>' . \FormaLms\lib\Forma::course()->getValue('name') . '</h1></div></div>', 'menu_over');

                cout('</div></div><br><br>&nbsp;', 'menu_over');
            }

            $info_panel .= '</div>' . "\n";

            $this->render('coursemenu_lat', [
                'dropdown' => $menu_module,
                'slider' => $menu_horizontal,
                'course_name' => $course_name,
                'course_img' => $course_img, ]);
        }
    }

    public function all()
    {
        $filter_text = FormaLms\lib\Get::req('filter_text', DOTY_STRING, '');

        $conditions = '';
        if (!empty($filter_text)) {
            $conditions = "AND cp.path_name LIKE '%" . addslashes($filter_text) . "%'";
        }

        $user_coursepath = $this->model->getAllCoursepath(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt(), $conditions);
        $coursepath_courses = $this->model->getCoursepathCourseDetails(array_keys($user_coursepath));

        if (count($user_coursepath) > 0) {
            $this->render('coursepath', ['type' => 'all',
                                                'user_coursepath' => $user_coursepath,
                                                'coursepath_courses' => $coursepath_courses, ]);
        } else {
            echo Lang::t('_NO_COURSEPATH_IN_SECTION', 'coursepath');
        }
    }
}
