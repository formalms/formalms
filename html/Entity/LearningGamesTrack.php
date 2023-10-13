<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;
use FormaLms\Entity\Timestamps;

/**
 * LearningGamesTrack
 *
 * @ORM\Table(name="learning_games_track", 
 *      indexes={
 *              @ORM\Index(name="idReference", columns={"idReference"}), 
 *              @ORM\Index(name="idUser", columns={"idUser"}),
 *              @ORM\Index(name="object_type_idx", columns={"objectType"}),
 *              @ORM\Index(name="id_track_idx", columns={"idTrack"})
 * })
 * @ORM\Entity
 */
class LearningGamesTrack
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
     */
    private $idtrack = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="objectType", type="string", length=20, nullable=false)

     */
    private $objecttype = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateAttempt", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $dateattempt = null;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     */
    private $status = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="firstAttempt", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $firstattempt = null;

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
