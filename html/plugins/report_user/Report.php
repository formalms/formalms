<?php

namespace Plugin\report_user;
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
require_once(_base_ . '/lib/lib.mailer.php');
require_once(_lms_ . '/lib/lib.course.php');

define('_RU_CATEGORY_COURSES', 'courses');
define('_RU_CATEGORY_GENERAL', 'general');
define('_RU_CATEGORY_COMPETENCES', 'competences');
define('_RU_CATEGORY_DELAY', 'delay');
define('_RU_CATEGORY_LO', 'LO');
define('_RU_CATEGORY_TESTSTAT', 'TESTSTAT');
define('_RU_CATEGORY_SCORM', 'scorm');
define('_RU_CATEGORY_COMMUNICATIONS', 'communications');
define('_RU_CATEGORY_GAMES', 'games');

define('_COURSES_FILTER_SESSION_NUMBER', 'opt1');
define('_COURSES_FILTER_SCORE_INIT', 'opt2');
define('_COURSES_FILTER_SCORE_END', 'opt3');
define('_COURSES_FILTER_INSCRIPTION_DATE', 'opt4');
define('_COURSES_FILTER_END_DATE', 'opt5');
define('_COURSES_FILTER_LASTACCESS_DATE', 'opt6');
define('_COURSES_FILTER_FIRSTACCESS_DATE', 'opt7');
define('_COURSES_FILTER_SCORE_COURSE', 'opt8');

define('_FILTER_INTEGER', 'int');
define('_FILTER_DATE', 'date');

define('_MILESTONE_NONE', 'ml_none');
define('_MILESTONE_START', 'ml_start');
define('_MILESTONE_END', 'ml_end');

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

class Report extends \ReportPlugin
{
    //var $rows_filter = array();

    var $status_u = array();
    var $status_c = array();

    var $page_title = false;
    var $use_mail = true;

    var $courses_filter_definition = array();

    var $LO_types = false;

    var $delay_columns;
    var $LO_columns;

