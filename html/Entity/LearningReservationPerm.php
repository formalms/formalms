<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReservationPerm
 *
 * @ORM\Table(name="learning_reservation_perm", indexes={
 *      @ORM\Index(name="event_id_idx", columns={"event_id"}),
 *      @ORM\Index(name="user_idst_idx", columns={"user_idst"}),
 *      @ORM\Index(name="perm_idx", columns={"perm"})
 * })
 * @ORM\Entity
 */
class LearningReservationPerm
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
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     
     */
    private $eventId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     
     */
    private $userIdst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="perm", type="string", length=255, nullable=false)
     
     */
    private $perm = '';


}
