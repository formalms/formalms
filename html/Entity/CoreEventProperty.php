<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventProperty
 *
 * @ORM\Table(name="core_event_property")
 * @ORM\Entity
 */
class CoreEventProperty
{
    /**
     * @var string
     *
     * @ORM\Column(name="property_name", type="string", length=50, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $propertyName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $idevent = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="property_value", type="text", length=65535, nullable=false)
     */
    private $propertyValue;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="property_date", type="date", nullable=false, options={"default"="0000-00-00"})
     */
    private $propertyDate = '0000-00-00';


}
