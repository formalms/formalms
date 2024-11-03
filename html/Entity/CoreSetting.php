<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreSetting
 *
 * @ORM\Table(name="core_setting", indexes={
 *     @ORM\Index(name="param_name_idx", columns={"param_name"})
 * })
 * @ORM\Entity
 */
class CoreSetting
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
     * @ORM\Column(name="param_name", type="string", length=255, nullable=false)
     
     */
    private $paramName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="param_value", type="string", length=65536, nullable=false)
     */
    private $paramValue;

    /**
     * @var string
     *
     * @ORM\Column(name="value_type", type="string", length=25, nullable=false, options={"default"="string"})
     */
    private $valueType = 'string';

    /**
     * @var int
     *
     * @ORM\Column(name="max_size", type="integer", nullable=false, options={"default"="255"})
     */
    private $maxSize = 255;

    /**
     * @var string
     *
     * @ORM\Column(name="pack", type="string", length=25, nullable=false, options={"default"="main"})
     */
    private $pack = 'main';

    /**
     * @var int
     *
     * @ORM\Column(name="regroup", type="integer", nullable=false)
     */
    private $regroup = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="param_load", type="boolean", nullable=false, options={"default"="1"})
     */
    private $paramLoad = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="hide_in_modify", type="boolean", nullable=false)
     */
    private $hideInModify = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="extra_info", type="string", length=65536, nullable=false)
     */
    private $extraInfo;


}
