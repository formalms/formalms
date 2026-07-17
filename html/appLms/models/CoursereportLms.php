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
    protected $idCourse;

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
        $org_tests_raw = $report_man->getTest();
        $org_tests = $this->extractNumericIds($org_tests_raw);

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

        // Ensure $stats is a valid array to prevent wrong evaluation records
        if (!is_array($stats)) {
            // If query failed or returned invalid data, initialize with safe defaults
            $stats = [
                'final_count' => 0,
                'total_count' => 0,
                'test_reports' => null
            ];
        }
        
        $final_score_report = (int)($stats['final_count'] ?? 0);
        $tot_report = (int)($stats['total_count'] ?? 0);

        // Add final vote if missing
        if ($final_score_report === 0) {
            $report_man->addFinalVoteToReport();
        }

        // Parse existing test reports
        $included_test = [];
        $test_reports_string = $stats['test_reports'] ?? null;
        if (!empty($test_reports_string)) {
            $test_reports = explode(',', $test_reports_string);
            foreach ($test_reports as $test_report) {
                if (!empty($test_report)) {
                    [$id_report, $id_source] = explode(':', $test_report);
                    $included_test[$id_source] = $id_source;
                }
            }
        }

        // Manage test reports
        $reports_modified = false;
        if ($tot_report === 0) {
            $report_man->initializeCourseReport($org_tests);
            $reports_modified = true;
        } else {
            // Compare test IDs properly using normalized IDs
            $org_test_ids = $org_tests;
            $test_to_add = array_diff($org_test_ids, $included_test);
            $test_to_del = array_diff($included_test, $org_test_ids);

            if (!empty($test_to_add) || !empty($test_to_del)) {
                if (!empty($test_to_add)) {
                    $report_man->addTestToReport($test_to_add, 1);
                }
                if (!empty($test_to_del)) {
                    $report_man->delTestToReport($test_to_del);
                }
                $reports_modified = true;
            }
        }

        // Only update test reports if they were modified
        if ($reports_modified) {
            $report_man->updateTestReport($org_tests);
        }

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


        //echo($query_report); exit;
        $re_report = sql_query($query_report);

        foreach ($re_report as $infoReport) {
            $report = new ReportLms($infoReport['id_report']);
            $this->courseReports[] = $report;
        }
    }

    /**
     * Clean up orphaned records from learning_coursereport table
     * that reference objects not belonging to this course
     * @todo to be removed in future releases
     */
    private function cleanupOrphanedReports($valid_tests)
    {
        // Build a conservative list of valid test IDs (ints) from both keys and values
        $valid_ids = $this->extractNumericIds(is_array($valid_tests) ? $valid_tests : []);
        if (empty($valid_ids)) {
            // If we cannot determine valid test IDs, skip cleanup to avoid accidental deletions
            return;
        }
        $valid_test_list = implode(',', $valid_ids);
        $cleanup_query = "
                DELETE FROM %lms_coursereport 
                WHERE id_course = '" . (int)$this->idCourse . "' 
                AND source_of = '" . self::SOURCE_OF_TEST . "'
                AND id_source NOT IN (" . $valid_test_list . ")
            ";

        sql_query($cleanup_query);
    }

    /**
     * Normalize a list/assoc array into a flat list of unique integer IDs.
     * Accepts arrays like [12, 34] or [12 => 12, 'x' => 34, 56 => '56'].
     * For indexed lists, uses only values; for associative arrays, uses numeric values
     * and, if needed, numeric keys. Finally validates IDs against existing tests.
     * @param array $arr
     * @return array
     */
    private function extractNumericIds($arr)
    {
        if (!is_array($arr)) {
            return [];
        }

        $keys = array_keys($arr);
        $isList = ($keys === array_keys($keys));

        $ids = [];
        if ($isList) {
            // Indexed list: consider only numeric values
            foreach ($arr as $v) {
                if (is_numeric($v) && (int)$v > 0) {
                    $ids[] = (int)$v;
                }
            }
        } else {
            // Associative: prefer numeric values; fallback to numeric keys
            foreach ($arr as $k => $v) {
                if (is_numeric($v) && (int)$v > 0) {
                    $ids[] = (int)$v;
                } elseif (is_numeric($k) && (int)$k > 0) {
                    $ids[] = (int)$k;
                }
            }
        }

        // Unique list
        $ids = array_values(array_unique($ids));

        // Validate against existing tests to avoid phantom IDs
        // IMPORTANT: Only filter out invalid IDs, but don't empty the entire list if validation fails
        if (!empty($ids)) {
            $gtm = new GroupTestManagement();
            $info = $gtm->getTestInfo($ids);
            // Only apply intersection if we got valid test info back
            // If getTestInfo returns empty array, keep original IDs to avoid accidental mass deletion
            if (!empty($info)) {
                $ids = array_values(array_intersect($ids, array_keys($info)));
            }
            // If $info is empty but $ids was not, something went wrong - keep original $ids for safety
        }

        return $ids;
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

    public static function getInsertQueryCourseReportAsForeignKey(array $params): string
    {
        // DUAL table is used here as a substitute for foreign key constraints,
        // allowing EXISTS clause evaluation on non-existent tables
        return 'INSERT IGNORE INTO %lms_coursereport 
                        (id_course, 
                        title,
                        max_score, 
                        required_score, 
                        weight, 
                        show_to_user, 
                        use_for_final, 
                        source_of, 
                        id_source, 
                        sequence)
        SELECT "' . $params['idCourse'] . '",
                "' . $params['title'] . '",
                "' . $params['max_score'] . '",
                "' . $params['required_score'] . '",
                "' . $params['weight'] . '",
                "' . $params['show_to_user'] . '",
                "' . $params['use_for_final'] . '",
                "' . $params['source_of'] . '",
                "' . $params['idSource'] . '",
                "' . $params['sequence'] . '"
        FROM dual
        WHERE EXISTS (
            SELECT 1
            FROM %lms_organization 
            WHERE idResource = "' . $params['idSource'] . '"
            AND idCourse = ' . $params['idCourse'] . '
        )';
    }
}
