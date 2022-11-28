<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormTracking
 *
 * @ORM\Table(name="learning_scorm_tracking", uniqueConstraints={@ORM\UniqueConstraint(name="Unique_tracking_usersco", columns={"idUser", "idReference", "idscorm_item"})}, indexes={@ORM\Index(name="idUser", columns={"idUser"}), @ORM\Index(name="idscorm_resource", columns={"idReference"})})
 * @ORM\Entity
 */
class LearningScormTracking
{
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_tracking", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormTracking;

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idReference", type="integer", nullable=false)
     */
    private $idreference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_item", type="integer", nullable=false)
     */
    private $idscormItem = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="user_name", type="string", length=255, nullable=true)
     */
    private $userName;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lesson_location", type="string", length=255, nullable=true)
     */
    private $lessonLocation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="credit", type="string", length=24, nullable=true)
     */
    private $credit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lesson_status", type="string", length=24, nullable=true)
     */
    private $lessonStatus;

    /**
     * @var string|null
     *
     * @ORM\Column(name="entry", type="string", length=24, nullable=true)
     */
    private $entry;

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
     * @var float|null
     *
     * @ORM\Column(name="score_min", type="float", precision=10, scale=0, nullable=true)
     */
    private $scoreMin;

    /**
     * @var string|null
     *
     * @ORM\Column(name="total_time", type="string", length=15, nullable=true, options={"default"="0000:00:00.00"})
     */
    private $totalTime = '0000:00:00.00';

    /**
     * @var string|null
     *
     * @ORM\Column(name="lesson_mode", type="string", length=24, nullable=true)
     */
    private $lessonMode;

    /**
     * @var string|null
     *
     * @ORM\Column(name="exit", type="string", length=24, nullable=true)
     */
    private $exit;

    /**
     * @var string|null
     *
     * @ORM\Column(name="session_time", type="string", length=15, nullable=true)
     */
    private $sessionTime;

    /**
     * @var string|null
     *
     * @ORM\Column(name="suspend_data", type="blob", length=65535, nullable=true)
     */
    private $suspendData;

    /**
     * @var string|null
     *
     * @ORM\Column(name="launch_data", type="blob", length=65535, nullable=true)
     */
    private $launchData;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comments", type="blob", length=65535, nullable=true)
     */
    private $comments;

    /**
     * @var string|null
     *
     * @ORM\Column(name="comments_from_lms", type="blob", length=65535, nullable=true)
     */
    private $commentsFromLms;

    /**
     * @var string|null
     *
     * @ORM\Column(name="xmldata", type="blob", length=0, nullable=true)
     */
    private $xmldata;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="first_access", type="datetime", nullable=true)
     */
    private $firstAccess;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_access", type="datetime", nullable=true)
     */
    private $lastAccess;


}
