<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFncroleLang
 *
 * @ORM\Table(name="core_fncrole_lang", indexes={
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"}),
 *     @ORM\Index(name="id_fncrole_idx", columns={"id_fncrole"})
 * })
 * @ORM\Entity
 */
class CoreFncroleLang
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
     * @ORM\Column(name="id_fncrole", type="integer", nullable=false, options={"unsigned"=true})

     */
    private $idFncrole = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=255, nullable=false)
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;


}
