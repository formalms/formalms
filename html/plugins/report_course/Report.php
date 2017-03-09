<?php
namespace Plugin\report_course;
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


define('_RCS_CATEGORY_USERS', 'users');
define('_RCS_CATEGORY_LO', 'LO');
define('_RCS_CATEGORY_DOC_VAL', 'doc_valutation');
define('_RCS_CATEGORY_COURSE_VAL', 'course_valutation');

define('_SUBSTEP_USERS', 0);
define('_SUBSTEP_COLUMNS', 1);

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

class Report extends \ReportPlugin{

    var $status_u = array();
    var $status_c = array();

    function __construct()
    {
        $this->lang =& DoceboLanguage::createInstance('report', 'framework');

        $this->usestandardtitle_rows = true;
        //$this->usestandardtitle_cols = false;

        $lang =& DoceboLanguage::CreateInstance('course', 'lms');

        $this->_set_columns_category(_RCS_CATEGORY_USERS, $this->lang->def('_RCS_CAT_USER'), 'get_user_filter', 'show_report_user', '_get_users_query', false);
        $this->_set_columns_category(_RCS_CATEGORY_DOC_VAL, $this->lang->def('_RCS_CAT_DOC_VAL'), 'get_doc_val_filter', 'show_report_doc_val', '_get_doc_val_query', false);
        $this->_set_columns_category(_RCS_CATEGORY_COURSE_VAL, $this->lang->def('_RCS_CAT_COURSE_VAL'), 'get_course_val_filter', 'show_report_course_val', '_get_course_val_query', false);

        $this->status_c = array(
            CST_PREPARATION => $lang->def('_CST_PREPARATION'),//, 'course', 'lms'),
            CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE'),//, 'course', 'lms'),
            CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED'),//, 'course', 'lms'),
            CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED'),//, 'course', 'lms'),
            CST_CANCELLED 	=> $lang->def('_CST_CANCELLED')//, 'course', 'lms')
        );

        $lang =& DoceboLanguage::CreateInstance('course', 'lms');
        $this->status_u = array(
            _CUS_CONFIRMED 		=> $lang->def('_USER_STATUS_CONFIRMED'),//, 'subscribe', 'lms'),

            _CUS_SUBSCRIBED 	=> $lang->def('_USER_STATUS_SUBS'),//, 'subscribe', 'lms'),
            _CUS_BEGIN 			=> $lang->def('_USER_STATUS_BEGIN'),//, 'subscribe', 'lms'),
            _CUS_END 			=> $lang->def('_USER_STATUS_END'),//, 'lms'),
            _CUS_SUSPEND 		=> $lang->def('_USER_STATUS_SUSPEND')//, 'subscribe', 'lms')
        );
    }

    public function getDynamicFilter($data)
    {
        if($data['columns_filter_category'] === 'doc_valutation' || $data['columns_filter_category'] === 'course_valutation')
            return false;
        return true;
    }

    function get_rows_filter()
    {
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
        if (!isset($_SESSION['report_tempdata']['rows_filter'])) {
            $_SESSION['report_tempdata']['rows_filter'] = array(
                'all_courses' => true,
                'selected_courses' => array()
            );
        }
        $ref =& $_SESSION['report_tempdata']['rows_filter'];
        $selector = new Selector_Course();

        if (isset($_POST['update_tempdata'])) {
            $selector->parseForState($_POST);
            $ref['all_courses'] = (Get::req('all_courses', DOTY_INT, 1)==1 ? true : false);
        }	else	{
            $selector->resetSelection($ref['selected_courses']);
        }

        //filter setting done, go to next step
        if (isset($_POST['import_filter'])) {
            $ref['selected_courses'] = $selector->getSelection($_POST);
            Util::jump_to($next_url);
        }

        $ref =& $_SESSION['report_tempdata']['rows_filter'];
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

        $box->footer = $boxlang->def('_CURRENT_SELECTION').': <span id="csel_foot">'.($ref['all_courses'] ? $boxlang->def('_ALL') : ($temp!='' ? $temp : '0')).'</span>';

        YuiLib::load('datasource');
        Util::get_js(Get::rel_path('lms').'/admin/modules/report/courses_filter.js', true, true);

        cout('<script type="text/javascript"> '."\n".
            'var courses_count="'.($temp!='' ? $temp : '0').'";'."\n".
            'var courses_all="'.$boxlang->def('_ALL').'";'."\n".
            'YAHOO.util.Event.addListener(window, "load", function(e){ courses_selector_init(); });'."\n".
            '</script>', 'page_head');

        cout(
            Form::openForm('first_step_user_filter', $jump_url, false, 'post').
            $box->get().
            Form::getHidden('update_tempdata', 'update_tempdata', 1).
            Form::openButtonSpace().
            //Form::getBreakRow().
            Form::getButton('ok_filter', 'import_filter', $lang->def('_NEXT')).
            Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO')).
            Form::closeButtonSpace().
            Form::closeForm(), 'content');
    }


