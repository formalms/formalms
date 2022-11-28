<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReservationPerm
 *
 * @ORM\Table(name="learning_reservation_perm")
 * @ORM\Entity
 */
class LearningReservationPerm
{
    /**
     * @var int
     *
     * @ORM\Column(name="event_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $eventId = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $userIdst = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="perm", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $perm = '';


}
