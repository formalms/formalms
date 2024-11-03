<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceBooking
 *
 * @ORM\Table(name="conference_booking")
 * @ORM\Entity
 */
class ConferenceBooking
{
    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="booking_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $bookingId;

    /**
     * @var int
     *
     * @ORM\Column(name="room_id", type="integer", nullable=false)
     */
    private $roomId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=255, nullable=false)
     */
    private $platform = '';

    /**
     * @var string
     *
     * @ORM\Column(name="module", type="string", length=100, nullable=false)
     */
    private $module = '';

    /**
     * @var int
     *
     * @ORM\Column(name="user_idst", type="integer", nullable=false)
     */
    private $userIdst = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="approved", type="boolean", nullable=false)
     */
    private $approved = '0';


}
