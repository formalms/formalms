<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreLangTranslation
 *
 * @ORM\Table(name="core_lang_translation")
 * @ORM\Entity
 */
class CoreLangTranslation
{
    /**
     * @var int
     *
     * @ORM\Column(name="id_text", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idText = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $langCode = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="translation_text", type="text", length=65535, nullable=true)
     */
    private $translationText;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="save_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $saveDate = '0000-00-00 00:00:00';


}
