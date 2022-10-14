<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGamesTrack
 *
 * @ORM\Table(name="learning_games_track", indexes={
 *      @ORM\Index(name="idReference", columns={"idReference"}), 
 *      @ORM\Index(name="idUser", columns={"idUser"}),
 *      @ORM\Index(name="id_track_idx", columns={"idTrack"}), 
 *      @ORM\Index(name="object_type_idx", columns={"objectType"})
 * })
 * @ORM\Entity
 */
class LearningGamesTrack
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
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
