<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningCertificate
 *
 * @ORM\Table(name="learning_certificate")
 * @ORM\Entity
 */
class LearningCertificate
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id_certificate", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idCertificate;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="base_language", type="string", length=255, nullable=false)
     */
    private $baseLanguage = '';

    /**
     * @var string
     *
     * @ORM\Column(name="cert_structure", type="text", length=65535, nullable=false)
     */
    private $certStructure;

    /**
     * @var string
     *
     * @ORM\Column(name="orientation", type="string", length=0, nullable=false, options={"default"="P"})
     */
    private $orientation = 'P';

    /**
     * @var string
     *
     * @ORM\Column(name="bgimage", type="string", length=255, nullable=false)
     */
    private $bgimage = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="meta", type="boolean", nullable=false)
     */
    private $meta = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="user_release", type="boolean", nullable=false)
     */
    private $userRelease = '0';


}
