<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTesttrackTimes
 *
 * @ORM\Table(name="learning_testtrack_times", indexes={
 *      @ORM\Index(name="number_time_idx", columns={"number_time"}),
 *      @ORM\Index(name="id_track_idx", columns={"idTrack"}),
 *      @ORM\Index(name="id_test_idx", columns={"idTest"})
 * })
 * @ORM\Entity
 */
class LearningTesttrackTimes
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
     * @var bool
     *
     * @ORM\Column(name="number_time", type="boolean", nullable=false)
     
     */
    private $numberTime = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     
     */
    private $idtrack = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idReference", type="integer", nullable=false)
     */
    private $idreference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTest", type="integer", nullable=false)
     
     */
    private $idtest = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_attempt", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateAttempt = null;

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float", precision=10, scale=0, nullable=false)
     */
    private $score = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="score_status", type="string", length=50, nullable=false)
     */
    private $scoreStatus = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_begin", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateBegin;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $dateEnd;

    /**
     * @var int
     *
     * @ORM\Column(name="time", type="integer", nullable=false)
     */
    private $time;


}
