<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceRoom
 *
 * @ORM\Table(name="conference_room", indexes={@ORM\Index(name="idCourse", columns={"idCourse"})})
 * @ORM\Entity
 */
class ConferenceRoom
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
     * @ORM\Column(name="idCal", type="bigint", nullable=false)
     */
    private $idcal = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="bigint", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idSt", type="bigint", nullable=false)
     */
    private $idst = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="room_type", type="string", length=255, nullable=true)
     */
    private $roomType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="starttime", type="bigint", nullable=true)
     */
    private $starttime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="endtime", type="bigint", nullable=true)
     */
    private $endtime;

    /**
     * @var int|null
     *
     * @ORM\Column(name="meetinghours", type="integer", nullable=true)
     */
    private $meetinghours;

    /**
     * @var int|null
     *
     * @ORM\Column(name="maxparticipants", type="integer", nullable=true)
     */
    private $maxparticipants;

    /**
     * @var bool
     *
     * @ORM\Column(name="bookable", type="boolean", nullable=false)
     */
    private $bookable = '0';


}
