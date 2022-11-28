<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreNewsletterSendto
 *
 * @ORM\Table(name="core_newsletter_sendto")
 * @ORM\Entity
 */
class CoreNewsletterSendto
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_send", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idSend = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idst = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stime", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $stime = '0000-00-00 00:00:00';


}
