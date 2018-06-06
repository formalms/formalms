<?php

class ReportLms extends Model
{
    /**
     * @var integer
     */
    protected $idReport;

    /**
     * @var integer
     */
    protected $idCourse;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var integer
     */
    protected $maxScore;

    /**
     * @var integer
     */
    protected $requiredScore;

    /**
     * @var integer
     */
    protected $weight;

    /**
     * @var boolean
     */
    protected $showToUser;

    /**
     * @var boolean
     */
    protected $useForFinal;

    /**
     * @var string
     */
    protected $sourceOf;

    /**
     * @var integer
     */
    protected $sequence;

    /**
     * @var integer
     */
    protected $idSource;

    /**
     * @var bool
     */
    protected $showInDetail;

    public function __construct($id_report = null, $title = '', $max_score = true, $required_score = true, $weight = true, $show_to_user = false, $use_for_final = false, $source_of = '', $id_source = 0, $show_in_detail = true)
    {
        if ($id_report !== NULL) {
            $query_report = "SELECT id_report,id_course, title, max_score, required_score, weight, show_to_user, use_for_final, source_of, id_source, show_in_detail
                        FROM " . $GLOBALS['prefix_lms'] . "_coursereport
	                    WHERE id_report = '" . $id_report . "'";

            $res = sql_query($query_report);
            if ($res) {
                list(
                    $this->idReport,
                    $this->idCourse,
                    $this->title,
                    $this->maxScore,
                    $this->requiredScore,
                    $this->weight,
                    $showToUser,
                    $useForFinal,
                    $this->sourceOf,
                    $this->idSource,
                    $showInDetail
                    ) = sql_fetch_row($res);

                $this->showToUser = $showToUser === 'true' ? true : false;
                $this->useForFinal = $useForFinal === 'true' ? true : false;
                $this->showInDetail = $showInDetail === '1' ? true : false;
            }
        } else {
            $this->idReport = $id_report;
            $this->title = $title;
            $this->maxScore = $max_score;
            $this->requiredScore = $required_score;
            $this->weight = $weight;
            $this->showToUser = ($show_to_user === 'true' ? true : false);
            $this->useForFinal = ($use_for_final === 'true' ? true : false);
            $this->sourceOf = $source_of;
            $this->idSource = $id_source;
            $this->showInDetail = $show_in_detail;
        }
    }

    /**
     * @param int $idReport
     */
    public function setIdReport($idReport)
    {
        $this->idReport = $idReport;
    }

    /**
     * @return int
     */
    public function getIdReport()
    {
        return $this->idReport;
    }

    /**
     * @return int
     */
    public function getIdCourse()
    {
        return $this->idCourse;
    }

    /**
     * @param int $idCourse
     * @return ReportLms
     */
    public function setIdCourse($idCourse)
    {
        $this->idCourse = $idCourse;
        return $this;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $maxScore
     */
    public function setMaxScore($maxScore)
    {
        $this->maxScore = $maxScore;
    }

    /**
     * @return int
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }

    /**
     * @param int $requiredScore
     */
    public function setRequiredScore($requiredScore)
    {
        $this->requiredScore = $requiredScore;
    }

    /**
     * @return int
     */
    public function getRequiredScore()
    {
        return $this->requiredScore;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param boolean $showToUser
     */
    public function setShowToUser($showToUser)
    {
        $this->showToUser = $showToUser;
    }

    /**
     * @return boolean
     */
    public function isShowToUser()
    {
        return $this->showToUser;
    }

    /**
     * @return string
     */
    public function isShowToUserToString()
    {
        return ($this->useForFinal ? 'true' : 'false');
    }

    /**
     * @param boolean $useForFinal
     */
    public function setUseForFinal($useForFinal)
    {
        $this->useForFinal = $useForFinal;
    }

    /**
     * @return boolean
     */
    public function isUseForFinal()
    {
        return $this->useForFinal;
    }

    /**
     * @return string
     */
    public function isUseForFinalToString()
    {
        return ($this->useForFinal ? 'true' : 'false');
    }

    /**
     * @param int $sequence
     */
    public function setSequence($sequence)
    {
        $this->sequence = $sequence;
    }

    /**
     * @return int
     */
    public function getSequence()
    {
        return $this->sequence;
    }

    /**
     * @param string $sourceOf
     */
    public function setSourceOf($sourceOf)
    {
        $this->sourceOf = $sourceOf;
    }

    /**
     * @return string
     */
    public function getSourceOf()
    {
        return $this->sourceOf;
    }

    /**
     * @param int $idSource
     */
    public function setIdSource($idSource)
    {
        $this->idSource = $idSource;
    }

    /**
     * @return int
     */
    public function getIdSource()
    {
        return $this->idSource;
    }

    /**
     * @return bool
     */
    public function isShowInDetail()
    {
        return $this->showInDetail;
    }

    /**
     * @param bool $showInDetail
     * @return ReportLms
     */
    public function setShowInDetail($showInDetail)
    {
        $this->showInDetail = $showInDetail;
        return $this;
    }

    public function updateShowInDetail()
    {
        $query_report = 'UPDATE `%lms_coursereport` SET  `show_in_detail` = ' . ($this->showInDetail ? 1 : 0) . ' WHERE `id_report` = ' . $this->idReport;

        $res = sql_query($query_report);

        if ($res){
            return true;
        }
        return false;
    }
}