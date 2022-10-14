<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceTeleskillRoom
 *
 * @ORM\Table(name="conference_teleskill_room", indexes={
 *     @ORM\Index(name="room_id_idx", columns={"roomId"})
 * })
 * @ORM\Entity
 */
class ConferenceTeleskillRoom
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
     * @ORM\Column(name="roomid", type="integer", nullable=false)
     
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
