<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningAggregatedCertMetadata
 *
 * @ORM\Table(name="learning_aggregated_cert_metadata")
 * @ORM\Entity
 */
class LearningAggregatedCertMetadata
{
    /**
     * @var int
     *
     * @ORM\Column(name="idAssociation", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idassociation;

    /**
     * @var int
     *
     * @ORM\Column(name="idCertificate", type="integer", nullable=false)
     */
    private $idcertificate = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=0, nullable=false)
     */
    private $description;


}
