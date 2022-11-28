<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePlatform
 *
 * @ORM\Table(name="core_platform")
 * @ORM\Entity
 */
class CorePlatform
{
    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $platform = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_file", type="string", length=255, nullable=false)
     */
    private $classFile = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name", type="string", length=255, nullable=false)
     */
    private $className = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_file_menu", type="string", length=255, nullable=false)
     */
    private $classFileMenu = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name_menu", type="string", length=255, nullable=false)
     */
    private $classNameMenu = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name_menu_managment", type="string", length=255, nullable=false)
     */
    private $classNameMenuManagment = '';

    /**
     * @var string
     *
     * @ORM\Column(name="file_class_config", type="string", length=255, nullable=false)
     */
    private $fileClassConfig = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_name_config", type="string", length=255, nullable=false)
     */
    private $classNameConfig = '';

    /**
     * @var string
     *
     * @ORM\Column(name="var_default_template", type="string", length=255, nullable=false)
     */
    private $varDefaultTemplate = '';

    /**
     * @var string
     *
     * @ORM\Column(name="class_default_admin", type="string", length=255, nullable=false)
     */
    private $classDefaultAdmin = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="is_active", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $isActive = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="mandatory", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $mandatory = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="dependencies", type="text", length=65535, nullable=false)
     */
    private $dependencies;

    /**
     * @var string
     *
     * @ORM\Column(name="main", type="string", length=0, nullable=false, options={"default"="true"})
     */
    private $main = 'true';

    /**
     * @var string
     *
     * @ORM\Column(name="hidden_in_config", type="string", length=0, nullable=false, options={"default"="false"})
     */
    private $hiddenInConfig = 'false';


}
