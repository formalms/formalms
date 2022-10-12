<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldEntry
 *
 * @ORM\Table(name="core_customfield_entry")
 * @ORM\Entity
 */
class CoreCustomfieldEntry
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
     * @ORM\Column(name="id_field", type="string", length=11, nullable=false)
     */
    private $idField = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_obj", type="integer", nullable=false)
     */
    private $idObj = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="obj_entry", type="text", length=65535, nullable=false)
     */
    private $objEntry;


}
