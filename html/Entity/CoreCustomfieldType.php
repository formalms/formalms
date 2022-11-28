<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreCustomfieldType
 *
 * @ORM\Table(name="core_customfield_type")
 * @ORM\Entity
 */
class CoreCustomfieldType
{
    /**
     * @var string
     *
     * @ORM\Column(name="type_field", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
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
