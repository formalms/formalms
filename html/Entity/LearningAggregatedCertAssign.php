<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAggregatedCertAssign
 *
 * @ORM\Table(name="learning_aggregated_cert_assign", indexes={
 *      @ORM\Index(name="id_user_idx", columns={"idUser"}),
 *      @ORM\Index(name="id_certificate_idx", columns={"idCertificate"}),
 *      @ORM\Index(name="id_association_idx", columns={"idAssociation"})
 * })
 * @ORM\Entity
 */
class LearningAggregatedCertAssign
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
     * @ORM\Column(name="idCertificate", type="integer", nullable=false)
     
     */
    private $idcertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAssociation", type="integer", nullable=false)
     
     */
    private $idassociation;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="on_date", type="datetime", nullable=true)
     */
    private $onDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cert_file", type="string", length=255, nullable=false)
     */
    private $certFile = '';


}
