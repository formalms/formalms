<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningModule
 *
 * @ORM\Table(name="learning_module")
 * @ORM\Entity
 */
class LearningModule
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idModule", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmodule;

    /**
     * @var string
     *
     * @ORM\Column(name="module_name", type="string", length=255, nullable=false)
     */
    private $moduleName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="default_op", type="string", length=255, nullable=false)
     */
    private $defaultOp = '';

    /**
     * @var string
     *
     * @ORM\Column(name="default_name", type="string", length=255, nullable=false)
     */
    private $defaultName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="token_associated", type="string", length=100, nullable=false)
     */
    private $tokenAssociated = '';

    /**
     * @var string
     *
     * @ORM\Column(name="file_name", type="string", length=255, nullable=false)
     */
    private $fileName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false)
     */
    private $className = '';

    /**
     * @var string
     *
     * @ORM\Column(name="module_info", type="string", length=50, nullable=false)
     */
    private $moduleInfo = '';

    /**
     * @var string
     *
     * @ORM\Column(name="mvc_path", type="string", length=255, nullable=false)
     */
    private $mvcPath = '';


}
