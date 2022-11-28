<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningTesttrack
 *
 * @ORM\Table(name="learning_testtrack")
 * @ORM\Entity
 */
class LearningTesttrack
{
    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtrack;

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
     * @ORM\Column(name="idTest", type="integer", nullable=false)
     */
    private $idtest = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_attempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateAttempt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_attempt_mod", type="datetime", nullable=true)
     */
    private $dateAttemptMod;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_end_attempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateEndAttempt = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="last_page_seen", type="integer", nullable=false)
     */
    private $lastPageSeen = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="last_page_saved", type="integer", nullable=false)
     */
    private $lastPageSaved = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="number_of_save", type="integer", nullable=false)
     */
    private $numberOfSave = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="number_of_attempt", type="integer", nullable=false)
     */
    private $numberOfAttempt = '0';

    /**
     * @var float|null
     *
     * @ORM\Column(name="score", type="float", precision=10, scale=0, nullable=true)
     */
    private $score;

    /**
     * @var float
     *
     * @ORM\Column(name="bonus_score", type="float", precision=10, scale=0, nullable=false)
     */
    private $bonusScore = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="score_status", type="string", length=0, nullable=false, options={"default"="not_complete"})
     */
    private $scoreStatus = 'not_complete';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="attempts_for_suspension", type="integer", nullable=false, options={"unsigned"=true})
     */
    private $attemptsForSuspension = '0';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="suspended_until", type="datetime", nullable=true)
     */
    private $suspendedUntil;


}