    function get_user_filter()
    {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once(_adm_.'/lib/lib.directory.php');
        require_once(_adm_.'/class.module/class.directory.php');
        require_once(_lms_.'/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $org_chart_subdivision 	= importVar('org_chart_subdivision', true, 0);

        //detect the step in which we are
        $substep = _SUBSTEP_USERS; //first substep
        switch (Get::req('substep', DOTY_STRING, 'no_step')) {
            case 'users_selection' : $substep = _SUBSTEP_USERS; break;
            case 'columns_selection' :$substep =_SUBSTEP_COLUMNS; break;
        }

        //draw page depending on the $substep variable
        if (!isset($_SESSION['report_tempdata']['columns_filter']))
            $_SESSION['report_tempdata']['columns_filter'] = array(
                'time_belt' => array('time_range'=> '', 'start_date'=>'', 'end_date'=>''),
                'org_chart_subdivision' => 0,
                'show_classrooms_editions' => false,
                'showed_cols' => array(),
                'show_percent'=> true,
                'show_suspended' => false,
                'only_students' => false,
                'show_assessment' => false
            );
        $ref = &$_SESSION['report_tempdata']['columns_filter'];

        switch ($substep) {

            case _SUBSTEP_COLUMNS: {
                //set session data
                if(Get::req('is_updating', DOTY_INT, 0)>0)	{
                    $ref['showed_cols'] = Get::req('cols', DOTY_MIXED, array());
                    $ref['show_percent'] = (Get::req('show_percent', DOTY_INT, 0)>0 ? true : false);
                    $ref['time_belt'] = array(
                        'time_range'=>$_POST['time_belt'],
                        'start_date' => Format::dateDb($_POST['start_time'], 'date'),
                        'end_date' => Format::dateDb($_POST['end_time'], 'date')
                    );
                    $ref['org_chart_subdivision'] = (isset($_POST['org_chart_subdivision']) ? 1 : 0);
                    $ref['show_classrooms_editions'] = (isset($_POST['show_classrooms_editions']) ? true : false);
                    $ref['show_suspended'] = Get::req('show_suspended', DOTY_INT, 0) > 0;
                    $ref['only_students'] = Get::req('only_students', DOTY_INT, 0) > 0;
                    $ref['show_assessment'] = Get::req('show_assessment', DOTY_INT, 0) > 0;
                } else {
                    //...
                }

                //check action
                if(isset($_POST['cancelselector']))
                    Util::jump_to($jump_url.'&substep=users_selection');

                if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter'])) {
                    $temp_url = $next_url;
                    if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
                    if (isset($_POST['show_filter'])) $temp_url.='&show=1';
                    Util::jump_to($temp_url);
                }

                cout($this->page_title, 'content');

                function is_showed($which, &$arr) {
                    if(isset($arr['showed_cols'])) return in_array($which, $arr['showed_cols']);
                    else return false;
                }

                /*$go_to_second_step = (isset($_POST['go_to_second_step']) ? true : false);
                $we_are_in_second_step = Get::req('second_step', DOTY_INT, false);*/

                $time_belt = array(
                    0 		=> $lang->def('_CUSTOM_BELT'),
                    7 		=> $lang->def('_LAST_WEEK'),
                    31		=> $lang->def('_LAST_MONTH'),
                    93 		=> $lang->def('_LAST_THREE_MONTH'),
                    186 	=> $lang->def('_LAST_SIX_MONTH'),
                    365 	=> $lang->def('_LAST_YEAR'),);

                cout(
                    Form::openForm('user_report_rows_courses', $jump_url).
                    Form::getHidden('update_tempdata', 'update_tempdata', 1).
                    Form::getHidden('is_updating', 'is_updating', 1).
                    Form::getHidden('substep', 'substep', 'columns_selection'), 'content');

                //box for time belt
                $box = new ReportBox('timebelt_box');
                $box->title = $lang->def('_REPORT_USER_TITLE_TIMEBELT');
                $box->description = Lang::t('_TIME_PERIOD_FILTER', 'report');
                $box->body =
                    Form::getDropdown($lang->def('_TIME_BELT'), 'time_belt_'.$this->id_report, 'time_belt', $time_belt, (isset($ref['time_belt']['time_range']) ? $ref['time_belt']['time_range'] : ''), '', '' ,
                        ' onchange="report_disableCustom( \'time_belt_'.$this->id_report.'\', \'start_time_'.$this->id_report.'\', \'end_time_'.$this->id_report.'\' )"')
                    .Form::getOpenFieldset($lang->def('_CUSTOM_BELT'), 'fieldset_'.$this->id_report)
                    .Form::getDatefield($lang->def('_START_TIME'), 'start_time_'.$this->id_report, 'start_time', Format::date($ref['time_belt']['start_date'], 'date') )
                    .Form::getDatefield($lang->def('_TO'), 'end_time_'.$this->id_report, 'end_time', Format::date($ref['time_belt']['end_date'], 'date') )
                    .Form::getCloseFieldset();

                cout($box->get().Form::getBreakRow(), 'content');

                $box = new ReportBox('other_options');
                $box->title = Lang::t('_OTHER_OPTION', 'course');
                $box->description = false;
                $box->body =
                    Form::getCheckbox($lang->def('ORG_CHART_SUBDIVISION'), 'org_chart_subdivision_'.$this->id_report,	'org_chart_subdivision',	1, ($ref['org_chart_subdivision']==1 ? true : false))
                    .Form::getCheckbox(Lang::t('_SHOW_SUSPENDED', 'organization_chart'), 'show_suspended', 'show_suspended', 1, (bool)$ref['show_suspended'])
                    .Form::getCheckbox(Lang::t('_SHOW_ONLY', 'subscribe').': '.Lang::t('_STUDENTS', 'coursereport'), 'only_students', 'only_students', 1, (bool)$ref['only_students'])
                    .Form::getCheckbox(Lang::t('_SHOW', 'standard').': '.Lang::t('_ASSESSMENT', 'standard'), 'show_assessment', 'show_assessment', 1, (bool)$ref['show_assessment']);

                cout($box->get().Form::getBreakRow(), 'content');

                $glang =& DoceboLanguage::createInstance('course', 'lms');
                $show_classrooms_editions = $ref['show_classrooms_editions'];
                cout('<script type="text/javascript">
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

                $box = new ReportBox('columns_sel_box');
                $box->title = $lang->def('_SELECT_THE_DATA_COL_NEEDED');
                $box->description = false;
                $box->body = Form::getHidden('is_updating', 'is_updating', 2)
                    //$glang->def('_COURSE_NAME')
                    //.Form::openElementSpace()
                    .Form::getOpenFieldset($lang->def('_COURSE_FIELDS'), 'fieldset_course_fields')
                    .Form::getCheckBox($lang->def('_COURSE_CODE'), 'col_sel_coursecode', 'cols[]', '_CODE_COURSE', is_showed('_CODE_COURSE', $ref))
                    //.Form::getCheckBox('', 'col_sel_coursename', 'cols[]', '_NAME_COURSE', true, "style='display:none;'")
                    .Form::getCheckBox($glang->def('_CATEGORY'), 'col_sel_category', 'cols[]', '_COURSE_CATEGORY', is_showed('_COURSE_CATEGORY', $ref))
                    .Form::getCheckBox($glang->def('_STATUS'), 'col_sel_status', 'cols[]', '_COURSESTATUS', is_showed('_COURSESTATUS', $ref))
                    .Form::getCheckBox($glang->def('_CATALOGUE'), 'col_sel_catalogue', 'cols[]', '_COURSECATALOGUE', is_showed('_COURSECATALOGUE', $ref))
                    .Form::getCheckBox($glang->def('_CREATION_DATE'), 'col_sel_publication', 'cols[]', '_PUBLICATION_DATE', is_showed('_PUBLICATION_DATE', $ref))
                    .Form::getCheckBox($glang->def('_LABEL'), 'col_sel_label', 'cols[]', '_COURSELABEL', is_showed('_COURSELABEL', $ref))
                    .Form::getCloseFieldset()

                    .Form::getOpenFieldset(
                        Form::getInputCheckbox('show_classrooms_editions', 'show_classrooms_editions', 1, $show_classrooms_editions, "onclick=activateClassrooms();")
                        ."&nbsp;&nbsp;".Lang::t('_CLASSROOM_FIELDS', 'report')
                        , 'fieldset_classroom_fields')
                    .'<div id="not_classrooms" style="display:'.($show_classrooms_editions ? "none" : "block").'">'
                    .Lang::t('_ACTIVATE_CLASSROOM_FIELDS', 'report')
                    .'</div>'
                    .'<div id="use_classrooms" style="display:'.($show_classrooms_editions ? "block" : "none").'">'
                    .Form::getCheckBox(Lang::t('_NAME', 'standard'), 'col_sel_classroomname', 'cols[]', '_TH_CLASSROOM_CODE', is_showed('_TH_CLASSROOM_CODE', $ref))
                    .Form::getCheckBox(Lang::t('_CODE', 'standard'), 'col_sel_classroomcode', 'cols[]', '_TH_CLASSROOM_NAME', is_showed('_TH_CLASSROOM_NAME', $ref))
                    .Form::getCheckBox(Lang::t('_DATE_BEGIN', 'standard'), 'col_sel_classroomdatebegin', 'cols[]', '_TH_CLASSROOM_MIN_DATE', is_showed('_TH_CLASSROOM_MIN_DATE', $ref))
                    .Form::getCheckBox(Lang::t('_DATE_END', 'standard'), 'col_sel_classroomdateend', 'cols[]', '_TH_CLASSROOM_MAX_DATE', is_showed('_TH_CLASSROOM_MAX_DATE', $ref))
                    .'</div>'
                    .Form::getCloseFieldset()

                    .Form::getOpenFieldset($lang->def('_COURSE_FIELDS_INFO'), 'fieldset_course_fields')
                    .Form::getCheckBox($glang->def('_COURSE_LANG_METHOD'), 'col_course_lang_method', 'cols[]', '_LANGUAGE', is_showed('_LANGUAGE', $ref))
                    .Form::getCheckBox($glang->def('_DIFFICULTY'), 'col_course_difficult', 'cols[]', '_DIFFICULT', is_showed('_DIFFICULT', $ref))
                    .Form::getCheckBox($glang->def('_DATE_BEGIN'), 'col_date_begin', 'cols[]', '_DATE_BEGIN', is_showed('_DATE_BEGIN', $ref))
                    .Form::getCheckBox($glang->def('_DATE_END'), 'col_date_end', 'cols[]', '_DATE_END', is_showed('_DATE_END', $ref))
                    .Form::getCheckBox($glang->def('_HOUR_BEGIN'), 'col_time_begin', 'cols[]', '_TIME_BEGIN', is_showed('_TIME_BEGIN', $ref))
                    .Form::getCheckBox($glang->def('_HOUR_END'), 'col_time_end', 'cols[]', '_TIME_END', is_showed('_TIME_END', $ref))
                    .Form::getCheckBox($glang->def('_MAX_NUM_SUBSCRIBE'), 'col_max_num_subscribe', 'cols[]', '_MAX_NUM_SUBSCRIBED', is_showed('_MAX_NUM_SUBSCRIBED', $ref))
                    .Form::getCheckBox($glang->def('_MIN_NUM_SUBSCRIBE'), 'col_min_num_subscribe', 'cols[]', '_MIN_NUM_SUBSCRIBED', is_showed('_MIN_NUM_SUBSCRIBED', $ref))
                    .Form::getCheckBox(Lang::t('_CREDITS', 'standard'), 'col_credits', 'cols[]', '_CREDITS', is_showed('_CREDITS', $ref))
                    .Form::getCheckBox($glang->def('_COURSE_PRIZE'), 'col_course_price', 'cols[]', '_PRICE', is_showed('_PRICE', $ref))
                    .Form::getCheckBox($glang->def('_COURSE_ADVANCE'), 'col_course_advance', 'cols[]', '_ADVANCE', is_showed('_ADVANCE', $ref))
                    .Form::getCheckBox($glang->def('_COURSE_TYPE'), 'col_course_type', 'cols[]', '_COURSE_TYPE', is_showed('_COURSE_TYPE', $ref))
                    .Form::getCheckBox($glang->def('_COURSE_AUTOREGISTRATION_CODE'), 'col_autoregistration_code', 'cols[]', '_AUTOREGISTRATION_CODE', is_showed('_AUTOREGISTRATION_CODE', $ref))
                    .Form::getCloseFieldset()

                    .Form::getOpenFieldset($lang->def('_STATS_FIELDS_INFO'), 'fieldset_course_fields')
                    .Form::getCheckBox($lang->def('_USER_STATUS_SUBS'), 'col_inscr', 'cols[]', '_INSCR', is_showed('_INSCR', $ref))
                    .Form::getCheckBox($lang->def('_MUSTBEGIN'), 'col_mustbegin', 'cols[]', '_MUSTBEGIN', is_showed('_MUSTBEGIN', $ref))
                    .Form::getCheckBox($lang->def('_USER_STATUS_BEGIN'), 'col_user_status_begin', 'cols[]', '_USER_STATUS_BEGIN', is_showed('_USER_STATUS_BEGIN', $ref))
                    .Form::getCheckBox($lang->def('_COMPLETED'), 'col_completecourse', 'cols[]', '_COMPLETECOURSE', is_showed('_COMPLETECOURSE', $ref))
                    .Form::getCheckBox($lang->def('_TOTAL_SESSION'), 'col_total_session', 'cols[]', '_TOTAL_SESSION', is_showed('_TOTAL_SESSION', $ref))
                    .Form::getBreakRow()
                    .Form::getCheckBox($lang->def('_PERCENTAGE'), 'show_percent', 'show_percent', '1', $ref['show_percent'])
                    .Form::getCloseFieldset();

                cout($box->get(), 'content');

                cout(	Form::openButtonSpace()
                    .Form::getBreakRow()
                    .Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
                    .Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK'))
                    .Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW'))
                    .Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO'))
                    .Form::closeButtonSpace()
                    .Form::closeForm(), 'content');
                cout('</div>', 'content'); //stdblock div

            } break;

            case _SUBSTEP_USERS: {
                //$aclManager = new DoceboACLManager();
                $user_select = new UserSelector();
                $user_select->use_suspended = true;

                if (Get::req('is_updating', DOTY_INT, 0)>0) {
                    $ref['all_users'] = ( Get::req('all_users', DOTY_INT, 0)>0 ? true : false );
                } else { //maybe redoundant
                    if (!isset($ref['all_users'])) $ref['all_users'] = false;
                    if (!isset($ref['users'])) $ref['users'] = array();
                    $user_select->requested_tab = PEOPLEVIEW_TAB;
                    $user_select->resetSelection($ref['users']);
                    //$ref['users'] = array(); it should already have been set to void array, if non existent
                }

                if(isset($_POST['cancelselector'])) {
                    Util::jump_to($back_url);
                } elseif(isset($_POST['okselector'])) {
                    $elem_selected 	= $user_select->getSelection($_POST);
                    $ref['all_users'] = ( Get::req('all_users', DOTY_INT, 0)>0 ? true : false );
                    $ref['users'] = $elem_selected;
                    Util::jump_to($jump_url.'&substep=columns_selection' );
                }

                //set page
                if($org_chart_subdivision == 0) {
                    $user_select->show_user_selector = TRUE;
                    $user_select->show_group_selector = TRUE;
                } else {
                    $user_select->show_user_selector = FALSE;
                    $user_select->show_group_selector = FALSE;
                }
                $user_select->show_orgchart_selector = TRUE;
                //$user_select->show_orgchart_simple_selector = FALSE;
                //$user_select->multi_choice = TRUE;

                $user_select->addFormInfo(
                    Form::getCheckbox($lang->def('_REPORT_FOR_ALL'), 'all_users', 'all_users', 1, ($ref['all_users'] ? 1 : 0)).
                    Form::getBreakRow().
                    Form::getHidden('org_chart_subdivision', 'org_chart_subdivision', $org_chart_subdivision).
                    Form::getHidden('is_updating', 'is_updating', 1).
                    Form::getHidden('substep', 'substep', 'user_selection').
                    Form::getHidden('second_step', 'second_step', 1)
                );

                cout($this->page_title, 'content');

                $user_select->loadSelector(Util::str_replace_once('&', '&amp;', $jump_url),
                    false,
                    $this->lang->def('_CHOOSE_USER_FOR_REPORT'),
                    true);

            } break;

        }

    }

    //Valutazione docenti
    function get_doc_val_filter()
    {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once(_adm_.'/lib/lib.directory.php');
        require_once(_adm_.'/class.module/class.directory.php');
        require_once(_lms_.'/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $glang =& DoceboLanguage::createInstance('admin_course_managment', 'lms');

        if (!isset($_SESSION['report_tempdata']['columns_filter']))
            $_SESSION['report_tempdata']['columns_filter'] = array();

        $ref = &$_SESSION['report_tempdata']['columns_filter'];

        if(Get::req('is_updating', DOTY_INT, 0)>0)
            $ref['showed_cols'] = Get::req('cols', DOTY_MIXED, array());

        function is_showed($which, &$arr)
        {
            if(isset($arr['showed_cols']))
                return in_array($which, $arr['showed_cols']);
            else
                return false;
        }

        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter']))
        {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        cout(	$this->page_title
            .Form::openForm('user_report_rows_courses', $jump_url)
            .Form::getHidden('update_tempdata', 'update_tempdata', 1)
            .Form::getHidden('is_updating', 'is_updating', 1)

            .Form::getOpenFieldset($lang->def('_COURSE_FIELDS'), 'fieldset_course_fields')
            .Form::getCheckBox($lang->def('_COURSE_CODE'), 'col_sel_coursecode', 'cols[]', '_CODE_COURSE', is_showed('_CODE_COURSE', $ref))
            //.Form::getCheckBox($glang->def('_COURSE_NAME'), 'col_sel_coursename', 'cols[]', '_NAME_COURSE', is_showed('_NAME_COURSE', $ref))
            .Form::getCheckBox($glang->def('_CATEGORY'), 'col_sel_category', 'cols[]', '_COURSE_CATEGORY', is_showed('_COURSE_CATEGORY', $ref))
            .Form::getCheckBox($glang->def('_STATUS'), 'col_sel_status', 'cols[]', '_COURSESTATUS', is_showed('_COURSESTATUS', $ref))
            .Form::getCheckBox($glang->def('_CREATION_DATE', 'report'), 'col_sel_publication', 'cols[]', '_PUBLICATION_DATE', is_showed('_PUBLICATION_DATE', $ref))
            .Form::getCloseFieldset()

            .Form::getOpenFieldset($lang->def('_LEVEL_6', 'levels'), 'fieldset_course_fields')
            .Form::getCheckBox($lang->def('_MAX_SCORE'), 'col_hight_vote', 'cols[]', '_HIGH_VOTE', is_showed('_HIGH_VOTE', $ref))
            .Form::getCheckBox($lang->def('_MIN_SCORE'), 'col_less_vote', 'cols[]', '_LESS_VOTE', is_showed('_LESS_VOTE', $ref))
            .Form::getCheckBox($lang->def('_SCORE'), 'show_medium_vote', 'cols[]', '_MEDIUM_VOTE', is_showed('_MEDIUM_VOTE', $ref))
            .Form::getCloseFieldset()
            .Form::openButtonSpace()
            .Form::getBreakRow()
            .Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
            .Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK'))
            .Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW'))
            .Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO'))
            .Form::closeButtonSpace()
            .Form::closeForm()
            .'</div>');
    }

    //Valutazione corsi
    function get_course_val_filter()
    {
        $back_url = $this->back_url;
        $jump_url = $this->jump_url;
        $next_url = $this->next_url;

        require_once(_base_.'/lib/lib.form.php');
        require_once(_adm_.'/lib/lib.directory.php');
        require_once(_adm_.'/class.module/class.directory.php');
        require_once(_lms_.'/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $glang =& DoceboLanguage::createInstance('admin_course_managment', 'lms');

        if (!isset($_SESSION['report_tempdata']['columns_filter']))
        {
            $_SESSION['report_tempdata']['columns_filter'] = array();
        }

        $ref = &$_SESSION['report_tempdata']['columns_filter'];

        if(Get::req('is_updating', DOTY_INT, 0)>0)
            $ref['showed_cols'] = Get::req('cols', DOTY_MIXED, array());

        function is_showed($which, &$arr)
        {
            if(isset($arr['showed_cols']))
                return in_array($which, $arr['showed_cols']);
            else
                return false;
        }

        if (isset($_POST['import_filter']) || isset($_POST['show_filter']) || isset($_POST['pre_filter']))
        {
            $temp_url = $next_url;
            if (isset($_POST['pre_filter'])) $temp_url.='&show=1&nosave=1';
            if (isset($_POST['show_filter'])) $temp_url.='&show=1';
            Util::jump_to($temp_url);
        }

        cout(	$this->page_title
            .Form::openForm('user_report_rows_courses', $jump_url)
            .Form::getHidden('update_tempdata', 'update_tempdata', 1)
            .Form::getHidden('is_updating', 'is_updating', 1)

            .Form::getOpenFieldset($lang->def('_COURSE_FIELDS'), 'fieldset_course_fields')
            .Form::getCheckBox($lang->def('_COURSE_CODE'), 'col_sel_coursecode', 'cols[]', '_CODE_COURSE', is_showed('_CODE_COURSE', $ref))
            //.Form::getCheckBox($glang->def('_COURSE_NAME'), 'col_sel_coursename', 'cols[]', '_NAME_COURSE', is_showed('_NAME_COURSE', $ref))
            .Form::getCheckBox($glang->def('_CATEGORY'), 'col_sel_category', 'cols[]', '_COURSE_CATEGORY', is_showed('_COURSE_CATEGORY', $ref))
            .Form::getCheckBox($glang->def('_STATUS'), 'col_sel_status', 'cols[]', '_COURSESTATUS', is_showed('_COURSESTATUS', $ref))
            .Form::getCheckBox($glang->def('_CREATION_DATE', 'report'), 'col_sel_publication', 'cols[]', '_PUBLICATION_DATE', is_showed('_PUBLICATION_DATE', $ref))
            .Form::getCheckBox($glang->def('_LABEL'), 'col_sel_label', 'cols[]', '_COURSELABEL', is_showed('_COURSELABEL', $ref))
            .Form::getCloseFieldset()

            .Form::getOpenFieldset($lang->def('_COURSE', 'levels'), 'fieldset_course_fields')
            .Form::getCheckBox($lang->def('_MAX_SCORE'), 'col_hight_vote', 'cols[]', '_HIGH_VOTE', is_showed('_HIGH_VOTE', $ref))
            .Form::getCheckBox($lang->def('_MIN_SCORE'), 'col_less_vote', 'cols[]', '_LESS_VOTE', is_showed('_LESS_VOTE', $ref))
            .Form::getCheckBox($lang->def('_SCORE'), 'show_medium_vote', 'cols[]', '_MEDIUM_VOTE', is_showed('_MEDIUM_VOTE', $ref))
            .Form::getCloseFieldset()
            .Form::openButtonSpace()
            .Form::getBreakRow()
            .Form::getButton('pre_filter', 'pre_filter', $lang->def('_SHOW_NOSAVE', 'report'))
            .Form::getButton('ok_filter', 'import_filter', $lang->def('_SAVE_BACK'))
            .Form::getButton('show_filter', 'show_filter', $lang->def('_SAVE_SHOW'))
            .Form::getButton('undo_filter', 'undo_filter', $lang->def('_UNDO'))
            .Form::closeButtonSpace()
            .Form::closeForm()
            .'</div>');
    }

    function show_report_user($report_data = NULL, $other = '')
    {
        if ($report_data===NULL)
            cout( $this->_get_users_query() );
        else
            cout( $this->_get_users_query('html', $report_data, $other) );
    }

    //Doc valutation
    function show_report_doc_val($report_data = NULL, $other = '')
    {
        if ($report_data===NULL)
            cout( $this->_get_doc_val_query() );
        else
            cout( $this->_get_doc_val_query('html', $report_data, $other) );
    }

    //Course valutation
    function show_report_course_val($report_data = NULL, $other = '')
    {
        if ($report_data===NULL)
            cout( $this->_get_course_val_query() );
        else
            cout( $this->_get_course_val_query('html', $report_data, $other) );
    }


    //Doc valutation
    function _get_doc_val_query($type='html', $report_data = NULL, $other = '')
    {
        checkPerm('view');
        $view_all_perm = checkPerm('view_all', true);

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if ($report_data==NULL)
            $ref =& $_SESSION['report_tempdata'];
        else
            $ref =& $report_data;

        $all_courses = $ref['rows_filter']['all_courses'];
        $course_selected =& $ref['rows_filter']['selected_courses'];

        if( !$view_all_perm ) {
            if ($all_courses == true) {
                // get all course
                $rs = sql_query("SELECT idCourse FROM %lms_course");
                $course_selected = array();
                while (list($id_course) = sql_fetch_row($rs)) { $course_selected[] = $id_course; }
            }
            //filter courses
            $all_courses = false;
            $admin_allcourses = false;
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            $course_selected = array_intersect($admin_courses['course'], $course_selected);
        }

        $query =	"SELECT c.idCourse, c.code, c.name, c.idCategory, c.status, c.create_date, p.id_quest, p.title_quest"
            ." FROM ".$GLOBALS['prefix_lms']."_course AS c"
            ." JOIN ".$GLOBALS['prefix_lms']."_organization AS o ON o.idCourse = c.idCourse"
            ." JOIN ".$GLOBALS['prefix_lms']."_pollquest AS p ON p.id_poll = o.idResource"
            ." WHERE o.objectType = 'poll'"
            ." AND p.type_quest = 'doc_valutation'"
            .($all_courses ? "" : " AND c.idCourse IN (".implode(',', $course_selected).")")
            ." GROUP BY c.idCourse, p.id_quest"
            ." ORDER BY c.idCourse, p.id_quest";

        $result = sql_query($query);

        $course_doc = array();
        $question_id = array();
        $question_answer = array();

        while($row = sql_fetch_assoc($result))
        {
            $course_doc[$row['idCourse'].'_'.$row['id_quest']] = $row;
            $question_id[$row['id_quest']] = $row['id_quest'];
        }

        //apply sub admin filters, if needed
        if( !$view_all_perm ) {
            //filter users
            require_once(_base_.'/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
        }

        if(empty($question_id))
            return $lang->def('_EMPTY_SELECTION');
        else
        {
            if( !$view_all_perm ) {
                $query =	"SELECT pta.id_quest, MIN(CAST(pta.more_info AS DECIMAL(65,30))) AS min_answer, MAX(CAST(pta.more_info AS DECIMAL(65,30))) AS max_answer, SUM(CAST(pta.more_info AS DECIMAL(65,30))) AS sum_answer, COUNT(*) AS num_answer"
                    ." FROM ".$GLOBALS['prefix_lms']."_polltrack_answer AS pta, ".$GLOBALS['prefix_lms']."_polltrack AS pt"
                    ." WHERE 1 AND pta.id_track = pt.id_track AND pt.id_user IN (".implode($ctrl_users , ',').") AND pta.id_quest IN (".implode(',', $question_id).")"
                    ." GROUP BY pta.id_quest";
            } else {
                $query =	"SELECT id_quest, MIN(CAST(more_info AS DECIMAL(65,30))) AS min_answer, MAX(CAST(more_info AS DECIMAL(65,30))) AS max_answer, SUM(CAST(more_info AS DECIMAL(65,30))) AS sum_answer, COUNT(*) AS num_answer"
                    ." FROM ".$GLOBALS['prefix_lms']."_polltrack_answer"
                    ." WHERE id_quest IN (".implode(',', $question_id).")"
                    ." GROUP BY id_quest";
            }

            $result = sql_query($query);

            while($row = sql_fetch_assoc($result))
            {
                $question_answer[$row['id_quest']]['min_value'] = (float)$row['min_answer'];
                $question_answer[$row['id_quest']]['max_value'] = (float)$row['max_answer'];
                $question_answer[$row['id_quest']]['everage_value'] = number_format(($row['sum_answer'] / $row['num_answer']), 2);
            }

            return $this->_printTable_doc($type, $course_doc, $question_answer, $ref['columns_filter']['showed_cols']);
        }
    }

    //Course valutation
    function _get_course_val_query($type='html', $report_data = NULL, $other = '')
    {
        checkPerm('view');
        $view_all_perm = checkPerm('view_all', true);

        $lang =& DoceboLanguage::createInstance('report', 'framework');

        if ($report_data==NULL)
            $ref =& $_SESSION['report_tempdata'];
        else
            $ref =& $report_data;

        $all_courses = $ref['rows_filter']['all_courses'];
        $course_selected =& $ref['rows_filter']['selected_courses'];

        if( !$view_all_perm ) {
            if ($all_courses == true) {
                // get all course
                $rs = sql_query("SELECT idCourse FROM %lms_course");
                $course_selected = array();
                while (list($id_course) = sql_fetch_row($rs)) { $course_selected[] = $id_course; }
            }
            //filter courses
            $all_courses = false;
            $admin_allcourses = false;
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            $course_selected = array_intersect($admin_courses['course'], $course_selected);
        }

        $query =	"SELECT c.idCourse, c.code, c.name, c.idCategory, c.status, c.create_date, p.id_quest"
            ." FROM ".$GLOBALS['prefix_lms']."_course AS c"
            ." JOIN ".$GLOBALS['prefix_lms']."_organization AS o ON o.idCourse = c.idCourse"
            ." JOIN ".$GLOBALS['prefix_lms']."_pollquest AS p ON p.id_poll = o.idResource"
            ." WHERE o.objectType = 'poll'"
            ." AND p.type_quest = 'course_valutation'"
            .($all_courses ? "" : " AND c.idCourse IN (".implode(',', $course_selected).")");

        $result = sql_query($query);

        $course_doc = array();
        $question_id = array();
        $question_answer = array();

        while($row = sql_fetch_assoc($result))
        {
            $course_doc[$row['idCourse']] = $row;
            $question_id[$row['id_quest']] = $row['id_quest'];
        }

        //apply sub admin filters, if needed
        if( !$view_all_perm ) {
            //filter users
            require_once(_base_.'/lib/lib.preference.php');
            $ctrlManager = new ControllerPreference();
            $ctrl_users = $ctrlManager->getUsers(Docebo::user()->getIdST());
        }


        if(empty($question_id))
            return $lang->def('_EMPTY_SELECTION');
        else
        {
            if( !$view_all_perm ) {
                $query =	"SELECT pta.id_quest, MIN(CAST(pta.more_info AS DECIMAL(65,30))) AS min_answer, MAX(CAST(pta.more_info AS DECIMAL(65,30))) AS max_answer, SUM(CAST(pta.more_info AS DECIMAL(65,30))) AS sum_answer, COUNT(*) AS num_answer"
                    ." FROM ".$GLOBALS['prefix_lms']."_polltrack_answer AS pta, ".$GLOBALS['prefix_lms']."_polltrack AS pt"
                    ." WHERE 1 AND pta.id_track = pt.id_track AND pt.id_user IN (".implode($ctrl_users , ',').") AND pta.id_quest IN (".implode(',', $question_id).")"
                    ." GROUP BY pta.id_quest";
            } else {
                $query =	"SELECT id_quest, MIN(CAST(more_info AS DECIMAL(65,30))) AS min_answer, MAX(CAST(more_info AS DECIMAL(65,30))) AS max_answer, SUM(CAST(more_info AS DECIMAL(65,30))) AS sum_answer, COUNT(*) AS num_answer"
                    ." FROM ".$GLOBALS['prefix_lms']."_polltrack_answer"
                    ." WHERE id_quest IN (".implode(',', $question_id).")"
                    ." GROUP BY id_quest";
            }

            $result = sql_query($query);

            while($row = sql_fetch_assoc($result))
            {
                $question_answer[$row['id_quest']]['min_value'] = (float)$row['min_answer'];
                $question_answer[$row['id_quest']]['max_value'] = (float)$row['max_answer'];
                $question_answer[$row['id_quest']]['everage_value'] = number_format(($row['sum_answer'] / $row['num_answer']), 2);
            }

            return $this->_printTable_course($type, $course_doc, $question_answer, $ref['columns_filter']['showed_cols']);
        }
    }


    function _get_users_query($type='html', $report_data = NULL, $other = '') {

        checkPerm('view');
        $view_all_perm = checkPerm('view_all', true);

        //$jump_url, $alluser, $org_chart_subdivision, $start_time, $end_time
        if ($report_data==NULL) {
            $ref =& $_SESSION['report_tempdata'];
        } else {
            $ref =& $report_data;
        }

        $all_courses = $ref['rows_filter']['all_courses'];
        $course_selected =& $ref['rows_filter']['selected_courses'];

        $time_range 			= $ref['columns_filter']['time_belt']['time_range'];
        $start_time 			= $ref['columns_filter']['time_belt']['start_date'];
        $end_time 				= $ref['columns_filter']['time_belt']['end_date'];
        $org_chart_subdivision 	= $ref['columns_filter']['org_chart_subdivision'];
        $filter_cols			= $ref['columns_filter']['showed_cols'];
        $show_percent     = (isset($ref['columns_filter']['show_percent']) ? (bool)$ref['columns_filter']['show_percent'] : true);

        $show_suspended   = (isset($ref['columns_filter']['show_suspended']) ? (bool)$ref['columns_filter']['show_suspended'] : false);
        $only_students    = (isset($ref['columns_filter']['only_students']) ? (bool)$ref['columns_filter']['only_students'] : false);
        $show_assessment  = (isset($ref['columns_filter']['show_assessment']) ? (bool)$ref['columns_filter']['show_assessment'] : false);

        $show_classrooms_editions = (isset($ref['columns_filter']['show_classrooms_editions']) ? (bool)$ref['columns_filter']['show_classrooms_editions'] : false);

        if($time_range != 0) {
            $start_time = date("Y-m-d H:i:s", time() - $time_range*24*3600);
            $end_time = date("Y-m-d H:i:s");
        } else {
            $start_time = $start_time;
            $end_time 	= $end_time;
        }
        $alluser = $ref['columns_filter']['all_users'];

        $output = '';

        $lang =& DoceboLanguage::createInstance('course', 'framework');

        require_once($GLOBALS['where_framework'].'/lib/lib.directory.php');
        require_once(_base_.'/lib/lib.userselector.php');

        $acl_man = new DoceboACLManager();
        $acl_man->include_suspended = TRUE;
        $course_man 	= new Man_Course();

        $user_level = Docebo::user()->getUserLevelId();

        if($alluser == 0) {
            $user_selected =& $acl_man->getAllUsersFromSelection($ref['columns_filter']['users']);
        } else {
            $user_selected =& $acl_man->getAllUsersIdst();
        }

        //apply filters for sub-admins
        if( !$view_all_perm ) {
            //filter users
            $alluser = 0;
            require_once(_base_.'/lib/lib.preference.php');
            $adminManager = new AdminPreference();
            $admin_users = $adminManager->getAdminUsers(Docebo::user()->getIdST());
            $admin_users = $acl_man->getAllUsersFromSelection($admin_users);
            $user_selected = array_intersect($user_selected, $admin_users);
            unset($admin_users);

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

        if($org_chart_subdivision == 1) {

            require_once($GLOBALS['where_framework'].'/lib/lib.orgchart.php');
            $org_man 	= new OrgChartManager();
            if($alluser == 1)
            {
                $user_level = Docebo::user()->getUserLevelId();

                if($user_level != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous())
                    $elem_selected = $user_selected;
                else
                    $elem_selected = $org_man->getAllGroupIdFolder();
            } else {
                $elem_selected = $user_selected;
            }
            $org_name = $org_man->getFolderFormIdst($elem_selected);

            if( $user_level != ADMIN_GROUP_GODADMIN && !Docebo::user()->isAnonymous() ) {
                require_once(_base_.'/lib/lib.preference.php');
                $adminManager = new AdminPreference();
                $admin_tree = $adminManager->getAdminTree(Docebo::user()->getIdST());

                $org_name_temp = $org_name;
                $org_name  = array();
                foreach($org_name_temp as $id => $value) {

                    if(isset($admin_tree[$id])) $org_name[$id] = $value;
                }
            }

        } else {
            $elem_selected = array();
        }

        if(empty($user_selected)) {
            //no users to display
            $GLOBALS['page']->add($lang->def('_NULL_SELECTION'), 'content');
            return;
        }

        // Retrieve all the course
        $id_courses = array();
        if (!$show_classrooms_editions) {
            $q_courses = "SELECT c.idCourse, c.code, c.name, c.description, c.course_type "
                ." FROM %lms_course As c "
                .($show_assessment ? "" : " WHERE c.course_type <> 'assessment' ")
                ." ORDER BY c.code, c.name";
            $r_courses = sql_query($q_courses);
            while(list($id, $code, $name, $description, $course_type) = sql_fetch_row($r_courses)) {
                $id_courses[$id] = array(
                    'id_course' => $id,
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                    'course_type' => $course_type
                );
            }
        } else {
            $q_courses = "SELECT c.idCourse, c.code, c.name, c.description, c.course_type, d.id_date "
                ." FROM %lms_course As c LEFT JOIN %lms_course_date AS d ON (c.idCourse = d.id_course) "
                .($show_assessment ? "" : " WHERE c.course_type <> 'assessment' ")
                ." ORDER BY c.code, c.name, d.code, d.name";
            $r_courses = sql_query($q_courses);
            while(list($id, $code, $name, $description, $course_type, $id_date) = sql_fetch_row($r_courses)) {
                $index = $course_type=='classroom' ? $id.'_'.$id_date : $id;
                $id_courses[$index] = array(
                    'id_course' => $id,
                    'code' => $code,
                    'name' => $name,
                    'description' => $description,
                    'course_type' => $course_type
                );
            }
        }
        if(empty($id_courses)) {
            //no courses on the platform
            cout($lang->def('_NULL_COURSE_SELECTION'), 'content');
            return;
        }
        $id_coursedates = array();

        $date_now = Format::date(date("Y-m-d H:i:s"));

        $classrooms_editions_info = array();
        if ($show_classrooms_editions) {
            //retrieve classrooms info
            $query = "SELECT d.*, MIN(dd.date_begin) AS date_1, MAX(dd.date_end) AS date_2 "
                ." FROM %lms_course_date AS d JOIN %lms_course_date_day AS dd ON (d.id_date = dd.id_date) "
                .(!$all_courses ? " AND d.id_course IN (".implode(',', $course_selected).") " : "")
                ." GROUP BY dd.id_date";
            $res = sql_query($query);
            while ($obj = sql_fetch_object($res)) {
                $classrooms_editions_info['classrooms'][$obj->id_date] = $obj;
            }

            //retrieve editions info
            //TO DO ...
        }

        if (!$show_classrooms_editions) {

            if($org_chart_subdivision == 0) {
                $query_course_user = "SELECT cu.idUser, cu.idCourse, cu.date_first_access, cu.date_complete, cu.status "
                    ." FROM %lms_courseuser AS cu JOIN %adm_user AS u ON (cu.idUser = u.idst) "
                    ." WHERE cu.idUser IN ( ".implode(',', $user_selected)." ) "
                    .($all_courses ? "" : "AND cu.idCourse IN (".implode(',', $course_selected).") ")
                    .($show_suspended ? "" : " AND u.valid = 1 ")
                    .($only_students ? " AND cu.level = 3 " : "");
                if($start_time != '' && $start_time != '0000-00-00') $query_course_user .= " AND greatest(coalesce(cu.date_complete, 0), coalesce(cu.date_first_access, 0), coalesce(cu.date_inscr), 0) >= '".$start_time."' ";
                if($end_time != '' && $end_time != '0000-00-00') $query_course_user .= " AND greatest(coalesce(cu.date_complete, 0), coalesce(cu.date_first_access, 0), coalesce(cu.date_inscr), 0) <= '".$end_time."'";

                $num_iscr 		= array();
                $num_nobegin 	= array();
                $num_itinere 	= array();
                $num_end 		= array();
                $time_in_course = array();
                $effective_user = array();

                $re_course_user = sql_query($query_course_user);
                while(list($id_u, $id_c, $fisrt_access, $date_complete, $status) = sql_fetch_row($re_course_user)) {

                    if(isset($num_iscr[$id_c])) ++$num_iscr[$id_c];
                    else $num_iscr[$id_c] = 1;
                    switch($status) {
                        case _CUS_CONFIRMED : {};break;
                        case _CUS_SUSPEND : {};break;
                        case _CUS_SUBSCRIBED : {
                            if(isset($num_nobegin[$id_c])) ++$num_nobegin[$id_c];
                            else $num_nobegin[$id_c] = 1;
                        };break;
                        case _CUS_BEGIN : {
                            if(isset($num_itinere[$id_c])) ++$num_itinere[$id_c];
                            else $num_itinere[$id_c] = 1;
                        };break;
                        case _CUS_END : {
                            if(isset($num_end[$id_c])) ++$num_end[$id_c];
                            else $num_end[$id_c] = 1;
                        };break;
                    }

                    $effective_user[] = $id_u;
                }
                if(!empty($effective_user)) {

                    $query_time = "
						SELECT idCourse, SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime))
						FROM %lms_tracksession
						WHERE  idUser IN ( ".implode(',', $effective_user)." )  ";
                    if($start_time != '' && $start_time != '0000-00-00') $query_time .= " AND enterTime >= '".$start_time."' ";
                    if($end_time != '' && $end_time != '0000-00-00') $query_time .= " AND enterTime <= '".$end_time."' ";
                    $query_time .= " GROUP BY idCourse ";

                    $re_time = sql_query($query_time);

                    while(list($id_c, $time_num) = sql_fetch_row($re_time)) {

                        $time_in_course[$id_c] = $time_num;
                    }
                }

                $output .= $this->_printTable_users(
                    $type,
                    $acl_man,
                    $id_courses,
                    $num_iscr,
                    $num_nobegin,
                    $num_itinere,
                    $num_end,
                    $time_in_course,
                    $filter_cols,
                    $show_percent,
                    $show_classrooms_editions,
                    $classrooms_editions_info
                );

            } else {

                reset($org_name);
                while(list($idst_group, $folder_name) = each($org_name)) {

                    if ($first) {
                        $first = FALSE;
                    } else {
                        $output .= '<br /><br /><br />';
                    }
                    $output .= '<div class="datasummary">'
                        .'<b>'.$lang->def('_FOLDER_NAME').' :</b> '.$folder_name['name']
                        .( $folder_name['type_of_folder'] == ORG_CHART_WITH_DESCENDANTS ? ' ('.$lang->def('_WITH_DESCENDANTS').')' : '' ).'<br />';
                    if(($start_time != '' && $start_time != '0000-00-00') || ($end_time != '' && $end_time != '0000-00-00')) {

                        $output .= '<b>'.$lang->def('_TIME_BELT_2').' :</b> '
                            .( $start_time != '' && $start_time != '0000-00-00'
                                ? ' <b>'.$lang->def('_START_TIME').' </b>'.Format::date($start_time, 'date')
                                : '' )
                            .( $end_time != '' && $end_time != '0000-00-00'
                                ? ' <b>'.$lang->def('_TO').' </b>'.Format::date($end_time, 'date')
                                : '' )
                            .'<br />';
                    }


                    $group_user = $acl_man->getGroupAllUser($idst_group);
                    $query_course_user = "SELECT cu.idUser, cu.idCourse, cu.date_first_access, cu.date_complete, cu.status "
                        ." FROM %lms_courseuser AS cu JOIN %adm_user AS u ON (cu.idUser = u.idst) "
                        ." WHERE cu.idUser IN ( ".implode(',', $group_user)." ) "
                        .($all_courses ? "" : "AND cu.idCourse IN (".implode(',', $course_selected).") ")
                        .($show_suspended ? "" : " AND u.valid = 1 ")
                        .($only_students ? " AND cu.level = 3 " : "");
                    if($start_time != '' && $start_time != '0000-00-00') $query_course_user .= " AND cu.date_complete >= '".$start_time."' ";
                    if($end_time != '' && $end_time != '0000-00-00') $query_course_user .= " AND cu.date_complete <= '".$end_time."'  AND cu.level='3'";

                    $num_iscr 		= array();
                    $num_nobegin 	= array();
                    $num_itinere 	= array();
                    $num_end 		= array();
                    $time_in_course = array();
                    $effective_user = array();

                    $re_course_user = sql_query($query_course_user);
                    while(list($id_u, $id_c, $fisrt_access, $date_complete) = sql_fetch_row($re_course_user)) {

                        if(isset($num_iscr[$id_c])) ++$num_iscr[$id_c];
                        else $num_iscr[$id_c] = 1;

                        if($fisrt_access === NULL) {
                            //never enter
                            if(isset($num_nobegin[$id_c])) ++$num_nobegin[$id_c];
                            else $num_nobegin[$id_c] = 1;
                        } elseif($date_complete === NULL) {
                            //enter
                            if(isset($num_itinere[$id_c])) ++$num_itinere[$id_c];
                            else $num_itinere[$id_c] = 1;
                        } else {
                            //complete
                            if(isset($num_end[$id_c])) ++$num_end[$id_c];
                            else $num_end[$id_c] = 1;
                        }
                        $effective_user[] = $id_u;
                    }
                    if(!empty($group_user)) {

                        $query_time = "
						SELECT idCourse, SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime))
						FROM %lms_tracksession
						WHERE  idUser IN ( ".implode(',', $group_user)." )  ";
                        if($start_time != '' && $start_time != '0000-00-00') $query_time .= " AND enterTime >= '".$start_time."' ";
                        if($end_time != '' && $end_time != '0000-00-00') $query_time .= " AND enterTime <= '".$end_time."' ";
                        $query_time .= " GROUP BY idCourse ";

                        $re_time = sql_query($query_time);
                        while(list($id_c, $time_num) = sql_fetch_row($re_time)) {

                            $time_in_course[$id_c] = $time_num;
                        }
                    }
                    reset($id_courses);

                    $output .= $this->_printTable_users(
                        $type,
                        $acl_man,
                        $id_courses,
                        $num_iscr ,
                        $num_nobegin,
                        $num_itinere,
                        $num_end,
                        $time_in_course,
                        $filter_cols,
                        $show_percent,
                        $show_classrooms_editions,
                        $classrooms_editions_info
                    );

                }
            }

        } else {


            //check classrooms and editions

            if($org_chart_subdivision == 0) {

                $query_course_user = "SELECT cu.idUser, cu.idCourse, cu.date_first_access, cu.date_complete, cu.status, c.course_type, d.id_date "
                    ." FROM (%lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user AS u ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst)) "
                    ." LEFT JOIN (%lms_course_date AS d JOIN %lms_course_date_user AS du ON (du.id_date=d.id_date)) "
                    ." ON (du.id_user = cu.idUser AND d.id_course = cu.idCourse) "
                    ." WHERE cu.idUser IN ( ".implode(',', $user_selected)." ) "
                    .($all_courses ? "" : "AND cu.idCourse IN (".implode(',', $course_selected).")")
                    .($show_suspended ? "" : " AND u.valid = 1 ")
                    .($only_students ? " AND cu.level = 3 " : "");;
                if($start_time != '' && $start_time != '0000-00-00') $query_course_user .= " AND cu.date_complete >= '".$start_time."' ";
                if($end_time != '' && $end_time != '0000-00-00') $query_course_user .= " AND cu.date_complete <= '".$end_time."'";

                $num_iscr 		= array();
                $num_nobegin 	= array();
                $num_itinere 	= array();
                $num_end 		= array();
                $time_in_course = array();
                $effective_user = array();

                $re_course_user = sql_query($query_course_user);
                while(list($id_u, $id_c, $fisrt_access, $date_complete, $status, $course_type, $id_date) = sql_fetch_row($re_course_user)) {

                    $index = $course_type == 'classroom' ? $id_c.'_'.$id_date : $id_c;

                    if(isset($num_iscr[$index])) ++$num_iscr[$index];
                    else $num_iscr[$index] = 1;
                    switch($status) {
                        case _CUS_CONFIRMED : {};break;
                        case _CUS_SUSPEND : {};break;
                        case _CUS_SUBSCRIBED : {
                            if(isset($num_nobegin[$index])) ++$num_nobegin[$index];
                            else $num_nobegin[$index] = 1;
                        };break;
                        case _CUS_BEGIN : {
                            if(isset($num_itinere[$index])) ++$num_itinere[$index];
                            else $num_itinere[$index] = 1;
                        };break;
                        case _CUS_END : {
                            if(isset($num_end[$index])) ++$num_end[$index];
                            else $num_end[$index] = 1;
                        };break;
                    }

                    if (!in_array($id_u, $effective_user)) $effective_user[] = $id_u;
                }

                if(!empty($effective_user)) {
                    $query_time = "SELECT idCourse, SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)) "
                        ." FROM %lms_tracksession WHERE  idUser IN ( ".implode(',', $effective_user)." ) ";
                    if ($start_time != '' && $start_time != '0000-00-00') $query_time .= " AND enterTime >= '".$start_time."' ";
                    if ($end_time != '' && $end_time != '0000-00-00') $query_time .= " AND enterTime <= '".$end_time."' ";
                    $query_time .= " GROUP BY idCourse ";
                    $re_time = sql_query($query_time);
                    while(list($id_c, $time_num) = sql_fetch_row($re_time)) {
                        $time_in_course[$id_c] = $time_num;
                    }
                }

                $output .= $this->_printTable_users(
                    $type,
                    $acl_man,
                    $id_courses,
                    $num_iscr ,
                    $num_nobegin,
                    $num_itinere,
                    $num_end,
                    $time_in_course,
                    $filter_cols,
                    $show_percent,
                    $show_classrooms_editions,
                    $classrooms_editions_info
                );

            } else {

                $first = true;
                reset($org_name);
                while(list($idst_group, $folder_name) = each($org_name)) {

                    if ($first) {
                        $first = FALSE;
                    } else {
                        $output .= '<br /><br /><br />';
                    }
                    $output .= '<div class="datasummary">'
                        .'<b>'.$lang->def('_FOLDER_NAME').' :</b> '.$folder_name['name']
                        .( $folder_name['type_of_folder'] == ORG_CHART_WITH_DESCENDANTS ? ' ('.$lang->def('_WITH_DESCENDANTS').')' : '' ).'<br />';
                    if(($start_time != '' && $start_time != '0000-00-00') || ($end_time != '' && $end_time != '0000-00-00')) {

                        $output .= '<b>'.$lang->def('_TIME_BELT_2').' :</b> '
                            .( $start_time != '' && $start_time != '0000-00-00'
                                ? ' <b>'.$lang->def('_START_TIME').' </b>'.Format::date($start_time, 'date')
                                : '' )
                            .( $end_time != '' && $end_time != '0000-00-00'
                                ? ' <b>'.$lang->def('_TO').' </b>'.Format::date($end_time, 'date')
                                : '' )
                            .'<br />';
                    }


                    $group_user = $acl_man->getGroupAllUser($idst_group);

                    $query_course_user = "SELECT cu.idUser, cu.idCourse, cu.date_first_access, cu.date_complete, cu.status, c.course_type, d.id_date "
                        ." FROM (%lms_courseuser AS cu JOIN %lms_course AS c JOIN %adm_user AS u ON (cu.idCourse = c.idCourse AND cu.idUser = u.idst)) "
                        ." LEFT JOIN (%lms_course_date AS d JOIN %lms_course_date_user AS du ON (du.id_date=d.id_date)) "
                        ." ON (du.id_user = cu.idUser AND d.id_course = cu.idCourse) "
                        ." WHERE cu.idUser IN ( ".implode(',', $group_user)." ) "
                        .($all_courses ? "" : "AND cu.idCourse IN (".implode(',', $course_selected).")")
                        .($show_suspended ? "" : " AND u.valid = 1 ")
                        .($only_students ? " AND cu.level = 3 " : "");;
                    if($start_time != '' && $start_time != '0000-00-00') $query_course_user .= " AND cu.date_complete >= '".$start_time."' ";
                    if($end_time != '' && $end_time != '0000-00-00') $query_course_user .= " AND cu.date_complete <= '".$end_time."'";

                    $num_iscr 		= array();
                    $num_nobegin 	= array();
                    $num_itinere 	= array();
                    $num_end 		= array();
                    $time_in_course = array();
                    $effective_user = array();

                    //$re_course_user = sql_query($query_course_user);
                    //while(list($id_u, $id_c, $fisrt_access, $date_complete) = sql_fetch_row($re_course_user)) {
                    $re_course_user = sql_query($query_course_user);
                    while(list($id_u, $id_c, $fisrt_access, $date_complete, $status, $course_type, $id_date) = sql_fetch_row($re_course_user)) {

                        $index = $course_type == 'classroom' ? $id_c.'_'.$id_date : $id_c;

                        if(isset($num_iscr[$index])) ++$num_iscr[$index];
                        else $num_iscr[$index] = 1;
                        if($fisrt_access === NULL) {
                            //never enter
                            if(isset($num_nobegin[$index])) ++$num_nobegin[$index];
                            else $num_nobegin[$index] = 1;
                        } elseif($date_complete === NULL) {
                            //enter
                            if(isset($num_itinere[$index])) ++$num_itinere[$index];
                            else $num_itinere[$index] = 1;
                        } else {
                            //complete
                            if(isset($num_end[$index])) ++$num_end[$index];
                            else $num_end[$index] = 1;
                        }

                        if (!in_array($id_u, $effective_user)) $effective_user[] = $id_u;
                    }

                    if (!empty($group_user)) {
                        $query_time = "SELECT idCourse, SUM(UNIX_TIMESTAMP(lastTime) - UNIX_TIMESTAMP(enterTime)) "
                            ." FROM %lms_tracksession WHERE  idUser IN ( ".implode(',', $group_user)." ) ";
                        if ($start_time != '' && $start_time != '0000-00-00') $query_time .= " AND enterTime >= '".$start_time."' ";
                        if ($end_time != '' && $end_time != '0000-00-00') $query_time .= " AND enterTime <= '".$end_time."' ";
                        $query_time .= " GROUP BY idCourse ";
                        $re_time = sql_query($query_time);
                        while(list($id_c, $time_num) = sql_fetch_row($re_time)) {
                            $time_in_course[$id_c] = $time_num;
                        }
                    }

                    reset($id_courses);

                    $output .= $this->_printTable_users(
                        $type,
                        $acl_man,
                        $id_courses,
                        $num_iscr ,
                        $num_nobegin,
                        $num_itinere,
                        $num_end,
                        $time_in_course,
                        $filter_cols,
                        $show_percent,
                        $show_classrooms_editions,
                        $classrooms_editions_info
                    );

                }
            }

        }

        return $output;
    }


    protected function _translateCourseType($ctype) {
        $output = "";
        switch ($ctype) {
            case 'elearning': $output = Lang::t('_ELEARNING', 'standard'); break;
            case 'classroom': $output = Lang::t('_CLASSROOM', 'standard'); break;
            case 'assessment': $output = Lang::t('_ASSESSMENT', 'standard'); break;
        }
        return $output;
    }


    function _printTable_users(
        $type,
        &$acl_man,
        &$id_courses,
        &$num_iscr ,
        &$num_nobegin,
        &$num_itinere,
        &$num_end,
        &$time_in_course,
        $filter_cols,
        $show_percent,
        $show_classrooms_editions,
        &$classrooms_editions_info) {
        require_once(_lms_.'/admin/modules/report/report_tableprinter.php');
        $buffer = new ReportTablePrinter($type);

        require_once(_lms_.'/admin/models/LabelAlms.php');
        $label_model = new LabelAlms();

        $output = '';

        require_once($GLOBALS['where_lms'].'/lib/lib.course.php');

        $lang =& DoceboLanguage::createInstance('course', 'lms');
        $course_lang =& DoceboLanguage::createInstance('course', 'lms');
        $rg_lang =& DoceboLanguage::createInstance('report', 'framework');

        $colspan_course = 0;
        if(in_array('_CODE_COURSE', $filter_cols)) $colspan_course++;
        $colspan_course++;
        if(in_array('_COURSE_CATEGORY', $filter_cols)) $colspan_course++;
        if(in_array('_COURSESTATUS', $filter_cols)) $colspan_course++;

        if(in_array('_COURSECATALOGUE', $filter_cols)) $colspan_course++;
        if(in_array('_PUBLICATION_DATE', $filter_cols)) $colspan_course++;
        if(in_array('_COURSELABEL', $filter_cols)) $colspan_course++;

        if(in_array('_LANGUAGE', $filter_cols)) $colspan_course++;
        if(in_array('_DIFFICULT', $filter_cols)) $colspan_course++;
        if(in_array('_DATE_BEGIN', $filter_cols)) $colspan_course++;
        if(in_array('_DATE_END', $filter_cols)) $colspan_course++;
        if(in_array('_TIME_BEGIN', $filter_cols)) $colspan_course++;
        if(in_array('_TIME_END', $filter_cols)) $colspan_course++;
        if(in_array('_MAX_NUM_SUBSCRIBED', $filter_cols)) $colspan_course++;
        if(in_array('_MIN_NUM_SUBSCRIBED', $filter_cols)) $colspan_course++;
        if(in_array('_CREDITS', $filter_cols)) $colspan_course++;
        if(in_array('_PRICE', $filter_cols)) $colspan_course++;
        if(in_array('_ADVANCE', $filter_cols)) $colspan_course++;
        if(in_array('_COURSE_TYPE', $filter_cols)) $colspan_course++;
        if(in_array('_AUTOREGISTRATION_CODE', $filter_cols)) $colspan_course++;

        $colspan_classrooms_editions = 0;
        if ($show_classrooms_editions) {
            if (in_array('_TH_CLASSROOM_CODE', $filter_cols)) $colspan_classrooms_editions++;
            if (in_array('_TH_CLASSROOM_NAME', $filter_cols)) $colspan_classrooms_editions++;
            if (in_array('_TH_CLASSROOM_MIN_DATE', $filter_cols)) $colspan_classrooms_editions++;
            if (in_array('_TH_CLASSROOM_MAX_DATE', $filter_cols)) $colspan_classrooms_editions++;
        }

        $colspan_stats = 0;
        if(in_array('_INSCR', $filter_cols)) $colspan_stats++;
        if(in_array('_MUSTBEGIN', $filter_cols)) $colspan_stats += ($show_percent ? 2 : 1);
        if(in_array('_USER_STATUS_BEGIN', $filter_cols)) $colspan_stats += ($show_percent ? 2 : 1);
        if(in_array('_COMPLETECOURSE', $filter_cols)) $colspan_stats += ($show_percent ? 2 : 1);

        $buffer->openTable(/*$rg_lang->def('_STATISTICS'), $rg_lang->def('_RG_SUMMAMRY_MANAGMENT')*/);

        $th1 = array(
            array('colspan'=>($colspan_course + $colspan_classrooms_editions), 'style'=>'align-center', 'value'=>$lang->def('_COURSE')),
            array('colspan'=>$colspan_stats, 'style'=>'align-center', 'value'=>$rg_lang->def('_USERS'))
        );

        $th2 = array();
        $th2[] = array('colspan'=>$colspan_course, 'value'=>'');
        if ($show_classrooms_editions) $th2[] = array('colspan'=> $colspan_classrooms_editions, 'style'=>'align-center', 'value' => Lang::t('_CLASSROOM', 'classroom'));
        if (in_array('_INSCR', $filter_cols)) $th2[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_USER_STATUS_SUBS'));
        if (in_array('_MUSTBEGIN', $filter_cols)) $th2[] = array('colspan'=>($show_percent ? 2 : 1), 'style'=>'align-center', 'value'=>$rg_lang->def('_MUSTBEGIN'));
        if (in_array('_USER_STATUS_BEGIN', $filter_cols)) $th2[] = array('colspan'=>($show_percent ? 2 : 1), 'style'=>'align-center', 'value'=>$rg_lang->def('_USER_STATUS_BEGIN'));
        if (in_array('_COMPLETECOURSE', $filter_cols)) $th2[] = array('colspan'=>($show_percent ? 2 : 1), 'style'=>'align-center', 'value'=>$rg_lang->def('_COMPLETED'));

        $th3 = array();

        if (in_array('_CODE_COURSE', $filter_cols)) $th3[] = $lang->def('_COURSE_CODE');
        $th3[] = $lang->def('_COURSE_NAME');
        if (in_array('_COURSE_CATEGORY', $filter_cols)) $th3[] = $lang->def('_CATEGORY');
        if (in_array('_COURSESTATUS', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_STATUS'));

        if(in_array('_COURSECATALOGUE', $filter_cols)) $th3[] = $lang->def('_CATALOGUE');
        if(in_array('_PUBLICATION_DATE', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_CREATION_DATE'));

        if (in_array('_COURSELABEL', $filter_cols)) $th3[] = $lang->def('_LABEL');

        if (in_array('_LANGUAGE', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_COURSE_LANG_METHOD'));
        if (in_array('_DIFFICULT', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_DIFFICULTY'));
        if (in_array('_DATE_BEGIN', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_DATE_BEGIN'));
        if (in_array('_DATE_END', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_DATE_END'));
        if (in_array('_TIME_BEGIN', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_HOUR_BEGIN'));
        if (in_array('_TIME_END', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_HOUR_END'));
        if (in_array('_MAX_NUM_SUBSCRIBED', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_MAX_NUM_SUBSCRIBE'));
        if (in_array('_MIN_NUM_SUBSCRIBED', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_MIN_NUM_SUBSCRIBE'));
        if (in_array('_CREDITS', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>Lang::t('_CREDITS', 'standard'));
        if (in_array('_PRICE', $filter_cols)) $th3[] = $lang->def('_COURSE_PRIZE');
        if (in_array('_ADVANCE', $filter_cols)) $th3[] = $lang->def('_COURSE_ADVANCE');
        if (in_array('_COURSE_TYPE', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$lang->def('_COURSE_TYPE'));
        if (in_array('_AUTOREGISTRATION_CODE', $filter_cols)) $th3[] = $lang->def('_AUTOREGISTRATION_CODE');


        if ($show_classrooms_editions) {
            if (in_array('_TH_CLASSROOM_CODE', $filter_cols)) { $th3[] = Lang::t('_NAME', 'standard'); }
            if (in_array('_TH_CLASSROOM_NAME', $filter_cols)) { $th3[] = Lang::t('_CODE', 'standard'); }
            if (in_array('_TH_CLASSROOM_MIN_DATE', $filter_cols)) { $th3[] = array('style'=>'align-center', 'value'=>Lang::t('_DATE_BEGIN', 'standard')); }
            if (in_array('_TH_CLASSROOM_MAX_DATE', $filter_cols)) { $th3[] = array('style'=>'align-center', 'value'=>Lang::t('_DATE_END', 'standard')); }
        }


        if (in_array('_INSCR', $filter_cols)) $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_NUM'));
        if (in_array('_MUSTBEGIN', $filter_cols)) { $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_NUM','report')); if ($show_percent) $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_PERC')); }
        if (in_array('_USER_STATUS_BEGIN', $filter_cols)) { $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_NUM','report')); if ($show_percent) $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_PERC')); }
        if (in_array('_COMPLETECOURSE', $filter_cols)) { $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_NUM','report')); if ($show_percent) $th3[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_PERC')); }


        if (in_array('_TOTAL_SESSION', $filter_cols)) {
            $th1[] = array('style'=>'align-center', 'value'=>$rg_lang->def('_TOTAL_SESSION'));
            $th2[] = '';
            $th3[] = '';
        }

        $buffer->openHeader();
        $buffer->addHeader($th1);
        $buffer->addHeader($th2);
        $buffer->addHeader($th3);
        $buffer->closeHeader();

        $i = 0;
        $tot_iscr = $tot_itinere = $tot_nobegin = $tot_comple = '';
        $tot_perc_itinere = $tot_perc_nobegin = $tot_perc_comple = '';
        $total_time = 0;

        $array_status = array(	CST_PREPARATION => $lang->def('_CST_PREPARATION', 'course', 'lms'),
            CST_AVAILABLE 	=> $lang->def('_CST_AVAILABLE', 'course', 'lms'),
            CST_EFFECTIVE 	=> $lang->def('_CST_CONFIRMED', 'course', 'lms'),
            CST_CONCLUDED 	=> $lang->def('_CST_CONCLUDED', 'course', 'lms'),
            CST_CANCELLED 	=> $lang->def('_CST_CANCELLED', 'course', 'lms') );

        //extract course categories
        $query =	"SELECT idCategory, path"
            ." FROM ".$GLOBALS['prefix_lms']."_category";
        $result = sql_query($query);
        $array_category = array(0 => $lang->def('_NONE'));
        while(list($id_cat, $name_cat) = sql_fetch_row($result))
            $array_category[$id_cat] = substr($name_cat, 5, (strlen($name_cat)-5));//strrpos($name_cat, '/') + 1 );


        //extract course catalogues and relations
        $query =	"SELECT idCatalogue, name"
            ." FROM ".$GLOBALS['prefix_lms']."_catalogue";
        $result = sql_query($query);
        $array_catalogue = array();
        while(list($id_cat, $name_cat) = sql_fetch_row($result))
            $array_catalogue[$id_cat] = $name_cat;//strrpos($name_cat, '/') + 1 );


        $catalogue_entries = array();
        $query = "select * FROM ".$GLOBALS['prefix_lms']."_catalogue_entry "; //where idst_member in (...)
        $result = sql_query($query);
        while (list($idcat, $entry, $type) = sql_fetch_row($result)) {
            switch ($type) {
                case 'course': {
                    if (!isset($catalogue_entries[$entry])) $catalogue_entries[$entry] = array();
                    $catalogue_entries[$entry][] = $idcat;
                } break;

                case 'coursepath': {
                    //...
                } break;
            } //end switch
        }

        $difficult_trans = array(
            'veryeasy' 		=> Lang::t('_DIFFICULT_VERYEASY', 'standard'),
            'easy' 			=> Lang::t('_DIFFICULT_EASY', 'standard'),
            'medium' 		=> Lang::t('_DIFFICULT_MEDIUM', 'standard'),
            'difficult' 	=> Lang::t('_DIFFICULT_DIFFICULT', 'standard'),
            'verydifficult' => Lang::t('_DIFFICULT_VERYDIFFICULT', 'standard')
        );

        $course_man = new Man_Course();
        $buffer->openBody();

        while(list($index, $course_info) = each($id_courses) ) {

            $idc = $id_date = 0;
            if ($show_classrooms_editions) {
                if (isset($course_info['course_type']) && $course_info['course_type'] == 'classroom') {
                    list($idc, $id_date) = explode('_', $index);
                } else {
                    $idc = $index;
                }
            } else {
                $idc = $index;
            }

            $info_course = $course_man->getCourseInfo($idc);

            $code_c = $course_info['code'];
            $name_c = $course_info['name'];

            $_date_create = $info_course['create_date'] != '0000-00-00 00:00:00' && $info_course['create_date'] != ""
                ? Format::date($info_course['create_date'], 'datetime')
                : "";
            $_date_begin = $info_course['date_begin'] != '0000-00-00 00:00:00' && $info_course['date_begin'] != ""
                ? Format::date($info_course['date_begin'], 'datetime')
                : "";
            $_date_end = $info_course['date_end'] != '0000-00-00 00:00:00' && $info_course['date_end'] != ""
                ? Format::date($info_course['date_end'], 'datetime')
                : "";

            $trow = array();
            if (in_array('_CODE_COURSE', $filter_cols)) $trow[] = addslashes($code_c);
            $trow[] = addslashes($name_c);
            if (in_array('_COURSE_CATEGORY', $filter_cols)) $trow[] = $array_category[$info_course['idCategory']];
            if (in_array('_COURSESTATUS', $filter_cols)) $trow[] = (isset($array_status[$info_course['status']]) ? $array_status[$info_course['status']] : '');

            if (in_array('_COURSELABEL', $filter_cols)) {
                $course_label_id = $label_model->getCourseLabel($course_info['id_course']);
                if ($course_label_id > 0) {
                    $arr_course_label = $label_model->getLabelInfo($course_label_id);
                    $trow[] = $arr_course_label[getLanguage()][LABEL_TITLE];
                } else {
                    $trow[] = "";
                }
            }

            if(in_array('_COURSECATALOGUE', $filter_cols)) {
                $temp = array();
                if (isset($catalogue_entries[$info_course['idCourse']])) {
                    foreach ($catalogue_entries[$info_course['idCourse']] as $idcat)
                        $temp[] = $array_catalogue[$idcat];
                }
                $trow[] = implode(', ', $temp);
            }
            if(in_array('_PUBLICATION_DATE', $filter_cols)) $trow[] = array('style'=>'align-center', 'value'=>$_date_create);

            if (in_array('_LANGUAGE', $filter_cols)) $trow[] = $info_course['lang_code'];
            if (in_array('_DIFFICULT', $filter_cols)) $trow[] = isset($difficult_trans[$info_course['difficult']]) ? $difficult_trans[$info_course['difficult']] : "";
            if (in_array('_DATE_BEGIN', $filter_cols)) $trow[] = array('style'=>'align-center', 'value'=>$_date_begin);
            if (in_array('_DATE_END', $filter_cols)) $trow[] = array('style'=>'align-center', 'value'=>$_date_end);
            if (in_array('_TIME_BEGIN', $filter_cols)) $trow[] = ($info_course['hour_begin']<0 ? '' : $info_course['hour_begin']);
            if (in_array('_TIME_END', $filter_cols)) $trow[] = ($info_course['hour_end']<0 ? '' : $info_course['hour_end']);
            if (in_array('_MAX_NUM_SUBSCRIBED', $filter_cols)) $trow[] = ($info_course['max_num_subscribe'] ? $info_course['max_num_subscribe'] : '');
            if (in_array('_MIN_NUM_SUBSCRIBED', $filter_cols)) $trow[] = ($info_course['min_num_subscribe'] ? $info_course['min_num_subscribe'] : '');
            if (in_array('_CREDITS', $filter_cols)) $trow[] = (isset($info_course['credits']) ? $info_course['credits'] : '');
            if (in_array('_PRICE', $filter_cols)) $trow[] = ($info_course['prize'] != '' ? $info_course['prize'] : '0');
            if (in_array('_ADVANCE', $filter_cols)) $trow[] = ($info_course['advance'] != '' ? $info_course['advance'] : '0');
            if (in_array('_COURSE_TYPE', $filter_cols)) $trow[] = array('style'=>'align-center', 'value'=>$this->_translateCourseType($info_course['course_type']));
            if (in_array('_AUTOREGISTRATION_CODE', $filter_cols)) $trow[] = $info_course['autoregistration_code'];


            if ($show_classrooms_editions) {
                $e_code = $e_name = $date_1 = $date_2 = '';
                if ($id_date > 0 && isset($classrooms_editions_info['classrooms'][$id_date])) {
                    $e_code = $classrooms_editions_info['classrooms'][$id_date]->code;
                    $e_name = $classrooms_editions_info['classrooms'][$id_date]->name;
                    $date_1 = Format::date($classrooms_editions_info['classrooms'][$id_date]->date_1, 'datetime');
                    $date_2 = Format::date($classrooms_editions_info['classrooms'][$id_date]->date_2, 'datetime');
                }
                if (in_array('_TH_CLASSROOM_CODE', $filter_cols)) { $trow[] = $e_code; }
                if (in_array('_TH_CLASSROOM_NAME', $filter_cols)) { $trow[] = $e_name; }
                if (in_array('_TH_CLASSROOM_MIN_DATE', $filter_cols)) { $trow[] = array('style'=>'align-center', 'value'=>$date_1); }
                if (in_array('_TH_CLASSROOM_MAX_DATE', $filter_cols)) { $trow[] = array('style'=>'align-center', 'value'=>$date_2); }
            }


            if( isset($num_iscr[$index]) )
            {
                if(in_array('_INSCR', $filter_cols))
                {
                    $trow[] = array('style'=>'img-cell', 'value'=>$num_iscr[$index]);
                }
                $tot_iscr += $num_iscr[$index];

                //no begin course
                if(in_array('_MUSTBEGIN', $filter_cols))
                {
                    if(isset($num_nobegin[$index]))
                    {
                        $perc = (($num_nobegin[$index] / $num_iscr[$index])*100);
                        $tot_nobegin += $num_nobegin[$index];
                        $tot_perc_nobegin += $perc;

                        $trow[] = array('style'=>'img-cell', 'value'=>$num_nobegin[$index]);
                        if ($show_percent) $trow[] = array('style'=>'img-cell', 'value'=>number_format($perc , 2, '.', '').'%');
                    }
                    else
                    {
                        $trow[] = '';
                        if ($show_percent) $trow[] = '';
                    }
                }

                //begin
                if(in_array('_USER_STATUS_BEGIN', $filter_cols))
                {
                    if(isset($num_itinere[$index]))
                    {
                        $perc = (($num_itinere[$index] / $num_iscr[$index])*100);
                        $tot_itinere += $num_itinere[$index];
                        $tot_perc_itinere += $perc;

                        $trow[] = array('style'=>'img-cell', 'value'=>$num_itinere[$index]);
                        if ($show_percent) $trow[] = array('style'=>'img-cell', 'value'=>number_format($perc , 2, '.', '').'%');
                    }
                    else
                    {
                        $trow[] = array('style'=>'img-cell', 'value'=>'');
                        if ($show_percent) $trow[] = array('style'=>'img-cell', 'value'=>'');
                    }
                }

                //end course
                if(in_array('_COMPLETECOURSE', $filter_cols))
                {
                    if(isset($num_end[$index]))
                    {
                        $perc = (($num_end[$index] / $num_iscr[$index])*100);
                        $tot_comple += $num_end[$index];
                        $tot_perc_comple += $perc;

                        $trow[] = array('style'=>'img-cell', 'value'=>$num_end[$index]);
                        if ($show_percent) $trow[] = array('style'=>'img-cell', 'value'=>number_format($perc , 2, '.', '').'%');
                    }
                    else
                    {
                        $trow[] = array('style'=>'img-cell', 'value'=>'');
                        if ($show_percent) $trow[] = array('style'=>'img-cell', 'value'=>'');
                    }
                }

                // time in
                if(in_array('_TOTAL_SESSION', $filter_cols))
                {
                    if(isset($time_in_course[$idc]))
                    {

                        $total_time += $time_in_course[$idc];

                        $trow[] = array('style'=>'img-cell', 'value'=>( ((int)($time_in_course[$idc]/3600)).'h '
                            .substr('0'.((int)(($time_in_course[$idc]%3600)/60)),-2).'m '
                            .substr('0'.((int)($time_in_course[$idc]%60)),-2).'s ' ));
                    }
                    else
                    {
                        $trow[] = array('style'=>'img-cell', 'value'=>'');
                    }
                }
            }
            else
            {
                if(in_array('_INSCR', $filter_cols)) {
                    $trow[] = ''; }

                //no begin course
                if(in_array('_MUSTBEGIN', $filter_cols)) {
                    $trow[]='';
                    if ($show_percent) $trow[]='';
                }

                //begin
                if(in_array('_USER_STATUS_BEGIN', $filter_cols)) {
                    $trow[]='';
                    if ($show_percent) $trow[]='';
                }

                //end course
                if(in_array('_COMPLETECOURSE', $filter_cols)) {
                    $trow[]='';
                    if ($show_percent) $trow[]='';
                }

                // time in
                if(in_array('_TOTAL_SESSION', $filter_cols)){
                    $trow[] = ''; }

            }

            //print row
            if( isset($num_iscr[$index]) && $num_iscr[$index]) {
                $buffer->addLine($trow);
            } else $i--;
        }

        $buffer->closeBody();

        $tfoot = array( array('colspan'=>($colspan_course + $colspan_classrooms_editions), 'value'=>$lang->def('_TOTAL')) );

        if (in_array('_INSCR', $filter_cols)) $tfoot[] = $tot_iscr;
        if (in_array('_MUSTBEGIN', $filter_cols)) {
            $tfoot[] = $tot_nobegin;
            if ($show_percent) $tfoot[] = ( $tot_nobegin ? number_format(( ($tot_nobegin/$tot_iscr)*100 ), 2 , '.', '').'%' : 'n.d.' );
        }
        if (in_array('_USER_STATUS_BEGIN', $filter_cols)) {
            $tfoot[] = $tot_itinere;
            if ($show_percent) $tfoot[] = ( $tot_itinere ? number_format(($tot_itinere/$tot_iscr)*100, 2 , '.', '').'%' : 'n.d.' );
        }
        if (in_array('_COMPLETECOURSE', $filter_cols)) {
            $tfoot[] = $tot_comple;
            if ($show_percent) $tfoot[] = ( $tot_comple ? number_format(($tot_comple/$tot_iscr)*100, 2 , '.', '').'%' : 'n.d.');
        }
        if (in_array('_TOTAL_SESSION', $filter_cols)) {
            $tfoot[] = ((int)($total_time/3600)).'h '.substr('0'.((int)($total_time/60)),-2).'m '.substr('0'.((int)$total_time),-2).'s ';
        }

        $buffer->setFoot($tfoot);
        $buffer->closeTable();

        //return $output;
        return $buffer->get();
    }



    //Doc valutation
    function _printTable_doc($type, $course, $stats, $filter_cols)
    {
        require_once(_lms_.'/admin/modules/report/report_tableprinter.php');
        $buffer = new ReportTablePrinter($type);

        $output = '';

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $glang =& DoceboLanguage::createInstance('admin_course_managment', 'lms');

        $query =	"SELECT idCategory, path"
            ." FROM ".$GLOBALS['prefix_lms']."_category";

        $result = sql_query($query);

        $array_category = array(0 => $lang->def('_NONE'));

        while(list($id_cat, $name_cat) = sql_fetch_row($result))
            $array_category[$id_cat] = substr($name_cat, 5, (strlen($name_cat)-5));

        $array_status = array(
            CST_PREPARATION => $lang->def('_CST_PREPARATION', 'admin_course_managment', 'lms'),
            CST_AVAILABLE 	=> $glang->def('_CST_AVAILABLE'),
            CST_EFFECTIVE 	=> $glang->def('_CST_CONFIRMED'),
            CST_CONCLUDED 	=> $glang->def('_CST_CONCLUDED'),
            CST_CANCELLED 	=> $glang->def('_CST_CANCELLED'));

        $colspan_course = 1;
        if(in_array('_CODE_COURSE', $filter_cols)) $colspan_course++;
        $colspan_course++;
        if(in_array('_COURSE_CATEGORY', $filter_cols)) $colspan_course++;
        if(in_array('_COURSESTATUS', $filter_cols)) $colspan_course++;
        if(in_array('_PUBLICATION_DATE', $filter_cols)) $colspan_course++;

        $colspan_stats = 0;
        if(in_array('_HIGH_VOTE', $filter_cols)) $colspan_stats++;
        if(in_array('_LESS_VOTE', $filter_cols)) $colspan_stats++;
        if(in_array('_MEDIUM_VOTE', $filter_cols)) $colspan_stats++;

        $th1 = array(
            array('colspan'=>$colspan_course, 'value'=>$lang->def('_COURSE')),
            array('colspan'=>$colspan_stats,  'value'=>$lang->def('_DOC_STATS'))
        );

        $th2 = array();

        $th2[] = $glang->def('_QUEST_TEXT');
        if (in_array('_CODE_COURSE', $filter_cols)) $th2[] = $glang->def('_COURSE_CODE');
        $th2[] = $glang->def('_COURSE_NAME');
        if (in_array('_COURSE_CATEGORY', $filter_cols)) $th2[] = $glang->def('_CATEGORY');
        if (in_array('_COURSESTATUS', $filter_cols)) $th2[] = $glang->def('_STATUS');
        if (in_array('_PUBLICATION_DATE', $filter_cols)) $th2[] = $glang->def('_CREATION_DATE');

        if (in_array('_HIGH_VOTE', $filter_cols)) $th2[] = $lang->def('_MAX_SCORE');
        if (in_array('_LESS_VOTE', $filter_cols)) $th2[] = $lang->def('_MIN_SCORE');
        if (in_array('_MEDIUM_VOTE', $filter_cols)) $th2[] = $lang->def('_SCORE');

        $buffer->openTable($lang->def('_DOC_CAPTION'), $lang->def('_DOC_SUMMAMRY_MANAGMENT'));
        $buffer->openHeader();
        $buffer->addHeader($th1);
        $buffer->addHeader($th2);
        $buffer->closeHeader();
        $buffer->openBody();

        foreach($course as $course_info)
        {
            $trow = array();

            $trow[] = addslashes($course_info['title_quest']);
            if (in_array('_CODE_COURSE', $filter_cols)) $trow[] = addslashes($course_info['code']);
            $trow[] = addslashes($course_info['name']);
            if (in_array('_COURSE_CATEGORY', $filter_cols)) $trow[] = $array_category[$course_info['idCategory']];
            if (in_array('_COURSESTATUS', $filter_cols)) $trow[] = (isset($array_status[$course_info['status']]) ? $array_status[$course_info['status']] : '');
            if (in_array('_PUBLICATION_DATE', $filter_cols)) $trow[] = Format::date($course_info['create_date'], 'datetime');

            if (in_array('_HIGH_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['max_value'] : '-');
            if (in_array('_LESS_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['min_value'] : '-');
            if (in_array('_MEDIUM_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['everage_value'] : '-');

            $buffer->addLine($trow);
        }

        $buffer->closeTable();
        $buffer->closeBody();

        return $buffer->get();
    }

    //Course valutation
    function _printTable_course($type, $course, $stats, $filter_cols)
    {
        require_once(_lms_.'/admin/modules/report/report_tableprinter.php');
        $buffer = new ReportTablePrinter($type);

        $output = '';

        $lang =& DoceboLanguage::createInstance('report', 'framework');
        $glang =& DoceboLanguage::createInstance('admin_course_managment', 'lms');

        $query =	"SELECT idCategory, path"
            ." FROM ".$GLOBALS['prefix_lms']."_category";

        $result = sql_query($query);

        $array_category = array(0 => $lang->def('_NONE'));

        while(list($id_cat, $name_cat) = sql_fetch_row($result))
            $array_category[$id_cat] = substr($name_cat, 5, (strlen($name_cat)-5));

        $array_status = array(
            CST_PREPARATION => $lang->def('_CST_PREPARATION', 'admin_course_managment', 'lms'),
            CST_AVAILABLE 	=> $glang->def('_CST_AVAILABLE'),
            CST_EFFECTIVE 	=> $glang->def('_CST_CONFIRMED'),
            CST_CONCLUDED 	=> $glang->def('_CST_CONCLUDED'),
            CST_CANCELLED 	=> $glang->def('_CST_CANCELLED'));

        $colspan_course = 0;
        if(in_array('_CODE_COURSE', $filter_cols)) $colspan_course++;
        $colspan_course++;
        if(in_array('_COURSE_CATEGORY', $filter_cols)) $colspan_course++;
        if(in_array('_COURSESTATUS', $filter_cols)) $colspan_course++;
        if(in_array('_PUBLICATION_DATE', $filter_cols)) $colspan_course++;

        $colspan_stats = 0;
        if(in_array('_HIGH_VOTE', $filter_cols)) $colspan_stats++;
        if(in_array('_LESS_VOTE', $filter_cols)) $colspan_stats++;
        if(in_array('_MEDIUM_VOTE', $filter_cols)) $colspan_stats++;

        $th1 = array(
            array('colspan'=>$colspan_course, 'value'=>$lang->def('_COURSE')),
            array('colspan'=>$colspan_stats,  'value'=>$lang->def('_DOC_STATS'))
        );

        $th2 = array();

        if (in_array('_CODE_COURSE', $filter_cols)) $th2[] = $glang->def('_COURSE_CODE');
        $th2[] = $glang->def('_COURSE_NAME');
        if (in_array('_COURSE_CATEGORY', $filter_cols)) $th2[] = $glang->def('_CATEGORY');
        if (in_array('_COURSESTATUS', $filter_cols)) $th2[] = $glang->def('_STATUS');
        if (in_array('_PUBLICATION_DATE', $filter_cols)) $th2[] = $glang->def('_CREATION_DATE');

        if (in_array('_HIGH_VOTE', $filter_cols)) $th2[] = $lang->def('_MAX_SCORE');
        if (in_array('_LESS_VOTE', $filter_cols)) $th2[] = $lang->def('_MIN_SCORE');
        if (in_array('_MEDIUM_VOTE', $filter_cols)) $th2[] = $lang->def('_SCORE');

        $buffer->openTable($lang->def('_COURSE_CAPTION'), $lang->def('_COURSE_SUMMAMRY_MANAGMENT'));
        $buffer->openHeader();
        $buffer->addHeader($th1);
        $buffer->addHeader($th2);
        $buffer->closeHeader();
        $buffer->openBody();

        foreach($course as $course_info)
        {
            $trow = array();

            if (in_array('_CODE_COURSE', $filter_cols)) $trow[] = addslashes($course_info['code']);
            $trow[] = addslashes($course_info['name']);
            if (in_array('_COURSE_CATEGORY', $filter_cols)) $trow[] = $array_category[$course_info['idCategory']];
            if (in_array('_COURSESTATUS', $filter_cols)) $trow[] = (isset($array_status[$course_info['status']]) ? $array_status[$course_info['status']] : '');
            if (in_array('_PUBLICATION_DATE', $filter_cols)) $trow[] = Format::date($course_info['create_date'], 'datetime');

            if (in_array('_HIGH_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['max_value'] : '-');
            if (in_array('_LESS_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['min_value'] : '-');
            if (in_array('_MEDIUM_VOTE', $filter_cols)) $trow[] = (isset($stats[$course_info['id_quest']]) ? (string)$stats[$course_info['id_quest']]['everage_value'] : '-');

            $buffer->addLine($trow);
        }

        $buffer->closeTable();
        $buffer->closeBody();

        return $buffer->get();
    }

}