<?php defined("IN_FORMA") or die('Direct access is forbidden.');

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

class DashboardAdm extends Model
{

    protected $db;

    protected $user_level;
    protected $users_filter;
    protected $courses_filter;

    //--- init functions ---------------------------------------------------------

    public function __construct()
    {
        $this->db = DbConn::getInstance();

        $this->users_filter = false;
        $this->courses_filter = false;

        $this->user_level = Docebo::user()->getUserLevelId();
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            require_once(_base_ . '/lib/lib.preference.php');

            $adminManager = new AdminPreference();
            $this->users_filter = $adminManager->getAdminUsers(Docebo::user()->getIdST());

            $all_courses = false;
            $array_courses = array();
            $admin_courses = $adminManager->getAdminCourse(Docebo::user()->getIdST());
            foreach ($admin_courses['course'] as $key => $id_course) {
                if ($key > 0) {
                    $array_courses[$key] = $id_course;
                }
            }
            if (isset($admin_courses['course'][0])) {
                $all_courses = true;
            } elseif (isset($admin_courses['course'][-1])) {
                require_once(_lms_ . '/lib/lib.catalogue.php');
                $cat_man = new Catalogue_Manager();
                $user_catalogue = $cat_man->getUserAllCatalogueId(Docebo::user()->getIdSt());
                if (count($user_catalogue) > 0) {
                    $courses = array();
                    foreach ($user_catalogue as $id_cat) {
                        $catalogue_course =& $cat_man->getCatalogueCourse($id_cat, true);
                        if (empty($courses)) {
                            $courses = $catalogue_course;
                        } else {
                            $courses = array_merge($courses, $catalogue_course);
                        }
                    }
                    foreach ($courses as $id_course) {
                        if ($id_course != 0) {
                            $array_courses[$id_course] = $id_course;
                        }
                    }
                } elseif (Get::sett('on_catalogue_empty', 'off') == 'on') {
                    $all_courses = true;
                }
            } else {
                if (!empty($admin_courses['coursepath'])) {
                    require_once(_lms_ . '/lib/lib.coursepath.php');
                    $path_man = new CoursePath_Manager();
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
            }

            if (!$all_courses) {
                $this->courses_filter = array_values($array_courses);
            }
            //if "$all_courses" is true, than leave "$this->courses_filter" as false
        }
    }

    public function getPerm()
    {
        return array('view' => 'standard/view.png');
    }

    //----------------------------------------------------------------------------
    public function deactivateFeeds()
    {
        $query = "UPDATE %adm_setting SET param_value = 'off' WHERE param_name = 'welcome_use_feed'";
        $res = $this->db->query($query);
        return $res ? true : false;
    }

    public function activateFeeds()
    {
        $query = "UPDATE %adm_setting SET param_value = 'on' WHERE param_name = 'welcome_use_feed'";
        $res = $this->db->query($query);
        return $res ? true : false;
    }

    public function getSqlInfo()
    {
        $query = "SELECT @@GLOBAL.sql_mode";
        $res = $this->db->query($query);
        list($sql_mode) = $this->db->fetch_row($res);

        $info_character = array();
        $info_collation = array();

        //string sql_client_encoding ([ resource $link_identifier ] )
        $query = "SHOW VARIABLES LIKE 'character_set%'";
        $res = $this->db->query($query);
        while (list($name, $value) = $this->db->fetch_row($res)) {
            $info_character[$name] = $value;
        }

        $query = "SHOW VARIABLES LIKE 'collation%'";
        $res = $this->db->query($query);
        while (list($name, $value) = $this->db->fetch_row($res)) {
            $info_collation[$name] = $value;
        }

        $query = "SELECT @@time_zone";
        $res = $this->db->query($query);
        list($sql_timezone) = $this->db->fetch_row($res);

        return array(
            'sql_mode' => $sql_mode,
            'character_info' => $info_character,
            'collation_info' => $info_collation,
            'sql_timezone' => $sql_timezone

        );
    }

