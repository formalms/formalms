<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormTrackingHistory
 *
 * @ORM\Table(name="learning_scorm_tracking_history", indexes={
 *      @ORM\Index(name="idscorm_tracking_idx", columns={"idscorm_tracking"}),
 *      @ORM\Index(name="date_action_idx", columns={"date_action"})
 * })
 * @ORM\Entity
 */
class LearningScormTrackingHistory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_tracking", type="integer", nullable=false)
     
     */
    private $idscormTracking = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_action", type="datetime", nullable=true, options={"default"="NULL"})
     
     */
    private $dateAction = null;

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
