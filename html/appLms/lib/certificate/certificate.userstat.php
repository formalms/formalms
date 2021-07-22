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

require_once(dirname(__FILE__) . '/certificate.base.php');

class CertificateSubs_UserStat extends CertificateSubstitution
{

    function getSubstitutionTags()
    {
        $subs = [];
        if ($this->id_meta != 0) {
            $subs['[table_blended]'] = Lang::t('_TABLE_BLENDED', 'certificate');
            $subs['[table_course]'] = Lang::t('_TABLE_COURSE', 'certificate');
            $subs['[meta_complete]'] = Lang::t('_META_COMPLETE', 'certificate');
            $subs['[meta_inscr]'] = Lang::t('_META_INSCR', 'certificate');
            $subs['[meta_access]'] = Lang::t('_META_ACCESS', 'certificate');
            //$subs['[meta_level]'] = Lang::t('_LEVEL','certificate');
        } else {
            $subs['[user_level]'] = Lang::t('_LEVEL', 'certificate');
            $subs['[date_enroll]'] = Lang::t('_DATE_ENROLL', 'certificate');
            $subs['[date_first_access]'] = Lang::t('_DATE_FIRST_ACCESS', 'certificate');
            $subs['[date_complete]'] = Lang::t('_DATE_COMPLETE', 'certificate');
            $subs['[date_complete_year]'] = Lang::t('_DATE_COMPLETE', 'certificate');
            $subs['[total_time]'] = Lang::t('_TOTAL_TIME', 'certificate');
            $subs['[total_time_hour]'] = Lang::t('_TOTAL_TIME_HOUR', 'certificate');
            $subs['[total_time_minute]'] = Lang::t('_TOTAL_TIME_MINUTE', 'certificate');
            $subs['[total_time_second]'] = Lang::t('_TOTAL_SECONDS', 'certificate');
            $subs['[test_score_start]'] = Lang::t('_TEST_SCORE_START', 'certificate');
            $subs['[test_score_start_max]'] = Lang::t('_TEST_SCORE_START_MAX', 'certificate');
            $subs['[test_score_final]'] = Lang::t('_TEST_SCORE_FINAL', 'certificate');
            $subs['[test_score_final_max]'] = Lang::t('_TEST_SCORE_FINAL_MAX', 'certificate');
            $subs['[course_score_final]'] = Lang::t('_FINAL_SCORE', 'certificate');
            $subs['[course_score_final_max]'] = Lang::t('_COURSE_SCORE_FINAL_MAX', 'certificate');
            $subs['[meta_assoc]'] = Lang::t('_META_ASSOC', 'certificate');
            $subs['[course_scorm_items]'] = Lang::t('_SCORM_ITEMS', 'certificate');
        }
        return $subs;
    }

