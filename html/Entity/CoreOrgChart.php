<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreOrgChart
 *
 * @ORM\Table(name="core_org_chart", indexes={
 *     @ORM\Index(name="id_dir_idx", columns={"id_dir"}),
 *     @ORM\Index(name="lang_code_idx", columns={"lang_code"}),
 * })
 * @ORM\Entity
 */
class CoreOrgChart
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
     * @var int
     *
     * @ORM\Column(name="id_dir", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idDir = '0';

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
     * @ORM\Column(name="translation", type="string", length=255, nullable=false)
     */
    private $translation = '';


}
