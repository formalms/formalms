<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldType
 *
 * @ORM\Table(name="core_customfield_type", indexes={
 *     @ORM\Index(name="type_field_idx", columns={"type_field"})
 * })
 * @ORM\Entity
 */
class CoreCustomfieldType
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
     * @ORM\Column(name="type_field", type="string", length=255, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $typeField = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_file", type="string", length=255, nullable=false)
     */
    private $typeFile = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_class", type="string", length=255, nullable=false)
     */
    private $typeClass = '';

    /**
     * @var string
     *
     * @ORM\Column(name="type_category", type="string", length=255, nullable=false, options={"default"="standard"})
     */
    private $typeCategory = 'standard';


}
