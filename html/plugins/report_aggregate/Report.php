<?php
namespace Plugin\report_aggregate;
defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */
require_once(_base_.'/lib/lib.mailer.php');
require_once(_lms_.'/lib/lib.course.php');
require_once(_lms_.'/admin/modules/report/report_tableprinter.php');

define('_RA_CATEGORY_COURSES', 'courses');
define('_RA_CATEGORY_COURSECATS', 'coursecategories');
define('_RA_CATEGORY_TIME', 'time');
define('_RA_CATEGORY_COMMUNICATIONS', 'communications');
define('_RA_CATEGORY_GAMES', 'games');

define('_DECIMAL_SEPARATOR', '.');
define('_PERCENT_SIMBOL', '%');

use DoceboLanguage;
use Lang;
use CourseSubscribe_Manager;
use YuiLib;
use Form;
use DoceboACLManager;
use UserSelector;
use Util;
use Docebo;
use AdminPreference;
use Get;
use Format;
use ReportBox;
use Catalogue_Manager;
use Man_Course;
use OrganizationManagement;
use CourseReportManager;
use ReportTablePrinter;
use CourseLevel;
use LabelAlms;
use FieldList;
use UsermanagementAdm;
use Selector_Course;
use Services_JSON;
use DbConn;

class Report extends \ReportPlugin{

    var $page_title = false;
    var $db = NULL;

    function __construct() {

        $this->db = DbConn::getInstance();

        $this->lang =& DoceboLanguage::createInstance('report', 'framework');
        $this->_set_columns_category(_RA_CATEGORY_COURSES, Lang::t('_RU_CAT_COURSES', 'report'), 'get_courses_filter', 'show_report_courses', '_get_courses_query');
        $this->_set_columns_category(_RA_CATEGORY_COURSECATS, Lang::t('_RA_CAT_COURSECATS', 'report'), 'get_coursecategories_filter', 'show_report_coursecategories', '_get_coursecategories_query');
        $this->_set_columns_category(_RA_CATEGORY_TIME, Lang::t('_RA_CAT_TIME', 'report'), 'get_time_filter', 'show_report_time', '_get_time_query');
        $this->_set_columns_category(_RA_CATEGORY_COMMUNICATIONS, Lang::t('_RU_CAT_COMMUNICATIONS', 'report'), 'get_communications_filter', 'show_report_communications', '_get_communications_query');
        $this->_set_columns_category(_RA_CATEGORY_GAMES, Lang::t('_RU_CAT_GAMES', 'report'), 'get_games_filter', 'show_report_games', '_get_games_query');
    }