    function __construct($id_report, $report_name = false)
    {
        parent::__construct($id_report, $report_name);
        $this->lang =& DoceboLanguage::createInstance('report', 'framework');
        $this->usestandardtitle_rows = false;


        $this->_set_columns_category(_RU_CATEGORY_COURSES, $this->lang->def('_RU_CAT_COURSES'), 'get_courses_filter', 'show_report_courses', '_get_courses_query');
        //$this->_set_columns_category(_RU_CATEGORY_GENERAL, $this->lang->def('_RU_CAT_GENERAL'), 'get_general_filter', 'show_report_general');
        //$this->_set_columns_category(_RU_CATEGORY_COMPETENCES, $this->lang->def('_RU_CAT_COMPETENCES'), 'get_competences_filter', 'show_report_competences', '_get_competences_query');
        $this->_set_columns_category(_RU_CATEGORY_DELAY, $this->lang->def('_RU_CAT_DELAY'), 'get_delay_filter', 'show_report_delay', '_get_delay_query');
        $this->_set_columns_category(_RU_CATEGORY_LO, $this->lang->def('_RU_CAT_LO'), 'get_LO_filter', 'show_report_LO', '_get_LO_query');
        $this->_set_columns_category(_RU_CATEGORY_TESTSTAT, $this->lang->def('_RU_CAT_TESTSTAT'), 'get_TESTSTAT_filter', 'show_report_TESTSTAT', '_get_TESTSTAT_query');
        $this->_set_columns_category(_RU_CATEGORY_COMMUNICATIONS, $this->lang->def('_RU_CAT_COMMUNICATIONS'), 'get_communications_filter', 'show_report_communications', '_get_communications_query');
        $this->_set_columns_category(_RU_CATEGORY_GAMES, $this->lang->def('_RU_CAT_GAMES'), 'get_games_filter', 'show_report_games', '_get_games_query');


        $this->status_c = array(
            CST_PREPARATION => Lang::t('_CST_PREPARATION', 'course'),
            CST_AVAILABLE => Lang::t('_CST_AVAILABLE', 'course'),
            CST_EFFECTIVE => Lang::t('_CST_CONFIRMED', 'course'),
            CST_CONCLUDED => Lang::t('_CST_CONCLUDED', 'course'),
            CST_CANCELLED => Lang::t('_CST_CANCELLED', 'course')
        );

        $csub = new CourseSubscribe_Manager();
        $this->status_u = $csub->getUserStatus();


        $this->courses_filter_definition = array(
            array('key' => _COURSES_FILTER_SESSION_NUMBER, 'label' => $this->lang->def('_COURSES_FILTER_SESSION_NUMBER'), 'type' => _FILTER_INTEGER),
            array('key' => _COURSES_FILTER_SCORE_INIT, 'label' => $this->lang->def('_COURSES_FILTER_SCORE_INIT'), 'type' => _FILTER_INTEGER),
            array('key' => _COURSES_FILTER_SCORE_END, 'label' => $this->lang->def('_FINAL_SCORE'), 'type' => _FILTER_INTEGER),
            array('key' => _COURSES_FILTER_SCORE_COURSE, 'label' => $this->lang->def('_COURSES_FILTER_SCORE_COURSE'), 'type' => _FILTER_INTEGER),
            array('key' => _COURSES_FILTER_INSCRIPTION_DATE, 'label' => $this->lang->def('_COURSES_FILTER_INSCRIPTION_DATE'), 'type' => _FILTER_DATE),
            array('key' => _COURSES_FILTER_FIRSTACCESS_DATE, 'label' => $this->lang->def('_DATE_FIRST_ACCESS'), 'type' => _FILTER_DATE),
            array('key' => _COURSES_FILTER_END_DATE, 'label' => $this->lang->def('_COURSES_FILTER_END_DATE'), 'type' => _FILTER_DATE),
            array('key' => _COURSES_FILTER_LASTACCESS_DATE, 'label' => $this->lang->def('_DATE_LAST_ACCESS'), 'type' => _FILTER_DATE)
        );

        $this->LO_columns = array(
            array('key' => 'userid', 'select' => false, 'group' => 'user', 'label' => Lang::t('_USERID', 'standard')),
            array('key' => 'user_name', 'select' => true, 'group' => 'user', 'label' => Lang::t('_FULLNAME', 'standard')),
            array('key' => 'email', 'select' => true, 'group' => 'user', 'label' => Lang::t('_EMAIL', 'standard')),
            array('key' => 'suspended', 'select' => true, 'group' => 'user', 'label' => Lang::t('_SUSPENDED', 'standard')),
            array('key' => '_CUSTOM_FIELDS_', 'select' => false, 'group' => 'user', 'label' => false),
            array('key' => 'course_code', 'select' => false, 'group' => 'course', 'label' => $this->lang->def('_CODE')),
            array('key' => 'course_name', 'select' => true, 'group' => 'course', 'label' => $this->lang->def('_COURSE_NAME')),
            array('key' => 'course_status', 'select' => true, 'group' => 'course', 'label' => $this->lang->def('_STATUS')),
            array('key' => 'lo_type', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_LO_COL_TYPE')),
            array('key' => 'lo_name', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_TITLE')),
            array('key' => 'lo_milestone', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_LO_COL_MILESTONE')),
            array('key' => 'firstAttempt', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_LO_COL_FIRSTATT')),
            array('key' => 'lastAttempt', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_LO_COL_LASTATT')),
            array('key' => 'lo_status', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_STATUS')),
            array('key' => 'lo_score', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_SCORE')),
            array('key' => 'lo_total_time', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_TOTAL_TIME'))
        );

        $this->TESTSTAT_columns = array(
            array('key' => 'userid', 'select' => false, 'group' => 'user', 'label' => Lang::t('_USERID', 'standard')),
            array('key' => 'user_name', 'select' => true, 'group' => 'user', 'label' => Lang::t('_FULLNAME', 'standard')),
            array('key' => 'email', 'select' => true, 'group' => 'user', 'label' => Lang::t('_EMAIL', 'standard')),
            array('key' => 'suspended', 'select' => true, 'group' => 'user', 'label' => Lang::t('_SUSPENDED', 'standard')),
            array('key' => '_CUSTOM_FIELDS_', 'select' => false, 'group' => 'user', 'label' => false),
            array('key' => 'course_code', 'select' => false, 'group' => 'course', 'label' => $this->lang->def('_CODE')),
            array('key' => 'course_name', 'select' => true, 'group' => 'course', 'label' => $this->lang->def('_COURSE_NAME')),
            array('key' => 'course_status', 'select' => true, 'group' => 'course', 'label' => $this->lang->def('_STATUS')),
            array('key' => 'lo_name', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_TITLE')),
            array('key' => 'lo_status', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_STATUS')),
            array('key' => 'lo_score', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_SCORE')),
            array('key' => 'lo_date', 'select' => true, 'group' => 'lo', 'label' => $this->lang->def('_DATE'))
        );

        $this->delay_columns = array(
            array('key' => 'userid', 'label' => Lang::t('_USERNAME', 'standard'), 'select' => false),
            array('key' => 'lastname', 'label' => Lang::t('_LASTNAME', 'standard'), 'select' => true),
            array('key' => 'firstname', 'label' => Lang::t('_FIRSTNAME', 'standard'), 'select' => true),
            array('key' => 'email', 'label' => Lang::t('_EMAIL', 'standard'), 'select' => true),
            array('key' => 'level', 'label' => Lang::t('_LEVEL', 'standard'), 'select' => true),
            array('key' => 'status', 'label' => Lang::t('_STATUS', 'standard'), 'select' => true),
            array('key' => 'date_subscription', 'label' => Lang::t('_DATE_INSCR', 'report'), 'select' => true),
            array('key' => 'date_first_access', 'label' => Lang::t('_DATE_FIRST_ACCESS', 'standard'), 'select' => true),
            array('key' => 'date_last_access', 'label' => Lang::t('_DATE_LAST_ACCESS', 'standard'), 'select' => true),
            array('key' => 'date_complete', 'label' => Lang::t('_DATE_END', 'standard'), 'select' => true)
        );

    }

    function getLOTypesTranslations()
    {
        if (!is_array($this->LO_types)) {
            $this->LO_types = array();
            $res = sql_query("SELECT objectType FROM %lms_lo_types");
            while (list($id_type) = sql_fetch_row($res)) {
                switch ($id_type) {
                    case 'scormorg':
                        $this->LO_types[$id_type] = Lang::t('_SCORMSECTIONNAME', 'scorm');
                        break;
                    case 'item':
                        $this->LO_types[$id_type] = Lang::t('_FILE', 'standard');
                        break;
                    default:
                        $this->LO_types[$id_type] = Lang::t('_LONAME_' . $id_type, 'storage');
                        break;
                }
            }
        }
        return $this->LO_types;
    }

    function _loadEmailIcon()
    {
        return '<span class="ico-sprite subs_unread"><span>' . Lang::t('_EMAIL', 'standard') . '</span></span>';
    }

    function _loadEmailActions()
    {
        YuiLib::load('selector');
        cout('<script type="text/javascript">
                function _getAllCheckBoxes() {
                    return YAHOO.util.Selector.query("input[id^=mail_]");
                }

                function selectAll() {
                    var sel = _getAllCheckBoxes();
                    for (var i=0; i<sel.length; i++) {
                        sel[i].checked=true;
                    }
                }

                function unselectAll() {
                    var sel = _getAllCheckBoxes();
                    for (var i=0; i<sel.length; i++) {
                        sel[i].checked=false;
                    }
                }

                YAHOO.util.Event.addListener("select_all-button", "click", selectAll);
                YAHOO.util.Event.addListener("unselect_all-button", "click", unselectAll);
            </script>', 'scripts');

        cout(
            Form::openButtonSpace()
            . Form::getHidden('no_show_repdownload', 'no_show_repdownload', 1)
            . Form::getButton('send_mail', 'send_mail', Lang::t('_SEND_MAIL', 'report'))
            . Form::getButton('select_all', false, Lang::t('_SELECT_ALL', 'standard'), FALSE, '', true, false)
            . Form::getButton('unselect_all', false, Lang::t('_UNSELECT_ALL', 'standard'), FALSE, '', true, false)
            . Form::closeButtonSpace()
        );
    }


    function get_rows_filter()
    {

        if (!isset($_SESSION['report_tempdata']['rows_filter'])) {
            $_SESSION['report_tempdata']['rows_filter'] = array(
                'users' => array(),
                'all_users' => false
            );
        }

        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_ . '/lib/lib.form.php');
        require_once($GLOBALS['where_framework'] . '/lib/lib.directory.php');
        require_once(_base_ . '/lib/lib.userselector.php');
        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $org_chart_subdivision = importVar('org_chart_subdivision', true, 0);

        $aclManager = new DoceboACLManager();
        $user_select = new UserSelector();
        $user_select->use_suspended = true;

        if (isset($_POST['cancelselector'])) {

            Util::jump_to($back_url);
        } elseif (isset($_POST['okselector'])) {

            $aclManager = new DoceboACLManager();

            $temp = $user_select->getSelection($_POST);

            $_SESSION['report_tempdata']['rows_filter']['users'] = $temp;
            $_SESSION['report_tempdata']['rows_filter']['all_users'] = (Get::req('all_users', DOTY_INT, 0) > 0 ? true : false);

            Util::jump_to($next_url);
        } else {

            // first step load selector
            if ($org_chart_subdivision == 0) {

                $user_select->show_user_selector = TRUE;
                $user_select->show_group_selector = TRUE;
            } else {

                $user_select->show_user_selector = FALSE;
                $user_select->show_group_selector = FALSE;
            }
            $user_select->show_orgchart_selector = TRUE;
            //$user_select->show_orgchart_simple_selector = FALSE;
            //$user_select->multi_choice = TRUE;

            if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
                $acl_man = new DoceboACLManager();

                require_once(_base_ . '/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
                $admin_users = $acl_man->getAllUsersFromIdst($admin_tree);

                $user_select->setUserFilter('user', $admin_users);
                $user_select->setUserFilter('group', $admin_tree);
            }

            if (Get::req('is_updating', DOTY_INT, false)) {
                //$_SESSION['report_tempdata']['rows_filter']['users'] = $user_select->getSelection($_POST);
                $_SESSION['report_tempdata']['rows_filter']['all_users'] = (Get::req('all_users', DOTY_INT, 0) > 0 ? true : false);
            } else {
                $user_select->requested_tab = PEOPLEVIEW_TAB;
                $user_select->resetSelection($_SESSION['report_tempdata']['rows_filter']['users']);
            }
            $user_select->addFormInfo(
                Form::getCheckbox($lang->def('_REPORT_FOR_ALL'), 'all_users', 'all_users', 1, $_SESSION['report_tempdata']['rows_filter']['all_users']) .//($_SESSION['report_tempdata']['rows_filter']['all_users'] ? 1 : 0)).
                Form::getBreakRow() .
                Form::getHidden('org_chart_subdivision', 'org_chart_subdivision', $org_chart_subdivision) .
                Form::getHidden('is_updating', 'is_updating', 1)
            );
            //$user_select->setPageTitle($this->page_title);
            cout($this->page_title, 'content');
            //$user_select->resetSelection($_SESSION['report_tempdata']['rows_filter']['users']);
            $user_select->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
                false,
                $lang->def('_CHOOSE_USER_FOR_REPORT'),
                true);

        }

    }

    //filter functions
    function get_courses_filter()
    {

        $out =& $GLOBALS['page'];
        $out->setWorkingZone('content');

        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_ . '/lib/lib.form.php');
        require_once(_lms_ . '/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        YuiLib::load('datasource');
        Util::get_js(Get::rel_path('lms') . '/admin/modules/report/courses_filter.js', true, true);

        Form::loadDatefieldScript();

        $time_belt = array(
            0 => $lang->def('_CUSTOM_BELT'),
            7 => $lang->def('_LAST_WEEK'),
            31 => $lang->def('_LAST_MONTH'),
            93 => $lang->def('_LAST_THREE_MONTH'),
            186 => $lang->def('_LAST_SIX_MONTH'),
            365 => $lang->def('_LAST_YEAR'),
        );


        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            //go back at the previous step
            Util::jump_to($back_url);
            //...
        }

        require_once($GLOBALS['where_framework'] . '/lib/lib.field.php');
        $fman = new FieldList();
        $fields = $fman->getFlatAllFields();
        $custom = array();
        foreach ($fields as $key => $val) {
            $custom[] = array('id' => $key, 'label' => $val, 'selected' => false);
        }

        //set $_POST data in $_SESSION['report_tempdata']
        if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'org_chart_subdivision' => 0,
                'all_courses' => true,
                'selected_courses' => array(),
                'sub_filters' => array(),
                'filter_exclusive' => 1,
                'showed_columns' => array(),
                'order_by' => 'userid',
                'order_dir' => 'asc',
                'show_suspended' => false,
                'custom_fields' => $custom,
                'show_classrooms_editions' => false
            );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter']; //echo print_r($ref,true);

        $selector = new Selector_Course();
        $selection =& $ref['selected_courses'];
        $selector->parseForState($_POST);

        if (isset($_POST['update_tempdata'])) {

            // parse for date fields

            $opt_type = array();
            foreach ($this->courses_filter_definition as $fd) {
                $opt_type[$fd['key']] = $fd['type'];
            }

            if (isset($_POST['courses_filter'])) {
                while (list($ind, $filter_data) = each($_POST['courses_filter'])) {
                    if ($opt_type[$filter_data['option']] == _FILTER_DATE) {
                        $_POST['courses_filter'][$ind]['value'] = Format::dateDb($filter_data['value'], 'date');
                    }
                }
            }

            $temp = array(
                'org_chart_subdivision' => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                'all_courses' => ($_POST['all_courses'] == 1 ? true : false),
                'selected_courses' => $selector->getSelection(),
                'sub_filters' => (isset($_POST['courses_filter']) ? $_POST['courses_filter'] : array()),
                'filter_exclusive' => (isset($_POST['filter_exclusive']) ? $_POST['filter_exclusive'] : false),
                'showed_columns' => (isset($_POST['cols']) ? $_POST['cols'] : array()),
                'order_by' => (isset($_POST['order_by']) ? $_POST['order_by'] : 'userid'),
                'order_dir' => (isset($_POST['order_dir']) ? $_POST['order_dir'] : 'asc'),
                'show_suspended' => (isset($_POST['show_suspended']) ? $_POST['show_suspended'] > 0 : false),
                'custom_fields' => array(),
                'show_classrooms_editions' => (isset($_POST['show_classrooms_editions']) && $_POST['show_classrooms_editions'] > 0 ? true : false)
            );

            foreach ($custom as $val) {
                $temp['custom_fields'][] = array(
                    'id' => $val['id'],
                    'label' => $val['label'],
                    'selected' => (isset($_POST['custom'][$val['id']]) ? true : false)
                );
            }

            $ref = $temp;
        } else {
            $selector->resetSelection($selection);

            //get users' custom fields
            if (!isset($ref['custom_fields'])) {
                $ref['custom_fields'] = $custom;
            } else {
                $t_arr = array();
                foreach ($custom as $val) {
                    $is_selected = FALSE;
                    foreach ($ref['custom_fields'] as $fieldrow) {
                        if ($fieldrow['id'] == $val['id']) {
                            $is_selected = $fieldrow['selected'];
                            break;
                        }
                    }
                    $t_arr[] = array(
                        'id' => $val['id'],
                        'label' => $val['label'],
                        'selected' => $is_selected
                    );
                }
                $ref['custom_fields'] = $t_arr;
            }
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
            Util::jump_to($temp_url);
        }

        cout(
        //Form::openForm('user_report_columns_courses', $jump_url)
            Form::getHidden('update_tempdata', 'update_tempdata', 1)
        );

        $lang = $this->lang;
        $temp = count($selection);
        $show_classrooms_editions = $ref['show_classrooms_editions'];

        $box = new ReportBox('course_selector');
        $box->title = $lang->def('_REPORT_COURSE_SELECTION');
        $box->description = false;
        $box->body .= '<div class="fc_filter_line filter_corr">';
        $box->body .= '<input id="all_courses" name="all_courses" type="radio" value="1" ' . ($ref['all_courses'] ? 'checked="checked"' : '') . ' />';
        $box->body .= ' <label for="all_courses">' . $lang->def('_ALL_COURSES') . '</label>';
        $box->body .= ' <input id="sel_courses" name="all_courses" type="radio" value="0" ' . ($ref['all_courses'] ? '' : 'checked="checked"') . ' />';
        $box->body .= ' <label for="sel_courses">' . $lang->def('_SEL_COURSES') . '</label>';
        $box->body .= '</div>';

        $box->body .= '<div id="selector_container"' . ($ref['all_courses'] ? ' style="display:none"' : '') . '>';
        $box->body .= $selector->loadCourseSelector(true);
        $box->body .= '</div>';
        $box->footer = $lang->def('_CURRENT_SELECTION') . ':&nbsp;<span id="csel_foot">' . ($ref['all_courses'] ? $lang->def('_ALL') : ($temp != '' ? $temp : '0')) . '</span>';
        cout($box->get());

        cout(
            '<script type="text/javascript">courses_count=' . ($temp == '' ? '0' : $temp) . ';' .
            'courses_all="' . $lang->def('_ALL') . '";</script>');

        //example selection options

        require_once(_base_ . '/lib/lib.json.php');

        $seldata = $this->courses_filter_definition;

        $filter_cases = array(
            '_FILTER_INTEGER' => _FILTER_INTEGER,
            '_FILTER_DATE' => _FILTER_DATE
        );

        $regset = Format::instance();
        $date_token = $regset->date_token;

        $json = new Services_JSON();
        $js_seldata = $json->encode($seldata);
        $js_filter_cases = $json->encode($filter_cases);

        $out->add('<script type="text/javascript">' . "\n" .
            //'_temp_seldata='.$js_seldata.';'."\n".
            'seldata_JSON=' . $js_seldata . ';' . "\n" .
            'filter_cases_JSON=\'' . $js_filter_cases . '\';' . "\n" .
            'courses_sel_opt_0=\'' . $lang->def('_COURSES_DROPDOWN_NULL_SELECT') . '\';' . "\n" .
            'courses_remove_filter=\'' . $lang->def('_RESET') . '\';' . "\n" .
            'var course_date_token=\'' . $date_token . '\';' . "\n" .
            'YAHOO.util.Event.addListener(window,"load",courses_init);' . "\n" .
            '</script>', 'page_head');

        //box for course filter conditions
        $temp =& $_SESSION['report_tempdata']['columns_filter']['sub_filters'];
        $inc_counter = count($temp);
        $already = '';
        $script_init = 'YAHOO.util.Event.onDOMReady( function() {' . "\n";

        if (is_array($temp)) {
            foreach ($temp as $key => $value) { //create filters html

                $value['value'] = substr($value['value'], 0, 10); //make sure that the date format is yyyy-mm-dd

                $index = str_replace('i', '', $key);

                $already .= '<div id="courses_filter_' . $index . '">';

                //generate option selection
                $already .= '<select id="courses_filter_sel_' . $index . '" name="courses_filter_sel[]">';
                $already .= '<option value="0">' . $lang->def('_COURSES_DROPDOWN_NULL_SELECT') . '</option>';
                foreach ($seldata as $selval) {
                    if ($value['option'] == $selval['key']) $selected = ' selected="selected"'; else $selected = '';
                    $already .= '<option value="' . $selval['key'] . '"' . $selected . '>' . $selval['label'] . '</option>';
                }
                $already .= '</select>';

                $already .= '<span id="courses_filter_params_' . $index . '">';

                //generate sign selection
                $signs = array('<', '<=', '=', '>=', '>');
                $already .= '<select name="courses_filter[' . $key . '][sign]">';
                foreach ($signs as $k2 => $v2) {
                    if ($value['sign'] == $v2) $selected = ' selected="selected"'; else $selected = '';
                    $already .= '<option value="' . $v2 . '"' . $selected . '>' . $v2 . '</option>';
                }
                $already .= '</select>';

                //generate value input
                $type = false;
                foreach ($this->courses_filter_definition as $def) { //this should be a switch
                    if ($value['option'] == $def['key']) $type = $def['type'];
                }

                $already .= '<input class="align_right" type="text" style="width: ' .
                    ($type == _FILTER_DATE ? '7' : '9') . 'em;" ' .
                    'name="courses_filter[' . $key . '][value]" value="' . ($type == _FILTER_DATE ? \Format::date($value['value'], 'date') : $value['value']) . '"' .
                    ' id="courses_filter_' . $index . '_value" />';

                if ($type == _FILTER_DATE) {
                    $_year = substr($value['value'], 0, 4);
                    $_month = substr($value['value'], 5, 2);
                    $_day = substr($value['value'], 8, 2);
                    $script_init .= 'YAHOO.dateInput.setCalendar('
                        . '"courses_filter_' . $index . '_value", '
                        . '"' . $_month . '/' . $_day . '/' . $_year . '", '
                        . '"' . $date_token . '");' . "\n";
                }

                //generate hidden index
                $already .= '<input type="hidden" name="courses_filter[' . $key . '][option]" ' .
                    'value="' . $value['option'] . '" /></span>';

                //generate remove link
                $already .= '<a href="javascript:courses_removefilter(' . $index . ');">' .
                    $lang->def('_RESET') . '</a>';

                $already .= '</div>';

                $script_init .= 'YAHOO.util.Event.addListener("courses_filter_sel_' . $index . '", "change", courses_create_filter);';
            }
        }

        $script_init .= '} );';
        $already .= '<script type="text/javascript">' . $script_init . '</script>';

        $temp = (isset($_SESSION['report_tempdata']['columns_filter']['filter_exclusive']) ? $_SESSION['report_tempdata']['columns_filter']['filter_exclusive'] : 1);
        $selected = ' checked="checked"';

        $box = new ReportBox('course_subfilters');
        $box->title = $lang->def('_REPORT_COURSE_CONDITIONS');
        $box->description = '';
        $box->body =
            Form::getBreakRow()
            . '<div id="courses_filter_list">' . $already . '</div>'

            . '<div class="fc_filter_line filter_corr">'
            . '<input type="radio" id="filter_exclusive_and" name="filter_exclusive" value="1" ' . ($temp > 0 ? $selected : '') . ' />
                <label for="filter_exclusive_and">' . $lang->def('_FILTER_ALL_CONDS') . '</label>&nbsp;'

            . '<input type="radio" id="filter_exclusive_or" name="filter_exclusive" value="0" ' . ($temp == 0 ? $selected : '') . ' />
                <label for="filter_exclusive_or">' . $lang->def('_FILTER_ONE_COND') . '</label>'
            . '</div>'

            . '<div class="fc_filter_line">'
            . '<span class="yui-button yui-link-button" id="fc_addfilter">
                    <span class="first-child">
                        <a href="#" onclick="courses_addfilter();return false;">' . $lang->def('_NEW_FILTER') . '</a>
                    </span>
                </span>'
            . '<span class="yui-button yui-link-button" id="fc_cancfilter">
                    <span class="first-child">
                        <a href="#" onclick="courses_resetfilters();return false;">' . $lang->def('_FILTER_RESET') . '</a>
                    </span>
                </span>'
            . '</div>'


            . Form::getHidden('inc_counter', 'inc_counter', $inc_counter)

            . '</div>';
        cout($box->get());

        function is_showed($which)
        {
            if (isset($_SESSION['report_tempdata']['columns_filter'])) {
                return in_array($which, $_SESSION['report_tempdata']['columns_filter']['showed_columns']);
            } else return false;
        }

        //box for columns selection
        $box = new ReportBox('columns_selection');
        $box->title = $lang->def('_SELECT_THE_DATA_COL_NEEDED');
        $box->description = false;
        //Form::openElementSpace()
        if (count($custom) > 0) {
            $box->body .= Form::getOpenFieldset($lang->def('_USER_CUSTOM_FIELDS'), 'fieldset_course_fields');
            $box->body .= Form::getCheckBox(Lang::t('_LASTNAME', 'standard'), 'col_sel_lastname', 'cols[]', '_TH_LASTNAME', is_showed('_TH_LASTNAME'));
            $box->body .= Form::getCheckBox(Lang::t('_FIRSTNAME', 'standard'), 'col_sel_firstname', 'cols[]', '_TH_FIRSTNAME', is_showed('_TH_FIRSTNAME'));
            $box->body .= Form::getCheckBox(Lang::t('_EMAIL', 'standard'), 'col_sel_email', 'cols[]', '_TH_EMAIL', is_showed('_TH_EMAIL'));
            $box->body .= Form::getCheckBox(Lang::t('_REGISTER_DATE', 'standard'), 'col_sel_register_date', 'cols[]', '_TH_REGISTER_DATE', is_showed('_TH_REGISTER_DATE'));
            $box->body .= Form::getCheckBox(Lang::t('_SUSPENDED', 'standard'), 'col_sel_suspended', 'cols[]', '_TH_SUSPENDED', is_showed('_TH_SUSPENDED'));
            $box->body .= Form::getCheckBox(Lang::t('_ORGCHART', 'standard'), 'col_sel_organization_chart', 'cols[]', '_TH_ORGANIZATION_CHART', is_showed('_TH_ORGANIZATION_CHART'));

            foreach ($custom as $key => $val) {
                $box->body .= Form::getCheckBox($val['label'], 'col_custom_' . $val['id'], 'custom[' . $val['id'] . ']', $val['id'], $ref['custom_fields'][$key]['selected']);
            }
            $box->body .= Form::getCloseFieldset();
        }


        $out->add('<script type="text/javascript">
                function activateClassrooms() {
                    var Y = YAHOO.util.Dom;
                    var b1 = Y.get("not_classrooms"), b2 = Y.get("use_classrooms");
                    var action = b1.style.display == "none" ? "hide" : "show";
                    switch (action) {
                        case "hide": {
                            b1.style.display = "block";
                            b2.style.display = "none";
                        } break;
                        case "show": {
                            b1.style.display = "none";
                            b2.style.display = "block";
                        } break;
                    }
                }
            </script>', 'page_head');

        $box->body .=
            Form::getOpenFieldset($lang->def('_COURSE_FIELDS'), 'fieldset_course_fields')
            . Form::getCheckBox($lang->def('_CATEGORY'), 'col_sel_category', 'cols[]', '_TH_CAT', is_showed('_TH_CAT'))
            . Form::getCheckBox($lang->def('_CODE'), 'col_sel_coursecode', 'cols[]', '_TH_CODE', is_showed('_TH_CODE'))
            . Form::getCheckBox($lang->def('_COURSE_TYPE'), 'col_sel_coursetype', 'cols[]', '_TH_COURSETYPE', is_showed('_TH_COURSETYPE'))
            . Form::getCheckBox($lang->def('_TH_COURSEPATH'), 'col_sel_coursepath', 'cols[]', '_TH_COURSEPATH', is_showed('_TH_COURSEPATH'))
            . Form::getCheckBox($lang->def('_STATUS'), 'col_sel_status', 'cols[]', '_TH_COURSESTATUS', is_showed('_TH_COURSESTATUS'))
            . Form::getCheckBox(Lang::t('_CREDITS', 'standard'), 'col_sel_credits', 'cols[]', '_TH_COURSECREDITS', is_showed('_TH_COURSECREDITS'))
            . Form::getCheckBox(Lang::t('_LABEL', 'standard'), 'col_sel_label', 'cols[]', '_TH_COURSELABEL', is_showed('_TH_COURSELABEL'))
            . Form::getCloseFieldset()

            . Form::getOpenFieldset(
                Form::getInputCheckbox('show_classrooms_editions', 'show_classrooms_editions', 1, $show_classrooms_editions, "onclick=activateClassrooms();")
                . "&nbsp;&nbsp;" . Lang::t('_CLASSROOM_FIELDS', 'report')
                , 'fieldset_classroom_fields')
            . '<div id="not_classrooms" style="display:' . ($show_classrooms_editions ? "none" : "block") . '">'
            . Lang::t('_ACTIVATE_CLASSROOM_FIELDS', 'report')
            . '</div>'
            . '<div id="use_classrooms" style="display:' . ($show_classrooms_editions ? "block" : "none") . '">'
            . Form::getCheckBox(Lang::t('_NAME', 'standard'), 'col_sel_classroomname', 'cols[]', '_TH_CLASSROOM_CODE', is_showed('_TH_CLASSROOM_CODE', $ref))
            . Form::getCheckBox(Lang::t('_CODE', 'standard'), 'col_sel_classroomcode', 'cols[]', '_TH_CLASSROOM_NAME', is_showed('_TH_CLASSROOM_NAME', $ref))
            . Form::getCheckBox(Lang::t('_DATE_BEGIN', 'standard'), 'col_sel_classroomdatebegin', 'cols[]', '_TH_CLASSROOM_MIN_DATE', is_showed('_TH_CLASSROOM_MIN_DATE', $ref))
            . Form::getCheckBox(Lang::t('_DATE_END', 'standard'), 'col_sel_classroomdateend', 'cols[]', '_TH_CLASSROOM_MAX_DATE', is_showed('_TH_CLASSROOM_MAX_DATE', $ref))
            . '</div>'
            . Form::getCloseFieldset()

            . Form::getOpenFieldset($lang->def('_COURSE_FIELDS_INFO'), 'fieldset_course_fields')
            . Form::getCheckBox($lang->def('_TH_USER_INSCRIPTION_DATE'), 'user_inscription_date', 'cols[]', '_TH_USER_INSCRIPTION_DATE', is_showed('_TH_USER_INSCRIPTION_DATE'))
            . Form::getCheckBox($lang->def('_DATE_FIRST_ACCESS'), 'user_start_date', 'cols[]', '_TH_USER_START_DATE', is_showed('_TH_USER_START_DATE'))
            . Form::getCheckBox($lang->def('_COMPLETED'), 'user_end_date', 'cols[]', '_TH_USER_END_DATE', is_showed('_TH_USER_END_DATE'))
            . Form::getCheckBox($lang->def('_DATE_LAST_ACCESS'), 'last_access_date', 'cols[]', '_TH_LAST_ACCESS_DATE', is_showed('_TH_LAST_ACCESS_DATE'))
            . Form::getCheckBox($lang->def('_LEVEL'), 'user_level', 'cols[]', '_TH_USER_LEVEL', is_showed('_TH_USER_LEVEL'))
            . Form::getCheckBox($lang->def('_STATUS'), 'user_status', 'cols[]', '_TH_USER_STATUS', is_showed('_TH_USER_STATUS'))
            . Form::getCheckBox($lang->def('_TH_USER_START_SCORE'), 'user_start_score', 'cols[]', '_TH_USER_START_SCORE', is_showed('_TH_USER_START_SCORE'))
            . Form::getCheckBox($lang->def('_FINAL_SCORE'), 'user_final_score', 'cols[]', '_TH_USER_FINAL_SCORE', is_showed('_TH_USER_FINAL_SCORE'))
            . Form::getCheckBox($lang->def('_TH_USER_COURSE_SCORE'), 'user_course_score', 'cols[]', '_TH_USER_COURSE_SCORE', is_showed('_TH_USER_COURSE_SCORE'))
            . Form::getCheckBox($lang->def('_TH_USER_NUMBER_SESSION'), 'user_number_session', 'cols[]', '_TH_USER_NUMBER_SESSION', is_showed('_TH_USER_NUMBER_SESSION'))
            . Form::getCheckBox($lang->def('_TOTAL_TIME'), 'user_elapsed_time', 'cols[]', '_TH_USER_ELAPSED_TIME', is_showed('_TH_USER_ELAPSED_TIME'))
            . Form::getCheckBox($lang->def('_TH_ESTIMATED_TIME'), 'estimated_time', 'cols[]', '_TH_ESTIMATED_TIME', is_showed('_TH_ESTIMATED_TIME'))
            . Form::getCloseFieldset();

        cout($box->get());


        //other options
        $box = new ReportBox('other_options');
        $box->title = Lang::t('_OTHER_OPTION', 'course');
        $box->description = false;

        $sort_list = array(
            'userid' => Lang::t('_USERID', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'course_code' => Lang::t('_COURSE_CODE', 'standard'),
            'course_name' => Lang::t('_COURSE_NAME', 'standard'),
            'status' => Lang::t('_STATUS', 'standard'),
            'level' => Lang::t('_LEVEL', 'standard'),
            'date_subscription' => Lang::t('_DATE_INSCR', 'report'),
            'date_first_access' => Lang::t('_DATE_FIRST_ACCESS', 'report'),
            'date_last_access' => Lang::t('_DATE_LAST_ACCESS', 'report'),
            'date_complete' => Lang::t('_DATE_END', 'standard')
        );
        $dir_list = array(
            'asc' => Lang::t('_ORD_ASC_TITLE', 'standard'),
            'desc' => Lang::t('_ORD_DESC_TITLE', 'standard')
        );

        $sort_selected = array_key_exists($ref['order_by'], $sort_list) ? $ref['order_by'] : 'userid';
        $dir_selected = array_key_exists($ref['order_dir'], $dir_list) ? $ref['order_dir'] : 'asc';

        $sort_dir_dropdown = Form::getInputDropdown('', 'order_dir', 'order_dir', $dir_list, $dir_selected, '');
        $box->body .= Form::getDropdown(Lang::t('_ORDER_BY', 'standard'), 'order_by', 'order_by', $sort_list, $sort_selected, $sort_dir_dropdown);

        $box->body .= Form::getCheckbox(Lang::t('_SHOW_SUSPENDED', 'organization_chart'), 'show_suspended', 'show_suspended', 1, (bool)$ref['show_suspended']);

        cout($box->get());
    }

    function get_competences_filter()
    {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        $cmodel = new CompetencesAdm();
        $lang =& DoceboLanguage::createInstance('report', 'framework');

        YuiLib::load();
        Util::get_js(Get::rel_path('lms') . '/admin/modules/report/competences_filter.js', true, true);
        addJs($GLOBALS['where_lms_relative'] . '/admin/modules/report/', 'competences_filter.js');

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            Util::jump_to($back_url);
        }
        if (Get::req('is_updating', DOTY_INT, 0) > 0) {
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'filters_list' => Get::req('rc_filter', DOTY_MIXED, array()),
                'exclusive' => (Get::req('rc_filter_exclusive', DOTY_INT, 0) > 0 ? true : false)
            );
        } else {
            if (!isset($_SESSION['report_tempdata']['columns_filter']))
                $_SESSION['report_tempdata']['columns_filter'] = array(
                    'filters_list' => array(),
                    'exclusive' => true
                );
        }
        $ref =& $_SESSION['report_tempdata']['columns_filter'];

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
            Util::jump_to($temp_url);
        }

        $cats = $cmodel->getAllCategories();
        $comps = $cmodel->getAllCompetences();

        $optdata = array(
            array(
                'name' => Lang::t('_COMPETENCES', 'competences'),
                'rows' => array()
            )
        );
        foreach ($comps as $key => $val) { //categories cycle

            $temp = array();
            $temp['id'] = $val->id_competence;
            $temp['name'] = $val->name;//str_replace("'", "\'", $value);
            $temp['type'] = $val->type;
            $temp['typology'] = $val->typology;
            //$temp['score'] = $val->score;

            $optdata[0]['rows'][] = $temp;
        }

        $prevdata = $ref['filters_list'];//array();

        $json = new Services_JSON();

        $js_prevdata = $json->encode($prevdata);
        $js_optdata = str_replace("'", "\'", $json->encode($optdata));

        cout('<script type="text/javascript">' .
            'optdata_JSON=\'' . $js_optdata . '\';' .
            'rc_sel_opt_0=\'' . $lang->def('_COMPETENCES_DROPDOWN_NULL_SELECT') . '\';' .
            'rc_remove_filter=\'' . $lang->def('_RESET') . '\';' .
            'rc_initial_filters=' . $js_prevdata . ';' .
            'YAHOO.util.Event.onDOMReady(rc_init);' .
            //'rc_auto_inc='.(count($ref)+1).';'.
            '</script>', 'page_head');

        $clang = $this->lang;
        $sel = ($ref['exclusive'] ? 1 : 0);
        $selected = ' checked="checked"';
        $box = new ReportBox();

        $box->title = $this->lang->def('_COMPETENCESFILTER_TITLE');
        $box->description = $this->lang->def('_COMPETENCESFILTER_TITLE_DESC');
        $box->body = Form::getBreakRow()
            . Form::getHidden('is_updating', 'is_updating', 1)

            . '<div id="rc_filter_list"></div>'

            . '<div class="fc_filter_line filter_corr">'
            . '<input type="radio" id="rc_filter_exclusive_and" name="rc_filter_exclusive" value="1" ' . ($sel > 0 ? $selected : '') . ' />
                <label for="rc_filter_exclusive_and">' . $clang->def('_FILTER_ALL_CONDS') . '</label>&nbsp;'

            . '<input type="radio" id="rc_filter_exclusive_or" name="rc_filter_exclusive" value="0" ' . ($sel == 0 ? $selected : '') . ' />
                <label for="rc_filter_exclusive_or">' . $clang->def('_FILTER_ONE_COND') . '</label>'
            . '</div>'

            . '<div class="fc_filter_line">'
            . '<span class="yui-button yui-link-button" id="fc_addfilter">
                    <span class="first-child">
                        <a href="#" onclick="rc_addfilter();return false;">' . $clang->def('_NEW_FILTER') . '</a>
                    </span>
                </span>'
            . '<span class="yui-button yui-link-button" id="fc_cancfilter">
                    <span class="first-child">
                        <a href="#" onclick="rc_resetfilters();return false;">' . $clang->def('_FILTER_RESET') . '</a>
                    </span>
                </span>'
            . '</div>';

        cout($box->get());
        cout(Form::getBreakRow());

    }


    function show_report_courses($report_data = NULL, $other = '')
    {
        $jump_url = ''; //show_report

        checkPerm('view');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['send_mail_confirm']))
            $op = 'send_mail_confirm';
        elseif (isset($_POST['send_mail'])) {
            $op = 'send_mail';
        } else {
            $op = 'show_result';
        }

        switch ($op) {

            case 'send_mail_confirm':
                {
                    $subject = importVar('mail_object', false, '[' . $lang->def('_SUBJECT') . ']');//'[No subject]');
                    $body = importVar('mail_body', false, '');
                    $acl_man = new DoceboACLManager();

                    $sender = Get::sett('sender_event');
                    $mail_recipients = unserialize(urldecode(Get::req('mail_recipients', DOTY_STRING, '')));

                    // prepare intestation for email
                    $from = "From: " . $sender . $GLOBALS['mail_br'];
                    $header = "MIME-Version: 1.0" . $GLOBALS['mail_br']
                        . "Content-type: text/html; charset=" . getUnicode() . $GLOBALS['mail_br'];
                    $header .= "Return-Path: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    //$header .= "Reply-To: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Sender: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Mailer: PHP/" . phpversion() . $GLOBALS['mail_br'];

                    // send mail
                    $arr_recipients = array();
                    foreach ($mail_recipients as $recipient) {
                        $rec_data = $acl_man->getUser($recipient, false);
                        //mail($rec_data[ACL_INFO_EMAIL] , stripslashes($subject), stripslashes(nl2br($body)), $from.$header."\r\n");
                        $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
                    }
                    $mailer = DoceboMailer::getInstance();
                    $mailer->addReplyTo(Get::sett('sender_event').$GLOBALS['mail_br']);
                    $mailer->SendMail($sender, $arr_recipients, stripslashes($subject), stripslashes(nl2br($body)));

                    $result = getResultUi($lang->def('_OPERATION_SUCCESSFUL'));

                    cout($this->_get_courses_query('html', NULL, $result));
                }
                break;

            case 'send_mail':
                {
                    require_once(_base_ . '/lib/lib.form.php');
                    $mail_recipients = Get::req('mail_recipients', DOTY_MIXED, array());
                    cout(''//Form::openForm('course_selection', Util::str_replace_once('&', '&amp;', $jump_url))
                        . Form::openElementSpace()
                        . Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
                        . Form::getTextarea($lang->def('_MAIL_BODY'), 'mail_body', 'mail_body')
                        . Form::getHidden('mail_recipients', 'mail_recipients', urlencode(serialize($mail_recipients)))
                        . Form::closeElementSpace()
                        . Form::openButtonSpace()
                        . Form::getButton('send_mail_confirm', 'send_mail_confirm', $lang->def('_SEND_MAIL'))
                        . Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
                        . Form::closeButtonSpace()
                        //.Form::closeForm()
                        . '</div>', 'content');
                }
                break;

            default:
                {
                    cout($this->_get_courses_query('html', $report_data, $other));
                }

        }
    }

    /**
     * Return the output in the selected format for the report with the filters given
     * @param string $type output type
     * @param array $report_data a properly formatted list of rule to follow
     * @param string $other
     * @return string the properly formated report
     */
    function _get_courses_query($type = 'html', $report_data = NULL, $other = '')
    {

        checkPerm('view');
        $view_all_perm = checkPerm('view_all', true);

        require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

        $output = '';
        $jump_url = '';
        $org_chart_subdivision = 0; // not implemented
        $elem_selected = array();

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = TRUE;
        $course_man = new Man_Course();

        // read form _SESSION (XXX: change this) the report setting
        $filter_userselection = (!$report_data && isset($_SESSION['report_tempdata']['rows_filter']['users'])
            ? $_SESSION['report_tempdata']['rows_filter']['users'] : $report_data['rows_filter']['users']);

        $filter_columns = (!$report_data && $_SESSION['report_tempdata']['columns_filter']
            ? $_SESSION['report_tempdata']['columns_filter'] : $report_data['columns_filter']);

        if (!$report_data && isset($_SESSION['report_tempdata']['rows_filter']['all_users'])) {
            $alluser = ($_SESSION['report_tempdata']['rows_filter']['all_users'] ? 1 : 0);
        } else {
            $alluser = ($report_data['rows_filter']['all_users'] ? 1 : 0);
        }
        // break filters into a more usable format
        $filter_allcourses = $filter_columns['all_courses'];
        $filter_courseselection =& $filter_columns['selected_courses'];

        $order_by = isset($filter_columns['order_by']) ? $filter_columns['order_by'] : "userid";
        $order_dir = isset($filter_columns['order_dir']) ? $filter_columns['order_dir'] : "asc";

        $show_suspended = 'active_only';
        if (isset($filter_columns['show_suspended']) && $filter_columns['show_suspended']) {
            $show_suspended = 'all';
        }

        // retrive the user selected
        if ($alluser > 0) {
            // all the user selected (we can avoid this ? no we need to hide the suspended users)
            $user_selected =& $acl_man->getAllUsersIdst();
        } else {
            // resolve the user selection
            $user_selected =& $acl_man->getAllUsersFromSelection($filter_userselection);
        }

        //apply sub admin filters, if needed
        if (!$view_all_perm) {
            //filter users
            $alluser = false;
            require_once(_base_ . '/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromSelection($admin_users);
            $user_selected = array_intersect($user_selected, $admin_users);
            unset($admin_users);

            //filter courses
            $admin_allcourses = false;
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            if (!$filter_allcourses) {
                $rs = sql_query("SELECT idCourse FROM %lms_course");
                $course_selected = array();
                while (list($id_course) = sql_fetch_row($rs)) {
                    $course_selected[] = $id_course;
                }
            }

            if (isset($admin_courses['course'][0])) {
                //No filter
                $admin_allcourses = true;
            } elseif (isset($admin_courses['course'][-1])) {
                require_once(_lms_ . '/lib/lib.catalogue.php');
                $cat_man = new Catalogue_Manager();

                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if (count($user_catalogue) > 0) {
                    $courses = array(0);

                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);

                        $courses = array_merge($courses, $catalogue_course);
                    }

                    foreach ($courses as $id_course)
                        if ($id_course != 0)
                            $admin_courses['course'][$id_course] = $id_course;
                } elseif (Get::sett('on_catalogue_empty', 'off') == 'on') {
                    //No filter
                }

                if (!empty($admin_courses['course'])) {
                    $rs = sql_query("SELECT idCourse FROM %lms_course");
                    $course_selected = array();
                    while (list($id_course) = sql_fetch_row($rs)) {
                        $course_selected[] = $id_course;
                    }
                    $_clist = array_values($admin_courses['course']);
                    $course_selected = array_intersect($course_selected, $_clist);
                } else
                    $course_selected = array();
            } else {
                $array_courses = array();
                $array_courses = array_merge($array_courses, $admin_courses['course']);

                if (!empty($admin_courses['coursepath'])) {
                    require_once(_lms_ . '/lib/lib.coursepath.php');
                    $path_man = new Catalogue_Manager();
                    $coursepath_course =& $path_man->getAllCourses($admin_courses['coursepath']);
                    $array_courses = array_merge($array_courses, $coursepath_course);
                }
                if (!empty($admin_courses['catalogue'])) {
                    require_once(_lms_ . '/lib/lib.catalogue.php');
                    $cat_man = new Catalogue_Manager();
                    foreach ($admin_courses['catalogue'] as $id_cat) {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
                        $array_courses = array_merge($array_courses, $catalogue_course);
                    }
                }
                $admin_courses['course'] = array_merge($admin_courses['course'], $array_courses);

                if (!empty($admin_courses['course'])) {
                    $rs = sql_query("SELECT idCourse FROM %lms_course");
                    $course_selected = array();
                    while (list($id_course) = sql_fetch_row($rs)) {
                        $course_selected[] = $id_course;
                    }
                    $_clist = array_values($admin_courses['course']);
                    $course_selected = array_intersect($course_selected, $_clist);
                } else
                    $course_selected = array();
            }

            unset($admin_courses);

            if (!$filter_allcourses) $filter_courseselection = array_intersect($filter_courseselection, $course_selected);
            else $filter_courseselection = $course_selected;
            if ($filter_allcourses && $admin_allcourses)
                $filter_allcourses = true;
            else
                $filter_allcourses = false;
        }
        $show_classrooms_editions = isset($filter_columns['show_classrooms_editions']) ? (bool)$filter_columns['show_classrooms_editions'] : false;

        $classrooms_editions_info = array();
        if ($show_classrooms_editions) {
            //retrieve classrooms info
            $query = "SELECT d.*, MIN(dd.date_begin) AS date_1, MAX(dd.date_end) AS date_2 "
                . " FROM %lms_course_date AS d JOIN %lms_course_date_day AS dd ON (d.id_date = dd.id_date) "
                . (!$filter_allcourses ? " AND d.id_course IN (" . implode(',', $filter_courseselection) . ") " : "")
                . " GROUP BY dd.id_date";
            $res = sql_query($query);
            while ($obj = sql_fetch_object($res)) {
                $classrooms_editions_info['classrooms'][$obj->id_date] = $obj;
            }

            //retrieve editions info
            //TO DO ...
        }


        // if we must subdived the users into the org_chart folders we must retrive some extra info
        if ($org_chart_subdivision == 1) {

            require_once($GLOBALS['where_framework'] . '/lib/lib.orgchart.php');
            $org_man = new OrgChartManager();
            if ($alluser == 1) $elem_selected = $org_man->getAllGroupIdFolder();
            else $elem_selected = $user_selected;

            $org_name = $org_man->getFolderFormIdst($elem_selected);

            if ($userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
                require_once(_base_ . '/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());

                $org_name_temp = $org_name;
                $org_name = array();
                foreach ($org_name_temp as $id => $value) {
                    if (isset($admin_tree[$id])) $org_name[$id] = $value;
                }
            }
        }

        if (empty($user_selected)) {
            cout($lang->def('_NULL_SELECTION'), 'content');
            return;
        }

        // Retrive all the course
        $id_courses = $course_man->getAllCourses();
        if (empty($id_courses)) {

            return $lang->def('_NULL_COURSE_SELECTION');
        }

        $re_category = sql_query("
        SELECT idCategory, path
        FROM " . $GLOBALS['prefix_lms'] . "_category");
        $category_list = array(0 => $lang->def('_NONE'));
        $category_path_list = array(0 => '/');
        while (list($id_cat, $name_cat) = sql_fetch_row($re_category)) {
            $category_list[$id_cat] = substr($name_cat, strrpos($name_cat, '/') + 1);
            $category_path_list[$id_cat] = substr($name_cat, 5, (strlen($name_cat) - 5)); //eliminates "/root"
        }

        $time_list = array();
        $session_list = array();
        $lastaccess_list = array();

        $query = "
        SELECT idUser, idCourse, COUNT(*), SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)), MAX(lastTime)
        FROM " . $GLOBALS['prefix_lms'] . "_tracksession
        WHERE 1 " .
            ($alluser > 0 ? "" : "AND idUser IN ( " . implode(',', $user_selected) . " ) ") .
            ($filter_allcourses ? "" : "AND idCourse IN (" . implode(',', $filter_courseselection) . ") ");
        //if($start_time != '') $query .= " AND enterTime >= '".$start_time."' ";
        //if($end_time != '') $query .= " AND lastTime <= '".$end_time."' ";
        $query .= "GROUP BY idUser, idCourse ";
        $re_time = sql_query($query);
        while (list($id_u, $id_c, $session_num, $time_num, $last_num) = sql_fetch_row($re_time)) {

            $session_list[$id_u . '_' . $id_c] = $session_num;
            $time_list[$id_u . '_' . $id_c] = $time_num;
            $lastaccess_list[$id_u . '_' . $id_c] = $last_num;
        }
        //recover start and final score
        require_once($GLOBALS['where_lms'] . '/lib/lib.orgchart.php');
        $org_man = new OrganizationManagement(false);

        $score_start = $org_man->getStartObjectScore($user_selected, array_keys($id_courses));
        $score_final = $org_man->getFinalObjectScore($user_selected, array_keys($id_courses));


        require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
        $rep_man = new CourseReportManager();

        $score_course = $rep_man->getUserFinalScore($user_selected, array_keys($id_courses));


        //set query suspended users condition
        $query_show_suspended = "u.valid = 1"; //default condition
        switch ($show_suspended) {
            case "all":
                $query_show_suspended = "1";
                break;
            case "suspended_only":
                $query_show_suspended = "u.valid = 0";
                break;
            case "active_only":
                $query_show_suspended = "u.valid = 1";
                break;
        }

        //set query order by param
        $_dir = "ASC";
        switch ($order_dir) {
            case "desc":
                $_dir = "DESC";
                break;
        }
        $query_order_by = "u.userid, c.code";
        switch ($order_by) {
            case 'firstname':
                $query_order_by = "u.firstname " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'lastname':
                $query_order_by = "u.lastname " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'email':
                $query_order_by = "u.email " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'course_code':
                $query_order_by = "c.code " . $_dir . ", u.userid " . $_dir . "";
                break;
            case 'course_name':
                $query_order_by = "c.name " . $_dir . ", c.code " . $_dir . ", u.userid " . $_dir . "";
                break;
            case 'status':
                $query_order_by = "cu.status " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'level':
                $query_order_by = "cu.level " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'date_subscription':
                $query_order_by = "cu.date_inscr " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'date_first_access':
                $query_order_by = "cu.date_first_access " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'date_last_access':
                $query_order_by = "cu.date_last_access " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
            case 'date_complete':
                $query_order_by = "cu.date_complete " . $_dir . ", u.userid " . $_dir . ", c.code " . $_dir . "";
                break;
        }

        if ($org_chart_subdivision == 0) {

            // find some information

            if ($show_classrooms_editions) {
                $query_course_user = "SELECT cu.idUser, c.code, c.idCourse, c.idCategory, c.name, c.status AS course_status, cu.level, "
                    . " cu.status, cu.date_inscr, cu.date_first_access, cu.date_complete, c.mediumTime, c.course_type, c.credits, "
                    . " u.userid, u.firstname, u.lastname, u.email, u.register_date, u.valid, "
                    . " d.id_date, du.date_subscription AS classroom_date_subscription, du.date_complete AS classroom_date_complete  "
                    . " FROM  ( %lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user as u "
                    . " ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst) ) "
                    . " LEFT JOIN (%lms_course_date AS d JOIN %lms_course_date_user AS du ON (d.id_date = du.id_date)) "
                    . " ON (d.id_course = cu.idCourse AND du.id_user = cu.idUser) "
                    . " WHERE " . $query_show_suspended . " "
                    . ($alluser > 0 ? "" : "AND cu.idUser IN ( " . implode(',', $user_selected) . " ) ")
                    . ($filter_allcourses ? "" : "AND c.idCourse IN (" . implode(',', $filter_courseselection) . ") ")
                    //if($start_time != '') $query_course_user .= " AND cu.date_inscr >= '".$start_time."' ";
                    //if($end_time != '') $query_course_user .= " AND cu.date_inscr <= '".$end_time."' AND cu.level='3' ";
                    . " ORDER BY " . $query_order_by;
            } else {

                $query_course_user = "SELECT cu.idUser, c.code, c.idCourse, c.idCategory, c.name, c.status AS course_status, cu.level, "
                    . " cu.status, cu.date_inscr, cu.date_first_access, cu.date_complete, c.mediumTime, c.course_type, c.credits, "
                    . " u.userid, u.firstname, u.lastname, u.email, u.register_date, u.valid "
                    . " FROM  %lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user as u "
                    . " ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst) "
                    . " WHERE " . $query_show_suspended . " = 1 " .
                    ($alluser > 0 ? "" : "AND cu.idUser IN ( " . implode(',', $user_selected) . " ) ") .
                    ($filter_allcourses ? "" : "AND c.idCourse IN (" . implode(',', $filter_courseselection) . ") ")
                    . " ORDER BY " . $query_order_by;
            }

            $output .= $this->_printTable_courses(
                $type,
                $query_course_user,
                $category_list,
                $category_path_list,
                $session_list,
                $lastaccess_list,
                $time_list,
                $score_start,
                $score_final,
                $score_course,
                $user_selected,//$filter_userselection,
                $filter_columns,
                $show_classrooms_editions,
                $classrooms_editions_info
            );

        } else {

            $date_now = Format::date(date("Y-m-d H:i:s"));

            reset($org_name);
            while (list($idst_group, $folder_name) = each($org_name)) {

                if ($type == 'html') {
                    cout('<div class="datasummary">'
                        . '<b>' . $lang->def('_FOLDER_NAME') . ' :</b> ' . $folder_name['name']
                        . ($folder_name['type_of_folder'] == ORG_CHART_WITH_DESCENDANTS ? ' (' . $lang->def('_WITH_DESCENDANTS') . ')' : '') . '<br />'
                        , 'content');

                    cout('<b>' . $lang->def('_CREATION_DATE') . ' :</b> ' . $date_now . '<br /></div>', 'content');
                }

                $group_user = $acl_man->getGroupAllUser($idst_group);

                // find some information

                if ($show_classrooms_editions) {
                    $query_course_user = "SELECT cu.idUser, c.code, c.idCourse, c.idCategory, c.name, c.status AS course_status, cu.level, "
                        . " cu.status, cu.date_inscr, cu.date_first_access, cu.date_complete, c.mediumTime, c.course_type, c.credits, "
                        . " u.userid, u.firstname, u.lastname, u.email, u.register_date, u.valid, "
                        . " d.id_date, du.date_subscription AS classroom_date_subscription, du.date_complete AS classroom_date_complete  "
                        . " FROM  ( %lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user as u "
                        . " ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst) ) "
                        . " LEFT JOIN (%lms_course_date AS d JOIN %lms_course_date_user AS du ON (d.id_date = du.id_date)) "
                        . " ON (d.id_course = cu.idCourse AND du.id_user = cu.idUser) "
                        . " WHERE " . $query_show_suspended . " "
                        . (!empty($group_user) ? " AND cu.idUser IN ( " . implode(',', $group_user) . " ) " : " AND 0 ")
                        . ($filter_allcourses ? "" : "AND c.idCourse IN (" . implode(',', $filter_courseselection) . ") ")
                        //if($start_time != '') $query_course_user .= " AND cu.date_inscr >= '".$start_time."' ";
                        //if($end_time != '') $query_course_user .= " AND cu.date_inscr <= '".$end_time."' AND cu.level='3' ";
                        . " ORDER BY " . $query_order_by;
                } else {

                    $query_course_user = "SELECT cu.idUser, c.code, c.idCourse, c.idCategory, c.name, c.status AS course_status, cu.level, "
                        . " cu.status, cu.date_inscr, cu.date_first_access, cu.date_complete, c.mediumTime, c.course_type, c.credits, "
                        . " u.userid, u.firstname, u.lastname, u.email, u.register_date, u.valid "
                        . " FROM  %lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user as u "
                        . " ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst) "
                        . " WHERE " . $query_show_suspended . " = 1 "
                        . (!empty($group_user) ? " AND cu.idUser IN ( " . implode(',', $group_user) . " ) " : " AND 0 ")
                        . ($filter_allcourses ? "" : "AND c.idCourse IN (" . implode(',', $filter_courseselection) . ") ")
                        . " ORDER BY " . $query_order_by;
                }

                $output .= $this->_printTable_courses(
                    $type,
                    $query_course_user,
                    $category_list,
                    $category_path_list,
                    $session_list,
                    $lastaccess_list,
                    $time_list,
                    $score_start,
                    $score_final,
                    $score_course,
                    $filter_userselection,
                    $filter_columns,
                    $show_classrooms_editions,
                    $classrooms_editions_info
                );
            }
        }

        return $output;
    }


    function _check($cmp1, $cmp2, $sign, $type = _FILTER_INTEGER)
    {
        $output = false;

        switch ($type) {

            case _FILTER_INTEGER:
                {
                    if ($cmp1 == '') $cmp1 = 0;
                    if ($cmp2 == '') $cmp2 = 0;
                }
                break;

            case _FILTER_DATE:
                {
                    $cmp1 = ($cmp1 != "" ? substr($cmp1, 0, 10) : 0);
                    $cmp2 = ($cmp2 != "" ? substr($cmp2, 0, 10) : 0);
                }

        }

        //make comparison
        switch ($sign) {
            case '<'  :
                $output = $cmp1 < $cmp2;
                break;
            case '<=' :
                $output = $cmp1 <= $cmp2;
                break;
            case '='  :
                $output = $cmp1 == $cmp2;
                break;
            case '>=' :
                $output = $cmp1 >= $cmp2;
                break;
            case '>'  :
                $output = $cmp1 > $cmp2;
                break;
        }

        return $output;
    }


    function _printTable_courses(
        $type,
        $query_course_user,
        &$category_list,
        &$category_path_list,
        &$session_list,
        &$lastaccess_list,
        &$time_list,
        &$score_start,
        &$score_final,
        &$score_course,
        &$filter_rows,
        &$filter_columns,
        $show_classrooms_editions,
        $classrooms_editions_info)
    {
        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');

        if (!$type) $type = 'html';
        $buffer = new ReportTablePrinter($type);

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $cols = $filter_columns['showed_columns'];
        $output = '';

        $course_types_trans = array(
            'elearning' => Lang::t('_ELEARNING', 'standard'),
            'classroom' => Lang::t('_CLASSROOM', 'standard')
        );

        require_once(_lms_ . '/lib/lib.levels.php');
        $clevels = new CourseLevel();
        $user_levels_trans = $clevels->getLevels();
        unset($clevels);

        require_once(_lms_ . '/admin/models/LabelAlms.php');
        $label_model = new LabelAlms();

        $buffer->openTable($lang->def('_RU_CAPTION'), $lang->def('_RU_CAPTION'));

        $th1 = array();
        $th2 = array();

        $colspanuser = 0;
        $th2[] = $lang->def('_USERNAME');
        $colspanuser++;
        if (in_array('_TH_LASTNAME', $cols)) {
            $th2[] = Lang::t('_LASTNAME', 'standard');
            $colspanuser++;
        }
        if (in_array('_TH_FIRSTNAME', $cols)) {
            $th2[] = Lang::t('_FIRSTNAME', 'standard');
            $colspanuser++;
        }
        if (in_array('_TH_EMAIL', $cols)) {
            $th2[] = Lang::t('_EMAIL', 'standard');
            $colspanuser++;
        }
        if (in_array('_TH_REGISTER_DATE', $cols)) {
            $th2[] = Lang::t('_REGISTER_DATE', 'standard');
            $colspanuser++;
        }
        if (in_array('_TH_SUSPENDED', $cols)) {
            $th2[] = Lang::t('_SUSPENDED', 'standard');
            $colspanuser++;
        }
        if (in_array('_TH_ORGANIZATION_CHART', $cols)) {
            $th2[] = Lang::t('_ORGCHART', 'standard');
            $colspanuser++;
        }

        require_once($GLOBALS['where_framework'] . '/lib/lib.field.php');
        $aclManager = new DoceboACLManager();
        $aclManager->include_suspended = TRUE;
        $_users = $aclManager->getAllUsersFromSelection($filter_rows);
        $fman = new FieldList();
        $field_values = array();
        $customcols =& $filter_columns['custom_fields'];
        $num_row = 0;
        foreach ($customcols as $the_idField) {
            list($exists) = sql_fetch_row(sql_query('Select idField from core_field where idField=' . $the_idField['id']));
            if ($exists == NULL)
                $customcols[$num_row]['selected'] = FALSE;

            $num_row++;
        }

        $custom_list = array();
        foreach ($customcols as $val) {
            if ($val['selected']) {
                $colspanuser++;
                $th2[] = $val['label'];
                $custom_list[] = $val['id'];
            }
        }
        $field_values = (!empty($custom_list)) ? $fman->getUsersFieldEntryData($_users, $custom_list) : array();

        $colspan1 = 0;
        $colspan2 = 0;
        $colspan3 = 1;

        if (in_array('_TH_CAT', $cols)) {
            $th2[] = $lang->def('_CATEGORY');
            $colspan1++;
        }
        if (in_array('_TH_CODE', $cols)) {
            $th2[] = $lang->def('_CODE');
            $colspan1++;
        }
        $th2[] = $lang->def('_COURSE_NAME');
        $colspan1++;
        if (in_array('_TH_COURSETYPE', $cols)) {
            $th2[] = Lang::t('_COURSE_TYPE', 'course');
            $colspan1++;
        }
        if (in_array('_TH_COURSELABEL', $cols)) {
            $th2[] = $lang->def('_TH_COURSELABEL');
            $colspan1++;
        }
        if (in_array('_TH_COURSEPATH', $cols)) {
            $th2[] = $lang->def('_TH_COURSEPATH');
            $colspan1++;
        }
        if (in_array('_TH_COURSESTATUS', $cols)) {
            $th2[] = $lang->def('_STATUS');
            $colspan1++;
        }
        if (in_array('_TH_COURSECREDITS', $cols)) {
            $th2[] = Lang::t('_CREDITS', 'standard');
            $colspan1++;
        }

        $colspan_classrooms_editions = 0;
        if ($show_classrooms_editions) {
            if (in_array('_TH_CLASSROOM_CODE', $cols)) {
                $th2[] = Lang::t('_NAME', 'standard');
                $colspan_classrooms_editions++;
            }
            if (in_array('_TH_CLASSROOM_NAME', $cols)) {
                $th2[] = Lang::t('_CODE', 'standard');
                $colspan_classrooms_editions++;
            }
            if (in_array('_TH_CLASSROOM_MIN_DATE', $cols)) {
                $th2[] = Lang::t('_DATE_BEGIN', 'standard');
                $colspan_classrooms_editions++;
            }
            if (in_array('_TH_CLASSROOM_MAX_DATE', $cols)) {
                $th2[] = Lang::t('_DATE_END', 'standard');
                $colspan_classrooms_editions++;
            }
        }

        if (in_array('_TH_USER_INSCRIPTION_DATE', $cols)) {
            $th2[] = $lang->def('_TH_USER_INSCRIPTION_DATE');
            $colspan2++;
        }
        if (in_array('_TH_USER_START_DATE', $cols)) {
            $th2[] = $lang->def('_DATE_FIRST_ACCESS');
            $colspan2++;
        }
        if (in_array('_TH_USER_END_DATE', $cols)) {
            $th2[] = $lang->def('_COMPLETED');
            $colspan2++;
        }
        if (in_array('_TH_LAST_ACCESS_DATE', $cols)) {
            $th2[] = $lang->def('_DATE_LAST_ACCESS');
            $colspan2++;
        }
        if (in_array('_TH_USER_LEVEL', $cols)) {
            $th2[] = $lang->def('_LEVEL');
            $colspan2++;
        }
        if (in_array('_TH_USER_STATUS', $cols)) {
            $th2[] = $lang->def('_STATUS');
            $colspan2++;
        }
        if (in_array('_TH_USER_START_SCORE', $cols)) {
            $th2[] = $lang->def('_TH_USER_START_SCORE');
            $colspan2++;
        }
        if (in_array('_TH_USER_FINAL_SCORE', $cols)) {
            $th2[] = $lang->def('_FINAL_SCORE');
            $colspan2++;
        }
        if (in_array('_TH_USER_COURSE_SCORE', $cols)) {
            $th2[] = $lang->def('_TH_USER_COURSE_SCORE');
            $colspan2++;
        }
        if (in_array('_TH_USER_NUMBER_SESSION', $cols)) {
            $th2[] = $lang->def('_TH_USER_NUMBER_SESSION');
            $colspan2++;
        }
        if (in_array('_TH_USER_ELAPSED_TIME', $cols)) {
            $th2[] = $lang->def('_TOTAL_TIME');
            $colspan2++;
        }
        if (in_array('_TH_ESTIMATED_TIME', $cols)) {
            $th2[] = $lang->def('_TH_ESTIMATED_TIME');
            $colspan2++;
        }
        //checkbox for mail
        if ($this->use_mail) $th2[] = array(
            'style' => 'img-cell',
            'value' => $this->_loadEmailIcon()
        );

        $th1 = array();
        $th1[] = array('colspan' => $colspanuser, 'value' => $lang->def('_USERS'));
        $th1[] = array('colspan' => $colspan1, 'value' => $lang->def('_COURSES'));
        if ($show_classrooms_editions) $th1[] = array('colspan' => $colspan_classrooms_editions, 'value' => Lang::t('_CLASSROOM', 'standard'));
        $th1[] = array('colspan' => $colspan2, 'value' => $lang->def('_STATUS'));
        //$th1[] = '';

        $buffer->openHeader();
        $buffer->addHeader($th1);
        $buffer->addHeader($th2);
        $buffer->closeHeader();

        $re_course_user = sql_query($query_course_user);

        $i = 0;
        $count_rows = 0;

        $buffer->openBody();
        $exclusive = ($filter_columns['filter_exclusive'] == 1 ? true : false); //1 if exclusive, 0 if inclusive
        while ($sql_row = sql_fetch_array($re_course_user)) {

            if ($show_classrooms_editions) {
                list($id_user, $code, $id_course, $id_category, $name, $status, $level_user,
                    $status_user, $date_inscr, $date_first_access, $date_complete, $medium_time, $course_type, $credits,
                    $userid, $firstname, $lastname, $email, $register_date, $valid,
                    $id_date, $classroom_date_subscription, $classroom_date_complete) = $sql_row;
            } else {
                list($id_user, $code, $id_course, $id_category, $name, $status, $level_user,
                    $status_user, $date_inscr, $date_first_access, $date_complete, $medium_time, $course_type, $credits,
                    $userid, $firstname, $lastname, $email, $register_date, $valid) = $sql_row;
            }

            //$draw_row = $exclusive;
            if (!isset($filter_columns['sub_filters'])) {
                $filter_columns['sub_filters'] = array();
            }

            if (count($filter_columns['sub_filters']) <= 0) {
                $condition = true; //no conditions to check
            } else {

                $condition = $exclusive;
                foreach ($filter_columns['sub_filters'] as $key => $value) {
                    $temp = false;

                    switch ($value['option']) {
                        case _COURSES_FILTER_SESSION_NUMBER:
                            {
                                if (isset($session_list[$id_user . '_' . $id_course]))
                                    $temp = $this->_check($session_list[$id_user . '_' . $id_course], $value['value'], $value['sign'], _FILTER_INTEGER);
                            }
                            break;

                        case _COURSES_FILTER_SCORE_INIT:
                            {
                                if (isset($score_start[$id_course][$id_user]))
                                    $temp = $this->_check($score_start[$id_course][$id_user]['score'], $value['value'], $value['sign'], _FILTER_INTEGER);
                            }
                            break;

                        case _COURSES_FILTER_SCORE_END:
                            {
                                if (isset($score_final[$id_course][$id_user]))
                                    $temp = $this->_check($score_final[$id_course][$id_user]['score'], $value['value'], $value['sign'], _FILTER_INTEGER);
                            }
                            break;

                        case _COURSES_FILTER_INSCRIPTION_DATE:
                            {
                                $temp = $this->_check($date_inscr, $value['value'], $value['sign'], _FILTER_DATE);
                            }
                            break;

                        case _COURSES_FILTER_END_DATE:
                            {
                                $temp = $this->_check($date_complete, $value['value'], $value['sign'], _FILTER_DATE);
                            }
                            break;

                        case _COURSES_FILTER_FIRSTACCESS_DATE:
                            {
                                $temp = $this->_check($date_first_access, $value['value'], $value['sign'], _FILTER_DATE);
                            }
                            break;

                        case _COURSES_FILTER_LASTACCESS_DATE:
                            {
                                if (isset($lastaccess_list[$id_user . '_' . $id_course]))
                                    $temp = $this->_check($lastaccess_list[$id_user . '_' . $id_course], $value['value'], $value['sign'], _FILTER_DATE);
                            }
                            break;

                        case _COURSES_FILTER_SCORE_COURSE:
                            {
                                if (isset($score_course[$id_user][$id_course]))
                                    $temp = $this->_check($score_course[$id_user][$id_course]['score'], $value['value'], $value['sign'], _FILTER_INTEGER);
                            }
                            break;
                    }

                    if ($exclusive) {
                        $condition = ($condition && $temp);
                        if (!$condition) break; //if false, no more conditions needed
                    } else {
                        $condition = ($condition || $temp);
                        if ($condition) break; //if true, no more conditions needed
                    }

                }
            }

            //cout('<div>'.($condition ? 'true' : 'false').'</div>');
            if ($condition) {


                $row = array();
                $row[] = Docebo::aclm()->relativeId($userid);
                if (in_array('_TH_LASTNAME', $cols)) $row[] = $lastname;
                if (in_array('_TH_FIRSTNAME', $cols)) $row[] = $firstname;
                if (in_array('_TH_EMAIL', $cols)) $row[] = $email;
                if (in_array('_TH_REGISTER_DATE', $cols)) $row[] = Format::date($register_date, 'datetime');
                if (in_array('_TH_SUSPENDED', $cols)) $row[] = $valid ? Lang::t('_NO', 'standard') : Lang::t('_YES', 'standard');

                require_once(_adm_ . "/models/UsermanagementAdm.php");
                $umodel = new UsermanagementAdm();
                $folders = $umodel->getUserFolders($id_user);
                $organization_chart = "";

                $first = true;
                foreach ($folders as $keyFolder => $valueFolder) {
                    if ($first) {
                        $organization_chart = '' . $valueFolder;
                        $first = false;
                    } else {
                        $organization_chart .= '</br>' . $valueFolder;
                    }
                }

                if (in_array('_TH_ORGANIZATION_CHART', $cols)) $row[] = $organization_chart;

                foreach ($customcols as $val) {
                    if ($val['selected']) {
                        if (isset($field_values[$id_user][$val['id']]))
                            $row[] = $field_values[$id_user][$val['id']];
                        else
                            $row[] = '';
                    }
                }

                if (in_array('_TH_CAT', $cols)) $row[] = $category_list[$id_category];
                if (in_array('_TH_CODE', $cols)) $row[] = $code;
                $row[] = $name;
                //add _TH_COURSETYPE

                if (in_array('_TH_COURSETYPE', $cols)) $row[] = isset($course_types_trans[$course_type]) ? $course_types_trans[$course_type] : "";
                if (in_array('_TH_COURSELABEL', $cols)) {
                    $course_label_id = $label_model->getCourseLabel($id_course);
                    if ($course_label_id > 0) {
                        $arr_course_label = $label_model->getLabelInfo($course_label_id);
                        $row[] = $arr_course_label[getLanguage()][LABEL_TITLE];
                    } else {
                        $row[] = "";
                    }
                }
                if (in_array('_TH_COURSEPATH', $cols)) $row[] = $category_path_list[$id_category];
                if (in_array('_TH_COURSESTATUS', $cols)) $row[] = $this->_convertStatusCourse($status);
                if (in_array('_TH_COURSECREDITS', $cols)) $row[] = $credits;

                if ($show_classrooms_editions) {

                    $e_code = $e_name = $date_1 = $date_2 = '';
                    if (isset($classrooms_editions_info['classrooms'][$id_date])) {
                        $e_code = $classrooms_editions_info['classrooms'][$id_date]->code;
                        $e_name = $classrooms_editions_info['classrooms'][$id_date]->name;
                        $date_1 = Format::date($classrooms_editions_info['classrooms'][$id_date]->date_1, 'datetime');
                        $date_2 = Format::date($classrooms_editions_info['classrooms'][$id_date]->date_2, 'datetime');
                    }

                    if (in_array('_TH_CLASSROOM_CODE', $cols)) $row[] = $e_code;
                    if (in_array('_TH_CLASSROOM_NAME', $cols)) $row[] = $e_name;
                    if (in_array('_TH_CLASSROOM_MIN_DATE', $cols)) $row[] = $date_1;
                    if (in_array('_TH_CLASSROOM_MAX_DATE', $cols)) $row[] = $date_2;
                }

                if (in_array('_TH_USER_INSCRIPTION_DATE', $cols)) $row[] = Format::date($date_inscr);
                if (in_array('_TH_USER_START_DATE', $cols)) $row[] = ($date_first_access !== NULL ? Format::date($date_first_access) : '&nbsp;');
                if (in_array('_TH_USER_END_DATE', $cols)) $row[] = ($date_complete !== NULL ? Format::date($date_complete) : '&nbsp;');
                if (in_array('_TH_LAST_ACCESS_DATE', $cols)) $row[] = (isset($lastaccess_list[$id_user . '_' . $id_course]) ? Format::date($lastaccess_list[$id_user . '_' . $id_course]) : '');
                if (in_array('_TH_USER_LEVEL', $cols)) $row[] = $user_levels_trans[$level_user];
                if (in_array('_TH_USER_STATUS', $cols)) $row[] = $this->_convertStatusUser($status_user);

                if (in_array('_TH_USER_START_SCORE', $cols)) $row[] = (isset($score_start[$id_course][$id_user])
                    ? $score_start[$id_course][$id_user]['score'] . ' / ' . $score_start[$id_course][$id_user]['max_score']
                    : '');

                if (in_array('_TH_USER_FINAL_SCORE', $cols)) $row[] = (isset($score_final[$id_course][$id_user])
                    ? $score_final[$id_course][$id_user]['score'] . ' / ' . $score_final[$id_course][$id_user]['max_score']
                    : '');

                if (in_array('_TH_USER_COURSE_SCORE', $cols)) $row[] = (isset($score_course[$id_user][$id_course])
                    ? $score_course[$id_user][$id_course]['score'] . ' / ' . $score_course[$id_user][$id_course]['max_score']
                    : '');

                if (in_array('_TH_USER_NUMBER_SESSION', $cols)) $row[] = (isset($session_list[$id_user . '_' . $id_course]) ? $session_list[$id_user . '_' . $id_course] : '');

                if (in_array('_TH_USER_ELAPSED_TIME', $cols)) $row[] = (isset($time_list[$id_user . '_' . $id_course]) ?
                    substr('0' . ((int)($time_list[$id_user . '_' . $id_course] / 3600)), -2) . 'h '
                    . substr('0' . ((int)(($time_list[$id_user . '_' . $id_course] % 3600) / 60)), -2) . 'm '
                    . substr('0' . ((int)($time_list[$id_user . '_' . $id_course] % 60)), -2) . 's ' : '&nbsp;');


                if (in_array('_TH_ESTIMATED_TIME', $cols)) $row[] = $medium_time . 'h';

                //checkbox for mail
                if ($this->use_mail) $row[] = '<div class="align_center">' . Form::getInputCheckbox('mail_' . $id_user, 'mail_recipients[]', $id_user, isset($_POST['select_all']), '') . '</div>';
                $buffer->addLine($row);

                $count_rows++;
            }
        }

        $buffer->closeBody();
        $buffer->closeTable();

        $output .= $buffer->get();

        YuiLib::load(array('selector' => 'selector-beta-min.js'));

        if ($this->use_mail) {
            cout('<script type="text/javascript">
                    function _getAllCheckBoxes() {
                        return YAHOO.util.Selector.query("input[id^=mail_]");
                    }

                    function selectAll() {
                        var sel = _getAllCheckBoxes();
                        for (var i=0; i<sel.length; i++) {
                            sel[i].checked=true;
                        }
                    }

                    function unselectAll() {
                        var sel = _getAllCheckBoxes();
                        for (var i=0; i<sel.length; i++) {
                            sel[i].checked=false;
                        }
                    }

                    YAHOO.util.Event.addListener("select_all-button", "click", selectAll);
                    YAHOO.util.Event.addListener("unselect_all-button", "click", unselectAll);
                </script>', 'scripts');
        }

        //if ($this->use_mail) { $this->_loadEmailActions(); }
        if ($this->use_mail) {
            $mlang =& DoceboLanguage::createInstance('report', 'framework');
            //$output .= Form::getHidden('no_show_repdownload', 'no_show_repdownload', 1);
            $output .= Form::openButtonSpace()
                . Form::getHidden('no_show_repdownload', 'no_show_repdownload', 1)
                . Form::openButtonSpace()
                . Form::getButton('send_mail', 'send_mail', $mlang->def('_SEND_MAIL'))
                //.'<button type="button" class="button" id="select_all" name="select_all" onclick="selectAll();">'.$mlang->def('_SELECT_ALL').'</button>'//.Form::getButton('select_all', 'select_all', $lang->def('_SELECT_ALL'))
                //.'<button type="button" class="button" id="unselect_all" name="unselect_all" onclick="unselectAll();">'.$mlang->def('_UNSELECT_ALL').'</button>'//.Form::getButton('unselect_all', 'unselect_all', $lang->def('_UNSELECT_ALL'))
                . Form::getButton('select_all', false, Lang::t('_SELECT_ALL', 'standard'), FALSE, 'onclick="selectAll();"', true, false)
                . Form::getButton('unselect_all', false, Lang::t('_UNSELECT_ALL', 'standard'), FALSE, 'onclick="unselectAll();"', true, false)
                . Form::closeButtonSpace();
            //cout(Form::closeForm());
        }
        if ($count_rows > 0)
            return $output;
        else
            return $lang->def('_NULL_REPORT_RESULT'); //null result string
    }


    function _convertStatusCourse($status)
    {
        if (isset($this->status_c[$status])) //just debug, sometimes it receives an inexistent status
            return $this->status_c[$status];
        else return '';
    }

    function _convertStatusUser($status)
    {
        return $this->status_u[$status];
    }


    //competences section **********************************************************


    function show_report_competences($report_data = NULL, $other = '')
    {
        $jump_url = ''; //show_report

        checkPerm('view');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['send_mail_confirm']))
            $op = 'send_mail_confirm';
        elseif (isset($_POST['send_mail'])) {
            $op = 'send_mail';
        } else {
            $op = 'show_result';
        }

        switch ($op) {

            case 'send_mail_confirm':
                {
                    $subject = importVar('mail_object', false, '[' . $lang->def('_SUBJECT') . ']');//'[No subject]');
                    $body = importVar('mail_body', false, '');
                    $acl_man = new DoceboACLManager();

                    $sender = Get::sett('sender_event');
                    $mail_recipients = unserialize(urldecode(Get::req('mail_recipients', DOTY_STRING, '')));

                    // prepare intestation for email
                    $from = "From: " . $sender . $GLOBALS['mail_br'];
                    $header = "MIME-Version: 1.0" . $GLOBALS['mail_br']
                        . "Content-type: text/html; charset=" . getUnicode() . $GLOBALS['mail_br'];
                    $header .= "Return-Path: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    //$header .= "Reply-To: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Sender: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Mailer: PHP/" . phpversion() . $GLOBALS['mail_br'];

                    // send mail
                    $arr_recipients = array();
                    foreach ($mail_recipients as $recipient) {
                        $rec_data = $acl_man->getUser($recipient, false);
                        //mail($rec_data[ACL_INFO_EMAIL] , stripslashes($subject), stripslashes(nl2br($body)), $from.$header."\r\n");
                        $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
                    }
                    $mailer = DoceboMailer::getInstance();
                    $mailer->addReplyTo(Get::sett('sender_event').$GLOBALS['mail_br']);
                    $mailer->SendMail($sender, $arr_recipients, stripslashes($subject), stripslashes(nl2br($body)));

                    $result = getResultUi($lang->def('_OPERATION_SUCCESSFUL'));

                    //$this->show_report($alluser, $jump_url, $org_chart_subdivision, $day_from_subscription, $day_until_course_end, $date_until_course_end, $report_type, $course_selected, $user_selected);
                    cout($this->_get_competences_query('html', NULL, $result));
                }
                break;

            case 'send_mail':
                {
                    require_once(_base_ . '/lib/lib.form.php');
                    $mail_recipients = Get::req('mail_recipients', DOTY_MIXED, array());
                    cout(
                        ''//Form::openForm('course_selection', Util::str_replace_once('&', '&amp;', $jump_url))
                        . Form::openElementSpace()
                        . Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
                        . Form::getTextarea($lang->def('_MAIL_BODY'), 'mail_body', 'mail_body')
                        . Form::getHidden('mail_recipients', 'mail_recipients', urlencode(serialize($mail_recipients)))
                        . Form::closeElementSpace()
                        . Form::openButtonSpace()
                        . Form::getButton('send_mail_confirm', 'send_mail_confirm', $lang->def('_SEND_MAIL'))
                        . Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
                        . Form::closeButtonSpace()
                        //.Form::closeForm()
                        . '</div>', 'content');
                }
                break;

            default:
                {
                    cout($this->_get_competences_query('html', $report_data, $other));
                }

        }
        //cout( $this->_get_competences_query() );
    }

    function _get_competences_query($type = 'html', $report_data = NULL, $other = '')
    {
        $cmodel = new CompetencesAdm();

        if ($report_data == NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $rc_filters =& $ref['columns_filter']['filters_list'];
        $rc_exclusive = $ref['columns_filter']['exclusive'];
//die('<pre>'.print_r($rc_filters, true).'</pre>');
        $final_arr = array();


        $all_users = $ref['rows_filter']['all_users'];
        $users_selection = $ref['rows_filter']['users'];

        //check admin permissions
        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_ . '/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());
            $admin_users = Docebo::aclm()->getAllUsersFromIdst($admin_tree);
            $all_users = false;
            $users_selection = array_intersect($users_selection, $admin_users);
            unset($admin_users); //free some memory
        }

        if (!$all_users && empty($users_selection)) {
            cout(Lang::t('_EMPTY_SELECTION', 'report'), 'content');
            return;
        }

        if (!$all_users)
            $user_query_select = " AND t4.idst IN (" . implode(',', $users_selection) . ")";
        else
            $user_query_select = "";


        //process filter and build query
        $table1 = "%lms_competence";
        $table2 = "%lms_competence_lang";
        $table3 = "%lms_competence_user";
        $table4 = "%adm_user";

        //extract all competneces for all selected users and store the data
        $arr_data = array();
        $arr_userids = array();
        $arr_competences = array();
        $language = getLanguage();
        $acl_man = Docebo::user()->getACLManager();
        $query = "SELECT t1.id_competence, t2.name, t3.id_user, t4.userid, t3.score_got "
            . " FROM (" . $table1 . " as t1 LEFT JOIN " . $table2 . " as t2 ON (t1.id_competence = t2.id_competence "
            . " AND t2.lang_code='" . $language . "')) JOIN " . $table3 . " as t3 ON (t1.id_competence = t3.id_competence) "
            . " JOIN " . $table4 . " as t4 ON (t3.id_user = t4.idst AND t4.valid=1 " . $user_query_select . " ) "
            . " ORDER BY t4.userid";
        $res = sql_query($query);
        while ($obj = sql_fetch_object($res)) {
            $arr_data[$obj->id_user][$obj->id_competence] = $obj->score_got;
            if (!in_array($obj->id_competence, $arr_competences)) $arr_competences[] = $obj->id_competence;
            $arr_userids[$obj->id_user] = $acl_man->relativeId($obj->userid);
        }

        if (count($arr_competences) <= 0) {
            cout(Lang::t('_NO_CONTENT', 'report'), 'content');
            return;
        }

        $cinfo = $cmodel->getCompetencesInfo($arr_competences);
        $ucount = 0;
        $signs = array('0' => '<', '1' => '<=', '2' => '=', '3' => '>=', '4' => '>');
        $conds = array();
        $icon_actv = '<span class="ico-sprite subs_actv"><span>' . Lang::t('_COMPETENCE_OBTAINED', 'competences') . '</span></span>';
        $icon_email = $this->_loadEmailIcon();

        //prepare buffer object
        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');
        $buffer = new ReportTablePrinter($type, true);
        $buffer->openTable(Lang::t('_RC_CAPTION', 'report'), Lang::t('RC_CAPTION', 'report'));
        $buffer->openHeader();

        //set header
        $_head = array(Lang::t('_USER', 'standard'));
        foreach ($arr_competences as $cid) {
            $_head[] = array('style' => 'img-cell', 'value' => $cinfo[$cid]->langs[$language]['name']);
        }
        if ($this->use_mail) $_head[] = array('style' => 'img-cell', 'value' => $icon_email);

        //render header
        $buffer->addHeader($_head);
        $buffer->closeHeader();
        $buffer->openBody();
//die('<pre>'.print_r($rc_filters, true).'</pre>');
        //check all data row and print them
        while (list($id_user, $ucomps) = each($arr_data)) {
            $is_valid = true;

            $satisfied = 0;
            $num_conditions = 0;

            foreach ($rc_filters as $id_competence => $filter) {
                if (isset($filter['flag'])) { //we are checking a competence of type 'flag' --> just check if the score exists and is > 0
                    $num_conditions++;
                    if ($filter['flag'] == 'yes') {
                        //check conditions
                        if (array_key_exists($id_competence, $ucomps) && $ucomps[$id_competence] > 0) {
                            $satisfied++;
                        }
                    } else {
                        if (!array_key_exists($id_competence, $ucomps) || $ucomps[$id_competence] <= 0) {
                            $satisfied++;
                        }
                    }
                } else {
                    foreach ($filter as $fvalue) {
                        $num_conditions++;
                        $_sign = $fvalue['sign'];
                        $_value = $fvalue['value'];
                        if (array_key_exists($id_competence, $ucomps) && $ucomps[$id_competence] > 0) {
                            //condition ok
                            $condition = false;
                            switch ($_sign) {
                                case 0:
                                    $condition = $ucomps[$id_competence] < $_value;
                                    break;
                                case 1:
                                    $condition = $ucomps[$id_competence] <= $_value;
                                    break;
                                case 2:
                                    $condition = $ucomps[$id_competence] == $_value;
                                    break;
                                case 3:
                                    $condition = $ucomps[$id_competence] >= $_value;
                                    break;
                                case 4:
                                    $condition = $ucomps[$id_competence] > $_value;
                                    break;
                            }
                            if ($condition) {
                                $satisfied++;
                            }
                        }

                    }
                }

            }

            $is_valid = true;
            if ($num_conditions > 0) {
                $is_valid = false;
                if ($rc_exclusive && $satisfied >= $num_conditions) $is_valid = true;
                if (!$rc_exclusive && $satisfied > 0) $is_valid = true;
            }

            if ($is_valid) {
                //update lines counter
                $ucount++;

                //set line values
                $line = array(
                    $arr_userids[$id_user]
                );
                foreach ($arr_competences as $id_competence) {
                    $line[] = array(
                        'style' => 'img-cell',
                        'value' => (array_key_exists($id_competence, $ucomps) && $ucomps[$id_competence] > 0
                            ? ($cinfo[$id_competence]->type == 'score' ? '<b>' . $ucomps[$id_competence] . '</b>' : $icon_actv)
                            : '')
                    );
                }
                if ($this->use_mail)
                    $line[] = array(
                        'style' => 'img-cell',
                        'value' => '<div class="align_center">' . Form::getInputCheckbox('mail_' . $id_user, 'mail_recipients[]', $id_user, isset($_POST['select_all']), '') . '</div>'
                    );

                //render line
                $buffer->addLine($line);
            }

        }

        //check if we have rendered any row
        if ($ucount <= 0) {
            cout(Lang::t('_NO_CONTENT', 'report'), 'content');
            return;
        }

        //close table
        $buffer->closeBody();
        $buffer->closeTable();

        //*****************

        cout($buffer->get());
        if ($this->use_mail) {
            $this->_loadEmailActions();
        }
    }


    //******************************************************************************


    protected function _check_delay_column($key)
    {
        //for beckward compatibility
        $convert = array(
            '_LASTNAME' => 'lastname',
            '_NAME' => 'firstname',
            '_STATUS' => 'status',
            '_EMAIL' => 'email',
            '_DATE_INSCR' => 'date_subscription',
            '_DATE_FIRST_ACCESS' => 'date_first_access',
            '_DATE_COURSE_COMPLETED' => 'date_course_completed'
        );
        return (isset($convert[$key]) ? $convert[$key] : $key);
    }

    function get_delay_filter()
    {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_ . '/lib/lib.form.php');
        require_once(_lms_ . '/lib/lib.course.php');

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            //go back at the previous step
            Util::jump_to($back_url);
        }

        //set $_POST data in $_SESSION['report_tempdata']
        $ref =& $_SESSION['report_tempdata']['columns_filter'];
        $selector = new Selector_Course();
        if (isset($_POST['update_tempdata'])) {
            $selector->parseForState($_POST);
            $temp = array(
                'report_type_completed' => ($_POST['report_type'] == "course_completed" || $_POST['report_type'] == "both" ? true : false),//( isset($_POST['report_type_completed']) ? true : false ),
                'report_type_started' => ($_POST['report_type'] == "course_started" || $_POST['report_type'] == "both" ? true : false),//( isset($_POST['report_type_started']) ? true : false ),
                'day_from_subscription' => $_POST['day_from_subscription'],
                'day_until_course_end' => $_POST['day_until_course_end'],
                'date_until_course_end' => Format::dateDb($_POST['date_until_course_end'], 'date'),
                'org_chart_subdivision' => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                'all_courses' => ($_POST['all_courses'] == 1 ? true : false),
                'selected_courses' => $selector->getSelection(),
                'showed_columns' => (isset($_POST['cols']) ? $_POST['cols'] : array()),
                'order_by' => Get::req('order_by', DOTY_STRING, 'userid'),
                'order_dir' => Get::req('order_dir', DOTY_STRING, 'asc'),
                'show_suspended' => Get::req('show_suspended', DOTY_INT, 0) > 0,
            );
            $_SESSION['report_tempdata']['columns_filter'] = $temp; //$ref = $temp;
        } else {
            //first loading of this page -> prepare $_SESSION data structure
            //if (isset($_SESSION['report_update']) /* && is equal to id_report */) break;

            if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
                $_SESSION['report_tempdata']['columns_filter'] = array(//$ref = array(
                    'report_type_completed' => false,
                    'report_type_started' => false,
                    'day_from_subscription' => '',
                    'day_until_course_end' => '',
                    'date_until_course_end' => '',
                    'org_chart_subdivision' => 0,
                    'all_users' => false,
                    'all_courses' => true,
                    'selected_courses' => array(),
                    'showed_columns' => array(),
                    'order_by' => 'userid',
                    'order_dir' => 'asc',
                    'show_suspended' => false,
                );
            }
        }
        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
            Util::jump_to($temp_url);
        }