    public function updateVersion($old_version, $new_version)
    {

        if ($this->db->query("UPDATE %adm_setting SET param_value = '" . $new_version . "' WHERE param_name = 'core_version'")) {

            return $new_version;
        } else {

            return $old_version;
        }
    }

    public function getVersionExternalInfo()
    {
        $version = array(
            'db_version' => Get::sett('core_version'),
            'file_version' => _file_version_,
            'online_version' => ''
        );

        // check for differences beetween files and database version
        if (version_compare($version['file_version'], $version['db_version']) == 1) {

            switch ($version['db_version']) {
                // handling old docebo ce version
                case "3.6.0.3" :
                case "3.6.0.4" :
                case "4.0.0" :
                case "4.0.5" :
                    break;
                case "4.0.1" :
                case "4.0.2" :
                case "4.0.3" :
                case "4.0.4" :
                    $version['db_version'] = $this->updateVersion($version['db_version'], "4.0.5");
                    break;
                // new formalms versions
                case "1.0" :
                case "1.1" :
                case "1.2" :
                    break;
            }
        }

        if (Get::sett('welcome_use_feed') == 'on') {

            require_once(_base_ . '/lib/lib.fsock_wrapper.php');
            $fp = new Fsock();
            $versions_raw = $fp->send_request('http://www.formalms.org/versions/list');
            if( $versions_raw 
                && ($versions = json_decode($versions_raw, true))
                && isset($versions[0])
                && isset($versions[0]['version'])
            ) {
                $version['online_version'] = $versions[0]['version'];
            }

        }

        return $version;
    }

    /**
     * various stats and data retrieving to display in the dashboard
     *
     * @param boolean $stats_required
     * @param boolean $arr_users
     * @return array
     */
    public function getUsersStats($stats_required = false, $arr_users = false)
    {

        $aclManager = Docebo::user()->getACLManager();
        $users = array();
        if ($stats_required == false || empty($stats_required) || !is_array($stats_required)) {
            $stats_required = array('all', 'suspended', 'register_today', 'register_yesterday', 'register_7d',
                'now_online', 'inactive_30d', 'waiting', 'superadmin', 'admin', 'public_admin');
        }
        $stats_required = array_flip($stats_required);

        $data = new PeopleDataRetriever($GLOBALS['dbConn'], $GLOBALS['prefix_fw']);

        if (!empty($this->users_filter)) $data->setUserFilter($this->users_filter);

        if (isset($stats_required['all'])) {
            $users['all'] = $data->getTotalRows();
        }
        if (isset($stats_required['suspended'])) {
            $data->addFieldFilter('valid', 0);
            $data->addFieldFilter('userid', 'Anonymous', '<>'); //or idst <> Docebo::user()->getAnonymousId() ...
            $users['suspended'] = $data->getTotalRows();
        }
        if (isset($stats_required['register_today'])) {
            $data->resetFieldFilter();
            $data->addFieldFilter('register_date', date("Y-m-d") . ' 00:00:00', '>');
            $users['register_today'] = $data->getTotalRows();
        }
        if (isset($stats_required['register_yesterday'])) {
            $data->resetFieldFilter();
            $yesterday = date("Y-m-d", time() - 86400);
            $data->addFieldFilter('register_date', $yesterday . ' 00:00:00', '>');
            $data->addFieldFilter('register_date', $yesterday . ' 23:59:59', '<');
            $users['register_yesterday'] = $data->getTotalRows();
        }
        if (isset($stats_required['register_7d'])) {
            $data->resetFieldFilter();
            $sevendaysago = date("Y-m-d", time() - (7 * 86400));
            $data->addFieldFilter('register_date', $sevendaysago . ' 00:00:00', '>');
            $users['register_7d'] = $data->getTotalRows();
        }
        if (isset($stats_required['now_online'])) {
            $data->resetFieldFilter();
            $data->addFieldFilter('lastenter', date("Y-m-d H:i:s", time() - REFRESH_LAST_ENTER), '>');
            $users['now_online'] = $data->getTotalRows();
            if (($arr_users !== false) && (is_array($arr_users)) && (count($arr_users) > 0)) {
                $data->setUserFilter($arr_users);
                $users['now_online_filtered'] = $data->getTotalRows();
            } else {
                $users['now_online_filtered'] = 0;
            }
        }
        if (isset($stats_required['inactive_30d'])) {
            $data->resetFieldFilter();
            $data->addFieldFilter('lastenter', date("Y-m-d", time() - 30 * 86400) . ' 00:00:00', '<');
            $data->addFieldFilter('userid', 'Anonymous', '<>'); //or idst <> Docebo::user()->getAnonymousId() ...
            $users['inactive_30d'] = $data->getTotalRows();
        }
        if (isset($stats_required['waiting'])) {
            $users['waiting'] = $aclManager->getTempUserNumber();
        }
        if (isset($stats_required['superadmin'])) {
            $idst_sadmin = $aclManager->getGroupST(ADMIN_GROUP_GODADMIN);
            $users['superadmin'] = $aclManager->getGroupUMembersNumber($idst_sadmin);
        }
        if (isset($stats_required['admin'])) {
            $idst_admin = $aclManager->getGroupST(ADMIN_GROUP_ADMIN);
            $users['admin'] = $aclManager->getGroupUMembersNumber($idst_admin);
        }
        return $users;
    }

