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


class CoursereportLms extends Model
{
    /**
     * @var int
     */
    protected $idCourse;


    public function __construct($idCourse)
    {
        $this->idCourse = $idCourse;


    }


    /**
     * This method returns count of course report from idCourse
     *
     * @return integer
     */
    public function getTotalCourseReport()
    {
        $query_tot_report = "SELECT COUNT(*) FROM " . $GLOBALS['prefix_lms'] . "_coursereport  WHERE id_course = '" . $this->idCourse . "'";
        list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

        return $tot_report;
    }

    /**
     * Returns tests included for idcourse
     *
     * @return array
     */
    public function getTests()
    {
        $query_tests = "SELECT id_source FROM " . $GLOBALS['prefix_lms'] . "_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of = 'test'";

        $re_tests = sql_query($query_tests);

        $included_test = array();

        while (list($id_s) = sql_fetch_row($re_tests)) {
            $included_test[$id_s] = $id_s;
        }

        return $included_test;
    }

    public function getTestCoursereport() {

        $query_tests = "SELECT id_report FROM " . $GLOBALS['prefix_lms'] . "_coursereport WHERE id_course = '" . $this->idCourse . "' AND source_of = 'test'";

        $re_tests = sql_query($query_tests);

        $included_test_report_id = array();

        while (list($id_r) = sql_fetch_row($re_tests)) {

            $included_test_report_id[$id_r] = $id_r;
        }

        return $included_test_report_id;
    }

    /**
     * Returns reports included for idcourse
     *
     * @return array
     */
    public function getReports()
    {
        $reports = array();

        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
                        FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	                    WHERE id_course = '" . $this->idCourse . "'
	                    ORDER BY sequence ";
        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {

            list($id_report, $title, $max_score, $required_score, $weight, $show_to_user, $use_for_final, $source_of, $id_source) = $info_report;

            $report = new ReportLms($id_report, $title, $max_score, $required_score, $weight, $show_to_user, $use_for_final, $source_of, $id_source);

            $reports[] = $report;
        }


    }



}