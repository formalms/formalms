<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificateMeta
 *
 * @ORM\Table(name="learning_certificate_meta")
 * @ORM\Entity
 */
class LearningCertificateMeta
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idMetaCertificate", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmetacertificate;

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