    public function getCoursesStats()
    {
        require_once(_lms_ . '/lib/lib.course.php');
        require_once(_lms_ . '/lib/lib.course_managment.php');

        $course_man = new AdminCourseManagment();
        return $course_man->getCoursesStats($this->courses_filter);
    }

    public function getCoursesMonthsStats()
    {
        $output = array(
            'month_subs_1' => 0,
            'month_subs_2' => 0,
            'month_subs_3' => 0
        );

        //extract subscriptions for the last three months
        for ($i = 0; $i < 3; $i++) {
            $date = date("Y-m", strtotime("-" . $i . " months"));
            $query = "SELECT COUNT(*) FROM %lms_courseuser WHERE date_inscr>'" . $date . "-01' AND date_inscr<'" . $date . "-31'";
            if ($this->user_level != ADMIN_GROUP_GODADMIN) {
                if ($this->users_filter !== false) {
                    if (empty($this->users_filter)) {
                        $query .= " AND 0 ";
                    } else {
                        $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                    }
                }
                if ($this->courses_filter !== false) {
                    if (empty($this->courses_filter)) {
                        $query .= " AND 0 ";
                    } else {
                        $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                    }
                }
            }
            list($num) = $this->db->fetch_row($this->db->query($query));
            $output['month_subs_' . ($i + 1)] = (int)$num;
        }

        return $output;
    }

