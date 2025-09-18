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

require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.coursereport.php');
require_once \FormaLms\lib\Forma::inc(_lms_ . '/lib/lib.test.php');

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

    public const TEST_STATUS_VARIANZA = 'varianza';

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
        $report_man = new CourseReportManager($this->idCourse);
        $org_tests = $report_man->getTest();

        // Clean up orphaned records first - remove records referencing non-existent course objects
        $this->cleanupOrphanedReports($org_tests);

        // Single optimized query to get all necessary counts and data
        $query_stats = "
            SELECT 
                COUNT(CASE WHEN source_of = '" . self::SOURCE_OF_FINAL_VOTE . "' THEN 1 END) as final_count,
                COUNT(*) as total_count,
                GROUP_CONCAT(
                    CASE WHEN source_of = '" . self::SOURCE_OF_TEST . "' 
                    THEN CONCAT(id_report, ':', id_source) 
                    END
                ) as test_reports
            FROM %lms_coursereport 
            WHERE id_course = '" . (int)$this->idCourse . "'
        ";

        $stats_result = sql_query($query_stats);
        $stats = sql_fetch_assoc($stats_result);
        
        $final_score_report = (int)$stats['final_count'];
        $tot_report = (int)$stats['total_count'];

        // Add final vote if missing
        if ($final_score_report === 0) {
            $report_man->addFinalVoteToReport();
        }

        // Handle case where only final vote exists
        if ($tot_report === 1 && $final_score_report === 1) {
            sql_query("DELETE FROM %lms_coursereport WHERE id_course = '" . (int)$this->idCourse . "'");
            $tot_report = 0;
        }

        // Parse existing test reports
        $included_test = [];
        if (!empty($stats['test_reports'])) {
            $test_reports = explode(',', $stats['test_reports']);
            foreach ($test_reports as $test_report) {
                if (!empty($test_report)) {
                    [$id_report, $id_source] = explode(':', $test_report);
                    $included_test[$id_source] = $id_source;
                }
            }
        }

        // Manage test reports
        if ($tot_report === 0) {
            $report_man->initializeCourseReport($org_tests);
        } else {
            $test_to_add = array_diff($org_tests, $included_test);
            $test_to_del = array_diff($included_test, $org_tests);

            if (!empty($test_to_add) || !empty($test_to_del)) {
                if (!empty($test_to_add)) {
                    $report_man->addTestToReport($test_to_add, 1);
                }
                if (!empty($test_to_del)) {
                    $report_man->delTestToReport($test_to_del);
                }
            }
        }

        $report_man->updateTestReport($org_tests);

        // Build final report query with proper escaping
        $query_report = "SELECT id_report FROM %lms_coursereport WHERE id_course = '" . (int)$this->idCourse . "'";

        if (!is_null($this->idReport)) {
            $query_report .= " AND id_report = '" . (int)$this->idReport . "'";
        }

        if (!is_null($this->sourceOf)) {
            if (is_array($this->sourceOf)) {
                $escaped_sources = array_map(function($s) { 
                    return "'" . sql_escape_string($s) . "'"; 
                }, $this->sourceOf);
                $query_report .= " AND source_of IN (" . implode(",", $escaped_sources) . ")";
            } else {
                $query_report .= " AND source_of = '" . sql_escape_string($this->sourceOf) . "'";
            }
        }

        if (!is_null($this->idSource)) {
            $query_report .= " AND id_source = '" . (int)$this->idSource . "'";
        }

        $query_report .= ' ORDER BY sequence ';

        $re_report = sql_query($query_report);

        foreach ($re_report as $infoReport) {
            $report = new ReportLms($infoReport['id_report']);
            $this->courseReports[] = $report;
        }
    }

    /**
     * Clean up orphaned records from learning_coursereport table
     * that reference objects not belonging to this course
     */
    private function cleanupOrphanedReports($valid_tests)
    {
        // Remove test reports that reference non-existent or invalid test objects
        if (!empty($valid_tests)) {
            $valid_test_ids = array_map('intval', array_keys($valid_tests));
            $valid_test_list = implode(',', $valid_test_ids);
            
            $cleanup_query = "
                DELETE FROM %lms_coursereport 
                WHERE id_course = '" . (int)$this->idCourse . "' 
                AND source_of = '" . self::SOURCE_OF_TEST . "'
                AND id_source NOT IN (" . $valid_test_list . ")
            ";
        } else {
            // If no valid tests, remove all test reports for this course
            $cleanup_query = "
                DELETE FROM %lms_coursereport 
                WHERE id_course = '" . (int)$this->idCourse . "' 
                AND source_of = '" . self::SOURCE_OF_TEST . "'
            ";
        }
        
        sql_query($cleanup_query);
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

        foreach ($re_report as $info_report) {
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
