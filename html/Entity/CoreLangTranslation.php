<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreLangTranslation
 *
 * @ORM\Table(name="core_lang_translation", indexes={
 *     @ORM\Index(name="id_text_idx", columns={"id_text"}),
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"})
 * })
 * @ORM\Entity
 */
class CoreLangTranslation
{
    /**
     * @var int
     *
    * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_text", type="integer", nullable=false)
     
     */
    private $idText = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     
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
     * @ORM\Column(name="save_date", type="datetime", nullable=true, options={"default"="NULL"})
     */
    private $saveDate = null;


}
