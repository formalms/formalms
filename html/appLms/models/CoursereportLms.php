<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2022 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

defined('IN_FORMA') or exit('Direct access is forbidden.');

require_once Forma::inc(_lms_ . '/lib/lib.coursereport.php');
require_once Forma::inc(_lms_ . '/lib/lib.test.php');

class CoursereportLms extends Model
{
    public const SOURCE_OF_TEST = 'test';
    public const SOURCE_OF_SCOITEM = 'scoitem';
    public const SOURCE_OF_ACTIVITY = 'activity';
    public const SOURCE_OF_FINAL_VOTE = 'final_vote';

    public const TEST_STATUS_NOT_COMPLETED = 'not_complete';
    public const TEST_STATUS_NOT_CHECKED = 'not_checked';
    public const TEST_STATUS_NOT_PASSED = 'not_passed';
    public const TEST_STATUS_PASSED = 'passed';
    public const TEST_STATUS_DOING = 'doing';
    public const TEST_STATUS_VALID = 'valid';

    /**
     * @var int
     */
    protected $idCourse;
    /**
     * @var int
     */
    protected $idReport;
    /**
     * @var string
     */
    protected $sourceOf;

    /**
     * @var int
     */
    protected $idSource;

    /**
     * @var int
     */
    protected $reportCount;
    /**
     * @var ReportLms[]
     */
    protected $courseReports;

    /**
     * CoursereportLms constructor.
     *
     * @param $idCourse
     * @param null $idReport
     * @param null $sourceOf
     * @param null $idSource
     */
    public function __construct($idCourse, $idReport = null, $sourceOf = null, $idSource = null)
    {
        parent::__construct();
        $this->idCourse = $idCourse;

        $this->idReport = $idReport;

        $this->sourceOf = $sourceOf;

        $this->idSource = $idSource;

        $this->courseReports = [];
    }

    /**
     * @param int $idCourse
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;

        $this->grabCourseReports();
    }

    /**
     * @return int
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    /**
     * @return ReportLms[]
     */
    public function getCourseReports()
    {
        if (count($this->courseReports) === 0) {
            $this->grabCourseReports();
        }

        return $this->courseReports;
    }

    /**
     * @return int
     */
    public function getReportCount()
    {
        $this->reportCount = count($this->courseReports);

        return $this->reportCount;
    }

    private function grabCourseReports()
    {
        $report_man = new CourseReportManager();
        $org_tests = &$report_man->getTest();

        $query_final_tot_report = "SELECT COUNT(*) FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of = '" . self::SOURCE_OF_FINAL_VOTE . "'";

        list($final_score_report) = sql_fetch_row(sql_query($query_final_tot_report));

        if ((int) $final_score_report === 0) {
            $report_man->addFinalVoteToReport();
        }

        $query_tot_report = "SELECT COUNT(*) FROM %lms_coursereport  WHERE id_course = '" . $this->idCourse . "'";

        list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

        if ((int) $tot_report === 1) {
            if ((int) $final_score_report === 1) {
                $query_remove_final_score = "DELETE FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of = '" . self::SOURCE_OF_FINAL_VOTE . "'";

                sql_query($query_remove_final_score);
            }

            $query_tot_report = 'SELECT COUNT(*) '
                . ' FROM %lms_coursereport '
                . " WHERE id_course = '" . $this->idCourse . "'";
            list($tot_report) = sql_fetch_row(sql_query($query_tot_report));
        }

        $query_tests = 'SELECT id_report, id_source '
            . ' FROM %lms_coursereport '
            . " WHERE id_course = '" . $this->idCourse . "' AND source_of = '" . self::SOURCE_OF_TEST . "'";

        $re_tests = sql_query($query_tests);

        $included_test = [];
        $included_test_report_id = [];
        //while (list($id_r, $id_t) = sql_fetch_row($re_tests)) {
        foreach ($re_tests as $re_test) {
            $included_test_report_id[$re_test['id_report']] = $re_test['id_report'];
            $included_test[$re_test['id_source']] = $re_test['id_source'];
        }

        // XXX: Update if needed
        if ((int) $tot_report === 0) {
            $report_man->initializeCourseReport($org_tests);
        } else {

            if (is_array($included_test)) {
                $test_to_add = array_diff($org_tests, $included_test);
            } else {
                $test_to_add = $org_tests;
            }

            if (is_array($included_test)) {
                $test_to_del = array_diff($included_test, $org_tests);
            } else {
                $test_to_del = $org_tests;
            }

            if (!empty($test_to_add) || !empty($test_to_del)) {
                $report_man->addTestToReport($test_to_add, 1);
                $report_man->delTestToReport($test_to_del);
            }
        }

        $report_man->updateTestReport($org_tests);

        $query_report = "SELECT id_report FROM %lms_coursereport WHERE id_course = '" . $this->idCourse . "'";

        if (!is_null($this->idReport)) {
            $query_report .= " AND id_report = '" . $this->idReport . "'";
        }

        if (!is_null($this->sourceOf)) {
            if (is_array($this->sourceOf)) {
                $query_report .= " AND source_of IN ('" . implode("','", $this->sourceOf) . "')";
            } else {
                $query_report .= " AND source_of = '" . $this->sourceOf . "'";
            }
        }

        if (!is_null($this->idSource)) {
            $query_report .= " AND id_source = '" . $this->idSource . "'";
        }

        $query_report .= ' ORDER BY sequence ';

        $re_report = sql_query($query_report);

        foreach ($re_report as $infoReport) {
            $report = new ReportLms($infoReport['id_report']);

            $this->courseReports[] = $report;
        }
    }

