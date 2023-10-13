<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CorePrivacypolicyLang
 *
 * @ORM\Table(name="core_privacypolicy_lang", indexes={
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"}),
 *     @ORM\Index(name="id_policy_idx", columns={"id_policy"})
 * })
 * @ORM\Entity
 */
class CorePrivacypolicyLang
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
     * @ORM\Column(name="id_policy", type="integer", nullable=false, options={"unsigned"=true})

     */
    private $idPolicy = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
 
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="translation", type="string", length=65536, nullable=false)
     */
    private $translation;


}
