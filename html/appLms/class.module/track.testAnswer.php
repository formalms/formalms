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

class Track_TestAnswer
{

    protected $trackId;

    protected $questId;

    protected $answerId;

    protected $score;

    protected $moreInfo;

    /**
     * Track_TestAnswer constructor.
     * @param $id
     */
    function __construct($trackId, $questId, $answerId, $score, $moreInfo)
    {
        $this->trackId = $trackId;
        $this->questId = $questId;
        $this->answerId = $answerId;
        $this->score = $score;
        $this->moreInfo = $moreInfo;
    }

    public function getQuestion(){
        $question =  new Question($this->questId);
        return $question;
    }

    /**
     * @return mixed
     */
    public function getTrackId()
    {
        return $this->trackId;
    }

    /**
     * @param mixed $trackId
     */
    public function setTrackId($trackId)
    {
        $this->trackId = $trackId;
    }

    /**
     * @return mixed
     */
    public function getQuestId()
    {
        return $this->questId;
    }

    /**
     * @param mixed $questId
     */
    public function setQuestId($questId)
    {
        $this->questId = $questId;
    }

    /**
     * @return mixed
     */
    public function getAnswerId()
    {
        return $this->answerId;
    }

    /**
     * @param mixed $answerId
     */
    public function setAnswerId($answerId)
    {
        $this->answerId = $answerId;
    }

    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param mixed $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getMoreInfo()
    {
        return $this->moreInfo;
    }

    /**
     * @param mixed $moreInfo
     */
    public function setMoreInfo($moreInfo)
    {
        $this->moreInfo = $moreInfo;
    }

}

?>
