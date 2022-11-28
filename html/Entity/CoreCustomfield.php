<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfield
 *
 * @ORM\Table(name="core_customfield")
 * @ORM\Entity
 */
class CoreCustomfield
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_field", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idField;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_field", type="string", length=255, nullable=false)
     */
    private $typeField = '';

    /**
     * @var int
     *
     * @ORM\Column(name="sequence", type="integer", nullable=false)
     */
    private $sequence = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="show_on_platform", type="string", length=255, nullable=false, options={"default"="framework,"})
     */
    private $showOnPlatform = 'framework,';

    /**
     * @var bool
     *
     * @ORM\Column(name="use_multilang", type="boolean", nullable=false)
     */
    private $useMultilang = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="area_code", type="string", length=255, nullable=false)
     */
    private $areaCode;


}
