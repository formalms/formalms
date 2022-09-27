<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreLangLanguage
 *
 * @ORM\Table(name="core_lang_language")
 * @ORM\Entity
 */
class CoreLangLanguage
{
    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
