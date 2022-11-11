<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreNewsletter
 *
 * @ORM\Table(name="core_newsletter")
 * @ORM\Entity
 */
class CoreNewsletter
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_send", type="integer", nullable=false)
     */
    private $idSend = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="sub", type="string", length=255, nullable=false)
     */
    private $sub = '';

    /**
     * @var string
     *
     * @ORM\Column(name="msg", type="text", length=65535, nullable=false)
     */
    private $msg;

    /**
     * @var string
     *
     * @ORM\Column(name="fromemail", type="string", length=255, nullable=false)
     */
    private $fromemail = '';

    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=false)
     */
    private $language = '';

    /**
     * @var int
     *
     * @ORM\Column(name="tot", type="integer", nullable=false)
     */
    private $tot = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="send_type", type="string", length=0, nullable=false, options={"default"="email"})
     */
    private $sendType = 'email';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stime", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $stime = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="text", length=65535, nullable=false)
     */
    private $file;


}
