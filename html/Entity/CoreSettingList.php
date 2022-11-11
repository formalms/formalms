<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreSettingList
 *
 * @ORM\Table(name="core_setting_list", indexes={
 *     @ORM\Index(name="path_name_idx", columns={"path_name"})
 * })
 * @ORM\Entity
 */
class CoreSettingList
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
     * @var string
     *
     * @ORM\Column(name="path_name", type="string", length=255, nullable=false)
     
     */
    private $pathName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label = '';

    /**
     * @var string
     *
     * @ORM\Column(name="default_value", type="text", length=65535, nullable=false)
     */
    private $defaultValue;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=false)
     */
    private $type = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="visible", type="boolean", nullable=false)
     */
    private $visible = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="load_at_startup", type="boolean", nullable=false)
     */
    private $loadAtStartup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';


}
