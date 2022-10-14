<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningScormResources
 *
 * @ORM\Table(name="learning_scorm_resources", uniqueConstraints={@ORM\UniqueConstraint(name="idsco_package_unique", columns={"idsco", "idscorm_package"})})
 * @ORM\Entity
 */
class LearningScormResources
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_resource", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idscormResource;

    /**
     * @var string
     *
     * @ORM\Column(name="idsco", type="string", length=255, nullable=false)
     */
    private $idsco = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idscorm_package", type="integer", nullable=false)
     */
    private $idscormPackage = '0';

    /**
     * @var array|null
     *
     * @ORM\Column(name="scormtype", type="simple_array", length=0, nullable=true)
     */
    private $scormtype;

    /**
     * @var string|null
     *
     * @ORM\Column(name="href", type="string", length=255, nullable=true)
     */
    private $href;


}
