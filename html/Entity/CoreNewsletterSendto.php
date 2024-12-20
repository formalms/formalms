<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreNewsletterSendto
 *
 * @ORM\Table(name="core_newsletter_sendto", indexes={
 *     @ORM\Index(name="id_send_idx", columns={"id_send"}),
 *     @ORM\Index(name="idst_idx", columns={"idst"})
 * })
 * @ORM\Entity
 */
class CoreNewsletterSendto
{

    use Timestamps;
    
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
     * @ORM\Column(name="id_send", type="integer", nullable=false)
     
     */
    private $idSend = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     
     */
    private $idst = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="stime", type="datetime", nullable=true, options={"default"=NULL})
     */
    private $stime = null;


}
