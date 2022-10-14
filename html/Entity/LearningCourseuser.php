<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCourseuser
 *
 * @ORM\Table(name="learning_courseuser", indexes={
 *      @ORM\Index(name="courseuser_course_idx", columns={"idCourse"}),
 *      @ORM\Index(name="edition_id_idx", columns={"edition_id"}),
 *      @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *      @ORM\Index(name="id_course_idx", columns={"idCourse"})
 * })
 * @ORM\Entity
 */
class LearningCourseuser
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
     * @ORM\Column(name="edition_id", type="integer", nullable=false)
     
     */
    private $editionId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_inscr", type="datetime", nullable=true)
     */
    private $dateInscr;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_first_access", type="datetime", nullable=true)
     */
    private $dateFirstAccess;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_complete", type="datetime", nullable=true)
     */
    private $dateComplete;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="waiting", type="boolean", nullable=false)
     */
    private $waiting = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="subscribed_by", type="integer", nullable=false)
     */
    private $subscribedBy = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="rule_log", type="integer", nullable=true)
     */
    private $ruleLog;

    /**
     * @var int|null
     *
     * @ORM\Column(name="score_given", type="integer", nullable=true)
     */
    private $scoreGiven;

    /**
     * @var string|null
     *
     * @ORM\Column(name="imported_from_connection", type="string", length=255, nullable=true)
     */
    private $importedFromConnection;

    /**
     * @var bool
     *
     * @ORM\Column(name="absent", type="boolean", nullable=false)
     */
    private $absent = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="cancelled_by", type="integer", nullable=false)
     */
    private $cancelledBy = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="new_forum_post", type="integer", nullable=false)
     */
    private $newForumPost = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_begin_validity", type="datetime", nullable=true)
     */
    private $dateBeginValidity;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_expire_validity", type="datetime", nullable=true)
     */
    private $dateExpireValidity;

    /**
     * @var bool
     *
     * @ORM\Column(name="requesting_unsubscribe", type="boolean", nullable=false)
     */
    private $requestingUnsubscribe = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="requesting_unsubscribe_date", type="datetime", nullable=true)
     */
    private $requestingUnsubscribeDate;


}
