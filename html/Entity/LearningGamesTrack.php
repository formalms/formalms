<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGamesTrack
 *
 * @ORM\Table(name="learning_games_track", indexes={@ORM\Index(name="idReference", columns={"idReference"}), @ORM\Index(name="idUser", columns={"idUser"})})
 * @ORM\Entity
 */
class LearningGamesTrack
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
     * @ORM\Column(name="idReference", type="integer", nullable=false)
     */
    private $idreference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idTrack", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idtrack = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", length=20, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $objecttype = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAttempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateattempt = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     */
    private $status = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="firstAttempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $firstattempt = '0000-00-00 00:00:00';

    /**
     * @var int|null
     *
     * @ORM\Column(name="current_score", type="integer", nullable=true)
     */
    private $currentScore;

    /**
     * @var int|null
     *
     * @ORM\Column(name="max_score", type="integer", nullable=true)
     */
    private $maxScore;

    /**
     * @var int
     *
     * @ORM\Column(name="num_attempts", type="integer", nullable=false)
     */
    private $numAttempts = '0';


}
