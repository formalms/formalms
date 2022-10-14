<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateMetaAssign
 *
 * @ORM\Table(name="learning_certificate_meta_assign", indexes={
 *     @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *     @ORM\Index(name="id_meta_certificate_idx", columns={"idMetaCertificate"})
 * })
 * @ORM\Entity
 */
class LearningCertificateMetaAssign
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
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idMetaCertificate", type="integer", nullable=false)
     
     */
    private $idmetacertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCertificate", type="integer", nullable=false)
     */
    private $idcertificate = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="on_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $onDate = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="cert_file", type="string", length=255, nullable=false)
     */
    private $certFile = '';


}
