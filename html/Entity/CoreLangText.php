<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreLangText
 *
 * @ORM\Table(name="core_lang_text", uniqueConstraints={@ORM\UniqueConstraint(name="text_key", columns={"text_key", "text_module", "plugin_id"})})
 * @ORM\Entity
 */
class CoreLangText
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_text", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idText;

    /**
     * @var string
     *
     * @ORM\Column(name="text_key", type="string", length=255, nullable=false)
     */
    private $textKey = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text_module", type="string", length=50, nullable=false)
     */
    private $textModule = '';

    /**
     * @var array
     *
     * @ORM\Column(name="text_attributes", type="simple_array", length=0, nullable=false)
     */
    private $textAttributes = '';

    /**
     * @var int
     *
     * @ORM\Column(name="plugin_id", type="integer", nullable=false)
     */
    private $pluginId = '0';


}
