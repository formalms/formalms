<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceTeleskillLog
 *
 * @ORM\Table(name="conference_teleskill_log", indexes={
 *     @ORM\Index(name="room_id_idx", columns={"roomid"}),
 *     @ORM\Index(name="id_user_idx", columns={"idUser"})
 * })
 * @ORM\Entity
 */
class ConferenceTeleskillLog
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
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $roomid = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="role", type="integer", nullable=false)
     */
    private $role = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $date = '0000-00-00 00:00:00';

    /**
     * @var int
     *
     * @ORM\Column(name="duration", type="integer", nullable=false)
     */
    private $duration = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="access", type="integer", nullable=false)
     */
    private $access = '0';


}