    //users and orgchart selection
    function get_rows_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
        require_once(_base_.'/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        //update session
        $ref =& $_SESSION['report_tempdata'];
        if (!isset($ref['rows_filter'])) {
            $ref['rows_filter'] = array( //default values
                'select_all' => false,
                'selection_type' => 'users',
                'selection' => array()
            );
        } else {
            //already resolved in switch block
        }

        $step = Get::req('step', DOTY_ALPHANUM, 'sel_type');
        switch ($step) {

            case 'sel_type': {
                $values = array('users' => $this->lang->def('_USERS'), 'groups'=>$this->lang->def('_GROUPS'));
                $sel_val = (isset($ref['rows_filter']['selection_type']) ? $ref['rows_filter']['selection_type'] : 'users');

                $out  = Form::openForm('selection_type_form', $jump_url);

                $out .= Form::getRadioSet($this->lang->def('_AGGREGATE_ON'), 'selection_type', 'selection_type', array_flip($values) , $sel_val)
                    .'<div class="nofloat"></div>';

                $out .= Form::openButtonSpace();
                $out .= Form::getButton('ok_selection', 'ok_selection', $this->lang->def('_CONFIRM'));
                $out .= Form::getButton('undo', 'undo', $this->lang->def('_UNDO'));
                $out .= Form::closeButtonSpace();
                $out .= Form::getHidden('step', 'step', 'sel_data');

                $out .= Form::closeForm();

                cout($out);
            } break;


            case 'sel_data': {
                $type = Get::req('selection_type', DOTY_ALPHANUM, 'users');

                //$aclManager = new DoceboACLManager();
                $user_select = new UserSelector();

                if (Get::req('is_updating', DOTY_INT, 0)>0) {
                    $ref['rows_filter']['select_all'] = ( Get::req('select_all', DOTY_INT, 0)>0 ? true : false );
                    $ref['rows_filter']['selection_type'] = $type;
                    //$ref['rows_filter']['selection'] = $user_select->getSelection($_POST);
                } else { //maybe redoundant
                    if (!isset($ref['rows_filter']['select_all'])) $ref['rows_filter']['select_all'] = false;
                    if (!isset($ref['rows_filter']['selection_type'])) $ref['rows_filter']['selection_type'] = 'groups';
                    if (!isset($ref['rows_filter']['selection'])) $ref['rows_filter']['selection'] = array();
                    $user_select->resetSelection($ref['rows_filter']['selection']);
                    //$ref['users'] = array(); it should already have been set to void array, if non existent
                }

                if(isset($_POST['cancelselector']))
                    Util::jump_to($back_url);
                elseif(isset($_POST['okselector'])) {
                    $ref['rows_filter']['selection'] = $user_select->getSelection($_POST);
                    Util::jump_to($next_url);
                }

                //set page
                switch ($type) {
                    case 'groups': {
                        $user_select->show_user_selector = FALSE;
                        $user_select->show_group_selector = TRUE;
                        $user_select->show_orgchart_selector = TRUE;
                    } break;
                    case 'users': {
                        $user_select->show_user_selector = TRUE;
                        $user_select->show_group_selector = TRUE;
                        $user_select->show_orgchart_selector = TRUE;
                    } break;
                }
                //$user_select->show_orgchart_simple_selector = FALSE;
                //$user_select->multi_choice = TRUE;

                $user_select->addFormInfo(
                    ($type=='users' ? Form::getCheckbox($lang->def('_REPORT_FOR_ALL'), 'select_all', 'select_all', 1, $ref['rows_filter']['select_all']) : '').
                    Form::getBreakRow().
                    Form::getHidden('selection_type', 'selection_type', $type).
                    Form::getHidden('step', 'step', 'sel_data').
                    Form::getHidden('is_updating', 'is_updating', 1).
                    Form::getHidden('substep', 'substep', 'user_selection').
                    Form::getHidden('second_step', 'second_step', 1));
                $user_select->setPageTitle('');
                $user_select->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
                    false,
                    $this->lang->def('_CHOOSE_USER_FOR_REPORT'),
                    true);

            } break;

        }
    }




    function get_courses_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course_managment.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        //$sel = new Course_Manager();
        //$sel->setLink('index.php?modname=report&op=report_rows_filter');

        if (isset($_POST['undo_filter'])) Util::jump_to($back_url);

        //set $_POST data in $_SESSION['report_tempdata']
        $selector = new Selector_Course();

        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'all_courses' => true,
                'selected_courses' => array(),
                'showed_columns' => array('completed'=>true, 'initinere'=>true, 'notstarted'=>true, 'show_percentages'=>true)
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];

        if (isset($_POST['update_tempdata'])) {
            $selector->parseForState($_POST);
            $temp = $selector->getSelection($_POST);
            $ref['selected_courses'] = $temp;
            $ref['all_courses'] = (Get::req('all_courses', DOTY_INT, 1)==1 ? true : false);
            $ref['showed_columns'] = array(
                'completed' => (Get::req('cols_completed', DOTY_INT, 0)>0 ? true : false),
                'initinere' => (Get::req('cols_initinere', DOTY_INT, 0)>0 ? true : false),
                'notstarted' => (Get::req('cols_notstarted', DOTY_INT, 0)>0 ? true : false),
                'show_percentages' => (Get::req('cols_show_percentages', DOTY_INT, 0)>0 ? true : false));
        }
        else
        {
            $selector->resetSelection($ref['selected_courses']);
        }

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            Util::jump_to($back_url);
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        $temp = count($ref['selected_courses']);


        $box = new ReportBox('courses_selector');
        $box->title = $this->lang->def('_COURSES_SELECTION_TITLE');
        $box->description = false;

        $boxlang =& DoceboLanguage::createInstance('report', 'framework');
        $box->body .= '<div class="fc_filter_line filter_corr">';
        $box->body .= '<input id="all_courses" name="all_courses" type="radio" value="1" '.($ref['all_courses'] ? 'checked="checked"' : '').' />';
        $box->body .= ' <label for="all_courses">'.$boxlang->def('_ALL_COURSES').'</label>';
        $box->body .= ' <input id="sel_courses" name="all_courses" type="radio" value="0" '.($ref['all_courses'] ? '' : 'checked="checked"').' />';
        $box->body .= ' <label for="sel_courses">'.$boxlang->def('_SEL_COURSES').'</label>';
        $box->body .= '</div>';
        $box->body .= '<div id="selector_container"'.($ref['all_courses'] ? ' style="display:none"' : '').'>';
        $box->body .= $selector->loadCourseSelector(true).'</div>';

        $box->footer = $boxlang->def('_CURRENT_SELECTION').':&nbsp;<span id="csel_foot">'.($ref['all_courses'] ? $boxlang->def('_ALL') : ($temp!='' ? $temp : '0')).'</span>';

        YuiLib::load(array(
            'yahoo'           => 'yahoo-min.js',
            'yahoo-dom-event' => 'yahoo-dom-event.js',
            'element'         => 'element-beta-min.js',
            'datasource'      => 'datasource-beta-min.js',
            'connection'      => 'connection-min.js',
            'event'           => 'event-min.js',
            'json'            => 'json-beta-min.js'
        ), array(
            '/assets/skins/sam' => 'skin.css'
        ));
        addJs($GLOBALS['where_lms_relative'].'/admin/modules/report/','courses_filter.js');

        cout('<script type="text/javascript"> '."\n".
            'var courses_count="'.($temp!='' ? $temp : '0').'";'."\n".
            'var courses_all="'.$boxlang->def('_ALL').'";'."\n".
            'YAHOO.util.Event.addListener(window, "load", function(e){ courses_selector_init(); });'."\n".
            '</script>', 'page_head');

        //columns selection
        $col_box = new ReportBox('columns_selection');
        $col_box->title = $this->lang->def('_REPORT_SEL_COLUMNS');
        $col_box->description = $this->lang->def('_SELECT_THE_DATA_COL_NEEDED');

        $col_box->body .= Form::getOpenFieldSet($this->lang->def('_STATUS'));
        $col_box->body .= Form::getCheckBox(Lang::t('_USER_STATUS_SUBS', 'course'), 'cols_notstarted', 'cols_notstarted', 1, $ref['showed_columns']['notstarted']);
        $col_box->body .= Form::getCheckBox(Lang::t('_USER_STATUS_BEGIN', 'course'), 'cols_initinere', 'cols_initinere', 1, $ref['showed_columns']['initinere']);
        $col_box->body .= Form::getCheckBox(Lang::t('_USER_STATUS_END', 'course'), 'cols_completed', 'cols_completed', 1, $ref['showed_columns']['completed']);
        $col_box->body .= Form::getCheckBox(Lang::t('_PERCENTAGE', 'course'), 'cols_show_percentages', 'cols_show_percentages', 1, $ref['showed_columns']['show_percentages']);
        $col_box->body .= Form::getCloseFieldSet();

        cout(Form::openForm('first_step_user_filter', $jump_url, false, 'post').
            $box->get().
            $col_box->get().
            Form::getHidden('update_tempdata', 'update_tempdata', 1));
    }

    function show_report_courses($data = NULL, $other = '') {
        if ($data===NULL)
            cout( $this->_get_courses_query() );
        else
            cout( $this->_get_courses_query('html', $data, $other) );
    }

    function _get_courses_query($type = 'html', $report_data = NULL, $other = '') {
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

        if ($report_data==NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $fw  = $GLOBALS['prefix_fw'];
        $lms = $GLOBALS['prefix_lms'];

        $sel_all = $ref['rows_filter']['select_all'];
        $sel_type = $ref['rows_filter']['selection_type'];
        $selection = $ref['rows_filter']['selection'];

        $all_courses = $ref['columns_filter']['all_courses'];
        $courses = $ref['columns_filter']['selected_courses'];
        $cols =& $ref['columns_filter']['showed_columns'];

        $acl = new DoceboACLManager();
        $html = '';

        $man = new Man_Course();
        $courses_codes = $man->getAllCourses();
        if ($all_courses) {
            $courses = array();
            foreach ($courses_codes as $key=>$val) $courses[] = $key;
        }
        /*
                if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN) {

                    // if the usre is a subadmin with only few course assigned
                    require_once(_base_.'/lib/lib.preference.php');
                    $adminManager = new AdminPreference();
                    $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
                    $courses = array_intersect($courses, $admin_tree['courses']);
                }
        */
        $increment = 0;
        if ($cols['completed']) $increment++;
        if ($cols['initinere']) $increment++;
        if ($cols['notstarted']) $increment++;
        if ($cols['show_percentages']) $increment = $increment*2;
        /*
                //admin users filter
                $acl_man = Docebo::user()->getACLManager();
                $userlevelid = Docebo::user()->getUserLevelId();
                if ( $userlevelid != ADMIN_GROUP_GODADMIN ) {
                    require_once(_base_.'/lib/lib.preference.php');
                    $adminManager = new AdminPreference();
                    $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
                    $admin_users = $acl_man->getAllUsersFromSelection($admin_tree);
                    $admin_users = array_unique($admin_users);
                }*/
        $userlevelid = Docebo::user()->getUserLevelId();
        if( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            //filter users
            $alluser = false;
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            //$user_selected = array_intersect($user_selected, $admin_users);
            //unset($admin_users);

            //filter courses
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            if ($all_courses)
            {
                $all_courses = false;
                $rs = sql_query("SELECT idCourse FROM %lms_course");
                $course_selected = array();
                while (list($id_course) = sql_fetch_row($rs)) { $course_selected[] = $id_course; }
            }

            if(isset($admin_courses['course'][0]))
            {
                //No filter
            }
            elseif(isset($admin_courses['course'][-1]))
            {
                require_once(_lms_.'/lib/lib.catalogue.php');
                $cat_man = new Catalogue_Manager();

                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if(count($user_catalogue) > 0)
                {
                    $courses = array(0);

                    foreach($user_catalogue as $id_cat)
                    {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    foreach($courses as $id_course)
                        if($id_course != 0)
                            $admin_courses['course'][$id_course] = $id_course;
                }
                elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
                {
                    //No filter
                }

                if(!empty($admin_courses['course']))
                {
                    $_clist = array_values($admin_courses['course']);
                    $course_selected = array_intersect($course_selected, $_clist);
                }
                else
                    $course_selected = array();
            }
            else
            {
                $array_courses = array();
                $array_courses = array_merge($array_courses, $admin_courses['course']);

                if(!empty($admin_courses['coursepath']))
                {
                    require_once(_lms_.'/lib/lib.coursepath.php');
                    $path_man = new Catalogue_Manager();
                    $coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
                    $array_courses = array_merge($array_courses, $coursepath_course);
                }
                if(!empty($admin_courses['catalogue']))
                {
                    require_once(_lms_.'/lib/lib.catalogue.php');
                    $cat_man = new Catalogue_Manager();
                    foreach($admin_courses['catalogue'] as $id_cat)
                    {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
                        $array_courses = array_merge($array_courses, $catalogue_course);
                    }
                }
                $admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);

                if(!empty($admin_courses['course']))
                {
                    $_clist = array_values($admin_courses['course']);
                    $course_selected = array_intersect($course_selected, $_clist);
                }
                else
                    $course_selected = array();
            }

            unset($admin_courses);
        }

        switch ($sel_type) {

            case 'groups': {

                //retrieve all labels
                $orgchart_labels = array();
                $query = "SELECT * FROM ".$fw."_org_chart WHERE lang_code='".getLanguage()."'";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    $orgchart_labels[$row['id_dir']] = $row['translation'];
                }

                $labels = array();
                $query = "SELECT * FROM ".$fw."_group WHERE (hidden='false' OR groupid LIKE '/oc_%' OR groupid LIKE '/ocd_%') AND type='free'";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    if ($row['hidden']=='false') {
                        $labels[$row['idst']] = $acl->relativeId($row['groupid']);
                    } else {
                        $temp = explode("_", $row['groupid']); //echo '<div>'.print_r($temp,true).'</div>';
                        if ($temp[0]=='/oc') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        } elseif ($temp[0]=='/ocd') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        }
                    }
                }

                $tot_size = 2;
                $head1 = array( array('colspan'=>2, 'value'=>$this->lang->def('_GROUPS')) );
                $head2 = array($this->lang->def('_NAME'), $this->lang->def('_TOTAL'));

                foreach ($courses as $course) {
                    $head1[] = array(
                        'value' => ( $courses_codes[$course]['code'] ? '['.$courses_codes[$course]['code'].'] ' : '' )
                            .$courses_codes[$course]['name'],
                        'colspan' => $increment
                    );

                    if ($cols['completed']) $head2[] = $this->lang->def('_USER_STATUS_END');
                    if ($cols['completed'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;
                    if ($cols['initinere']) $head2[] = $this->lang->def('_USER_STATUS_BEGIN');
                    if ($cols['initinere'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;
                    if ($cols['notstarted']) $head2[] = $this->lang->def('_USER_STATUS_SUBS');
                    if ($cols['notstarted'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;

                    $tot_size += $increment;
                }

                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $buffer->openHeader();
                $buffer->addHeader($head1);
                $buffer->addHeader($head2);
                $buffer->closeHeader();

                $tot_users = 0;
                $course_stats = array();

                //for each group, retrieve label and user statistics
                foreach ($selection as $dir_id=>$group_id) {
                    $group_users = $acl->getGroupAllUser($group_id);
                    if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) { $group_users = array_intersect($group_users, $admin_users); }
                    $users_num = count($group_users);

                    $line = array();
                    $line[] = $labels[$group_id];
                    $line[] = $users_num;
                    $tot_users += $users_num;

                    if (count($group_users)>0) {
                        $query = "SELECT cu.idUser, cu.idCourse, cu.status ".
                            " FROM ".$lms."_courseuser as cu, ".$lms."_course as c, ".$fw."_user as u ".
                            " WHERE cu.idUser=u.idst AND cu.idCourse=c.idCourse ".
                            " AND u.idst IN (".implode(",", $group_users).") ".
                            (!$all_courses ? " AND c.idCourse IN (".implode(",", $courses)." )" : "");

                        $res = sql_query($query);

                        //$tot_completed = 0;
						while ($row = sql_fetch_assoc($res) ) {
                            if (!isset($course_stats[$row['idCourse']][$group_id])) {
                                $course_stats[$row['idCourse']][$group_id] = array(
                                    'completed' => 0,
                                    'initinere' => 0,
                                    'notstarted' => 0,
                                    'total' => 0
                                );
                            }
                            switch ((int)$row['status']) {
                                case 2: $course_stats[$row['idCourse']][$group_id]['completed']++; break;
                                case 1: $course_stats[$row['idCourse']][$group_id]['initinere']++; break;
                                case 0: $course_stats[$row['idCourse']][$group_id]['notstarted']++; break;
                            }
                            $course_stats[$row['idCourse']][$group_id]['total']++;
                        }

                        foreach ($courses as $course) {
                            if (isset($course_stats[$course][$group_id])) {
                                if ($course_stats[$course][$group_id]['total']==0) $dividend = 1; else $dividend = $course_stats[$course][$group_id]['total'];
                                if ($cols['completed']) $line[] = $course_stats[$course][$group_id]['completed'];
                                if ($cols['completed'] && $cols['show_percentages']) $line[] = number_format(100.0*$course_stats[$course][$group_id]['completed']/$dividend, 2, ',', '')._PERCENT_SIMBOL;
                                if ($cols['initinere']) $line[] = $course_stats[$course][$group_id]['initinere'];
                                if ($cols['initinere'] && $cols['show_percentages']) $line[] = number_format(100.0*$course_stats[$course][$group_id]['initinere']/$dividend, 2, ',', '')._PERCENT_SIMBOL;
                                if ($cols['notstarted']) $line[] = $course_stats[$course][$group_id]['notstarted'];
                                if ($cols['notstarted'] && $cols['show_percentages']) $line[] = number_format(100.0*$course_stats[$course][$group_id]['notstarted']/$dividend, 2, ',', '')._PERCENT_SIMBOL;
                            } else {
                                if ($cols['completed']) $line[] = '0';
                                if ($cols['completed'] && $cols['show_percentages']) $line[] = '0,00%';
                                if ($cols['initinere']) $line[] = '0';
                                if ($cols['initinere'] && $cols['show_percentages']) $line[] = '0,00%';
                                if ($cols['notstarted']) $line[] = '0';
                                if ($cols['notstarted'] && $cols['show_percentages']) $line[] = '0,00%';
                            }
                        }

                        //$line[] = $tot_completed;

                    } else {
                        foreach ($courses as $course) {
                            if ($cols['completed']) $line[] = '0';
                            if ($cols['completed'] && $cols['show_percentages']) $line[] = '0,00%';
                            if ($cols['initinere']) $line[] = '0';
                            if ($cols['initinere'] && $cols['show_percentages']) $line[] = '0,00%';
                            if ($cols['notstarted']) $line[] = '0';
                            if ($cols['notstarted'] && $cols['show_percentages']) $line[] = '0,00%';
                        }
                    }
                    $buffer->addLine($line);


                }

                $buffer->closeBody();
                //echo '<pre>'.print_r($course_stats,true).'</pre>';
                //calc totals
                $foot = array('', $tot_users);
                foreach ($courses as $course) {

                    $completed_total = 0;
                    $initinere_total = 0;
                    $notstarted_total = 0;
                    $total_total = 0;
                    foreach ($selection as $dir_id=>$group_id) {
                        $completed_total += (isset($course_stats[$course][$group_id]['completed']) ? $course_stats[$course][$group_id]['completed'] : 0);
                        $initinere_total += (isset($course_stats[$course][$group_id]['initinere']) ? $course_stats[$course][$group_id]['initinere'] : 0);
                        $notstarted_total += (isset($course_stats[$course][$group_id]['notstarted']) ? $course_stats[$course][$group_id]['notstarted'] : 0);
                        $total_total += (isset($course_stats[$course][$group_id]['total']) ? $course_stats[$course][$group_id]['total'] : 0);
                    }
                    if ($cols['completed']) $foot[] = $completed_total;
                    if ($cols['completed'] && $cols['show_percentages']) $foot[] = ($total_total!=0 ? number_format(100.0*$completed_total/$total_total, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL);
                    if ($cols['initinere']) $foot[] = $initinere_total;
                    if ($cols['initinere'] && $cols['show_percentages']) $foot[] = ($total_total!=0 ? number_format(100.0*$initinere_total/$total_total, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL);
                    if ($cols['notstarted']) $foot[] = $notstarted_total;
                    if ($cols['notstarted'] && $cols['show_percentages']) $foot[] = ($total_total!=0 ? number_format(100.0*$notstarted_total/$total_total, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL);
                }

                $buffer->setFoot($foot);
                $buffer->closeTable();
                $html .= $buffer->get();
            } break;



            case 'users': {



                //** LRZ - #8583
                if($sel_all==1){
                     $users =& $acl->getAllUsersIdst();

                    require_once(_base_.'/lib/lib.userselector.php');
                    require_once(_base_.'/lib/lib.preference.php');

                    $acl_man = new \DoceboACLManager();
                    $adminManager = new \AdminPreference();

                    $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
                    $admin_users = $acl_man->getAllUsersFromSelection($admin_users);
                    $users = array_intersect($users, $admin_users);
                    unset($admin_users);
                     //***


                }   else{

                    $temp = array();
                    // resolve the user selection
                    $users 	=& $acl->getAllUsersFromIdst($selection);
                    if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) { $users = array_intersect($users, $admin_users); }
                    if (count($users)<=0) {
                        $html .= '<p>'.$this->lang->def('_EMPTY_SELECTION').'</p>';
                        break;
                    }

                }


                $query = "SELECT cu.idUser, cu.idCourse, cu.status, u.userId, c.code, u.firstname, u.lastname ".
                    " FROM ( ".$lms."_courseuser as cu ".
                    " JOIN  ".$lms."_course as c ON ( cu.idCourse = c.idCourse) ) ".
                    " JOIN ".$fw."_user as u ON (cu.idUser = u.idst)  ".
                    " WHERE 1 ".
					" AND cu.idCourse IN (".implode(",", $courses).") ";

                    //($sel_all ? "" : " AND idUser IN (".implode(",", $users).")")."";
                  //** LRZ
                  if($sel_all==1) $query = $query." AND idUser IN (".implode(",", $users).")";
                  if($sel_all==0) $query = $query." AND idUser IN (".implode(",", $users).")";


                $res = sql_query($query);

				while ($row = sql_fetch_array($res) ) {

                    if(!isset($temp[$row['idUser']])) {
                        $temp[$row['idUser']] = array (
                            'username' => $acl->relativeId($row['userId']),
                            'fullname' => $row['lastname'].' '.$row['firstname'],
                            'courses' => array()
                        );
                    }
                    $temp[$row['idUser']]['courses'][$row['idCourse']] = $row['status'];
                }
                //echo '<pre>';
                //print_r($temp);

                //draw table
                $tot_size = 1;
                $head2 = array($this->lang->def('_USERNAME'), $this->lang->def('_FULLNAME'));
                $head1 = array(array('colspan'=>2, 'value'=>$this->lang->def('_USER')));
                foreach ($courses as $course) {
                    $head1[] = array(
                        'value' => ( $courses_codes[$course]['code'] ? '['.$courses_codes[$course]['code'].'] ' : '' )
                            .$courses_codes[$course]['name'],
                        'colspan' => $increment
                    );

                    if ($cols['completed']) $head2[] = $this->lang->def('_USER_STATUS_END');
                    if ($cols['completed'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;
                    if ($cols['initinere']) $head2[] = $this->lang->def('_USER_STATUS_BEGIN');
                    if ($cols['initinere'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;
                    if ($cols['notstarted']) $head2[] = $this->lang->def('_USER_STATUS_SUBS');
                    if ($cols['notstarted'] && $cols['show_percentages']) $head2[] = _PERCENT_SIMBOL;

                    $tot_size += $increment;
                }

                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $buffer->openHeader();
                $buffer->addHeader($head1);
                $buffer->addHeader($head2);
                $buffer->closeHeader();

                $completed_total = array();
                $initinere_total = array();
                $notstarted_total = array();
                $courses_total = array();

                foreach($courses as $course) {
                    $completed_total[$course] = 0;
                    $initinere_total[$course] = 0;
                    $notstarted_total[$course] = 0;
                    $courses_total[$course] = 0;
                }

                $buffer->openBody();
                foreach ($temp as $id_user => $table_row) {
                    $line = array();
                    $line[] = $table_row['username'];
                    $line[] = $table_row['fullname'];
                    foreach ($courses as $course) {
                        if(isset($table_row['courses'][$course])) {

                            if ($cols['completed']) $line[] = ($table_row['courses'][$course] == 2 ? 1 : 0);
                            if ($cols['completed'] && $cols['show_percentages']) $line[] = ($table_row['courses'][$course] == 2 ? '100'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL : '0'._PERCENT_SIMBOL);
                            if ($cols['initinere']) $line[] = ($table_row['courses'][$course] == 1 ? 1 : 0);
                            if ($cols['initinere'] && $cols['show_percentages']) $line[] = ($table_row['courses'][$course] == 1 ? '100'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL : '0'._PERCENT_SIMBOL);
                            if ($cols['notstarted']) $line[] = ($table_row['courses'][$course] == 0 ? 1 : 0);
                            if ($cols['notstarted'] && $cols['show_percentages']) $line[] = ($table_row['courses'][$course] == 0 ? '100'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL : '0'._PERCENT_SIMBOL);

                            switch ((int)$table_row['courses'][$course]) {
                                case 2: if (isset($completed_total[$course])) $completed_total[$course] += 1; else $completed_course[$course] = 1; break;
                                case 1: if (isset($initinere_total[$course])) $initinere_total[$course] += 1; else $initinere_course[$course] = 1; break;
                                case 0: if (isset($notstarted_total[$course])) $notstarted_total[$course] += 1; else $notstarted_course[$course] = 1; break;
                            }

                            if (isset($courses_total[$course])) $courses_total[$course] += 1; else $courses_total[$course] = 1;
                        } else {

                            if ($cols['completed']) $line[] = '0';
                            if ($cols['completed'] && $cols['show_percentages']) $line[] = '0'._PERCENT_SIMBOL;
                            if ($cols['initinere']) $line[] = '0';
                            if ($cols['initinere'] && $cols['show_percentages']) $line[] = '0'._PERCENT_SIMBOL;
                            if ($cols['notstarted']) $line[] = '0';
                            if ($cols['notstarted'] && $cols['show_percentages']) $line[] = '0'._PERCENT_SIMBOL;

                            if (isset($courses_total[$course])) $courses_total[$course] += 1; else $courses_total[$course] = 1;
                        }
                    }
                    $buffer->addLine($line);
                }
                $buffer->closeBody();

                $totals_line = array('', '');
                foreach ($courses as $course) {

                    $completed_num = isset($completed_total[$course]) ? $completed_total[$course] : '0';
                    $initinere_num = isset($initinere_total[$course]) ? $initinere_total[$course] : '0';
                    $notstarted_num = isset($notstarted_total[$course]) ? $notstarted_total[$course] : '0';
                    $total_num = isset($courses_total[$course]) ? $courses_total[$course] : '0';

                    if ($cols['completed']) $totals_line[] = $completed_num;
                    if ($cols['completed'] && $cols['show_percentages']) $totals_line[] = $total_num!=0 ? number_format(100.0*$completed_num/$total_num, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL;
                    if ($cols['initinere']) $totals_line[] = $initinere_num;
                    if ($cols['initinere'] && $cols['show_percentages']) $totals_line[] = $total_num!=0 ? number_format(100.0*$initinere_num/$total_num, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL;
                    if ($cols['notstarted']) $totals_line[] = $notstarted_num;
                    if ($cols['notstarted'] && $cols['show_percentages']) $totals_line[] = $total_num!=0 ? number_format(100.0*$notstarted_num/$total_num, 2, _DECIMAL_SEPARATOR, '')._PERCENT_SIMBOL : '0'._DECIMAL_SEPARATOR.'00'._PERCENT_SIMBOL;
                }
                $buffer->setFoot($totals_line);

                $buffer->closeTable();

                $html .= $buffer->get();
            } break;

        }


        return $html;

    }


    //----------------------------------------------------------------------------

    function show_report_coursecategories($data = NULL, $other = '') {
        if ($data===NULL)
            cout( $this->_get_coursecategories_query() );
        else
            cout( $this->_get_coursecategories_query('html', $data, $other) );
    }

    function get_coursecategories_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');
        require_once($GLOBALS['where_lms'].'/lib/category/lib.categorytree.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['undo_filter'])) Util::jump_to($back_url);


        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'all_categories' => true,
                'selected_categories' => array(),
                'showed_columns' => array(/*'completed'=>true, 'initinere'=>true, 'notstarted'=>true, 'show_percentages'=>true*/)
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];

        $tree = new \CourseCategoryTree('course_categories_selector', false, false, _TREE_COLUMNS_TYPE_RADIO);
        $tree->init();

        if (isset($_POST['update_tempdata'])) {

            $ref['selected_categories'] = isset($_POST['course_categories_selector_input']) ? explode(",", $_POST['course_categories_selector_input']) : array();
            $ref['showed_columns'] = array();
        } else {

            if ( isset($ref['selected_categories']) && count($ref['selected_categories'])>0 )
                $tree->setInitialSelection($ref['selected_categories']);
        }

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            Util::jump_to($back_url);
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }


        //produce output
        $html = '';
        $output = $tree->get(true, true, 'treeCat');

        cout($output['js'], 'page_head');

        $box = new ReportBox('coursecategories_selector');
        $box->title = $this->lang->def('_COURSES_SELECTION_TITLE');
        $box->description = false;

        $boxlang =& DoceboLanguage::createInstance('report', 'framework');
        $box->body .= '<div class="">'.$output['html'].'</div>';
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);
        $box->body .= Form::openButtonSpace();
        $box->body .= '<button class="button" type="button" onclick="treeCat.clearSelection();">'. Lang::t('_RESET', 'standard').'</button>';
        $box->body .= Form::closeButtonSpace();

        $html = $box->get();
        cout($html);
    }



    function _get_coursecategories_query($type = 'html', $report_data = NULL, $other = '') {
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');


        if ($report_data==NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $fw  = $GLOBALS['prefix_fw'];
        $lms = $GLOBALS['prefix_lms'];

        $sel_all = $ref['rows_filter']['select_all'];
        $sel_type = $ref['rows_filter']['selection_type'];
        $selection = $ref['rows_filter']['selection'];

        $categories = $ref['columns_filter']['selected_categories'];
        $cols =& $ref['columns_filter']['showed_columns'];

        if (!$sel_all && count($selection)<=0) {
            cout( '<p>'.$this->lang->def('_EMPTY_SELECTION').'</p>' );
            return;
        }

        $acl = new DoceboACLManager();
        $acl->include_suspended = true;
        $html = '';


        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl->getAllUsersFromIdst($admin_tree);
            $admin_users = array_unique($admin_users);
        }

        //course categories names
        $res = sql_query("SELECT * FROM ".$lms."_category ");
        $categories_names = array();
        $categories_limit = array();
		while ($row = sql_fetch_assoc($res)) {
            $categories_names[ $row['idCategory'] ] = ($row['path']!='/root/' ? end( explode("/", $row['path'])) : Lang::t('_CATEGORY', 'admin_course_management', 'lms'));// Lang::t('_ROOT'));
            $categories_paths[ $row['idCategory'] ] = ($row['path']!='/root/' ? substr($row['path'], 5, (strlen($row['path'])-5)) : Lang::t('_CATEGORY', 'admin_course_management'));// Lang::t('_ROOT'));
            $categories_limit[ $row['idCategory'] ] = array($row['iLeft'], $row['iRight']);
        }

        $user_courses = false;
        if(Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {

            // if the usre is a subadmin with only few course assigned
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());

            if(isset($admin_courses['course'][0]))
                $user_course = false;
            elseif(isset($admin_courses['course'][-1]))
            {
                require_once(_lms_.'/lib/lib.catalogue.php');
                $cat_man = new Catalogue_Manager();

                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if(count($user_catalogue) > 0)
                {
                    $courses = array(0);

                    foreach($user_catalogue as $id_cat)
                    {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    foreach($courses as $id_course)
                        if($id_course != 0)
                            $admin_courses['course'][$id_course] = $id_course;
                }
                elseif(Get::sett('on_catalogue_empty', 'off') == 'on')
                    $user_course = false;

                $user_courses = $admin_courses['course'];
            }
            else
            {
                $array_courses = array();
                $array_courses = array_merge($array_courses, $admin_courses['course']);

                if(!empty($admin_courses['coursepath']))
                {
                    require_once(_lms_.'/lib/lib.coursepath.php');
                    $path_man = new Catalogue_Manager();
                    $coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
                    $array_courses = array_merge($array_courses, $coursepath_course);
                }
                if(!empty($admin_courses['catalogue']))
                {
                    require_once(_lms_.'/lib/lib.catalogue.php');
                    $cat_man = new Catalogue_Manager();
                    foreach($admin_courses['catalogue'] as $id_cat)
                    {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
                        $array_courses = array_merge($array_courses, $catalogue_course);
                    }
                }
                $admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);

                $user_courses = $admin_courses['course'];
            }
        }
        //create table
        switch ($sel_type) {

            case 'users': {
                //table data
                $data = array();

                $head1 = array('');
                $head2 = array( $this->lang->def('_USER'));

                $totals = array();

                foreach ($categories as $idcat) {
                    $index = (int)str_replace("d", "", $idcat);
                    $head1[] = array('colspan'=>2, 'value'=>$categories_paths[$index]);
                    $head2[] = $this->lang->def('_COMPLETED');
                    $head2[] = $this->lang->def('incomplete');

                    $is_descendant = strpos($idcat, "d");
                    if ($is_descendant === false) {
                        $condition = " AND cat.idCategory=".$index." ";
                    } else {
                        list($left, $right) = $categories_limit[$index];//sql_fetch_row( sql_query("SELECT iLeft, iRight FROM ".$lms."_category WHERE idCAtegory=".$index) );
                        $condition = " AND cat.iLeft >= ".$left." AND cat.iRight <= ".$right." ";
                    }

                    //resolve user selection
                    if ($sel_all)
                        $selection = $acl->getAllUsersIdst();
                    else
                        $selection = $acl->getAllUsersFromIdst( $selection ); //resolve group and orgchart selection

                    $query = "SELECT cu.idUser, cat.idCategory, c.idCourse, c.code, cu.status "
                        ." FROM ".$lms."_course as c JOIN ".$lms."_category as cat JOIN ".$lms."_courseuser as cu "
                        ." ON (c.idCourse=cu.idCourse AND c.idCategory=cat.idCategory) "
                        ." WHERE ".($sel_all ? " 1 " : " cu.idUser IN (".implode(",", $selection).") " )
                        .$condition
                        .( $user_courses != false ? " AND c.idCourse IN ( '".implode("','", $user_courses)."' ) " : '' );

                    $res = sql_query($query);
                    $temp = array();
                    $total_1 = 0;
                    $total_2 = 0;
					while ($row = sql_fetch_assoc($res)) {
                        $iduser = $row['idUser'];

                        if (!isset($temp[ $iduser ]))
                            $temp[ $iduser ] = array(
                                'completed' => 0,
                                'not_completed' => 0
                            );

                        switch ($row['status']) {
                            case 0:
                            case 1: { $temp[$iduser]['not_completed']++; $total_2++; } break;
                            case 2: { $temp[$iduser]['completed']++; $total_1++; } break;
                        }
                    }

                    $totals[] = $total_1;
                    $totals[] = $total_2;

                    $data[ $index ] = $temp;
                    //unset($temp); //free memory
                }

                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $buffer->openHeader();
                $buffer->addHeader($head1);
                $buffer->addHeader($head2);
                $buffer->closeHeader();

                //retrieve usernames
                $usernames = array();
                $res = sql_query("SELECT idst, userid FROM ".$fw."_user WHERE idst IN (".implode(",", $selection).")");
                while (list($idst, $userid) = sql_fetch_row($res))
                    $usernames[$idst] = $acl->relativeId( $userid );

                //user cycle
                $buffer->openBody();
                foreach ($selection as $user) {
                    $line = array();

                    $line[] = ( isset($usernames[ $user ]) ? $usernames[ $user ] : '' );
                    foreach ($categories as $idcat) {
                        if ($idcat != '') {
                            $index = (int)str_replace("d", "", $idcat);
                            if (isset($data[$index][$user])) {
                                $line[] = $data[$index][$user]['completed'];
                                $line[] = $data[$index][$user]['not_completed'];
                            } else {
                                $line[] = '0';
                                $line[] = '0';
                            }
                        }
                    }

                    $buffer->addLine($line);
                }
                $buffer->closeBody();

                //set totals
                $foot = array('');
                foreach ($totals as $total) { $foot[] = $total; }
                $buffer->setFoot($foot);

                //unset($data); //free memory
                $buffer->closeTable();
                $html .= $buffer->get();
            } break;

            //-----------------------------------------

            case 'groups': {
                //table data
                $data = array();

                //retrieve all labels
                $orgchart_labels = array();
                $query = "SELECT * FROM ".$fw."_org_chart WHERE lang_code='".getLanguage()."'";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    $orgchart_labels[$row['id_dir']] = $row['translation'];
                }

                $labels = array();
                //$query = "SELECT * FROM ".$fw."_group WHERE (hidden='false' OR groupid LIKE '/oc_%' OR groupid LIKE '/ocd_%') AND type='free'";
                $query = "SELECT * FROM ".$fw."_group WHERE groupid LIKE '/oc\_%' OR groupid LIKE '/ocd\_%' OR hidden = 'false' ";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    if ($row['hidden']=='false') {
                        $labels[$row['idst']] = $acl->relativeId($row['groupid']);
                    } else {
                        $temp = explode("_", $row['groupid']); //echo '<div>'.print_r($temp,true).'</div>';
                        if ($temp[0]=='/oc') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        } elseif ($temp[0]=='/ocd') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        }
                    }
                }


                //solve groups user
                $solved_groups = array();
                $subgroups_list = array();
                foreach ($selection as $group) {
                    $temp = $acl->getGroupGDescendants($group);
                    $temp[] = $group;
                    foreach ($temp as $idst_subgroup) {
                        $solved_groups[$idst_subgroup] = $group;
                    }
                    $subgroups_list = array_merge( $subgroups_list, $temp );
                }

                $tot_size = 2;
                $totals = array();
                $head1 = array( array('colspan'=>2, 'value'=>$this->lang->def('_GROUPS')) );
                $head2 = array($this->lang->def('_NAME'), $this->lang->def('_TOTAL'));

                foreach ($categories as $idcat) {
                    $index = (int)str_replace("d", "", $idcat);
                    $head1[] = array('colspan'=>2, 'value'=>$categories_paths[$index]);
                    $head2[] = $this->lang->def('_COMPLETED');
                    $head2[] = $this->lang->def('incomplete');

                    $is_descendant = strpos($idcat, "d");
                    $condition = '';
                    if ($is_descendant === false) {
                        $condition = " AND cat.idCategory=".$index." ";
                    } else {
                        list($left, $right) = $categories_limit[$index];//sql_fetch_row( sql_query("SELECT iLeft, iRight FROM ".$lms."_category WHERE idCAtegory=".$index) );
                        $condition = " AND cat.iLeft >= ".$left." AND cat.iRight <= ".$right." ";
                    }


                    $query = "SELECT gm.idst as idGroup, cu.idUser, cat.idCategory, c.idCourse, c.code, cu.status "
                        ." FROM ".$lms."_course as c JOIN ".$lms."_category as cat JOIN ".$lms."_courseuser as cu JOIN ".$fw."_group_members as gm "
                        ." ON (c.idCourse=cu.idCourse AND c.idCategory=cat.idCategory AND cu.idUser=gm.idstMember) "
                        ." WHERE ".($sel_all ? " 1 " : " gm.idst IN (".implode(",", $subgroups_list).") " ) //idst of the groups
                        .$condition
                        .( $user_courses != false ? " AND c.idCourse IN ( '".implode("','", $user_courses)."' ) " : '' );

                    $res = sql_query($query);
                    $temp = array();
                    $total_1 = 0;
                    $total_2 = 0;
					while ($row = sql_fetch_assoc($res)) {
                        $id_group = $solved_groups[ $row['idGroup'] ];

                        if (!isset($temp[ $id_group ]))
                            $temp[ $id_group ] = array(
                                'completed' => 0,
                                'not_completed' => 0
                            );

                        switch ($row['status']) {
                            case 0:
                            case 1: { $temp[$id_group]['not_completed']++; $total_2++; } break;
                            case 2: { $temp[$id_group]['completed']++; $total_1++; } break;
                        }
                    }

                    $totals[]= $total_1;
                    $totals[]= $total_2;

                    $data[ $index ] = $temp;
                    //unset($temp); //free memory
                }


                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $buffer->openHeader();
                $buffer->addHeader($head1);
                $buffer->addHeader($head2);
                $buffer->closeHeader();

                $tot_users = 0;
                $buffer->openBody();

                foreach ($selection as $dir_id=>$group_id) {
                    $group_users = $acl->getGroupAllUser($group_id);
                    if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) { $group_users = array_intersect($group_users, $admin_users); }
                    $users_num = count($group_users);

                    $line = array();
                    $line[] = $labels[$group_id];
                    $line[] = $users_num;
                    $tot_users += $users_num;


                    foreach ($categories as $idcat) {
                        if ($idcat != '') {
                            $index = (int)str_replace("d", "", $idcat);
                            if (isset($data[$index][$group_id])) {
                                $line[] = $data[$index][$group_id]['completed'];
                                $line[] = $data[$index][$group_id]['not_completed'];
                            } else {
                                $line[] = '0';
                                $line[] = '0';
                            }
                        }
                    }

                    $buffer->addLine($line);
                }
                $buffer->closeBody();

                //totals ...

                $foot = array('', $tot_users);
                foreach ($totals as $total) {	$foot[] = $total;	}
                $buffer->setFoot($foot);

                $buffer->closeTable();
                $html .= $buffer->get();
            } break;

        } //end switch

        $GLOBALS['page']->add($html, 'content');
    }

    //----------------------------------------------------------------------------

    function show_report_time($data = NULL, $other = '') {
        if ($data===NULL)
            cout( $this->_get_time_query() );
        else
            cout( $this->_get_time_query('html', $data, $other) );
    }

    function get_time_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        //$sel = new Course_Manager();
        //$sel->setLink('index.php?modname=report&op=report_rows_filter');

        if (isset($_POST['undo_filter'])) Util::jump_to($back_url);


        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'timetype' => 'years',
                'years' => 1,
                'months' => 12
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];


        if (isset($_POST['update_tempdata'])) {
            $ref['years'] = Get::req('years', DOTY_INT, 1);
        } else {
            //...
        }

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            Util::jump_to($back_url);
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        $box = new ReportBox('choose_time');
        $box->title = $this->lang->def('_CHOOSE_TIME');
        $box->description = false;

        $year = date('Y');
        $dropdownyears = array(
            1 => $year,
            2 => $year.' - '.($year - 1),
            3 => $year.' - '.($year - 2),
            4 => $year.' - '.($year - 3),
            5 => $year.' - '.($year - 4),
            6 => $year.' - '.($year - 5),
            7 => $year.' - '.($year - 6),
        );
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);
        $box->body .= Form::getDropDown($this->lang->def('_RA_CAT_TIME'), 'years', 'years', $dropdownyears, $ref['years']);

        $html = $box->get();
        cout($html);

    }

    function _get_time_query($type = 'html', $report_data = NULL, $other = '') {
        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

        if ($report_data==NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $fw  = $GLOBALS['prefix_fw'];
        $lms = $GLOBALS['prefix_lms'];

        $sel_all = $ref['rows_filter']['select_all'];
        $sel_type = $ref['rows_filter']['selection_type'];
        $selection = $ref['rows_filter']['selection'];

        $timetype = $ref['columns_filter']['timetype'];
        $years =& $ref['columns_filter']['years'];
        $months =& $ref['columns_filter']['months'];

        if (!$sel_all && count($selection)<=0) {
            cout( '<p>'.$this->lang->def('_EMPTY_SELECTION').'</p>' );
            return;
        }

        $acl = new DoceboACLManager();
        $acl->include_suspended = true;

        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
            $admin_users = array_unique($admin_users);
        }

        $html = '';
        $times = array();
        switch ($timetype) {
            case 'years': {
                $now = date('Y');
                for ($i = $now-$years+1; $i<=$now; $i++) { $times[] = $i; }
            } break;
            case 'months':{
                //...
            } break;
        }

        switch ($sel_type) {

            case 'users': {
                $data = array();

                $users_list = ($sel_all ? $acl->getAllUsersIdst() : $acl->getAllUsersFromIdst($selection) );
                $users_list = array_unique($users_list);
                if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) $users_list = array_intersect($users_list, $admin_users);

                $query = "SELECT idUser, YEAR(date_complete) as yearComplete "
                    ." FROM ".$lms."_courseuser "
                    ." WHERE status=2 "
                    .( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()? " AND idUser IN (".implode(",", $users_list).") " : "" );


                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    //$data[ $row['idUser'] ][ $row['yearComplete'] ] = $row['complete'];
                    $idUser = $row['idUser'];
                    $year = $row['yearComplete'];
                    if (!isset($data[ $idUser ][ $year ])) $data[ $idUser ][ $year ] = 0;
                    $data[ $idUser ][ $year ]++;
                }

                $usernames = array();
                $query = "SELECT idst, userid FROM ".$fw."_user WHERE idst IN (".implode(",", $users_list).")";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    $usernames[ $row['idst'] ] = $acl->relativeId( $row['userid'] );
                }

                //draw table
                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $head = array($this->lang->def('_USER'));
                foreach ($times as $time) {
                    $head[] = $time;
                }
                $head[] = $this->lang->def('_TOTAL');

                $buffer->openHeader();
                $buffer->addHeader($head);
                $buffer->closeHeader();

                $tot_total = 0;
                $buffer->openBody();
                foreach ($users_list as $user) {

                    if(!isset($usernames[$user])) break;
                    $line = array();
                    $line_total = 0;
                    $line[] = $usernames[$user];
                    foreach ($times as $time) { //years or months

                        switch ($timetype) {

                            case 'years': {
                                if (isset($data[$user][$time])) {
                                    $line[] = $data[$user][$time];
                                    $line_total += $data[$user][$time];
                                } else
                                    $line[] = '0';
                            } break;

                            case 'months': {
                                //$year = ...
                                //$month = ...
                                //$line[] = (isset($data[$group][$year][$month]) ? $data[$group][$year][$month] : '0'); break;
                            }

                        }

                    }

                    $line[] = $line_total;
                    $tot_total += $line_total;
                    $buffer->addLine($line);
                }

                $buffer->closeBody();

                //totals
                $foot = array('');
                foreach ($times as $time) {
                    $temp = 0;
                    foreach ($users_list as $user) {
                        if (isset($data[$user][$time])) $temp += $data[$user][$time];
                    }
                    $foot[] = $temp;
                }
                $foot[] = $tot_total;
                $buffer->setFoot($foot);

                $buffer->closeTable();
                $html .= $buffer->get();
            } break;



            //--------------------

            case 'groups': {
                //retrieve all labels
                $orgchart_labels = array();
                $query = "SELECT * FROM ".$fw."_org_chart WHERE lang_code='".getLanguage()."'";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    $orgchart_labels[$row['id_dir']] = $row['translation'];
                }

                $labels = array();
                //$query = "SELECT * FROM ".$fw."_group WHERE (hidden='false' OR groupid LIKE '/oc_%' OR groupid LIKE '/ocd_%') AND type='free'";
                $query = "SELECT * FROM ".$fw."_group WHERE groupid LIKE '/oc\_%' OR groupid LIKE '/ocd\_%' OR hidden = 'false' ";
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    if ($row['hidden']=='false') {
                        $labels[$row['idst']] = $acl->relativeId($row['groupid']);
                    } else {
                        $temp = explode("_", $row['groupid']); //echo '<div>'.print_r($temp,true).'</div>';
                        if ($temp[0]=='/oc') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        } elseif ($temp[0]=='/ocd') {
                            $labels[$row['idst']] = ($temp[1]!=0 ? $orgchart_labels[$temp[1]] : '');
                        }
                    }
                }


                //solve groups user
                $solved_groups = array();
                $subgroups_list = array();
                foreach ($selection as $group) {
                    $temp = $acl->getGroupGDescendants($group);
                    $temp[] = $group;
                    foreach ($temp as $idst_subgroup) {
                        $solved_groups[$idst_subgroup] = $group;
                    }
                    $subgroups_list = array_merge( $subgroups_list, $temp );
                }



                $query = "SELECT gm.idst as idGroup, YEAR(cu.date_complete) as yearComplete, MONTH(cu.date_complete) as monthComplete "
                    ." FROM ".$lms."_courseuser as cu JOIN ".$fw."_group_members as gm ON (cu.idUser=gm.idstMember) "
                    ." WHERE status=2 AND gm.idst IN (".implode(",", $subgroups_list).")";

                $data = array();
                $res = sql_query($query);
				while ($row = sql_fetch_assoc($res)) {
                    $idGroup = $solved_groups[ $row['idGroup'] ];
                    $year = $row['yearComplete'];
                    $month = $row['monthComplete'];

                    switch ($timetype) {

                        case 'years': {
                            if (!isset($data[ $idGroup ][$year])) $data[ $idGroup ][$year] = 0;
                            $data[ $idGroup ][$year]++;
                        } break;

                        case 'months': {
                            if (!isset($data[ $idGroup ][$year][$month])) $data[ $idGroup ][$year][$month] = 0;
                            $data[ $idGroup ][$year][$month]++;
                        } break;

                    } //end switch
                }


                //draw table
                $buffer = new ReportTablePrinter($type, true);
                $buffer->openTable('','');

                $head = array($this->lang->def('_GROUPS'), $this->lang->def('_USERS'));
                foreach ($times as $time) {
                    $head[] = $time;
                }
                $head[] = $this->lang->def('_TOTAL');

                $buffer->openHeader();
                $buffer->addHeader($head);
                $buffer->closeHeader();

                $tot_users = 0;
                $tot_total = 0;
                $buffer->openBody();
                foreach ($selection as $group) {
                    $group_users = $acl->getGroupAllUser($group);
                    if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) { $group_users = array_intersect($group_users, $admin_users); }
                    $users_num = count($group_users);

                    $line = array();
                    $line_total = 0;
                    $line[] = $labels[$group];
                    $line[] = $users_num;
                    foreach ($times as $time) { //years or months

                        switch ($timetype) {

                            case 'years': {
                                if (isset($data[$group][$time])) {
                                    $line[] = $data[$group][$time];
                                    $line_total += $data[$group][$time];
                                } else
                                    $line[] = '0';
                            } break;

                            case 'months': {
                                //$year = ...
                                //$month = ...
                                //$line[] = (isset($data[$group][$year][$month]) ? $data[$group][$year][$month] : '0'); break;
                            }

                        }

                    }

                    $line[] = $line_total;
                    $tot_users += $users_num;
                    $tot_total += $line_total;
                    $buffer->addLine($line);
                }

                $buffer->closeBody();

                //totals
                $foot = array('', $tot_users);
                foreach ($times as $time) {
                    $temp = 0;
                    foreach ($selection as $group) {
                        if (isset($data[$group][$time])) $temp += $data[$group][$time];
                    }
                    $foot[] = $temp;
                }
                $foot[] = $tot_total;
                $buffer->setFoot($foot);

                $buffer->closeTable();
                $html .= $buffer->get();
            } break;

        } //end switch

        cout($html);
    }



    //----------------------------------------------------------------------------
    //---- communications report part --------------------------------------------
    //----------------------------------------------------------------------------


    function show_report_communications($data = NULL, $other = '') {
        if ($data===NULL)
            cout( $this->_get_communications_query() );
        else
            cout( $this->_get_communications_query('html', $data, $other) );
    }

    function get_communications_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        //preliminary checks
        if (isset($_POST['undo_filter'])) Util::jump_to($back_url);

        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'comm_selection' => array(),
                'all_communications' => false,
                'comm_start_date' => '',
                'comm_end_date' => ''
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];


        if (isset($_POST['update_tempdata'])) {
            $ref['all_communications'] = Get::req('all_communications', DOTY_INT, 0) > 0;
            $ref['comm_selection'] = Get::req('comm_selection', DOTY_MIXED, array());
            $ref['comm_start_date'] = Format::dateDb(Get::req('comm_start_date', DOTY_STRING, ''), 'date');
            $ref['comm_end_date'] = Format::datedb(Get::req('comm_end_date', DOTY_STRING, ''), 'date');
        } else {
            //...
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        //draw filter boxes
        $html = '';

        //time period
        $box = new ReportBox('comm_selector');
        $box->title = Lang::t('_TIME_PERIOD_FILTER', 'report');
        $box->description = false;
        $box->body .= Form::getDatefield(Lang::t('_FROM', 'standard'), 'comm_start_date', 'comm_start_date', $ref['comm_start_date']);
        $box->body .= Form::getDatefield(Lang::t('_TO', 'standard'), 'comm_end_date', 'comm_end_date', $ref['comm_end_date']);

        $html = $box->get();

        //communications selector
        $box = new ReportBox('comm_selector');
        $box->title = Lang::t('_COMMUNICATIONS', 'report');
        $box->description = false;

        require_once(_lms_.'/lib/lib.report.php'); //the comm. table function
        $box->body .= Form::getCheckbox(Lang::t('_ALL', 'report'), 'all_communications', 'all_communications', 1, $ref['all_communications']);
        $box->body .= '<br />';
        $box->body .= getCommunicationsTable($ref['comm_selection']);
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);

        $html .= $box->get();

        cout($html);
    }




    function _get_communications_query($type = 'html', $report_data = NULL, $other = '') {

        if ($report_data==NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $_ERR_NOUSER = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NOCOMM = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NODATA = Lang::t('_EMPTY_SELECTION', 'report');

        $lang_type = array(
            'none' => Lang::t('_NONE', 'communication'),
            'file' => Lang::t('_LONAME_item', 'storage'),
            'scorm' => Lang::t('_LONAME_scormorg', 'storage')
        );

        $sel_all = $ref['rows_filter']['select_all'];
        $arr_selected_users = $ref['rows_filter']['selection']; //list of users selected in the filter (users, groups and org.branches)

        $comm_all = $ref['columns_filter']['all_communications'];
        $arr_selected_comm = $ref['columns_filter']['comm_selection']; //list of communications selected in the filter

        $start_date = isset($ref['columns_filter']['comm_start_date']) ? substr($ref['columns_filter']['comm_start_date'], 0, 10) : '';
        $end_date = isset($ref['columns_filter']['comm_end_date']) ? substr($ref['columns_filter']['comm_end_date'], 0, 10) : '';

        //check and validate time period dates
        if (!preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $start_date) || $start_date == '0000-00-00')
            $start_date = '';
        if (!preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $end_date) || $end_date == '0000-00-00')
            $end_date = '';

        if ($start_date != '') $start_date .= ' 00:00:00';
        if ($end_date != '') $end_date .= ' 23:59:59';
        if ($start_date != '' && $end_date != '')
            if ($start_date > $end_date) { //invalid time period
                $start_date = '';
                $end_date = '';
            }


        //instantiate an acl manager
        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = true;

        //extract user idst from selection
        if ($sel_all) {
            $arr_selected_users = $acl_man->getAllUsersIdst();
        } else {
            $arr_selected_users = $acl_man->getAllUsersFromIdst($arr_selected_users);
        }

        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
            $admin_users = array_unique($admin_users);
            //filter users selection by admin visible users
            $arr_selected_users = array_intersect($arr_selected_users, $admin_users);
            //free some memory
            unset($admin_tree);
            unset($admin_users);
            unset($adminManager);
        }

        //Has the "All communications" options been selected ?
        if ($comm_all) {
            $query = "SELECT id_comm FROM %lms_communication";
            $res = $this->db->query($query);
            $arr_selected_comm = array();
            while (list($id_comm) = $this->db->fetch_row($res))
                $arr_selected_comm[] = $id_comm;
        }

        //check selected users ...
        //$arr_selected_users = array(); //list of users selected in the filter (users, groups and org.branches)
        if ($arr_selected_users <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        //$arr_selected_comm = array(); //list of communications selected in the filter
        if ($arr_selected_comm <= 0) {
            out('<p>'.$_ERR_NOCOMM.'</p>');
            return;
        }

        //order selected communications by publish date
        $arr_selected_comm = array_unique($arr_selected_comm);
        $query = "SELECT id_comm FROM %lms_communication "
            ." WHERE id_comm IN (".implode(",", $arr_selected_comm).") "
            ." ORDER BY publish_date DESC, title ASC";
        $res = $this->db->query($query);
        if ($this->db->num_rows($res) == count($arr_selected_comm)) {
            $arr_selected_comm = array();
            while (list($id_comm) = $this->db->fetch_row($res)) {
                $arr_selected_comm[] = $id_comm;
            }
        }

        $arr_comm = array(); //array $id_comm => list of generic idst
        foreach ($arr_selected_comm as $id_comm) $arr_comm[$id_comm] = array(); //if no users have been assigned to the games, than display as 0 - 0
        $arr_idst = array(); //flat list of idst
        $query = "SELECT * FROM %lms_communication_access WHERE id_comm IN (".implode(",", $arr_selected_comm).")";
        $res = $this->db->query($query);
        while (list($id_comm, $idst) = $this->db->fetch_row($res)) {
            $arr_idst[] = $idst;
            $arr_comm[$id_comm][] = $idst;
        }

        if (count($arr_idst) <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        $arr_groups = array(); //flat list of group idst
        $query = "SELECT idst FROM %adm_group WHERE idst IN (".implode(",", $arr_idst).")";
        $res = $this->db->query($query);
        while (list($idst) = $this->db->fetch_row($res)) {
            $arr_groups[] = $idst;
        }

        //if any group selected, then extract users and create an array [id_group][id_user]
        $arr_idst_users_flat = array();
        $arr_members = array(); //array $idst group => list of idst
        if (count($arr_groups) > 0) {
            $query = "SELECT idst, idstMember FROM %adm_group_members WHERE "
                ." idst IN (".implode(",", $arr_groups).")"
                ." AND idstMember IN (".implode(",", $arr_selected_users).")";
            $res = $this->db->query($query);
            while (list($idst, $idstMember) = $this->db->fetch_row($res)) {
                $arr_members[$idst][] = $idstMember;
                $arr_idst_users_flat[] = $idstMember;
            }
        }

        //set an array with all users idst ($_all)
        $diff = array_diff($arr_selected_users, $arr_groups);
        $_all_users = array_merge($arr_idst_users_flat, $diff);
        unset($diff);

        if (count($_all_users) <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        //users have been extracted by group, now calculate report's rows ----------

        //get communications info data and put it in an array by id_comm => {info}
        $arr_comm_data = array();
        $query = "SELECT * FROM %lms_communication WHERE id_comm IN (".implode(",", $arr_selected_comm).")";
        $res = $this->db->query($query);
        while ($obj = $this->db->fetch_obj($res)) {
            $arr_comm_data[$obj->id_comm] = array(
                'title' => $obj->title,
                'type_of' => $obj->type_of,
                'publish_date' => $obj->publish_date
            );
        }

        //which selected communication has been seen by selected users?
        $arr_viewed = array();
        $query = "SELECT idReference, COUNT(idUser) as count "
            ." FROM %lms_communication_track WHERE status IN ('completed', 'passed') "
            ." AND idUser IN (".implode(",", $_all_users).") "
            ." AND id_comm IN (".implode(",", $arr_selected_comm).") "
            .($start_date != '' ? " AND dateAttempt >= '".$start_date."' " : "")
            .($end_date != '' ? " AND dateAttempt <= '".$end_date."' " : "")
            ." GROUP BY id_comm";
        $res = $this->db->query($query);
        while ($obj = $this->db->fetch_obj($res)) {
            $arr_viewed[$obj->id_comm] = $obj->count;
        }

        /*
                //user details buffer
                $acl_man = Docebo::user()->getAclManager();
                $user_details = array();
                $query = "SELECT idst, userid FROM %adm_user WHERE idst IN (".implode(",", $_all_users).")";
                $res = $this->db->query($query);
                while ($obj = $this->db->fetch_obj($res)) {
                    $user_details[$obj->idst] = $acl_man($obj->userid);
                }
        */
        //set table properties and buffer
        $head = array(
            Lang::t('_DATE', 'report'),
            Lang::t('_COMMUNICATIONS_TITLE', 'report'),
            Lang::t('_COMMUNICATIONS_TYPE', 'report'),
            Lang::t('_COMMUNICATIONS_SEEN', 'report'),
            Lang::t('_COMMUNICATIONS_TOTAL', 'report'),
            Lang::t('_PERCENTAGE', 'report')
        );


        $buffer = new ReportTablePrinter();
        $buffer->openTable('','');

        $buffer->openHeader();
        $buffer->addHeader($head);
        $buffer->closeHeader();

        $buffer->openBody();


        //rows cycle
        foreach ($arr_comm as $id_comm => $comm_id_list) {
            //calculate total assigned users for every communication
            $count = 0;
            foreach ($comm_id_list as $idst) {
                if (array_key_exists($idst, $arr_members)) {
                    foreach ($arr_members[$idst] as $idst_user) {
                        $count++;
                    }
                } else {
                    $count++;
                }
            }

            //line (one per communication)
            $line = array();
            $type_of = $arr_comm_data[$id_comm]['type_of'];
            $seen = isset($arr_viewed[$id_comm]) ? $arr_viewed[$id_comm] : 0;

            $line[] = $arr_comm_data[$id_comm]['publish_date'];
            $line[] = $arr_comm_data[$id_comm]['title'];
            $line[] = isset($lang_type[$type_of]) ? $lang_type[$type_of] : '';
            //$line[] = $arr_comm_data[$id_comm]['publish_date'];
            $line[] = $seen;
            $line[] = $count;
            $line[] = number_format(($count > 0 ? $seen/$count : 0)*100, 2, ',', '').' %';

            $buffer->addLine($line);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        cout($buffer->get());
    }



    //----------------------------------------------------------------------------
    //---- games report part --------------------------------------------
    //----------------------------------------------------------------------------


    function show_report_games($data = NULL, $other = '') {
        if ($data===NULL)
            cout( $this->_get_games_query() );
        else
            cout( $this->_get_games_query('html', $data, $other) );
    }

    function get_games_filter() {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        //preliminary checks
        if (isset($_POST['undo_filter'])) Util::jump_to($back_url);

        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'comp_selection' => array(),
                'all_games' => false,
                'comp_start_date' => '',
                'comp_end_date' => ''
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];


        if (isset($_POST['update_tempdata'])) {
            $ref['all_games'] = Get::req('all_games', DOTY_INT, 0) > 0;
            $ref['comp_selection'] = Get::req('comp_selection', DOTY_MIXED, array());
            $ref['comp_start_date'] = Format::dateDb(Get::req('comp_start_date', DOTY_STRING, ''), 'date');
            $ref['comp_end_date'] = Format::datedb(Get::req('comp_end_date', DOTY_STRING, ''), 'date');
        } else {
            //...
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        //draw filter boxes
        $html = '';

        //time period
        $box = new ReportBox('comm_selector');
        $box->title = Lang::t('_TIME_PERIOD_FILTER', 'report');
        $box->description = false;
        $box->body .= Form::getDatefield(Lang::t('_FROM', 'standard'), 'comp_start_date', 'comp_start_date', $ref['comp_start_date']);
        $box->body .= Form::getDatefield(Lang::t('_TO', 'standard'), 'comp_end_date', 'comp_end_date', $ref['comp_end_date']);

        $html .= $box->get();

        //draw games selector
        $box = new ReportBox('comp_selector');
        $box->title = Lang::t('_CONTEST');
        $box->description = false;

        require_once(_lms_.'/lib/lib.report.php'); //the comm. table function
        $box->body .= Form::getCheckbox(Lang::t('_ALL', 'report'), 'all_games', 'all_games', 1, $ref['all_games']);
        $box->body .= '<br />';
        $box->body .= getGamesTable($ref['comp_selection']);
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);

        $html .= $box->get();

        cout($html);
    }




    function _get_games_query($type = 'html', $report_data = NULL, $other = '') {

        if ($report_data==NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $_ERR_NOUSER = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NOCOMP = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NODATA = Lang::t('_NO_CONTENT', 'report');

        require_once(_lms_.'/lib/lib.report.php');
        $lang_type = _getLOtranslations();

        $sel_all = $ref['rows_filter']['select_all'];
        $arr_selected_users = $ref['rows_filter']['selection']; //list of users selected in the filter (users, groups and org.branches)

        $comp_all = $ref['columns_filter']['all_games'];
        $arr_selected_comp = $ref['columns_filter']['comp_selection']; //list of communications selected in the filter

        $start_date = substr($ref['columns_filter']['comp_start_date'], 0, 10);
        $end_date = substr($ref['columns_filter']['comp_end_date'], 0, 10);

        //check and validate time period dates
        if (!preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $start_date) || $start_date == '0000-00-00')
            $start_date = '';
        if (!preg_match('/^(\d{4})\D?(0[1-9]|1[0-2])\D?([12]\d|0[1-9]|3[01])$/', $end_date) || $end_date == '0000-00-00')
            $end_date = '';

        if ($start_date != '') $start_date .= ' 00:00:00';
        if ($end_date != '') $end_date .= ' 23:59:59';
        if ($start_date != '' && $end_date != '')
            if ($start_date > $end_date) { //invalid time period
                $start_date = '';
                $end_date = '';
            }


        //instantiate acl manager
        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = true;

        //extract user idst from selection
        if ($sel_all) {
            $arr_selected_users = $acl_man->getAllUsersIdst();
        } else {
            $arr_selected_users = $acl_man->getAllUsersFromIdst($arr_selected_users);
        }

        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ( $userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);
            $admin_users = array_unique($admin_users);
            //filter users selection by admin visible users
            $arr_selected_users = array_intersect($arr_selected_users, $admin_users);
            //free some memory
            unset($admin_tree);
            unset($admin_users);
            unset($adminManager);
        }

        //Has the "All games" options been selected ?
        if ($comp_all) {
            $query = "SELECT id_game FROM %lms_games";
            $res = $this->db->query($query);
            $arr_selected_comp = array();
            while (list($id_game) = $this->db->fetch_row($res))
                $arr_selected_comp[] = $id_game;
        }

        //check selected users ...
        //$arr_selected_users = array(); //list of users selected in the filter (users, groups and org.branches)
        if ($arr_selected_users <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        //$arr_selected_comp = array(); //list of communications selected in the filter
        if ($arr_selected_comp <= 0) {
            cout('<p>'.$_ERR_NOCOMP.'</p>');
            return;
        }

        $arr_comp = array(); //array $id_comm => list of generic idst
        foreach ($arr_selected_comp as $id_game) $arr_comp[$id_game] = array(); //if no users have been assigned to the games, than display as 0 - 0
        $arr_idst = array(); //flat list of idst
        $query = "SELECT * FROM %lms_games_access WHERE id_comp IN (".implode(",", $arr_selected_comp).")";
        $res = $this->db->query($query);
        while (list($id_game, $idst) = $this->db->fetch_row($res)) {
            $arr_idst[] = $idst;
            $arr_comp[$id_game][] = $idst;
        }

        if (count($arr_idst) <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        $arr_groups = array(); //flat list of group idst
        $query = "SELECT idst FROM %adm_group WHERE idst IN (".implode(",", $arr_idst).")";
        $res = $this->db->query($query);
        while (list($idst) = $this->db->fetch_row($res)) {
            $arr_groups[] = $idst;
        }

        //if any group selected, then extract users and create an array [id_group][id_user]
        $arr_idst_users_flat = array();
        $arr_members = array(); //array $idst group => list of idst
        if (count($arr_groups) > 0) {
            $query = "SELECT idst, idstMember FROM %adm_group_members WHERE "
                ." idst IN (".implode(",", $arr_groups).")"
                ." AND idstMember IN (".implode(",", $arr_selected_users).")";
            $res = $this->db->query($query);
            while (list($idst, $idstMember) = $this->db->fetch_row($res)) {
                $arr_members[$idst][] = $idstMember;
                $arr_idst_users_flat[] = $idstMember;
            }
        }

        //set an array with all users idst ($_all)
        $diff = array_diff($arr_selected_users, $arr_groups);
        $_all_users = array_merge($arr_idst_users_flat, $diff);
        unset($diff);

        if (count($_all_users) <= 0) {
            cout('<p>'.$_ERR_NOUSER.'</p>');
            return;
        }

        //users have been extracted by group, now calculate report's rows ----------

        //get games info data and put it in an array by id_game => {info}
        $arr_comp_data = array();
        $query = "SELECT * FROM %lms_games WHERE id_game IN (".implode(",", $arr_selected_comp).")";
        $res = $this->db->query($query);
        while ($obj = $this->db->fetch_obj($res)) {
            $arr_comp_data[$obj->id_game] = array(
                'title' => $obj->title,
                'type_of' => $obj->type_of,
                'start_date' => $obj->start_date,
                'end_date' => $obj->end_date
            );
        }

        //which selected communication has been seen by selected users?
        $arr_viewed = array();
        $query = "SELECT idReference, COUNT(idUser) as count "
            ." FROM %lms_games_track WHERE status IN ('completed', 'passed') "
            ." AND idUser IN (".implode(",", $_all_users).") "
            ." AND idReference IN (".implode(",", $arr_selected_comp).") "
            .($start_date != '' ? " AND dateAttempt >= '".$start_date."' " : "")
            .($end_date != '' ? " AND dateAttempt <= '".$end_date."' " : "")
            ." GROUP BY idReference";
        $res = $this->db->query($query);
        while ($obj = $this->db->fetch_obj($res)) {
            $arr_viewed[$obj->idReference] = $obj->count;
        }

        //calculate average values, no conditions on the status
        $arr_average = array();
        $query = "SELECT idReference, AVG(current_score) as average_current_score, "
            ." AVG(max_score) as average_max_score, AVG(num_attempts) as average_num_attempts "
            ." FROM %lms_games_track WHERE idUser IN (".implode(",", $_all_users).") "
            ." AND idReference IN (".implode(",", $arr_selected_comp).") "
            .($start_date != '' ? " AND dateAttempt >= '".$start_date."' " : "")
            .($end_date != '' ? " AND dateAttempt <= '".$end_date."' " : "")
            ." GROUP BY idReference";
        $res = $this->db->query($query);
        while ($obj = $this->db->fetch_obj($res)) {
            $arr_average[$obj->idReference] = $obj;
        }

        /*
                //user details buffer
                $acl_man = Docebo::user()->getAclManager();
                $user_details = array();
                $query = "SELECT idst, userid FROM %adm_user WHERE idst IN (".implode(",", $_all_users).")";
                $res = $this->db->query($query);
                while ($obj = $this->db->fetch_obj($res)) {
                    $user_details[$obj->idst] = $acl_man($obj->userid);
                }
        */
        //set table properties and buffer
        $head = array(
            Lang::t('_GAMES_TITLE', 'report'),
            Lang::t('_GAMES_TYPE', 'report'),
            Lang::t('_FROM', 'standard'),
            Lang::t('_TO', 'standard'),
            Lang::t('_GAMES_ATTEMPTED', 'report'),
            Lang::t('_GAMES_TOTAL', 'report'),
            Lang::t('_GAMES_PERCENT', 'report'),
            Lang::t('_GAMES_AVG_SCORE', 'report'),
            Lang::t('_GAMES_AVG_MAX_SCORE', 'report'),
            Lang::t('_GAMES_AVG_NUM_ATTEMPTS', 'report'),
        );


        $buffer = new ReportTablePrinter();
        $buffer->openTable('','');

        $buffer->openHeader();
        $buffer->addHeader($head);
        $buffer->closeHeader();

        $buffer->openBody();


        //rows cycle
        foreach ($arr_comp as $id_game => $comp_id_list) {
            //calculate total assigned users for every communication
            $count = 0;
            foreach ($comp_id_list as $idst) {
                if (array_key_exists($idst, $arr_members)) {
                    foreach ($arr_members[$idst] as $idst_user) {
                        $count++;
                    }
                } else {
                    $count++;
                }
            }

            //line (one per communication)
            $line = array();
            $type_of = $arr_comp_data[$id_game]['type_of'];
            $completed_by = isset($arr_viewed[$id_game]) ? $arr_viewed[$id_game] : 0;

            $line[] = $arr_comp_data[$id_game]['title'];
            $line[] = isset($lang_type[$type_of]) ? $lang_type[$type_of] : '';
            $line[] = Format::date($arr_comp_data[$id_game]['start_date'], 'date');
            $line[] = Format::date($arr_comp_data[$id_game]['end_date'], 'date');
            $line[] = $completed_by;
            $line[] = $count;
            $line[] = number_format(($count > 0 ? $completed_by/$count : 0)*100, 2, ',', '').' %';

            $avg1 = isset($arr_average[$id_game]) ? $arr_average[$id_game]->average_current_score : '';
            $avg2 = isset($arr_average[$id_game]) ? $arr_average[$id_game]->average_max_score : '';
            $avg3 = isset($arr_average[$id_game]) ? $arr_average[$id_game]->average_num_attempts : '';

            $line[] = number_format($avg1, 2, ',', '.');
            $line[] = number_format($avg2, 2, ',', '.');
            $line[] = number_format($avg3, 2, ',', '.');

            $buffer->addLine($line);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        cout($buffer->get());
    }

}