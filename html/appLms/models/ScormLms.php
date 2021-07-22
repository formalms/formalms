<?php

/**
 * Created by PhpStorm.
 * User: giuseppenucifora
 * Date: 11/12/2016
 * Time: 15:22
 */
class ScormLms extends Model
{

    protected $idSource;

    protected $idUser;

    protected $passed;

    protected $notPassed;

    protected $notChecked;

    protected $average;

    protected $varianza;

    protected $maxScore;

    protected $minScore;

    protected $scoreRaw;

    protected $idTrack;

    public function __construct($id_source, $idUser = false)
    {

        $this->idSource = $id_source;
        $this->idUser = $idUser;
        $this->passed = 0;
        $this->notPassed = 0;
        $this->average = 0;
        $this->varianza = 0;
        $this->maxScore = 0;
        $this->minScore = 0;

        $query_report = "
						SELECT *
						FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking
						WHERE idscorm_item = '" . $this->idSource . "'";

        if ($idUser) {
            $query_report .= " AND idUser = '" . $this->idUser . "'";
        }

        $passed = 0;
        $total = 0;
        $media = 0;
        $varianza = 0;
        $votomassimo = 0;
        $votominimo = 9999;
        $result = sql_query($query_report);
        while ($report = sql_fetch_assoc($result)) {
            if ($report['score_raw'] != NULL) {
                if ($report['score_raw'] > $votomassimo) {
                    $votomassimo = $report['score_raw'];
                }
                if ($report['score_raw'] < $votominimo) {
                    $votominimo = $report['score_raw'];
                }

                $media = $media + $report['score_raw'];
                $total = $total + 1;

                if ($report['lesson_status'] == 'passed') {
                    $passed++;
                }
            }

            $this->idTrack = (isset($report['idscorm_tracking']) ? $report['idscorm_tracking'] : 0);
        }

        $media = ($total == 0 ? '0' : $media / $total);
        $result = sql_query($query_report);
        $var = 0;
        while ($report = sql_fetch_assoc($result))
            if ($report['score_raw'] != NULL) {
                $var = $var + pow($media - $report['score_raw'], 2);
                if ($this->idUser) {
                    $this->scoreRaw = $report['score_raw'];
                }
            } else {
                $this->scoreRaw = "-";
            }
        $varianza = ($total == 0 ? '0' : floor($var / $total));

        if ($votominimo == 9999) {
            $votominimo = "";
        }

        $this->passed = $passed;
        $this->notPassed = $total - $passed;
        $this->notChecked = "-";
        $this->average = $media;
        $this->varianza = $varianza;
        $this->maxScore = $votomassimo;
        $this->minScore = $votominimo;
    }

    /**
     * @return mixed
     */
    public function getIdSource()
    {
        return $this->idSource;
    }

    /**
     * @return mixed
     */
    public function getIdUser()
    {
        return $this->idUser;
    }

    /**
     * @return mixed
     */
    public function getPassed()
    {
        return $this->passed;
    }

    /**
     * @return mixed
     */
    public function getNotPassed()
    {
        return $this->notPassed;
    }

    /**
     * @return mixed
     */
    public function getNotChecked()
    {
        return $this->notChecked;
    }

    /**
     * @return float|int|string
     */
    public function getAverage()
    {
        return $this->average;
    }

    /**
     * @return mixed
     */
    public function getVarianza()
    {
        return $this->varianza;
    }

    /**
     * @return mixed
     */
    public function getMaxScore()
    {
        return $this->maxScore;
    }

    /**
     * @return mixed
     */
    public function getMinScore()
    {
        return $this->minScore;
    }

    /**
     * @return string
     */
    public function getScoreRaw()
    {
        return $this->scoreRaw;
    }

    /**
     * @return int
     */
    public function getIdTrack()
    {
        return $this->idTrack;
    }

    public function getHistory(){
        $query_report = "SELECT * FROM " . $GLOBALS['prefix_lms'] . "_scorm_tracking_history
						                    WHERE idscorm_tracking = '" . $this->idTrack . "'";

        //echo $query_report;
        $query = sql_query($query_report);
        $num = sql_num_rows($query);

        return $num;
    }

}