    public function getUsersChartAccessData($how_many_days)
    {
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT MAX(enterTime) FROM %lms_tracksession "
            . " WHERE enterTime>'" . $last_date . " 00:00:00' "
            . " AND enterTime<='" . $today . " 23:59:59' GROUP BY idUser";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($last_access) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($last_access));
            if (isset($dates[$date])) $dates[$date]++;
        }

        foreach ($dates as $date => $count) {
            $output[] = array('x_axis' => $date, 'c' => $count);
        }

        return $output;
    }

    public function getUsersChartRegisterData($how_many_days)
    {
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT register_date FROM %adm_user "
            . " WHERE register_date>'" . $last_date . " 00:00:00' "
            . " AND register_date<='" . $today . " 23:59:59' ";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idst IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
        }
        $query .= " ORDER BY register_date DESC";
        $res = $this->db->query($query);
        while (list($last_access) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($last_access));
            if (isset($dates[$date])) $dates[$date]++;
        }

        foreach ($dates as $date => $count) {
            $output[] = array('x_axis' => $date, 'y_axis' => $count);
        }

        return $output;
    }

    public function getUsersChartAccessDataJS($how_many_days)
    {
        require_once(_base_.'/lib/lib.json.php');
        $json = new Services_JSON();
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT MAX(enterTime) FROM %lms_tracksession "
            . " WHERE enterTime>'" . $last_date . " 00:00:00' "
            . " AND enterTime<='" . $today . " 23:59:59' GROUP BY idUser";

        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);

        while (list($last_access) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($last_access));
            if (isset($dates[$date])) $dates[$date]++;
        }
        $outputCounts = array();
        $outputDates = array();
        foreach ($dates as $date => $count) {
            if (!is_array($count) && !is_array($date)) {
                $outputCounts[] = $count;
                $outputDates[] = $date;
            }
        }

        return array('x_axis' => $json->encode($outputDates), 'y_axis' => $json->encode($outputCounts));
    }

    public function getUsersChartRegisterDataJS($how_many_days)
    {
        require_once(_base_.'/lib/lib.json.php');
        $json = new Services_JSON();
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT register_date FROM %adm_user "
            . " WHERE register_date>'" . $last_date . " 00:00:00' "
            . " AND register_date<='" . $today . " 23:59:59' ";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idst IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
        }
        $query .= " ORDER BY register_date DESC";
        $res = $this->db->query($query);
        while (list($last_access) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($last_access));
            if (isset($dates[$date])) $dates[$date]++;
        }

        $outputCounts = array();
        $outputDates = array();
        foreach ($dates as $date => $count) {
            if (!is_array($count) && !is_array($date)) {
                $outputCounts[] = $count;
                $outputDates[] = $date;
            }
        }

        return array('x_axis' => $json->encode($outputDates), 'y_axis' => $json->encode($outputCounts));
    }

    public function getCoursesChartSubscriptionData($how_many_days)
    {
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_inscr FROM %lms_courseuser "
            . " WHERE date_inscr>'" . $last_date . " 00:00:00' AND date_inscr<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_inscr) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_inscr));
            if (isset($dates[$date])) $dates[$date]++;
        }

        foreach ($dates as $date => $count) {
            $output[] = array('x_axis' => $date, 'y_axis' => $count);
        }

        return $output;
    }

    public function getCoursesChartStartAttendingData($how_many_days)
    {
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_first_access FROM %lms_courseuser "
            . " WHERE date_first_access>'" . $last_date . " 00:00:00' AND date_first_access<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_first) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_first));
            if (isset($dates[$date])) $dates[$date]++;
        }

        foreach ($dates as $date => $count) {
            $output[] = array('x_axis' => $date, 'y_axis' => $count);
        }

        return $output;
    }

    public function getCoursesChartCompletedData($how_many_days)
    {
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_complete FROM %lms_courseuser "
            . " WHERE date_complete>'" . $last_date . " 00:00:00' AND date_complete<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_first) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_first));
            if (isset($dates[$date])) $dates[$date]++;
        }

        foreach ($dates as $date => $count) {
            $output[] = array('x_axis' => $date, 'y_axis' => $count);
        }

        return $output;
    }

    public function getCoursesChartSubscriptionDataJS($how_many_days)
    {
        require_once(_base_.'/lib/lib.json.php');
        $json = new Services_JSON();
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_inscr FROM %lms_courseuser "
            . " WHERE date_inscr>'" . $last_date . " 00:00:00' AND date_inscr<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_inscr) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_inscr));
            if (isset($dates[$date])) $dates[$date]++;
        }


        $outputCounts = array();
        $outputDates = array();
        foreach ($dates as $date => $count) {
            if (!is_array($count) && !is_array($date)) {
                $outputCounts[] = $count;
                $outputDates[] = $date;
            }
        }

        return array('x_axis' => $json->encode($outputDates), 'y_axis' => $json->encode($outputCounts));

    }

    public function getCoursesChartStartAttendingDataJS($how_many_days)
    {
        require_once(_base_.'/lib/lib.json.php');
        $json = new Services_JSON();
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_first_access FROM %lms_courseuser "
            . " WHERE date_first_access>'" . $last_date . " 00:00:00' AND date_first_access<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_first) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_first));
            if (isset($dates[$date])) $dates[$date]++;
        }


        $outputCounts = array();
        $outputDates = array();
        foreach ($dates as $date => $count) {
            if (!is_array($count) && !is_array($date)) {
                $outputCounts[] = $count;
                $outputDates[] = $date;
            }
        }

        return array('x_axis' => $json->encode($outputDates), 'y_axis' => $json->encode($outputCounts));
    }

    public function getCoursesChartCompletedDataJS($how_many_days)
    {
        require_once(_base_.'/lib/lib.json.php');
        $json = new Services_JSON();
        $output = array();
        $dates = array();

        $today = date("Y-m-d");
        for ($i = $how_many_days - 1; $i >= 0; $i--) {//for ($i=0; $i<$how_many_days; $i++) {
            $date = date("Y-m-d", strtotime("-" . (int)$i . " days"));
            $dates[$date] = 0;
        }
        $last_date = date("Y-m-d", strtotime("-" . ((int)$how_many_days - 1) . " days"));

        $query = "SELECT date_complete FROM %lms_courseuser "
            . " WHERE date_complete>'" . $last_date . " 00:00:00' AND date_complete<='" . $today . " 23:59:59'";
        if ($this->user_level != ADMIN_GROUP_GODADMIN) {
            if ($this->users_filter !== false) {
                if (empty($this->users_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idUser IN (" . implode(",", $this->users_filter) . ") ";
                }
            }
            if ($this->courses_filter !== false) {
                if (empty($this->courses_filter)) {
                    $query .= " AND 0 ";
                } else {
                    $query .= " AND idCourse IN (" . implode(",", $this->courses_filter) . ")";
                }
            }
        }
        $res = $this->db->query($query);
        while (list($date_first) = $this->db->fetch_row($res)) {
            $date = date("Y-m-d", strtotime($date_first));
            if (isset($dates[$date])) $dates[$date]++;
        }


        $outputCounts = array();
        $outputDates = array();
        foreach ($dates as $date => $count) {
            if (!is_array($count) && !is_array($date)) {
                $outputCounts[] = $count;
                $outputDates[] = $date;
            }
        }

        return array('x_axis' => $json->encode($outputDates), 'y_axis' => $json->encode($outputCounts));
    }

    public function getDashBoardReportList()
    {
        $report_list = array();
        $where_cond = "";
        $user_idst = Docebo::user()->getIdSt();
        $user_level = Docebo::user()->getUserLevelId();

        if ($user_level != ADMIN_GROUP_GODADMIN)
            $where_cond .= "AND (author='" . $user_idst . "' OR is_public>0)";


        $query = "SELECT id_filter, filter_name, author, creation_date, is_public "
            . " FROM %lms_report_filter "
            . " WHERE (author>0 OR is_public>0) " . $where_cond
            . " ORDER BY filter_name ASC ";

        $r = $this->db->query($query);
        while (list($idrep, $name, $author, $creation_date, $is_public) = $this->db->fetch_row($r)) {
            $report_list[$idrep] = $name;
        }
        return $report_list;
    }

    public function getDashBoardCertList($id_course, $id_user)
    {
        $query = "SELECT cc.id_certificate, ce.name, available_for_status, cu.status "
            . " FROM (" . $GLOBALS['prefix_lms'] . "_certificate AS ce "
            . " JOIN " . $GLOBALS['prefix_lms'] . "_certificate_course AS cc "
            . "        ON (ce.id_certificate = cc.id_certificate) )"
            . " JOIN " . $GLOBALS['prefix_lms'] . "_courseuser AS cu "
            . "        ON (cu.idCourse = cc.id_course)"
            . " WHERE cu.idCourse = " . (int)$id_course . " "
            . "    AND idUser = " . (int)$id_user . " ";
        return sql_query($query);

    }


}
