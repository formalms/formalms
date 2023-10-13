<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRegSetting
 *
 * @ORM\Table(name="core_reg_setting", indexes={
 *     @ORM\Index(name="region_id_idx", columns={"region_id"}),
 *     @ORM\Index(name="val_name_idx", columns={"val_name"})
 * })
 * @ORM\Entity
 */
class CoreRegSetting
{

    use Timestamps;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="region_id", type="string", length=100, nullable=false)
     
     */
    private $regionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="val_name", type="string", length=100, nullable=false)
     
     */
    private $valName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value = '';


}
