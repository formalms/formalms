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

//report session managemente class

//report superclass

class Report
{
    public $id_report;

    public $report_name = '';

    public $report_descr = '';

    public $back_url = '';
    public $jump_url = '';
    public $next_url = '';

    public $lang;

    public $usestandardtitle_rows = true;
    //var $usestandardtitle_cols = true;

    public $columns_categories = [];

    public $db = null;

    protected $session;

    public const _REPORT_SESSION = 'report_tempdata';
    public const _RS_ID = 'id_report';
    public const _RS_ROWS_FILTER = 'rows_filter';
    public const _RS_COLS_FILTER = 'columns_filter';
    public const _RS_COLS_CATEGORY = 'columns_filter_category';

    public function __construct($id_report, $report_name = false)
    {
        $this->id_report = $id_report;
        if ($report_name == false) {
            $this->_load();
        } else {
            $this->lang = &DoceboLanguage::createInstance('report', 'framework');
            $this->report_name = $this->lang->def($report_name);
            $this->report_descr = $this->lang->def($report_name);
        }

        $this->db = DbConn::getInstance();
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
    }

    public function get_name()
    {
        return $this->report_name;
    }

    public function get_description()
    {
        return $this->report_descr;
    }

    //function to override in subclasses
    public function play_filter_rows()
    {
        return '';
    }

    //function to override in subclasses
    public function play_filter_columns()
    {
        return '';
    }

    public function apply_filter()
    {
    }

    //******************************************************************************
    public function _set_columns_category($key, $name, $filter, $results, $get_data, $stdtitle = true)
    {
        $this->columns_categories[$key] = [
            'key' => $key,
            'name' => $name,
            'filter' => $filter,
            'show' => $results,
            'get_data' => $get_data,
            'stdtitle' => $stdtitle,
        ];
    }

    public function get_columns_categories()
    {
        $temp = [];
        foreach ($this->columns_categories as $key => $value) {
            $temp[$key] = $value['name'];
        }

        return $temp;
    }

    public function get_columns_filter($cat)
    {
        $name_func = $this->columns_categories[$cat]['filter'];

        return $this->$name_func();
    }

    public function useStandardTitle_Rows()
    {
        return $this->usestandardtitle_rows;
    }

    public function useStandardTitle_Columns()
    {
        $reportTempData = $this->session->get(self::_REPORT_SESSION);
        if (array_key_exists('columns_filter_category', $reportTempData) && isset($reportTempData['columns_filter_category'])) {
            $temp = $reportTempData['columns_filter_category'];
        } else {
            return true;
        }

        if (isset($this->columns_categories[$temp])) {
            return $this->columns_categories[$temp]['stdtitle'];
        } else {
            return true;
        }
    }

    public function show_results($cat = false, $report_data = null)
    {
        $reportTempData = $this->session->get(self::_REPORT_SESSION);

        if (!$cat) {
            $cat = $reportTempData['columns_filter_category'];
        }

        $name_func = $this->columns_categories[$cat]['show']; //['get_data'];

        return $this->$name_func($report_data);
    }

    public function _get_data($type = 'html', $cat = false, $report_data = null)
    {
        $reportTempData = $this->session->get(self::_REPORT_SESSION);
        if (!$cat) {
            $cat = $reportTempData['columns_filter_category'];
        }
        $name_func = $this->columns_categories[$cat]['get_data'];

        return $this->$name_func($type, $report_data);
    }

    public function getHTML($cat = false, $report_data = null)
    {
        return $this->_get_data('html', $cat, $report_data);
    }

    public function getCSV($cat = false, $report_data = null)
    {
        return $this->_get_data('csv', $cat, $report_data);
    }

    public function getXLS($cat = false, $report_data = null)
    {
        return $this->_get_data('xls', $cat, $report_data);
    }

    /**
     * load the report info into the class variables.
     */
    public function _load()
    {
        $this->lang = &DoceboLanguage::createInstance('report', 'framework');

        $query_report = '
		SELECT report_name
		FROM ' . $GLOBALS['prefix_lms'] . "_report
		WHERE id_report = '" . $this->id_report . "'";
        $re_report = sql_query($query_report);
        list($report_name) = sql_fetch_row($re_report);

        $this->report_name = $this->lang->def($report_name);
        $this->report_descr = $this->lang->def($report_name);
    }

