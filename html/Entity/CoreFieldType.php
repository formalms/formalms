<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreFieldType
 *
 * @ORM\Table(name="core_field_type")
 * @ORM\Entity
 */
class CoreFieldType
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