        $lang = $this->lang;

        cout(Form::getHidden('update_tempdata', 'update_tempdata', 1), 'content');


        $array_report_type = array(
            $lang->def('_COURSE_COMPLETED') => "course_completed",
            $lang->def('_COURSE_STARTED') => "course_started",
            $lang->def('_FILTER_ALL_CONDS') => "both"
        );

        //box for rpeort options
        $box = new ReportBox('delay_options_box');
        $box->title = $lang->def('_REPORT_USER_TITLE_TIMEBELT');
        $box->description = $lang->def('_REPORT_USER_TITLE_TIMEBELT_DESC');
        $selected_radio = "both";
        if (!$ref['report_type_completed'] || !$ref['report_type_started']) {
            if ($ref['report_type_completed']) $selected_radio = 'course_completed';
            if ($ref['report_type_started']) $selected_radio = 'course_started';
        }
        $box->body =
            Form::getRadioSet('', 'report_type', 'report_type', $array_report_type, $selected_radio)
            // Form::getCheckBox($dlang->def( '_COURSE_COMPLETED' ), 'report_type_completed', 'report_type_completed', 1, (isset($ref['report_type_completed']) ? $ref['report_type_completed'] : false) )
            //.Form::getCheckBox($dlang->def( '_COURSE_STARTED' ), 'report_type_started', 'report_type_started', 1, (isset($ref['report_type_started']) ? $ref['report_type_started'] : false) )
            . Form::getTextfield($lang->def('_DAY_FROM_SUBSCRIPTION'), 'day_from_subscription', 'day_from_subscription', 20, $ref['day_from_subscription'])
            . Form::getTextfield($lang->def('_DAY_UNTIL_COURSE_END'), 'day_until_course_end', 'day_until_course_end', 20, $ref['day_until_course_end'])
            . Form::getDatefield($lang->def('_DATE_UNTIL_COURSE_END'), 'date_until_course_end', 'date_until_course_end', Format::date($ref['date_until_course_end'], 'date'))
            //.Form::getCloseFieldset()
            /*.Form::getCheckbox(   $lang->def('ORG_CHART_SUBDIVISION'), 'org_chart_subdivision_'.$id_report,   'org_chart_subdivision', $ref['org_chart_subdivision'] )*/
            . Form::getBreakRow();

