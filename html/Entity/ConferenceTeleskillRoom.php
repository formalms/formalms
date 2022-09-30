<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceTeleskillRoom
 *
 * @ORM\Table(name="conference_teleskill_room", indexes={
 *     @ORM\Index(name="room_idx", columns={"room_id"})
 * })
 * @ORM\Entity
 */
class ConferenceTeleskillRoom
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
     * @ORM\Column(name="roomid", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roomid = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="uid", type="integer", nullable=false)
     */
    private $uid = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="zone", type="string", length=255, nullable=false)
     */
    private $zone = '';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $startDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $endDate = '0000-00-00 00:00:00';

    /**
     * @var bool
     *
     * @ORM\Column(name="bookable", type="boolean", nullable=false)
     */
    private $bookable = '0';

    /**
     * @var int|null
     *
     * @ORM\Column(name="capacity", type="integer", nullable=true)
     */
    private $capacity;


}
