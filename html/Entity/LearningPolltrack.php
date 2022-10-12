<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningPolltrack
 *
 * @ORM\Table(name="learning_polltrack")
 * @ORM\Entity
 */
class LearningPolltrack
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_track", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idTrack;

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_reference", type="integer", nullable=false)
     */
    private $idReference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_poll", type="integer", nullable=false)
     */
    private $idPoll = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_attempt", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $dateAttempt = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=0, nullable=false, options={"default"="not_complete"})
     */
    private $status = 'not_complete';


}
