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


    public function __construct()
    {
    }


    /**
     * This method returns count of course report from idCourse
     *
     * @param $idCourse
     * @return mixed
     */
    public function getTotalCourseReport($idCourse)
    {
        $query_tot_report = "SELECT COUNT(*) FROM " . $GLOBALS['prefix_lms'] . "_coursereport  WHERE id_course = '" . $idCourse . "'";
        list($tot_report) = sql_fetch_row(sql_query($query_tot_report));

        return $tot_report;
    }

    /**
     * Returns tests and report included for idcourse
     *
     * @param $idCourse
     * @return array
     */
    public function getTestsAndReports($idCourse) {

        $included_test_and_report = array();

        $query_tests = "SELECT id_report, id_source FROM " . $GLOBALS['prefix_lms'] . "_coursereport WHERE id_course = '" . $idCourse . "' AND source_of = 'test'";

        $re_tests = sql_query($query_tests);

        while (list($id_r, $id_s) = sql_fetch_row($re_tests)) {
            $included_test[$id_s] = $id_s;
            $included_test_report_id[$id_r] = $id_r;
        }

        $included_test_and_report['source'] = $included_test;
        $included_test_and_report['report'] = $included_test_report_id;

        return $included_test_and_report;
    }
}