    function getSubstitution()
    {

        $subs = [];

        if ($this->id_meta != 0) {
            require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');
            require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
            require_once($GLOBALS['where_lms'] . '/lib/lib.aggregated_certificate.php');

            $aggCertLib = new AggregatedCertificate();


            $array_coursetype = array('elearning' => Lang::t('_COURSE_TYPE_ELEARNING', 'course', 'lms'),
                'classroom' => Lang::t('_CLASSROOM', 'course', 'lms'),
                'web_seminar' => Lang::t('Web seminar'));

            $course_time = 0;
            $blended_time = 0;
            $array_meta_complete = [];
            $array_meta_inscr = [];
            $array_meta_access = [];

            $assocType = $aggCertLib->getTypeAssoc($this->id_meta, $this->id_user);

            switch ($assocType) {
                case COURSE_PATH:
                    $table_blended = '<table width="100%" cellspacing="1" cellpadding="1" border="1" align="">'
                        . '<thead>'
                        . '<tr>'
                        . '<td>' . Lang::t('_COURSEPATH') . '</td>'
                        . '<td>' . Lang::t('_TOTAL_SESSION', 'report') . '</td>'
                        . '</tr>'
                        . '</thead>'
                        . '<tbody>';

                    $path = $aggCertLib->getIdsCoursePath($this->id_meta, $this->id_user);
                    require_once($GLOBALS['where_lms'] . '/lib/lib.coursepath.php');
                    $coursePath_man = new CoursePath_Manager();
                    foreach ($path as $id_path) {
                        $courses = $coursePath_man->getAllCourses($id_path);
                        $courses_path_time = 0;
                        foreach ($courses as $id_course) {
                            $query = "SELECT date_complete, date_inscr, date_first_access "
                                . " FROM " . $GLOBALS['prefix_lms'] . "_courseuser"
                                . " WHERE idCourse = '" . $id_course . "'"
                                . " AND idUser = '" . $this->id_user . "'";

                            list($date_complete_meta, $date_inscr_meta, $date_access_meta) = sql_fetch_row(sql_query($query));

                            $array_meta_complete[] = $date_complete_meta;
                            $array_meta_inscr[] = $date_inscr_meta;
                            $array_meta_access[] = $date_access_meta;

                            $man_course = new Man_Course();

                            $course_info = $man_course->getCourseInfo($id_course);

                            $rep_man = new CourseReportManager();

                            $score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));
                            $courses_path_time += $course_info['mediumTime'];
                            $course_time += $course_info['mediumTime'];
                        }
                        $info_path = $coursePath_man->getCoursepathInfo($id_path);
                        $table_blended .= '<tr>'
                            . '<td>' . $info_path['path_name'] . '</td>'
                            . '<td>' . $courses_path_time . '</td>'
                            . '</tr>';

                    }
                    $table_blended .= '<tr>'
                        . '<td>' . Lang::t('_TOTAL_TIME') . '</td>'
                        . '<td>' . $course_time . '</td>'
                        . '</tr>'
                        . '</tbody>'
                        . '</table>';
                    $subs['[table_blended]'] = $table_blended;
                    $subs['[table_course]'] = $subs['[table_blended]'];
                    break;
                case COURSE:
                    $table_course = '<table width="100%" cellspacing="1" cellpadding="1" border="1" align="">'
                        . '<thead>'
                        . '<tr>'
                        . '<td>' . Lang::t('_COURSE_NAME') . '</td>'
                        . '<td>' . Lang::t('_COURSE_TYPE', 'course') . '</td>'
                        . '<td>' . Lang::t('_TOTAL_SESSION', 'report') . '</td>'
                        . '</tr>'
                        . '</thead>'
                        . '<tbody>';
                    $courses = $aggCertLib->getIdsCourse($this->id_meta, $this->id_user);
                    foreach ($courses as $id_course) {
                        $query = "SELECT date_complete, date_inscr, date_first_access, level"
                            . " FROM " . $GLOBALS['prefix_lms'] . "_courseuser"
                            . " WHERE idCourse = '" . $id_course . "'"
                            . " AND idUser = '" . $this->id_user . "'";

                        list($date_complete_meta, $date_inscr_meta, $date_access_meta, $level) = sql_fetch_row(sql_query($query));

                        $array_meta_complete[] = $date_complete_meta;
                        $array_meta_inscr[] = $date_inscr_meta;
                        $array_meta_access[] = $date_access_meta;
                        //$array_meta_level[] = $level;

                        $man_course = new Man_Course();

                        $course_info = $man_course->getCourseInfo($id_course);

                        $rep_man = new CourseReportManager();

                        $score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));
                        $table_course .= '<tr>'
                            . '<td>' . $course_info['name'] . '</td>'
                            . '<td>' . $array_coursetype[$course_info['course_type']] . '</td>'
                            . '<td align="right">' . $course_info['mediumTime'] . '</td>'
                            . '</tr>';
                        $course_time += $course_info['mediumTime'];

