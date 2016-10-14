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

    public function __construct($id_report = 0, $title = '', $max_score = true, $required_score = true, $weight = true, $show_to_user = false, $use_for_final = false, $source_of = '', $id_source = 0)
    {
        $this->idReport = $id_report;
        $this->title = $title;
        $this->maxSccore = $max_score;
        $this->requiredScore = $required_score;
        $this->weight = $weight;
        $this->showToUser = $show_to_user === 'true'? true : false;
        $this->useForFinal = $use_for_final === 'true'? true : false;
        $this->sourceOf = $source_of;
        $this->idSource = $id_source;

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
    public function isShowToUserToString() {
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
    public function isUseForFinalToString() {
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

    public function save() {
        
    }
}