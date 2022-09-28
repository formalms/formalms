<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRegSetting
 *
 * @ORM\Table(name="core_reg_setting")
 * @ORM\Entity
 */
class CoreRegSetting
{
    /**
     * @var string
     *
     * @ORM\Column(name="region_id", type="string", length=100, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $regionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="val_name", type="string", length=100, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $valName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value = '';


}
