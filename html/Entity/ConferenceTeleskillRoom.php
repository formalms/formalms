<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceTeleskillRoom
 *
 * @ORM\Table(name="conference_teleskill_room")
 * @ORM\Entity
 */
class ConferenceTeleskillRoom
{
    /**
     * @var int
     *
     * @ORM\Column(name="roomid", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