        cout($box->get());

        YuiLib::load('datasource');
        Util::get_js(Get::rel_path('lms') . '/admin/modules/report/courses_filter.js', true, true);

        //box for direct course selection
        $selection =& $ref['selected_courses'];
        $selector->parseForState($_POST);
        $selector->resetSelection($selection);
        $temp = count($selection);

        $box = new ReportBox('course_selector');
        $box->title = $lang->def('_REPORT_COURSE_SELECTION');
        $box->description = false;
        $box->body .= '<div class="fc_filter_line filter_corr">';
        $box->body .= '<input id="all_courses" name="all_courses" type="radio" value="1" ' . ($ref['all_courses'] ? 'checked="checked"' : '') . ' />';
        $box->body .= ' <label for="all_courses">' . $lang->def('_ALL_COURSES') . '</label>';
        $box->body .= ' <input id="sel_courses" name="all_courses" type="radio" value="0" ' . ($ref['all_courses'] ? '' : 'checked="checked"') . ' />';
        $box->body .= ' <label for="sel_courses">' . $lang->def('_SEL_COURSES') . '</label>';
        $box->body .= '</div>';

        $box->body .= '<div id="selector_container"' . ($ref['all_courses'] ? ' style="display:none"' : '') . '>';
        //$box->body .= Form::openElementSpace();
        $box->body .= $selector->loadCourseSelector(true);
        //$box->body .= Form::closeElementSpace();
        $box->body .= '</div>';
        $box->footer = $lang->def('_CURRENT_SELECTION') . ':&nbsp;<span id="csel_foot">' . ($ref['all_courses'] ? $lang->def('_ALL') : ($temp != '' ? $temp : '0')) . '</span>';
        cout($box->get());

