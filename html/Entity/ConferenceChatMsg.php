<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ConferenceChatMsg
 *
 * @ORM\Table(name="conference_chat_msg")
 * @ORM\Entity
 */
class ConferenceChatMsg
{
    /**
     * @var int
     *
     * @ORM\Column(name="msg_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $msgId;

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_room", type="integer", nullable=false)
     */
    private $idRoom = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="userid", type="string", length=255, nullable=false)
     */
    private $userid = '';

    /**
     * @var int|null
     *
     * @ORM\Column(name="send_to", type="integer", nullable=true)
     */
    private $sendTo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $sentDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=65535, nullable=false)
     */
    private $text;


}
