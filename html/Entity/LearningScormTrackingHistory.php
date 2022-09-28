<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormTrackingHistory
 *
 * @ORM\Table(name="learning_scorm_tracking_history")
 * @ORM\Entity
 */
class LearningScormTrackingHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_tracking", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idscormTracking = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_action", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $dateAction = '0000-00-00 00:00:00';

    /**
     * @var float|null
     *
     * @ORM\Column(name="score_raw", type="float", precision=10, scale=0, nullable=true)
     */
    private $scoreRaw;

    /**
     * @var float|null
     *
     * @ORM\Column(name="score_max", type="float", precision=10, scale=0, nullable=true)
     */
    private $scoreMax;

    /**
     * @var string|null
     *
     * @ORM\Column(name="session_time", type="string", length=15, nullable=true)
     */
    private $sessionTime;

    /**
     * @var string
     *
     * @ORM\Column(name="lesson_status", type="string", length=24, nullable=false)
     */
    private $lessonStatus = '';


}