    /**
     * Returns reports included for idcourse filrt.
     *
     * @return ReportLms[]
     */
    public function getReportsFilteredBySourceOf($sourceOf = null, $grab = true)
    {
        $result = [];

        if (count($this->courseReports) === 0 && $grab) {
            $this->grabCourseReports();
        }
        foreach ($this->courseReports as $courseReport) {
            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {
                $result[] = $courseReport;
            }
        }

        return $result;
    }

    /**
     * @param array $id_sources
     */
    public function getCourseReportsVisibleInDetail()
    {
        $result = [];

        if (count($this->courseReports) === 0) {
            $this->grabCourseReports();
        }

        foreach ($this->courseReports as $info_report) {
            if ($info_report->isShowInDetail()) {
                $result[] = $info_report;
            }
        }

        return $result;
    }

    /**
     * @return ReportLms[]
     */
    public function getReportsForFinal()
    {
        $reports = [];

        foreach ($this->courseReports as $courseReport) {
            if ($courseReport->isUseForFinal() && $courseReport->getSourceOf() != self::SOURCE_OF_FINAL_VOTE) {
                $reports[] = $courseReport;
            }
        }

        return $reports;
    }

    /**
     * @param $idCourse
     *
     * @return ReportLms
     */
    public static function getReportFinalScore($idCourse)
    {
        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
		FROM %lms_coursereport
		WHERE id_course = '" . $idCourse . "' AND source_of = '" . self::SOURCE_OF_FINAL_VOTE . "' AND id_source = '0'";

        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {
            $report = new ReportLms($info_report['id_report'], $info_report['title'], $info_report['max_score'], $info_report['required_score'], $info_report['weight'], $info_report['show_to_user'], $info_report['use_for_final'], $info_report['source_of'], $info_report['id_source']);
        }

        return $report;
    }

    public function getReportsId($sourceOf = null)
    {
        $responseArray = [];

        foreach ($this->courseReports as $courseReport) {
            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {
                $responseArray[] = $courseReport->getIdReport();
            }
        }

        return $responseArray;
    }

    public function getSourcesId($sourceOf = null)
    {
        $responseArray = [];

        if (count($this->courseReports) == 0) {
            $this->grabCourseReports();
        }
        foreach ($this->courseReports as $courseReport) {
            if ($sourceOf === null || $courseReport->getSourceOf() === $sourceOf) {
                $responseArray[] = $courseReport->getIdSource();
            }
        }

        return $responseArray;
    }
}
