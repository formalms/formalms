<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceTeleskill
 *
 * @ORM\Table(name="conference_teleskill", indexes={@ORM\Index(name="idConference", columns={"idConference"})})
 * @ORM\Entity
 */
class ConferenceTeleskill
{
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
     * @ORM\Column(name="idConference", type="bigint", nullable=false)
     */
    private $idconference = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="roomid", type="bigint", nullable=false)
     */
    private $roomid = '0';


}
