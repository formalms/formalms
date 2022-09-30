<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreLangLanguage
 *
 * @ORM\Table(name="core_lang_language", indexes={
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"})
 * })
 * @ORM\Entity
 */
class CoreLangLanguage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_description", type="string", length=255, nullable=false)
     */
    private $langDescription = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_browsercode", type="string", length=50, nullable=false)
     */
    private $langBrowsercode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_direction", type="string", length=0, nullable=false, options={"default"="ltr"})
     */
    private $langDirection = 'ltr';


}