                        $subs['[course_scorm_items]'] .= $this->getSubstitutionScormItems($id_course, $this->id_user);
                    }
                    $table_course .= '<tr>'
                        . '<td align="right" colspan="2">' . Lang::t('_TOTAL_TIME') . '</td>'
                        . '<td align="right">' . $course_time . '</td>'
                        . '</tr>'
                        . '</tbody>'
                        . '</table>';
                    $subs['[table_course]'] = $table_course;
                    $subs['[table_blended]'] = $subs['[table_course]'];

                    $subs['[course_scorm_items]'] = $this->getSubstitutionScormItems($id_course, $this->id_user);
                    break;
            }

            rsort($array_meta_complete);
            sort($array_meta_inscr);
            sort($array_meta_access);

            $subs['[meta_complete]'] = $array_meta_complete[0];
            $subs['[meta_inscr]'] = $array_meta_inscr[0];
            $subs['[meta_access]'] = $array_meta_access[0];

            $sql = "
				SELECT title FROM " . $aggCertLib->table_cert_meta_association . " AS cm 
				WHERE cm.idAssociation = {$this->id_meta}";
            $q = sql_query($sql);
            $meta = sql_fetch_object($q);
            $subs['[meta_assoc]'] = $meta->title;

        } else {
            require_once($GLOBALS['where_lms'] . '/lib/lib.course.php');

            $courseuser = new Man_CourseUser();
            $course_stat =& $courseuser->getUserCourses($this->id_user, false, false, false, array($this->id_course));

            if (isset($course_stat[$this->id_course])) {

                $subs['[date_enroll]'] = Format::date($course_stat[$this->id_course]['date_inscr'], 'date');
                $subs['[date_first_access]'] = Format::date($course_stat[$this->id_course]['date_first_access'], 'date');
                $subs['[date_complete]'] = Format::date($course_stat[$this->id_course]['date_complete'], 'date');
                $subs['[date_complete_year]'] = substr($course_stat[$this->id_course]['date_complete'], 0, 4);

                $subs['[user_level]'] = Lang::t('_LEVEL_' . $course_stat[$this->id_course]['level'], 'levels');
            } else {

                $subs['[date_enroll]'] = '';
                $subs['[date_first_access]'] = '';
                $subs['[date_complete]'] = '';
                $subs['[date_complete_year]'] = '';
                $subs['[user_level]'] = '';
            }

            require_once($GLOBALS['where_lms'] . '/lib/lib.orgchart.php');
            $org_man = new OrganizationManagement($this->id_course);

            $score_start = $org_man->getStartObjectScore(array($this->id_user), array($this->id_course));
            $score_final = $org_man->getFinalObjectScore(array($this->id_user), array($this->id_course));


            require_once($GLOBALS['where_lms'] . '/lib/lib.coursereport.php');
            $rep_man = new CourseReportManager();

            $score_course = $rep_man->getUserFinalScore(array($this->id_user), array($this->id_course));


            $subs['[test_score_start]'] = (isset($score_start[$this->id_course][$this->id_user]) ? $score_start[$this->id_course][$this->id_user]['score'] : '');
            $subs['[test_score_start_max]'] = (isset($score_start[$this->id_course][$this->id_user]) ? $score_start[$this->id_course][$this->id_user]['max_score'] : '');
            $subs['[test_score_final]'] = (isset($score_final[$this->id_course][$this->id_user]) ? $score_final[$this->id_course][$this->id_user]['score'] : '');
            $subs['[test_score_final_max]'] = (!empty($score_final[$this->id_course][$this->id_user]['max_score'])
                ? $score_final[$this->id_course][$this->id_user]['max_score']
                : '100');

            $subs['[course_score_final]'] = (isset($score_course[$this->id_user][$this->id_course]) ? $score_course[$this->id_user][$this->id_course]['score'] : '');
            $subs['[course_score_final_max]'] = (isset($score_course[$this->id_user][$this->id_course]) ? $score_course[$this->id_user][$this->id_course]['max_score'] : '');

            require_once($GLOBALS['where_lms'] . '/lib/lib.track_user.php');
            $time_in = TrackUser::getUserTotalCourseTime($this->id_user, $this->id_course);

            $hours = (int)($time_in / 3600);
            $minutes = (int)(($time_in % 3600) / 60);
            $seconds = (int)($time_in % 60);
            if ($minutes < 10) $minutes = '0' . $minutes;
            if ($seconds < 10) $seconds = '0' . $seconds;

            $subs['[total_time]'] = $hours . 'h ' . $minutes . 'm ' . $seconds . 's';
            $subs['[total_time_hour]'] = $hours;
            $subs['[total_time_minute]'] = $minutes;
            $subs['[total_time_second]'] = $seconds;

            $subs['[course_scorm_items]'] = $this->getSubstitutionScormItems($this->id_user, $this->id_course);
        }

        return $subs;
    }

    public function getSubstitutionScormItems($idUser, $idCourse)
    {

        $courseReportModel = new CoursereportLms($idCourse);

        $scormItemsTable = '<table width="100%" cellspacing="1" cellpadding="1" border="1" align="">'
            . '<thead>'
            . '<tr>'
            . '<td>' . Lang::t('_SCORM_TITLE', 'certificate') . '</td>'
            . '<td>' . Lang::t('_SCORM_SCORE', 'certificate') . '</td>'
            . '</tr>'
            . '</thead>'
            . '<tbody>';

        foreach ($courseReportModel->getReportsFilteredBySourceOf(CoursereportLms::SOURCE_OF_SCOITEM) as $info_report) {

            $name = strip_tags($info_report->getTitle());
            $scormItem = new ScormLms($info_report->getIdSource(), $idUser);

            $userScore = $scormItem->getScoreRaw();
            $scormItemsTable .= '<tr>'
                . '<td>' . $name . '</td>'
                . '<td>' . $userScore . '</td>'
                . '</tr>';

        }

        $scormItemsTable .= '</tbody>'
            . '</table>';

        return $scormItemsTable;
    }

}
