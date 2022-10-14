<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreMenuUnder
 *
 * @ORM\Table(name="core_menu_under")
 * @ORM\Entity
 */
class CoreMenuUnder
{
    /**
     * @var int
     *
     * @ORM\Column(name="idUnder", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idunder;

    /**
     * @var int
     *
     * @ORM\Column(name="idMenu", type="integer", nullable=false)
     */
    private $idmenu = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="module_name", type="string", length=255, nullable=true)
     */
    private $moduleName;

    /**
     * @var string
     *
     * @ORM\Column(name="default_name", type="string", length=255, nullable=false)
     */
    private $defaultName = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="default_op", type="string", length=255, nullable=true)
     */
    private $defaultOp;

    /**
     * @var string|null
     *
     * @ORM\Column(name="associated_token", type="string", length=255, nullable=true)
     */
    private $associatedToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="of_platform", type="string", length=255, nullable=true)
     */
    private $ofPlatform;

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="class_file", type="string", length=255, nullable=true)
     */
    private $classFile;

    /**
     * @var string|null
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=true)
     */
    private $className;

    /**
     * @var string|null
     *
     * @ORM\Column(name="mvc_path", type="string", length=255, nullable=true)
     */
    private $mvcPath;


}