        cout(
            '<script type="text/javascript">courses_count=' . ($temp == '' ? '0' : $temp) . ';' .
            'courses_all="' . $lang->def('_ALL') . '";' . "\n" .
            'YAHOO.util.Event.addListener(window, "load", courses_selector_init);</script>');


        $box = new ReportBox('columns_selector');
        $box->title = $lang->def('_SELECT_THE_DATA_COL_NEEDED');
        $box->description = false;
        $box->body = Form::getOpenFieldset($lang->def('_SHOWED_COLUMNS'));

        //backward compatibility
        $arr_check_columns = array();
        foreach ($ref['showed_columns'] as $_column_key) {
            $arr_check_columns[] = $this->_check_delay_column($_column_key);
        }

        foreach ($this->delay_columns as $delay_row) {
            if ($delay_row['select']) { //column is selectable
                $box->body .= Form::getCheckBox(
                    $delay_row['label'],
                    'col_' . $delay_row['key'],
                    'cols[]',
                    $delay_row['key'],
                    in_array($delay_row['key'], $arr_check_columns) ? true : false
                );
            }
        }
        $box->body .= Form::getCloseFieldset();

        cout($box->get());

        //other options
        $box = new ReportBox('other_options');
        $box->title = Lang::t('_OTHER_OPTION', 'course');
        $box->description = false;

        $sort_list = array(
            'userid' => Lang::t('_USERID', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'status' => Lang::t('_STATUS', 'standard'),
            'date_subscription' => Lang::t('_DATE_INSCR', 'report'),
            'date_first_access' => Lang::t('_DATE_FIRST_ACCESS', 'standard'),
            'date_last_access' => Lang::t('_DATE_LAST_ACCESS', 'standard'),
            'date_complete' => Lang::t('_DATE_END', 'standard')
        );
        $dir_list = array(
            'asc' => Lang::t('_ORD_ASC_TITLE', 'standard'),
            'desc' => Lang::t('_ORD_DESC_TITLE', 'standard')
        );

        $sort_selected = array_key_exists($ref['order_by'], $sort_list) ? $ref['order_by'] : 'userid';
        $dir_selected = array_key_exists($ref['order_dir'], $dir_list) ? $ref['order_dir'] : 'asc';

        $sort_dir_dropdown = Form::getInputDropdown('', 'order_dir', 'order_dir', $dir_list, $dir_selected, '');
        $box->body .= Form::getDropdown(Lang::t('_ORDER_BY', 'standard'), 'order_by', 'order_by', $sort_list, $sort_selected, $sort_dir_dropdown);

        $box->body .= Form::getCheckbox(Lang::t('_SHOW_SUSPENDED', 'organization_chart'), 'show_suspended', 'show_suspended', 1, (bool)$ref['show_suspended']);

        cout($box->get());
    }


