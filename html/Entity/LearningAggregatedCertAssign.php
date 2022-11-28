<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAggregatedCertAssign
 *
 * @ORM\Table(name="learning_aggregated_cert_assign")
 * @ORM\Entity
 */
class LearningAggregatedCertAssign
{
    /**
     * @var int
     *
     * @ORM\Column(name="idUser", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $iduser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idCertificate", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idcertificate = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idAssociation", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
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
