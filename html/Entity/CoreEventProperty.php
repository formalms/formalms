<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreEventProperty
 *
 * @ORM\Table(name="core_event_property", indexes={
 *     @ORM\Index(name="property_name_idx", columns={"property_name"}),
 *     @ORM\Index(name="id_event_idx", columns={"idEvent"})
 * })
 * @ORM\Entity
 */
class CoreEventProperty
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
     * @ORM\Column(name="property_name", type="string", length=50, nullable=false)
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $propertyName = '';

    /**
     * @var int
     *
     * @ORM\Column(name="idEvent", type="integer", nullable=false)
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
