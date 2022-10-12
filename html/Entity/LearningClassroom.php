<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningClassroom
 *
 * @ORM\Table(name="learning_classroom")
 * @ORM\Entity
 */
class LearningClassroom
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idClassroom", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idclassroom;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="location_id", type="integer", nullable=false)
     */
    private $locationId = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="room", type="string", length=255, nullable=false)
     */
    private $room = '';

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=255, nullable=false)
     */
    private $street = '';

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=false)
     */
    private $city = '';

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="string", length=255, nullable=false)
     */
    private $state = '';

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=255, nullable=false)
     */
    private $zipCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=false)
     */
    private $phone = '';

    /**
     * @var string
     *
     * @ORM\Column(name="fax", type="string", length=255, nullable=false)
     */
    private $fax = '';

    /**
     * @var string
     *
     * @ORM\Column(name="capacity", type="string", length=255, nullable=false)
     */
    private $capacity = '';

    /**
     * @var string
     *
     * @ORM\Column(name="disposition", type="text", length=65535, nullable=false)
     */
    private $disposition;

    /**
     * @var string
     *
     * @ORM\Column(name="instrument", type="text", length=65535, nullable=false)
     */
    private $instrument;

    /**
     * @var string
     *
     * @ORM\Column(name="available_instrument", type="text", length=65535, nullable=false)
     */
    private $availableInstrument;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", length=65535, nullable=false)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="responsable", type="string", length=255, nullable=false)
     */
    private $responsable = '';


}
