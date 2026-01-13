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

    public $usestandardtitle_rows = true;
    //var $usestandardtitle_cols = true;

    public $columns_categories = [];

    public $db = null;

    protected $session;

    protected $lang;

    /**
     * Cache dei dati elaborati per la richiesta corrente
     * @var array
     */
    protected $requestCache = [];

    /**
     * Cache delle query per evitare duplicati
     * @var array
     */
    protected $queryCache = [];

    /**
     * Flag per abilitare/disabilitare cache
     * @var bool
     */
    protected $cacheEnabled = true;

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

            $this->report_name = Lang::t($report_name,'report');
            $this->report_descr = Lang::t($report_name,'report');
        }

        $this->lang = FormaLanguage::createInstance('report', 'framework');

        $this->db = \FormaLms\db\DbConn::getInstance();
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

    // ==================== CACHE METHODS ====================

    /**
     * Genera hash univoco basato su filtri e parametri
     * @param string $cat Categoria colonne
     * @param mixed $report_data Dati del report
     * @return string Hash MD5
     */
    protected function generateCacheKey($cat, $report_data = null)
    {
        $keyData = [
            'cat' => $cat,
            'report_data' => $report_data,
            'class' => get_class($this),
            'id_report' => $this->id_report,
        ];

        return md5(serialize($keyData));
    }

    /**
     * Verifica se esistono dati in cache
     * @param string $key Chiave cache
     * @return bool
     */
    protected function hasCache($key)
    {
        return $this->cacheEnabled && isset($this->requestCache[$key]);
    }

    /**
     * Recupera dati dalla cache
     * @param string $key Chiave cache
     * @return mixed|null
     */
    protected function getCache($key)
    {
        return $this->hasCache($key) ? $this->requestCache[$key] : null;
    }

    /**
     * Salva dati in cache
     * @param string $key Chiave cache
     * @param mixed $data Dati da salvare
     * @return void
     */
    protected function setCache($key, $data)
    {
        if ($this->cacheEnabled) {
            $this->requestCache[$key] = $data;
        }
    }

    /**
     * Pulisce la cache
     * @return void
     */
    public function clearCache()
    {
        $this->requestCache = [];
        $this->queryCache = [];
    }

    /**
     * Esegue query con caching automatico per evitare duplicati
     * @param string $query SQL query
     * @param string|null $cacheKey Chiave identificativa opzionale
     * @return array Risultato query come array
     */
    protected function cachedQuery($query, $cacheKey = null)
    {
        $key = $cacheKey ?: md5($query);

        if (isset($this->queryCache[$key])) {
            return $this->queryCache[$key];
        }

        $result = sql_query($query);
        $data = [];

        if ($result) {
            while ($row = sql_fetch_assoc($result)) {
                $data[] = $row;
            }
        }

        $this->queryCache[$key] = $data;

        return $data;
    }

    /**
     * Esegue query con caching e restituisce array di singola colonna
     * @param string $query SQL query
     * @param string $column Nome colonna da estrarre
     * @param string|null $cacheKey Chiave identificativa opzionale
     * @return array Array di valori della colonna
     */
    protected function cachedQueryColumn($query, $column, $cacheKey = null)
    {
        $rows = $this->cachedQuery($query, $cacheKey);
        $result = [];

        foreach ($rows as $row) {
            if (isset($row[$column])) {
                $result[] = $row[$column];
            }
        }

        return $result;
    }

    /**
     * Recupera tutti gli ID corso con caching
     * @return array
     */
    protected function getAllCourseIds()
    {
        return $this->cachedQueryColumn(
            'SELECT idCourse FROM %lms_course',
            'idCourse',
            'all_course_ids'
        );
    }

    /**
     * Pre-carica utenti di multipli gruppi in batch (risolve N+1)
     * @param array $groupIds Array di ID gruppi
     * @return array Array associativo [group_id => [user_ids]]
     */
    protected function preloadGroupUsers(array $groupIds)
    {
        if (empty($groupIds)) {
            return [];
        }

        $cacheKey = 'group_users_' . md5(serialize($groupIds));

        if ($this->hasCache($cacheKey)) {
            return $this->getCache($cacheKey);
        }

        $groupUsers = [];
        $prefix = $GLOBALS['prefix_fw'];

        $query = "SELECT idst, idstMember
                  FROM {$prefix}_group_members
                  WHERE idst IN (" . implode(',', array_map('intval', $groupIds)) . ')';

        $result = sql_query($query);
        if ($result) {
            while ($row = sql_fetch_assoc($result)) {
                $gid = $row['idst'];
                if (!isset($groupUsers[$gid])) {
                    $groupUsers[$gid] = [];
                }
                $groupUsers[$gid][] = $row['idstMember'];
            }
        }

        $this->setCache($cacheKey, $groupUsers);

        return $groupUsers;
    }

    // ==================== END CACHE METHODS ====================

    /**
     * load the report info into the class variables.
     */
    public function _load()
    {

        $query_report = '
		SELECT report_name
		FROM %lms_report
		WHERE id_report = "' . $this->id_report . '"';
        $re_report = sql_query($query_report);
        [$report_name] = sql_fetch_row($re_report);

        $this->report_name = Lang::t($report_name,'report');
        $this->report_descr = Lang::t($report_name,'report');
    }

    public function play($jump_url, $back_url, $alluser, $org_chart_subdivision, $start_rime, $end_time)
    {
    }

    public function getAllUserIdst()
    {
        $p_dr = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);

        $userlevelid = \FormaLms\lib\FormaUser::getCurrentUser()->getUserLevelId();
        if ($userlevelid != ADMIN_GROUP_GODADMIN) {
            require_once _base_ . '/lib/lib.preference.php';
            $adminManager = new AdminPreference();
            $p_dr->intersectGroupFilter(
                $adminManager->getAdminTree(\FormaLms\lib\FormaUser::getCurrentUser()->getIdSt())
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

    public function __construct($id = '')
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

    public function setRowsFilter($data)
    {
        $this->data[self::_RS_ROWS_FILTER] = $data;
    }

    public function getRowsFilter()
    {
        return $this->data[self::_RS_ROWS_FILTER];
    }

    public function setColsFilter($data)
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
