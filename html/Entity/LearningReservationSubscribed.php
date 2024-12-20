<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReservationSubscribed
 *
 * @ORM\Table(name="learning_reservation_subscribed", indexes={
 *      @ORM\Index(name="idst_user_idx", columns={"idstUser"}),
 *      @ORM\Index(name="id_event_idx", columns={"idEvent"})
 * })
 * @ORM\Entity
 */
class LearningReservationSubscribed
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
     * @ORM\Column(name="idstUser", type="integer", nullable=false)
     
     */
    private $idstuser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)

     
     */
    private $idevent = '0';


}
