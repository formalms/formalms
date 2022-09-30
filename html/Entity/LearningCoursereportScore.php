<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCoursereportScore
 *
 * @ORM\Table(name="learning_coursereport_score")
 * @ORM\Entity
 */
class LearningCoursereportScore
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
     * @ORM\Column(name="id_report", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idReport = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idUser = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_attempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateAttempt = '0000-00-00 00:00:00';

    /**
     * @var float
     *
     * @ORM\Column(name="score", type="float", precision=5, scale=2, nullable=false, options={"default"="0.00"})
     */
    private $score = 0.00;

    /**
     * @var string
     *
     * @ORM\Column(name="score_status", type="string", length=0, nullable=false, options={"default"="valid"})
     */
    private $scoreStatus = 'valid';

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;


}
