<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningReservationCategory
 *
 * @ORM\Table(name="learning_reservation_category")
 * @ORM\Entity
 */
class LearningReservationCategory
{
    use Timestamps;    
      
    /**
     * @var int
     *
     * @ORM\Column(name="idCategory", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcategory;

    /**
     * @var int
     *
     * @ORM\Column(name="idCourse", type="integer", nullable=false)
     */
    private $idcourse = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name = '';

    /**
     * @var int
     *
     * @ORM\Column(name="maxSubscription", type="integer", nullable=false)
     */
    private $maxsubscription = '0';


}
