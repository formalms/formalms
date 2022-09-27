<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * LearningClassLocation
 *
 * @ORM\Table(name="learning_class_location")
 * @ORM\Entity
 */
class LearningClassLocation
{
    /**
     * @var int
     *
     * @ORM\Column(name="location_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $locationId;

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=false)
     */
    private $location = '';


}
