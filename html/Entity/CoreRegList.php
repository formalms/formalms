<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRegList
 *
 * @ORM\Table(name="core_reg_list", indexes={
 *     @ORM\Index(name="region_id_idx", columns={"region_id"})
 * })
 * @ORM\Entity
 */
class CoreRegList
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"autoincrement":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="region_id", type="string", length=100, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $regionId = '';

    /**
     * @var string
     *
     * @ORM\Column(name="lang_code", type="string", length=50, nullable=false)
     */
    private $langCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="region_desc", type="string", length=255, nullable=false)
     */
    private $regionDesc = '';

    /**
     * @var bool
     *
     * @ORM\Column(name="default_region", type="boolean", nullable=false)
     */
    private $defaultRegion = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="browsercode", type="string", length=255, nullable=false)
     */
    private $browsercode = '';


}
