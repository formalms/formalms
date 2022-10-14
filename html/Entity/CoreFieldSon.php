<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFieldSon
 *
 * @ORM\Table(name="core_field_son")
 * @ORM\Entity
 */
class CoreFieldSon
{
    /**
     * @var int
     *
     * @ORM\Column(name="idSon", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idson;

    /**
     * @var int
     *
     * @ORM\Column(name="idField", type="integer", nullable=false)
     */
    private $idfield = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id_common_son", type="integer", nullable=false)
     */
    private $idCommonSon = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
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
     * @var int
     *
     * @ORM\Column(name="selected", type="integer", nullable=false)
     */
    private $selected = '0';


}
