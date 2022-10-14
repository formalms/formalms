<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreField
 *
 * @ORM\Table(name="core_field")
 * @ORM\Entity
 */
class CoreField
{
    /**
     * @var int
     *
     * @ORM\Column(name="idField", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idfield;

    /**
     * @var int
     *
     * @ORM\Column(name="id_common", type="integer", nullable=false)
     */
    private $idCommon = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="type_field", type="string", length=255, nullable=false)
     */
    private $typeField = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="translation", type="string", length=255, nullable=false)
     */
    private $translation = '';

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


}
