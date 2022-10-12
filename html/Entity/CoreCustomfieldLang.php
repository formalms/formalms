<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldLang
 *
 * @ORM\Table(name="core_customfield_lang")
 * @ORM\Entity
 */
class CoreCustomfieldLang
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
     * @var int
     *
     * @ORM\Column(name="id_field", type="integer", nullable=false)
     */
    private $idField = '0';

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


}
