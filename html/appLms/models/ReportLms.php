<?php

class ReportLms extends Model
{
    /**
     * @var integer
     */
    protected $idReport;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var integer
     */
    protected $maxSccore;

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


    public function __construct($id_report, $title, $max_score, $required_score, $weight, $show_to_user, $use_for_final, $source_of, $id_source)
    {

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
     * @param int $maxSccore
     */
    public function setMaxSccore($maxSccore)
    {
        $this->maxSccore = $maxSccore;
    }

    /**
     * @return int
     */
    public function getMaxSccore()
    {
        return $this->maxSccore;
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
}