    public function play($jump_url, $back_url, $alluser, $org_chart_subdivision, $start_rime, $end_time)
    {
    }

    public function &getAllUserIdst()
    {
        $p_dr = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);

        $userlevelid = Docebo::user()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $p_dr->intersectGroupFilter(
                $adminManager->getAdminTree(Docebo::user()->getIdSt())
            );
        }

        $re_people = $p_dr->getAllRowsIdst();

        $user_selected = [];
        if (!$re_people) {
            return $user_selected;
        }

        while (list($idst) = sql_fetch_row($re_people)) {
            $user_selected[$idst] = $idst;
        }

        return $user_selected;
    }
}

//little class for filter box management
addCss('style_filterbox');

class ReportBox
{
    public $id = '';

    public $title = '';
    public $description = '';
    public $body = '';
    public $footer = '';
    public $title_css = 'filter_details';

    public $collapsed = false;

    public $show_collapse_cmd = false;

    public function ReportBox($id = '')
    {
        $this->id = $id;
    }

    //...

    public function get()
    {
        $boxid = ($this->id != '' ? ' id="' . $this->id . '"' : '');
        $output = '<div' . $boxid . ' class="filter_container' . ($this->collapsed ? '' : '') . '">';

        $output .=
            '<div class="fc_header">' .
            //'<div class="tl_corner"></div>'.
            '<h2 class="' . $this->title_css . '">' . $this->title . '</h2>' .
            //'<div class="cmd_expand"><a href="#">'.'Expand'.'</a></div>'.
            '';
        if ($this->show_collapse_cmd) {
            $output .= '<div class="cmd_collapse"><a href="#">Collapse</a></div>';
        }
        $output .= '' . //'<div class="tr_corner"></div>'.
            '</div>';

        if ($this->description) {
            $output .= '<p class="fc_sec_header">' . $this->description . '</p>';
        }

        $output .= '<div class="fc_body_filter">' . $this->body . '</div>';

        if ($this->footer != '') {
            $output .= '<p class="fc_sec_header align_right">' . $this->footer . '</p>';
        }

        $output .= '</div>';

        return $output;
    }
}

class ReportSessionManager
{
    public $data = null;

    protected $session;

    public const _REPORT_SESSION = 'report_tempdata';
    public const _RS_ID = 'id_report';
    public const _RS_ROWS_FILTER = 'rows_filter';
    public const _RS_COLS_FILTER = 'columns_filter';
    public const _RS_COLS_CATEGORY = 'columns_filter_category';

    public function __construct()
    {
        $this->session = \FormaLms\lib\Session\SessionManager::getInstance()->getSession();
        if (!$this->_is_initialized()) {
            $this->_initialize();
        }
    }

    public function _is_initialized()
    {
        return $this->session->has(self::_REPORT_SESSION);
    }

    public function _initialize()
    {
        $this->session->set(self::_REPORT_SESSION, [
            self::_RS_ID => false,
            self::_RS_ROWS_FILTER => false,
            self::_RS_COLS_CATEGORY => false,
            self::_RS_COLS_FILTER => false,
        ]);
        $this->session->save();
    }

    public function setId($id)
    {
        $this->data[self::_RS_ID] = $id;
    }

    public function getId()
    {
        return $this->data[self::_RS_ID];
    }

    public function setRowsFilter(&$data)
    {
        $this->data[self::_RS_ROWS_FILTER] = $data;
    }

    public function getRowsFilter()
    {
        return $this->data[self::_RS_ROWS_FILTER];
    }

    public function setColsFilter(&$data)
    {
        $this->data[self::_RS_COLS_FILTER] = $data;
    }

    public function getColsFilter()
    {
        return $this->data[self::_RS_COLS_FILTER];
    }

    public function setColsCategory($category)
    {
        $this->data[self::_RS_COLS_CATEGORY] = $category;
    }

    public function getColsCategory()
    {
        return $this->data[self::_RS_COLS_CATEGORY];
    }

    public function flush()
    {
        $this->initialize();
    }

    private function initialize()
    {
    }
}
