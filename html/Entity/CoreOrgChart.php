<?php



namespace FormaLms\Entity;

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
     * @ORM\Column(name="id_dir", type="integer", nullable=false)
     
     */
    private $idDir = '0';

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


}
