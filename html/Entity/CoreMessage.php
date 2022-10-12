<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreMessage
 *
 * @ORM\Table(name="core_message")
 * @ORM\Entity
 */
class CoreMessage
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idMessage", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmessage;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sender", type="integer", nullable=false)
     */
    private $sender = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="posted", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $posted = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="textof", type="text", length=65535, nullable=false)
     */
    private $textof;

    /**
     * @var string
     *
     * @ORM\Column(name="attach", type="string", length=255, nullable=false)
     */
    private $attach = '';

    /**
     * @var int
     *
     * @ORM\Column(name="priority", type="integer", nullable=false)
     */
    private $priority = '0';


}
