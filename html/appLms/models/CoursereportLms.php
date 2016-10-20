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
     * @param $idCourse
     * @param null $idReport
     * @param null $sourceOf
     * @param null $idSource
     */

    public function __construct($idCourse, $idReport = null, $sourceOf = null, $idSource = null)
    {
        $this->idCourse = $idCourse;

        $this->idReport = $idReport;

        $this->sourceOf = $sourceOf;

        $this->idSource = $idSource;

        $this->courseReports = array();

        $this->grabCourseReports();

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

    private function grabCourseReports() {

        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
                        FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	                    WHERE id_course = '" . $this->idCourse ."'";

        if (!is_null($this->idReport)) {
            $query_report .= " AND id_report = '". $this->idReport ."'";
        }

        if (!is_null($this->sourceOf)) {

            if (is_array($this->sourceOf)){

                $query_report .=" AND source_of IN ('". implode($this->sourceOf,"','") ."')";
            }
            else {
                $query_report .= " AND source_of = '". $this->sourceOf ."'";
            }
        }

        if (!is_null($this->idSource)) {
            $query_report .= " AND id_source = '". $this->idSource ."'";
        }

        $query_report .= " ORDER BY sequence ";

        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {

            $report = new ReportLms($info_report['id_report'], $info_report['title'], $info_report['max_score'], $info_report['required_score'], $info_report['weight'], $info_report['show_to_user'], $info_report['use_for_final'], $info_report['source_of'], $info_report['id_source']);

            $this->courseReports[] = $report;
        }
    }

    /**
     * Returns reports included for idcourse filrt
     *
     * @return  ReportLms[]
     */
    public function getReportsFilteredBySourceOf($sourceOf = null)
    {
        $result = array();

        foreach ($this->courseReports as $courseReport) {

            if ($courseReport->getSourceOf() === $sourceOf){
                $result[] = $courseReport;
            }
        }

        return $result;
    }

    /**
     * @return ReportLms[]
     */
    public function getReportsForFinal(){

        $reports = array();

        foreach ($this->courseReports as $courseReport){

            if ($courseReport->isUseForFinal() && $courseReport->getSourceOf() != 'final_vote'){
                $reports[] = $courseReport;
            }
        }

        return $reports;
    }

    /**
     * @param $idCourse
     * @return ReportLms
     */
    public static function getReportFinalScore($idCourse)
    {

        $query_report = "SELECT id_report, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source
		FROM " . $GLOBALS['prefix_lms'] . "_coursereport
		WHERE id_course = '" . $idCourse . "' AND source_of = 'final_vote' AND id_source = '0'";

        $re_report = sql_query($query_report);

        while ($info_report = sql_fetch_assoc($re_report)) {

            $report = new ReportLms($info_report['id_report'], $info_report['title'], $info_report['max_score'], $info_report['required_score'], $info_report['weight'], $info_report['show_to_user'], $info_report['use_for_final'], $info_report['source_of'], $info_report['id_source']);

        }

        return $report;
    }
}