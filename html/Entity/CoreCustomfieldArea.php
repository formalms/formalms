<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldArea
 *
 * @ORM\Table(name="core_customfield_area")
 * @ORM\Entity
 */
class CoreCustomfieldArea
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
     * @var string
     *
     * @ORM\Column(name="area_code", type="string", length=255, nullable=false)
     */
    private $areaCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="area_name", type="string", length=255, nullable=false)
     */
    private $areaName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="area_table", type="string", length=255, nullable=false)
     */
    private $areaTable = '';

    /**
     * @var string
     *
     * @ORM\Column(name="area_field", type="string", length=255, nullable=false)
     */
    private $areaField = '';


}
