<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningInstmsg
 *
 * @ORM\Table(name="learning_instmsg", indexes={@ORM\Index(name="id_sender", columns={"id_sender", "id_receiver"})})
 * @ORM\Entity
 */
class LearningInstmsg
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_msg", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idMsg;

    /**
     * @var int
     *
     * @ORM\Column(name="id_sender", type="integer", nullable=false)
     */
    private $idSender = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_receiver", type="integer", nullable=false)
     */
    private $idReceiver = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="msg", type="text", length=65535, nullable=true)
     */
    private $msg;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", nullable=false)
     */
    private $status = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $data = '0000-00-00 00:00:00';


}