    //show function
    function show_report_delay($report_data = NULL, $other = '')
    {

        //$alluser, , $org_chart_subdivision, $day_from_subscription, $day_until_course_end, $date_until_course_end, $report_type, $course_selected, $user_selected, $mail)
        $jump_url = ''; //show_report

        checkPerm('view');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['send_mail_confirm']))
            $op = 'send_mail_confirm';
        elseif (isset($_POST['send_mail'])) {
            $op = 'send_mail';
        } else {
            $op = 'show_result';
        }

        switch ($op) {

            case 'send_mail_confirm':
                {
                    $subject = importVar('mail_object', false, '[' . $lang->def('_SUBJECT') . ']');//'[No subject]');
                    $body = importVar('mail_body', false, '');
                    $acl_man = new DoceboACLManager();
                    $sender = Get::sett('sender_event');
                    $mail_recipients = unserialize(urldecode(Get::req('mail_recipients', DOTY_STRING, '')));

                    // prepare intestation for email
                    $from = "From: " . $sender . $GLOBALS['mail_br'];
                    $header = "MIME-Version: 1.0" . $GLOBALS['mail_br']
                        . "Content-type: text/html; charset=" . getUnicode() . $GLOBALS['mail_br'];
                    $header .= "Return-Path: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    //$header .= "Reply-To: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Sender: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Mailer: PHP/" . phpversion() . $GLOBALS['mail_br'];

                    // send mail
                    $arr_recipients = array();
                    foreach ($mail_recipients as $recipient) {
                        $rec_data = $acl_man->getUser($recipient, false);
                        //mail($rec_data[ACL_INFO_EMAIL] , stripslashes($subject), stripslashes(nl2br($body)), $from.$header."\r\n");
                        $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
                    }
                    $mailer = DoceboMailer::getInstance();
                    $mailer->addReplyTo(Get::sett('sender_event').$GLOBALS['mail_br']);
                    $mailer->SendMail($sender, $arr_recipients, stripslashes($subject), stripslashes(nl2br($body)));

                    $result = getResultUi($lang->def('_OPERATION_SUCCESSFUL'));

                    //$this->show_report($alluser, $jump_url, $org_chart_subdivision, $day_from_subscription, $day_until_course_end, $date_until_course_end, $report_type, $course_selected, $user_selected);
                    cout($this->_get_delay_query('html', NULL, $result));
                }
                break;

            case 'send_mail':
                {
                    require_once(_base_ . '/lib/lib.form.php');
                    $mail_recipients = Get::req('mail_recipients', DOTY_MIXED, array());
                    cout(
                        ''//Form::openForm('course_selection', Util::str_replace_once('&', '&amp;', $jump_url))
                        . Form::openElementSpace()
                        . Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
                        . Form::getTextarea($lang->def('_MAIL_BODY'), 'mail_body', 'mail_body')
                        . Form::getHidden('mail_recipients', 'mail_recipients', urlencode(serialize($mail_recipients)))
                        . Form::closeElementSpace()
                        . Form::openButtonSpace()
                        . Form::getButton('send_mail_confirm', 'send_mail_confirm', $lang->def('_SEND_MAIL'))
                        . Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
                        . Form::closeButtonSpace()
                        //.Form::closeForm()
                        . '</div>', 'content');
                }
                break;

            default:
                {
                    cout($this->_get_delay_query('html', $report_data, $other));
                }

        }
    }


    //query function
    function _get_delay_query($type = 'html', $report_data = NULL, $other = '')
    {

        if ($report_data == NULL) $report_data =& $_SESSION['report_tempdata'];

        $rdata =& $report_data['rows_filter'];
        $cdata =& $report_data['columns_filter'];

        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = TRUE;
        $course_man = new Man_Course();

        $alluser = $rdata['all_users'];
        $jump_url = '';
        $org_chart_subdivision = (isset($cdata['org_chart_subdivision']) ? $cdata['org_chart_subdivision'] : false);
        $day_from_subscription = ($cdata['day_from_subscription'] != "" ? $cdata['day_from_subscription'] : false);
        $day_until_course_end = ($cdata['day_until_course_end'] != "" ? $cdata['day_until_course_end'] : false);
        $date_until_course_end = ($cdata['date_until_course_end'] != "" ? $cdata['date_until_course_end'] : false);
        $report_type_completed = (isset($cdata['report_type_completed']) ? $cdata['report_type_completed'] : false);
        $report_type_started = (isset($cdata['report_type_started']) ? $cdata['report_type_started'] : false);
        $course_selected = $cdata['selected_courses'];
        $all_courses = $cdata['all_courses'];

        $order_by = (isset($cdata['order_by']) ? $cdata['order_by'] : 'userid');
        $order_dir = (isset($cdata['order_dir']) ? $cdata['order_dir'] : 'asc');
        $show_suspended = (isset($cdata['show_suspended']) ? (bool)$cdata['show_suspended'] : false);

        if (!$alluser)
            $user_selected =& $acl_man->getAllUsersFromIdst($rdata['users']);
        else
            $user_selected =& $acl_man->getAllUsersIdst();

        //apply sub admin filters, if needed
        if (Docebo::user()->getUserLevelId() != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            //filter users
            $alluser = false;
            require_once(_base_ . '/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromSelection($admin_users);
            $user_selected = array_intersect($user_selected, $admin_users);
            unset($admin_users);
        }

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        $lang_u =& DoceboLanguage::CreateInstance('stats', 'lms');

        if (empty($user_selected)) {
            return $lang->def('_NULL_SELECTION');
        }

        if (empty($course_selected) && !$all_courses) {
            return $lang->def('_NULL_COURSE_SELECTION');
        }


        if (1 == 1)//($org_chart_subdivision === 0)
        {
            $date_now = Format::date(date("Y-m-d H:i:s"));

            //set query suspended users condition
            $query_show_suspended = "AND u.valid = 1"; //default condition
            switch ($show_suspended) {
                case "all":
                    $query_show_suspended = "";
                    break;
                case "suspended_only":
                    $query_show_suspended = " AND u.valid = 0 ";
                    break;
                case "active_only":
                    $query_show_suspended = " AND u.valid = 1 ";
                    break;
            }

            //set query order by param
            $_dir = "ASC";
            switch ($order_dir) {
                case "desc":
                    $_dir = "DESC";
                    break;
            }
            $query_order_by = "u.lastname, u.firstname, u.userid";
            switch ($order_by) {
                case 'firstname':
                    $query_order_by = "u.firstname " . $_dir . ", u.userid " . $_dir;
                    break;
                case 'lastname':
                    $query_order_by = "u.lastname " . $_dir . ", u.userid " . $_dir;
                    break;
                case 'email':
                    $query_order_by = "u.email " . $_dir . ", u.userid " . $_dir;
                    break;
                case 'status':
                    $query_order_by = "cu.status " . $_dir . ", u.userid " . $_dir . ", u.lastname " . $_dir;
                    break;
                //case 'level': $query_order_by = "cu.level ".$_dir.", u.userid ".$_dir.", c.code ".$_dir.""; break;
                case 'date_subscription':
                    $query_order_by = "cu.date_inscr " . $_dir . ", u.userid " . $_dir . ", u.lastname " . $_dir;
                    break;
                case 'date_first_access':
                    $query_order_by = "cu.date_first_access " . $_dir . ", u.userid " . $_dir . ", u.lastname " . $_dir;
                    break;
                case 'date_last_access':
                    $query_order_by = "cu.date_last_access " . $_dir . ", u.userid " . $_dir . ", u.lastname " . $_dir;
                    break;
                case 'date_complete':
                    $query_order_by = "cu.date_complete " . $_dir . ", u.userid " . $_dir . ", u.lastname " . $_dir;
                    break;
            }

            $query_course_user = "
                SELECT cu.idUser, cu.idCourse, cu.edition_id, cu.date_inscr, cu.date_first_access,
                cu.date_complete, cu.status, cu.level,
                u.userid, u.firstname, u.lastname, u.email, u.valid
                FROM " . $GLOBALS['prefix_lms'] . "_courseuser AS cu " .
                " JOIN " . $GLOBALS['prefix_fw'] . "_user as u ON cu.idUser = u.idst
                WHERE cu.idCourse > 0 " . $query_show_suspended .
                ($alluser ? "" : " AND cu.idUser IN ( " . implode(',', $user_selected) . " ) ") .
                ($all_courses ? '' : " AND cu.idCourse IN (" . implode(',', $course_selected) . ")")
                . " ORDER BY " . $query_order_by;

            $re_course_user = sql_query($query_course_user);

            $element_to_print = array();
            $courses_codes = array();

            while (list($id_u, $id_c, $id_e, $date_inscr, $date_first_access, $date_complete, $status, $level,
                $date_last_access, $u_userid, $u_firstname, $u_lastname, $u_email, $u_valid) = sql_fetch_row($re_course_user)) {

                if ($level == '3') { //$report_type === 'course_started' && $level == '3') {

                    $user_check = false;
                    $now_timestamp = mktime('0', '0', '0', date('m'), date('d'), date('Y'));

                    //check the condition on status (course started and/or completed)
                    $status_condition = $status != _CUS_END; //&& $status != _CUS_SUSPEND;
                    if ($report_type_completed && !$report_type_started) $status_condition = $status_condition && ($status == _CUS_BEGIN);
                    if ($report_type_started && !$report_type_completed) $status_condition = $status_condition && ($status != _CUS_BEGIN);

                    if ($day_from_subscription) {

                        if ($status_condition) {
                            $user_timestamp = mktime('0', '0', '0', $date_inscr{5} . $date_inscr{6}, ($date_inscr{8} . $date_inscr{9}) + $day_from_subscription, $date_inscr{0} . $date_inscr{1} . $date_inscr{2} . $date_inscr{3});
                            if ($user_timestamp < $now_timestamp) $user_check = true;
                        }
                    }


                    if ($day_until_course_end) {

                        if ($status_condition) {

                            if ($id_e > 0) {

                                $query = "SELECT date_end"
                                    . " FROM " . $GLOBALS['prefix_lms'] . "_course_edition"
                                    . " WHERE idCourseEdition = '" . $id_e . "'";
                                list ($date_end) = sql_fetch_row(sql_query($query));
                                $user_timestamp = mktime('0', '0', '0', $date_end{5} . $date_end{6}, ($date_end{8} . $date_end{9}) - $day_until_course_end, $date_end{0} . $date_end{1} . $date_end{2} . $date_end{3});
                                if ($user_timestamp < $now_timestamp) $user_check = true;

                            } else {

                                $query = "SELECT date_end"
                                    . " FROM " . $GLOBALS['prefix_lms'] . "_course"
                                    . " WHERE idCourse = '" . $id_c . "'";
                                list ($date_end) = sql_fetch_row(sql_query($query));
                                $user_timestamp = mktime('0', '0', '0', $date_end{5} . $date_end{6}, ($date_end{8} . $date_end{9}) - $day_until_course_end, $date_end{0} . $date_end{1} . $date_end{2} . $date_end{3});
                                if ($user_timestamp < $now_timestamp) $user_check = true;

                            }

                        }

                    }


                    if ($date_until_course_end) {

                        if ($status_condition) {

                            if ($id_e > 0) {

                                $query = "SELECT COUNT(*)"
                                    . " FROM " . $GLOBALS['prefix_lms'] . "_course_edition"
                                    . " WHERE idCourseEdition = '" . $id_e . "'"
                                    . " AND date_end < '" . Format::dateDb($date_until_course_end, 'date') . "'";
                                list ($control) = sql_fetch_row(sql_query($query));
                                if ($control) $user_check = true;

                            } else {
                                $query = "SELECT COUNT(*)"
                                    . " FROM " . $GLOBALS['prefix_lms'] . "_course"
                                    . " WHERE idCourse = '" . $id_c . "'"
                                    . " AND date_end < '" . Format::dateDb($date_until_course_end, 'date') . "'";
                                list ($control) = sql_fetch_row(sql_query($query));
                                if ($control) $user_check = true;

                            }

                        }

                    }


                    if (!$date_until_course_end && !$day_from_subscription && !$date_until_course_end)
                        if ($status_condition) {
                            $user_check = true;
                        }

                    if ($user_check) {
                        $course_info = $course_man->getCourseInfo($id_c);
                        //$user_detail = $acl_man->getUser($id_u, false);

                        $element_to_print[$id_c]['name'] = $course_info['name'];
                        $element_to_print[$id_c]['code'] = $course_info['code'];
                        $element_to_print[$id_c]['data'][] = array(
                            'idUser' => $id_u,
                            'idCourse' => $id_c,
                            'idCourseEdition' => $id_e,
                            'status' => $status,
                            'level' => $level,
                            'userid' => Docebo::aclm()->relativeId($u_userid),
                            'firstname' => $u_firstname,
                            'lastname' => $u_lastname,
                            'mail' => $u_email,
                            'suspended' => $u_valid <= 0,
                            'date_subscription' => $date_inscr,
                            'date_first_access' => $date_first_access,
                            'date_completed' => $date_complete,
                            'date_last_access' => $date_last_access
                        );

                    }

                }

            }

            //backward compatibility
            $showed_columns = array();
            foreach ($cdata['showed_columns'] as $_column_key) {
                $showed_columns[] = $this->_check_delay_column($_column_key);
            }

            //print course table
            $this->_printTable_delay($type, $element_to_print, $showed_columns);
            if ($this->use_mail) {
                $this->_loadEmailActions();
            }
        }
    }


    function _printTable_delay($type, &$element_to_print, $showed_cols = array())
    {
        if (!$type) $type = 'html';

        if (empty($element_to_print)) {
            cout($this->lang->def('_NO_USER_FOUND'), 'content');
        } else {
            require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');
            $buffer = new ReportTablePrinter($type);

            //ksort($element_to_print);

            foreach ($element_to_print as $id_course => $info) {
                $course_name = $info['name'];

                $header = array();
                foreach ($this->delay_columns as $delay_row) {
                    $index = $this->_check_delay_column($delay_row['key']); //backward compatibility
                    if (($delay_row['select'] && in_array($index, $showed_cols)) || !$delay_row['select']) {
                        $header[] = $delay_row['label'];
                    }
                }
                if ($this->use_mail) $header[] = array('style' => 'img-cell', 'value' => $this->_loadEmailIcon());

                $title = Lang::t('_COURSE', 'standard') . ': "' . $course_name . '" (' . $info['code'] . ')';

                $buffer->openTable($title, $title);

                $buffer->openHeader();
                $buffer->addHeader($header);
                $buffer->closeHeader();

                $buffer->openBody();

                $user_levels_trans = false;

                $i = 0;
                foreach ($info['data'] as $user_info) {

                    $line = array();

                    foreach ($this->delay_columns as $delay_row) {
                        $index = $this->_check_delay_column($delay_row['key']); //backward compatibility
                        if (($delay_row['select'] && in_array($index, $showed_cols)) || !$delay_row['select']) {
                            switch ($index) {
                                case 'level':
                                    {
                                        if ($user_levels_trans === false) {
                                            require_once(_lms_ . '/lib/lib.levels.php');
                                            $clevels = new CourseLevel();
                                            $user_levels_trans = $clevels->getLevels();
                                            unset($clevels);
                                        }
                                        $line[] = array('style' => 'align-center', 'value' => $user_levels_trans[$user_info['level']]);
                                    }
                                    break;
                                case 'status':
                                    {
                                        $line[] = array('style' => 'align-center', 'value' => $this->status_u[$user_info['status']]);
                                    }
                                    break;
                                case 'date_subscription':
                                case 'date_first_access':
                                case 'date_last_access':
                                case 'date_complete':
                                    {
                                        if ($user_info[$index] == '0000-00-00 00:00:00' || $user_info[$index] == '') {
                                            $line[] = '';
                                        } else {
                                            $line[] = array('style' => 'align-center', 'value' => Format::date($user_info[$index], 'datetime'));
                                        }
                                    }
                                    break;
                                case 'email':
                                    $line[] = trim($user_info['mail']);
                                    break;
                                default:
                                    $line[] = $user_info[$index];
                                    break;
                            }
                        }
                    }


                    if ($this->use_mail) {
                        $line[] = array(
                            'style' => 'img-cell',
                            'value' => '<div class="align_center">' . Form::getInputCheckbox('mail_' . $user_info['idUser'], 'mail_recipients[]', $user_info['idUser'], isset($_POST['select_all']), '') . '</div>'
                        );
                    }

                    $buffer->addLine($line);

                }
                $buffer->closeBody();
                $buffer->closeTable();
                $buffer->addBreak();

            }

            cout($buffer->get(), 'content');
        }
    }

    function getHTML($cat = false, $report_data = NULL)
    {
        $this->use_mail = false;
        return $this->_get_data('html', $cat, $report_data);
    }

    function getCSV($cat = false, $report_data = NULL)
    {
        $this->use_mail = false;
        return $this->_get_data('csv', $cat, $report_data);
    }

    function getXLS($cat = false, $report_data = NULL)
    {
        $this->use_mail = false;
        return $this->_get_data('xls', $cat, $report_data);
    }

    //learning objects report functions

    function get_LO_filter()
    {
        //addCss('style_filterbox');

        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_ . '/lib/lib.form.php');
        require_once(_lms_ . '/lib/lib.course.php');

        $ref =& $_SESSION['report_tempdata']['columns_filter'];

        YuiLib::load();
        Util::get_js(Get::rel_path('lms') . '/admin/modules/report/courses_filter.js', true, true);

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            //go back at the previous step
            Util::jump_to($back_url);
        }

        //set $_POST data in $_SESSION['report_tempdata']
        $selector = new Selector_Course();
        if (isset($_POST['update_tempdata'])) {
            $selector->parseForState($_POST);
            $temp = array(
                //'org_chart_subdivision'   => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                'all_courses' => ($_POST['all_courses'] == 1 ? true : false),
                'selected_courses' => $selector->getSelection(),
                'lo_types' => (isset($_POST['lo_types']) ? $_POST['lo_types'] : array()),
                'lo_milestones' => (isset($_POST['lo_milestones']) ? $_POST['lo_milestones'] : array()),
                'showed_columns' => (isset($_POST['cols']) ? $_POST['cols'] : array()),
                'custom_fields' => array(),
                'order_by' => Get::req('order_by', DOTY_STRING, 'userid'),
                'order_dir' => Get::req('order_dir', DOTY_STRING, 'asc'),
                'show_suspended' => Get::req('show_suspended', DOTY_INT, 0) > 0
            );

            foreach ($ref['custom_fields'] as $val) {
                $temp['custom_fields'][] = array(
                    'id' => $val['id'],
                    'label' => $val['label'],
                    'selected' => (isset($_POST['custom'][$val['id']]) ? true : false)
                );
            }

            $_SESSION['report_tempdata']['columns_filter'] = $temp;
        } else {
            //first loading of this page -> prepare $_SESSION data structure
            //if (isset($_SESSION['report_update']) /* && is equal to id_report */) break;
            //get users' custom fields
            require_once(_adm_ . '/lib/lib.field.php');
            $fman = new FieldList();
            $fields = $fman->getFlatAllFields();
            $custom = array();
            foreach ($fields as $key => $val) {
                $custom[] = array('id' => $key, 'label' => $val, 'selected' => false);
            }

            if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
                $_SESSION['report_tempdata']['columns_filter'] = array(
                    //'org_chart_subdivision'   => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                    'all_courses' => false,
                    'selected_courses' => $selector->getSelection(),
                    'lo_types' => array(),
                    'lo_milestones' => array(),
                    'showed_columns' => array(),
                    'custom_fields' => $custom,
                    'order_by' => 'userid',
                    'order_dir' => 'asc',
                    'show_suspended' => 'show_suspended'
                );
            }
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
            Util::jump_to($temp_url);
        }

        cout(Form::getHidden('update_tempdata', 'update_tempdata', 1), 'content');

        $lang = $this->lang;

        //box for direct course selection
        $selection =& $ref['selected_courses'];
        $selector->parseForState($_POST);
        $selector->resetSelection($selection);
        $temp = count($selection);

        $box = new ReportBox('course_selector');
        $box->title = Lang::t('_REPORT_COURSE_SELECTION', 'report');
        $box->description = false;
        $box->body .= '<div class="fc_filter_line filter_corr">';
        $box->body .= '<input id="all_courses" name="all_courses" type="radio" value="1" ' . ($ref['all_courses'] ? 'checked="checked"' : '') . ' />';
        $box->body .= ' <label for="all_courses">' . $lang->def('_ALL_COURSES') . '</label>';
        $box->body .= ' <input id="sel_courses" name="all_courses" type="radio" value="0" ' . ($ref['all_courses'] ? '' : 'checked="checked"') . ' />';
        $box->body .= ' <label for="sel_courses">' . $lang->def('_SEL_COURSES') . '</label>';
        $box->body .= '</div>';

        $box->body .= '<div id="selector_container"' . ($ref['all_courses'] ? ' style="display:none"' : '') . '>';
        //$box->body .= Form::openElementSpace();
        $box->body .= $selector->loadCourseSelector(true);
        //$box->body .= Form::closeElementSpace();
        $box->body .= '<br /></div>';
        $box->footer = $lang->def('_CURRENT_SELECTION') . ':&nbsp;<span id="csel_foot">' . ($ref['all_courses'] ? Lang::t('_ALL', 'standard') : ($temp != '' ? $temp : '0')) . '</span>';
        //.'</div>';
        cout($box->get(), 'content');


        cout(
            '<script type="text/javascript">courses_count=' . ($temp == '' ? '0' : $temp) . ';' .
            'courses_all="' . Lang::t('_ALL', 'standard') . '";' . "\n" .
            'YAHOO.util.Event.addListener(window, "load", courses_selector_init);</script>', 'page_head');


        $box = new ReportBox('lo_selection');
        $box->title = $lang->def('_SELECT_LO_OPTIONS');

        //LO columns selection
        $lo_trans = $this->getLOTypesTranslations();
        $box->body .= Form::getOpenFieldset(Lang::t('_RU_LO_TYPES', 'report'), 'lotypes_fieldset');
        $res = sql_query("SELECT * FROM %lms_lo_types");
        while ($row = sql_fetch_assoc($res)) {
            $trans = isset($lo_trans[$row['objectType']]) ? $lo_trans[$row['objectType']] : "";
            $box->body .= Form::getCheckBox($trans, 'lo_type_' . $row['objectType'], 'lo_types[' . $row['objectType'] . ']', $row['objectType'], (in_array($row['objectType'], $ref['lo_types']) ? true : false));
        }
        $box->body .= Form::getCloseFieldset();

        $box->body .= Form::getOpenFieldset($lang->def('_RU_LO_MILESTONES'), 'lomilestones_fieldset');
        $box->body .= Form::getCheckBox($lang->def('_NONE'), 'lo_milestone_0', 'lo_milestones[]', _MILESTONE_NONE, (in_array(_MILESTONE_NONE, $ref['lo_milestones']) ? true : false));
        $box->body .= Form::getCheckBox($lang->def('_START'), 'lo_milestone_1', 'lo_milestones[]', _MILESTONE_START, (in_array(_MILESTONE_START, $ref['lo_milestones']) ? true : false));;
        $box->body .= Form::getCheckBox($lang->def('_END'), 'lo_milestone_2', 'lo_milestones[]', _MILESTONE_END, (in_array(_MILESTONE_END, $ref['lo_milestones']) ? true : false));;
        $box->body .= Form::getCloseFieldset();

        cout($box->get(), 'content');


        function is_showed($which)
        {
            if (isset($_SESSION['report_tempdata']['columns_filter'])) {
                return in_array($which, $_SESSION['report_tempdata']['columns_filter']['showed_columns']);
            } else return false;
        }

        ;

        //box for columns selection
        $arr_fieldset = array(
            'user' => '',
            'course' => '',
            'lo' => ''
        );

        $box = new ReportBox('columns_selection');
        $box->title = $lang->def('_SELECT_THE_DATA_COL_NEEDED');
        $box->description = false;


        //prepare fieldsets
        foreach ($this->LO_columns as $val) {
            if ($val['select']) {
                $line = Form::getCheckBox($val['label'], 'col_sel_' . $val['key'], 'cols[]', $val['key'], is_showed($val['key']));
                switch ($val['group']) {
                    case 'user':
                        $arr_fieldset['user'] .= $line;
                        break;
                    case 'course':
                        $arr_fieldset['course'] .= $line;
                        break;
                    case 'lo':
                        $arr_fieldset['lo'] .= $line;
                        break;
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    //custom fields
                    if (count($ref['custom_fields']) > 0) {
                        foreach ($ref['custom_fields'] as $key => $val) {
                            $arr_fieldset['user'] .= Form::getCheckBox($val['label'], 'col_custom_' . $val['id'], 'custom[' . $val['id'] . ']', $val['id'], $val['selected']);
                        }
                    }
                }
            }
        }

        //print fieldsets
        foreach ($arr_fieldset as $fid => $fieldset) {
            $ftitle = '';
            switch ($fid) {
                case 'user':
                    {
                        $ftitle = Lang::t('_USER_CUSTOM_FIELDS', 'report');
                    }
                    break;
                case 'course':
                    {
                        $ftitle = Lang::t('_COURSE_FIELDS', 'report');
                    }
                    break;
                case 'lo':
                    {
                        $ftitle = Lang::t('_LEARNING_OBJECTS', 'standard');
                    }
                    break;
            }
            $box->body .= Form::getOpenFieldset($ftitle, 'fieldset_' . $fid . '_fields');
            $box->body .= $fieldset;
            $box->body .= Form::getCloseFieldset();
        }

        cout($box->get(), 'content');


        //other options
        $box = new ReportBox('other_options');
        $box->title = Lang::t('_OTHER_OPTION', 'course');
        $box->description = false;

        $sort_list = array(
            'userid' => Lang::t('_USERID', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'course_code' => Lang::t('_COURSE_CODE', 'standard'),
            'course_name' => Lang::t('_COURSE_NAME', 'standard'),
            'object_title' => Lang::t('_LEARNING_OBJECTS', 'standard'),
            'object_type' => Lang::t('_RU_LO_TYPES', 'report'),
            'first_attempt' => Lang::t('_LO_COL_FIRSTATT', 'report'),
            'last_attempt' => Lang::t('_LO_COL_LASTATT', 'report')
        );
        $dir_list = array(
            'asc' => Lang::t('_ORD_ASC_TITLE', 'standard'),
            'desc' => Lang::t('_ORD_DESC_TITLE', 'standard')
        );

        $sort_selected = array_key_exists($ref['order_by'], $sort_list) ? $ref['order_by'] : 'userid';
        $dir_selected = array_key_exists($ref['order_dir'], $dir_list) ? $ref['order_dir'] : 'asc';

        $sort_dir_dropdown = Form::getInputDropdown('', 'order_dir', 'order_dir', $dir_list, $dir_selected, '');
        $box->body .= Form::getDropdown(Lang::t('_ORDER_BY', 'standard'), 'order_by', 'order_by', $sort_list, $sort_selected, $sort_dir_dropdown);

        $box->body .= Form::getCheckbox(Lang::t('_SHOW_SUSPENDED', 'organization_chart'), 'show_suspended', 'show_suspended', 1, (bool)$ref['show_suspended']);

        cout($box->get(), 'content');
    }


    function show_report_LO($report_data = NULL, $other = '')
    {
        $jump_url = ''; //show_report

        checkPerm('view');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['send_mail_confirm']))
            $op = 'send_mail_confirm';
        elseif (isset($_POST['send_mail'])) {
            $op = 'send_mail';
        } else {
            $op = 'show_result';
        }

        switch ($op) {

            case 'send_mail_confirm':
                {
                    $subject = importVar('mail_object', false, '[' . $lang->def('_SUBJECT') . ']');//'[No subject]');
                    $body = importVar('mail_body', false, '');
                    $acl_man = new DoceboACLManager();
                    $sender = Get::sett('sender_event');

                    $mail_recipients = unserialize(urldecode(Get::req('mail_recipients', DOTY_STRING, '')));

                    // prepare intestation for email
                    $from = "From: " . $sender . $GLOBALS['mail_br'];
                    $header = "MIME-Version: 1.0" . $GLOBALS['mail_br']
                        . "Content-type: text/html; charset=" . getUnicode() . $GLOBALS['mail_br'];
                    $header .= "Return-Path: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    //$header .= "Reply-To: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Sender: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Mailer: PHP/" . phpversion() . $GLOBALS['mail_br'];

                    // send mail
                    $arr_recipients = array();
                    foreach ($mail_recipients as $recipient) {
                        $rec_data = $acl_man->getUser($recipient, false);
                        //mail($rec_data[ACL_INFO_EMAIL] , stripslashes($subject), stripslashes(nl2br($body)), $from.$header."\r\n");
                        $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
                    }
                    $mailer = DoceboMailer::getInstance();
                    $mailer->addReplyTo(Get::sett('sender_event').$GLOBALS['mail_br']);
                    $mailer->SendMail($sender, $arr_recipients, stripslashes($subject), stripslashes(nl2br($body)));

                    $result = getResultUi($lang->def('_OPERATION_SUCCESSFUL'));

                    cout($this->_get_LO_query('html', NULL, $result));
                }
                break;

            case 'send_mail':
                {
                    require_once(_base_ . '/lib/lib.form.php');
                    $mail_recipients = Get::req('mail_recipients', DOTY_MIXED, array());
                    cout(
                        ''//Form::openForm('course_selection', Util::str_replace_once('&', '&amp;', $jump_url))
                        . Form::openElementSpace()
                        . Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
                        . Form::getTextarea($lang->def('_MAIL_BODY'), 'mail_body', 'mail_body')
                        . Form::getHidden('mail_recipients', 'mail_recipients', urlencode(serialize($mail_recipients)))
                        . Form::closeElementSpace()
                        . Form::openButtonSpace()
                        . Form::getButton('send_mail_confirm', 'send_mail_confirm', $lang->def('_SEND_MAIL'))
                        . Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
                        . Form::closeButtonSpace()
                        //.Form::closeForm()
                        . '</div>', 'content');
                }
                break;

            default:
                {
                    cout($this->_get_LO_query('html', $report_data, $other));
                }

        }
    }


    function _convertDate($date)
    {
        $output = '';
        if ($date != '0000-00-00 00:00:00')
            $output = Format::date($date);
        return $output;
    }

    function _get_LO_query($type = 'html', $report_data = NULL, $other = '')
    {

        checkPerm('view');
        $view_all_perm = checkPerm('view_all', true);

        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');

        function is_showed($which, $data)
        {
            if (isset($data['columns_filter'])) {
                return in_array($which, $data['columns_filter']['showed_columns']);
            } else return false;
        }

        ;

        if ($report_data == NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;
        $_rows =& $ref['rows_filter'];
        $_cols =& $ref['columns_filter'];
        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = TRUE;

        $all_users = &$_rows['all_users']; //select root & descendants from orgchart instead
        $all_courses = &$_cols['all_courses'];
        $courses = &$_cols['selected_courses'];
        $types = &$_cols['lo_types'];
        $milestones = &$_cols['lo_milestones'];
        $showed = &$_cols['showed_columns'];
        $customcols = &$_cols['custom_fields'];
        $order_by = isset($_cols['order_by']) ? $_cols['order_by'] : 'userid';
        $order_dir = isset($_cols['order_dir']) ? $_cols['order_dir'] : 'asc';
        $suspended = isset($_cols['show_suspended']) ? (bool)$_cols['show_suspended'] : false;
        if ($all_users) {
            $users =& $acl_man->getAllUsersIdst();
        } else {
            $users =& $acl_man->getAllUsersFromSelection($_rows['users']);
        }

        //apply sub admin filters, if needed
        if (!$view_all_perm && Docebo::user()->getUserLevelId() == '/framework/level/admin') {
            //filter users
            $all_users = false;
            require_once(_base_ . '/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromSelection($admin_users);
            $users = array_intersect($users, $admin_users);
            unset($admin_users);
        }

        $temptypes = array();
        foreach ($types as $val) {
            $temptypes[] = "'" . $val . "'";
        }

        $tempmilestones = array();
        foreach ($milestones as $val) {
            switch ($val) {
                case _MILESTONE_NONE:
                    {
                        $tempmilestones[] = "''";
                        $tempmilestones[] = "'-'";
                    }
                    break;
                case _MILESTONE_START:
                    {
                        $tempmilestones[] = "'start'";
                    }
                    break;
                case _MILESTONE_END:
                    {
                        $tempmilestones[] = "'end'";
                    }
                    break;
            }
        }

        $colspans = array('user' => 0, 'course' => 0, 'lo' => 0);
        foreach ($this->LO_columns as $val) {
            if ($val['select']) {
                if (in_array($val['key'], $showed)) {
                    switch ($val['key']) {
                        case 'user_name':
                            $colspans[$val['group']] += 2;
                            break;
                        default:
                            $colspans[$val['group']]++;
                            break;
                    }
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    ;//do nothing ...
                } else {
                    $colspans[$val['group']]++;
                }
            }
        }

        //custom user fields
        require_once(_adm_ . '/lib/lib.field.php');
        $fman = new FieldList();
        $field_values = array();
        $temp_head2 = array();
        foreach ($customcols as $val) {
            if ($val['selected']) {
                $colspans['user']++;
                $temp_head2[] = $val['label'];
                $field_values[$val['id']] = $fman->fieldValue((int)$val['id'], $users);
            }
        }

        $lang = $this->lang;

        $head1 = array();
        $head1[] = array('colspan' => $colspans['user'], 'value' => $lang->def('_USER')); //_TH_USER
        $head1[] = array('colspan' => $colspans['course'], 'value' => $lang->def('_COURSE')); //_TH_COURSE
        $head1[] = array('colspan' => $colspans['lo'], 'value' => $lang->def('_LEARNING_OBJECTS'));
        if ($this->use_mail)
            $head1[] = array(
                'style' => 'img-cell',
                'value' => $this->_loadEmailIcon()
            );

        $head2 = array();
        foreach ($this->LO_columns as $val) {
            if ($val['select']) {
                if (in_array($val['key'], $showed)) {
                    switch ($val['key']) { //manages exceptions through switch
                        case 'user_name':
                            {
                                $head2[] = Lang::t('_LASTNAME', 'standard');
                                $head2[] = Lang::t('_FIRSTNAME', 'standard');
                            }
                            break;
                        default:
                            $head2[] = $val['label'];
                            break;
                    }
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    foreach ($temp_head2 as $tval) {
                        $head2[] = $tval;
                    }
                } else {
                    $head2[] = $val['label']; //label
                }
            }
        }


        if ($this->use_mail) $head2[] = '';//'<img src="'.getPathImage().'standard/email.gif"/>';//''; //header for checkbox

        $buffer = new ReportTablePrinter($type);
        $buffer->openTable('', '');

        $buffer->openHeader();
        $buffer->addHeader($head1);
        $buffer->addHeader($head2);
        $buffer->closeHeader();

        //retrieve LOs from courses

        $score_arr = array(
            'test' => array(),
            'scorm' => array()
        );

        $total_time_arr = array(
            'scorm' => array()
        );

        //retrieve test score
        $query = "SELECT t1.idOrg, t2.idUser, t1.idCourse, t2.score, t2.bonus_score, t2.score_status "
            . " FROM %lms_organization AS t1 "
            . " JOIN %lms_testtrack AS t2 ON ( t1.objectType = 'test' "
            . " AND t1.idOrg = t2.idReference ), %adm_user as t3 "
            . "WHERE t3.idst=t2.idUser " . ($suspended ? "" : "AND t3.valid=1 ")
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? " AND t1.milestone IN (" . implode(',', $tempmilestones) . ") " : "");
        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $score_arr['test'][$row['idOrg']][$row['idUser']] = $row['score'] + $row['bonus_score'];
        }

        //retrievescorm score
        $query = "SELECT t1.idOrg, t2.idUser, t1.idCourse, t2.score_raw, t2.score_min, t2.score_max, "
            . " SEC_TO_TIME(SUM(TIME_TO_SEC(th.session_time))) AS total_time "
            . " FROM %lms_organization AS t1 "
            . " JOIN %lms_scorm_tracking AS t2 ON ( t1.objectType = 'scormorg' "
            . " AND t1.idOrg = t2.idReference )"
            . " JOIN %adm_user as t3 "
            . "     ON t3.idst=t2.idUser " . ($suspended ? "" : "AND t3.valid=1 ")
            . " LEFT JOIN %lms_scorm_tracking_history AS th "
            . "     ON th.idscorm_tracking = t2.idscorm_tracking "
            . "WHERE 1 "
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? " AND t1.milestone IN (" . implode(',', $tempmilestones) . ") " : "")
            . " GROUP BY t1.idOrg, t2.idUser, t1.idCourse, t2.score_raw, t2.score_min, t2.score_max, t2.total_time";
        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $score_arr['scorm'][$row['idOrg']][$row['idUser']] = $row['score_raw'];
            $total_time_arr['scorm'][$row['idOrg']][$row['idUser']] = $row['total_time'];
        }

        $buffer->openBody();

        //retrieve LO types translations
        $LO_types = $this->getLOTypesTranslations();

        //retrieve LO's data
        $_dir = "ASC";
        switch ($order_dir) {
            case "desc":
                $_dir = "DESC";
                break;
        }
        $query_order_by = "t0.userid " . $_dir . ", t1.title " . $_dir;
        switch ($order_by) {
            case 'firstname':
                $query_order_by = "t0.firstname " . $_dir . ", t0.lastname, " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'lastname':
                $query_order_by = "t0.lastname " . $_dir . ", t0.firstname, " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'email':
                $query_order_by = "t0.email " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'course_code':
                $query_order_by = "t3.code " . $_dir . ", t3.name " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'course_name':
                $query_order_by = "t3.name " . $_dir . ", t3.code " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'object_title':
                $query_order_by = "t1.title " . $_dir . ", t0.userid " . $_dir;
                break;
            case 'object_type':
                $query_order_by = "t1.objectType " . $_dir . ", t1.title " . $_dir . ", t0.userid " . $_dir;
                break;
            case 'first_attempt':
                $query_order_by = "t2.firstAttempt";
                break;
            case 'last_attempt':
                $query_order_by = "t2.dateAttempt";
                break;
        }

        $query = "SELECT t0.idst as user_st, t0.userid, t0.firstname, t0.lastname, t0.email, t0.valid, "
            . " t1.idOrg, t1.objectType, t1.title, t1.idResource, t1.milestone, "
            . " t3.idCourse, t3.code, t3.name, t3.status as course_status, "
            . " t2.firstAttempt, t2.dateAttempt, t2.status "
            . " FROM %adm_user as t0, "
            . " %lms_organization as t1, "
            . " %lms_commontrack as t2, "
            . " %lms_course as t3 "
            . " WHERE "
            . " t0.idst=t2.idUser AND t1.idOrg=t2.idReference AND t1.idCourse=t3.idCourse "
            . ($suspended ? "" : "AND t0.valid=1 ")
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (count($temptypes) > 0 ? " AND t2.objectType IN (" . implode(',', $temptypes) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? "AND t1.milestone IN (" . implode(',', $tempmilestones) . ")" : "")
            . " ORDER BY " . $query_order_by;
        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {

            $temp = array();
            foreach ($this->LO_columns as $val)
                switch ($val['key']) {
                    case 'userid':
                        $temp[] = $acl_man->relativeId($row['userid']);
                        break;
                    case 'user_name':
                        {
                            if (in_array($val['key'], $showed)) {
                                $temp[] = $row['lastname'];
                                $temp[] = $row['firstname'];
                            }
                        }
                        break;
                    case 'email':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['email'];
                        }
                        break;
                    case 'suspended':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = ($row['valid'] > 0 ? Lang::t('_NO', 'standard') : Lang::t('_YES', 'standard'));
                        }
                        break;
                    case '_CUSTOM_FIELDS_':
                        {
                            foreach ($customcols as $field) {
                                if ($field['selected']) {
                                    if (isset($field_values[$field['id']][$row['user_st']])) {
                                        $temp[] = $field_values[$field['id']][$row['user_st']];
                                    } else {
                                        $temp[] = '';
                                    }
                                }
                            }
                        }
                        break;
                    case 'course_code':
                        $temp[] = $row['code'];
                        break;
                    case 'course_name':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['name'];
                        }
                        break;
                    case 'course_status':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $this->_convertStatusCourse($row['course_status']);
                        }
                        break;
                    case 'lo_type':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = isset($LO_types[$row['objectType']]) ? $LO_types[$row['objectType']] : "";
                        }
                        break;
                    case 'lo_name':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['title'];
                        }
                        break;
                    case 'lo_milestone':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['milestone'];
                        }
                        break;
                    case 'firstAttempt':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $this->_convertDate($row['firstAttempt']);
                        }
                        break;
                    case 'lastAttempt':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $this->_convertDate($row['dateAttempt']);
                        }
                        break;
                    case 'lo_status':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = Lang::t($row['status'], 'storage');
                        }
                        break;
                    case 'lo_score':
                        {
                            if (in_array($val['key'], $showed)) {
                                switch ($row['objectType']) {

                                    case 'test':
                                        {
                                            if (isset($score_arr['test'][$row['idOrg']][$row['user_st']]))
                                                $score_val = $score_arr['test'][$row['idOrg']][$row['user_st']];
                                            else
                                                $score_val = '0';
                                            $temp[] = $score_val;
                                        }
                                        break;

                                    case 'scormorg' :
                                        {
                                            if (isset($score_arr['scorm'][$row['idOrg']][$row['user_st']]))
                                                $score_val = $score_arr['scorm'][$row['idOrg']][$row['user_st']];
                                            else
                                                $score_val = '0';
                                            $temp[] = $score_val;
                                        }
                                        break;

                                    default:
                                        {
                                            $temp[] = '';
                                        }
                                        break;
                                }
                            }
                        }
                        break;
                    case 'lo_total_time':
                        {
                            $temp[] = $total_time_arr['scorm'][$row['idOrg']][$row['user_st']];
                        }
                        break;
                    default:
                        {
                            if (in_array($val['key'], $showed)) $temp[] = '';
                        }
                        break;
                } //end switch - end for

            if ($this->use_mail) {
                $temp[] =//'<input type="checkbox" value="'.$row['idst'].'"/>'; //header for checkbox
                    '<div class="align_center">' . Form::getInputCheckbox('mail_' . $row['user_st'], 'mail_recipients[]', $row['user_st'], isset($_POST['select_all']), '') . '</div>';

            }

            $buffer->addLine($temp);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        $output = $buffer->get();

        if ($this->use_mail) {
            $output .= $this->_loadEmailActions();
        }

        return $output;
    }


    //----------------------------------------------------------------------------
    //---- communications report part --------------------------------------------
    //----------------------------------------------------------------------------

    function show_report_communications($data = NULL, $other = '')
    {
        if ($data === NULL)
            cout($this->_get_communications_query());
        else
            cout($this->_get_communications_query('html', $data, $other));
    }

    function get_communications_filter()
    {
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
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
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

        require_once(_lms_ . '/lib/lib.report.php'); //the comm. table function
        $box->body .= Form::getCheckbox(Lang::t('_ALL', 'report'), 'all_communications', 'all_communications', 1, $ref['all_communications']);
        $box->body .= '<br />';
        $box->body .= getCommunicationsTable($ref['comm_selection']);
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);

        $html .= $box->get();

        cout($html);
    }

    function _get_communications_query($type = 'html', $report_data = NULL, $other = '')
    {
        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');

        if ($report_data == NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $_ERR_NOUSER = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NOCOMM = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NODATA = Lang::t('_NO_CONTENT', 'report');

        $lang_type = array(
            'none' => Lang::t('_NONE', 'communication'),
            'file' => Lang::t('_LONAME_item', 'storage'),
            'scorm' => Lang::t('_LONAME_scormorg', 'storage')
        );

        $sel_all = $ref['rows_filter']['all_users'];
        $arr_selected_users = $ref['rows_filter']['users']; //list of users selected in the filter (users, groups and org.branches)

        $comm_all = $ref['columns_filter']['all_communications'];
        $arr_selected_comm = $ref['columns_filter']['comm_selection'];  //list of communications selected in the filter

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

        //some other checkings and validations
        if (!$sel_all && count($selection) <= 0) {
            cout('<p>' . $_ERR_NOUSER . '</p>');
            return;
        }

        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = true;

        //extract user idst from selection
        if ($sel_all) {
            $arr_selected_users = $acl_man->getAllUsersIdst();
        } else {
            $arr_selected_users = $acl_man->getAllUsersFromIdst($arr_selected_users);
        }

        if ($comm_all) {
            $query = "SELECT id_comm FROM %lms_communication";
            $res = $this->db->query($query);
            $arR_selected_comm = array();
            while (list($id_comm) = $this->db->fetch_row($res))
                $arr_selected_comm[] = $id_comm;
        }

        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_ . '/lib/lib.preference.php');
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


        //check selected users ...
        if (count($arr_selected_users) <= 0) {
            //message: no users selected
            cout('<p>' . $_ERR_NOUSER . '</p>');
            return;
        }

        //check selected communications ...
        if (count($arr_selected_comm) <= 0) {
            //message: no communications selected
            cout('<p>' . $_ERR_NOCOMM . '</p>');
            return;
        }

        //set table properties and buffer
        $head = array(
            Lang::t('_COMMUNICATIONS_TITLE', 'report'),
            Lang::t('_COMMUNICATIONS_TYPE', 'report'),
            Lang::t('_DATE', 'report'),
            Lang::t('_USER', 'report'),
            Lang::t('_COMMUNICATIONS_HAS_SEEN', 'report'),
            Lang::t('_COMMUNICATIONS_VIEW_DATE', 'report')
        );

        if ($this->use_mail) {
            $head[] = array(
                'style' => 'img-cell',
                'value' => $this->_loadEmailIcon()//'<span class="ico-sprite subs_email"><span>'.Lang::t('_EMAIL').'</span></span>'
            );
        }

        $buffer = new ReportTablePrinter();
        $buffer->openTable('', '');

        $buffer->openHeader();
        $buffer->addHeader($head);
        $buffer->closeHeader();

        $buffer->openBody();

        //rows cycle
        //which selected communication has been seen by selected users?
        $_YES = Lang::t('_YES', 'standard');
        $_NO = Lang::t('_NO', 'standard');
        $arr_viewed = array();
        $query = "SELECT ct.idReference, c.title, c.type_of, c.publish_date, ct.status, "
            . " ct.dateAttempt, ct.idUser, u.userid, u.firstname, u.lastname "
            . " FROM (%lms_communication_track as ct "
            . " JOIN %lms_communication as c ON (ct.id_comm=c.id_comm)) "
            . " JOIN %adm_user as u ON (ct.idUser=u.idst) "
            . " WHERE ct.idUser IN (" . implode(",", $arr_selected_users) . ") "
            . " AND c.id_comm IN (" . implode(",", $arr_selected_comm) . ") "
            . ($start_date != '' ? " AND ct.dateAttempt >= '" . $start_date . "' " : "")
            . ($end_date != '' ? " AND ct.dateAttempt <= '" . $end_date . "' " : "")
            . " ORDER BY c.publish_date DESC, c.title ASC, u.userid ASC";
        $res = $this->db->query($query);

        if ($this->db->num_rows($res) <= 0) {
            //message: no users ...
            cout('<p>' . $_ERR_NODATA . '</p>');
            return;
        }


        while ($obj = $this->db->fetch_obj($res)) {
            $line = array();

            $line[] = $obj->title;
            $line[] = isset($lang_type[$obj->type_of]) ? $lang_type[$obj->type_of] : '';
            $line[] = Format::date($obj->publish_date, "date");

            $line[] = $acl_man->relativeId($obj->userid);
            $line[] = $obj->status == 'completed' || $obj->status == 'passed' ? $_YES : $_NO;
            $line[] = Format::date($obj->dateAttempt, "datetime");

            if ($this->use_mail) $line[] = '<div class="align_center">' .
                Form::getInputCheckbox('mail_' . $obj->idUser, 'mail_recipients[]', $obj->idUser, isset($_POST['select_all']), '') . '</div>';

            $buffer->addLine($line);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        cout($buffer->get());
        $this->_loadEmailActions();
    }



    //----------------------------------------------------------------------------
    //---- games report part ----------------------------------------------
    //----------------------------------------------------------------------------


    function show_report_games($data = NULL, $other = '')
    {
        if ($data === NULL)
            cout($this->_get_games_query());
        else
            cout($this->_get_games_query('html', $data, $other));
    }

    function get_games_filter()
    {
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
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
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

        require_once(_lms_ . '/lib/lib.report.php'); //the comm. table function
        $box->body .= Form::getCheckbox(Lang::t('_ALL', 'report'), 'all_games', 'all_games', 1, $ref['all_games']);
        $box->body .= '<br />';
        $box->body .= getGamesTable($ref['comp_selection']);
        $box->body .= Form::getHidden('update_tempdata', 'update_tempdata', 1);

        $html .= $box->get();

        cout($html);
    }


    function _get_games_query($type = 'html', $report_data = NULL, $other = '')
    {
        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');

        if ($report_data == NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;

        $_ERR_NOUSER = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NOCOMP = Lang::t('_EMPTY_SELECTION', 'report');
        $_ERR_NODATA = Lang::t('_NO_CONTENT', 'report');

        //LO object types translations
        require_once(_lms_ . '/lib/lib.report.php');
        $lang_type = _getLOtranslations();

        $sel_all = $ref['rows_filter']['all_users'];
        $arr_selected_users = $ref['rows_filter']['users']; //list of users selected in the filter (users, groups and org.branches)

        $comp_all = isset($ref['columns_filter']['all_games']) ? $ref['columns_filter']['all_games'] : false;
        $arr_selected_comp = isset($ref['columns_filter']['comp_selection']) ? $ref['columns_filter']['comp_selection'] : array();  //list of communications selected in the filter

        $start_date = isset($ref['columns_filter']['comp_start_date']) ? substr($ref['columns_filter']['comp_start_date'], 0, 10) : '';
        $end_date = isset($ref['columns_filter']['comp_end_date']) ? substr($ref['columns_filter']['comp_end_date'], 0, 10) : '';

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

        //other checkings and validations
        if (!$sel_all && count($selection) <= 0) {
            cout('<p>' . $_ERR_NOUSER . '</p>');
            return;
        }

        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = true;

        //extract user idst from selection
        if ($sel_all) {
            $arr_selected_users = $acl_man->getAllUsersIdst();
        } else {
            $arr_selected_users = $acl_man->getAllUsersFromIdst($arr_selected_users);
        }

        if ($comp_all) {
            $query = "SELECT id_game FROM %lms_games";
            $res = $this->db->query($query);
            $arr_selected_comp = array();
            while (list($id_game) = $this->db->fetch_row($res))
                $arr_selected_comp[] = $id_game;
        }

        //admin users filter
        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous()) {
            require_once(_base_ . '/lib/lib.preference.php');
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


        //check selected users ...
        if (count($arr_selected_users) <= 0) {
            //message: no users selected
            cout('<p>' . $_ERR_NOUSER . '</p>');
            return;
        }

        //check selected communications ...
        if (count($arr_selected_comp) <= 0) {
            //message: no communications selected
            cout('<p>' . $_ERR_NOCOMP . '</p>');
            return;
        }

        //set table properties and buffer
        $head = array(
            Lang::t('_GAMES_TITLE', 'report'),
            Lang::t('_GAMES_TYPE', 'report'),
            Lang::t('_FROM', 'report'),
            Lang::t('_TO', 'report'),
            Lang::t('_USER', 'report'),
            Lang::t('_GAMES_ATTEMPTED', 'report'),
            Lang::t('_GAMES_ATTEMPT_DATE', 'report'),
            Lang::t('_GAMES_FIRST_ATTEMPT_DATE', 'report'),
            Lang::t('_GAMES_CURRENT_SCORE', 'report'),
            Lang::t('_GAMES_MAX_SCORE', 'report'),
            Lang::t('_GAMES_NUM_ATTEMPTS', 'report')
        );

        if ($this->use_mail) {
            $head[] = array(
                'style' => 'img-cell',
                'value' => $this->_loadEmailIcon()//'<span class="ico-sprite subs_email"><span>'.Lang::t('_EMAIL').'</span></span>'
            );
        }

        $buffer = new ReportTablePrinter();
        $buffer->openTable('', '');

        $buffer->openHeader();
        $buffer->addHeader($head);
        $buffer->closeHeader();

        $buffer->openBody();

        //rows cycle
        //which selected communication has been seen by selected users?
        $_YES = Lang::t('_YES', 'standard');
        $_NO = Lang::t('_NO', 'standard');
        $arr_viewed = array();
        $query = "SELECT ct.idReference, c.title, c.type_of, c.start_date, c.end_date, ct.status, "
            . " ct.dateAttempt, ct.firstAttempt, ct.idUser, u.userid, u.firstname, u.lastname, "
            . " ct.current_score, ct.max_score, ct.num_attempts "
            . " FROM (%lms_games_track as ct "
            . " JOIN %lms_games as c ON (ct.idReference=c.id_game)) "
            . " JOIN %adm_user as u ON (ct.idUser=u.idst) "
            . " WHERE ct.idUser IN (" . implode(",", $arr_selected_users) . ") "
            . " AND c.id_game IN (" . implode(",", $arr_selected_comp) . ") "
            . ($start_date != '' ? " AND ct.dateAttempt >= '" . $start_date . "' " : "")
            . ($end_date != '' ? " AND ct.dateAttempt <= '" . $end_date . "' " : "")
            . " ORDER BY c.title, u.userid";
        $res = $this->db->query($query);

        if ($this->db->num_rows($res) <= 0) {
            cout('<p>' . $_ERR_NODATA . '</p>');
            return;
        }


        while ($obj = $this->db->fetch_obj($res)) {
            $line = array();

            $line[] = $obj->title;
            $line[] = isset($lang_type[$obj->type_of]) ? $lang_type[$obj->type_of] : '';
            $line[] = Format::date($obj->start_date, "date");
            $line[] = Format::date($obj->end_date, "date");

            $line[] = $acl_man->relativeId($obj->userid);
            $line[] = $obj->status == 'completed' || $obj->status == 'passed' ? $_YES : $_NO;
            $line[] = Format::date($obj->dateAttempt, "datetime");
            $line[] = Format::date($obj->firstAttempt, "datetime");

            $line[] = $obj->current_score;
            $line[] = $obj->max_score;
            $line[] = $obj->num_attempts;

            if ($this->use_mail) $line[] = '<div class="align_center">' .
                Form::getInputCheckbox('mail_' . $obj->idUser, 'mail_recipients[]', $obj->idUser, isset($_POST['select_all']), '') . '</div>';

            $buffer->addLine($line);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        cout($buffer->get());
        $this->_loadEmailActions();
    }


// +++++++++++++++++++++++++++++++++
//     TEST STAT report functions
// +++++++++++++++++++++++++++++++++
    function get_TESTSTAT_filter()
    {
        //addCss('style_filterbox');

        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_ . '/lib/lib.form.php');
        require_once(_lms_ . '/lib/lib.course.php');

        $ref =& $_SESSION['report_tempdata']['columns_filter'];

        YuiLib::load();
        Util::get_js(Get::rel_path('lms') . '/admin/modules/report/courses_filter.js', true, true);

        //back to columns category selection
        if (isset($_POST['undo_filter'])) {
            //go back at the previous step
            Util::jump_to($back_url);
        }

        //set $_POST data in $_SESSION['report_tempdata']
        $selector = new Selector_Course();
        if (isset($_POST['update_tempdata'])) {
            $selector->parseForState($_POST);
            $temp = array(
                //'org_chart_subdivision'   => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                'all_courses' => ($_POST['all_courses'] == 1 ? true : false),
                'selected_courses' => $selector->getSelection(),
                'showed_columns' => (isset($_POST['cols']) ? $_POST['cols'] : array()),
                'custom_fields' => array(),
                'order_by' => Get::req('order_by', DOTY_STRING, 'userid'),
                'order_dir' => Get::req('order_dir', DOTY_STRING, 'asc'),
                'show_suspended' => Get::req('show_suspended', DOTY_INT, 0) > 0
            );

            foreach ($ref['custom_fields'] as $val) {
                $temp['custom_fields'][] = array(
                    'id' => $val['id'],
                    'label' => $val['label'],
                    'selected' => (isset($_POST['custom'][$val['id']]) ? true : false)
                );
            }

            $_SESSION['report_tempdata']['columns_filter'] = $temp;
        } else {
            //first loading of this page -> prepare $_SESSION data structure
            //if (isset($_SESSION['report_update']) /* && is equal to id_report */) break;
            //get users' custom fields
            require_once(_adm_ . '/lib/lib.field.php');
            $fman = new FieldList();
            $fields = $fman->getFlatAllFields();
            $custom = array();
            foreach ($fields as $key => $val) {
                $custom[] = array('id' => $key, 'label' => $val, 'selected' => false);
            }

            if (!isset($_SESSION['report_tempdata']['columns_filter'])) {
                $_SESSION['report_tempdata']['columns_filter'] = array(
                    //'org_chart_subdivision'   => (isset($_POST['org_chart_subdivision']) ? 1 : 0),
                    'all_courses' => false,
                    'selected_courses' => $selector->getSelection(),
                    'showed_columns' => array(),
                    'custom_fields' => $custom,
                    'order_by' => 'userid',
                    'order_dir' => 'asc',
                    'show_suspended' => 'show_suspended'
                );
            }
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url .= '&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url .= '&show=1';
            Util::jump_to($temp_url);
        }

        cout(Form::getHidden('update_tempdata', 'update_tempdata', 1), 'content');

        $lang = $this->lang;

        //box for direct course selection
        $selection =& $ref['selected_courses'];
        $selector->parseForState($_POST);
        $selector->resetSelection($selection);
        $temp = count($selection);

        $box = new ReportBox('course_selector');
        $box->title = Lang::t('_REPORT_COURSE_SELECTION', 'report');
        $box->description = false;
        $box->body .= '<div class="fc_filter_line filter_corr">';
        $box->body .= '<input id="all_courses" name="all_courses" type="radio" value="1" ' . ($ref['all_courses'] ? 'checked="checked"' : '') . ' />';
        $box->body .= ' <label for="all_courses">' . $lang->def('_ALL_COURSES') . '</label>';
        $box->body .= ' <input id="sel_courses" name="all_courses" type="radio" value="0" ' . ($ref['all_courses'] ? '' : 'checked="checked"') . ' />';
        $box->body .= ' <label for="sel_courses">' . $lang->def('_SEL_COURSES') . '</label>';
        $box->body .= '</div>';

        $box->body .= '<div id="selector_container"' . ($ref['all_courses'] ? ' style="display:none"' : '') . '>';
        //$box->body .= Form::openElementSpace();
        $box->body .= $selector->loadCourseSelector(true);
        //$box->body .= Form::closeElementSpace();
        $box->body .= '<br /></div>';
        $box->footer = $lang->def('_CURRENT_SELECTION') . ':&nbsp;<span id="csel_foot">' . ($ref['all_courses'] ? Lang::t('_ALL', 'standard') : ($temp != '' ? $temp : '0')) . '</span>';
        //.'</div>';
        cout($box->get(), 'content');


        cout(
            '<script type="text/javascript">courses_count=' . ($temp == '' ? '0' : $temp) . ';' .
            'courses_all="' . Lang::t('_ALL', 'standard') . '";' . "\n" .
            'YAHOO.util.Event.addListener(window, "load", courses_selector_init);</script>', 'page_head');


        function is_showed($which)
        {
            if (isset($_SESSION['report_tempdata']['columns_filter'])) {
                return in_array($which, $_SESSION['report_tempdata']['columns_filter']['showed_columns']);
            } else return false;
        }

        ;

        //box for columns selection
        $arr_fieldset = array(
            'user' => '',
            'course' => '',
            'lo' => ''
        );

        $box = new ReportBox('columns_selection');
        $box->title = $lang->def('_SELECT_THE_DATA_COL_NEEDED');
        $box->description = false;


        //prepare fieldsets
        foreach ($this->TESTSTAT_columns as $val) {
            if ($val['select']) {
                $line = Form::getCheckBox($val['label'], 'col_sel_' . $val['key'], 'cols[]', $val['key'], is_showed($val['key']));
                switch ($val['group']) {
                    case 'user':
                        $arr_fieldset['user'] .= $line;
                        break;
                    case 'course':
                        $arr_fieldset['course'] .= $line;
                        break;
                    case 'lo':
                        $arr_fieldset['lo'] .= $line;
                        break;
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    //custom fields
                    if (count($ref['custom_fields']) > 0) {
                        foreach ($ref['custom_fields'] as $key => $val) {
                            $arr_fieldset['user'] .= Form::getCheckBox($val['label'], 'col_custom_' . $val['id'], 'custom[' . $val['id'] . ']', $val['id'], $val['selected']);
                        }
                    }
                }
            }
        }

        //print fieldsets
        foreach ($arr_fieldset as $fid => $fieldset) {
            $ftitle = '';
            switch ($fid) {
                case 'user':
                    {
                        $ftitle = Lang::t('_USER_CUSTOM_FIELDS', 'report');
                    }
                    break;
                case 'course':
                    {
                        $ftitle = Lang::t('_COURSE_FIELDS', 'report');
                    }
                    break;
                case 'lo':
                    {
                        $ftitle = Lang::t('_LEARNING_OBJECTS', 'standard');
                    }
                    break;
            }
            $box->body .= Form::getOpenFieldset($ftitle, 'fieldset_' . $fid . '_fields');
            $box->body .= $fieldset;
            $box->body .= Form::getCloseFieldset();
        }

        cout($box->get(), 'content');


        //other options
        $box = new ReportBox('other_options');
        $box->title = Lang::t('_OTHER_OPTION', 'course');
        $box->description = false;

        $sort_list = array(
            'userid' => Lang::t('_USERID', 'standard'),
            'firstname' => Lang::t('_FIRSTNAME', 'standard'),
            'lastname' => Lang::t('_LASTNAME', 'standard'),
            'email' => Lang::t('_EMAIL', 'standard'),
            'course_code' => Lang::t('_COURSE_CODE', 'standard'),
            'course_name' => Lang::t('_COURSE_NAME', 'standard'),
            'object_title' => Lang::t('_LEARNING_OBJECTS', 'standard'),
            'object_type' => Lang::t('_RU_LO_TYPES', 'report'),
            'first_attempt' => Lang::t('_LO_COL_FIRSTATT', 'report'),
            'last_attempt' => Lang::t('_LO_COL_LASTATT', 'report')
        );
        $dir_list = array(
            'asc' => Lang::t('_ORD_ASC_TITLE', 'standard'),
            'desc' => Lang::t('_ORD_DESC_TITLE', 'standard')
        );

        $sort_selected = array_key_exists($ref['order_by'], $sort_list) ? $ref['order_by'] : 'userid';
        $dir_selected = array_key_exists($ref['order_dir'], $dir_list) ? $ref['order_dir'] : 'asc';

        $sort_dir_dropdown = Form::getInputDropdown('', 'order_dir', 'order_dir', $dir_list, $dir_selected, '');
        $box->body .= Form::getDropdown(Lang::t('_ORDER_BY', 'standard'), 'order_by', 'order_by', $sort_list, $sort_selected, $sort_dir_dropdown);

        $box->body .= Form::getCheckbox(Lang::t('_SHOW_SUSPENDED', 'organization_chart'), 'show_suspended', 'show_suspended', 1, (bool)$ref['show_suspended']);

        cout($box->get(), 'content');
    }


    function show_report_TESTSTAT($report_data = NULL, $other = '')
    {
        $jump_url = ''; //show_report

        checkPerm('view');

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if (isset($_POST['send_mail_confirm']))
            $op = 'send_mail_confirm';
        elseif (isset($_POST['send_mail'])) {
            $op = 'send_mail';
        } else {
            $op = 'show_result';
        }

        switch ($op) {

            case 'send_mail_confirm':
                {
                    $subject = importVar('mail_object', false, '[' . $lang->def('_SUBJECT') . ']');//'[No subject]');
                    $body = importVar('mail_body', false, '');
                    $acl_man = new DoceboACLManager();
                    $sender = Get::sett('sender_event');

                    $mail_recipients = unserialize(urldecode(Get::req('mail_recipients', DOTY_STRING, '')));

                    // prepare intestation for email
                    $from = "From: " . $sender . $GLOBALS['mail_br'];
                    $header = "MIME-Version: 1.0" . $GLOBALS['mail_br']
                        . "Content-type: text/html; charset=" . getUnicode() . $GLOBALS['mail_br'];
                    $header .= "Return-Path: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    //$header .= "Reply-To: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Sender: " . Get::sett('sender_event') . $GLOBALS['mail_br'];
                    $header .= "X-Mailer: PHP/" . phpversion() . $GLOBALS['mail_br'];

                    // send mail
                    $arr_recipients = array();
                    foreach ($mail_recipients as $recipient) {
                        $rec_data = $acl_man->getUser($recipient, false);
                        //mail($rec_data[ACL_INFO_EMAIL] , stripslashes($subject), stripslashes(nl2br($body)), $from.$header."\r\n");
                        $arr_recipients[] = $rec_data[ACL_INFO_EMAIL];
                    }
                    $mailer = DoceboMailer::getInstance();
                    $mailer->addReplyTo(Get::sett('sender_event').$GLOBALS['mail_br']);
                    $mailer->SendMail($sender, $arr_recipients, stripslashes($subject), stripslashes(nl2br($body)));

                    $result = getResultUi($lang->def('_OPERATION_SUCCESSFUL'));

                    cout($this->_get_TESTSTAT_query('html', NULL, $result));
                }
                break;

            case 'send_mail':
                {
                    require_once(_base_ . '/lib/lib.form.php');
                    $mail_recipients = Get::req('mail_recipients', DOTY_MIXED, array());
                    cout(
                        ''//Form::openForm('course_selection', Util::str_replace_once('&', '&amp;', $jump_url))
                        . Form::openElementSpace()
                        . Form::getTextfield($lang->def('_SUBJECT'), 'mail_object', 'mail_object', 255)
                        . Form::getTextarea($lang->def('_MAIL_BODY'), 'mail_body', 'mail_body')
                        . Form::getHidden('mail_recipients', 'mail_recipients', urlencode(serialize($mail_recipients)))
                        . Form::closeElementSpace()
                        . Form::openButtonSpace()
                        . Form::getButton('send_mail_confirm', 'send_mail_confirm', $lang->def('_SEND_MAIL'))
                        . Form::getButton('undo_mail', 'undo_mail', $lang->def('_UNDO'))
                        . Form::closeButtonSpace()
                        //.Form::closeForm()
                        . '</div>', 'content');
                }
                break;

            default:
                {
                    cout($this->_get_TESTSTAT_query('html', $report_data, $other));
                }

        }
    }

    function _get_TESTSTAT_query($type = 'html', $report_data = NULL, $other = '')
    {
        require_once(_lms_ . '/admin/modules/report/report_tableprinter.php');

        function is_showed($which, $data)
        {
            if (isset($data['columns_filter'])) {
                return in_array($which, $data['columns_filter']['showed_columns']);
            } else return false;
        }

        ;

        if ($report_data == NULL) $ref =& $_SESSION['report_tempdata']; else $ref =& $report_data;
        $_rows =& $ref['rows_filter'];
        $_cols =& $ref['columns_filter'];
        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = TRUE;

        $all_users = &$_rows['all_users']; //select root & descendants from orgchart instead
        $all_courses = &$_cols['all_courses'];
        $courses = &$_cols['selected_courses'];
        $types = &$_cols['lo_types'];
        $milestones = &$_cols['lo_milestones'];
        $showed = &$_cols['showed_columns'];
        $customcols = &$_cols['custom_fields'];
        $order_by = isset($_cols['order_by']) ? $_cols['order_by'] : 'userid';
        $order_dir = isset($_cols['order_dir']) ? $_cols['order_dir'] : 'asc';
        $suspended = isset($_cols['show_suspended']) ? (bool)$_cols['show_suspended'] : false;
        if ($all_users) {
            $users =& $acl_man->getAllUsersIdst();
        } else {
            $users =& $acl_man->getAllUsersFromSelection($_rows['users']);
        }

        $temptypes = array();
        foreach ($types as $val) {
            $temptypes[] = "'" . $val . "'";
        }

        $tempmilestones = array();
        foreach ($milestones as $val) {
            switch ($val) {
                case _MILESTONE_NONE:
                    {
                        $tempmilestones[] = "''";
                        $tempmilestones[] = "'-'";
                    }
                    break;
                case _MILESTONE_START:
                    {
                        $tempmilestones[] = "'start'";
                    }
                    break;
                case _MILESTONE_END:
                    {
                        $tempmilestones[] = "'end'";
                    }
                    break;
            }
        }

        $colspans = array('user' => 0, 'course' => 0, 'lo' => 0);
        foreach ($this->TESTSTAT_columns as $val) {
            if ($val['select']) {
                if (in_array($val['key'], $showed)) {
                    switch ($val['key']) {
                        case 'user_name':
                            $colspans[$val['group']] += 2;
                            break;
                        default:
                            $colspans[$val['group']]++;
                            break;
                    }
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    ;//do nothing ...
                } else {
                    $colspans[$val['group']]++;
                }
            }
        }

        //custom user fields
        require_once(_adm_ . '/lib/lib.field.php');
        $fman = new FieldList();
        $field_values = array();
        $temp_head2 = array();
        foreach ($customcols as $val) {
            if ($val['selected']) {
                $colspans['user']++;
                $temp_head2[] = $val['label'];
                $field_values[$val['id']] = $fman->fieldValue((int)$val['id'], $users);
            }
        }

        $lang = $this->lang;

        $head1 = array();
        $head1[] = array('colspan' => $colspans['user'], 'value' => $lang->def('_USER')); //_TH_USER
        $head1[] = array('colspan' => $colspans['course'], 'value' => $lang->def('_COURSE')); //_TH_COURSE
        $head1[] = array('colspan' => $colspans['lo'], 'value' => $lang->def('_LEARNING_OBJECTS'));
        if ($this->use_mail)
            $head1[] = array(
                'style' => 'img-cell',
                'value' => $this->_loadEmailIcon()
            );

        $head2 = array();
        foreach ($this->TESTSTAT_columns as $val) {
            if ($val['select']) {
                if (in_array($val['key'], $showed)) {
                    switch ($val['key']) { //manages exceptions through switch
                        case 'user_name':
                            {
                                $head2[] = Lang::t('_LASTNAME', 'standard');
                                $head2[] = Lang::t('_FIRSTNAME', 'standard');
                            }
                            break;
                        default:
                            $head2[] = $val['label'];
                            break;
                    }
                }
            } else {
                if ($val['key'] == '_CUSTOM_FIELDS_') {
                    foreach ($temp_head2 as $tval) {
                        $head2[] = $tval;
                    }
                } else {
                    $head2[] = $val['label']; //label
                }
            }
        }


        if ($this->use_mail) $head2[] = '';//'<img src="'.getPathImage().'standard/email.gif"/>';//''; //header for checkbox

        $buffer = new ReportTablePrinter($type);
        $buffer->openTable('', '');

        $buffer->openHeader();
        $buffer->addHeader($head1);
        $buffer->addHeader($head2);
        $buffer->closeHeader();

        //retrieve LOs from courses

        $score_arr = array(
            'test' => array(),
            'scorm' => array()
        );

        //retrieve test score
        $query = "SELECT t1.idOrg, t2.idUser, t1.idCourse, t4.score, t2.bonus_score, t2.score_status "
            . " FROM %lms_organization AS t1 "
            . " INNER JOIN %lms_testtrack AS t2 ON ( t1.objectType = 'test' "
            . " AND t1.idOrg = t2.idReference )"
            . " INNER JOIN %lms_testtrack_times t4 ON t2.idTrack = t4.idTrack "
            . " INNER JOIN %adm_user as t3 "
            . " ON t3.idst=t2.idUser " . ($suspended ? "" : "AND t3.valid=1 ")
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? " AND t1.milestone IN (" . implode(',', $tempmilestones) . ") " : ""
                . "ORDER BY t4.date_end ASC");
        $res = sql_query($query);
        $color_id = 0;
        while ($row = sql_fetch_assoc($res)) {
            $color = "#efefef";
            if ($color_id % 2 == 0) {
                $color = "#FFF";
            }
            $score_arr['test'][$row['idOrg']][$row['idUser']] .= "<div style='display:inline-block; background-color: " . $color . "; height: 16px;'>" . ($row['score'] + $row['bonus_score']) . "</div> ";
            $color_id++;
        }

        //retrievescorm score
        $query = "SELECT t1.idOrg, t2.idUser, t1.idCourse, t2.score_raw, t2.score_min, t2.score_max "
            . " FROM %lms_organization AS t1 "
            . " JOIN %lms_scorm_tracking AS t2 ON ( t1.objectType = 'scormorg' "
            . " AND t1.idOrg = t2.idReference ), %adm_user as t3 "
            . "WHERE t3.idst=t2.idUser " . ($suspended ? "" : "AND t3.valid=1 ")
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? " AND t1.milestone IN (" . implode(',', $tempmilestones) . ") " : "");
        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {
            $score_arr['scorm'][$row['idOrg']][$row['idUser']] = $row['score_raw'];
        }

        $buffer->openBody();

        //retrieve LO types translations
        $LO_types = $this->getLOTypesTranslations();

        //retrieve LO's data
        $_dir = "ASC";
        switch ($order_dir) {
            case "desc":
                $_dir = "DESC";
                break;
        }
        $query_order_by = "t0.userid " . $_dir . ", t1.title " . $_dir;
        switch ($order_by) {
            case 'firstname':
                $query_order_by = "t0.firstname " . $_dir . ", t0.lastname, " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'lastname':
                $query_order_by = "t0.lastname " . $_dir . ", t0.firstname, " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'email':
                $query_order_by = "t0.email " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'course_code':
                $query_order_by = "t3.code " . $_dir . ", t3.name " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'course_name':
                $query_order_by = "t3.name " . $_dir . ", t3.code " . $_dir . ", t0.userid " . $_dir . ", t1.title " . $_dir;
                break;
            case 'object_title':
                $query_order_by = "t1.title " . $_dir . ", t0.userid " . $_dir;
                break;
            case 'object_type':
                $query_order_by = "t1.objectType " . $_dir . ", t1.title " . $_dir . ", t0.userid " . $_dir;
                break;
            case 'first_attempt':
                $query_order_by = "t2.firstAttempt";
                break;
            case 'last_attempt':
                $query_order_by = "t2.dateAttempt";
                break;
        }

        $query = "SELECT t0.idst as user_st, t0.userid, t0.firstname, t0.lastname, t0.email, t0.valid, "
            . " t1.idOrg, t1.objectType, t1.title, t1.idResource, t1.milestone, "
            . " t3.idCourse, t3.code, t3.name, t3.status as course_status, "
            . " t2.firstAttempt, t2.status,"
            . " t5.score, t5.date_attempt "
            . " FROM %adm_user as t0 "
            . " INNER JOIN %lms_organization as t1"
            . " INNER JOIN %lms_course as t3 ON t1.idCourse=t3.idCourse"
            . " INNER JOIN %lms_testtrack as t4 ON (t1.idOrg=t4.idReference AND t0.idst=t4.idUser)"
            . " INNER JOIN %lms_commontrack as t2 ON (t0.idst = t2.idUser AND t4.idReference=t2.idReference )"
            . " INNER JOIN %lms_testtrack_times as t5 ON t4.idTrack = t5.idTrack"
            . " WHERE t1.objectType= 'test'"

            . ($suspended ? "" : "AND t0.valid=1 ")
            . (!$all_courses ? " AND t1.idCourse IN (" . implode(',', $courses) . ") " : "")
            . (count($temptypes) > 0 ? " AND t2.objectType IN (" . implode(',', $temptypes) . ") " : "")
            . (!$all_users ? " AND t2.idUser IN (" . implode(',', $users) . ") " : "")
            . (count($tempmilestones) > 0 ? "AND t1.milestone IN (" . implode(',', $tempmilestones) . ")" : "")
            . " GROUP BY t5.score, t5.date_attempt, t1.title, user_st"
            . " ORDER BY " . $query_order_by;

        $res = sql_query($query);
        while ($row = sql_fetch_assoc($res)) {

            $temp = array();
            foreach ($this->TESTSTAT_columns as $val) {
                switch ($val['key']) {
                    case 'userid':
                        $temp[] = $acl_man->relativeId($row['userid']);
                        break;
                    case 'user_name':
                        {
                            if (in_array($val['key'], $showed)) {
                                $temp[] = $row['lastname'];
                                $temp[] = $row['firstname'];
                            }
                        }
                        break;
                    case 'email':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['email'];
                        }
                        break;
                    case 'suspended':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = ($row['valid'] > 0 ? Lang::t('_NO', 'standard') : Lang::t('_YES', 'standard'));
                        }
                        break;
                    case '_CUSTOM_FIELDS_':
                        {
                            foreach ($customcols as $field) {
                                if ($field['selected']) {
                                    if (isset($field_values[$field['id']][$row['user_st']])) {
                                        $temp[] = $field_values[$field['id']][$row['user_st']];
                                    } else {
                                        $temp[] = '';
                                    }
                                }
                            }
                        }
                        break;
                    case 'course_code':
                        $temp[] = $row['code'];
                        break;
                    case 'course_name':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['name'];
                        }
                        break;
                    case 'course_status':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $this->_convertStatusCourse($row['course_status']);
                        }
                        break;
                    case 'lo_name':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $row['title'];
                        }
                        break;
                    case 'lo_date':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = $this->_convertDate($row['date_attempt']);
                        }
                        break;
                    case 'lo_status':
                        {
                            if (in_array($val['key'], $showed)) $temp[] = Lang::t($row['status'], 'storage');
                        }
                        break;
                    case 'lo_score':
                        {
                            if (in_array($val['key'], $showed)) {
                                $temp[] = $row['score'];
                            }
                        }
                        break;
                    default:
                        { /*if (in_array($val['key'], $showed)) $temp[]='';*/
                        }
                        break;
                }
                //end switch - end for
            }
            if ($this->use_mail) {
                $temp[] =//'<input type="checkbox" value="'.$row['idst'].'"/>'; //header for checkbox
                    '<div class="align_center">' . Form::getInputCheckbox('mail_' . $row['user_st'], 'mail_recipients[]', $row['user_st'], isset($_POST['select_all']), '') . '</div>';

            }

            $buffer->addLine($temp);
        }

        $buffer->closeBody();
        $buffer->closeTable();

        $output = $buffer->get();

        if ($this->use_mail) {
            $output .= $this->_loadEmailActions();
        }

        return $output;
    }


}